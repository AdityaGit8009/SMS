<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IdCardController extends Controller
{
    public function show(Request $request)
    {
        $student = $request->user()->student;
        abort_unless($student, 404);
        $student->load(['user', 'course', 'academicSession']);
        return view('admin.students.id-card', compact('student'));
    }
}
