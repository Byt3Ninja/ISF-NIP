@extends('emails.layout.master')

@section('content')
@if(($locale ?? 'en') === 'en')
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
																<p style="margin: 0;">Welcome to Our Platform</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">We are glad to inform you that we have received your registration request.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">Your request is currently under review. You can log in to your dashboard to track the status of your application using the credentials below:</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">
																	<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
																		<tr>
																			<td>Login Email</td>
																			<td> {{ $email }} </td>
																		</tr>
																	</table>
																</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">
																	You may log in through the following link: <br />
																	<a href="{{ $login_url }}" target="_blank"> Log In to Your Dashboard </a>
																</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">This sign-in link works once and is valid for 24 hours. After signing in, set your password from your profile.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">Best regards,</p>
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
																		<strong>
																			تم استقبال طلب التسجيل الخاص بك
																		</strong>
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
																<p style="margin: 0;">مرحباً بك في منصتنا</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">يسعدنا أن نخبرك أننا تلقينا طلب التسجيل الخاص بك.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">طلبك قيد المراجعة حاليًا. يمكنك تسجيل الدخول إلى لوحة التحكم لمتابعة حالة طلبك باستخدام بيانات الدخول التالية:</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">
																	<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
																		<tr>
																			<td>البريد الإلكتروني للدخول:</td>
																			<td> {{ $email }} </td>
																		</tr>
																	</table>
																</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">
																	يمكنك تسجيل الدخول من خلال الرابط التالي: <br />
																	<a href="{{ $login_url }}" target="_blank"> تسجيل الدخول إلى لوحة التحكم </a>
																</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">يعمل رابط تسجيل الدخول هذا مرة واحدة وهو صالح لمدة 24 ساعة. بعد تسجيل الدخول، عيّن كلمة المرور من ملفك الشخصي.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">أطيب التحيات،</p>
															</div>
														</td>
													</tr>
												</table>
@endif
@endsection
