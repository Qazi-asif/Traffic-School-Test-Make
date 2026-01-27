<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\CourtCode;
use App\Models\CourtCodeMapping;
use App\Rules\CourtCodeFormat;
use App\Services\CourtCodeService;
use Illuminate\Http\Request;

class CourtCodeController extends Controller
{
    public function __construct(protected CourtCodeService $service) {}

    public function index(Request $request)
    {
        $query = $request->input('q', '');
        $filters = [
            'state' => $request->input('state'),
            'type' => $request->input('type'),
            'is_active' => $request->input('is_active'),
            'court_id' => $request->input('court_id'),
        ];

        $codes = $this->service->searchCodes($query, $filters);
        $states = Court::distinct()->pluck('state')->sort();
        $types = ['tvcc', 'court_id', 'location_code', 'branch_code', 'state_code'];

        return view('admin.court-codes.index', compact('codes', 'states', 'types', 'query', 'filters'));
    }

    public function create()
    {
        $courts = Court::orderBy('state')->orderBy('court')->get();
        $types = ['tvcc', 'court_id', 'location_code', 'branch_code', 'state_code'];
        $systems = ['flhsmv', 'dicds', 'dmv', 'state_portal', 'other'];

        return view('admin.court-codes.create', compact('courts', 'types', 'systems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'court_id' => 'required|exists:courts,id',
            'code_type' => 'required|in:tvcc,court_id,location_code,branch_code,state_code',
            'code_value' => ['required', 'string', 'max:50', new CourtCodeFormat($request->code_type)],
            'code_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'effective_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after:effective_date',
            'notes' => 'nullable|string',
        ]);

        if ($this->service->checkForDuplicates($validated['code_value'], $validated['code_type'])) {
            return back()->withErrors(['code_value' => 'This code already exists.'])->withInput();
        }

        $court = Court::findOrFail($validated['court_id']);
        $code = $this->service->createCode($court, $validated);

        // Handle mappings if provided
        if ($request->has('mappings')) {
            foreach ($request->mappings as $mapping) {
                if (! empty($mapping['external_code'])) {
                    $this->service->addMapping(
                        $code,
                        $mapping['external_system'],
                        $mapping['external_code'],
                        $mapping['external_name'] ?? null
                    );
                }
            }
        }

        return redirect()->route('admin.court-codes.show', $code)
            ->with('success', 'Court code created successfully.');
    }

    public function show(CourtCode $code)
    {
        $code->load(['court', 'mappings.verifiedBy', 'history.changedBy', 'createdBy']);

        return view('admin.court-codes.show', compact('code'));
    }

    public function edit(CourtCode $code)
    {
        $courts = Court::orderBy('state')->orderBy('court')->get();
        $types = ['tvcc', 'court_id', 'location_code', 'branch_code', 'state_code'];
        $systems = ['flhsmv', 'dicds', 'dmv', 'state_portal', 'other'];

        $code->load('mappings');

        return view('admin.court-codes.edit', compact('code', 'courts', 'types', 'systems'));
    }

    public function update(Request $request, CourtCode $code)
    {
        $validated = $request->validate([
            'court_id' => 'required|exists:courts,id',
            'code_type' => 'required|in:tvcc,court_id,location_code,branch_code,state_code',
            'code_value' => ['required', 'string', 'max:50', new CourtCodeFormat($request->code_type)],
            'code_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'effective_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after:effective_date',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        if ($this->service->checkForDuplicates($validated['code_value'], $validated['code_type'], $code->id)) {
            return back()->withErrors(['code_value' => 'This code already exists.'])->withInput();
        }

        $reason = $validated['reason'] ?? null;
        unset($validated['reason']);

        $this->service->updateCode($code, $validated, $reason);

        return redirect()->route('admin.court-codes.show', $code)
            ->with('success', 'Court code updated successfully.');
    }

    public function destroy(CourtCode $code)
    {
        $code->delete();

        return redirect()->route('admin.court-codes.index')
            ->with('success', 'Court code deleted successfully.');
    }

    public function deactivate(Request $request, CourtCode $code)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $this->service->deactivateCode($code, $request->reason);

        return back()->with('success', 'Court code deactivated successfully.');
    }

    public function reactivate(CourtCode $code)
    {
        $this->service->reactivateCode($code);

        return back()->with('success', 'Court code reactivated successfully.');
    }

    public function mappings(CourtCode $code)
    {
        $code->load('mappings.verifiedBy');
        $systems = ['flhsmv', 'dicds', 'dmv', 'state_portal', 'other'];

        return view('admin.court-codes.mappings', compact('code', 'systems'));
    }

    public function addMapping(Request $request, CourtCode $code)
    {
        $validated = $request->validate([
            'external_system' => 'required|in:flhsmv,dicds,dmv,state_portal,other',
            'external_code' => 'required|string|max:100',
            'external_name' => 'nullable|string|max:255',
        ]);

        $this->service->addMapping(
            $code,
            $validated['external_system'],
            $validated['external_code'],
            $validated['external_name'] ?? null
        );

        return back()->with('success', 'Mapping added successfully.');
    }

    public function removeMapping(CourtCodeMapping $mapping)
    {
        $mapping->delete();

        return back()->with('success', 'Mapping removed successfully.');
    }

    public function verifyMapping(CourtCodeMapping $mapping)
    {
        $this->service->verifyMapping($mapping);

        return back()->with('success', 'Mapping verified successfully.');
    }

    public function history(CourtCode $code)
    {
        $history = $code->history()->with('changedBy')->latest()->paginate(50);

        return view('admin.court-codes.history', compact('code', 'history'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $filters = [
            'state' => $request->input('state'),
            'type' => $request->input('type'),
        ];

        $codes = $this->service->searchCodes($query, $filters);

        return response()->json($codes);
    }

    public function lookup(string $codeValue)
    {
        $code = $this->service->findByCode($codeValue);

        if (! $code) {
            return response()->json(['error' => 'Code not found'], 404);
        }

        return response()->json($code->load('court', 'mappings'));
    }

    public function translateCode(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'from_system' => 'required|string',
            'to_system' => 'required|string',
        ]);

        $translated = $this->service->translateCode(
            $validated['code'],
            $validated['from_system'],
            $validated['to_system']
        );

        if (! $translated) {
            return response()->json(['error' => 'Translation not found'], 404);
        }

        return response()->json(['code' => $translated]);
    }

    public function importForm()
    {
        $states = Court::distinct()->pluck('state')->sort();

        return view('admin.court-codes.import', compact('states'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'state' => 'required|string|size:2',
        ]);

        $file = $request->file('file');
        $path = $file->storeAs('imports', 'court_codes_'.time().'.csv');

        $stats = $this->service->importFromCsv(storage_path('app/'.$path), $request->state);

        return back()->with('success', "Import completed. Imported: {$stats['imported']}, Skipped: {$stats['skipped']}");
    }

    public function export(Request $request)
    {
        $filters = [
            'state' => $request->input('state'),
            'type' => $request->input('type'),
            'is_active' => $request->input('is_active'),
        ];

        $filename = $this->service->exportToCsv($filters);

        return response()->download($filename)->deleteFileAfterSend();
    }

    public function expiringCodes()
    {
        $codes = $this->service->getExpiringCodes(30);

        return view('admin.court-codes.reports.expiring', compact('codes'));
    }

    public function unmappedCodes()
    {
        $codes = $this->service->getUnmappedCodes();

        return view('admin.court-codes.reports.unmapped', compact('codes'));
    }

    public function statistics()
    {
        $stats = $this->service->getCodeStatistics();

        return view('admin.court-codes.reports.statistics', compact('stats'));
    }

    public function byCourtIndex(Court $court)
    {
        $codes = $this->service->findForCourt($court);
        $types = ['tvcc', 'court_id', 'location_code', 'branch_code', 'state_code'];

        return view('admin.court-codes.by-court', compact('court', 'codes', 'types'));
    }

    public function addCodeToCourt(Request $request, Court $court)
    {
        $validated = $request->validate([
            'code_type' => 'required|in:tvcc,court_id,location_code,branch_code,state_code',
            'code_value' => ['required', 'string', 'max:50', new CourtCodeFormat($request->code_type)],
            'code_name' => 'nullable|string|max:255',
        ]);

        if ($this->service->checkForDuplicates($validated['code_value'], $validated['code_type'])) {
            return back()->withErrors(['code_value' => 'This code already exists.'])->withInput();
        }

        $this->service->createCode($court, $validated);

        return back()->with('success', 'Court code added successfully.');
    }
}
