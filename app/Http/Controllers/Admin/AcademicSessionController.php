<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\AcademicSession; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class AcademicSessionController extends Controller {
 public function index(){ $items=AcademicSession::latest('start_date')->paginate(15); return view('admin.academic-sessions.index',compact('items')); }
 public function create(){ return view('admin.academic-sessions.form',['item'=>new AcademicSession]); }
 public function store(Request $r){ $d=$r->validate(['name'=>['required','max:50','unique:academic_sessions,name'],'start_date'=>'required|date','end_date'=>'required|date|after:start_date','is_current'=>'nullable|boolean']); if($r->boolean('is_current')) AcademicSession::query()->update(['is_current'=>false]); $d['is_current']=$r->boolean('is_current'); AcademicSession::create($d); return redirect()->route('admin.academic-sessions.index')->with('success','Academic session created.'); }
 public function edit(AcademicSession $academicSession){ return view('admin.academic-sessions.form',['item'=>$academicSession]); }
 public function update(Request $r, AcademicSession $academicSession){ $d=$r->validate(['name'=>['required','max:50',Rule::unique('academic_sessions','name')->ignore($academicSession->id)],'start_date'=>'required|date','end_date'=>'required|date|after:start_date','is_current'=>'nullable|boolean']); if($r->boolean('is_current')) AcademicSession::where('id','!=',$academicSession->id)->update(['is_current'=>false]); $d['is_current']=$r->boolean('is_current'); $academicSession->update($d); return redirect()->route('admin.academic-sessions.index')->with('success','Academic session updated.'); }
 public function destroy(AcademicSession $academicSession){ if($academicSession->students()->exists()) return back()->withErrors(['delete'=>'Cannot delete a session used by students.']); $academicSession->delete(); return back()->with('success','Academic session deleted.'); }
}
