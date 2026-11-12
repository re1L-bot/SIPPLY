<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your order has been delivered {{ $order->order_number }}</title>
</head>
<body style="margin:0;background:#f7f7f1;color:#20352c;font-family:Arial,sans-serif">
    <table role="presentation" style="width:100%;border-collapse:collapse">
        <tr>
            <td align="center" style="padding:32px 16px">
                <table role="presentation" style="width:100%;max-width:620px;border:1px solid #e8e9e1;border-radius:16px;background:#fff;border-collapse:separate">
                    <tr>
                        <td style="padding:28px 30px;background:#244b3b;color:#fff;border-radius:16px 16px 0 0">
                            <p style="margin:0;font-size:12px;letter-spacing:2px;text-transform:uppercase">SipPly order update</p>
                            <h1 style="margin:12px 0 0;font-family:Georgia,serif;font-size:30px;font-weight:400">Your order has been delivered!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 30px">
                            <p style="margin:0 0 8px">Hi {{ $order->recipient_name }},</p>
                            <p style="margin:0 0 20px;color:#748178">Your SipPly order has been marked as delivered. We hope you enjoy it!</p>
                            <p style="margin:0 0 18px;font-size:14px"><strong>Order number:</strong> {{ $order->order_number }}</p>
                            <p style="margin:0;font-size:14px;line-height:1.6"><strong>Delivered to:</strong> {{ $order->delivery_address }}</p>
                            <p style="margin:24px 0 0;color:#748178;font-size:13px">Thank you for choosing SipPly. Something good is brewing.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
