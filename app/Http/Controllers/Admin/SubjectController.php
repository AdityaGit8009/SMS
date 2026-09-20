<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\{Course,Semester,Subject,Teacher}; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class SubjectController extends Controller {
 public function index(Request $r){ $q=Subject::with(['course','semester','teacher.user']); if($r->filled('course'))$q->where('course_id',$r->course); $items=$q->latest()->paginate(20)->withQueryString(); $courses=Course::orderBy('name')->get(); return view('admin.subjects.index',compact('items','courses')); }
 public function create(){ return view('admin.subjects.form',['item'=>new Subject,'courses'=>Course::where('status',true)->orderBy('name')->get(),'semesters'=>Semester::with('course')->orderBy('course_id')->orderBy('number')->get(),'teachers'=>Teacher::with('user')->get()]); }
 public function store(Request $r){ Subject::create($this->validated($r)); return redirect()->route('admin.subjects.index')->with('success','Subject created.'); }
 public function edit(Subject $subject){ return view('admin.subjects.form',['item'=>$subject,'courses'=>Course::where('status',true)->orderBy('name')->get(),'semesters'=>Semester::with('course')->orderBy('course_id')->orderBy('number')->get(),'teachers'=>Teacher::with('user')->get()]); }
 public function update(Request $r,Subject $subject){ $subject->update($this->validated($r,$subject)); return redirect()->route('admin.subjects.index')->with('success','Subject updated.'); }
 public function destroy(Subject $subject){ if($subject->attendances()->exists()) return back()->withErrors(['delete'=>'Subject has attendance data and cannot be deleted.']); $subject->delete(); return back()->with('success','Subject deleted.'); }
 private function validated(Request $r,?Subject $subject=null){ $d=$r->validate(['course_id'=>'required|exists:courses,id','semester_id'=>'required|exists:semesters,id','teacher_id'=>'nullable|exists:teachers,id','name'=>'required|max:191','code'=>['required','max:30',Rule::unique('subjects','code')->ignore($subject?->id)],'credits'=>'required|integer|min:1|max:20']); $sem=Semester::find($d['semester_id']); if(!$sem||$sem->course_id!=(int)$d['course_id']) abort(422,'Selected semester does not belong to course.'); return $d; }
}
