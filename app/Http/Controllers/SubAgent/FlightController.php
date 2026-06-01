<?php

namespace App\Http\Controllers\SubAgent;

use App\Http\Controllers\Agent\FlightController as AgentFlightController;

class FlightController extends AgentFlightController
{
    protected function flightsRoutePrefix(): string
    {
        return 'subagent';
    }

    protected function panelLabel(): string
    {
        return 'Sub Agent';
    }
}
