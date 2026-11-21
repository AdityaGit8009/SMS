<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function index(Request $r)
    {
        $q = Student::with(['user', 'course', 'semester', 'section', 'academicSession']);
        if ($r->filled('q')) {
            $q->where(function ($x) use ($r) {
                $x->where('admission_no', 'like', '%'.$r->q.'%')->orWhere('roll_no', 'like', '%'.$r->q.'%')->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$r->q.'%')->orWhere('email', 'like', '%'.$r->q.'%'));
            });
        } foreach (['course_id', 'semester_id', 'section_id', 'academic_session_id', 'status'] as $f) {
            if ($r->filled($f)) {
                $q->where($f, $r->$f);
            }
        } $items = $q->latest()->paginate(20)->withQueryString();

        return view('admin.students.index', ['items' => $items, 'courses' => Course::orderBy('name')->get(), 'semesters' => Semester::orderBy('number')->get(), 'sections' => Section::orderBy('name')->get(), 'sessions' => AcademicSession::latest('start_date')->get()]);
    }

    public function create()
    {
        return view('admin.students.form', $this->formData(new Student));
    }

    public function store(Request $r)
    {
        $d = $this->validated($r);
        DB::transaction(function () use ($r, $d) {
            $user = User::create(['name' => $d['name'], 'email' => $d['email'], 'password' => $d['password'], 'role' => 'student', 'status' => $r->boolean('status'), 'phone' => $d['phone'] ?? null]);
            $photo = $r->file('photo')?->store('students', 'public');
            Student::create(['user_id' => $user->id, 'admission_no' => $d['admission_no'], 'roll_no' => $d['roll_no'], 'course_id' => $d['course_id'], 'semester_id' => $d['semester_id'], 'section_id' => $d['section_id'] ?? null, 'academic_session_id' => $d['academic_session_id'], 'dob' => $d['dob'] ?? null, 'gender' => $d['gender'] ?? null, 'phone' => $d['phone'] ?? null, 'address' => $d['address'] ?? null, 'photo' => $photo, 'status' => $r->boolean('status')]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Student created.');
    }

    public function show(Student $student)
    {
        $student->load(['user', 'course.department', 'semester', 'section', 'academicSession', 'documents', 'feePayments.feeStructure']);

        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $student->load('user');

        return view('admin.students.form', $this->formData($student));
    }

    public function update(Request $r, Student $student)
    {
        $d = $this->validated($r, $student);
        DB::transaction(function () use ($r, $d, $student) {
            $student->user->update(['name' => $d['name'], 'email' => $d['email'], 'status' => $r->boolean('status'), 'phone' => $d['phone'] ?? null]);
            if (! empty($d['password'])) {
                $student->user->update(['password' => $d['password']]);
            } $data = ['admission_no' => $d['admission_no'], 'roll_no' => $d['roll_no'], 'course_id' => $d['course_id'], 'semester_id' => $d['semester_id'], 'section_id' => $d['section_id'] ?? null, 'academic_session_id' => $d['academic_session_id'], 'dob' => $d['dob'] ?? null, 'gender' => $d['gender'] ?? null, 'phone' => $d['phone'] ?? null, 'address' => $d['address'] ?? null, 'status' => $r->boolean('status')];
            if ($r->hasFile('photo')) {
                if ($student->photo) {
                    Storage::disk('public')->delete($student->photo);
                } $data['photo'] = $r->file('photo')->store('students', 'public');
            } $student->update($data);
        });

        return redirect()->route('admin.students.index')->with('success', 'Student updated.');
    }

    public function destroy(Student $student)
    {
        DB::transaction(function () use ($student) {
            $user = $student->user;
            $documents = $student->documents()->get();
            $publicFiles = $user->leaveRequests()->whereNotNull('attachment')->pluck('attachment')
                ->merge(AssignmentSubmission::where('student_id', $student->id)->pluck('file_path'))
                ->push($student->photo, $user->profile_photo)->filter()->values()->all();

            if (! $user->delete()) {
                throw ValidationException::withMessages(['delete' => 'The student account could not be deleted.']);
            }

            DB::afterCommit(function () use ($publicFiles, $documents) {
                Storage::disk('public')->delete($publicFiles);
                foreach ($documents as $document) {
                    $document->deleteStoredCopies();
                }
            });
        });

        return back()->with('success', 'Student deleted.');
    }

    public function uploadDocument(Request $r, Student $student)
    {
        $d = $r->validate(['type' => 'required|max:50', 'title' => 'nullable|max:191', 'document' => 'required|file|max:5120']);
        $path = $r->file('document')->store('student-documents', 'local');
        if ($path === false) {
            throw ValidationException::withMessages(['document' => 'The file could not be stored. Please try again.']);
        }
        try {
            $student->documents()->create(['type' => $d['type'], 'title' => $d['title'] ?? null, 'file_path' => $path]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return back()->with('success', 'Document uploaded.');
    }

    public function downloadDocument(Student $student, StudentDocument $document): StreamedResponse
    {
        abort_unless($document->student_id === $student->id, 404);
        $disk = $document->readDisk();
        abort_unless($disk->exists($document->file_path), 404);

        return $disk->download($document->file_path, null, ['Cache-Control' => 'private, no-store']);
    }

    public function deleteDocument(Student $student, StudentDocument $document)
    {
        abort_unless($document->student_id === $student->id, 404);
        $document->delete();
        $document->deleteStoredCopies();

        return back()->with('success', 'Document deleted.');
    }

    public function idCard(Student $student)
    {
        $student->load(['user', 'course', 'academicSession']);

        return view('admin.students.id-card', compact('student'));
    }

    private function formData(Student $item)
    {
        return ['item' => $item, 'courses' => Course::where('status', true)->orderBy('name')->get(), 'semesters' => Semester::with('course')->orderBy('course_id')->orderBy('number')->get(), 'sections' => Section::with('course')->orderBy('name')->get(), 'sessions' => AcademicSession::latest('start_date')->get()];
    }

    private function validated(Request $r, ?Student $student = null)
    {
        $d = $r->validate(['name' => 'required|max:191', 'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($student?->user_id)], 'password' => [$student ? 'nullable' : 'required', 'nullable', 'min:8'], 'admission_no' => ['required', 'max:50', Rule::unique('students', 'admission_no')->ignore($student?->id)], 'roll_no' => ['required', 'max:50', Rule::unique('students', 'roll_no')->ignore($student?->id)], 'course_id' => 'required|exists:courses,id', 'semester_id' => 'required|exists:semesters,id', 'section_id' => 'nullable|exists:sections,id', 'academic_session_id' => 'required|exists:academic_sessions,id', 'dob' => 'nullable|date', 'gender' => 'nullable|in:male,female,other', 'phone' => 'nullable|max:30', 'address' => 'nullable|string', 'photo' => 'nullable|image|max:2048', 'status' => 'nullable|boolean']);
        $sem = Semester::find($d['semester_id']);
        if (! $sem || $sem->course_id !== (int) $d['course_id']) {
            abort(422, 'Semester does not belong to selected course.');
        }
        if (! empty($d['section_id'])) {
            $section = Section::find($d['section_id']);
            if (! $section || ($section->course_id !== null && $section->course_id !== (int) $d['course_id'])) {
                abort(422, 'Section does not belong to selected course.');
            }
        }

        return $d;
    }
}
