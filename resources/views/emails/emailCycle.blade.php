@extends('emails.layout.master')
@section('content')

                                    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                                        <tr>
                                            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                                                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:150%;text-align:center;mso-line-height-alt:45px;">
                                                    <p style="margin: 0; word-break: break-word;"><span style="color: #000000;"><strong>{{__('email.cycle_email.title')}}</strong></span></p>
                                                    <p style="margin: 0; word-break: break-word;"><span style="color: #000000;"><strong>{{__('email.cycle_email.dear')}}</strong></span></p>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                                        <tr>
                                            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                                                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:left;mso-line-height-alt:22.5px;">
                                                    @if($status== 'in')
                                                    <p style="margin: 0;">  {{__('email.cycle_email.dear')}} {{$owner_name_en}},</p>
                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;">{{__('email.cycle_email.in_body_1', ['company_name' => $company_name_en, 'last_step' => $last_step, 'new_step' => $new_step])}}</p>
                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;">{{__('email.cycle_email.in_body_2')}}</p>
                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;">{{__('email.cycle_email.in_body_3')}}</p>
                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;">{{__('email.cycle_email.regards')}}</p>
                                                    <p style="margin: 0;">{{ $organization_name_en }}</p>
                                                    @else
                                                    <p style="margin: 0;">{{__('email.cycle_email.dear')}} {{$owner_name_en}},</p>
                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;">{{__('email.cycle_email.in_body_1', ['company_name' => $company_name_en, 'last_step' => $last_step, 'new_step' => $new_step])}}</p>                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;">{{__('email.cycle_email.in_body_2')}}</p>                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;">{{__('email.cycle_email.regards')}}</p>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    </table>

@endsection