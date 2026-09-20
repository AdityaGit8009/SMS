<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $items = Exam::with(['course', 'semester', 'academicSession', 'schedules.subject'])->latest()->paginate(15);
        $courses = Course::orderBy('name')->get();
        $semesters = Semester::with('course')->orderBy('number')->get();
        $sessions = AcademicSession::latest('start_date')->get();
        $subjects = Subject::with(['course', 'semester'])->orderBy('name')->get();

        return view('admin.exams.index', compact('items', 'courses', 'semesters', 'sessions', 'subjects'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedExam($request);
        Exam::create($data);
        return back()->with('success', 'Exam created.');
    }

    public function update(Request $request, Exam $exam)
    {
        $data = $this->validatedExam($request);
        $exam->update($data);
        return back()->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return back()->with('success', 'Exam deleted.');
    }

    public function storeSchedule(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'exam_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'max_marks' => ['required', 'numeric', 'min:1'],
            'passing_marks' => ['required', 'numeric', 'min:0', 'lte:max_marks'],
        ]);

        $subject = Subject::findOrFail($data['subject_id']);
        abort_unless(
            $subject->course_id === $exam->course_id && $subject->semester_id === $exam->semester_id,
            422,
            'Subject does not belong to this exam course/semester.'
        );

        if ($data['exam_date'] < $exam->start_date->toDateString() || $data['exam_date'] > $exam->end_date->toDateString()) {
            return back()->withErrors(['exam_date' => 'Exam date must be inside the exam start and end dates.'])->withInput();
        }

        ExamSchedule::updateOrCreate(
            ['exam_id' => $exam->id, 'subject_id' => $subject->id],
            $data
        );

        return back()->with('success', 'Exam schedule saved.');
    }

    public function deleteSchedule(ExamSchedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Exam schedule deleted.');
    }

    private function validatedExam(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'max:191'],
            'course_id' => ['required', 'exists:courses,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:draft,published,completed'],
        ]);

        $semester = Semester::findOrFail($data['semester_id']);
        abort_unless($semester->course_id === (int) $data['course_id'], 422, 'Semester does not belong to selected course.');

        return $data;
    }
}
