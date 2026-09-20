@extends('layouts.auth')
@section('title','Forgot Password')
@section('content')
<div class="sms-auth-wrapper"><div class="sms-small-auth-card"><a href="{{ route('login') }}" class="sms-back-link"><i class="bi bi-arrow-left"></i> Back to Login</a><div class="sms-auth-icon"><i class="bi bi-key"></i></div><h2>Forgot Password?</h2><p class="sms-auth-description">Enter your registered email address. We will send you a password reset link.</p>
@if(session('status'))<div class="alert alert-success sms-alert">{{ session('status') }}</div>@endif @if($errors->any())<div class="alert alert-danger sms-alert">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.email') }}">@csrf<div class="sms-form-group"><label class="sms-form-label">Email Address</label><div class="sms-input-group"><i class="bi bi-envelope"></i><input type="email" name="email" value="{{ old('email') }}" class="form-control sms-form-control" required autofocus></div></div><button class="btn sms-primary-btn" type="submit">Send Reset Link <i class="bi bi-send"></i></button></form></div></div>
@endsection
