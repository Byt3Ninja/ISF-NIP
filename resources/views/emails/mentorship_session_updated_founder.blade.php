@extends('emails.layout.master')

@section('content')
@php
    $isRtl = ($updLocale ?? 'en') === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $t = fn ($key, $replace = []) => __('email.founder_session_updated_email.' . $key, $replace, $isRtl ? 'ar' : 'en');
    $salutation = $isRtl
        ? ($updGender === 'female' ? $t('dear_female') : $t('dear_male'))
        : $t('dear');
    $font = 'Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif';
    $labelStyle = 'padding:6px 0;font-size:14px;color:#777777;font-weight:600;width:38%;vertical-align:top;text-align:' . $alignStart . ';';
    $valueStyle = 'padding:6px 0;font-size:14px;color:#333333;vertical-align:top;word-break:break-word;text-align:' . $alignStart . ';';
@endphp
    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dirAttr }}" style="color:#555555;font-family:{{ $font }};font-size:15px;line-height:150%;text-align:{{ $alignStart }};">
                    <p style="margin: 0;">{{ $salutation }} {{ $updFounderName }}{{ $isRtl ? '،' : ',' }}</p>
                    <p style="margin: 15px 0px;"></p>
                    <p style="margin: 0;">{{ $t('intro') }}</p>
                </div>
            </td>
        </tr>
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:5px;">
                <div dir="{{ $dirAttr }}" style="font-family:{{ $font }};text-align:{{ $alignStart }};">
                    <p style="margin: 0 0 8px; font-size:17px; font-weight:700; color:#2b303a;">{{ $t('details_title') }}</p>
                    <table dir="{{ $dirAttr }}" border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%" style="border-top:1px solid #e5e5e5;border-bottom:1px solid #e5e5e5;font-family:{{ $font }};">
                        <tr><td style="{{ $labelStyle }}">{{ $t('mentor') }}</td><td style="{{ $valueStyle }}">{{ $updMentorName }}</td></tr>
                        <tr><td style="{{ $labelStyle }}">{{ $t('date') }}</td><td style="{{ $valueStyle }}">{{ $updDate }}</td></tr>
                        <tr><td style="{{ $labelStyle }}">{{ $t('time') }}</td><td style="{{ $valueStyle }}">{{ $updTime }}</td></tr>
                        <tr><td style="{{ $labelStyle }}">{{ $t('duration') }}</td><td style="{{ $valueStyle }}">{{ $updDuration }}</td></tr>
                        <tr><td style="{{ $labelStyle }}">{{ $t('session_type') }}</td><td style="{{ $valueStyle }}">{{ $updIsOnline ? $t('type_online') : $t('type_in_person') }}</td></tr>
                        @if($updIsOnline)
                            <tr>
                                <td style="{{ $labelStyle }}">{{ $t('meeting_link') }}</td>
                                <td style="{{ $valueStyle }}">@if($updHasMeetingLink)<a href="{{ $updMeetingLink }}" target="_blank" style="color:#1aa19c;">{{ $updMeetingLink }}</a>@else - @endif</td>
                            </tr>
                            @if($updPasscode !== '')
                                <tr><td style="{{ $labelStyle }}">{{ $t('passcode') }}</td><td style="{{ $valueStyle }}">{{ $updPasscode }}</td></tr>
                            @endif
                        @else
                            <tr>
                                <td style="{{ $labelStyle }}">{{ $t('location') }}</td>
                                <td style="{{ $valueStyle }}">@if($updLocation !== '-' && $updMapLink !== '')<a href="{{ $updMapLink }}" target="_blank" style="color:#1aa19c;">{{ $updLocation }}</a>@else{{ $updLocation }}@endif</td>
                            </tr>
                            <tr><td style="{{ $labelStyle }}">{{ $t('address') }}</td><td style="{{ $valueStyle }}">{{ $updAddress }}</td></tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
        @if($updNotes !== '')
            <tr>
                <td class="pad" style="padding-bottom:5px;padding-left:40px;padding-right:40px;padding-top:10px;">
                    <div dir="{{ $dirAttr }}" style="color:#555555;font-family:{{ $font }};font-size:14px;line-height:150%;text-align:{{ $alignStart }};">
                        <p style="margin: 0 0 4px; font-weight:700; color:#2b303a;">{{ $t('notes') }}</p>
                        <p style="margin: 0; white-space:pre-line;">{{ $updNotes }}</p>
                    </div>
                </td>
            </tr>
        @endif
        @if(!empty($updAttachments))
            <tr>
                <td class="pad" style="padding-bottom:5px;padding-left:40px;padding-right:40px;padding-top:10px;">
                    <div dir="{{ $dirAttr }}" style="color:#555555;font-family:{{ $font }};font-size:14px;line-height:150%;text-align:{{ $alignStart }};">
                        <p style="margin: 0 0 4px; font-weight:700; color:#2b303a;">{{ $t('attachments') }}</p>
                        @foreach($updAttachments as $attachment)
                            <p style="margin: 0 0 4px;"><a href="{{ $attachment['url'] }}" target="_blank" style="color:#1aa19c;">{{ $attachment['name'] }}</a></p>
                        @endforeach
                    </div>
                </td>
            </tr>
        @endif
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:15px;">
                <div dir="{{ $dirAttr }}" style="color:#555555;font-family:{{ $font }};font-size:15px;line-height:150%;text-align:{{ $alignStart }};">
                    <p style="margin: 0;">{{ $t('review') }}</p>
                </div>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;text-align:center;">
                <!--[if mso]>
                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $updViewUrl }}" style="height:46px;v-text-anchor:middle;width:240px;" arcsize="11%" stroke="f" fillcolor="#1aa19c">
                    <w:anchorlock/>
                    <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">{{ $t('button') }}</center>
                </v:roundrect>
                <![endif]-->
                <!--[if !mso]><!-- -->
                <a href="{{ $updViewUrl }}" target="_blank" style="background-color:#1aa19c;border-radius:5px;color:#ffffff;display:inline-block;font-family:{{ $font }};font-size:15px;font-weight:bold;line-height:46px;text-align:center;text-decoration:none;width:240px;-webkit-text-size-adjust:none;mso-hide:all;">{{ $t('button') }}</a>
                <!--<![endif]-->
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:20px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dirAttr }}" style="color:#555555;font-family:{{ $font }};font-size:15px;line-height:150%;text-align:{{ $alignStart }};">
                    <p style="margin: 0 0 16px;">{{ $t('help') }}</p>
                    <p style="margin: 0;">{{ $t('regards') }}</p>
                    <p style="margin: 0; font-weight: 600;">{{ $t('team', ['organization_name' => $updOrgName]) }}</p>
                </div>
            </td>
        </tr>
    </table>
@endsection
