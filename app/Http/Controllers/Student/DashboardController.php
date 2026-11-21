<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\ExamSchedule;
use App\Models\FeeStructure;
use App\Models\Notice;
use App\Models\Timetable;

class DashboardController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;
        abort_unless($student, 404, 'Student profile not found.');
        $total = Attendance::where('student_id', $student->id)->count();
        $present = Attendance::where('student_id', $student->id)->where('status', 'present')->count();
        $attendancePercentage = $total ? round(($present / $total) * 100, 1) : 0;
        $due = FeeStructure::where('course_id', $student->course_id)->where('academic_session_id', $student->academic_session_id)->sum('amount');
        $paid = $student->feePayments()->sum('amount');
        $pending = max($due - $paid, 0);
        $upcomingExams = ExamSchedule::with(['exam', 'subject'])->whereHas('exam', fn ($q) => $q->where('course_id', $student->course_id)->where('semester_id', $student->semester_id))->whereDate('exam_date', '>=', today())->orderBy('exam_date')->take(5)->get();
        $assignments = Assignment::with('subject')->whereHas('subject', fn ($q) => $q->where('course_id', $student->course_id)->where('semester_id', $student->semester_id))->latest()->take(5)->get();
        $notices = Notice::published()->where(function ($q) {
            $q->whereNull('target_role')->orWhere('target_role', 'student');
        })->where(function ($q) use ($student) {
            $q->whereNull('course_id')->orWhere('course_id', $student->course_id);
        })->latest('publish_date')->take(5)->get();
        $timetable = Timetable::with('subject')->where('course_id', $student->course_id)->where('semester_id', $student->semester_id)->where(function ($q) use ($student) {
            $q->whereNull('section_id')->orWhere('section_id', $student->section_id);
        })->where('day_of_week', now()->format('l'))->orderBy('start_time')->get();

        return view('student.dashboard', compact('student','attendancePercentage','pending','upcomingExams','assignments','notices','timetable'));
    }
}
