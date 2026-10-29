<?php

namespace Tests\Unit;

use App\Http\Controllers\Concerns\HandlesFlightWorkflow;
use ReflectionMethod;
use Tests\TestCase;

class FlightPassengerSlotsTest extends TestCase
{
    public function test_passenger_slots_include_adults_children_and_infants(): void
    {
        $controller = new class
        {
            use HandlesFlightWorkflow;

            protected function flightsRoutePrefix(): string
            {
                return 'admin';
            }
        };

        $method = new ReflectionMethod($controller, 'passengerSlotsFromSearch');
        $method->setAccessible(true);

        $slots = $method->invoke($controller, [
            'adults' => 3,
            'children' => 1,
            'infants' => 1,
        ]);

        $this->assertCount(5, $slots);
        $this->assertSame(['ADT', 'ADT', 'ADT', 'CHD', 'INF'], array_column($slots, 'type'));
        $this->assertSame('Adult 1', $slots[0]['label']);
        $this->assertSame('Child 1', $slots[3]['label']);
        $this->assertSame('Infant 1', $slots[4]['label']);
    }
}
