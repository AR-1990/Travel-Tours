<?php

namespace Tests\Unit;

use App\Support\SunSpringAirports;
use Tests\TestCase;

class SunSpringAirportsTest extends TestCase
{
    public function test_allow_list_includes_core_sepehran_hubs(): void
    {
        $this->assertTrue(SunSpringAirports::isAllowed('THR'));
        $this->assertTrue(SunSpringAirports::isAllowed('MHD'));
        $this->assertTrue(SunSpringAirports::isAllowed('SYZ'));
        $this->assertFalse(SunSpringAirports::isAllowed('JFK'));
        $this->assertFalse(SunSpringAirports::isAllowed('LHR'));
    }

    public function test_supports_search_only_for_network_airports(): void
    {
        $this->assertTrue(SunSpringAirports::supportsSearch([
            'origin' => 'THR',
            'destination' => 'MHD',
        ]));
        $this->assertFalse(SunSpringAirports::supportsSearch([
            'origin' => 'JFK',
            'destination' => 'LAX',
        ]));
        $this->assertTrue(SunSpringAirports::supportsSearch([
            'legs' => [
                ['origin' => 'THR', 'destination' => 'SYZ'],
                ['origin' => 'SYZ', 'destination' => 'MHD'],
            ],
        ]));
    }

    public function test_search_filters_to_allowed_airports(): void
    {
        $results = SunSpringAirports::search('teh', 10);
        $this->assertNotEmpty($results);
        foreach ($results as $row) {
            $this->assertTrue(SunSpringAirports::isAllowed((string) $row['code']));
        }

        $london = SunSpringAirports::search('London', 10);
        $this->assertSame([], $london);
    }
}
