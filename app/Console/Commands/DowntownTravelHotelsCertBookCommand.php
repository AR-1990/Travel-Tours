<?php

namespace App\Console\Commands;

use App\Services\DowntownTravel\DowntownTravelHotelService;
use App\Services\DowntownTravel\DowntownTravelHotelsClient;
use App\Services\DowntownTravel\DowntownTravelHotelsIntegrationConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Downtown Travel Hotels certification booking:
 * arbitrary city/dates, 2 adults + 1 child → share order_id with Downtown.
 */
class DowntownTravelHotelsCertBookCommand extends Command
{
    protected $signature = 'downtown-hotels:cert-book
        {--destination=london : Destination key (london, dubai, paris, …)}
        {--check-in= : Check-in Y-m-d (default +21 days)}
        {--check-out= : Check-out Y-m-d (default +23 days)}
        {--child-age=7 : Age of the one child}
        {--email=cert.hotels@wisetrusttravel.com : Lead contact email}
        {--phone=+14057787503 : Lead phone E.164}
        {--poll=12 : Times to poll Get Order Details after book}
        {--poll-sleep=10 : Seconds between polls}';

    protected $description = 'Book a Downtown Travel hotel (2 ADT + 1 CHD) for certification and print the order id.';

    public function handle(DowntownTravelHotelService $hotels, DowntownTravelHotelsClient $client): int
    {
        if (! DowntownTravelHotelsIntegrationConfig::isReadyForHotels()) {
            $this->error('Downtown Travel Hotels is not configured.');
            $this->line('Set credentials under Admin → Integrations → Downtown Travel Hotels');
            $this->line('or in .env: DOWNTOWN_TRAVEL_HOTELS_CLIENT_ID / CLIENT_SECRET / USERNAME / PASSWORD');

            return self::FAILURE;
        }

        $token = $client->getToken(true);
        if (! ($token['ok'] ?? false)) {
            $this->error('OAuth failed: '.($token['message'] ?? 'unknown'));

            return self::FAILURE;
        }
        $this->info('OAuth OK ('.DowntownTravelHotelsIntegrationConfig::hotelsBaseUrl().')');

        $destination = strtolower((string) $this->option('destination'));
        $checkIn = (string) ($this->option('check-in') ?: now()->addDays(21)->format('Y-m-d'));
        $checkOut = (string) ($this->option('check-out') ?: now()->addDays(23)->format('Y-m-d'));
        $childAge = max(0, min(17, (int) $this->option('child-age')));
        $email = (string) $this->option('email');
        $phone = (string) $this->option('phone');

        $root = storage_path('app/downtown-travel-hotels-cert/'.now()->format('Ymd-His'));
        File::ensureDirectoryExists($root);

        $this->info("Search: {$destination} {$checkIn} → {$checkOut} · 2 ADT + 1 CHD (age {$childAge})");

        $search = $hotels->searchAvailability([
            'destination' => $destination,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => 2,
            'children' => 1,
            'child_ages' => [$childAge],
        ]);
        File::put($root.'/01-search.json', json_encode($search, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if (! ($search['ok'] ?? false) || empty($search['solutions'][0])) {
            $this->error($search['message'] ?? 'Search failed / no hotels.');

            return self::FAILURE;
        }

        $solution = $search['solutions'][0];
        $key = (string) ($solution['key'] ?? '');
        $this->info('Picked: '.($solution['hotel_name'] ?? $key).' · '.($solution['currency'] ?? '').' '.($solution['total_price'] ?? ''));

        $prebook = $hotels->recheckAndPreBook($key);
        File::put($root.'/02-prebook.json', json_encode($prebook, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if (! ($prebook['ok'] ?? false)) {
            $this->error($prebook['message'] ?? 'Prebook / validate failed.');

            return self::FAILURE;
        }

        $book = $hotels->book([
            'email' => $email,
            'phone' => $phone,
            'prefix' => 'Mr.',
            'first_name' => 'Adam',
            'last_name' => 'Certhotel',
            'nationality' => 'US',
            'guests' => [
                [
                    'prefix' => 'Mr.',
                    'first_name' => 'Adam',
                    'last_name' => 'Certhotel',
                    'pax_type' => 'Adult',
                    'nationality' => 'US',
                ],
                [
                    'prefix' => 'Mrs.',
                    'first_name' => 'Beth',
                    'last_name' => 'Certhotel',
                    'pax_type' => 'Adult',
                    'nationality' => 'US',
                ],
                [
                    'prefix' => 'Mstr.',
                    'first_name' => 'Carl',
                    'last_name' => 'Certhotel',
                    'pax_type' => 'Child',
                    'child_age' => $childAge,
                    'nationality' => 'US',
                ],
            ],
        ]);
        File::put($root.'/03-book.json', json_encode($book, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if (! ($book['ok'] ?? false)) {
            $this->error($book['message'] ?? 'Book failed.');
            if (! empty($book['order_id'])) {
                $this->warn('Partial order_id: '.$book['order_id']);
            }

            return self::FAILURE;
        }

        $orderId = (string) ($book['order_id'] ?? data_get($book, 'booking.booking_id', ''));
        $readable = (string) data_get($book, 'booking.reference_no', '');
        $this->info('Book submitted. order_id='.$orderId.' readable_id='.$readable);

        $polls = max(1, (int) $this->option('poll'));
        $sleep = max(1, (int) $this->option('poll-sleep'));
        $final = null;
        for ($i = 1; $i <= $polls; $i++) {
            $final = $hotels->getOrderDetails($orderId);
            File::put($root.'/04-order-poll-'.$i.'.json', json_encode($final, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $status = (string) ($final['booking_status'] ?? data_get($final, 'order.rooms.0.booking_status', ''));
            $this->line("  poll {$i}/{$polls}: status=".($status !== '' ? $status : '(pending)'));
            if (in_array(strtolower($status), ['booked', 'confirmed', 'canceled', 'cancelled', 'failed', 'rejected'], true)) {
                break;
            }
            if ($i < $polls) {
                sleep($sleep);
            }
        }

        $order = is_array($final['order'] ?? null) ? $final['order'] : [];
        $readable = (string) ($order['readable_id'] ?? $readable);
        $status = (string) ($final['booking_status'] ?? data_get($order, 'rooms.0.booking_status', ''));
        $hotelName = (string) data_get($order, 'property.name', $solution['hotel_name'] ?? '');

        $summary = [
            'ok' => true,
            'provider' => 'downtown_travel_hotels',
            'environment' => DowntownTravelHotelsIntegrationConfig::merged()['environment'] ?? 'sandbox',
            'destination' => $destination,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => 2,
            'children' => 1,
            'child_age' => $childAge,
            'hotel_name' => $hotelName,
            'order_id' => $orderId,
            'readable_id' => $readable,
            'booking_status' => $status,
            'total_price' => data_get($book, 'booking.total_price'),
            'currency' => data_get($book, 'booking.currency'),
            'guests' => 'Adam Certhotel (ADT), Beth Certhotel (ADT), Carl Certhotel (CHD '.$childAge.')',
            'email' => $email,
            'phone' => $phone,
            'logs' => $root,
        ];
        File::put($root.'/SUMMARY.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->newLine();
        $this->info('=== Downtown Hotels certification order ===');
        $this->line('order_id:      '.$orderId);
        $this->line('readable_id:   '.$readable);
        $this->line('status:        '.$status);
        $this->line('hotel:         '.$hotelName);
        $this->line('stay:          '.$checkIn.' → '.$checkOut.' ('.$destination.')');
        $this->line('occupancy:     2 adults + 1 child (age '.$childAge.')');
        $this->line('logs:          '.$root);

        return self::SUCCESS;
    }
}
