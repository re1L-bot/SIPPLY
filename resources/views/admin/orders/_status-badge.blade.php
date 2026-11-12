{{-- resources/views/admin/orders/_status-badge.blade.php  |  usage: @include('admin.orders._status-badge', ['status' => $order->order_status]) --}}
@php
    $badge = match ($status) {
        'cancelled' => 'badge-bad',
        'completed' => 'badge-ok',
        'out_for_delivery', 'preparing', 'pending' => 'badge-warn',
        default => 'badge-neutral',
    };
    $label = match ($status) {
        'completed' => 'Delivered',
        'out_for_delivery' => 'Out of delivery',
        default => ucfirst(str_replace('_', ' ', $status)),
    };
    $live = in_array($status, ['pending', 'preparing', 'out_for_delivery'], true);
@endphp
@once
    @push('styles')
    <style>
        .live-dot { display:inline-block; width:7px; height:7px; margin-right:7px; border-radius:99px; background:currentColor; vertical-align:middle; animation:live-pulse 1.8s ease-out infinite; }
        @keyframes live-pulse { 0% { box-shadow:0 0 0 0 rgba(143,91,23,.5); } 70%,100% { box-shadow:0 0 0 8px rgba(143,91,23,0); } }
    </style>
    @endpush
@endonce
<span class="badge {{ $badge }} {{ $size ?? '' }}">@if ($live)<span class="live-dot" aria-hidden="true"></span>@endif{{ $label }}</span>
