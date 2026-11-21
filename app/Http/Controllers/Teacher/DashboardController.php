<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Notice;
use App\Models\Timetable;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 404, 'Teacher profile not found.');
        $subjects = $teacher->subjects()->with(['course', 'semester'])->get();
        $assignments = Assignment::where('teacher_id', $teacher->id)->latest()->take(5)->get();
        $timetable = Timetable::with('subject')->where('teacher_id', $teacher->id)->where('day_of_week', now()->format('l'))->orderBy('start_time')->get();
        $notices = Notice::published()->where(function ($q) {
            $q->whereNull('target_role')->orWhere('target_role', 'teacher');
        })->where(fn ($query) => $query->whereNull('course_id')->orWhereIn('course_id', $subjects->pluck('course_id')))
            ->latest('publish_date')->take(5)->get();

        return view('teacher.dashboard', compact('teacher', 'subjects', 'assignments', 'timetable', 'notices'));
    }
}
