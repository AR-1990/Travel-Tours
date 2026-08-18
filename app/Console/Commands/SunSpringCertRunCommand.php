<?php

namespace App\Console\Commands;

use App\Services\SunSpring\SunSpringAirService;
use App\Services\SunSpring\SunSpringExchangeLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SunSpringCertRunCommand extends Command
{
    protected $signature = 'sunspring:cert-run';

    protected $description = 'Run SunSpring sandbox booking scenarios and save RQ/RS logs.';

    public function handle(SunSpringAirService $air): int
    {
        if (! $air->isReady()) {
            $this->error('SunSpring is not ready. Save credentials under Admin → Integrations → SunSpring.');

            return self::FAILURE;
        }

        $root = storage_path('app/sunspring-cert/'.now()->format('Ymd-His'));
        File::ensureDirectoryExists($root);

        $scenarios = [
            [
                'folder' => '01-one-adult',
                'title' => 'Booking for one adult',
                'search' => ['adults' => 1, 'children' => 0, 'infants' => 0, 'trip_type' => 'oneway'],
                'passengers' => [
                    $this->adult('Ahmad', 'Testi', '1990-03-12'),
                ],
            ],
            [
                'folder' => '02-one-adult-one-child',
                'title' => 'Booking for one adult and one child',
                'search' => ['adults' => 1, 'children' => 1, 'infants' => 0, 'trip_type' => 'oneway'],
                'passengers' => [
                    $this->adult('Ahmad', 'Testi', '1990-03-12'),
                    $this->child('Sara', 'Testi', '2018-06-20'),
                ],
            ],
            [
                'folder' => '03-two-adults-child-infant',
                'title' => 'Booking for two adults, one child, and one infant',
                'search' => ['adults' => 2, 'children' => 1, 'infants' => 1, 'trip_type' => 'oneway'],
                'passengers' => [
                    $this->adult('Ahmad', 'Testi', '1990-03-12'),
                    $this->adult('Fatima', 'Testi', '1992-08-04', 'F', 'Mrs'),
                    $this->child('Sara', 'Testi', '2018-06-20'),
                    $this->infant('Omar', 'Testi', now()->subMonths(10)->format('Y-m-d')),
                ],
            ],
            [
                'folder' => '04-two-adults-roundtrip',
                'title' => 'Booking for two adults (round trip)',
                'search' => ['adults' => 2, 'children' => 0, 'infants' => 0, 'trip_type' => 'roundtrip'],
                'passengers' => [
                    $this->adult('Ahmad', 'Testi', '1990-03-12'),
                    $this->adult('Fatima', 'Testi', '1992-08-04', 'F', 'Mrs'),
                ],
            ],
        ];

        $this->info('Probing sandbox inventory (Sepehran routes, several dates)…');
        $inventory = $this->findInventory($air);
        if ($inventory === null) {
            $this->warn('No fares found. Scenarios will still save FlightSearch RQ/RS.');
        } else {
            $this->info(sprintf(
                'Using inventory: %s–%s on %s%s',
                $inventory['origin'],
                $inventory['destination'],
                $inventory['departure_date'],
                ! empty($inventory['return_date']) ? ' / '.$inventory['return_date'] : ''
            ));
        }

        $summaries = [];
        foreach ($scenarios as $scenario) {
            $dir = $root.'/'.$scenario['folder'];
            SunSpringExchangeLogger::start($dir);
            $this->info('Running: '.$scenario['title']);
            $summaries[] = $this->runScenario($air, $scenario, $dir, $inventory);
        }
        SunSpringExchangeLogger::stop();

        File::put($root.'/SUMMARY.json', json_encode($summaries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        File::put($root.'/README.txt', implode("\n", [
            'SunSpring sandbox certification RQ/RS',
            'Environment: https://sandbox.sunspring.ae',
            'Generated: '.now()->toDateTimeString(),
            '',
            'Scenarios:',
            '01 — one adult (one way)',
            '02 — one adult + one child (one way)',
            '03 — two adults + one child + one infant (one way)',
            '04 — two adults (round trip)',
            '',
            'Each folder contains numbered *-RQ.json and *-RS.json files.',
            'Authorize token / passwords are not included.',
        ]));
        $this->newLine();
        $this->info('Logs saved in: '.$root);
        foreach ($summaries as $row) {
            $this->line(($row['ok'] ? '[OK] ' : '[FAIL] ').$row['title'].' — '.$row['message']);
        }

        return self::SUCCESS;
    }

    /**
     * @return array{origin: string, destination: string, departure_date: string, return_date: ?string}|null
     */
    protected function findInventory(SunSpringAirService $air): ?array
    {
        SunSpringExchangeLogger::stop();

        $routes = \App\Support\SunSpringAirports::POPULAR_ROUTES;
        $dayOffsets = [1, 2, 3, 7, 10, 14, 21, 30, 45];

        foreach ($routes as [$origin, $destination]) {
            foreach ($dayOffsets as $offset) {
                $depart = now()->addDays($offset)->format('Y-m-d');
                $result = $air->lowFareSearch([
                    'origin' => $origin,
                    'destination' => $destination,
                    'departure_date' => $depart,
                    'adults' => 1,
                    'children' => 0,
                    'infants' => 0,
                    'trip_type' => 'oneway',
                ]);
                if (! empty($result['solutions'])) {
                    $return = now()->addDays($offset + 7)->format('Y-m-d');

                    return [
                        'origin' => $origin,
                        'destination' => $destination,
                        'departure_date' => $depart,
                        'return_date' => $return,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $scenario
     * @param  array{origin: string, destination: string, departure_date: string, return_date: ?string}|null  $inventory
     * @return array<string, mixed>
     */
    protected function runScenario(SunSpringAirService $air, array $scenario, string $dir, ?array $inventory): array
    {
        $origin = $inventory['origin'] ?? 'THR';
        $destination = $inventory['destination'] ?? 'MHD';
        $dates = $inventory !== null
            ? [\Illuminate\Support\Carbon::parse($inventory['departure_date'])]
            : [
                now()->addDays(21),
                now()->addDays(45),
                now()->addDays(90),
            ];

        $searchResult = null;
        foreach ($dates as $depart) {
            $searchInput = array_merge($scenario['search'], [
                'origin' => $origin,
                'destination' => $destination,
                'departure_date' => $depart->format('Y-m-d'),
                'return_date' => ($scenario['search']['trip_type'] ?? '') === 'roundtrip'
                    ? ($inventory['return_date'] ?? $depart->copy()->addDays(7)->format('Y-m-d'))
                    : null,
            ]);
            $searchResult = $air->lowFareSearch($searchInput);
            if (! empty($searchResult['solutions'])) {
                break;
            }
        }

        $solutions = $searchResult['solutions'] ?? [];
        if ($solutions === []) {
            File::put($dir.'/RESULT.txt', "Search returned no fares for {$origin}-{$destination} on tried dates.\n".($searchResult['message'] ?? ''));

            return [
                'ok' => false,
                'title' => $scenario['title'],
                'message' => $searchResult['message'] ?? 'No inventory',
                'folder' => $dir,
            ];
        }

        $solution = $solutions[0];
        $price = $air->airPrice([
            'solution_key' => (string) ($solution['key'] ?? ''),
            'adults' => (int) $scenario['search']['adults'],
            'children' => (int) $scenario['search']['children'],
            'infants' => (int) $scenario['search']['infants'],
        ]);
        if (! ($price['ok'] ?? false)) {
            File::put($dir.'/RESULT.txt', 'Price failed: '.($price['message'] ?? ''));

            return [
                'ok' => false,
                'title' => $scenario['title'],
                'message' => $price['message'] ?? 'Price failed',
                'folder' => $dir,
            ];
        }

        $book = $air->book([
            'country_code' => '+98',
            'passengers' => $scenario['passengers'],
        ]);
        if (! ($book['ok'] ?? false)) {
            File::put($dir.'/RESULT.txt', 'Book failed: '.($book['message'] ?? ''));

            return [
                'ok' => false,
                'title' => $scenario['title'],
                'message' => $book['message'] ?? 'Book failed',
                'folder' => $dir,
            ];
        }

        $reference = (string) ($book['reference_id'] ?? '');
        $ticket = $air->issueTicket(['reference_id' => $reference]);
        if ($reference !== '') {
            $air->cancel(['reference' => $reference, 'type' => 'General']);
        }

        File::put($dir.'/RESULT.txt', implode("\n", [
            $scenario['title'],
            'Search: '.($searchResult['message'] ?? ''),
            'Price: '.($price['message'] ?? ''),
            'Book: '.($book['message'] ?? ''),
            'Ticket: '.($ticket['message'] ?? ''),
            'Reference: '.$reference,
            'Cancelled after test: yes',
        ]));

        return [
            'ok' => true,
            'title' => $scenario['title'],
            'message' => 'Booked ref '.$reference.' (then cancelled)',
            'folder' => $dir,
            'reference' => $reference,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function adult(string $first, string $last, string $dob, string $gender = 'M', string $prefix = 'Mr'): array
    {
        return $this->pax('ADT', $first, $last, $dob, $gender, $prefix);
    }

    /**
     * @return array<string, mixed>
     */
    protected function child(string $first, string $last, string $dob): array
    {
        return $this->pax('CHD', $first, $last, $dob, 'F', 'Miss');
    }

    /**
     * @return array<string, mixed>
     */
    protected function infant(string $first, string $last, string $dob): array
    {
        return $this->pax('INF', $first, $last, $dob, 'M', 'Mstr');
    }

    /**
     * @return array<string, mixed>
     */
    protected function pax(string $type, string $first, string $last, string $dob, string $gender, string $prefix): array
    {
        return [
            'type' => $type,
            'first' => $first,
            'last' => $last,
            'dob' => $dob,
            'gender' => $gender,
            'prefix' => $prefix,
            'email' => 'cert.test@example.com',
            'phone' => '9151112233',
            'nationality' => 'IRN',
            'national_id' => '1234567890',
        ];
    }
}
