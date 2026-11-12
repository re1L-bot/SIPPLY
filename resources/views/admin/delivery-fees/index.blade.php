@extends('admin.layout')

@section('title', 'Delivery fees')

@section('content')
    @push('styles')
    <style>
        .fees { max-width:900px; margin-inline:auto; }
        .fees .field-label { display:block; margin-bottom:6px; font-size:12px; font-weight:700; color:var(--muted); }
        .fees .err { margin:4px 0 0; font-size:12px; color:#a4473d; }
        .fees .add-grid { display:grid; gap:16px; grid-template-columns:1fr; }
        @media (min-width:640px) { .fees .add-grid { grid-template-columns:1fr 1fr auto; align-items:end; } }

        .alert { display:flex; align-items:flex-start; gap:12px; margin-top:20px; padding:14px 20px; border-radius:18px; font-size:14px; animation:alert-in .45s cubic-bezier(.2,.8,.2,1); }
        .alert-ok { background:#e5f3e3; color:#2f6a45; font-weight:600; }
        .alert-err { background:#ffe3df; color:#a4473d; }
        .alert-err ul { margin:8px 0 0; padding-left:18px; list-style:disc; }
        @keyframes alert-in { from { opacity:0; transform:translateY(-10px); } }

        .fee-row { animation:row-in .5s cubic-bezier(.2,.7,.2,1) backwards; animation-delay:calc(var(--i) * 55ms); }
        @keyframes row-in { from { opacity:0; transform:translateY(10px); } }
        .fee-row td { transition:background-color .2s ease; }
        .fee-row td:first-child { border-radius:16px 0 0 16px; position:relative; }
        .fee-row td:last-child { border-radius:0 16px 16px 0; }
        .fee-row:hover td, .fee-row:focus-within td { background:rgba(245,226,209,.6); }
        .fee-row td:first-child::before { content:""; position:absolute; left:0; top:20%; bottom:20%; width:4px; border-radius:4px; background:var(--caramel); transform:scaleY(0); transition:transform .25s ease; }
        .fee-row:hover td:first-child::before, .fee-row:focus-within td:first-child::before { transform:scaleY(1); }

        .pin { display:grid; place-items:center; width:40px; height:40px; border-radius:99px; background:var(--beige); color:var(--accent); transition:transform .3s cubic-bezier(.3,1.6,.5,1), background-color .2s ease, color .2s ease; }
        .fee-row:hover .pin, .fee-row:focus-within .pin { transform:translateY(-4px) scale(1.08); background:var(--accent); color:#fff7ef; }
        .fee-name { font-weight:700; font-size:14px; cursor:pointer; }

        .fee-input-wrap { display:inline-flex; align-items:center; gap:10px; }
        .fee-input { width:150px; height:44px; text-align:right; font-weight:700; font-variant-numeric:tabular-nums; transition:border-color .2s ease, box-shadow .2s ease, transform .2s ease, background-color .2s ease; }
        .fee-input:hover { border-color:var(--caramel); }
        .fee-input:focus { transform:translateY(-1px); }
        .fee-row.is-dirty .fee-input { border-color:var(--caramel); background:#fff8f0; }
        .edited-tag { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:700; color:#8f5b17; opacity:0; transform:translateX(8px); pointer-events:none; transition:opacity .2s ease, transform .2s ease; }
        .fee-row.is-dirty .edited-tag { opacity:1; transform:none; }
        .edited-dot { width:7px; height:7px; border-radius:99px; background:var(--caramel); animation:dirty-pulse 1.6s ease-out infinite; }
        @keyframes dirty-pulse { 0% { box-shadow:0 0 0 0 rgba(217,154,105,.7); } 70%,100% { box-shadow:0 0 0 9px rgba(217,154,105,0); } }

        .save-all { transition:transform .2s ease, box-shadow .2s ease, background-color .2s ease; }
        .save-all:hover { box-shadow:0 8px 18px rgba(84,40,18,.28); }
        .save-all.is-dirty { animation:btn-glow 2s ease-in-out infinite; }
        @keyframes btn-glow { 0%,100% { box-shadow:0 0 0 0 rgba(117,69,45,.45); } 50% { box-shadow:0 0 0 10px rgba(117,69,45,0); } }
        .add-btn:hover { box-shadow:0 8px 18px rgba(84,40,18,.28); }
        .add-btn svg { transition:transform .3s ease; }
        .add-btn:hover svg { transform:rotate(90deg); }
        .sr { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); white-space:nowrap; }

        .del-btn { display:grid; place-items:center; width:36px; height:36px; border-radius:99px; border:none; background:transparent; color:var(--muted); cursor:pointer; transition:background-color .2s ease, color .2s ease, transform .2s ease; }
        .del-btn:hover { background:#ffe3df; color:#a4473d; transform:scale(1.12); }
        .del-btn:focus-visible { outline:2px solid #a4473d; outline-offset:2px; }
    </style>
    @endpush

    <div class="fees">
        <h1 class="font-display" style="margin:0;font-size:38px;font-weight:700;letter-spacing:-1px">Delivery fees</h1>
        <p style="margin:6px 0 0;font-size:14px;color:var(--muted)">Add service areas and set the delivery fee customers pay for each municipality.</p>

        @if (session('status'))
            <div class="alert alert-ok" role="status">
                <svg class="ico" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-err" role="alert">
                <svg class="ico" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                <div>
                    <p style="margin:0;font-weight:700">Please review the municipality and delivery fee details.</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Add municipality --}}
        <section class="card" style="margin-top:24px;padding:28px" aria-labelledby="add-municipality-title">
            <h2 class="font-display" style="margin:0 0 18px;font-size:24px;font-weight:700" id="add-municipality-title">Add municipality</h2>
            <form class="add-grid" method="POST" action="{{ route('admin.delivery-fees.store') }}">
                @csrf
                <div>
                    <label class="field-label" for="new_municipality">Municipality</label>
                    <input class="input" id="new_municipality" name="municipality" value="{{ old('municipality') }}" maxlength="50" required>
                    @error('municipality') <p class="err">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="new_municipality_fee">Delivery fee (₱)</label>
                    <input class="input" id="new_municipality_fee" name="fee" type="number" value="{{ old('fee') }}" min="0" max="999999.99" step="0.01" required>
                    @error('fee') <p class="err">{{ $message }}</p> @enderror
                </div>
                <button class="btn btn-primary add-btn" type="submit">
                    <svg class="ico" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    Add municipality
                </button>
            </form>
        </section>

        {{-- Current fees --}}
        <section class="card" style="margin-top:24px;padding:28px" aria-labelledby="current-fees-title">
            <h2 class="font-display" style="margin:0 0 18px;font-size:24px;font-weight:700" id="current-fees-title">Current fees</h2>
            <form method="POST" action="{{ route('admin.delivery-fees.update') }}" data-fees-form>
                @csrf
                @method('PUT')
                <div class="table-wrap">
                    <table class="table" style="min-width:520px">
                        <thead>
                            <tr><th>Municipality</th><th class="num">Delivery fee (₱)</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($fees as $fee)
                                @php $value = old('fees.'.$fee->delivery_fee_id, $fee->fee); @endphp
                                <tr class="fee-row" style="--i:{{ $loop->index }}">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <span class="pin"><svg class="ico" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></span>
                                            <label class="fee-name" for="fee-{{ $fee->delivery_fee_id }}">{{ $fee->municipality }}<span class="sr"> delivery fee (₱)</span></label>
                                        </div>
                                    </td>
                                    <td class="num">
                                        <div class="fee-input-wrap">
                                            <span class="edited-tag" aria-hidden="true"><span class="edited-dot"></span>Edited</span>
                                            <input class="input fee-input" id="fee-{{ $fee->delivery_fee_id }}" name="fees[{{ $fee->delivery_fee_id }}]" type="number" value="{{ $value }}" data-original="{{ $fee->fee }}" min="0" max="999999.99" step="0.01" required>
                                        </div>
                                        @error('fees.'.$fee->delivery_fee_id) <p class="err">{{ $message }}</p> @enderror
                                    </td>
                                    <td style="width:56px;text-align:center">
                                        <button class="del-btn" type="button"
                                            title="Remove {{ $fee->municipality }}"
                                            aria-label="Remove {{ $fee->municipality }}"
                                            data-del-name="{{ $fee->municipality }}"
                                            data-del-action="{{ route('admin.delivery-fees.destroy', $fee->delivery_fee_id) }}">
                                            <svg class="ico" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="padding:40px 18px;text-align:center;color:var(--muted)">No municipalities yet. Add your first service area above.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if (count($fees))
                    <div style="display:flex;justify-content:flex-end;margin-top:18px">
                        <button class="btn btn-primary save-all" type="submit" data-save-all>Save delivery fees</button>
                    </div>
                @endif
            </form>
        </section>

        {{-- Hidden delete form (reused for all rows) --}}
        <form id="del-fee-form" method="POST" style="display:none">
            @csrf
            @method('DELETE')
        </form>
    </div>

    @push('scripts')
    <script>
        // Highlights edited fees and shows how many changes are waiting to be saved.
        (function () {
            var form = document.querySelector('[data-fees-form]');
            var btn = document.querySelector('[data-save-all]');
            if (!form || !btn) { return; }
            var inputs = form.querySelectorAll('.fee-input');
            function update() {
                var n = 0;
                inputs.forEach(function (i) {
                    var dirty = parseFloat(i.value) !== parseFloat(i.getAttribute('data-original'));
                    i.closest('.fee-row').classList.toggle('is-dirty', dirty);
                    if (dirty) { n++; }
                });
                btn.classList.toggle('is-dirty', n > 0);
                btn.textContent = n > 0 ? 'Save ' + n + (n === 1 ? ' change' : ' changes') : 'Save delivery fees';
            }
            inputs.forEach(function (i) { i.addEventListener('input', update); });
            update();
        })();

        // Delete confirmation using the shared hidden form.
        (function () {
            var delForm = document.getElementById('del-fee-form');
            document.querySelectorAll('.del-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var name = btn.getAttribute('data-del-name');
                    if (!confirm('Remove "' + name + '"? This cannot be undone.')) { return; }
                    delForm.action = btn.getAttribute('data-del-action');
                    delForm.submit();
                });
            });
        })();
    </script>
    @endpush
@endsection
