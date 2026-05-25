<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>Halo {{ $donation->user->name ?? 'Donatur' }},</p>
    
    <p>Terima kasih! Pembayaran donasi Anda untuk campaign <strong>{{ $donation->campaign->title }}</strong> telah kami terima.</p>
    
    <p>Detail Donasi:</p>
    <ul>
        <li>Nomor Donasi: {{ $donation->donation_number }}</li>
        <li>Nominal: Rp {{ number_format($donation->amount) }}</li>
        <li>Status: Berhasil</li>
    </ul>

    <p>Anda dapat mengunduh invoice resmi Anda melalui tautan di bawah ini atau melalui lampiran email ini:</p>
    <p><a href="{{ $donation->invoice_url }}">Unduh Invoice PDF</a></p>

    <p>Terima kasih atas kebaikan Anda.</p>
    
    <p>Salam,<br>Tim Fundraiser</p>
</body>
</html>
