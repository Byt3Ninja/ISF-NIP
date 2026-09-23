@extends('emails.layout.master')

@section('content')
@php
    $isRtl = ($locale ?? app()->getLocale()) === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $showOrganizations = $scope === 'Organization' && !empty($organizations);
    $orgLabel = $isRtl
        ? (count($organizations ?? []) === 1 ? 'الجهة' : 'الجهات')
        : (count($organizations ?? []) === 1 ? 'Organization' : 'Organizations');
@endphp
    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dirAttr }}" style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ $alignStart }};mso-line-height-alt:22.5px;">
                    <h2 style="margin: 0 0 16px; color: #1f2937; font-size: 20px; font-weight: 600;">
                        {{ $isRtl ? 'مرحباً' : 'Hello' }} {{ $name }}،
                    </h2>

                    @if(!empty($messageBody))
                        <p style="margin: 0 0 16px;">{{ $messageBody }}</p>
                    @else
                        <p style="margin: 0 0 16px;">{{ $isRtl ? 'تمت دعوتك للانضمام كمرشد.' : 'You have been invited to join as a mentor.' }}</p>
                    @endif

                    <p style="margin: 0 0 10px; font-weight: 600; color: #111827;">{{ $isRtl ? 'تفاصيل الدعوة' : 'Invitation Details' }}</p>

                    <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 12px 16px; margin: 0 0 16px;">
                        <p style="margin: 0 0 8px; font-size: 14px;">
                            <strong>{{ $isRtl ? 'المنصة' : 'Platform' }}:</strong> {{ $invitePlatformName ?? '-' }}
                        </p>
                        @if($showOrganizations)
                            <p style="margin: 0; font-size: 14px;">
                                <strong>{{ $orgLabel }}:</strong>
                                {{ collect($organizations)->pluck('name')->implode('، ') }}
                            </p>
                        @endif
                    </div>

                    <p style="margin: 0 0 16px;">{{ $isRtl ? 'يرجى الضغط على الزر أدناه لقبول الدعوة واستكمال عملية التسجيل.' : 'Please click the button below to accept the invitation and complete your registration.' }}</p>
                </div>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" class="button_block" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
        <tr>
            <td class="pad" style="padding: 20px 40px; text-align: center;">
                <a href="{{ env('APP_URL') }}/register?id=MentorRole" target="_blank" style="display: inline-block; padding: 14px 28px; background-color: #1aa19c; color: #ffffff; text-decoration: none; border-radius: 6px; font-family: Montserrat, Trebuchet MS, sans-serif; font-size: 15px; font-weight: 600;">
                    {{ $isRtl ? 'قبول الدعوة' : 'Accept Invitation' }}
                </a>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-5" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div dir="{{ $dirAttr }}" style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ $alignStart }};mso-line-height-alt:22.5px;">
                    <p style="margin: 0 0 16px;">{{ $isRtl ? 'إذا كانت لديك أي استفسارات أو كنت بحاجة إلى مساعدة، يرجى التواصل معنا.' : 'If you have any questions or need assistance, please feel free to reach out.' }}</p>
                    <p style="margin: 0;">{{ $isRtl ? 'مع أطيب التحيات،' : 'Best regards,' }}</p>
                    <p style="margin: 0; font-weight: 600;">
                        @if($isRtl)
                            فريق {{ $invitePlatformName ?? config('app.name', 'GrowthLabs') }}
                        @else
                            {{ $invitePlatformName ?? config('app.name', 'GrowthLabs') }} Team
                        @endif
                    </p>
                </div>
            </td>
        </tr>
    </table>
@endsection
