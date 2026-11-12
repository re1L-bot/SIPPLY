<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order confirmation {{ $order->order_number }}</title>
</head>
<body style="margin:0;background:#f7f7f1;color:#20352c;font-family:Arial,sans-serif">
    <table role="presentation" style="width:100%;border-collapse:collapse">
        <tr>
            <td align="center" style="padding:32px 16px">
                <table role="presentation" style="width:100%;max-width:620px;border:1px solid #e8e9e1;border-radius:16px;background:#fff;border-collapse:separate">
                    <tr>
                        <td style="padding:28px 30px;background:#244b3b;color:#fff;border-radius:16px 16px 0 0">
                            <p style="margin:0;font-size:12px;letter-spacing:2px;text-transform:uppercase">SipPly order</p>
                            <h1 style="margin:12px 0 0;font-family:Georgia,serif;font-size:30px;font-weight:400">Thanks for your order!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 30px">
                            <p style="margin:0 0 8px">Hi {{ $order->recipient_name }},</p>
                            <p style="margin:0 0 20px;color:#748178">We’ve received your order and will get it ready for you.</p>
                            <p style="margin:0 0 18px;font-size:14px"><strong>Order number:</strong> {{ $order->order_number }}</p>
                            <table role="presentation" style="width:100%;border-collapse:collapse">
                                @foreach ($items as $item)
                                    <tr>
                                        <td style="padding:10px 0;border-bottom:1px solid #edf0e9">{{ $item->quantity }} × {{ $item->product_name }}{{ ($item->size_name ?? null) ? ' · '.$item->size_name : '' }}</td>
                                        <td align="right" style="padding:10px 0;border-bottom:1px solid #edf0e9">₱{{ number_format((float) $item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #edf0e9">Delivery fee</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #edf0e9">₱{{ number_format((float) $order->delivery_fee, 2) }}</td>
                                </tr>
                                @if ((float) ($order->discount_amount ?? 0) > 0)
                                    <tr>
                                        <td style="padding:10px 0;border-bottom:1px solid #edf0e9">Coupon discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</td>
                                        <td align="right" style="padding:10px 0;border-bottom:1px solid #edf0e9">−₱{{ number_format((float) $order->discount_amount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:16px 0 0;font-weight:bold">Total</td>
                                    <td align="right" style="padding:16px 0 0;font-weight:bold;color:#244b3b">₱{{ number_format((float) $order->grand_total, 2) }}</td>
                                </tr>
                            </table>
                            <p style="margin:24px 0 6px;font-size:14px"><strong>Payment:</strong> {{ $order->method_name }} (pending)</p>
                            <p style="margin:0;font-size:14px;line-height:1.6"><strong>Delivering to:</strong> {{ $order->delivery_address }}</p>
                            <p style="margin:24px 0 0;color:#748178;font-size:13px">We’ll be in touch if we need anything else. SipPly · Something good is brewing.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
