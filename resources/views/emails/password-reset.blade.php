@extends('emails.layout.master')

@section('content')
@php
    $isRtl = ($resetLocale ?? 'en') === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $t = fn ($key, $replace = []) => __('email.password_reset_email.' . $key, $replace, $isRtl ? 'ar' : 'en');
    $font = 'Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif';
@endphp
    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dirAttr }}" style="color:#555555;font-family:{{ $font }};font-size:15px;line-height:150%;text-align:{{ $alignStart }};mso-line-height-alt:22.5px;">
                    <p style="margin: 0;">{{ $t('hello', ['name' => $resetUserName]) }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('intro') }}</p>
                </div>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;text-align:center;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
                    <tr>
                        <td align="center">
                            <!--[if mso]>
                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $resetUrl }}" style="height:46px;v-text-anchor:middle;width:240px;" arcsize="11%" stroke="f" fillcolor="#1aa19c">
                                <w:anchorlock/>
                                <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">{{ $t('button') }}</center>
                            </v:roundrect>
                            <![endif]-->
                            <!--[if !mso]><!-- -->
                            <a href="{{ $resetUrl }}"
                               target="_blank"
                               style="background-color:#1aa19c;border-radius:5px;color:#ffffff;display:inline-block;font-family:{{ $font }};font-size:15px;font-weight:bold;line-height:46px;text-align:center;text-decoration:none;width:240px;-webkit-text-size-adjust:none;mso-hide:all;">
                                {{ $t('button') }}
                            </a>
                            <!--<![endif]-->
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:20px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dirAttr }}" style="color:#555555;font-family:{{ $font }};font-size:15px;line-height:150%;text-align:{{ $alignStart }};mso-line-height-alt:22.5px;">
                    <p style="margin: 0 0 16px;">{{ $t('expire', ['count' => $resetExpireMinutes]) }}</p>
                    <p style="margin: 0 0 16px;">{{ $t('ignore') }}</p>
                    <p style="margin: 0 0 8px;">{{ $t('fallback') }}</p>
                    <p dir="ltr" style="margin: 0 0 16px; font-size:13px; word-break: break-all; text-align:{{ $alignStart }};"><a href="{{ $resetUrl }}" target="_blank" style="color:#1aa19c;">{{ $resetUrl }}</a></p>
                    <p style="margin: 0;">{{ $t('regards') }}</p>
                    <p style="margin: 0; font-weight: 600;">{{ $t('team', ['platform_name' => $resetPlatformName]) }}</p>
                </div>
            </td>
        </tr>
    </table>
@endsection
