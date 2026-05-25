<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $donation->donation_number }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; position: relative; }
        .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .invoice-info { display: table; width: 100%; margin-bottom: 20px; }
        .invoice-info div { display: table-cell; }
        .total { font-size: 1.2em; font-weight: bold; margin-top: 20px; text-align: right; }
        .footer { margin-top: 50px; text-align: center; font-size: 0.8em; color: #777; }
        
        /* PAID Stamp Watermark Style */
        .paid-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            border: 15px solid #28a745;
            color: #28a745;
            font-size: 150px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 20px 60px;
            opacity: 0.15;
            z-index: -1;
            border-radius: 20px;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="paid-stamp">PAID</div>
        <div class="header">
            <h1>INVOICE DONASI</h1>
            <p>Fundraiser Application</p>
        </div>

        <div class="invoice-info">
            <div>
                <strong>Donatur:</strong><br>
                {{ $donation->user->name ?? 'Anonim' }}<br>
                {{ $donation->user->email ?? '' }}
            </div>
            <div style="text-align: right;">
                <strong>Detail:</strong><br>
                Nomor: {{ $donation->donation_number }}<br>
                Tanggal: {{ $donation->created_at->format('d M Y H:i') }}
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background: #f9f9f9;">
                    <th style="border: 1px solid #eee; padding: 10px; text-align: left;">Campaign</th>
                    <th style="border: 1px solid #eee; padding: 10px; text-align: right;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #eee; padding: 10px;">{{ $donation->campaign->title }}</td>
                    <td style="border: 1px solid #eee; padding: 10px; text-align: right;">Rp {{ number_format($donation->amount) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total">
            Total Pembayaran: Rp {{ number_format($donation->amount) }}
        </div>

        <div class="footer">
            <p>Terima kasih atas bantuan Anda. Donasi Anda sangat berarti.</p>
            <p>&copy; {{ date('Y') }} Fundraiser. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
