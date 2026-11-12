<nav class="mb-6 flex gap-2 overflow-x-auto pb-2 text-sm" aria-label="Admin modules">
    @foreach ([
        'admin.dashboard' => 'Dashboard',
        'admin.menu.index' => 'Products & Categories',
        'admin.orders.index' => 'Orders',
        'admin.inventory.index' => 'Inventory',
        'admin.customers.index' => 'Customers',
        'admin.delivery-fees.index' => 'Delivery Fees',
    ] as $routeName => $label)
        <a class="shrink-0 rounded-full px-3 py-2 font-semibold {{ request()->routeIs($routeName) ? 'bg-[#244b3b] text-white' : 'bg-white text-[#52635a] hover:bg-[#edf2e9]' }}" href="{{ route($routeName) }}">{{ $label }}</a>
    @endforeach
</nav>
