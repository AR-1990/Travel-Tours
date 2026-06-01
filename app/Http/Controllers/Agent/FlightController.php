<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Concerns\HandlesTravelportAir;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FlightController extends Controller
{
    use HandlesTravelportAir;

    protected function ensureFlightAccess(): void
    {
        $user = Auth::user();
        if (! $user || $user->user_type !== 'tenant_admin') {
            abort(403, 'Tenant admin access only.');
        }
    }

    protected function flightsRoutePrefix(): string
    {
        return 'agent';
    }

    protected function panelLabel(): string
    {
        return 'Agent';
    }
}
