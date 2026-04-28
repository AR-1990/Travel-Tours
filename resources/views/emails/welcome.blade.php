@extends('emails.layout')

@section('title', 'Welcome')

@section('header-title', 'Welcome!')

@section('content')
<h2>Welcome to {{ config('app.name', 'Laravel Boilerplate') }}!</h2>

<p>Hello {{ $user->first_name }},</p>

<p>We're excited to have you on board! Your account has been successfully created.</p>

<div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0e7ff 100%); padding: 24px; border-radius: 8px; margin: 24px 0;">
    <p style="margin: 0; color: #1e40af;"><strong>Your Account Details:</strong></p>
    <p style="margin: 8px 0 0 0; color: #1e40af;">
        <strong>Name:</strong> {{ $user->first_name }} {{ $user->last_name }}<br>
        <strong>Email:</strong> {{ $user->email }}
    </p>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/dashboard') }}" class="button">Go to Dashboard</a>
</div>

<p>Here are some things you can do:</p>
<ul style="color: #4b5563;">
    <li>Complete your profile</li>
    <li>Explore the dashboard</li>
    <li>Update your settings</li>
</ul>

<p>If you have any questions, feel free to reach out to our support team.</p>

<p>Best regards,<br>
<strong>{{ config('app.name', 'Laravel Boilerplate') }} Team</strong></p>
@endsection
