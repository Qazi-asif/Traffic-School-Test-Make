<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Services\CourtCodeService;
use Illuminate\Http\Request;

class CourtCodeApiController extends Controller
{
    public function __construct(protected CourtCodeService $service) {}

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $filters = [
            'state' => $request->input('state'),
            'type' => $request->input('type'),
            'is_active' => $request->input('is_active', true),
        ];

        $codes = $this->service->searchCodes($query, $filters);

        return response()->json($codes);
    }

    public function lookup(string $code)
    {
        $courtCode = $this->service->findByCode($code);

        if (! $courtCode) {
            return response()->json(['error' => 'Code not found'], 404);
        }

        return response()->json($courtCode->load('court', 'mappings'));
    }

    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'type' => 'required|in:tvcc,court_id,location_code,branch_code,state_code',
        ]);

        $isValid = $this->service->validateCode($request->code, $request->type);
        $exists = $this->service->findByCode($request->code, $request->type) !== null;

        return response()->json([
            'valid_format' => $isValid,
            'exists' => $exists,
        ]);
    }

    public function forCourt(Court $court)
    {
        $codes = $this->service->findForCourt($court);

        return response()->json($codes);
    }

    public function translate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'from_system' => 'required|string',
            'to_system' => 'required|string',
        ]);

        $translated = $this->service->translateCode(
            $request->code,
            $request->from_system,
            $request->to_system
        );

        if (! $translated) {
            return response()->json(['error' => 'Translation not found'], 404);
        }

        return response()->json(['code' => $translated]);
    }
}
