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
                                                                        style="word-break: break-word; color: #000000;"><strong>New Training Created</strong></span>
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
                                                            <p style="margin: 0;">Dear {{ $data['name'] }},</p>
                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">New Training created</p>
                                                            <p style="margin: 0;"> </p>

                                                            <p style="margin: 0;">We are excited to inforam you that a new training session has been created: {{ $data['title'] }}.
                                                                This training will provide valuable insights and skills to help your company grow.</p>

                                                            <p style="margin: 0;">
                                                                **Date:** {{ $data['date'] }} </p>
                                                                <p style="margin: 0;"> </p>
                                                                @if($data['type'] == 'sessions')
                                                            <p style="margin: 0;">
                                                                **Link:** {{ $data['join_url'] }}
                                                            </p>
                                                            @elseif($data['type'] == 'online')
                                                            <p style="margin: 0;">
                                                                **Link:** {{ $data['join_url'] }}
                                                            </p>
                                                            @elseif($data['type'] == 'offline')
                                                            <p style="margin: 0;">
                                                                **Google Map Link:** {{ $data['join_url'] }}
                                                            </p>
                                                            <p style="margin: 0;">
                                                                **Address:** {{ $data['Address'] }}
                                                            </p>
                                                            @endif


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
                                                            <p style="margin: 0;">عزيزي {{ $data['name'] }},</p>
                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">تم انشاء تدريب جديد</p>
                                                            <p style="margin: 0;"> </p>

                                                            <p style="margin: 0;">
                                                                يسعدنا إعلامكم بأنه قد تم إنشاء تدريب جديد:  {{ $data['title'] }}.
                                                                سيوفر هذا التدريب رؤى ومهارات قيّمة لمساعدتكم على تطوير أعمالكم.</p>

                                                                <p style="margin: 0;">
                                                                    **التاريخ:** {{ $data['date'] }} </p>
                                                                    <p style="margin: 0;"> </p>
                                                                    @if($data['type'] == 'sessions')
                                                                <p style="margin: 0;">
                                                                    **لينك:** {{ $data['join_url'] }}
                                                                </p>
                                                                @elseif($data['type'] == 'online')
                                                                <p style="margin: 0;">
                                                                    **لينك:** {{ $data['join_url'] }}
                                                                </p>
                                                                @elseif($data['type'] == 'offline')
                                                                <p style="margin: 0;">
                                                                    **لينك خرائط جوجل:** {{ $data['join_url'] }}
                                                                </p>
                                                                <p style="margin: 0;">
                                                                    **العنوان:** {{ $data['Address'] }}
                                                                </p>
                                                                @endif


                                                                <p style="margin: 0;"> </p>


                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">أطيب التحيات،</p>
                                                            {{-- <p style="margin: 0;">{{ $organization_name_ar }}</p> --}}
                                                        </div>
                                                    </td>
                                                </tr>


                                            </table>
@endsection
