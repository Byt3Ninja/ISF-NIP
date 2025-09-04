@extends('emails.layout.master')

@section('content')
<table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3"
       role="presentation"
       style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
       width="100%">
    <tr>
        <td class="pad"
            style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
            <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:150%;text-align:center;mso-line-height-alt:45px;">
                <p style="margin: 0; word-break: break-word;"><span
                            style="word-break: break-word; color: #000000;"><strong>{{ __('email.custom_mail.welcome_title') }}</strong></span>
                </p>
            </div>
        </td>
    </tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-5"
       role="presentation"
       style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
       width="100%">

    <tr>
        <td class="pad"
            style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
            <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:right;mso-line-height-alt:22.5px;">
                <p style="margin: 0;">{{ __('email.custom_mail.dear_participant') }},</p>
                <p style="margin: 0;"> </p>
                <p style="margin: 0;">{{ __('email.custom_mail.welcome_message') }}</p>
                <p style="margin: 0;"> </p>
                
                <p style="margin: 0;">{{ __('email.custom_mail.email') }} : {{ $user->email }}</p>
                <p style="margin: 0;">
                {{ __('email.custom_mail.password_reset_message') }}
                <a href="{{ $resetUrl }}">{{ __('email.custom_mail.password_reset_link') }}</a>
                </p>
                <br/><br/>
                <a href="https://shorturl.at/9rdSu">{{ __('email.custom_mail.guide_link') }}</a>
                <br/><br/>
                <p style="margin: 0;"><img src="https://incubation.sa/biac.png" alt="BIAC Logo" width="639" height="720"></p>
                <p style="margin: 0;">{{ __('email.custom_mail.best_regards') }},</p>
            </div>
        </td>
    </tr>

</table>
@endsection