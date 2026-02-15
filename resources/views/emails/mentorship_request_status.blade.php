@extends('emails.layout.master')

@section('content')
    {{-- English --}}
    <table>
        <tr>
            <td>
                <p>Dear {{ $data['mentorName'] }},</p>
                <p>A new mentorship session has been {{ $data['status'] == 'Yes' ? 'Approved' : 'Declined' }}.</p>
                <ul>
                    <li><strong>Startup:</strong> {{ $data['startupName'] }}</li>
                    <li><strong>Date & Time:</strong> {{ $data['sessionDate'] }}</li>
                    <li><strong>Duration:</strong> {{ $data['duration'] }}</li>
                    <li><strong>Type:</strong> {{ $data['sessionType'] }}</li>
                    @if($data['sessionType'] == 'Online')
                        <li><strong>Meeting Link:</strong> {{ $data['meetingLink'] }}</li>
                        <li><strong>Meeting Pass:</strong> {{ $data['meetingPass'] }}</li>
                    @else
                        <li><strong>Location:</strong> {{ $data['location'] }}</li>
                    @endif
                    @if(!empty($data['notes']))
                        <li><strong>Notes:</strong> {{ $data['notes'] }}</li>
                    @endif
                    @if(!empty($data['attachments']))
                        <li><strong>Attachments:</strong>
                            @foreach($data['attachments'] as $att)
                                <a href="{{ $att['url'] }}">{{ $att['name'] }}</a>
                            @endforeach
                        </li>
                    @endif
                </ul>
                <p>Best regards,<br>{{ env('APP_NAME') }}</p>
            </td>
        </tr>
    </table>

    {{-- Arabic --}}
    <table dir="rtl">
        <tr>
            <td>
                <p>عزيزي {{ $data['mentorName'] }},</p>
                <p>تم {{ $data['status'] == 'Yes' ? 'موافقة' : 'رفض' }}  جلسة إرشاد جديدة.</p>
                <ul>
                    <li><strong>الشركة الناشئة:</strong> {{ $data['startupName'] }}</li>
                    <li><strong>التاريخ والوقت:</strong> {{ $data['sessionDate'] }}</li>
                    <li><strong>المدة:</strong> {{ $data['duration'] }}</li>
                    <li><strong>النوع:</strong> {{ $data['sessionType'] }}</li>
                    @if($data['sessionType'] == 'Online')
                        <li><strong>رابط الاجتماع:</strong> {{ $data['meetingLink'] }}</li>
                        <li><strong> رمز الدخول:</strong> {{ $data['meetingPass'] }}</li>
                    @else
                        <li><strong>الموقع:</strong> {{ $data['location'] }}</li>
                    @endif
                    @if(!empty($data['notes']))
                        <li><strong>ملاحظات:</strong> {{ $data['notes'] }}</li>
                    @endif
                    @if(!empty($data['attachments']))
                        <li><strong>المرفقات:</strong>
                            @foreach($data['attachments'] as $att)
                                <a href="{{ $att['url'] }}">{{ $att['name'] }}</a>
                            @endforeach
                        </li>
                    @endif
                </ul>
                <p>أطيب التحيات,<br>{{ env('APP_NAME') }}</p>
            </td>
        </tr>
    </table>
@endsection
