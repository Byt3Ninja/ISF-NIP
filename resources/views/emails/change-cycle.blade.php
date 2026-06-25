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
    <tr>
        <td class="content-block" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 16px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
            {{--  <h1 style="font-family: 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif; box-sizing: border-box; font-size: 24px; color: #000; line-height: 1.2; font-weight: 400; margin: 20px 0 30px; padding: 0;">
                {{ $lang === 'en' ? "Congratulations! You've Advanced to the Next Phase" : "تهانينا! لقد انتقلت إلى المرحلة التالية" }}
            </h1>  --}}
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 16px; line-height: 1.6; margin: 0 0 20px; padding: 0;">
                {{ $lang === 'en' ? "Hello" : "مرحبًا" }} {{ $data['owner_name_'. $lang] ?? __('email.user') }},
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 16px; line-height: 1.6; margin: 0 0 20px; padding: 0;">
                {{ $lang === 'en' ? "We’re pleased to inform you that your startup has successfully progressed to the next phase of the program." : "يسعدنا إبلاغكم بأن شركتكم الناشئة قد انتقلت بنجاح إلى المرحلة التالية من البرنامج." }}
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 0; color: #666;">
                {{ $lang === 'en' ? "Phase Details:" : "تفاصيل المرحلة:" }}
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 0; color: #666;">
                {{ $lang === 'en' ? "Previous Phase: " : "المرحلة السابقة: " }}: {{ $data['last_step'] ?? '-' }}
                <br />
                {{ $lang === 'en' ? "New Phase: " : "المرحلة الجديدة: " }}: {{ $data['new_step'] ?? '-' }}
                <br />
                {{ $lang === 'en' ? "Start Date:" : "تاريخ البدء: " }}: {{ $data['start_date'] ?? '-' }}
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 0; color: #666;">
                {{ $lang === 'en' ? "Please log in to the platform to review the upcoming activities and requirements for this phase." : "يرجى تسجيل الدخول إلى المنصة للاطلاع على الأنشطة والمتطلبات القادمة الخاصة بهذه المرحلة." }}
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 10px; background: #f5f5f5; border-radius: 3px; word-break: break-all;">
                {{ $lang === 'en' ? "We look forward to supporting your continued journey and success." : "نتطلع إلى مواصلة دعم رحلتكم وتحقيق المزيد من النجاح." }}
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;">
                {{ $lang === 'en' ? "Best regards" : "مع أطيب التحيات" }},<br>
                {{ $platformName }}
            </p>
        </td>
    </tr>
@endsection
