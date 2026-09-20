<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'department']);

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('employee_id', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($userQuery) use ($term) {
                        $userQuery->where(function ($u) use ($term) {
                            $u->where('name', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%");
                        });
                    });
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('admin.teachers.index', compact('items', 'departments'));
    }

    public function create()
    {
        return view('admin.teachers.form', [
            'item' => new Teacher,
            'departments' => Department::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'teacher',
                'status' => $request->boolean('status'),
                'phone' => $data['phone'] ?? null,
            ]);

            $photo = $request->file('photo')?->store('teachers', 'public');

            Teacher::create([
                'user_id' => $user->id,
                'employee_id' => $data['employee_id'],
                'department_id' => $data['department_id'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'designation' => $data['designation'] ?? null,
                'joining_date' => $data['joining_date'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'photo' => $photo,
            ]);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher created.');
    }

    public function show(Teacher $teacher)
    {
        $teacher->load(['user', 'department', 'subjects.course', 'subjects.semester']);
        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load('user');
        return view('admin.teachers.form', [
            'item' => $teacher,
            'departments' => Department::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $data = $this->validated($request, $teacher);

        DB::transaction(function () use ($request, $data, $teacher) {
            $teacher->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => $request->boolean('status'),
                'phone' => $data['phone'] ?? null,
            ]);

            if (! empty($data['password'])) {
                $teacher->user->update(['password' => $data['password']]);
            }

            $teacherData = [
                'employee_id' => $data['employee_id'],
                'department_id' => $data['department_id'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'designation' => $data['designation'] ?? null,
                'joining_date' => $data['joining_date'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ];

            if ($request->hasFile('photo')) {
                if ($teacher->photo) {
                    Storage::disk('public')->delete($teacher->photo);
                }
                $teacherData['photo'] = $request->file('photo')->store('teachers', 'public');
            }

            $teacher->update($teacherData);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher updated.');
    }

    public function destroy(Teacher $teacher)
    {
        if ($teacher->subjects()->exists()) {
            return back()->withErrors(['delete' => 'Reassign teacher subjects before deleting.']);
        }

        DB::transaction(function () use ($teacher) {
            if ($teacher->photo) {
                Storage::disk('public')->delete($teacher->photo);
            }
            $teacher->user->delete();
        });

        return back()->with('success', 'Teacher deleted.');
    }

    private function validated(Request $request, ?Teacher $teacher = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($teacher?->user_id)],
            'password' => [$teacher ? 'nullable' : 'required', 'nullable', 'min:8'],
            'employee_id' => ['required', 'max:50', Rule::unique('teachers', 'employee_id')->ignore($teacher?->id)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'qualification' => ['nullable', 'max:191'],
            'designation' => ['nullable', 'max:191'],
            'joining_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'max:30'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['nullable', 'boolean'],
        ]);
    }
}
