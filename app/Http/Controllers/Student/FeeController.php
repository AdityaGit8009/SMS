<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;
        abort_unless($student, 404);
        $student->load(['feePayments.feeStructure', 'course', 'academicSession']);

        $structures = FeeStructure::where('course_id', $student->course_id)
            ->where('academic_session_id', $student->academic_session_id)->get();
        $due = $structures->sum('amount');
        $paid = $student->feePayments->sum('amount');
        $pending = max($due - $paid, 0);

        return view('student.fees', compact('student', 'structures', 'due', 'paid', 'pending'));
    }
}
