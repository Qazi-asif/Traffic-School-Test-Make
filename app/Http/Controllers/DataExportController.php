<?php

namespace App\Http\Controllers;

use App\Models\DataExportRequest;
use App\Models\SecurityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataExportController extends Controller
{
    public function requestExport(Request $request): JsonResponse
    {
        try {
            if (! auth()->check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'request_type' => 'required|in:gdpr,ccpa,user_request',
            ]);

            $exportRequest = DataExportRequest::create([
                'user_id' => auth()->id(),
                'request_type' => $request->request_type,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            // Log the export request
            SecurityLog::create([
                'user_id' => auth()->id(),
                'event_type' => 'data_access',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Data export requested: {$request->request_type}",
                'metadata' => ['export_id' => $exportRequest->id],
                'risk_level' => 'low',
                'created_at' => now(),
            ]);

            return response()->json($exportRequest);
        } catch (\Exception $e) {
            \Log::error('Data export request failed: '.$e->getMessage());

            return response()->json(['error' => 'Failed to create export request: '.$e->getMessage()], 500);
        }
    }

    public function getStatus(int $id): JsonResponse
    {
        try {
            if (! auth()->check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request = DataExportRequest::where('id', $id)
                ->where('user_id', auth()->id())
                ->first();

            if (! $request) {
                return response()->json(['error' => 'Export request not found'], 404);
            }

            return response()->json($request);
        } catch (\Exception $e) {
            \Log::error('Get export status failed: '.$e->getMessage());

            return response()->json(['error' => 'Failed to get export status: '.$e->getMessage()], 500);
        }
    }

    public function download(int $id): JsonResponse
    {
        try {
            if (! auth()->check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request = DataExportRequest::where('id', $id)
                ->where('user_id', auth()->id())
                ->where('status', 'completed')
                ->first();

            if (! $request) {
                return response()->json(['error' => 'Export request not found or not completed'], 404);
            }

            // Log the download
            SecurityLog::create([
                'user_id' => auth()->id(),
                'event_type' => 'data_access',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'description' => "Data export downloaded: {$request->request_type}",
                'metadata' => ['export_id' => $request->id],
                'risk_level' => 'low',
                'created_at' => now(),
            ]);

            return response()->json(['download_url' => $request->file_path]);
        } catch (\Exception $e) {
            \Log::error('Export download failed: '.$e->getMessage());

            return response()->json(['error' => 'Failed to download export: '.$e->getMessage()], 500);
        }
    }
}
