@extends('emails.layout.master')
@section('content')

    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ app()->getLocale() === 'ar' ? 'right' : 'left' }};mso-line-height-alt:22.5px;">
                    <p style="margin: 0;">{{ __('email.hello') . ' ' . trim($party->name) }},</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.startup_mentorship_session_notify_email.approval.informing') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.startup_mentorship_session_notify_email.approval.session_details') }} : </p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.startup_mentorship_session_notify_email.approval.mentor') }} : {{ $mentorName }}</p>
                    <p style="margin: 0;"> {{ __('email.startup_mentorship_session_notify_email.approval.date') }} : {{ $sessionDate }} </p>
                    <p style="margin: 0;"> {{ __('email.startup_mentorship_session_notify_email.approval.time') }} : {{ $sessionTime }} </p>
                    <p style="margin: 0;"> {{ __('email.startup_mentorship_session_notify_email.approval.session_type') }} : {{ $sessionType == 'online' ? __('email.online') : __('email.offline') }} </p>
                    @if( $sessionType == 'online' || $sessionType == 'both' )
                        <p style="margin: 0;"> {{ __('email.startup_mentorship_session_notify_email.approval.session_link') }} : {{ $sessionLink }} </p>
                    @endif
                    @if( $sessionType == 'offline' || $sessionType == 'both' )
                        <p style="margin: 0;"> {{ __('email.startup_mentorship_session_notify_email.approval.session_address') }} : {{ $location }} </p>
                        <p style="margin: 0;"> {{ __('email.startup_mentorship_session_notify_email.approval.map_location_link') }} : {{ $mapLink }} </p>
                    @endif
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.startup_mentorship_session_notify_email.approval.ensure_on_time') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.startup_mentorship_session_notify_email.approval.wishes') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.regards') }}</p>
                    <p style="margin: 0;"> {{ __('email.team', ['platform_name' => $platformName]) }}</p>
                </div>
            </td>
        </tr>
    </table>

@endsection