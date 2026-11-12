@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    @php
        // ---- Data (unchanged logic from your original view) ----
        $isPaginator = $recentOrders instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
        $orderRows = $isPaginator ? collect($recentOrders->items()) : collect($recentOrders);

        $monthSales = $monthSales ?? null;
        if ($monthSales === null) {
            try {
                $monthSales = \Illuminate\Support\Facades\DB::table('orders')
                    ->where('order_status', 'completed')
                    ->whereBetween('placed_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('grand_total');
            } catch (\Throwable $e) {
                $monthSales = null;
            }
        }
        $monthNote = $monthSales !== null ? '₱'.number_format((float) $monthSales, 2).' in '.now()->format('F') : null;

        $firstName = trim(explode(' ', trim(auth()->user()->first_name ?? auth()->user()->name ?? 'there'))[0]);

        $stats = [
            ['Total Products', $productCount, 'package'],
            ['Total Orders', $orderCount, 'clipboard'],
            ['Pending Orders', $pendingOrders, 'clock'],
            ['Completed Orders', $completedOrders, 'check'],
            ['Total Customers', $customerCount, 'users'],
        ];
        $icons = [
            'package' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
            'clipboard' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
            'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'check' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'trend' => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
        ];
        // Put your cup photo (transparent PNG works best) at public/images/admin-hero.png
        $heroImage = file_exists(public_path('images/admin-hero.png')) ? asset('images/admin-hero.png') : null;
    @endphp

    @push('styles')
    <style>
        .hero { position:relative; overflow:hidden; display:grid; grid-template-columns:1.1fr 1fr; align-items:center; min-height:270px; border-radius:36px; background:linear-gradient(100deg,#FBEBDB 0%,#F5E2D1 45%,#D99A69 140%); border:1px solid var(--line); }
        .hero-copy { position:relative; z-index:2; padding:40px 44px; }
        .hero-label { display:inline-flex; align-items:center; gap:12px; font-size:12px; font-weight:700; letter-spacing:.22em; color:var(--accent); }
        .hero-label::after { content:""; width:56px; height:1px; background:currentColor; opacity:.4; }
        .hero h1 { margin:14px 0 10px; font-size:clamp(34px,4.2vw,52px); line-height:1.02; letter-spacing:-1.5px; color:var(--ink); }
        .hero p { margin:0 0 22px; max-width:34ch; color:#5b4234; font-size:16px; }
        .hero-art { position:relative; align-self:stretch; min-height:220px; }
        .hero-art::before { content:""; position:absolute; right:-6%; bottom:-35%; width:120%; aspect-ratio:1; border-radius:50%; background:var(--side); }
        .hero-art img { position:absolute; right:12%; bottom:0; height:112%; max-height:330px; object-fit:contain; filter:drop-shadow(0 18px 24px rgba(56,32,21,.35)); }
        .hero-cta { position:absolute; z-index:3; top:24px; right:28px; }
        .bean { position:absolute; width:26px; height:16px; border-radius:50%; background:#3a1a0b; transform:rotate(var(--r)); box-shadow:inset 0 0 0 1px rgba(255,255,255,.06); }
        .bean::after { content:""; position:absolute; inset:7px 3px auto; height:1.5px; background:#1d0c04; border-radius:2px; }
        @media (max-width:820px) { .hero { grid-template-columns:1fr; } .hero-art { min-height:180px; order:2; } .hero-cta { position:static; margin:0 44px 22px; order:3; display:inline-flex; } .hero-copy { padding:32px 26px 8px; } }

        .stat { position:relative; overflow:hidden; display:flex; align-items:center; gap:18px; padding:24px; }
        .stat-ico { display:grid; place-items:center; width:60px; height:60px; border-radius:999px; background:var(--beige); color:var(--accent); }
        .stat-label { font-size:14px; font-weight:600; color:var(--muted); }
        .stat-value { margin-top:2px; font-size:32px; font-weight:700; line-height:1.1; color:var(--ink); }
        .stat-dark { background:linear-gradient(135deg,#6b351c,var(--side)); border-color:transparent; color:#fff7ef; }
        .stat-dark .stat-ico { background:rgba(255,255,255,.14); color:#fff; }
        .stat-dark .stat-label { color:rgba(255,247,239,.8); } .stat-dark .stat-value { color:#fff; }
        .stat-note { margin-top:6px; font-size:12px; font-weight:700; color:var(--caramel); }
        .stat-bars { position:absolute; right:20px; bottom:0; display:flex; align-items:flex-end; gap:6px; opacity:.28; }
        .stat-bars i { width:10px; border-radius:4px 4px 0 0; background:#fff; }
        /* Live animation: drifting beans, rising steam, growing sales bars */
        @keyframes bean-float { 0%,100% { transform:translateY(0) rotate(var(--r)); } 50% { transform:translateY(-12px) rotate(calc(var(--r) + 18deg)); } }
        .bean { animation:bean-float var(--d,5s) ease-in-out var(--delay,0s) infinite; }
        @keyframes steam-rise { 0% { transform:translateY(0) scaleX(1); opacity:0; } 20% { opacity:.55; } 100% { transform:translateY(-90px) scaleX(1.8); opacity:0; } }
        .steam { position:absolute; z-index:1; width:16px; height:64px; border-radius:50%; background:radial-gradient(closest-side,rgba(255,255,255,.75),rgba(255,255,255,0)); filter:blur(5px); opacity:0; animation:steam-rise 4.5s ease-out var(--delay,0s) infinite; }
        @keyframes bar-rise { from { transform:scaleY(.25); } to { transform:scaleY(1); } }
        .stat-bars i { transform-origin:bottom; animation:bar-rise 1.6s ease-in-out var(--delay,0s) infinite alternate; }
        .order-link { display:inline-flex; align-items:center; gap:10px; font-weight:700; color:var(--accent); text-decoration:underline; text-decoration-color:rgba(217,154,105,.6); text-underline-offset:4px; }
        .order-link:hover { color:#c75f26; }
    </style>
    @endpush

    {{-- Welcome banner --}}
    <section class="hero" aria-label="Welcome">
        <div class="hero-copy">
            <span class="hero-label">ADMIN DASHBOARD</span>
            <h1 class="font-display">Welcome back,<br>{{ $firstName }}!</h1>
            <p>Manage your store and keep brewing success.</p>
            <a class="btn btn-primary" href="{{ route('admin.orders.index') }}">View Orders
                <svg class="ico" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>
        <a class="btn btn-ghost hero-cta" href="{{ route('admin.orders.index', ['status' => 'pending']) }}">
            Review pending orders ({{ $pendingOrders }})
            <svg class="ico" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
        <div class="hero-art" aria-hidden="true">
            @if ($heroImage)<img src="{{ $heroImage }}" alt="">@endif
            <span class="steam" style="right:23%;bottom:62%;--delay:0s"></span>
            <span class="steam" style="right:27%;bottom:60%;--delay:1.5s"></span>
            <span class="steam" style="right:31%;bottom:63%;--delay:3s"></span>
            <span class="bean" style="--r:-20deg;--d:5s;--delay:0s;right:6%;bottom:12%"></span>
            <span class="bean" style="--r:35deg;--d:6s;--delay:-1s;right:16%;bottom:6%"></span>
            <span class="bean" style="--r:10deg;--d:4.5s;--delay:-2s;right:28%;bottom:10%"></span>
            <span class="bean" style="--r:-50deg;--d:5.5s;--delay:-3s;right:3%;bottom:30%"></span>
            <span class="bean" style="--r:60deg;--d:6.5s;--delay:-4s;right:38%;bottom:4%"></span>
        </div>
    </section>

    {{-- Statistics --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" style="margin-top:24px">
        @foreach ($stats as [$label, $value, $icon])
            <div class="card stat">
                <span class="stat-ico"><svg class="ico" width="28" height="28" viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$icon] !!}</svg></span>
                <div style="min-width:0">
                    <p class="stat-label">{{ $label }}</p>
                    <p class="stat-value font-display" data-count="{{ (int) $value }}">{{ $value }}</p>
                </div>
            </div>
        @endforeach
        <div class="card stat stat-dark">
            <span class="stat-ico"><svg class="ico" width="28" height="28" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['trend'] !!}</svg></span>
            <div style="min-width:0">
                <p class="stat-label">Total Sales</p>
                <p class="stat-value font-display" data-count-money="{{ (float) $completedSales }}">₱{{ number_format((float) $completedSales, 2) }}</p>
                @if ($monthNote)<p class="stat-note">↑ {{ $monthNote }}</p>@endif
            </div>
            <span class="stat-bars" aria-hidden="true"><i style="height:22px;--delay:0s"></i><i style="height:34px;--delay:.3s"></i><i style="height:28px;--delay:.6s"></i><i style="height:46px;--delay:.9s"></i><i style="height:58px;--delay:1.2s"></i></span>
        </div>
    </div>

    {{-- Recent orders --}}
    <section class="card" style="margin-top:24px;padding:28px" aria-labelledby="recent-orders-title">
        <div class="flex flex-wrap items-center justify-between gap-3" style="margin-bottom:20px">
            <h2 id="recent-orders-title" class="font-display" style="margin:0;font-size:26px;font-weight:700;color:var(--ink)">Recent Orders</h2>
            <a class="btn btn-soft" href="{{ route('admin.orders.index') }}">View All Orders
                <svg class="ico" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>

        <div class="table-wrap">
            <table class="table" @unless ($isPaginator) data-paginate-rows data-page-size="6" @endunless>
                <thead>
                    <tr><th>Order ID</th><th>Customer</th><th>Status</th><th>Payment</th><th class="num">Total</th><th>Placed</th></tr>
                </thead>
                <tbody>
                    @forelse ($orderRows as $order)
                        @php
                            $statusBadge = match ($order->order_status) {
                                'completed' => 'badge-ok',
                                'cancelled' => 'badge-bad',
                                'pending', 'out_for_delivery', 'ready' => 'badge-warn',
                                default => 'badge-neutral',
                            };
                            $paymentBadge = match (strtolower((string) ($order->payment_status ?? 'unknown'))) {
                                'paid', 'completed', 'success' => 'badge-ok',
                                'failed', 'refunded', 'cancelled' => 'badge-bad',
                                'pending', 'unpaid' => 'badge-warn',
                                default => 'badge-neutral',
                            };
                            $placed = $order->placed_at ? \Illuminate\Support\Carbon::parse($order->placed_at) : null;
                        @endphp
                        <tr data-row>
                            <td>
                                <a class="order-link" href="{{ route('admin.orders.show', $order->order_id) }}">
                                    <svg class="ico" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" style="color:var(--ink)"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>{{ trim($order->first_name.' '.$order->last_name) }}</td>
                            <td><span class="badge {{ $statusBadge }}">{{ $order->order_status === 'completed' ? 'Delivered' : ucfirst(str_replace('_', ' ', $order->order_status)) }}</span></td>
                            <td><span class="badge {{ $paymentBadge }}">{{ ucfirst($order->payment_status ?? 'unknown') }}</span></td>
                            <td class="num" style="font-weight:700">₱{{ number_format((float) $order->grand_total, 2) }}</td>
                            <td style="white-space:nowrap;color:var(--muted)">{{ $placed ? $placed->format('M j, Y · g:i A') : $order->placed_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="padding:48px 0;text-align:center;color:var(--muted)">No orders yet. New orders will show up here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($isPaginator)
            @if ($recentOrders->total() > 0)
                <div class="pager">
                    <p style="margin:0;font-size:12px;color:var(--muted)">Showing <strong style="color:var(--ink)">{{ $recentOrders->firstItem() }}–{{ $recentOrders->lastItem() }}</strong> of <strong style="color:var(--ink)">{{ $recentOrders->total() }}</strong> orders</p>
                    @if ($recentOrders->hasPages())
                        @php
                            $cp = $recentOrders->currentPage();
                            $lp = $recentOrders->lastPage();
                            $from = max(1, $cp - 2);
                            $to = min($lp, $cp + 2);
                        @endphp
                        <nav class="pager-nav" aria-label="Recent orders pages">
                            <a class="pg" href="{{ $recentOrders->previousPageUrl() ?? '#' }}" @if ($recentOrders->onFirstPage()) aria-disabled="true" tabindex="-1" @endif aria-label="Previous page"><svg class="ico" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></a>
                            @if ($from > 1)
                                <a class="pg" href="{{ $recentOrders->url(1) }}">1</a>
                                @if ($from > 2)<span style="padding:0 4px;color:var(--muted)">…</span>@endif
                            @endif
                            @foreach ($recentOrders->getUrlRange($from, $to) as $page => $url)
                                <a class="pg {{ $page === $cp ? 'is-current' : '' }}" href="{{ $url }}" @if ($page === $cp) aria-current="page" @endif>{{ $page }}</a>
                            @endforeach
                            @if ($to < $lp)
                                @if ($to < $lp - 1)<span style="padding:0 4px;color:var(--muted)">…</span>@endif
                                <a class="pg" href="{{ $recentOrders->url($lp) }}">{{ $lp }}</a>
                            @endif
                            <a class="pg" href="{{ $recentOrders->nextPageUrl() ?? '#' }}" @unless ($recentOrders->hasMorePages()) aria-disabled="true" tabindex="-1" @endunless aria-label="Next page"><svg class="ico" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></a>
                        </nav>
                    @endif
                </div>
            @endif
        @else
            <div class="pager" data-pager hidden>
                <p style="margin:0;font-size:12px;color:var(--muted)" data-pager-summary></p>
                <nav class="pager-nav" data-pager-nav aria-label="Recent orders pages"></nav>
            </div>
        @endif
    </section>

    @push('scripts')
    <script>
        // Count-up on load. Final text is already in the HTML, so it is correct without JS.
        (function () {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
            var els = document.querySelectorAll('[data-count], [data-count-money]');
            var dur = 1100, t0 = null;
            var items = Array.prototype.map.call(els, function (el) {
                var money = el.hasAttribute('data-count-money');
                return { el: el, money: money, end: parseFloat(el.getAttribute(money ? 'data-count-money' : 'data-count')) || 0 };
            });
            function fmt(it, v) {
                return it.money ? '₱' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : String(Math.round(v));
            }
            function tick(ts) {
                if (t0 === null) { t0 = ts; }
                var k = Math.min((ts - t0) / dur, 1), e = 1 - Math.pow(1 - k, 3);
                items.forEach(function (it) { it.el.textContent = fmt(it, it.end * e); });
                if (k < 1) { requestAnimationFrame(tick); } else { items.forEach(function (it) { it.el.textContent = fmt(it, it.end); }); }
            }
            items.forEach(function (it) { it.el.textContent = fmt(it, 0); });
            requestAnimationFrame(tick);
        })();
    </script>
    @endpush

    @unless ($isPaginator)
        @push('scripts')
        <script>
            // Browser-side pagination, used when the controller passes a plain collection.
            (function () {
                var table = document.querySelector('[data-paginate-rows]');
                var pager = document.querySelector('[data-pager]');
                if (!table || !pager) { return; }
                var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr[data-row]'));
                var size = parseInt(table.getAttribute('data-page-size'), 10) || 6;
                if (rows.length <= size) { return; }
                var pages = Math.ceil(rows.length / size);
                var summary = pager.querySelector('[data-pager-summary]');
                var nav = pager.querySelector('[data-pager-nav]');
                pager.hidden = false;

                function button(label, page, opts) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'pg' + (opts.current ? ' is-current' : '');
                    b.innerHTML = label;
                    if (opts.aria) { b.setAttribute('aria-label', opts.aria); }
                    if (opts.current) { b.setAttribute('aria-current', 'page'); }
                    if (opts.disabled) { b.disabled = true; }
                    b.addEventListener('click', function () { render(page); });
                    return b;
                }
                function render(page) {
                    var current = Math.min(Math.max(page, 1), pages);
                    var start = (current - 1) * size;
                    rows.forEach(function (row, i) { row.hidden = !(i >= start && i < start + size); });
                    summary.innerHTML = 'Showing <strong>' + (start + 1) + '–' + Math.min(start + size, rows.length) + '</strong> of <strong>' + rows.length + '</strong> orders';
                    nav.innerHTML = '';
                    nav.appendChild(button('<svg class="ico" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>', current - 1, { aria: 'Previous page', disabled: current === 1 }));
                    for (var p = 1; p <= pages; p++) { nav.appendChild(button(String(p), p, { current: p === current })); }
                    nav.appendChild(button('<svg class="ico" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>', current + 1, { aria: 'Next page', disabled: current === pages }));
                }
                render(1);
            })();
        </script>
        @endpush
    @endunless
@endsection