@extends('admin.layout')

@section('title', 'Inventory')

@section('content')
    @php
        $rows = collect($products->items());
        $barMax = max((int) $rows->max('stock_quantity'), 1);
        $lowCount = $rows->filter(fn ($p) => $p->stock_quantity <= 5)->count();
    @endphp

    @push('styles')
    <style>
        .inv-row td { transition:background-color .2s ease, transform .2s ease; }
        .inv-row td:first-child { border-radius:16px 0 0 16px; position:relative; }
        .inv-row td:last-child { border-radius:0 16px 16px 0; }
        .inv-row:hover td { background:rgba(245,226,209,.6); }
        .inv-row td:first-child::before { content:""; position:absolute; left:0; top:20%; bottom:20%; width:4px; border-radius:4px; background:var(--caramel); transform:scaleY(0); transition:transform .25s ease; }
        .inv-row:hover td:first-child::before { transform:scaleY(1); }
        .inv-row:hover .stock-fill { filter:brightness(1.1); }
        .inv-row:hover .stock-fill::after { animation:shimmer 1.1s ease-in-out; }

        .stock-track { position:relative; width:130px; height:8px; margin-top:8px; border-radius:99px; background:var(--beige); overflow:hidden; }
        .stock-fill { position:relative; height:100%; border-radius:99px; background:linear-gradient(90deg,var(--caramel),var(--accent)); transform-origin:left; animation:stock-grow .9s cubic-bezier(.2,.7,.2,1) backwards; animation-delay:calc(var(--i) * 70ms); }
        .stock-fill.is-low { background:linear-gradient(90deg,#e8776a,#a4473d); }
        .stock-fill::after { content:""; position:absolute; inset:0; background:linear-gradient(100deg,transparent 30%,rgba(255,255,255,.55) 50%,transparent 70%); transform:translateX(-100%); }
        @keyframes stock-grow { from { transform:scaleX(0); } }
        @keyframes shimmer { to { transform:translateX(100%); } }

        .low-badge { display:inline-flex; align-items:center; gap:7px; margin-left:10px; padding:4px 11px; border-radius:99px; background:#ffe3df; color:#a4473d; font-size:12px; font-weight:700; vertical-align:middle; }
        .low-dot { width:7px; height:7px; border-radius:99px; background:currentColor; animation:low-pulse 1.5s ease-out infinite; }
        @keyframes low-pulse { 0% { box-shadow:0 0 0 0 rgba(164,71,61,.55); } 70%,100% { box-shadow:0 0 0 9px rgba(164,71,61,0); } }
        .stock-num { font-size:20px; font-weight:700; font-variant-numeric:tabular-nums; }
        .stock-num.is-low { color:#a4473d; }

        .save-form { display:flex; align-items:center; gap:10px; }
        .qty { width:96px; height:42px; padding:0 12px; text-align:center; font-weight:700; transition:box-shadow .2s ease, border-color .2s ease, transform .2s ease; }
        .qty:hover { border-color:var(--caramel); }
        .qty:focus { transform:translateY(-1px); }
        .save-btn { gap:0; padding:10px 20px; overflow:hidden; }
        .save-btn svg { width:0; opacity:0; transition:width .22s ease, opacity .22s ease, margin .22s ease; }
        .save-btn:hover { box-shadow:0 8px 18px rgba(84,40,18,.28); transform:translateY(-2px); }
        .save-btn:hover svg { width:16px; opacity:1; margin-right:6px; }
        .save-btn:active { transform:translateY(0) scale(.97); }
        .save-btn[disabled] { opacity:.7; pointer-events:none; }
        .inv-pill { display:inline-flex; align-items:center; gap:8px; padding:8px 16px; border-radius:99px; font-size:13px; font-weight:700; }
    </style>
    @endpush

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display" style="margin:0;font-size:38px;font-weight:700;letter-spacing:-1px">Inventory</h1>
            <p style="margin:6px 0 0;font-size:14px;color:var(--muted)">Products with five or fewer units are flagged as low stock.</p>
        </div>
        @if ($lowCount > 0)
            <span class="inv-pill" style="background:#ffe3df;color:#a4473d"><span class="low-dot"></span>{{ $lowCount }} low on this page</span>
        @else
            <span class="inv-pill badge-ok">All stocked on this page</span>
        @endif
    </div>

    <section class="card" style="margin-top:24px;padding:24px">
        <div class="table-wrap">
            <table class="table" style="min-width:650px">
                <thead><tr><th>Product</th><th>Category</th><th>Current stock</th><th>Update stock</th></tr></thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $low = $product->stock_quantity <= 5;
                            $pct = max(4, min(100, round($product->stock_quantity / $barMax * 100)));
                        @endphp
                        <tr class="inv-row" style="--i:{{ $loop->index }}">
                            <td style="font-weight:700">{{ $product->product_name }}</td>
                            <td><span class="badge badge-neutral">{{ $product->category_name }}</span></td>
                            <td>
                                <span class="stock-num {{ $low ? 'is-low' : '' }}">{{ $product->stock_quantity }}</span>@if ($low)<span class="low-badge"><span class="low-dot"></span>Low stock</span>@endif
                                <div class="stock-track" aria-hidden="true"><div class="stock-fill {{ $low ? 'is-low' : '' }}" style="width:{{ $pct }}%"></div></div>
                            </td>
                            <td>
                                <form class="save-form" method="POST" action="{{ route('admin.inventory.update', $product->product_id) }}" data-save-form>
                                    @csrf @method('PUT')
                                    <input class="input qty" type="number" name="stock_quantity" min="0" max="4294967295" value="{{ $product->stock_quantity }}" required aria-label="Stock for {{ $product->product_name }}">
                                    <button class="btn btn-primary save-btn" type="submit">
                                        <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg><span>Save</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="padding:48px 0;text-align:center;color:var(--muted)">No products yet. Add a product to start tracking stock.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:16px">{{ $products->links() }}</div>
    </section>

    @push('scripts')
    <script>
        // Show "Saving…" once a stock update is submitted (the form still posts normally).
        document.querySelectorAll('[data-save-form]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var b = form.querySelector('.save-btn');
                b.querySelector('span').textContent = 'Saving…';
                setTimeout(function () { b.disabled = true; }, 0);
            });
        });
    </script>
    @endpush
@endsection