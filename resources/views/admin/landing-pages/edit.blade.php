@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
  <style>
    .ql-toolbar .ql-custom-button {
      width: auto !important;
      padding: 0 8px;
      font-weight: 600;
      font-size: 13px;
    }
    .ql-toolbar .ql-custom-button::after {
      content: 'Button';
    }
    .custom-quill-editor.ql-container {
      position: relative;
      height: auto !important;
      z-index: 1;
    }
    .custom-quill-editor.ql-container .ql-editor {
      height: auto !important;
      min-height: 160px;
    }
    .custom-field-group[data-field="content"],
    .custom-field-group[data-field="content_ar"] {
      position: relative;
      z-index: 1;
      margin-bottom: 0.5rem;
    }
    /* Ended programs: grey out the row without touching the "Ended"
       badge/tooltip — opacity is applied to specific siblings of the
       badge (never an ancestor), so the badge stays fully legible
       instead of inheriting a compositing-reduced opacity. */
    .program-row-ended {
      background-color: rgba(94, 96, 103, 0.06);
    }
    .program-row-ended td:not(:first-child) {
      opacity: 0.6;
    }
    .program-row-ended td:first-child .avatar img,
    .program-row-ended td:first-child a.fw-medium,
    .program-row-ended td:first-child .text-muted.small {
      opacity: 0.6;
    }
  </style>
@endsection

@section('content')

  {{-- Header --}}
  <div class="mb-4">
    <h4 class="mb-1">
      {{ $l == 'ar' ? 'تعديل صفحة هبوط' : 'Edit Landing Page' }}
    </h4>
    <p class="text-secondary mb-0">
      {{ $l == 'ar' ? 'أدِر صفحة هبوط مخصصة لبرامجك.' : 'Manage tailored landing page for your programs.' }}
    </p>
  </div>

  {{-- Custom Landing Page URL configuration --}}
  <form class="card mb-4" id="lpUrlForm" novalidate>
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="form-check form-switch mb-0">
            <input
              type="checkbox"
              class="form-check-input lp-url-toggle"
              id="lpUrlEnabledToggle"
              name="lpUrlActive"
              @checked($currentPage->status)
            >
          </div>
          <div>
            <h5 class="card-title mb-0 d-flex align-items-center gap-2">
              <i class="ti ti-link"></i>
              {{ $l == 'ar' ? 'رابط صفحة الهبوط المخصص' : 'Custom Landing Page URL' }}
            </h5>
            <small class="text-muted" id="lpUrlStatusText">
              @if($currentPage->status)
                <span class="text-success">
                  <i class="ti ti-check me-1"></i>
                  {{ $l == 'ar' ? 'رابط صفحة الهبوط نشط' : 'Landing page URL is active' }}
                </span>
              @else
                <span class="text-warning">
                  <i class="ti ti-eye-off me-1"></i>
                  {{ $l == 'ar' ? 'رابط صفحة الهبوط غير نشط' : 'Landing page URL is inactive' }}
                </span>
              @endif
            </small>
            <small class="text-muted @if(!$currentPage->slug) d-none @endif" id="lpUrlSavedLinkWrap">
              <i class="ti ti-link me-1"></i>
              <a
                href="{{ $currentPage->slug ? url('/lp/' . $currentPage->slug) : '#' }}"
                id="lpUrlSavedLink"
                target="_blank"
                rel="noopener"
                class="fw-medium text-primary"
              >{{ $currentPage->slug ? url('/lp/' . $currentPage->slug) : '' }}</a>
            </small>
          </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-outline-primary" id="lpUrlPreviewBtn">
            <i class="ti ti-external-link me-1"></i>
            {{ $l == 'ar' ? 'معاينة صفحة الهبوط' : 'Preview Landing Page' }}
          </button>
          <button type="submit" class="btn btn-primary" id="lpUrlSaveBtn">
            <i class="ti ti-device-floppy me-1"></i>
            {{ $l == 'ar' ? 'حفظ' : 'Save' }}
          </button>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-4">
        <div class="col-md-6">
          <label class="form-label" for="lpUrlTitle">
            {{ $l == 'ar' ? 'العنوان' : 'Title' }}
            <span class="text-danger">*</span>
          </label>
          <input
            type="text"
            class="form-control"
            id="lpUrlTitle"
            name="lpUrlTitle"
            value="{{ $currentPage->title }}"
            maxlength="100"
            placeholder="{{ $l == 'ar' ? 'صيف 2025' : 'Summer 2025' }}"
          >
          <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="lpUrlName">
            {{ $l == 'ar' ? 'اسم الرابط' : 'URL Name' }}
            <span class="text-danger">*</span>
          </label>
          <input
            type="text"
            class="form-control"
            id="lpUrlName"
            name="lpUrlName"
            maxlength="100"
            placeholder="{{ $l == 'ar' ? 'برنامج الصيف' : 'Summer Program' }}"
          >
          <div class="invalid-feedback"></div>
        </div>
        <div class="col-12">
          <label class="form-label" for="lpUrlSlug">
            {{ $l == 'ar' ? 'الرابط المخصص' : 'Custom URL' }}
            <span class="text-danger">*</span>
          </label>
          <div class="row g-2 align-items-start">
            <div class="col-md-6 col-xl-7">
              <div class="input-group input-group-merge shadow-none">
                <span class="input-group-text text-muted">{{ url('/lp/') }}/</span>
                <input
                  type="text"
                  class="form-control"
                  id="lpUrlSlug"
                  name="lpUrlSlug"
                  value="{{ $currentPage->slug }}"
                  maxlength="100"
                  placeholder="summer-2025"
                  pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                >
              </div>
              <div class="invalid-feedback"></div>
              <div id="lpUrlAvailabilityFeedback" class="mt-1 small fw-medium"></div>
              <small class="text-muted">
                {{ $l == 'ar' ? 'يجب أن يحتوي الرابط على أحرف إنجليزية صغيرة وأرقام وشرطات فقط.' : 'URL must contain only lowercase letters, numbers, and hyphens.' }}
              </small>
            </div>
            <div class="col-md-6 col-xl-5 d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-outline-secondary text-nowrap" id="lpUrlGenerateBtn">
                <i class="ti ti-refresh me-1"></i>
                {{ $l == 'ar' ? 'إنشاء من العنوان' : 'Generate from title' }}
              </button>
              <button type="button" class="btn btn-outline-info text-nowrap" id="lpUrlCheckBtn">
                <i class="ti ti-check me-1"></i>
                {{ $l == 'ar' ? 'تحقق من التوفر' : 'Check Availability' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <div class="card mb-4">
    <div class="card-header border-bottom">
      <h6 class="card-title mb-0 d-flex align-items-center gap-2">
        <i class="ti ti-layout-dashboard"></i>{{ __('admin.Landing Page Sections') }}
      </h6>
    </div>
    <div class="card-body p-0">
      <div class="nav-align-left">
        <ul class="nav nav-tabs py-4" role="tablist">
          @foreach ($sections as $index => $section)
            <li class="nav-item py-1" role="presentation">
              <button
                type="button"
                class="nav-link waves-effect @if($index === 0) active @endif"
                role="tab"
                data-bs-toggle="tab"
                data-bs-target="#navs-{{ $section['key'] }}"
                aria-controls="navs-{{ $section['key'] }}"
                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                @if($index !== 0) tabindex="-1" @endif
              >
                <i class="ti {{ $section['icon'] }} me-2"></i>
                {{ $l == 'ar' ? $section['label_ar'] : $section['label_en'] }}
              </button>
            </li>
          @endforeach
        </ul>

        <div class="tab-content">
          @foreach ($sections as $index => $section)
            <div
              class="tab-pane fade @if($index === 0) show active @endif"
              id="navs-{{ $section['key'] }}"
              role="tabpanel"
            >
              <form
                action="{{ $section['route'] }}"
                method="POST"
                enctype="multipart/form-data"
                class="section-form"
                data-section="{{ $section['key'] }}"
                novalidate
              >
                @csrf
                @method('PUT')

                {{-- Save button, top-right of each section --}}
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <h5 class="mb-0">
                    {{ $l == 'ar' ? $section['label_ar'] : $section['label_en'] }}
                  </h5>
                  <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                    <i class="ti ti-device-floppy"></i>
                    {{ $l == 'ar' ? 'حفظ' : 'Save' }}
                  </button>
                </div>

                <div class="row g-4">
                  @foreach ($section['fields'] as $field)
                    @php
                      $fieldName  = "{$section['key']}[{$field['name']}]";
                      $fieldId    = "{$section['key']}_{$field['name']}";
                      $fieldLabel = $l == 'ar' ? $field['label_ar'] : $field['label_en'];
                      $colWidth   = $field['col'] ?? 12;
                    @endphp
                    <div class="col-md-{{ $colWidth }}">
                      <label for="{{ $fieldId }}" class="form-label">
                        {{ $fieldLabel }}
                        @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                      </label>

                      @switch($field['type'])
                        @case('textarea')
                          <textarea
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            rows="4"
                            @if(!empty($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                            @if(!empty($field['required'])) required @endif
                            class="form-control @error($fieldName) is-invalid @enderror"
                          >{{ old($fieldName, $field['value']) }}</textarea>
                          @if(!empty($field['maxlength']))
                            <div class="form-text">
                              {{ $l == 'ar' ? 'الحد الأقصى' : 'Max' }} {{ $field['maxlength'] }}
                              {{ $l == 'ar' ? 'حرف' : 'characters' }}
                            </div>
                          @endif
                          @break

                        @case('url')
                          <input
                            type="url"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ old($fieldName, $field['value']) }}"
                            @if(!empty($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                            @if(!empty($field['required'])) required @endif
                            placeholder="https://"
                            class="form-control @error($fieldName) is-invalid @enderror"
                          >
                          @break

                        @case('select')
                          <select
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            @if(!empty($field['required'])) required @endif
                            class="form-select @error($fieldName) is-invalid @enderror"
                          >
                            @foreach ($field['options'] ?? [] as $optionValue => $optionLabel)
                              <option
                                value="{{ $optionValue }}"
                                @selected(old($fieldName, $field['value']) == $optionValue)
                              >
                                {{ $optionLabel }}
                              </option>
                            @endforeach
                          </select>
                          @break

                        @case('checkbox')
                          <div class="form-check form-switch">
                            <input
                              type="hidden"
                              name="{{ $fieldName }}"
                              value="0"
                            >
                            <input
                              type="checkbox"
                              id="{{ $fieldId }}"
                              name="{{ $fieldName }}"
                              value="1"
                              class="form-check-input"
                              @checked(old($fieldName, $field['value']))
                            >
                          </div>
                          @break

                        @case('image')
                          <div class="d-flex align-items-center gap-3">
                            @if(!empty($field['value']))
                              <img
                                src="{{ $field['value'] }}"
                                alt="{{ $fieldLabel }}"
                                class="rounded"
                                style="width: 64px; height: 64px; object-fit: cover;"
                              >
                            @endif
                            <div class="flex-grow-1">
                              <input
                                type="file"
                                id="{{ $fieldId }}"
                                name="{{ $fieldName }}"
                                accept="{{ $field['accept'] ?? 'image/*' }}"
                                @if(!empty($field['required']) && empty($field['value'])) required @endif
                                class="form-control @error($fieldName) is-invalid @enderror"
                              >
                              @if(!empty($field['accept']) || !empty($field['max_size_mb']))
                                <div class="form-text">
                                  @if(!empty($field['accept']))
                                    {{ strtoupper(str_replace(['.', ','], ['', ', '], trim($field['accept']))) }}
                                  @endif
                                  @if(!empty($field['max_size_mb']))
                                    &mdash; {{ $l == 'ar' ? 'الحد الأقصى' : 'Max' }} {{ $field['max_size_mb'] }}MB
                                  @endif
                                </div>
                              @endif
                            </div>
                          </div>
                          @break

                        @default
                          <input
                            type="text"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ old($fieldName, $field['value']) }}"
                            @if(!empty($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                            @if(!empty($field['required'])) required @endif
                            class="form-control @error($fieldName) is-invalid @enderror"
                          >
                      @endswitch

                      @error($fieldName)
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                      @enderror
                    </div>
                  @endforeach
                </div>

                @if($section['key'] === 'about')
                  <hr class="my-4">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">
                      {{ $l == 'ar' ? 'البطاقات' : 'Cards' }}
                    </h6>
                    <button
                      type="button"
                      class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                      id="aboutAddCard"
                    >
                      <i class="ti ti-plus"></i>
                      {{ $l == 'ar' ? 'إضافة بطاقة' : 'Add Card' }}
                    </button>
                  </div>
                  <div class="row g-4" id="aboutCardsContainer"></div>
                @endif

                @if($section['key'] === 'programs')
                  <hr class="my-4">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                      <h6 class="mb-1 d-flex align-items-center gap-2">
                        <i class="ti ti-rocket text-primary"></i>
                        {{ $l == 'ar' ? 'اختيار البرامج' : 'Programs Selection' }}
                      </h6>
                      <small class="text-muted">
                        {{ $l == 'ar' ? 'حدد البرامج التي ستظهر على صفحة الهبوط الخاصة بك.' : 'Choose which programs will appear on your landing page.' }}
                      </small>
                    </div>
                  </div>
                  <div class="row g-3 mb-3">
                    <div class="col-12 col-lg-5 col-xl-4">
                      <label class="form-label mb-1" for="programsSearchInput">
                        {{ $l == 'ar' ? 'البحث' : 'Search' }}
                      </label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                        <input
                          type="search"
                          id="programsSearchInput"
                          class="form-control"
                          placeholder="{{ $l == 'ar' ? 'ابحث عن البرامج...' : 'Search programs...' }}"
                          aria-label="{{ $l == 'ar' ? 'البحث في البرامج' : 'Search programs' }}"
                        >
                      </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 col-xl-3">
                      <label class="form-label mb-1" for="programsVisibilityFilter">
                        {{ $l == 'ar' ? 'الظهور' : 'Visibility' }}
                      </label>
                      <select class="form-select select2" id="programsVisibilityFilter" data-allow-clear="true">
                        <option value=""></option>
                        <option value="all">{{ $l == 'ar' ? 'الكل' : 'All' }}</option>
                        <option value="visible">{{ $l == 'ar' ? 'ظاهر' : 'Visible' }}</option>
                        <option value="hidden">{{ $l == 'ar' ? 'مخفي' : 'Hidden' }}</option>
                      </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 col-xl-3">
                      <label class="form-label mb-1" for="programsOrganizationFilter">
                        {{ $l == 'ar' ? 'المنظمة' : 'Organization' }}
                      </label>
                      <select class="form-select select2" id="programsOrganizationFilter" data-allow-clear="true">
                        <option value=""></option>
                        <option value="all">{{ $l == 'ar' ? 'الكل' : 'All' }}</option>
                        @foreach($programOrganizations as $org)
                          <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                      <label class="form-label mb-1" for="programsTypeFilter">
                        {{ $l == 'ar' ? 'نوع البرنامج' : 'Program Type' }}
                      </label>
                      <select class="form-select select2" id="programsTypeFilter" data-allow-clear="true">
                        <option value=""></option>
                        <option value="all">{{ $l == 'ar' ? 'الكل' : 'All' }}</option>
                        <option value="Hackathon">{{ __('cycles.Hackathon') }}</option>
                        <option value="General_Program">{{ __('cycles.General_Program') }}</option>
                      </select>
                    </div>
                  </div>
                  <div class="table-responsive border rounded">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>{{ $l == 'ar' ? 'البرنامج' : 'Program' }}</th>
                          <th>{{ $l == 'ar' ? 'النوع' : 'Type' }}</th>
                          <th>{{ $l == 'ar' ? 'المنظمة' : 'Organization' }}</th>
                          <th>{{ $l == 'ar' ? 'الظهور' : 'Visible' }}</th>
                        </tr>
                      </thead>
                      <tbody id="programsListBody">
                        @foreach($cycles as $program)
                          <tr
                            class="programs-list-row @if($program->is_expired) program-row-ended @endif"
                            data-program-id="{{ $program->id }}"
                            data-search-title="{{ $program->title }} {{ $program->organization->name ?? '' }}"
                            data-program-visibility="{{ $program->is_expired ? 'hidden' : ($program->landing_visible ? 'visible' : 'hidden') }}"
                            data-program-organization="{{ $program->organization_id }}"
                            data-program-type="{{ $program->type }}"
                          >
                            <td>
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-sm">
                                  <img src="{{ $program->logo }}" alt="{{ $program->title }}" class="rounded-circle">
                                </div>
                                <div class="flex-grow-1" style="min-width: 0;">
                                  <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <a
                                      href="{{ url('dashboard/programlist/show/' . $program->id) }}"
                                      target="_blank"
                                      rel="noopener"
                                      class="fw-medium d-inline-flex align-items-center gap-1"
                                    >
                                      {{ $program->title }}
                                      <i class="ti ti-external-link text-muted"></i>
                                    </a>
                                    @if($program->is_expired)
                                      <span
                                        class="badge bg-label-secondary"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="{{ $l == 'ar' ? 'انتهى هذا البرنامج، لذلك لا يمكن عرضه على صفحة الهبوط' : 'This program has ended and can no longer be shown on the landing page' }}"
                                      >
                                        {{ $l == 'ar' ? 'منتهي' : 'Ended' }}
                                      </span>
                                    @endif
                                  </div>
                                  @if($program->location)
                                    <div class="text-muted small d-flex align-items-center gap-1">
                                      <i class="ti ti-map-pin"></i>
                                      {{ $program->location }}
                                    </div>
                                  @endif
                                </div>
                              </div>
                            </td>
                            <td>
                              <span class="badge bg-label-{{ $program->type == 'Hackathon' ? 'warning' : 'info' }}">
                                {{ $program->type == 'Hackathon' ? __('cycles.Hackathon') : __('cycles.General_Program') }}
                              </span>
                            </td>
                            <td>
                              @if($program->organization)
                                <span class="d-inline-flex align-items-center gap-1 text-muted">
                                  <i class="ti ti-building"></i>
                                  {{ $program->organization->name }}
                                </span>
                              @else
                                <span class="text-muted">&mdash;</span>
                              @endif
                            </td>
                            <td class="text-end">
                              <div class="form-check form-switch mb-0">
                                <input
                                  type="hidden"
                                  name="programs[selected_programs][{{ $program->id }}]"
                                  value="0"
                                >
                                <input
                                  type="checkbox"
                                  name="programs[selected_programs][{{ $program->id }}]"
                                  value="1"
                                  class="form-check-input program-visibility-switch"
                                  @checked(!$program->is_expired && in_array($program->id, $selectedCycleIds))
                                  @disabled($program->is_expired)
                                >
                              </div>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  <div id="programsNoResults" class="text-center py-5 d-none">
                    <i class="ti ti-search text-muted fs-1 mb-2 d-block"></i>
                    <h5 class="text-muted mb-1" id="programsNoResultsSearch">
                      {{ $l == 'ar' ? 'لم يتم العثور على برامج.' : 'No programs found.' }}
                    </h5>
                    <h5 class="text-muted mb-1 d-none" id="programsNoResultsFilters">
                      {{ $l == 'ar' ? 'لا توجد برامج تطابق عوامل التصفية المحددة.' : 'No programs match the selected filters.' }}
                    </h5>
                  </div>
                  <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 mt-3">
                    <small class="text-muted" id="programsPaginationInfo"></small>
                    <nav aria-label="Programs pagination" id="programsPaginationNav">
                      <ul class="pagination justify-content-center mb-0" id="programsPaginationList"></ul>
                    </nav>
                  </div>
                @endif

                @if($section['key'] === 'how')
                  <hr class="my-4">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">
                      {{ $l == 'ar' ? 'الخطوات' : 'Process Steps' }}
                    </h6>
                    <button
                      type="button"
                      class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                      id="howAddStep"
                    >
                      <i class="ti ti-plus"></i>
                      {{ $l == 'ar' ? 'إضافة خطوة' : 'Add Step' }}
                    </button>
                  </div>
                  <div class="row g-4" id="howStepsContainer"></div>
                @endif

                @if($section['key'] === 'custom')
                  <hr class="my-4">
                  <div class="d-flex align-items-center justify-content-end mb-3">
                    <button
                      type="button"
                      class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                      id="customAddSection"
                    >
                      <i class="ti ti-plus"></i>
                      {{ $l == 'ar' ? 'إضافة قسم مخصص' : 'Add Custom Section' }}
                    </button>
                  </div>
                  <div id="customSectionsContainer"></div>
                  <div id="customSectionsEmpty" class="text-center py-5 border border-dashed rounded">
                    <i class="ti ti-layout-grid text-muted fs-1 mb-2 d-block"></i>
                    <h5 class="text-muted mb-1">
                      {{ $l == 'ar' ? 'لا توجد أقسام مخصصة بعد' : 'No custom sections yet' }}
                    </h5>
                    <p class="text-muted small mb-0">
                      {{ $l == 'ar' ? 'أضف أقسامًا مخصصة لإثراء صفحة الهبوط الخاصة بك' : 'Add custom sections to enrich your landing page' }}
                    </p>
                  </div>
                @endif
              </form>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
@endsection

{{-- Button-link modal for the custom section rich-text content --}}
<div class="modal fade" id="customButtonModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          {{ $l == 'ar' ? 'إضافة زر' : 'Add Button' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label" for="customButtonText">
            {{ $l == 'ar' ? 'نص الزر' : 'Button Text' }}
          </label>
          <input type="text" class="form-control" id="customButtonText" maxlength="50">
        </div>
        <div class="mb-3">
          <label class="form-label" for="customButtonLink">
            {{ $l == 'ar' ? 'رابط الزر' : 'Button Link' }}
          </label>
          <input type="text" class="form-control" id="customButtonLink" placeholder="https://">
          <div class="text-danger small mt-1" id="customButtonLinkError" style="display:none"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          {{ $l == 'ar' ? 'إلغاء' : 'Cancel' }}
        </button>
        <button type="button" class="btn btn-primary" id="customButtonSave">
          {{ $l == 'ar' ? 'إضافة' : 'Add' }}
        </button>
      </div>
    </div>
  </div>
</div>

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
@endsection

@section('pagescripts')
  @php
    // Edit-mode pre-fill data, sourced from the current landing page's
    // saved records ($aboutCards / $howSteps already come from the
    // controller with this exact shape; custom sections are mapped from
    // the persisted LandingPageCustomSection models).
    $customSections = $customSectionBlocks->map(fn ($block) => [
      'id'             => $block->id,
      'visibility'     => (bool) $block->visibility,
      'title'          => $block->title,
      'title_ar'       => $block->title_ar,
      'title_tag'      => $block->title_tag,
      'title_tag_ar'   => $block->title_tag_ar,
      'content'        => $block->content,
      'content_ar'     => $block->content_ar,
      'button_text'    => $block->button_text,
      'button_link'    => $block->button_link,
    ])->values()->all();
  @endphp
  <script>
    $(function () {
      const urlTab = new URLSearchParams(window.location.search).get('tab');
      if (urlTab) {
        const $targetBtn = $('button[data-bs-target="#' + urlTab + '"]');
        if ($targetBtn.length) {
          new bootstrap.Tab($targetBtn[0]).show();
        }
      }

      $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        const tabId = $(this).attr('data-bs-target').replace('#', '');
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
      });

      // ─────────────────────────────────────────────────────────────
      // Custom landing-page URL configuration (simulated backend)
      // Save/check hit session-backed controller stubs; validation uses
      // the exact acceptance-criteria messages for both languages.
      // ─────────────────────────────────────────────────────────────
      const isArabic = {{ $l == 'ar' ? 'true' : 'false' }};
      const lpUrlI18n = {
        titleRequired:  isArabic ? 'يرجى إدخال العنوان.' : 'Please enter the title.',
        nameRequired:   isArabic ? 'يرجى إدخال الاسم.' : 'Please enter the name.',
        slugRequired:   isArabic ? 'يرجى إدخال الرابط.' : 'Please enter the URL.',
        slugInvalid:    isArabic ? 'يمكن أن يحتوي الرابط على أحرف إنجليزية صغيرة وأرقام وشرطات فقط.' : 'URL slug can only contain lowercase letters, numbers, and hyphens.',
        available:      isArabic ? 'هذا الرابط متاح.' : 'This URL is available.',
        taken:          isArabic ? 'هذا الرابط مستخدم بالفعل. يرجى اختيار رابط آخر.' : 'This URL is already taken. Please choose another one.',
        checking:       isArabic ? 'جاري التحقق من توفر الرابط...' : 'Checking URL availability...',
        saved:          isArabic ? 'تم حفظ رابط صفحة الهبوط المخصصة بنجاح.' : 'Custom landing page URL saved successfully.',
        activeStatus:   isArabic ? 'رابط صفحة الهبوط نشط' : 'Landing page URL is active',
        inactiveStatus: isArabic ? 'رابط صفحة الهبوط غير نشط' : 'Landing page URL is inactive',
        previewNotSaved: isArabic ? 'لا يمكن معاينة صفحة الهبوط قبل حفظ رابط مخصص صالح.' : 'Cannot preview the landing page before saving a valid custom URL.',
        titleMax:       isArabic ? 'يجب ألا يتجاوز العنوان 100 حرف.' : 'Title must not exceed 100 characters.',
        nameMax:        isArabic ? 'يجب ألا يتجاوز الاسم 100 حرف.' : 'URL name must not exceed 100 characters.',
      };

      const lpUrlBase = "{{ url('/lp/') }}/";
      let lpUrlSlugChecked = false;   // current slug value verified against the backend
      let lpUrlSlugAvailable = false; // result of the last availability check
      let lpUrlSavedSlug = null;      // slug of the currently saved configuration
      let lpUrlActive = true;         // mirrors the Active toggle

      const lpUrlSlugify = function (value) {
        return String(value || '')
          .toLowerCase()
          .replace(/[^\w\s-]/g, '')
          .replace(/[\s_]+/g, '-')
          .replace(/-+/g, '-')
          .replace(/^-+|-+$/g, '');
      };

      const lpUrlIsValidSlug = function (slug) {
        return /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug);
      };

      const lpUrlFindFeedback = function ($input) {
        const $sibling = $input.siblings('.invalid-feedback');
        if ($sibling.length) return $sibling;
        return $input.closest('.col-md-6, .col-xl-7, .col-12').find('.invalid-feedback').first();
      };

      const lpUrlShowFieldError = function ($input, message) {
        $input.addClass('is-invalid');
        lpUrlFindFeedback($input).text(message).addClass('d-block');
      };

      const lpUrlClearFieldError = function ($input) {
        $input.removeClass('is-invalid');
        lpUrlFindFeedback($input).text('').removeClass('d-block');
      };

      const lpUrlSetAvailability = function (state, message) {
        const $fb = $('#lpUrlAvailabilityFeedback');
        if (!message) {
          $fb.removeClass('text-success text-danger').text('').hide();
          return;
        }
        $fb.removeClass('text-success text-danger')
          .addClass(state === 'available' ? 'text-success' : 'text-danger')
          .text(message)
          .show();
      };

      const lpUrlAjax = function (url, payload) {
        return $.ajax({
          url: url,
          method: 'POST',
          data: Object.assign({ _token: "{{ csrf_token() }}" }, payload),
          dataType: 'json',
        });
      };

      // FormValidation wiring for the custom-URL card.
      const lpUrlFvFields = {
        lpUrlTitle: {
          validators: {
            notEmpty: { message: lpUrlI18n.titleRequired },
            stringLength: { max: 100, message: lpUrlI18n.titleMax },
          },
        },
        lpUrlName: {
          validators: {
            notEmpty: { message: lpUrlI18n.nameRequired },
            stringLength: { max: 100, message: lpUrlI18n.nameMax },
          },
        },
        lpUrlSlug: {
          validators: {
            notEmpty: { message: lpUrlI18n.slugRequired },
            regexp: {
              regexp: /^[a-z0-9]+(?:-[a-z0-9]+)*$/,
              message: lpUrlI18n.slugInvalid,
            },
            callback: {
              message: lpUrlI18n.taken,
              callback: function (input) {
                const slug = (input.value || '').trim().toLowerCase();
                if (!lpUrlIsValidSlug(slug)) return { valid: true };
                if (!lpUrlSlugChecked) return { valid: true };
                return { valid: lpUrlSlugAvailable };
              },
            },
          },
        },
      };

      let lpUrlFv = null;
      if (typeof FormValidation !== 'undefined') {
        const $lpUrlForm = document.getElementById('lpUrlForm');
        if ($lpUrlForm) {
          lpUrlFv = FormValidation.formValidation($lpUrlForm, {
            fields: lpUrlFvFields,
            plugins: {
              trigger: new FormValidation.plugins.Trigger(),
              bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '' }),
              autoFocus: new FormValidation.plugins.AutoFocus(),
            },
            init: instance => {
              instance.on('plugins.message.placed', function (e) {
                if (e.element.parentElement.classList.contains('input-group')) {
                  e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                }
              });
            },
          });
        }
      }

      // Always resolves with { valid, available, message }.
      const lpUrlCheckAvailability = function () {
        const $slug = $('#lpUrlSlug');
        const slug = ($slug.val() || '').trim().toLowerCase();
        const d = $.Deferred();
        lpUrlSlugChecked = false;
        lpUrlSlugAvailable = false;

        if (!slug) {
          lpUrlSetAvailability('taken', lpUrlI18n.slugRequired);
          d.resolve({ valid: false, available: false, message: lpUrlI18n.slugRequired, empty: true });
          return d.promise();
        }
        if (!lpUrlIsValidSlug(slug)) {
          lpUrlSetAvailability('taken', lpUrlI18n.slugInvalid);
          d.resolve({ valid: false, available: false, message: lpUrlI18n.slugInvalid });
          return d.promise();
        }

        lpUrlSetAvailability('', lpUrlI18n.checking);

        lpUrlAjax("{{ route('landing-pages.url.check', $currentPage->id) }}", { slug: slug })
          .done(function (res) {
            lpUrlSlugChecked = true;
            lpUrlSlugAvailable = !!res.available;
            lpUrlSetAvailability(res.available ? 'available' : 'taken', res.message);
            d.resolve({ valid: !!res.valid, available: !!res.available, message: res.message });
          })
          .fail(function (xhr) {
            const message = (xhr && xhr.responseJSON && xhr.responseJSON.message) || lpUrlI18n.slugInvalid;
            lpUrlSetAvailability('taken', message);
            d.resolve({ valid: false, available: false, message: message });
          });
        return d.promise();
      };

      const lpUrlRenderStatus = function () {
        const $status = $('#lpUrlStatusText');
        if (lpUrlActive) {
          $status.html('<span class="text-success"><i class="ti ti-check me-1"></i>' + lpUrlI18n.activeStatus + '</span>');
        } else {
          $status.html('<span class="text-warning"><i class="ti ti-eye-off me-1"></i>' + lpUrlI18n.inactiveStatus + '</span>');
        }
      };

      const lpUrlRenderSavedLink = function () {
        const $wrap = $('#lpUrlSavedLinkWrap');
        if (!lpUrlSavedSlug) {
          $wrap.addClass('d-none');
          return;
        }
        const href = lpUrlBase + lpUrlSavedSlug;
        $('#lpUrlSavedLink').attr('href', href).text(href);
        $wrap.removeClass('d-none');
      };

      $('#lpUrlEnabledToggle').on('change', function () {
        lpUrlActive = this.checked;
        lpUrlRenderStatus();
      });

      $('#lpUrlGenerateBtn').on('click', function () {
        const $title = $('#lpUrlTitle');
        if (lpUrlFv) {
          lpUrlFv.revalidateField('lpUrlTitle');
          if (!$title.val().trim()) return;
        } else {
          if (!$title.val().trim()) {
            lpUrlShowFieldError($title, lpUrlI18n.titleRequired);
            return;
          }
          lpUrlClearFieldError($title);
        }
        $('#lpUrlSlug').val(lpUrlSlugify($title.val()));
        lpUrlSlugChecked = false;
        lpUrlSlugAvailable = false;
        lpUrlSetAvailability('', '');
        if (lpUrlFv) lpUrlFv.revalidateField('lpUrlSlug');
      });

      $('#lpUrlCheckBtn').on('click', function () {
        lpUrlCheckAvailability().done(function () {
          if (lpUrlFv) lpUrlFv.revalidateField('lpUrlSlug');
        });
      });

      $('#lpUrlSlug').on('input', function () {
        lpUrlSlugChecked = false;
        lpUrlSlugAvailable = false;
        lpUrlSetAvailability('', '');
      });

      $('#lpUrlForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#lpUrlSaveBtn');
        const $title = $('#lpUrlTitle');
        const $name = $('#lpUrlName');
        const $slug = $('#lpUrlSlug');

        const proceedSave = function () {
          const originalHtml = $btn.html();
          $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

          lpUrlAjax("{{ route('landing-pages.url.save', $currentPage->id) }}", {
            title: $title.val().trim(),
            name: $name.val().trim(),
            slug: ($slug.val() || '').trim().toLowerCase(),
            active: lpUrlActive ? 1 : 0,
          })
            .done(function (res) {
              lpUrlSavedSlug = ($slug.val() || '').trim().toLowerCase();
              lpUrlSlugChecked = true;
              lpUrlSlugAvailable = true;
              lpUrlRenderSavedLink();
              releaseSuccessAlert({
                title: "{{ __('admin.success') }}",
                message: res.message || lpUrlI18n.saved,
              });
              $btn.removeClass('btn-primary').addClass('btn-success').html('<i class="ti ti-check"></i>');
              setTimeout(function () {
                $btn.removeClass('btn-success').addClass('btn-primary').html(originalHtml);
              }, 1500);
            })
            .fail(function (xhr) {
              const message = (xhr && xhr.responseJSON && xhr.responseJSON.message) || lpUrlI18n.slugInvalid;
              releaseFailureAlert({
                title: "{{ __('admin.Error') }}",
                message: message,
              });
              $btn.html(originalHtml);
            })
            .always(function () {
              $btn.prop('disabled', false);
            });
        };

        const runChecks = function () {
          if (lpUrlFv) {
            lpUrlFv.validate().then(function (status) {
              if (status !== 'Valid') return;
              if (lpUrlSlugChecked && lpUrlSlugAvailable) {
                proceedSave();
                return;
              }
              lpUrlCheckAvailability().done(function (res) {
                if (lpUrlFv) lpUrlFv.revalidateField('lpUrlSlug');
                if (res.valid && res.available) proceedSave();
              });
            });
            return;
          }

          // Fallback when the FormValidation plugin is unavailable.
          const slug = ($slug.val() || '').trim().toLowerCase();
          let firstError = null;
          const mark = function ($input, message) {
            lpUrlShowFieldError($input, message);
            if (!firstError) firstError = message;
          };
          if (!$title.val().trim()) {
            mark($title, lpUrlI18n.titleRequired);
          } else {
            lpUrlClearFieldError($title);
          }
          if (!$name.val().trim()) {
            mark($name, lpUrlI18n.nameRequired);
          } else {
            lpUrlClearFieldError($name);
          }
          if (!slug) {
            mark($slug, lpUrlI18n.slugRequired);
          } else if (!lpUrlIsValidSlug(slug)) {
            mark($slug, lpUrlI18n.slugInvalid);
          } else {
            lpUrlClearFieldError($slug);
          }
          if (firstError) return;

          const proceed = function (checkResult) {
            if (checkResult.valid && checkResult.available) {
              proceedSave();
            }
          };
          if (lpUrlSlugChecked && lpUrlSlugAvailable) {
            proceed({ valid: true, available: true });
          } else {
            lpUrlCheckAvailability().done(proceed);
          }
        };

        runChecks();
      });

      $('#lpUrlPreviewBtn').on('click', function () {
        if (!lpUrlSavedSlug) {
          releaseFailureAlert({
            title: lpUrlI18n.activeStatus,
            message: lpUrlI18n.previewNotSaved,
          });
          return;
        }
        // Active slug renders the landing page; inactive/unknown slug
        // returns a real 404 from the public route.
        window.open(lpUrlBase + lpUrlSavedSlug, '_blank', 'noopener');
      });

      // ─────────────────────────────────────────────────────────────
      // Section forms — FormValidation (client-side)
      // Validation errors only block the save; they never reset the form.
      // ─────────────────────────────────────────────────────────────
      const maxImageSize = 5 * 1024 * 1024; // 5 MB

      const validationMessages = {
        logoRequired:       isArabic ? 'يرجى تحميل الشعار.' : 'Please upload a logo.',
        logoUnsupported:    isArabic ? 'صيغة الملف غير مدعومة. يرجى تحميل ملف بصيغة PNG أو JPG أو JPEG أو WEBP.' : 'Unsupported file format. Please upload a PNG, JPG, JPEG, or WEBP file.',
        logoSize:           isArabic ? 'يجب ألا يتجاوز حجم الشعار 5 ميجابايت.' : 'Logo size must not exceed 5 MB.',
        description:        isArabic ? 'يرجى إدخال الوصف.' : 'Please enter a description.',
        descriptionArabic:  isArabic ? 'يرجى إدخال الوصف باللغة العربية.' : 'Please enter a description in Arabic.',
        heroTitle:          isArabic ? 'يرجى إدخال العنوان.' : 'Please enter the title.',
        heroSubtitle:       isArabic ? 'يرجى إدخال العنوان الفرعي.' : 'Please enter the subtitle.',
        heroTitleArabic:    isArabic ? 'يرجى إدخال العنوان باللغة العربية.' : 'Please enter the title in Arabic.',
        heroSubtitleArabic: isArabic ? 'يرجى إدخال العنوان الفرعي باللغة العربية.' : 'Please enter the subtitle in Arabic.',
        heroImageRequired:  isArabic ? 'يرجى تحميل صورة القسم الرئيسي.' : 'Please upload a hero image.',
        heroImageFormat:    isArabic ? 'صيغة الملف غير مدعومة. يرجى تحميل ملف بصيغة PNG أو JPG أو JPEG أو WEBP.' : 'Unsupported file format. Please upload a PNG, JPG, JPEG, or WEBP file.',
        heroImageSize:      isArabic ? 'يجب ألا يتجاوز حجم صورة القسم الرئيسي 5 ميجابايت.' : 'Hero image size must not exceed 5 MB.',
        aboutTitle:         isArabic ? 'يرجى إدخال عنوان القسم.' : 'Please enter the section title.',
        aboutSubtitle:      isArabic ? 'يرجى إدخال العنوان الفرعي للقسم.' : 'Please enter the section subtitle.',
        aboutDescription:   isArabic ? 'يرجى إدخال وصف القسم.' : 'Please enter the section description.',
        aboutTitleArabic:   isArabic ? 'يرجى إدخال عنوان القسم باللغة العربية.' : 'Please enter the section title in Arabic.',
        aboutSubtitleArabic: isArabic ? 'يرجى إدخال العنوان الفرعي للقسم باللغة العربية.' : 'Please enter the section subtitle in Arabic.',
        aboutDescriptionArabic: isArabic ? 'يرجى إدخال وصف القسم باللغة العربية.' : 'Please enter the section description in Arabic.',
        programsRequired:       isArabic ? 'هذا الحقل مطلوب' : 'This field is required.',
        howTitle:               isArabic ? 'يرجى إدخال عنوان القسم.' : 'Please enter the section title.',
        howTitleArabic:         isArabic ? 'يرجى إدخال عنوان القسم باللغة العربيه.' : 'Please enter the section title in Arabic.',
        howSubtitle:            isArabic ? 'يرجى إدخال العنوان الفرعي للقسم.' : 'Please enter the section subtitle.',
        howSubtitleArabic:      isArabic ? 'يرجى إدخال العنوان الفرعي للقسم باللعة العربية.' : 'Please enter the section subtitle in Arabic.',
        howDescription:         isArabic ? 'يرجى إدخال وصف القسم.' : 'Please enter the section description.',
        howDescriptionArabic:   isArabic ? 'يرجى إدخال وصف القسم باللغة العربية.' : 'Please enter the section description in Arabic.',
        howStepTitle:           isArabic ? 'يرجى إدخال عنوان الخطوة.' : 'Please enter the step title.',
        howStepTitleArabic:     isArabic ? 'يرجى إدخال عنوان الخطوة باللغة  العربية.' : 'Please enter the step title in Arabic.',
        howStepIcon:            isArabic ? 'يرجى اختيار أيقونة.' : 'Please select an icon.',
        howStepDescription:     isArabic ? 'يرجى إدخال وصف الخطوة.' : 'Please enter the step description.',
        howStepDescriptionArabic: isArabic ? 'يرجى إدخال وصف الخطوة باللغة العربيه.' : 'Please enter the step description in Arabic.',
        customRequired:         isArabic ? 'هذا الحقل مطلوب.' : 'This field is required.',
        customUnsupportedType:  isArabic ? 'نوع الملف غير مدعوم. يرجى رفع صورة بصيغة JPG أو PNG أو WEBP.' : 'Unsupported file type. Please upload a JPG, PNG, or WEBP image.',
        customImageSize:        isArabic ? 'حجم الصورة يتجاوز 5 ميجابايت.' : 'Image size exceeds 5 MB.',
        customButtonLink:       isArabic ? 'يرجى إدخال رابط زر صالح.' : 'Please enter a valid button link.',
        cardIcon:           isArabic ? 'يرجى اختيار أيقونة.' : 'Please select an icon.',
        cardTitle:          isArabic ? 'يرجى إدخال عنوان البطاقة.' : 'Please enter a card title.',
        cardDescription:    isArabic ? 'يرجى إدخال وصف البطاقة.' : 'Please enter a card description.',
        cardTitleArabic:    isArabic ? 'يرجى إدخال عنوان البطاقة باللغة العربية.' : 'Please enter a card title in Arabic.',
        cardDescriptionArabic: isArabic ? 'يرجى إدخال وصف البطاقة باللغة العربية.' : 'Please enter a card description in Arabic.',
      };

      const successMessages = {
        general: isArabic ? 'تم حفظ المعلومات العامة لصفحة الهبوط بنجاح.' : 'General landing page information saved successfully.',
        hero:    isArabic ? 'تم حفظ معلومات القسم الرئيسي بنجاح.' : 'Hero section information saved successfully.',
        about:   isArabic ? 'تم حفظ معلومات قسم من نحن بنجاح.' : 'About section information saved successfully.',
        programs: isArabic ? 'تم حفظ قسم البرامج بنجاح' : 'Programs section saved successfully.',
        how:      isArabic ? 'تم حفظ المعلومات بنجاح.' : 'Information saved successfully.',
        custom:   isArabic ? 'تم حفظ القسم المخصص بنجاح.' : 'Custom section saved successfully.',
      };

      const buildImageField = function (requiredMsg, formatMsg, sizeMsg) {
        return {
          validators: {
            notEmpty: {
              message: requiredMsg,
            },
            file: {
              extension: 'png,jpg,jpeg,webp',
              message: formatMsg,
            },
            callback: {
              message: sizeMsg,
              callback: function (input) {
                const files = input.element.files;
                if (!files || !files.length) {
                  return { valid: true };
                }
                return { valid: files[0].size <= maxImageSize };
              },
            },
          },
        };
      };

      const sectionFields = {
        general: {
          'general[logo]': buildImageField(
            validationMessages.logoRequired,
            validationMessages.logoUnsupported,
            validationMessages.logoSize
          ),
          'general[description]': {
            validators: {
              notEmpty: { message: validationMessages.description },
            },
          },
          'general[description_ar]': {
            validators: {
              notEmpty: { message: validationMessages.descriptionArabic },
            },
          },
        },
        hero: {
          'hero[title]': {
            validators: {
              notEmpty: { message: validationMessages.heroTitle },
            },
          },
          'hero[title_tag]': {
            validators: {
              notEmpty: { message: validationMessages.heroSubtitle },
            },
          },
          'hero[description]': {
            validators: {
              notEmpty: { message: validationMessages.description },
            },
          },
          'hero[title_ar]': {
            validators: {
              notEmpty: { message: validationMessages.heroTitleArabic },
            },
          },
          'hero[title_tag_ar]': {
            validators: {
              notEmpty: { message: validationMessages.heroSubtitleArabic },
            },
          },
          'hero[description_ar]': {
            validators: {
              notEmpty: { message: validationMessages.descriptionArabic },
            },
          },
          'hero[hero_image]': buildImageField(
            validationMessages.heroImageRequired,
            validationMessages.heroImageFormat,
            validationMessages.heroImageSize
          ),
        },
        about: {
          'about[title]': {
            validators: {
              notEmpty: { message: validationMessages.aboutTitle },
            },
          },
          'about[title_tag]': {
            validators: {
              notEmpty: { message: validationMessages.aboutSubtitle },
            },
          },
          'about[description]': {
            validators: {
              notEmpty: { message: validationMessages.aboutDescription },
            },
          },
          'about[title_ar]': {
            validators: {
              notEmpty: { message: validationMessages.aboutTitleArabic },
            },
          },
          'about[title_tag_ar]': {
            validators: {
              notEmpty: { message: validationMessages.aboutSubtitleArabic },
            },
          },
          'about[description_ar]': {
            validators: {
              notEmpty: { message: validationMessages.aboutDescriptionArabic },
            },
          },
        },
        programs: {
          'programs[title]': {
            validators: {
              notEmpty: { message: validationMessages.programsRequired },
            },
          },
          'programs[title_tag]': {
            validators: {
              notEmpty: { message: validationMessages.programsRequired },
            },
          },
          'programs[description]': {
            validators: {
              notEmpty: { message: validationMessages.programsRequired },
            },
          },
          'programs[title_ar]': {
            validators: {
              notEmpty: { message: validationMessages.programsRequired },
            },
          },
          'programs[title_tag_ar]': {
            validators: {
              notEmpty: { message: validationMessages.programsRequired },
            },
          },
          'programs[description_ar]': {
            validators: {
              notEmpty: { message: validationMessages.programsRequired },
            },
          },
        },
        how: {
          'how[title]': {
            validators: {
              notEmpty: { message: validationMessages.howTitle },
            },
          },
          'how[title_tag]': {
            validators: {
              notEmpty: { message: validationMessages.howSubtitle },
            },
          },
          'how[description]': {
            validators: {
              notEmpty: { message: validationMessages.howDescription },
            },
          },
          'how[title_ar]': {
            validators: {
              notEmpty: { message: validationMessages.howTitleArabic },
            },
          },
          'how[title_tag_ar]': {
            validators: {
              notEmpty: { message: validationMessages.howSubtitleArabic },
            },
          },
          'how[description_ar]': {
            validators: {
              notEmpty: { message: validationMessages.howDescriptionArabic },
            },
          },
        },
      };

      // Optional social links: only validate when a value is filled in.
      // Each network is matched against its own domain-specific pattern
      // instead of a generic URL check.
      const socialLinkPatterns = {
        linkedin:  /^(https?:\/\/)?(www\.)?(?:linkedin\.com\/(?:in|company)\/[a-zA-Z0-9\-_]+|lnkd\.in\/[a-zA-Z0-9\-_]+)(?:\/?(?:[?#][^\s]*)?)?$/i,
        facebook:  /^(https?:\/\/)?(www\.)?facebook\.com\/[a-zA-Z0-9._]+(?:\/?(?:[?#][^\s]*)?)?$/i,
        instagram: /^(https?:\/\/)?(www\.)?instagram\.com\/[a-zA-Z0-9._]+(?:\/?(?:[?#][^\s]*)?)?$/i,
        x:         /^(https?:\/\/)?(www\.)?(?:x\.com|twitter\.com)\/[a-zA-Z0-9_]+(?:\/?(?:[?#][^\s]*)?)?$/i,
        tiktok:    /^(https?:\/\/)?(www\.)?tiktok\.com\/@?[a-zA-Z0-9._]+(?:\/?(?:[?#][^\s]*)?)?$/i,
        youtube:   /^(https?:\/\/)?(www\.)?(?:youtube\.com\/(?:@[a-zA-Z0-9_\-]+|channel\/[a-zA-Z0-9_\-]+|watch\?v=[a-zA-Z0-9_\-]{11})|youtu\.be\/[a-zA-Z0-9_\-]{11})(?:\/?(?:[?&][a-zA-Z0-9_\-=]+)*)?$/i,
      };

      const socialLinkMessages = {
        linkedin:  isArabic ? 'يرجى إدخال رابط لينكدإن صالح (مثال: https://www.linkedin.com/in/username).' : 'Please enter a valid LinkedIn URL (e.g. https://www.linkedin.com/in/username).',
        facebook:  isArabic ? 'يرجى إدخال رابط فيسبوك صالح (مثال: https://www.facebook.com/page).' : 'Please enter a valid Facebook URL (e.g. https://www.facebook.com/page).',
        instagram: isArabic ? 'يرجى إدخال رابط إنستغرام صالح (مثال: https://www.instagram.com/username).' : 'Please enter a valid Instagram URL (e.g. https://www.instagram.com/username).',
        x:         isArabic ? 'يرجى إدخال رابط إكس / تويتر صالح (مثال: https://x.com/username).' : 'Please enter a valid X / Twitter URL (e.g. https://x.com/username).',
        tiktok:    isArabic ? 'يرجى إدخال رابط تيك توك صالح (مثال: https://www.tiktok.com/@username).' : 'Please enter a valid TikTok URL (e.g. https://www.tiktok.com/@username).',
        youtube:   isArabic ? 'يرجى إدخال رابط يوتيوب صالح (مثال: https://www.youtube.com/@channel).' : 'Please enter a valid YouTube URL (e.g. https://www.youtube.com/@channel).',
      };

      ['linkedin', 'facebook', 'instagram', 'x', 'tiktok', 'youtube'].forEach(function (name) {
        sectionFields.general['general[' + name + ']'] = {
          validators: {
            regexp: {
              regexp: socialLinkPatterns[name],
              message: socialLinkMessages[name],
            },
          },
        };
      });

      const sectionValidations = {};
      $('.section-form').each(function () {
        const sectionKey = $(this).data('section');
        const fields = sectionFields[sectionKey];
        if (fields && typeof FormValidation !== 'undefined') {
          sectionValidations[sectionKey] = FormValidation.formValidation(this, {
            fields: fields,
            plugins: {
              trigger: new FormValidation.plugins.Trigger(),
              bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleValidClass: '',
              }),
              autoFocus: new FormValidation.plugins.AutoFocus(),
            },
          });
        }
      });

      // ─────────────────────────────────────────────────────────────
      // About section — cards repeater (max 3 cards)
      // Card fields are all-or-nothing: an untouched card is valid, but
      // once any field of a card is filled every card field becomes
      // mandatory.
      // ─────────────────────────────────────────────────────────────
      const aboutMaxCards = 3;
      const aboutCardFields = ['icon', 'title', 'description', 'title_ar', 'description_ar'];
      const aboutCardMessages = {
        icon:           validationMessages.cardIcon,
        title:          validationMessages.cardTitle,
        description:    validationMessages.cardDescription,
        title_ar:       validationMessages.cardTitleArabic,
        description_ar: validationMessages.cardDescriptionArabic,
      };
      const aboutIcons = [
        { value: 'ti-rocket', label: 'Rocket' },
        { value: 'ti-bulb', label: 'Idea' },
        { value: 'ti-target', label: 'Target' },
        { value: 'ti-chart-line', label: 'Growth' },
        { value: 'ti-users', label: 'Community' },
        { value: 'ti-trophy', label: 'Achievement' },
        { value: 'ti-shield', label: 'Protection' },
        { value: 'ti-heart', label: 'Care' },
        { value: 'ti-star', label: 'Quality' },
        { value: 'ti-book', label: 'Education' },
        { value: 'ti-code', label: 'Code' },
        { value: 'ti-cpu', label: 'Technology' },
        { value: 'ti-world', label: 'Global' },
        { value: 'ti-flag', label: 'Goal' },
        { value: 'ti-crown', label: 'Leadership' },
        { value: 'ti-gift', label: 'Reward' },
        { value: 'ti-briefcase', label: 'Career' },
        { value: 'ti-cloud', label: 'Cloud' },
      ];
      let aboutCardCounter = 0;

      const validateAboutCardField = function (element) {
        const $card = $(element).closest('.about-card');
        const hasAny = $card.find('[data-card-field]').toArray().some(function (el) {
          const $el = $(el);
          return $el.is('select') ? !!$el.val() : $el.val().trim() !== '';
        });
        if (!hasAny) return { valid: true };
        if (element.tagName === 'SELECT') {
          return { valid: element.value !== '' };
        }
        return { valid: element.value.trim() !== '' };
      };

      const aboutCardHtml = function (id) {
        const iconOptions = aboutIcons.map(function (icon) {
          return '<option value="' + icon.value + '">' + icon.label + '</option>';
        }).join('');
        return `
          <div class="col-12 about-card" data-card-id="${id}">
            <div class="border rounded p-3">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 about-card-label">${isArabic ? 'البطاقة' : 'Card'}</h6>
                <button type="button" class="btn btn-label-danger btn-sm d-flex align-items-center gap-1 remove-about-card">
                  <i class="ti ti-trash"></i>
                  ${isArabic ? 'حذف' : 'Remove'}
                </button>
              </div>
              <div class="row g-4">
                <div class="col-md-4">
                  <label class="form-label">${isArabic ? 'الأيقونة' : 'Icon'}</label>
                  <select name="about[cards][${id}][icon]" class="form-select" data-card-field>
                    <option value="">${isArabic ? '— اختر أيقونة —' : '— Select an icon —'}</option>
                    ${iconOptions}
                  </select>
                </div>
                <div class="col-md-8">
                  <label class="form-label">${isArabic ? 'العنوان' : 'Title'}</label>
                  <input type="text" name="about[cards][${id}][title]" maxlength="100" class="form-control" data-card-field>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${isArabic ? 'العنوان بالعربية' : 'Title (Arabic)'}</label>
                  <input type="text" name="about[cards][${id}][title_ar]" maxlength="100" class="form-control" data-card-field>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${isArabic ? 'الوصف' : 'Description'}</label>
                  <textarea name="about[cards][${id}][description]" maxlength="500" rows="4" class="form-control" data-card-field></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${isArabic ? 'الوصف بالعربية' : 'Description (Arabic)'}</label>
                  <textarea name="about[cards][${id}][description_ar]" maxlength="500" rows="4" class="form-control" data-card-field></textarea>
                </div>
              </div>
            </div>
          </div>
        `;
      };

      const updateAboutCardButtons = function () {
        const count = $('#aboutCardsContainer .about-card').length;
        $('#aboutAddCard').prop('disabled', count >= aboutMaxCards);
        $('#aboutCardsContainer .about-card').each(function (i) {
          $(this).find('.about-card-label').text((isArabic ? 'البطاقة ' : 'Card ') + (i + 1));
        });
      };

      const addAboutCard = function () {
        const fv = sectionValidations.about;
        const id = ++aboutCardCounter;
        $('#aboutCardsContainer').append(aboutCardHtml(id));
        if (fv) {
          aboutCardFields.forEach(function (field) {
            fv.addField('about[cards][' + id + '][' + field + ']', {
              validators: {
                callback: {
                  message: aboutCardMessages[field],
                  callback: function (input) {
                    return validateAboutCardField(input.element);
                  },
                },
              },
            });
          });
        }
        updateAboutCardButtons();
        return id;
      };

      $('#aboutAddCard').on('click', addAboutCard);

      $('#aboutCardsContainer')
        .on('input change', '[data-card-field]', function () {
          const fv = sectionValidations.about;
          if (!fv) return;
          const id = $(this).closest('.about-card').data('card-id');
          aboutCardFields.forEach(function (field) {
            fv.revalidateField('about[cards][' + id + '][' + field + ']');
          });
        })
        .on('click', '.remove-about-card', function () {
          const fv = sectionValidations.about;
          const $card = $(this).closest('.about-card');
          const id = $card.data('card-id');
          if (fv) {
            aboutCardFields.forEach(function (field) {
              fv.removeField('about[cards][' + id + '][' + field + ']');
            });
          }
          $card.remove();
          updateAboutCardButtons();
        });

      // About main fields are only mandatory while the section is enabled.
      $('#about_is_enabled').on('change', function () {
        const fv = sectionValidations.about;
        if (!fv) return;
        const enabled = this.checked;
        ['title', 'title_tag', 'description', 'title_ar', 'title_tag_ar', 'description_ar'].forEach(function (name) {
          const fieldName = 'about[' + name + ']';
          if (enabled) {
            fv.enableValidator(fieldName, 'notEmpty');
          } else {
            fv.disableValidator(fieldName, 'notEmpty');
          }
          fv.revalidateField(fieldName);
        });
      });

      // ─────────────────────────────────────────────────────────────
      // How It Works section — process steps repeater (max 3 steps)
      // Step fields are all-or-nothing: an untouched step is valid, but
      // once any field of a step is filled every step field becomes
      // mandatory.
      // ─────────────────────────────────────────────────────────────
      const howMaxSteps = 3;
      const howStepFields = ['title', 'icon', 'description', 'title_ar', 'description_ar'];
      const howStepMessages = {
        title:        validationMessages.howStepTitle,
        icon:         validationMessages.howStepIcon,
        description:  validationMessages.howStepDescription,
        title_ar:     validationMessages.howStepTitleArabic,
        description_ar: validationMessages.howStepDescriptionArabic,
      };
      const howIcons = aboutIcons;
      let howStepCounter = 0;

      const validateHowStepField = function (element) {
        const $step = $(element).closest('.how-step');
        const hasAny = $step.find('[data-step-field]').toArray().some(function (el) {
          const $el = $(el);
          return $el.is('select') ? !!$el.val() : $el.val().trim() !== '';
        });
        if (!hasAny) return { valid: true };
        if (element.tagName === 'SELECT') {
          return { valid: element.value !== '' };
        }
        return { valid: element.value.trim() !== '' };
      };

      const howStepHtml = function (id) {
        const iconOptions = howIcons.map(function (icon) {
          return '<option value="' + icon.value + '">' + icon.label + '</option>';
        }).join('');
        return `
          <div class="col-12 how-step" data-step-id="${id}">
            <div class="border rounded p-3">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 how-step-label">${isArabic ? 'الخطوة' : 'Step'}</h6>
                <button type="button" class="btn btn-label-danger btn-sm d-flex align-items-center gap-1 remove-how-step">
                  <i class="ti ti-trash"></i>
                  ${isArabic ? 'حذف' : 'Remove'}
                </button>
              </div>
              <div class="row g-4">
                <div class="col-md-4">
                  <label class="form-label">${isArabic ? 'الأيقونة' : 'Icon'}</label>
                  <select name="how[steps][${id}][icon]" class="form-select" data-step-field>
                    <option value="">${isArabic ? '— اختر أيقونة —' : '— Select an icon —'}</option>
                    ${iconOptions}
                  </select>
                </div>
                <div class="col-md-8">
                  <label class="form-label">${isArabic ? 'عنوان الخطوة' : 'Step Title'}</label>
                  <input type="text" name="how[steps][${id}][title]" maxlength="100" class="form-control" data-step-field>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${isArabic ? 'عنوان الخطوة بالعربية' : 'Step Title (Arabic)'}</label>
                  <input type="text" name="how[steps][${id}][title_ar]" maxlength="100" class="form-control" data-step-field>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${isArabic ? 'وصف الخطوة' : 'Step Description'}</label>
                  <textarea name="how[steps][${id}][description]" maxlength="300" rows="4" class="form-control" data-step-field></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${isArabic ? 'وصف الخطوة بالعربية' : 'Step Description (Arabic)'}</label>
                  <textarea name="how[steps][${id}][description_ar]" maxlength="300" rows="4" class="form-control" data-step-field></textarea>
                </div>
              </div>
            </div>
          </div>
        `;
      };

      const updateHowStepButtons = function () {
        const count = $('#howStepsContainer .how-step').length;
        $('#howAddStep').prop('disabled', count >= howMaxSteps);
        $('#howStepsContainer .how-step').each(function (i) {
          $(this).find('.how-step-label').text((isArabic ? 'الخطوة ' : 'Step ') + (i + 1));
        });
      };

      const addHowStep = function () {
        const fv = sectionValidations.how;
        const id = ++howStepCounter;
        $('#howStepsContainer').append(howStepHtml(id));
        if (fv) {
          howStepFields.forEach(function (field) {
            fv.addField('how[steps][' + id + '][' + field + ']', {
              validators: {
                callback: {
                  message: howStepMessages[field],
                  callback: function (input) {
                    return validateHowStepField(input.element);
                  },
                },
              },
            });
          });
        }
        updateHowStepButtons();
        return id;
      };

      $('#howAddStep').on('click', addHowStep);

      $('#howStepsContainer')
        .on('input change', '[data-step-field]', function () {
          const fv = sectionValidations.how;
          if (!fv) return;
          const id = $(this).closest('.how-step').data('step-id');
          howStepFields.forEach(function (field) {
            fv.revalidateField('how[steps][' + id + '][' + field + ']');
          });
        })
        .on('click', '.remove-how-step', function () {
          const fv = sectionValidations.how;
          const $step = $(this).closest('.how-step');
          const id = $step.data('step-id');
          if (fv) {
            howStepFields.forEach(function (field) {
              fv.removeField('how[steps][' + id + '][' + field + ']');
            });
          }
          $step.remove();
          updateHowStepButtons();
        });

      // How It Works main fields are only mandatory while the section is enabled.
      $('#how_is_enabled').on('change', function () {
        const fv = sectionValidations.how;
        if (!fv) return;
        const enabled = this.checked;
        ['title', 'title_tag', 'description', 'title_ar', 'title_tag_ar', 'description_ar'].forEach(function (name) {
          const fieldName = 'how[' + name + ']';
          if (enabled) {
            fv.enableValidator(fieldName, 'notEmpty');
          } else {
            fv.disableValidator(fieldName, 'notEmpty');
          }
          fv.revalidateField(fieldName);
        });
      });

      // Programs main fields are only mandatory while the section is enabled.
      $('#programs_is_enabled').on('change', function () {
        const fv = sectionValidations.programs;
        if (!fv) return;
        const enabled = this.checked;
        ['title', 'title_tag', 'description', 'title_ar', 'title_tag_ar', 'description_ar'].forEach(function (name) {
          const fieldName = 'programs[' + name + ']';
          if (enabled) {
            fv.enableValidator(fieldName, 'notEmpty');
          } else {
            fv.disableValidator(fieldName, 'notEmpty');
          }
          fv.revalidateField(fieldName);
        });
      });

      // ─────────────────────────────────────────────────────────────
      // Programs selection list — client-side search + filters +
      // pagination (10 rows/page). Filtering only hides/shows rows so
      // any pending visibility switch changes are never lost.
      // ─────────────────────────────────────────────────────────────
      const programsPerPage = 10;
      let programsCurrentPage = 1;
      let programsSearchTerm = '';
      const programsFilters = {
        visibility: 'all',
        organization: 'all',
        type: 'all',
      };

      const getFilteredProgramRows = function () {
        const $rows = $('#programsListBody .programs-list-row');
        const term = programsSearchTerm.trim().toLowerCase();

        return $rows.filter(function () {
          const $row = $(this);
          if (term && ($row.attr('data-search-title') || '').toLowerCase().indexOf(term) === -1) return false;
          if (programsFilters.visibility !== 'all') {
            // "Visible" = currently checked (selected for this landing page);
            // "Hidden" = currently unchecked. Read live so toggling a switch
            // updates the filter without needing a page reload — the
            // data-program-visibility attribute is only seeded once at
            // render time and drifts out of sync as switches are toggled.
            const isChecked = $row.find('.program-visibility-switch').is(':checked');
            if ((programsFilters.visibility === 'visible') !== isChecked) return false;
          }
          if (programsFilters.organization !== 'all' && $row.attr('data-program-organization') !== programsFilters.organization) return false;
          if (programsFilters.type !== 'all' && $row.attr('data-program-type') !== programsFilters.type) return false;
          return true;
        });
      };

      const renderProgramsPagination = function () {
        const $allRows = $('#programsListBody .programs-list-row');
        const $rows = getFilteredProgramRows();
        const total = $rows.length;
        const totalPages = Math.max(1, Math.ceil(total / programsPerPage));

        const $list = $('#programsPaginationList').empty();
        const $nav = $('#programsPaginationNav');
        const $info = $('#programsPaginationInfo');

        if (total === 0) {
          $allRows.hide();
          $('#programsNoResults').removeClass('d-none');
          const hasActiveFilters = programsFilters.visibility !== 'all' || programsFilters.organization !== 'all' || programsFilters.type !== 'all';
          $('#programsNoResultsSearch').toggleClass('d-none', hasActiveFilters);
          $('#programsNoResultsFilters').toggleClass('d-none', !hasActiveFilters);
          $nav.hide();
          $info.text('');
          return;
        }

        $('#programsNoResults').addClass('d-none');

        if (programsCurrentPage > totalPages) programsCurrentPage = totalPages;
        const start = (programsCurrentPage - 1) * programsPerPage;

        $allRows.hide();
        $rows.slice(start, start + programsPerPage).show();

        if (totalPages <= 1) {
          $nav.hide();
          $info.text('');
          return;
        }

        $nav.show();
        const from = start + 1;
        const to = Math.min(start + programsPerPage, total);
        $info.text(isArabic ? `عرض ${from} - ${to} من ${total}` : `Showing ${from} - ${to} of ${total}`);

        $list.append(`<li class="page-item ${programsCurrentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${programsCurrentPage - 1}">&laquo;</a></li>`);
        for (let i = 1; i <= totalPages; i++) {
          if (i === 1 || i === totalPages || Math.abs(i - programsCurrentPage) <= 1) {
            $list.append(`<li class="page-item ${i === programsCurrentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
          } else if (Math.abs(i - programsCurrentPage) === 2) {
            $list.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
          }
        }
        $list.append(`<li class="page-item ${programsCurrentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${programsCurrentPage + 1}">&raquo;</a></li>`);
      };

      // Re-apply the filter/pagination when a switch is toggled, so the
      // Visible/Hidden filter (read live above) stays accurate without a
      // page reload.
      $('#programsListBody').on('change', '.program-visibility-switch', function () {
        if (programsFilters.visibility !== 'all') renderProgramsPagination();
      });

      $('#programsPaginationList').on('click', '.page-link', function (e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'), 10);
        if (isNaN(page) || page < 1 || page === programsCurrentPage) return;
        programsCurrentPage = page;
        renderProgramsPagination();
      });

      $('#programsSearchInput').on('input', function () {
        programsSearchTerm = $(this).val();
        programsCurrentPage = 1;
        renderProgramsPagination();
      });

      $('#programsVisibilityFilter, #programsOrganizationFilter, #programsTypeFilter').on('change', function () {
        programsFilters.visibility = $('#programsVisibilityFilter').val() || 'all';
        programsFilters.organization = $('#programsOrganizationFilter').val() || 'all';
        programsFilters.type = $('#programsTypeFilter').val() || 'all';
        programsCurrentPage = 1;
        renderProgramsPagination();
      });

      renderProgramsPagination();

      // ─────────────────────────────────────────────────────────────
      // Custom sections — block repeater with rich-text (Quill) editors
      // A block can stay fully empty; once any required field of a
      // block is filled, every required field becomes mandatory.
      // ─────────────────────────────────────────────────────────────
      const customQuills = new Map();
      const customRequiredFields = ['title', 'title_tag', 'content', 'title_ar', 'title_tag_ar', 'content_ar'];
      let customSectionCounter = 0;

      const customSectionHtml = function (id) {
        return `
          <div class="card mb-3 border shadow-sm custom-section-card" data-section-id="${id}">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
              <h6 class="mb-0 custom-section-label">${isArabic ? 'القسم المخصص' : 'Custom Section'}</h6>
              <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-custom-section" title="${isArabic ? 'حذف' : 'Remove'}">
                <i class="ti ti-trash"></i>
              </button>
            </div>
            <div class="card-body">
              <div class="row g-4">
                <input type="hidden" name="custom_sections[${id}][id]" value="">
                <div class="col-12 custom-field-group" data-field="visibility">
                  <label class="form-label d-block">${isArabic ? 'الظهور' : 'Visibility'}</label>
                  <div class="form-check form-switch">
                    <input type="hidden" name="custom_sections[${id}][visibility]" value="0">
                    <input type="checkbox" name="custom_sections[${id}][visibility]" value="1" class="form-check-input custom-visibility-switch" checked>
                  </div>
                </div>
                <div class="col-md-6 custom-field-group" data-field="title">
                  <label class="form-label">${isArabic ? 'العنوان' : 'Title'}</label>
                  <input type="text" name="custom_sections[${id}][title]" maxlength="255" class="form-control custom-section-input" data-custom-field>
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                <div class="col-md-6 custom-field-group" data-field="title_ar">
                  <label class="form-label">${isArabic ? 'العنوان بالعربية' : 'Title (Arabic)'}</label>
                  <input type="text" name="custom_sections[${id}][title_ar]" maxlength="255" class="form-control custom-section-input" data-custom-field>
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                <div class="col-md-6 custom-field-group" data-field="title_tag">
                  <label class="form-label">${isArabic ? 'العنوان الفرعي' : 'Title tag'}</label>
                  <input type="text" name="custom_sections[${id}][title_tag]" maxlength="255" class="form-control custom-section-input" data-custom-field>
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                <div class="col-md-6 custom-field-group" data-field="title_tag_ar">
                  <label class="form-label">${isArabic ? 'العنوان الفرعي بالعربية' : 'Title tag (Arabic)'}</label>
                  <input type="text" name="custom_sections[${id}][title_tag_ar]" maxlength="255" class="form-control custom-section-input" data-custom-field>
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                <div class="col-12 custom-field-group" data-field="content">
                  <label class="form-label">${isArabic ? 'المحتوى' : 'Content'}</label>
                  <div class="custom-quill-editor" id="custom-quill-content-${id}" style="min-height: 160px"></div>
                  <input type="hidden" name="custom_sections[${id}][content]" class="custom-hidden-content" value="">
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                <div class="col-12 custom-field-group" data-field="content_ar">
                  <label class="form-label">${isArabic ? 'المحتوى بالعربية' : 'Content (Arabic)'}</label>
                  <div class="custom-quill-editor" id="custom-quill-content-ar-${id}" style="min-height: 160px"></div>
                  <input type="hidden" name="custom_sections[${id}][content_ar]" class="custom-hidden-content" value="">
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${isArabic ? 'نص الزر' : 'Button text'} <span class="text-muted">(${isArabic ? 'اختياري' : 'Optional'})</span></label>
                  <input type="text" name="custom_sections[${id}][button_text]" maxlength="255" class="form-control">
                </div>
                <div class="col-md-6 custom-field-group" data-field="button_link">
                  <label class="form-label">${isArabic ? 'رابط الزر' : 'Button link'} <span class="text-muted">(${isArabic ? 'اختياري' : 'Optional'})</span></label>
                  <input type="url" name="custom_sections[${id}][button_link]" maxlength="500" class="form-control" placeholder="https://" data-custom-field>
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
              </div>
            </div>
          </div>
        `;
      };

      const setCustomFieldError = function ($group, message) {
        const $err = $group.find('.custom-error-msg');
        if (message) {
          $err.text(message).show();
          $group.find('.form-control, .custom-quill-editor, .form-check-input').addClass('is-invalid');
        } else {
          $err.text('').hide();
          $group.find('.is-invalid').removeClass('is-invalid');
        }
      };

      const customFieldValue = function ($card, field) {
        const $group = $card.find('.custom-field-group[data-field="' + field + '"]');
        const $input = $group.find('.custom-section-input').first();
        if ($input.length) return $input.val().trim();
        return ($group.find('.custom-hidden-content').val() || '').trim();
      };

      const syncCustomSection = function ($card) {
        $card.find('.custom-quill-editor').each(function () {
          const quill = customQuills.get(this);
          const $input = $(this).closest('.custom-field-group').find('.custom-hidden-content');
          if (!quill) return;
          let html = quill.root.innerHTML;
          html = html.replace(/<p><br\s*\/?><\/p>/gi, '').replace(/&nbsp;/g, ' ').trim();
          $input.val(html);
        });
      };

      const validateCustomSection = function ($card) {
        const used = customRequiredFields.some(function (field) {
          return customFieldValue($card, field) !== '';
        });

        if (!used) {
          customRequiredFields.forEach(function (field) {
            setCustomFieldError($card.find('.custom-field-group[data-field="' + field + '"]'), '');
          });
          return true;
        }

        let valid = true;
        customRequiredFields.forEach(function (field) {
          const filled = customFieldValue($card, field) !== '';
          setCustomFieldError($card.find('.custom-field-group[data-field="' + field + '"]'), filled ? '' : validationMessages.customRequired);
          if (!filled) valid = false;
        });
        return valid;
      };

      const validateCustomSections = function () {
        let allValid = true;
        $('#customSectionsContainer .custom-section-card').each(function () {
          syncCustomSection($(this));
          if (!validateCustomSection($(this))) allValid = false;
        });
        return allValid;
      };

      const updateCustomSectionsEmpty = function () {
        const count = $('#customSectionsContainer .custom-section-card').length;
        $('#customSectionsEmpty').toggle(count === 0);
      };

      const updateCustomSectionLabels = function () {
        $('#customSectionsContainer .custom-section-card').each(function (i) {
          $(this).find('.custom-section-label').text((isArabic ? 'القسم المخصص ' : 'Custom Section ') + (i + 1));
        });
      };

      const initCustomQuill = function ($scope) {
        if (typeof Quill === 'undefined') return;
        $scope.find('.custom-quill-editor').each(function () {
          const el = this;
          if (customQuills.has(el)) return;

          const quill = new Quill(el, {
            theme: 'snow',
            modules: {
              toolbar: {
                container: [
                  ['bold', 'italic', 'underline'],
                  [{ list: 'ordered' }, { list: 'bullet' }],
                  ['link', 'image', 'clean'],
                  ['custom-button'],
                ],
                handlers: {
                  image: function () {
                    handleCustomImage(this.quill, $(el));
                  },
                  'custom-button': function () {
                    openCustomButtonModal(this.quill);
                  },
                },
              },
              clipboard: { matchVisual: false },
            },
            placeholder: isArabic ? 'أدخل المحتوى...' : 'Enter content...',
            direction: isArabic ? 'rtl' : 'ltr',
          });

          customQuills.set(el, quill);

          const $group = $(el).closest('.custom-field-group');
          const $input = $group.find('.custom-hidden-content');
          quill.on('text-change', function () {
            let html = quill.root.innerHTML;
            html = html.replace(/<p><br\s*\/?><\/p>/gi, '').replace(/&nbsp;/g, ' ').trim();
            $input.val(html);
            if (html) setCustomFieldError($group, '');
          });
        });
      };

      const handleCustomImage = function (quill, $editor) {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/jpeg,image/png,image/webp';
        input.onchange = function () {
          const file = input.files && input.files[0];
          if (!file) return;
          const $group = $editor.closest('.custom-field-group');
          const ext = (file.name.split('.').pop() || '').toLowerCase();
          const allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
          const allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

          if (allowedExt.indexOf(ext) === -1 && allowedMime.indexOf(file.type) === -1) {
            setCustomFieldError($group, validationMessages.customUnsupportedType);
            return;
          }
          if (file.size > maxImageSize) {
            setCustomFieldError($group, validationMessages.customImageSize);
            return;
          }

          const reader = new FileReader();
          reader.onload = function (e) {
            setCustomFieldError($group, '');
            const range = quill.getSelection(true);
            const index = range ? range.index : quill.getLength();
            quill.insertEmbed(index, 'image', e.target.result);
            quill.setSelection(index + 1);
          };
          reader.readAsDataURL(file);
        };
        input.click();
      };

      const escapeCustomHtml = function (str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
      };

      const escapeCustomAttr = function (str) {
        return escapeCustomHtml(str).replace(/"/g, '&quot;');
      };

      const isValidButtonUrl = function (value) {
        try {
          const u = new URL(value);
          return u.protocol === 'http:' || u.protocol === 'https:';
        } catch (e) {
          return false;
        }
      };

      let activeButtonQuill = null;

      const openCustomButtonModal = function (quill) {
        activeButtonQuill = quill;
        $('#customButtonText').val('');
        $('#customButtonLink').val('');
        $('#customButtonLinkError').hide();
        const modalEl = document.getElementById('customButtonModal');
        if (modalEl) {
          const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
          modal.show();
        }
      };

      $('#customButtonSave').on('click', function () {
        const $err = $('#customButtonLinkError');
        const text = $('#customButtonText').val().trim();
        const link = $('#customButtonLink').val().trim();

        if (!link || !isValidButtonUrl(link)) {
          $err.text(validationMessages.customButtonLink).show();
          return;
        }

        $err.hide();
        if (activeButtonQuill) {
          const range = activeButtonQuill.getSelection(true);
          const index = range ? range.index : activeButtonQuill.getLength();
          const html = '<a class="btn btn-primary" href="' + escapeCustomAttr(link) + '" target="_blank" rel="noopener">' + escapeCustomHtml(text || link) + '</a>&nbsp;';
          activeButtonQuill.clipboard.dangerouslyPasteHTML(index, html, 'user');
          activeButtonQuill.setSelection(index + 1);
        }
        const modalEl = document.getElementById('customButtonModal');
        if (modalEl) {
          bootstrap.Modal.getInstance(modalEl).hide();
        }
      });

      const addCustomSection = function () {
        const id = ++customSectionCounter;
        $('#customSectionsContainer').append(customSectionHtml(id));
        initCustomQuill($('#customSectionsContainer'));
        updateCustomSectionsEmpty();
        updateCustomSectionLabels();
        setTimeout(function () {
          $('#customSectionsContainer .custom-section-card').last().find('.custom-section-input').first().trigger('focus');
        }, 100);
        return id;
      };

      $('#customAddSection').on('click', addCustomSection);

      $('#customSectionsContainer')
        .on('input change', '[data-custom-field]', function () {
          const $group = $(this).closest('.custom-field-group');
          if ($(this).val().trim()) setCustomFieldError($group, '');
        })
        .on('click', '.remove-custom-section', function () {
          const $card = $(this).closest('.custom-section-card');

          const removeCard = function () {
            $card.find('.custom-quill-editor').each(function () {
              customQuills.delete(this);
            });
            $card.remove();
            updateCustomSectionsEmpty();
            updateCustomSectionLabels();
          };

          if ($card.attr('data-persisted') !== 'true') {
            removeCard();
            return;
          }

          releaseWarningAlert({
            title: isArabic ? 'حذف القسم المخصص' : 'Delete Custom Section',
            message: isArabic ? 'هل أنت متأكد من حذف هذا القسم المخصص؟ لا يمكن التراجع عن هذا الإجراء.' : 'Are you sure you want to delete this custom section? This action cannot be undone.',
            confirmText: isArabic ? 'حذف' : 'Delete',
            cancelText: isArabic ? 'إلغاء' : 'Cancel',
            confirmColor: 'danger',
            onConfirm: removeCard,
          });
        });

      // ─────────────────────────────────────────────────────────────
      // Pre-fill repeaters from server-rendered data (edit mode)
      // ─────────────────────────────────────────────────────────────
      const aboutCards = @json($aboutCards);
      const howSteps = @json($howSteps);
      const customSections = @json($customSections);

      aboutCards.forEach(function (card) {
        const id = addAboutCard();
        const $card = $('#aboutCardsContainer .about-card[data-card-id="' + id + '"]');
        $card.find('[name="about[cards][' + id + '][icon]"]').val(card.icon || '');
        $card.find('[name="about[cards][' + id + '][title]"]').val(card.title || '');
        $card.find('[name="about[cards][' + id + '][title_ar]"]').val(card.title_ar || '');
        $card.find('[name="about[cards][' + id + '][description]"]').val(card.description || '');
        $card.find('[name="about[cards][' + id + '][description_ar]"]').val(card.description_ar || '');
      });

      howSteps.forEach(function (step) {
        const id = addHowStep();
        const $step = $('#howStepsContainer .how-step[data-step-id="' + id + '"]');
        $step.find('[name="how[steps][' + id + '][icon]"]').val(step.icon || '');
        $step.find('[name="how[steps][' + id + '][title]"]').val(step.title || '');
        $step.find('[name="how[steps][' + id + '][title_ar]"]').val(step.title_ar || '');
        $step.find('[name="how[steps][' + id + '][description]"]').val(step.description || '');
        $step.find('[name="how[steps][' + id + '][description_ar]"]').val(step.description_ar || '');
      });

      customSections.forEach(function (section) {
        const id = addCustomSection();
        const $card = $('#customSectionsContainer .custom-section-card[data-section-id="' + id + '"]');
        $card.attr('data-persisted', 'true');
        $card.find('[name="custom_sections[' + id + '][id]"]').val(section.id || '');
        $card.find('[name="custom_sections[' + id + '][visibility]"]').prop('checked', !!section.visibility);
        $card.find('[name="custom_sections[' + id + '][title]"]').val(section.title || '');
        $card.find('[name="custom_sections[' + id + '][title_ar]"]').val(section.title_ar || '');
        $card.find('[name="custom_sections[' + id + '][title_tag]"]').val(section.title_tag || '');
        $card.find('[name="custom_sections[' + id + '][title_tag_ar]"]').val(section.title_tag_ar || '');
        $card.find('[name="custom_sections[' + id + '][button_text]"]').val(section.button_text || '');
        $card.find('[name="custom_sections[' + id + '][button_link]"]').val(section.button_link || '');
        $card.find('.custom-quill-editor').each(function (i) {
          const quill = customQuills.get(this);
          if (!quill) return;
          const html = i === 0 ? (section.content || '') : (section.content_ar || '');
          if (html) {
            quill.clipboard.dangerouslyPasteHTML(0, html, 'user');
          }
        });
      });

      // Each section saves independently via AJAX so switching tabs never
      // loses unsaved input in other sections.
      $('.section-form').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const sectionKey = $form.data('section');
        const fv = sectionValidations[sectionKey];

        const submitViaAjax = function () {
          const $btn = $form.find('button[type="submit"]');
          const originalHtml = $btn.html();

          $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

          $.ajax({
            url: $form.attr('action'),
            method: 'POST', // method-spoofed to PUT via @method('PUT') hidden field
            data: new FormData(this),
            processData: false,
            contentType: false,
          })
            .done(function () {
              $btn.removeClass('btn-primary').addClass('btn-success').html('<i class="ti ti-check"></i>');
              if (successMessages[sectionKey]) {
                releaseSuccessAlert({
                  title: "{{ __('admin.success') }}",
                  message: successMessages[sectionKey],
                });
              }
              setTimeout(() => {
                $btn.removeClass('btn-success').addClass('btn-primary').html(originalHtml);
              }, 1500);
            })
            .fail(function (xhr) {
              $btn.html(originalHtml);
              let errorMessage = sectionKey === 'programs'
                ? (isArabic ? 'حدث خطأ ما. يرجى المحاولة مرة أخرى' : 'Something went wrong. Please try again.')
                : "{{ __('admin.unexpected_error') }}";
              if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
              releaseFailureAlert({
                title: "{{ __('admin.Error') }}",
                message: errorMessage,
              });
            })
            .always(function () {
              $btn.prop('disabled', false);
            });
        }.bind(this);

        // Custom sections use hand-rolled validation (rich-text editors,
        // image upload checks and button-link checks).
        if (sectionKey === 'custom') {
          if (!validateCustomSections()) return;
          submitViaAjax();
          return;
        }

        // Block the save until client-side validation passes.
        if (!fv) {
          submitViaAjax();
          return;
        }

        fv.validate().then(function (status) {
          if (status !== 'Valid') return;
          submitViaAjax();
        });
      });
    });
  </script>
@endsection
