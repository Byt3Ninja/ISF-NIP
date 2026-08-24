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
                        {{--  <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">{{ __('email.startup_mentorship_session_notify_email.subject') }}</p>  --}}
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ __('email.hello') . ' ' . trim($party->name) }},</h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.startup_mentorship_session_notify_email.informing') }}</p>

                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ __('email.startup_mentorship_session_notify_email.session_details') }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.mentor') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $mentorName }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.date') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionDate }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.time') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionTime }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.session_type') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionType == 'online' ? __('email.online') : __('email.offline') }}</td>
                                        </tr>
                                        @if( $sessionType == 'online' || $sessionType == 'both' )
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.session_link') }}:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: {{ $mainColor }}; font-weight: 600; text-align: {{ $alignEnd }};"><a href="{{ $sessionLink }}" style="color: {{ $mainColor }};">{{ __('email.Join_Meeting') }}</a></td>
                                            </tr>
                                        @endif
                                        @if( $sessionType == 'offline' || $sessionType == 'both' )
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.session_address') }}:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $location }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.startup_mentorship_session_notify_email.map_location_link') }}:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: {{ $mainColor }}; font-weight: 600; text-align: {{ $alignEnd }};"><a href="{{ $mapLink }}" style="color: {{ $mainColor }};">{{ __('email.open_maps') }}</a></td>
                                            </tr>
                                        @endif
                                        @if( !empty( $attachments ) )
                                            <tr>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280; width: 150px; vertical-align: top;">{{ __('email.Attachments') }}:</td>
                                                <td style="padding-bottom: 0px; font-size: 14px; text-align: {{ $alignEnd }};">
                                                    @foreach( $attachments as $attachment )
                                                        <a href="{{ $attachment['url'] }}" style="display: block; margin-bottom: 4px; color: {{ $mainColor }}; text-decoration: underline; font-weight: 600;">📎 {{ $attachment['name'] }}</a>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endif
                                        @if( $sessionNotes != '-' )
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px; vertical-align: top !important;">{{ __('email.mentor_notify_email.session_notes') }}:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionNotes }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.startup_mentorship_session_notify_email.ensure_on_time') }}</p>
                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.startup_mentorship_session_notify_email.wishes') }}</p>

                        @if($isRtl)
                            <p style="margin: 20px 0 0 0; font-size: 15px; color: #374151;">{{ __('email.action_session_details') }}</p>
                        @endif

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
