<?php

namespace Tests\Feature;

use App\Models\FlightReservation;
use App\Models\Users\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Live Travelport journey: Search → Price → Book → Reservation page.
 * Run manually: php artisan test --group=live-journey
 * Not part of default CI (skipped unless TRAVELPORT_LIVE=1).
 */
#[Group('live-journey')]
class LiveFullJourneyTest extends TestCase
{
    public function test_full_admin_search_price_book_reservation_journey(): void
    {
        if (env('TRAVELPORT_LIVE') !== '1' && getenv('TRAVELPORT_LIVE') !== '1') {
            $this->markTestSkipped('Set TRAVELPORT_LIVE=1 to run the live Travelport journey.');
        }

        $user = User::where('email', 'superadmin@traveltours.com')->first();
        $this->assertNotNull($user, 'Demo super admin missing — run TenantRbacSeeder.');

        $origin = 'LHR';
        $destination = 'JFK';
        $departure = now()->addDays(45)->format('Y-m-d');

        fwrite(STDERR, "\n=== LIVE JOURNEY: {$origin} → {$destination} on {$departure} ===\n");

        // 1) Search
        $search = $this->actingAs($user)->post(route('admin.flights.search'), [
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $departure,
            'adults' => 1,
            'trip_type' => 'oneway',
        ]);
        if ($search->exception) {
            throw $search->exception;
        }
        $search->assertRedirect(route('admin.flights.search', ['page' => 1]));
        $searchStore = session('travelport.flight_search');
        $this->assertIsArray($searchStore, 'Search session missing. Location='.$search->headers->get('Location').' errors='.json_encode(session('errors')));
        $this->assertTrue((bool) ($searchStore['result']['ok'] ?? false), 'Search failed: '.($searchStore['result']['message'] ?? ''));
        $solutions = $searchStore['result']['solutions'] ?? [];
        $this->assertNotEmpty($solutions, 'No fares returned from Low Fare Search.');
        fwrite(STDERR, 'SEARCH OK — '.count($solutions).' solutions (total_found='.($searchStore['result']['total_found'] ?? '?').")\n");

        $booked = false;
        $lastBookError = null;
        $maxAttempts = min(5, count($solutions));

        for ($i = 0; $i < $maxAttempts; $i++) {
            $solutionKey = (string) ($solutions[$i]['key'] ?? '');
            fwrite(STDERR, "PRICE attempt #".($i + 1)." key=".($solutionKey !== '' ? $solutionKey : '(first)')." …\n");

            // 2) Price (auto-redirects to book on success)
            $price = $this->actingAs($user)->post(route('admin.flights.price'), [
                'solution_key' => $solutionKey,
            ]);
            $price->assertRedirect();

            $priceStore = session('travelport.flight_price');
            if (! ($priceStore['result']['ok'] ?? false)) {
                $lastBookError = $priceStore['result']['message'] ?? 'Price failed';
                fwrite(STDERR, "  PRICE FAIL: {$lastBookError}\n");

                continue;
            }
            fwrite(STDERR, '  PRICE OK — '.($priceStore['result']['message'] ?? 'priced')."\n");
            $price->assertRedirect(route('admin.flights.book'));

            // 3) Book page loads
            $this->actingAs($user)->get(route('admin.flights.book'))->assertOk();

            $unique = Str::upper(Str::random(5));
            $book = $this->actingAs($user)->post(route('admin.flights.book.store'), [
                'passenger_prefix' => 'Mr',
                'passenger_first' => 'Journey',
                'passenger_last' => 'Test'.$unique,
                'passenger_gender' => 'M',
                'passenger_dob' => '1990-05-15',
                'passenger_email' => 'journey.test@example.com',
                'passenger_phone' => '447700900123',
                'form_of_payment' => 'Cash',
            ]);

            if ($book->isRedirect() && str_contains((string) $book->headers->get('Location'), '/flights/reservations/')) {
                $book->assertRedirect();
                $location = (string) $book->headers->get('Location');
                fwrite(STDERR, "  BOOK OK → {$location}\n");

                // 4) Reservation show
                $show = $this->actingAs($user)->followRedirects($book);
                $show->assertOk();
                $show->assertSee('Journey');
                $show->assertSee('Test'.$unique);

                $reservationId = (int) session('travelport.last_reservation_id');
                $this->assertGreaterThan(0, $reservationId);
                $reservation = FlightReservation::query()->find($reservationId);
                $this->assertNotNull($reservation);
                $this->assertSame('reserved', $reservation->status);
                $this->assertNotEmpty($reservation->universal_locator ?: $reservation->air_reservation_locator);

                // 5) List includes it
                $list = $this->actingAs($user)->get(route('admin.flights.reservations.index'));
                $list->assertOk();
                $list->assertSee($reservation->universal_locator ?? $reservation->air_reservation_locator);

                // 6) Confirmation shortcut redirects to show
                $confirm = $this->actingAs($user)->get(route('admin.flights.confirmation'));
                $confirm->assertRedirect(route('admin.flights.reservations.show', $reservation));

                fwrite(STDERR, "RESERVATION #{$reservation->id}\n");
                fwrite(STDERR, '  UR='.($reservation->universal_locator ?? '—')."\n");
                fwrite(STDERR, '  Air='.($reservation->air_reservation_locator ?? '—')."\n");
                fwrite(STDERR, '  Route='.$reservation->origin.'→'.$reservation->destination."\n");
                fwrite(STDERR, "=== JOURNEY PASSED ===\n");

                $booked = true;
                break;
            }

            $lastBookError = session('error') ?? 'Book did not redirect to reservation';
            fwrite(STDERR, "  BOOK FAIL: {$lastBookError}\n");
            if (session('travelport_last_error_reason')) {
                fwrite(STDERR, '  reason: '.session('travelport_last_error_reason')."\n");
            }
        }

        $this->assertTrue($booked, 'Could not complete book after '.$maxAttempts.' fare attempts. Last error: '.$lastBookError);
    }
}
