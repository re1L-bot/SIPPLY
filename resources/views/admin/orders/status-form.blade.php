@php
    $allowedStatuses = ['preparing', 'out_for_delivery', 'completed', 'cancelled'];
    $availableTransitions = array_values(array_intersect($transitions[$order->order_status] ?? [], $allowedStatuses));
@endphp

@if (!empty($availableTransitions))
<form class="{{ $class ?? 'mt-5 flex flex-wrap items-end gap-3 border-t border-[#f1e8de] pt-4' }}" @if (!empty($pad)) style="padding:24px" @endif method="POST" action="{{ route('admin.orders.update', $order->order_id) }}" data-cancellation-form>
    @csrf @method('PUT')
    <div>
        <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:var(--muted)" for="status-{{ $order->order_id }}">Next order status</label>
        <select class="input" style="width:auto;min-width:170px" id="status-{{ $order->order_id }}" name="order_status">
            @foreach ($availableTransitions as $status)
                <option value="{{ $status }}">
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
    </div>
    <div class="min-w-52 flex-1">
        <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:var(--muted)" for="notes-{{ $order->order_id }}">Status note (optional)</label>
        <input class="input" id="notes-{{ $order->order_id }}" name="notes" maxlength="500">
    </div>
    <input type="hidden" name="cancellation_reason">
    <button type="submit" class="btn btn-primary">Update order</button>
    <dialog class="sip-dialog" data-cancellation-dialog aria-labelledby="cancel-order-title-{{ $order->order_id }}">
        <div style="padding:28px">
            <h2 class="font-display" style="margin:0;font-size:22px;font-weight:700" id="cancel-order-title-{{ $order->order_id }}">Cancel order?</h2>
            <p style="margin:8px 0 0;font-size:14px;color:var(--muted)">Enter a reason. The customer will be able to see it in their order history.</p>
            <label style="display:block;margin-top:16px;font-size:14px;font-weight:600" for="cancel-order-reason-{{ $order->order_id }}">Cancellation reason</label>
            <textarea class="input" style="height:auto;padding:12px 16px;margin-top:8px" id="cancel-order-reason-{{ $order->order_id }}" rows="3" maxlength="500" data-cancellation-reason></textarea>
            <div class="flex justify-end gap-2" style="margin-top:20px">
                <button class="btn btn-soft" type="button" data-dismiss-cancellation>Keep order</button>
                <button class="btn btn-danger" style="background:#b3402f;color:#fff" type="button" data-confirm-cancellation>Confirm cancellation</button>
            </div>
        </div>
    </dialog>
</form>

@once
    @push('styles')
    <style>
        .sip-dialog { width:min(100% - 2rem, 28rem); padding:0; border:1px solid var(--line); border-radius:28px; background:var(--card); color:var(--ink); box-shadow:0 24px 60px rgba(56,32,21,.3); }
        .sip-dialog::backdrop { background:rgba(56,32,21,.5); }
        .sip-dialog[open] { animation:dialog-in .22s ease-out; }
        @keyframes dialog-in { from { opacity:0; transform:translateY(10px) scale(.97); } }
    </style>
    @endpush
    @push('scripts')
    <script>
        {{-- Cancellation dialog behaviour. Remove this block if your old layout already ran this script. --}}
        (function () {
            document.querySelectorAll('[data-cancellation-form]').forEach(function (form) {
                var select = form.querySelector('select[name="order_status"]');
                var dialog = form.querySelector('[data-cancellation-dialog]');
                var hidden = form.querySelector('input[name="cancellation_reason"]');
                var reason = form.querySelector('[data-cancellation-reason]');
                var confirmed = false;
                if (!select || !dialog) { return; }
                form.addEventListener('submit', function (e) {
                    if (select.value !== 'cancelled' || confirmed) { return; }
                    e.preventDefault();
                    dialog.showModal();
                    reason.focus();
                });
                form.querySelector('[data-dismiss-cancellation]').addEventListener('click', function () { dialog.close(); });
                form.querySelector('[data-confirm-cancellation]').addEventListener('click', function () {
                    hidden.value = reason.value.trim();
                    confirmed = true;
                    dialog.close();
                    form.submit();
                });
            });
        })();
    </script>
    @endpush
@endonce
@endif