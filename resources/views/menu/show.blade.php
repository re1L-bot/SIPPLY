<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f4ed">
    <title>{{ $product->product_name }} | SipPly</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="warm-theme min-h-screen bg-[#f7f7f1] font-sans text-[#20352c] antialiased">
    <header class="border-b border-[#e7e9df]">
        <div class="mx-auto flex h-[76px] max-w-7xl items-center justify-between gap-5 px-5 sm:px-8">
            <a class="flex items-center gap-2 font-serif text-[25px] font-bold tracking-[-1px] text-[#4b2413]" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('images/favicon.png') }}" alt="SipPly Coffee Shop logo" width="56" height="56">SipPly</a>
            @include('partials.store-navigation')
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-5 py-10 sm:px-8">
        <a class="text-sm font-semibold text-[#315d47] underline" href="{{ route('products.index') }}">← Back to menu</a>
        <section class="mt-6 grid gap-8 rounded-3xl border border-[#e8e9e1] bg-white p-5 sm:p-8 lg:grid-cols-2">
            @if ($product->image_path)
                <img class="aspect-[1.2] w-full rounded-2xl bg-[#eff3eb] object-cover" src="{{ asset('storage/'.ltrim($product->image_path, '/')) }}" alt="{{ $product->product_name }}">
            @else
                <div class="grid aspect-[1.2] place-items-center rounded-2xl bg-[#eff3eb] font-serif text-7xl text-[#4c765c]">S</div>
            @endif
            <div class="flex flex-col justify-center">
                <p class="text-xs font-bold uppercase tracking-[.18em] text-[#78907b]">{{ $product->category_name }}</p>
                <h1 class="mt-2 font-serif text-4xl">{{ $product->product_name }}</h1>
                <p class="mt-4 text-lg font-bold text-[#315d47]">From ₱{{ number_format((float) $product->base_price, 2) }}</p>
                <p class="mt-4 leading-7 text-[#748178]">{{ $product->product_description ?: 'Made fresh for a refreshing moment.' }}</p>
                @if ($ingredients->isNotEmpty())
                    <div class="mt-5">
                        <h2 class="text-sm font-bold text-[#52635a]">Ingredients</h2>
                        <ul class="mt-2 flex flex-wrap gap-2">
                            @foreach ($ingredients as $ingredient)
                                <li class="rounded-full bg-[#f1f5ed] px-3 py-1.5 text-xs text-[#496352]">{{ $ingredient }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <p class="mt-5 text-sm font-semibold {{ $product->stock_quantity > 0 ? 'text-[#557760]' : 'text-[#a4473d]' }}">
                    {{ $product->stock_quantity > 0 ? $product->stock_quantity.' in stock' : 'Currently sold out' }}
                </p>
                <div class="mt-3 flex flex-wrap gap-2" aria-label="Available sizes">
                    @foreach ($sizes as $size)
                        <span class="rounded-full bg-[#f1f5ed] px-3 py-1.5 text-xs font-medium text-[#496352]">{{ $size->size_name }} · ₱{{ number_format((float) $size->price, 2) }}</span>
                    @endforeach
                </div>
                @if ($product->stock_quantity > 0)
                    <form class="mt-5 flex flex-wrap gap-3" method="POST" action="{{ route('cart.items.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                        <label class="sr-only" for="size_id">Choose a size</label>
                        <select class="min-w-40 rounded-full border border-[#dce3d8] bg-white px-4 py-2 text-sm" id="size_id" name="size_id" required>
                            @foreach ($sizes as $size)
                                <option value="{{ $size->size_id }}" @selected((string) old('size_id') === (string) $size->size_id)>{{ $size->size_name }} · ₱{{ number_format((float) $size->price, 2) }}</option>
                            @endforeach
                        </select>
                        <label class="sr-only" for="quantity">Quantity</label>
                        <input class="w-24 rounded-full border border-[#dce3d8] px-3 py-2 text-center" id="quantity" name="quantity" type="number" value="1" min="1" max="{{ min($product->stock_quantity, 99) }}" required>
                        <button class="rounded-full border border-[#dce3d8] px-5 py-2 text-sm font-bold text-[#315d47]" type="submit">Add to cart</button>
                        <button class="rounded-full bg-[#244b3b] px-5 py-2 text-sm font-bold text-white" type="submit" formaction="{{ route('buy-now', $product->product_id) }}">Buy now</button>
                    </form>
                @endif
            </div>
        </section>

        @if ($relatedProducts->isNotEmpty())
            <section class="mt-12">
                <h2 class="font-serif text-3xl">You might also like</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($relatedProducts as $relatedProduct)
                        <a class="rounded-2xl border border-[#e8e9e1] bg-white p-5 hover:border-[#c8d6c6]" href="{{ route('menu.show', $relatedProduct->product_id) }}">
                            <p class="font-serif text-xl">{{ $relatedProduct->product_name }}</p>
                            <p class="mt-2 text-sm font-bold text-[#315d47]">₱{{ number_format((float) $relatedProduct->base_price, 2) }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
    @include('partials.footer')
</body>
</html>
