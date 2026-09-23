@extends('emails.layout.master')
@section('content')

@php
    $locale = ($locale ?? 'en') === 'ar' ? 'ar' : 'en';
    $isRtl = $locale === 'ar';
    $dir = $isRtl ? 'rtl' : 'ltr';
    $align = $isRtl ? 'right' : 'left';

    // Resolve against the platform's default language rather than the app
    // locale: this view is rendered from a queued notification, where the
    // worker's locale has nothing to do with the platform setting.
    $t = function ($key, $replace = []) use ($locale) {
        return \Illuminate\Support\Facades\Lang::get('email.judge_account_welcome_email.' . $key, $replace, $locale);
    };

    $gender = trim(strtolower((string) ($judgerGender ?? '')));
    $salutation = $isRtl
        ? ($gender === 'female' ? $t('dear_female') : $t('dear_male'))
        : $t('dear');
@endphp
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F3F4F6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 40px 0;">
    <tr>
        <td align="center" style="padding: 0 16px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                <tr>
                    <td height="6" style="background-color: #4F46E5;"></td>
                </tr>
                <tr>
                    <td style="padding: 40px 40px 20px 40px; text-align: {{ $align }}; direction: {{ $dir }};">
    <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ $salutation }} {{ $judgerName }}{{ $isRtl ? '،' : ',' }}</h1>
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('intro', ['platform_name' => $judgeWelcomePlatformName]) }}</p>
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('access_intro') }}</p>
    @if(!empty($magicLoginUrl))
        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
        <tr>
            <td align="center">
                <a href="{{ $magicLoginUrl }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: #4F46E5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">{{ $t('login_button') }}</a>
            </td>
        </tr>
    </table>
    @endif
    <p style="margin: 0 0 10px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('capabilities_intro') }}</p>
    <ul style="margin: 0 0 20px 0; padding-{{ $isRtl ? 'right' : 'left' }}: 20px; font-size: 15px; line-height: 150%; color: #374151;">
        <li style="margin: 0 0 4px 0;">{{ $t('capability_1') }}</li>
        <li style="margin: 0 0 4px 0;">{{ $t('capability_2') }}</li>
        <li style="margin: 0 0 4px 0;">{{ $t('capability_3') }}</li>
        <li style="margin: 0 0 4px 0;">{{ $t('capability_4') }}</li>
    </ul>
    <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
        {{ $t('regards') }}<br>
        <strong>{{ $t('team', ['platform_name' => $judgeWelcomePlatformName]) }}</strong>
    </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
