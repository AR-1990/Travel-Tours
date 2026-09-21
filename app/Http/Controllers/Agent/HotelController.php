<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Concerns\ManagesHotelReservations;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HotelController extends Controller
{
    use ManagesHotelReservations;

    protected function ensureHotelAccess(): void
    {
        $user = Auth::user();
        if (! $user || $user->user_type !== 'tenant_admin') {
            abort(403, 'Tenant admin access only.');
        }
    }

    protected function hotelsRoutePrefix(): string
    {
        return 'agent';
    }

    protected function panelLabel(): string
    {
        return 'Agent';
    }
}
