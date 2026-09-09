<?php

namespace App\Console\Commands;

use App\Services\SunSpring\SunSpringAirService;
use App\Services\SunSpring\SunSpringExchangeLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class SunSpringCertRunCommand extends Command
{
    protected $signature = 'sunspring:cert-run';

    protected $description = 'Run SunSpring sandbox booking scenarios and save RQ/RS logs.';

    private int $nidSeed = 500100000;

    private int $passportSeed = 50010000;

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
                    $this->adult('Ahmad', 'Oneadult', '1990-03-12'),
                ],
            ],
            [
                'folder' => '02-one-adult-one-child',
                'title' => 'Booking for one adult and one child',
                'search' => ['adults' => 1, 'children' => 1, 'infants' => 0, 'trip_type' => 'oneway'],
                'passengers' => [
                    $this->adult('Hassan', 'Withchild', '1988-05-10'),
                    $this->child('Sara', 'Withchild', '2018-06-20'),
                ],
            ],
            [
                'folder' => '03-two-adults-child-infant',
                'title' => 'Booking for two adults, one child, and one infant',
                'search' => ['adults' => 2, 'children' => 1, 'infants' => 1, 'trip_type' => 'oneway'],
                'passengers' => [
                    $this->adult('Reza', 'Family', '1987-01-15'),
                    $this->adult('Fatima', 'Family', '1992-08-04', 'F', 'Mrs'),
                    $this->child('Dina', 'Family', '2017-04-12'),
                    $this->infant('Omar', 'Family', now()->subMonths(10)->format('Y-m-d')),
                ],
            ],
            [
                'folder' => '04-two-adults-roundtrip',
                'title' => 'Booking for two adults (round trip)',
                'search' => ['adults' => 2, 'children' => 0, 'infants' => 0, 'trip_type' => 'roundtrip'],
                'passengers' => [
                    $this->adult('Mehdi', 'Roundtrip', '1985-09-09'),
                    $this->adult('Zahra', 'Roundtrip', '1991-11-22', 'F', 'Mrs'),
                ],
            ],
        ];

        $this->info('Calling FlightSchedule for active routes…');
        $scheduleDir = $root.'/00-flight-schedule';
        SunSpringExchangeLogger::start($scheduleDir);
        $schedule = $air->flightSchedule([
            'from_date' => now()->format('Y-m-d'),
            'to_date' => now()->addDays(30)->format('Y-m-d'),
        ]);
        File::put($scheduleDir.'/RESULT.txt', ($schedule['message'] ?? '')."\nflights=".count($schedule['flights'] ?? []));
        SunSpringExchangeLogger::stop();

        $this->info('Finding bookable inventory from FlightSchedule…');
        $inventory = $this->findInventory($air, $schedule['flights'] ?? []);
        if ($inventory === null) {
            $this->warn('No fares found after FlightSchedule. Scenarios may stop at search.');
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
            'Route discovery: FlightSchedule (from_date / to_date)',
            'Booking route used: '.(($inventory['origin'] ?? '?').'-'.($inventory['destination'] ?? '?')),
            '',
            'Flow per scenario:',
            'FlightSearch → AirPrice → Book → Confirm → AirDemandTicket → Cancel',
            '',
            '00-flight-schedule — active routes',
            '01 — one adult (one way)',
            '02 — one adult + one child (one way)',
            '03 — two adults + one child + one infant (one way)',
            '04 — two adults (round trip)',
            '',
            'Authorize token / passwords are not included.',
        ]));
        File::put($root.'/EMAIL.txt', $this->emailDraft($inventory, $summaries));

        $this->newLine();
        $this->info('Logs saved in: '.$root);
        foreach ($summaries as $row) {
            $this->line(($row['ok'] ? '[OK] ' : '[FAIL] ').$row['title'].' — '.$row['message']);
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<array<string, mixed>>  $scheduleFlights
     * @return array{origin: string, destination: string, departure_date: string, return_date: ?string}|null
     */
    protected function findInventory(SunSpringAirService $air, array $scheduleFlights): ?array
    {
        SunSpringExchangeLogger::stop();

        $candidates = [];
        foreach ($scheduleFlights as $row) {
            if (! is_array($row)) {
                continue;
            }
            $origin = strtoupper((string) ($row['departure'] ?? ''));
            $destination = strtoupper((string) ($row['arrival'] ?? ''));
            $date = (string) ($row['actual_departure_date'] ?? '');
            if ($origin === '' || $destination === '' || $date === '') {
                continue;
            }
            if (! \App\Support\SunSpringAirports::isAllowed($origin) || ! \App\Support\SunSpringAirports::isAllowed($destination)) {
                continue;
            }
            $key = $origin.'|'.$destination.'|'.$date;
            $candidates[$key] = [
                'origin' => $origin,
                'destination' => $destination,
                'departure_date' => $date,
            ];
        }

        $ordered = array_values($candidates);
        usort($ordered, static function (array $a, array $b): int {
            $score = static function (array $row): int {
                $pair = $row['origin'].'-'.$row['destination'];
                $preferred = ['IKA-MCT' => 100, 'MCT-IKA' => 90, 'MHD-MCT' => 80, 'MCT-MHD' => 70];

                return $preferred[$pair] ?? 10;
            };

            return $score($b) <=> $score($a) ?: strcmp($a['departure_date'], $b['departure_date']);
        });

        foreach ($ordered as $candidate) {
            $result = $air->lowFareSearch([
                'origin' => $candidate['origin'],
                'destination' => $candidate['destination'],
                'departure_date' => $candidate['departure_date'],
                'adults' => 1,
                'children' => 0,
                'infants' => 0,
                'trip_type' => 'oneway',
            ]);
            if (empty($result['solutions'])) {
                continue;
            }

            $returnDate = Carbon::parse($candidate['departure_date'])->addDays(7)->format('Y-m-d');
            $rtOk = false;
            foreach ([7, 3, 5, 10, 14] as $offset) {
                $tryReturn = Carbon::parse($candidate['departure_date'])->addDays($offset)->format('Y-m-d');
                $rt = $air->lowFareSearch([
                    'origin' => $candidate['origin'],
                    'destination' => $candidate['destination'],
                    'departure_date' => $candidate['departure_date'],
                    'return_date' => $tryReturn,
                    'adults' => 2,
                    'children' => 0,
                    'infants' => 0,
                    'trip_type' => 'roundtrip',
                ]);
                if (! empty($rt['solutions'])) {
                    $returnDate = $tryReturn;
                    $rtOk = true;
                    break;
                }
            }

            return [
                'origin' => $candidate['origin'],
                'destination' => $candidate['destination'],
                'departure_date' => $candidate['departure_date'],
                'return_date' => $rtOk ? $returnDate : $returnDate,
            ];
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
        $origin = $inventory['origin'] ?? 'IKA';
        $destination = $inventory['destination'] ?? 'MCT';
        $depart = $inventory['departure_date'] ?? now()->addDays(1)->format('Y-m-d');
        $returnDate = ($scenario['search']['trip_type'] ?? '') === 'roundtrip'
            ? ($inventory['return_date'] ?? Carbon::parse($depart)->addDays(7)->format('Y-m-d'))
            : null;

        $searchInput = array_merge($scenario['search'], [
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $depart,
            'return_date' => $returnDate,
        ]);
        $searchResult = $air->lowFareSearch($searchInput);
        $solutions = $searchResult['solutions'] ?? [];
        if ($solutions === []) {
            File::put($dir.'/RESULT.txt', "Search returned no fares for {$origin}-{$destination} on {$depart}.\n".($searchResult['message'] ?? ''));

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
        $ticketNumbers = is_array($ticket['ticket_numbers'] ?? null) ? $ticket['ticket_numbers'] : [];
        $pnrs = is_array($ticket['pnrs'] ?? null) ? $ticket['pnrs'] : [];
        if ($pnrs === [] && is_array($ticket['raw'] ?? null)) {
            $pnrs = $air->extractPnrsFromPayload($ticket['raw']);
        }

        $cancelMsg = 'skipped';
        if ($reference !== '') {
            $cancel = $air->cancel([
                'reference' => $reference,
                'type' => 'General',
                'tickets' => $ticketNumbers,
                'pnrs' => $pnrs,
                'ticket_rows' => is_array($ticket['tickets'] ?? null) ? $ticket['tickets'] : [],
            ]);
            $cancelMsg = (($cancel['ok'] ?? false) ? 'ok' : 'fail').' — '.($cancel['message'] ?? '');
        }

        File::put($dir.'/RESULT.txt', implode("\n", [
            $scenario['title'],
            'Route: '.$origin.'-'.$destination.($returnDate ? ' / return '.$returnDate : ''),
            'Search: '.($searchResult['message'] ?? ''),
            'Price: '.($price['message'] ?? ''),
            'Book: '.($book['message'] ?? ''),
            'Ticket: '.($ticket['message'] ?? ''),
            'Reference: '.$reference,
            'Ticket numbers: '.implode(', ', $ticketNumbers),
            'Cancel: '.$cancelMsg,
        ]));

        $ok = ($book['ok'] ?? false) && ($ticket['ok'] ?? false);

        return [
            'ok' => $ok,
            'title' => $scenario['title'],
            'message' => $ok
                ? ('Booked+ticketed ref '.$reference.($ticketNumbers !== [] ? ' tickets '.implode(',', $ticketNumbers) : ''))
                : (($ticket['message'] ?? $book['message'] ?? 'Incomplete')),
            'folder' => $dir,
            'reference' => $reference,
            'ticket_numbers' => $ticketNumbers,
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
        // Sandbox accepts INF with empty accompanied (non-empty values return 10296).
        return $this->pax('INF', $first, $last, $dob, 'M', 'Mstr');
    }

    /**
     * @return array<string, mixed>
     */
    protected function pax(string $type, string $first, string $last, string $dob, string $gender, string $prefix): array
    {
        $nationalId = $this->nextNationalId();
        $passport = 'A'.$this->passportSeed++;

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
            'national_id' => $nationalId,
            'passport_number' => $passport,
            'passport_expire' => '2030-12-31',
            'accompanied' => '',
        ];
    }

    protected function nextNationalId(): string
    {
        $base9 = str_pad((string) $this->nidSeed++, 9, '0', STR_PAD_LEFT);
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $base9[$i] * (10 - $i);
        }
        $rem = $sum % 11;
        $check = $rem < 2 ? $rem : 11 - $rem;

        return $base9.$check;
    }

    /**
     * @param  array{origin?: string, destination?: string, departure_date?: string, return_date?: ?string}|null  $inventory
     * @param  list<array<string, mixed>>  $summaries
     */
    protected function emailDraft(?array $inventory, array $summaries): string
    {
        $route = ($inventory['origin'] ?? 'IKA').'–'.($inventory['destination'] ?? 'MCT');
        $date = $inventory['departure_date'] ?? '';
        $lines = [
            'Subject: Sandbox test cases — complete booking RQ & RS (4 scenarios)',
            '',
            'Hi,',
            '',
            'Thank you for the guidance on FlightSchedule.',
            '',
            'We used POST /api/v2/inventory/FlightSchedule (from_date / to_date) to identify currently active routes, then completed the full booking process for each requested scenario.',
            '',
            'Active route used for bookings: '.$route.($date !== '' ? ' on '.$date : '').'.',
            '',
            'Please find attached the complete RQ/RS logs, including:',
            '',
            '00. FlightSchedule (active routes)',
            'For each of the 4 scenarios:',
            '1. FlightSearch',
            '2. AirPrice',
            '3. Book',
            '4. Confirm',
            '5. AirDemandTicket',
            '6. Cancel (cleanup after test)',
            '',
            'Scenarios:',
            '1. One adult (one way)',
            '2. One adult + one child (one way)',
            '3. Two adults + one child + one infant (one way)',
            '4. Two adults (round trip)',
            '',
            'Results:',
        ];
        foreach ($summaries as $row) {
            $lines[] = '- '.(($row['ok'] ?? false) ? 'OK' : 'FAIL').': '.($row['title'] ?? '').' — '.($row['message'] ?? '');
        }
        $lines[] = '';
        $lines[] = 'Please review. If everything looks good, kindly share live/production API credentials.';
        $lines[] = '';
        $lines[] = 'Thank you,';
        $lines[] = '[Your name]';
        $lines[] = 'Wise Trust Travel & Tourism';
        $lines[] = 'info@wisetrust.com';
        $lines[] = '+2 123 4567 897';

        return implode("\n", $lines);
    }
}
