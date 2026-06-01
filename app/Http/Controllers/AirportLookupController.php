<?php

namespace App\Http\Controllers;

use App\Support\AirportDirectory;
use Illuminate\Http\Request;

class AirportLookupController extends Controller
{
    public function search(Request $request)
    {
        $q = (string) $request->query('q', '');
        $limit = min(30, max(5, (int) $request->query('limit', 15)));

        return response()->json([
            'results' => AirportDirectory::search($q, $limit),
        ]);
    }

    public function show(string $code)
    {
        $airport = AirportDirectory::find($code);

        if ($airport === null) {
            return response()->json(['airport' => null], 404);
        }

        return response()->json(['airport' => $airport]);
    }
}
