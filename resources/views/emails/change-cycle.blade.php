@php
    $setting = App\Settings::first();
    $platformName = $setting ? ( $setting->Platform_Name ?? config('app.name') ) : config('app.name');
    $lang = app()->getLocale();
    if( in_array( strtolower( $data['preferred_language'] ), ['en', 'ar'] ) )
    {
        $lang = $data['preferred_language'];
    }
@endphp

@extends('emails.layout.master')

@section('content')
@php
    $mainColor = '#4F46E5';
    $bgColor = '#F3F4F6';
    $isRtl = $lang === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $alignEnd = $isRtl ? 'left' : 'right';
@endphp

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: {{ $bgColor }}; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 40px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                <tr>
                    <td height="6" style="background-color: {{ $mainColor }};"></td>
                </tr>
                <tr>
                    <td style="padding: 40px 40px 20px 40px; text-align: {{ $alignStart }}; direction: {{ $dirAttr }};">
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ $lang === 'en' ? 'Hello' : 'مرحبًا' }} {{ $data['owner_name_'. $lang] ?? __('email.user') }},</h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $lang === 'en' ? "We’re pleased to inform you that your startup has successfully progressed to the next phase of the program." : "يسعدنا إبلاغكم بأن شركتكم الناشئة قد انتقلت بنجاح إلى المرحلة التالية من البرنامج." }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ $lang === 'en' ? 'Previous Phase' : 'المرحلة السابقة' }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $data['last_step'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #6B7280; width: 150px;">{{ $lang === 'en' ? 'New Phase' : 'المرحلة الجديدة' }}:</td>
                                            <td style="padding-bottom: 10px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $data['new_step'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280; width: 150px;">{{ $lang === 'en' ? 'Start Date' : 'تاريخ البدء' }}:</td>
                                            <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $data['start_date'] ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $lang === 'en' ? "Please log in to the platform to review the upcoming activities and requirements for this phase." : "يرجى تسجيل الدخول إلى المنصة للاطلاع على الأنشطة والمتطلبات القادمة الخاصة بهذه المرحلة." }}</p>
                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ $lang === 'en' ? "We look forward to supporting your continued journey and success." : "نتطلع إلى مواصلة دعم رحلتكم وتحقيق المزيد من النجاح." }}</p>

                        <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                            {{ $lang === 'en' ? 'Best regards' : 'مع أطيب التحيات' }},<br>
                            <strong>{{ $platformName }}</strong>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
