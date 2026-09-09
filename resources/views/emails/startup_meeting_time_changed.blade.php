@extends('emails.layout.master')

@section('content')
@php
    // A cancelled meeting is an alert, a reschedule is informational, so the
    // accent bar changes colour while the layout stays identical.
    $mainColor = $cancelled ? '#DC2626' : '#4F46E5';
    $bgColor = '#F3F4F6';
    $isRtl = app()->getLocale() === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $alignEnd = $isRtl ? 'left' : 'right';
    $key = $cancelled ? 'cancelled' : 'rescheduled';
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
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ __('email.startup_meeting_changed_email.greeting', ['name' => trim($recipient->name ?? '')]) }}</h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.startup_meeting_changed_email.' . $key . '.intro', ['program_name' => $programName, 'startup_name' => $startupName]) }}</p>

                        @unless ($cancelled)
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                                        <p style="margin: 0 0 15px 0; font-size: 14px; color: #111827; font-weight: bold;">{{ __('email.startup_meeting_changed_email.new_meeting_time') }}</p>
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_meeting_changed_email.date') }}:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $meetingDate }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 0; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_meeting_changed_email.time') }}:</td>
                                                <td style="padding-bottom: 0; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $meetingTime }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        @endunless

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.startup_meeting_changed_email.' . $key . '.please_check') }}</p>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 20px 0;">
                            <tr>
                                <td align="center" style="background-color: {{ $mainColor }}; border-radius: 6px;">
                                    <a href="{{ $dashboardUrl }}" style="display: inline-block; padding: 12px 28px; font-size: 15px; font-weight: 600; color: #ffffff; text-decoration: none;">{{ __('email.startup_meeting_changed_email.open_dashboard') }}</a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.startup_meeting_changed_email.inquiry_suggest') }}</p>

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
