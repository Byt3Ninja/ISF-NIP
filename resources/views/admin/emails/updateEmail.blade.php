@extends('emails.layout.master')

@section('content')
@php
    $isRtl = ($locale ?? 'en') === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $t = fn ($key, $replace = []) => __('email.email_change_confirm_email.' . $key, $replace, $isRtl ? 'ar' : 'en');
    $salutation = $isRtl
        ? ($emailChangeGender === 'female' ? $t('dear_female') : $t('dear_male'))
        : $t('dear');
@endphp
    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dirAttr }}" style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ $alignStart }};mso-line-height-alt:22.5px;">
                    <p style="margin: 0;">{{ $salutation }} {{ $emailChangeFirstName }}{{ $isRtl ? '،' : ',' }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('intro', ['platform_name' => $emailChangePlatformName]) }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('cta_intro') }}</p>
                </div>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" class="button_block block-5" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;text-align:center;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
                    <tr>
                        <td align="center">
                            <!--[if mso]>
                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}" style="height:46px;v-text-anchor:middle;width:240px;" arcsize="11%" stroke="f" fillcolor="#1aa19c">
                                <w:anchorlock/>
                                <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">{{ $t('button') }}</center>
                            </v:roundrect>
                            <![endif]-->
                            <!--[if !mso]><!-- -->
                            <a href="{{ $url }}"
                               target="_blank"
                               style="background-color:#1aa19c;border-radius:5px;color:#ffffff;display:inline-block;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;font-weight:bold;line-height:46px;text-align:center;text-decoration:none;width:240px;-webkit-text-size-adjust:none;mso-hide:all;">
                                {{ $t('button') }}
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
            <td class="pad" style="padding-bottom:20px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dirAttr }}" style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ $alignStart }};mso-line-height-alt:22.5px;">
                    <p style="margin: 0 0 16px;">{{ $t('ignore') }}</p>
                    <p style="margin: 0;">{{ $t('regards') }}</p>
                    <p style="margin: 0; font-weight: 600;">{{ $t('team', ['platform_name' => $emailChangePlatformName]) }}</p>
                </div>
            </td>
        </tr>
    </table>
@endsection
