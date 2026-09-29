@extends('emails.layout.master')

@section('content')
@php
    $mainColor = '#4F46E5';
    $bgColor = '#F3F4F6';
    $isRtl = app()->getLocale() === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $alignEnd = $isRtl ? 'left' : 'right';

    // The recipient may be an AnonymousNotifiable (invitation sent to a bare
    // email address with no registered user yet), so the greeting has to work
    // with no name and no gender at all.
    $gender = trim(strtolower((string) ($recipientGender ?? '')));
    $greeting = match ($gender) {
        'male' => __('email.dear_male'),
        'female' => __('email.dear_female'),
        default => __('email.hackathon_email.dear'),
    };
    $greetingLine = trim(trim($greeting) . ' ' . trim((string) ($recipientName ?? '')));

    $leaderName = trim((string) ($leaderName ?? ''));
    $teamTitle = $teamName->name ?? '';
    $inviteUrl = isset($teamName->cycle_id) ? url("dashboard/hackathon/details/{$teamName->cycle_id}") : url('dashboard');
@endphp

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: {{ $bgColor }}; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 40px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                <tr>
                    <td height="6" style="background-color: {{ $mainColor }};"></td>
                </tr>
                <tr>
                    <td style="padding: 40px 40px 20px 40px; text-align: {{ $alignStart }}; direction: {{ $dirAttr }};">
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ $greetingLine }},</h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.hackathon_invite_email.invited', ['team' => $teamTitle]) }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="padding-bottom: {{ $leaderName !== '' ? '10px' : '0px' }}; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.hackathon_invite_email.team') }}</td>
                                            <td style="padding-bottom: {{ $leaderName !== '' ? '10px' : '0px' }}; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $teamTitle }}</td>
                                        </tr>
                                        @if($leaderName !== '')
                                            <tr>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.hackathon_invite_email.invited_by') }}</td>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $leaderName }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.hackathon_invite_email.cta_hint') }}</p>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
                            <tr>
                                <td align="center" style="border-radius: 6px; background-color: {{ $mainColor }};">
                                    <a href="{{ $inviteUrl }}" style="display: inline-block; padding: 12px 28px; font-size: 15px; font-weight: bold; color: #ffffff; text-decoration: none; border-radius: 6px;">{{ __('email.hackathon_invite_email.cta') }}</a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 150%; color: #6B7280;">{{ __('email.hackathon_invite_email.expires') }}</p>

                        <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                            {{ __('email.regards') }}<br>
                            <strong>{{ __('email.team', ['platform_name' => $org_name ?? $platformName]) }}</strong>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
