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
        if (! $user || ! in_array($user->user_type, ['tenant_admin', 'sub_agent'], true)) {
            abort(403);
        }

        if ($user->user_type === 'tenant_admin') {
            return;
        }

        if (! $user->hasPermission('flights.search')) {
            abort(403, 'You do not have permission to access flights.');
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
