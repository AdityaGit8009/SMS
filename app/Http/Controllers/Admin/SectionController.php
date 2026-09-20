<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\{Course,Section}; use Illuminate\Http\Request;
class SectionController extends Controller {
 public function index(){ $items=Section::with('course')->latest()->paginate(20); return view('admin.sections.index',compact('items')); }
 public function create(){ return view('admin.sections.form',['item'=>new Section,'courses'=>Course::where('status',true)->orderBy('name')->get()]); }
 public function store(Request $r){ $d=$r->validate(['course_id'=>'nullable|exists:courses,id','name'=>'required|max:50','code'=>'required|max:20']); if(Section::where('course_id',$d['course_id']??null)->where('code',$d['code'])->exists()) return back()->withErrors(['code'=>'Section code already exists for this course.'])->withInput(); Section::create($d); return redirect()->route('admin.sections.index')->with('success','Section created.'); }
 public function edit(Section $section){ return view('admin.sections.form',['item'=>$section,'courses'=>Course::where('status',true)->orderBy('name')->get()]); }
 public function update(Request $r,Section $section){ $d=$r->validate(['course_id'=>'nullable|exists:courses,id','name'=>'required|max:50','code'=>'required|max:20']); if(Section::where('course_id',$d['course_id']??null)->where('code',$d['code'])->where('id','!=',$section->id)->exists()) return back()->withErrors(['code'=>'Section code already exists for this course.'])->withInput(); $section->update($d); return redirect()->route('admin.sections.index')->with('success','Section updated.'); }
 public function destroy(Section $section){ if($section->students()->exists()) return back()->withErrors(['delete'=>'Section is assigned to students.']); $section->delete(); return back()->with('success','Section deleted.'); }
}
