<?php

namespace App\Http\Controllers\Enhanced;

use App\Http\Controllers\Controller;
use App\Models\FinalExamResult;
use App\Models\FinalExamQuestionResult;
use App\Models\UserCourseEnrollment;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FinalExamController extends Controller
{
    /**
     * Check if student is eligible for final exam
     */
    public function checkEligibility(Request $request, $enrollmentId)
    {
        try {
            $user = Auth::user();
            
            $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
                ->where('user_id', $user->id)
                ->firstOrFail();
            
            // Chec