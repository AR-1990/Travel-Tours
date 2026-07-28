<?php

namespace Tests\Unit;

use App\Http\Controllers\Concerns\NormalizesFlightSearchInput;
use Illuminate\Http\Request;
use Tests\TestCase;

class NormalizesFlightSearchInputTest extends TestCase
{
    use NormalizesFlightSearchInput;

    public function test_multicity_normalizes_legs(): void
    {
        $request = Request::create('/flights/search', 'POST', [
            'trip_type' => 'multi-city',
            'adults' => 2,
            'legs' => [
                ['origin' => 'LHR', 'destination' => 'CDG', 'departure_date' => '09/01/2026'],
                ['origin' => 'Paris — Charles de Gaulle (CDG)', 'destination' => 'JFK', 'departure_date' => '2026-09-05'],
            ],
        ]);

        $input = $this->validatedFlightSearchInput($request);

        $this->assertNotNull($input);
        $this->assertSame('multicity', $input['trip_type']);
        $this->assertSame('LHR', $input['origin']);
        $this->assertSame('JFK', $input['destination']);
        $this->assertCount(2, $input['legs']);
        $this->assertSame('2026-09-01', $input['legs'][0]['departure_date']);
        $this->assertSame(2, $input['adults']);
    }

    public function test_multicity_requires_two_legs(): void
    {
        $request = Request::create('/flights/search', 'POST', [
            'trip_type' => 'multicity',
            'legs' => [
                ['origin' => 'LHR', 'destination' => 'CDG', 'departure_date' => '2026-09-01'],
            ],
        ]);

        $this->assertNull($this->validatedFlightSearchInput($request));
    }
}
