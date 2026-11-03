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
                                                                        style="word-break: break-word; color: #000000;"><strong>{{ __('email.organization_created_heading', [], 'en') }}</strong></span>
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
                                                            <p style="margin: 0;">Dear {{ $user->name }},</p>
                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">{{ __('email.organization_created_welcome', [], 'en') }}</p>
                                                            <p style="margin: 0;"> </p>
                                                             <p style="margin: 0;">{{ __('email.organization_created_name_is', ['name' => $organization->name_en], 'en') }}</p>

                                                            <p style="margin: 0;">Email is: {{ $user->email }}</p>
                                                            <p style="margin: 0;">Sign in with this link (valid for 24 hours), then set your password from your profile: <a href="{{ $loginUrl }}">Sign in</a></p>

                                                            <p style="margin: 0;"> </p>
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
                                                            <p style="margin: 0;">عزيزي {{ $user->name }},</p>
                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">{{ __('email.organization_created_welcome', [], 'ar') }}</p>
                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">الاسم بالعربي: {{ $organization->name_ar }}</p>


                                                            <p style="margin: 0;">البريد الالكترونى : {{ $user->email }}</p>
                                                            <p style="margin: 0;">سجّل الدخول من خلال هذا الرابط (صالح لمدة 24 ساعة)، ثم عيّن كلمة المرور من ملفك الشخصي: <a href="{{ $loginUrl }}">تسجيل الدخول</a></p>

                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">أطيب التحيات،</p>
                                                            {{-- <p style="margin: 0;">{{ $organization_name_ar }}</p> --}}
                                                        </div>
                                                    </td>
                                                </tr>


                                            </table>
@endsection
