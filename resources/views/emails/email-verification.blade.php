@extends('emails.layout')

@section('title', 'Verify Your Email')

@section('header-title', 'Email Verification')

@section('content')
<h2>Verify Your Email Address</h2>

<p>Hello{{ $userName ? ', ' . $userName : '' }},</p>

<p>Thank you for registering with {{ config('app.name', 'Laravel Boilerplate') }}! Please verify your email address by clicking the button below:</p>

<div style="text-align: center;">
    <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
</div>

<p>Or copy and paste this link into your browser:</p>
<p style="word-break: break-all; color: #6366f1; font-size: 14px;">{{ $verificationUrl }}</p>

<div class="info-box">
    <p><strong>ℹ️ Note:</strong> This verification link will expire in 24 hours. If you didn't create an account, please ignore this email.</p>
</div>

<p>Once verified, you'll be able to access all features of your account.</p>

<p>Best regards,<br>
<strong>{{ config('app.name', 'Laravel Boilerplate') }} Team</strong></p>
@endsection
