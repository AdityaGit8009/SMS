<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;
        abort_unless($student, 404);
        $student->load(['user', 'course', 'semester', 'academicSession']);
        $marks = Mark::with(['examSchedule.exam', 'examSchedule.subject'])
            ->where('student_id', $student->id)
            ->whereHas('examSchedule.exam', fn ($query) => $query->whereIn('status', ['published', 'completed']))
            ->get()
            ->groupBy(fn ($mark) => $mark->examSchedule->exam_id);

        return view('results.student', compact('student', 'marks'));
    }
}
