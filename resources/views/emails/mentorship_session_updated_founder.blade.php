@extends('emails.layout.master')

@section('content')
@php
    $isRtl = ($updLocale ?? 'en') === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $alignEnd = $isRtl ? 'left' : 'right';
    $t = fn ($key, $replace = []) => __('email.founder_session_updated_email.' . $key, $replace, $isRtl ? 'ar' : 'en');
    $salutation = $isRtl
        ? ($updGender === 'female' ? $t('dear_female') : $t('dear_male'))
        : $t('dear');
    $labelStyle = 'padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px; vertical-align: top;';
    $valueStyle = 'padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: ' . $alignEnd . '; word-break: break-word;';
    $linkStyle = 'color: #4F46E5;';
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
    <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ $salutation }} {{ $updFounderName }}{{ $isRtl ? '،' : ',' }}</h1>

    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('intro') }}</p>

    <p style="margin: 0 0 10px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ $t('details_title') }}</p>

    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
        <tr>
            <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                <table width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr><td style="{{ $labelStyle }}">{{ $t('mentor') }}:</td><td style="{{ $valueStyle }}">{{ $updMentorName }}</td></tr>
                    <tr><td style="{{ $labelStyle }}">{{ $t('date') }}:</td><td style="{{ $valueStyle }}">{{ $updDate }}</td></tr>
                    <tr><td style="{{ $labelStyle }}">{{ $t('time') }}:</td><td style="{{ $valueStyle }}">{{ $updTime }}</td></tr>
                    <tr><td style="{{ $labelStyle }}">{{ $t('duration') }}:</td><td style="{{ $valueStyle }}">{{ $updDuration }}</td></tr>
                    <tr><td style="{{ $labelStyle }}">{{ $t('session_type') }}:</td><td style="{{ $valueStyle }}">{{ $updIsOnline ? $t('type_online') : $t('type_in_person') }}</td></tr>
                    @if($updIsOnline)
                        <tr>
                            <td style="{{ $labelStyle }}">{{ $t('meeting_link') }}:</td>
                            <td style="{{ $valueStyle }}">@if($updHasMeetingLink)<a href="{{ $updMeetingLink }}" target="_blank" style="{{ $linkStyle }}">{{ $updMeetingLink }}</a>@else - @endif</td>
                        </tr>
                        @if($updPasscode !== '')
                            <tr><td style="{{ $labelStyle }}">{{ $t('passcode') }}:</td><td style="{{ $valueStyle }}">{{ $updPasscode }}</td></tr>
                        @endif
                    @else
                        <tr>
                            <td style="{{ $labelStyle }}">{{ $t('location') }}:</td>
                            <td style="{{ $valueStyle }}">@if($updLocation !== '-' && $updMapLink !== '')<a href="{{ $updMapLink }}" target="_blank" style="{{ $linkStyle }}">{{ $updLocation }}</a>@else{{ $updLocation }}@endif</td>
                        </tr>
                        <tr><td style="{{ $labelStyle }}">{{ $t('address') }}:</td><td style="{{ $valueStyle }}">{{ $updAddress }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @if($updNotes !== '')
        <p style="margin: 0 0 6px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ $t('notes') }}</p>
        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151; white-space: pre-line;">{{ $updNotes }}</p>
    @endif

    @if(!empty($updAttachments))
        <p style="margin: 0 0 6px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ $t('attachments') }}</p>
        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%;">
            @foreach($updAttachments as $attachment)
                <a href="{{ $attachment['url'] }}" target="_blank" style="{{ $linkStyle }}">{{ $attachment['name'] }}</a><br>
            @endforeach
        </p>
    @endif

    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('review') }}</p>

    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
        <tr>
            <td align="center">
                <a href="{{ $updViewUrl }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: #4F46E5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">{{ $t('button') }}</a>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('help') }}</p>

    <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
        {{ $t('regards') }}<br>
        <strong>{{ $t('team', ['organization_name' => $updOrgName]) }}</strong>
    </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
