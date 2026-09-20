<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\Department; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class DepartmentController extends Controller {
 public function index(){ $items=Department::withCount(['courses','teachers'])->latest()->paginate(15); return view('admin.departments.index',compact('items')); }
 public function create(){ return view('admin.departments.form',['item'=>new Department]); }
 public function store(Request $r){ $d=$r->validate(['name'=>'required|max:191','code'=>'required|max:30|unique:departments,code','status'=>'nullable|boolean']); $d['status']=$r->boolean('status'); Department::create($d); return redirect()->route('admin.departments.index')->with('success','Department created.'); }
 public function edit(Department $department){ return view('admin.departments.form',['item'=>$department]); }
 public function update(Request $r,Department $department){ $d=$r->validate(['name'=>'required|max:191','code'=>['required','max:30',Rule::unique('departments','code')->ignore($department->id)],'status'=>'nullable|boolean']); $d['status']=$r->boolean('status'); $department->update($d); return redirect()->route('admin.departments.index')->with('success','Department updated.'); }
 public function destroy(Department $department){ if($department->courses()->exists()||$department->teachers()->exists()) return back()->withErrors(['delete'=>'Department is in use and cannot be deleted.']); $department->delete(); return back()->with('success','Department deleted.'); }
}
