<?php

namespace App\Console\Commands;

use App\Services\DowntownTravel\DowntownTravelAirService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Downtown Travel sandbox certification scenarios (air):
 * 1) 1 ADT OW → book → cancel (no ticket)
 * 2) 2 ADT + 1 CHD + 1 INF RT → book with wrong expected_agent_net_price → confirm → ticket → void
 * 3) 2 ADT OW → book → issue with wrong expected_agent_net_price → confirm issue
 */
class DowntownTravelCertRunCommand extends Command
{
    protected $signature = 'downtown:cert-run
        {--origin=NYC : Origin city/airport IATA}
        {--destination=ZRH : Destination city/airport IATA}
        {--depart= : Departure date Y-m-d (default +21 days)}
        {--return= : Return date Y-m-d (default +28 days)}
        {--only= : Run only case 1, 2, or 3}';

    protected $description = 'Run Downtown Travel air certification cases and print order IDs.';

    public function handle(DowntownTravelAirService $air): int
    {
        if (! $air->isReady()) {
            $this->error('Downtown Travel Air is not ready. Configure Admin → Integrations → Downtown Travel.');

            return self::FAILURE;
        }

        $root = storage_path('app/downtown-travel-cert/'.now()->format('Ymd-His'));
        File::ensureDirectoryExists($root);

        $origin = strtoupper((string) $this->option('origin'));
        $destination = strtoupper((string) $this->option('destination'));
        $depart = (string) ($this->option('depart') ?: now()->addDays(21)->format('Y-m-d'));
        $return = (string) ($this->option('return') ?: now()->addDays(28)->format('Y-m-d'));
        $only = $this->option('only') !== null ? (int) $this->option('only') : null;

        $summaries = [];

        if ($only === null || $only === 1) {
            $this->info('=== Case 1: 1 ADT one-way → book → cancel (no ticket) ===');
            $summaries[] = $this->runCase1($air, $root, $origin, $destination, $depart);
        }
        if ($only === null || $only === 2) {
            $this->info('=== Case 2: Family RT → wrong book price → confirm → ticket → void ===');
            $summaries[] = $this->runCase2($air, $root, $origin, $destination, $depart, $return);
        }
        if ($only === null || $only === 3) {
            $this->info('=== Case 3: 2 ADT → book → wrong issue price → confirm issue ===');
            $summaries[] = $this->runCase3($air, $root, $origin, $destination, $depart);
        }

        File::put($root.'/SUMMARY.json', json_encode($summaries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->newLine();
        $this->info('SUMMARY (order IDs for Downtown Travel):');
        foreach ($summaries as $row) {
            $this->line(sprintf(
                '  Case %s: order_id=%s readable_id=%s ok=%s — %s',
                $row['case'] ?? '?',
                $row['order_id'] ?? '(none)',
                $row['readable_id'] ?? '(none)',
                ! empty($row['ok']) ? 'YES' : 'NO',
                $row['message'] ?? ''
            ));
        }
        $this->info('Logs: '.$root);

        $failed = collect($summaries)->contains(fn (array $r): bool => empty($r['ok']));

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    protected function runCase1(
        DowntownTravelAirService $air,
        string $root,
        string $origin,
        string $destination,
        string $depart
    ): array {
        $dir = $root.'/01-one-adult-ow-cancel';
        File::ensureDirectoryExists($dir);

        $search = $this->searchAndPrice($air, [
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $depart,
            'trip_type' => 'oneway',
            'adults' => 1,
            'children' => 0,
            'infants' => 0,
        ], $dir);
        if (! ($search['ok'] ?? false)) {
            return $this->failSummary(1, $search['message'] ?? 'Search failed', $dir);
        }

        $passengers = [
            $this->pax('ADT', 'Ada', 'Certone', '1990-03-12', 'F', 'US'),
        ];
        $book = $air->book([
            'email' => 'cert.case1@example.com',
            'phone' => '+14057787503',
            'passengers' => $passengers,
        ]);
        File::put($dir.'/03-Book.json', json_encode($book, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if (! ($book['ok'] ?? false)) {
            return $this->failSummary(1, $book['message'] ?? 'Book failed', $dir, $book);
        }

        $orderId = (string) ($book['order_id'] ?? $book['universal_locator'] ?? '');
        $recordId = (string) ($book['booking_record_id'] ?? '');
        if ($recordId === '' && $orderId !== '') {
            $order = $air->getOrder($orderId);
            File::put($dir.'/04-GetOrder.json', json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $recordId = (string) ($order['booking_record_id'] ?? '');
        }

        $cancel = $air->cancelBookingRecord($recordId);
        File::put($dir.'/05-Cancel.json', json_encode($cancel, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $ok = ($cancel['ok'] ?? false);
        $msg = $ok
            ? 'Booked then cancelled without ticketing.'
            : ('Booked but cancel failed: '.($cancel['message'] ?? ''));

        return [
            'case' => 1,
            'ok' => $ok,
            'order_id' => $orderId,
            'readable_id' => $book['readable_id'] ?? null,
            'booking_record_id' => $recordId,
            'message' => $msg,
            'dir' => $dir,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function runCase2(
        DowntownTravelAirService $air,
        string $root,
        string $origin,
        string $destination,
        string $depart,
        string $return
    ): array {
        $dir = $root.'/02-family-rt-price-change-void';
        File::ensureDirectoryExists($dir);

        $search = $this->searchAndPrice($air, [
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $depart,
            'return_date' => $return,
            'trip_type' => 'roundtrip',
            'adults' => 2,
            'children' => 1,
            'infants' => 1,
        ], $dir);
        if (! ($search['ok'] ?? false)) {
            return $this->failSummary(2, $search['message'] ?? 'Search failed', $dir);
        }

        $agentNet = (float) data_get($search, 'solution.agent_net_total', data_get($search, 'solution.total_amount', 0));
        $wrongNet = max(1.0, round($agentNet - 25.0, 2));
        if ($wrongNet === round($agentNet, 2)) {
            $wrongNet = max(1.0, round($agentNet * 0.5, 2));
        }

        $passengers = [
            $this->pax('ADT', 'Adam', 'Certtwoa', '1985-01-15', 'M', 'US'),
            $this->pax('ADT', 'Beth', 'Certtwob', '1988-06-20', 'F', 'US'),
            // Infant must follow accompanying adult so mapPassengers nests it correctly.
            $this->pax('INF', 'Dan', 'Certtwoa', now()->subMonths(8)->format('Y-m-d'), 'M', 'US'),
            $this->pax('CHD', 'Cara', 'Certtwoc', now()->subYears(6)->format('Y-m-d'), 'F', 'US'),
        ];

        File::put($dir.'/03-WrongPrice.txt', "correct_agent_net={$agentNet}\nwrong_expected_agent_net_price={$wrongNet}\n");

        $book = $air->book([
            'email' => 'cert.case2@example.com',
            'phone' => '+14057787503',
            'expected_agent_net_price' => $wrongNet,
            'passengers' => $passengers,
        ]);
        File::put($dir.'/04-Book.json', json_encode($book, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if (! ($book['ok'] ?? false)) {
            return $this->failSummary(2, $book['message'] ?? 'Book (price change) failed', $dir, $book);
        }

        $orderId = (string) ($book['order_id'] ?? $book['universal_locator'] ?? '');
        $recordId = (string) ($book['booking_record_id'] ?? '');
        if ($recordId === '' && $orderId !== '') {
            $order = $air->getOrder($orderId);
            File::put($dir.'/05-GetOrder.json', json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $recordId = (string) ($order['booking_record_id'] ?? '');
        }

        $issue = $air->issueTickets([
            'booking_record_id' => $recordId,
            'order_id' => $orderId,
            'email' => 'cert.case2@example.com',
            'phone' => '+14057787503',
            'passengers' => $passengers,
        ]);
        File::put($dir.'/06-Issue.json', json_encode($issue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if (! ($issue['ok'] ?? false)) {
            return array_merge($this->failSummary(2, $issue['message'] ?? 'Issue failed', $dir, $book), [
                'order_id' => $orderId,
                'readable_id' => $book['readable_id'] ?? null,
                'booking_record_id' => $recordId,
            ]);
        }

        // Issue is async (processing_issue → issued). Poll until can_void before voiding.
        $order = null;
        $ticketNumbers = $issue['ticket_numbers'] ?? [];
        for ($attempt = 1; $attempt <= 30; $attempt++) {
            if ($orderId === '') {
                break;
            }
            $order = $air->getOrder($orderId);
            File::put($dir.'/07-GetOrder-AfterIssue.json', json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $recordId = (string) ($order['booking_record_id'] ?? $recordId);
            $ticketNumbers = $order['ticket_numbers'] ?? $ticketNumbers;
            if (! empty($order['can_void'])) {
                break;
            }
            sleep(5);
        }

        $void = $air->voidBookingRecord($recordId);
        File::put($dir.'/08-Void.json', json_encode($void, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $ok = ($void['ok'] ?? false);
        $msg = sprintf(
            'price_changed_on_book=%s; ticketed; void=%s',
            ! empty($book['price_changed']) ? 'yes' : 'no',
            $ok ? 'ok' : ($void['message'] ?? 'failed')
        );

        return [
            'case' => 2,
            'ok' => $ok,
            'order_id' => $orderId,
            'readable_id' => $book['readable_id'] ?? null,
            'booking_record_id' => $recordId,
            'price_changed_on_book' => (bool) ($book['price_changed'] ?? false),
            'ticket_numbers' => $ticketNumbers,
            'message' => $msg,
            'dir' => $dir,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function runCase3(
        DowntownTravelAirService $air,
        string $root,
        string $origin,
        string $destination,
        string $depart
    ): array {
        $dir = $root.'/03-two-adults-issue-price-change';
        File::ensureDirectoryExists($dir);

        $search = $this->searchAndPrice($air, [
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $depart,
            'trip_type' => 'oneway',
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
        ], $dir);
        if (! ($search['ok'] ?? false)) {
            return $this->failSummary(3, $search['message'] ?? 'Search failed', $dir);
        }

        $passengers = [
            $this->pax('ADT', 'Eve', 'Certthreea', '1987-04-04', 'F', 'US'),
            $this->pax('ADT', 'Frank', 'Certthreeb', '1984-09-09', 'M', 'US'),
        ];

        $book = $air->book([
            'email' => 'cert.case3@example.com',
            'phone' => '+14057787503',
            'passengers' => $passengers,
        ]);
        File::put($dir.'/03-Book.json', json_encode($book, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if (! ($book['ok'] ?? false)) {
            return $this->failSummary(3, $book['message'] ?? 'Book failed', $dir, $book);
        }

        $orderId = (string) ($book['order_id'] ?? $book['universal_locator'] ?? '');
        $recordId = (string) ($book['booking_record_id'] ?? '');
        $agentNet = (float) data_get($search, 'solution.agent_net_total', data_get($search, 'solution.total_amount', 0));
        if ($orderId !== '') {
            $order = $air->getOrder($orderId);
            File::put($dir.'/04-GetOrder.json', json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $recordId = (string) ($order['booking_record_id'] ?? $recordId);
            $agentNet = (float) (
                data_get($order, 'order.sell_invoices.0.components.tickets.0.items.0.agent_net_total')
                ?? data_get($order, 'raw.sell_invoices.0.components.tickets.0.items.0.agent_net_total')
                ?? $agentNet
            );
        }

        $wrongNet = max(1.0, round($agentNet - 15.0, 2));
        if ($wrongNet === round($agentNet, 2)) {
            $wrongNet = max(1.0, round($agentNet * 0.5, 2));
        }
        File::put($dir.'/05-WrongIssuePrice.txt', "agent_net≈{$agentNet}\nwrong_expected_agent_net_price={$wrongNet}\n");

        $issue = $air->issueTickets([
            'booking_record_id' => $recordId,
            'order_id' => $orderId,
            'email' => 'cert.case3@example.com',
            'phone' => '+14057787503',
            'expected_agent_net_price' => $wrongNet,
            'passengers' => $passengers,
        ]);
        File::put($dir.'/06-Issue.json', json_encode($issue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $orderAfterIssue = $orderId !== ''
            ? $air->getOrder($orderId)
            : ['ok' => false, 'message' => 'Missing order id after issue'];
        File::put($dir.'/07-GetOrder-AfterIssue.json', json_encode($orderAfterIssue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $ok = (bool) ($issue['ok'] ?? false) && (bool) ($orderAfterIssue['ok'] ?? false);

        return [
            'case' => 3,
            'ok' => $ok,
            'order_id' => $orderId,
            'readable_id' => $book['readable_id'] ?? null,
            'booking_record_id' => $recordId,
            'ticket_numbers' => $issue['ticket_numbers'] ?? [],
            'message' => $ok
                ? 'Booked then issued after price_changed on issue.'
                : ($issue['message'] ?? 'Issue failed'),
            'dir' => $dir,
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{ok: bool, message?: string, solution?: array<string, mixed>}
     */
    protected function searchAndPrice(DowntownTravelAirService $air, array $params, string $dir): array
    {
        $routes = [
            [$params['origin'], $params['destination']],
            ['NYC', 'ZRH'],
            ['JFK', 'LHR'],
            ['LHR', 'JFK'],
            ['DXB', 'LHR'],
        ];

        $lastMessage = 'No inventory';
        foreach ($routes as [$origin, $destination]) {
            $attempt = array_merge($params, [
                'origin' => $origin,
                'destination' => $destination,
            ]);
            $this->line("  Searching {$origin}→{$destination} …");
            $search = $air->lowFareSearch($attempt);
            $searchRequest = session('downtown_travel.last_search.request');
            if (is_array($searchRequest)) {
                File::put(
                    $dir.'/00-SearchRequest-'.$origin.'-'.$destination.'.json',
                    json_encode($searchRequest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                );
            }
            File::put(
                $dir.'/01-Search-'.$origin.'-'.$destination.'.json',
                json_encode($search, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
            $solutions = is_array($search['solutions'] ?? null) ? $search['solutions'] : [];
            if (! ($search['ok'] ?? false) || $solutions === []) {
                $lastMessage = (string) ($search['message'] ?? 'No solutions');

                continue;
            }

            $solution = $solutions[0];
            $price = $air->airPrice([
                'solution_key' => (string) ($solution['key'] ?? ''),
                'adults' => (int) ($params['adults'] ?? 1),
                'children' => (int) ($params['children'] ?? 0),
                'infants' => (int) ($params['infants'] ?? 0),
            ]);
            File::put($dir.'/02-Price.json', json_encode([
                'route' => $origin.'-'.$destination,
                'solution' => $solution,
                'price' => $price,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            if (! ($price['ok'] ?? false)) {
                $lastMessage = (string) ($price['message'] ?? 'Price failed');

                continue;
            }

            return [
                'ok' => true,
                'solution' => $solution,
                'message' => 'Found fare '.$origin.'→'.$destination,
            ];
        }

        return ['ok' => false, 'message' => $lastMessage];
    }

    /**
     * @return array<string, mixed>
     */
    protected function pax(
        string $type,
        string $first,
        string $last,
        string $dob,
        string $gender,
        string $nationality
    ): array {
        return [
            'type' => $type,
            'first' => $first,
            'last' => $last,
            'dob' => $dob,
            'gender' => $gender,
            'nationality' => $nationality,
            'email' => 'cert@example.com',
            'phone' => '+14057787503',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $book
     * @return array<string, mixed>
     */
    protected function failSummary(int $case, string $message, string $dir, ?array $book = null): array
    {
        $this->error("  Case {$case} failed: {$message}");

        return [
            'case' => $case,
            'ok' => false,
            'order_id' => $book['order_id'] ?? $book['universal_locator'] ?? null,
            'readable_id' => $book['readable_id'] ?? null,
            'message' => $message,
            'dir' => $dir,
        ];
    }
}
