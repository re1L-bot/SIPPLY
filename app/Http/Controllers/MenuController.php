<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    private const DEFAULT_CATEGORIES = ['Coffee', 'Milk Tea', 'Pastries'];

    /**
     * @var array<string, string>
     */
    private const CATEGORY_SLUGS = [
        'coffee' => 'Coffee',
        'milk-tea' => 'Milk Tea',
        'pastries' => 'Pastries',
        'pizza' => 'Pastries',
    ];

    public function home(Request $request): View
    {
        $featuredProducts = $this->availableProductQuery()
            ->orderByDesc('products.created_at')
            ->orderBy('products.product_name')
            ->limit(4)
            ->get();

        return view('home', [
            'categories' => DB::table('categories')
                ->join('products', 'products.category_id', '=', 'categories.category_id')
                ->where('categories.is_active', true)
                ->whereIn('categories.category_name', self::DEFAULT_CATEGORIES)
                ->where('products.is_available', true)
                ->select('categories.category_id', 'categories.category_name')
                ->selectRaw('COUNT(products.product_id) as product_count')
                ->groupBy('categories.category_id', 'categories.category_name')
                ->orderBy('categories.category_name')
                ->get(),
            'featuredProducts' => $featuredProducts,
            'promotions' => $this->activePromotions(),
            'user' => $request->user(),
            'cartCount' => $this->cartCount($request),
        ]);
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'category_id')->where(
                    fn ($query) => $query->where('is_active', true)
                        ->whereIn('category_name', self::DEFAULT_CATEGORIES),
                ),
            ],
            'sort' => ['nullable', Rule::in(['price_asc', 'price_desc'])],
        ]);
        $productsQuery = DB::table('categories')
            ->join('products', function (JoinClause $join): void {
                $join->on('products.category_id', '=', 'categories.category_id')
                    ->where('products.is_available', true);
            })
            ->where('categories.is_active', true)
            ->whereIn('categories.category_name', self::DEFAULT_CATEGORIES)
            ->when($validated['search'] ?? null, fn ($query, string $search) => $query->where('products.product_name', 'like', '%'.$search.'%'))
            ->when($validated['category'] ?? null, fn ($query, int $categoryId) => $query->where('categories.category_id', $categoryId))
            ->select([
                'categories.category_id',
                'categories.category_name',
                'categories.description as category_description',
                'products.product_id',
                'products.product_name',
                'products.description as product_description',
                'products.base_price',
                'products.stock_quantity',
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
            );
        if (($validated['sort'] ?? null) === 'price_asc') {
            $productsQuery->orderBy('products.base_price')->orderBy('products.product_name');
        } elseif (($validated['sort'] ?? null) === 'price_desc') {
            $productsQuery->orderByDesc('products.base_price')->orderBy('products.product_name');
        } else {
            $productsQuery->orderBy('categories.category_name')->orderBy('products.product_name');
        }

        $products = $productsQuery->get();
        $this->withSizeOptions($products);
        $menuCategories = $products
            ->groupBy('category_id')
            ->map(function (Collection $products): object {
                $category = $products->first();

                return (object) [
                    'category_id' => $category->category_id,
                    'category_name' => $category->category_name,
                    'category_description' => $category->category_description,
                    'products' => $products,
                ];
            })
            ->values();

        $salesSubquery = DB::table('order_items')
            ->select('order_items.product_id')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->groupBy('order_items.product_id');

        $bestSellers = $this->availableProductQuery()
            ->leftJoinSub($salesSubquery, 'product_sales', function (JoinClause $join): void {
                $join->on('product_sales.product_id', '=', 'products.product_id');
            })
            ->addSelect(DB::raw('COALESCE(product_sales.total_sold, 0) as total_sold'))
            ->orderByDesc('total_sold')
            ->orderBy('products.product_name')
            ->limit(3)
            ->get();
        $this->withSizeOptions($bestSellers);

        return view('menu.index', [
            'menuCategories' => $menuCategories,
            'bestSellers' => $bestSellers,
            'categories' => DB::table('categories')
                ->where('is_active', true)
                ->whereIn('category_name', self::DEFAULT_CATEGORIES)
                ->orderBy('category_name')
                ->get(['category_id', 'category_name']),
            'filters' => [
                'search' => $validated['search'] ?? '',
                'category' => $validated['category'] ?? '',
                'sort' => $validated['sort'] ?? '',
            ],
            'promotions' => $this->activePromotions(),
            'user' => $request->user(),
            'cartCount' => $this->cartCount($request),
        ]);
    }

    public function category(Request $request, string $categorySlug): View
    {
        $categoryName = self::CATEGORY_SLUGS[$categorySlug] ?? null;
        abort_if($categoryName === null, 404);

        $category = DB::table('categories')
            ->where('category_name', $categoryName)
            ->where('is_active', true)
            ->first(['category_id', 'category_name', 'description']);
        abort_if($category === null, 404);

        $products = $this->availableProductQuery()
            ->where('products.category_id', $category->category_id)
            ->orderBy('products.product_name')
            ->get();
        $this->withSizeOptions($products);

        $salesSubquery = DB::table('order_items')
            ->select('order_items.product_id')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->groupBy('order_items.product_id');

        $bestSellers = $this->availableProductQuery()
            ->where('products.category_id', $category->category_id)
            ->leftJoinSub($salesSubquery, 'product_sales', function (JoinClause $join): void {
                $join->on('product_sales.product_id', '=', 'products.product_id');
            })
            ->addSelect(DB::raw('COALESCE(product_sales.total_sold, 0) as total_sold'))
            ->orderByDesc('total_sold')
            ->orderBy('products.product_name')
            ->limit(3)
            ->get();
        $this->withSizeOptions($bestSellers);

        return view('menu.category', [
            'category' => $category,
            'categorySlug' => $categorySlug,
            'products' => $products,
            'bestSellers' => $bestSellers,
            'cartCount' => $this->cartCount($request),
        ]);
    }

    public function show(Request $request, int $productId): View
    {
        $product = $this->availableProductQuery()
            ->where('products.product_id', $productId)
            ->first();
        abort_if($product === null, 404);

        $specificPrices = Schema::hasTable('product_size_prices')
            ? DB::table('product_size_prices')
                ->where('product_id', $productId)
                ->pluck('price', 'size_id')
            : collect();

        $sizes = DB::table('product_sizes')
            ->where('is_active', true)
            ->orderBy('size_id')
            ->get()
            ->map(function (object $size) use ($product, $specificPrices): object {
                $price = isset($specificPrices[$size->size_id])
                    ? (float) $specificPrices[$size->size_id]
                    : ((float) $product->base_price + (float) $size->price_adjustment);

                return (object) [
                    'size_id' => $size->size_id,
                    'size_name' => $size->size_name,
                    'price' => number_format($price, 2, '.', ''),
                ];
            });

        return view('menu.show', [
            'product' => $product,
            'sizes' => $sizes,
            'ingredients' => collect(preg_split('/\R/', (string) $product->ingredients))
                ->map(fn (string $ingredient): string => trim($ingredient))
                ->filter(),
            'relatedProducts' => $this->availableProductQuery()
                ->where('products.category_id', $product->category_id)
                ->where('products.product_id', '!=', $productId)
                ->orderBy('products.product_name')
                ->limit(3)
                ->get(),
            'cartCount' => $this->cartCount($request),
        ]);
    }

    private function activePromotions(): Collection
    {
        return DB::table('promotions')
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderByDesc('created_at')
            ->get(['title', 'description']);
    }

    private function cartCount(Request $request): int
    {
        if ($request->user() === null) {
            return (int) collect($request->session()->get('guest_cart', []))
                ->sum(fn (mixed $item): int => is_array($item) ? (int) ($item['quantity'] ?? 0) : (int) $item);
        }

        return (int) DB::table('carts')
            ->join('cart_items', 'cart_items.cart_id', '=', 'carts.cart_id')
            ->where('carts.user_id', $request->user()->user_id)
            ->where('carts.cart_status', 'active')
            ->sum('cart_items.quantity');
    }

    private function withSizeOptions(Collection $products): Collection
    {
        $sizeOptions = DB::table('product_sizes')
            ->where('is_active', true)
            ->orderBy('size_id')
            ->get();

        $productIds = $products->pluck('product_id');
        $specificPrices = Schema::hasTable('product_size_prices') && $productIds->isNotEmpty()
            ? DB::table('product_size_prices')
                ->whereIn('product_id', $productIds)
                ->get()
                ->groupBy('product_id')
                ->map(fn ($rows) => $rows->pluck('price', 'size_id'))
            : collect();

        return $products->each(function (object $product) use ($sizeOptions, $specificPrices): void {
            $productPrices = $specificPrices->get($product->product_id, collect());
            $product->sizes = $sizeOptions->map(function (object $size) use ($product, $productPrices): object {
                $price = isset($productPrices[$size->size_id])
                    ? (float) $productPrices[$size->size_id]
                    : ((float) $product->base_price + (float) $size->price_adjustment);

                return (object) [
                    'size_id' => $size->size_id,
                    'size_name' => $size->size_name,
                    'price' => number_format($price, 2, '.', ''),
                ];
            });
        });
    }

    private function availableProductQuery(): Builder
    {
        return DB::table('products')
            ->join('categories', 'categories.category_id', '=', 'products.category_id')
            ->where('products.is_available', true)
            ->where('categories.is_active', true)
            ->whereIn('categories.category_name', self::DEFAULT_CATEGORIES)
            ->select([
                'products.product_id',
                'products.category_id',
                'products.product_name',
                'products.description as product_description',
                'products.ingredients',
                'products.base_price',
                'products.stock_quantity',
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
            );
    }
}
