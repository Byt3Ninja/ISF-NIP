@extends('emails.layout.master')
@section('content')

    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ app()->getLocale() === 'ar' ? 'right' : 'left' }};mso-line-height-alt:22.5px;">
                    <p style="margin: 0;">{{ trim( trim( strtolower( $party->gender ) ) == 'male' ? __('email.dear_male') : __('email.dear_female') ) . ' ' . trim($party->name) }},</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.mentor_notify_email.informing') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.mentor_notify_email.startup_name') }} : {{ $startupName }}</p>
                    <p style="margin: 0;"> {{ __('email.mentor_notify_email.date') }} : {{ $sessionDate }} </p>
                    <p style="margin: 0;"> {{ __('email.mentor_notify_email.time') }} : {{ $sessionTime }} </p>
                    <p style="margin: 0;"> {{ __('email.mentor_notify_email.session_link') }} : {{ $sessionLink }} </p>
                    @if( $sessionNotes != '-' )
                    <p style="margin: 0;"> {{ __('email.mentor_notify_email.session_notes') }} : {{ $sessionNotes }} </p>
                    @endif
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.mentor_notify_email.ensure_on_time') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.mentor_notify_email.inquiry_suggest') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.regards') }}</p>
                    <p style="margin: 0;"> {{ __('email.team', ['platform_name' => $platformName]) }}</p>
                </div>
            </td>
        </tr>
    </table>

@endsection