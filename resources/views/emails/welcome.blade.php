@php
    $setting = App\Settings::first();
    $platformName = $setting ? ( $setting->Platform_Name ?? config('app.name') ) : config('app.name');
@endphp

@extends('emails.layout.master')

@section('content')
<div class="email-content">
    <div class="welcome-header">
        <h1>{{ __('email.welcome_email.subject') }}</h1>
        <p class="greeting">{{ __('email.welcome_email.dear') }} {{ $user->first_name_en }} {{ $user->last_name_en }},</p>
    </div>

    <div class="welcome-body">
        <p>{{ __('email.welcome_email.m1') }}</p>
        
        <div class="login-details">
            <p><strong>{{ __('email.welcome_email.m2') }}</strong></p>
            <p class="email-highlight">{{ $user->email }}</p>
        </div>

        <div class="password-info">
            <p><strong>{{ __('admin.Temporary_Password') }}:</strong> {{ $password }}</p>
            <p class="note">{{ __('admin.Please_change_your_password_after_first_login') }}</p>
        </div>

        <p>{{ __('email.welcome_email.m3') }}</p>
    </div>

    <div class="welcome-footer">
        <p>{{ __('email.welcome_email.best_regards') }}</p>
        <p>{{ $platformName }}</p>
    </div>

    <div class="action-buttons">
        <a href="{{ route('login') }}" class="btn btn-primary">
            {{ __('admin.Login_to_Platform') }}
        </a>
    </div>
</div>
@endsection

