<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;
        abort_unless($student, 404);
        $items = Notice::with(['creator', 'course'])
            ->published()
            ->where(function ($q) {
                $q->whereNull('target_role')->orWhere('target_role', 'student');
            })
            ->where(function ($q) use ($student) {
                $q->whereNull('course_id')->orWhere('course_id', $student->course_id);
            })
            ->latest('publish_date')->paginate(20);

        return view('student.notices', compact('items'));
    }
}
