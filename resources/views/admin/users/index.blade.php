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
          <tbody>
            @foreach ($users as $user)
            <tr id="row_{{ $user->id }}">
              <td>
                <a href="{{ route('users.edit', ['user' => $user->id]) }}">{{ $user->id }}</a>
              </td>
              <td>
                <a href="{{ route('users.edit', ['user' => $user->id]) }}">
                  <small>{{ $user->{'first_name_' . $l} }} {{ $user->{'last_name_' . $l} }}</small>
                </a>
              </td>
              <td style="max-width: 150px;"><small>{{ $user->email }}</small></td>
              <td><small>{{ $user->phone }}</small></td>
              <td
                data-search="{{ $user->gender }}"
                data-sort="{{ $user->gender }}"
              >
                <small>{{ trim($user->gender) ? __('admin.' . $user->gender) : '-' }}</small>
              </td>
              @php
                $roleName = $user->roles->first()?->name ?? 'No Role Assigned';
                $label = $user->email_verified_at ? $roleName : 'Not Verified';

                // color palette from vuexy/core.css uses --bs-primary etc.
                // map each known role to a distinct badge color; add new roles here as needed
                $roleColors = [
                    'admin'            => 'primary',
                    'organizationManager'          => 'dark',
                    'mentor'           => 'info',
                    'registered'          => 'warning',
                    'judge'         => 'danger',
                    'serviceProvider'             => 'success',
                    'No Role Assigned' => 'light',
                    // special states below
                    'Not Verified'     => 'secondary',
                ];

                $color = $roleColors[$label] ?? 'secondary';
              @endphp
              <td data-search="{{ $label }}">
                <small>
                  <span class="badge bg-label-{{ $color }}">
                    {{ __('admin.' . $label) }}
                  </span>
                </small>
              </td>
              <td data-search="{{ $user->company ? 'Yes' : 'No' }}">
                <small>{{ $user->company ? __('admin.Yes') : __('admin.No') }}</small>
              </td>
              <td style="max-width: 150px;"><small>{{ $user->created_at }}</small></td>
              <td>
                <div class="d-flex align-items-center gap-1">
                  @if($_user->ableTo('users.login_as', $permissions))
                    <a
                      href="{{ route('users.login_as', ['id' => $user->id]) }}"
                      title="{{ __('admin.Login as User') }}"
                      class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill confirm-login-as"
                    >
                      <i class="fa-solid fa-user-secret fs-5"></i>
                    </a>
                  @endif
                  <button
                    class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill send-message-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#MessageModal"
                    data-user-id="{{ $user->id }}"
                    data-user-name="{{ $user->{'first_name_' . $l} }} {{ $user->{'last_name_' . $l} }}"
                  >
                    <i class="ti ti-message ti-md"></i>
                  </button>
                  @if($_user->ableTo('users.destroy', $permissions))
                    <button
                      id="deleteAction"
                      data-id="{{ $user->id }}"
                      data-url="{{ route('users.destroy', ['user' => $user->id]) }}"
                      type="button"
                      class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                    >
                      <i class="ti ti-trash ti-md"></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
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
<script>
$(function () {

  /* ================================================================
   * DataTable — custom sort type (role priority)
   * ============================================================== */
  $.fn.dataTable.ext.type.order['role-priority-pre'] = function (data) {
    const div = document.createElement('div');
    div.innerHTML = data;
    const s = div.textContent.toLowerCase().trim();
    if (s.includes('not verified'))  return 4;
    if (s.includes('admin'))         return 1;
    if (s.includes('no role'))       return 3;
    return 2;
  };

  /* ================================================================
   * DataTable init
   * ============================================================== */
  const table = $('#usersTable').DataTable({
    ...getSharedDataTableConfig({ title: 'Users', descIndex: 7, actionsIndex: 8 }),
    columnDefs: [
      {
        // Read sort/filter value from data-search attribute for
        // Gender (4), Role (5), HaveCompany (6) columns so the
        // DataTable works with the raw English value, not the
        // translated display text.
        targets: [4, 5, 6],
        render(data, type, row, meta) {
          if (type === 'filter' || type === 'sort') {
            // The <td> has a data-search attribute with the raw value.
            // DataTables passes the HTML string as `data` when orthogonal
            // data is not configured via columns.data — grab from the DOM.
            const cell = table
              ? table.cell(meta.row, meta.col).node()
              : null;
            if (cell) {
              return cell.getAttribute('data-search') || data;
            }
          }
          return data;
        }
      },
      { targets: 5, type: 'role-priority' },
      { targets: [8], orderable: false, searchable: false }
    ],
    dom:
      '<"row"' +
      '<"col-12 col-lg-9 d-flex flex-wrap align-items-center ps-0 ps-lg-6 pb-6 pb-lg-0 mb-2"l>' +
      '<"col-12 col-lg-3 dt-action-buttons d-flex justify-content-lg-end pe-0 pe-lg-6"f>' +
      '>t<"row px-6"<"col-lg-6"i><"col-lg-6 d-flex justify-content-end"p>>',
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

      @if (request('role') === 'registered')
        $('#filterRole').val('registered').trigger('change');
      @endif
    }
  });

  /* ================================================================
   * Column filters — built with DataTable column().search() API
   * identical pattern to Template A (invitations)
   * ============================================================== */
  function buildTableFilters(api) {
    if ($('#filterHaveCompany').length) return; // guard against duplicate build

    const container = $('<div class="d-flex gap-3 ms-3"></div>').insertAfter('.dataTables_length');

    // Helper: create a <select> filter wired to a column
    function addColumnFilter(colIdx, id, defaultLabel, useExactMatch) {
      const select = $(
        `<select id="${id}" class="form-select" style="min-width:150px">` +
        `<option value="">${defaultLabel}</option></select>`
      )
        .appendTo(container)
        .on('change', function () {
          const val = $(this).val();
          if (useExactMatch && val !== '') {
            api.column(colIdx)
               .search('^' + $.fn.dataTable.util.escapeRegex(val) + '$', true, false)
               .draw();
          } else {
            api.column(colIdx).search(val, false, false).draw();
          }
        });

      return select;
    }

    // HaveCompany filter (col 6) — exact match on raw data-search value
    const companyFilter = addColumnFilter(6, 'filterHaveCompany', '{{ __("admin.HaveCompany") }}', true);
    companyFilter.append('<option value="Yes">{{ __("admin.Yes") }}</option>');
    companyFilter.append('<option value="No">{{ __("admin.No") }}</option>');

    // Role filter (col 5) — partial match sufficient
    const roleFilter = addColumnFilter(5, 'filterRole', '{{ __("admin.All_Roles") }}', false);
    @foreach (App\Role::get() as $role)
      roleFilter.append(`<option value="{{ $role->name }}">{{ __('admin.' . $role->name) }}</option>`);
    @endforeach
    roleFilter.append('<option value="Not Verified">{{ __("admin.NotVerified") }}</option>');

    // Gender filter (col 4) — exact match
    const genderFilter = addColumnFilter(4, 'filterGender', '{{ __("admin.All_Genders") }}', true);
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
   * Export buttons
   * ============================================================== */
  $('#exportCsv').on('click', () => table.button('.buttons-csv').trigger());
  $('#exportExcel').on('click', () => table.button('.buttons-excel').trigger());

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
          table.row('#row_' + id).remove().draw();
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
