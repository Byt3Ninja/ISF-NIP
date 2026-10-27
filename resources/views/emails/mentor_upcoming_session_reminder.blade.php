@extends('emails.layout.master')

@section('content')
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F3F4F6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 40px 0;">
    <tr>
        <td align="center" style="padding: 0 16px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                <tr>
                    <td height="6" style="background-color: #4F46E5;"></td>
                </tr>
                <tr>
                    <td dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" style="padding: 40px 40px 20px 40px; text-align: {{ $locale === 'ar' ? 'right' : 'left' }}; direction: {{ $locale === 'ar' ? 'rtl' : 'ltr' }};">
                        <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">{{ __('email.mentor_session_reminder.eyebrow') }}</p>
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ __('email.mentor_session_reminder.greeting', ['name' => $mentorName ?: '-']) }}</h1>
                        <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.mentor_session_reminder.intro', ['founder' => $founderName ?: '-']) }}</p>

                        {{-- Session details --}}
                        <h3 style="margin: 0 0 12px 0; font-size: 17px; color: #111827; font-weight: bold; border-bottom: 1px solid #E5E7EB; padding-bottom: 8px;">{{ __('email.mentor_session_reminder.session_details') }}</h3>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
                            <tr>
                                <td width="140" valign="top" style="padding: 6px 0; font-size: 14px; color: #6B7280;">{{ __('email.mentor_session_reminder.founder') }}:</td>
                                <td valign="top" style="padding: 6px 0; font-size: 14px; color: #111827; font-weight: 600;">
                                    @if(!empty($founderProfileUrl))
                                        <a href="{{ $founderProfileUrl }}" target="_blank" style="color: #4F46E5; text-decoration: underline;">{{ $founderName ?: '-' }}</a>
                                    @else
                                        {{ $founderName ?: '-' }}
                                    @endif
                                </td>
                            </tr>
                            @if(!empty($startupName))
                                <tr>
                                    <td width="140" valign="top" style="padding: 6px 0; font-size: 14px; color: #6B7280;">{{ __('email.mentor_session_reminder.startup') }}:</td>
                                    <td valign="top" style="padding: 6px 0; font-size: 14px; color: #111827; font-weight: 600;">
                                        @if(!empty($startupProfileUrl))
                                            <a href="{{ $startupProfileUrl }}" target="_blank" style="color: #4F46E5; text-decoration: underline;">{{ $startupName }}</a>
                                        @else
                                            {{ $startupName }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td width="140" valign="top" style="padding: 6px 0; font-size: 14px; color: #6B7280;">{{ __('email.mentor_session_reminder.date') }}:</td>
                                <td valign="top" style="padding: 6px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ $date ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td width="140" valign="top" style="padding: 6px 0; font-size: 14px; color: #6B7280;">{{ __('email.mentor_session_reminder.time') }}:</td>
                                <td valign="top" style="padding: 6px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ $startTime ?: '-' }} – {{ $endTime ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td width="140" valign="top" style="padding: 6px 0; font-size: 14px; color: #6B7280;">{{ __('email.mentor_session_reminder.duration') }}:</td>
                                <td valign="top" style="padding: 6px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ $duration ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td width="140" valign="top" style="padding: 6px 0; font-size: 14px; color: #6B7280;">{{ __('email.mentor_session_reminder.session_type') }}:</td>
                                <td valign="top" style="padding: 6px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ $isOnline ? __('email.mentor_session_reminder.online') : __('email.mentor_session_reminder.in_person') }}</td>
                            </tr>
                        </table>

                        @if($isOnline)
                            {{-- Online session --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px; background-color: #EEF2FF; border-radius: 6px;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <p style="margin: 0 0 10px 0; font-size: 15px; color: #111827; font-weight: bold;">{{ __('email.mentor_session_reminder.online_section') }}</p>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="124" valign="top" style="padding: 4px 0; font-size: 14px; color: #6B7280;">{{ __('email.mentor_session_reminder.meeting_link') }}:</td>
                                                <td valign="top" style="padding: 4px 0; font-size: 14px; color: #111827; font-weight: 600; word-break: break-all;">
                                                    @if(!empty($meetingLink) && $meetingLink !== '-')
                                                        <a href="{{ $meetingLink }}" target="_blank" style="color: #4F46E5; text-decoration: underline;">{{ $meetingLink }}</a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                            @if(!empty($passcode))
                                                <tr>
                                                    <td width="124" valign="top" style="padding: 4px 0; font-size: 14px; color: #6B7280;">{{ __('email.mentor_session_reminder.passcode') }}:</td>
                                                    <td valign="top" style="padding: 4px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ $passcode }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        @else
                            {{-- In-person session --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px; background-color: #EEF2FF; border-radius: 6px;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <p style="margin: 0 0 10px 0; font-size: 15px; color: #111827; font-weight: bold;">{{ __('email.mentor_session_reminder.in_person_section') }}</p>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="124" valign="top" style="padding: 4px 0; font-size: 14px; color: #6B7280;">{{ __('email.mentor_session_reminder.location') }}:</td>
                                                <td valign="top" style="padding: 4px 0; font-size: 14px; color: #111827; font-weight: 600;">
                                                    @if(!empty($locationLink))
                                                        <a href="{{ $locationLink }}" target="_blank" style="color: #4F46E5; text-decoration: underline;">{{ $locationName ?: $locationLink }}</a>
                                                    @else
                                                        {{ $locationName ?: '-' }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="124" valign="top" style="padding: 4px 0; font-size: 14px; color: #6B7280;">{{ __('email.mentor_session_reminder.address') }}:</td>
                                                <td valign="top" style="padding: 4px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ $address ?: '-' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        @if(!empty($notes))
                            <p style="margin: 0 0 8px 0; font-size: 15px; color: #111827; font-weight: bold;">{{ __('email.mentor_session_reminder.notes') }}:</p>
                            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 150%; color: #374151; background-color: #F9FAFB; padding: 12px; border-radius: 6px; border: 1px solid #E5E7EB; white-space: pre-line;">{{ $notes }}</p>
                        @endif

                        @if(!empty($attachments))
                            <p style="margin: 0 0 8px 0; font-size: 15px; color: #111827; font-weight: bold;">{{ __('email.mentor_session_reminder.attachments') }}:</p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
                                @foreach($attachments as $attachment)
                                    <tr>
                                        <td style="padding: 4px 0; font-size: 14px;">
                                            <a href="{{ $attachment['url'] }}" target="_blank" style="color: #4F46E5; text-decoration: underline;">📎 {{ $attachment['name'] }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif

                        <p style="margin: 24px 0 12px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.mentor_session_reminder.prepare') }}</p>
                        <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.mentor_session_reminder.contact') }}</p>

                        <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                            {{ __('email.mentor_session_reminder.regards') }}<br>
                            <strong style="color: #111827;">{{ __('email.mentor_session_reminder.team', ['org' => $org_name]) }}</strong>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
