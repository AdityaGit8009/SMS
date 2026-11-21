<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TimetableController extends Controller
{
    public function index()
    {
        $items = Timetable::with(['course', 'semester', 'section', 'subject', 'teacher.user'])
            ->orderByRaw("case day_of_week when 'Monday' then 1 when 'Tuesday' then 2 when 'Wednesday' then 3 when 'Thursday' then 4 when 'Friday' then 5 when 'Saturday' then 6 else 7 end")
            ->orderBy('start_time')
            ->orderBy('id')
            ->paginate(30);

        return view('admin.timetables.index', [
            'items' => $items,
            'courses' => Course::orderBy('name')->get(),
            'semesters' => Semester::with('course')->orderBy('number')->get(),
            'sections' => Section::with('course')->orderBy('name')->get(),
            'subjects' => Subject::with(['course', 'semester'])->orderBy('name')->get(),
            'teachers' => Teacher::with('user')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Timetable::create($this->validated($request));

        return back()->with('success', 'Timetable entry created.');
    }

    public function update(Request $request, Timetable $timetable)
    {
        $timetable->update($this->validated($request, $timetable));

        return back()->with('success', 'Timetable entry updated.');
    }

    public function destroy(Timetable $timetable)
    {
        $timetable->delete();

        return back()->with('success', 'Timetable entry deleted.');
    }

    private function validated(Request $request, ?Timetable $current = null): array
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'day_of_week' => ['required', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'max:100'],
        ]);

        $semester = Semester::findOrFail($data['semester_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        $teacher = Teacher::findOrFail($data['teacher_id']);

        abort_unless($semester->course_id === (int) $data['course_id'], 422, 'Semester does not belong to selected course.');
        abort_unless(
            $subject->course_id === (int) $data['course_id'] && $subject->semester_id === (int) $data['semester_id'],
            422,
            'Subject does not belong to selected course/semester.'
        );
        abort_unless($subject->teacher_id === null || $subject->teacher_id === $teacher->id, 422, 'Selected teacher is not assigned to this subject.');

        if (! empty($data['section_id'])) {
            $section = Section::findOrFail($data['section_id']);
            abort_unless($section->course_id === null || $section->course_id === (int) $data['course_id'], 422, 'Section does not belong to selected course.');
        }

        $baseConflict = Timetable::query()
            ->where('day_of_week', $data['day_of_week'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time']);

        if ($current) {
            $baseConflict->whereKeyNot($current->id);
        }

        if ((clone $baseConflict)->where('teacher_id', $data['teacher_id'])->exists()) {
            throw ValidationException::withMessages(['start_time' => 'Teacher already has another class in this time slot.']);
        }

        $classConflict = clone $baseConflict;
        $classConflict->where('course_id', $data['course_id'])
            ->where('semester_id', $data['semester_id'])
            ->when($data['section_id'] ?? null, fn ($query, $sectionId) => $query
                ->where(fn ($sections) => $sections->whereNull('section_id')->orWhere('section_id', $sectionId)));

        if ($classConflict->exists()) {
            throw ValidationException::withMessages(['start_time' => 'This class already has another timetable entry in the selected time slot.']);
        }

        return $data;
    }
}
