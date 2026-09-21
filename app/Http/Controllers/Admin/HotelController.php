<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesHotelWorkflow;
use App\Http\Controllers\Concerns\ManagesHotelReservations;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HotelController extends Controller
{
    use HandlesHotelWorkflow;
    use ManagesHotelReservations;

    protected function ensureHotelAccess(): void
    {
        $user = Auth::user();
        if (! $user || $user->user_type !== 'super_admin') {
            abort(403, 'Only super admin can access platform hotel tools.');
        }
    }

    protected function hotelsRoutePrefix(): string
    {
        return 'admin';
    }

    protected function panelLabel(): string
    {
        return 'Super Admin';
    }
}
