<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
class ProfileController extends Controller {
 public function edit(){ return view('profile.edit'); }
 public function update(Request $request){
  $user=$request->user();
  $data=$request->validate(['name'=>['required','string','max:191'],'email'=>['required','email',Rule::unique('users')->ignore($user->id)],'phone'=>['nullable','string','max:30'],'profile_photo'=>['nullable','image','max:2048']]);
  if($request->hasFile('profile_photo')){ if($user->profile_photo) Storage::disk('public')->delete($user->profile_photo); $data['profile_photo']=$request->file('profile_photo')->store('profiles','public'); }
  $user->update($data); return back()->with('success','Profile updated successfully.');
 }
}
