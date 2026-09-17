@extends('emails.layout.master')

@section('content')
@php
    $mainColor = '#4F46E5';
    $bgColor = '#F3F4F6';
    $isRtl = app()->getLocale() === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $alignEnd = $isRtl ? 'left' : 'right';
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
                        {{--  <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">{{ __('email.startup_mentorship_session_notify_email.approval.subject') }}</p>  --}}
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ __('email.hello') . ' ' . trim($party->name) }},</h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.startup_mentorship_session_notify_email.approval.informing') }}</p>

                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ __('email.startup_mentorship_session_notify_email.approval.session_details') }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.approval.mentor') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $mentorName }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.approval.date') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionDate }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.approval.time') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionTime }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.approval.session_type') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionType == 'online' ? __('email.startup_mentorship_session_notify_email.approval.session_type_online') : __('email.startup_mentorship_session_notify_email.approval.session_type_in_person') }}</td>
                                        </tr>
                                        @if( $sessionType == 'online' || $sessionType == 'both' )
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.approval.meeting_link') }}:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: {{ $mainColor }}; font-weight: 600; text-align: {{ $alignEnd }};"><a href="{{ $sessionLink }}" style="color: {{ $mainColor }};">{{ $sessionLink }}</a></td>
                                            </tr>
                                            @if(!empty($passcode))
                                                <tr>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.Passcode') }}:</td>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $passcode }}</td>
                                                </tr>
                                            @endif
                                        @else
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.Location') }}:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">
                                                    @if(!empty($locationName) && !empty($mapLink))
                                                        <a href="{{ $mapLink }}" style="color: {{ $mainColor }};">{{ $locationName }}</a>
                                                    @else
                                                        {{ $locationName ?: '-' }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.address') }}:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $address ?: '-' }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.startup_mentorship_session_notify_email.approval.ensure_on_time') }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $viewSessionUrl }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: {{ $mainColor }}; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">{{ __('email.cta.view_session') }}</a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                            {{ __('email.regards') }}<br>
                            <strong>{{ __('email.team', ['platform_name' => $platformName]) }}</strong>
                        </p>
                    </td>
                </tr>
                {{--  <tr>
                    <td align="center" style="background-color: #F9FAFB; padding: 20px; border-top: 1px solid #E5E7EB;">
                        <p style="font-size: 12px; color: #9CA3AF; margin: 0;">© {{ date('Y') }} {{ $platformName }}. All rights reserved.</p>
                    </td>
                </tr>  --}}
            </table>
        </td>
    </tr>
</table>
@endsection
