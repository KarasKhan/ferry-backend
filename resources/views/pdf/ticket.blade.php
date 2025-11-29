<!DOCTYPE html>
<html>
<head>
    <title>E-Ticket - {{ $booking->booking_code }}</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .container { width: 100%; border: 2px solid #0056b3; padding: 20px; border-radius: 10px; }
        .header { text-align: center; border-bottom: 2px dashed #ccc; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #0056b3; }
        .booking-code { font-size: 18px; font-weight: bold; margin-top: 5px; }
        
        .content { width: 100%; }
        .row { display: table; width: 100%; margin-bottom: 10px; }
        .col { display: table-cell; width: 50%; vertical-align: top; }
        
        .label { font-size: 10px; color: #777; text-transform: uppercase; }
        .value { font-size: 14px; font-weight: bold; margin-bottom: 10px; }
        
        .passengers { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .passengers th { text-align: left; background: #f0f0f0; padding: 5px; font-size: 12px; }
        .passengers td { border-bottom: 1px solid #ddd; padding: 5px; font-size: 12px; }
        
        .qr-area { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 2px dashed #ccc; }
        .footer { text-align: center; font-size: 10px; color: #777; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">FerryApp E-Ticket</div>
            <div class="booking-code">BOOKING: {{ $booking->booking_code }}</div>
        </div>

        <div class="content">
            <div class="row">
                <div class="col">
                    <div class="label">KAPAL</div>
                    <div class="value">{{ $booking->schedule->ship->name }}</div>
                </div>
                <div class="col">
                    <div class="label">TANGGAL BERANGKAT</div>
                    <div class="value">{{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('d F Y, H:i') }} WIB</div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="label">DARI (ASAL)</div>
                    <div class="value">{{ $booking->schedule->route->origin->name }}</div>
                </div>
                <div class="col">
                    <div class="label">KE (TUJUAN)</div>
                    <div class="value">{{ $booking->schedule->route->destination->name }}</div>
                </div>
            </div>
        </div>

        <table class="passengers">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Penumpang</th>
                    <th>Identitas (NIK)</th>
                    <th>Kategori</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->passengers as $index => $pax)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $pax->name }}</td>
                    <td>{{ $pax->identity_number }}</td>
                    <td>{{ $pax->ticket_category->name ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="qr-area">
            <img src="data:image/svg+xml;base64, {{ $qrcode }}" width="150">
            <p>Scan kode ini di gerbang pelabuhan</p>
        </div>

        <div class="footer">
            Harap datang 60 menit sebelum keberangkatan. Tiket ini sah dan dapat digunakan sebagai bukti pembayaran.
        </div>
    </div>
</body>
</html>