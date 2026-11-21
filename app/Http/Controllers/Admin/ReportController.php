<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Exam;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with(['user', 'course', 'semester', 'section', 'academicSession']);
        if ($request->filled('q')) {
            $students->where(function ($q) use ($request) {
                $q->where('roll_no', 'like', '%'.$request->q.'%')
                    ->orWhere('admission_no', 'like', '%'.$request->q.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$request->q.'%'));
            });
        }
        if ($request->filled('course_id')) {
            $students->where('course_id', $request->course_id);
        }
        if ($request->filled('status')) {
            $students->where('status', $request->status);
        }

        $studentRows = $students->orderBy('roll_no')->paginate(20, ['*'], 'students')->withQueryString();
        $teacherRows = Teacher::with(['user', 'department'])->orderBy('employee_id')->paginate(15, ['*'], 'teachers')->withQueryString();
        $attendanceRows = Attendance::with(['student.user', 'student.course', 'subject'])->latest('date')->paginate(25, ['*'], 'attendance')->withQueryString();
        $examRows = Exam::with(['course', 'semester', 'academicSession'])->latest('start_date')->paginate(15, ['*'], 'exams')->withQueryString();
        $resultRows = Mark::with(['student.user', 'examSchedule.exam', 'examSchedule.subject'])->latest()->paginate(25, ['*'], 'results')->withQueryString();

        $pendingRows = Student::with(['user', 'course', 'academicSession', 'feePayments'])->get()->map(function ($student) {
            $due = FeeStructure::where('course_id', $student->course_id)
                ->where('academic_session_id', $student->academic_session_id)->sum('amount');
            $paid = $student->feePayments->sum('amount');
            $student->fee_due_total = (float) $due;
            $student->fee_paid_total = (float) $paid;
            $student->fee_pending_total = max((float) $due - (float) $paid, 0);

            return $student;
        })->filter(fn ($s) => $s->fee_pending_total > 0)->sortByDesc('fee_pending_total')->take(50);

        $summary = [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'attendance_records' => Attendance::count(),
            'fee_collected' => FeePayment::sum('amount'),
            'exams' => Exam::count(),
            'marks_entries' => Mark::count(),
        ];
        $courses = Course::orderBy('name')->get();

        return view('admin.reports.index', compact(
            'studentRows', 'teacherRows', 'attendanceRows', 'pendingRows', 'examRows', 'resultRows', 'summary', 'courses'
        ));
    }

    public function studentsCsv()
    {
        $rows = Student::with(['user', 'course', 'semester', 'section', 'academicSession'])->orderBy('roll_no')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Admission No', 'Roll No', 'Name', 'Email', 'Course', 'Semester', 'Section', 'Session', 'Status'], escape: '');
            foreach ($rows as $s) {
                fputcsv($out, [$s->admission_no, $s->roll_no, $s->user->name, $s->user->email, $s->course->name, $s->semester->name, $s->section?->name, $s->academicSession->name, $s->status ? 'Active' : 'Inactive'], escape: '');
            }
            fclose($out);
        }, 'students-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function feesCsv()
    {
        $rows = FeePayment::with(['student.user', 'feeStructure'])->orderBy('payment_date')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Student', 'Roll No', 'Fee', 'Amount', 'Method', 'Transaction ID'], escape: '');
            foreach ($rows as $p) {
                fputcsv($out, [$p->payment_date?->format('Y-m-d'), $p->student->user->name, $p->student->roll_no, $p->feeStructure?->name, $p->amount, $p->payment_method, $p->transaction_id], escape: '');
            }
            fclose($out);
        }, 'fees-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function teachersCsv()
    {
        $rows = Teacher::with(['user', 'department'])->orderBy('employee_id')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee ID', 'Name', 'Email', 'Phone', 'Department', 'Designation', 'Qualification', 'Joining Date'], escape: '');
            foreach ($rows as $t) {
                fputcsv($out, [
                    $t->employee_id, $t->user->name, $t->user->email, $t->phone, $t->department?->name,
                    $t->designation, $t->qualification, $t->joining_date?->format('Y-m-d'),
                ], escape: '');
            }
            fclose($out);
        }, 'teachers-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function attendanceCsv()
    {
        $rows = Attendance::with(['student.user', 'student.course', 'subject'])->orderByDesc('date')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Admission No', 'Roll No', 'Student', 'Course', 'Subject', 'Status'], escape: '');
            foreach ($rows as $a) {
                fputcsv($out, [
                    $a->date?->format('Y-m-d'), $a->student->admission_no, $a->student->roll_no,
                    $a->student->user->name, $a->student->course->name, $a->subject->name, ucfirst($a->status),
                ], escape: '');
            }
            fclose($out);
        }, 'attendance-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function examsCsv()
    {
        $rows = Exam::with(['course', 'semester', 'academicSession'])->orderByDesc('start_date')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Exam', 'Course', 'Semester', 'Session', 'Start Date', 'End Date', 'Status'], escape: '');
            foreach ($rows as $e) {
                fputcsv($out, [
                    $e->name, $e->course->name, $e->semester->name, $e->academicSession->name,
                    $e->start_date?->format('Y-m-d'), $e->end_date?->format('Y-m-d'), ucfirst($e->status),
                ], escape: '');
            }
            fclose($out);
        }, 'exams-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function resultsCsv()
    {
        $rows = Mark::with(['student.user', 'examSchedule.exam', 'examSchedule.subject'])->latest()->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Student', 'Roll No', 'Exam', 'Subject', 'Theory', 'Practical', 'Total', 'Maximum', 'Passing'], escape: '');
            foreach ($rows as $m) {
                fputcsv($out, [
                    $m->student->user->name, $m->student->roll_no, $m->examSchedule->exam->name,
                    $m->examSchedule->subject->name, $m->theory_marks, $m->practical_marks, $m->total_marks,
                    $m->examSchedule->max_marks, $m->examSchedule->passing_marks,
                ], escape: '');
            }
            fclose($out);
        }, 'results-report.csv', ['Content-Type' => 'text/csv']);
    }
}
