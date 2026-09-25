<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;background:#f4f6fb;color:#344054;font-family:Arial,Helvetica,sans-serif;line-height:1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6fb;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border:1px solid #e8edf5;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:#5064d8;padding:26px 32px;">
                            <div style="color:#ffffff;font-size:21px;font-weight:700;letter-spacing:.08em;">NETIVO</div>
                            <div style="color:#dfe4ff;font-size:13px;margin-top:4px;">Sistem Pembayaran Billing Internet</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;color:#667085;font-size:14px;">Halo {{ $recipientName }},</p>
                            <h1 style="margin:0 0 14px;color:#243b6b;font-size:24px;line-height:1.3;">{{ $title }}</h1>
                            <p style="margin:0;color:#475467;font-size:15px;">{{ $notificationMessage }}</p>

                            @if($url)
                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:26px;">
                                    <tr>
                                        <td style="border-radius:7px;background:#5064d8;">
                                            <a href="{{ $url }}" style="display:inline-block;padding:12px 20px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;">Lihat detail di Netivo</a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:28px 0 0;color:#98a2b3;font-size:12px;">Email ini dikirim otomatis oleh Netivo. Mohon tidak membalas email ini.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top:1px solid #eef1f6;padding:18px 32px;color:#98a2b3;font-size:12px;">
                            © {{ now()->year }} {{ config('app.name', 'Netivo') }} · Notifikasi sistem
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
