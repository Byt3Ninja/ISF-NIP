@extends('emails.layout.master')

@section('content')
@php
    $isRtl = ($joinLocale ?? 'en') === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $t = fn ($key, $replace = []) => __('email.startup_join_program_email.' . $key, $replace, $isRtl ? 'ar' : 'en');
    $salutation = $isRtl
        ? ($joinGender === 'female' ? $t('dear_female') : $t('dear_male'))
        : $t('dear');
    $font = 'Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif';
@endphp
    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:20px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dirAttr }}" style="color:#555555;font-family:{{ $font }};font-size:15px;line-height:150%;text-align:{{ $alignStart }};mso-line-height-alt:22.5px;">
                    <p style="margin: 0;">{{ $salutation }} {{ $joinFounderName }}{{ $isRtl ? '،' : ',' }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('congrats', ['program_name' => $joinProgramName]) }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('excited') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('login_before') }} <a href="{{ $joinDashboardUrl }}" target="_blank" style="color:#1aa19c;font-weight:bold;">{{ $joinPlatformName }}</a> {{ $t('login_after') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('help') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('closing') }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('regards') }}</p>
                    <p style="margin: 0; font-weight: 600;">{{ $t('team', ['organization_name' => $joinOrgName]) }}</p>
                </div>
            </td>
        </tr>
    </table>
@endsection
