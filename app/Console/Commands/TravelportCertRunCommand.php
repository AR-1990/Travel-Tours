<?php

namespace App\Console\Commands;

use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportExchangeLogger;
use App\Services\Travelport\TravelportIntegrationConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Travelport copy/pre-prod certification: return + connecting, 1 ADT + 1 CHD + 1 INF.
 * Saves full SOAP RQ/RS XML under storage/app/travelport-cert/{timestamp}/
 */
class TravelportCertRunCommand extends Command
{
    protected $signature = 'travelport:cert-run
        {--origin=KWI : Origin airport}
        {--destination=NBO : Destination airport}
        {--via=DXB : Prefer connections through this airport}
        {--depart= : Outbound date Y-m-d (default +21 days)}
        {--return= : Return date Y-m-d (default +28 days)}
        {--ticket : Also attempt AirTicketing (may fail on copy)}
        {--keep : Keep the PNR (skip cancel)}';

    protected $description = 'Run Travelport copy E2E (return+connecting, ADT+CHD+INF) and save XML RQ/RS.';

    public function handle(TravelportAirService $air): int
    {
        if (! TravelportIntegrationConfig::isReadyForAir()) {
            $this->error('Travelport is not ready. Configure username, password, and target branch under Admin → Integrations → Travelport (or TRAVELPORT_* in .env), enable the integration, then re-run.');

            return self::FAILURE;
        }

        $origin = strtoupper((string) $this->option('origin'));
        $destination = strtoupper((string) $this->option('destination'));
        $via = strtoupper((string) $this->option('via'));
        $depart = (string) ($this->option('depart') ?: now()->addDays(21)->format('Y-m-d'));
        $return = (string) ($this->option('return') ?: now()->addDays(28)->format('Y-m-d'));
        $traceId = 'wt-cert-'.Str::lower(Str::random(12));

        $root = storage_path('app/travelport-cert/'.now()->format('Ymd-His'));
        File::ensureDirectoryExists($root);
        TravelportExchangeLogger::start($root);

        $this->info("TraceId (all steps): {$traceId}");
        $this->info("Route: {$origin}-{$destination} return, prefer via {$via}");
        $this->info("Dates: {$depart} / {$return}");
        $this->info("Logs: {$root}");

        $base = [
            '_trace_id' => $traceId,
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $depart,
            'return_date' => $return,
            'adults' => 1,
            'children' => 1,
            'infants' => 1,
            'child_age' => 8,
            'infant_age' => 1,
            'trip_type' => 'roundtrip',
        ];

        $passengers = [
            [
                'prefix' => 'Mr', 'first' => 'Ahmed', 'last' => 'CertAdult',
                'email' => 'cert.adult@wisetrust.com', 'phone' => '971500000001',
                'dob' => '1988-04-12', 'gender' => 'M', 'type' => 'ADT',
            ],
            [
                'prefix' => 'Mstr', 'first' => 'Omar', 'last' => 'CertChild',
                'email' => 'cert.adult@wisetrust.com', 'phone' => '971500000001',
                'dob' => now()->subYears(8)->format('Y-m-d'), 'gender' => 'M', 'type' => 'CNN',
            ],
            [
                'prefix' => 'Mstr', 'first' => 'Baby', 'last' => 'CertInfant',
                'email' => '', 'phone' => '',
                'dob' => now()->subMonths(9)->format('Y-m-d'), 'gender' => 'M', 'type' => 'INF',
            ],
        ];

        $summary = [
            'trace_id' => $traceId,
            'origin' => $origin,
            'destination' => $destination,
            'via' => $via,
            'departure_date' => $depart,
            'return_date' => $return,
            'steps' => [],
        ];

        // 1) Low Fare Search
        $this->info('1/5 Low Fare Search…');
        $search = $air->execute('low_fare_search', $base);
        $summary['steps']['low_fare_search'] = [
            'ok' => $search['ok'],
            'message' => $search['message'] ?? '',
            'solutions' => count($search['solutions'] ?? []),
        ];
        if (! ($search['ok'] ?? false) || empty($search['solutions'])) {
            return $this->failRun($root, $summary, 'Low Fare Search failed or returned no solutions.');
        }

        $lfsXml = (string) session('travelport.last_lfs_xml', '');
        $candidates = $this->orderedConnectingCandidates($search['solutions'], $via);
        if ($candidates === []) {
            $candidates = array_values(array_filter($search['solutions'], 'is_array'));
        }
        $candidates = array_slice($candidates, 0, 8);

        $picked = null;
        $priceXml = '';
        $book = null;
        $lastPriceMsg = '';
        $lastBookMsg = '';

        foreach ($candidates as $i => $candidate) {
            $this->info('Attempt '.($i + 1).'/'.count($candidates).': '.$this->describeSolution($candidate));

            $price = $air->execute('air_price', array_merge($base, [
                'solution_key' => (string) ($candidate['key'] ?? ''),
                '_lfs_xml' => $lfsXml,
            ]));
            $lastPriceMsg = (string) ($price['message'] ?? '');
            if (! ($price['ok'] ?? false)) {
                $this->warn('  Price failed: '.$lastPriceMsg);

                continue;
            }
            $priceXml = (string) session('travelport.last_air_price_xml', '');

            $book = $air->execute('air_create_reservation', array_merge($base, [
                'passengers' => $passengers,
                'form_of_payment' => 'Cash',
                '_air_price_xml' => $priceXml,
            ]));
            $lastBookMsg = (string) ($book['message'] ?? '');
            if (($book['ok'] ?? false) && ! empty($book['universal_locator'])) {
                $picked = $candidate;
                $summary['steps']['air_price'] = ['ok' => true, 'message' => $lastPriceMsg];
                $summary['steps']['air_create_reservation'] = [
                    'ok' => true,
                    'message' => $lastBookMsg,
                    'universal_locator' => $book['universal_locator'] ?? null,
                    'air_reservation_locator' => $book['air_reservation_locator'] ?? null,
                ];
                break;
            }
            $this->warn('  Book failed: '.$lastBookMsg);
        }

        if ($picked === null || $book === null || empty($book['universal_locator'])) {
            $summary['steps']['air_price'] = ['ok' => false, 'message' => $lastPriceMsg];
            $summary['steps']['air_create_reservation'] = [
                'ok' => false,
                'message' => $lastBookMsg,
                'universal_locator' => null,
                'air_reservation_locator' => null,
            ];

            return $this->failRun($root, $summary, 'Booking failed after trying '.count($candidates).' fare(s).');
        }

        $ur = (string) $book['universal_locator'];
        $this->info("  UR locator: {$ur}");

        // 4) Retrieve
        $this->info('4/5 Universal Record Retrieve…');
        $retrieve = $air->execute('universal_record_retrieve', array_merge($base, [
            'universal_locator' => $ur,
        ]));
        $summary['steps']['universal_record_retrieve'] = [
            'ok' => $retrieve['ok'],
            'message' => $retrieve['message'] ?? '',
        ];

        // 5) Optional ticket
        if ($this->option('ticket')) {
            $this->info('5/5 Air Ticketing…');
            $ticket = $air->execute('air_ticketing', array_merge($base, [
                'air_reservation_locator' => (string) ($book['air_reservation_locator'] ?? ''),
                'universal_locator' => $ur,
            ]));
            $summary['steps']['air_ticketing'] = [
                'ok' => $ticket['ok'],
                'message' => $ticket['message'] ?? '',
            ];
        } else {
            $summary['steps']['air_ticketing'] = ['ok' => null, 'message' => 'skipped (pass --ticket to attempt)'];
        }

        // Cleanup
        if (! $this->option('keep')) {
            $this->info('Cancel Universal Record…');
            $cancel = $air->execute('universal_record_cancel', array_merge($base, [
                'universal_locator' => $ur,
                'version' => (string) ($retrieve['version'] ?? $book['version'] ?? '0'),
            ]));
            $summary['steps']['universal_record_cancel'] = [
                'ok' => $cancel['ok'],
                'message' => $cancel['message'] ?? '',
            ];
        }

        TravelportExchangeLogger::stop();
        $summary['ok'] = true;
        $summary['message'] = 'E2E workflow completed.';
        $this->writeArtifacts($root, $summary, $picked);

        $this->newLine();
        $this->info('[OK] Certification run finished.');
        $this->line($root);

        return self::SUCCESS;
    }

    /**
     * Prefer via-hub connections, then any multi-segment solutions.
     *
     * @param  list<array<string, mixed>>  $solutions
     * @return list<array<string, mixed>>
     */
    protected function orderedConnectingCandidates(array $solutions, ?string $via): array
    {
        $viaHits = [];
        $other = [];
        foreach ($solutions as $sol) {
            if (! is_array($sol) || ! $this->solutionHasConnection($sol)) {
                continue;
            }
            $airports = $this->airportsFromSolution($sol);
            if ($via !== null && $via !== '' && in_array($via, $airports, true)) {
                $viaHits[] = $sol;
            } else {
                $other[] = $sol;
            }
        }

        return array_merge($viaHits, $other);
    }

    /**
     * @param  array<string, mixed>  $sol
     */
    protected function solutionHasConnection(array $sol): bool
    {
        if (count($sol['segments'] ?? []) >= 2) {
            return true;
        }
        foreach ($sol['journeys'] ?? [] as $j) {
            if (is_array($j) && count($j['segments'] ?? []) >= 2) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $solutions
     * @return array<string, mixed>|null
     */
    protected function pickConnectingSolution(array $solutions, ?string $via): ?array
    {
        $ordered = $this->orderedConnectingCandidates($solutions, $via);

        return $ordered[0] ?? null;
    }

    /**
     * @param  array<string, mixed>  $sol
     * @return list<string>
     */
    protected function airportsFromSolution(array $sol): array
    {
        $codes = [];
        $segments = $sol['segments'] ?? [];
        if (! is_array($segments) || $segments === []) {
            foreach ($sol['journeys'] ?? [] as $j) {
                if (! is_array($j)) {
                    continue;
                }
                foreach ($j['segments'] ?? [] as $seg) {
                    if (is_array($seg)) {
                        $segments[] = $seg;
                    }
                }
            }
        }
        foreach ($segments as $seg) {
            if (! is_array($seg)) {
                continue;
            }
            foreach (['origin', 'destination'] as $k) {
                $c = strtoupper((string) ($seg[$k] ?? ''));
                if ($c !== '') {
                    $codes[] = $c;
                }
            }
        }

        return array_values(array_unique($codes));
    }

    /**
     * @param  array<string, mixed>  $sol
     */
    protected function describeSolution(array $sol): string
    {
        $parts = [];
        $segments = $sol['segments'] ?? [];
        if ((! is_array($segments) || $segments === []) && is_array($sol['journeys'] ?? null)) {
            foreach ($sol['journeys'] as $j) {
                foreach (($j['segments'] ?? []) as $seg) {
                    $segments[] = $seg;
                }
            }
        }
        foreach ($segments as $seg) {
            if (! is_array($seg)) {
                continue;
            }
            $parts[] = sprintf(
                '%s%s %s-%s',
                $seg['carrier'] ?? '??',
                $seg['flight_number'] ?? '',
                $seg['origin'] ?? '?',
                $seg['destination'] ?? '?'
            );
        }

        return ($sol['total_price'] ?? '?').' | '.implode(' / ', $parts ?: ['(no segment detail)']).' | key='.($sol['key'] ?? '');
    }

    /**
     * @param  array<string, mixed>  $summary
     * @param  array<string, mixed>  $picked
     */
    protected function writeArtifacts(string $root, array $summary, array $picked): void
    {
        File::put($root.'/SUMMARY.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        File::put($root.'/SELECTED_SOLUTION.json', json_encode($picked, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        File::put($root.'/README.txt', implode("\n", [
            'Travelport Universal API — certification E2E RQ/RS (copy / pre-production)',
            'Agency: Wise Trust Travel & Tourism',
            'Generated: '.now()->toDateTimeString(),
            '',
            'Scenario: return journey with connecting flights',
            'Passengers: 1 ADT + 1 CHD (CNN) + 1 INF',
            'Same TraceId on every request (see SUMMARY.json)',
            '',
            'Flow: LowFareSearch → AirPrice → AirCreateReservation → UniversalRecordRetrieve',
            '       → (optional AirTicketing) → UniversalRecordCancel',
            '',
            'Files: NN-operation-RQ.xml / NN-operation-RS.xml / NN-operation-META.json',
        ]));
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    protected function failRun(string $root, array $summary, string $message): int
    {
        TravelportExchangeLogger::stop();
        $summary['ok'] = false;
        $summary['message'] = $message;
        File::put($root.'/SUMMARY.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->error($message);
        $this->line('Partial logs: '.$root);

        return self::FAILURE;
    }
}
