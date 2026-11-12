<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OrderWorkflow
{
    /**
     * @var array<string, list<string>>
     */
    public const TRANSITIONS = [
        'pending' => ['preparing', 'out_for_delivery', 'cancelled', 'confirmed'],
        'confirmed' => ['preparing', 'out_for_delivery', 'completed', 'cancelled'],
        'preparing' => ['out_for_delivery', 'completed', 'cancelled', 'ready'],
        'ready' => ['out_for_delivery', 'completed', 'cancelled'],
        'out_for_delivery' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function transition(
        int $orderId,
        string $newStatus,
        ?int $adminId,
        ?string $notes,
        ?int $customerUserId = null,
        ?Closure $afterTransition = null,
    ): object {
        return DB::transaction(function () use ($orderId, $newStatus, $adminId, $notes, $customerUserId, $afterTransition): object {
            $orderQuery = DB::table('orders')->where('order_id', $orderId);
            if ($customerUserId !== null) {
                $orderQuery->where('user_id', $customerUserId);
            }
            $order = $orderQuery->lockForUpdate()->first();
            abort_if($order === null, 404);

            if (! in_array($newStatus, self::TRANSITIONS[$order->order_status] ?? [], true)) {
                throw ValidationException::withMessages([
                    'order_status' => "An order cannot move from {$order->order_status} to {$newStatus}.",
                ]);
            }

            if ($customerUserId !== null && $newStatus === 'cancelled' && $order->order_status === 'out_for_delivery') {
                throw ValidationException::withMessages([
                    'order_status' => 'Orders that are already out for delivery cannot be cancelled.',
                ]);
            }

            $now = now();
            DB::table('orders')->where('order_id', $orderId)->update([
                'order_status' => $newStatus,
                'updated_at' => $now,
            ]);
            DB::table('order_status_history')->insert([
                'order_id' => $orderId,
                'admin_id' => $adminId,
                'status' => $newStatus,
                'notes' => $notes,
                'changed_at' => $now,
            ]);

            $deliveryStatus = match ($newStatus) {
                'out_for_delivery' => 'assigned',
                'completed' => 'delivered',
                'cancelled' => 'failed',
                default => null,
            };
            if ($deliveryStatus !== null) {
                DB::table('deliveries')->where('order_id', $orderId)->update([
                    'delivery_status' => $deliveryStatus,
                    'updated_at' => $now,
                ]);
            }

            if (Schema::hasTable('payments')) {
                if ($newStatus === 'completed') {
                    DB::table('payments')->where('order_id', $orderId)->update([
                        'payment_status' => 'paid',
                        'paid_at' => $now,
                        'updated_at' => $now,
                    ]);
                } elseif ($newStatus === 'cancelled') {
                    DB::table('payments')->where('order_id', $orderId)->update([
                        'payment_status' => 'cancelled',
                        'updated_at' => $now,
                    ]);
                }
            }

            if ($newStatus === 'cancelled') {
                $hasRestoredColumn = Schema::hasColumn('orders', 'stock_restored_at');
                $alreadyRestored = $hasRestoredColumn && $order->stock_restored_at !== null;

                if (! $alreadyRestored) {
                    if ($hasRestoredColumn) {
                        DB::table('orders')->where('order_id', $orderId)->update([
                            'stock_restored_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    $items = DB::table('order_items')
                        ->where('order_id', $orderId)
                        ->lockForUpdate()
                        ->get(['product_id', 'quantity']);

                    $productTotals = $items->groupBy('product_id')->map(fn ($group): int => (int) $group->sum('quantity'));

                    foreach ($productTotals as $productId => $quantity) {
                        if ($productId !== null && $quantity > 0) {
                            DB::table('products')->where('product_id', $productId)
                                ->increment('stock_quantity', $quantity, ['updated_at' => $now]);
                        }
                    }
                }
            }

            $afterTransition?->__invoke($order);

            return $order;
        });
    }
}
