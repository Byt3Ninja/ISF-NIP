@extends('layouts.admin')

@php
$currentRoute = \Route::currentRouteName();

$tabs = [
  ['title' => __('admin.allMentors'), 'route' => 'mentors.list', 'icon' => 'users'],
  ['title' => __('admin.invitations'), 'route' => 'invitations.index', 'icon' => 'inbox'],
];
@endphp

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@endsection

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills mb-6">
      @foreach($tabs as $tab)
        <li class="nav-item">
          <a 
            class="nav-link {{ $currentRoute === $tab['route'] ? 'active' : '' }}" 
            href="{{ route($tab['route']) }}"
          >
            <i class="ti ti-{{ $tab['icon'] }} me-2"></i>
            {{ $tab['title'] }}
          </a>
        </li>
      @endforeach
    </ul>
    
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">Invitations</h5>
              <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#newInvitationOffcanvas">
                <i class="ti ti-plus me-1"></i> New Invitation
              </button>
            </div>
          </div>
          <div class="card-datatable table-responsive">
            <table id="invitationsTable" class="table">
              <thead class="table-light">
                <tr>
                  <th>Invitee Name</th>
                  <th>Title</th>
                  <th>Scope</th>
                  <th>Status</th>
                  <th>Created At</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($invitations as $item)
                <tr data-scope="{{ $item['scope'] }}" data-status="{{ $item['status'] }}">
                  <td>{{ $item['invitee_name'] }}</td>
                  <td>{{ $item['title'] }}</td>
                  <td>
                    <span class="badge bg-label-{{ $item['scope'] == 'Public' ? 'primary' : 'info' }}">{{ $item['scope'] }}</span>
                  </td>
                  <td>
                    <span class="badge
                      @if($item['status'] == 'Accepted') bg-label-success
                      @elseif($item['status'] == 'Pending') bg-label-warning
                      @else bg-label-danger
                      @endif">
                      <i class="ti ti-{{ $item['status'] == 'Accepted' ? 'circle-check' : ($item['status'] == 'Pending' ? 'clock' : 'circle-x') }} ti-sm me-1"></i>
                      {{ $item['status'] }}
                    </span>
                  </td>
                  <td>{{ $item['date_timestamp'] }}</td>
                  <td>
                    <button class="btn btn-sm btn-primary view-details" data-bs-toggle="offcanvas" data-bs-target="#invitationOffcanvas" data-invitee-name="{{ $item['invitee_name'] }}" data-invitee-email="{{ $item['invitee_email'] }}" data-title="{{ $item['title'] }}" data-scope="{{ $item['scope'] }}" data-status="{{ $item['status'] }}" data-message="{{ $item['message_content'] }}" data-date="{{ $item['date_timestamp'] }}" data-attachments="{{ implode(',', $item['attachments']) }}" data-response-message="{{ $item['response_message'] }}">
                      <i class="ti ti-eye me-1"></i> View Details
                    </button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Invitation Details Offcanvas -->
    <div class="offcanvas offcanvas-end w-px-600" tabindex="-1" id="invitationOffcanvas" aria-labelledby="invitationOffcanvasLabel">
      <!-- Header: Mail Subject Style -->
      <div class="offcanvas-header border-bottom">
        <div class="d-flex justify-content-between w-100">
          <div class="d-flex flex-column">
            <h5 class="offcanvas-title fw-bold mb-1" id="invitationTitle">Invitation Subject</h5>
            <small class="text-muted mb-2">
              <i class="ti ti-calendar me-1"></i>
              Sent on: <span id="dateTimestamp"></span>
            </small>
            <div class="d-flex align-items-center gap-2">
              <div id="invitationScope"></div>
              <div id="invitationStatus"></div>
            </div>
          </div>
          <button type="button" class="btn-close m-0 p-0" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
      </div>
    
      <div class="offcanvas-body d-flex flex-column">
        <!-- Scrollable Conversation Area -->
        <div class="conversation-thread flex-grow-1">
    
          <!-- 1. The Original Invitation (Admin Perspective) -->
          <div class="mail-message mb-4">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar avatar-sm me-2">
                <span class="avatar-initial rounded-circle bg-secondary">AD</span>
              </div>
              <div>
                <h6 class="mb-0 small fw-bold">Admin (You)</h6>
                <small class="text-muted">Original Message</small>
              </div>
            </div>
            <div class="p-3 bg-white border rounded shadow-sm">
              <div id="originalMessageContent" class="text-body small">
                <!-- Original message text goes here -->
              </div>
            </div>
          </div>
    
          <!-- Divider -->
          <div class="text-center my-8 position-relative">
            <hr>
            <span class="position-absolute top-50 start-50 translate-middle badge bg-label-secondary small">Replies</span>
          </div>
    
          <!-- 2. Invitee Replies (Incoming) -->
          <div class="mail-message mb-4">
            <div class="d-flex align-items-center mb-2 flex-row-reverse">
              <div class="avatar avatar ms-2">
                <span class="avatar-initial rounded-circle bg-primary" id="inviteeInitials">JS</span>
              </div>
              <div class="text-end">
                <h6 class="mb-0 small fw-bold" id="inviteeNameDisplay">John Smith</h6>
                <small class="text-muted m-0" id="inviteeEmailDisplay">john@example.com</small>
              </div>
            </div>
            <div class="p-3 border rounded shadow-sm bg-light-subtle">
              <div id="replyMessageContent" class="text-body small mb-3">
                <!-- Invitee reply text goes here -->
              </div>
    
              <!-- Attachments specifically for this reply -->
              <div id="attachmentsSection" class="border-top pt-2 mt-2">
                <p class="small fw-bold mb-2"><i class="ti ti-paperclip me-1"></i>Attachments</p>
                <div class="d-flex flex-column gap-2 ps-6" id="attachmentsList">
                  <!-- JS injected: <a class="btn btn-xs btn-white border">filename.pdf</a> -->
                </div>
              </div>
            </div>
          </div>
        </div>
    
        <!-- 3. Sticky Follow-up Section (Footer) -->
        <div class="offcanvas-footer border-top pt-4">
          <label class="form-label fw-bold small text-primary mb-2">
            <i class="ti ti-arrow-back-up me-1"></i> 
            Send Follow-up Message
          </label>
          <div class="input-group">
            <textarea class="form-control" id="followUpMessage" rows="3" placeholder="Write your message..."></textarea>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="attachment-upload">
              <button class="btn btn-sm btn-outline-secondary border-0" title="Attach Files">
                <i class="ti ti-paperclip fs-4"></i>
              </button>
            </div>
            <button type="button" class="btn btn-primary px-4" id="btnSendFollowUp">
              Send <i class="ti ti-send ms-1"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- New Invitation Offcanvas -->
    <div class="offcanvas offcanvas-end w-px-600" tabindex="-1" id="newInvitationOffcanvas" aria-labelledby="newInvitationOffcanvasLabel">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="newInvitationOffcanvasLabel">New Invitation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="invitationForm" enctype="multipart/form-data" class="d-flex flex-column h-100">
          <meta name="csrf-token" content="{{ csrf_token() }}">
          <div class="row">
            <div class="col-12 mb-4">
              <label for="mentorEmail" class="form-label">Mentor Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="mentorEmail" name="mentor_email" maxlength="255" required>
              <div class="invalid-feedback" id="mentorEmailError"></div>
            </div>
            <div class="col-12 mb-4">
              <label for="invitationScope" class="form-label">Invitation Scope <span class="text-danger">*</span></label>
              <select class="form-select" id="invitationScope" name="invitation_scope" required>
                <option value="">Select Scope</option>
                <option value="Public">Public</option>
                <option value="Select Organizations">Select Organizations</option>
              </select>
              <div class="invalid-feedback" id="invitationScopeError"></div>
            </div>
            <div class="col-12 mb-4" id="organizationsContainer" style="display: none;">
              <label for="organizations" class="form-label">Organizations <span class="text-danger">*</span></label>
              <select class="form-select" id="organizations" name="organizations[]" multiple required>
                <!-- Organizations will be populated via JS -->
              </select>
              <div class="invalid-feedback" id="organizationsError"></div>
            </div>
            <div class="col-12 mb-4">
              <label for="emailSubject" class="form-label">Email Subject <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="emailSubject" name="email_subject" maxlength="255" required>
              <div class="invalid-feedback" id="emailSubjectError"></div>
            </div>
            <div class="col-12 mb-4">
              <label for="emailBody" class="form-label">Email Body <span class="text-danger">*</span></label>
              <div id="emailBodyEditor" style="height: 200px; overflow-y: scroll;"></div>
              <textarea class="d-none" id="emailBody" name="email_body" maxlength="10000" required></textarea>
              <div class="invalid-feedback" id="emailBodyError"></div>
            </div>
            <div class="col-12">
              <label for="attachments" class="form-label">Attachments (Optional)</label>
              <input type="file" class="form-control" id="attachments" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xlsx,.pptx,.png,.jpg,.jpeg">
              <div class="form-text">Supported types: PDF, DOC, DOCX, XLSX, PPTX, PNG, JPG. Max 5 files, 10MB each.</div>
              <div class="invalid-feedback" id="attachmentsError"></div>
              <div id="attachmentsList" class="mt-2"></div>
            </div>
          </div>

          <hr />

          <div class="row mt-auto">
            <div class="col-12">
              <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendInvitationBtn">
                  <i class="ti ti-send me-1"></i> Send Invitation
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
<script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
<script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
<script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
<script src="/vuexy/assets/vendor/libs/block-ui/block-ui.js"></script>
@endsection

@section('pagescripts')
<script>
// Initialize Quill Editor
const quill = new Quill('#emailBodyEditor', {
  theme: 'snow',
  placeholder: 'Compose your email message...',
  modules: {
    toolbar: [
      [{ 'header': [1, 2, 3, false] }],
      ['bold', 'italic', 'underline', 'strike'],
      [{ 'list': 'ordered'}, { 'list': 'bullet' }],
      [{ 'color': [] }, { 'background': [] }],
      ['link'],
      ['clean']
    ]
  }
});

// Update hidden textarea when Quill content changes
quill.on('text-change', function() {
  const html = quill.root.innerHTML;
  const text = quill.getText().trim();
  $('#emailBody').val(html);
  // Trigger validation if needed
  if (fv) {
    fv.revalidateField('email_body');
  }
});

// Initialize invitations DataTable
$('#invitationsTable').DataTable({
  processing: true,
  pageLength: 10,
  dom: '<"row"' +
    '<"col-12 col-lg-9 d-flex flex-wrap align-items-center ps-0 ps-lg-3"l>' +
    '<"col-12 col-lg-3 dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 pe-0 pe-lg-6"f>' +
    '>' +
    't' +
    '<"row px-6"' +
    '<"col-sm-12 col-lg-6"i>' +
    '<"col-sm-12 col-lg-6 d-flex justify-content-end"p>' +
    '>',
  language: {
    url: '{{ app()->getLocale() }}' === "ar"
      ? "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
      : "https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json",
    sLengthMenu: '_MENU_',
    search: '',
    searchPlaceholder: '{{ __('admin.Search') }}...',
  },

  initComplete: function () {
    const api = this.api();

    const filterContainer = $(
      '<div class="d-flex flex-wrap flex-md-nowrap gap-3 ms-3"></div>'
    );

    $('.dataTables_length').after(filterContainer);

    const addDropdownFilter = (colIndex, id, placeholder, width = 150) => {
      api.columns(colIndex).every(function () {
        const column = this;

        const select = $(`
          <select id="${id}"
            class="form-select text-capitalize"
            style="min-width:${width}px">
            <option value="">${placeholder}</option>
          </select>
        `)
          .appendTo(filterContainer)
          .on('change', function () {
            let val = $(this).val();

            if (!val) {
              column.search('', true, false).draw();
              return;
            }

            val = val.trim().toLowerCase();
            val = $.fn.dataTable.util.escapeRegex(val);

            column.search(`^\\s*${val}\\s*$`, true, false).draw();
          });

        // Populate unique values (clean text only)
        column
          .data()
          .unique()
          .sort()
          .each(function (d) {
            const text = $('<div>')
              .html(d)
              .text()
              .replace(/\s+/g, ' ')
              .trim();

            if (text) {
              select.append(
                `<option value="${text.toLowerCase()}">${text}</option>`
              );
            }
          });
      });
    };

    // Scope filter (column 2)
    addDropdownFilter(2, 'scopeFilter', 'All Scopes', 150);

    // Status filter (column 3)
    addDropdownFilter(3, 'statusFilter', 'All Status', 150);

    setTimeout(() => {
      $('.dataTables_filter .form-control').removeClass('form-control-sm');
      $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);
  }
});

// Handle View Details button click
$(document).on('click', '.view-details', function() {
  const data = $(this).data();

  $('#inviteeNameDisplay').text(data.inviteeName);
  $('#inviteeEmailDisplay').text(data.inviteeEmail);
  $('#invitationTitle').text(data.title);
  $('#originalMessageContent').text(data.message);
  $('#replyMessageContent').text(data.responseMessage);
  $('#dateTimestamp').text(data.date);
  $('#invitationScope').append(
    `<span class="badge bg-label-${data.scope === 'Public' ? 'primary' : 'info'}">${data.scope}</span>`
  )
  $('#invitationStatus').append(
    `<span class="badge bg-label-${data.status === 'Accepted' ? 'success' : (data.status === 'Pending' ? 'warning' : 'danger')}">${data.status}</span>`
  );

  const attachments = data.attachments ? data.attachments.split(',') : [];
  const filteredAttachments = attachments.filter(att => att.trim() !== '');
  if (filteredAttachments.length > 0) {
    $('#attachmentsList').html(filteredAttachments.map(att => `<a class="btn btn-xs btn-white border">${att}</a>`).join(''));
    $('#attachmentsSection').show();
  } else {
    $('#attachmentsSection').hide();
  }

  $('#invitationOffcanvas').offcanvas('show');
});

// Invitation Form Logic
let organizations = []; // Will be populated from API

// Load organizations
$.ajax({
  url: '/api/organizations', // Assume this endpoint exists
  method: 'GET',
  success: function(data) {
    organizations = data;
    populateOrganizations();
  },
  error: function() {
    // Fallback: hardcoded organizations for demo
    organizations = [
      {id: 1, name_en: 'Organization 1', name_ar: 'المنظمة 1'},
      {id: 2, name_en: 'Organization 2', name_ar: 'المنظمة 2'},
      {id: 3, name_en: 'Organization 3', name_ar: 'المنظمة 3'}
    ];
    populateOrganizations();
  }
});

function populateOrganizations() {
  const locale = '{{ app()->getLocale() }}';
  const nameField = locale === 'ar' ? 'name_ar' : 'name_en';
  const $select = $('#organizations');
  $select.empty();
  organizations.forEach(org => {
    $select.append(`<option value="${org.id}">${org[nameField]}</option>`);
  });
}

// Toggle organizations selector based on scope
$('#invitationScope').on('change', function() {
  const scope = $(this).val();
  if (scope === 'Select Organizations') {
    $('#organizationsContainer').show();
    $('#organizations').prop('required', true);
  } else {
    $('#organizationsContainer').hide();
    $('#organizations').prop('required', false);
  }
});

// Initialize Select2 for multi-select
$('#organizations').select2({
  placeholder: 'Select organizations',
  allowClear: true
});

// File upload handling
$('#attachments').on('change', function() {
  const files = this.files;
  const $list = $('#attachmentsList');
  $list.empty();

  if (files.length > 5) {
    $(this).val('');
    showError('attachments', '{{ app()->getLocale() === "ar" ? "يمكنك تحميل 5 ملفات كحد أقصى" : "You can upload a maximum of 5 files" }}');
    return;
  }

  const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'image/png', 'image/jpeg', 'image/jpg'];
  const maxSize = 10 * 1024 * 1024; // 10MB

  for (let file of files) {
    if (!allowedTypes.includes(file.type)) {
      $(this).val('');
      showError('attachments', '{{ app()->getLocale() === "ar" ? "نوع الملف غير مدعوم. يرجى تحميل ملف من نوع PDF أو DOC أو DOCX أو XLSX أو PPTX أو PNG أو JPG." : "Unsupported file type. Please upload a PDF, DOCX, XLSX, PPTX, PNG, or JPG file." }}');
      return;
    }
    if (file.size > maxSize) {
      $(this).val('');
      showError('attachments', '{{ app()->getLocale() === "ar" ? "حجم الملف يتجاوز الحد المسموح به (10 ميجابايت لكل ملف)" : "File size exceeds the allowed limit (10 MB per file)." }}');
      return;
    }
    $list.append(`<div class="badge bg-label-secondary me-1">${file.name}</div>`);
  }
});

// Initialize FormValidation
const fv = FormValidation.formValidation(document.getElementById('invitationForm'), {
  fields: {
    mentor_email: {
      validators: {
        notEmpty: {
          message: '{{ app()->getLocale() === "ar" ? "يرجى إدخال البريد الإلكتروني للمرشد" : "Please enter the mentor email address." }}'
        },
        emailAddress: {
          message: '{{ app()->getLocale() === "ar" ? "يرجى إدخال بريد إلكتروني صالح" : "Please enter a valid email address." }}'
        },
        stringLength: {
          max: 255,
          message: '{{ app()->getLocale() === "ar" ? "البريد الإلكتروني يجب ألا يتجاوز 255 حرفاً" : "Email must not exceed 255 characters." }}'
        }
      }
    },
    invitation_scope: {
      validators: {
        notEmpty: {
          message: '{{ app()->getLocale() === "ar" ? "يرجى اختيار نطاق الدعوة" : "Please select the invitation scope." }}'
        }
      }
    },
    'organizations[]': {
      validators: {
        callback: {
          callback: function(value, validator, $field) {
            const scope = $('#invitationScope').val();
            if (scope === 'Select Organizations') {
              return value && value.length > 0;
            }
            return true;
          },
          message: '{{ app()->getLocale() === "ar" ? "يرجى اختيار مؤسسة واحدة على الأقل" : "Please select at least one organization." }}'
        }
      }
    },
    email_subject: {
      validators: {
        notEmpty: {
          message: '{{ app()->getLocale() === "ar" ? "يرجى إدخال عنوان البريد الإلكتروني" : "Please enter the email subject." }}'
        },
        stringLength: {
          max: 255,
          message: '{{ app()->getLocale() === "ar" ? "عنوان البريد الإلكتروني يجب ألا يتجاوز 255 حرفاً" : "Email subject must not exceed 255 characters." }}'
        }
      }
    },
    email_body: {
      validators: {
        notEmpty: {
          message: '{{ app()->getLocale() === "ar" ? "يرجى إدخال محتوى البريد الإلكتروني" : "Please enter the email content." }}'
        },
        stringLength: {
          max: 10000,
          message: '{{ app()->getLocale() === "ar" ? "محتوى البريد الإلكتروني يجب ألا يتجاوز 10000 حرف" : "Email content must not exceed 10000 characters." }}'
        }
      }
    }
  },
  plugins: {
    trigger: new FormValidation.plugins.Trigger(),
    bootstrap5: new FormValidation.plugins.Bootstrap5({
      rowSelector: '.mb-3'
    }),
    submitButton: new FormValidation.plugins.SubmitButton(),
    defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
    autoFocus: new FormValidation.plugins.AutoFocus()
  }
});

// Form submission
$('#sendInvitationBtn').on('click', function() {
  fv.validate().then(function(status) {
    if (status !== 'Valid') {
      return;
    }

  const formData = new FormData($('#invitationForm')[0]);
  const selectedOrgs = $('#organizations').val();
  if (selectedOrgs && selectedOrgs.length > 0) {
    selectedOrgs.forEach(org => formData.append('organizations[]', org));
  }

  // Show loading
  $(this).prop('disabled', true).html('<i class="ti ti-loader me-1"></i> Sending...');

  $.ajax({
    url: '/invitations', // Assume POST endpoint
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function(response) {
      // Show success message
      showToast('{{ app()->getLocale() === "ar" ? "تم إرسال الدعوة بنجاح" : "Invitation sent successfully." }}', 'success');
      $('#newInvitationOffcanvas').offcanvas('hide');
      // Refresh page to update table
      location.reload();
    },
    error: function(xhr) {
      const error = xhr.responseJSON?.message || '{{ app()->getLocale() === "ar" ? "فشل إرسال الدعوة. يرجى المحاولة مرة أخرى" : "Failed to send the invitation. Please try again." }}';
      showToast(error, 'error');
    },
    complete: function() {
      $('#sendInvitationBtn').prop('disabled', false).html('<i class="ti ti-send me-1"></i> Send Invitation');
    }
  });
  });
});

// Reset form when offcanvas is hidden
$('#newInvitationOffcanvas').on('hidden.bs.offcanvas', function() {
  resetForm();
});

function resetForm() {
  $('#invitationForm')[0].reset();
  fv.resetForm();
  $('#invitationForm .form-control').removeClass('is-valid is-invalid');
  $('#invitationForm .invalid-feedback').hide();
  $('#organizationsContainer').hide();
  $('#organizations').prop('required', false);
  $('#attachmentsList').empty();
  // Clear Quill editor
  quill.setContents([]);
}

function showError(fieldId, message) {
  const $errorElement = $(`#${fieldId}Error`);
  $errorElement.text(message).show();
  $(`#${fieldId}`).addClass('is-invalid');
}

function showToast(message, type) {
  // Simple toast implementation - you might want to use a proper toast library
  const toastClass = type === 'success' ? 'bg-success' : 'bg-danger';
  const toast = $(`<div class="toast align-items-center text-white ${toastClass} border-0 position-fixed top-0 end-0 m-3" role="alert">
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>`);
  $('body').append(toast);
  const bsToast = new bootstrap.Toast(toast);
  bsToast.show();
  setTimeout(() => toast.remove(), 5000);
}
</script>
@endsection
