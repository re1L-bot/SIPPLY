<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OrderConfirmationMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use RuntimeException;

class CheckoutController extends Controller
{
    public function applyCoupon(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);
        $code = strtoupper(trim((string) ($validated['coupon_code'] ?? '')));

        if ($code === '') {
            $request->session()->forget('checkout.coupon_code');

            return redirect()->route('checkout.index')->with('status', 'Coupon removed.');
        }

        if ($this->activeCoupon($code) === null) {
            return redirect()->route('checkout.index')
                ->withErrors(['coupon_code' => 'This coupon is invalid, inactive, expired, or has reached its usage limit.'])
                ->withInput(['coupon_code' => $code]);
        }

        $request->session()->put('checkout.coupon_code', $code);

        return redirect()->route('checkout.index')->with('status', 'Coupon applied.');
    }

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $selectedItems = $request->session()->get('checkout.selected_items');
        if ($selectedItems !== null) {
            $request->session()->forget('checkout.buy_now');
        }
        $buyNow = $request->session()->get('checkout.buy_now');
        $lines = $this->checkoutLines(
            $user,
            $buyNow,
            $request->session()->get('guest_cart', []),
            is_array($selectedItems) ? $selectedItems : null,
        );

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')
                ->withErrors(['cart' => 'Add an available drink to your cart before checkout.']);
        }

        if ($lines->contains(fn (object $line): bool => ! $line->is_available || $line->quantity > $line->stock_quantity)) {
            return redirect()->route('cart.index')
                ->withErrors(['cart' => 'Sorry! This product is no longer available in the requested quantity. Please update your cart.']);
        }
        if ($lines->groupBy('product_id')->contains(
            fn (Collection $productLines): bool => $productLines->sum('quantity') > $productLines->first()->stock_quantity,
        )) {
            return redirect()->route('cart.index')
                ->withErrors(['cart' => 'Sorry! This product is no longer available in the requested quantity. Please update your cart.']);
        }

        $addresses = $user === null
            ? collect()
            : DB::table('user_addresses')
                ->where('user_id', $user->user_id)
                ->orderByDesc('is_default')
                ->orderByDesc('updated_at')
                ->get();
        $deliveryFees = DB::table('delivery_fees')->pluck('fee', 'municipality');
        $selectedAddress = $addresses->firstWhere('address_id', old('address_id'))
            ?? $addresses->firstWhere('is_default', true)
            ?? $addresses->first();
        $selectedMunicipality = $addresses->isNotEmpty()
            ? $selectedAddress?->city
            : old('city');
        $deliveryFee = $deliveryFees->get($selectedMunicipality, '0.00');
        $sessionCouponCode = $request->session()->get('checkout.coupon_code');
        $coupon = $this->activeCoupon(is_string($sessionCouponCode) ? $sessionCouponCode : null);
        $discountAmount = $coupon === null ? '0.00' : $this->couponDiscount($coupon, $this->subtotal($lines));
        $paymentMethods = DB::table('payment_methods')
            ->where('is_active', true)
            ->orderBy('payment_method_id')
            ->get();

        if ($paymentMethods->isEmpty()) {
            return redirect()->route('cart.index')
                ->withErrors(['payment_method_id' => 'No payment methods are currently available. Please contact the store.']);
        }

        return view('checkout.index', [
            'lines' => $lines,
            'subtotal' => $this->subtotal($lines),
            'addresses' => $addresses,
            'paymentMethods' => $paymentMethods,
            'municipalities' => $deliveryFees->keys(),
            'deliveryFees' => $deliveryFees,
            'deliveryFee' => $deliveryFee,
            'coupon' => $coupon,
            'discountAmount' => $discountAmount,
            'selectedAddressId' => $selectedAddress?->address_id,
            'buyNow' => $request->session()->has('checkout.buy_now'),
            'user' => $user,
        ]);
    }

    public function store(Request $request, OrderConfirmationMailer $orderConfirmationMailer): RedirectResponse
    {
        $user = $request->user();
        $hasSavedAddresses = $user !== null && DB::table('user_addresses')
            ->where('user_id', $user->user_id)
            ->exists();
        $validated = $request->validate([
            'address_id' => $user === null ? ['prohibited'] : [
                $hasSavedAddresses ? 'required' : 'nullable',
                'integer',
                Rule::exists('user_addresses', 'address_id')
                    ->where(fn ($query) => $query->where('user_id', $user->user_id)),
            ],
            'email' => [$user === null ? 'required' : 'nullable', 'email', 'max:254'],
            'recipient_name' => ['required_without:address_id', 'nullable', 'string', 'max:200'],
            'phone_number' => ['required_without:address_id', 'nullable', 'string', 'max:25'],
            'address_line' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'barangay' => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'city' => [
                'required_without:address_id',
                'nullable',
                'string',
                Rule::exists('delivery_fees', 'municipality'),
            ],
            'province' => ['required_without:address_id', 'nullable', 'string', Rule::in(['Cebu'])],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'save_address' => ['sometimes', 'boolean'],
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('payment_methods', 'payment_method_id')->where('is_active', true),
            ],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $order = DB::transaction(function () use ($request, $user, $validated): object {
                if ($user !== null) {
                    DB::table('users')
                        ->where('user_id', $user->user_id)
                        ->lockForUpdate()
                        ->first();
                }

                $selectedItems = $request->session()->get('checkout.selected_items');
                if ($selectedItems !== null) {
                    $request->session()->forget('checkout.buy_now');
                }
                $buyNow = $request->session()->get('checkout.buy_now');
                $cart = null;
                $selectedItems = is_array($selectedItems) ? $selectedItems : null;

                if (is_array($buyNow)) {
                    $requestedItems = collect([[
                        'product_id' => (int) ($buyNow['product_id'] ?? 0),
                        'size_id' => isset($buyNow['size_id']) ? (int) $buyNow['size_id'] : null,
                        'quantity' => (int) ($buyNow['quantity'] ?? 0),
                    ]]);
                } elseif ($user !== null) {
                    $cart = DB::table('carts')
                        ->where('user_id', $user->user_id)
                        ->where('cart_status', 'active')
                        ->orderBy('cart_id')
                        ->lockForUpdate()
                        ->first();

                    if ($cart === null) {
                        throw ValidationException::withMessages([
                            'cart' => 'Your cart is empty.',
                        ]);
                    }

                    $requestedItems = DB::table('cart_items')
                        ->where('cart_id', $cart->cart_id)
                        ->when($selectedItems !== null, fn ($q) => $q->whereIn('cart_item_id', $selectedItems))
                        ->orderBy('product_id')
                        ->lockForUpdate()
                        ->get(['cart_item_id', 'product_id', 'size_id', 'quantity'])
                        ->map(fn (object $item): array => [
                            'product_id' => (int) $item->product_id,
                            'size_id' => $item->size_id === null ? null : (int) $item->size_id,
                            'quantity' => (int) $item->quantity,
                        ])
                        ->values();
                } else {
                    $requestedItems = $this->guestCheckoutItems($request->session()->get('guest_cart', []), $selectedItems);
                }

                if ($requestedItems->isEmpty()) {
                    throw ValidationException::withMessages([
                        'cart' => 'Your cart is empty.',
                    ]);
                }

                $requestedItems = $this->completeSizeSelections($requestedItems);
                if ($requestedItems->contains(fn (array $item): bool => ! isset($item['quantity']) || ! is_numeric($item['quantity']) || (int) $item['quantity'] < 1 || (int) $item['quantity'] > 99)) {
                    throw ValidationException::withMessages([
                        'cart' => 'Invalid order quantity. Please update your cart.',
                    ]);
                }

                // Deterministically sort product IDs to prevent deadlocks across concurrent transactions
                $productIds = $requestedItems->pluck('product_id')->unique()->sort()->values();

                // 1. Validate product availability and lock products in consistent ascending order
                $products = DB::table('products')
                    ->whereIn('product_id', $productIds)
                    ->orderBy('product_id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                $productQuantities = $requestedItems->groupBy('product_id')->map(fn ($items): int => (int) $items->sum('quantity'));

                foreach ($productQuantities as $productId => $quantity) {
                    $product = $products->get((int) $productId);
                    if ($product === null || ! $product->is_available || $quantity > $product->stock_quantity) {
                        throw ValidationException::withMessages([
                            'cart' => 'Sorry! This product is no longer available in the requested quantity. Please update your cart.',
                        ]);
                    }
                }

                $sizes = DB::table('product_sizes')
                    ->whereIn('size_id', $requestedItems->pluck('size_id')->unique())
                    ->where('is_active', true)
                    ->get()
                    ->keyBy('size_id');

                $sizeIds = $requestedItems->pluck('size_id')->filter()->unique();
                $specificPrices = Schema::hasTable('product_size_prices') && $productIds->isNotEmpty()
                    ? DB::table('product_size_prices')
                        ->whereIn('product_id', $productIds)
                        ->whereIn('size_id', $sizeIds)
                        ->get()
                        ->keyBy(fn ($r) => "{$r->product_id}-{$r->size_id}")
                    : collect();

                $lines = $requestedItems->map(function (array $item) use ($products, $sizes, $specificPrices): object {
                    $product = $products->get($item['product_id']);

                    if ($product === null || ! $product->is_available) {
                        throw ValidationException::withMessages([
                            'cart' => 'Sorry! This product is no longer available in the requested quantity. Please update your cart.',
                        ]);
                    }

                    $size = $sizes->get($item['size_id']);
                    if ($size === null) {
                        throw ValidationException::withMessages([
                            'cart' => 'A selected size is no longer available. Update your cart and try again.',
                        ]);
                    }

                    $key = "{$product->product_id}-{$size->size_id}";
                    $specific = $specificPrices->get($key);
                    $unitPrice = $specific !== null
                        ? (float) $specific->price
                        : ((float) $product->base_price + (float) $size->price_adjustment);

                    return (object) [
                        'product_id' => $product->product_id,
                        'product_name' => $product->product_name,
                        'size_id' => $size->size_id,
                        'size_name' => $size->size_name,
                        'quantity' => (int) $item['quantity'],
                        'unit_price' => number_format($unitPrice, 2, '.', ''),
                        'line_total' => number_format($unitPrice * (int) $item['quantity'], 2, '.', ''),
                        'stock_quantity' => $product->stock_quantity,
                    ];
                });

                $now = now();

                // 2. Atomic Stock Deductions: Deduct product inventory with conditional SQL update
                foreach ($productIds as $productId) {
                    $totalToDeduct = $productQuantities->get($productId, 0);
                    if ($totalToDeduct <= 0) {
                        continue;
                    }

                    $affectedRows = DB::table('products')
                        ->where('product_id', $productId)
                        ->where('is_available', true)
                        ->where('stock_quantity', '>=', $totalToDeduct)
                        ->decrement('stock_quantity', $totalToDeduct, ['updated_at' => $now]);

                    if ($affectedRows !== 1) {
                        throw ValidationException::withMessages([
                            'cart' => 'Sorry! This product is no longer available in the requested quantity. Please update your cart.',
                        ]);
                    }
                }

                $address = $this->resolveDeliveryAddress($user, $validated);
                $deliveryFee = $this->deliveryFeeForMunicipality($address->city);
                $paymentMethod = DB::table('payment_methods')
                    ->where('payment_method_id', $validated['payment_method_id'])
                    ->where('is_active', true)
                    ->first();

                if ($paymentMethod === null) {
                    throw ValidationException::withMessages([
                        'payment_method_id' => 'This payment method is no longer available.',
                    ]);
                }

                $subtotal = $this->subtotal($lines);
                $couponCode = $request->session()->get('checkout.coupon_code');
                $coupon = null;
                $discountAmount = '0.00';
                if (is_string($couponCode) && $couponCode !== '') {
                    $coupon = DB::table('discount_coupons')
                        ->where('code', strtoupper($couponCode))
                        ->lockForUpdate()
                        ->first();
                    if (! $this->couponIsValid($coupon)) {
                        throw ValidationException::withMessages([
                            'coupon_code' => 'This coupon is no longer valid. Apply a valid coupon and try again.',
                        ]);
                    }
                    $discountAmount = $this->couponDiscount($coupon, $subtotal);
                }
                $grandTotal = number_format(max(0, (float) $subtotal + (float) $deliveryFee - (float) $discountAmount), 2, '.', '');
                $orderNumber = 'SP-'.Str::upper(Str::random(12));

                // 3. Create the order
                $orderId = DB::table('orders')->insertGetId([
                    'user_id' => $user?->user_id,
                    'address_id' => $address->address_id,
                    'customer_email' => $user?->email ?? $validated['email'],
                    'order_number' => $orderNumber,
                    'order_status' => 'pending',
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'discount_amount' => $discountAmount,
                    'coupon_code' => $coupon?->code,
                    'grand_total' => $grandTotal,
                    'recipient_name' => $address->recipient_name,
                    'recipient_phone' => $address->phone_number,
                    'delivery_address' => $this->formatAddress($address),
                    'delivery_notes' => $validated['delivery_notes'] ?? null,
                    'placed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($coupon !== null) {
                    DB::table('discount_coupons')->where('coupon_id', $coupon->coupon_id)->increment('used_count', 1, [
                        'updated_at' => $now,
                    ]);
                }

                // 4. Create order items
                foreach ($lines as $line) {
                    DB::table('order_items')->insert([
                        'order_id' => $orderId,
                        'product_id' => $line->product_id,
                        'product_name' => $line->product_name,
                        'size_name' => $line->size_name,
                        'quantity' => $line->quantity,
                        'unit_price' => $line->unit_price,
                        'line_total' => $line->line_total,
                    ]);
                }

                // 5. Save relevant order and payment information
                DB::table('payments')->insert([
                    'order_id' => $orderId,
                    'payment_method_id' => $paymentMethod->payment_method_id,
                    'amount' => $grandTotal,
                    'payment_status' => 'pending',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('deliveries')->insert([
                    'order_id' => $orderId,
                    'delivery_status' => 'pending',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('order_status_history')->insert([
                    'order_id' => $orderId,
                    'status' => 'pending',
                    'notes' => 'Order placed by customer.',
                    'changed_at' => $now,
                ]);

                if ($cart !== null) {
                    if ($selectedItems !== null) {
                        DB::table('cart_items')
                            ->where('cart_id', $cart->cart_id)
                            ->whereIn('cart_item_id', $selectedItems)
                            ->delete();
                        $remainingItems = DB::table('cart_items')->where('cart_id', $cart->cart_id)->exists();
                        if (! $remainingItems) {
                            DB::table('carts')
                                ->where('cart_id', $cart->cart_id)
                                ->update(['cart_status' => 'converted', 'updated_at' => $now]);
                        }
                    } else {
                        DB::table('cart_items')->where('cart_id', $cart->cart_id)->delete();
                        DB::table('carts')
                            ->where('cart_id', $cart->cart_id)
                            ->update(['cart_status' => 'converted', 'updated_at' => $now]);
                    }
                }

                return (object) [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                ];
            }, 5);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Checkout processing error: '.$e->getMessage(), [
                'user_id' => $user?->user_id,
                'trace' => $e->getTraceAsString(),
            ]);

            throw ValidationException::withMessages([
                'cart' => 'Sorry! This product is no longer available in the requested quantity. Please update your cart.',
            ]);
        }

        $wasBuyNow = is_array($request->session()->get('checkout.buy_now'));
        $request->session()->forget('checkout.buy_now');
        $request->session()->forget('checkout.coupon_code');
        $request->session()->forget('checkout.selected_items');
        if ($user === null && ! $wasBuyNow) {
            if ($selectedItems !== null) {
                $guestCart = $request->session()->get('guest_cart', []);
                foreach ($guestCart as $key => $line) {
                    $productId = is_array($line) && isset($line['product_id']) ? (int) $line['product_id'] : (int) $key;
                    $sizeId = is_array($line) && isset($line['size_id']) ? (int) $line['size_id'] : null;
                    $formattedKey = "p{$productId}-s{$sizeId}";

                    if (in_array((string) $key, $selectedItems, true)
                        || in_array($formattedKey, $selectedItems, true)
                        || in_array((string) $productId, $selectedItems, true)) {
                        unset($guestCart[$key]);
                    }
                }
                $request->session()->put('guest_cart', $guestCart);
            } else {
                $request->session()->forget('guest_cart');
            }
        }
        $customerEmail = $user?->email ?? $validated['email'];
        $emailSent = true;

        try {
            $orderConfirmationMailer->send($order->order_id, $customerEmail);
        } catch (PHPMailerException|RuntimeException $exception) {
            $emailSent = false;
            Log::error('Order placed but confirmation email could not be sent.', [
                'order_id' => $order->order_id,
                'user_id' => $user?->user_id,
                'exception_class' => $exception::class,
            ]);
        }

        $response = redirect()->route($user === null ? 'home' : 'account')->with(
            'status',
            "Order {$order->order_number} placed successfully.",
        );

        if (! $emailSent) {
            $response->with('email_warning', 'Your order was placed, but we could not send the confirmation email. Please contact the store.');
        }

        return $response;
    }

    private function checkoutLines(?User $user, mixed $buyNow, array $guestItems, ?array $selectedItems = null): Collection
    {
        if (is_array($buyNow)) {
            $requestedItems = collect([[
                'product_id' => (int) ($buyNow['product_id'] ?? 0),
                'size_id' => isset($buyNow['size_id']) ? (int) $buyNow['size_id'] : null,
                'quantity' => (int) ($buyNow['quantity'] ?? 0),
            ]]);
        } elseif ($user === null) {
            $requestedItems = $this->guestCheckoutItems($guestItems, $selectedItems);
        } else {
            $cart = DB::table('carts')
                ->where('user_id', $user->user_id)
                ->where('cart_status', 'active')
                ->orderBy('cart_id')
                ->first();

            if ($cart === null) {
                return collect();
            }

            $requestedItems = DB::table('cart_items')
                ->where('cart_id', $cart->cart_id)
                ->when($selectedItems !== null, fn ($q) => $q->whereIn('cart_item_id', $selectedItems))
                ->orderBy('product_id')
                ->get(['cart_item_id', 'product_id', 'size_id', 'quantity'])
                ->map(fn (object $item): array => [
                    'product_id' => (int) $item->product_id,
                    'size_id' => $item->size_id === null ? null : (int) $item->size_id,
                    'quantity' => (int) $item->quantity,
                ])
                ->values();
        }

        $requestedItems = $this->completeSizeSelections($requestedItems);
        $productIds = $requestedItems->pluck('product_id')->unique();
        $sizeIds = $requestedItems->pluck('size_id')->filter()->unique();
        $products = DB::table('products')
            ->whereIn('product_id', $productIds)
            ->get(['product_id', 'product_name', 'base_price', 'stock_quantity', 'is_available'])
            ->keyBy('product_id');
        $sizes = DB::table('product_sizes')
            ->whereIn('size_id', $sizeIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('size_id');
        $specificPrices = Schema::hasTable('product_size_prices') && $productIds->isNotEmpty()
            ? DB::table('product_size_prices')
                ->whereIn('product_id', $productIds)
                ->whereIn('size_id', $sizeIds)
                ->get()
                ->keyBy(fn ($r) => "{$r->product_id}-{$r->size_id}")
            : collect();

        return $requestedItems->map(function (array $item) use ($products, $sizes, $specificPrices): object {
            $product = $products->get($item['product_id']);
            $size = $sizes->get($item['size_id']);

            if ($product === null || $size === null) {
                return (object) [
                    'product_id' => $item['product_id'],
                    'product_name' => 'Unavailable drink',
                    'size_name' => null,
                    'quantity' => $item['quantity'],
                    'unit_price' => 0,
                    'line_total' => 0,
                    'stock_quantity' => 0,
                    'is_available' => false,
                ];
            }

            $key = "{$product->product_id}-{$size->size_id}";
            $specific = $specificPrices->get($key);
            $unitPrice = $specific !== null
                ? (float) $specific->price
                : ((float) $product->base_price + (float) $size->price_adjustment);

            return (object) [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'size_name' => $size->size_name,
                'quantity' => $item['quantity'],
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'line_total' => number_format($unitPrice * $item['quantity'], 2, '.', ''),
                'stock_quantity' => $product->stock_quantity,
                'is_available' => (bool) $product->is_available,
            ];
        });
    }

    /**
     * @param  array<int|string, mixed>  $guestItems
     * @param  array<int, string>|null  $selectedItems
     * @return Collection<int, array{product_id: int, size_id: int|null, quantity: int}>
     */
    private function guestCheckoutItems(array $guestItems, ?array $selectedItems = null): Collection
    {
        return collect($guestItems)
            ->filter(function (mixed $line, int|string $key) use ($selectedItems): bool {
                if ($selectedItems === null) {
                    return true;
                }

                $productId = is_array($line) && isset($line['product_id']) ? (int) $line['product_id'] : (int) $key;
                $sizeId = is_array($line) && isset($line['size_id']) ? (int) $line['size_id'] : null;
                $formattedKey = "p{$productId}-s{$sizeId}";

                return in_array((string) $key, $selectedItems, true)
                    || in_array($formattedKey, $selectedItems, true)
                    || in_array((string) $productId, $selectedItems, true);
            })
            ->map(function (mixed $line, int|string $key): array {
                if (is_array($line) && isset($line['product_id'], $line['quantity'])) {
                    return [
                        'product_id' => (int) $line['product_id'],
                        'size_id' => isset($line['size_id'])
                            ? (int) $line['size_id']
                            : (ctype_digit((string) $key) ? (int) $key : null),
                        'quantity' => (int) $line['quantity'],
                    ];
                }

                return [
                    'product_id' => (int) $key,
                    'size_id' => null,
                    'quantity' => (int) $line,
                ];
            })
            ->values();
    }

    /**
     * @param  Collection<int, array{product_id: int, size_id?: int|null, quantity: int}>  $items
     * @return Collection<int, array{product_id: int, size_id: int|null, quantity: int}>
     */
    private function completeSizeSelections(Collection $items): Collection
    {
        $defaultSizes = DB::table('product_sizes')
            ->where('is_active', true)
            ->orderBy('size_id')
            ->get()
            ->first();

        return $items->map(fn (array $item): array => [
            'product_id' => (int) $item['product_id'],
            'size_id' => isset($item['size_id'])
                ? (int) $item['size_id']
                : ($defaultSizes === null ? null : (int) $defaultSizes->size_id),
            'quantity' => (int) $item['quantity'],
        ]);
    }

    private function resolveDeliveryAddress(?User $user, array $validated): object
    {
        if (! empty($validated['address_id'])) {
            abort_if($user === null, 403);
            $address = DB::table('user_addresses')
                ->where('user_id', $user->user_id)
                ->where('address_id', $validated['address_id'])
                ->lockForUpdate()
                ->first();

            if ($address === null) {
                throw ValidationException::withMessages([
                    'address_id' => 'Choose one of your saved delivery addresses.',
                ]);
            }

            return $address;
        }

        $now = now();
        $hasAddresses = $user !== null && DB::table('user_addresses')->where('user_id', $user->user_id)->exists();
        $shouldSaveAddress = (bool) ($validated['save_address'] ?? false);

        if (! $shouldSaveAddress || $user === null) {
            return (object) [
                'address_id' => null,
                'recipient_name' => $validated['recipient_name'],
                'phone_number' => $validated['phone_number'],
                'address_line' => $validated['address_line'],
                'barangay' => $validated['barangay'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'] ?? null,
            ];
        }

        $addressId = DB::table('user_addresses')->insertGetId([
            'user_id' => $user->user_id,
            'label' => 'Delivery',
            'recipient_name' => $validated['recipient_name'],
            'phone_number' => $validated['phone_number'],
            'address_line' => $validated['address_line'],
            'barangay' => $validated['barangay'],
            'city' => $validated['city'],
            'province' => $validated['province'],
            'postal_code' => $validated['postal_code'] ?? null,
            'is_default' => ! $hasAddresses,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return DB::table('user_addresses')->where('address_id', $addressId)->first();
    }

    private function formatAddress(object $address): string
    {
        return implode(', ', array_filter([
            $address->address_line,
            $address->barangay,
            $address->city,
            $address->province,
            $address->postal_code,
        ]));
    }

    private function subtotal(Collection $lines): string
    {
        $cents = $lines->sum(
            fn (object $line): int => (int) round((float) $line->unit_price * 100) * (int) $line->quantity,
        );

        return number_format($cents / 100, 2, '.', '');
    }

    private function deliveryFeeForMunicipality(string $municipality): string
    {
        $fee = DB::table('delivery_fees')
            ->where('municipality', $municipality)
            ->lockForUpdate()
            ->value('fee');

        if ($fee === null) {
            throw ValidationException::withMessages([
                'city' => 'This address municipality is not currently configured for delivery.',
            ]);
        }

        return (string) $fee;
    }

    private function activeCoupon(?string $code): ?object
    {
        if ($code === null || $code === '') {
            return null;
        }

        $coupon = DB::table('discount_coupons')->where('code', strtoupper($code))->first();

        return $this->couponIsValid($coupon) ? $coupon : null;
    }

    private function couponIsValid(?object $coupon): bool
    {
        if ($coupon === null || ! $coupon->is_active) {
            return false;
        }

        if ($coupon->starts_at !== null && now()->lt($coupon->starts_at)) {
            return false;
        }
        if ($coupon->expires_at !== null && now()->gt($coupon->expires_at)) {
            return false;
        }

        return $coupon->usage_limit === null || $coupon->used_count < $coupon->usage_limit;
    }

    private function couponDiscount(object $coupon, string $subtotal): string
    {
        $discount = $coupon->discount_type === 'percent'
            ? (float) $subtotal * (float) $coupon->discount_value / 100
            : (float) $coupon->discount_value;

        return number_format(min((float) $subtotal, $discount), 2, '.', '');
    }
}
