<?php

namespace App\Http\Controllers;

use App\Models\StateStamp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StateStampController extends Controller
{
    public function index()
    {
        $stamps = StateStamp::orderBy('state_name')->get();
        
        // Static list of states that have courses
        $states = collect([
            (object)['code' => 'DE', 'name' => 'Delaware'],
            (object)['code' => 'FL', 'name' => 'Florida'],
            (object)['code' => 'MO', 'name' => 'Missouri'],
            (object)['code' => 'TX', 'name' => 'Texas'],
        ]);

        return view('admin.state-stamps.index', compact('stamps', 'states'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'state_code' => 'required|string|size:2|unique:state_stamps,state_code',
            'state_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('state-stamps', 'public');
        }

        $stamp = StateStamp::create([
            'state_code' => strtoupper($validated['state_code']),
            'state_name' => $validated['state_name'],
            'logo_path' => $logoPath,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'stamp' => $stamp,
        ]);
    }

    public function update(Request $request, $id)
    {
        $stamp = StateStamp::findOrFail($id);

        $validated = $request->validate([
            'state_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($stamp->logo_path) {
                Storage::disk('public')->delete($stamp->logo_path);
            }
            $stamp->logo_path = $request->file('logo')->store('state-stamps', 'public');
        }

        $stamp->state_name = $validated['state_name'];
        $stamp->description = $validated['description'] ?? null;
        $stamp->is_active = $request->has('is_active');
        $stamp->save();

        return response()->json([
            'success' => true,
            'stamp' => $stamp,
        ]);
    }

    public function destroy($id)
    {
        $stamp = StateStamp::findOrFail($id);

        // Delete logo file
        if ($stamp->logo_path) {
            Storage::disk('public')->delete($stamp->logo_path);
        }

        $stamp->delete();

        return response()->json([
            'success' => true,
            'message' => 'State stamp deleted successfully',
        ]);
    }

    public function getByStateCode($stateCode)
    {
        $stamp = StateStamp::where('state_code', strtoupper($stateCode))
            ->where('is_active', true)
            ->first();

        return response()->json([
            'stamp' => $stamp,
        ]);
    }
}
