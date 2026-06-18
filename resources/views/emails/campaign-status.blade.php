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
                    <td style="background:{{ $status === 'approved' ? '#1677ff' : '#DC2626' }};padding:40px;text-align:center;">
                        <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:700;letter-spacing:0.5px;">Fundraiser</h1>
                        <p style="margin:8px 0 0;color:{{ $status === 'approved' ? '#bfdbfe' : '#fecaca' }};font-size:14px;">Status Campaign</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:40px;">
                        <p style="margin:0 0 8px;color:#111827;font-size:16px;">Halo, <strong>{{ $userName }}</strong>,</p>
                        <p style="margin:0 0 32px;color:#6B7280;font-size:15px;line-height:1.6;">
                            Kami ingin menginformasikan update terbaru mengenai campaign yang Anda ajukan.
                        </p>

                        <div style="background:{{ $status === 'approved' ? '#F0F7FF' : '#FEF2F2' }};border:2px dashed {{ $status === 'approved' ? '#1677ff' : '#DC2626' }};border-radius:12px;padding:32px;text-align:center;margin-bottom:32px;">
                            <p style="margin:0 0 8px;color:#6B7280;font-size:13px;text-transform:uppercase;letter-spacing:1px;font-weight:600;">{{ $campaignTitle }}</p>
                            <h2 style="margin:0;font-size:28px;font-weight:800;letter-spacing:1px;color:{{ $status === 'approved' ? '#1677ff' : '#DC2626' }};">
                                {{ $status === 'approved' ? 'DISETUJUI' : 'DITOLAK' }}
                            </h2>
                        </div>

                        @if($status === 'approved')
                            <p style="margin:0;color:#6B7280;font-size:14px;line-height:1.6;">
                                Campaign Anda sekarang sudah aktif dan dapat mulai menerima donasi.
                            </p>
                        @else
                            <p style="margin:0;color:#6B7280;font-size:14px;line-height:1.6;">
                                Mohon maaf, campaign Anda belum dapat kami setujui saat ini. Silakan hubungi dukungan kami untuk informasi lebih lanjut.
                            </p>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="background:#F9FAFB;border-top:1px solid #E5E7EB;padding:24px 40px;text-align:center;">
                        <p style="margin:0;color:#9CA3AF;font-size:12px;">© 2026 Fundraiser. All rights reserved.</p>
                        <p style="margin:8px 0 0;color:#9CA3AF;font-size:12px;">This is an automated email. Please do not reply.</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
