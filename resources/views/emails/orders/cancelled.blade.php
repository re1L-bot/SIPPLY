<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your order has been cancelled {{ $order->order_number }}</title>
</head>
<body style="margin:0;background:#f7f7f1;color:#20352c;font-family:Arial,sans-serif">
    <table role="presentation" style="width:100%;border-collapse:collapse">
        <tr>
            <td align="center" style="padding:32px 16px">
                <table role="presentation" style="width:100%;max-width:620px;border:1px solid #e8e9e1;border-radius:16px;background:#fff;border-collapse:separate">
                    <tr>
                        <td style="padding:28px 30px;background:#8f3d35;color:#fff;border-radius:16px 16px 0 0">
                            <p style="margin:0;font-size:12px;letter-spacing:2px;text-transform:uppercase">SipPly order update</p>
                            <h1 style="margin:12px 0 0;font-family:Georgia,serif;font-size:30px;font-weight:400">Your order has been cancelled</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 30px">
                            <p style="margin:0 0 8px">Hi {{ $order->recipient_name }},</p>
                            <p style="margin:0 0 20px;color:#748178">Your SipPly order <strong>#{{ $order->order_number }}</strong> has been cancelled.</p>
                            @if (!empty($cancellationReason))
                                <div style="margin:0 0 20px;padding:16px;background:#fff5f3;border-left:4px solid #c94a3d;border-radius:6px">
                                    <p style="margin:0;font-size:13px;font-weight:bold;color:#8f3d35">Reason for cancellation:</p>
                                    <p style="margin:6px 0 0;font-size:14px;color:#444">{{ $cancellationReason }}</p>
                                </div>
                            @endif
                            <p style="margin:0 0 18px;font-size:14px"><strong>Order number:</strong> {{ $order->order_number }}</p>
                            @if (!empty($order->delivery_address))
                                <p style="margin:0 0 18px;font-size:14px;line-height:1.6"><strong>Delivery address:</strong> {{ $order->delivery_address }}</p>
                            @endif
                            <p style="margin:0;font-size:14px;line-height:1.6">If you have questions or believe this was done in error, please feel free to contact us.</p>
                            <p style="margin:24px 0 0;color:#748178;font-size:13px">Thank you for visiting SipPly.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

