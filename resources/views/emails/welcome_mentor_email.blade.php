@extends('emails.layout.master')
@section('content')
@php
    $isRtl = app()->getLocale() === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
@endphp

    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dirAttr }}" style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ $alignStart }};mso-line-height-alt:22.5px;">
                    <p style="margin: 0;">{{ trim( strtolower( $user->gender ) ) == 'male' ? __('email.mentor_welcome_email.dear_male') : __('email.mentor_welcome_email.dear_female') }} {{ $user->name }}{{ $isRtl ? '،' : ',' }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.mentor_welcome_email.message1', ['platform_name' => $mentorWelcomePlatformName]) }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.mentor_welcome_email.message2') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.mentor_welcome_email.access_intro') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.mentor_welcome_email.support') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;"> {{ __('email.mentor_welcome_email.regards') }}</p>
                    <p style="margin: 0;"> {{ __('email.mentor_welcome_email.team', ['platform_name' => $mentorWelcomePlatformName]) }}</p>
                </div>
            </td>
        </tr>
    </table>

    @isset($magicLoginUrl)
    <table border="0" cellpadding="0" cellspacing="0" class="button_block block-5" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;text-align:center;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
                    <tr>
                        <td align="center">
                            <!--[if mso]>
                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $magicLoginUrl }}" style="height:46px;v-text-anchor:middle;width:240px;" arcsize="11%" stroke="f" fillcolor="#1aa19c">
                                <w:anchorlock/>
                                <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">{{ __('email.mentor_welcome_email.login_button') }}</center>
                            </v:roundrect>
                            <![endif]-->
                            <!--[if !mso]><!-- -->
                            <a href="{{ $magicLoginUrl }}"
                               target="_blank"
                               style="background-color:#1aa19c;border-radius:5px;color:#ffffff;display:inline-block;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;font-weight:bold;line-height:46px;text-align:center;text-decoration:none;width:240px;-webkit-text-size-adjust:none;mso-hide:all;">
                                {{ __('email.mentor_welcome_email.login_button') }}
                            </a>
                            <!--<![endif]-->
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-6" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:0px;">
                <div style="color:#888888;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:13px;line-height:150%;text-align:center;font-style:italic;">
                    {{ __('email.welcome_email.login_link_note') }}
                </div>
            </td>
        </tr>
    </table>
    @endisset

@endsection