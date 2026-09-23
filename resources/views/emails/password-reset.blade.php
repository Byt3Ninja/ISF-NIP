@extends('emails.layout.master')

@section('content')
@php
    $isRtl = ($resetLocale ?? 'en') === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $t = fn ($key, $replace = []) => __('email.password_reset_email.' . $key, $replace, $isRtl ? 'ar' : 'en');
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
    <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ $t('hello', ['name' => $resetUserName]) }}</h1>
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('intro') }}</p>
    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
        <tr>
            <td align="center">
                <a href="{{ $resetUrl }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: #4F46E5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">{{ $t('button') }}</a>
            </td>
        </tr>
    </table>
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('expire', ['count' => $resetExpireMinutes]) }}</p>
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('ignore') }}</p>
    <p style="margin: 0 0 8px 0; font-size: 13px; color: #6B7280;">{{ $t('fallback') }}</p>
    <p dir="ltr" style="margin: 0 0 20px 0; font-size: 12px; word-break: break-all; text-align: {{ $alignStart }};"><a href="{{ $resetUrl }}" target="_blank" style="color: #4F46E5;">{{ $resetUrl }}</a></p>
    <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
        {{ $t('regards') }}<br>
        <strong>{{ $t('team', ['platform_name' => $resetPlatformName]) }}</strong>
    </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
