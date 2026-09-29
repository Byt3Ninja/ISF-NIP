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
																<p style="margin: 0;">Thank you for your recent request and for the effort you put into presenting your proposal. We truly appreciate your interest in working with us.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">After careful consideration, we regret to inform you that we are unable to proceed with your request at this time due to:</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">{{ $reason }}</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">This decision was made based on our current priorities and requirements, and does not reflect the quality of your work or professionalism.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">We value the relationship we have with you and hope that there may be opportunities to collaborate in the future. Thank you again for your understanding.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">If you need any clarification, please feel free to reach out.</p>
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
																<p style="margin: 0;">نشكر لكم تواصلكم الأخير والجهد المبذول في تقديم مقترحكم. نحن نُقدّر اهتمامكم بالتعاون معنا.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">بعد دراسة متأنية، نود إبلاغكم -- مع كامل التقدير -- بأنه يتعذر علينا المضي قدمًا في طلبكم في الوقت الحالي، وذلك بسبب:</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">{{ $reason }}</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">وقد تم اتخاذ هذا القرار بناءً على مراجعة أولوياتنا واحتياجاتنا الحالية، ولا يعكس بأي شكل من الأشكال جودة عملكم أو مهنيتكم.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">إننا نثمّن علاقتنا المهنية معكم، ونأمل أن تتاح لنا فرص للتعاون مستقبلاً. نشكركم مرة أخرى على تفهّمكم.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">وفي حال احتجتم أي توضيح، فلا تترددوا في التواصل معنا.</p>
																<p style="margin: 0;"> </p>
																<p style="margin: 0;">مع خالص التحية</p>
															</div>
														</td>
													</tr>
												</table>
@endif
@endsection
