<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MaintenanceController extends Controller
{
    /**
     * Show maintenance mode control panel
     */
    public function index(Request $request)
    {
        $downFile = storage_path('framework/down');
        $isEnabled = File::exists($downFile);
        $status = $request->get('status');
        $error = $request->get('error');
        
        return view('maintenance.control', compact('isEnabled', 'status', 'error'));
    }
    
    /**
     * Enable maintenance mode
     */
    public function enable(Request $request)
    {
        try {
            $downFile = storage_path('framework/down');
            
            \Log::info('Attempting to enable maintenance mode', ['down_file' => $downFile]);
            
            // Create the down file with maintenance data
            $maintenanceData = [
                'time' => time(),
                'message' => 'Site under maintenance',
                'retry' => 60,
                'refresh' => 60
            ];
            
            // Ensure the framework directory exists
            $frameworkDir = storage_path('framework');
            if (!File::exists($frameworkDir)) {
                \Log::info('Creating framework directory', ['dir' => $frameworkDir]);
                File::makeDirectory($frameworkDir, 0755, true);
            }
            
            // Write the maintenance file
            $jsonData = json_encode($maintenanceData);
            \Log::info('Writing maintenance data', ['data' => $jsonData]);
            
            $result = File::put($downFile, $jsonData);
            
            if ($result === false) {
                throw new \Exception('Failed to write maintenance file');
            }
            
            // Verify the file was created
            if (!File::exists($downFile)) {
                throw new \Exception('Maintenance file was not created');
            }
            
            \Log::info('Maintenance mode enabled successfully');
            
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'enabled' => true,
                    'message' => 'Maintenance mode ENABLED - Normal users see 503 page'
                ]);
            }
            
            return redirect()->route('maintenance.control', ['status' => 'enabled']);
            
        } catch (\Exception $e) {
            \Log::error('Failed to enable maintenance mode: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            
            // Return JSON error response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to enable maintenance mode: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('maintenance.control', ['error' => 'Failed to enable maintenance mode: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Disable maintenance mode
     */
    public function disable(Request $request)
    {
        try {
            $downFile = storage_path('framework/down');
            
            \Log::info('Attempting to disable maintenance mode', ['down_file' => $downFile]);
            
            // Remove the down file if it exists
            if (File::exists($downFile)) {
                File::delete($downFile);
            }
            
            // Verify the file was removed
            if (File::exists($downFile)) {
                throw new \Exception('Failed to remove maintenance file');
            }
            
            \Log::info('Maintenance mode disabled successfully');
            
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'enabled' => false,
                    'message' => 'Maintenance mode DISABLED - Site is online'
                ]);
            }
            
            return redirect()->route('maintenance.control', ['status' => 'disabled']);
            
        } catch (\Exception $e) {
            \Log::error('Failed to disable maintenance mode: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            
            // Return JSON error response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to disable maintenance mode: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('maintenance.control', ['error' => 'Failed to disable maintenance mode: ' . $e->getMessage()]);
        }
    }
}