<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\Mark;
use App\Models\Student;
use Illuminate\Http\Request;

class MarkController extends Controller
{
    public function index(Request $request)
    {
        $schedules = ExamSchedule::with(['exam.course', 'exam.semester', 'subject'])
            ->latest('exam_date')->get();
        $selectedSchedule = null;
        $students = collect();
        $marks = collect();

        if ($request->filled('schedule_id')) {
            $selectedSchedule = ExamSchedule::with(['exam', 'subject'])->findOrFail($request->schedule_id);
            $students = Student::with('user')
                ->where('course_id', $selectedSchedule->exam->course_id)
                ->where('semester_id', $selectedSchedule->exam->semester_id)
                ->where('academic_session_id', $selectedSchedule->exam->academic_session_id)
                ->where('status', true)
                ->orderBy('roll_no')
                ->get();
            $marks = Mark::where('exam_schedule_id', $selectedSchedule->id)->get()->keyBy('student_id');
        }

        return view('admin.marks.index', compact('schedules', 'selectedSchedule', 'students', 'marks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'schedule_id' => ['required', 'exists:exam_schedules,id'],
            'marks' => ['required', 'array'],
            'marks.*.theory' => ['nullable', 'numeric', 'min:0'],
            'marks.*.practical' => ['nullable', 'numeric', 'min:0'],
        ]);

        $schedule = ExamSchedule::with('exam')->findOrFail($data['schedule_id']);

        foreach ($data['marks'] as $studentId => $row) {
            $student = Student::whereKey($studentId)
                ->where('course_id', $schedule->exam->course_id)
                ->where('semester_id', $schedule->exam->semester_id)
                ->where('academic_session_id', $schedule->exam->academic_session_id)
                ->where('status', true)
                ->first();

            if (! $student) {
                continue;
            }

            $theory = (float) ($row['theory'] ?? 0);
            $practical = (float) ($row['practical'] ?? 0);
            $total = $theory + $practical;

            if ($total > (float) $schedule->max_marks) {
                return back()->withErrors([
                    'marks' => "Marks for {$student->roll_no} exceed maximum marks ({$schedule->max_marks}).",
                ])->withInput();
            }

            Mark::updateOrCreate(
                ['exam_schedule_id' => $schedule->id, 'student_id' => $student->id],
                [
                    'theory_marks' => $theory,
                    'practical_marks' => $practical,
                    'total_marks' => $total,
                    'entered_by' => $request->user()->id,
                ]
            );
        }

        return back()->with('success', 'Marks saved successfully.');
    }

    public function result(Student $student)
    {
        $student->load(['user', 'course', 'semester', 'academicSession']);
        $marks = Mark::with(['examSchedule.exam', 'examSchedule.subject'])
            ->where('student_id', $student->id)
            ->get()
            ->groupBy(fn ($mark) => $mark->examSchedule->exam_id);

        return view('results.student', compact('student', 'marks'));
    }
}
