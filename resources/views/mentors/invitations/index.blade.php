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

<style>
.ql-file::after {
  content: "\f0c6";
  font-family: "Font Awesome 6 Free";
  font-weight: 900;
  color: #6c757d;
}
</style>
@endsection

@section('content')
<div class="">
  <div class="">

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
              <h5 class="card-title mb-0">{{ __('admin.invitations') }}</h5>
              <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#newInvitationOffcanvas">
                <i class="ti ti-plus me-1"></i> {{ __('admin.new_invitation') }}
              </button>
            </div>
          </div>
          <div class="card-datatable table-responsive">
            <table id="invitationsTable" class="table">
              <thead class="table-light">
                <tr>
                  <th>{{ __('admin.invitee') }}</th>
                  <th>{{ __('admin.subject') }}</th>
                  <th>{{ __('admin.Scope') }}</th>
                  <th>{{ __('admin.status') }}</th>
                  <th>{{ __('admin.Accepted_At') }}</th>
                  <th>{{ __('admin.Created At') }}</th>
                  <th>{{ __('admin.Created By') }}</th>
                  <th>{{ __('admin.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($invitations as $item)
                <tr data-scope="{{ $item['scope'] }}" data-status="{{ $item['status'] }}">
                  <td><h6 class="mb-0 fw-semibold">{{ $item['invitee_name'] }}</h6> <small>{{ $item['invitee_email'] }}</small></td>
                  <td>{{ $item['subject'] }}</td>
                  <td>
                    <span class="badge bg-label-{{ $item['scope'] == 'Public' ? 'primary' : 'info' }}">
                      {{ __('admin.' . strtolower($item['scope'])) }}
                    </span>
                  </td>
                  <td>
                    <span class="badge
                      @if($item['status'] == 'Accepted') bg-label-success
                      @elseif($item['status'] == 'Pending') bg-label-warning
                      @else bg-label-info
                      @endif">
                      {{-- <i class="ti ti-{{ $item['status'] == 'Accepted' ? 'circle-check' : ($item['status'] == 'Pending' ? 'clock' : 'circle-x') }} ti-xs me-1"></i> --}}
                      {{ __('admin.' . strtolower($item['status'])) }}
                    </span>
                  </td>
                  <td>{{ $item['accepted_at'] }}</td>
                  <td>{{ $item['created_at'] }}</td>
                  <td> {{ $item['inviter_name'] }}
                  <td>
                    <button class="btn btn-sm btn-outline-primary text-nowrap view-details" data-bs-toggle="offcanvas" data-bs-target="#invitationOffcanvas" data-invitee-name="{{ $item['invitee_name'] }}" data-invitee-email="{{ $item['invitee_email'] }}" data-subject="{{ $item['subject'] }}" data-scope="{{ $item['scope'] }}" data-status="{{ $item['status'] }}" data-message="{{ $item['message_content'] }}" data-date="{{ $item['created_at'] }}" data-attachments="{{ implode(',', $item['attachments']) }}" data-response-message="{{ $item['response_message'] }}" data-orgs="{{ json_encode($item['organizations']) }}">
                      <i class="ti ti-eye me-1"></i> {{ __('admin.view_details') }}
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
      <div class="offcanvas-header border-bottom pb-0">
        <div class="d-flex justify-content-between w-100">
          <div class="d-flex flex-column">
            <h5 class="offcanvas-title fw-bold mb-1" id="invitationTitle">
              @lang('admin.invitation_subject')
            </h5>
            <small class="text-muted mb-2">
              <i class="ti ti-calendar me-1"></i>
              @lang('admin.sent_on'): <span id="dateTimestamp"></span>
            </small>
            <div class="d-flex align-items-center gap-2 mb-4">
              <div id="invitationScopeDisplay"></div>
              <div id="invitationStatus"></div>
            </div>
          </div>
          <button type="button" class="btn-close m-0 p-0" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
      </div>

      <div class="d-flex flex-column gap-2 border-bottom px-8 pb-4 pt-3" id="invitationOrgsContainer" style="display: none;">
        <h6 class="mb-0">{{ __('admin.Organizations') }}:</h6>
        <div class="d-flex flex-wrap align-items-center gap-2" id="invitationOrgs">
          <!-- JS injected -->
        </div>
      </div>
    
      <div class="offcanvas-body d-flex flex-column">
        <!-- Scrollable Conversation Area -->
        <div class="conversation-thread flex-grow-1">
    
          <!-- 1. The Original Invitation (Admin Perspective) -->
          <div class="mail-message mb-4">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar avatar-sm me-2">
                @if (auth()->user()->image_url)
                  <img src="{{ auth()->user()->image_url }}" alt="{{ auth()->user()->name }}" class="rounded-circle" onerror="this.src='/images/user-placeholder.jpeg'">
                @else
                  <span class="avatar-initial rounded-circle bg-secondary">
                    {{ strtoupper(substr(auth()->user()->first_name_en ?? 'U', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name_en ?? 'U', 0, 1)) }}
                  </span>
                @endif
              </div>
              <div>
                <h6 class="mb-0 small fw-bold">{{ auth()->user()->name }} (@lang('admin.you'))</h6>
                <small class="text-muted">@lang('admin.original_message')</small>
              </div>
            </div>
            <div class="p-3 bg-card border rounded shadow-none">
              <div id="originalMessageContent" class="text-body small">
                <!-- Original message text goes here -->
              </div>
            </div>
          </div>
    
          <!-- Divider -->
          <div class="text-center my-8 position-relative">
            <hr>
            <span class="position-absolute top-50 start-50 translate-middle badge bg-label-secondary small">@lang('admin.replies')</span>
          </div>
    
          <!-- 2. Invitee Replies (Incoming) -->
          <div class="mail-message mb-4">
            <div class="d-flex align-items-center mb-2 flex-row-reverse">
              <div class="avatar avatar ms-2">
                <span class="avatar-initial rounded-circle bg-label-primary" id="inviteeInitials">
                  <i class="ti ti-user"></i>
                </span>
              </div>
              <div class="text-end">
                <h6 class="mb-0 small fw-bold" id="inviteeNameDisplay"></h6>
                <small class="text-muted m-0" id="inviteeEmailDisplay"></small>
              </div>
            </div>
            <div class="p-3 border rounded shadow-none bg-light-subtle">
              <div id="replyMessageContent" class="text-body small mb-3 border-bottom pb-3">
                <!-- Invitee reply text goes here -->
              </div>
    
              <!-- Attachments specifically for this reply -->
              <div id="attachmentsSection">
                <p class="small fw-bold mb-2"><i class="ti ti-paperclip me-1"></i>@lang('admin.attachments')</p>
                <div class="d-flex flex-column gap-2 ps-6" id="attachmentsList">
                  <!-- JS injected: <a class="btn btn-xs btn-white border">filename.pdf</a> -->
                </div>
              </div>
            </div>
          </div>
        </div>
    
        <!-- 3. Sticky Follow-up Section (Footer) -->
        {{-- <div class="offcanvas-footer border-top pt-4">
          <label class="form-label fw-bold small text-primary mb-2">
            <i class="ti ti-arrow-back-up me-1"></i> 
            @lang('admin.send_follow_up_message')
          </label>
          <div class="input-group">
            <textarea class="form-control" id="followUpMessage" rows="3" placeholder="@lang('admin.write_your_message')..."></textarea>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-6">
            <div class="attachment-upload">
              <button class="btn btn-sm btn-outline-secondary border-0" title="Attach Files">
                <i class="ti ti-paperclip fs-4"></i>
              </button>
            </div>
            <button type="button" class="btn btn-primary px-4" id="btnSendFollowUp">
              @lang('admin.Send_Message') <i class="ti ti-send ms-2"></i>
            </button>
          </div>
        </div> --}}
      </div>
    </div>

    <!-- New Invitation Offcanvas -->
    <div class="offcanvas offcanvas-end w-px-600" tabindex="-1" id="newInvitationOffcanvas" aria-labelledby="newInvitationOffcanvasLabel">
      <div class="offcanvas-header border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h5 class="offcanvas-title" id="newInvitationOffcanvasLabel">@lang('admin.new_invitation')</h5>
          <button type="button" class="btn-close m-0 p-0" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
      </div>
      <div class="offcanvas-body">
        <form id="invitationForm" enctype="multipart/form-data" class="d-flex flex-column h-100">
          <meta name="csrf-token" content="{{ csrf_token() }}">
          <input type="hidden" id="uploadedAttachments" name="attachments" value="[]">
          <div class="row">
            <div class="col-12 mb-4">
              <label for="inviteeName" class="form-label">
                @lang('admin.invitee_name')
                <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="inviteeName" name="invitee_name" placeholder="@lang('admin.enter_invitee_name')" required maxlength="255">
              <div class="invalid-feedback" id="inviteeNameError"></div>
            </div>
            <div class="col-12 mb-4">
              <label for="mentorEmail" class="form-label">
                @lang('admin.invitee_email')
                <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="mentorEmail" name="invitee_email" placeholder="@lang('admin.enter_invitee_email')" required>
              <div class="invalid-feedback" id="mentorEmailError"></div>
            </div>
            <div class="col-12 mb-4">
              <label for="invitationScope" class="form-label">@lang('admin.invitation_scope') <span class="text-danger">*</span></label>
              <select class="form-select" id="invitationScope" name="invitation_scope" required>
                <option value="">@lang('admin.select_scope')</option>
                <option value="Public">@lang('admin.Not_Associated') (@lang('admin.Public'))</option>
                <option value="Organization">@lang('admin.Associated') (@lang('admin.organization'))</option>
              </select>
              <div class="invalid-feedback" id="invitationScopeError"></div>
            </div>
            <div class="col-12 mb-4" id="organizationsContainer" style="display: none;">
              <label for="organizations" class="form-label">@lang('admin.Organizations') <span class="text-danger">*</span></label>
              <select class="form-select select2" id="organizations" name="organizations[]" data-placeholder="@lang('admin.Select Organizations')" multiple required>
                @foreach($organizations as $organization)
                  <option value="{{ $organization['id'] }}">{{ $organization['name'] }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback" id="organizationsError"></div>
            </div>
            <div class="col-12 mb-4">
              <label for="invitationSubject" class="form-label">@lang('admin.invitation_subject') <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="invitationSubject" name="invitation_subject" placeholder="@lang('admin.enter_invitation_subject')" required>
              <div class="invalid-feedback" id="invitationSubjectError"></div>
            </div>
            <div class="col-12 mb-4">
              <label for="invitationMessage" class="form-label">@lang('admin.invitation_message') <span class="text-danger">*</span></label>
              <div id="invitationMessageEditor" style="height: 150px; overflow-y: scroll;"></div>
              <textarea class="d-none" id="invitationMessage" name="invitation_message" maxlength="10000" required></textarea>
              <div class="invalid-feedback" id="invitationMessageError"></div>
            </div>
          </div>

          <div class="row mt-auto py-6 border-top">
            <div class="col-12">
              <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">
                  <i class="ti ti-x me-2"></i>
                  @lang('admin.cancel')
                </button>
                <button type="submit" class="btn btn-primary">
                  <i class="ti ti-send me-2"></i> @lang('admin.send_invitation')
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal for showing organizations of an invitation -->
<div class="modal fade" id="invitationOrgsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('admin.Organizations') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div id="invitationOrgsModalBody"></div>
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
$(function () {
  // Translation variables for JavaScript
  const translations = {
    public: '{{ __("admin.public") }}',
    organization: '{{ __("admin.organization") }}',
    accepted: '{{ __("admin.accepted") }}',
    pending: '{{ __("admin.pending") }}',
    declined: '{{ __("admin.declined") }}',
    sent: '{{ __("admin.sent") }}',
    view_details: '{{ __("admin.view_details") }}',
    no_replies_yet: '{{ __("admin.no_replies_yet") }}',
    no_attachments: '{{ __("admin.no_attachments") }}',
    uploading: '{{ __("admin.uploading") }}',
  };

  // Toolbar configuration
  const toolbarOptions = [
    [{ header: [1, 2, 3, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    [{ color: [] }, { background: [] }],
    ['image', 'file'],
    ['clean']
  ];

  // Initialize Quill
  const quill = new Quill('#invitationMessageEditor', {
    theme: 'snow',
    placeholder: '{{ __('admin.compose_your_invitation_message') }}...',
    modules: {
      toolbar: {
        container: toolbarOptions,
        handlers: {
          image: imageHandler,
          file: fileHandler
        }
      }
    }
  });

  // Handle image
  function imageHandler() {
    const input = $('<input type="file" accept="image/*" multiple>');
    
    input.on('change', function() {
      const files = this.files;
      const totalSize = Array.from(files).reduce((sum, file) => sum + file.size, 0);
      const maxSize = 10 * 1024 * 1024; // 10 MB

      if (totalSize > maxSize) {
        releaseFailureAlert({
          title: "{{ __('admin.Error') }}!",
          message: "{{ __('admin.file_exceeds_10_limit') }}"
        });
        return;
      }

      const range = quill.getSelection(true);
      const loadingText = `🖼️ ${translations.uploading}`;
      quill.insertText(range.index, loadingText + '\n');

      uploadFiles(files, range, loadingText, '🖼️');
    });
    
    input.click();
  }

  // Handle file
  function fileHandler() {
    const input = $('<input type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" multiple>');
    
    input.on('change', function() {
      const files = this.files;
      const totalSize = Array.from(files).reduce((sum, file) => sum + file.size, 0);
      const maxSize = 10 * 1024 * 1024; // 10 MB

      if (totalSize > maxSize) {
        releaseFailureAlert({
          title: "{{ __('admin.Error') }}!",
          message: "{{ __('admin.file_exceeds_10_limit') }}"
        });
        return;
      }

      const range = quill.getSelection(true);
      const loadingText = `📎 ${translations.uploading}`;
      quill.insertText(range.index, loadingText + '\n');

      uploadFiles(files, range, loadingText, '📎');
    });
    
    input.click();
  }

  // Handle files upload
  function uploadFiles(files, range, loadingText, icon) {
    let uploadedCount = 0;
    const uploadedItems = [];

    Array.from(files).forEach(file => {
      const formData = new FormData();
      formData.append('file', file);

      $.ajax({
        url: '{{ route("invitations.upload") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
          if (response.success) {
            uploadedItems.push({ name: file.name, url: response.data.file_url });
            uploadedCount++;

            if (uploadedCount === files.length) {
              insertUploadedItems(range, loadingText, uploadedItems, icon);
            }
          } else {
            handleUploadError(range, loadingText, response.message);
          }
        },
        error: function(xhr) {
          handleUploadError(range, loadingText, xhr.responseJSON?.message);
        }
      });
    });
  }

  // Insert uploaded items into editor
  function insertUploadedItems(range, loadingText, items, icon) {
    quill.deleteText(range.index, loadingText.length + 1);

    let insertIndex = range.index;
    items.forEach(item => {
      quill.insertText(insertIndex, `${icon} ${item.name}`, { link: item.url });
      quill.insertText(insertIndex + item.name.length + 4, '\n');
      insertIndex += item.name.length + 5;
    });

    quill.setSelection(insertIndex);

    // Update attachments
    const currentAttachments = JSON.parse($('#uploadedAttachments').val() || '[]');
    const newAttachments = [...currentAttachments, ...items.map(i => i.url)];
    $('#uploadedAttachments').val(JSON.stringify(newAttachments));
  }

  // Handle upload errors
  function handleUploadError(range, loadingText, message) {
    quill.deleteText(range.index, loadingText.length + 1);
    releaseFailureAlert({
      title: "{{ __('admin.Error') }}!",
      message: message || "{{ __('admin.unexpected_error') }}"
    })
  }

  // Debounced detection for removed uploaded files
  let _deleteDebounceTimer = null;
  quill.on('text-change', () => {
    $('#invitationMessage').val(quill.root.innerHTML);
    fv && fv.revalidateField('invitation_message');

    // debounce to avoid many rapid requests
    clearTimeout(_deleteDebounceTimer);
    _deleteDebounceTimer = setTimeout(() => {
      detectAndDeleteRemovedFiles();
    }, 900);
  });

  // Detect links/images present in editor and call server to delete removed uploads
  function detectAndDeleteRemovedFiles() {
    try {
      const currentAttachments = JSON.parse($('#uploadedAttachments').val() || '[]');
      if (!Array.isArray(currentAttachments) || currentAttachments.length === 0) return;

      const parser = new DOMParser();
      const doc = parser.parseFromString(quill.root.innerHTML, 'text/html');
      const anchors = Array.from(doc.querySelectorAll('a')).map(a => a.href).filter(Boolean);
      const imgs = Array.from(doc.querySelectorAll('img')).map(i => i.src).filter(Boolean);
      const present = [...anchors, ...imgs];

      // Files that were uploaded before but no longer present in editor
      const removed = currentAttachments.filter(u => !present.includes(u));
      if (removed.length === 0) return;

      removed.forEach(url => {
        // send only the stored file name (basename) as expected by controller
        const fileName = url.split('/').pop();
        $.ajax({
          url: '{{ route("invitations.DeleteAttachment") }}',
          method: 'POST',
          data: { file_name: fileName },
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success: function(response) {
            // on success remove from hidden attachments
            const cur = JSON.parse($('#uploadedAttachments').val() || '[]');
            const updated = (cur || []).filter(u => u !== url);
            $('#uploadedAttachments').val(JSON.stringify(updated));
          },
          error: function(xhr) {
            alert(xhr.responseJSON?.message ? xhr.responseJSON.message : '{{ __("admin.File upload failed") }}');
          }
        });
      });
    } catch (e) {
      alert(e?.message ? e?.message : '{{ __("admin.File upload failed") }}'); 
    }
  }

  /* =======================
   * DataTable
   * ======================= */
  const table = $('#invitationsTable').DataTable({
    responsive: true,
    processing: true,
    pageLength: 10,
    order: [[5, 'desc']],
    dom:
      '<"row"' +
      '<"col-12 col-lg-9 d-flex flex-wrap align-items-center ps-0 ps-lg-6"l>' +
      '<"col-12 col-lg-3 dt-action-buttons d-flex justify-content-lg-end pe-0 pe-lg-6"f>' +
      '>' +
      't' +
      '<"row px-6"' +
      '<"col-lg-6"i>' +
      '<"col-lg-6 d-flex justify-content-end"p>' +
      '>',
    language: {
      url:
        '{{ app()->getLocale() }}' === 'ar'
          ? 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
          : 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json',
      sLengthMenu: '_MENU_',
      search: '',
      searchPlaceholder: '{{ __('admin.Search') }}...'
    },
    initComplete() {
      const api = this.api();
      const container = $('<div class="d-flex gap-3 ms-3"></div>').insertAfter('.dataTables_length');

      const addFilter = (col, id, label, width = 150) => {
        const select = $(`
          <select id="${id}" class="form-select" style="min-width:${width}px">
            <option value="">${label}</option>
          </select>
        `)
          .appendTo(container)
          .on('change', function () {
            const val = $(this).val();
            api
              .column(col)
              .search(val ? `^\\s*${$.fn.dataTable.util.escapeRegex(val)}\\s*$` : '', true, false)
              .draw();
          });

        api
          .column(col)
          .data()
          .unique()
          .sort()
          .each(d => {
            const text = $('<div>').html(d).text().replace(/\s+/g, ' ').trim();
            text && select.append(`<option value="${text.toLowerCase()}">${text}</option>`);
          });
      };

      addFilter(2, 'scopeFilter', '@lang('admin.all_scopes')');
      addFilter(3, 'statusFilter', '@lang('admin.All_statuses')');

      // Add sort order filter (newest/oldest first)
      const sortSelect = $(`
        <select id="sortOrderFilter" class="form-select" style="min-width:150px">
          <option value="desc">@lang('admin.newest_first')</option>
          <option value="asc">@lang('admin.oldest_first')</option>
        </select>
      `)
        .appendTo(container)
        .on('change', function () {
          const order = $(this).val();
          api.order([[5, order]]).draw();
        });

      setTimeout(() => {
        $('.form-control-sm, .form-select-sm').removeClass('form-control-sm form-select-sm');
      }, 300);
    }
  });

  /* =======================
   * View Details
   * ======================= */
  $(document).on('click', '.view-details', function () {
    const d = $(this).data();

    $('#inviteeNameDisplay').text(d.inviteeName);
    $('#inviteeEmailDisplay').text(d.inviteeEmail);
    $('#invitationTitle').text(d.subject);
    $('#originalMessageContent').text(d.message.replace(/🖼️[^\n]*/g, '').replace(/📎[^\n]*/g, '').trim());
    $('#replyMessageContent').text(d.responseMessage || translations.no_replies_yet);
    $('#dateTimestamp').text(d.date);

    $('#invitationOrgs').empty();

    const MAX_ORGS = 10;

    if (d.orgs && d.orgs.length) {
      $('#invitationOrgsContainer').show();
      $('#invitationOrgs').empty();

      d.orgs.slice(0, MAX_ORGS).forEach(org => {
        $('#invitationOrgs').append(
          `<span class="badge bg-label-secondary me-1">${org.name}</span>`
        );
      });

      if (d.orgs.length > MAX_ORGS) {
        const remaining = d.orgs.length - MAX_ORGS;
        const hiddenOrgs = d.orgs.slice(MAX_ORGS);

        $('#invitationOrgs').append(`
          <button
            type="button"
            class="badge bg-label-primary border-0"
            data-bs-toggle="modal"
            data-bs-target="#invitationOrgsModal"
            data-orgs='${JSON.stringify(hiddenOrgs)}'>
            +${hiddenOrgs.length} @lang("admin.more")
          </button>
        `);
      }
    }

    const scopeLabels = {
      Public: '@lang("admin.public")',
      Organization: '@lang("admin.organization")'
    };

    const statusLabels = {
      Accepted: '@lang("admin.accepted")',
      Pending: '@lang("admin.pending")',
      Declined: '@lang("admin.declined")',
      Sent: '@lang("admin.sent")'
    };

    $('#invitationScopeDisplay').html(
      `<span class="badge bg-label-${d.scope === 'Public' ? 'primary' : 'info'}">
        ${scopeLabels[d.scope] || d.scope}
      </span>`
    );

    $('#invitationStatus').html(
      `<span class="badge bg-label-${
        d.status === 'Accepted' ? 'success' :
        d.status === 'Pending' ? 'warning' :
        d.status === 'Sent' ? 'info' : 'danger'
      }">
        ${statusLabels[d.status] || d.status}
      </span>`
    );

    const attachments = (d.attachments || '').split(',').filter(a => a.trim());
    $('#attachmentsSection').show();
    if (attachments.length > 0) {
      $('#attachmentsList').html(
        attachments.map(a => `<a href="${a}" target="_blank" class="badge bg-card text-start text-primary border text-truncate">${a.split('/').pop()}</a>`).join('')
      );
    } else {
      $('#attachmentsList').html(`<span class="text-muted small">${translations.no_attachments}</span>`);
    }

    $('#invitationOffcanvas').offcanvas('show');
  });

  /* =======================
   * Scope / Organizations
   * ======================= */
  const toggleOrganizations = show => {
    $('#organizationsContainer').toggle(show);
    $('#organizations').val(null).trigger('change');
    fv.revalidateField('organizations[]');
  };

  $('#invitationScope').on('change', function () {
    toggleOrganizations($(this).val() === 'Organization');
  });

  $('#organizations').on('change select2:select select2:unselect', () => {
    fv.revalidateField('organizations[]');
  });

  /* =======================
   * Form Validation
   * ======================= */
  const fv = FormValidation.formValidation(document.getElementById('invitationForm'), {
    fields: {
      invitee_name: {
        validators: {
          notEmpty: { message: '{{ __('admin.invitee_name_required') }}' },
          stringLength: { max: 255, message: '{{ __('admin.invitee_name_max_length') }}' }
        }
      },
      invitee_email: {
        validators: {
          notEmpty: { message: '{{ __('admin.Please enter the email address') }}' },
          emailAddress: { message: '{{ __('admin.Please enter a valid email address') }}' },
          stringLength: { max: 255, message: '{{ __('admin.email_max_length') }}' }
        }
      },
      invitation_scope: {
        validators: { notEmpty: { message: '{{ __('admin.invitation_scope_required') }}' } }
      },
      'organizations[]': {
        validators: {
          callback: {
            message: '{{ __('admin.organizations_select_required') }}',
            callback: function () {
              const scope = $('#invitationScope').val();
              const value = $('#organizations').val();
  
              if (scope === 'Organization') {
                return value && value.length > 0;
              }
              return true;
            }
          }
        }
      },
      invitation_subject: {
        validators: {
          notEmpty: { message: '{{ __('admin.invitation_subject_required') }}' },
          stringLength: { max: 255, message: '{{ __('admin.invitation_subject_max_length') }}' }
        }
      },
      invitation_message: {
        validators: { notEmpty: { message: '{{ __('admin.invitation_message_required') }}' } }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        eleInvalidClass: 'is-invalid',
        rowSelector: '.mb-4'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });

  /* =======================
   * Submit Form
   * ======================= */
  fv.on('core.form.valid', () => {
    const $btn = $('#invitationForm button[type="submit"]')
      .prop('disabled', true)
      .html('<i class="ti ti-loader me-1"></i> @lang("admin.sending")...');

    $.ajax({
      url: '/dashboard/mentors/invitations',
      method: 'POST',
      data: new FormData($('#invitationForm')[0]),
      processData: false,
      contentType: false,
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success(response) {
        if (response.success === true || response.success === "true") {
          releaseSuccessAlert({
            title: '{{ __('admin.success') }}',
            message:
              '{{ app()->getLocale() === "ar" ? "تم إرسال الدعوة بنجاح" : "Invitation sent successfully." }}'
          });
          $('#newInvitationOffcanvas').offcanvas('hide');
          resetForm();

          // Add new row to table in real-time
          if (response.data && response.data.length > 0) {
            addInvitationToTable(response.data[0]);
          }
        } else {
          // Handle server-side validation errors
          releaseFailureAlert({
            title: '{{ __('admin.error') }}',
            message: response.message || '{{ __('admin.unexpected_error') }}'
          });
        }
      },
      error(xhr) {
        releaseFailureAlert({
          title: '{{ __('admin.error') }}',
          message: xhr.responseJSON?.message || '{{ __('admin.unexpected_error') }}'
        });
      },
      complete() {
        $btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i> Send Invitation');
      }
    });
  });

  /* =======================
   * Add Invitation to Table (Real-time)
   * ======================= */
  function addInvitationToTable(invitation) {
    const scopeBadgeClass = invitation.scope === 'Public' ? 'primary' : 'info';
    const scopeLabel = translations[invitation.scope.toLowerCase()] || invitation.scope;

    let statusBadgeClass, statusIcon, statusLabel;
    switch (invitation.status) {
      case 'Accepted':
        statusBadgeClass = 'success';
        statusIcon = 'circle-check';
        statusLabel = translations.accepted;
        break;
      case 'Pending':
        statusBadgeClass = 'warning';
        statusIcon = 'clock';
        statusLabel = translations.pending;
        break;
      case 'Sent':
        statusBadgeClass = 'info';
        statusIcon = 'send';
        statusLabel = translations.sent;
        break;
      default:
        statusBadgeClass = 'danger';
        statusIcon = 'circle-x';
        statusLabel = translations.declined;
        break;
    }

    const newRow = `
      <tr data-scope="${invitation.scope}" data-status="${invitation.status}">
        <td>${invitation.invitee_name}<br><small>${invitation.invitee_email}</small></td>
        <td>${invitation.subject}</td>
        <td>
          <span class="badge bg-label-${scopeBadgeClass}">
            ${scopeLabel}
          </span>
        </td>
        <td>
          <span class="badge bg-label-${statusBadgeClass}">
            <!-- <i class="ti ti-${statusIcon} ti-xs me-1"></i> -->
            ${statusLabel}
          </span>
        </td>
        <td>${invitation.accepted_at}</td>
        <td>${invitation.created_at}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary text-nowrap view-details" data-bs-toggle="offcanvas" data-bs-target="#invitationOffcanvas" data-invitee-name="${invitation.invitee_name}" data-invitee-email="${invitation.invitee_email}" data-subject="${invitation.subject}" data-scope="${invitation.scope}" data-status="${invitation.status}" data-message="${invitation.message_content}" data-date="${invitation.created_at}" data-attachments="${JSON.parse(invitation.attachments || '[]').join(',')}" data-response-message="${invitation.response_message || ''}" data-orgs='${JSON.stringify(invitation.organizations || [])}'>
            <i class="ti ti-eye me-1"></i> ${translations.view_details}
          </button>
        </td>
      </tr>
    `;

    // Add row to DataTable
    table.row.add($(newRow)[0]).draw(false);

    // Update filters if needed
    updateTableFilters();
  }

  function updateTableFilters() {
    // Rebuild the filter options to include the new row data
    const api = table;
    const container = $('.dataTables_length').next('.d-flex');

    // Remove existing filters
    container.empty();

    // Re-add filters
    const addFilter = (col, id, label, width = 150) => {
      const select = $(`
        <select id="${id}" class="form-select" style="min-width:${width}px">
          <option value="">${label}</option>
        </select>
      `)
        .appendTo(container)
        .on('change', function () {
          const val = $(this).val();
          api
            .column(col)
            .search(val ? `^\\s*${$.fn.dataTable.util.escapeRegex(val)}\\s*$` : '', true, false)
            .draw();
        });

      api
        .column(col)
        .data()
        .unique()
        .sort()
        .each(d => {
          const text = $('<div>').html(d).text().replace(/\s+/g, ' ').trim();
          text && select.append(`<option value="${text.toLowerCase()}">${text}</option>`);
        });
    };

    addFilter(2, 'scopeFilter', '@lang('admin.all_scopes')');
    addFilter(3, 'statusFilter', '@lang('admin.All_statuses')');

    // Add sort order filter (newest/oldest first)
    const sortSelect = $(`
      <select id="sortOrderFilter" class="form-select" style="min-width:150px">
        <option value="desc">@lang('admin.newest_first')</option>
        <option value="asc">@lang('admin.oldest_first')</option>
      </select>
    `)
      .appendTo(container)
      .on('change', function () {
        const order = $(this).val();
        api.order([[5, order]]).draw();
      });

    setTimeout(() => {
      $('.form-control-sm, .form-select-sm').removeClass('form-control-sm form-select-sm');
    }, 300);
  }

  /* =======================
   * View Details
   * ======================= */
  $('#newInvitationOffcanvas').on('hidden.bs.offcanvas', resetForm);

  function resetForm() {
    $('#invitationForm')[0].reset();
    fv.resetForm();
    $('#organizationsContainer').hide();
    $('#attachmentsList').empty();
    $('#uploadedAttachments').val('[]');
    quill.setContents([]);
  }

  $('#invitationOrgsModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);
    const orgs = button.data('orgs');

    const $body = $('#invitationOrgsModalBody');
    $body.empty();

    if (orgs && orgs.length) {
      orgs.forEach(org => {
        $body.append(
          `<span class="badge bg-label-secondary me-2 mb-2">
            ${org.name}
          </span>`
        );
      });
    } else {
      $body.text('No organizations found.');
    }
  });
});
</script>
@endsection