@extends('emails.layout.master')

@section('content')
    @php
        // إعدادات الألوان والنصوص بناءً على الحالة
        $isApproved = $data['status'] == 'Yes';
        $statusColor = $isApproved ? '#10B981' : '#EF4444'; // أخضر للموافقة، أحمر للرفض
        $mainColor = '#4F46E5'; // اللون الرئيسي للموقع (يمكنك تغييره هنا)
        $bgColor = '#F3F4F6';
        
        // تجهيز النصوص الإنجليزية
        $text_en = 'Declined';
        $title_en = 'Session Declined';
        if ($isApproved) {
            $text_en = 'Approved';
            $title_en = 'Session Approved';
            if ($data['action'] == 'modifySession') {
                $text_en .= ' and Rescheduled';
                $title_en = 'Session Rescheduled';
            }
        }

        // تجهيز النصوص العربية
        $text_ar = 'رفض';
        $title_ar = 'تم رفض الجلسة';
        if ($isApproved) {
            $text_ar = 'الموافقة';
            $title_ar = 'تمت الموافقة';
            if ($data['action'] == 'modifySession') {
                $text_ar .= ' وتعديل';
                $title_ar = 'تم تعديل الموعد';
            }
        }
    @endphp

    {{-- وعاء الإيميل الرئيسي --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: {{ $bgColor }}; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 40px 0;">
        <tr>
            <td align="center">
                {{-- الكارت الأبيض --}}
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                    
                    {{-- الشريط العلوي الملون --}}
                    <tr>
                        <td height="6" style="background-color: {{ $mainColor }};"></td>
                    </tr>

                    {{-- ================= ENGLISH SECTION ================= --}}
                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: left;">
                            {{-- Logo Placeholder (Optional) --}}
                            <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Mentorship Update</p>
                            
                            <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">
                                Dear {{ $data['mentorName'] }},
                            </h1>
                            
                            <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.5; color: #374151;">
                                A new mentorship session has been <strong style="color: {{ $statusColor }};">{{ $text_en }}</strong>.
                            </p>

                            {{-- Details Box --}}
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">Startup:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $data['startupName'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">Date & Time:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $data['sessionDate'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">Duration:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $data['duration'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280;">Type:</td>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $data['sessionType'] }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Action Button / Location --}}
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 25px;">
                                @if($data['sessionType'] == 'Online')
                                    <tr>
                                        <td align="center">
                                            <a href="{{ $data['meetingLink'] }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: {{ $mainColor }}; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">Join Meeting</a>
                                            <p style="margin-top: 10px; font-size: 12px; color: #6B7280;">Passcode: <strong>{{ $data['meetingPass'] }}</strong></p>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td style="background-color: #eff6ff; padding: 15px; border-radius: 6px; color: #1e40af; border: 1px solid #dbeafe;">
                                            <strong>📍 Location:</strong> {{ $data['location'] }}
                                        </td>
                                    </tr>
                                @endif
                            </table>

                            {{-- Notes & Attachments --}}
                            @if(!empty($data['notes']) || !empty($data['attachments']))
                                <div style="margin-top: 25px; border-top: 1px solid #E5E7EB; padding-top: 20px;">
                                    @if(!empty($data['notes']))
                                        <p style="font-size: 14px; color: #374151; margin-bottom: 10px;">
                                            <strong>Notes:</strong><br>
                                            <span style="font-style: italic;">{{ $data['notes'] }}</span>
                                        </p>
                                    @endif

                                    @if(!empty($data['attachments']))
                                        <p style="font-size: 14px; color: #374151; margin-bottom: 5px;"><strong>Attachments:</strong></p>
                                        @foreach($data['attachments'] as $att)
                                            <a href="{{ $att['url'] }}" style="display: inline-block; margin-right: 10px; color: {{ $mainColor }}; text-decoration: underline; font-size: 14px;">📎 {{ $att['name'] }}</a>
                                        @endforeach
                                    @endif
                                </div>
                            @endif
                            
                            <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                                Best regards,<br>
                                <strong>{{ env('APP_NAME') }}</strong>
                            </p>
                        </td>
                    </tr>

                    {{-- Separator --}}
                    <tr>
                        <td style="padding: 0 40px;">
                            <hr style="border: none; border-top: 1px dashed #D1D5DB; margin: 0;">
                        </td>
                    </tr>

                    {{-- ================= ARABIC SECTION ================= --}}
                    <tr>
                        <td dir="rtl" style="padding: 20px 40px 40px 40px; text-align: right; font-family: 'Tahoma', 'Segoe UI', 'Arial', sans-serif;">
                            
                            <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">تحديث الجلسة</p>

                            <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">
                                عزيزي {{ $data['mentorName'] }},
                            </h1>
                            
                            <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.5; color: #374151;">
                                تم <strong style="color: {{ $statusColor }};">{{ $text_ar }}</strong> على جلسة إرشاد جديدة.
                            </p>

                            {{-- Details Box (Arabic) --}}
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">الشركة الناشئة:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $data['startupName'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">التاريخ والوقت:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $data['sessionDate'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">المدة:</td>
                                                <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $data['duration'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280;">النوع:</td>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $data['sessionType'] }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Action Button / Location (Arabic) --}}
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 25px;">
                                @if($data['sessionType'] == 'Online')
                                    <tr>
                                        <td align="center">
                                            <a href="{{ $data['meetingLink'] }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: {{ $mainColor }}; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">انضم للاجتماع</a>
                                            <p style="margin-top: 10px; font-size: 12px; color: #6B7280;">رمز الدخول: <strong>{{ $data['meetingPass'] }}</strong></p>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td style="background-color: #eff6ff; padding: 15px; border-radius: 6px; color: #1e40af; border: 1px solid #dbeafe;">
                                            <strong>📍 الموقع:</strong> {{ $data['location'] }}
                                        </td>
                                    </tr>
                                @endif
                            </table>

                            {{-- Notes & Attachments (Arabic) --}}
                            @if(!empty($data['notes']) || !empty($data['attachments']))
                                <div style="margin-top: 25px; border-top: 1px solid #E5E7EB; padding-top: 20px;">
                                    @if(!empty($data['notes']))
                                        <p style="font-size: 14px; color: #374151; margin-bottom: 10px;">
                                            <strong>ملاحظات:</strong><br>
                                            <span style="font-style: italic;">{{ $data['notes'] }}</span>
                                        </p>
                                    @endif

                                    @if(!empty($data['attachments']))
                                        <p style="font-size: 14px; color: #374151; margin-bottom: 5px;"><strong>المرفقات:</strong></p>
                                        @foreach($data['attachments'] as $att)
                                            <a href="{{ $att['url'] }}" style="display: inline-block; margin-left: 10px; color: {{ $mainColor }}; text-decoration: underline; font-size: 14px;">📎 {{ $att['name'] }}</a>
                                        @endforeach
                                    @endif
                                </div>
                            @endif

                            <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                                أطيب التحيات،<br>
                                <strong>{{ env('APP_NAME') }}</strong>
                            </p>
                        </td>
                    </tr>
                    
                    {{-- Footer --}}
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