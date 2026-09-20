<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'student') {
            $student = $user->student;
            abort_unless($student, 404, 'Student profile not found.');

            $items = Assignment::with(['subject', 'teacher.user'])
                ->whereHas('subject', fn ($q) => $q
                    ->where('course_id', $student->course_id)
                    ->where('semester_id', $student->semester_id))
                ->latest()
                ->paginate(20);

            $submissions = AssignmentSubmission::where('student_id', $student->id)
                ->get()
                ->keyBy('assignment_id');

            return view('student.assignments', compact('items', 'submissions'));
        }

        $teacher = $user->teacher;
        abort_unless($teacher, 404, 'Teacher profile not found.');

        $items = Assignment::with(['subject', 'submissions.student.user'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(20);
        $subjects = Subject::where('teacher_id', $teacher->id)
            ->with(['course', 'semester'])
            ->orderBy('name')
            ->get();

        return view('teacher.assignments', compact('items', 'subjects'));
    }

    public function store(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($request->user()->role === 'teacher' && $teacher, 403);

        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'max:191'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date', 'after:now'],
            'max_marks' => ['required', 'numeric', 'min:1'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $subject = Subject::findOrFail($data['subject_id']);
        abort_unless($subject->teacher_id === $teacher->id, 403);

        $data['teacher_id'] = $teacher->id;
        $data['file_path'] = $request->file('file')?->store('assignments', 'public');
        unset($data['file']);

        Assignment::create($data);

        return back()->with('success', 'Assignment created.');
    }

    public function destroy(Request $request, Assignment $assignment)
    {
        abort_unless(
            $request->user()->role === 'teacher'
                && $assignment->teacher_id === $request->user()->teacher?->id,
            403
        );

        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        foreach ($assignment->submissions as $submission) {
            Storage::disk('public')->delete($submission->file_path);
        }

        $assignment->delete();
        return back()->with('success', 'Assignment deleted.');
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $student = $request->user()->student;
        abort_unless($student, 403);
        abort_unless(
            $assignment->subject->course_id === $student->course_id
                && $assignment->subject->semester_id === $student->semester_id,
            403
        );

        $request->validate(['file' => ['required', 'file', 'max:10240']]);

        $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing?->file_path) {
            Storage::disk('public')->delete($existing->file_path);
        }

        $path = $request->file('file')->store('assignment-submissions', 'public');

        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            [
                'file_path' => $path,
                'submitted_at' => now(),
                'status' => now()->gt($assignment->due_date) ? 'late' : 'submitted',
                'marks' => null,
                'remarks' => null,
            ]
        );

        return back()->with('success', 'Assignment submitted.');
    }

    public function grade(Request $request, AssignmentSubmission $submission)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher && $submission->assignment->teacher_id === $teacher->id, 403);

        $data = $request->validate([
            'marks' => ['required', 'numeric', 'min:0', 'max:'.$submission->assignment->max_marks],
            'remarks' => ['nullable', 'string'],
        ]);

        $submission->update([
            'marks' => $data['marks'],
            'remarks' => $data['remarks'] ?? null,
            'status' => 'graded',
        ]);

        return back()->with('success', 'Submission graded.');
    }
}
