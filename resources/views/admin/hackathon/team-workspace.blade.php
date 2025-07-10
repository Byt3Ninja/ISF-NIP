<!DOCTYPE html>
<html lang="{{ $l }}" dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}" data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
    class="light-style layout-navbar-fixed layout-compact" data-theme="theme-default" data-assets-path="/vuexy/assets/"
    data-template="vertical-menu-template" data-style="light">

<head>
    @include('admin.includes.head')
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Additional Styles -->
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
    <style>
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .team-workspace-container {
                padding: 1rem !important;
            }
            
            #chatForm .btn {
                flex-shrink: 0;
                min-width: auto;
            }
            
            #chatForm input {
                font-size: 16px; /* Prevent iOS zoom */
            }
            
            .nav-pills .nav-link {
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
            }
        }
        
        @media (max-width: 575.98px) {
            .card.card-body {
                padding: 1rem;
            }
            
            .btn-group-sm > .btn, .btn-sm {
                font-size: 0.75rem;
            }
        }
    </style>
</head>

@php
$teamData = [
'name' => 'Team Champions',
'name_ar' => 'فريق الأبطال',
'hackathon' => 'Future of AI Hackathon',
'hackathon_ar' => 'هاكثون مستقبل الذكاء الاصطناعي',
'members' => [
['name' => 'Ahmed', 'name_ar' => 'أحمد'],
['name' => 'Sara', 'name_ar' => 'سارة'],
['name' => 'You', 'name_ar' => 'أنت'],
]
];
@endphp

<body>
    <!-- Include original navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/dashboard/hackathon">
                <i class="ti ti-tournament text-primary me-2"></i> {{ $l == 'en' ? 'Hackathon Platform' : 'منصة
                الهاكثونات' }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard/hackathon">
                            <i class="ti ti-home"></i>
                            {{ $l == 'en' ? 'Home' : 'الرئيسية' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard/hackathon/manage" id="back-to-team-management">
                            <i class="ti ti-arrow-left"></i> 
                            {{ $l == 'en' ? 'Back to Team Management' : 'العودة لإدارة الفريق' }}
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-user-circle"></i> {{ $l == 'en' ? 'Hello, Entrepreneur' : 'مرحباً، رائد
                            أعمال' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                            <li><a class="dropdown-item" href="#"><i class="ti ti-user"></i> {{ $l == 'en' ? 'My
                                    Profile' : 'ملفي
                                    الشخصي' }}</a></li>
                            <li><a class="dropdown-item"
                                    href="/dashboard/hackathon/my-team"><i
                                        class="ti ti-users"></i> {{ $l == 'en' ? 'My Team' : 'فريقي'
                                    }}</a></li>
                            <li><a class="dropdown-item" href="/dashboard"><i class="ti ti-dashboard"></i> {{ $l == 'en'
                                    ? 'Admin
                                    Dashboard' : 'لوحة تحكم الإدارة' }}</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="#"><i class="ti ti-logout"></i> {{ $l == 'en' ? 'Logout'
                                    : 'تسجيل
                                    الخروج' }}</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-fluid mb-5 mb-md-12">

        <div class="container-fluid mt-3 mt-md-4 px-3 px-md-4 team-workspace-container">
            <!-- Simple Header -->
            <div class="mb-4">
                <h3 class="mb-1">
                    {{ $l == 'en' ? 'Team Workspace:' : 'مساحة عمل فريق:' }}
                    <span id="team-name-collab" class="text-primary">{{ $l == 'en' ? $teamData['name'] :
                        $teamData['name_ar'] }}</span>
                </h3>
                <p class="text-muted mb-0">
                    {{ $l == 'en' ? 'Hackathon:' : 'هاكثون:' }}
                    <span id="hackathon-name-collab">{{ $l == 'en' ? $teamData['hackathon'] : $teamData['hackathon_ar']
                        }}</span>
                </p>
            </div>

            <div class="row g-4">
                <!-- Chat Section -->
                <div class="col-12 col-lg-5">
                    <div class="card card-body h-100">
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="ti ti-message-dots me-2"></i> {{ $l == 'en' ? 'Team Chat' : 'محادثة
                            الفريق' }}</h5>

                        <div class="border rounded p-3 mb-3"
                            style="height: 400px; overflow-y: auto; background-color: #f8f9fa;" id="chatWindow" 
                            data-mobile-height="300px">
                            <div class="mb-3 p-2 border-bottom">
                                <div class="fw-bold small">{{ $l == 'en' ? 'Ahmed:' : 'أحمد:' }}</div>
                                <div class="mb-1">{{ $l == 'en' ? 'Hello everyone, how are you?' : 'السلام عليكم، كيف
                                    حالكم؟' }}</div>
                                <div class="text-muted small">{{ $l == 'en' ? 'Yesterday, 10:30 AM' : 'أمس, 10:30
                                    صباحًا' }}</div>
                            </div>
                            <div class="mb-3 p-2 border-bottom">
                                <div class="fw-bold small">{{ $l == 'en' ? 'Sara:' : 'سارة:' }}</div>
                                <div class="mb-1">{{ $l == 'en' ? 'Hello! Fine, thank God. Ready to work!' : 'وعليكم
                                    السلام، بخير الحمد لله. مستعدون للعمل!' }}</div>
                                <div class="text-muted small">{{ $l == 'en' ? 'Yesterday, 10:32 AM' : 'أمس, 10:32
                                    صباحًا' }}</div>
                            </div>
                            <div class="mb-3 p-2 border-bottom">
                                <div class="fw-bold small">{{ $l == 'en' ? 'You:' : 'أنت:' }}</div>
                                <div class="mb-1">{{ $l == 'en' ? 'Excellent! Let\'s review today\'s work plan.' :
                                    'ممتاز! دعونا نراجع خطة العمل اليوم.' }}</div>
                                <div class="text-muted small">{{ $l == 'en' ? 'Today, 09:00 AM' : 'اليوم, 09:00 صباحًا'
                                    }}</div>
                            </div>
                            <!-- Messages will be appended here by JS -->
                        </div>

                        <form id="chatForm" class="d-flex gap-2">
                            <input type="text" class="form-control" id="chatMessageInput"
                                placeholder="{{ $l == 'en' ? 'Type your message...' : 'اكتب رسالتك...' }}" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-send me-2"></i> {{ $l == 'en' ? 'Send' : 'إرسال' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

                <!-- Files & Tasks Section -->
                <div class="col-12 col-lg-7">
                    <div class="card card-body h-100">
                    <ul class="nav nav-pills mb-4" id="collabTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="files-tab" data-bs-toggle="tab" data-bs-target="#files"
                                type="button" role="tab" aria-controls="files" aria-selected="true">
                                <i class="ti ti-folder me-1"></i> {{ $l == 'en' ? 'Shared Files' : 'الملفات المشتركة' }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks"
                                type="button" role="tab" aria-controls="tasks" aria-selected="false">
                                <i class="ti ti-list-check me-1"></i> {{ $l == 'en' ? 'Task List' : 'قائمة المهام' }}
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content p-0 mt-4 border rounded shadow overflow-hidden" id="collabTabsContent">
                        <!-- Files Tab -->
                        <div class="tab-pane fade show active p-6 border-top-0 rounded-bottom bg-white"
                            id="files" role="tabpanel" aria-labelledby="files-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>{{ $l == 'en' ? 'Uploaded Files:' : 'الملفات المرفوعة:' }}</h6>
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#uploadFileModal">
                                    <i class="ti ti-cloud-upload me-2"></i> {{ $l == 'en' ? 'Upload New File' : 'رفع ملف
                                    جديد' }}
                                </button>
                            </div>
                            <ul class="list-group list-group-flush" id="fileList">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="ti ti-file-type-ppt text-danger me-2"></i>
                                        {{ $l == 'en' ? 'Initial Presentation.pptx' : 'عرض تقديمي أولي.pptx' }}
                                        <small class="text-muted d-block">
                                            (2.1MB - {{ $l == 'en' ? 'Uploaded by: Sara' : 'رفع بواسطة: سارة' }})
                                        </small>
                                    </div>
                                    <div>
                                        <a href="#" class="btn btn-sm btn-outline-primary me-1"
                                            onclick="downloadFile('{{ $l == 'en' ? 'Initial Presentation.pptx' : 'عرض تقديمي أولي.pptx' }}', 'Initial_Presentation.pptx')"
                                            title="{{ $l == 'en' ? 'Download' : 'تنزيل' }}">
                                            <i class="ti ti-download"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="deleteFile(this, '{{ $l == 'en' ? 'Initial Presentation.pptx' : 'عرض تقديمي أولي.pptx' }}')"
                                            title="{{ $l == 'en' ? 'Delete' : 'حذف' }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="ti ti-file-zip text-warning me-2"></i>
                                        {{ $l == 'en' ? 'Design Resources.zip' : 'موارد التصميم.zip' }}
                                        <small class="text-muted d-block">
                                            (5.7MB - {{ $l == 'en' ? 'Uploaded by: Ahmed' : 'رفع بواسطة: أحمد' }})
                                        </small>
                                    </div>                                 <div>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1"
                                    onclick="downloadFile('{{ $l == 'en' ? 'Design Resources.zip' : 'موارد التصميم.zip' }}', 'Design_Resources.zip')"
                                    title="{{ $l == 'en' ? 'Download' : 'تنزيل' }}">
                                    <i class="ti ti-download"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="deleteFile(this, '{{ $l == 'en' ? 'Design Resources.zip' : 'موارد التصميم.zip' }}')"
                                    title="{{ $l == 'en' ? 'Delete' : 'حذف' }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="ti ti-file-text text-info me-2"></i>
                                {{ $l == 'en' ? 'Meeting Notes.docx' : 'ملاحظات الاجتماع.docx' }}
                                <small class="text-muted d-block">
                                    (150KB - {{ $l == 'en' ? 'Uploaded by: You' : 'رفع بواسطة: أنت' }})
                                </small>
                            </div>
                            <div>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1"
                                    onclick="downloadFile('{{ $l == 'en' ? 'Meeting Notes.docx' : 'ملاحظات الاجتماع.docx' }}', 'Meeting_Notes.docx')"
                                    title="{{ $l == 'en' ? 'Download' : 'تنزيل' }}">
                                    <i class="ti ti-download"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="deleteFile(this, '{{ $l == 'en' ? 'Meeting Notes.docx' : 'ملاحظات الاجتماع.docx' }}')"
                                    title="{{ $l == 'en' ? 'Delete' : 'حذف' }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <!-- Tasks Tab -->
                        <div class="tab-pane fade p-3 border-top-0 rounded-bottom bg-white" id="tasks"
                            role="tabpanel" aria-labelledby="tasks-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>{{ $l == 'en' ? 'Current Tasks:' : 'المهام الحالية:' }}</h6>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#addTaskModal">
                                    <i class="ti ti-plus me-1"></i> {{ $l == 'en' ? 'Add New Task' : 'إضافة مهمة جديدة'
                                    }}
                                </button>
                            </div>
                            <ul class="list-group list-group-flush" id="taskList">
                                <li class="list-group-item">
                                    <div class="form-check d-flex justify-content-between align-items-center">
                                        <div>
                                            <input class="form-check-input me-2" type="checkbox" value="" id="task1"
                                                checked>
                                            <label class="form-check-label" for="task1"
                                                style="text-decoration: line-through;">
                                                {{ $l == 'en' ? 'Design main interface for prototype' : 'تصميم الواجهة
                                                الرئيسية للنموذج الأولي' }}
                                            </label>
                                            <small class="d-block text-muted">
                                                {{ $l == 'en' ? 'Responsible: Ahmed - Due: March 15' : 'المسؤول: أحمد -
                                                تاريخ الاستحقاق: 15 مارس' }}
                                            </small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-secondary me-1" 
                                                onclick="editTask('task1', '{{ $l == 'en' ? 'Design main interface for prototype' : 'تصميم الواجهة الرئيسية للنموذج الأولي' }}', '{{ $l == 'en' ? 'Ahmed' : 'أحمد' }}', '2024-03-15')"
                                                title="{{ $l == 'en' ? 'Edit Task' : 'تعديل المهمة' }}">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteTask('task1', '{{ $l == 'en' ? 'Design main interface for prototype' : 'تصميم الواجهة الرئيسية للنموذج الأولي' }}')"
                                                title="{{ $l == 'en' ? 'Delete Task' : 'حذف المهمة' }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="form-check d-flex justify-content-between align-items-center">
                                        <div>
                                            <input class="form-check-input me-2" type="checkbox" value="" id="task2">
                                            <label class="form-check-label" for="task2">
                                                {{ $l == 'en' ? 'Develop basic analysis algorithm' : 'تطوير خوارزمية
                                                التحليل الأساسية' }}
                                            </label>
                                            <small class="d-block text-muted">
                                                {{ $l == 'en' ? 'Responsible: Sara - Due: March 16' : 'المسؤول: سارة -
                                                تاريخ الاستحقاق: 16 مارس' }}
                                            </small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-secondary me-1" 
                                                onclick="editTask('task2', '{{ $l == 'en' ? 'Develop basic analysis algorithm' : 'تطوير خوارزمية التحليل الأساسية' }}', '{{ $l == 'en' ? 'Sara' : 'سارة' }}', '2024-03-16')"
                                                title="{{ $l == 'en' ? 'Edit Task' : 'تعديل المهمة' }}">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteTask('task2', '{{ $l == 'en' ? 'Develop basic analysis algorithm' : 'تطوير خوارزمية التحليل الأساسية' }}')"
                                                title="{{ $l == 'en' ? 'Delete Task' : 'حذف المهمة' }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="form-check d-flex justify-content-between align-items-center">
                                        <div>
                                            <input class="form-check-input me-2" type="checkbox" value="" id="task3">
                                            <label class="form-check-label" for="task3">
                                                {{ $l == 'en' ? 'Prepare final presentation' : 'إعداد العرض التقديمي
                                                النهائي' }}
                                            </label>
                                            <small class="d-block text-muted">
                                                {{ $l == 'en' ? 'Responsible: You - Due: March 17' : 'المسؤول: أنت -
                                                تاريخ الاستحقاق: 17 مارس' }}
                                            </small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-secondary me-1" 
                                                onclick="editTask('task3', '{{ $l == 'en' ? 'Prepare final presentation' : 'إعداد العرض التقديمي النهائي' }}', '{{ $l == 'en' ? 'You' : 'أنت' }}', '2024-03-17')"
                                                title="{{ $l == 'en' ? 'Edit Task' : 'تعديل المهمة' }}">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteTask('task3', '{{ $l == 'en' ? 'Prepare final presentation' : 'إعداد العرض التقديمي النهائي' }}')"
                                                title="{{ $l == 'en' ? 'Delete Task' : 'حذف المهمة' }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <!-- Upload File Modal -->
    <div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadFileModalLabel">{{ $l == 'en' ? 'Upload New File' : 'رفع ملف جديد'
                        }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="fileUploadForm">
                        <div class="mb-3">
                            <label for="fileInput" class="form-label">{{ $l == 'en' ? 'Choose File' : 'اختر ملف'
                                }}</label>
                            <input class="form-control" type="file" id="fileInput" required>
                        </div>
                        <div class="mb-3">
                            <label for="fileDescription" class="form-label">{{ $l == 'en' ? 'File Description
                                (Optional)' : 'وصف الملف (اختياري)' }}</label>
                            <input type="text" class="form-control" id="fileDescription"
                                placeholder="{{ $l == 'en' ? 'e.g., Initial logo design' : 'مثال: تصميم الشعار الأولي' }}">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $l == 'en' ? 'Cancel' :
                        'إلغاء' }}</button>
                    <button type="button" class="btn btn-primary" onclick="uploadFile()">{{ $l == 'en' ? 'Upload' :
                        'رفع' }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTaskModalLabel">{{ $l == 'en' ? 'Add New Task' : 'إضافة مهمة جديدة'
                        }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="taskForm">
                        <div class="mb-3">
                            <label for="taskTitle" class="form-label">{{ $l == 'en' ? 'Task Title' : 'عنوان المهمة'
                                }}</label>
                            <input type="text" class="form-control" id="taskTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="taskAssignee" class="form-label">{{ $l == 'en' ? 'Assignee' : 'المسؤول'
                                }}</label>
                            <select class="form-select" id="taskAssignee">
                                <option selected value="{{ $l == 'en' ? 'You' : 'أنت' }}">{{ $l == 'en' ? 'You' : 'أنت'
                                    }}</option>
                                @foreach($teamData['members'] as $member)
                                @if(($l == 'en' ? $member['name'] : $member['name_ar']) !== ($l == 'en' ? 'You' :
                                'أنت'))
                                <option value="{{ $l == 'en' ? $member['name'] : $member['name_ar'] }}">{{ $l == 'en' ?
                                    $member['name'] : $member['name_ar'] }}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="taskDueDate" class="form-label">{{ $l == 'en' ? 'Due Date' : 'تاريخ الاستحقاق'
                                }}</label>
                            <input type="date" class="form-control" id="taskDueDate">
                        </div>
                        <div class="mb-3">
                            <label for="taskDescription" class="form-label">{{ $l == 'en' ? 'Task Description
                                (Optional)' : 'وصف المهمة (اختياري)' }}</label>
                            <textarea class="form-control" id="taskDescription" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $l == 'en' ? 'Cancel' :
                        'إلغاء' }}</button>
                    <button type="button" class="btn btn-primary" onclick="addTask()">{{ $l == 'en' ? 'Add Task' :
                        'إضافة المهمة' }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include original footer -->
    <footer class="footer mt-auto py-3 bg-dark text-white">
        <div class="container text-center">
            <span>
                © 2024 {{ $l == 'en' ? 'Incubator Hackathon Platform. All rights reserved.' : 'منصة المسرعة للهاكثونات.
                جميع الحقوق محفوظة.' }}
            </span>
        </div>
    </footer>
    <!-- Core JS -->
    <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
    <script src="/vuexy/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/vuexy/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="/vuexy/assets/vendor/libs/i18n/i18n.js"></script>
    <script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="/vuexy/assets/vendor/js/menu.js"></script>
    <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script src="/vuexy/assets/js/main.js"></script>
    <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
    <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>

    <!-- Custom JS -->
    <script src="/vuexy/assets/js/forms-selects.js"></script>

    <script>
        // Responsive adjustments
        function adjustForMobile() {
            const chatWindow = document.getElementById('chatWindow');
            if (window.innerWidth < 992) { // lg breakpoint
                chatWindow.style.height = '300px';
            } else {
                chatWindow.style.height = '400px';
            }
        }

        // Call on load and resize
        window.addEventListener('load', adjustForMobile);
        window.addEventListener('resize', adjustForMobile);

        document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const hackathonId = urlParams.get('hackathonId') || '1';
        const teamId = urlParams.get('teamId') || '123';
        const teamName = "{{ $l == 'en' ? $teamData['name'] : $teamData['name_ar'] }}";
        const hackathonName = "{{ $l == 'en' ? $teamData['hackathon'] : $teamData['hackathon_ar'] }}";

        document.getElementById('team-name-collab').textContent = teamName;
        document.getElementById('hackathon-name-collab').textContent = hackathonName;
        // document.getElementById('back-to-team-management').href = `/dashboard/hackathon/my-team?hackathonId=${hackathonId}&teamCreated=true`;

        const chatForm = document.getElementById('chatForm');
        const chatMessageInput = document.getElementById('chatMessageInput');
        const chatWindow = document.getElementById('chatWindow');

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const messageText = chatMessageInput.value.trim();
            if (messageText) {
                addMessageToChat('{{ $l == "en" ? "You" : "أنت" }}', messageText);
                chatMessageInput.value = '';
                // In a real app, send message to server here
            }
        });

        function addMessageToChat(sender, text) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('mb-3', 'p-2', 'border-bottom');
            
            const senderDiv = document.createElement('div');
            senderDiv.classList.add('fw-bold', 'small');
            senderDiv.textContent = sender + ':';
            
            const textDiv = document.createElement('div');
            textDiv.classList.add('mb-1');
            textDiv.textContent = text;
            
            const timestampDiv = document.createElement('div');
            timestampDiv.classList.add('text-muted', 'small');
            timestampDiv.textContent = new Date().toLocaleTimeString('{{ $l == "en" ? "en-US" : "ar-SA" }}', { hour: '2-digit', minute: '2-digit' });

            messageDiv.appendChild(senderDiv);
            messageDiv.appendChild(textDiv);
            messageDiv.appendChild(timestampDiv);
            
            chatWindow.appendChild(messageDiv);
            chatWindow.scrollTop = chatWindow.scrollHeight; // Scroll to bottom
        }

        // Simulate receiving a message
        setTimeout(() => {
            addMessageToChat('{{ $l == "en" ? "Ahmed (simulation)" : "أحمد (محاكاة)" }}', '{{ $l == "en" ? "Good idea, I am working on the interface now." : "فكرة جيدة، أنا أعمل على الواجهة الآن." }}');
        }, 5000);
    });

    // Test function availability
    window.addEventListener('load', function() {
        console.log('Page loaded. Testing function availability:');
        console.log('uploadFile:', typeof uploadFile);
        console.log('deleteFile:', typeof deleteFile);
        console.log('deleteTask:', typeof deleteTask);
        console.log('editTask:', typeof editTask);
        console.log('addTask:', typeof addTask);
        console.log('Swal:', typeof Swal);
    });

    function downloadFile(fileName, downloadName) {
        console.log('downloadFile called with:', fileName, downloadName);
        
        // In a real application, you would have a server endpoint for downloading files
        // For now, we'll simulate the download or use a placeholder
        
        // Get URL parameters to build the download path
        const urlParams = new URLSearchParams(window.location.search);
        const hackathonId = urlParams.get('hackathonId') || '1';
        const teamId = urlParams.get('teamId') || '123';
        
        // Build the download URL - this should point to your Laravel route
        const downloadUrl = `/dashboard/hackathon/team-workspace/download/${hackathonId}/${teamId}/${encodeURIComponent(fileName)}`;
        
        // Try to download the file
        fetch(downloadUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            } else if (response.status === 404) {
                throw new Error('File not found');
            } else {
                throw new Error('Download failed');
            }
        })
        .then(blob => {
            // Create a temporary download link
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = downloadName || fileName;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '{{ $l == "en" ? "Download Started" : "بدأ التنزيل" }}',
                    text: `{{ $l == "en" ? "Downloading: " : "جاري تنزيل: " }}"${fileName}"`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        })
        .catch(error => {
            console.error('Download error:', error);
            
            // For demo purposes, create a sample file download
            if (error.message === 'File not found' || error.message === 'Download failed') {
                // Create a sample file for demonstration
                const sampleContent = `{{ $l == "en" ? "This is a sample file for: " : "هذا ملف تجريبي لـ: " }}"${fileName}"\n{{ $l == "en" ? "Created on: " : "تم إنشاؤه في: " }}${new Date().toLocaleString()}\n\n{{ $l == "en" ? "In a real application, this would be the actual file content." : "في التطبيق الحقيقي، سيكون هذا محتوى الملف الفعلي." }}`;
                const blob = new Blob([sampleContent], { type: 'text/plain' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = downloadName || fileName.replace(/[^a-zA-Z0-9.-]/g, '_');
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: '{{ $l == "en" ? "Demo Download" : "تنزيل تجريبي" }}',
                        text: `{{ $l == "en" ? "Downloaded sample file: " : "تم تنزيل ملف تجريبي: " }}"${fileName}"`,
                        confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}'
                    });
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ $l == "en" ? "Download Failed" : "فشل التنزيل" }}',
                        text: '{{ $l == "en" ? "Could not download the file. Please try again." : "لا يمكن تنزيل الملف. يرجى المحاولة مرة أخرى." }}',
                        confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}'
                    });
                }
            }
        });
    }

    function uploadFile() {
        const fileInput = document.getElementById('fileInput');
        const fileDescription = document.getElementById('fileDescription').value;
        
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            
            // Create FormData object for file upload
            const formData = new FormData();
            formData.append('file', file);
            formData.append('description', fileDescription);
            
            // Get URL parameters for context
            const urlParams = new URLSearchParams(window.location.search);
            const hackathonId = urlParams.get('hackathonId') || '1';
            const teamId = urlParams.get('teamId') || '123';
            formData.append('hackathon_id', hackathonId);
            formData.append('team_id', teamId);
            
            // Show loading indicator
            Swal.fire({
                title: '{{ $l == "en" ? "Uploading..." : "جاري الرفع..." }}',
                text: '{{ $l == "en" ? "Please wait while we upload your file." : "يرجى الانتظار أثناء رفع الملف." }}',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Upload file to server
            fetch('/dashboard/hackathon/team-workspace/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Use SweetAlert2 for success message
                    Swal.fire({
                        icon: 'success',
                        title: '{{ $l == "en" ? "File Uploaded!" : "تم رفع الملف!" }}',
                        text: `{{ $l == "en" ? "File uploaded: " : "تم رفع الملف: " }}${file.name} (${(file.size / 1024).toFixed(1)}KB)`,
                        confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}'
                    });

                    // Add to file list UI
                    const fileList = document.getElementById('fileList');
                    const newItem = document.createElement('li');
                    newItem.className = 'list-group-item d-flex justify-content-between align-items-center file-list-item';
                    
                    // Simple icon logic based on extension
                    let iconClass = 'ti-file-text';
                    if (file.name.endsWith('.zip')) iconClass = 'ti-file-zip text-warning';
                    else if (file.name.endsWith('.pptx')) iconClass = 'ti-file-type-ppt text-danger';
                    else if (file.name.endsWith('.docx')) iconClass = 'ti-file-text text-info';
                    else if (file.name.endsWith('.pdf')) iconClass = 'ti-file-type-pdf text-danger';
                    else if (file.name.endsWith('.jpg') || file.name.endsWith('.png')) iconClass = 'ti-photo text-success';

                    newItem.innerHTML = `
                        <div><i class="ti ${iconClass} me-2"></i> ${file.name} <small class="text-muted">(${(file.size / 1024).toFixed(1)}KB - {{ $l == "en" ? "Uploaded by: You" : "رفع بواسطة: أنت" }})</small></div>
                        <div>
                            <a href="#" class="btn btn-sm btn-outline-primary me-1" onclick="downloadFile('${file.name}', '${file.name.replace(/\s+/g, '_')}')" title="{{ $l == "en" ? "Download" : "تنزيل" }}"><i class="ti ti-download"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteFile(this, '${file.name}')" title="{{ $l == "en" ? "Delete" : "حذف" }}"><i class="ti ti-trash"></i></button>
                        </div>`;
                    fileList.prepend(newItem); // Add to top
                    
                    const uploadModal = bootstrap.Modal.getInstance(document.getElementById('uploadFileModal'));
                    uploadModal.hide();
                    document.getElementById('fileUploadForm').reset();
                } else {
                    throw new Error(data.message || 'Upload failed');
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                
                // For demo purposes, still add the file to UI but show warning
                Swal.fire({
                    icon: 'warning',
                    title: '{{ $l == "en" ? "Demo Mode" : "وضع التجريبي" }}',
                    text: `{{ $l == "en" ? "File added to demo list: " : "تم إضافة الملف للقائمة التجريبية: " }}${file.name}`,
                    confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}'
                });

                // Add to file list UI (demo mode)
                const fileList = document.getElementById('fileList');
                const newItem = document.createElement('li');
                newItem.className = 'list-group-item d-flex justify-content-between align-items-center file-list-item';
                
                let iconClass = 'ti-file-text';
                if (file.name.endsWith('.zip')) iconClass = 'ti-file-zip text-warning';
                else if (file.name.endsWith('.pptx')) iconClass = 'ti-file-type-ppt text-danger';
                else if (file.name.endsWith('.docx')) iconClass = 'ti-file-text text-info';
                else if (file.name.endsWith('.pdf')) iconClass = 'ti-file-type-pdf text-danger';
                else if (file.name.endsWith('.jpg') || file.name.endsWith('.png')) iconClass = 'ti-photo text-success';

                newItem.innerHTML = `
                    <div><i class="ti ${iconClass} me-2"></i> ${file.name} <small class="text-muted">(${(file.size / 1024).toFixed(1)}KB - {{ $l == "en" ? "Uploaded by: You (Demo)" : "رفع بواسطة: أنت (تجريبي)" }})</small></div>
                    <div>
                        <a href="#" class="btn btn-sm btn-outline-primary me-1" onclick="downloadFile('${file.name}', '${file.name.replace(/\s+/g, '_')}')" title="{{ $l == "en" ? "Download" : "تنزيل" }}"><i class="ti ti-download"></i></a>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteFile(this, '${file.name}')" title="{{ $l == "en" ? "Delete" : "حذف" }}"><i class="ti ti-trash"></i></button>
                    </div>`;
                fileList.prepend(newItem);
                
                const uploadModal = bootstrap.Modal.getInstance(document.getElementById('uploadFileModal'));
                uploadModal.hide();
                document.getElementById('fileUploadForm').reset();
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: '{{ $l == "en" ? "No File Selected" : "لم يتم اختيار ملف" }}',
                text: '{{ $l == "en" ? "Please choose a file first." : "يرجى اختيار ملف أولاً." }}',
                confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}'
            });
        }
    }

    function deleteFile(buttonElement, fileName) {
        console.log('deleteFile called with:', buttonElement, fileName);
        
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded!');
            alert('Delete functionality not available - SweetAlert2 not loaded');
            return;
        }
        
        Swal.fire({
            title: '{{ $l == "en" ? "Are you sure?" : "هل أنت متأكد؟" }}',
            text: `{{ $l == "en" ? "Do you want to delete the file: " : "هل تريد حذف الملف: " }}"${fileName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '{{ $l == "en" ? "Yes, delete it!" : "نعم، احذفه!" }}',
            cancelButtonText: '{{ $l == "en" ? "Cancel" : "إلغاء" }}'
        }).then((result) => {
            if (result.isConfirmed) {
                // Remove the file from the DOM
                const fileElement = buttonElement.closest('li');
                if (fileElement) {
                    fileElement.remove();
                }
                
                Swal.fire({
                    title: '{{ $l == "en" ? "Deleted!" : "تم الحذف!" }}',
                    text: '{{ $l == "en" ? "File has been deleted." : "تم حذف الملف." }}',
                    icon: 'success',
                    confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}'
                });
            }
        });
    }

    function deleteTask(taskId, taskTitle) {
        console.log('deleteTask called with:', taskId, taskTitle);
        
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded!');
            alert('Delete functionality not available - SweetAlert2 not loaded');
            return;
        }
        
        Swal.fire({
            title: '{{ $l == "en" ? "Are you sure?" : "هل أنت متأكد؟" }}',
            text: `{{ $l == "en" ? "Do you want to delete the task: " : "هل تريد حذف المهمة: " }}"${taskTitle}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '{{ $l == "en" ? "Yes, delete it!" : "نعم، احذفها!" }}',
            cancelButtonText: '{{ $l == "en" ? "Cancel" : "إلغاء" }}'
        }).then((result) => {
            if (result.isConfirmed) {
                // Remove the task from the DOM
                const taskElement = document.getElementById(taskId).closest('li');
                if (taskElement) {
                    taskElement.remove();
                }
                
                Swal.fire({
                    title: '{{ $l == "en" ? "Deleted!" : "تم الحذف!" }}',
                    text: '{{ $l == "en" ? "Task has been deleted." : "تم حذف المهمة." }}',
                    icon: 'success',
                    confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}'
                });
            }
        });
    }

    function editTask(taskId, taskTitle, taskAssignee, taskDueDate) {
        console.log('editTask called with:', taskId, taskTitle, taskAssignee, taskDueDate);
        
        try {
            // Pre-fill the modal with existing task data
            document.getElementById('taskTitle').value = taskTitle;
            document.getElementById('taskAssignee').value = taskAssignee;
            document.getElementById('taskDueDate').value = taskDueDate || '';
            
            // Change the modal title to indicate editing
            document.getElementById('addTaskModalLabel').textContent = '{{ $l == "en" ? "Edit Task" : "تعديل المهمة" }}';
            
            // Store the task ID for updating
            document.getElementById('addTaskModal').setAttribute('data-editing-task', taskId);
            
            // Show the modal
            const editModal = new bootstrap.Modal(document.getElementById('addTaskModal'));
            editModal.show();
        } catch (error) {
            console.error('Error in editTask:', error);
        }
    }

    // Update the addTask function to handle both adding and editing
    function addTask() {
        const taskTitle = document.getElementById('taskTitle').value;
        const taskAssignee = document.getElementById('taskAssignee').value;
        const taskDueDate = document.getElementById('taskDueDate').value;
        const taskModal = document.getElementById('addTaskModal');
        const editingTaskId = taskModal.getAttribute('data-editing-task');
        
        if (!taskTitle) {
            Swal.fire({
                icon: 'warning',
                title: '{{ $l == "en" ? "Task Title Required" : "عنوان المهمة مطلوب" }}',
                text: '{{ $l == "en" ? "Please enter a task title." : "يرجى إدخال عنوان للمهمة." }}',
                confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}'
            });
            return;
        }

        if (editingTaskId) {
            // Update existing task
            const taskElement = document.getElementById(editingTaskId);
            const taskLabel = taskElement.nextElementSibling;
            const taskDetails = taskLabel.nextElementSibling;
            
            taskLabel.textContent = taskTitle;
            taskDetails.textContent = `{{ $l == "en" ? "Responsible: " : "المسؤول: " }}${taskAssignee} ${taskDueDate ? '{{ $l == "en" ? "- Due: " : "- تاريخ الاستحقاق: " }}' + new Date(taskDueDate).toLocaleDateString('{{ $l == "en" ? "en-US" : "ar-EG" }}') : ''}`;
            
            // Update the onclick attributes
            const editButton = taskElement.closest('li').querySelector('.btn-outline-secondary');
            const deleteButton = taskElement.closest('li').querySelector('.btn-outline-danger');
            editButton.setAttribute('onclick', `editTask('${editingTaskId}', '${taskTitle}', '${taskAssignee}', '${taskDueDate}')`);
            deleteButton.setAttribute('onclick', `deleteTask('${editingTaskId}', '${taskTitle}')`);
            
            Swal.fire({
                icon: 'success',
                title: '{{ $l == "en" ? "Task Updated!" : "تم تحديث المهمة!" }}',
                text: `{{ $l == "en" ? "Task updated: " : "تم تحديث المهمة: " }}"${taskTitle}"`,
                confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}'
            });
            
            // Reset modal
            taskModal.removeAttribute('data-editing-task');
            document.getElementById('addTaskModalLabel').textContent = '{{ $l == "en" ? "Add New Task" : "إضافة مهمة جديدة" }}';
        } else {
            // Add new task
            Swal.fire({
                icon: 'success',
                title: '{{ $l == "en" ? "Task Added!" : "تمت إضافة المهمة!" }}',
                text: `{{ $l == "en" ? "Task added: " : "تمت إضافة المهمة: " }}"${taskTitle}"{{ $l == "en" ? "\nAssignee: " : "\nالمسؤول: " }}${taskAssignee}{{ $l == "en" ? "\nDue Date: " : "\nتاريخ الاستحقاق: " }}${taskDueDate || '{{ $l == "en" ? "Not specified" : "غير محدد" }}'}`,
                confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}'
            });

            // Add to task list UI
            const taskList = document.getElementById('taskList');
            const newItem = document.createElement('li');
            newItem.className = 'list-group-item task-list-item';
            const newTaskId = 'task' + (taskList.children.length + 1);
            newItem.innerHTML = `
                <div class="form-check d-flex justify-content-between align-items-center">
                    <div>
                        <input class="form-check-input me-2" type="checkbox" value="" id="${newTaskId}">
                        <label class="form-check-label" for="${newTaskId}">
                            ${taskTitle}
                        </label>
                        <small class="d-block text-muted">{{ $l == "en" ? "Responsible: " : "المسؤول: " }}${taskAssignee} ${taskDueDate ? '{{ $l == "en" ? "- Due: " : "- تاريخ الاستحقاق: " }}' + new Date(taskDueDate).toLocaleDateString('{{ $l == "en" ? "en-US" : "ar-EG" }}') : ''}</small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-1" onclick="editTask('${newTaskId}', '${taskTitle}', '${taskAssignee}', '${taskDueDate}')" title="{{ $l == "en" ? "Edit Task" : "تعديل المهمة" }}"><i class="ti ti-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTask('${newTaskId}', '${taskTitle}')" title="{{ $l == "en" ? "Delete Task" : "حذف المهمة" }}"><i class="ti ti-trash"></i></button>
                    </div>
                </div>`;
            taskList.appendChild(newItem);
        }
        
        const addTaskModal = bootstrap.Modal.getInstance(document.getElementById('addTaskModal'));
        addTaskModal.hide();
        document.getElementById('taskForm').reset();
    }
    </script>
</body>

</html>