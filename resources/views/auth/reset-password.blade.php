@extends('layouts.auth')
@section('title','Reset Password')
@section('content')
<div class="sms-auth-wrapper"><div class="sms-small-auth-card"><div class="sms-auth-icon"><i class="bi bi-shield-lock"></i></div><h2>Reset Password</h2><p class="sms-auth-description">Create a new secure password.</p>@if($errors->any())<div class="alert alert-danger sms-alert">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}">
<div class="sms-form-group"><label class="sms-form-label">Email</label><div class="sms-input-group"><i class="bi bi-envelope"></i><input type="email" name="email" value="{{ old('email',$email) }}" class="form-control sms-form-control" required></div></div>
<div class="sms-form-group"><label class="sms-form-label">New Password</label><div class="sms-input-group"><i class="bi bi-lock"></i><input type="password" id="resetPassword" name="password" class="form-control sms-form-control sms-password-input" required><button type="button" class="sms-password-toggle" data-password-toggle="resetPassword"><i class="bi bi-eye"></i></button></div></div>
<div class="sms-form-group"><label class="sms-form-label">Confirm Password</label><div class="sms-input-group"><i class="bi bi-lock-fill"></i><input type="password" id="resetPasswordConfirmation" name="password_confirmation" class="form-control sms-form-control sms-password-input" required><button type="button" class="sms-password-toggle" data-password-toggle="resetPasswordConfirmation"><i class="bi bi-eye"></i></button></div></div>
<button class="btn sms-primary-btn" type="submit">Reset Password</button></form></div></div>
@endsection
