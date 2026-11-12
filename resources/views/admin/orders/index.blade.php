@extends('admin.layout')

@section('title', 'Orders')

@section('content')
    @php $search = trim((string) request('search')); @endphp

    @push('styles')
    <style>
        .toolbar { display:flex; flex-wrap:wrap; align-items:center; gap:12px; padding:14px; margin-top:24px; }
        .ord-search { position:relative; flex:1 1 320px; display:flex; align-items:center; }
        .ord-search .s-ico { position:absolute; left:16px; color:var(--muted); pointer-events:none; transition:color .2s ease, transform .3s ease; }
        .ord-search:focus-within .s-ico { color:var(--accent); transform:scale(1.15); }
        .ord-search .input { padding-left:46px; padding-right:62px; transition:box-shadow .2s ease, border-color .2s ease, transform .2s ease; }
        .ord-search .input:hover { border-color:var(--caramel); }
        .ord-search .input:focus { transform:translateY(-1px); }
        .ord-search kbd { position:absolute; right:14px; padding:2px 9px; border-radius:8px; background:var(--beige); color:var(--accent); font:700 12px ui-sans-serif,system-ui; transition:opacity .2s ease; }
        .ord-search .input:focus ~ kbd, .ord-search .input:not(:placeholder-shown) ~ kbd { opacity:0; }
        .clear-btn { position:absolute; right:10px; display:none; place-items:center; width:30px; height:30px; border:0; border-radius:99px; background:var(--beige); color:var(--accent); cursor:pointer; transition:transform .2s ease, background-color .2s ease; }
        .clear-btn:hover { background:var(--caramel); color:#fff; transform:rotate(90deg); }
        .ord-search .input:not(:placeholder-shown) ~ .clear-btn { display:grid; }

        .result-line { display:flex; align-items:center; gap:10px; margin:16px 4px 0; font-size:13px; color:var(--muted); }
        .live-chip { display:inline-flex; align-items:center; gap:8px; padding:5px 12px; border-radius:99px; background:#e5f3e3; color:#2f6a45; font-weight:700; }
        .live-chip i { width:7px; height:7px; border-radius:99px; background:currentColor; animation:chip-pulse 1.8s ease-out infinite; }
        @keyframes chip-pulse { 0% { box-shadow:0 0 0 0 rgba(63,155,95,.55); } 70%,100% { box-shadow:0 0 0 8px rgba(63,155,95,0); } }

        .order-card { position:relative; overflow:hidden; padding:0; transition:transform .25s ease, box-shadow .25s ease; animation:card-in .55s cubic-bezier(.2,.7,.2,1) backwards; animation-delay:calc(var(--i) * 70ms); }
        @keyframes card-in { from { opacity:0; transform:translateY(14px); } }
        .order-card::before { content:""; position:absolute; left:0; top:18px; bottom:18px; width:4px; border-radius:0 4px 4px 0; background:var(--caramel); transform:scaleY(0); transition:transform .3s ease; }
        .order-card:hover, .order-card:focus-within { transform:translateY(-3px); box-shadow:0 2px 4px rgba(84,40,18,.06), 0 16px 36px rgba(84,40,18,.14); }
        .order-card:hover::before, .order-card:focus-within::before { transform:scaleY(1); }
        .order-card[hidden] { display:none; }
        .oc-head { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; padding:20px 26px; border-bottom:1px solid var(--line); background:linear-gradient(90deg,rgba(245,226,209,.55),rgba(245,226,209,0)); }
        .oc-body { display:grid; gap:22px; padding:22px 26px; }
        @media (min-width:768px) { .oc-body { grid-template-columns:minmax(0,.8fr) minmax(0,1.4fr); } }
        .oc-foot { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; padding:16px 26px; background:rgba(245,226,209,.35); }
        .oc-extra { padding:0 26px 22px; }
        .oc-extra form { margin-top:0 !important; }
        .order-link { display:inline-flex; align-items:center; gap:8px; font-size:18px; font-weight:700; color:var(--accent); text-decoration:none; }
        .order-link svg { width:0; opacity:0; transition:width .25s ease, opacity .25s ease; }
        .order-card:hover .order-link svg, .order-link:focus-visible svg { width:16px; opacity:1; }
        .order-link:hover { color:#c75f26; }
        .item-row { padding:7px 10px; margin:0 -10px; border-radius:12px; transition:background-color .15s ease, transform .15s ease; }
        .item-row:hover { background:var(--beige); transform:translateX(4px); }
        .oc-total { transition:transform .25s ease; transform-origin:right center; }
        .order-card:hover .oc-total { transform:scale(1.08); }
        .oc-label { margin:0 0 6px; font-size:12px; font-weight:700; color:var(--muted); }
        .no-match { display:none; padding:48px; text-align:center; font-size:14px; color:var(--muted); animation:card-in .4s ease; }
        .no-match.is-on { display:block; }
        mark.hit { background:#f8d9b8; color:inherit; border-radius:4px; padding:0 2px; }
    </style>
    @endpush

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display" style="margin:0;font-size:38px;font-weight:700;letter-spacing:-1px">Orders</h1>
            <p style="margin:6px 0 0;font-size:14px;color:var(--muted)">Review and update customer orders.</p>
        </div>
        <span class="live-chip"><i></i>Live orders</span>
    </div>

    {{-- Search + filter --}}
    <form method="GET" class="card toolbar" role="search" data-order-search-form>
        <div class="ord-search">
            <svg class="ico s-ico" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <label class="sr-only" for="order-search">Search by order ID or customer name</label>
            <input class="input" id="order-search" name="search" value="{{ $search }}" placeholder="Search order ID or customer name" autocomplete="off" data-order-search>
            <kbd aria-hidden="true">/</kbd>
            <button type="button" class="clear-btn" aria-label="Clear search" data-clear-search><svg class="ico" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
        </div>
        <label class="sr-only" for="order-status-filter">Filter by status</label>
        <select class="input" style="width:auto;min-width:190px" id="order-status-filter" name="status" data-auto-submit>
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected($selectedStatus === $status)>
                    @if ($status === 'completed')
                        Delivered
                    @elseif ($status === 'out_for_delivery')
                        Out of delivery
                    @elseif ($status === 'cancelled')
                        Cancel order
                    @else
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    @endif
                </option>
            @endforeach
        </select>
        <button class="btn btn-primary">Search</button>
    </form>

    @if ($orders->count())
        <p class="result-line" aria-live="polite">
            <span data-result-count>Showing {{ $orders->count() }} {{ \Illuminate\Support\Str::plural('order', $orders->count()) }} on this page</span>
            @if ($search !== '')<span class="badge badge-neutral">“{{ $search }}”</span>@endif
        </p>

        <div class="grid gap-4" style="margin-top:14px" data-order-list>
            @foreach ($orders as $order)
                @php
                    $placed = $order->placed_at ? \Illuminate\Support\Carbon::parse($order->placed_at) : null;
                    $customerName = trim($order->first_name.' '.$order->last_name);
                    $haystack = mb_strtolower($order->order_number.' '.$customerName.' '.$order->email);
                @endphp
                <article class="card order-card" style="--i:{{ min($loop->index, 8) }}" data-order-card data-haystack="{{ $haystack }}">
                    <div class="oc-head">
                        <div>
                            <h2 style="margin:0"><a class="order-link" href="{{ route('admin.orders.show', $order->order_id) }}"><span data-hl>{{ $order->order_number }}</span><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a></h2>
                            <p style="margin:4px 0 0;font-size:12px;color:var(--muted)">{{ $placed ? $placed->format('M j, Y · g:i A') : $order->placed_at }}</p>
                        </div>
                        @include('admin.orders._status-badge', ['status' => $order->order_status])
                    </div>

                    <div class="oc-body">
                        {{-- Customer --}}
                        <div class="min-w-0">
                            <p class="oc-label">Customer</p>
                            <p style="margin:0;font-size:15px;font-weight:700"><span data-hl>{{ $customerName }}</span></p>
                            <p class="truncate" style="margin:2px 0 0;font-size:12px;color:var(--muted)">{{ $order->email }}</p>
                        </div>

                        {{-- Items & address --}}
                        <div class="min-w-0">
                            <p class="oc-label">Items</p>
                            <ul style="margin:0;padding:0;list-style:none;font-size:14px">
                                @foreach ($items->get($order->order_id, collect()) as $item)
                                    <li class="item-row flex justify-between gap-4"><span class="min-w-0">{{ $item->quantity }} × {{ $item->product_name }}</span><span class="shrink-0" style="color:var(--muted);font-variant-numeric:tabular-nums">₱{{ number_format((float) $item->line_total, 2) }}</span></li>
                                @endforeach
                            </ul>
                            <p style="margin:12px 0 0;font-size:12px;color:var(--muted)">{{ $order->delivery_address }}</p>
                        </div>
                    </div>

                    <div class="oc-foot">
                        <span style="font-size:13px;color:var(--muted)">Payment: <strong style="color:var(--ink)">{{ ucfirst($order->payment_status ?? 'unknown') }}</strong></span>
                        <p class="font-display oc-total" style="margin:0;font-size:26px;font-weight:700">₱{{ number_format((float) $order->grand_total, 2) }}</p>
                    </div>

                    @if ($order->order_status === 'cancelled' && isset($cancellationReasons) && $cancellationReasons->has($order->order_id) && filled($cancellationReasons->get($order->order_id)->notes))
                        <div class="oc-extra" style="padding-top:18px"><p style="margin:0;padding:10px 14px;border-radius:14px;background:#ffe3df;color:#9c382a;font-size:12px"><strong>Cancellation reason:</strong> {{ $cancellationReasons->get($order->order_id)->notes }}</p></div>
                    @endif

                    @if (($transitions[$order->order_status] ?? []) !== [])
                        <div class="oc-extra" style="padding-top:18px">@include('admin.orders.status-form', ['order' => $order])</div>
                    @endif
                </article>
            @endforeach
            <p class="card no-match" data-no-match>No orders on this page match your search. Press Enter to search all orders.</p>
        </div>
    @else
        <p class="card" style="margin-top:24px;padding:48px;text-align:center;font-size:14px;color:var(--muted)">No orders match{{ $search !== '' ? ' “'.$search.'”' : ' this filter' }}. Check the order ID or name, or clear the filters.</p>
    @endif

    <div style="margin-top:20px">{{ $orders->links() }}</div>

    @push('scripts')
    <script>
        (function () {
            var input = document.querySelector('[data-order-search]');
            var cards = Array.prototype.slice.call(document.querySelectorAll('[data-order-card]'));
            var counter = document.querySelector('[data-result-count]');
            var none = document.querySelector('[data-no-match]');
            var total = cards.length;

            // Status dropdown applies right away
            var sel = document.querySelector('[data-auto-submit]');
            if (sel) { sel.addEventListener('change', function () { sel.form.submit(); }); }

            if (!input) { return; }

            function esc(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
            function highlight(card, q) {
                card.querySelectorAll('[data-hl]').forEach(function (el) {
                    if (el.dataset.raw === undefined) { el.dataset.raw = el.textContent; }
                    var raw = el.dataset.raw;
                    if (!q) { el.textContent = raw; return; }
                    var re = new RegExp('(' + esc(q) + ')', 'ig');
                    el.innerHTML = raw.replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }).replace(new RegExp('(' + esc(q.replace(/[&<>"]/g, '')) + ')', 'ig'), '<mark class="hit">$1</mark>');
                });
            }
            function run() {
                var q = input.value.trim().toLowerCase();
                var shown = 0;
                cards.forEach(function (c) {
                    var match = !q || c.getAttribute('data-haystack').indexOf(q) !== -1;
                    c.hidden = !match;
                    if (match) { shown++; }
                    highlight(c, q);
                });
                if (counter) {
                    counter.textContent = q ? 'Showing ' + shown + ' of ' + total + ' on this page' : 'Showing ' + total + ' ' + (total === 1 ? 'order' : 'orders') + ' on this page';
                }
                if (none) { none.classList.toggle('is-on', shown === 0 && total > 0); }
            }
            input.addEventListener('input', run);
            document.querySelector('[data-clear-search]').addEventListener('click', function () { input.value = ''; run(); input.focus(); });
            document.addEventListener('keydown', function (e) {
                var tag = (document.activeElement && document.activeElement.tagName) || '';
                if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(tag)) { e.preventDefault(); input.focus(); input.select(); }
            });
            run();
        })();
    </script>
    @endpush
@endsection