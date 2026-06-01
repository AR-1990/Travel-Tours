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
        ];

        $readyOps = [
            'low_fare_search',
            'availability_search',
            'air_fare_display',
            'flight_time_table',
            'air_fare_rules',
            'seat_map',
            'universal_record_retrieve',
            'universal_record_cancel',
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
