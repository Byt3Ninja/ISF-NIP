@extends('emails.layout.master')

@section('content')
                                            <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                                                <tr>
                                                    <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                                                        <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:left;mso-line-height-alt:22.5px;">
                                                            <p style="margin: 0;">Dear {{ $admin->first_name_en . ' ' . $admin->last_name_en }},</p>
                                                            <p style="margin: 0;">A New Program Titled "<strong>{{ $cycle->title }}</strong>" has been Created.</p>
                                                            <p style="margin: 0;">Best regards,</p>
                                                            <p style="margin: 0;">{{ $org_name ?? config('app.name', 'GrowthLabs') }}</p>
                                                            </div>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-5" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                                                <tr>
                                                    <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                                                        <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:right;mso-line-height-alt:22.5px;">
                                                            <p style="margin: 0;">عزيزي {{ $admin->first_name_en . ' ' . $admin->last_name }},</p>
                                                            <p style="margin: 0;">تم إنشاء برنامج جديد بعنوان "<strong>{{ $cycle->title }}</strong>".</p>
                                                            <p style="margin: 0;">أطيب التحيات،</p>
                                                            <p style="margin: 0;">{{ $org_name ?? config('app.name', 'GrowthLabs') }}</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
@endsection
