<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $items = Notice::with(['creator', 'course'])->latest('publish_date')->paginate(20);
        $courses = Course::orderBy('name')->get();
        return view('admin.notices.index', compact('items', 'courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'max:191'],
            'content' => ['required', 'string'],
            'type' => ['required', 'in:general,exam,holiday,fee,event'],
            'target_role' => ['nullable', 'in:admin,teacher,student'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'publish_date' => ['required', 'date'],
            'status' => ['nullable', 'boolean'],
        ]);
        $data['created_by'] = $request->user()->id;
        $data['status'] = $request->boolean('status');
        Notice::create($data);
        return back()->with('success', 'Notice published.');
    }

    public function update(Request $request, Notice $notice)
    {
        $data = $request->validate([
            'title' => ['required', 'max:191'],
            'content' => ['required', 'string'],
            'type' => ['required', 'in:general,exam,holiday,fee,event'],
            'target_role' => ['nullable', 'in:admin,teacher,student'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'publish_date' => ['required', 'date'],
            'status' => ['nullable', 'boolean'],
        ]);
        $data['status'] = $request->boolean('status');
        $notice->update($data);
        return back()->with('success', 'Notice updated.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return back()->with('success', 'Notice deleted.');
    }
}
