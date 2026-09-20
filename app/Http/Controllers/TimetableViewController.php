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
            $query->where('teacher_id', $user->teacher?->id ?? 0);
        } elseif ($user->role === 'student') {
            $student = $user->student;
            $query->where('course_id', $student->course_id)
                ->where('semester_id', $student->semester_id)
                ->where(fn ($q) => $q->whereNull('section_id')->orWhere('section_id', $student->section_id));
        }

        $items = $query->get()->groupBy('day_of_week');
        return view('timetable.view', compact('items'));
    }
}
