<?php

namespace App\Http\Controllers\SubAgent;

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
        if (! $user || $user->user_type !== 'sub_agent') {
            abort(403, 'Sub-agent access only.');
        }
    }

    protected function hotelsRoutePrefix(): string
    {
        return 'subagent';
    }

    protected function panelLabel(): string
    {
        return 'Sub Agent';
    }
}
