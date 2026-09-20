<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Attendance,Course,ExamSchedule,FeeStructure,Notice,Student,Subject,Teacher,User};
class DashboardController extends Controller {
 public function index(){
  $students=Student::with(['course','semester','user'])->latest()->take(6)->get();
  $allStudents=Student::with('feePayments')->get();
  $pendingFees=$allStudents->sum(function($student){ $due=FeeStructure::where('course_id',$student->course_id)->where('academic_session_id',$student->academic_session_id)->sum('amount'); $paid=$student->feePayments->sum('amount'); return max($due-$paid,0); });
  $stats=['students'=>Student::count(),'teachers'=>Teacher::count(),'courses'=>Course::count(),'subjects'=>Subject::count(),'pending_fees'=>$pendingFees,'attendance_today'=>Attendance::whereDate('date',today())->where('status','present')->count()];
  $upcomingExams=ExamSchedule::with(['exam','subject'])->whereDate('exam_date','>=',today())->orderBy('exam_date')->take(5)->get();
  $notices=Notice::where('status',true)->orderByDesc('publish_date')->take(5)->get();
  return view('admin.dashboard',compact('stats','students','upcomingExams','notices'));
 }
}
