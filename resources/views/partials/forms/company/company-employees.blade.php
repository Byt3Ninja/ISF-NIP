<div class="row">
  <!-- Navigation Pills -->
  <div class="col-12 mb-4">
    <ul class="nav nav-pills" id="employees-tabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="statistics-tab" data-bs-toggle="pill" data-bs-target="#statistics-pane" type="button" role="tab" aria-controls="statistics-pane" aria-selected="true">
          <i class="ti ti-chart-bar me-2"></i>{{ $l == 'ar' ? 'إحصائيات الموظفين' : 'Overall Employees Statistics' }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="employees-tab" data-bs-toggle="pill" data-bs-target="#employees-pane" type="button" role="tab" aria-controls="employees-pane" aria-selected="false">
          <i class="ti ti-users-group me-2"></i>{{ $l == 'ar' ? 'الموظفون' : 'Employees' }}
        </button>
      </li>
    </ul>
  </div>

  <!-- Tab Content -->
  <div class="col-12 tab-content py-0 px-2" id="employees-tabs-content">

    <!-- ========== TAB 1: Overall Employees Statistics ========== -->
    <div class="tab-pane fade show active" id="statistics-pane" role="tabpanel" aria-labelledby="statistics-tab">
      <div class="card border">
        <div class="card-body">
          <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
            {{-- <i class="ti ti-chart-infographic text-primary me-2" style="font-size: 1.5rem;"></i> --}}
            <div>
              <h5 class="mb-0">{{ $l == 'ar' ? 'إحصائيات الموظفين الإجمالية' : 'Overall Employees Statistics' }}</h5>
              <small class="text-muted">{{ $l == 'ar' ? 'أدخل الأرقام الإجمالية للموظفين' : 'Enter the overall employee figures' }}</small>
            </div>
          </div>

          <div class="row g-4">
            <!-- Total Employees -->
            <div class="col-12 col-sm-6 col-lg-4">
              <div class="mb-0">
                <label for="stat-total-employees" class="form-label fw-semibold">
                  {{ $l == 'ar' ? 'إجمالي الموظفين' : 'Total Employees' }}
                  <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text"><i class="ti ti-users-group"></i></span>
                  <input
                    type="number"
                    id="stat-total-employees"
                    class="form-control"
                    placeholder="0"
                    min="0"
                    maxlength="6"
                    value="{{ $client->company->total_employees ?? '' }}"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)"
                  />
                </div>
                <div class="invalid-feedback d-block" id="stat-total-employees-error"></div>
              </div>
            </div>

            <!-- Male Employees -->
            <div class="col-12 col-sm-6 col-lg-4">
              <div class="mb-0">
                <label for="stat-male-employees" class="form-label fw-semibold">
                  {{ $l == 'ar' ? 'الموظفون الذكور' : 'Male Employees' }}
                  <span class="text-muted fw-normal">({{ $l == 'ar' ? 'اختياري' : 'Optional' }})</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text"><i class="ti ti-gender-male"></i></span>
                  <input
                    type="number"
                    id="stat-male-employees"
                    class="form-control"
                    placeholder="0"
                    min="0"
                    maxlength="6"
                    value="{{ $client->company->male_employees ?? '' }}"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)"
                  />
                </div>
                <div class="invalid-feedback d-block" id="stat-male-employees-error"></div>
              </div>
            </div>

            <!-- Female Employees -->
            <div class="col-12 col-sm-6 col-lg-4">
              <div class="mb-0">
                <label for="stat-female-employees" class="form-label fw-semibold">
                  {{ $l == 'ar' ? 'الموظفات الإناث' : 'Female Employees' }}
                  <span class="text-muted fw-normal">({{ $l == 'ar' ? 'اختياري' : 'Optional' }})</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text"><i class="ti ti-gender-female"></i></span>
                  <input
                    type="number"
                    id="stat-female-employees"
                    class="form-control"
                    placeholder="0"
                    min="0"
                    maxlength="6"
                    value="{{ $client->company->female_employees ?? '' }}"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)"
                  />
                </div>
                <div class="invalid-feedback d-block" id="stat-female-employees-error"></div>
              </div>
            </div>

            <!-- Gender Sum Error -->
            <div class="col-12">
              <div id="stat-gender-sum-error" class="text-danger small d-none">
                <i class="ti ti-alert-circle me-1"></i>
                <span id="stat-gender-sum-error-text"></span>
              </div>
            </div>
          </div>

          <!-- Save Button -->
          <div class="d-flex justify-content-end mt-8">
            <button type="button" id="save-statistics-btn" class="btn btn-primary">
              <i class="ti ti-device-floppy me-2"></i>{{ $l == 'ar' ? 'حفظ الإحصائيات' : 'Save Statistics' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ========== TAB 2: Employees ========== -->
    <div class="tab-pane fade" id="employees-pane" role="tabpanel" aria-labelledby="employees-tab">
      <!-- Employee Cards Container -->
      <div id="employees-container" class="col-12">
        <!-- Template for a single employee card (will be cloned by JS) -->
        <template id="employee-card-template">
          <div class="card border mb-4 employee-card position-relative overflow-hidden">
            <!-- Status indicator -->
            <div class="position-absolute top-0 start-0 w-100 h-px-3 bg-primary employee-status-bar"></div>
            
            <div class="card-body">
              <!-- Enhanced Card Header -->
              <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                  <span class="badge bg-primary rounded-circle w-px-30 h-px-30 d-flex justify-content-center align-items-center employee-number">
                    <!-- Employee number goes here -->
                  </span>
                  <h5 class="card-title mb-1 employee-name-display">{{ __('startup.Employee') }}</h5>
                </div>
                <div class="employee-header-actions d-flex gap-2">
                  <!-- Status badge -->
                  <span class="badge bg-label-success employee-status-badge d-none">
                    <i class="ti ti-check me-1"></i>{{ $l == 'ar' ? 'محفوظ' : 'Saved' }}
                  </span>
                  <!-- Remove button for unsaved cards -->
                  <button type="button" class="btn btn-sm btn-label-danger remove-unsaved-employee-btn d-none" title="{{ __('admin.Remove') }}">
                    <i class="ti ti-trash me-1"></i>{{ __('admin.Remove') }}
                  </button>
                  <!-- Delete button for saved cards -->
                   <button type="button" class="btn btn-sm btn-label-danger delete-employee-btn d-none" title="{{ __('admin.Remove') }}">
                    <i class="ti ti-trash me-1"></i>{{ __('admin.Delete') }}
                  </button>
                </div>
              </div>

              <!-- Form for adding/editing an employee -->
              <div class="employee-form-section">
                <div class="mb-3">
                  <label for="employee-name-input" class="form-label">{{ $l == 'ar' ? 'الاسم الكامل' : 'Full Name' }}</label>
                  <input 
                    type="text" 
                    id="employee-name-input" 
                    class="form-control employee-name" 
                    placeholder="{{ $l == 'ar' ? 'الاسم الكامل' : 'Full Name' }}"
                    maxlength="100"
                  />
                  <div class="invalid-feedback employee-name-error"></div>
                </div>

                <div class="mb-3">
                  <label for="employee-job-title-input" class="form-label">{{ $l == 'ar' ? 'المسمى الوظيفي / الدور' : 'Job Title / Role' }}</label>
                  <input 
                    type="text" 
                    id="employee-job-title-input" 
                    class="form-control employee-job-title" 
                    placeholder="{{ $l == 'ar' ? 'المسمى الوظيفي / الدور' : 'Job Title / Role' }}"
                    maxlength="100"
                  />
                  <div class="invalid-feedback employee-job-title-error"></div>
                </div>

                <div class="mb-3">
                  <label for="employee-gender-select" class="form-label">{{ $l == 'ar' ? 'النوع' : 'Gender' }}</label>
                  <select 
                    id="employee-gender-select" 
                    class="form-select employee-gender"
                  >
                    <option value="">{{ $l == 'ar' ? 'اختر النوع' : 'Select Gender' }}</option>
                    <option value="male">{{ $l == 'ar' ? 'ذكر' : 'Male' }}</option>
                    <option value="female">{{ $l == 'ar' ? 'أنثى' : 'Female' }}</option>
                  </select>
                  <div class="invalid-feedback employee-gender-error"></div>
                </div>

                <div class="mb-3">
                  <label for="employee-employment-type-select" class="form-label">{{ $l == 'ar' ? 'نوع التوظيف' : 'Employment Type' }}</label>
                  <select 
                    id="employee-employment-type-select" 
                    class="form-select employee-employment-type"
                  >
                    <option value="">{{ $l == 'ar' ? 'اختر نوع التوظيف' : 'Select Employment Type' }}</option>
                    <option value="full-time">{{ $l == 'ar' ? 'دوام كامل' : 'Full-time' }}</option>
                    <option value="part-time">{{ $l == 'ar' ? 'دوام جزئي' : 'Part-time' }}</option>
                    <option value="contractor">{{ $l == 'ar' ? 'متعاقد' : 'Contractor' }}</option>
                    <option value="intern">{{ $l == 'ar' ? 'متدرب' : 'Intern' }}</option>
                  </select>
                  <div class="invalid-feedback employee-employment-type-error"></div>
                </div>

                <div class="mb-3">
                  <label for="employee-hiring-date" class="form-label">{{ $l == 'ar' ? 'تاريخ التوظيق' : 'Hiring Date' }}</label>
                  <input 
                    type="text" 
                    id="employee-hiring-date" 
                    class="form-control employee-hiring-date"
                    placeholder="{{ $l == 'ar' ? 'تاريخ التوظيق' : 'Hiring Date' }}"
                  />
                  <div class="invalid-feedback employee-hiring-date-error"></div>
                </div>

                <div class="mb-3">
                  <label for="employee-documents-input" class="form-label">
                    {{ $l == 'ar' ? 'المستندات الداعمة' : 'Supporting Documents' }}
                  </label>
                  <input 
                    type="file" 
                    id="employee-documents-input" 
                    class="form-control employee-documents" 
                    multiple
                    accept=".pdf,.jpg,.jpeg,.png,.docx"
                  />
                  <div class="invalid-feedback employee-documents-error"></div>
                  <small class="form-text text-muted">
                    {{ $l == 'ar' ? 'تنسيق الملفات المسموح به: PDF، JPG، PNG، و DOCX. الحد الأقصى للحجم = 10 ميجابايت لكل ملف.' : 'Allowed file formats: PDF, JPG, PNG, and DOCX. Max size = 10 MB per file.' }}
                  </small>
                  <div class="uploaded-documents mt-2">
                    <!-- Uploaded files will be listed here by JS -->
                  </div>
                </div>

                <div class="d-flex w-100 gap-2 justify-content-end">
                  <button type="button" class="btn btn-primary submit-employee-btn">{{ __('admin.Save') }}</button>
                  <button type="button" class="btn btn-label-secondary cancel-edit-btn d-none">{{ __('admin.Cancel') }}</button>
                </div>
              </div>

              <!-- Enhanced Display section for submitted employees -->
              <div class="employee-display-section d-none">
                <!-- Personal Information Card -->
                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <div class="bg-label-primary rounded p-3 h-100">
                      <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-user-circle text-primary me-2"></i>
                        <h6 class="mb-0 text-primary">{{ $l == 'ar' ? 'المعلومات الشخصية' : 'Personal Information' }}</h6>
                      </div>
                      <div class="mb-2">
                        <small class="text-muted d-block">{{ $l == 'ar' ? 'الاسم الكامل' : 'Full Name' }}</small>
                        <strong class="employee-name-display-preview"></strong>
                      </div>
                      <div>
                        <small class="text-muted d-block">{{ $l == 'ar' ? 'النوع' : 'Gender' }}</small>
                        <span class="badge bg-label-info employee-gender-display"></span>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="bg-label-success rounded p-3 h-100">
                      <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-briefcase text-success me-2"></i>
                        <h6 class="mb-0 text-success">{{ $l == 'ar' ? 'معلومات العمل' : 'Work Information' }}</h6>
                      </div>
                      <div class="mb-2">
                        <small class="text-muted d-block">{{ $l == 'ar' ? 'المسمى الوظيفي' : 'Job Title' }}</small>
                        <strong class="employee-job-title-display"></strong>
                      </div>
                      <div class="mb-2">
                        <small class="text-muted d-block">{{ $l == 'ar' ? 'نوع التوظيف' : 'Employment Type' }}</small>
                        <span class="badge bg-label-success employee-employment-type-display"></span>
                      </div>
                      <div>
                        <small class="text-muted d-block">{{ $l == 'ar' ? 'تاريخ البدء' : 'Start Date' }}</small>
                        <span class="text-body employee-hiring-date-display"></span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Documents Section -->
                <div class="supporting-documents-display mb-4">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                      <i class="ti ti-files text-info"></i>
                      <h6 class="mb-0">{{ $l == 'ar' ? 'المستندات الداعمة' : 'Supporting Documents' }}</h6>
                    </div>
                    <span class="badge bg-label-secondary employee-documents-count">0</span>
                  </div>
                  
                  <div class="uploaded-files-list">
                    <!-- Display uploaded file names with download links -->
                  </div>
                  
                  <div class="text-center py-4 employee-no-documents d-none">
                    <i class="ti ti-file-x text-muted mb-2" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0">{{ $l == 'ar' ? 'لا توجد مستندات مرفقة' : 'No documents attached' }}</p>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                  <button type="button" class="btn-sm btn-label-primary edit-employee-btn">
                    <i class="ti ti-pencil me-2"></i>{{ __('admin.Save') }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- Dynamic Employee Cards will be inserted here by JavaScript -->
      </div>

      <!-- Add New Employee Button (always visible) -->
      <div class="col-12 text-center mt-4">
        <button id="add-employee-btn" class="btn btn-outline-primary">
          <i class="ti ti-plus me-2"></i>{{ $l == 'ar' ? 'إضافة موظف جديد' : 'Add New Employee' }}
        </button>
      </div>
    </div>

  </div><!-- /.tab-content -->
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

  // ============================================================
  // STATISTICS TAB LOGIC
  // ============================================================
  const isArabic = {{ $l == 'ar' ? 'true' : 'false' }};

  const STAT_MESSAGES = {
    required:    isArabic ? 'هذا الحقل مطلوب.'                                                                    : 'This field is required.',
    invalidNum:  isArabic ? 'يرجى إدخال رقم صحيح.'                                                                : 'Please enter a valid number.',
    negative:    isArabic ? 'لا يمكن أن تكون القيمة أقل من 0.'                                                    : 'Value cannot be less than 0.',
    genderSum:   isArabic ? 'يجب أن يساوي مجموع الموظفين الذكور والإناث إجمالي عدد الموظفين.'                     : 'Male and female employees total must equal total employees.',
    saveSuccess: isArabic ? 'تم تحديث إحصائيات الموظفين بنجاح.'                                                   : 'Employee statistics updated successfully.',
    saving:      isArabic ? 'جارٍ الحفظ...'                                                                       : 'Saving...',
  };

  const statTotalInput   = document.getElementById('stat-total-employees');
  const statMaleInput    = document.getElementById('stat-male-employees');
  const statFemaleInput  = document.getElementById('stat-female-employees');
  const statTotalError   = document.getElementById('stat-total-employees-error');
  const statMaleError    = document.getElementById('stat-male-employees-error');
  const statFemaleError  = document.getElementById('stat-female-employees-error');
  const statGenderSumErr = document.getElementById('stat-gender-sum-error');
  const statGenderSumTxt = document.getElementById('stat-gender-sum-error-text');
  const saveStatisticsBtn = document.getElementById('save-statistics-btn');

  function clearStatError(input, errorEl) {
    input.classList.remove('is-invalid');
    errorEl.textContent = '';
  }

  function showStatError(input, errorEl, msg) {
    input.classList.add('is-invalid');
    errorEl.textContent = msg;
  }

  function clearAllStatErrors() {
    [statTotalInput, statMaleInput, statFemaleInput].forEach((inp, i) => {
      inp.classList.remove('is-invalid');
    });
    statTotalError.textContent  = '';
    statMaleError.textContent   = '';
    statFemaleError.textContent = '';
    statGenderSumErr.classList.add('d-none');
    statGenderSumTxt.textContent = '';
  }

  // Clear on input
  statTotalInput.addEventListener('input',  () => clearStatError(statTotalInput,  statTotalError));
  statMaleInput.addEventListener('input',   () => clearStatError(statMaleInput,   statMaleError));
  statFemaleInput.addEventListener('input', () => clearStatError(statFemaleInput, statFemaleError));

  function validateStatField(input, errorEl, required) {
    const raw = input.value.trim();

    if (raw === '') {
      if (required) {
        showStatError(input, errorEl, STAT_MESSAGES.required);
        return false;
      }
      return true; // optional & empty → valid
    }

    // Must be digits only (no decimals, no negatives slipping through)
    if (!/^\d+$/.test(raw)) {
      showStatError(input, errorEl, STAT_MESSAGES.invalidNum);
      return false;
    }

    const num = parseInt(raw, 10);

    if (num < 0) {
      showStatError(input, errorEl, STAT_MESSAGES.negative);
      return false;
    }

    return true;
  }

  saveStatisticsBtn.addEventListener('click', function () {
    clearAllStatErrors();

    const totalValid  = validateStatField(statTotalInput,  statTotalError,  true);
    const maleValid   = validateStatField(statMaleInput,   statMaleError,   false);
    const femaleValid = validateStatField(statFemaleInput, statFemaleError, false);

    if (!totalValid || !maleValid || !femaleValid) return;

    // Gender sum validation (only when both are provided)
    const maleRaw   = statMaleInput.value.trim();
    const femaleRaw = statFemaleInput.value.trim();
    const totalRaw  = statTotalInput.value.trim();

    if (maleRaw !== '' && femaleRaw !== '') {
      const maleVal   = parseInt(maleRaw,   10);
      const femaleVal = parseInt(femaleRaw, 10);
      const totalVal  = parseInt(totalRaw,  10);

      if (maleVal + femaleVal !== totalVal) {
        statGenderSumTxt.textContent = STAT_MESSAGES.genderSum;
        statGenderSumErr.classList.remove('d-none');
        // Also highlight total, male, female inputs
        statTotalInput.classList.add('is-invalid');
        statMaleInput.classList.add('is-invalid');
        statFemaleInput.classList.add('is-invalid');
        return;
      }
    }

    // ---- Submit ----
    const originalHtml = saveStatisticsBtn.innerHTML;
    saveStatisticsBtn.disabled = true;
    saveStatisticsBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${STAT_MESSAGES.saving}`;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const companyId = @json(request()->route('id') ?? auth()->user()->company?->id);

    fetch(`{{ route('company.employees_statistics', '') }}/${companyId}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        total_employees:  parseInt(statTotalInput.value.trim(), 10),
        male_employees:   maleRaw   !== '' ? parseInt(maleRaw,   10) : null,
        female_employees: femaleRaw !== '' ? parseInt(femaleRaw, 10) : null,
      })
    })
    .then(async response => {
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        // Handle server-side validation errors
        if (data.errors) {
          if (data.errors.total_employees)  showStatError(statTotalInput,  statTotalError,  data.errors.total_employees[0]);
          if (data.errors.male_employees)   showStatError(statMaleInput,   statMaleError,   data.errors.male_employees[0]);
          if (data.errors.female_employees) showStatError(statFemaleInput, statFemaleError, data.errors.female_employees[0]);
          return;
        }
        throw new Error(data.message || 'Server error');
      }
      toastr.success(STAT_MESSAGES.saveSuccess);
    })
    .catch(error => {
      toastr.error(error.message || 'An unexpected error occurred.');
    })
    .finally(() => {
      saveStatisticsBtn.disabled = false;
      saveStatisticsBtn.innerHTML = originalHtml;
    });
  });


  // ============================================================
  // EMPLOYEES TAB LOGIC (unchanged from original)
  // ============================================================
  const employeesContainer = document.getElementById('employees-container');
  const addEmployeeBtn = document.getElementById('add-employee-btn');
  const employeeCardTemplate = document.getElementById('employee-card-template');
  let nextEmployeeId = 1;
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  
  const companyId = @json(
      request()->route('id') ?? auth()->user()->company?->id
  );
  
  const MESSAGES = {
    nameRequired: "{{ __('startup.employee_name_required') }}",
    jobTitleRequired: "{{ __('startup.employee_job_title_required') }}",
    genderRequired: "{{ __('startup.employee_gender_required') }}",
    employmentTypeRequired: "{{ __('startup.employee_employment_required') }}",
    hiringDateRequired: "{{ __('startup.employee_hiring_date_required') }}",
    fileType: "{{ __('startup.employee_document_invalid_type') }}",
    fileSize: "{{ __('startup.document_too_large') }}",
    uploadFailed: "{{ __('startup.document_upload_failed') }}",
    saving: "{{ __('startup.saving') }}",
    addSuccess: "{{  __('startup.employee_add_success') }}",
    editSuccess: "{{  __('startup.employee_edit_success') }}",
    deleteSuccess: "{{ __('startup.employee_delete_success') }}",
    deleteConfirm: "{{ __('startup.employee_delete_confirm') }}",
    documentDeleteConfirm: "{{ __('startup.document_delete_confirm') }}",
    error: "{{ __('startup.unexpected_error') }}",
    deleteError: "{{ __('startup.employee_delete_failed') }}"
  };

  const ALLOWED_FILE_TYPES = ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
  const MAX_FILE_SIZE = 10 * 1024 * 1024;

  const GENDER_LABELS = {
    male: "{{ $l == 'ar' ? 'ذكر' : 'Male' }}",
    female: "{{ $l == 'ar' ? 'أنثى' : 'Female' }}"
  };

  const EMPLOYMENT_TYPE_LABELS = {
    'full-time': "{{ $l == 'ar' ? 'دوام كامل' : 'Full-time' }}",
    'part-time': "{{ $l == 'ar' ? 'دوام جزئي' : 'Part-time' }}",
    'contractor': "{{ $l == 'ar' ? 'متعاقد' : 'Contractor' }}",
    'intern': "{{ $l == 'ar' ? 'متدرب' : 'Intern' }}"
  };

  function loadExistingEmployees() {
    @if($employees && $employees->count() > 0)
    @php
      $employees->map(function($emp) {
        return [
          'id' => $emp->id,
          'full_name' => $emp->full_name,
          'job_title' => $emp->job_title,
          'gender' => $emp->gender,
          'employment_type' => $emp->employment_type,
          'hiring_date' => $emp->hiring_date ? $emp->hiring_date->format('Y-m-d') : null,
          'documents' => $emp->documents
        ];
      });
    @endphp
    const existingEmployees = @json($employees);
      
      existingEmployees.forEach((employee, index) => {
        const employeeCard = createEmployeeCard(index === 0, employee);
        populateEmployeeCardInForm(employeeCard, employee);
      });
      updateEmployeeNumbers();
    @else
      createEmployeeCard(true);
    @endif
  }

  function parseDocuments(documents) {
    if (typeof documents === 'string') {
      try { documents = JSON.parse(documents); }
      catch (e) { return []; }
    }
    return Array.isArray(documents) ? documents : [];
  }

  function getFileIcon(fileName) {
    const extension = fileName.split('.').pop().toLowerCase();
    const iconMap = {
      pdf: 'ti ti-file-type-pdf text-danger',
      jpg: 'ti ti-photo text-info',
      jpeg: 'ti ti-photo text-info',
      png: 'ti ti-photo text-info',
      docx: 'ti ti-file-type-docx text-primary',
      doc: 'ti ti-file-type-docx text-primary'
    };
    return iconMap[extension] || 'ti ti-file';
  }

  function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('{{ $l == "ar" ? "ar-EG" : "en-US" }}', {
      year: 'numeric', month: 'long', day: 'numeric'
    });
  }

  function populateEmployeeCard(card, employee) {
    Object.assign(card.dataset, { employeeId: employee.id, isNew: false });
    card.employeeData = employee;
    card.querySelector('.employee-form-section').classList.add('d-none');
    card.querySelector('.employee-display-section').classList.remove('d-none');
    card.querySelector('.remove-unsaved-employee-btn').classList.add('d-none');
    card.querySelector('.delete-employee-btn').classList.remove('d-none');
    card.querySelector('.employee-name-display-preview').textContent = employee.full_name;
    card.querySelector('.employee-job-title-display').textContent = employee.job_title;
    card.querySelector('.employee-gender-display').textContent = GENDER_LABELS[employee.gender] || employee.gender;
    card.querySelector('.employee-employment-type-display').textContent = EMPLOYMENT_TYPE_LABELS[employee.employment_type] || employee.employment_type;
    card.querySelector('.employee-hiring-date-display').textContent = formatDate(employee.hiring_date);
    const documents = parseDocuments(employee.documents);
    const uploadedFilesList = card.querySelector('.uploaded-files-list');
    const documentsCount = card.querySelector('.employee-documents-count');
    const noDocumentsDiv = card.querySelector('.employee-no-documents');
    uploadedFilesList.innerHTML = '';
    documentsCount.textContent = documents.length;
    if (documents.length > 0) {
      noDocumentsDiv.classList.add('d-none');
      documents.forEach(doc => {
        const listItem = document.createElement('div');
        listItem.className = 'mb-2';
        listItem.innerHTML = `
          <div class="card border-0 bg-light">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                  <div class="avatar avatar-sm bg-label-primary rounded">
                    <i class="${getFileIcon(doc.name)}"></i>
                  </div>
                  <div>
                    <h6 class="mb-0">${doc.name}</h6>
                    <small class="text-muted">{{ $l == 'ar' ? 'مستند داعم' : 'Supporting Document' }}</small>
                  </div>
                </div>
                <a href="${doc.url}" target="_blank" class="btn btn-sm btn-outline-primary">
                  <i class="ti ti-download me-1"></i>{{ $l == 'ar' ? 'تحميل' : 'Download' }}
                </a>
              </div>
            </div>
          </div>`;
        uploadedFilesList.appendChild(listItem);
      });
    } else {
      noDocumentsDiv.classList.remove('d-none');
    }
    addValidationClearListeners(card);
  }

  function populateEmployeeCardInForm(card, employee) {
    Object.assign(card.dataset, { employeeId: employee.id, isNew: false });
    card.employeeData = employee;
    card.querySelector('.employee-form-section').classList.remove('d-none');
    card.querySelector('.employee-display-section').classList.add('d-none');
    card.querySelector('.remove-unsaved-employee-btn').classList.add('d-none');
    card.querySelector('.delete-employee-btn').classList.remove('d-none');
    card.querySelector('.cancel-edit-btn').classList.add('d-none');
    card.querySelector('.submit-employee-btn').textContent = '{{ __("admin.Save") }}';
    card.querySelector('.employee-name').value = employee.full_name || '';
    card.querySelector('.employee-job-title').value = employee.job_title || '';
    card.querySelector('.employee-gender').value = employee.gender || '';
    card.querySelector('.employee-employment-type').value = employee.employment_type || '';
    card.querySelector('.employee-hiring-date').value = employee.hiring_date ? moment(employee.hiring_date).format('YYYY-MM-DD') : '';
    populateExistingDocumentsInForm(card);
    addValidationClearListeners(card);
  }

  function createEmployeeCard(isInitialCard = false, existingData = null) {
    const newCard = employeeCardTemplate.content.cloneNode(true).querySelector('.employee-card');
    const currentEmployeeId = existingData ? existingData.id : `temp_${nextEmployeeId++}`;
    Object.assign(newCard.dataset, { employeeId: currentEmployeeId, isNew: !existingData });
    if (isInitialCard) { newCard.classList.add('initial-employee-card'); newCard.dataset.isNew = false; }
    const employeeText = '{{ $l == 'ar' ? 'موظف' : 'Employee' }}';
    const employeeNumber = employeesContainer.children.length + 1;
    newCard.querySelector('.employee-number').textContent = employeeNumber;
    newCard.querySelector('.employee-name-display').textContent = `${employeeText} ${employeeNumber}`;
    const removeBtn = newCard.querySelector('.remove-unsaved-employee-btn');
    const deleteBtn = newCard.querySelector('.delete-employee-btn');
    const submitBtn = newCard.querySelector('.submit-employee-btn');
    removeBtn.classList.toggle('d-none', isInitialCard);
    deleteBtn.classList.add('d-none');
    submitBtn.textContent = existingData ? '{{ __("admin.Update") }}' : '{{ __("admin.Save") }}';
    employeesContainer.appendChild(newCard);
    updateEmployeeNumbers();
    addValidationClearListeners(newCard);
    $('.employee-hiring-date').flatpickr({ dateFormat: "Y-m-d" });
    return newCard;
  }

  function updateEmployeeNumbers() {
    const employeeText = '{{ $l == 'ar' ? 'موظف' : 'Employee' }}';
    document.querySelectorAll('.employee-card').forEach((card, index) => {
      const number = index + 1;
      card.querySelector('.employee-number').textContent = number;
      const nameDisplay = card.querySelector('.employee-name-display');
      if (nameDisplay && !card.querySelector('.employee-display-section').classList.contains('d-none')) {
        const savedName = card.querySelector('.employee-name-display-preview')?.textContent;
        if (savedName) nameDisplay.textContent = savedName;
      } else if (nameDisplay) {
        nameDisplay.textContent = `${employeeText} ${number}`;
      }
    });
  }

  function clearValidation(card) {
    card.querySelectorAll('.form-control, .form-select').forEach(input => input.classList.remove('is-invalid'));
    card.querySelectorAll('.invalid-feedback').forEach(errorDiv => errorDiv.textContent = '');
  }

  function showValidationError(inputElement, errorDiv, message) {
    if (inputElement) inputElement.classList.add('is-invalid');
    if (errorDiv) errorDiv.textContent = message;
  }

  function clearSingleValidation(inputElement) {
    if (!inputElement) return;
    inputElement.classList.remove('is-invalid');
    const card = inputElement.closest('.employee-card');
    if (!card) return;
    const errorClassMap = {
      'employee-name': '.employee-name-error',
      'employee-job-title': '.employee-job-title-error',
      'employee-gender': '.employee-gender-error',
      'employee-employment-type': '.employee-employment-type-error',
      'employee-hiring-date': '.employee-hiring-date-error',
      'employee-documents': '.employee-documents-error'
    };
    for (const [inputClass, errorClass] of Object.entries(errorClassMap)) {
      if (inputElement.classList.contains(inputClass)) {
        const errorDiv = card.querySelector(errorClass);
        if (errorDiv) errorDiv.textContent = '';
        break;
      }
    }
  }

  function addValidationClearListeners(card) {
    const inputs = card.querySelectorAll('.employee-name, .employee-job-title, .employee-gender, .employee-employment-type, .employee-hiring-date, .employee-documents');
    inputs.forEach(input => {
      ['input', 'change', 'keyup'].forEach(eventType => {
        input.addEventListener(eventType, () => {
          clearSingleValidation(input);
          if (input.classList.contains('employee-name') || input.classList.contains('employee-job-title')) {
            const cursorPos = input.selectionStart;
            const trimmedValue = input.value.replace(/^\s+/, '');
            if (input.value !== trimmedValue) {
              input.value = trimmedValue;
              input.setSelectionRange(Math.max(0, cursorPos - (input.value.length - trimmedValue.length)), Math.max(0, cursorPos - (input.value.length - trimmedValue.length)));
            }
          }
        });
      });
    });
  }

  function handleServerValidationErrors(card, errors) {
    const fieldMap = {
      full_name: { errorSelector: '.employee-name-error', inputSelector: '.employee-name' },
      job_title: { errorSelector: '.employee-job-title-error', inputSelector: '.employee-job-title' },
      gender: { errorSelector: '.employee-gender-error', inputSelector: '.employee-gender' },
      employment_type: { errorSelector: '.employee-employment-type-error', inputSelector: '.employee-employment-type' },
      hiring_date: { errorSelector: '.employee-hiring-date-error', inputSelector: '.employee-hiring-date' },
      documents: { errorSelector: '.employee-documents-error', inputSelector: '.employee-documents' }
    };
    Object.entries(errors).forEach(([field, messages]) => {
      const fieldKey = field.startsWith('documents.') ? 'documents' : field;
      const mapping = fieldMap[fieldKey];
      if (mapping) {
        const errorElement = card.querySelector(mapping.errorSelector);
        const inputElement = card.querySelector(mapping.inputSelector);
        if (errorElement && inputElement) showValidationError(inputElement, errorElement, messages[0]);
      }
    });
  }

  function handleSuccessfulSubmission(card, data, inputs) {
    try {
      card.dataset.isNew = false;
      if (data.employee?.id) card.dataset.employeeId = data.employee.id;
      if (data.employee) {
        card.employeeData = {
          id: data.employee.id,
          full_name: data.employee.full_name,
          job_title: data.employee.job_title,
          gender: data.employee.gender,
          employment_type: data.employee.employment_type,
          hiring_date: data.employee.hiring_date ? moment(data.employee.hiring_date).format('YYYY-MM-DD') : '',
          documents: data.employee.documents
        };
      }
      const removeBtn = card.querySelector('.remove-unsaved-employee-btn');
      const deleteBtn = card.querySelector('.delete-employee-btn');
      const cancelBtn = card.querySelector('.cancel-edit-btn');
      if (removeBtn) removeBtn.classList.add('d-none');
      if (deleteBtn) deleteBtn.classList.remove('d-none');
      if (cancelBtn) cancelBtn.classList.add('d-none');
      const submitBtn = card.querySelector('.submit-employee-btn');
      if (submitBtn) submitBtn.textContent = '{{ __("admin.Update") }}';
      const nameDisplay = card.querySelector('.employee-name-display');
      const nameDisplayPreview = card.querySelector('.employee-name-display-preview');
      const jobTitleDisplay = card.querySelector('.employee-job-title-display');
      const genderDisplay = card.querySelector('.employee-gender-display');
      const employmentTypeDisplay = card.querySelector('.employee-employment-type-display');
      const hiringDateDisplay = card.querySelector('.employee-hiring-date-display');
      if (nameDisplay && inputs.name) nameDisplay.textContent = inputs.name.value;
      if (nameDisplayPreview && inputs.name) nameDisplayPreview.textContent = inputs.name.value;
      if (jobTitleDisplay && inputs.jobTitle) jobTitleDisplay.textContent = inputs.jobTitle.value;
      if (genderDisplay && inputs.gender) genderDisplay.textContent = GENDER_LABELS[inputs.gender.value] || inputs.gender.value;
      if (employmentTypeDisplay && inputs.employmentType) employmentTypeDisplay.textContent = EMPLOYMENT_TYPE_LABELS[inputs.employmentType.value] || inputs.employmentType.value;
      if (hiringDateDisplay && inputs.startDate) hiringDateDisplay.textContent = formatDate(inputs.startDate.value);
      if (data.employee) {
        const nameInput = card.querySelector('.employee-name');
        const jobTitleInput = card.querySelector('.employee-job-title');
        const genderInput = card.querySelector('.employee-gender');
        const employmentTypeInput = card.querySelector('.employee-employment-type');
        const hiringDateInput = card.querySelector('.employee-hiring-date');
        if (nameInput && data.employee.full_name) nameInput.value = data.employee.full_name;
        if (jobTitleInput && data.employee.job_title) jobTitleInput.value = data.employee.job_title;
        if (genderInput && data.employee.gender) genderInput.value = data.employee.gender;
        if (employmentTypeInput && data.employee.employment_type) employmentTypeInput.value = data.employee.employment_type;
        if (hiringDateInput && data.employee.hiring_date) hiringDateInput.value = data.employee.hiring_date ? moment(data.employee.hiring_date).format('YYYY-MM-DD') : '';
      }
      populateExistingDocumentsInForm(card);
      const documentsInput = card.querySelector('.employee-documents');
      if (documentsInput) documentsInput.value = '';
      releaseAlert('success', {
        title: '{{ __('admin.Success!') }}',
        message: data.state || (data.employee?.id ? MESSAGES.editSuccess : MESSAGES.addSuccess),
      });
    } catch (error) {
      console.error('Error in handleSuccessfulSubmission:', error);
      releaseAlert('success', {
        title: '{{ __('admin.Success!') }}',
        message: data.state || (data.employee?.id ? MESSAGES.editSuccess : MESSAGES.addSuccess),
      });
    }
  }

  function handleEmployeeDelete(card) {
    const isLastCard = document.querySelectorAll('.employee-card').length === 1;
    releaseAlert('warning', {
      title: "{{ __('Are you sure?') }}",
      message: MESSAGES.deleteConfirm,
      confirmText: "{{ __('admin.DYes') }}",
      cancelText: "{{ __('Cancel') }}",
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
        const employeeId = card.dataset.employeeId;
        fetch(`{{ route('company.employees_delete', '') }}/${companyId}`, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: employeeId })
        })
        .then(response => {
          if (!response.ok) {
            return response.json().then(data => {
              throw new Error(data.errors?.general?.[0] || data.errors?.employee?.[0] || 'Server error');
            });
          }
          return response.json();
        })
        .then(data => {
          if (data.success || data.state) {
            card.remove();
            if (isLastCard) createEmployeeCard(true);
            else updateEmployeeNumbers();
            releaseAlert('success', { title: "{{ __('admin.Deleted') }}", message: MESSAGES.deleteSuccess });
          } else {
            throw new Error(MESSAGES.deleteError);
          }
        })
        .catch(error => {
          releaseAlert('danger', { title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}', message: error.message || MESSAGES.deleteError });
        });
      }
    });
  }

  function populateExistingDocumentsInForm(card) {
    const uploadedDocumentsContainer = card.querySelector('.uploaded-documents');
    uploadedDocumentsContainer.innerHTML = '';
    const employee = card.employeeData;
    if (!employee?.documents) return;
    const documents = parseDocuments(employee.documents);
    if (documents.length > 0) {
      const documentsTitle = document.createElement('small');
      documentsTitle.className = 'form-text text-muted mb-2 d-block';
      documentsTitle.textContent = '{{ $l == "ar" ? "الملفات الحالية:" : "Current files:" }}';
      uploadedDocumentsContainer.appendChild(documentsTitle);
      documents.forEach((doc, index) => {
        const docElement = document.createElement('div');
        docElement.className = 'existing-document d-flex align-items-center justify-content-between p-2 mb-2 border rounded';
        docElement.dataset.documentIndex = index;
        docElement.innerHTML = `
          <div class="d-flex align-items-center">
            <i class="ti ti-file me-2"></i>
            <a href="${doc.url}" target="_blank" class="text-decoration-none">${doc.name}</a>
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger remove-document-btn">
            <i class="ti ti-trash"></i>
          </button>`;
        uploadedDocumentsContainer.appendChild(docElement);
      });
    }
  }

  function handleDocumentRemoval(removeBtn) {
    const documentElement = removeBtn.closest('.existing-document');
    releaseAlert('warning', {
      title: "{{ __('Are you sure?') }}",
      message: MESSAGES.documentDeleteConfirm,
      confirmText: "{{ __('admin.DYes') }}",
      cancelText: "{{ __('Cancel') }}",
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
        documentElement.classList.add('document-to-remove');
        Object.assign(documentElement.style, { opacity: '0.5', textDecoration: 'line-through' });
        removeBtn.innerHTML = '<i class="ti ti-rotate-clockwise"></i>';
        removeBtn.className = 'btn btn-sm btn-outline-secondary restore-document-btn';
        removeBtn.title = '{{ $l == "ar" ? "استعادة المستند" : "Restore document" }}';
      }
    });
  }

  function handleDocumentRestore(restoreBtn) {
    const documentElement = restoreBtn.closest('.existing-document');
    documentElement.classList.remove('document-to-remove');
    Object.assign(documentElement.style, { opacity: '', textDecoration: '' });
    restoreBtn.innerHTML = '<i class="ti ti-trash"></i>';
    restoreBtn.className = 'btn btn-sm btn-outline-danger remove-document-btn';
    restoreBtn.title = '';
  }

  function handleEditMode(card) {
    card.querySelector('.employee-form-section').classList.remove('d-none');
    card.querySelector('.employee-display-section').classList.add('d-none');
    card.querySelector('.cancel-edit-btn').classList.remove('d-none');
    card.querySelector('.delete-employee-btn').classList.add('d-none');
    const showRemoveBtn = !card.classList.contains('initial-employee-card') && card.dataset.isNew === 'true';
    card.querySelector('.remove-unsaved-employee-btn').classList.toggle('d-none', !showRemoveBtn);
    card.querySelector('.submit-employee-btn').textContent = '{{ __("admin.Update") }}';
    const employee = card.employeeData;
    if (employee) {
      card.querySelector('.employee-name').value = employee.full_name || '';
      card.querySelector('.employee-job-title').value = employee.job_title || '';
      card.querySelector('.employee-gender').value = employee.gender || '';
      card.querySelector('.employee-employment-type').value = employee.employment_type || '';
      card.querySelector('.employee-hiring-date').value = employee.hiring_date || '';
    }
    populateExistingDocumentsInForm(card);
    clearValidation(card);
  }

  function handleCancelEdit(card) {
    clearValidation(card);
    if (card.dataset.isNew === 'true') {
      card.remove();
      updateEmployeeNumbers();
    } else {
      card.querySelector('.employee-form-section').classList.add('d-none');
      card.querySelector('.employee-display-section').classList.remove('d-none');
      card.querySelector('.cancel-edit-btn').classList.add('d-none');
      card.querySelector('.uploaded-documents').innerHTML = '';
      card.querySelector('.delete-employee-btn').classList.remove('d-none');
      card.querySelector('.remove-unsaved-employee-btn').classList.add('d-none');
    }
  }

  loadExistingEmployees();

  addEmployeeBtn.addEventListener('click', function () { createEmployeeCard(false); });

  employeesContainer.addEventListener('change', function (event) {
    if (event.target.classList.contains('employee-documents')) {
      const documentsInput = event.target;
      const card = documentsInput.closest('.employee-card');
      const files = documentsInput.files;
      if (files.length > 0) {
        const existingDocs = card.employeeData?.documents ? parseDocuments(card.employeeData.documents) : [];
        const existingNames = existingDocs.filter(doc => !card.querySelector(`[data-document-index="${existingDocs.indexOf(doc)}"].document-to-remove`)).map(doc => doc.name);
        for (const file of files) {
          if (existingNames.includes(file.name)) {
            showValidationError(documentsInput, card.querySelector('.employee-documents-error'), '{{ $l == "ar" ? "هذا الملف موجود بالفعل" : "This file already exists" }}');
            documentsInput.value = '';
            return;
          }
        }
        clearSingleValidation(documentsInput);
      }
    }
  });

  employeesContainer.addEventListener('click', function (event) {
    const card = event.target.closest('.employee-card');
    if (!card) return;

    const submitBtn       = event.target.closest('.submit-employee-btn');
    const editBtn         = event.target.closest('.edit-employee-btn');
    const deleteBtn       = event.target.closest('.delete-employee-btn');
    const removeUnsavedBtn = event.target.closest('.remove-unsaved-employee-btn');
    const cancelEditBtn   = event.target.closest('.cancel-edit-btn');

    if (submitBtn) {
      const nameInput           = card.querySelector('.employee-name');
      const jobTitleInput       = card.querySelector('.employee-job-title');
      const genderInput         = card.querySelector('.employee-gender');
      const employmentTypeInput = card.querySelector('.employee-employment-type');
      const startDateInput      = card.querySelector('.employee-hiring-date');
      const documentsInput      = card.querySelector('.employee-documents');

      clearValidation(card);

      let isValid = true;
      let firstInvalidInput = null;

      const validationRules = [
        { input: nameInput,           errorSelector: '.employee-name-error',            message: MESSAGES.nameRequired,            validate: () => !nameInput.value.trim() },
        { input: jobTitleInput,       errorSelector: '.employee-job-title-error',        message: MESSAGES.jobTitleRequired,        validate: () => !jobTitleInput.value.trim() },
        { input: genderInput,         errorSelector: '.employee-gender-error',           message: MESSAGES.genderRequired,          validate: () => !genderInput.value },
        { input: employmentTypeInput, errorSelector: '.employee-employment-type-error',  message: MESSAGES.employmentTypeRequired,  validate: () => !employmentTypeInput.value },
        { input: startDateInput,      errorSelector: '.employee-hiring-date-error',      message: MESSAGES.hiringDateRequired,      validate: () => !startDateInput.value }
      ];

      const processedInputs = new Set();
      validationRules.forEach(rule => {
        if (!processedInputs.has(rule.input) && rule.validate()) {
          showValidationError(rule.input, card.querySelector(rule.errorSelector), rule.message);
          processedInputs.add(rule.input);
          isValid = false;
          if (!firstInvalidInput) firstInvalidInput = rule.input;
        }
      });

      const files = documentsInput.files;
      if (files.length > 0) {
        const existingDocs = card.employeeData?.documents ? parseDocuments(card.employeeData.documents) : [];
        const existingNames = existingDocs.filter(doc => !card.querySelector(`[data-document-index="${existingDocs.indexOf(doc)}"].document-to-remove`)).map(doc => doc.name);
        for (const file of files) {
          if (!ALLOWED_FILE_TYPES.includes(file.type)) {
            showValidationError(documentsInput, card.querySelector('.employee-documents-error'), MESSAGES.fileType);
            isValid = false;
            if (!firstInvalidInput) firstInvalidInput = documentsInput;
            break;
          }
          if (file.size > MAX_FILE_SIZE) {
            showValidationError(documentsInput, card.querySelector('.employee-documents-error'), MESSAGES.fileSize);
            isValid = false;
            if (!firstInvalidInput) firstInvalidInput = documentsInput;
            break;
          }
          if (existingNames.includes(file.name)) {
            showValidationError(documentsInput, card.querySelector('.employee-documents-error'), '{{ $l == "ar" ? "هذا الملف موجود بالفعل" : "This file already exists" }}');
            isValid = false;
            if (!firstInvalidInput) firstInvalidInput = documentsInput;
            break;
          }
        }
      }

      if (isValid) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${MESSAGES.saving}`;
        const formData = new FormData();
        Object.entries({
          '_token': csrfToken,
          'full_name': nameInput.value,
          'job_title': jobTitleInput.value,
          'gender': genderInput.value,
          'employment_type': employmentTypeInput.value,
          'hiring_date': startDateInput.value
        }).forEach(([key, value]) => formData.append(key, value));
        Array.from(files).forEach((file, index) => { formData.append(`documents[${index}]`, file); });
        const documentsToRemove = Array.from(card.querySelectorAll('.existing-document.document-to-remove')).map(doc => parseInt(doc.dataset.documentIndex));
        if (documentsToRemove.length > 0) formData.append('remove_documents', JSON.stringify(documentsToRemove));
        const isEditing = card.dataset.isNew === 'false' && card.dataset.employeeId && !card.dataset.employeeId.toString().startsWith('temp_');
        const url = isEditing ? `{{ route('company.employees_edit', '') }}/${companyId}` : `{{ route('company.employees_add', '') }}/${companyId}`;
        if (isEditing) formData.append('id', card.dataset.employeeId);
        fetch(url, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } })
        .then(async response => {
          const contentType = response.headers.get('content-type');
          let data;
          if (contentType && contentType.includes('application/json')) {
            try { data = await response.json(); }
            catch (e) {
              if (!response.ok) return Promise.reject(`HTTP ${response.status}: ${response.statusText}`);
              data = { success: true };
            }
          } else {
            const text = await response.text();
            if (!response.ok) return Promise.reject(`HTTP ${response.status}: ${response.statusText}`);
            data = { success: true, message: text };
          }
          if (!response.ok) return Promise.reject(data);
          return data;
        })
        .then(data => {
          if (data.errors) { handleServerValidationErrors(card, data.errors); return; }
          if (data.success || data.state || data.employee) {
            handleSuccessfulSubmission(card, data, { name: nameInput, jobTitle: jobTitleInput, gender: genderInput, employmentType: employmentTypeInput, startDate: startDateInput });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          let errorMessage = MESSAGES.error;
          if (typeof error === 'string') errorMessage = error;
          else if (error?.message) errorMessage = error.message;
          else if (error?.errors) {
            if (error.errors.general?.[0]) errorMessage = error.errors.general[0];
            else if (error.errors.employee?.[0]) errorMessage = error.errors.employee[0];
            else { const firstError = Object.values(error.errors)[0]; errorMessage = Array.isArray(firstError) ? firstError[0] : firstError; }
          }
          releaseAlert('danger', { title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}', message: errorMessage });
        })
        .finally(() => {
          submitBtn.disabled = false;
          const isEditingNow = card.dataset.isNew === 'false' && card.dataset.employeeId && !card.dataset.employeeId.toString().startsWith('temp_');
          submitBtn.innerHTML = isEditingNow ? '{{ __("admin.Update") }}' : '{{ __("admin.Save") }}';
        });
      } else {
        if (firstInvalidInput) firstInvalidInput.focus();
      }
    } else if (editBtn) {
      handleEditMode(card);
    } else if (deleteBtn) {
      handleEmployeeDelete(card);
    } else if (removeUnsavedBtn) {
      const isLastCard = document.querySelectorAll('.employee-card').length === 1;
      card.remove();
      if (isLastCard) createEmployeeCard(true);
      else updateEmployeeNumbers();
    } else if (event.target.closest('.remove-document-btn')) {
      handleDocumentRemoval(event.target.closest('.remove-document-btn'));
    } else if (event.target.closest('.restore-document-btn')) {
      handleDocumentRestore(event.target.closest('.restore-document-btn'));
    } else if (cancelEditBtn) {
      handleCancelEdit(card);
    }
  });

  $('.employee-hiring-date').flatpickr({ dateFormat: "Y-m-d" });
});
</script>
@endpush