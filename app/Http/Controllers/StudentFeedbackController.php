<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentFeedbackController extends Controller
{
    /**
     * Show the student feedback form
     */
    public function show()
    {
        return view('student.feedback.show');
    }
}