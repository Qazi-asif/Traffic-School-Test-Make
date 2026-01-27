<?php

namespace App\Http\Controllers;

use App\Models\CertificateInventory;
use App\Models\FloridaCertificate;

class FloridaDashboardController extends Controller
{
    public function index()
    {
        return view('admin.florida-dashboard');
    }

    public function stats()
    {
        $inventory = CertificateInventory::all();

        return response()->json([
            'available' => $inventory->sum('available_count'),
            'used_this_month' => FloridaCertificate::whereMonth('completion_date', now()->month)->count(),
            'pending' => 0,
            'failed' => 0,
            'inventory' => $inventory,
            'recent_submissions' => FloridaCertificate::orderBy('created_at', 'desc')->limit(5)->get(),
        ]);
    }
}
