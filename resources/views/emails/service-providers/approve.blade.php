@extends('emails.layout.master')

@section('content')
@if(($locale ?? 'en') === 'en')
												<table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
													<tr>
														<td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
															<div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:150%;text-align:center;mso-line-height-alt:45px;">
																<p style="margin: 0; word-break: break-word;">
																	<span style="word-break: break-word; color: #000000;">
																		<strong>Update on Your Request</strong>
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
																<p style="margin: 0;">Thank you for your recent request to join our platform and for the time you invested in preparing your submission.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">We are pleased to inform you that your request <strong>has been approved</strong>. After reviewing your details and capabilities, we believe that your services align well with our standards and operational needs.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">You can now access your account using the credentials you received during registration.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">
																	You may log in through the following link: <br />
																	<a href="{{ $login_url }}" target="_blank"> Log In to Your Dashboard </a>
																</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">
																	We look forward to building a successful and productive partnership with you. <br />
																	If you have any questions or need assistance during the onboarding process, please feel free to reach out.
																</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">Kind regards,</p>
															</div>
														</td>
													</tr>
												</table>
@else
												<table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
													<tr>
														<td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
															<div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:150%;text-align:center;mso-line-height-alt:45px;">
																<p style="margin: 0; word-break: break-word;">
																	<span style="word-break: break-word; color: #000000;">
																		<strong>تحديث بخصوص طلبكم</strong>
																	</span>
																</p>
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
															<div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:right;mso-line-height-alt:22.5px;direction:rtl;">
																<p style="margin: 0;">عزيزي {{ $contact_name }},</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">نشكر لكم طلبكم الأخير للانضمام إلى منصّتنا، ونقدّر الوقت والجهد اللذين بذلتموهما في تقديم طلبكم.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">يسعدنا إبلاغكم بأنه <strong>تم قبول طلبكم</strong>. بعد مراجعة معلوماتكم وقدراتكم، وجدنا أن خدماتكم تتوافق بشكل جيد مع معاييرنا واحتياجاتنا الحالية.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">يمكنكم الآن الوصول إلى حسابكم باستخدام بيانات الدخول التي تلقيتموها عند التسجيل.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">
																	يمكنكم تسجيل الدخول من خلال الرابط التالي: <br />
																	<a href="{{ $login_url }}" target="_blank"> تسجيل الدخول إلى لوحة التحكم </a>
																</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">
																	نحن نتطلع إلى بناء علاقة عمل ناجحة ومثمرة معكم. <br />
																	في حال احتجتم لأي مساعدة أو كان لديكم أي استفسار، فلا تترددوا في التواصل معنا.
																</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">مع خالص التحية</p>
															</div>
														</td>
													</tr>
												</table>
@endif
@endsection
