  @extends('emails.layout.master')
@section('content')  

                                    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                                        <tr>
                                            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                                                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:120%;text-align:center;mso-line-height-alt:36px;">
                                                    <p style="margin: 0; word-break: break-word;"><span style="color:#2b303a;"><strong>{{__("email.verify_email.subject")}}</strong></span></p>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                                        <tr>
                                            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                                                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:left;mso-line-height-alt:22.5px;">
                                                    <p style="margin: 0;">{{__("email.verify_email.greeting")}}{{ $name }},</p>
                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;">{{__('email.verify_email.in_body_1')}}</p>
                                                    <p style="margin: 0;">{{__('email.verify_email.in_body_2')}}</p>
                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;"><strong>{{__("email.verify_email.action_text")}}</strong></p>
                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;">{{__('email.verify_email.in_body_3')}}</p>
                                                    <p style="margin: 0;"> </p>
                                                    <table border="0" cellpadding="0" cellspacing="0" class="button_block block-5" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
                                                        <tr>
                                                            <td class="pad" style="padding-left:10px;padding-right:10px;padding-top:15px;text-align:center;">

                                                                <div align="center" class="alignment"><!--[if mso]>
                                                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" style="height:62px;width:247px;v-text-anchor:middle;" arcsize="97%" stroke="false" fillcolor="#1aa19c">
                                                                        <w:anchorlock/>
                                                                        <v:textbox inset="0px,0px,0px,0px">
                                                                            <center style="color:#ffffff; font-family:Tahoma, sans-serif; font-size:16px">
                                                                    <![endif]-->
                                                                    <a href="{{ $url }}" target="_blank">
                                                                    <div style="text-decoration:none;display:inline-block;color:#ffffff;background-color:#1aa19c;border-radius:60px;width:auto;border-top:0px solid transparent;font-weight:undefined;border-right:0px solid transparent;border-bottom:0px solid transparent;border-left:0px solid transparent;padding-top:15px;padding-bottom:15px;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:16px;text-align:center;mso-border-alt:none;word-break:keep-all;"><span style="padding-left:30px;padding-right:30px;font-size:16px;display:inline-block;letter-spacing:normal;"><span style="margin: 0; word-break: break-word; line-height: 32px;"><strong>{{__('email.verify_email.action_text')}}</strong></span></span></div><!--[if mso]></center></v:textbox></v:roundrect><![endif]-->
                                                                    </a>
                                                                </div>

                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;">{{__('email.verify_email.in_body_4')}}</p>
                                                    <p style="margin: 0;">{{__('email.verify_email.in_body_5')}}</p>
                                                    <p style="margin: 0;"> </p>
                                                    <p style="margin: 0;">{{__('email.verify_email.regards')}}</p>
                                                    <p style="margin: 0;">{{__('email.verify_email.team')}}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" class="divider_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
                                        <tr>
                                            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:25px;">
                                                <div align="center" class="alignment">
                                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
                                                        <tr>
                                                            <td class="divider_inner" style="font-size: 1px; line-height: 1px; border-top: 1px solid #555961;"><span> </span></td>
                                                        </tr>
                                                        <td class="pad" style="padding-bottom:30px;padding-left:40px;padding-right:40px;padding-top:20px;">
                                                            <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:12px;line-height:120%;text-align:left;mso-line-height-alt:14.399999999999999px;">
                                                                <p style="margin: 0; word-break: break-word;"><span style="color: #95979c;">
                                                                            {{__('email.verify_email.trouble_clicking',[ 'link' => $url ])}} 
                                            </span></p>
                                                            </div>
                                                        </td>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
@endsection