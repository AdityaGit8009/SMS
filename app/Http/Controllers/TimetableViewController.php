<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableViewController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Timetable::with(['course', 'semester', 'section', 'subject', 'teacher.user']);

        if ($user->role === 'teacher') {
            $teacher = $user->teacher;
            abort_unless($teacher, 404);
            $query->where('teacher_id', $teacher->id);
        } elseif ($user->role === 'student') {
            $student = $user->student;
            abort_unless($student, 404);
            $query->where('course_id', $student->course_id)
                ->where('semester_id', $student->semester_id)
                ->where(fn ($q) => $q->whereNull('section_id')->orWhere('section_id', $student->section_id));
        }

        $items = $query->orderBy('start_time')->orderBy('id')->get()->groupBy('day_of_week');

        return view('timetable.view', compact('items'));
    }
}
