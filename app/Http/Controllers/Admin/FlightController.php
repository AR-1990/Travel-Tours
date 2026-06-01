<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesTravelportAir;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FlightController extends Controller
{
    use HandlesTravelportAir;

    protected function ensureFlightAccess(): void
    {
        $user = Auth::user();
        if (! $user || $user->user_type !== 'super_admin') {
            abort(403, 'Only super admin can access platform flight tools.');
        }
    }

    protected function flightsRoutePrefix(): string
    {
        return 'admin';
    }

    protected function panelLabel(): string
    {
        return 'Super Admin';
    }
}
