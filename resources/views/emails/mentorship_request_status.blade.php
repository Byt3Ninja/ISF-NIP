@extends('emails.layout.master')

@section('content')
    @php
        // إعدادات الألوان والنصوص بناءً على الحالة
        $isApproved = ($data['status'] ?? '') == 'Yes';
        $statusColor = $isApproved ? '#10B981' : '#EF4444'; // أخضر للموافقة، أحمر للرفض
        $mainColor = '#4F46E5'; // اللون الرئيسي للموقع (يمكنك تغييره هنا)
        $bgColor = '#F3F4F6';
        
        // تجهيز النصوص الإنجليزية
        $text_en = 'Declined';
        $title_en = 'Session Declined';
        if ($isApproved) {
            $text_en = 'Approved';
            $title_en = 'Session Approved';
            if (($data['action'] ?? '') == 'modifySession') {
                $text_en = ' Rescheduled';
                $title_en = 'Session Rescheduled';
            }
        }

        // تجهيز النصوص العربية
        $text_ar = 'رفض';
        $title_ar = 'تم رفض الجلسة';
        if ($isApproved) {
            $text_ar = 'الموافقة';
            $title_ar = 'تمت الموافقة';
            if (($data['action'] ?? '') == 'modifySession') {
                $text_ar = ' تعديل';
                $title_ar = 'تم تعديل الموعد';
            }
        }
    @endphp


    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: {{ $bgColor }}; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                    <tr>
                        <td height="6" style="background-color: {{ $mainColor }};"></td>
                    </tr>
                    @if($data['lang'] == 'ar')
                        {{-- ================= ARABIC SECTION ================= --}}
                        <tr>
                            <td style="padding: 40px 40px 20px 40px; text-align: right; direction: rtl;">
                                {{-- Logo Placeholder (Optional) --}}
                                <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">{{ __('email.Mentorship_Update', [], 'ar') }}</p>
                                @if(($data['user'] ?? 'user') == 'mentor')
                                <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">
                                    عزيزي/عزيزتي {{ $data['mentorName'] ?? '' }}،
                                </h1>
                                @else
                                <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">
                                    {{ __('email.dear_male', [], 'ar') }} {{ $data['startupOwnerName'] ?? '' }}،
                                </h1>
                                @endif
                                
                                <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.5; color: #374151;">
                                    @if(($data['user'] ?? 'user') == 'mentor')
                                        تم تحديث تفاصيل جلسة الإرشاد القادمة مع {{ $data['startupOwnerName'] ?? '' }}.
                                    @else
                                        @if($isApproved)
                                            @if(($data['action'] ?? '') == 'modifySession')
                                                {{ __('email.mentorship_session_action_notify.user_rescheduled', ['mentor' => $data['mentorName'] ?? ''], 'ar') }}
                                            @else
                                                {{ __('email.mentorship_session_action_notify.user_approved', ['mentor' => $data['mentorName'] ?? ''], 'ar') }}
                                            @endif
                                        @else
                                            {{ __('email.mentorship_session_action_notify.user_declined', ['mentor' => $data['mentorName'] ?? ''], 'ar') }}
                                        @endif
                                    @endif
                                </p>

                                @if(($data['user'] ?? 'user') == 'mentor')
                                    <p style="margin: 0 0 10px 0; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">تفاصيل الجلسة المحدثة</p>
                                @endif
                                <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; direction: rtl;">
                                    <tr>
                                        <td style="padding: 20px;">
                                            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                                @if(($data['user'] ?? 'user') == 'mentor')
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; text-align: right;">{{ __('email.founder_name', [], 'ar') }}:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">
                                                            @if(!empty($data['startupOwnerId']))
                                                                <a href="{{ route('clients.profile_view', $data['startupOwnerId']) }}" style="color: #4F46E5;">{{ $data['startupOwnerName'] ?? '' }}</a>
                                                            @else
                                                                {{ $data['startupOwnerName'] ?? '-' }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @if(!empty($data['startupName']))
                                                        <tr>
                                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; text-align: right;">{{ __('email.startup_name', [], 'ar') }}:</td>
                                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">
                                                                @if(!empty($data['startupId']))
                                                                    <a href="{{ route('company.profile_view', $data['startupId']) }}" style="color: #4F46E5;">{{ $data['startupName'] }}</a>
                                                                @else
                                                                    {{ $data['startupName'] }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; text-align: right;">التاريخ:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $data['sessionDate'] ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; text-align: right;">الوقت:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $data['sessionTime'] ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; text-align: right;">{{ __('email.Duration', [], 'ar') }}:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $data['duration'] ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280; text-align: right;">{{ __('email.Type', [], 'ar') }}:</td>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ strtolower($data['sessionType'] ?? '') === 'online' ? 'عن بُعد' : 'حضورية' }}</td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; text-align: right;">{{ __('email.Mentor', [], 'ar') }}:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $data['mentorName'] ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; text-align: right;">{{ __('email.Date & Time', [], 'ar') }}:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $data['sessionDate'] ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; text-align: right;">{{ __('email.Duration', [], 'ar') }}:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ $data['duration'] ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280; text-align: right;">{{ __('email.Type', [], 'ar') }}:</td>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: left;">{{ ($data['sessionType'] ?? '') === 'Online' ? 'أونلاين' : 'أوفلاين' }}</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                 {{-- Action Button / Location --}}
                                <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 25px;">
                                    @if(strtolower($data['sessionType'] ?? '') == 'online')
                                        <tr>
                                            <td align="center">
                                                <a href="{{ $data['meetingLink'] ?? '' }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: {{ $mainColor }}; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">{{__('email.Join_Meeting', [], 'ar')}}</a>
                                                @if(!empty($data['meetingPass']))
                                                    <p style="margin-top: 10px; font-size: 12px; color: #6B7280; text-align: center;">{{ __('email.Passcode', [], 'ar') }}: <strong>{{ $data['meetingPass'] }}</strong></p>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td style="background-color: #eff6ff; padding: 15px; border-radius: 6px; color: #1e40af; border: 1px solid #dbeafe; text-align: right;">
                                                <strong>📍 {{ __('email.Location', [], 'ar') }}:</strong>
                                                @if(!empty($data['location']) && !empty($data['mapLink']))
                                                    <a href="{{ $data['mapLink'] }}" style="color: #1e40af;">{{ $data['location'] }}</a>
                                                @else
                                                    {{ $data['location'] ?? '-' }}
                                                @endif
                                                <br>
                                                <strong>{{ __('email.address', [], 'ar') }}:</strong> {{ $data['address'] ?? '-' }}
                                            </td>
                                        </tr>
                                    @endif
                                </table>

                                {{-- Notes & Attachments --}}
                                @if(!empty($data['notes']))
                                    <div style="margin-top: 25px; border-top: 1px solid #E5E7EB; padding-top: 20px; text-align: right;">
                                        <p style="font-size: 14px; color: #374151; margin-bottom: 10px;">
                                            <strong>{{ __('email.Notes', [], 'ar') }}:</strong><br>
                                            <span style="font-style: italic;">{{ $data['notes'] }}</span>
                                        </p>

                                        @if(!empty($data['attachments']))
                                            <p style="font-size: 14px; color: #374151; margin-bottom: 5px;"><strong>{{ __('email.Attachments', [], 'ar') }}:</strong></p>
                                            @foreach($data['attachments'] as $att)
                                                <a href="{{ $att['url'] }}" style="display: inline-block; margin-left: 10px; color: {{ $mainColor }}; text-decoration: underline; font-size: 14px;">📎 {{ $att['name'] }}</a>
                                            @endforeach
                                        @endif
                                    </div>
                                @endif

                                @if($data['user'] !== 'mentor' && $isApproved)
                                    <p style="margin: 20px 0 0 0; font-size: 15px; color: #374151;">{{ __('email.action_session_details', [], 'ar') }}</p>
                                @endif

                                @if(($data['user'] ?? 'user') == 'mentor')
                                    <p style="margin: 20px 0 0 0; font-size: 15px; color: #374151;">يرجى مراجعة تفاصيل الجلسة المحدثة والتأكد من توافرك في الموعد المحدد.</p>
                                    <p style="margin: 10px 0 0 0; font-size: 15px; color: #374151;">إذا كانت لديك أي استفسارات أو كنت بحاجة إلى مساعدة إضافية، يرجى التواصل معنا.</p>
                                @endif

                                <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                                    {{ __('email.Best_regards', [], 'ar') }}،<br>
                                    <strong>{{ $data['orgName'] ?? env('APP_NAME') }}</strong>
                                </p>
                            </td>
                        </tr>

                        {{-- Separator --}}
                        <tr>
                            <td style="padding: 0 40px;">
                                <hr style="border: none; border-top: 1px dashed #D1D5DB; margin: 0;">
                            </td>
                        </tr>
                    @elseif($data['lang'] == 'en')
                        {{-- ================= ENGLISH SECTION ================= --}}
                        <tr>
                            <td style="padding: 40px 40px 20px 40px; text-align: left;">
                                {{-- Logo Placeholder (Optional) --}}
                                <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">{{ __('email.Mentorship_Update') }}</p>
                                @if(($data['user'] ?? 'user') == 'mentor')
                                <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">
                                    Dear {{ $data['mentorName'] ?? '' }},
                                </h1>
                                @else
                                <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">
                                    {{ __('email.Dear') }} {{ $data['startupOwnerName'] ?? '' }},
                                </h1>
                                @endif

                                <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.5; color: #374151;">
                                    @if(($data['user'] ?? 'user') == 'mentor')
                                        The details of your upcoming mentorship session with {{ $data['startupOwnerName'] ?? '' }} have been updated.
                                    @else
                                        A new mentorship session has been <strong style="color: {{ $statusColor }};">{{ $text_en }}</strong>.
                                    @endif
                                </p>

                                @if(($data['user'] ?? 'user') == 'mentor')
                                    <p style="margin: 0 0 10px 0; font-size: 14px; color: #111827; font-weight: 600;">Updated Session Details</p>
                                @endif
                                {{-- Details Box --}}
                                <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB;">
                                    <tr>
                                        <td style="padding: 20px;">
                                            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                                @if(($data['user'] ?? 'user') == 'mentor')
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">{{ __('email.founder_name') }}:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">
                                                            @if(!empty($data['startupOwnerId']))
                                                                <a href="{{ route('clients.profile_view', $data['startupOwnerId']) }}" style="color: {{ $mainColor }};">{{ $data['startupOwnerName'] ?? '' }}</a>
                                                            @else
                                                                {{ $data['startupOwnerName'] ?? '-' }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @if(!empty($data['startupName']))
                                                        <tr>
                                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">{{ __('email.startup_name') }}:</td>
                                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">
                                                                @if(!empty($data['startupId']))
                                                                    <a href="{{ route('company.profile_view', $data['startupId']) }}" style="color: {{ $mainColor }};">{{ $data['startupName'] }}</a>
                                                                @else
                                                                    {{ $data['startupName'] }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">Date:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $data['sessionDate'] ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">Time:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $data['sessionTime'] ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">{{ __('email.Duration') }}:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $data['duration'] ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280;">{{ __('email.Type') }}:</td>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ strtolower($data['sessionType'] ?? '') === 'online' ? 'Online' : 'In Person' }}</td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">{{ __('email.Mentor') }}:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $data['mentorName'] ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">{{ __('email.Date & Time') }}:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $data['sessionDate'] ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280;">{{ __('email.Duration') }}:</td>
                                                        <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $data['duration'] ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280;">{{ __('email.Type') }}:</td>
                                                        <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">{{ $data['sessionType'] ?? '' }}</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                 {{-- Action Button / Location --}}
                                <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 25px;">
                                    @if(strtolower($data['sessionType'] ?? '') == 'online')
                                        <tr>
                                            <td align="center">
                                                <a href="{{ $data['meetingLink'] ?? '' }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: {{ $mainColor }}; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">{{__('email.Join_Meeting')}}</a>
                                                @if(!empty($data['meetingPass']))
                                                    <p style="margin-top: 10px; font-size: 12px; color: #6B7280;">{{ __('email.Passcode') }}: <strong>{{ $data['meetingPass'] }}</strong></p>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td style="background-color: #eff6ff; padding: 15px; border-radius: 6px; color: #1e40af; border: 1px solid #dbeafe;">
                                                <strong>📍 {{ __('email.Location') }}:</strong>
                                                @if(!empty($data['location']) && !empty($data['mapLink']))
                                                    <a href="{{ $data['mapLink'] }}" style="color: #1e40af;">{{ $data['location'] }}</a>
                                                @else
                                                    {{ $data['location'] ?? '-' }}
                                                @endif
                                                <br>
                                                <strong>{{ __('email.address') }}:</strong> {{ $data['address'] ?? '-' }}
                                            </td>
                                        </tr>
                                    @endif
                                </table>

                                {{-- Notes & Attachments --}}
                                @if(!empty($data['notes']))
                                    <div style="margin-top: 25px; border-top: 1px solid #E5E7EB; padding-top: 20px;">
                                        @if(!empty($data['notes']))
                                            <p style="font-size: 14px; color: #374151; margin-bottom: 10px;">
                                                <strong>{{ __('email.Notes') }}:</strong><br>
                                                <span style="font-style: italic;">{{ $data['notes'] }}</span>
                                            </p>
                                        @endif

                                        @if(!empty($data['attachments']))
                                            <p style="font-size: 14px; color: #374151; margin-bottom: 5px;"><strong>{{ __('email.Attachments') }}:</strong></p>
                                            @foreach($data['attachments'] as $att)
                                                <a href="{{ $att['url'] }}" style="display: inline-block; margin-right: 10px; color: {{ $mainColor }}; text-decoration: underline; font-size: 14px;">📎 {{ $att['name'] }}</a>
                                            @endforeach
                                        @endif
                                    </div>
                                @endif

                                @if(($data['user'] ?? 'user') == 'mentor')
                                    <p style="margin: 20px 0 0 0; font-size: 15px; color: #374151;">Please review the updated session details and make sure you are available at the scheduled time.</p>
                                    <p style="margin: 10px 0 0 0; font-size: 15px; color: #374151;">If you have any questions or need additional assistance, please feel free to reach out.</p>
                                @endif

                                <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                                    {{ __('email.Best_regards') }},<br>
                                    <strong>{{ $data['orgName'] ?? env('APP_NAME') }}</strong>
                                </p>
                            </td>
                        </tr>

                        {{-- Separator --}}
                        <tr>
                            <td style="padding: 0 40px;">
                                <hr style="border: none; border-top: 1px dashed #D1D5DB; margin: 0;">
                            </td>
                        </tr>
                   
                    @endif
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