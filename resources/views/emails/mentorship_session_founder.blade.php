@extends('emails.layout.master')
@section('content')

    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ $locale === 'ar' ? 'right' : 'left' }};mso-line-height-alt:22.5px;">
                    @if($locale === 'ar')
                        {{-- ARABIC --}}
                        <p style="margin: 0;">لديك جلسة إرشاد جديدة مع {{ $mentorName }}.</p>
                        <p style="margin: 5px 0 0 0;">اسم المرشد: {{ $mentorName }}.</p>
                        <p style="margin: 5px 0 0 0;">التاريخ: {{ $sessionDate }}.</p>
                        <p style="margin: 5px 0 0 0;">الوقت: {{ $startTime }} – {{ $endTime }} (AM/PM).</p>
                        <p style="margin: 5px 0 0 0;">النوع: {{ $sessionType === 'Online' ? 'أونلاين' : 'أوفلاين' }}.</p>
                        @if($sessionType === 'Offline' && !empty($location))
                            <p style="margin: 5px 0 0 0;">الموقع: {{ $location }}.</p>
                        @elseif($sessionType === 'Online' && !empty($meetingLink))
                            <p style="margin: 5px 0 0 0;">رابط الاجتماع: {{ $meetingLink }}.</p>
                        @endif
                        <p style="margin: 5px 0 0 0;">الإجراء: تفاصيل الجلسة.</p>
                        <p style="margin: 15px 0px;"></p>
                        <p style="margin: 0;">
                            <a href="{{ url('/dashboard') }}" target="_blank" style="display: inline-block; padding: 10px 20px; background-color: #1aa19c; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">تفاصيل الجلسة</a>
                        </p>
                    @else
                        {{-- ENGLISH --}}
                        <p style="margin: 0;">You have a new session(s) with {{ $mentorName }}.</p>
                        <p style="margin: 5px 0 0 0;">Mentor name: {{ $mentorName }}.</p>
                        <p style="margin: 5px 0 0 0;">Date(s): {{ $sessionDate }}.</p>
                        <p style="margin: 5px 0 0 0;">Time: {{ $startTime }} – {{ $endTime }} (AM/PM).</p>
                        <p style="margin: 5px 0 0 0;">Type: {{ $sessionType }}.</p>
                        @if($sessionType === 'Offline' && !empty($location))
                            <p style="margin: 5px 0 0 0;">Location: {{ $location }}.</p>
                        @elseif($sessionType === 'Online' && !empty($meetingLink))
                            <p style="margin: 5px 0 0 0;">Meeting Link: {{ $meetingLink }}.</p>
                        @endif
                        <p style="margin: 15px 0px;"></p>
                        <p style="margin: 0;">
                            <a href="{{ url('/dashboard') }}" target="_blank" style="display: inline-block; padding: 10px 20px; background-color: #1aa19c; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">Session Details</a>
                        </p>
                    @endif
                </div>
            </td>
        </tr>
    </table>

@endsection
