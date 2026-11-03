@extends('emails.layout.master')

@section('content')
@php
    $isAr     = app()->getLocale() === 'ar';
    $isRtl    = $isAr;
    $isMentor = !$notifiable->hasRole('registered');

    $task            = $data['task'];
    $taskTitle       = $task->title ?? '';
    $taskDescription = $task->description ?? '';
    $taskPriority    = $task->priority ?? '';
    $taskStatus      = $task->status ?? '';
    $startups        = $data['startupNames'] ?? [];
    $mentorName      = $data['mentor']->name ?? '';
    $programName     = $task->program?->title ?? $task->mentorshipRequest?->cycle?->title ?? '';

    $decision = $data['decision'] ?? '';
    $isPassed = $decision === 'pass';

    $actionHeading = [
        'add'      => $isAr ? 'تمت إضافة مهمة جديدة'            : 'A New Task Has Been Added',
        'update'   => $isAr ? 'تم تحديث المهمة'                  : 'Task Has Been Updated',
        'archive'  => $isAr ? 'تمت أرشفة المهمة'                 : 'Task Has Been Archived',
        'reopen'   => $isAr ? 'تمت إعادة فتح المهمة'             : 'Task Has Been Reopened',
        'move'     => $isAr ? 'تم نقل المهمة'                    : 'A Task Has Been Moved',
        'comment'  => $isAr ? 'تم إضافة تعليق جديد على مهمتك'   : 'A New Comment Has Been Added to Your Task',
        'feedback' => $isAr
            ? ($isPassed ? 'تم اجتياز المهمة' : 'مطلوب تعديلات على المهمة')
            : ($isPassed ? 'Task Passed'       : 'Revisions Requested'),
    ][$action];

    $actionIcon = match($action) {
        'add'      => '✦',
        'update'   => '✎',
        'archive'  => '▣',
        'reopen'   => '↺',
        'move'     => '⇄',
        'comment'  => '✉',
        'feedback' => $isPassed ? '✔' : '⚑',
        default    => '●',
    };

    $priorityLabel = match(strtolower($taskPriority)) {
        'low'    => $isAr ? 'ضعيف'   : 'Low',
        'medium' => $isAr ? 'متوسط'  : 'Medium',
        'high'   => $isAr ? 'عالي'   : 'High',
        default  => $taskPriority,
    };

    $priorityColor = '#1aa19c';
    $priorityBg    = '#e6f7f6';

    $statusLabel = [
        'pending'   => $isAr ? 'قيد الانتظار' : 'Pending',
        'progress'  => $isAr ? 'قيد التنفيذ'  : 'In Progress',
        'review'    => $isAr ? 'قيد المراجعة' : 'In Review',
        'completed' => $isAr ? 'مكتملة'        : 'Completed',
        'archived'  => $isAr ? 'مؤرشفة'        : 'Archived',
    ][strtolower($taskStatus)] ?? $taskStatus;

    $statusColor = '#1aa19c';
    $statusBg    = '#e6f7f6';

    $actionSentence = $isMentor
        ? match($action) {
            'add'     => $isAr ? 'تمت إضافة مهمة جديدة إلى الشركات الناشئة التالية:'    : 'A new task has been added for the following startups:',
            'update'  => $isAr ? 'تم تحديث مهمة للشركات الناشئة التالية:'                : 'A task has been updated for the following startups:',
            'archive' => $isAr ? 'تمت أرشفة مهمة للشركات الناشئة التالية:'               : 'A task has been archived for the following startups:',
            'reopen'  => $isAr ? 'أصبحت المهمة نشطة مرة أخرى للشركات الناشئة التالية:' : 'A task has been reopened for the following startups:',
            default   => $isAr ? 'تحديث على مهمة للشركات الناشئة التالية:'               : 'An update on a task for the following startups:',
        }
        : match($action) {
            'add'     => $isAr ? "تم إسناد مهمة جديدة إليك على منصة {$platformName}." : "You have been assigned a new task on {$platformName}.",
            'update'  => $isAr ? 'تم تحديث مهمة مسندة إليك.'         : 'A task assigned to you has been updated.',
            'archive' => $isAr ? 'تمت أرشفة مهمة مسندة إليك.'        : 'A task assigned to you has been archived.',
            'reopen'  => $isAr ? 'أصبحت المهمة نشطة مرة أخرى.'       : 'A task assigned to you has been reopened.',
            default   => $isAr ? 'تحديث على مهمة مسندة إليك.'         : 'An update on a task assigned to you.',
        };

    $dueDate = $task->due_date
        ? \Carbon\Carbon::parse($task->due_date)->translatedFormat('F j, Y')
        : ($isAr ? 'غير محدد' : 'Not specified');

    $reviewStatusLabel = $isAr
        ? ($isPassed ? 'تم الاجتياز'   : 'يحتاج إلى تعديل')
        : ($isPassed ? 'Passed'         : 'Needs Revision');

    $closingNote = $isAr
        ? ($isPassed ? 'تم اعتماد التسليم الخاص بك.' : 'يرجى مراجعة الملاحظات وتحديث التسليم.')
        : ($isPassed ? 'Your submission has been approved.' : 'Please review the feedback and update your submission.');

    $setting      = \App\Settings::find(1);
    $platformName = $setting?->Platform_Name ?? config('app.name', 'Growth Labs');

    $loginPrompt = $isAr
        ? 'يرجى تسجيل الدخول إلى حسابك للاطلاع على المهمة واتخاذ الإجراء المطلوب.'
        : 'Please log in to your account to review the task and take the needed action.';

    $dir      = $isRtl ? 'rtl' : 'ltr';
    $align    = $isRtl ? 'right' : 'left';
    $padStart = $isRtl ? 'padding-right' : 'padding-left';
    $padEnd   = $isRtl ? 'padding-left'  : 'padding-right';
    $font     = 'Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif';
@endphp

{{-- ── Heading Banner ── --}}
<table border="0" cellpadding="0" cellspacing="0" role="presentation"
       style="mso-table-lspace:0pt;mso-table-rspace:0pt;background-color:#1aa19c;border-radius:0;" width="100%">
    <tr>
        <td align="center" style="padding:36px 40px 32px;">
            <p style="margin:0;font-family:{{ $font }};font-size:13px;font-weight:600;letter-spacing:2px;color:rgba(255,255,255,0.75);text-transform:uppercase;">
                {{ $actionIcon }}
            </p>
            <p style="margin:8px 0 0;font-family:{{ $font }};font-size:26px;font-weight:700;line-height:1.3;color:#ffffff;text-align:center;">
                {{ $actionHeading }}
            </p>
        </td>
    </tr>
</table>

{{-- ── Greeting ── --}}
<table border="0" cellpadding="0" cellspacing="0" role="presentation"
       style="mso-table-lspace:0pt;mso-table-rspace:0pt;" width="100%">
    <tr>
        <td style="padding:32px 40px 0;" dir="{{ $dir }}">
            <p style="margin:0;font-family:{{ $font }};font-size:16px;color:#1a1a2e;text-align:{{ $align }};">
                {{ $isAr ? 'مرحباً' : 'Hello' }} <strong>{{ $notifiable->name }}</strong>,
            </p>
            <p style="margin:12px 0 0;font-family:{{ $font }};font-size:15px;color:#555555;line-height:1.6;text-align:{{ $align }};">
                {{ $actionSentence }}
            </p>

            @if($isMentor && count($startups))
            <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                   style="mso-table-lspace:0pt;mso-table-rspace:0pt;margin-top:12px;background:#e6f7f6;border-radius:8px;border:1px solid #1aa19c;" width="100%">
                <tr>
                    <td style="padding:14px 20px;" dir="{{ $dir }}">
                        @foreach($startups as $startup)
                        <p style="margin:{{ $loop->first ? '0' : '6px' }} 0 0;font-family:{{ $font }};font-size:14px;color:#148a86;{{ $padStart }}:12px;">
                            ● &nbsp;{{ $startup }}
                        </p>
                        @endforeach
                    </td>
                </tr>
            </table>
            @endif
        </td>
    </tr>
</table>

{{-- ── Task Details Card ── --}}
<table border="0" cellpadding="0" cellspacing="0" role="presentation"
       style="mso-table-lspace:0pt;mso-table-rspace:0pt;" width="100%">
    <tr>
        <td style="padding:24px 40px 0;">

            {{-- Card --}}
            <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                   style="mso-table-lspace:0pt;mso-table-rspace:0pt;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;" width="100%">

                {{-- Card header --}}
                <tr>
                    <td style="padding:16px 20px 14px;border-bottom:1px solid #e2e8f0;" dir="{{ $dir }}">
                        <p style="margin:0;font-family:{{ $font }};font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#94a3b8;text-align:{{ $align }};">
                            {{ $isAr ? 'تفاصيل المهمة' : 'Task Details' }}
                        </p>
                    </td>
                </tr>

                {{-- Title row --}}
                <tr>
                    <td style="padding:16px 20px 0;" dir="{{ $dir }}">
                        <p style="margin:0;font-family:{{ $font }};font-size:11px;font-weight:600;letter-spacing:0.5px;color:#94a3b8;text-transform:uppercase;text-align:{{ $align }};">
                            {{ $isAr ? 'العنوان' : 'Title' }}
                        </p>
                        <p style="margin:4px 0 0;font-family:{{ $font }};font-size:16px;font-weight:700;color:#1a1a2e;text-align:{{ $align }};">
                            {{ $taskTitle }}
                        </p>
                    </td>
                </tr>

                {{-- Description row --}}
                @if($taskDescription)
                <tr>
                    <td style="padding:14px 20px 0;" dir="{{ $dir }}">
                        <p style="margin:0;font-family:{{ $font }};font-size:11px;font-weight:600;letter-spacing:0.5px;color:#94a3b8;text-transform:uppercase;text-align:{{ $align }};">
                            {{ $isAr ? 'الوصف' : 'Description' }}
                        </p>
                        <p style="margin:4px 0 0;font-family:{{ $font }};font-size:14px;color:#475569;line-height:1.6;text-align:{{ $align }};">
                            {{ $taskDescription }}
                        </p>
                    </td>
                </tr>
                @endif

                {{-- Program row --}}
                @if($programName)
                <tr>
                    <td style="padding:14px 20px 0;" dir="{{ $dir }}">
                        <p style="margin:0;font-family:{{ $font }};font-size:11px;font-weight:600;letter-spacing:0.5px;color:#94a3b8;text-transform:uppercase;text-align:{{ $align }};">
                            {{ $isAr ? 'البرنامج' : 'Program' }}
                        </p>
                        <p style="margin:4px 0 0;font-family:{{ $font }};font-size:14px;color:#1a1a2e;text-align:{{ $align }};">
                            {{ $programName }}
                        </p>
                    </td>
                </tr>
                @endif

                {{-- Due date row --}}
                <tr>
                    <td style="padding:14px 20px 0;" dir="{{ $dir }}">
                        <p style="margin:0;font-family:{{ $font }};font-size:11px;font-weight:600;letter-spacing:0.5px;color:#94a3b8;text-transform:uppercase;text-align:{{ $align }};">
                            {{ $isAr ? 'تاريخ التسليم' : 'Due Date' }}
                        </p>
                        <p style="margin:4px 0 0;font-family:{{ $font }};font-size:14px;color:#1a1a2e;text-align:{{ $align }};">
                            {{ $dueDate }}
                        </p>
                    </td>
                </tr>

                {{-- Status + Priority badges --}}
                <tr>
                    <td style="padding:16px 20px 20px;" dir="{{ $dir }}">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                               style="mso-table-lspace:0pt;mso-table-rspace:0pt;">
                            <tr>
                                {{-- Status badge --}}
                                <td style="padding-{{ $isRtl ? 'left' : 'right' }}:8px;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                           style="mso-table-lspace:0pt;mso-table-rspace:0pt;background:{{ $statusBg }};border-radius:20px;border:1px solid {{ $statusColor }}30;">
                                        <tr>
                                            <td style="padding:4px 14px;">
                                                <p style="margin:0;font-family:{{ $font }};font-size:12px;font-weight:700;color:{{ $statusColor }};white-space:nowrap;">
                                                    {{ $statusLabel }}
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                {{-- Priority badge --}}
                                <td>
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                           style="mso-table-lspace:0pt;mso-table-rspace:0pt;background:{{ $priorityBg }};border-radius:20px;border:1px solid {{ $priorityColor }}30;">
                                        <tr>
                                            <td style="padding:4px 14px;">
                                                <p style="margin:0;font-family:{{ $font }};font-size:12px;font-weight:700;color:{{ $priorityColor }};white-space:nowrap;">
                                                    {{ $isAr ? 'أولوية:' : 'Priority:' }} {{ $priorityLabel }}
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

{{-- ── Feedback Section ── --}}
@if($action === 'feedback')
<table border="0" cellpadding="0" cellspacing="0" role="presentation"
       style="mso-table-lspace:0pt;mso-table-rspace:0pt;" width="100%">
    <tr>
        <td style="padding:20px 40px 0;">
            <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                   style="mso-table-lspace:0pt;mso-table-rspace:0pt;background:#e6f7f6;border-radius:10px;border-{{ $isRtl ? 'right' : 'left' }}:4px solid #1aa19c;" width="100%">
                <tr>
                    <td style="padding:16px 20px;" dir="{{ $dir }}">
                        <p style="margin:0;font-family:{{ $font }};font-size:13px;font-weight:700;color:#148a86;text-align:{{ $align }};">
                            {{ $isAr ? 'حالة المراجعة:' : 'Review Status:' }}
                            <span style="font-weight:400;">{{ $reviewStatusLabel }}</span>
                        </p>
                        @if(!empty($data['feedbackNote']))
                        <p style="margin:8px 0 0;font-family:{{ $font }};font-size:14px;color:#148a86;line-height:1.6;text-align:{{ $align }};">
                            {{ $isAr ? 'الملاحظات:' : 'Feedback:' }} {{ $data['feedbackNote'] }}
                        </p>
                        @endif
                        <p style="margin:8px 0 0;font-family:{{ $font }};font-size:13px;font-style:italic;color:#1aa19c;text-align:{{ $align }};">
                            {{ $closingNote }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif

{{-- ── CTA Button ── --}}
<table border="0" cellpadding="0" cellspacing="0" role="presentation"
       style="mso-table-lspace:0pt;mso-table-rspace:0pt;" width="100%">
    <tr>
        <td align="center" style="padding:32px 40px 8px;">
            <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                   style="mso-table-lspace:0pt;mso-table-rspace:0pt;">
                <tr>
                    <td align="center" style="background-color:#1aa19c;border-radius:8px;mso-padding-alt:14px 36px;">
                        <a href="{{ $taskUrl }}"
                           style="display:inline-block;font-family:{{ $font }};font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;padding:14px 36px;mso-text-raise:10pt;letter-spacing:0.3px;">
                            {{ $isAr ? 'عرض المهمة' : 'View Task' }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ── Sign-off ── --}}
<table border="0" cellpadding="0" cellspacing="0" role="presentation"
       style="mso-table-lspace:0pt;mso-table-rspace:0pt;" width="100%">
    <tr>
        <td style="padding:24px 40px 36px;" dir="{{ $dir }}">
            @if($action !== 'feedback')
            <p style="margin:0 0 16px;font-family:{{ $font }};font-size:14px;color:#555555;line-height:1.6;text-align:{{ $align }};">
                {{ $loginPrompt }}
            </p>
            @endif
            <p style="margin:0;font-family:{{ $font }};font-size:14px;color:#555555;text-align:{{ $align }};">
                {{ $isAr ? 'مع أطيب التحيات،' : 'Best regards,' }}
            </p>
        </td>
    </tr>
</table>

@endsection
