@extends('emails.layout.master')

@section('content')
@php
    $isRtl = ($trainingLocale ?? 'en') === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $alignEnd = $isRtl ? 'left' : 'right';
    $t = fn ($key, $replace = []) => __('email.new_training_email.' . $key, $replace, $isRtl ? 'ar' : 'en');
    $labelStyle = 'padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px; vertical-align: top;';
    $valueStyle = 'padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: ' . $alignEnd . '; word-break: break-word;';
    $linkStyle = 'color: #4F46E5;';
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
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ $t('hello') }} {{ $trainingRecipientName }}{{ $isRtl ? '،' : ',' }}</h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $t('intro', ['title' => $trainingTitle]) }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="{{ $labelStyle }}">{{ $t('date') }}:</td>
                                            <td style="{{ $valueStyle }}">{{ $trainingDate }}</td>
                                        </tr>
                                        @if($trainingStartTime !== '' || $trainingEndTime !== '')
                                            <tr>
                                                <td style="{{ $labelStyle }}">{{ $t('time') }}:</td>
                                                <td style="{{ $valueStyle }}">{{ implode(' - ', array_filter([$trainingStartTime, $trainingEndTime], 'strlen')) }}</td>
                                            </tr>
                                        @endif
                                        @if($trainingIsOnline)
                                            <tr>
                                                <td style="{{ $labelStyle }}">{{ $t('online_link') }}:</td>
                                                <td style="{{ $valueStyle }}">
                                                    @if($trainingLink !== '')<a href="{{ $trainingLink }}" target="_blank" style="{{ $linkStyle }}">{{ $trainingLink }}</a>@else - @endif
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td style="{{ $labelStyle }}">{{ $t('map_link') }}:</td>
                                                <td style="{{ $valueStyle }}">
                                                    @if($trainingMapLink !== '')<a href="{{ $trainingMapLink }}" target="_blank" style="{{ $linkStyle }}">{{ $trainingMapLink }}</a>@else - @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="{{ $labelStyle }}">{{ $t('address') }}:</td>
                                                <td style="{{ $valueStyle }}">{{ $trainingAddress !== '' ? $trainingAddress : '-' }}</td>
                                            </tr>
                                        @endif
                                        @if($trainingTrainerName !== '')
                                            <tr>
                                                <td style="{{ $labelStyle }}">{{ $t('trainer_name') }}:</td>
                                                <td style="{{ $valueStyle }}">{{ $trainingTrainerName }}</td>
                                            </tr>
                                        @endif
                                        @if($trainingTrainerEmail !== '')
                                            <tr>
                                                <td style="{{ $labelStyle }}">{{ $t('trainer_email') }}:</td>
                                                <td style="{{ $valueStyle }}"><a href="mailto:{{ $trainingTrainerEmail }}" style="{{ $linkStyle }}">{{ $trainingTrainerEmail }}</a></td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                            {{ $t('regards') }}<br>
                            <strong>{{ $t('team', ['organization_name' => $trainingOrgName]) }}</strong>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
