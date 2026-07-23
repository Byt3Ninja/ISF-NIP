<!-- Forms Table -->
<div class="tab-pane fade {{ $activeTab == 'forms' ? 'show active' : '' }}" id="forms-pane" role="tabpanel">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center border-bottom">
      <h5 class="card-title mb-0">{{ __('forms.forms_list') }}</h5>
      <a href="/dashboard/show-builder" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i> {{ __('forms.add_new_form') }}
      </a>
    </div>
    <div class="card-datatable table-responsive">
      <table class="table datatables-basic" id="formsTable">
        <thead class="table-light">
          <tr>
            <th style="width: 60px;">{{ __('forms.form_id') }}</th>
            <th style="width:110px;">{{ __('forms.form_name') }}</th>
            <th>{{ __('forms.assigned_organization') }}</th>
            <th>{{ __('forms.submissions') }}</th>
            <th style="width:140px;">{{ __('forms.last_modified') }}</th>
            <th style="width:140px;">{{ __('forms.created_date') }}</th>
            <th style="width:110px; text-align:center;">{{ __('forms.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @if(!empty($data) && count($data))
            @foreach($data as $form)
              @php
                $form_id = $form->form_id ?? ($form->id ?? '');
                $title = $form->form_name ?? $form->name ?? '—';
                $assignedOrg = $form->assigned_organization ?? ($form->organization_name ?? '—');
                $submissions = $form->submissions ?? 0;
                $updated = (isset($form->last_modified) && $form->last_modified != 'null') ? \Carbon\Carbon::parse($form->last_modified)->format('Y-m-d') : '—';
                $created = (isset($form->created_date) && $form->created_date != 'null') ? \Carbon\Carbon::parse($form->created_date)->format('Y-m-d') : '—';
                $org_id = $form->org_id ?? '';
              @endphp
              <tr data-org="{{ $assignedOrg }}">
                <td>{{ $form_id }}</td>
                <td>{{ $title }}</td>
                <td>{{ $assignedOrg }}</td>
                <td>{{ $submissions }}</td>
                <td>{{ $updated }}</td>
                <td>{{ $created }}</td>
                <td>
                  <div class="d-flex flex-nowrap justify-content-center gap-1">
                    <a href="/dashboard/show-form/{{ $form_id }}" class="bbtn btn-icon btn-text-secondary waves-effect waves-light rounded-pill" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('forms.preview') }}">
                      <i class="ti ti-eye ti-md"></i>
                    </a>
                    <a href="/dashboard/show-builder/{{ $form_id }}{{ $org_id ? '?org_id=' . $org_id : '' }}"
                       class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('forms.edit') }}">
                      <i class="ti ti-edit ti-md"></i>
                    </a>
                    <a 
                      class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                      onclick="confirmDeleteWithSweetAlert({{ $form_id }}, '{{ $title }}', this)"
                      data-bs-toggle="tooltip" data-bs-placement="top"
                      title="{{ __('forms.delete') }}"
                    >
                      <i class="ti ti-trash ti-md text-dark"></i>
                    </a>
                  </div>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('scripts')
<script>
function confirmDeleteWithSweetAlert(formId, formName, buttonElement) {
  const lang = '{{ $l }}';
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // Show SweetAlert confirmation
  Swal.fire({
    title: '{{ __('admin.SureDelete') }}',
    text: '{{ __('admin.Recover') }}',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '{{ __('admin.DYes') }}',
    cancelButtonText: '{{ __('admin.Cancel') }}',
    reverseButtons: lang === 'ar',
    customClass: {
      confirmButton: 'btn btn-danger me-2',
      cancelButton: 'btn btn-label-secondary'
    },
    buttonsStyling: false
  }).then((result) => {
    if (result.isConfirmed) {
      // Disable the delete button to prevent multiple clicks
      const deleteButton = buttonElement;
      deleteButton.disabled = true;
      deleteButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>';

      // Find the row to delete
      const row = deleteButton.closest('tr');

      // Make AJAX request to delete the form
      fetch(`/dashboard/form/${formId}/delete`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Show success message
          const successMessage = lang === 'ar' ? data.message_ar : data.message_en;

          releaseSuccessAlert({
            title: '{{ __('admin.Success') }}',
            message: successMessage,
          });

          // Remove the row from the table in real-time
          row.style.opacity = '0.5';
          setTimeout(() => {
            row.remove();
          }, 300);

        } else {
          // Show error message
          const errorMessage = lang === 'ar' ? data.message_ar : data.message_en;

          releaseFailureAlert({
            title: '{{ __('admin.Error') }}',
            message: errorMessage || '{{ __('admin.unexpected_error') }}',
          });

          // Re-enable the button
          deleteButton.disabled = false;
          deleteButton.innerHTML = '<i class="ti ti-trash ti-sm"></i>';
        }
      })
      .catch(error => {
        releaseFailureAlert({
          title: '{{ __('admin.Error') }}',
          message: error instanceof Error ? error?.message : '{{ __('admin.unexpected_error') }}',
        });

        // Re-enable the button
        deleteButton.disabled = false;
        deleteButton.innerHTML = '<i class="ti ti-trash ti-sm"></i>';
      });
    }
  });
}
</script>
@endpush