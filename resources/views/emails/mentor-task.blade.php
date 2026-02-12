@extends('emails.layout.master')

@section('content')
                                            <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3"
                                                   role="presentation"
                                                   style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
                                                   width="100%">
                                                <tr>
                                                    @php
                                                    $actionMessages = [
                                                        'add' => 'A New Task Has Been Added',
                                                        'move' => 'A Task Has Been Moved',
                                                        'comment' => 'A New Comment Has Been Added to Your Task',
                                                    ];
                                                   @endphp
                                                    <td class="pad"
                                                        style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                                                        <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:150%;text-align:center;mso-line-height-alt:45px;">
                                                            <p style="margin: 0; word-break: break-word;"><span
                                                                        style="word-break: break-word; color: #000000;"><strong>{{ $actionMessages[$action] }}</strong></span>
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
                                                            <p style="margin: 0;">Dear {{ $notifiable->name }},</p>
                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">Welcome in Our Platform</p>
                                                            <p style="margin: 0;"> </p>
                                                            {{-- <p style="margin: 0;">Session will start at: {{ $date }}</p> --}}
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
                                                            <p style="margin: 0;">عزيزي {{ $notifiable->name }},</p>
                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">مرحباً بك  فى منصتنا</p>
                                                            <p style="margin: 0;"> </p>


                                                            </p>
                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">أطيب التحيات،</p>
                                                            {{-- <p style="margin: 0;">{{ $organization_name_ar }}</p> --}}
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
@endsection
