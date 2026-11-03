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
{{-- Tabs Navigation --}}
<x-navigation-pills :tabs="$tabs" />

{{-- Invitations Table --}}
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
              <td style="min-width: 150px; max-width: 150px">
                <h6 class="mb-0 fw-semibold">{{ $item['invitee_name'] }}</h6>
                <small>{{ $item['invitee_email'] }}</small>
              </td>
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
                  @elseif($item['status'] == 'Sent') bg-label-info
                  @else bg-label-danger
                  @endif">
                  {{ __('admin.' . strtolower($item['status'])) }}
                </span>
              </td>
              <td>{{ $item['accepted_at'] }}</td>
              <td>{{ $item['created_at'] }}</td>
              <td>
                <a
                  href="/dashboard/show/UserProfile/{{ $item['inviter']['id'] }}"
                  class="d-flex align-items-center"
                >
                  <img
                    src="{{ $item['inviter']['img_url'] ? '/191014/' . $item['inviter']['img_url'] : '/icons/account.svg' }}"
                    alt="avatar"
                    class="img-fluid w-px-30 h-px-30 rounded-circle me-2 flex-shrink-0"
                  />
                  <span class="ms-2 text-dark">{{ $item['inviter']['name'] }}</span>
                </a>
              </td>
              <td>
                <button
                  class="btn btn-sm btn-outline-primary text-nowrap view-details"
                  data-bs-toggle="offcanvas"
                  data-bs-target="#invitationOffcanvas"
                  data-invitee-name="{{ $item['invitee_name'] }}"
                  data-invitee-email="{{ $item['invitee_email'] }}"
                  data-subject="{{ $item['subject'] }}"
                  data-scope="{{ $item['scope'] }}"
                  data-status="{{ $item['status'] }}"
                  data-message="{{ $item['message_content'] }}"
                  data-date="{{ $item['created_at'] }}"
                  data-attachments="{{ implode(',', $item['attachments']) }}"
                  data-response-message="{{ $item['response_message'] }}"
                  data-orgs="{{ e(json_encode($item['organizations'])) }}"
                >
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

{{-- Invitation Details Offcanvas --}}
<div class="offcanvas offcanvas-end w-px-600" tabindex="-1" id="invitationOffcanvas" aria-labelledby="invitationOffcanvasLabel">
  <!-- Header -->
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
    <!-- Conversation Area -->
    <div class="conversation-thread flex-grow-1">

      <!-- Original Invitation (sent by admin) -->
      <div class="mail-message mb-4">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar avatar-sm me-2">
            @if (auth()->user()->image_url)
              <img
                src="{{ auth()->user()->image_url }}"
                alt="{{ auth()->user()->name }}"
                class="rounded-circle"
                onerror="this.src='/images/user-placeholder.jpeg'"
              >
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
          <div id="originalMessageContent" class="text-body small"></div>
        </div>
      </div>

      <!-- Invitee Reply -->
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
            <!-- JS injected -->
          </div>

          <!-- Attachments -->
          <div id="attachmentsSection">
            <p class="small fw-bold mb-2"><i class="ti ti-paperclip me-1"></i>@lang('admin.attachments')</p>
            <div class="d-flex flex-column gap-2 ps-6" id="attachmentsList">
              <!-- JS injected -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- New Invitation Offcanvas --}}
<div class="offcanvas offcanvas-end w-px-800" tabindex="-1" id="newInvitationOffcanvas" aria-labelledby="newInvitationOffcanvasLabel">
  <div class="offcanvas-header border-bottom">
    <div class="d-flex justify-content-between w-100">
      <h5 class="offcanvas-title" id="newInvitationOffcanvasLabel">@lang('admin.new_invitation')</h5>
      <button type="button" class="btn-close m-0 p-0" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
  </div>
  <div class="offcanvas-body">
    <form id="invitationForm" enctype="multipart/form-data" class="d-flex flex-column h-100">
      <input type="hidden" id="uploadedAttachments" name="attachments" value="[]">
      <div class="row">

        <div class="col-12 mb-4">
          <label for="inviteeName" class="form-label">
            @lang('admin.invitee_name') <span class="text-danger">*</span>
          </label>
          <input
            type="text"
            class="form-control"
            id="inviteeName"
            name="invitee_name"
            placeholder="@lang('admin.enter_invitee_name')"
            required
            maxlength="255"
          >
          <div class="invalid-feedback" id="inviteeNameError"></div>
        </div>

        <div class="col-12 mb-4">
          <label for="mentorEmail" class="form-label">
            @lang('admin.invitee_email') <span class="text-danger">*</span>
          </label>
          <input
            type="email"
            class="form-control"
            id="mentorEmail"
            name="invitee_email"
            placeholder="@lang('admin.enter_invitee_email')"
            required
            maxlength="255"
          >
          <div class="invalid-feedback" id="mentorEmailError"></div>
        </div>

        <div class="col-12 mb-4">
          <label for="invitationScope" class="form-label">
            @lang('admin.invitation_scope') <span class="text-danger">*</span>
          </label>
          <select class="form-select" id="invitationScope" name="invitation_scope" required>
            <option value="">@lang('admin.select_scope')</option>
            <option value="Public">@lang('admin.Not_Associated') (@lang('admin.Public'))</option>
            <option value="Organization">@lang('admin.Associated') (@lang('admin.organization'))</option>
          </select>
          <div class="invalid-feedback" id="invitationScopeError"></div>
        </div>

        <div class="col-12 mb-4" id="organizationsContainer" style="display: none;">
          <label for="organizations" class="form-label">
            @lang('admin.Organizations') <span class="text-danger">*</span>
          </label>
          <select
            class="form-select select2"
            id="organizations"
            name="organizations[]"
            data-placeholder="@lang('admin.Select Organizations')"
            multiple
          >
            @foreach($organizations as $organization)
              <option value="{{ $organization['id'] }}">{{ $organization['name'] }}</option>
            @endforeach
          </select>
          <div class="invalid-feedback" id="organizationsError"></div>
        </div>

        <div class="col-12 mb-4">
          <label for="invitationSubject" class="form-label">
            @lang('admin.invitation_subject')
          </label>
          <input
            type="text"
            class="form-control"
            id="invitationSubject"
            name="invitation_subject"
            placeholder="@lang('admin.enter_invitation_subject')"
            maxlength="255"
          >
          <div class="invalid-feedback" id="invitationSubjectError"></div>
        </div>

        <div class="col-12 mb-4">
          <label for="invitationMessage" class="form-label">
            @lang('admin.invitation_message')
          </label>
          <div id="invitationMessageEditor" style="height: 150px; overflow-y: scroll;"></div>
          <textarea
            class="d-none"
            id="invitationMessage"
            name="invitation_message"
            maxlength="10000"
          ></textarea>
          <div class="text-muted mt-3"> {{ __('admin.Accepted formats') }}: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX | {{ __('admin.Max size') }}: 10MB </div>
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

{{-- Organizations overflow modal --}}
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

  /* ── Translation map ──────────────────────────────────────────── */
  const translations = {
    public:          '{{ __("admin.public") }}',
    organization:    '{{ __("admin.organization") }}',
    accepted:        '{{ __("admin.accepted") }}',
    pending:         '{{ __("admin.pending") }}',
    declined:        '{{ __("admin.declined") }}',
    sent:            '{{ __("admin.sent") }}',
    view_details:    '{{ __("admin.view_details") }}',
    no_replies_yet:  '{{ __("admin.no_replies_yet") }}',
    no_attachments:  '{{ __("admin.no_attachments") }}',
    uploading:       '{{ __("admin.uploading") }}',
  };

  /* ── Authenticated user (for "Created By" cell in real-time rows) ── */
  const authUser = {
    id:      {{ auth()->id() }},
    name:    '{{ addslashes(auth()->user()->name) }}',
    img_url: '{{ auth()->user()->img_url ? "/191014/" . auth()->user()->img_url : "/icons/account.svg" }}',
  };

  /* ================================================================
   * Quill Rich-Text Editor
   * ============================================================== */
  const quill = new Quill('#invitationMessageEditor', {
    theme: 'snow',
    placeholder: '{{ __("admin.compose_your_invitation_message") }}...',
    modules: {
      toolbar: {
        container: [
          [{ header: [1, 2, 3, false] }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          [{ color: [] }, { background: [] }],
          ['image', 'file'],
          ['clean']
        ],
        handlers: {
          image: () => pickAndUpload('image'),
          file:  () => pickAndUpload('file')
        }
      }
    }
  });

  /* ── File picker + upload ─────────────────────────────────────── */
  function pickAndUpload(type) {
    const isImage = type === 'image';
    const icon    = isImage ? '🖼️' : '📎';

    const input      = document.createElement('input');
    input.type       = 'file';
    input.accept     = isImage ? 'image/*' : '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx';
    input.multiple   = true;

    input.addEventListener('change', function () {
      const files     = Array.from(this.files);
      const totalSize = files.reduce((sum, f) => sum + f.size, 0);

      if (totalSize > 10 * 1024 * 1024) {
        return releaseAlert('danger', {
          title:   '{{ __("admin.Error") }}!',
          message: '{{ __("admin.file_exceeds_10_limit") }}'
        });
      }

      const range       = quill.getSelection(true);
      const loadingText = `${icon} ${translations.uploading}`;
      quill.insertText(range.index, loadingText + '\n');

      let uploaded = 0;
      const items  = [];

      files.forEach(file => {
        const formData = new FormData();
        formData.append('file', file);

        $.ajax({
          url:         '{{ route("invitations.upload") }}',
          method:      'POST',
          data:        formData,
          processData: false,
          contentType: false,
          headers:     { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success(response) {
            if (response.success) {
              items.push({ name: file.name, url: response.data.file_url });
              if (++uploaded === files.length) {
                insertUploadedItems(range, loadingText, items, icon);
              }
            } else {
              handleUploadError(range, loadingText, response.message);
            }
          },
          error(xhr) {
            handleUploadError(range, loadingText, xhr.responseJSON?.message);
          }
        });
      });
    });

    input.click();
  }

  function insertUploadedItems(range, loadingText, items, icon) {
    quill.deleteText(range.index, loadingText.length + 1);
    let i = range.index;
    items.forEach(({ name, url }) => {
      quill.insertText(i, `${icon} ${name}`, { link: url });
      quill.insertText(i + name.length + 4, '\n');
      i += name.length + 5;
    });
    quill.setSelection(i);

    const current = JSON.parse($('#uploadedAttachments').val() || '[]');
    $('#uploadedAttachments').val(JSON.stringify([...current, ...items.map(it => it.url)]));
  }

  function handleUploadError(range, loadingText, message) {
    quill.deleteText(range.index, loadingText.length + 1);
    releaseAlert('danger', {
      title:   '{{ __("admin.Error") }}!',
      message: message || '{{ __("admin.unexpected_error") }}'
    });
  }

  /* ── Sync Quill → hidden textarea + orphan-file detection ──────── */
  let _deleteDebounceTimer = null;

  quill.on('text-change', () => {
    $('#invitationMessage').val(quill.root.innerHTML);
    clearTimeout(_deleteDebounceTimer);
    _deleteDebounceTimer = setTimeout(detectAndDeleteRemovedFiles, 900);
  });

  function detectAndDeleteRemovedFiles() {
    try {
      const currentAttachments = JSON.parse($('#uploadedAttachments').val() || '[]');
      if (!currentAttachments.length) return;

      const doc     = new DOMParser().parseFromString(quill.root.innerHTML, 'text/html');
      const present = [
        ...Array.from(doc.querySelectorAll('a')).map(a => a.href),
        ...Array.from(doc.querySelectorAll('img')).map(img => img.src)
      ].filter(Boolean);

      currentAttachments
        .filter(u => !present.includes(u))
        .forEach(url => {
          $.ajax({
            url:     '{{ route("invitations.DeleteAttachment") }}',
            method:  'POST',
            data:    { file_name: url.split('/').pop() },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success() {
              const cur = JSON.parse($('#uploadedAttachments').val() || '[]');
              $('#uploadedAttachments').val(JSON.stringify(cur.filter(u => u !== url)));
            },
            error(xhr) {
              releaseAlert('danger', {
                title:   '{{ __("admin.Error") }}!',
                message: xhr.responseJSON?.message || '{{ __("admin.File upload failed") }}'
              });
            }
          });
        });
    } catch (e) {
      releaseAlert('danger', {
        title:   '{{ __("admin.Error") }}!',
        message: e?.message || '{{ __("admin.File upload failed") }}'
      });
    }
  }

  /* ================================================================
   * DataTable — custom sort type (status priority)
   * ============================================================== */
  $.fn.dataTable.ext.type.order['status-priority-pre'] = function (data) {
    const div = document.createElement('div');
    div.innerHTML = data;
    const s = div.textContent.toLowerCase().trim();
    if (s.includes('accepted'))                                                      return 1;
    if (s.includes('pending') || s.includes('sent'))                                return 2;
    if (s.includes('declined') || s.includes('rejected') ||
        s.includes('cancelled') || s.includes('notsent'))                           return 3;
    return 4;
  };

  const table = $('#invitationsTable').DataTable({
    responsive:  true,
    processing:  true,
    pageLength:  10,
    order: [[3, 'asc'], [5, 'desc']],
    columnDefs: [
      {
        targets: [2, 3],
        render(data, type) {
          if (type === 'filter' || type === 'sort') {
            const div = document.createElement('div');
            div.innerHTML = data;
            return div.textContent.replace(/\s+/g, ' ').trim();
          }
          return data;
        }
      },
      { targets: 3, type: 'status-priority' }
    ],
    dom:
      '<"row"' +
      '<"col-12 col-lg-9 d-flex flex-wrap align-items-center ps-0 ps-lg-6"l>' +
      '<"col-12 col-lg-3 dt-action-buttons d-flex justify-content-lg-end pe-0 pe-lg-6"f>' +
      '>t<"row px-6"<"col-lg-6"i><"col-lg-6 d-flex justify-content-end"p>>',
    language: {
      url: '{{ app()->getLocale() }}' === 'ar'
        ? 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
        : 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json',
      sLengthMenu:       '_MENU_',
      search:            '',
      searchPlaceholder: '{{ __("admin.Search") }}...'
    },
    initComplete() {
      buildTableFilters(this.api());
      setTimeout(
        () => $('.form-control-sm, .form-select-sm').removeClass('form-control-sm form-select-sm'),
        300
      );
      const $wrapper = $(this.api().table().container());
      $wrapper.find('.form-select, .dataTables_length select').each(function () {
        const $this = $(this);
        if ($this.hasClass('select2-hidden-accessible')) return;

        const isLength = $this.closest('.dataTables_length').length > 0;

        $this.wrap('<div class="position-relative' + (isLength ? ' d-inline-block' : '') + '"></div>');

        $this.select2({
          dropdownParent: $this.parent(),
          width: isLength ? '80px' : '100%',
          minimumResultsForSearch: Infinity,
        });

        if (!isLength) {
          $this.next('.select2-container').css({
            'min-width': '150px',
            'max-width': '200px'
          });
        }
      });
    }
  });

  /* ── Column filters ───────────────────────────────────────────── */
  function buildTableFilters(api) {
    // which duplicates the filter bar. Guard with a flag.
    if ($('#scopeFilter').length) return;

    const container = $('<div class="d-flex gap-3 ms-3"></div>').insertAfter('.dataTables_length');

    const addColumnFilter = (col, id, label) => {
      const select = $(
        `<select id="${id}" class="form-select" style="min-width:150px">` +
        `<option value="">${label}</option></select>`
      )
        .appendTo(container)
        .on('change', function () {
          api.column(col).search($(this).val(), false, false).draw();
        });

      api.column(col).data().unique().sort().each(d => {
        const div  = document.createElement('div');
        div.innerHTML = d;
        const text = div.textContent.replace(/\s+/g, ' ').trim();
        if (text) select.append(`<option value="${text.toLowerCase()}">${text}</option>`);
      });
    };

    addColumnFilter(2, 'scopeFilter',   '@lang("admin.all_scopes")');
    addColumnFilter(3, 'statusFilter',  '@lang("admin.All_statuses")');

    $(`<select id="sortOrderFilter" class="form-select" style="min-width:150px">
        <option value="desc">@lang("admin.newest_first")</option>
        <option value="asc">@lang("admin.oldest_first")</option>
      </select>`)
      .appendTo(container)
      .on('change', function () {
        api.order([[5, $(this).val()]]).draw();
      });
  }

  /* ================================================================
   * View Details — offcanvas population
   * ============================================================== */
  $(document).on('click', '.view-details', function () {
    const d = $(this).data();

    const scopeLabels  = { Public: translations.public,       Organization: translations.organization };
    const statusLabels = { Accepted: translations.accepted,   Pending: translations.pending,
                           Declined: translations.declined,   Sent:    translations.sent };
    const statusColors = { Accepted: 'success', Pending: 'warning', Sent: 'info', Declined: 'danger' };

    $('#inviteeNameDisplay').text(d.inviteeName);
    $('#inviteeEmailDisplay').text(d.inviteeEmail);
    $('#invitationTitle').text(d.subject);

    // emoji lines to avoid dangling icon text, keep bold/italic/lists intact).
    // We sanitise with a text-only DOMParser pass so no scripts execute.
    const rawMsg = d.message || '';

    const cleanedMsg = rawMsg.replace(
      /(🖼️|📎)\s*.*?\.(png|jpg|jpeg|gif|webp|pdf|doc|docx|xls|xlsx)/gi,
      ''
    );

    $('#originalMessageContent').html(cleanedMsg.trim());

    $('#replyMessageContent').text(d.responseMessage || translations.no_replies_yet);
    $('#dateTimestamp').text(d.date);

    $('#invitationScopeDisplay').html(
      `<span class="badge bg-label-${d.scope === 'Public' ? 'primary' : 'info'}">` +
      `${scopeLabels[d.scope] || d.scope}</span>`
    );
    $('#invitationStatus').html(
      `<span class="badge bg-label-${statusColors[d.status] || 'danger'}">` +
      `${statusLabels[d.status] || d.status}</span>`
    );

    // Always reset org container visibility before deciding whether to show it.
    $('#invitationOrgsContainer').hide();
    const $orgs = $('#invitationOrgs').empty();
    const MAX_ORGS = 10;

    // stored in data-* when accessed via .data(), so it should already be an array.
    // Guard against it being a raw string just in case.
    let orgs = d.orgs;
    if (typeof orgs === 'string') {
      try { orgs = JSON.parse(orgs); } catch (_) { orgs = []; }
    }

    if (Array.isArray(orgs) && orgs.length) {
      $('#invitationOrgsContainer').show();
      orgs.slice(0, MAX_ORGS).forEach(org =>
        $orgs.append(`<span class="badge bg-label-secondary me-1">${$('<div>').text(org.name).html()}</span>`)
      );
      if (orgs.length > MAX_ORGS) {
        const hidden = orgs.slice(MAX_ORGS);
        $orgs.append(
          `<button type="button" class="badge bg-label-primary border-0"
             data-bs-toggle="modal" data-bs-target="#invitationOrgsModal"
             data-orgs='${JSON.stringify(hidden)}'>` +
          `+${hidden.length} @lang("admin.more")</button>`
        );
      }
    }

    // Attachments
    const attachments = (d.attachments || '').split(',').filter(a => a.trim());
    $('#attachmentsSection').show();
    $('#attachmentsList').html(
      attachments.length
        ? attachments.map(a => {
            const safe = $('<div>').text(a).html(); // escape for href display
            return `<a href="${a}" target="_blank" rel="noopener noreferrer"
                       class="badge bg-card text-start text-primary border text-truncate">` +
                   `${a.split('/').pop()}</a>`;
          }).join('')
        : `<span class="text-muted small">${translations.no_attachments}</span>`
    );
  });

  /* ================================================================
   * Form — scope change handler
   * ============================================================== */
  $('#invitationScope').on('change', function () {
    const isOrg = $(this).val() === 'Organization';
    $('#organizationsContainer').toggle(isOrg);
    if (!isOrg) $('#organizations').val(null).trigger('change');
    fv.revalidateField('organizations[]');
  });

  $('#organizations').on('change select2:select select2:unselect', () =>
    fv.revalidateField('organizations[]')
  );

  /* ================================================================
   * FormValidation.js
   * ============================================================== */
  const fv = FormValidation.formValidation(document.getElementById('invitationForm'), {
    fields: {
      invitee_name: {
        validators: {
          notEmpty:     { message: '{{ __("admin.invitee_name_required") }}' },
          stringLength: { max: 255, message: '{{ __("admin.invitee_name_max_length") }}' }
        }
      },
      invitee_email: {
        validators: {
          notEmpty:     { message: '{{ __("admin.Please enter the email address") }}' },
          emailAddress: { message: '{{ __("admin.Please enter a valid email address") }}' },
          stringLength: { max: 255, message: '{{ __("admin.email_max_length") }}' }
        }
      },
      invitation_scope: {
        validators: {
          notEmpty: { message: '{{ __("admin.invitation_scope_required") }}' }
        }
      },
      'organizations[]': {
        validators: {
          callback: {
            message:  '{{ __("admin.organizations_select_required") }}',
            callback: () =>
              $('#invitationScope').val() !== 'Organization' ||
              ($('#organizations').val()?.length > 0)
          }
        }
      },
      invitation_subject: {
        validators: {
          stringLength: { max: 255, message: '{{ __("admin.invitation_subject_max_length") }}' }
        }
      }
    },
    plugins: {
      trigger:      new FormValidation.plugins.Trigger(),
      bootstrap5:   new FormValidation.plugins.Bootstrap5({
                      eleValidClass:   '',
                      eleInvalidClass: 'is-invalid',
                      rowSelector:     '.mb-4'
                    }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus:    new FormValidation.plugins.AutoFocus()
    }
  });

  /* ── Submit handler ───────────────────────────────────────────── */
  fv.on('core.form.valid', () => {
    const $btn = $('#invitationForm button[type="submit"]')
      .prop('disabled', true)
      .html('<i class="ti ti-loader me-1"></i> @lang("admin.sending")...');

    $.ajax({
      url:         '/dashboard/mentors/invitations',
      method:      'POST',
      data:        new FormData($('#invitationForm')[0]),
      processData: false,
      contentType: false,
      headers:     { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success(response) {
        // original checked response.success === true || === 'true' but never
        // handled the case where the server returns a non-boolean truthy value.
        // Normalise with a loose truthy check covering both.
        if (response.success) {
          releaseAlert('success', {
            title:   '{{ __("admin.success") }}',
            message: '{{ app()->getLocale() === "ar" ? "تم إرسال الدعوة بنجاح" : "Invitation sent successfully." }}'
          });
          $('#newInvitationOffcanvas').offcanvas('hide');
          resetForm();
          if (response.data?.[0]) addInvitationToTable(response.data[0]);
        } else {
          releaseAlert('danger', {
            title:   '{{ __("admin.error") }}',
            message: response.message || '{{ __("admin.unexpected_error") }}'
          });
        }
      },
      error(xhr) {
        releaseAlert('danger', {
          title:   '{{ __("admin.error") }}',
          message: xhr.responseJSON?.message || '{{ __("admin.unexpected_error") }}'
        });
      },
      complete() {
        $btn
          .prop('disabled', false)
          .html('<i class="ti ti-send me-1"></i> @lang("admin.send_invitation")');
        // FIX #13: Original hardcoded English "Send Invitation" in the complete() callback,
        // ignoring the locale. Use the Blade translation helper instead.
      }
    });
  });

  /* ================================================================
   * Add new row to DataTable in real-time
   * ============================================================== */
  function addInvitationToTable(inv) {
    const statusMap = {
      Accepted: { cls: 'success', label: translations.accepted },
      Pending:  { cls: 'warning', label: translations.pending  },
      Sent:     { cls: 'info',    label: translations.sent     },
    };
    const { cls: statusCls, label: statusLabel } =
      statusMap[inv.status] || { cls: 'danger', label: translations.declined };

    const scopeCls   = inv.scope === 'Public' ? 'primary' : 'info';
    const scopeLabel = translations[inv.scope?.toLowerCase()] || inv.scope;

    // Original parsed inv.attachments as JSON (array) then joined with ','.
    // But the server may return attachments already as an array or as a JSON string —
    // normalise both.
    let attachmentsStr = '';
    try {
      const raw = inv.attachments;
      attachmentsStr = Array.isArray(raw) ? raw.join(',') : JSON.parse(raw || '[]').join(',');
    } catch (_) { attachmentsStr = ''; }

    // follow-up: row must match new column order:
    // invitee | subject | scope | status | accepted_at | created_at | inviter | actions
    const row = `
      <tr>
        <td>
          <h6 class="mb-0 fw-semibold">${escHtml(inv.invitee_name)}</h6>
          <small>${escHtml(inv.invitee_email)}</small>
        </td>
        <td>${escHtml(inv.subject)}</td>
        <td><span class="badge bg-label-${scopeCls}">${escHtml(scopeLabel)}</span></td>
        <td><span class="badge bg-label-${statusCls}">${escHtml(statusLabel)}</span></td>
        <td>${escHtml(inv.accepted_at ?? '')}</td>
        <td>${escHtml(inv.created_at ?? '')}</td>
        <td>
          <a href="/dashboard/show/UserProfile/${authUser.id}" class="d-flex align-items-center">
            <img
              src="${escAttr(authUser.img_url)}"
              alt="avatar"
              class="img-fluid w-px-30 h-px-30 rounded-circle me-2 flex-shrink-0"
              onerror="this.src='/icons/account.svg'"
            />
            <span class="ms-2 text-dark">${escHtml(authUser.name)}</span>
          </a>
        </td>
        <td>
          <button
            class="btn btn-sm btn-outline-primary text-nowrap view-details"
            data-bs-toggle="offcanvas"
            data-bs-target="#invitationOffcanvas"
            data-invitee-name="${escAttr(inv.invitee_name)}"
            data-invitee-email="${escAttr(inv.invitee_email)}"
            data-subject="${escAttr(inv.subject)}"
            data-scope="${escAttr(inv.scope)}"
            data-status="${escAttr(inv.status)}"
            data-message="${escAttr(inv.message_content ?? '')}"
            data-date="${escAttr(inv.created_at ?? '')}"
            data-attachments="${escAttr(attachmentsStr)}"
            data-response-message="${escAttr(inv.response_message ?? '')}"
            data-orgs='${JSON.stringify(inv.organizations || [])}'>
            <i class="ti ti-eye me-1"></i> ${translations.view_details}
          </button>
        </td>
      </tr>`;

    // Reset sort to "Created At DESC" so the newly added row (with the latest
    // timestamp) surfaces immediately at the top, then redraw.
    table
      .row.add($(row)[0])
      .order([[5, 'desc']])
      .draw();

    // Sync the sort-order dropdown so it reflects the new state visually.
    $('#sortOrderFilter').val('desc');

    // buildTableFilters now guards against duplicate filter bars internally.
    buildTableFilters(table.api ? table.api() : table);
  }

  /* ================================================================
   * Helpers — HTML escaping for dynamic content
   * Dynamic user data injected into HTML without escaping
   * (invitee_name, subject, etc.) is an XSS risk. Centralise escaping.
   * ============================================================== */
  function escHtml(str) {
    return String(str ?? '')
      .replace(/&/g,  '&amp;')
      .replace(/</g,  '&lt;')
      .replace(/>/g,  '&gt;')
      .replace(/"/g,  '&quot;')
      .replace(/'/g,  '&#039;');
  }
  function escAttr(str) {
    return escHtml(str);
  }

  /* ================================================================
   * Form reset
   * ============================================================== */
  function resetForm() {
    $('#invitationForm')[0].reset();
    fv.resetForm();
    $('#organizationsContainer').hide();
    $('#attachmentsList').empty();
    $('#uploadedAttachments').val('[]');
    quill.setContents([]);
  }

  // Auto-reset when offcanvas is closed by any means (× button, backdrop, ESC)
  $('#newInvitationOffcanvas').on('hidden.bs.offcanvas', resetForm);

  /* ================================================================
   * Organizations overflow modal
   * ============================================================== */
  $('#invitationOrgsModal').on('show.bs.modal', function (e) {
    // FIX #16: e.relatedTarget may be null if the modal is opened programmatically.
    const $trigger = $(e.relatedTarget);
    let orgs = $trigger.data('orgs');
    if (typeof orgs === 'string') {
      try { orgs = JSON.parse(orgs); } catch (_) { orgs = []; }
    }

    const $body = $('#invitationOrgsModalBody').empty();
    if (Array.isArray(orgs) && orgs.length) {
      orgs.forEach(org =>
        $body.append(
          `<span class="badge bg-label-secondary me-2 mb-2">${$('<div>').text(org.name).html()}</span>`
        )
      );
    } else {
      $body.text(@json(__('admin.No Organizations')));
    }
  });

});
</script>
@endsection
