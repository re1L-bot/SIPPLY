<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $request->session()->forget('checkout.buy_now');
        $request->session()->forget('checkout.selected_items');

        $user = $request->user();
        $cart = $user === null ? null : $this->activeCart($user->user_id);
        if ($user === null) {
            $guestItems = $this->normalizeGuestCart($request);
            if (empty($guestItems)) {
                $items = collect();
            } else {
                $productIds = collect($guestItems)->pluck('product_id')->unique();
                $sizeIds = collect($guestItems)->pluck('size_id')->unique();
                $specificPrices = Schema::hasTable('product_size_prices')
                    ? DB::table('product_size_prices')
                        ->whereIn('product_id', $productIds)
                        ->whereIn('size_id', $sizeIds)
                        ->get()
                        ->keyBy(fn ($row) => "p{$row->product_id}-s{$row->size_id}")
                    : collect();

                $items = DB::table('products')
                    ->crossJoin('product_sizes')
                    ->whereIn('products.product_id', $productIds)
                    ->whereIn('product_sizes.size_id', $sizeIds)
                    ->select([
                        'products.product_id',
                        'products.product_name',
                        'product_sizes.size_id',
                        'product_sizes.size_name',
                        'product_sizes.is_active as size_is_active',
                        'products.base_price',
                        'product_sizes.price_adjustment',
                        'stock_quantity',
                        'is_available',
                    ])
                    ->orderBy('product_name')
                    ->get()
                    ->filter(fn (object $item): bool => array_key_exists(
                        $this->guestCartKey((int) $item->product_id, (int) $item->size_id),
                        $guestItems,
                    ))
                    ->keyBy(fn (object $item): string => $this->guestCartKey((int) $item->product_id, (int) $item->size_id))
                    ->map(function (object $item, string $key) use ($guestItems, $specificPrices): object {
                        $item->cart_item_id = $key;
                        $item->quantity = (int) ($guestItems[$key]['quantity'] ?? 0);
                        $specific = $specificPrices->get($key);
                        $price = $specific !== null
                            ? (float) $specific->price
                            : ((float) $item->base_price + (float) $item->price_adjustment);
                        $item->base_price = number_format($price, 2, '.', '');

                        return $item;
                    });
            }
        } else {
            if ($cart === null) {
                $items = collect();
            } else {
                $cartRows = DB::table('cart_items')
                    ->join('products', 'products.product_id', '=', 'cart_items.product_id')
                    ->leftJoin('product_sizes', 'product_sizes.size_id', '=', 'cart_items.size_id')
                    ->where('cart_items.cart_id', $cart->cart_id)
                    ->select([
                        'cart_items.cart_item_id',
                        'cart_items.quantity',
                        'cart_items.unit_price as cart_unit_price',
                        'products.product_id',
                        'products.product_name',
                        'cart_items.size_id',
                        DB::raw("COALESCE(product_sizes.size_name, 'Regular') as size_name"),
                        DB::raw('COALESCE(product_sizes.is_active, 1) as size_is_active'),
                        'products.base_price',
                        'product_sizes.price_adjustment',
                        'products.stock_quantity',
                        'products.is_available',
                    ])
                    ->orderBy('cart_items.cart_item_id')
                    ->get();

                $cartProductIds = $cartRows->pluck('product_id')->unique();
                $cartSizeIds = $cartRows->pluck('size_id')->filter()->unique();
                $authSpecificPrices = Schema::hasTable('product_size_prices') && $cartProductIds->isNotEmpty()
                    ? DB::table('product_size_prices')
                        ->whereIn('product_id', $cartProductIds)
                        ->whereIn('size_id', $cartSizeIds)
                        ->get()
                        ->keyBy(fn ($r) => "p{$r->product_id}-s{$r->size_id}")
                    : collect();

                $items = $cartRows->map(function (object $item) use ($authSpecificPrices): object {
                    $key = "p{$item->product_id}-s{$item->size_id}";
                    $specific = $authSpecificPrices->get($key);
                    $price = $specific !== null
                        ? (float) $specific->price
                        : ((float) ($item->cart_unit_price ?: ((float) $item->base_price + (float) ($item->price_adjustment ?? 0))));
                    $item->base_price = number_format($price, 2, '.', '');

                    return $item;
                });
            }
        }

        $subtotal = $items->sum(
            fn (object $item): float => (float) $item->base_price * (int) $item->quantity,
        );

        return view('cart.index', [
            'items' => $items,
            'subtotal' => $subtotal,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'size_id' => ['nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);
        $user = $request->user();

        if ($user === null) {
            DB::transaction(function () use ($request, $validated): void {
                $product = DB::table('products')
                    ->where('product_id', $validated['product_id'])
                    ->lockForUpdate()
                    ->first();

                if ($product === null || ! $product->is_available) {
                    throw ValidationException::withMessages([
                        'product' => 'This drink is no longer available.',
                    ]);
                }

                $size = $this->resolveSize($product, $validated['size_id'] ?? null);
                $items = $this->normalizeGuestCart($request);
                $currentProductQuantity = collect($items)
                    ->filter(fn (array $item): bool => (int) $item['product_id'] === (int) $product->product_id)
                    ->sum('quantity');
                $cartItemKey = $this->guestCartKey((int) $product->product_id, (int) $size->size_id);
                $sizeQuantity = (int) ($items[$cartItemKey]['quantity'] ?? 0) + (int) $validated['quantity'];
                if ($currentProductQuantity + (int) $validated['quantity'] > $product->stock_quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => "Only {$product->stock_quantity} of this drink are in stock.",
                    ]);
                }

                $items[$cartItemKey] = [
                    'product_id' => $product->product_id,
                    'size_id' => $size->size_id,
                    'quantity' => $sizeQuantity,
                ];
                $request->session()->put('guest_cart', $items);
            });

            return redirect()->route('cart.index')->with('status', 'Added to your cart.');
        }

        DB::transaction(function () use ($validated, $user): void {
            DB::table('users')
                ->where('user_id', $user->user_id)
                ->lockForUpdate()
                ->first();

            $product = DB::table('products')
                ->where('product_id', $validated['product_id'])
                ->lockForUpdate()
                ->first();

            if ($product === null || ! $product->is_available) {
                throw ValidationException::withMessages([
                    'product' => 'This drink is no longer available.',
                ]);
            }

            $size = $this->resolveSize($product, $validated['size_id'] ?? null);
            $cart = $this->activeCart($user->user_id, lockForUpdate: true);

            if ($cart === null) {
                $cartId = DB::table('carts')->insertGetId([
                    'user_id' => $user->user_id,
                    'cart_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $cartId = $cart->cart_id;
            }

            $cartItem = DB::table('cart_items')
                ->where('cart_id', $cartId)
                ->where('product_id', $product->product_id)
                ->where('size_id', $size->size_id)
                ->lockForUpdate()
                ->first();
            $quantity = (int) ($cartItem->quantity ?? 0) + (int) $validated['quantity'];
            $otherSizeQuantity = DB::table('cart_items')
                ->where('cart_id', $cartId)
                ->where('product_id', $product->product_id)
                ->where(function ($query) use ($size): void {
                    $query->whereNull('size_id')->orWhere('size_id', '!=', $size->size_id);
                })
                ->sum('quantity');

            if ($quantity + (int) $otherSizeQuantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$product->stock_quantity} of this drink are in stock.",
                ]);
            }

            if ($cartItem === null) {
                DB::table('cart_items')->insert([
                    'cart_id' => $cartId,
                    'product_id' => $product->product_id,
                    'size_id' => $size->size_id,
                    'quantity' => $quantity,
                    'unit_price' => $size->price,
                    'special_instructions' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('cart_items')
                    ->where('cart_item_id', $cartItem->cart_item_id)
                    ->update([
                        'quantity' => $quantity,
                        'unit_price' => $size->price,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('carts')->where('cart_id', $cartId)->update(['updated_at' => now()]);
        });

        return redirect()->route('cart.index')->with('status', 'Added to your cart.');
    }

    public function selectForCheckout(Request $request): RedirectResponse
    {
        $request->session()->forget('checkout.buy_now');

        if ($request->isMethod('GET')) {
            return redirect()->route('cart.index');
        }

        $validated = $request->validate([
            'selected_items' => ['nullable', 'array'],
            'selected_items.*' => ['string'],
        ]);

        $selectedItems = $validated['selected_items'] ?? [];

        if (empty($selectedItems)) {
            return redirect()->route('cart.index')
                ->withErrors(['cart' => 'Please select at least one item to check out.']);
        }

        $request->session()->put('checkout.selected_items', $selectedItems);

        return redirect()->route('checkout.index');
    }

    public function buyNow(Request $request, int $productId): RedirectResponse
    {
        $validated = $request->validate([
            'size_id' => ['nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $product = DB::table('products')
            ->where('product_id', $productId)
            ->where('is_available', true)
            ->first();

        abort_if($product === null, 404);

        $size = $this->resolveSize($product, $validated['size_id'] ?? null);
        if ((int) $validated['quantity'] > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$product->stock_quantity} of this drink are in stock.",
            ]);
        }

        $request->session()->put('checkout.buy_now', [
            'product_id' => $product->product_id,
            'size_id' => $size->size_id,
            'quantity' => (int) $validated['quantity'],
        ]);

        return redirect()->route('checkout.index');
    }

    public function update(Request $request, string $cartItemId): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);
        $user = $request->user();
        if ($user === null) {
            $items = $this->normalizeGuestCart($request);
            $item = $items[$cartItemId] ?? null;
            if ($item === null && ctype_digit($cartItemId)) {
                $legacyKey = collect($items)->search(
                    fn (array $cartLine): bool => (int) $cartLine['product_id'] === (int) $cartItemId,
                );
                $cartItemId = is_string($legacyKey) ? $legacyKey : $cartItemId;
                $item = $items[$cartItemId] ?? null;
            }
            abort_if($item === null, 404);
            $product = DB::table('products')->where('product_id', $item['product_id'])->first();
            if ($product === null || ! $product->is_available) {
                throw ValidationException::withMessages([
                    'quantity' => 'This drink is no longer available.',
                ]);
            }
            $totalForOtherSizes = collect($items)
                ->filter(fn (array $cartLine, int|string $key): bool => (int) $cartLine['product_id'] === (int) $product->product_id && (string) $key !== $cartItemId)
                ->sum('quantity');
            if ($totalForOtherSizes + (int) $validated['quantity'] > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$product->stock_quantity} of this drink are in stock.",
                ]);
            }
            $items[$cartItemId]['quantity'] = (int) $validated['quantity'];
            $request->session()->put('guest_cart', $items);

            return redirect()->route('cart.index')->with('status', 'Cart updated.');
        }

        $cart = $this->activeCart($user->user_id);

        abort_if($cart === null, 404);

        DB::transaction(function () use ($validated, $cart, $cartItemId): void {
            $cartItem = DB::table('cart_items')
                ->where('cart_id', $cart->cart_id)
                ->where('cart_item_id', $cartItemId)
                ->lockForUpdate()
                ->first();

            abort_if($cartItem === null, 404);

            $product = DB::table('products')
                ->where('product_id', $cartItem->product_id)
                ->lockForUpdate()
                ->first();

            if ($product === null || ! $product->is_available) {
                throw ValidationException::withMessages([
                    'quantity' => 'This drink is no longer available.',
                ]);
            }

            $otherSizeQuantity = DB::table('cart_items')
                ->where('cart_id', $cart->cart_id)
                ->where('product_id', $product->product_id)
                ->where('cart_item_id', '!=', $cartItemId)
                ->sum('quantity');
            if ((int) $otherSizeQuantity + (int) $validated['quantity'] > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$product->stock_quantity} of this drink are in stock.",
                ]);
            }

            $size = $this->resolveSize($product, $cartItem->size_id);
            DB::table('cart_items')
                ->where('cart_item_id', $cartItemId)
                ->update([
                    'quantity' => $validated['quantity'],
                    'size_id' => $size->size_id,
                    'unit_price' => $size->price,
                    'updated_at' => now(),
                ]);

            DB::table('carts')->where('cart_id', $cart->cart_id)->update(['updated_at' => now()]);
        });

        return redirect()->route('cart.index')->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, string $cartItemId): RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            $items = $this->normalizeGuestCart($request);
            if (! array_key_exists($cartItemId, $items) && ctype_digit($cartItemId)) {
                $legacyKey = collect($items)->search(
                    fn (array $cartLine): bool => (int) $cartLine['product_id'] === (int) $cartItemId,
                );
                $cartItemId = is_string($legacyKey) ? $legacyKey : $cartItemId;
            }
            abort_if(! array_key_exists($cartItemId, $items), 404);
            unset($items[$cartItemId]);
            $request->session()->put('guest_cart', $items);

            return redirect()->route('cart.index')->with('status', 'Item removed from your cart.');
        }

        $cart = $this->activeCart($user->user_id);

        abort_if($cart === null, 404);

        $deleted = DB::table('cart_items')
            ->where('cart_id', $cart->cart_id)
            ->where('cart_item_id', $cartItemId)
            ->delete();

        abort_if($deleted === 0, 404);

        return redirect()->route('cart.index')->with('status', 'Item removed from your cart.');
    }

    private function activeCart(int $userId, bool $lockForUpdate = false): ?object
    {
        $query = DB::table('carts')
            ->where('user_id', $userId)
            ->where('cart_status', 'active')
            ->orderBy('cart_id');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    /**
     * @return array<string, array{product_id: int, size_id: int, quantity: int}>
     */
    private function normalizeGuestCart(Request $request): array
    {
        $guestItems = $request->session()->get('guest_cart', []);
        $items = [];
        $defaultSizeId = (int) (DB::table('product_sizes')
            ->where('is_active', true)
            ->orderBy('size_id')
            ->value('size_id') ?? 0);

        foreach ($guestItems as $key => $line) {
            if (is_array($line) && isset($line['product_id'], $line['quantity'])) {
                $productId = (int) $line['product_id'];
                $sizeId = isset($line['size_id'])
                    ? (int) $line['size_id']
                    : (ctype_digit((string) $key) ? (int) $key : $defaultSizeId);
                if ($sizeId > 0) {
                    $items[$this->guestCartKey($productId, $sizeId)] = [
                        'product_id' => $productId,
                        'size_id' => $sizeId,
                        'quantity' => (int) $line['quantity'],
                    ];
                }

                continue;
            }

            $productId = (int) $key;
            if ($defaultSizeId > 0) {
                $items[$this->guestCartKey($productId, $defaultSizeId)] = [
                    'product_id' => $productId,
                    'size_id' => $defaultSizeId,
                    'quantity' => (int) $line['quantity'],
                ];
            }
        }

        if ($items !== $guestItems) {
            $request->session()->put('guest_cart', $items);
        }

        return $items;
    }

    private function guestCartKey(int $productId, int $sizeId): string
    {
        return "p{$productId}-s{$sizeId}";
    }

    private function resolveSize(object $product, int|string|null $sizeId): object
    {
        $sizeQuery = DB::table('product_sizes')->where('is_active', true);
        if ($sizeId !== null) {
            $sizeQuery->where('size_id', $sizeId);
        } else {
            $sizeQuery->orderBy('size_id');
        }
        $size = $sizeQuery->first();

        if ($size === null) {
            throw ValidationException::withMessages([
                'size_id' => 'Choose a valid size for this drink.',
            ]);
        }

        $specificPrice = Schema::hasTable('product_size_prices')
            ? DB::table('product_size_prices')
                ->where('product_id', $product->product_id)
                ->where('size_id', $size->size_id)
                ->value('price')
            : null;

        $finalPrice = $specificPrice !== null
            ? (float) $specificPrice
            : ((float) $product->base_price + (float) $size->price_adjustment);

        $size->price = number_format($finalPrice, 2, '.', '');

        return $size;
    }
}
