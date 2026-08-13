@extends('emails.layout.master')
@section('content')

@php
    $locale = ($locale ?? 'en') === 'ar' ? 'ar' : 'en';
    $isRtl = $locale === 'ar';
    $dir = $isRtl ? 'rtl' : 'ltr';
    $align = $isRtl ? 'right' : 'left';

    // Resolve against the recipient's locale rather than the app locale: this
    // view is rendered from a queued notification, where the worker's locale
    // has nothing to do with the judge's language preference.
    $t = function ($key, $replace = []) use ($locale) {
        return \Illuminate\Support\Facades\Lang::get('email.judger_welcome_email.' . $key, $replace, $locale);
    };

    $gender = trim(strtolower((string) ($judgerGender ?? '')));
    if ($gender === 'male') {
        $salutation = $t('dear_male');
    } elseif ($gender === 'female') {
        $salutation = $t('dear_female');
    } else {
        $salutation = $t('dear_neutral');
    }
@endphp

    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dir }}" style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ $align }};mso-line-height-alt:22.5px;">

                    <p style="margin: 0;">{{ $salutation }}{{ $judgerName }},</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('intro', ['platform_name' => $platformName]) }}</p>
                    <p style="margin: 15px 0px;"></p>

                    <p style="margin: 0;"><strong>{{ $t('account_details') }}</strong></p>
                    <p style="margin: 5px 0 0 0;">{{ $t('role') }}{{ $t('role_value') }}</p>
                    <p style="margin: 5px 0 0 0;">{{ $t('email') }}<strong>{{ $judgerEmail }}</strong></p>
                    @if(!empty($assignedBy))
                        <p style="margin: 5px 0 0 0;">{{ $t('assigned_by') }}{{ $assignedBy }}</p>
                    @endif
                    <p style="margin: 15px 0px;"></p>

                    <p style="margin: 0;">{{ $t('capabilities_intro') }}</p>
                    <ul style="margin: 5px 0 0 0; padding-{{ $isRtl ? 'right' : 'left' }}: 20px;">
                        <li style="margin: 0 0 4px 0;">{{ $t('capability_1') }}</li>
                        <li style="margin: 0 0 4px 0;">{{ $t('capability_2') }}</li>
                        <li style="margin: 0 0 4px 0;">{{ $t('capability_3') }}</li>
                        <li style="margin: 0 0 4px 0;">{{ $t('capability_4') }}</li>
                    </ul>
                    <p style="margin: 15px 0px;"></p>

                    <p style="margin: 0;">{{ $t('cta_intro') }}</p>
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
                                <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">{{ $t('login_button') }}</center>
                            </v:roundrect>
                            <![endif]-->
                            <!--[if !mso]><!-- -->
                            <a href="{{ $magicLoginUrl }}"
                               target="_blank"
                               style="background-color:#1aa19c;border-radius:5px;color:#ffffff;display:inline-block;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;font-weight:bold;line-height:46px;text-align:center;text-decoration:none;width:240px;-webkit-text-size-adjust:none;mso-hide:all;">
                                {{ $t('login_button') }}
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
                <div dir="{{ $dir }}" style="color:#888888;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:13px;line-height:150%;text-align:center;font-style:italic;">
                    {{ $t('login_link_note') }}
                </div>
            </td>
        </tr>
    </table>
    @endisset

    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-7" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:20px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dir }}" style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ $align }};mso-line-height-alt:22.5px;">
                    <p style="margin: 0;">{{ $t('unexpected') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('regards') }}</p>
                    <p style="margin: 0;">{{ $t('team', ['platform_name' => $platformName]) }}</p>
                </div>
            </td>
        </tr>
    </table>

@endsection
