@extends('admin.layout')

@section('title', 'Customers')

@section('content')
    @php
        $rows = collect($customers->items());
        $valueMax = max((float) $rows->max('completed_total'), 1);
    @endphp

    @push('styles')
    <style>
        .cust-search { position:relative; display:flex; align-items:center; gap:8px; }
        .cust-search .input { width:240px; padding-left:44px; transition:width .3s ease, box-shadow .2s ease, border-color .2s ease; }
        .cust-search .input:focus { width:320px; }
        .cust-search .s-ico { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--muted); pointer-events:none; transition:color .2s ease; }
        .cust-search:focus-within .s-ico { color:var(--accent); }
        @media (max-width:520px) { .cust-search .input, .cust-search .input:focus { width:100%; } .cust-search { width:100%; } }

        .cust-row { animation:row-in .5s cubic-bezier(.2,.7,.2,1) backwards; animation-delay:calc(var(--i) * 60ms); }
        @keyframes row-in { from { opacity:0; transform:translateY(10px); } }
        .cust-row td { transition:background-color .2s ease; }
        .cust-row td:first-child { border-radius:16px 0 0 16px; position:relative; }
        .cust-row td:last-child { border-radius:0 16px 16px 0; }
        .cust-row:hover td { background:rgba(245,226,209,.6); }
        .cust-row td:first-child::before { content:""; position:absolute; left:0; top:20%; bottom:20%; width:4px; border-radius:4px; background:var(--caramel); transform:scaleY(0); transition:transform .25s ease; }
        .cust-row:hover td:first-child::before { transform:scaleY(1); }

        .c-avatar { position:relative; display:grid; place-items:center; width:42px; height:42px; border-radius:99px; background:linear-gradient(135deg,var(--caramel),var(--accent)); color:#fff7ef; font-size:14px; font-weight:700; transition:transform .3s cubic-bezier(.3,1.6,.5,1), box-shadow .3s ease; }
        .cust-row:hover .c-avatar { transform:scale(1.12) rotate(-6deg); box-shadow:0 8px 18px rgba(84,40,18,.25); }
        .c-live { position:absolute; right:-1px; bottom:-1px; width:12px; height:12px; border-radius:99px; background:#3f9b5f; border:2px solid var(--card); animation:c-pulse 1.8s ease-out infinite; }
        @keyframes c-pulse { 0% { box-shadow:0 0 0 0 rgba(63,155,95,.55); } 70%,100% { box-shadow:0 0 0 9px rgba(63,155,95,0); } }

        .copy-btn { display:inline-grid; place-items:center; width:28px; height:28px; margin-left:6px; border:0; border-radius:99px; background:var(--beige); color:var(--accent); cursor:pointer; opacity:0; transform:translateX(-6px) scale(.8); transition:opacity .2s ease, transform .2s ease, background-color .2s ease; vertical-align:middle; }
        .cust-row:hover .copy-btn, .copy-btn:focus-visible { opacity:1; transform:none; }
        .copy-btn:hover { background:var(--caramel); color:#fff; }
        .copy-btn.is-copied { background:#3f9b5f; color:#fff; opacity:1; transform:none; }
        @media (hover:none) { .copy-btn { opacity:1; transform:none; } }

        .val-track { width:110px; height:6px; margin-top:7px; border-radius:99px; background:var(--beige); overflow:hidden; }
        .val-fill { height:100%; border-radius:99px; background:linear-gradient(90deg,var(--caramel),var(--accent)); transform-origin:left; animation:val-grow .9s cubic-bezier(.2,.7,.2,1) backwards; animation-delay:calc(var(--i) * 60ms + 250ms); }
        @keyframes val-grow { from { transform:scaleX(0); } }
        .orders-chip { display:inline-grid; place-items:center; min-width:34px; height:34px; padding:0 10px; border-radius:99px; background:var(--beige); color:var(--accent); font-weight:700; transition:background-color .2s ease, color .2s ease, transform .2s ease; }
        .cust-row:hover .orders-chip { background:var(--accent); color:#fff7ef; transform:scale(1.08); }
    </style>
    @endpush

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display" style="margin:0;font-size:38px;font-weight:700;letter-spacing:-1px">Customers</h1>
            <p style="margin:6px 0 0;font-size:14px;color:var(--muted)">A summary of everyone who has ordered from SipPly.</p>
        </div>
        <form class="cust-search" method="GET">
            <label class="sr-only" for="customer-search">Search customers</label>
            <svg class="ico s-ico" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input class="input" id="customer-search" name="search" value="{{ $search }}" placeholder="Name or email">
            <button class="btn btn-primary">Search</button>
        </form>
    </div>

    <section class="card" style="margin-top:24px;padding:24px">
        <div class="table-wrap">
            <table class="table" style="min-width:860px">
                <thead><tr><th>Customer Name</th><th>Email</th><th>Contact Number</th><th class="num">Total Orders</th><th class="num">Total Completed Value</th><th>Latest Order</th></tr></thead>
                <tbody>
                    @forelse ($customers as $customer)
                        @php
                            $name = trim((string) $customer->recipient_name);
                            $initials = collect(preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
                            $last = $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at) : null;
                            $recent = $last && $last->greaterThanOrEqualTo(now()->subDays(7));
                            $pct = max(4, min(100, round((float) $customer->completed_total / $valueMax * 100)));
                        @endphp
                        <tr class="cust-row" style="--i:{{ $loop->index }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="c-avatar" @if ($recent) title="Ordered in the last 7 days" @endif>{{ $initials }}@if ($recent)<span class="c-live"></span>@endif</span>
                                    <span style="font-weight:700">{{ $customer->recipient_name }}</span>
                                </div>
                            </td>
                            <td style="white-space:nowrap">{{ $customer->email }}<button type="button" class="copy-btn" data-copy="{{ $customer->email }}" aria-label="Copy email for {{ $customer->recipient_name }}"><svg class="ico" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg></button></td>
                            <td style="white-space:nowrap">{{ $customer->phone_number ?: '—' }}</td>
                            <td class="num"><span class="orders-chip">{{ $customer->order_count }}</span></td>
                            <td class="num">
                                <span style="font-weight:700">₱{{ number_format((float) $customer->completed_total, 2) }}</span>
                                <div class="val-track" style="margin-left:auto" aria-hidden="true"><div class="val-fill" style="width:{{ $pct }}%"></div></div>
                            </td>
                            <td style="white-space:nowrap;color:var(--muted)">
                                {{ $last ? $last->format('M j, Y · g:i A') : $customer->last_order_at }}
                                @if ($last)<span style="display:block;font-size:12px;color:{{ $recent ? '#2f6a45' : 'var(--muted)' }};font-weight:{{ $recent ? 700 : 500 }}">{{ $last->diffForHumans() }}</span>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="padding:48px 0;text-align:center;color:var(--muted)">No customers found in order history. Try a different name or email.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:16px">{{ $customers->links() }}</div>
    </section>

    @push('scripts')
    <script>
        // Copy-email buttons
        document.querySelectorAll('[data-copy]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var done = function () {
                    btn.classList.add('is-copied');
                    var old = btn.innerHTML;
                    btn.innerHTML = '<svg class="ico" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
                    setTimeout(function () { btn.classList.remove('is-copied'); btn.innerHTML = old; }, 1400);
                };
                if (navigator.clipboard) { navigator.clipboard.writeText(btn.getAttribute('data-copy')).then(done); }
            });
        });
    </script>
    @endpush
@endsection 