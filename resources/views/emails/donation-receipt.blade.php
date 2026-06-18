<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#F3F4F6;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F3F4F6;padding:40px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                <tr>
                    <td style="background:#1677ff;padding:40px;text-align:center;">
                        <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:700;letter-spacing:0.5px;">Fundraiser</h1>
                        <p style="margin:8px 0 0;color:#bfdbfe;font-size:14px;">Bukti Donasi</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:40px;">
                        <p style="margin:0 0 8px;color:#111827;font-size:16px;">Halo, <strong>{{ $donation->user->name ?? 'Donatur' }}</strong>,</p>
                        <p style="margin:0 0 32px;color:#6B7280;font-size:15px;line-height:1.6;">
                            Terima kasih, pembayaran donasi Anda untuk campaign berikut telah kami terima.
                        </p>

                        <div style="background:#F0F7FF;border:2px dashed #1677ff;border-radius:12px;padding:32px;text-align:center;margin-bottom:32px;">
                            <p style="margin:0 0 8px;color:#6B7280;font-size:13px;text-transform:uppercase;letter-spacing:1px;font-weight:600;">{{ $donation->campaign->title }}</p>
                            <h2 style="margin:0;font-size:28px;font-weight:800;color:#1677ff;">Rp {{ number_format($donation->amount) }}</h2>
                        </div>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px;">
                            <tr>
                                <td style="padding:12px 0;border-bottom:1px solid #E5E7EB;color:#6B7280;font-size:14px;">Nomor Donasi</td>
                                <td style="padding:12px 0;border-bottom:1px solid #E5E7EB;color:#111827;font-size:14px;text-align:right;font-weight:600;">{{ $donation->donation_number }}</td>
                            </tr>
                            <tr>
                                <td style="padding:12px 0;color:#6B7280;font-size:14px;">Status</td>
                                <td style="padding:12px 0;text-align:right;">
                                    <span style="background:#ECFDF5;color:#059669;font-size:12px;font-weight:600;padding:4px 12px;border-radius:999px;">Berhasil</span>
                                </td>
                            </tr>
                        </table>

                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center">
                                    <a href="{{ $donation->invoice_url }}" style="display:inline-block;background:#1677ff;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;padding:14px 32px;border-radius:8px;">Unduh Invoice PDF</a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:32px 0 0;color:#9CA3AF;font-size:13px;line-height:1.6;text-align:center;">
                            Invoice resmi juga tersedia sebagai lampiran pada email ini.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="background:#F9FAFB;border-top:1px solid #E5E7EB;padding:24px 40px;text-align:center;">
                        <p style="margin:0;color:#9CA3AF;font-size:12px;">Terima kasih atas kebaikan Anda.</p>
                        <p style="margin:8px 0 0;color:#9CA3AF;font-size:12px;">© 2026 Fundraiser. All rights reserved.</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
