@extends('emails.layout.master')

@section('content')
												<table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
													<tr>
														<td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
															<div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:150%;text-align:center;mso-line-height-alt:45px;">
																<p style="margin: 0; word-break: break-word;">
																	<span style="word-break: break-word; color: #000000;">
																		<strong>
																			Your registration request has been received
																		</strong>
																	</span>
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
																<p style="margin: 0;">Dear {{ $contact_name }},</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">Welcome in Our Platform</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">We glad to inform you that we have received your registration request.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">Please wait for our response soon.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">Best regards,</p>
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
																<p style="margin: 0;">عزيزي {{ $contact_name }},</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">مرحباً بك فى منصتنا</p>
																<p style="margin: 0;">يسعدنا أن نخبرك أننا تلقينا طلب التسجيل الخاص بك.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">يرجى انتظار ردنا قريبا.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">أطيب التحيات،</p>
															</div>
														</td>
													</tr>
												</table>
@endsection
