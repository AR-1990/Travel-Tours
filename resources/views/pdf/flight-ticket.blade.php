<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>E-Ticket {{ $pnr }}</title>
    <style>
        body { font-family: Georgia, "Times New Roman", serif; color: #1a1a1a; margin: 32px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #0b3d5c; padding-bottom: 12px; margin-bottom: 20px; }
        .logo { max-height: 56px; max-width: 160px; }
        .airline-code { font-size: 28px; font-weight: 700; color: #0b3d5c; letter-spacing: .04em; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .meta { color: #555; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; font-size: 13px; }
        th { background: #f4f7fa; }
        .box { border: 1px solid #d7dde5; padding: 12px 14px; margin-top: 16px; border-radius: 4px; }
        .actions { margin-top: 24px; }
        @media print {
            .actions { display: none; }
            body { margin: 12mm; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>{{ $agency }}</h1>
            <div class="meta">Electronic ticket / itinerary receipt</div>
        </div>
        <div style="text-align:right">
            @if(!empty($logo_path) && is_file($logo_path))
                <img class="logo" src="{{ $logo_url }}" alt="{{ $carrier }}">
            @else
                <div class="airline-code">{{ $carrier }}</div>
            @endif
            <div class="meta">{{ $airline_label }}</div>
        </div>
    </div>

    <div class="box">
        <strong>Passenger:</strong> {{ $passenger_name }}<br>
        <strong>PNR / booking ref:</strong> {{ $pnr }}<br>
        <strong>Route:</strong> {{ $origin }} → {{ $destination }}
        @if($return_date) (return {{ $return_date }}) @endif<br>
        <strong>Departure:</strong> {{ $departure_date }} · <strong>Status:</strong> {{ strtoupper($status) }}<br>
        <strong>Ticket no:</strong> {{ $ticket_numbers_label }} · <strong>Payment:</strong> {{ $form_of_payment ?: '—' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Flight</th>
                <th>From</th>
                <th>To</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Cabin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($segments as $seg)
                <tr>
                    <td>{{ ($seg['carrier'] ?? '').($seg['flight_number'] ?? '') }}</td>
                    <td>{{ $seg['origin'] ?? '' }}</td>
                    <td>{{ $seg['destination'] ?? '' }}</td>
                    <td>{{ $seg['departure'] ?? '' }}</td>
                    <td>{{ $seg['arrival'] ?? '' }}</td>
                    <td>{{ $seg['cabin'] ?? ($seg['class_of_service'] ?? '') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Itinerary details unavailable.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($show_price))
        <div class="box">
            <strong>Total paid / quoted:</strong> {{ $currency }} {{ $total }}
            @if(!empty($form_of_payment))
                <br><strong>Payment:</strong> {{ $form_of_payment }}
            @endif
        </div>
    @else
        <div class="box">
            <strong>Fare amount:</strong> Not shown on this print
        </div>
    @endif

    <div class="actions">
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>
</body>
</html>
