@extends('emails.layout.master')

@section('content')
@php
    $mainColor = '#4F46E5';
    $bgColor = '#F3F4F6';
@endphp

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: {{ $bgColor }}; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 40px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                <tr>
                    <td height="6" style="background-color: {{ $mainColor }};"></td>
                </tr>
                <tr>
                    <td style="padding: 40px 40px 20px 40px; {{ $locale == 'ar' ? 'text-align: right; direction: rtl;' : 'text-align: left; direction: ltr;' }}">
                        
                        @if($locale == 'ar')
                            {{-- ARABIC SECTION --}}
                            <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">تأكيد جلسة إرشاد</p>
                            <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">لديك جلسة إرشاد جديدة مع {{ $founderName }}.</h1>
                            
                            @foreach($sessions as $session)
                                <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                                    <tr>
                                        <td style="padding: 20px; direction: rtl; text-align: right;">
                                            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                                <tr>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">اسم المؤسس:</td>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $session['founder_name'] }}</td>
                                                </tr>
                                                @if(!empty($session['startup_name']) && !$session['is_hackathon'])
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">اسم الشركة الناشئة:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $session['startup_name'] }}</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">التاريخ:</td>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $session['date'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">الوقت:</td>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $session['start_time'] }} – {{ $session['end_time'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">النوع:</td>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $session['type'] == 'Online' ? 'أونلاين' : 'أوفلاين' }}</td>
                                                </tr>
                                                @if($session['type'] == 'Offline' && !empty($session['location']))
                                                    <tr>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280;">الموقع:</td>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $session['location'] }}</td>
                                                    </tr>
                                                @elseif($session['type'] == 'Online' && !empty($session['link']))
                                                    <tr>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280;">رابط الاجتماع:</td>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #4F46E5; font-weight: 600; text-align: left;"><a href="{{ $session['link'] }}" style="color: #4F46E5;">اضغط هنا للانضمام</a></td>
                                                    </tr>
                                                    @if(!empty($session['passcode']))
                                                        <tr>
                                                            <td style="padding-bottom: 0px; padding-top: 10px; font-size: 14px; color: #6B7280;">{{ __('email.Passcode', [], 'ar') }}:</td>
                                                            <td style="padding-bottom: 0px; padding-top: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $session['passcode'] }}</td>
                                                        </tr>
                                                    @endif
                                                @endif
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @endforeach
                        @else
                            {{-- ENGLISH SECTION --}}
                            <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Mentorship Session Confirmation</p>
                            <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">You have a new session(s) with {{ $founderName }}.</h1>
                            
                            @foreach($sessions as $session)
                                <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                                    <tr>
                                        <td style="padding: 20px; direction: ltr; text-align: left;">
                                            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                                <tr>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">Founder name:</td>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $session['founder_name'] }}</td>
                                                </tr>
                                                @if(!empty($session['startup_name']) && !$session['is_hackathon'])
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">Startup name:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $session['startup_name'] }}</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">Date (S):</td>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $session['date'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">Time (s):</td>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $session['start_time'] }} – {{ $session['end_time'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">Type:</td>
                                                    <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $session['type'] }}</td>
                                                </tr>
                                                @if($session['type'] == 'Offline' && !empty($session['location']))
                                                    <tr>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280;">Location:</td>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $session['location'] }}</td>
                                                    </tr>
                                                @elseif($session['type'] == 'Online' && !empty($session['link']))
                                                    <tr>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280;">Meeting Link:</td>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #4F46E5; font-weight: 600; text-align: right;"><a href="{{ $session['link'] }}" style="color: #4F46E5;">Join Meeting</a></td>
                                                    </tr>
                                                    @if(!empty($session['passcode']))
                                                        <tr>
                                                            <td style="padding-bottom: 0px; padding-top: 10px; font-size: 14px; color: #6B7280;">{{ __('email.Passcode', [], 'en') }}:</td>
                                                            <td style="padding-bottom: 0px; padding-top: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $session['passcode'] }}</td>
                                                        </tr>
                                                    @endif
                                                @endif
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @endforeach
                        @endif

                        <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                            {{ $locale == 'ar' ? 'تحياتنا،' : 'Best regards,' }}<br>
                            <strong>{{ $org_name ?? config('app.name') }}</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="background-color: #F9FAFB; padding: 20px; border-top: 1px solid #E5E7EB;">
                        <p style="font-size: 12px; color: #9CA3AF; margin: 0;">© {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
