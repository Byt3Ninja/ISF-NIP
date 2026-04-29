@extends('emails.layout.master')

@section('content')
<div class="email-content">
    <div class="welcome-header">
        <h1>{{ __('notifications.welcome_judger.subject') }}</h1>
        <p class="greeting">{{ __('email.welcome_email.dear') }} {{ $data['name'] ?? $data['first_name_en'] ?? 'Judger' }},</p>
                                                </div>

    <div class="welcome-body">
        <p>{{ __('notifications.welcome_judger.database') }}</p>
        
        <div class="judger-info">
            <p><strong>{{ __('admin.Role') }}:</strong> {{ __('admin.Judger') }}</p>
            <p><strong>{{ __('admin.Email') }}:</strong> {{ $data['email'] ?? 'N/A' }}</p>
                                                </div>

        <div class="password-info">
            <p><strong>{{ __('admin.Temporary_Password') }}:</strong> {{ $data['password'] ?? 'N/A' }}</p>
            <p class="note">{{ __('admin.Please_change_your_password_after_first_login') }}</p>
                                                </div>

        <p>{{ __('admin.As_a_judger_you_will_have_access_to') }}:</p>
        <ul>
            <li>{{ __('admin.Evaluate_startup_proposals') }}</li>
            <li>{{ __('admin.Review_business_plans') }}</li>
            <li>{{ __('admin.Participate_in_selection_processes') }}</li>
            <li>{{ __('admin.Provide_expert_feedback') }}</li>
        </ul>
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