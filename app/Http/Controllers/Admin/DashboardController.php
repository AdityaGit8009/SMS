<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\ExamSchedule;
use App\Models\Notice;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $students = Student::with(['course', 'semester', 'user'])->latest()->take(6)->get();
        $pendingFees = Student::withFeeBalances()->whereColumn('fee_due_total', '>', 'fee_paid_total')
            ->sum(DB::raw('fee_due_total - fee_paid_total'));
        $stats = ['students' => Student::count(), 'teachers' => Teacher::count(), 'courses' => Course::count(), 'subjects' => Subject::count(), 'pending_fees' => $pendingFees, 'attendance_today' => Attendance::whereDate('date', today())->where('status', 'present')->count()];
        $upcomingExams = ExamSchedule::with(['exam', 'subject'])->whereDate('exam_date', '>=', today())->orderBy('exam_date')->take(5)->get();
        $notices = Notice::where('status', true)->orderByDesc('publish_date')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'students', 'upcomingExams', 'notices'));
    }
}
