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
}
