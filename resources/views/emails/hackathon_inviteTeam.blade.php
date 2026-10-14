@extends('emails.layout.master')

@section('content')
@php
    $isRtl = app()->getLocale() === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $t = fn ($key, $replace = []) => __('email.hackathon_invite_email.' . $key, $replace, $isRtl ? 'ar' : 'en');

    $greetingName = trim((string) ($recipientName ?? ''));
    $isNew = $isNewUser ?? ($greetingName === '');

    $teamTitle = $teamTitle ?? (is_object($teamName ?? null) ? ($teamName->name ?? '') : ($teamName ?? ''));
    $hackathonTitle = $hackathonTitle ?? (is_object($teamName ?? null) ? ($teamName->cycle->name ?? __('admin.Hackathon')) : __('admin.Hackathon'));
    $expiryDuration = $expiryDuration ?? ($isRtl ? '24 ساعة' : '24 hours');
    $inviteUrl = $inviteUrl ?? ($isNew ? url('/register') : (is_object($teamName ?? null) && isset($teamName->cycle_id) ? url("dashboard/hackathon/details/{$teamName->cycle_id}") : url('dashboard')));
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
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ $isRtl ? 'مرحباً' : 'Hello' }} {{ $greetingName }}{{ $isRtl ? '،' : ',' }}</h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('body', ['team' => $teamTitle, 'hackathon' => $hackathonTitle]) }}</p>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">
                            @if($isNew)
                                {{ $t('new_user_hint') }}
                            @else
                                {{ $t('review_hint') }}
                            @endif
                        </p>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151; font-weight: 600;">{{ $t('expires_in', ['duration' => $expiryDuration]) }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $inviteUrl }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: #4F46E5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
                                        @if($isNew)
                                            {{ $t('cta_new_user') }}
                                        @else
                                            {{ $t('cta') }}
                                        @endif
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                            {{ $t('regards', ['org_name' => $org_name ?? $platformName]) }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
