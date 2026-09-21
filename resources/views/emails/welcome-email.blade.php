@php
    $setting = App\Settings::first();
    $platformName = $setting ? ( $setting->Platform_Name ?? config('app.name') ) : config('app.name');
@endphp

@extends('emails.layout.master')
@section('content')
@php
    $isRtl = app()->getLocale() === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
@endphp
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F3F4F6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 40px 0;">
    <tr>
        <td align="center" style="padding: 0 16px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                <tr>
                    <td height="6" style="background-color: #4F46E5;"></td>
                </tr>
                <tr>
                    <td style="padding: 40px 40px 20px 40px; text-align: {{ $alignStart }}; direction: {{ $dirAttr }};">
    <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ trim(strtolower($user->gender ?? '')) == 'female' ? __('email.new_user_welcome_email.dear_female') : __('email.new_user_welcome_email.dear_male') }} {{ $user->first_name_en . ' ' . $user->last_name_en }}{{ $isRtl ? '،' : ',' }}</h1>
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.new_user_welcome_email.m1', ['platform_name' => $platformName]) }}</p>
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.new_user_welcome_email.m2') }}</p>
    @isset($magicLoginUrl)
        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
        <tr>
            <td align="center">
                <a href="{{ $magicLoginUrl }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: #4F46E5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">{{ __('email.new_user_welcome_email.login_button') }}</a>
            </td>
        </tr>
    </table>
        <p style="margin: 0 0 20px 0; font-size: 12px; color: #9CA3AF; text-align: center; font-style: italic;">{{ __('email.welcome_email.login_link_note') }}</p>
    @endisset
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.new_user_welcome_email.m3', ['platform_name' => $platformName]) }}</p>
    <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
        {{ __('email.new_user_welcome_email.best_regards') }}<br>
        <strong>{{ __('email.new_user_welcome_email.team', ['platform_name' => $platformName]) }}</strong>
    </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
