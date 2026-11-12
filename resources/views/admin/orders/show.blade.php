@extends('admin.layout')

@section('title', 'Order '.$order->order_number)

@section('content')
    @php
        $placed = $order->placed_at ? \Illuminate\Support\Carbon::parse($order->placed_at) : null;
        $statusText = fn ($s) => $s === 'completed' ? 'Delivered' : ($s === 'out_for_delivery' ? 'Out of delivery' : ucfirst(str_replace('_', ' ', $s)));
    @endphp

    <a class="btn btn-soft" style="padding:9px 18px" href="{{ route('admin.orders.index') }}">
        <svg class="ico" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        Back to orders
    </a>

    <div class="flex flex-wrap items-center justify-between gap-3" style="margin-top:20px">
        <div>
            <h1 class="font-display" style="margin:0;font-size:38px;font-weight:700;letter-spacing:-1px">{{ $order->order_number }}</h1>
            <p style="margin:6px 0 0;font-size:14px;color:var(--muted)">{{ $placed ? $placed->format('M j, Y · g:i A') : $order->placed_at }} · {{ $order->email }}</p>
        </div>
        @include('admin.orders._status-badge', ['status' => $order->order_status, 'size' => 'badge-lg'])
    </div>

    @if ($order->order_status === 'cancelled')
        @php $cancellationEntry = $history->firstWhere('status', 'cancelled'); @endphp
        <div style="margin-top:20px;padding:22px 26px;border-radius:24px;background:#ffe3df;color:#9c382a">
            <h2 style="margin:0;font-size:16px;font-weight:700">Order cancelled</h2>
            <p style="margin:6px 0 0;font-size:14px"><strong>Reason:</strong> {{ $cancellationEntry?->notes ?: 'No cancellation reason recorded.' }}</p>
            @if ($cancellationEntry?->first_name)
                <p style="margin:8px 0 0;font-size:12px;opacity:.8">Recorded by {{ $cancellationEntry->first_name }} {{ $cancellationEntry->last_name }} on {{ $cancellationEntry->changed_at }}</p>
            @elseif ($cancellationEntry?->changed_at)
                <p style="margin:8px 0 0;font-size:12px;opacity:.8">Recorded on {{ $cancellationEntry->changed_at }}</p>
            @endif
        </div>
    @endif

    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_360px]" style="margin-top:24px">
        {{-- Left: items & totals --}}
        <section class="card" style="padding:28px">
            <h2 class="font-display" style="margin:0;font-size:22px;font-weight:700">Items ordered</h2>
            <div style="margin-top:12px">
                @foreach ($items as $item)
                    <div class="flex justify-between gap-4" style="padding:14px 0;border-bottom:1px solid var(--line);font-size:14px">
                        <span>{{ $item->quantity }} × {{ $item->product_name }}{{ $item->size_name ? ' · '.$item->size_name : '' }}<span style="display:block;font-size:12px;color:var(--muted)">₱{{ number_format((float) $item->unit_price, 2) }} each</span></span>
                        <span style="font-weight:600;font-variant-numeric:tabular-nums">₱{{ number_format((float) $item->line_total, 2) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="space-y-2" style="margin-top:14px;font-size:14px;color:var(--muted)">
                <p class="flex justify-between" style="margin:0"><span>Subtotal</span><span>₱{{ number_format((float) $order->subtotal, 2) }}</span></p>
                <p class="flex justify-between" style="margin:0"><span>Delivery fee</span><span>₱{{ number_format((float) $order->delivery_fee, 2) }}</span></p>
                <p class="flex justify-between" style="margin:0"><span>Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span><span>−₱{{ number_format((float) $order->discount_amount, 2) }}</span></p>
            </div>
            <p class="flex items-center justify-between" style="margin:18px 0 0;padding:18px 22px;border-radius:20px;background:linear-gradient(135deg,#6b351c,var(--side));color:#fff7ef">
                <span style="font-weight:600">Total</span>
                <span class="font-display" style="font-size:26px;font-weight:700">₱{{ number_format((float) $order->grand_total, 2) }}</span>
            </p>
        </section>

        {{-- Right: delivery, update, history --}}
        <aside class="space-y-5">
            <section class="card" style="padding:24px">
                <h2 class="font-display" style="margin:0;font-size:20px;font-weight:700">Delivery and payment</h2>
                <dl class="space-y-3" style="margin:14px 0 0;font-size:14px">
                    <div><dt style="font-size:12px;color:var(--muted)">Recipient</dt><dd style="margin:0;font-weight:600">{{ $order->recipient_name }} · {{ $order->recipient_phone }}</dd></div>
                    <div><dt style="font-size:12px;color:var(--muted)">Delivery address</dt><dd style="margin:0">{{ $order->delivery_address }}</dd></div>
                    @if ($order->delivery_notes)
                        <div><dt style="font-size:12px;color:var(--muted)">Customer notes</dt><dd style="margin:0">{{ $order->delivery_notes }}</dd></div>
                    @endif
                    <div><dt style="font-size:12px;color:var(--muted)">Payment method</dt><dd style="margin:0">{{ $paymentMethod ?: 'Not recorded' }} · {{ ucfirst($order->payment_status ?? 'unknown') }}</dd></div>
                </dl>
            </section>

            @if (($transitions[$order->order_status] ?? []) !== [])
                @include('admin.orders.status-form', ['order' => $order, 'class' => 'card flex flex-wrap items-end gap-3', 'pad' => true])
            @endif

            <section class="card" style="padding:24px">
                <h2 class="font-display" style="margin:0;font-size:20px;font-weight:700">Status history</h2>
                <ol style="margin:16px 0 0;padding:0;list-style:none">
                    @foreach ($history as $entry)
                        <li class="timeline-item {{ $loop->last ? 'is-latest' : '' }}">
                            <p style="margin:0;font-size:14px;font-weight:600">{{ $statusText($entry->status) }}</p>
                            <p style="margin:2px 0 0;font-size:12px;color:var(--muted)">{{ $entry->changed_at }}{{ $entry->first_name ? ' · '.$entry->first_name.' '.$entry->last_name : '' }}</p>
                            @if ($entry->notes)<p style="margin:6px 0 0;font-size:14px;color:#5b4234">{{ $entry->notes }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </section>
        </aside>
    </div>

    @push('styles')
    <style>
        .badge-lg { padding:8px 18px; font-size:14px; }
        .timeline-item { position:relative; padding:0 0 18px 24px; border-left:2px solid var(--beige); margin-left:5px; }
        .timeline-item:last-child { padding-bottom:0; border-left-color:transparent; }
        .timeline-item::before { content:""; position:absolute; left:-7px; top:3px; width:12px; height:12px; border-radius:99px; background:var(--caramel); border:2px solid var(--card); }
        .timeline-item.is-latest::before { background:var(--side); animation:live-pulse 1.8s ease-out infinite; }
    </style>
    @endpush
@endsection