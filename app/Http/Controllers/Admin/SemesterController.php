<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\{Course,Semester}; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class SemesterController extends Controller {
 public function index(){ $items=Semester::with('course')->orderBy('course_id')->orderBy('number')->paginate(20); return view('admin.semesters.index',compact('items')); }
 public function create(){ return view('admin.semesters.form',['item'=>new Semester,'courses'=>Course::where('status',true)->orderBy('name')->get()]); }
 public function store(Request $r){ $d=$r->validate(['course_id'=>'required|exists:courses,id','name'=>'required|max:50','number'=>'required|integer|min:1|max:20']); if(Semester::where('course_id',$d['course_id'])->where('number',$d['number'])->exists()) return back()->withErrors(['number'=>'This semester number already exists for the selected course.'])->withInput(); Semester::create($d); return redirect()->route('admin.semesters.index')->with('success','Semester created.'); }
 public function edit(Semester $semester){ return view('admin.semesters.form',['item'=>$semester,'courses'=>Course::where('status',true)->orderBy('name')->get()]); }
 public function update(Request $r,Semester $semester){ $d=$r->validate(['course_id'=>'required|exists:courses,id','name'=>'required|max:50','number'=>'required|integer|min:1|max:20']); if(Semester::where('course_id',$d['course_id'])->where('number',$d['number'])->where('id','!=',$semester->id)->exists()) return back()->withErrors(['number'=>'This semester number already exists for the selected course.'])->withInput(); $semester->update($d); return redirect()->route('admin.semesters.index')->with('success','Semester updated.'); }
 public function destroy(Semester $semester){ if($semester->subjects()->exists()) return back()->withErrors(['delete'=>'Semester is in use.']); $semester->delete(); return back()->with('success','Semester deleted.'); }
}
