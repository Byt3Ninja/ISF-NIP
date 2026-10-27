@extends('layouts.admin')

@section('title', __('admin.Organization Types'))
@section('subTitle', __('admin.Organization Types`s List'))

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
<style>
  .dataTables_wrapper .dataTables_filter,
  .dataTables_wrapper .dataTables_length {
    margin-bottom: 0 !important;
  }
</style>
@stop

@section('style')
<style>
  .dataTables_wrapper .dataTables_filter,
  .dataTables_wrapper .dataTables_length {
    margin-bottom: 0 !important;
  }
</style>
@stop

@section('content')
@if (session()->has('msg'))
  <x-alert
    type="success"
    message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}"
  />
@endif
@if (session()->has('message2'))
  <x-alert
    type="danger"
    message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}"
  />
@endif

<div class="card">
  <div class="card-header border-bottom">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-3 mb-md-0">
        {{ __('admin.Organization Types`s List') }}
      </h5>
      <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
          <button
            class="btn btn-label-secondary dropdown-toggle"
            data-bs-toggle="dropdown"
          >
            <i class="ti ti-file-export me-2"></i>
            {{ __('admin.Export') }}
          </button>

          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item" id="exportExcel">
                <i class="ti ti-file-spreadsheet me-2"></i>
                Excel
              </a>
            </li>
            <li>
              <a class="dropdown-item" id="exportCsv">
                <i class="ti ti-file-text me-2"></i>
                CSV
              </a>
            </li>
          </ul>
        </div>

        @if ($_user->ableTo('organization-types.create', $permissions))
          <button
            class="btn btn-primary"
            id="createBtn"
          >
            <i class="ti ti-plus me-2"></i>
            {{ __('admin.Add_New') }}
          </button>
        @endif
      </div>
    </div>
  </div>

  <div class="table-responsive card-datatable">
    <table id="organization-types-datatable" class="table datatables-ajax">
      <thead>
        <tr class="table-light">
          <th style="width: 50px">#</th>
          <th>{{ __('admin.name_en') }}</th>
          <th>{{ __('admin.name_ar') }}</th>
          <th>{{ __('admin.Created At') }}</th>
          <th>{{ __('admin.updated_at') }}</th>
          <th>{{ __('admin.Actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($organizationTypes as $index => $orgType)
          <tr id="row_{{ $orgType->id }}" data-id="{{ $orgType->id }}">
            <td>{{ $index + 1 }}</td>

            <td>
              <span class="title-en">{{ $orgType->title_en }}</span>
            </td>

            <td>
              <span class="title-ar">{{ $orgType->title_ar }}</span>
            </td>

            <td>
              <span class="created-at">{{ $orgType->created_at->format('Y-m-d H:i') }}</span>
            </td>

            <td>
              <span class="updated-at">{{ $orgType->updated_at->format('Y-m-d H:i') }}</span>
            </td>

            <td style="max-width: 100px;">
              <div class="d-flex align-items-center gap-1">
                @if ($_user->ableToAny(['organization-types.edit', 'organization-types.update'], $permissions))
                  <button
                    class="btn btn-sm btn-text-secondary waves-effect waves-light editAction"
                    data-id="{{ $orgType->id }}"
                    data-title-en="{{ $orgType->title_en }}"
                    data-title-ar="{{ $orgType->title_ar }}"
                    data-created-at="{{ $orgType->created_at->format('Y-m-d H:i') }}"
                    data-updated-at="{{ $orgType->updated_at->format('Y-m-d H:i') }}"
                    data-url="{{ route('organization-types.update', ['organization_type' => $orgType->id]) }}"
                  >
                    <i class="ti ti-pencil ti-md"></i>
                  </button>
                @endif

                @if ($_user->ableTo('organization-types.destroy', $permissions))
                  <button
                    class="btn btn-sm btn-text-secondary waves-effect waves-light deleteAction"
                    data-id="{{ $orgType->id }}"
                    data-url="{{ route('organization-types.destroy', ['organization_type' => $orgType->id]) }}"
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

<!-- Unified Create/Edit Modal -->
<div class="modal fade" id="orgTypeModal" tabindex="-1" aria-labelledby="orgTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orgTypeModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="orgTypeForm" method="POST">
        @csrf
        <input type="hidden" id="form_method" name="_method" value="POST">
        <input type="hidden" id="org_type_id" name="id">
        <div class="modal-body">
          <div class="mb-3">
            <label for="title_en" class="form-label">{{ __('admin.name_en') }}</label>
            <input type="text" class="form-control" id="title_en" name="title_en" placeholder="{{ __('admin.name_en_placeholder') }}" required>
          </div>
          <div class="mb-3">
            <label for="title_ar" class="form-label">{{ __('admin.name_ar') }}</label>
            <input type="text" class="form-control" id="title_ar" name="title_ar" placeholder="{{ __('admin.name_ar_placeholder') }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
@stop

@section('pagescripts')
<script>
$(function () {
  /* ========================
  DATATABLE INITIALIZATION
  ======================== */

  const table = $('#organization-types-datatable').DataTable({
    ...getSharedDataTableConfig({ descIndex: 3, actionsIndex: 5, withoutFilter: true }),
    columns: [
      { data: 'index' },
      { data: 'title_en' },
      { data: 'title_ar' },
      { data: 'created_at' },
      { data: 'updated_at' },
      { data: 'actions', orderable: false, searchable: false }
    ],
  });

  // Handle export buttons
  $('#exportCsv').on('click', function() {
    table.button('.buttons-csv').trigger();
  });

  $('#exportExcel').on('click', function() {
    table.button('.buttons-excel').trigger();
  });

  const modal = new bootstrap.Modal(document.getElementById('orgTypeModal'));
  let isEditMode = false;

  /* ========================
  FORM VALIDATION
  ======================== */

  const fv = FormValidation.formValidation(
    document.getElementById('orgTypeForm'), {
      fields: {
        title_en: {
          validators: {
            notEmpty: {
              message: "{{ __('admin.English Name is required') }}"
            },
            stringLength: {
              max: 255,
              message: "{{ __('admin.max_255_chars') }}"
            },
            regexp: {
              regexp: /^[A-Za-z\s]+$/,
              message: "{{ __('admin.must contain English letters only') }}"
            }
          }
        },
        title_ar: {
          validators: {
            notEmpty: {
              message: "{{ __('admin.Arabic Name is required') }}"
            },
            stringLength: {
              max: 255,
              message: "{{ __('admin.max_255_chars') }}"
            },
            regexp: {
              regexp: /^[\u0600-\u06FF\s]+$/,
              message: "{{ __('admin.must contain Arabic letters only') }}"
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
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    }
  );

  /* ========================
  ACTION BUTTON HTML
  ======================== */

  function buildActions(data) {
    return `
      <div class="d-flex align-items-center gap-1">

        @if ($_user->ableToAny(['organization-types.edit','organization-types.update'], $permissions))
        <button
          class="btn btn-icon btn-text-info rounded-pill editAction"
          data-id="${data.id}"
          data-title-en="${data.title_en}"
          data-title-ar="${data.title_ar}"
          data-created-at="${data.created_at}"
          data-updated-at="${data.updated_at}"
          data-url="{{ route('organization-types.index') }}/${data.id}">
          <i class="ti ti-edit"></i>
        </button>
        @endif

        @if ($_user->ableTo('organization-types.destroy', $permissions))
        <button
          class="btn btn-icon btn-text-danger rounded-pill deleteAction"
          data-id="${data.id}"
          data-url="{{ route('organization-types.index') }}/${data.id}">
          <i class="ti ti-trash"></i>
        </button>
        @endif

      </div>
    `;
  }

  /* ========================
  ADD ROW
  ======================== */

  function addRow(data) {
    table.row.add({
      id: data.id,
      index: 1,
      title_en: data.title_en,
      title_ar: data.title_ar,
      created_at: data.created_at,
      updated_at: data.updated_at,
      actions: buildActions(data)
    }).draw(false);
  }

  /* ========================
  UPDATE ROW
  ======================== */

  function updateRow(data) {
    const rowSelector = '#row_' + data.id;
    const row = table.row(rowSelector);

    if (!row.node()) return;

    const currentData = row.data();

    row.data({
      id: data.id,
      index: currentData.index,
      title_en: data.title_en,
      title_ar: data.title_ar,
      created_at: data.created_at,
      updated_at: data.updated_at,
      actions: buildActions(data)
    }).draw(false);
  }

  /* ========================
  RESET FORM
  ======================== */

  function resetForm() {
    $('#orgTypeForm')[0].reset();

    $('#org_type_id').val('');

    fv.resetForm(true);
  }

  /* ========================
  CREATE BUTTON
  ======================== */

  $('#createBtn').on('click', function () {
    isEditMode = false;

    resetForm();

    $('#orgTypeModalLabel').text("{{ __('admin.Add_New') }}");

    $('#orgTypeForm').attr('action', "{{ route('organization-types.store') }}");

    $('#form_method').val('POST');

    modal.show();
  });

  /* ========================
  EDIT BUTTON
  ======================== */

  $(document).on('click', '.editAction', function () {
    isEditMode = true;

    resetForm();

    $('#org_type_id').val($(this).data('id'));
    $('#title_en').val($(this).data('title-en'));
    $('#title_ar').val($(this).data('title-ar'));

    $('#orgTypeModalLabel').text("{{ __('admin.EditType') }}");

    $('#orgTypeForm').attr('action', $(this).data('url'));

    $('#form_method').val('PUT');

    modal.show();
  });

  /* ========================
  FORM SUBMIT
  ======================== */

  fv.on('core.form.valid', function() {
    const form = $('#orgTypeForm');

    $.post(form.attr('action'), form.serialize(), function (response) {

      modal.hide();

      if (isEditMode) {
        updateRow(response.data);
      } else {
        addRow(response.data);
      }

      releaseAlert('success', {
        title: "{{ __('admin.Success') }}",
        message: response.message ?? "{{ __('admin.SavedSuccessfully') }}"
      });

    }).fail(function (xhr) {

      const errMsg = xhr?.responseJSON?.message || xhr?.responseText;

      releaseAlert('danger', {
        title: "{{ __('admin.Error') }}",
        message: errMsg || "{{ __('admin.unexpected_error') }}"
      });
    });
  });

  /* ========================
  DELETE
  ======================== */

  $(document).on('click', '.deleteAction', function () {
    const id = $(this).data('id');
    const url = $(this).data('url');
    const button = $(this);

    releaseAlert('warning', {
      title: "{{ __('admin.Warning') }}",
      message: "{{ __('admin.SureDelete') }}",
      confirmColor: 'danger',
      confirmText: "{{ __('admin.DYes') }}",
      cancelText: "{{ __('admin.No') }}",
      onConfirm: () => handleDelete(button, id, url),
    });
  });

  function handleDelete(button, id, url) {
    const row = table.row(button.closest('tr'));

    $.post(url, {
      _method: 'DELETE',
      _token: '{{ csrf_token() }}'
    }, function (response) {

      row.remove().draw(false);

      releaseAlert('success', {
        title: "{{ __('admin.Deleted') }}",
        message: response.message ? response.message : "{{ __('admin.DeletedSuccessfully') }}"
      });

    }).fail(function (xhr) {

      const errMsg = xhr?.responseJSON?.message || xhr?.responseText;

      releaseAlert('danger', {
        title: "{{ __('admin.Error') }}",
        message: errMsg || "{{ __('admin.unexpected_error') }}",
      });
    });
  }

  /* ========================
  MODAL RESET
  ======================== */

  $('#orgTypeModal').on('hidden.bs.modal', function () {
    resetForm();
  });
});
</script>
@stop