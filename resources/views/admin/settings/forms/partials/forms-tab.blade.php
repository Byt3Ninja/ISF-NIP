<!-- Forms Table -->
<div class="tab-pane fade" id="forms-pane" role="tabpanel">
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
                <td class="table-actions text-center">
                  <a href="/dashboard/show-form/{{ $form_id }}" class="btn btn-sm btn-label-primary" title="{{ __('forms.search') }}">
                    <i class="ti ti-eye ti-sm"></i>
                  </a>
                  <a href="/dashboard/show-builder/{{ $form_id }}{{ $org_id ? '?org_id=' . $org_id : '' }}"
                     class="btn btn-sm btn-label-info" title="Edit">
                    <i class="ti ti-edit ti-sm"></i>
                  </a>
                  <a class="btn btn-sm btn-label-danger" style="margin-top: 5px;" 
                          onclick="confirmDelete({{ $form_id }}, '{{ $title }}')" 
                          title="{{ __('forms.delete') }}">
                    <i class="ti ti-trash ti-sm"></i>
</a>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteFormModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ $l == 'ar' ? 'حذف النموذج' : 'Delete Form' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="deleteConfirmMessage">
          <p id="confirmText">
            {{ $l == 'ar' ? 'هل أنت متأكد من أنك تريد حذف هذا النموذج؟' : 'Are you sure you want to delete this form? ' }}
          </p>
          <p class="text-muted"><strong id="formNameToDelete"></strong></p>
        </div>
        <div id="deleteErrorMessage" class="alert alert-danger" style="display: none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          {{ $l == 'ar' ? 'إلغاء' : 'Cancel' }}
        </button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deleteForm()">
          {{ $l == 'ar' ? 'حذف' : 'Delete' }}
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let formIdToDelete = null;

function confirmDelete(formId, formName) {
  formIdToDelete = formId;
  document.getElementById('formNameToDelete').textContent = formName;
  document.getElementById('deleteConfirmMessage').style.display = 'block';
  document.getElementById('deleteErrorMessage').style.display = 'none';
  document.getElementById('confirmDeleteBtn').style.display = 'inline-block';
  
  const modal = new bootstrap.Modal(document.getElementById('deleteFormModal'));
  modal.show();
}

function deleteForm() {
  if (!formIdToDelete) return;
  
  const confirmBtn = document.getElementById('confirmDeleteBtn');
  confirmBtn.disabled = true;
  confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>{{ $l == "ar" ? "جاري الحذف..." : "Deleting..." }}';
  
  fetch(`/dashboard/form/${formIdToDelete}/delete`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Show success message
      const successMessage = '{{ $l }}' === 'ar' ? data.message_ar : data.message_en;
      
      // Close modal
      bootstrap.Modal.getInstance(document.getElementById('deleteFormModal')).hide();
      
      // Show success alert
      const alertDiv = document.createElement('div');
      alertDiv.className = 'alert alert-success alert-dismissible fade show';
      alertDiv.innerHTML = `
        ${successMessage}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      document.querySelector('.card-header').after(alertDiv);
      
      // Reload page after short delay
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      // Show error in modal
      const errorMessage = '{{ $l }}' === 'ar' ? data.message_ar : data.message_en;
      document.getElementById('deleteConfirmMessage').style.display = 'none';
      document.getElementById('confirmDeleteBtn').style.display = 'none';
      document.getElementById('deleteErrorMessage').textContent = errorMessage;
      document.getElementById('deleteErrorMessage').style.display = 'block';
      
      confirmBtn.disabled = false;
      confirmBtn.innerHTML = '{{ $l == "ar" ? "حذف" : "Delete" }}';
    }
  })
  .catch(error => {
    console.error('Error:', error);
    const errorMessage = '{{ $l }}' === 'ar' ? 'حدث خطأ أثناء حذف النموذج' : 'An error occurred while deleting the form';
    document.getElementById('deleteConfirmMessage').style.display = 'none';
    document.getElementById('confirmDeleteBtn').style.display = 'none';
    document.getElementById('deleteErrorMessage').textContent = errorMessage;
    document.getElementById('deleteErrorMessage').style.display = 'block';
    
    confirmBtn.disabled = false;
    confirmBtn.innerHTML = '{{ $l == "ar" ? "حذف" : "Delete" }}';
  });
}
</script>
