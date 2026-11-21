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
use Illuminate\Validation\Rule;

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
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')->where('course_id', $exam->course_id)->where('semester_id', $exam->semester_id)],
            'exam_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$exam->start_date->toDateString(), 'before_or_equal:'.$exam->end_date->toDateString()],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'max_marks' => ['required', 'numeric', 'min:1'],
            'passing_marks' => ['required', 'numeric', 'min:0', 'lte:max_marks'],
        ]);

        ExamSchedule::updateOrCreate(
            ['exam_id' => $exam->id, 'subject_id' => $data['subject_id']],
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
            'semester_id' => ['required', 'integer', Rule::exists('semesters', 'id')->where('course_id', $request->integer('course_id'))],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:draft,published,completed'],
        ]);

        return $data;
    }
}
