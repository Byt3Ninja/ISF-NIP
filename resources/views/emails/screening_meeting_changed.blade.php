@extends('emails.layout.master')

@section('content')
{{--
    Screening / final screening meeting scheduled, rescheduled or cancelled,
    for judges and founders alike (GV-3295). The notification class supplies
    every string already translated, plus the accent colour and direction;
    this view only lays them out.
--}}
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F3F4F6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 40px 0;">
    <tr>
        <td align="center" style="padding: 0 16px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                <tr>
                    <td height="6" style="background-color: {{ $mainColor }};"></td>
                </tr>
                <tr>
                    <td style="padding: 40px 40px 20px 40px; text-align: {{ $alignStart }}; direction: {{ $dirAttr }};">
    <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ __('email.screening_meeting_email.greeting', ['name' => $recipientName]) }}</h1>
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $intro }}</p>
    @if (!empty($details))
        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
            <tr>
                <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                    <p style="margin: 0 0 15px 0; font-size: 14px; color: #111827; font-weight: bold;">{{ $detailsTitle }}</p>
                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        @foreach ($details as $label => $value)
                            <tr>
                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px; vertical-align: top;">{{ $label }}:</td>
                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; word-break: break-word;">
                                    @if (filter_var($value, FILTER_VALIDATE_URL))
                                        <a href="{{ $value }}" target="_blank" style="color: #4F46E5; text-decoration: underline;">{{ $value }}</a>
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    @endif
    @foreach ($outro as $line)
        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $line }}</p>
    @endforeach
    @if (!empty($cta))
        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
            <tr>
                <td align="center">
                    <a href="{{ $cta['url'] }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: {{ $mainColor }}; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">{{ $cta['label'] }}</a>
                </td>
            </tr>
        </table>
    @endif
    @foreach ($afterCta ?? [] as $line)
        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $line }}</p>
    @endforeach
    <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
        {{ $signoff ?? __('email.screening_meeting_email.thanks') }}<br>
        <strong>{{ $signoffTeam ?? $org_name }}</strong>
    </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
