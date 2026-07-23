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

                        <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">{{ __('email.mentorship_request_email.subject') }}</p>
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ trim( trim( strtolower( $party->gender ) ) == 'male' ? __('email.dear_male') : __('email.dear_female') ) . ' ' . trim($party->name) }},</h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.mentorship_request_email.informing') }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.mentorship_request_email.startup_name') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $startupName }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.mentorship_request_email.requested_mentor') }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $requestedMentor }}</td>
                                        </tr>
                                        @if( trim( $sessionNotes ) )
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.mentorship_request_email.session_notes') }}:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionNotes }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.mentorship_request_email.preferred_date_time') }}:</td>
                                            <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $sessionDateTime }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.mentorship_request_email.review_request') }}</p>
                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.mentorship_request_email.ensure_parties_notified') }}</p>
                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.mentorship_request_email.support_offer') }}</p>

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
