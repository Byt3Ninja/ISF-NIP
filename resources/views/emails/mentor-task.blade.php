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
                                                            $isAr        = app()->getLocale() === 'ar';
                                                            $isMentor    = !$notifiable->hasRole('registered');
                                                            $mentorName  = $data['mentor']->name ?? '';
                                                            $taskTitle   = $data['task']->title ?? '';
                                                            $startups    = $data['startupNames'] ?? [];
                                                            $isRtl       = $isAr;
                                                            $taskStatus  = $data['task']->status ?? '';
                                                            $programName = $data['task']->program?->title
                                                                ?? $data['task']->mentorshipRequest?->cycle?->title
                                                                ?? '';

                                                            $statusLabels = [
                                                                'pending'   => $isAr ? 'قيد الانتظار' : 'Pending',
                                                                'progress'  => $isAr ? 'قيد التنفيذ'  : 'In Progress',
                                                                'review'    => $isAr ? 'قيد المراجعة' : 'Under Review',
                                                                'completed' => $isAr ? 'مكتملة'        : 'Completed',
                                                                'archived'  => $isAr ? 'مؤرشفة'        : 'Archived',
                                                            ];
                                                            $statusLabel = $statusLabels[$taskStatus] ?? $taskStatus;

                                                            $mentorActionAr = match($action) {
                                                                'add'     => 'تمت إضافة مهمة جديدة إلى الشركات الناشئة التالية:',
                                                                'update'  => 'تم تحديث مهمة للشركات الناشئة التالية:',
                                                                'archive' => 'تمت أرشفة مهمة للشركات الناشئة التالية:',
                                                                'reopen'  => 'أصبحت المهمة نشطة مرة أخرى للشركات الناشئة التالية:',
                                                                default   => 'تحديث على مهمة للشركات الناشئة التالية:',
                                                            };
                                                            $mentorActionEn = match($action) {
                                                                'add'     => 'A new task has been added for the following startups:',
                                                                'update'  => 'A task has been updated for the following startups:',
                                                                'archive' => 'A task has been archived for the following startups:',
                                                                'reopen'  => 'A task has been reopened for the following startups:',
                                                                default   => 'An update on a task for the following startups:',
                                                            };

                                                            $startupActionAr = match($action) {
                                                                'add'     => 'تمت إضافة مهمة جديدة مسندة إليك.',
                                                                'update'  => 'تم تحديث مهمة مسندة إليك.',
                                                                'archive' => 'تمت أرشفة مهمة مسندة إليك.',
                                                                'reopen'  => 'أصبحت المهمة نشطة مرة أخرى.',
                                                                default   => 'تحديث على مهمة مسندة إليك.',
                                                            };
                                                            $startupActionEn = match($action) {
                                                                'add'     => 'A new task has been assigned to you.',
                                                                'update'  => 'A task assigned to you has been updated.',
                                                                'archive' => 'A task assigned to you has been archived.',
                                                                'reopen'  => 'A task assigned to you has been reopened.',
                                                                default   => 'An update on a task assigned to you.',
                                                            };
                                                        @endphp
                                                        <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ $isRtl ? 'right' : 'left' }};mso-line-height-alt:22.5px;" {{ $isRtl ? 'dir=rtl' : '' }}>
                                                            <p style="margin: 0;">{{ $isAr ? 'مرحباً' : 'Dear' }} {{ $notifiable->name }},</p>
                                                            <p style="margin: 0;"> </p>
                                                            @if($isMentor)
                                                                <p style="margin: 0;">{{ $isAr ? $mentorActionAr : $mentorActionEn }}</p>
                                                                <p style="margin: 0;"> </p>
                                                                <ul style="margin: 0; {{ $isRtl ? 'padding-right: 20px;' : 'padding-left: 20px;' }}">
                                                                    @foreach($startups as $startup)
                                                                        <li style="margin-bottom: 4px;">{{ $startup }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <p style="margin: 0;">{{ $isAr ? $startupActionAr : $startupActionEn }}</p>
                                                            @endif
                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;"><strong>{{ $isAr ? 'تفاصيل المهمة:' : 'Task details:' }}</strong></p>
                                                            <p style="margin: 0;">{{ $isAr ? 'العنوان:' : 'Title:' }} {{ $taskTitle }}.</p>
                                                            @if($programName)
                                                            <p style="margin: 0;">{{ $isAr ? 'البرنامج:' : 'Program:' }} {{ $programName }}.</p>
                                                            @endif
                                                            <p style="margin: 0;">{{ $isAr ? 'الحالة:' : 'Status:' }} {{ $statusLabel }}.</p>
                                                            @if($mentorName)
                                                            <p style="margin: 0;">{{ $isAr ? 'المينتور:' : 'Mentor:' }} {{ $mentorName }}.</p>
                                                            @endif
                                                            <p style="margin: 0;"> </p>
                                                            @php
                                                                $taskUrl = $data['task'] instanceof \App\MentorshipTask
                                                                    ? route('mentorships.sessions_list_detailed') . '?nav=menu'
                                                                    : route('tasks.show', $data['task']->id);
                                                            @endphp
                                                            <p style="margin: 0; text-align: center;">
                                                                <a href="{{ $taskUrl }}"
                                                                   style="display:inline-block;background-color:#4CAF50;color:#ffffff;font-family:Montserrat,Trebuchet MS,sans-serif;font-size:15px;font-weight:bold;text-decoration:none;padding:12px 30px;border-radius:6px;mso-padding-alt:0;mso-text-raise:10pt;">
                                                                    {{ $isAr ? 'عرض المهمة' : 'View Task' }}
                                                                </a>
                                                            </p>
                                                            <p style="margin: 0;"> </p>
                                                            <p style="margin: 0;">{{ $isAr ? 'مع أطيب التحيات،' : 'Best regards,' }}</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
@endsection
