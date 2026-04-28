@extends('emails.layout')

@section('title', 'Reset Your Password')

@section('header-title', 'Password Reset')

@section('content')
<h2>Reset Your Password</h2>

<p>Hello,</p>

<p>We received a request to reset your password for your account. Click the button below to reset your password:</p>

<div style="text-align: center;">
    <a href="{{ $resetUrl }}" class="button">Reset Password</a>
</div>

<p>Or copy and paste this link into your browser:</p>
<p style="word-break: break-all; color: #6366f1; font-size: 14px;">{{ $resetUrl }}</p>

<div class="info-box">
    <p><strong>⚠️ Security Notice:</strong> This link will expire in 60 minutes. If you didn't request this password reset, please ignore this email.</p>
</div>

<p>If you're having trouble clicking the button, copy and paste the URL above into your web browser.</p>

<p>Best regards,<br>
<strong>{{ config('app.name', 'Laravel Boilerplate') }} Team</strong></p>
@endsection
