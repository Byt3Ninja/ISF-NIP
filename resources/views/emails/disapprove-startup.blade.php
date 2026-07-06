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
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 0;">
                {{ $lang === 'en' ? "Thank you for your interest in joining " . $data['program_title'] . " and for taking the time to complete your application." : "شكرًا لاهتمامكم بالانضمام إلى " . $data['program_title'] . " ولقضاء الوقت في استكمال طلب التقديم." }}
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 0;">
                {{ $lang === 'en' ? "After careful review, we regret to inform you that your startup has not been selected to proceed to the next stage of the program at this time." : "بعد مراجعة الطلبات بعناية، نأسف لإبلاغكم بأنه لم يتم اختيار شركتكم الناشئة للانتقال إلى المرحلة التالية من البرنامج في الوقت الحالي." }}
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 0;">
                {{ $lang === 'en' ? "We appreciate the effort invested in your application and encourage you to continue developing your startup journey. We also encourage you to apply for future opportunities and programs that may align with your goals." : "نُقدّر الجهد المبذول في تقديم الطلب، ونتمنى لكم التوفيق في رحلة تطوير شركتكم. كما ندعوكم للتقديم على البرامج والفرص القادمة التي قد تتناسب مع أهدافكم." }}
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 0;">
                {{ $lang === 'en' ? "We wish you and your team continued success." : "نتمنى لكم ولفريقكم دوام النجاح والتوفيق." }}
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 0;">
                {{ $lang === 'en' ? "Best regards" : "مع أطيب التحيات" }},<br>
                {{ $platformName }}
            </p>
        </td>
    </tr>
@endsection
