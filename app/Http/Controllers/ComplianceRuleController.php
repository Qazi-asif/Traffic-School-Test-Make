<?php

namespace App\Http\Controllers;

use App\Models\ComplianceRule;
use Illuminate\Http\Request;

class ComplianceRuleController extends Controller
{
    public function index($stateConfigId)
    {
        $rules = ComplianceRule::where('state_config_id', $stateConfigId)
            ->orderBy('rule_type')
            ->orderBy('rule_name')
            ->get();

        return response()->json($rules);
    }

    public function store(Request $request)
    {
        $request->validate([
            'state_config_id' => 'required|exists:state_configurations,id',
            'rule_type' => 'required|in:timing,grading,content,submission',
            'rule_name' => 'required|string|max:255',
            'rule_value' => 'required|string',
        ]);

        $rule = ComplianceRule::create($request->all());

        return response()->json($rule);
    }

    public function update(Request $request, ComplianceRule $complianceRule)
    {
        $request->validate([
            'rule_type' => 'required|in:timing,grading,content,submission',
            'rule_name' => 'required|string|max:255',
            'rule_value' => 'required|string',
        ]);

        $complianceRule->update($request->all());

        return response()->json($complianceRule);
    }

    public function destroy(ComplianceRule $complianceRule)
    {
        $complianceRule->delete();

        return response()->json(['message' => 'Compliance rule deleted successfully']);
    }
}
