@extends('emails.layout.master')

@section('content')
@php
    $isRtl = ($data['locale'] ?? 'en') === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $alignEnd = $isRtl ? 'left' : 'right';
    $t = fn ($key, $replace = []) => __('email.importer_founder_added_email.' . $key, $replace, $isRtl ? 'ar' : 'en');
    $salutation = $isRtl
        ? (($data['founder_gender'] ?? '') === 'female' ? $t('dear_female') : $t('dear_male'))
        : $t('dear');
    $labelStyle = 'padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px; vertical-align: top;';
    $valueStyle = 'padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: ' . $alignEnd . '; word-break: break-word;';
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
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ $salutation }} {{ $data['founder_name'] }}{{ $isRtl ? '،' : ',' }}</h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('added', ['program_name' => $data['program_name']]) }}</p>

                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ $t('details_title') }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="{{ $labelStyle }}">{{ $t('program_type') }}:</td>
                                            <td style="{{ $valueStyle }}">{{ $data['program_type'] ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="{{ $labelStyle }}">{{ $t('start_date') }}:</td>
                                            <td style="{{ $valueStyle }}">{{ $data['start_date'] ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="{{ $labelStyle }}">{{ $t('end_date') }}:</td>
                                            <td style="{{ $valueStyle }}">{{ $data['end_date'] ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="{{ $labelStyle }}">{{ $t('organization') }}:</td>
                                            <td style="{{ $valueStyle }}">{{ $data['organization'] ?: '-' }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('excited') }}</p>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('login_before') }} <a href="{{ $data['login_url'] }}" target="_blank" style="color: #4F46E5; font-weight: bold;">{{ $data['organization'] }}</a> {{ $t('login_after') }}</p>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('help') }}</p>

                        <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                            {{ $t('regards') }}<br>
                            <strong>{{ $t('team', ['organization_name' => $data['organization']]) }}</strong>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
