<?php

namespace App\Http\Controllers\SubAgent;

use App\Http\Controllers\Concerns\HandlesTravelportAir;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FlightController extends Controller
{
    use HandlesTravelportAir;

    protected function ensureFlightAccess(): void
    {
        $user = Auth::user();
        if (! $user || $user->user_type !== 'sub_agent') {
            abort(403, 'Sub-agent access only.');
        }

        if (! $user->hasPermission('flights.search')) {
            abort(403, 'You do not have permission to access flights.');
        }
    }

    protected function flightsRoutePrefix(): string
    {
        return 'subagent';
    }

    protected function panelLabel(): string
    {
        return 'Sub Agent';
    }
}
