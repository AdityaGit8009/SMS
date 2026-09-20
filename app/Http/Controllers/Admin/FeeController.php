<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Student;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $structures = FeeStructure::with(['course', 'academicSession'])
            ->latest()->paginate(12, ['*'], 'structures_page');
        $payments = FeePayment::with(['student.user', 'feeStructure'])
            ->latest('payment_date')->paginate(15, ['*'], 'payments_page');
        $courses = Course::orderBy('name')->get();
        $sessions = AcademicSession::latest('start_date')->get();
        $students = Student::with('user')->where('status', true)->orderBy('roll_no')->get();

        return view('admin.fees.index', compact('structures', 'payments', 'courses', 'sessions', 'students'));
    }

    public function storeStructure(Request $request)
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'name' => ['required', 'max:191'],
            'type' => ['required', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $exists = FeeStructure::where('course_id', $data['course_id'])
            ->where('academic_session_id', $data['academic_session_id'])
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'This fee structure already exists for the selected course and session.'])->withInput();
        }

        FeeStructure::create($data);
        return back()->with('success', 'Fee structure created.');
    }

    public function deleteStructure(FeeStructure $feeStructure)
    {
        if ($feeStructure->payments()->exists()) {
            return back()->withErrors(['delete' => 'This fee structure has payment records and cannot be deleted.']);
        }

        $feeStructure->delete();
        return back()->with('success', 'Fee structure deleted.');
    }

    public function storePayment(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'fee_structure_id' => ['required', 'exists:fee_structures,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,upi,card,bank_transfer,cheque'],
            'transaction_id' => ['nullable', 'max:191'],
            'remarks' => ['nullable', 'string'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $structure = FeeStructure::findOrFail($data['fee_structure_id']);

        abort_unless(
            $structure->course_id === $student->course_id
                && $structure->academic_session_id === $student->academic_session_id,
            422,
            'Selected fee structure does not belong to this student.'
        );

        $alreadyPaid = FeePayment::where('student_id', $student->id)
            ->where('fee_structure_id', $structure->id)
            ->sum('amount');
        $remaining = max((float) $structure->amount - (float) $alreadyPaid, 0);

        if ((float) $data['amount'] > $remaining) {
            return back()->withErrors([
                'amount' => 'Payment exceeds the remaining amount of ₹'.number_format($remaining, 2).'.',
            ])->withInput();
        }

        $data['received_by'] = $request->user()->id;
        $payment = FeePayment::create($data);

        return redirect()->route('admin.fees.receipt', $payment)->with('success', 'Payment recorded.');
    }

    public function receipt(FeePayment $payment)
    {
        $payment->load(['student.user', 'student.course', 'student.academicSession', 'feeStructure']);
        return view('admin.fees.receipt', compact('payment'));
    }

    public function studentLedger(Student $student)
    {
        $student->load(['user', 'course', 'academicSession', 'feePayments.feeStructure']);
        $structures = FeeStructure::where('course_id', $student->course_id)
            ->where('academic_session_id', $student->academic_session_id)->get();
        $due = $structures->sum('amount');
        $paid = $student->feePayments->sum('amount');
        $pending = max($due - $paid, 0);

        return view('admin.fees.ledger', compact('student', 'structures', 'due', 'paid', 'pending'));
    }
}
