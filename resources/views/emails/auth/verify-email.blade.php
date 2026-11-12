<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify your email address | SipPly</title>
</head>
<body style="margin:0;padding:0;background:#f7f7f1;color:#20352c;font-family:'Plus Jakarta Sans',Arial,sans-serif;-webkit-font-smoothing:antialiased">
    <table role="presentation" style="width:100%;border-collapse:collapse">
        <tr>
            <td align="center" style="padding:36px 16px">
                <table role="presentation" style="width:100%;max-width:560px;border:1px solid #e8e9e1;border-radius:20px;background:#ffffff;border-collapse:separate;overflow:hidden;box-shadow:0 6px 20px rgba(84,40,18,0.06)">
                    <tr>
                        <td style="padding:32px 36px 28px;background:radial-gradient(120% 90% at 20% 0%, #7a4426 0%, #542812 55%, #3f1d0d 100%);color:#fff7ef">
                            <table role="presentation" style="width:100%;border-collapse:collapse">
                                <tr>
                                    <td style="vertical-align:middle">
                                        <p style="margin:0;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#d99a69">Welcome to SipPly</p>
                                        <h1 style="margin:10px 0 0;font-family:Georgia,serif;font-size:26px;font-weight:700;line-height:1.2;color:#ffffff">Verify your email address</h1>
                                    </td>
                                    <td align="right" style="width:52px;vertical-align:middle">
                                        <img src="{{ asset('images/favicon.png') }}" alt="SipPly" width="48" height="48" style="border-radius:50%;display:block">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px">
                            <p style="margin:0 0 14px;font-size:16px;line-height:1.5;color:#382015">Hi {{ $user->first_name ?? 'there' }},</p>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#5a4336">
                                Thanks for creating your SipPly account! Before you dive in and start ordering your favorite drinks, please confirm that this is your email address by clicking the button below.
                            </p>
                            
                            <table role="presentation" style="margin:28px 0;width:100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:14px 32px;background:#542812;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;border-radius:999px;box-shadow:0 8px 18px rgba(84,40,18,0.25)">
                                            Verify email address
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 10px;font-size:13px;line-height:1.6;color:#8c7769">
                                This verification link will expire in 60 minutes. If you did not create an account with SipPly, please ignore this email or reach out to us.
                            </p>

                            <hr style="border:0;border-top:1px solid #edf0e9;margin:28px 0 20px">

                            <p style="margin:0 0 8px;font-size:12px;color:#8c7769">
                                If you're having trouble clicking the "Verify email address" button, copy and paste the URL below into your web browser:
                            </p>
                            <p style="margin:0;font-size:11px;line-height:1.5;word-break:break-all;color:#75452d">
                                <a href="{{ $url }}" style="color:#75452d">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 36px 24px;background:#fbf7f2;border-top:1px solid #f0e6dc;font-size:12px;color:#8c7769;text-align:center">
                            &copy; {{ date('Y') }} SipPly &mdash; Something good is brewing. &nbsp;&middot;&nbsp; Bantayan, Cebu, Philippines
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
