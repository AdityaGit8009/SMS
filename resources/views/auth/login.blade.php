@extends('layouts.auth')
@section('title','Login')
@section('content')
<div class="sms-auth-wrapper"><div class="sms-auth-card">
<div class="sms-auth-brand-panel"><div><div class="sms-auth-logo"><i class="bi bi-mortarboard-fill"></i></div><h1>Student Management System</h1><p>Manage students, teachers, attendance, fees, examinations and academic activities from one secure platform.</p></div><div class="sms-auth-brand-footer"><i class="bi bi-shield-check"></i> Secure Role-Based Access</div></div>
<div class="sms-auth-form-panel"><div class="sms-auth-form-container"><div class="sms-auth-heading"><span class="sms-auth-mobile-logo"><i class="bi bi-mortarboard-fill"></i></span><h2>Welcome Back</h2><p>Sign in to continue to your dashboard.</p></div>
@if(session('status'))<div class="alert alert-success sms-alert">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger sms-alert">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('login.submit') }}">@csrf
<div class="sms-form-group"><label class="sms-form-label">Email Address</label><div class="sms-input-group"><i class="bi bi-envelope"></i><input type="email" name="email" value="{{ old('email') }}" class="form-control sms-form-control" placeholder="Enter your email" required autofocus></div></div>
<div class="sms-form-group"><div class="d-flex justify-content-between align-items-center"><label class="sms-form-label">Password</label><a href="{{ route('password.request') }}" class="sms-forgot-link">Forgot Password?</a></div><div class="sms-input-group"><i class="bi bi-lock"></i><input type="password" name="password" id="loginPassword" class="form-control sms-form-control sms-password-input" placeholder="Enter password" required><button type="button" class="sms-password-toggle" data-password-toggle="loginPassword"><i class="bi bi-eye"></i></button></div></div>
<div class="form-check sms-remember"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Remember me</label></div>
<button type="submit" class="btn sms-primary-btn"><span>Sign In</span><i class="bi bi-arrow-right"></i></button></form>
<div class="sms-auth-note"><i class="bi bi-info-circle"></i> Access is available to authorised administrators, teachers and students.</div></div></div>
</div></div>
@endsection
