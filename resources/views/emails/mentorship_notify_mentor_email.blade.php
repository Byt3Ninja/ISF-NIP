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
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">
                            @if($isRtl)
                                عزيزي/عزيزتي {{ trim($party->name) }}،
                            @else
                                Dear {{ trim($party->name) }},
                            @endif
                        </h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.mentor_notify_email.informing') }}</p>

                        <p style="margin: 0 0 10px 0; font-size: 16px; font-weight: 700; color: #111827;">{{ __('email.mentor_notify_email.session_details') }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.mentor_notify_email.founder') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">
                                                @if(!empty($founder))
                                                    <a href="{{ route('clients.profile_view', $founder->id) }}" style="color: {{ $mainColor }};">{{ $founder->name }}</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        @if(!empty($startup))
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.mentor_notify_email.startup_name') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">
                                                <a href="{{ route('company.profile_view', $startup->id) }}" style="color: {{ $mainColor }};">{{ $startupName }}</a>
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.mentor_notify_email.date') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionDate }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.mentor_notify_email.time') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionTime }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.mentor_notify_email.session_type') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionType == 'online' ? __('email.mentor_notify_email.session_type_online') : __('email.mentor_notify_email.session_type_in_person') }}</td>
                                        </tr>
                                        @if( trim( $sessionNotes ?? '' ) )
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px; vertical-align: top !important;">{{ __('email.mentor_notify_email.session_notes') }}:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionNotes }}</td>
                                            </tr>
                                        @endif
                                        @if( !empty( $attachments ) )
                                            <tr>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280; vertical-align: top;">{{ __('email.Attachments') }}:</td>
                                                <td style="padding-bottom: 0px; font-size: 14px; text-align: {{ $alignEnd }};">
                                                    @foreach( $attachments as $attachment )
                                                        <a href="{{ $attachment['url'] }}" style="display: block; margin-bottom: 4px; color: {{ $mainColor }}; text-decoration: underline; font-weight: 600;">📎 {{ $attachment['name'] }}</a>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endif
                                        {{-- A declined session has no meeting link or location to share. --}}
                                        @if(empty($isRejected))
                                        @if( $sessionType == 'online' || $sessionType == 'both' )
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.mentor_notify_email.meeting_link') }}:</td>
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
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.mentor_notify_email.ensure_on_time') }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $viewSessionUrl }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: {{ $mainColor }}; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">{{ __('email.cta.view_session') }}</a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                            @if($isRtl)
                                مع أطيب التحيات،<br>
                            @else
                                {{ __('email.regards') }}<br>
                            @endif
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
