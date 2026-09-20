<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class LoginController extends Controller {
 public function showLoginForm(){ return view('auth.login'); }
 public function login(Request $request){
  $credentials=$request->validate(['email'=>['required','email'],'password'=>['required']]);
  if(!Auth::attempt($credentials,$request->boolean('remember'))) return back()->withErrors(['email'=>'Invalid email or password.'])->onlyInput('email');
  $request->session()->regenerate(); $user=$request->user();
  if(!$user->status){ Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return back()->withErrors(['email'=>'Your account is inactive.'])->onlyInput('email'); }
  return match($user->role){ 'admin'=>redirect()->route('admin.dashboard'), 'teacher'=>redirect()->route('teacher.dashboard'), 'student'=>redirect()->route('student.dashboard'), default=>$this->invalidRole($request) };
 }
 private function invalidRole(Request $request){ Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login')->withErrors(['email'=>'Invalid account role.']); }
 public function logout(Request $request){ Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login')->with('status','Logged out successfully.'); }
}
