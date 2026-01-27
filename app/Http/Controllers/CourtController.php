<?php

namespace App\Http\Controllers;

use App\Models\Court;
use Illuminate\Http\JsonResponse;

class CourtController extends Controller
{
    public function getStates(): JsonResponse
    {
        $states = Court::distinct()
            ->pluck('state')
            ->filter()
            ->sort()
            ->values();

        return response()->json($states);
    }
}
