@extends('emails.layout.master')

@section('content')
@php
    $mainColor = '#4F46E5';
    $bgColor = '#F3F4F6';
    $isRtl = app()->getLocale() === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $alignEnd = $isRtl ? 'left' : 'right';

    // Gendered greeting, matching emails.mentorship_notify_mentor_email. The
    // recipient is the team leader; fall back to the neutral "Dear" when the
    // gender column is empty so the greeting is never blank.
    $gender = trim(strtolower((string) ($leaderGender ?? '')));
    $greeting = match ($gender) {
        'male' => __('email.dear_male'),
        'female' => __('email.dear_female'),
        default => __('email.hackathon_email.dear'),
    };
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
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ trim($greeting) . ' ' . trim($leaderName) }},</h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">
                            @if($status == 'Accept')
                                {{ __('email.hackathon_email.in_body_1', ['member' => $memberName]) }}
                            @elseif($status == 'Reject')
                                {{ __('email.hackathon_email.in_body_2', ['member' => $memberName]) }}
                            @elseif($status == 'cancelled')
                                {{ __('email.hackathon_email.in_body_4') }}
                            @endif
                        </p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.hackathon_email.in_body_3') }}</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $teams->name ?? '' }}</td>
                                        </tr>
                                        @if($memberName !== '')
                                            <tr>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.hackathon_email.member') }}</td>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $memberName }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>

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
