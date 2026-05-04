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

        <p>{{ __('email.welcome_email.m3') }}</p>
    </div>

    @isset($magicLoginUrl)
    <div class="action-buttons" style="text-align:center;margin:20px 0;">
        <!--[if mso]>
        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $magicLoginUrl }}" style="height:46px;v-text-anchor:middle;width:240px;" arcsize="11%" stroke="f" fillcolor="#1aa19c">
            <w:anchorlock/>
            <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">{{ __('email.welcome_email.login_button') }}</center>
        </v:roundrect>
        <![endif]-->
        <!--[if !mso]><!-- -->
        <a href="{{ $magicLoginUrl }}"
           target="_blank"
           class="btn btn-primary"
           style="background-color:#1aa19c;border-radius:5px;color:#ffffff;display:inline-block;font-family:Montserrat, Arial, sans-serif;font-size:15px;font-weight:bold;line-height:46px;text-align:center;text-decoration:none;width:240px;-webkit-text-size-adjust:none;mso-hide:all;">
            {{ __('email.welcome_email.login_button') }}
        </a>
        <!--<![endif]-->
        <p style="color:#888888;font-size:13px;font-style:italic;margin-top:10px;">
            {{ __('email.welcome_email.login_link_note') }}
        </p>
    </div>
    @endisset

    <div class="welcome-footer">
        <p>{{ __('email.welcome_email.best_regards') }}</p>
        <p>{{ $platformName }}</p>
    </div>
</div>
@endsection

