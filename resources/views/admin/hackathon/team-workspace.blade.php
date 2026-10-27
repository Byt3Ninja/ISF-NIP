@extends('layouts.admin')

@section('style')
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
            font-size: 16px;
            /* Prevent iOS zoom */
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

        .btn-group-sm>.btn,
        .btn-sm {
            font-size: 0.75rem;
        }
    }
</style>
@stop

@php
// $teamData = [
// 'name' => 'Team Champions',
// 'name_ar' => 'فريق الأبطال',
// 'hackathon' => 'Future of AI Hackathon',
// 'hackathon_ar' => 'هاكثون مستقبل الذكاء الاصطناعي',
// 'members' => [
// ['name' => 'Ahmed', 'name_ar' => 'أحمد'],
// ['name' => 'Sara', 'name_ar' => 'سارة'],
// ['name' => 'You', 'name_ar' => 'أنت'],
// ]
// ];
@endphp

@section('content')
<!-- Main Content -->
<div class="">
    <div class="">
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
                        <div class="tab-pane fade show active p-6 border-top-0 rounded-bottom bg-white" id="files"
                            role="tabpanel" aria-labelledby="files-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>{{ $l == 'en' ? 'Uploaded Files:' : 'الملفات المرفوعة:' }}</h6>
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#uploadFileModal">
                                    <i class="ti ti-cloud-upload me-2"></i> {{ $l == 'en' ? 'Upload New File' : 'رفع ملف
                                    جديد' }}
                                </button>
                            </div>

                            <ul class="list-group list-group-flush" id="fileList">
                                @foreach($sharedFile as $file)
                                
                                 @php
                                 $user = Auth::user();
                                    $fullPath = $file->sf_file;
                                    $pathInfo = pathinfo($fullPath);
                                    $filename = $pathInfo['filename'];
                                    $extension = $pathInfo['extension'] ?? '';
                                    if (\Illuminate\Support\Facades\Storage::disk('hackathon_shared')->exists($fullPath)) {
                                        $bytes = \Illuminate\Support\Facades\Storage::disk('hackathon_shared')->size($fullPath);
                                        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                                        $factor = floor((strlen($bytes) - 1) / 3);
                                        $sizeReadable = sprintf('%.2f', $bytes / pow(1024, $factor)) . $units[$factor];
                                    } else {
                                        $sizeReadable = $l == 'en' ? 'File not found' : 'الملف غير موجود';
                                    }
                                  
                                 @endphp
                                 
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="ti ti-file-type-ppt text-danger me-2"></i>
                                            {{ $file->sf_original_name ?? ($filename.".".$extension) }}
                                            <small class="text-muted d-block">
                                                ( {{ $sizeReadable }} - {{ $l == 'en' ? 'Uploaded by: ' . $file->{"user_name_". $l} : 'رفع بواسطة: ' . $file->{"user_name_". $l} }})
                                            </small>
                                        </div>
                                        <div>
                                            <a href="{{ route('hackathon.downloadSharedFile', $file->sf_id) }}"
                                                onclick="downloadFile(event, {{ $file->sf_id }})"
                                                class="btn btn-sm btn-outline-primary me-1"
                                                title="{{ $l == 'en' ? 'Download' : 'تنزيل' }}">
                                                <i class="ti ti-download"></i>
                                            </a>
                                            @if($file->sf_user_id == $user->id)
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteFile(this, {{ $file->sf_id }})"
                                                    title="{{ $l == 'en' ? 'Delete' : 'حذف' }}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            @elseif($file->tf_user_id == $user->id)
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteFile(this, {{ $file->sf_id }})"
                                                    title="{{ $l == 'en' ? 'Delete' : 'حذف' }}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <!-- Tasks Tab -->
                        <div class="tab-pane fade p-3 border-top-0 rounded-bottom bg-white" id="tasks" role="tabpanel"
                            aria-labelledby="tasks-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>{{ $l == 'en' ? 'Current Tasks:' : 'المهام الحالية:' }}</h6>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#addTaskModal">
                                    <i class="ti ti-plus me-1"></i> {{ $l == 'en' ? 'Add New Task' : 'إضافة مهمة جديدة'
                                    }}
                                </button>
                            </div>
                            <ul class="list-group list-group-flush" id="taskList">
                                @if($todoList->count() > 0)
                            @foreach($todoList as $task)
                                <li class="list-group-item">
                                    <div class="form-check d-flex justify-content-between align-items-center">
                                        <div>
                                            <input class="form-check-input me-2" type="checkbox" value="" id="task1"
                                                 @if($task->tl_status == "Done")
                                                checked
                                                @endif>
                                            <label class="form-check-label" for="task1"
                                                @if($task->tl_status == "Done")
                                                style="text-decoration: line-through;" 
                                                @endif
                                                >
                                                {{ $task->tl_title }}
                                            </label>
                                            <small class="d-block text-muted">
                                                {{ $l == 'en' ? 'Responsible: '. $task->assignee_name_en .' - Due: ' . $task->tl_due_date : 'المسؤول: '. $task->assignee_name_en .' -
                                                تاريخ الاستحقاق: ' . $task->tl_due_date }}
                                            </small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-secondary me-1"
                                                onclick="editTask('{{ $task->tl_id }}', '{{ $task->tl_title }}', '{{ $task->assignee_name_en }}', '{{ $task->tl_due_date }}', '{{ $task->tl_description }}')"
                                                title="{{ $l == 'en' ? 'Edit Task' : 'تعديل المهمة' }}">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="deleteTask('{{ $task->tl_id }}', '{{ $task->tl_title }}')"
                                                title="{{ $l == 'en' ? 'Delete Task' : 'حذف المهمة' }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                                @endif


                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Upload File Modal -->
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadFileModalLabel">{{ $l == 'en' ? 'Upload New File' : 'رفع ملف جديد'
                    }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="" method="POST" action="/dashboard/hackathon/addSharedFile" enctype="multipart/form-data" >
                    <div class="mb-3">
                        <label for="fileInput" class="form-label">{{ $l == 'en' ? 'Choose File' : 'اختر ملف'
                            }}</label>
                        <input class="form-control" name="file" type="file" id="fileInput" required>
                    </div>
                    <div class="mb-3">
                        <label for="fileDescription" class="form-label">{{ $l == 'en' ? 'File Description
                            (Optional)' : 'وصف الملف (اختياري)' }}</label>
                        <input type="text" class="form-control" id="fileDescription" name="description"
                            placeholder="{{ $l == 'en' ? 'e.g., Initial logo design' : 'مثال: تصميم الشعار الأولي' }}">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $l == 'en' ? 'Cancel' :
                    'إلغاء' }}</button>
                <button type="submit" class="btn btn-primary" onclick="uploadFile()">{{ $l == 'en' ? 'Upload' :
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
                <form id="taskForm" method="POST" action="/dashboard/hackathon/AddtodoList">
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">{{ $l == 'en' ? 'Task Title' : 'عنوان المهمة'
                            }}</label>
                        <input type="text" class="form-control" name="title" id="taskTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="taskAssignee" class="form-label">{{ $l == 'en' ? 'Assignee' : 'المسؤول'
                            }}</label>
                        <select class="form-select" id="taskAssignee">
                            <option selected  value="You">{{ $l == 'en' ? 'You' : 'أنت' }}</option>

                            @foreach($teamData['members'] as $member)
                            

                            <option value="{{ $member['id'] }}">{{ $l == 'en' ?
                                $member['name'] : $member['name_ar'] }}</option>

                           
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="taskDueDate" class="form-label">{{ $l == 'en' ? 'Due Date' : 'تاريخ الاستحقاق'
                            }}</label>
                        <input type="date" name="due_date" class="form-control" id="taskDueDate">
                    </div>
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">{{ $l == 'en' ? 'Task Description
                            (Optional)' : 'وصف المهمة (اختياري)' }}</label>
                        <textarea class="form-control" id="taskDescription" name="description" rows="2"></textarea>
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

<!-- Edit Task Modal -->
<div class="modal fade" id="EditTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTaskModalLabel">{{ $l == 'en' ? 'Edit Task' : 'تعديل المهمة'
                    }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="taskForm">
                    <div class="mb-3">
                        <label for="EtaskTitle" class="form-label">{{ $l == 'en' ? 'Task Title' : 'عنوان المهمة'
                            }}</label>
                        <input type="text" class="form-control" name="title" id="EtaskTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="EtaskAssignee" class="form-label">{{ $l == 'en' ? 'Assignee' : 'المسؤول'
                            }}</label>
                        <select class="form-select" id="EtaskAssignee">
                            <option selected  value="You">{{ $l == 'en' ? 'You' : 'أنت' }}</option>

                            @foreach($teamData['members'] as $member)
                            

                            <option value="{{ $member['id'] }}">{{ $l == 'en' ?
                                $member['name'] : $member['name_ar'] }}</option>

                           
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="EtaskDueDate" class="form-label">{{ $l == 'en' ? 'Due Date' : 'تاريخ الاستحقاق'
                            }}</label>
                        <input type="date" name="due_date" class="form-control" id="EtaskDueDate">
                    </div>
                    <div class="mb-3">
                        <label for="EtaskDescription" class="form-label">{{ $l == 'en' ? 'Task Description
                            (Optional)' : 'وصف المهمة (اختياري)' }}</label>
                        <textarea class="form-control" id="EtaskDescription" name="description" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $l == 'en' ? 'Cancel' :
                    'إلغاء' }}</button>
                <button type="button" class="btn btn-primary" onclick="updateTask()">{{ $l == 'en' ? 'Edit Task' :
                    'تعديل المهمة' }}</button>
            </div>
        </div>
    </div>
</div>

@stop

@section('pagescripts')
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

    function downloadFile(event, fileId) {
        if (event && typeof event.preventDefault === 'function') event.preventDefault();
        const l = '{{ $l }}';

        fetch(`/dashboard/hackathon/sharedFile/${fileId}/download`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('download-failed');
            const disposition = response.headers.get('Content-Disposition') || '';
            const match = disposition.match(/filename\*?=(?:UTF-8''|")?([^";]+)/i);
            const name = match ? decodeURIComponent(match[1].replace(/"/g, '')) : 'download';
            return response.blob().then(blob => ({ blob, name }));
        })
        .then(({ blob, name }) => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = name;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(() => {
            if (typeof Swal !== 'undefined') {
                releaseAlert('danger', {
                  title: '{{ $l == "en" ? "Error" : "خطأ" }}',
                  message: '{{ $l == "en" ? "File download failed. Please try again." : "فشل تحميل الملف. يرجى المحاولة مرة أخرى." }}',
                  confirmText: '{{ $l == "en" ? "OK" : "موافق" }}'
                });
            }
        });

        return false;
    }

    function uploadFile() {
        const fileInput = document.getElementById('fileInput');
        const fileDescription = document.getElementById('fileDescription') ? document.getElementById('fileDescription').value : '';
        const l = '{{ $l }}'; // "en" أو "ar"

        if (fileInput && fileInput.files.length > 0) {
            const file = fileInput.files[0];

            // بناء FormData
            const formData = new FormData();
            formData.append('file', file);
            formData.append('description', fileDescription);

            // باراميترز من الـ URL
            const urlParams = new URLSearchParams(window.location.search);
            const hackathonId = urlParams.get('hackathonId') || '1';
            const teamId = urlParams.get('teamId') || '123';
            formData.append('hackathon_id', hackathonId);
            formData.append('team_id', teamId);

            // إظهار loading
            Swal.fire({
                title: l === 'en' ? "Uploading..." : "جاري الرفع...",
                text: l === 'en' ? "Please wait while we upload your file." : "يرجى الانتظار أثناء رفع الملف.",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // دالة مساعدة لاختيار الأيقونة
            const getIconClass = (name) => {
                if (name.endsWith('.zip')) return 'ti-file-zip text-warning';
                if (name.endsWith('.pptx')) return 'ti-file-type-ppt text-danger';
                if (name.endsWith('.docx')) return 'ti-file-text text-info';
                if (name.endsWith('.pdf')) return 'ti-file-type-pdf text-danger';
                if (name.match(/\.(jpe?g|png)$/i)) return 'ti-photo text-success';
                return 'ti-file-text';
            };

            const createListItem = (file, record) => {
                const fileList = document.getElementById('fileList');
                if (!fileList) return;
                const newItem = document.createElement('li');
                newItem.className = 'list-group-item d-flex justify-content-between align-items-center file-list-item';

                const escapeHtml = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                const iconClass = getIconClass(file.name);
                const uploadedByText = l === 'en' ? 'Uploaded by: You' : 'رفع بواسطة: أنت';
                const displayName = escapeHtml((record && record.original_name) || file.name);

                newItem.innerHTML = `
                    <div><i class="ti ${iconClass} me-2"></i> ${displayName} <small class="text-muted">(${(file.size / 1024).toFixed(1)}KB - ${uploadedByText})</small></div>
                    <div>
                        <a href="/dashboard/hackathon/sharedFile/${record.id}/download" onclick="downloadFile(event, ${record.id})" class="btn btn-sm btn-outline-primary me-1" title="${l === 'en' ? "Download" : "تنزيل"}"><i class="ti ti-download"></i></a>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteFile(this, '${record.id}')" title="${l === 'en' ? "Delete" : "حذف"}"><i class="ti ti-trash"></i></button>
                    </div>`;
                fileList.prepend(newItem);
            };

            // إرسال الطلب للراوت الجديد
            fetch('/dashboard/hackathon/addSharedFile', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    releaseAlert('success', {
                      title: l === 'en' ? "File Uploaded!" : "تم رفع الملف!",
                      message: data.message,
                      confirmText: l === 'en' ? "OK" : "موافق"
                    });
                    createListItem(file, data);
                } else {
                    releaseAlert('danger', {
                      title: l === 'en' ? "Error" : "خطأ",
                      message: data.message || (l === 'en' ? "Upload failed" : "فشل رفع الملف"),
                      confirmText: l === 'en' ? "OK" : "موافق"
                    });
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                releaseAlert('danger', {
                  title: l === 'en' ? "Error" : "خطأ",
                  message: error.message || (l === 'en' ? "Upload failed" : "فشل رفع الملف"),
                  confirmText: l === 'en' ? "OK" : "موافق"
                });
            })
            .finally(() => {
                const uploadModalEl = document.getElementById('uploadFileModal');
                const uploadModal = uploadModalEl ? bootstrap.Modal.getInstance(uploadModalEl) : null;
                if (uploadModal) uploadModal.hide();

                const form = document.getElementById('fileUploadForm');
                if (form && typeof form.reset === 'function') form.reset();
            });
        } else {
            releaseAlert('warning', {
              title: l === 'en' ? "No File Selected" : "لم يتم اختيار ملف",
              message: l === 'en' ? "Please choose a file first." : "يرجى اختيار ملف أولاً.",
              confirmText: l === 'en' ? "OK" : "موافق",
              hideCancel: true
            });
        }
    }

    function deleteFile(buttonElement, fileId) {
        console.log('deleteFile called with:', buttonElement, fileId);

        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded!');
            alert('Delete functionality not available - SweetAlert2 not loaded');
            return;
        }

        releaseAlert('warning', {
          title: '{{ $l == "en" ? "Are you sure?" : "هل أنت متأكد؟" }}',
          message: '{{ $l == "en" ? "Do you want to delete this file?" : "هل تريد حذف هذا الملف؟" }}',
          confirmText: '{{ $l == "en" ? "Yes, delete it!" : "نعم، احذفه!" }}',
          cancelText: '{{ $l == "en" ? "Cancel" : "إلغاء" }}',
          confirmColor: 'danger'
        }).then((ok) => {
            if (ok) {
                fetch("{{ url('/dashboard/hackathon/deleteSharedFile') }}/" + fileId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error("Error deleting file");
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        // إزالة العنصر من الصفحة
                        const fileElement = buttonElement.closest('li');
                        if (fileElement) fileElement.remove();

                        releaseAlert('success', {
                          title: '{{ $l == "en" ? "Deleted!" : "تم الحذف!" }}',
                          message: '{{ $l == "en" ? "File has been deleted." : "تم حذف الملف." }}',
                          confirmText: '{{ $l == "en" ? "OK" : "موافق" }}'
                        });
                    } else {
                        releaseAlert('danger', {
                          title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
                          message: data.message || '{{ $l == "en" ? "Could not delete file." : "تعذر حذف الملف." }}'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    releaseAlert('danger', {
                      title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
                      message: '{{ $l == "en" ? "Could not delete file." : "تعذر حذف الملف." }}'
                    });
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

        releaseAlert('warning', {
          title: '{{ $l == "en" ? "Are you sure?" : "هل أنت متأكد؟" }}',
          message: `{{ $l == "en" ? "Do you want to delete the task: " : "هل تريد حذف المهمة: " }}"${taskTitle}"?`,
          confirmText: '{{ $l == "en" ? "Yes, delete it!" : "نعم، احذفها!" }}',
          cancelText: '{{ $l == "en" ? "Cancel" : "إلغاء" }}',
          confirmColor: 'danger'
        }).then((ok) => {
            if (ok) {
                fetch("{{ url('/dashboard/hackathon/deleteTodoList') }}/" + taskId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' || data.message?.toLowerCase().includes('successfully')) {
                        releaseAlert('success', {
                          title: '{{ $l == "en" ? "Deleted!" : "تم الحذف!" }}',
                          message: data.message || '{{ $l == "en" ? "Task has been deleted." : "تم حذف المهمة." }}',
                          confirmText: '{{ $l == "en" ? "OK" : "موافق" }}'
                        }).then(() => {
                            // Reload the page after pressing OK
                            location.reload();
                        });
                    } else {
                        releaseAlert('danger', {
                          title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
                          message: data.message || '{{ $l == "en" ? "Could not delete the task." : "تعذر حذف المهمة." }}'
                        });
                    }
                })
                .catch(error => {
                    console.error(error);
                    releaseAlert('danger', {
                      title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
                      message: '{{ $l == "en" ? "Could not delete the task." : "تعذر حذف المهمة." }}'
                    });
                });
            }
        });
    }




    function editTask(taskId, taskTitle, taskAssignee, taskDueDate, taskDescription) {
        console.log('editTask called with:', taskId, taskTitle, taskAssignee, taskDueDate);
        
        try {
            // Pre-fill the modal with existing task data
            document.getElementById('EtaskTitle').value = taskTitle;
            document.getElementById('EtaskAssignee').onchange = taskAssignee;
            document.getElementById('EtaskDueDate').value = taskDueDate || '';
            document.getElementById('EtaskDescription').value = taskDescription || '';
            
           
            // Store the task ID for updating
            document.getElementById('EditTaskModal').setAttribute('data-editing-task', taskId);
            
            // Show the modal
            const editModal = new bootstrap.Modal(document.getElementById('EditTaskModal'));
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
        const taskDescription = document.getElementById('taskDescription').value;

        const taskModal = document.getElementById('addTaskModal');
        const editingTaskId = taskModal.getAttribute('data-editing-task');

        if (!taskTitle) {
            releaseAlert('warning', {
              title: '{{ $l == "en" ? "Task Title Required" : "عنوان المهمة مطلوب" }}',
              message: '{{ $l == "en" ? "Please enter a task title." : "يرجى إدخال عنوان للمهمة." }}',
              confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
              hideCancel: true
            });
            return;
        }

        const formData = {
            title: taskTitle,
            assignee: taskAssignee,
            due_date: taskDueDate,
            description: taskDescription
        };

        fetch('/dashboard/hackathon/AddtodoList', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success === true) {
                releaseAlert('success', {
                  title: '{{ $l == "en" ? "Task Added!" : "تمت إضافة المهمة!" }}',
                  message: data.message || '{{ $l == "en" ? "Task added successfully." : "تمت إضافة المهمة بنجاح." }}',
                  confirmText: '{{ $l == "en" ? "OK" : "موافق" }}'
                });

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

                const addTaskModal = bootstrap.Modal.getInstance(document.getElementById('addTaskModal'));
                addTaskModal.hide();
                document.getElementById('taskForm').reset();

            } else {
                releaseAlert('danger', {
                  title: '{{ $l == "en" ? "Error" : "خطأ" }}',
                  message: data.message || '{{ $l == "en" ? "Something went wrong." : "حدث خطأ ما." }}',
                  confirmText: '{{ $l == "en" ? "OK" : "موافق" }}'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            releaseAlert('danger', {
              title: '{{ $l == "en" ? "Error" : "خطأ" }}',
              message: '{{ $l == "en" ? "Failed to connect to the server." : "فشل الاتصال بالخادم." }}',
              confirmText: '{{ $l == "en" ? "OK" : "موافق" }}'
            });
        });
    }

    function updateTask() {
        const taskTitle = document.getElementById('EtaskTitle').value;
        const taskAssignee = document.getElementById('EtaskAssignee').value;
        const taskDueDate = document.getElementById('EtaskDueDate').value;
        const taskDescription = document.getElementById('EtaskDescription').value;

        const taskModal = document.getElementById('EditTaskModal');
        const editingTaskId = taskModal.getAttribute('data-editing-task');

        if (!taskTitle) {
            releaseAlert('warning', {
              title: '{{ $l == "en" ? "Task Title Required" : "عنوان المهمة مطلوب" }}',
              message: '{{ $l == "en" ? "Please enter a task title." : "يرجى إدخال عنوان للمهمة." }}',
              confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
              hideCancel: true
            });
            return;
        }

        const formData = {
            id: editingTaskId,
            title: taskTitle,
            assignee: taskAssignee,
            due_date: taskDueDate,
            description: taskDescription
        };

        fetch('/dashboard/hackathon/EdittodoList', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success === true) {
                releaseAlert('success', {
                  title: '{{ $l == "en" ? "Task Updated!" : "تم تعديل المهمة!" }}',
                  message: data.message || '{{ $l == "en" ? "Task updated successfully." : "تم تعديل المهمة بنجاح." }}',
                  confirmText: '{{ $l == "en" ? "OK" : "موافق" }}'
                }).then(() => {
                    window.location.reload();
                });

                // تحديث اسم المهمة في العنصر الظاهر إن وجد
                const taskElement = document.getElementById(editingTaskId);
                if (taskElement) {
                    const label = taskElement.querySelector('label');
                    const small = taskElement.querySelector('small');

                    if (label) label.textContent = taskTitle;
                    if (small) {
                        small.innerHTML = `{{ $l == "en" ? "Responsible: " : "المسؤول: " }}${taskAssignee} ${taskDueDate ? '{{ $l == "en" ? "- Due: " : "- تاريخ الاستحقاق: " }}' + new Date(taskDueDate).toLocaleDateString('{{ $l == "en" ? "en-US" : "ar-EG" }}') : ''}`;
                    }
                }

                const editTaskModal = bootstrap.Modal.getInstance(document.getElementById('EditTaskModal'));
                editTaskModal.hide();
                document.getElementById('taskForm').reset();
                taskModal.removeAttribute('data-editing-task');
                

            } else {
                releaseAlert('danger', {
                  title: '{{ $l == "en" ? "Error" : "خطأ" }}',
                  message: data.message || '{{ $l == "en" ? "Something went wrong." : "حدث خطأ ما." }}',
                  confirmText: '{{ $l == "en" ? "OK" : "موافق" }}'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            releaseAlert('danger', {
              title: '{{ $l == "en" ? "Error" : "خطأ" }}',
              message: '{{ $l == "en" ? "Failed to connect to the server." : "فشل الاتصال بالخادم." }}',
              confirmText: '{{ $l == "en" ? "OK" : "موافق" }}'
            });
        });
    }

</script>
@stop