@extends('layouts.panel')
@section('title','Change Password') @section('page-title','Change Password') @section('page-subtitle','Update your security credentials')
@section('content')
<div class="row justify-content-center"><div class="col-xl-7 col-lg-9"><div class="sms-panel"><div class="sms-section-title"><div class="sms-title-icon"><i class="bi bi-shield-lock"></i></div><div><h4>Change Password</h4><p>Use a strong password that you do not use elsewhere.</p></div></div>
<form method="POST" action="{{ route('password.change.update') }}">@csrf @method('PUT')
@foreach([['current_password','Current Password','currentPassword'],['password','New Password','newPassword'],['password_confirmation','Confirm New Password','confirmPassword']] as $f)
<div class="sms-form-group"><label class="sms-form-label">{{ $f[1] }}</label><div class="sms-input-group"><i class="bi bi-lock"></i><input type="password" name="{{ $f[0] }}" id="{{ $f[2] }}" class="form-control sms-form-control sms-password-input" required><button type="button" class="sms-password-toggle" data-password-toggle="{{ $f[2] }}"><i class="bi bi-eye"></i></button></div></div>
@endforeach
<button class="btn sms-primary-btn sms-auto-width" type="submit"><i class="bi bi-shield-check"></i> Update Password</button></form></div></div></div>
@endsection
