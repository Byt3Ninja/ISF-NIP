@extends('emails.layout.master')

@section('content')
                                            <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3"
                                                   role="presentation"
                                                   style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
                                                   width="100%">
                                                <tr>

                                                    <td class="pad"
                                                        style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                                                        <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:150%;text-align:center;mso-line-height-alt:45px;">
                                                            <p style="margin: 0; word-break: break-word;"><span
                                                                        style="word-break: break-word; color: #000000;"><strong></strong></span>
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4"
                                                   role="presentation"
                                                   style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
                                                   width="100%">
                                                <tr>

                                                    <td class="pad"
                                                        style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                                                        <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:left;mso-line-height-alt:22.5px;">
                                                            <p style="margin: 0;">Hello {{ $invitation->first_name }},</p>
                                                            <p style="margin: 0;">Welcome in Our Platform</p>
                                                            <p style="margin: 0;">You've been invited to join the Organization {{ $invitation->organization->name }}.</p>
                                                            <p style="margin: 0;">Click the Link below to set your Password and complete your Registration:</p>
                                                            <a href="{{ $invitation->setPasswordUrl() }}">Set Your Password</a>
                                                            <p>This invitation will expire in 2 days.</p>
                                                            {{-- <p style="margin: 0;">Session will start at: {{ $date }}</p> --}}
                                                            <p style="margin: 0;">Best regards,</p>
                                                            {{-- <p style="margin: 0;">{{ $organization_name_en }}</p> --}}
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-5"
                                                   role="presentation"
                                                   style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
                                                   width="100%">
                                                <tr>
                                                    <td class="pad"
                                                        style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                                                        <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:right;mso-line-height-alt:22.5px;">
                                                            <p style="margin: 0;">عزيزي ,{{ $invitation->first_name }}</p>
                                                            <p style="margin: 0;">مرحباً بك فى منصتنا</p>
                                                            <p style="margin: 0;">لقد تمت دعوتك للانضمام إلى المنظمة {{ $invitation->organization->name }}.</p>
                                                            <p style="margin: 0;">اضغط فوق الرابط أدناه لتعيين كلمة المرور الخاصة بك وإكمال التسجيل الخاص بك:</p>
                                                            <a href="{{ $invitation->setPasswordUrl() }}">قم بتعيين كلمة المرور الخاصة بك</a>
                                                            <p style="margin: 0;">ستنتهي صلاحية هذه الدعوة خلال يومين.</p>
                                                            <p style="margin: 0;">أطيب التحيات،</p>
                                                            {{-- <p style="margin: 0;">{{ $organization_name_ar }}</p> --}}
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
@endsection
