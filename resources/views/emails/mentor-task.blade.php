@extends('emails.layout.master')

@section('content')
                                            <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3"
                                                   role="presentation"
                                                   style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
                                                   width="100%">
                                                <tr>
                                                    @php
                                                    $isArH = app()->getLocale() === 'ar';
                                                    $actionMessages = [
                                                        'add'     => $isArH ? 'تمت إضافة مهمة جديدة'        : 'A New Task Has Been Added',
                                                        'update'  => $isArH ? 'تم تحديث المهمة'             : 'Task Has Been Updated',
                                                        'archive' => $isArH ? 'تمت أرشفة المهمة'            : 'Task Has Been Archived',
                                                        'reopen'  => $isArH ? 'تمت إعادة فتح المهمة'        : 'Task Has Been Reopened',
                                                        'move'    => $isArH ? 'تم نقل المهمة'               : 'A Task Has Been Moved',
                                                        'comment' => $isArH ? 'تم إضافة تعليق جديد على مهمتك' : 'A New Comment Has Been Added to Your Task',
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
                                                        @php
                                                            $isAr       = app()->getLocale() === 'ar';
                                                            $isMentor   = !$notifiable->hasRole('registered');
                                                            $mentorName = $data['mentor']->name ?? '';
                                                            $taskTitle  = $data['task']->title ?? '';
                                                            $startups   = $data['startupNames'] ?? [];
                                                            $isRtl      = $isAr;
                                                        @endphp
                                                        <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ $isRtl ? 'right' : 'left' }};mso-line-height-alt:22.5px;" {{ $isRtl ? 'dir=rtl' : '' }}>
                                                            <p style="margin: 0;">{{ $isAr ? 'عزيزي' : 'Dear' }} {{ $notifiable->name }},</p>
                                                            <p style="margin: 0;"> </p>
                                                            @if($isMentor)
                                                                @php
                                                                    $mentorBodyAr = match($action) {
                                                                        'add'     => "تم إضافة المهمة <strong>{$taskTitle}</strong> بنجاح إلى الشركات الناشئة التالية:",
                                                                        'update'  => "تم تحديث المهمة <strong>{$taskTitle}</strong> للشركات الناشئة التالية:",
                                                                        'archive' => "تمت أرشفة المهمة <strong>{$taskTitle}</strong> - الشركات الناشئة:",
                                                                        'reopen'  => "تمت إعادة فتح المهمة <strong>{$taskTitle}</strong> للشركات الناشئة التالية:",
                                                                        default   => "تحديث على المهمة <strong>{$taskTitle}</strong> - الشركات:",
                                                                    };
                                                                    $mentorBodyEn = match($action) {
                                                                        'add'     => "The task <strong>{$taskTitle}</strong> has been successfully added to the following startups:",
                                                                        'update'  => "The task <strong>{$taskTitle}</strong> has been updated for the following startups:",
                                                                        'archive' => "The task <strong>{$taskTitle}</strong> has been archived — Startups:",
                                                                        'reopen'  => "The task <strong>{$taskTitle}</strong> has been reopened for the following startups:",
                                                                        default   => "Update on task <strong>{$taskTitle}</strong> — Startups:",
                                                                    };
                                                                @endphp
                                                                <p style="margin: 0;">{!! $isAr ? $mentorBodyAr : $mentorBodyEn !!}</p>
                                                                <p style="margin: 0;"> </p>
                                                                <ul style="margin: 0; {{ $isRtl ? 'padding-right: 20px;' : 'padding-left: 20px;' }}">
                                                                    @foreach($startups as $startup)
                                                                        <li style="margin-bottom: 4px;">{{ $startup }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                @php
                                                                    $startupBodyAr = match($action) {
                                                                        'add'     => "تم إضافة مهمة جديدة <strong>{$taskTitle}</strong> إليك من خلال المينتور <strong>{$mentorName}</strong>.",
                                                                        'update'  => "تم تحديث المهمة <strong>{$taskTitle}</strong> من خلال المينتور <strong>{$mentorName}</strong>.",
                                                                        'archive' => "تمت أرشفة المهمة <strong>{$taskTitle}</strong> من خلال المينتور <strong>{$mentorName}</strong>.",
                                                                        'reopen'  => "تمت إعادة فتح المهمة <strong>{$taskTitle}</strong> من خلال المينتور <strong>{$mentorName}</strong>.",
                                                                        default   => "تحديث على المهمة <strong>{$taskTitle}</strong> من المينتور <strong>{$mentorName}</strong>.",
                                                                    };
                                                                    $startupBodyEn = match($action) {
                                                                        'add'     => "A new task <strong>{$taskTitle}</strong> has been assigned to you by mentor <strong>{$mentorName}</strong>.",
                                                                        'update'  => "The task <strong>{$taskTitle}</strong> has been updated by mentor <strong>{$mentorName}</strong>.",
                                                                        'archive' => "The task <strong>{$taskTitle}</strong> has been archived by mentor <strong>{$mentorName}</strong>.",
                                                                        'reopen'  => "The task <strong>{$taskTitle}</strong> has been reopened by mentor <strong>{$mentorName}</strong>.",
                                                                        default   => "Update on task <strong>{$taskTitle}</strong> by mentor <strong>{$mentorName}</strong>.",
                                                                    };
                                                                @endphp
                                                                <p style="margin: 0;">{!! $isAr ? $startupBodyAr : $startupBodyEn !!}</p>
                                                            @endif
                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">{{ $isAr ? 'أطيب التحيات،' : 'Best regards,' }}</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
@endsection
