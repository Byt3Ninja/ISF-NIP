@extends('layouts.admin')

@section('title', __('admin.Users'))
@section('subTitle', __('admin.Users`s List'))

@section('content')

{{-- Flash Messages --}}
@if (session()->has('msg'))
  <x-alert type="success" message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" />
@endif
@if (session()->has('message2'))
  <x-alert type="danger" message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" />
@endif

{{-- Users Table --}}
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header border-bottom">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
          <h5 class="card-title mb-3 mb-md-0">{{ __('admin.Users`s List') }}</h5>
          <div class="d-flex align-items-center gap-3">
            <x-export-dropdown />

            <a href="{{ route('users.create') }}" class="btn btn-primary">
              <i class="ti ti-plus me-1"></i>{{ __('admin.Add New') }}
            </a>
          </div>
        </div>
      </div>

      <div class="card-datatable table-responsive">
        <table id="usersTable" class="table">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>{{ __('admin.Name') }}</th>
              <th>{{ __('admin.Email') }}</th>
              <th>{{ __('admin.Phone') }}</th>
              <th>{{ __('admin.Gender') }}</th>
              <th>{{ __('admin.Role') }}</th>
              <th>{{ __('admin.HaveCompany') }}</th>
              <th>{{ __('admin.CreatedAt') }}</th>
              <th>{{ __('admin.Actions') }}</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Send Message Modal --}}
<div class="modal fade" id="MessageModal" tabindex="-1" role="dialog" aria-labelledby="MessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="MessageModalLabel">
          {{ __('messages.SendMessageTo') }} <span id="messageReceiverName"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="/dashboard/send_message" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="sender" value="{{ Auth::user()->id }}">
        <input type="hidden" name="receiver" id="messageReceiverId" value="">
        <div class="modal-body">
          <div class="mb-4">
            <label class="form-label">{{ __('messages.Subject') }}:</label>
            <input
              name="subject"
              type="text"
              placeholder="{{ __('messages.Subject') }}"
              class="form-control"
              required
            >
          </div>
          <div class="mb-4">
            <label class="form-label">{{ __('messages.Message') }}:</label>
            <textarea
              class="form-control"
              name="message"
              rows="8"
              placeholder="{{ __('messages.Write your message') }}"
              required
            ></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            {{ __('messages.Close') }}
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-send me-2"></i>{{ __('messages.Send') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('pagescripts')
@php
  $roleLabels = collect($roles)->push('Not Verified', 'No Role Assigned')
    ->mapWithKeys(fn ($role) => [$role => __('admin.' . $role)]);
@endphp
<script>
$(function () {

  /* ================================================================
   * Rows come from users.index page by page (DataTables server-side):
   * the search box, column filters and sort run on the server over
   * every user, and only the visible page is loaded.
   * ============================================================== */
  const initialRole = @json($initialRole);
  const labels = {
    roles: @json($roleLabels),
    genders: { Male: @json(__('admin.Male')), Female: @json(__('admin.Female')) },
    yes: @json(__('admin.Yes')),
    no: @json(__('admin.No')),
  };
  const can = {
    loginAs: @json($_user->ableTo('users.login_as', $permissions)),
    edit: @json($_user->ableTo('users.edit', $permissions)),
    destroy: @json($_user->ableTo('users.destroy', $permissions)),
  };
  const urls = {
    edit: @json(route('users.edit', ['user' => '__ID__'])),
    loginAs: @json(route('users.login_as', ['id' => '__ID__'])),
    destroy: @json(route('users.destroy', ['user' => '__ID__'])),
  };
  const urlFor = (name, id) => urls[name].replace('__ID__', encodeURIComponent(id));
  const esc = value => String(value ?? '').replace(/[&<>"']/g, c => (
    { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
  ));

  // map each known role to a distinct badge color; add new roles here as needed.
  // Keys are lowercase: role names are stored as "Admin", "Registered", "Judger", ...
  const roleColors = {
    'admin': 'primary',
    'organizationmanager': 'dark',
    'mentor': 'info',
    'registered': 'warning',
    'judger': 'danger',
    'judge': 'danger',
    'serviceprovider': 'success',
    'no role assigned': 'light',
    'not verified': 'secondary',
  };
  const roleColor = role => roleColors[String(role ?? '').toLowerCase()] ?? 'secondary';

  const small = value => `<small>${esc(value)}</small>`;
  const narrow = td => { td.style.maxWidth = '150px'; };

  const table = $('#usersTable').DataTable({
    ...getSharedDataTableConfig({ title: 'Users', descIndex: 7, actionsIndex: 8 }),
    processing: true,
    serverSide: true,
    searchDelay: 400,
    ajax: { url: @json(route('users.index')) },
    // The ?role= / ?r= preselection is part of the very first request.
    searchCols: Array.from({ length: 9 }, (_, i) => (i === 5 && initialRole ? { search: initialRole } : null)),
    columns: [
      { data: 'id', render: (id, type) => type === 'display' ? `<a href="${urlFor('edit', id)}">${id}</a>` : id },
      { data: 'name', render: (name, type, row) => type === 'display' ? `<a href="${urlFor('edit', row.id)}">${small(name)}</a>` : name },
      { data: 'email', render: small, createdCell: narrow },
      { data: 'phone', render: small },
      { data: 'gender', render: gender => small(gender ? (labels.genders[gender] ?? gender) : '-') },
      {
        data: 'role',
        render: role => `<small><span class="badge bg-label-${roleColor(role)}">${esc(labels.roles[role] ?? role)}</span></small>`,
      },
      { data: 'has_company', render: has => small(has ? labels.yes : labels.no) },
      { data: 'created_at', render: small, createdCell: narrow },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: (_, type, row) => {
          const id = row.id;
          const name = esc(row.name);
          return '<div class="d-flex align-items-center gap-1">'
            + (can.loginAs
              ? `<a href="${urlFor('loginAs', id)}" title="{{ __('admin.Login as User') }}"
                   class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill confirm-login-as">
                   <i class="fa-solid fa-user-secret fs-5"></i></a>`
              : '')
            + (can.edit
              ? `<a href="${urlFor('edit', id)}" title="{{ __('admin.Edit') }}"
                   class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill">
                   <i class="ti ti-edit ti-md"></i></a>`
              : '')
            + `<button class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill send-message-btn"
                 data-bs-toggle="modal" data-bs-target="#MessageModal" data-user-id="${id}" data-user-name="${name}">
                 <i class="ti ti-message ti-md"></i></button>`
            + (can.destroy
              ? `<button id="deleteAction" data-id="${id}" data-url="${urlFor('destroy', id)}" type="button"
                   class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill">
                   <i class="ti ti-trash ti-md"></i></button>`
              : '')
            + '</div>';
        },
      },
    ],
    columnDefs: [],
    dom:
      '<"row"' +
      '<"col-12 col-lg-9 d-flex flex-wrap align-items-center ps-0 ps-lg-6 pb-6 pb-lg-0 mb-2"l>' +
      '<"col-12 col-lg-3 dt-action-buttons d-flex justify-content-lg-end pe-0 pe-lg-6"f>' +
      '>t<"row px-6"<"col-lg-6"i><"col-lg-6 d-flex justify-content-end"p>>',
    initComplete() {
      buildTableFilters(this.api());
      $('#filterRole').val(initialRole);
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

  /* ================================================================
   * Column filters: each sends its raw value as that column's search,
   * which the server applies to the whole user list.
   * ============================================================== */
  function buildTableFilters(api) {
    if ($('#filterHaveCompany').length) return; // guard against duplicate build

    const container = $('<div class="d-flex gap-3 ms-3"></div>').insertAfter('.dataTables_length');

    // Helper: create a <select> filter wired to a column
    function addColumnFilter(colIdx, id, defaultLabel) {
      return $(
        `<select id="${id}" class="form-select" style="min-width:150px">` +
        `<option value="">${defaultLabel}</option></select>`
      )
        .appendTo(container)
        .on('change', function () {
          api.column(colIdx).search($(this).val() || '').draw();
        });
    }

    // HaveCompany filter (col 6)
    const companyFilter = addColumnFilter(6, 'filterHaveCompany', '{{ __("admin.HaveCompany") }}');
    companyFilter.append('<option value="Yes">{{ __("admin.Yes") }}</option>');
    companyFilter.append('<option value="No">{{ __("admin.No") }}</option>');

    // Role filter (col 5)
    const roleFilter = addColumnFilter(5, 'filterRole', '{{ __("admin.All_Roles") }}');
    @foreach ($roles as $roleName)
      roleFilter.append(`<option value="{{ $roleName }}">{{ __('admin.' . $roleName) }}</option>`);
    @endforeach
    roleFilter.append('<option value="Not Verified">{{ __("admin.NotVerified") }}</option>');

    // Gender filter (col 4)
    const genderFilter = addColumnFilter(4, 'filterGender', '{{ __("admin.All_Genders") }}');
    genderFilter.append('<option value="Male">{{ __("admin.Male") }}</option>');
    genderFilter.append('<option value="Female">{{ __("admin.Female") }}</option>');

    // Created At sort order (mirrors Template A pattern)
    $(`<select id="sortOrderFilter" class="form-select" style="min-width:150px">
        <option value="desc">{{ __("admin.newest_first") }}</option>
        <option value="asc">{{ __("admin.oldest_first") }}</option>
      </select>`)
      .appendTo(container)
      .on('change', function () {
        api.order([[7, $(this).val()]]).draw();
      });
  }

  /* ================================================================
   * Export: the table only holds one page, so load every row that
   * matches the current search/filters, export it, then restore the
   * page size.
   * ============================================================== */
  function exportAll(button) {
    const pageLength = table.page.len();
    table.one('draw', function () {
      table.button(button).trigger();
      table.page.len(pageLength).draw(false);
    });
    table.page.len(-1).draw(false);
  }
  $('#exportCsv').on('click', () => exportAll('.buttons-csv'));
  $('#exportExcel').on('click', () => exportAll('.buttons-excel'));

  /* ================================================================
   * Send Message Modal — populate receiver dynamically
   * ============================================================== */
  $(document).on('click', '.send-message-btn', function () {
    const userId   = $(this).data('user-id');
    const userName = $(this).data('user-name');
    $('#messageReceiverId').val(userId);
    $('#messageReceiverName').text(userName);
  });

  // Clear modal fields on hide
  $('#MessageModal').on('hidden.bs.modal', function () {
    $(this).find('input[name="subject"], textarea[name="message"]').val('');
    $('#messageReceiverId').val('');
    $('#messageReceiverName').text('');
  });

  /* ================================================================
   * Delete action
   * ============================================================== */
  $(document).on('click', '#deleteAction', function (e) {
    e.preventDefault();
    const url   = $(this).data('url');
    const id    = $(this).data('id');
    const token = '{{ csrf_token() }}';

    releaseAlert('warning', {
      title: '{{ __("admin.SureDelete") }}',
      message: '{{ __("admin.Recover") }}',
      confirmText: '{{ __("admin.DYes") }}',
      cancelText: '{{ __("admin.No") }}',
      confirmColor: 'danger'
    }).then(ok => {
      if (!ok) return;

      $.post(url, { _method: 'DELETE', _token: token })
        .done(() => {
          table.draw(false);
          releaseAlert('success', {
            title:   '{{ __("admin.Deleted") }}!',
            message: '{{ __("admin.DeletedSuccessfully") }}.',
          });
        })
        .fail(xhr => {
          releaseAlert('danger', {
            title:   '{{ __("admin.Error") }}!',
            message: xhr.responseJSON?.message
                     || '{{ __("admin.An unexpected error occurred, please try again later") }}',
          });
        });
    });
  });

  $(document).on('click', '.confirm-login-as', function (e) {
    e.preventDefault();

    let url = $(this).attr('href');

    releaseAlert('warning', {
      title: "{{ __('admin.Confirm Action') }}",
      message: "{{ __('admin.start_imperonation_message') }}",
      confirmText: "{{ __('admin.Yes') }}",
      cancelText: "{{ __('admin.cancel') }}",
      onConfirm: function() {
        window.location.href = url;
      }
    });
  });
});
</script>
@endsection
