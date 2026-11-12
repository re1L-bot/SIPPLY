<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller as BaseController;
use App\Services\AdminActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class MenuController extends BaseController
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'category_id'),
            ],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'available', 'unavailable'])],
            'availability' => ['nullable', Rule::in(['available', 'unavailable', 'active', 'inactive'])],
            'stock' => ['nullable', Rule::in(['in_stock', 'out_of_stock'])],
        ]);
        $statusFilter = $validated['status'] ?? $validated['availability'] ?? null;
        $categories = DB::table('categories')
            ->select('categories.*')
            ->selectSub(
                DB::table('products')->whereColumn('products.category_id', 'categories.category_id')->selectRaw('count(*)'),
                'products_count'
            )
            ->orderBy('category_name')
            ->get();

        $products = DB::table('products')
            ->join('categories', 'categories.category_id', '=', 'products.category_id')
            ->when($validated['search'] ?? null, fn ($query, string $search) => $query->where('products.product_name', 'like', '%'.$search.'%'))
            ->when($validated['category'] ?? null, fn ($query, int $categoryId) => $query->where('products.category_id', $categoryId))
            ->when(in_array($statusFilter, ['active', 'available'], true), fn ($query) => $query->where('products.is_available', true))
            ->when(in_array($statusFilter, ['inactive', 'unavailable'], true), fn ($query) => $query->where('products.is_available', false))
            ->when(($validated['stock'] ?? null) === 'in_stock', fn ($query) => $query->where('products.stock_quantity', '>', 0))
            ->when(($validated['stock'] ?? null) === 'out_of_stock', fn ($query) => $query->where('products.stock_quantity', 0))
            ->select([
                'products.product_id',
                'products.category_id',
                'products.product_name',
                'products.description',
                'products.ingredients',
                'products.base_price',
                'products.stock_quantity',
                'products.is_available',
                'products.created_at',
                'categories.category_name',
            ])
            ->selectSub(
                DB::table('product_images')
                    ->select('image_path')
                    ->whereColumn('product_images.product_id', 'products.product_id')
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->orderBy('image_id')
                    ->limit(1),
                'image_path',
            )
            ->orderByDesc('products.created_at')
            ->paginate(12)
            ->withQueryString();

        $productIds = $products->getCollection()->pluck('product_id');
        $productSizePrices = Schema::hasTable('product_size_prices') && $productIds->isNotEmpty()
            ? DB::table('product_size_prices')
                ->whereIn('product_id', $productIds)
                ->get()
                ->groupBy('product_id')
                ->map(fn ($rows) => $rows->keyBy('size_id')->map(fn ($r) => number_format((float) $r->price, 2, '.', '')))
            : collect();

        return view('admin.menu.index', [
            'admin' => request()->user('admin'),
            'categories' => $categories,
            'products' => $products,
            'sizes' => DB::table('product_sizes')->where('is_active', true)->orderBy('size_id')->get(),
            'productSizePrices' => $productSizePrices,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'category' => $validated['category'] ?? '',
                'status' => $statusFilter ?? '',
                'availability' => $statusFilter ?? '',
                'stock' => $validated['stock'] ?? '',
            ],
        ]);
    }

    public function storeProduct(Request $request, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate($this->productRules());
        $image = $validated['image'] ?? null;
        unset($validated['image']);
        $sizePrices = $validated['size_prices'] ?? [];
        unset($validated['size_prices']);
        $validated['is_available'] = (bool) ($validated['is_available'] ?? false);
        $now = now();

        $productId = DB::table('products')->insertGetId([
            ...$validated,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (! empty($sizePrices) && Schema::hasTable('product_size_prices')) {
            $priceRows = [];
            foreach ($sizePrices as $sizeId => $price) {
                if ($price !== null && $price !== '') {
                    $priceRows[] = [
                        'product_id' => $productId,
                        'size_id' => (int) $sizeId,
                        'price' => (float) $price,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            if (! empty($priceRows)) {
                DB::table('product_size_prices')->upsert(
                    $priceRows,
                    ['product_id', 'size_id'],
                    ['price', 'updated_at']
                );
            }
        }

        if ($image !== null) {
            $imagePath = $this->storeProductImage($image, (int) $productId);

            DB::table('product_images')->insert([
                'product_id' => $productId,
                'image_path' => $imagePath,
                'is_primary' => true,
                'sort_order' => 0,
                'created_at' => now(),
            ]);
        }
        $activityLogger->record('created', 'product', (int) $productId, "Product {$validated['product_name']} created.");

        return redirect()->route('admin.menu.index', ['_fragment' => 'products'])
            ->with('status', 'Product created.');
    }

    public function updateProduct(Request $request, int $productId, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $productExists = DB::table('products')->where('product_id', $productId)->exists();
        abort_unless($productExists, 404);

        $validated = $request->validate($this->productRules());
        $image = $validated['image'] ?? null;
        unset($validated['image']);
        $sizePrices = $validated['size_prices'] ?? [];
        unset($validated['size_prices']);
        $validated['is_available'] = (bool) ($validated['is_available'] ?? false);
        $previousImage = null;
        $newImagePath = null;

        if ($image !== null) {
            $previousImage = DB::table('product_images')
                ->where('product_id', $productId)
                ->where('is_primary', true)
                ->orderBy('image_id')
                ->first();
            $newImagePath = $this->storeProductImage($image, $productId);
        }

        $now = now();
        DB::transaction(function () use ($validated, $productId, $previousImage, $newImagePath, $sizePrices, $now): void {
            DB::table('products')
                ->where('product_id', $productId)
                ->update([
                    ...$validated,
                    'updated_at' => $now,
                ]);

            if (Schema::hasTable('product_size_prices')) {
                if (! empty($sizePrices)) {
                    $priceRows = [];
                    foreach ($sizePrices as $sizeId => $price) {
                        if ($price !== null && $price !== '') {
                            $priceRows[] = [
                                'product_id' => $productId,
                                'size_id' => (int) $sizeId,
                                'price' => (float) $price,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                    if (! empty($priceRows)) {
                        DB::table('product_size_prices')->upsert(
                            $priceRows,
                            ['product_id', 'size_id'],
                            ['price', 'updated_at']
                        );
                    }
                }
            }

            if ($newImagePath !== null) {
                if ($previousImage !== null) {
                    DB::table('product_images')->where('image_id', $previousImage->image_id)->delete();
                }

                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'image_path' => $newImagePath,
                    'is_primary' => true,
                    'sort_order' => 0,
                    'created_at' => now(),
                ]);
            }
        });

        if ($previousImage !== null) {
            $this->deleteManagedProductImage($previousImage->image_path, $productId);
        }
        $activityLogger->record('updated', 'product', $productId, "Product {$validated['product_name']} updated.");

        return redirect()->route('admin.menu.index', ['_fragment' => 'products'])
            ->with('status', 'Product updated.');
    }

    public function deleteProduct(int $productId, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $product = DB::table('products')->where('product_id', $productId)->first();
        abort_if($product === null, 404);

        if (DB::table('cart_items')->where('product_id', $productId)->exists()) {
            return back()->withErrors([
                'product' => 'This drink is in a customer cart. Mark it unavailable instead of deleting it.',
            ]);
        }
        if (DB::table('order_items')->where('product_id', $productId)->exists()) {
            return back()->withErrors([
                'product' => 'This drink is part of an order history. Mark it unavailable instead of deleting it.',
            ]);
        }

        $imagePaths = DB::table('product_images')
            ->where('product_id', $productId)
            ->pluck('image_path');

        DB::table('products')->where('product_id', $productId)->delete();

        foreach ($imagePaths as $imagePath) {
            $this->deleteManagedProductImage($imagePath, $productId);
        }
        $activityLogger->record('deleted', 'product', $productId, "Product {$product->product_name} deleted.");

        return redirect()->route('admin.menu.index', ['_fragment' => 'products'])
            ->with('status', 'Product deleted.');
    }

    public function storeCategory(Request $request, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate([
            'category_name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('categories', 'category_name'),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => trim($validated['category_name']),
            'description' => $validated['description'] ?? null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $activityLogger->record('created', 'category', (int) $categoryId, "Category {$validated['category_name']} created.");

        return redirect()->route('admin.menu.index', ['_fragment' => 'categories'])
            ->with('status', 'Category created successfully.');
    }

    public function updateCategory(Request $request, int $categoryId, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $category = DB::table('categories')->where('category_id', $categoryId)->first();
        abort_if($category === null, 404);

        $validated = $request->validate([
            'category_name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('categories', 'category_name')->ignore($categoryId, 'category_id'),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::table('categories')->where('category_id', $categoryId)->update([
            'category_name' => trim($validated['category_name']),
            'description' => $validated['description'] ?? null,
            'updated_at' => now(),
        ]);

        $activityLogger->record('updated', 'category', $categoryId, "Category {$validated['category_name']} updated.");

        return redirect()->route('admin.menu.index', ['_fragment' => 'categories'])
            ->with('status', 'Category updated successfully.');
    }

    public function deleteCategory(int $categoryId, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $category = DB::table('categories')->where('category_id', $categoryId)->first();
        abort_if($category === null, 404);

        $hasAssignedProducts = DB::table('products')->where('category_id', $categoryId)->exists();
        if ($hasAssignedProducts) {
            return redirect()->route('admin.menu.index', ['_fragment' => 'categories'])->withErrors([
                'category' => "Cannot delete '{$category->category_name}' because it has products assigned to it. Please reassign or delete those products first.",
            ]);
        }

        DB::table('categories')->where('category_id', $categoryId)->delete();
        $activityLogger->record('deleted', 'category', $categoryId, "Category {$category->category_name} deleted.");

        return redirect()->route('admin.menu.index', ['_fragment' => 'categories'])
            ->with('status', 'Category deleted successfully.');
    }

    public function storeSize(Request $request, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate([
            'size_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('product_sizes', 'size_name'),
            ],
            'price_adjustment' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
        ]);

        $sizeId = DB::table('product_sizes')->insertGetId([
            'size_name' => trim($validated['size_name']),
            'price_adjustment' => $validated['price_adjustment'] ?? 0,
            'is_active' => true,
        ]);

        $activityLogger->record('created', 'size', (int) $sizeId, "Size {$validated['size_name']} created.");

        return redirect()->route('admin.menu.index', ['_fragment' => 'sizes'])
            ->with('status', "Size '{$validated['size_name']}' added successfully.");
    }

    public function deleteSize(int $sizeId, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $size = DB::table('product_sizes')->where('size_id', $sizeId)->first();
        abort_if($size === null, 404);

        $inCart = DB::table('cart_items')->where('size_id', $sizeId)->exists();
        if ($inCart) {
            return redirect()->route('admin.menu.index', ['_fragment' => 'sizes'])->withErrors([
                'size' => "Cannot delete size '{$size->size_name}' because it is in a customer cart.",
            ]);
        }

        DB::table('product_sizes')->where('size_id', $sizeId)->delete();
        $activityLogger->record('deleted', 'size', $sizeId, "Size {$size->size_name} deleted.");

        return redirect()->route('admin.menu.index', ['_fragment' => 'sizes'])
            ->with('status', "Size '{$size->size_name}' deleted.");
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function productRules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'category_id')->where('is_active', true),
            ],
            'product_name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'ingredients' => ['nullable', 'string', 'max:5000'],
            'base_price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'is_available' => ['sometimes', 'boolean'],
            'size_prices' => ['nullable', 'array'],
            'size_prices.*' => ['nullable', 'numeric', 'min:0.01', 'max:99999999.99'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000'],
        ];
    }

    private function storeProductImage(UploadedFile $image, int $productId): string
    {
        $imagePath = $image->storePublicly('menu-products/'.$productId, 'public');

        if ($imagePath === false) {
            throw new RuntimeException('The product image could not be saved.');
        }

        return $imagePath;
    }

    private function deleteManagedProductImage(string $imagePath, int $productId): void
    {
        if (! str_starts_with($imagePath, 'menu-products/'.$productId.'/')) {
            return;
        }

        if (! Storage::disk('public')->delete($imagePath)) {
            throw new RuntimeException('The product image could not be deleted.');
        }
    }
}
