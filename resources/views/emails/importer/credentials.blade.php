@php $locale = $data['locale'] ?? 'en'; @endphp
@if ($locale === 'ar')
<div dir="rtl">
<p>مرحبًا {{ $data['founder_name'] }}،</p>
<p>تمت إضافتك إلى برنامج {{ $data['program_name'] }}.</p>
<p>نوع البرنامج: {{ $data['program_type'] }}.</p>
<p>تاريخ البداية: {{ $data['start_date'] }}.</p>
<p>تاريخ النهاية: {{ $data['end_date'] }}.</p>
<p>الجهة: {{ $data['organization'] }}.</p>
<p>يمكنك تسجيل الدخول باستخدام البيانات التالية:</p>
<p>البريد الإلكتروني: {{ $data['email'] }}.<br>كلمة المرور: {{ $data['password'] }}.</p>
<p>يرجى تسجيل الدخول وتغيير كلمة المرور بعد أول تسجيل دخول.</p>
</div>
@else
<p>Hello {{ $data['founder_name'] }},</p>
<p>You have been added to the program {{ $data['program_name'] }}.</p>
<p>Program type: {{ $data['program_type'] }}.</p>
<p>Start date: {{ $data['start_date'] }}.</p>
<p>End date: {{ $data['end_date'] }}.</p>
<p>Organization: {{ $data['organization'] }}.</p>
<p>You can log in using the following details:</p>
<p>Email: {{ $data['email'] }}.<br>Password: {{ $data['password'] }}.</p>
<p>Please log in and change your password after your first login.</p>
@endif
