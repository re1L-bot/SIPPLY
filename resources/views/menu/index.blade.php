<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f4ed">
    <title>Our menu | SipPly</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="warm-theme min-h-screen bg-[#f7f7f1] font-sans text-[#20352c] antialiased">
    <header class="sticky top-0 z-20 border-b border-[#e7e9df] bg-[#f7f7f1]/95 backdrop-blur">
        <div class="mx-auto flex h-[76px] max-w-7xl items-center justify-between gap-5 px-5 sm:px-8">
            <a class="flex shrink-0 items-center gap-2.5 no-underline" href="{{ route('home') }}" aria-label="SipPly menu">
                <img class="brand-logo" src="{{ asset('images/favicon.png') }}" alt="SipPly Coffee Shop logo" width="56" height="56">
                <span class="font-serif text-[25px] font-bold tracking-[-1px] text-[#4b2413]">SipPly</span>
            </a>

            @include('partials.store-navigation')
        </div>
    </header>

    <main>
        @if ($promotions->isNotEmpty())
            <section class="mx-auto max-w-7xl px-5 pt-6 sm:px-8" aria-label="Current promotions">
                <div class="grid gap-3 md:grid-cols-2">
                    @foreach ($promotions as $promotion)
                        <article class="rounded-2xl border border-[#dce8d9] bg-[#edf5e9] p-5">
                            <p class="text-[10px] font-bold uppercase tracking-[.18em] text-[#567c5e]">Current offer</p>
                            <h2 class="mt-1 font-serif text-xl">{{ $promotion->title }}</h2>
                            <p class="mt-1 text-sm leading-6 text-[#52635a]">{{ $promotion->description }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
        <section class="mx-auto max-w-7xl px-5 pb-9 pt-7 sm:px-8 sm:pt-10">
            <div class="relative isolate overflow-hidden rounded-[26px] bg-[#244b3b] px-6 py-10 text-white sm:px-10 sm:py-14 lg:px-14">
                <div class="absolute -right-20 -top-32 -z-10 size-80 rounded-full border border-white/10 sm:size-[440px]"></div>
                <div class="absolute -right-6 -top-20 -z-10 size-64 rounded-full border border-white/10 sm:size-[350px]"></div>
                <div class="absolute bottom-[-115px] right-[22%] -z-10 size-56 rounded-full bg-[#9abb83]/10 blur-2xl"></div>
                <p class="text-[11px] font-bold uppercase tracking-[.22em] text-[#c2d7bf]">Your little moment of refresh</p>
                <h1 class="mt-4 max-w-2xl font-serif text-4xl font-normal leading-[1.08] tracking-[-1.4px] sm:text-5xl lg:text-[58px]">
                    Good days start<br class="hidden sm:block"> with a good sip.
                </h1>
                <p class="mt-4 max-w-lg text-sm leading-7 text-[#d3dfd4] sm:text-base">
                    {{ $user ? 'Hey, '.$user->first_name.'. ' : '' }}Find your next favorite, made fresh and just the way you like it.
                </p>
                <a class="mt-7 inline-flex items-center gap-2 rounded-full bg-[#f4d6aa] px-5 py-3 text-sm font-bold text-[#264b39] transition hover:-translate-y-0.5 hover:bg-[#ffe3bd]" href="#menu">
                    Explore the menu
                    <svg class="size-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </section>

        <section id="menu" class="mx-auto max-w-7xl scroll-mt-24 px-5 pb-20 sm:px-8">
            <div class="flex flex-col justify-between gap-5 border-b border-[#e5e8df] pb-5 sm:flex-row sm:items-end">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[.2em] text-[#78907b]">Made to make your day</p>
                    <h2 class="mt-2 font-serif text-3xl font-normal tracking-[-.8px] sm:text-[38px]">Find your sip</h2>
                </div>
            </div>

            <form class="mt-6 grid gap-3 rounded-2xl border border-[#e8e9e1] bg-white p-4 sm:grid-cols-[minmax(0,1fr)_200px_180px_auto]" method="GET" action="{{ route('products.index') }}">
                <label class="sr-only" for="menu-search">Search drinks</label>
                <input class="rounded-xl border border-[#dce3d8] px-4 py-3 text-sm" id="menu-search" name="search" value="{{ $filters['search'] }}" maxlength="100" placeholder="Search drinks">
                <label class="sr-only" for="menu-category">Filter by category</label>
                <select class="rounded-xl border border-[#dce3d8] bg-white px-4 py-3 text-sm" id="menu-category" name="category">
                    <option value="">All categories</option>
                    @foreach ($categories as $filterCategory)
                        <option value="{{ $filterCategory->category_id }}" @selected((string) $filters['category'] === (string) $filterCategory->category_id)>{{ $filterCategory->category_name }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="menu-sort">Sort drinks</label>
                <select class="rounded-xl border border-[#dce3d8] bg-white px-4 py-3 text-sm" id="menu-sort" name="sort">
                    <option value="">Recommended</option>
                    <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Price: low to high</option>
                    <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Price: high to low</option>
                </select>
                <button class="rounded-full bg-[#244b3b] px-5 py-3 text-sm font-bold text-white" type="submit">Find drinks</button>
            </form>

            @if (empty($filters['search']) && empty($filters['category']) && empty($filters['sort']) && isset($bestSellers) && $bestSellers->isNotEmpty())
                <section class="pt-9">
                    <div class="mb-5 flex items-end justify-between gap-4">
                        <div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#f4d6aa] px-3 py-1 text-xs font-bold text-[#4b2413]">
                                <svg class="size-3.5 fill-[#c75f26]" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                Top 3 Favorites
                            </span>
                            <h3 class="mt-2 font-serif text-2xl font-normal tracking-[-.4px] sm:text-[28px]">Store Best Sellers</h3>
                            <p class="mt-1 text-sm text-[#748178]">Our top 3 most popular picks based on number of items sold.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
                        @foreach ($bestSellers as $product)
                            <article class="group relative overflow-hidden rounded-2xl border border-[#d8e0d4] bg-white transition duration-200 hover:-translate-y-1 hover:border-[#bdcbb9] hover:shadow-[0_16px_38px_rgba(32,53,44,.09)]">
                                <span class="absolute left-3 top-3 z-10 inline-flex items-center gap-1 rounded-full bg-[#4b2413]/90 px-2.5 py-1 text-[11px] font-bold text-white shadow backdrop-blur">
                                    ★ #{{ $loop->iteration }} Best Seller
                                </span>
                                @if ($product->image_path)
                                    <img class="aspect-[1.65] w-full bg-[#edf1e9] object-cover" src="{{ asset('storage/'.ltrim($product->image_path, '/')) }}" alt="{{ $product->product_name }}" loading="lazy">
                                @else
                                    <div class="grid aspect-[1.65] place-items-center bg-[#eff3eb]">
                                        <span class="font-serif text-3xl font-bold text-[#557760]">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->product_name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div class="flex min-h-[148px] flex-col p-5">
                                    <div class="flex items-start justify-between gap-3">
                                        <h4 class="font-serif text-xl leading-snug tracking-[-.3px]">
                                            <a class="hover:text-[#557760]" href="{{ route('menu.show', $product->product_id) }}">{{ $product->product_name }}</a>
                                        </h4>
                                        <span class="shrink-0 rounded-full bg-[#f1f5ed] px-3 py-1.5 text-sm font-bold text-[#315d47]">From ₱{{ number_format((float) $product->base_price, 2) }}</span>
                                    </div>
                                    @if ($product->product_description)
                                        <p class="mt-2 line-clamp-2 text-sm leading-6 text-[#78847c]">{{ $product->product_description }}</p>
                                    @endif
                                    <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-4">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-semibold {{ $product->stock_quantity > 0 ? 'text-[#78907b]' : 'text-[#a4473d]' }}">
                                                {{ $product->stock_quantity > 0 ? $product->stock_quantity.' in stock' : 'Sold out' }}
                                            </span>
                                            @if (isset($product->total_sold) && (int) $product->total_sold > 0)
                                                <span class="text-xs font-bold text-[#c75f26]">· {{ $product->total_sold }} sold</span>
                                            @endif
                                        </div>
                                        @if ($product->stock_quantity > 0)
                                            <form class="flex flex-wrap items-center gap-2" method="POST" action="{{ route('cart.items.store') }}">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                                                <select class="max-w-32 rounded-full border border-[#dce3d8] bg-white px-2 py-2 text-xs" name="size_id" required>
                                                    @foreach ($product->sizes as $size)
                                                        <option value="{{ $size->size_id }}">{{ $size->size_name }} · ₱{{ number_format((float) $size->price, 2) }}</option>
                                                    @endforeach
                                                </select>
                                                <input class="w-16 rounded-full border border-[#dce3d8] px-2 py-2 text-center text-xs" name="quantity" type="number" value="1" min="1" max="{{ min($product->stock_quantity, 99) }}" required>
                                                <button class="rounded-full border border-[#dce3d8] px-3 py-2 text-xs font-bold text-[#315d47] transition hover:bg-[#f1f5ed]" type="submit">Add to cart</button>
                                                <button class="rounded-full bg-[#244b3b] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#193c2e]" type="submit" formaction="{{ route('buy-now', $product->product_id) }}">Buy now</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @forelse ($menuCategories as $category)
                <section id="category-{{ $category->category_id }}" class="scroll-mt-28 pt-9">
                    <div class="mb-5 flex items-end justify-between gap-4">
                        <div>
                            <h3 class="font-serif text-2xl font-normal tracking-[-.4px] sm:text-[28px]">{{ $category->category_name }}</h3>
                            @if ($category->category_description)
                                <p class="mt-1.5 max-w-2xl text-sm leading-6 text-[#748178]">{{ $category->category_description }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-xs font-medium text-[#87938a]">{{ $category->products->count() }} {{ \Illuminate\Support\Str::plural('drink', $category->products->count()) }}</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
                        @foreach ($category->products as $product)
                            <article class="group overflow-hidden rounded-2xl border border-[#e8e9e1] bg-white transition duration-200 hover:-translate-y-1 hover:border-[#d3ddce] hover:shadow-[0_16px_38px_rgba(32,53,44,.09)]">
                                @if ($product->image_path)
                                    <img
                                        class="aspect-[1.65] w-full bg-[#edf1e9] object-cover"
                                        src="{{ asset('storage/'.ltrim($product->image_path, '/')) }}"
                                        alt="{{ $product->product_name }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="grid aspect-[1.65] place-items-center overflow-hidden bg-[#eff3eb]">
                                        <div class="relative grid size-32 place-items-center rounded-full bg-[#e1eadc] transition duration-300 group-hover:scale-105">
                                            <span class="absolute -left-2 top-7 size-5 rounded-full bg-[#d49f70]/80"></span>
                                            <span class="absolute -right-1 bottom-7 size-4 rounded-full bg-[#d49f70]/70"></span>
                                            <svg class="size-[72px] text-[#4c765c]" viewBox="0 0 72 72" fill="none" aria-hidden="true">
                                                <path d="M19 22h34l-3.5 39h-27L19 22Z" fill="#fffaf0" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/>
                                                <path d="M22 35c8 4 20 4 28 0" stroke="#90ad88" stroke-width="2"/>
                                                <path d="M29 12v13m14-18v18" stroke="#73543d" stroke-width="3" stroke-linecap="round"/>
                                                <path d="M27 29h18" stroke="#c88a59" stroke-width="4" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex min-h-[148px] flex-col p-5">
                                    <div class="flex items-start justify-between gap-3">
                                        <h4 class="font-serif text-xl leading-snug tracking-[-.3px]"><a class="hover:text-[#557760]" href="{{ route('menu.show', $product->product_id) }}">{{ $product->product_name }}</a></h4>
                                        <span class="shrink-0 rounded-full bg-[#f1f5ed] px-3 py-1.5 text-sm font-bold text-[#315d47]">From ₱{{ number_format((float) $product->base_price, 2) }}</span>
                                    </div>
                                    @if ($product->product_description)
                                        <p class="mt-2 line-clamp-2 text-sm leading-6 text-[#78847c]">{{ $product->product_description }}</p>
                                    @endif
                                    <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-4">
                                        <span class="text-xs font-semibold {{ $product->stock_quantity > 0 ? 'text-[#78907b]' : 'text-[#a4473d]' }}">
                                            {{ $product->stock_quantity > 0 ? $product->stock_quantity.' in stock' : 'Sold out' }}
                                        </span>
                                        @if ($product->stock_quantity > 0)
                                            <form class="flex flex-wrap items-center gap-2" method="POST" action="{{ route('cart.items.store') }}">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                                                <label class="sr-only" for="cart-size-{{ $product->product_id }}">Size for {{ $product->product_name }}</label>
                                                <select class="max-w-32 rounded-full border border-[#dce3d8] bg-white px-2 py-2 text-xs" id="cart-size-{{ $product->product_id }}" name="size_id" required>
                                                    @foreach ($product->sizes as $size)
                                                        <option value="{{ $size->size_id }}">{{ $size->size_name }} · ₱{{ number_format((float) $size->price, 2) }}</option>
                                                    @endforeach
                                                </select>
                                                <label class="sr-only" for="cart-quantity-{{ $product->product_id }}">Quantity for {{ $product->product_name }}</label>
                                                <input class="w-16 rounded-full border border-[#dce3d8] px-2 py-2 text-center text-xs" id="cart-quantity-{{ $product->product_id }}" name="quantity" type="number" value="1" min="1" max="{{ min($product->stock_quantity, 99) }}" required>
                                                <button class="rounded-full border border-[#dce3d8] px-3 py-2 text-xs font-bold text-[#315d47] transition hover:bg-[#f1f5ed]" type="submit">Add to cart</button>
                                                <button class="rounded-full bg-[#244b3b] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#193c2e]" type="submit" formaction="{{ route('buy-now', $product->product_id) }}">Buy now</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="mt-9 rounded-2xl border border-dashed border-[#d8e0d4] bg-white/70 px-6 py-14 text-center sm:py-20">
                    <div class="mx-auto grid size-16 place-items-center rounded-full bg-[#eff3eb] text-[#557760]">
                        <svg class="size-8" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                            <path d="M8 11h16l-2 17H10L8 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M11 5v8m10-11v11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M10 19c4 2 8 2 12 0" stroke="currentColor" stroke-width="1.6"/>
                        </svg>
                    </div>
                    <h3 class="mt-5 font-serif text-2xl font-normal">Our menu is being prepared</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#78847c]">
                        There aren’t any available drinks just yet. Please check back soon for something refreshing.
                    </p>
                </div>
            @endforelse
        </section>
    </main>

    @include('partials.footer')
</body>
</html>
