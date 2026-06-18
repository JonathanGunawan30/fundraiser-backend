<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $donation->donation_number }}</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; line-height: 1.5; color: #2d2d2d; font-size: 13px; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 30px; position: relative; }

        .header-table { width: 100%; border-bottom: 3px solid #1677ff; padding-bottom: 16px; margin-bottom: 24px; }
        .header-table td { vertical-align: top; }
        .brand-name { font-size: 20px; font-weight: bold; color: #1677ff; margin: 0; }
        .brand-sub { font-size: 11px; color: #888; margin: 2px 0 0; }
        .invoice-label { font-size: 24px; font-weight: bold; color: #2d2d2d; margin: 0; text-align: right; letter-spacing: 1px; }
        .invoice-number { font-size: 12px; color: #888; margin: 4px 0 0; text-align: right; }

        .status-badge { display: inline-block; background: #e8f8ee; color: #1d8a4c; font-size: 11px; font-weight: bold; padding: 4px 12px; border-radius: 3px; text-transform: uppercase; letter-spacing: 0.5px; }

        .info-table { width: 100%; margin-bottom: 28px; }
        .info-table td { vertical-align: top; width: 50%; }
        .info-label { font-size: 10px; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 6px; }
        .info-value { font-size: 13px; color: #2d2d2d; margin: 0; }
        .info-value-strong { font-weight: bold; font-size: 14px; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .items-table th { background: #2d2d2d; color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 10px 12px; text-align: left; }
        .items-table th.align-right { text-align: right; }
        .items-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .items-table td.align-right { text-align: right; }

        .totals-table { width: 100%; margin-top: 0; }
        .totals-table td { padding: 8px 12px; font-size: 13px; }
        .totals-table .label-col { width: 60%; }
        .totals-table .total-row td { border-top: 2px solid #2d2d2d; font-size: 16px; font-weight: bold; padding-top: 14px; color: #1677ff; }

        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #eee; text-align: center; font-size: 11px; color: #999; }
        .footer p { margin: 3px 0; }

        .paid-stamp-wrapper { position: absolute; top: 0; left: 0; width: 100%; height: 100%; text-align: center; }
        .paid-stamp {
            display: inline-block;
            margin-top: 250px;
            border: 16px solid #1d8a4c;
            color: #1d8a4c;
            font-size: 170px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 12px 52px;
            opacity: 0.12;
            border-radius: 20px;
            white-space: nowrap;
            transform: rotate(-25deg);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="paid-stamp-wrapper">
        <div class="paid-stamp">PAID</div>
    </div>

    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <p class="brand-name">Fundraiser</p>
                <p class="brand-sub">Platform Donasi Terpercaya</p>
            </td>
            <td style="width: 50%;">
                <p class="invoice-label">INVOICE</p>
                <p class="invoice-number">No. {{ $donation->donation_number }}</p>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td>
                <p class="info-label">Diterbitkan Untuk</p>
                <p class="info-value info-value-strong">{{ $donation->user->name ?? 'Anonim' }}</p>
                <p class="info-value">{{ $donation->user->email ?? '' }}</p>
            </td>
            <td style="text-align: right;">
                <p class="info-label">Tanggal Transaksi</p>
                <p class="info-value">{{ $donation->created_at->format('d M Y, H:i') }}</p>
                <p class="info-label" style="margin-top: 10px;">Status Pembayaran</p>
                <span class="status-badge">Berhasil</span>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
        <tr>
            <th>Deskripsi</th>
            <th class="align-right">Jumlah</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                Donasi untuk campaign<br>
                <strong>{{ $donation->campaign->title }}</strong>
            </td>
            <td class="align-right">Rp {{ number_format($donation->amount) }}</td>
        </tr>
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="label-col" style="color: #888;">Subtotal</td>
            <td style="text-align: right;">Rp {{ number_format($donation->amount) }}</td>
        </tr>
        <tr class="total-row">
            <td class="label-col">Total Pembayaran</td>
            <td style="text-align: right;">Rp {{ number_format($donation->amount) }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Invoice ini diterbitkan secara otomatis oleh sistem dan sah tanpa tanda tangan basah.</p>
        <p>Terima kasih atas kebaikan dan kepercayaan Anda kepada Fundraiser.</p>
        <p>&copy; {{ date('Y') }} Fundraiser. All rights reserved.</p>
    </div>
</div>
</body>
</html>
