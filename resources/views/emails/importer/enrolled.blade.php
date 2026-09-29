@php $locale = $data['locale'] ?? 'en'; @endphp
@if ($locale === 'ar')
<div dir="rtl">
<p>مرحبًا {{ $data['founder_name'] }}،</p>
<p>تمت إضافتك إلى برنامج {{ $data['program_name'] }}.</p>
<p>نوع البرنامج: {{ $data['program_type'] }}.</p>
<p>تاريخ البداية: {{ $data['start_date'] }}.</p>
<p>تاريخ النهاية: {{ $data['end_date'] }}.</p>
<p>الجهة: {{ $data['organization'] }}.</p>
<p>يرجى تسجيل الدخول إلى حسابك للاطلاع على تفاصيل البرنامج.</p>
</div>
@else
<p>Hello {{ $data['founder_name'] }},</p>
<p>You have been added to the program {{ $data['program_name'] }}.</p>
<p>Program type: {{ $data['program_type'] }}.</p>
<p>Start date: {{ $data['start_date'] }}.</p>
<p>End date: {{ $data['end_date'] }}.</p>
<p>Organization: {{ $data['organization'] }}.</p>
<p>Please log in to your account to view the program details.</p>
@endif
