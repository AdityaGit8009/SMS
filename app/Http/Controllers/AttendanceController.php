<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $subjectQuery = Subject::with(['course', 'semester', 'teacher.user']);

        if ($user->role === 'teacher') {
            $subjectQuery->where('teacher_id', $user->teacher?->id ?? 0);
        }

        $subjects = $subjectQuery->orderBy('name')->get();
        $students = collect();
        $records = collect();
        $selectedSubject = null;
        $date = $request->input('date', now()->toDateString());

        if ($request->filled('subject_id')) {
            $selectedSubject = Subject::findOrFail($request->subject_id);

            if ($user->role === 'teacher') {
                abort_unless($selectedSubject->teacher_id === $user->teacher?->id, 403);
            }

            $students = Student::with('user')
                ->where('course_id', $selectedSubject->course_id)
                ->where('semester_id', $selectedSubject->semester_id)
                ->where('status', true)
                ->orderBy('roll_no')
                ->get();

            $records = Attendance::where('subject_id', $selectedSubject->id)
                ->whereDate('date', $date)
                ->get()
                ->keyBy('student_id');
        }

        return view('attendance.index', compact(
            'subjects', 'students', 'records', 'selectedSubject', 'date'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'date' => ['required', 'date'],
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', 'in:present,absent,leave'],
        ]);

        $subject = Subject::findOrFail($data['subject_id']);

        if ($request->user()->role === 'teacher') {
            abort_unless($subject->teacher_id === $request->user()->teacher?->id, 403);
        }

        $studentIds = array_keys($data['attendance']);
        $eligible = Student::whereKey($studentIds)
            ->where('course_id', $subject->course_id)
            ->where('semester_id', $subject->semester_id)
            ->where('status', true)
            ->count();
        if ($eligible !== count($studentIds)) {
            throw ValidationException::withMessages(['attendance' => 'The class roster has changed. Reload the students before saving attendance.']);
        }

        $date = $request->date('date')->toDateString();
        DB::transaction(function () use ($data, $subject, $date, $request) {
            foreach ($data['attendance'] as $studentId => $status) {
                Attendance::whereDate('date', $date)->updateOrCreate(
                    ['student_id' => $studentId, 'subject_id' => $subject->id],
                    ['date' => $date, 'status' => $status, 'marked_by' => $request->user()->id]
                );
            }
        });

        return back()->with('success', 'Attendance saved successfully.');
    }

    public function studentHistory(Request $request)
    {
        $student = $request->user()->student;
        abort_unless($student, 404);

        $records = Attendance::with('subject')
            ->where('student_id', $student->id)
            ->latest('date')
            ->paginate(30);

        $all = Attendance::with('subject')->where('student_id', $student->id)->get();
        $total = $all->count();
        $present = $all->where('status', 'present')->count();
        $percentage = $total ? round(($present / $total) * 100, 1) : 0;
        $subjectSummary = $all->groupBy('subject_id')->map(function ($rows) {
            $total = $rows->count();
            $present = $rows->where('status', 'present')->count();

            return [
                'subject' => $rows->first()->subject,
                'total' => $total,
                'present' => $present,
                'percentage' => $total ? round(($present / $total) * 100, 1) : 0,
            ];
        });

        return view('student.attendance', compact('student', 'records', 'percentage', 'subjectSummary'));
    }
}
