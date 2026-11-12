<nav class="store-navigation flex max-w-full items-center gap-1 overflow-x-auto sm:gap-2" aria-label="Main navigation">
    <a class="store-navigation-link {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">Home</a>
    <a class="store-navigation-link {{ request()->routeIs('account') ? 'is-active' : '' }}" href="{{ auth()->check() ? route('account') : route('login') }}">My account</a>
    @foreach (['Coffee' => 'coffee', 'Milk Tea' => 'milk-tea', 'Pastries' => 'pastries'] as $categoryName => $categorySlug)
        <a class="store-navigation-link {{ request()->routeIs('categories.show') && request()->route('categorySlug') === $categorySlug ? 'is-active' : '' }}" href="{{ route('categories.show', $categorySlug) }}">{{ $categoryName }}</a>
    @endforeach
    <a class="store-navigation-link {{ request()->routeIs('cart.index') ? 'is-active' : '' }}" href="{{ route('cart.index') }}">Cart{{ isset($cartCount) && $cartCount > 0 ? " ({$cartCount})" : '' }}</a>
</nav>
