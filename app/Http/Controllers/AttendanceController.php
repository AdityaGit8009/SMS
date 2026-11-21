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
        $request->validate(['subject_id' => ['nullable', 'integer'], 'date' => ['nullable', 'date_format:Y-m-d']]);
        $user = $request->user();
        $subjectQuery = Subject::with(['course', 'semester', 'teacher.user']);

        if ($user->role === 'teacher') {
            abort_unless($user->teacher, 404, 'Teacher profile not found.');
            $subjectQuery->where('teacher_id', $user->teacher->id);
        }

        $subjects = $subjectQuery->orderBy('name')->get();
        $students = collect();
        $records = collect();
        $selectedSubject = null;
        $date = $request->input('date', now()->toDateString());

        if ($request->filled('subject_id')) {
            $selectedSubject = Subject::with(['course', 'semester'])->findOrFail($request->subject_id);

            if ($user->role === 'teacher') {
                abort_unless($selectedSubject->teacher_id === $user->teacher->id, 403);
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
            $teacher = $request->user()->teacher;
            abort_unless($teacher, 404, 'Teacher profile not found.');
            abort_unless($subject->teacher_id === $teacher->id, 403);
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
            ->orderByDesc('id')
            ->paginate(30);

        $counts = Attendance::with('subject')
            ->where('student_id', $student->id)
            ->select('subject_id')
            ->selectRaw('count(*) as total, sum(case when status = ? then 1 else 0 end) as present', ['present'])
            ->groupBy('subject_id')
            ->get();

        $total = $counts->sum('total');
        $percentage = $total ? round($counts->sum('present') / $total * 100, 1) : 0;
        $subjectSummary = $counts->map(fn (Attendance $count) => [
            'subject' => $count->subject,
            'total' => (int) $count->total,
            'present' => (int) $count->present,
            'percentage' => round($count->present / $count->total * 100, 1),
        ]);

        return view('student.attendance', compact('student', 'records', 'percentage', 'subjectSummary'));
    }
}
