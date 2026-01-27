<?php

namespace App\Http\Controllers;

use App\Models\StateConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StateConfigurationController extends Controller
{
    public function index()
    {
        $configurations = StateConfiguration::with('complianceRules')->get();

        return response()->json($configurations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'state_code' => 'required|string|size:2|unique:state_configurations',
            'state_name' => 'required|string|max:255',
            'submission_method' => 'required|in:api,portal,email,manual',
            'api_endpoint' => 'required_if:submission_method,api|url',
            'portal_url' => 'required_if:submission_method,portal|url',
            'certificate_template' => 'required|string',
        ]);

        $configuration = StateConfiguration::create($request->all());

        return response()->json($configuration->load('complianceRules'));
    }

    public function show(StateConfiguration $stateConfiguration)
    {
        return response()->json($stateConfiguration->load('complianceRules'));
    }

    public function update(Request $request, StateConfiguration $stateConfiguration)
    {
        $request->validate([
            'state_code' => 'required|string|size:2|unique:state_configurations,state_code,'.$stateConfiguration->id,
            'state_name' => 'required|string|max:255',
            'submission_method' => 'required|in:api,portal,email,manual',
            'api_endpoint' => 'required_if:submission_method,api|url',
            'portal_url' => 'required_if:submission_method,portal|url',
            'certificate_template' => 'required|string',
        ]);

        $stateConfiguration->update($request->all());

        return response()->json($stateConfiguration->load('complianceRules'));
    }

    public function destroy(StateConfiguration $stateConfiguration)
    {
        $stateConfiguration->delete();

        return response()->json(['message' => 'State configuration deleted successfully']);
    }

    public function testConnection($stateCode)
    {
        $config = StateConfiguration::where('state_code', $stateCode)->firstOrFail();

        // Test connection based on submission method
        switch ($config->submission_method) {
            case 'api':
                return $this->testApiConnection($config);
            case 'portal':
                return $this->testPortalConnection($config);
            case 'email':
                return $this->testEmailConnection($config);
            default:
                return response()->json(['status' => 'success', 'message' => 'Manual method - no connection test needed']);
        }
    }

    private function testApiConnection($config)
    {
        try {
            // Simple ping to API endpoint
            $response = Http::timeout(10)->get($config->api_endpoint);

            return response()->json([
                'status' => 'success',
                'message' => 'API connection successful',
                'response_code' => $response->status(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'API connection failed: '.$e->getMessage(),
            ], 500);
        }
    }

    private function testPortalConnection($config)
    {
        try {
            // Simple HTTP check for portal URL
            $response = Http::timeout(10)->get($config->portal_url);

            return response()->json([
                'status' => 'success',
                'message' => 'Portal connection successful',
                'response_code' => $response->status(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Portal connection failed: '.$e->getMessage(),
            ], 500);
        }
    }

    private function testEmailConnection($config)
    {
        try {
            // Validate email format
            if (! filter_var($config->email_recipient, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email format');
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Email recipient is valid',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email validation failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
