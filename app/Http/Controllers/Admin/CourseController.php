<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\{Course,Department}; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class CourseController extends Controller {
 public function index(Request $r){ $q=Course::with('department')->withCount('students'); if($r->filled('department'))$q->where('department_id',$r->department); $items=$q->latest()->paginate(15)->withQueryString(); $departments=Department::orderBy('name')->get(); return view('admin.courses.index',compact('items','departments')); }
 public function create(){ return view('admin.courses.form',['item'=>new Course,'departments'=>Department::where('status',true)->orderBy('name')->get()]); }
 public function store(Request $r){ $d=$this->validated($r); $d['status']=$r->boolean('status'); Course::create($d); return redirect()->route('admin.courses.index')->with('success','Course created.'); }
 public function edit(Course $course){ return view('admin.courses.form',['item'=>$course,'departments'=>Department::where('status',true)->orderBy('name')->get()]); }
 public function update(Request $r,Course $course){ $d=$this->validated($r,$course); $d['status']=$r->boolean('status'); $course->update($d); return redirect()->route('admin.courses.index')->with('success','Course updated.'); }
 public function destroy(Course $course){ if($course->students()->exists()) return back()->withErrors(['delete'=>'Course has students and cannot be deleted.']); $course->delete(); return back()->with('success','Course deleted.'); }
 private function validated(Request $r,?Course $course=null){ return $r->validate(['department_id'=>'required|exists:departments,id','name'=>'required|max:191','code'=>['required','max:30',Rule::unique('courses','code')->ignore($course?->id)],'duration_years'=>'required|integer|min:1|max:10','total_semesters'=>'required|integer|min:1|max:20','course_fee'=>'required|numeric|min:0','status'=>'nullable|boolean']); }
}
