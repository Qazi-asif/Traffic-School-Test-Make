<?php

namespace App\Http\Controllers;

use App\Models\Court;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountyController extends Controller
{
    public function index(): JsonResponse
    {
        $states = Court::distinct()->pluck('state')->filter()->sort()->values();

        return response()->json($states);
    }

    public function getCounties($state): JsonResponse
    {
        $counties = Court::where('state', $state)
            ->distinct()
            ->pluck('county')
            ->filter()
            ->sort()
            ->values();

        return response()->json($counties);
    }

    public function getCourts($state, $county): JsonResponse
    {
        $courts = Court::where('state', $state)
            ->where('county', $county)
            ->get();

        return response()->json($courts);
    }

    public function storeCourt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'state' => 'required|string',
            'county' => 'required|string',
            'court' => 'required|string',
        ]);

        $court = Court::create($validated);

        return response()->json($court, 201);
    }

    public function updateCourt(Request $request, $id): JsonResponse
    {
        $court = Court::findOrFail($id);
        $validated = $request->validate([
            'state' => 'required|string',
            'county' => 'required|string',
            'court' => 'required|string',
        ]);

        $court->update($validated);

        return response()->json($court);
    }

    public function deleteCourt($id): JsonResponse
    {
        Court::findOrFail($id)->delete();

        return response()->json(['message' => 'Court deleted']);
    }

    public function storeState(Request $request): JsonResponse
    {
        $validated = $request->validate(['state' => 'required|string|unique:courts,state']);
        Court::create(['state' => $validated['state'], 'county' => '', 'court' => '']);

        return response()->json(['message' => 'State added'], 201);
    }

    public function deleteState($state): JsonResponse
    {
        Court::where('state', $state)->delete();

        return response()->json(['message' => 'State deleted']);
    }

    public function storeCounty(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'state' => 'required|string',
            'county' => 'required|string',
        ]);
        Court::create(['state' => $validated['state'], 'county' => $validated['county'], 'court' => '']);

        return response()->json(['message' => 'County added'], 201);
    }

    public function deleteCounty($state, $county): JsonResponse
    {
        Court::where('state', $state)->where('county', $county)->delete();

        return response()->json(['message' => 'County deleted']);
    }
}
