<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);
        $courseIds = $teacher->subjects()->pluck('course_id')->unique();
        $courses = Course::whereIn('id', $courseIds)->orderBy('name')->get();
        $items = Notice::where('created_by', $request->user()->id)->latest('publish_date')->paginate(20);
        return view('teacher.notices', compact('items', 'courses'));
    }

    public function store(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);
        $allowedCourses = $teacher->subjects()->pluck('course_id')->unique()->all();
        $data = $request->validate([
            'title' => ['required', 'max:191'],
            'content' => ['required', 'string'],
            'type' => ['required', 'in:general,exam,holiday,fee,event'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'publish_date' => ['required', 'date'],
        ]);
        if (! empty($data['course_id']) && ! in_array((int) $data['course_id'], $allowedCourses, true)) abort(403);
        Notice::create($data + ['created_by' => $request->user()->id, 'target_role' => 'student', 'status' => true]);
        return back()->with('success', 'Notice published to students.');
    }

    public function destroy(Request $request, Notice $notice)
    {
        abort_unless($notice->created_by === $request->user()->id, 403);
        $notice->delete();
        return back()->with('success', 'Notice deleted.');
    }
}
