@php
  $org = $organization ?? null;
  $locale = app()->getLocale();
  $isRtl = $locale === 'ar';
  $managers = $assignedManagers ?? collect();
@endphp

<!-- Selected Managers Bar -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-bold text-primary mb-0">
      <i class="ti ti-users me-2"></i>{{ __('admin.Selected Managers') }}
    </h6>
    <button type="button" class="btn btn-sm btn-label-danger clear-all-managers-btn" style="display: none;">
      <i class="ti ti-trash me-1"></i>{{ __('admin.Clear All') }}
    </button>
  </div>
  <div id="selectedManagersContainer" class="d-flex flex-wrap gap-2 p-3 bg-light rounded min-height-50">
    <span class="text-muted no-managers-message">{{ __('admin.No managers selected') }}</span>
  </div>
  <!-- Hidden inputs for manager IDs -->
  <div id="managerIdsContainer"></div>
</div>

<!-- Tabs for Existing / Create -->
<ul class="nav nav-tabs mb-3" id="managerTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="existing-manager-tab" data-bs-toggle="tab" data-bs-target="#existing-manager-content" type="button" role="tab">
      <i class="ti ti-user-search me-2"></i>{{ __('admin.Select Existing Manager') }}
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="create-manager-tab" data-bs-toggle="tab" data-bs-target="#create-manager-content" type="button" role="tab">
      <i class="ti ti-user-plus me-2"></i>{{ __('admin.Create New Manager') }}
    </button>
  </li>
</ul>

<div class="tab-content" id="managerTabsContent">
  <!-- Tab: Select Existing Manager -->
  <div class="tab-pane fade show active" id="existing-manager-content" role="tabpanel">
    <!-- Search -->
    <div class="mb-3">
      <div class="position-relative">
        <i class="ti ti-search position-absolute top-50 translate-middle-y {{ $isRtl ? 'end-0 me-3' : 'start-0 ms-3' }} text-muted"></i>
        <input type="text" class="form-control form-control-lg rounded-3 {{ $isRtl ? 'pe-5' : 'ps-5' }}" id="managerSearchInput" placeholder="{{ __('admin.Search by name, email, or role') }}...">
      </div>
    </div>

    <!-- Managers List -->
    <div id="managersListContainer">
      <div class="managers-list" id="managersList">
        <!-- Cards will be populated via JavaScript -->
      </div>

      <!-- Loading Spinner -->
      <div class="text-center py-4" id="managersLoading" style="display: none;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">{{ __('admin.Loading') }}...</span>
        </div>
      </div>

      <!-- Empty State -->
      <div class="text-center py-4 text-muted" id="managersEmpty" style="display: none;">
        <i class="ti ti-users-minus fs-1 mb-2 d-block"></i>
        {{ __('admin.No managers found') }}
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center mt-3" id="managersPagination" style="display: none;">
        <small class="text-muted" id="managersInfo"></small>
        <nav>
          <ul class="pagination pagination-sm mb-0" id="managersPaginationList">
            <!-- Pagination will be populated via JavaScript -->
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <!-- Tab: Create New Manager -->
  <div class="tab-pane fade" id="create-manager-content" role="tabpanel">
    <div id="createManagerForm">
      <div class="row">
        <!-- First Name (English) -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold" for="manager_first_name_en">
            {{ __('admin.First Name (English)') }} <span class="text-danger">*</span>
          </label>
          <input type="text" class="form-control" id="manager_first_name_en" name="manager_first_name_en" placeholder="{{ __('admin.Enter first name') }}" maxlength="255">
          <div class="invalid-feedback"></div>
        </div>

        <!-- Last Name (English) -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold" for="manager_last_name_en">
            {{ __('admin.Last Name (English)') }} <span class="text-danger">*</span>
          </label>
          <input type="text" class="form-control" id="manager_last_name_en" name="manager_last_name_en" placeholder="{{ __('admin.Enter last name') }}" maxlength="255">
          <div class="invalid-feedback"></div>
        </div>

        <!-- First Name (Arabic) -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold" for="manager_first_name_ar">
            {{ __('admin.First Name (Arabic)') }}
            <small class="text-muted">({{ __('admin.Optional') }})</small>
          </label>
          <input type="text" class="form-control" id="manager_first_name_ar" name="manager_first_name_ar" placeholder="{{ __('admin.Enter first name in Arabic') }}" dir="rtl" maxlength="255">
          <div class="invalid-feedback"></div>
        </div>

        <!-- Last Name (Arabic) -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold" for="manager_last_name_ar">
            {{ __('admin.Last Name (Arabic)') }}
            <small class="text-muted">({{ __('admin.Optional') }})</small>
          </label>
          <input type="text" class="form-control" id="manager_last_name_ar" name="manager_last_name_ar" placeholder="{{ __('admin.Enter last name in Arabic') }}" dir="rtl" maxlength="255">
          <div class="invalid-feedback"></div>
        </div>

        <!-- Gender -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold" for="manager_gender">
            {{ __('admin.Gender') }} <span class="text-danger">*</span>
          </label>
          <select class="form-select" id="manager_gender" name="manager_gender">
            <option value="">{{ __('admin.Select Gender') }}</option>
            <option value="Male">{{ __('admin.Male') }}</option>
            <option value="Female">{{ __('admin.Female') }}</option>
          </select>
          <div class="invalid-feedback"></div>
        </div>

        <!-- Email -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold" for="manager_email">
            {{ __('admin.Email') }} <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-mail"></i></span>
            <input type="email" class="form-control" id="manager_email" name="manager_email" placeholder="{{ __('admin.Enter email address') }}" maxlength="255">
            <div class="invalid-feedback"></div>
          </div>
        </div>

        <!-- Phone -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold" for="manager_phone">
            {{ __('admin.Phone') }} <span class="text-danger">*</span>
          </label>
          <input type="tel" class="form-control" id="manager_phone" name="manager_phone" placeholder="{{ __('admin.Enter phone number') }}">
          <input type="hidden" id="manager_country_code" name="manager_country_code">
          <small class="text-danger mt-1" id="manager-phone-error" style="display: none;"></small>
          <div class="invalid-feedback"></div>
        </div>

        <!-- Password -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold" for="manager_password">
            {{ __('admin.Password') }} <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <input type="password" class="form-control" id="manager_password" name="manager_password" placeholder="{{ __('admin.Enter password') }}" minlength="8">
            <button type="button" class="btn btn-outline-secondary toggle-password-btn" tabindex="-1">
              <i class="ti ti-eye"></i>
            </button>
            <div class="invalid-feedback"></div>
          </div>
          <small class="text-muted">{{ __('admin.Password must include uppercase, lowercase, number and special character') }}</small>
        </div>

        <!-- Confirm Password -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold" for="manager_password_confirmation">
            {{ __('admin.Confirm Password') }} <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <input type="password" class="form-control" id="manager_password_confirmation" name="manager_password_confirmation" placeholder="{{ __('admin.Confirm password') }}">
            <button type="button" class="btn btn-outline-secondary toggle-password-btn" tabindex="-1">
              <i class="ti ti-eye"></i>
            </button>
            <div class="invalid-feedback"></div>
          </div>
        </div>

        <!-- Create Button -->
        <div class="col-12 mt-3">
          <button type="button" class="btn btn-success" id="createManagerBtn">
            <i class="ti ti-user-plus me-2"></i>{{ __('admin.Create Manager') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .min-height-50 {
    min-height: 50px;
  }
  .manager-tag {
    display: inline-flex;
    align-items: center;
    background-color: #e7f1ff;
    color: #0d6efd;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
  }
  .manager-tag .remove-manager {
    margin-{{ $isRtl ? 'right' : 'left' }}: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
  }
  .manager-tag .remove-manager:hover {
    opacity: 1;
  }

  /* Manager Card Styles */
  .managers-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  .manager-card {
    display: flex;
    align-items: center;
    padding: 1rem 1.25rem;
    border: 2px solid #e9ecef;
    border-radius: 0.75rem;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .manager-card:hover {
    border-color: #c5ccd4;
    background-color: #fafbfc;
  }
  .manager-card.selected {
    border-color: #696cff;
    background-color: #f8f8ff;
  }
  .manager-card .manager-checkbox {
    width: 1.25rem;
    height: 1.25rem;
    margin-{{ $isRtl ? 'left' : 'right' }}: 1rem;
    cursor: pointer;
    accent-color: #696cff;
  }
  .manager-card .manager-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    margin-{{ $isRtl ? 'left' : 'right' }}: 1rem;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: #6c757d;
    flex-shrink: 0;
  }
  .manager-card .manager-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
  }
  .manager-card .manager-info {
    flex: 1;
    min-width: 0;
  }
  .manager-card .manager-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.125rem;
    font-size: 0.95rem;
  }
  .manager-card .manager-email {
    color: #6c757d;
    font-size: 0.85rem;
    margin-bottom: 0.375rem;
  }
  .manager-card .manager-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.8rem;
    color: #6c757d;
  }
  .manager-card .manager-meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
  }
  .manager-card .manager-meta-item i {
    font-size: 0.9rem;
    opacity: 0.7;
  }
</style>

@push('scripts')
<script>
@if($isEdit)

// Selected managers state
let selectedManagers = @json($assignedManagers ?? []);

// Initialize selected managers on load
function initializeSelectedManagers() {
  updateSelectedManagersUI();
  updateManagerIdsInputs();
  updatePublishButtonVisibility();
}

// Update the selected managers UI (tags bar)
function updateSelectedManagersUI() {
  const container = $('#selectedManagersContainer');
  const noManagersMsg = container.find('.no-managers-message');
  const clearAllBtn = $('.clear-all-managers-btn');

  // Remove existing tags (keep the no-managers message)
  container.find('.manager-tag').remove();

  if (selectedManagers.length === 0) {
    noManagersMsg.show();
    clearAllBtn.hide();
  } else {
    noManagersMsg.hide();
    clearAllBtn.show();

    selectedManagers.forEach(function(manager) {
      const tag = $('<span class="manager-tag">' +
        manager.name +
        ' <i class="ti ti-x remove-manager" data-id="' + manager.id + '"></i>' +
        '</span>');
      container.append(tag);
    });
  }
}

// Update hidden inputs for manager IDs
function updateManagerIdsInputs() {
  const container = $('#managerIdsContainer');
  container.empty();

  selectedManagers.forEach(function(manager) {
    container.append('<input type="hidden" name="manager_ids[]" value="' + manager.id + '">');
  });
}

// Update publish button visibility
function updatePublishButtonVisibility() {
  const publishBtn = $('#publishBtnStep3');
  if (selectedManagers.length > 0) {
    publishBtn.show();
  } else {
    publishBtn.hide();
  }
}

// Add manager to selection
function addManager(manager) {
  // Check if already selected
  if (selectedManagers.find(m => m.id === manager.id)) {
    return false;
  }
  selectedManagers.push(manager);
  updateSelectedManagersUI();
  updateManagerIdsInputs();
  updatePublishButtonVisibility();
  return true;
}

// Remove manager from selection
function removeManager(managerId) {
  selectedManagers = selectedManagers.filter(m => m.id !== managerId);
  updateSelectedManagersUI();
  updateManagerIdsInputs();
  updatePublishButtonVisibility();
  // Update card checkbox if visible
  $('.manager-card').each(function() {
    if ($(this).data('id') == managerId) {
      $(this).find('.manager-checkbox').prop('checked', false);
      $(this).removeClass('selected');
    }
  });
}

// Clear all managers
$('.clear-all-managers-btn').on('click', function() {
  selectedManagers = [];
  updateSelectedManagersUI();
  updateManagerIdsInputs();
  updatePublishButtonVisibility();
  // Uncheck all checkboxes in cards
  $('.manager-card .manager-checkbox').prop('checked', false);
  $('.manager-card').removeClass('selected');
});

// Remove manager tag click
$(document).on('click', '.remove-manager', function() {
  const managerId = $(this).data('id');
  removeManager(managerId);
});

// Managers list state
let searchTimeout = null;
let currentPage = 0;
let pageLength = 5;
let managersInitialized = false;

// Load managers list
function loadManagersList(page = 0) {
  const $list = $('#managersList');
  const $loading = $('#managersLoading');
  const $empty = $('#managersEmpty');
  const $pagination = $('#managersPagination');

  $list.hide();
  $empty.hide();
  $pagination.hide();
  $loading.show();

  $.ajax({
    url: '{{ route("organization.wizard.managers.list", $organization->id ?? 0) }}',
    type: 'GET',
    data: {
      search_term: $('#managerSearchInput').val(),
      start: page * pageLength,
      length: pageLength,
      draw: 1
    },
    success: function(response) {
      $loading.hide();

      if (response.data && response.data.length > 0) {
        renderManagerCards(response.data);
        $list.show();

        // Update pagination
        const totalPages = Math.ceil(response.recordsTotal / pageLength);
        if (totalPages > 1) {
          renderPagination(page, totalPages, response.recordsTotal);
          $pagination.show();
        }
      } else {
        $empty.show();
      }
    },
    error: function() {
      $loading.hide();
      $empty.show();
    }
  });
}

// Render manager cards
function renderManagerCards(managers) {
  const $list = $('#managersList');
  $list.empty();

  managers.forEach(function(manager) {
    const isSelected = selectedManagers.find(m => m.id === manager.id);
    const initials = getInitials(manager.name);
    const avatarUrl = manager.avatar || null;

    const card = `
      <div class="manager-card ${isSelected ? 'selected' : ''}" data-id="${manager.id}" data-name="${escapeHtml(manager.name)}" data-email="${escapeHtml(manager.email)}">
        <input type="checkbox" class="manager-checkbox" ${isSelected ? 'checked' : ''}>
        <div class="manager-avatar">
          ${avatarUrl
            ? `<img src="${avatarUrl}" onerror="this.src='/images/placeholder.jpg'" alt="${escapeHtml(manager.name)}">`
            : `<i class="ti ti-user"></i>`
          }
        </div>
        <div class="manager-info">
          <div class="manager-name">${escapeHtml(manager.name)}</div>
          <div class="manager-email">${escapeHtml(manager.email)}</div>
          <div class="manager-meta">
            <span class="manager-meta-item">
              <i class="ti ti-briefcase"></i>
              ${escapeHtml(manager.role)}
            </span>
            <span class="manager-meta-item">
              <i class="ti ti-building"></i>
              ${manager.organizations_count} {{ __('admin.Organizations') }}
            </span>
          </div>
        </div>
      </div>
    `;
    $list.append(card);
  });
}

// Render pagination
function renderPagination(currentPage, totalPages, totalRecords) {
  const $info = $('#managersInfo');
  const $list = $('#managersPaginationList');

  const start = currentPage * pageLength + 1;
  const end = Math.min((currentPage + 1) * pageLength, totalRecords);
  $info.text(lang === 'ar'
    ? `عرض ${start} - ${end} من ${totalRecords}`
    : `Showing ${start} - ${end} of ${totalRecords}`
  );

  $list.empty();

  // Previous button
  $list.append(`
    <li class="page-item ${currentPage === 0 ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo;</a>
    </li>
  `);

  // Page numbers
  for (let i = 0; i < totalPages; i++) {
    if (totalPages <= 5 || i === 0 || i === totalPages - 1 || Math.abs(i - currentPage) <= 1) {
      $list.append(`
        <li class="page-item ${i === currentPage ? 'active' : ''}">
          <a class="page-link" href="#" data-page="${i}">${i + 1}</a>
        </li>
      `);
    } else if (Math.abs(i - currentPage) === 2) {
      $list.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
    }
  }

  // Next button
  $list.append(`
    <li class="page-item ${currentPage >= totalPages - 1 ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${currentPage + 1}">&raquo;</a>
    </li>
  `);
}

// Get initials from name
function getInitials(name) {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
}

// Escape HTML
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Pagination click handler
$(document).on('click', '#managersPaginationList .page-link', function(e) {
  e.preventDefault();
  const page = $(this).data('page');
  if (page !== undefined && !$(this).parent().hasClass('disabled')) {
    currentPage = page;
    loadManagersList(page);
  }
});

// Initialize managers list when step 3 tab is shown
$('[data-bs-target="#pills-organization_manager"]').on('shown.bs.tab', function() {
  if (!managersInitialized) {
    loadManagersList(0);
    managersInitialized = true;
  }
});

// If we're already on step 3, initialize immediately
if ({{ $activeStep }} === 3) {
  setTimeout(function() {
    loadManagersList(0);
    managersInitialized = true;
  }, 100);
}

// Search input handler with debounce
$('#managerSearchInput').on('input', function() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(function() {
    currentPage = 0;
    loadManagersList(0);
  }, 300);
});

// Card click handler
$(document).on('click', '.manager-card', function(e) {
  if ($(e.target).is('input[type="checkbox"]')) return;

  const $card = $(this);
  const $checkbox = $card.find('.manager-checkbox');
  $checkbox.prop('checked', !$checkbox.prop('checked'));

  const managerId = $card.data('id');
  const managerName = $card.data('name');
  const managerEmail = $card.data('email');

  if ($checkbox.is(':checked')) {
    addManager({ id: managerId, name: managerName, email: managerEmail });
    $card.addClass('selected');
  } else {
    removeManager(managerId);
    $card.removeClass('selected');
  }
});

// Checkbox change handler
$(document).on('change', '.manager-card .manager-checkbox', function(e) {
  e.stopPropagation();
  const $card = $(this).closest('.manager-card');
  const managerId = $card.data('id');
  const managerName = $card.data('name');
  const managerEmail = $card.data('email');

  if ($(this).is(':checked')) {
    addManager({ id: managerId, name: managerName, email: managerEmail });
    $card.addClass('selected');
  } else {
    removeManager(managerId);
    $card.removeClass('selected');
  }
});

// Initialize phone input for manager creation
let managerPhoneIti = null;
const managerPhoneInput = document.getElementById('manager_phone');
const managerPhoneError = document.getElementById('manager-phone-error');
const managerCountryCodeInput = document.getElementById('manager_country_code');

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

  managerPhoneInput.addEventListener('input', function() {
    if (managerPhoneInput.value.trim() && !managerPhoneIti.isValidNumber()) {
      managerPhoneError.style.display = 'block';
      managerPhoneError.innerText = lang === 'en' ? 'Invalid phone number' : 'رقم الهاتف غير صحيح';
    } else {
      managerPhoneError.style.display = 'none';
    }
  });
}

// Toggle password visibility
$(document).on('click', '.toggle-password-btn', function() {
  const input = $(this).siblings('input');
  const icon = $(this).find('i');

  if (input.attr('type') === 'password') {
    input.attr('type', 'text');
    icon.removeClass('ti-eye').addClass('ti-eye-off');
  } else {
    input.attr('type', 'password');
    icon.removeClass('ti-eye-off').addClass('ti-eye');
  }
});

// Validate create manager form
function validateCreateManagerForm() {
  let isValid = true;
  const form = $('#createManagerForm');

  // Clear previous errors
  form.find('.form-control, .form-select').removeClass('is-invalid');
  form.find('.invalid-feedback').text('');

  // First Name (EN)
  const firstNameEn = $('#manager_first_name_en');
  if (!firstNameEn.val().trim()) {
    firstNameEn.addClass('is-invalid');
    firstNameEn.siblings('.invalid-feedback').text('{{ __("admin.Please enter the first name") }}');
    isValid = false;
  }

  // Last Name (EN)
  const lastNameEn = $('#manager_last_name_en');
  if (!lastNameEn.val().trim()) {
    lastNameEn.addClass('is-invalid');
    lastNameEn.siblings('.invalid-feedback').text('{{ __("admin.Please enter the last name") }}');
    isValid = false;
  }

  // Gender
  const gender = $('#manager_gender');
  if (!gender.val()) {
    gender.addClass('is-invalid');
    gender.siblings('.invalid-feedback').text('{{ __("admin.Please select the gender") }}');
    isValid = false;
  }

  // Email
  const email = $('#manager_email');
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email.val().trim()) {
    email.addClass('is-invalid');
    email.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please enter the email address") }}');
    isValid = false;
  } else if (!emailRegex.test(email.val().trim())) {
    email.addClass('is-invalid');
    email.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please enter a valid email address") }}');
    isValid = false;
  }

  // Phone
  const phone = $('#manager_phone');
  if (!phone.val().trim()) {
    phone.addClass('is-invalid');
    phone.siblings('.invalid-feedback').text('{{ __("admin.Please enter the phone number") }}');
    isValid = false;
  } else if (managerPhoneIti && !managerPhoneIti.isValidNumber()) {
    phone.addClass('is-invalid');
    phone.siblings('.invalid-feedback').text(lang === 'en' ? 'Invalid phone number' : 'رقم الهاتف غير صحيح');
    isValid = false;
  }

  // Password
  const password = $('#manager_password');
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/;
  if (!password.val()) {
    password.addClass('is-invalid');
    password.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please enter the password") }}');
    isValid = false;
  } else if (!passwordRegex.test(password.val())) {
    password.addClass('is-invalid');
    password.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Password must include uppercase, lowercase, number and special character") }}');
    isValid = false;
  }

  // Confirm Password
  const confirmPassword = $('#manager_password_confirmation');
  if (!confirmPassword.val()) {
    confirmPassword.addClass('is-invalid');
    confirmPassword.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please confirm the password") }}');
    isValid = false;
  } else if (confirmPassword.val() !== password.val()) {
    confirmPassword.addClass('is-invalid');
    confirmPassword.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Passwords do not match") }}');
    isValid = false;
  }

  return isValid;
}

// Create manager button handler
$('#createManagerBtn').on('click', function() {
  if (!validateCreateManagerForm()) {
    return;
  }

  const btn = $(this);
  btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-2"></i>{{ __("admin.Creating...") }}');

  $.ajax({
    url: '{{ route("organization.wizard.managers.create", $organization->id ?? 0) }}',
    type: 'POST',
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
    success: function(response) {
      if (response.success) {
        // Add the new manager to selection
        addManager(response.manager);

        // Clear form
        $('#createManagerForm input').val('');
        $('#createManagerForm select').val('');

        // Reset phone input
        if (managerPhoneIti) {
          managerPhoneIti.setCountry('eg');
        }

        // Show success message
        if (typeof toastr !== 'undefined') {
          toastr.success(response.message);
        }

        // Reload managers list to show new manager
        loadManagersList(0);

        // Switch to existing managers tab
        $('#existing-manager-tab').tab('show');
      }
    },
    error: function(xhr) {
      let errorMessage = '{{ __("admin.Error creating manager") }}';
      if (xhr.responseJSON) {
        if (xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }
        if (xhr.responseJSON.errors) {
          // Show validation errors
          Object.keys(xhr.responseJSON.errors).forEach(function(key) {
            const fieldName = 'manager_' + key;
            const field = $('#' + fieldName);
            if (field.length) {
              field.addClass('is-invalid');
              const feedback = field.siblings('.invalid-feedback').length ?
                field.siblings('.invalid-feedback') :
                field.closest('.input-group').find('.invalid-feedback');
              feedback.text(xhr.responseJSON.errors[key][0]);
            }
          });
        }
      }
      if (typeof toastr !== 'undefined') {
        toastr.error(errorMessage);
      }
    },
    complete: function() {
      btn.prop('disabled', false).html('<i class="ti ti-user-plus me-2"></i>{{ __("admin.Create Manager") }}');
    }
  });
});

// Helper function to submit Step 3 form
function submitStep3Form(redirectTo) {
  // Submit step 3 form
  const step3Form = $('#step3Form');

  // Clear previous inputs (except token, method, redirect_to)
  step3Form.find('input:not([name="_token"]):not([name="_method"]):not([name="redirect_to"])').remove();

  // Set redirect_to value
  $('#redirect_to_step3').val(redirectTo);

  // Add manager IDs
  selectedManagers.forEach(function(manager) {
    step3Form.append('<input type="hidden" name="manager_ids[]" value="' + manager.id + '">');
  });

  step3Form.submit();
}

// Save Changes for Step 3 - validates and stays on same page
$(document).on('click', '.save-changes-step3-btn', function(e) {
  e.preventDefault();

  // Validate at least one manager selected
  if (selectedManagers.length === 0) {
    if (typeof toastr !== 'undefined') {
      toastr.error('{{ __("admin.Please select or create at least one organization manager") }}');
    }
    return;
  }

  submitStep3Form('same');
});

// Save & Continue for Step 3 - validates and goes to next step
$(document).on('click', '.save-continue-step3-btn', function(e) {
  e.preventDefault();

  // Validate at least one manager selected
  if (selectedManagers.length === 0) {
    if (typeof toastr !== 'undefined') {
      toastr.error('{{ __("admin.Please select or create at least one organization manager") }}');
    }
    return;
  }

  submitStep3Form('next');
});

// Save as Draft for Step 3 - NO validation, stays on same page
$(document).on('click', '.save-draft-step3-btn', function(e) {
  e.preventDefault();

  // Copy step 3 form data to draft form
  $('#draft_current_step').val(3);
  copyStep3DataToDraft();
  $('#draftForm').submit();
});

// Helper function to copy Step 3 data to draft form
function copyStep3DataToDraft() {
  const draftForm = $('#draftForm');

  // Remove previous dynamic inputs from draft form (keep token, method, current_step)
  draftForm.find('input:not([name="_token"]):not([name="_method"]):not([name="current_step"])').remove();

  // Copy manager IDs
  selectedManagers.forEach(function(manager) {
    draftForm.append('<input type="hidden" name="manager_ids[]" value="' + manager.id + '">');
  });
}

// Initialize on load
initializeSelectedManagers();

@endif
</script>
@endpush

