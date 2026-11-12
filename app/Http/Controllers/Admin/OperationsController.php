<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminActivityLogger;
use App\Services\OrderConfirmationMailer;
use App\Services\OrderWorkflow;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function dashboard(): View
    {
        $customerCount = (int) DB::table('orders')
            ->whereNotNull('customer_email')
            ->selectRaw('COUNT(DISTINCT LOWER(customer_email)) as customer_count')
            ->value('customer_count');

        return view('admin.dashboard', [
            'admin' => request()->user('admin'),
            'orderCount' => DB::table('orders')->count(),
            'pendingOrders' => DB::table('orders')->where('order_status', 'pending')->count(),
            'completedOrders' => DB::table('orders')->where('order_status', 'completed')->count(),
            'customerCount' => $customerCount,
            'productCount' => DB::table('products')->count(),
            'lowStockCount' => DB::table('products')->where('stock_quantity', '<=', 5)->count(),
            'completedSales' => DB::table('orders')->where('order_status', 'completed')->sum('grand_total'),
            'recentOrders' => $this->ordersQuery()->limit(8)->get(),
        ]);
    }

    public function orders(Request $request): View
    {
        $statuses = ['completed', 'preparing', 'out_for_delivery', 'cancelled'];
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'completed', 'cancelled'])],
        ]);
        $status = $validated['status'] ?? null;
        $orders = $this->ordersQuery()
            ->when($status, fn ($query) => $query->where('orders.order_status', $status))
            ->paginate(15)
            ->withQueryString();

        $orderIds = $orders->getCollection()->pluck('order_id');

        $items = $orderIds->isEmpty()
            ? collect()
            : DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->orderBy('order_item_id')
                ->get()
                ->groupBy('order_id');

        $cancellationReasons = $orderIds->isEmpty() || ! Schema::hasTable('order_status_history')
            ? collect()
            : DB::table('order_status_history')
                ->whereIn('order_id', $orderIds)
                ->where('status', 'cancelled')
                ->orderByDesc('changed_at')
                ->get(['order_id', 'notes'])
                ->keyBy('order_id');

        return view('admin.orders.index', [
            'admin' => $request->user('admin'),
            'orders' => $orders,
            'items' => $items,
            'statuses' => $statuses,
            'selectedStatus' => $status,
            'transitions' => OrderWorkflow::TRANSITIONS,
            'cancellationReasons' => $cancellationReasons,
        ]);
    }

    public function orderDetails(int $orderId): View
    {
        $order = $this->ordersQuery()->where('orders.order_id', $orderId)->first();
        abort_if($order === null, 404);

        return view('admin.orders.show', [
            'admin' => request()->user('admin'),
            'order' => $order,
            'items' => DB::table('order_items')->where('order_id', $orderId)->orderBy('order_item_id')->get(),
            'paymentMethod' => DB::table('payments')
                ->join('payment_methods', 'payment_methods.payment_method_id', '=', 'payments.payment_method_id')
                ->where('payments.order_id', $orderId)
                ->value('payment_methods.method_name'),
            'history' => DB::table('order_status_history')
                ->leftJoin('admins', 'admins.admin_id', '=', 'order_status_history.admin_id')
                ->where('order_status_history.order_id', $orderId)
                ->orderBy('order_status_history.changed_at')
                ->get([
                    'order_status_history.status',
                    'order_status_history.notes',
                    'order_status_history.changed_at',
                    'admins.first_name',
                    'admins.last_name',
                ]),
            'transitions' => OrderWorkflow::TRANSITIONS,
        ]);
    }

    public function updateOrder(
        Request $request,
        int $orderId,
        AdminActivityLogger $activityLogger,
        OrderWorkflow $orderWorkflow,
        OrderConfirmationMailer $orderConfirmationMailer,
    ): RedirectResponse {
        $validated = $request->validate([
            'order_status' => ['required', Rule::in(['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:500'],
            'cancellation_reason' => ['required_if:order_status,cancelled', 'nullable', 'string', 'max:500'],
            'payment_status' => ['nullable', Rule::in(['pending', 'paid', 'failed', 'refunded'])],
            'transaction_reference' => ['nullable', 'string', 'max:150'],
        ]);

        if (isset($validated['payment_status']) || isset($validated['transaction_reference'])) {
            $paymentUpdates = ['updated_at' => now()];
            if (isset($validated['payment_status'])) {
                $paymentUpdates['payment_status'] = $validated['payment_status'];
                if ($validated['payment_status'] === 'paid') {
                    $paymentUpdates['paid_at'] = now();
                }
            }
            if (isset($validated['transaction_reference'])) {
                $paymentUpdates['transaction_reference'] = $validated['transaction_reference'];
            }
            DB::table('payments')->where('order_id', $orderId)->update($paymentUpdates);
        }

        $adminId = $request->user('admin')->admin_id;
        $orderWorkflow->transition(
            $orderId,
            $validated['order_status'],
            $adminId,
            $validated['order_status'] === 'cancelled'
                ? $validated['cancellation_reason']
                : ($validated['notes'] ?? null),
            afterTransition: function (object $order) use ($activityLogger, $orderId, $validated): void {
                $activityLogger->record('updated', 'order', $orderId, "Order {$order->order_number} status set to {$validated['order_status']}.");
            },
        );

        if ($validated['order_status'] === 'completed') {
            try {
                $orderConfirmationMailer->sendDelivered($orderId);
            } catch (\Throwable $e) {
                Log::error('Delivery email failed: '.$e->getMessage(), ['order_id' => $orderId]);
            }
        } elseif ($validated['order_status'] === 'cancelled') {
            try {
                $orderConfirmationMailer->sendCancelled($orderId, $validated['cancellation_reason']);
            } catch (\Throwable $e) {
                Log::error('Cancellation email failed: '.$e->getMessage(), ['order_id' => $orderId]);
            }
        }

        $statusMessage = match ($validated['order_status']) {
            'completed' => 'Order marked as delivered and customer notified by email.',
            'cancelled' => 'Order cancelled and customer notified by email.',
            default => 'Order status updated.',
        };

        return redirect()->route('admin.orders.index')->with('status', $statusMessage);
    }

    public function inventory(): View
    {
        return view('admin.inventory.index', [
            'admin' => request()->user('admin'),
            'products' => DB::table('products')
                ->join('categories', 'categories.category_id', '=', 'products.category_id')
                ->select('products.product_id', 'products.product_name', 'products.stock_quantity', 'categories.category_name')
                ->orderBy('products.stock_quantity')
                ->orderBy('products.product_name')
                ->paginate(20),
        ]);
    }

    public function updateInventory(Request $request, int $productId, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate([
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);
        $product = DB::table('products')->where('product_id', $productId)->first();
        abort_if($product === null, 404);

        DB::table('products')->where('product_id', $productId)->update([
            'stock_quantity' => $validated['stock_quantity'],
            'updated_at' => now(),
        ]);
        $activityLogger->record('updated', 'inventory', $productId, "Stock for {$product->product_name} changed from {$product->stock_quantity} to {$validated['stock_quantity']}.");

        return redirect()->route('admin.inventory.index')->with('status', 'Inventory updated.');
    }

    public function customers(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $customerSummaries = DB::table('orders as customer_orders')
            ->whereNotNull('customer_orders.customer_email')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('customer_orders.customer_email', 'like', "%{$search}%")
                    ->orWhereExists(function ($nameQuery) use ($search): void {
                        $nameQuery->selectRaw('1')
                            ->from('orders as named_orders')
                            ->whereRaw('LOWER(named_orders.customer_email) = LOWER(customer_orders.customer_email)')
                            ->where('named_orders.recipient_name', 'like', "%{$search}%");
                    });
            }))
            ->selectRaw('LOWER(customer_orders.customer_email) as email, COUNT(*) as order_count, MAX(customer_orders.placed_at) as last_order_at')
            ->selectRaw("SUM(CASE WHEN customer_orders.order_status = 'completed' THEN customer_orders.grand_total ELSE 0 END) as completed_total")
            ->groupByRaw('LOWER(customer_orders.customer_email)');
        $latestCustomerOrders = DB::table('orders as latest_orders')
            ->whereNotNull('latest_orders.customer_email')
            ->selectRaw('LOWER(latest_orders.customer_email) as email, latest_orders.recipient_name, latest_orders.recipient_phone')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY LOWER(latest_orders.customer_email) ORDER BY latest_orders.placed_at DESC, latest_orders.order_id DESC) as customer_order_rank');
        $customers = DB::query()
            ->fromSub($customerSummaries, 'customer_summaries')
            ->joinSub($latestCustomerOrders, 'latest_customer_orders', function (JoinClause $join): void {
                $join->on('latest_customer_orders.email', '=', 'customer_summaries.email')
                    ->where('latest_customer_orders.customer_order_rank', '=', 1);
            })
            ->select([
                'customer_summaries.email',
                'customer_summaries.order_count',
                'customer_summaries.last_order_at',
                'latest_customer_orders.recipient_name',
                'latest_customer_orders.recipient_phone as phone_number',
                'customer_summaries.completed_total',
            ])
            ->orderByDesc('customer_summaries.last_order_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', [
            'admin' => $request->user('admin'),
            'customers' => $customers,
            'search' => $search,
        ]);
    }

    private function ordersQuery(): Builder
    {
        return DB::table('orders')
            ->leftJoin('users', 'users.user_id', '=', 'orders.user_id')
            ->leftJoin('payments', 'payments.order_id', '=', 'orders.order_id')
            ->selectRaw("orders.order_id, orders.order_number, orders.order_status, orders.subtotal, orders.delivery_fee, orders.discount_amount, orders.coupon_code, orders.grand_total, orders.recipient_name, orders.recipient_phone, orders.delivery_address, orders.delivery_notes, orders.placed_at, COALESCE(users.first_name, orders.recipient_name) as first_name, COALESCE(users.last_name, '') as last_name, COALESCE(orders.customer_email, users.email) as email, payments.payment_status")
            ->orderByDesc('orders.placed_at');
    }
}
