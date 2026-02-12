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
                                                            <p style="margin: 0;">Hello ,</p>
                                                            <p style="margin: 0;">You have been invited to join the team: {{ $teamName->name }}</p>
                                                            <p style="margin: 0;">This link will expire in 24 hours</p>
                                                            <p style="margin: 0;">Please click the link below to accept or reject :</p>
                                                            <a style="display:inline-block; padding:10px 20px; background-color:#198754; color:#ffffff; text-decoration:none; border-radius:5px; font-weight:bold;" href="{{ url("dashboard/hackathon/details/{$teamName->cycle_id}") }}"> View </a>

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
                                                            <p style="margin: 0;">عزيزي ,</p>
                                                            <p style="margin: 0;">مرحباً بك فى منصتنا</p>
                                                            <p style="margin: 0;">لقد تم دعوتك للفريق: {{ $teamName->name }}</p>
                                                            <p style="margin: 0;">سينتهى هذا الرابط بعد 24 ساعة</p>
                                                            <p style="margin: 0;">يرجى الضغط على اللينك التالي للمشاهدة :</p>
                                                            <a style="display:inline-block; padding:10px 20px; background-color:#198754; color:#ffffff; text-decoration:none; border-radius:5px; font-weight:bold;" href="{{ url("dashboard/hackathon/details/{$teamName->cycle_id}") }}"> مشاهدة </a>
                                                            <p style="margin: 0;">أطيب التحيات،</p>
                                                            {{-- <p style="margin: 0;">{{ $organization_name_ar }}</p> --}}
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
@endsection
