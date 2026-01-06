<!-- Selected Managers Bar -->
<div class="card bg-light-subtle shadow-none border-bottom border-top rounded-0">
  <div class="card-body py-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="card-title mb-0 ps-2">
        <i class="ti ti-users me-2"></i>{{ __('admin.Selected Managers') }}
      </h6>
      <button type="button" class="btn btn-sm btn-outline-danger border-0" id="clearAllManagers" style="display: none;">
        <i class="ti ti-trash me-1"></i>{{ __('admin.Clear All') }}
      </button>
    </div>
    
    <div id="selectedManagersContainer" class="d-flex flex-wrap gap-2 p-2 bg-light-subtle rounded border border-dashed" style="min-height: 50px;">
      <span class="text-muted my-auto no-managers-message w-100 text-center">{{ __('admin.No managers selected') }}</span>
    </div>
    
    <!-- Hidden inputs container -->
    <div id="managerIdsContainer"></div>
  </div>
</div>

<div class="card shadow-none">
  <div class="card-header border-bottom py-4 mb-6">
    <!-- Navigation Tabs -->
    <ul class="nav nav-pills gap-2" id="managerTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active border" id="existing-manager-tab" data-bs-toggle="tab" data-bs-target="#existing-manager-content" type="button">
          <i class="ti ti-user-search me-2"></i>{{ __('admin.Select Existing Manager') }}
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link border" id="create-manager-tab" data-bs-toggle="tab" data-bs-target="#create-manager-content" type="button">
          <i class="ti ti-user-plus me-2"></i>{{ __('admin.Create New Manager') }}
        </button>
      </li>
    </ul>

  </div>
  <div class="card-body">
    <div class="tab-content bg-card p-0 shadow-none">
      <!-- Tab: Select Existing Manager -->
      <div class="tab-pane fade show active" id="existing-manager-content">
        <div class="input-group input-group-lg mb-6">
          <span class="input-group-text bg-white"><i class="ti ti-search text-muted"></i></span>
          <input type="text" class="form-control border-start-0" id="managerSearchInput" placeholder="{{ __('admin.Search by name, email, or role') }}...">
        </div>
    
        <div id="managersList" class="list-group">
          <!-- Populated via JS -->
        </div>
    
        <!-- States -->
        <div id="managersLoading" class="text-center py-5" style="display: none;">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
    
        <div id="managersEmpty" class="text-center py-5 text-muted" style="display: none;">
          <i class="ti ti-users-minus fs-1 d-block mb-2"></i>
          {{ __('admin.No managers found') }}
        </div>
    
        <!-- Pagination -->
        <div id="managersPagination" class="d-flex justify-content-between align-items-center mt-3 border-top pt-3" style="display: none;">
          <small class="text-muted" id="managersInfo"></small>
          <nav><ul class="pagination pagination-sm mb-0" id="managersPaginationList"></ul></nav>
        </div>
      </div>
    
      <!-- Tab: Create New Manager -->
      <div class="tab-pane fade" id="create-manager-content">
        <form id="createManagerForm">
          <div class="row g-4">
            @foreach(['en' => 'English', 'ar' => 'Arabic'] as $key => $lang)
            <div class="col-md-6">
              <label class="form-label">{{ __("admin.First Name ($lang)") }} @if($key == 'en')<span class="text-danger">*</span>@endif</label>
              <input type="text" class="form-control" name="first_name_{{$key}}" id="manager_first_name_{{$key}}" dir="{{ $key == 'ar' ? 'rtl' : 'ltr' }}" placeholder="{{ $key == 'ar' ? __("admin.Enter first name in Arabic") : __("admin.Enter first name") }}">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __("admin.Last Name ($lang)") }} @if($key == 'en')<span class="text-danger">*</span>@endif</label>
              <input type="text" class="form-control" name="last_name_{{$key}}" id="manager_last_name_{{$key}}" dir="{{ $key == 'ar' ? 'rtl' : 'ltr' }}" placeholder="{{ $key == 'ar' ? __("admin.Enter last name in Arabic") : __("admin.Enter last name") }}">
              <div class="invalid-feedback"></div>
            </div>
            @endforeach
    
            <div class="col-md-6">
              <label class="form-label">{{ __('admin.Gender') }} <span class="text-danger">*</span></label>
              <select class="form-select" name="gender" id="manager_gender">
                <option value="">{{ __('admin.Select Gender') }}</option>
                <option value="Male">{{ __('admin.Male') }}</option>
                <option value="Female">{{ __('admin.Female') }}</option>
              </select>
              <div class="invalid-feedback"></div>
            </div>
      
            <div class="col-md-6">
              <label class="form-label">{{ __('admin.Email') }} <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email" id="manager_email" placeholder="{{ __('admin.Enter email address') }}">
              <div class="invalid-feedback"></div>
            </div>
      
            <div class="col-md-6">
              <label class="form-label">{{ __('admin.Phone') }} <span class="text-danger">*</span></label>
             <input type="tel" class="form-control" id="manager_phone" name="phone" placeholder="{{ __('admin.Enter phone number') }}">
              <input type="hidden" id="manager_country_code" name="country_code">
              <div class="invalid-feedback"></div>
            </div>
    
            <div class="col-md-6 form-password-toggle">
              <label class="form-label" for="password">{{ __('auth.Password') }}</label>
              <div class="input-group input-group-merge shadow-none">
                <input type="password" name="password" id="manager_password" class="form-control" placeholder="{{ __('admin.Enter password') }}" />
                <span class="input-group-text cursor-pointer border border-light"><i class="ti ti-eye-off"></i></span>
              </div>
              <div class="invalid-feedback"></div>
            </div>
    
            <div class="col-md-6 form-password-toggle">
              <label class="form-label" for="password_confirmation">{{ __('admin.Confirm Password') }}</label>
              <div class="input-group input-group-merge shadow-none">
                <input type="password" name="password_confirmation" id="manager_password_confirmation" class="form-control" placeholder="{{ __('admin.Confirm password') }}" />
                <span class="input-group-text cursor-pointer border border-light"><i class="ti ti-eye-off"></i></span>
              </div>
              <div class="invalid-feedback"></div>
            </div>
      
            <div class="col-12 mt-4 text-end">
              <button type="button" class="btn btn-primary px-4" id="createManagerBtn">
                <i class="ti ti-user-plus me-1"></i>{{ __('admin.Create Manager') }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
const passwordMismatchMessage = '{{ __("admin.Passwords do not match") }}';

$(function () {
  const orgId = "{{ $organization->id ?? 0 }}";
  let selectedManagers = @json($assignedManagers ?? []);
  let searchTimeout = null;
  let currentPage = 0;

  let managerPhoneIti = null;
  const managerPhoneInput = $('#manager_phone')[0];
  const managerCountryCodeInput = $('#manager_country_code')[0];

  // --- Core UI Logic ---

  function syncSelection() {
    const $container = $('#selectedManagersContainer');
    const $inputStore = $('#managerIdsContainer');
    const $noMsg = $('.no-managers-message');
    const $clearBtn = $('#clearAllManagers');

    $container.find('.badge').remove();
    $inputStore.empty();

    if (selectedManagers.length === 0) {
      $noMsg.show();
      $clearBtn.hide();
    } else {
      $noMsg.hide();
      $clearBtn.show();

      selectedManagers.forEach(m => {
        $container.append(`
          <span class="badge bg-label-primary d-flex align-items-center gap-1 p-2 fw-normal border">
            ${m.name}
            <i class="ti ti-x cursor-pointer remove-manager" data-id="${m.id}"></i>
          </span>
        `);
        $inputStore.append(`<input type="hidden" name="manager_ids[]" value="${m.id}">`);
      });
    }
      
    // Sync List Checkboxes
    $('.manager-checkbox').prop('checked', false).closest('.list-group-item').removeClass('active bg-light-subtle');
    selectedManagers.forEach(m => {
      $(`.manager-card[data-id="${m.id}"]`).addClass('active bg-light-subtle').find('.manager-checkbox').prop('checked', true);
    });
  }

  // --- Data Loading ---

  function loadManagers(page = 0) {
    currentPage = page;
    const query = $('#managerSearchInput').val();
    
    $('#managersList, #managersEmpty, #managersPagination').hide();
    $('#managersLoading').show();

    $.get('{{ route("organization.wizard.managers.list", ":id") }}'.replace(':id', orgId), {
      search_term: query,
      start: page * 5,
      length: 5
    }, function(res) {
      $('#managersLoading').hide();
      if (!res.data || res.data.length === 0) return $('#managersEmpty').show();

      const items = res.data.map(m => {
        const isChecked = selectedManagers.some(s => s.id == m.id);
        return `
          <div class="list-group-item list-group-item-action d-flex align-items-center p-3 cursor-pointer manager-card ${isChecked ? 'active bg-light-subtle' : ''}" 
                data-id="${m.id}" data-name="${m.name}" data-email="${m.email}">
            <div class="form-check mb-0 me-3">
              <input class="form-check-input manager-checkbox" type="checkbox" ${isChecked ? 'checked' : ''}>
            </div>
            <div class="avatar me-3">
              <span class="avatar-initial rounded-circle bg-label-secondary">${m.name.charAt(0)}</span>
            </div>
            <div class="flex-grow-1">
              <h6 class="mb-0 fw-bold">${m.name}</h6>
              <small class="text-muted">${m.email}</small>
              <div class="mt-1 small text-muted">
                <span class="me-2"><i class="ti ti-briefcase me-1"></i>${m.role}</span>
                <span><i class="ti ti-building me-1"></i>${m.organizations_count} {{ __('admin.Organizations') }}</span>
              </div>
            </div>
          </div>`;
      });

      $('#managersList').html(items.join('')).show();
      renderPagination(page, Math.ceil(res.recordsTotal / 5), res.recordsTotal);
    });
  }

  function renderPagination(current, total, totalRecords) {
    if (total <= 1) return;
    const $list = $('#managersPaginationList').empty();
    $('#managersInfo').text(`Showing ${current * 5 + 1} - ${Math.min((current + 1) * 5, totalRecords)} of ${totalRecords}`);
    
    $list.append(`<li class="page-item ${current === 0 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current - 1}">&laquo;</a></li>`);
    for (let i = 0; i < total; i++) {
      if (i === 0 || i === total - 1 || Math.abs(i - current) <= 1) {
        $list.append(`<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i + 1}</a></li>`);
      } else if (Math.abs(i - current) === 2) {
        $list.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
      }
    }
    $list.append(`<li class="page-item ${current >= total - 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current + 1}">&raquo;</a></li>`);
    $('#managersPagination').show();
  }

  // --- Events ---

  $(document).on('click', '.manager-card', function(e) {
    if ($(e.target).is('input')) return;
    const $cb = $(this).find('.manager-checkbox');
    $cb.prop('checked', !$cb.prop('checked')).trigger('change');
  });

  $(document).on('change', '.manager-checkbox', function() {
    const $card = $(this).closest('.manager-card');
    const data = { id: $card.data('id'), name: $card.data('name'), email: $card.data('email') };

    if (this.checked) {
      if (!selectedManagers.find(m => m.id == data.id)) selectedManagers.push(data);
    } else {
      selectedManagers = selectedManagers.filter(m => m.id != data.id);
    }
    syncSelection();
  });

  $(document).on('click', '.remove-manager', function() {
    selectedManagers = selectedManagers.filter(m => m.id != $(this).data('id'));
    syncSelection();
  });

  $('#clearAllManagers').on('click', function() {
    selectedManagers = [];
    syncSelection();
  });

  $('#managerSearchInput').on('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadManagers(0), 400);
  });

  $(document).on('click', '#managersPaginationList .page-link', function(e) {
    e.preventDefault();
    loadManagers($(this).data('page'));
  });

  // --- Form Logic ---
  $('#createManagerBtn').on('click', function () {
    const $btn = $(this);

    // Client-side phone validation
    if (managerPhoneIti && managerPhoneInput.value.trim() && !managerPhoneIti.isValidNumber()) {
      // attach invalid feedback 
      $('#manager_phone').addClass('is-invalid');
      $('#manager_phone').closest('.col-md-6').find('.invalid-feedback').text('{{ __("auth.Invalid_Phone_Number") }}');
      return; // Prevent submission if phone is invalid
    }

    $btn.prop('disabled', true);
    $.ajax({
      url: '{{ route("organization.wizard.managers.create", ":id") }}'.replace(':id', orgId),
      method: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        first_name_en: $('#manager_first_name_en').val(),
        last_name_en: $('#manager_last_name_en').val(),
        first_name_ar: $('#manager_first_name_ar').val(),
        last_name_ar: $('#manager_last_name_ar').val(),
        gender: $('#manager_gender').val(),
        email: $('#manager_email').val(),
        country_code: $('#manager_country_code').val(),
        phone: $('#manager_phone').val(),
        password: $('#manager_password').val(),
        password_confirmation: $('#manager_password_confirmation').val()
      },
      success(res) {
        if (res.success) {
          selectedManagers.push(res.manager);
          syncSelection();
          $('#createManagerForm').trigger('reset');
          $('#existing-manager-tab').tab('show');
          loadManagers(0);

          // Hide validation errors
          $('#createManagerForm').find('.is-invalid').removeClass('is-invalid');
          $('#createManagerForm').find('.invalid-feedback').text('');

          releaseSuccessAlert({
            title: '{{ __("admin.success") }}',
            message: '{{ __("admin.Manager created successfully") }}'
          });
        }
      },
      error(xhr) {
        if (xhr.responseJSON?.errors) {
          const errors = xhr.responseJSON.errors;
          const fields = ['first_name_en', 'last_name_en', 'first_name_ar', 'last_name_ar', 'gender', 'email', 'phone', 'password', 'password_confirmation'];

          fields.forEach(field => {
            const el = $(`[name="${field}"]`);
            if (errors[field]) {
              const messages = errors[field];
              el.addClass('is-invalid');

              const mismatchMsg = messages.find(msg => msg === passwordMismatchMessage);
              if (field === 'password' && mismatchMsg) {
                // Set mismatch error on both password fields
                el.closest('.col-md-6').find('.invalid-feedback').text(mismatchMsg);
                $(`[name="password_confirmation"]`).addClass('is-invalid').closest('.col-md-6').find('.invalid-feedback').text(mismatchMsg);
              } else {
                // Set the first message for other errors
                el.closest('.col-md-6, .col-12').find('.invalid-feedback').text(messages[0]);
              }
            } else {
              el.removeClass('is-invalid');
              el.closest('.col-md-6, .col-12').find('.invalid-feedback').text('');
            }
          });
        }
      },
      complete() {
        $btn.prop('disabled', false);
      }
    });
  });

  // Initialize phone input for manager creation
  if (managerPhoneInput) {
    managerPhoneIti = intlTelInput(managerPhoneInput, {
      initialCountry: 'eg',
      preferredCountries: ['eg', 'ae', 'sa', 'ma', 'dz', 'tn'],
      utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js',
      formatOnDisplay: false,
      separateDialCode: true,
      autoPlaceholder: 'aggressive',
    });

    managerPhoneInput.addEventListener('countrychange', function() {
      const countryData = managerPhoneIti.getSelectedCountryData();
      managerCountryCodeInput.value = countryData.dialCode;
    });

    setTimeout(function() {
      if (managerPhoneIti) {
        const countryData = managerPhoneIti.getSelectedCountryData();
        if (countryData.dialCode) {
          managerCountryCodeInput.value = countryData.dialCode;
        }
      }
    }, 100);
  }

  // --- Initialization ---
  syncSelection();
  loadManagers(0);
});
</script>
@endpush
