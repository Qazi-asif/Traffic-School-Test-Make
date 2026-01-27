<?php

namespace App\Http\Controllers;

use App\Models\FloridaSchool;

class FloridaSchoolController extends Controller
{
    public function indexWeb()
    {
        $schools = FloridaSchool::where('is_active', true)->get();

        return response()->json($schools);
    }
}
