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
