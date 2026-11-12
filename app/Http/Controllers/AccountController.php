<?php

namespace App\Http\Controllers;

use App\Services\OrderConfirmationMailer;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $municipalities = DB::table('delivery_fees')
            ->orderBy('municipality')
            ->pluck('municipality');
        $addresses = DB::table('user_addresses')
            ->where('user_id', $user->user_id)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->get();
        $orders = DB::table('orders')
            ->where('user_id', $user->user_id)
            ->orderByDesc('placed_at')
            ->paginate(8, ['*'], 'orders_page');

        $orderIds = $orders->getCollection()->pluck('order_id');

        $orderItems = $orderIds->isEmpty()
            ? collect()
            : DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->orderBy('order_item_id')
                ->get()
                ->groupBy('order_id');

        $deliveries = $orderIds->isEmpty() || ! Schema::hasTable('deliveries')
            ? collect()
            : DB::table('deliveries')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->keyBy('order_id');

        $cancellationReasons = $orderIds->isEmpty() || ! Schema::hasTable('order_status_history')
            ? collect()
            : DB::table('order_status_history')
                ->whereIn('order_id', $orderIds)
                ->where('status', 'cancelled')
                ->orderByDesc('changed_at')
                ->get(['order_id', 'notes'])
                ->keyBy('order_id');

        return view('auth.account', [
            'user' => $user,
            'addresses' => $addresses,
            'municipalities' => $municipalities,
            'orders' => $orders,
            'orderItems' => $orderItems,
            'deliveries' => $deliveries,
            'cancellationReasons' => $cancellationReasons,
        ]);
    }

    public function cancelOrder(
        Request $request,
        int $orderId,
        OrderWorkflow $orderWorkflow,
        OrderConfirmationMailer $orderConfirmationMailer,
    ): RedirectResponse {
        $order = DB::table('orders')
            ->where('order_id', $orderId)
            ->where('user_id', $request->user()->user_id)
            ->first();
        abort_unless($order !== null, 404);

        if ($order->order_status === 'out_for_delivery') {
            throw ValidationException::withMessages([
                'order_status' => 'Orders that are already out for delivery cannot be cancelled.',
            ]);
        }

        if (! in_array($order->order_status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'order_status' => "An order cannot move from {$order->order_status} to cancelled.",
            ]);
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        $orderWorkflow->transition(
            $orderId,
            'cancelled',
            adminId: null,
            notes: $validated['cancellation_reason'],
            customerUserId: $request->user()->user_id,
        );

        try {
            $orderConfirmationMailer->sendCancelled($orderId, $validated['cancellation_reason']);
        } catch (\Throwable $e) {
            Log::error('Cancellation email failed: '.$e->getMessage(), ['order_id' => $orderId]);
        }

        return redirect()->route('account')->with('status', 'Your order has been cancelled.');
    }

    public function deleteOrder(Request $request, int $orderId): RedirectResponse
    {
        $user = $request->user();
        $order = DB::table('orders')
            ->where('order_id', $orderId)
            ->where('user_id', $user->user_id)
            ->first();

        abort_unless($order !== null, 404);

        if ($order->order_status === 'out_for_delivery') {
            throw ValidationException::withMessages([
                'order_status' => 'Orders that are currently out for delivery cannot be deleted.',
            ]);
        }

        DB::transaction(function () use ($orderId, $order): void {
            $hasRestoredColumn = Schema::hasColumn('orders', 'stock_restored_at');
            $alreadyRestored = $hasRestoredColumn && ($order->stock_restored_at ?? null) !== null;

            if (! $alreadyRestored && in_array($order->order_status, ['pending', 'confirmed'], true)) {
                $items = DB::table('order_items')
                    ->where('order_id', $orderId)
                    ->get(['product_id', 'quantity']);

                $productTotals = $items->groupBy('product_id')->map(fn ($group): int => (int) $group->sum('quantity'));

                foreach ($productTotals as $productId => $quantity) {
                    if ($productId !== null && $quantity > 0) {
                        DB::table('products')
                            ->where('product_id', $productId)
                            ->increment('stock_quantity', $quantity, ['updated_at' => now()]);
                    }
                }
            }

            if (Schema::hasTable('order_status_history')) {
                DB::table('order_status_history')->where('order_id', $orderId)->delete();
            }
            if (Schema::hasTable('payments')) {
                DB::table('payments')->where('order_id', $orderId)->delete();
            }
            if (Schema::hasTable('deliveries')) {
                DB::table('deliveries')->where('order_id', $orderId)->delete();
            }
            if (Schema::hasTable('order_items')) {
                DB::table('order_items')->where('order_id', $orderId)->delete();
            }

            DB::table('orders')->where('order_id', $orderId)->delete();
        });

        return redirect()->route('account')->with('status', 'Your order has been deleted from your history.');
    }

    public function clearOrders(Request $request): RedirectResponse
    {
        $user = $request->user();

        $orders = DB::table('orders')
            ->where('user_id', $user->user_id)
            ->where('order_status', '!=', 'out_for_delivery')
            ->get(['order_id', 'order_status']);

        if ($orders->isEmpty()) {
            return redirect()->route('account')->with('status', 'No orders available to clear.');
        }

        DB::transaction(function () use ($orders): void {
            $orderIds = $orders->pluck('order_id');

            $pendingOrderIds = $orders->whereIn('order_status', ['pending', 'confirmed'])->pluck('order_id');
            if ($pendingOrderIds->isNotEmpty() && Schema::hasTable('order_items')) {
                $items = DB::table('order_items')
                    ->whereIn('order_id', $pendingOrderIds)
                    ->get(['product_id', 'quantity']);

                foreach ($items as $item) {
                    if ($item->product_id !== null) {
                        DB::table('products')
                            ->where('product_id', $item->product_id)
                            ->increment('stock_quantity', $item->quantity, ['updated_at' => now()]);
                    }
                }
            }

            if (Schema::hasTable('order_status_history')) {
                DB::table('order_status_history')->whereIn('order_id', $orderIds)->delete();
            }
            if (Schema::hasTable('payments')) {
                DB::table('payments')->whereIn('order_id', $orderIds)->delete();
            }
            if (Schema::hasTable('deliveries')) {
                DB::table('deliveries')->whereIn('order_id', $orderIds)->delete();
            }
            if (Schema::hasTable('order_items')) {
                DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
            }

            DB::table('orders')->whereIn('order_id', $orderIds)->delete();
        });

        return redirect()->route('account')->with('status', 'Your order history has been cleared.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->user_id, 'user_id'),
            ],
            'phone_number' => ['nullable', 'string', 'max:25'],
            'current_password' => ['required', 'current_password'],
        ]);

        $user->fill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => mb_strtolower(trim($validated['email'])),
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        $emailChanged = $user->isDirty('email');
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                Log::error('Failed to send verification email after email update: '.$e->getMessage(), [
                    'user_id' => $user->user_id,
                ]);
            }

            return redirect()->route('account')->with('status', 'Your profile has been updated. A verification link has been sent to your new email address.');
        }

        return redirect()->route('account')->with('status', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        $request->session()->regenerate();

        return redirect()->route('account')->with('status', 'Your password has been changed.');
    }

    public function uploadPhoto(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=3000,max_height=3000'],
        ]);

        $user = $request->user();
        $previousPhotoPath = $user->profile_photo_path;
        $photoPath = $validated['photo']->storePublicly('profile-photos/'.$user->user_id, 'public');

        if ($photoPath === false) {
            throw new RuntimeException('The profile photo could not be saved.');
        }

        $user->profile_photo_path = $photoPath;
        $user->save();

        if ($previousPhotoPath !== null && ! Storage::disk('public')->delete($previousPhotoPath)) {
            throw new RuntimeException('The previous profile photo could not be deleted.');
        }

        return redirect()->route('account')->with('status', 'Your profile photo has been updated.');
    }

    public function deletePhoto(Request $request): RedirectResponse
    {
        $user = $request->user();
        $photoPath = $user->profile_photo_path;

        if ($photoPath !== null && ! Storage::disk('public')->delete($photoPath)) {
            throw new RuntimeException('The profile photo could not be deleted.');
        }

        $user->profile_photo_path = null;
        $user->save();

        return redirect()->route('account')->with('status', 'Your profile photo has been removed.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->addressRules());
        $user = $request->user();
        $isFirstAddress = ! DB::table('user_addresses')->where('user_id', $user->user_id)->exists();
        $isDefault = (bool) ($validated['is_default'] ?? false) || $isFirstAddress;
        unset($validated['is_default']);

        DB::transaction(function () use ($validated, $user, $isDefault): void {
            if ($isDefault) {
                DB::table('user_addresses')
                    ->where('user_id', $user->user_id)
                    ->update(['is_default' => false]);
            }

            DB::table('user_addresses')->insert([
                ...$validated,
                'user_id' => $user->user_id,
                'is_default' => $isDefault,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('account')->with('status', 'Your address has been saved.');
    }

    public function updateAddress(Request $request, int $addressId): RedirectResponse
    {
        $validated = $request->validate($this->addressRules());
        $user = $request->user();
        $address = $this->findUserAddress($user->user_id, $addressId);
        $wasDefault = (bool) $address->is_default;
        $isDefault = (bool) ($validated['is_default'] ?? false);
        unset($validated['is_default']);

        DB::transaction(function () use ($validated, $user, $addressId, $isDefault, $wasDefault): void {
            if ($isDefault) {
                DB::table('user_addresses')
                    ->where('user_id', $user->user_id)
                    ->update(['is_default' => false]);
            }

            DB::table('user_addresses')
                ->where('user_id', $user->user_id)
                ->where('address_id', $addressId)
                ->update([
                    ...$validated,
                    'is_default' => $isDefault,
                    'updated_at' => now(),
                ]);

            if (! $isDefault && $wasDefault) {
                $replacementAddressId = DB::table('user_addresses')
                    ->where('user_id', $user->user_id)
                    ->where('address_id', '!=', $addressId)
                    ->orderBy('address_id')
                    ->value('address_id');

                if ($replacementAddressId !== null) {
                    DB::table('user_addresses')
                        ->where('user_id', $user->user_id)
                        ->where('address_id', $replacementAddressId)
                        ->update([
                            'is_default' => true,
                            'updated_at' => now(),
                        ]);
                }
            }
        });

        return redirect()->route('account')->with('status', 'Your address has been updated.');
    }

    public function setDefaultAddress(Request $request, int $addressId): RedirectResponse
    {
        $user = $request->user();
        $this->findUserAddress($user->user_id, $addressId);

        DB::transaction(function () use ($user, $addressId): void {
            DB::table('user_addresses')
                ->where('user_id', $user->user_id)
                ->update(['is_default' => false]);

            DB::table('user_addresses')
                ->where('user_id', $user->user_id)
                ->where('address_id', $addressId)
                ->update([
                    'is_default' => true,
                    'updated_at' => now(),
                ]);
        });

        return redirect()->route('account')->with('status', 'Your default address has been changed.');
    }

    public function deleteAddress(Request $request, int $addressId): RedirectResponse
    {
        $user = $request->user();
        $address = $this->findUserAddress($user->user_id, $addressId);

        DB::transaction(function () use ($user, $addressId, $address): void {
            DB::table('user_addresses')
                ->where('user_id', $user->user_id)
                ->where('address_id', $addressId)
                ->delete();

            if ($address->is_default) {
                $replacementAddressId = DB::table('user_addresses')
                    ->where('user_id', $user->user_id)
                    ->orderBy('address_id')
                    ->value('address_id');

                if ($replacementAddressId !== null) {
                    DB::table('user_addresses')
                        ->where('user_id', $user->user_id)
                        ->where('address_id', $replacementAddressId)
                        ->update([
                            'is_default' => true,
                            'updated_at' => now(),
                        ]);
                }
            }
        });

        return redirect()->route('account')->with('status', 'Your address has been removed.');
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function addressRules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:200'],
            'phone_number' => ['required', 'string', 'max:25'],
            'address_line' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', Rule::exists('delivery_fees', 'municipality')],
            'province' => ['required', 'string', Rule::in(['Cebu'])],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    private function findUserAddress(int $userId, int $addressId): object
    {
        $address = DB::table('user_addresses')
            ->where('user_id', $userId)
            ->where('address_id', $addressId)
            ->first();

        abort_if($address === null, 404);

        return $address;
    }
}
