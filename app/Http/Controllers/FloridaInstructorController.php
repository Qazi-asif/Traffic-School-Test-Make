<?php

namespace App\Http\Controllers;

use App\Models\FloridaInstructor;
use Illuminate\Http\Request;

class FloridaInstructorController extends Controller
{
    public function index()
    {
        return response()->json(FloridaInstructor::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'instructor_id' => 'required|string|unique:florida_instructors',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        $instructor = FloridaInstructor::create($request->all());

        return response()->json($instructor, 201);
    }

    public function update(Request $request, FloridaInstructor $floridaInstructor)
    {
        $request->validate([
            'instructor_id' => 'required|string|unique:florida_instructors,instructor_id,'.$floridaInstructor->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        $floridaInstructor->update($request->all());

        return response()->json($floridaInstructor);
    }

    public function destroy(FloridaInstructor $floridaInstructor)
    {
        $floridaInstructor->delete();

        return response()->json(['message' => 'Instructor deleted successfully']);
    }
}
