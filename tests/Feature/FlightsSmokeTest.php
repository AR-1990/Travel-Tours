<?php

namespace Tests\Feature;

use App\Models\Users\User;
use App\Services\Travelport\TravelportAirXmlBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FlightsSmokeTest extends TestCase
{
    #[DataProvider('operationKeysProvider')]
    public function test_admin_operation_pages_render(string $operation): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $response = $this->actingAs($user)->get(route('admin.flights.operation', ['operation' => $operation]));

        if ($operation === 'low_fare_search') {
            $response->assertRedirect(route('admin.flights.search'));
        } elseif ($operation === 'air_create_reservation') {
            $response->assertRedirect(route('admin.flights.book'));
        } else {
            $response->assertOk();
        }
    }

    public function test_admin_flights_hub_and_search_render(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->actingAs($user)->get(route('admin.flights.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.flights.search'))->assertOk();
    }

    public function test_agent_and_sub_agent_flights_hub_render(): void
    {
        $tenantAdmin = User::where('email', 'tenantadmin@traveltours.com')->first();
        $salesAgent = User::where('email', 'sales.agent@traveltours.com')->first();

        if (! $tenantAdmin || ! $salesAgent) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->actingAs($tenantAdmin)->get(route('agent.flights.index'))->assertOk();
        $this->actingAs($salesAgent)->get(route('subagent.flights.index'))->assertOk();
    }

    public function test_airport_api_returns_json_publicly(): void
    {
        $this->getJson(route('api.airports.search', ['q' => 'London']))
            ->assertOk()
            ->assertJsonStructure(['results']);

        $this->getJson(route('api.airports.search', ['q' => 'ORD']))
            ->assertOk()
            ->assertJsonFragment(['code' => 'ORD']);
    }

    public function test_airport_api_returns_json_when_authenticated(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->actingAs($user)
            ->getJson(route('api.airports.search', ['q' => 'London']))
            ->assertOk()
            ->assertJsonStructure(['results']);
    }

    public function test_ready_operations_build_xml(): void
    {
        $builder = new TravelportAirXmlBuilder;
        $params = [
            'origin' => 'LHR',
            'destination' => 'JFK',
            'departure_date' => now()->addDays(30)->format('Y-m-d'),
            'return_date' => now()->addDays(37)->format('Y-m-d'),
            'adults' => 1,
            'universal_locator' => 'TEST12',
            'fare_basis' => 'Y26',
            'carrier' => 'BA',
            'flight_number' => '117',
            'departure_time' => now()->addDays(30)->format('Y-m-d').'T10:00:00',
            '_air_pricing_solution_xml' => '      <air:AirPricingSolution Key="s1" TotalPrice="GBP100"/>',
            '_pricing_solution_xml' => '      <air:AirPricingSolution Key="s1"><air:AirSegmentRef Key="seg1"/></air:AirPricingSolution>',
            '_lfs_xml' => '<air:LowFareSearchRsp xmlns:air="http://www.travelport.com/schema/air_v52_0"><air:AirPricingSolution Key="s1"><air:AirSegmentRef Key="seg1"/></air:AirPricingSolution><air:AirSegment Key="seg1" Carrier="BA" FlightNumber="117" Origin="LHR" Destination="JFK" DepartureTime="2026-08-01T10:00:00"/></air:LowFareSearchRsp>',
            'passengers' => [[
                'prefix' => 'Mr', 'first' => 'Test', 'last' => 'User',
                'email' => 't@example.com', 'phone' => '555', 'dob' => '1990-01-01', 'gender' => 'M', 'type' => 'ADT',
            ]],
        ];

        $readyOps = [
            'low_fare_search',
            'low_fare_search_async',
            'availability_search',
            'air_fare_display',
            'flight_time_table',
            'flight_details',
            'flight_information',
            'air_fare_rules',
            'seat_map',
            'air_reprice',
            'air_create_reservation',
            'universal_record_retrieve',
            'universal_record_cancel',
            'universal_record_modify',
            'air_ticketing',
            'air_retrieve_document',
            'air_void_ticket',
            'air_refund_quote',
            'air_refund',
            'air_cancel',
            'air_exchange_quote',
            'air_exchange',
            'air_exchange_ticketing',
            'air_merchandising',
            'air_pre_pay',
        ];

        foreach ($readyOps as $key) {
            $xml = $builder->build($key, $params, 52);
            $this->assertNotEmpty($xml, "No XML for: {$key}");
            $this->assertStringContainsString('soapenv:Envelope', $xml);
        }
    }

    public function test_unknown_operation_returns_404(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->actingAs($user)
            ->get(route('admin.flights.operation', ['operation' => 'not_a_real_op']))
            ->assertNotFound();
    }

    public function test_public_flight_results_redirects_without_session(): void
    {
        $this->get(route('frontend.flights.results'))
            ->assertRedirect(route('home'));
    }

    public function test_public_flight_price_redirects_without_session(): void
    {
        $this->get(route('frontend.flights.price.show'))
            ->assertRedirect(route('frontend.flights.results'));
    }

    public function test_public_flight_operation_pages_render(): void
    {
        $ops = array_keys(\App\Services\Travelport\TravelportAirCatalog::operations());
        foreach ($ops as $operation) {
            if ($operation === 'low_fare_search') {
                $this->get(route('frontend.flights.operation', ['operation' => $operation]))
                    ->assertRedirect(route('home'));
                continue;
            }
            if ($operation === 'air_create_reservation') {
                $this->get(route('frontend.flights.operation', ['operation' => $operation]))
                    ->assertRedirect(route('frontend.flights.book'));
                continue;
            }
            $this->get(route('frontend.flights.operation', ['operation' => $operation]))
                ->assertOk();
        }
    }

    public function test_public_flight_hub_renders(): void
    {
        $this->get(route('frontend.flights.hub'))->assertOk();
    }

    public function test_public_workflow_routes_redirect_without_session(): void
    {
        $this->get(route('frontend.flights.book'))->assertRedirect(route('frontend.flights.price.show'));
        $this->get(route('frontend.flights.confirmation'))->assertRedirect(route('frontend.flights.results'));
        $this->post(route('frontend.flights.ticket'))->assertRedirect(route('frontend.flights.confirmation'));
    }

    public function test_panel_workflow_routes_redirect_without_session(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->actingAs($user)->get(route('admin.flights.price.show'))
            ->assertRedirect(route('admin.flights.search'));
        $this->actingAs($user)->get(route('admin.flights.book'))
            ->assertRedirect(route('admin.flights.price.show'));
        $this->actingAs($user)->get(route('admin.flights.confirmation'))
            ->assertRedirect(route('admin.flights.reservations.index'));
    }

    public function test_reservation_list_and_show_render(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $reservation = \App\Models\FlightReservation::query()->create([
            'user_id' => $user->id,
            'channel' => 'admin',
            'status' => \App\Models\FlightReservation::STATUS_RESERVED,
            'universal_locator' => 'ABC123',
            'air_reservation_locator' => 'XYZ987',
            'origin' => 'LHR',
            'destination' => 'JFK',
            'departure_date' => now()->addDays(30)->toDateString(),
            'adults' => 1,
            'passenger_first' => 'Test',
            'passenger_last' => 'Traveler',
            'passenger_email' => 'test@example.com',
            'booked_at' => now(),
        ]);

        $this->actingAs($user)->get(route('admin.flights.reservations.index'))->assertOk();
        $this->actingAs($user)
            ->get(route('admin.flights.reservations.show', $reservation))
            ->assertOk()
            ->assertSee('ABC123')
            ->assertSee('Test Traveler');

        session(['public.last_reservation_id' => $reservation->id]);
        $this->get(route('frontend.flights.reservations.show', $reservation))
            ->assertOk()
            ->assertSee('ABC123');
    }

    public function test_sub_agent_without_book_permission_cannot_access_book_page(): void
    {
        $salesAgent = User::where('email', 'sales.agent@traveltours.com')->first();
        if (! $salesAgent) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        session([
            'travelport.flight_price' => [
                'result' => ['ok' => true, 'solutions' => [['total_price' => 'USD100']]],
            ],
            'travelport.last_air_price_xml' => '<air:AirPriceRsp/>',
        ]);

        if ($salesAgent->hasPermission('flights.book')) {
            $this->actingAs($salesAgent)->get(route('subagent.flights.book'))->assertOk();
        } else {
            $this->actingAs($salesAgent)->get(route('subagent.flights.book'))->assertForbidden();
        }
    }

    public function test_parse_ticket_numbers(): void
    {
        $parser = new \App\Services\Travelport\TravelportFlightParser;
        $xml = '<air:AirRetrieveDocumentRsp><air:ETR TicketNumber="1234567890123"/></air:AirRetrieveDocumentRsp>';
        $numbers = $parser->parseTicketNumbers($xml);
        $this->assertContains('1234567890123', $numbers);
    }

    public function test_public_unknown_flight_operation_returns_404(): void
    {
        $this->get(route('frontend.flights.operation', ['operation' => 'not_a_real_op']))
            ->assertNotFound();
    }

  /**
     * @return array<string, array{0: string}>
     */
    public static function operationKeysProvider(): array
    {
        return [
            'low_fare_search' => ['low_fare_search'],
            'availability_search' => ['availability_search'],
            'air_fare_display' => ['air_fare_display'],
            'flight_time_table' => ['flight_time_table'],
            'air_price' => ['air_price'],
            'air_fare_rules' => ['air_fare_rules'],
            'seat_map' => ['seat_map'],
            'universal_record_retrieve' => ['universal_record_retrieve'],
            'universal_record_cancel' => ['universal_record_cancel'],
            'air_ticketing' => ['air_ticketing'],
            'air_create_reservation' => ['air_create_reservation'],
        ];
    }
}
