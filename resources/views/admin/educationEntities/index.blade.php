@extends('layouts.admin')

{{-- GV-10436 (ISF): Entity options for the founder's Education Status. --}}
@section('title', __('admin.education_entities'))
@section('subTitle', __('admin.education_entities_list'))

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/form-validation/form-validation.css" />
@stop

@php
  $canSave = $_user->ableToAny(['education-entities.store', 'education-entities.update'], $permissions);
  $canDelete = $_user->ableTo('education-entities.destroy', $permissions);
@endphp

@section('content')
<div class="card">
  <div class="card-header border-bottom">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-3 mb-md-0">{{ __('admin.education_entities_list') }}</h5>
      @if ($_user->ableTo('education-entities.store', $permissions))
        <button class="btn btn-primary" id="createBtn">
          <i class="ti ti-plus me-2"></i>{{ __('admin.Add_Education_Entity') }}
        </button>
      @endif
    </div>
  </div>

  <div class="table-responsive card-datatable">
    <table id="education-entities-datatable" class="table datatables-ajax">
      <thead>
        <tr>
          <th>{{ __('admin.education_entity_type') }}</th>
          <th>{{ __('admin.name_en') }}</th>
          <th>{{ __('admin.name_ar') }}</th>
          <th>{{ __('admin.updated_at') }}</th>
          <th>{{ __('admin.Actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($entities as $entity)
          <tr id="row_{{ $entity->id }}">
            <td>{{ __('admin.education_entity_type_' . $entity->type) }}</td>
            <td>{{ $entity->name_en }}</td>
            <td>{{ $entity->name_ar }}</td>
            <td>{{ $entity->updated_at->format('Y-m-d H:i') }}</td>
            <td>
              <div class="d-flex align-items-center gap-1">
                @if ($canSave)
                  <button class="btn btn-icon btn-text-info rounded-pill editAction"
                    data-id="{{ $entity->id }}" data-type="{{ $entity->type }}"
                    data-name-en="{{ $entity->name_en }}" data-name-ar="{{ $entity->name_ar }}">
                    <i class="ti ti-edit"></i>
                  </button>
                @endif
                @if ($canDelete)
                  <button class="btn btn-icon btn-text-danger rounded-pill deleteAction" data-id="{{ $entity->id }}">
                    <i class="ti ti-trash"></i>
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

<div class="modal fade" id="entityModal" tabindex="-1" aria-labelledby="entityModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="entityModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="entityForm" method="POST" novalidate>
        @csrf
        <input type="hidden" id="form_method" name="_method" value="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label for="type" class="form-label">{{ __('admin.education_entity_type') }} <span class="text-danger">*</span></label>
            <select class="form-select" id="type" name="type" required>
              <option value="">{{ __('admin.Select_Education_Entity_Type') }}</option>
              @foreach ($types as $type)
                <option value="{{ $type }}">{{ __('admin.education_entity_type_' . $type) }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="name_en" class="form-label">{{ __('admin.name_en') }} <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name_en" name="name_en" maxlength="191" required>
          </div>
          <div class="mb-3">
            <label for="name_ar" class="form-label">{{ __('admin.name_ar') }} <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name_ar" name="name_ar" maxlength="191" dir="rtl" required>
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
<script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
@stop

@section('pagescripts')
<script>
$(function () {
  const baseUrl = @json(route('education-entities.index'));
  const canSave = @json($canSave);
  const canDelete = @json($canDelete);
  const table = $('#education-entities-datatable').DataTable({
    ...getSharedDataTableConfig({ descIndex: 3, actionsIndex: 4 }),
  });
  const modal = new bootstrap.Modal(document.getElementById('entityModal'));
  const $form = $('#entityForm');
  let editingId = null;

  // Entity names are typed by admins, so everything put back into the table is escaped.
  const esc = value => $('<div>').text(value ?? '').html();

  function actionsHtml(d) {
    return '<div class="d-flex align-items-center gap-1">'
      + (canSave ? '<button class="btn btn-icon btn-text-info rounded-pill editAction" data-id="' + esc(d.id) + '" data-type="' + esc(d.type)
        + '" data-name-en="' + esc(d.name_en) + '" data-name-ar="' + esc(d.name_ar) + '"><i class="ti ti-edit"></i></button>' : '')
      + (canDelete ? '<button class="btn btn-icon btn-text-danger rounded-pill deleteAction" data-id="' + esc(d.id) + '"><i class="ti ti-trash"></i></button>' : '')
      + '</div>';
  }

  function rowCells(d) {
    return [esc(d.type_label), esc(d.name_en), esc(d.name_ar), esc(d.updated_at), actionsHtml(d)];
  }

  const fv = FormValidation.formValidation($form[0], {
    fields: {
      type: { validators: { notEmpty: { message: @json(__('admin.education_entity_type_required')) } } },
      name_en: { validators: { notEmpty: { message: @json(__('admin.education_entity_name_en_required')) }, stringLength: { max: 191 } } },
      name_ar: { validators: { notEmpty: { message: @json(__('admin.education_entity_name_ar_required')) }, stringLength: { max: 191 } } },
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({ rowSelector: '.mb-3' }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus(),
    },
  });

  function openModal(title, id, values) {
    $form[0].reset();
    fv.resetForm(true);
    editingId = id;
    $('#form_method').val(id ? 'PUT' : 'POST');
    $('#type').val(values.type || '');
    $('#name_en').val(values.name_en || '');
    $('#name_ar').val(values.name_ar || '');
    $('#entityModalLabel').text(title);
    modal.show();
  }

  function fail(xhr) {
    releaseAlert('danger', {
      title: @json(__('admin.Error')),
      message: xhr?.responseJSON?.message || @json(__('admin.unexpected_error')),
      hideCancel: true,
    });
  }

  $('#createBtn').on('click', () => openModal(@json(__('admin.Add_Education_Entity')), null, {}));

  $(document).on('click', '.editAction', function () {
    const $b = $(this);
    openModal(@json(__('admin.Edit_Education_Entity')), $b.data('id'), {
      type: $b.data('type'), name_en: $b.data('name-en'), name_ar: $b.data('name-ar'),
    });
  });

  fv.on('core.form.valid', function () {
    const url = editingId ? baseUrl + '/' + editingId : baseUrl;
    $.ajax({ url, method: 'POST', data: $form.serialize(), headers: { Accept: 'application/json' } })
      .done(function (response) {
        modal.hide();
        const d = response.data;
        const row = table.row('#row_' + d.id);
        if (row.node()) {
          row.data(rowCells(d)).draw(false);
        } else {
          $(table.row.add(rowCells(d)).draw(false).node()).attr('id', 'row_' + d.id);
        }
        releaseAlert('success', { title: @json(__('admin.Success')), message: response.message, hideCancel: true });
      })
      .fail(fail);
  });

  $(document).on('click', '.deleteAction', function () {
    const id = $(this).data('id');
    releaseAlert('warning', {
      title: @json(__('admin.Warning')),
      message: @json(__('admin.SureDelete')),
      confirmColor: 'danger',
      confirmText: @json(__('admin.DYes')),
      cancelText: @json(__('admin.No')),
      onConfirm: () => {
        $.ajax({
          url: baseUrl + '/' + id,
          method: 'POST',
          data: { _method: 'DELETE', _token: @json(csrf_token()) },
          headers: { Accept: 'application/json' },
        })
          .done(function (response) {
            table.row('#row_' + id).remove().draw(false);
            releaseAlert('success', { title: @json(__('admin.Deleted')), message: response.message, hideCancel: true });
          })
          .fail(fail);
      },
    });
  });
});
</script>
@stop
