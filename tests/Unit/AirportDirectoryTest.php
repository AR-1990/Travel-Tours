<?php

namespace Tests\Unit;

use App\Support\AirportDirectory;
use Tests\TestCase;

class AirportDirectoryTest extends TestCase
{
    public function test_directory_includes_major_world_airports(): void
    {
        $this->assertGreaterThan(4000, AirportDirectory::count());

        $ord = AirportDirectory::find('ORD');
        $this->assertNotNull($ord);
        $this->assertStringContainsString('Chicago', $ord['label']);
        $this->assertStringContainsString('ORD', $ord['label']);

        $cdg = AirportDirectory::find('CDG');
        $this->assertStringContainsString('Paris', $cdg['label']);

        $results = AirportDirectory::search('chicago', 10);
        $this->assertNotEmpty($results);
        $this->assertTrue(collect($results)->contains(fn ($r) => $r['code'] === 'ORD' || $r['code'] === 'CHI'));
    }

    public function test_isb_is_islamabad_not_attock(): void
    {
        $isb = AirportDirectory::find('ISB');
        $this->assertNotNull($isb);
        $this->assertSame('Islamabad', $isb['city']);
        $this->assertStringContainsString('Islamabad', $isb['label']);
        $this->assertStringNotContainsString('Attock', $isb['label']);

        $byCode = AirportDirectory::search('ISB', 5);
        $this->assertCount(1, $byCode);
        $this->assertSame('ISB', $byCode[0]['code']);
        $this->assertSame('Islamabad', $byCode[0]['city']);

        $byCity = AirportDirectory::search('Islamabad', 5);
        $this->assertSame('ISB', $byCity[0]['code']);
    }

    public function test_exact_iata_codes_resolve_cleanly(): void
    {
        $cases = [
            'KHI' => 'Karachi',
            'LHE' => 'Lahore',
            'DXB' => 'Dubai',
            'LHR' => 'London',
            'JFK' => 'New York',
            'DEL' => 'New Delhi',
            'CGK' => 'Jakarta',
            'VVO' => 'Vladivostok',
        ];

        foreach ($cases as $code => $city) {
            $found = AirportDirectory::find($code);
            $this->assertSame($city, $found['city'], "City for {$code}");

            $search = AirportDirectory::search($code, 3);
            $this->assertSame($code, $search[0]['code'], "Search top hit for {$code}");
            $this->assertSame($city, $search[0]['city'], "Search city for {$code}");
        }
    }

    public function test_city_name_searches_rank_expected_airports(): void
    {
        $this->assertSame('DXB', AirportDirectory::search('Dubai', 1)[0]['code']);
        $this->assertSame('KHI', AirportDirectory::search('Karachi', 1)[0]['code']);
        $this->assertTrue(in_array(AirportDirectory::search('London', 5)[0]['code'], ['LHR', 'LGW', 'LON', 'STN', 'LTN', 'LCY'], true));
        $this->assertTrue(in_array(AirportDirectory::search('New York', 5)[0]['code'], ['JFK', 'LGA', 'EWR', 'NYC'], true));
    }

    public function test_short_query_does_not_match_midstring_noise(): void
    {
        $results = AirportDirectory::search('ISB', 10);
        $codes = array_column($results, 'code');
        $this->assertSame(['ISB'], $codes);
        $this->assertNotContains('BNE', $codes);
        $this->assertNotContains('LIS', $codes);
    }
}
