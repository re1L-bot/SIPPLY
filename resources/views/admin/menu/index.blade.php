@extends('admin.layout')

@section('title', 'Menu administration')

@section('content')
    @push('styles')
    <style>
        /* Scoped so the shared product-fields partial keeps its original look */
        :where(.menu-page .ico) { width:1.25rem; height:1.25rem; }
        .menu-page .display { font-family:Georgia,'Times New Roman',serif; }
        .menu-page .eyebrow { letter-spacing:.2em; text-transform:uppercase; font-size:11px; font-weight:800; color:var(--accent); }
        .menu-page .panel { background:var(--card); border:1px solid var(--line); border-radius:28px; box-shadow:0 1px 2px rgba(84,40,18,.05), 0 8px 24px rgba(84,40,18,.06); }
        .menu-page .label { display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:var(--accent); }
        :where(.menu-page) .field { width:100%; border-radius:14px; border:1px solid var(--line); background:#fff; padding:11px 14px; font-size:14px; color:var(--ink); outline:none; transition:border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
        .menu-page .field:hover { border-color:var(--caramel); }
        .menu-page .field:focus { border-color:var(--caramel); box-shadow:0 0 0 4px rgba(217,154,105,.25); }
        :where(.menu-page) select.field { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238C7769' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; padding-right:36px; }
        .menu-page .pill-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; white-space:nowrap; border-radius:999px; padding:12px 22px; font-size:14px; font-weight:700; border:1px solid transparent; cursor:pointer; transition:transform .2s ease, background-color .2s ease, box-shadow .2s ease; }
        .menu-page .pill-button:hover { transform:translateY(-2px); }
        .menu-page .pill-button:active { transform:translateY(0) scale(.97); }
        .menu-page .btn-primary { background:var(--side); color:#fff7ef; box-shadow:0 8px 20px rgba(84,40,18,.2); }
        .menu-page .btn-primary:hover { background:var(--accent); box-shadow:0 12px 24px rgba(84,40,18,.28); }
        .menu-page .btn-outline { background:var(--card); color:var(--side); border-color:rgba(84,40,18,.22); }
        .menu-page .btn-outline:hover { background:var(--beige); }
        .menu-page .btn-small { padding:8px 14px; font-size:12px; }
        .menu-page .btn-danger { background:transparent; color:#a4473d; padding:8px 14px; border-radius:999px; font-size:12px; font-weight:700; border:0; cursor:pointer; transition:background-color .2s ease, transform .2s ease; }
        .menu-page .btn-danger:hover { background:#ffe3df; transform:scale(1.05); }
        .menu-page summary { list-style:none; cursor:pointer; }
        .menu-page summary::-webkit-details-marker { display:none; }
        .menu-page .chev { width:16px; height:16px; transition:transform .25s ease; }
        .menu-page .btn-small .chev { width:14px; height:14px; }
        .menu-page details[open] > summary .chev { transform:rotate(180deg); }
        .menu-page details[open] > form { animation:reveal .3s cubic-bezier(.2,.8,.2,1); }
        @keyframes reveal { from { opacity:0; transform:translateY(-8px); } }

        /* Hero */
        .m-hero { position:relative; overflow:hidden; display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:20px; padding:40px 44px; border-radius:36px; border:1px solid var(--line); background:linear-gradient(100deg,#FBEBDB,#F5E2D1 55%,#E7B88C 140%); }
        .m-hero h1 { margin:10px 0 8px; font-size:clamp(32px,4vw,48px); line-height:1.05; letter-spacing:-1.5px; }
        .m-hero p { margin:0; max-width:46ch; font-size:14px; line-height:1.6; color:#5b4234; }
        .m-hero > * { position:relative; z-index:2; }
        .m-orb { position:absolute !important; z-index:1 !important; right:-60px; top:-70px; width:260px; height:260px; border-radius:50%; background:var(--side); opacity:.92; }
        .m-bean { position:absolute !important; z-index:1 !important; width:24px; height:15px; border-radius:50%; background:#3a1a0b; animation:bean-float var(--d,5s) ease-in-out var(--delay,0s) infinite; }
        .m-bean::after { content:""; position:absolute; inset:6px 3px auto; height:1.5px; background:#1d0c04; border-radius:2px; }
        @keyframes bean-float { 0%,100% { transform:translateY(0) rotate(var(--r)); } 50% { transform:translateY(-12px) rotate(calc(var(--r) + 18deg)); } }
        .m-chip { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:99px; background:rgba(255,252,248,.92); border:1px solid var(--line); font-size:13px; font-weight:700; color:var(--accent); transition:transform .25s ease, box-shadow .25s ease; }
        .m-chip:hover { transform:translateY(-3px); box-shadow:0 10px 20px rgba(84,40,18,.15); }
        .m-chip b { font-size:20px; font-family:Georgia,serif; color:var(--ink); font-variant-numeric:tabular-nums; }

        /* Jump tabs with scroll-spy */
        .m-tabs { position:sticky; top:12px; z-index:20; display:inline-flex; gap:4px; margin-top:20px; padding:6px; border-radius:99px; background:rgba(255,252,248,.88); -webkit-backdrop-filter:blur(12px); backdrop-filter:blur(12px); border:1px solid var(--line); box-shadow:0 8px 24px rgba(84,40,18,.1); }
        .m-tab { padding:9px 18px; border-radius:99px; font-size:13px; font-weight:700; color:var(--muted); transition:background-color .25s ease, color .25s ease, transform .2s ease; }
        .m-tab:hover { color:var(--ink); background:var(--beige); transform:translateY(-1px); }
        .m-tab.is-on { background:var(--side); color:#fff7ef; box-shadow:0 6px 14px rgba(84,40,18,.25); }

        /* Alerts */
        .m-alert { display:flex; align-items:flex-start; gap:12px; margin-top:20px; padding:14px 20px; border-radius:18px; font-size:14px; animation:alert-in .45s cubic-bezier(.2,.8,.2,1); }
        .m-alert-ok { background:#e5f3e3; color:#2f6a45; font-weight:600; }
        .m-alert-bad { background:#ffe3df; color:#a4473d; }
        @keyframes alert-in { from { opacity:0; transform:translateY(-10px); } }

        /* Row cards */
        .row-card { position:relative; background:var(--card); border:1px solid var(--line); border-radius:22px; transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease; animation:card-in .5s cubic-bezier(.2,.7,.2,1) backwards; animation-delay:calc(var(--i, 0) * 60ms); }
        @keyframes card-in { from { opacity:0; transform:translateY(12px); } }
        .row-card:hover, .row-card:focus-within { transform:translateY(-2px); border-color:rgba(217,154,105,.55); box-shadow:0 14px 30px rgba(84,40,18,.12); }
        .row-card[hidden] { display:none; }
        .tile { display:grid; place-items:center; flex-shrink:0; background:var(--beige); color:var(--accent); transition:transform .3s cubic-bezier(.3,1.6,.5,1), background-color .2s ease, color .2s ease; }
        .row-card:hover .tile { transform:scale(1.1) rotate(-6deg); background:var(--accent); color:#fff7ef; }
        .p-img { width:64px; height:64px; flex-shrink:0; border-radius:18px; object-fit:cover; background:var(--beige); transition:transform .35s ease, box-shadow .35s ease; }
        .row-card:hover .p-img { transform:scale(1.12) rotate(-3deg); box-shadow:0 10px 20px rgba(84,40,18,.22); }
        .count-pill { border-radius:99px; background:var(--beige); padding:4px 11px; font-size:11px; font-weight:700; color:var(--accent); }

        .avail { display:inline-flex; align-items:center; gap:7px; border-radius:99px; padding:6px 13px; font-size:11px; font-weight:700; }
        .avail-ok { background:#e5f3e3; color:#2f6a45; }
        .avail-no { background:#ffe3df; color:#a4473d; }
        .dot { width:7px; height:7px; border-radius:99px; background:currentColor; }
        .avail-ok .dot { animation:pulse-g 1.8s ease-out infinite; }
        @keyframes pulse-g { 0% { box-shadow:0 0 0 0 rgba(63,155,95,.55); } 70%,100% { box-shadow:0 0 0 8px rgba(63,155,95,0); } }
        .low { color:#a4473d; font-weight:700; }
        .low .dot { display:inline-block; margin-right:5px; animation:pulse-r 1.5s ease-out infinite; }
        @keyframes pulse-r { 0% { box-shadow:0 0 0 0 rgba(164,71,61,.55); } 70%,100% { box-shadow:0 0 0 8px rgba(164,71,61,0); } }

        /* Filter bar */
        .menu-page .m-filter-search .m-s-ico { position:absolute; left:14px; top:0; bottom:0; margin:auto 0; width:16px; height:16px; color:var(--muted); pointer-events:none; transition:color .2s ease, scale .3s ease; }
        .menu-page .m-filter-search:focus-within .m-s-ico { color:var(--accent); scale:1.15; }
        .menu-page .m-filter-search .m-s-input { padding-left:42px; }
        .m-live { display:inline-flex; align-items:center; gap:8px; margin:14px 4px 0; font-size:13px; color:var(--muted); }
        .m-none { display:none; padding:40px; text-align:center; font-size:14px; color:var(--muted); }
        .m-none.is-on { display:block; animation:card-in .4s ease; }

        @media (max-width:640px) { .m-hero { padding:28px 22px; } .m-tabs { display:flex; overflow-x:auto; } }
    </style>
    @endpush

    <div class="menu-page">
        {{-- Hero --}}
        <section class="m-hero" aria-label="Menu overview">
            <span class="m-orb" aria-hidden="true"></span>
            <span class="m-bean" style="--r:-20deg;--d:5s;right:7%;top:30%" aria-hidden="true"></span>
            <span class="m-bean" style="--r:35deg;--d:6s;--delay:-1.5s;right:16%;top:62%" aria-hidden="true"></span>
            <span class="m-bean" style="--r:10deg;--d:4.5s;--delay:-3s;right:3%;top:70%" aria-hidden="true"></span>
            <div>
                <p class="eyebrow">Store management</p>
                <h1 class="display">Menu dashboard</h1>
                <p>Manage products, ingredients, prices, images, and availability. The customer menu uses three fixed categories.</p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <span class="m-chip"><b data-count="{{ $categories->count() }}">{{ $categories->count() }}</b> {{ \Illuminate\Support\Str::plural('category', $categories->count()) }}</span>
                <span class="m-chip"><b data-count="{{ $products->total() }}">{{ $products->total() }}</b> {{ \Illuminate\Support\Str::plural('drink', $products->total()) }}</span>
            </div>
        </section>

        <nav class="m-tabs" aria-label="Jump to section">
            <a class="m-tab is-on" href="#categories" data-tab="categories">Categories</a>
            <a class="m-tab" href="#sizes" data-tab="sizes">Sizes</a>
            <a class="m-tab" href="#products" data-tab="products">Products</a>
        </nav>

        {{-- Alerts --}}
        @if (session('status'))
            <div class="m-alert m-alert-ok" role="status">
                <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="m-alert m-alert-bad" role="alert">
                <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <div>
                    <p style="margin:0;font-weight:700">Please review the changes and try again.</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Categories --}}
        <section id="categories" class="panel mt-8 scroll-mt-24 p-5 sm:p-8">
            <div class="mb-6">
                <h2 class="display text-3xl font-bold" style="margin:0;letter-spacing:-.5px">Category management</h2>
                <p class="mt-1.5 max-w-2xl text-sm" style="color:var(--muted)">Add, edit, or remove categories for your menu items. Duplicate names are prevented, and categories with assigned products cannot be deleted.</p>
            </div>

            <details class="row-card mb-5 overflow-hidden">
                <summary class="flex items-center justify-between gap-3 px-5 py-4 text-sm font-bold" style="color:var(--accent)">
                    <span class="flex items-center gap-2.5">
                        <span class="tile size-8 rounded-lg"><svg class="ico size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg></span>
                        Add a new category
                    </span>
                    <svg class="ico chev size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </summary>
                <form class="p-5" style="border-top:1px solid var(--line)" method="POST" action="{{ route('admin.categories.store') }}">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="new-category-name">Category name</label>
                            <input class="field" id="new-category-name" name="category_name" maxlength="120" placeholder="e.g. Smoothies" required>
                        </div>
                        <div>
                            <label class="label" for="new-category-description">Description <span class="font-normal" style="color:var(--muted)">(optional)</span></label>
                            <input class="field" id="new-category-description" name="description" maxlength="1000" placeholder="Brief description">
                        </div>
                    </div>
                    <button class="pill-button btn-primary btn-small mt-4" type="submit">Create category</button>
                </form>
            </details>

            <div class="space-y-3">
                @forelse ($categories as $category)
                    <div class="row-card flex flex-wrap items-center justify-between gap-3 p-4 sm:px-5" style="--i:{{ min($loop->index, 8) }}">
                        <details class="min-w-0 flex-1">
                            <summary class="flex flex-wrap items-center justify-between gap-3">
                                <span class="flex min-w-0 items-center gap-3">
                                    <span class="tile size-10 rounded-xl"><svg class="ico size-5" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg></span>
                                    <span class="truncate text-sm font-bold">{{ $category->category_name }}</span>
                                    <span class="count-pill shrink-0">{{ $category->products_count }} {{ \Illuminate\Support\Str::plural('product', $category->products_count) }}</span>
                                </span>
                                <span class="pill-button btn-outline btn-small">Edit <svg class="ico chev" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></span>
                            </summary>
                            <form class="mt-4 pt-4" style="border-top:1px solid var(--line)" method="POST" action="{{ route('admin.categories.update', $category->category_id) }}">
                                @csrf
                                @method('PUT')
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="label" for="edit-cat-name-{{ $category->category_id }}">Category name</label>
                                        <input class="field" id="edit-cat-name-{{ $category->category_id }}" name="category_name" value="{{ $category->category_name }}" maxlength="120" required>
                                    </div>
                                    <div>
                                        <label class="label" for="edit-cat-desc-{{ $category->category_id }}">Description</label>
                                        <input class="field" id="edit-cat-desc-{{ $category->category_id }}" name="description" value="{{ $category->description ?? '' }}" maxlength="1000">
                                    </div>
                                </div>
                                <button class="pill-button btn-primary btn-small mt-4" type="submit">Save category</button>
                            </form>
                        </details>
                        <div>
                            @if ($category->products_count > 0)
                                <span class="text-xs italic" style="color:var(--muted)" title="Cannot delete: assigned to {{ $category->products_count }} products">In use ({{ $category->products_count }})</span>
                            @else
                                <form method="POST" action="{{ route('admin.categories.destroy', $category->category_id) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from('Are you sure you want to delete category "'.$category->category_name.'"?') }})">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-danger" type="submit">Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="rounded-2xl px-5 py-8 text-center text-sm" style="border:1px dashed rgba(84,40,18,.25);color:var(--muted)">No categories yet. Add your first category above.</p>
                @endforelse
            </div>
        </section>

        {{-- Sizes --}}
        <section id="sizes" class="panel mt-8 scroll-mt-24 p-5 sm:p-8">
            <div class="mb-6">
                <h2 class="display text-3xl font-bold" style="margin:0;letter-spacing:-.5px">Size management</h2>
                <p class="mt-1.5 max-w-2xl text-sm" style="color:var(--muted)">Add or remove drink sizes. Each size becomes available across all menu items with customizable product-specific pricing.</p>
            </div>

            <details class="row-card mb-5 overflow-hidden">
                <summary class="flex items-center justify-between gap-3 px-5 py-4 text-sm font-bold" style="color:var(--accent)">
                    <span class="flex items-center gap-2.5">
                        <span class="tile size-8 rounded-lg"><svg class="ico size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg></span>
                        Add a new size
                    </span>
                    <svg class="ico chev size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </summary>
                <form class="p-5" style="border-top:1px solid var(--line)" method="POST" action="{{ route('admin.sizes.store') }}">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="new-size-name">Size name</label>
                            <input class="field" id="new-size-name" name="size_name" maxlength="50" placeholder="e.g. Medium, 22oz" required>
                        </div>
                        <div>
                            <label class="label" for="new-size-adj">Default price adjustment (₱) <span class="font-normal" style="color:var(--muted)">(optional)</span></label>
                            <input class="field" id="new-size-adj" name="price_adjustment" type="number" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <button class="pill-button btn-primary btn-small mt-4" type="submit">Add size</button>
                </form>
            </details>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($sizes as $size)
                    <div class="row-card flex items-center justify-between gap-3 p-4" style="--i:{{ min($loop->index, 8) }}">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="tile size-10 rounded-xl"><svg class="ico size-5" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg></span>
                            <div class="min-w-0">
                                <span class="block truncate text-sm font-bold">{{ $size->size_name }}</span>
                                <span class="text-xs" style="color:var(--muted)">Default adjustment: +₱{{ number_format((float) $size->price_adjustment, 2) }}</span>
                            </div>
                        </div>
                        @if ($sizes->count() > 1)
                            <form method="POST" action="{{ route('admin.sizes.destroy', $size->size_id) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from('Remove size "'.$size->size_name.'"?') }})">
                                @csrf
                                @method('DELETE')
                                <button class="btn-danger" type="submit">Delete</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Products --}}
        <section id="products" class="mt-8 scroll-mt-24">
            <div class="mb-5">
                <h2 class="display text-3xl font-bold" style="margin:0;letter-spacing:-.5px">Products</h2>
            </div>

            <details class="panel overflow-hidden">
                <summary class="flex items-center justify-between gap-3 px-5 py-4 text-sm font-bold sm:px-7" style="color:var(--accent)">
                    <span class="flex items-center gap-2.5">
                        <span class="tile size-8 rounded-lg"><svg class="ico size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg></span>
                        Add a Product
                    </span>
                    <svg class="ico chev size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </summary>Product name
                <form class="p-5 sm:p-7" style="border-top:1px solid var(--line)" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('admin.menu.partials.product-fields', ['product' => null, 'formKey' => 'new'])
                    <button class="pill-button btn-primary mt-5" type="submit">Create Product</button>
                </form>
            </details>

            <form class="panel mt-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr_auto]" method="GET" action="{{ route('admin.menu.index') }}">
                <div class="relative m-filter-search">
                    <svg class="ico m-s-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <label class="sr-only" for="product-search">Search products</label>
                    <input class="field m-s-input" id="product-search" name="search" value="{{ $filters['search'] }}" placeholder="Search products" autocomplete="off" data-product-search>
                </div>
                <div>
                    <label class="sr-only" for="product-category">Filter by category</label>
                    <select class="field" id="product-category" name="category">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->category_id }}" @selected((string) $filters['category'] === (string) $category->category_id)>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sr-only" for="product-status">Filter by Status</label>
                    <select class="field" id="product-status" name="status">
                        <option value="">All statuses</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active' || ($filters['availability'] ?? '') === 'available')>Active</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive' || ($filters['availability'] ?? '') === 'unavailable')>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="sr-only" for="product-stock">Stock</label>
                    <select class="field" id="product-stock" name="stock">
                        <option value="">All stock</option>
                        <option value="in_stock" @selected($filters['stock'] === 'in_stock')>In stock</option>
                        <option value="out_of_stock" @selected($filters['stock'] === 'out_of_stock')>Out of stock</option>
                    </select>
                </div>
                <button class="pill-button btn-primary" type="submit">Filter</button>
            </form>

            @if ($products->count())
                <p class="m-live" aria-live="polite" data-product-count>Showing {{ $products->count() }} {{ \Illuminate\Support\Str::plural('drink', $products->count()) }} on this page</p>
            @endif

            <div class="mt-4 space-y-3" data-product-list>
                @forelse ($products as $product)
                    @php $lowStock = $product->stock_quantity <= 5; @endphp
                    <article class="row-card overflow-hidden" style="--i:{{ min($loop->index, 8) }}" data-product-card data-haystack="{{ mb_strtolower($product->product_name.' '.$product->category_name) }}">
                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 sm:px-5">
                            <details class="min-w-0 flex-1">
                                <summary class="flex flex-wrap items-center justify-between gap-4">
                                    <span class="flex min-w-0 items-center gap-4">
                                        @if ($product->image_path)
                                            <img class="p-img" src="{{ asset('storage/'.ltrim($product->image_path, '/')) }}" alt="{{ $product->product_name }}">
                                        @else
                                            <span class="tile p-img" aria-hidden="true"><svg class="ico size-7" viewBox="0 0 24 24" style="stroke-width:1.5"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg></span>
                                        @endif
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-bold">{{ $product->product_name }}</span>
                                            <span class="mt-1 block text-xs leading-5" style="color:var(--muted)">{{ $product->category_name }} · from ₱{{ number_format((float) $product->base_price, 2) }} · {{ $sizes->pluck('size_name')->join(', ') }} sizes · @if ($lowStock)<span class="low"><span class="dot"></span>{{ $product->stock_quantity }} in stock · low</span>@else{{ $product->stock_quantity }} in stock @endif</span>
                                        </span>
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <span class="avail {{ $product->is_available ? 'avail-ok' : 'avail-no' }}"><span class="dot"></span>{{ $product->is_available ? 'Available' : 'Unavailable' }}</span>
                                        <span class="pill-button btn-outline btn-small">Edit <svg class="ico chev" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></span>
                                    </span>
                                </summary>
                                <form class="mt-5 pt-5" style="border-top:1px solid var(--line)" method="POST" action="{{ route('admin.products.update', $product->product_id) }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    @include('admin.menu.partials.product-fields', ['product' => $product, 'formKey' => 'edit-'.$product->product_id])
                                    <button class="pill-button btn-primary mt-5" type="submit">Save changes</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.products.destroy', $product->product_id) }}" onsubmit="return confirm('Delete this drink?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="panel px-6 py-14 text-center" style="border-style:dashed">
                        <span class="tile mx-auto size-14 rounded-2xl"><svg class="ico size-7" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg></span>
                        <p class="mt-4 text-sm font-semibold">No drinks yet</p>
                        <p class="mt-1 text-sm" style="color:var(--muted)">Create your first menu item above.</p>
                    </div>
                @endforelse
                <p class="panel m-none" data-product-none>No drinks on this page match your search. Press Enter to search all drinks.</p>
            </div>

            @if ($products->hasPages())
                <div class="mt-5">{{ $products->links() }}</div>
            @endif
        </section>
    </div>

    @push('scripts')
    <script>
        (function () {
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // Count-up for the hero chips
            if (!reduce) {
                document.querySelectorAll('[data-count]').forEach(function (el) {
                    var end = parseInt(el.getAttribute('data-count'), 10) || 0, t0 = null;
                    el.textContent = '0';
                    requestAnimationFrame(function tick(ts) {
                        if (t0 === null) { t0 = ts; }
                        var k = Math.min((ts - t0) / 900, 1);
                        el.textContent = Math.round(end * (1 - Math.pow(1 - k, 3)));
                        if (k < 1) { requestAnimationFrame(tick); } else { el.textContent = end; }
                    });
                });
            }

            // Scroll-spy for the jump tabs
            var tabs = document.querySelectorAll('[data-tab]');
            if ('IntersectionObserver' in window && tabs.length) {
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (en) {
                        if (!en.isIntersecting) { return; }
                        tabs.forEach(function (t) { t.classList.toggle('is-on', t.getAttribute('data-tab') === en.target.id); });
                    });
                }, { rootMargin: '-35% 0px -55% 0px' });
                ['categories', 'sizes', 'products'].forEach(function (id) { var s = document.getElementById(id); if (s) { io.observe(s); } });
            }

            // Live filter for the drinks on this page
            var input = document.querySelector('[data-product-search]');
            var cards = Array.prototype.slice.call(document.querySelectorAll('[data-product-card]'));
            var counter = document.querySelector('[data-product-count]');
            var none = document.querySelector('[data-product-none]');
            if (input && cards.length) {
                var total = cards.length;
                function run() {
                    var q = input.value.trim().toLowerCase(), shown = 0;
                    cards.forEach(function (c) {
                        var ok = !q || c.getAttribute('data-haystack').indexOf(q) !== -1;
                        c.hidden = !ok;
                        if (ok) { shown++; }
                    });
                    if (counter) { counter.textContent = q ? 'Showing ' + shown + ' of ' + total + ' on this page' : 'Showing ' + total + ' ' + (total === 1 ? 'drink' : 'drinks') + ' on this page'; }
                    if (none) { none.classList.toggle('is-on', shown === 0); }
                }
                input.addEventListener('input', run);
                run();
            }
        })();
    </script>
    @endpush
@endsection