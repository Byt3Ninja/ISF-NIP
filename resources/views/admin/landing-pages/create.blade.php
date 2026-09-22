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
      height: 300px !important;
      overflow-y: auto;
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
    /* Localized file input: the native control is kept in the DOM (so the
       form still submits it and `required` still validates) but visually
       replaced, because its "Choose File" / "No file chosen" chrome is
       drawn by the browser in the browser's locale, not the app's. */
    .localized-file-input {
      cursor: pointer;
      overflow: hidden;
    }
    .localized-file-input-native {
      position: absolute;
      width: 1px;
      height: 1px;
      opacity: 0;
      pointer-events: none;
    }
    .localized-file-input-button {
      flex: 0 0 auto;
      pointer-events: none;
    }
    .localized-file-input-name {
      min-width: 0;
    }
    /* Programs table — drag-to-reorder handle */
    .program-drag-handle {
      cursor: grab;
      touch-action: none;
    }
    .program-drag-handle:active {
      cursor: grabbing;
    }
    .program-drag-ghost {
      opacity: 0.4;
    }
    .program-drag-chosen {
      background-color: rgba(105, 108, 255, 0.06);
    }
    /* Keep the handle in step with the rest of an ended row's dimming */
    .program-row-ended td:first-child .program-drag-handle {
      opacity: 0.6;
    }
  </style>
@endsection

@section('content')
  {{-- Header --}}
  <div class="mb-4">
    <h4 class="mb-1">
      {{ __('landing.Create_Landing_Page') }}
    </h4>
    <p class="text-secondary mb-0">
      {{ __('landing.Create_LandingPage_Description') }}
    </p>
  </div>

  <div class="card mb-4">
    <div class="card-header border-bottom">
      <h6 class="card-title mb-0 d-flex align-items-center gap-2">
        <i class="ti ti-layout-dashboard"></i>{{ __('admin.Landing Page Sections') }}
      </h6>
    </div>
    
    <div style="max-width: 100%; overflow-x: auto;" class="p-4 d-md-none">
      <ul class="nav nav-pills flex-nowrap" role="tablist">
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
              @if(!empty($section['locked'])) disabled title="{{ __('landing.Save_General_First') }}" @endif
            >
              <i class="ti {{ $section['icon'] }} me-2"></i>
              {{ $l == 'ar' ? $section['label_ar'] : $section['label_en'] }}
            </button>
          </li>
        @endforeach
      </ul>
    </div>
    
    <div class="card-body p-0">
      <div class="nav-align-left">
        <ul class="nav nav-tabs d-none d-md-block py-4" role="tablist">
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
                @if(!empty($section['locked'])) disabled title="{{ __('landing.Save_General_First') }}" @endif
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

                {{-- Backend validation errors surfaced from the AJAX save --}}
                <div class="alert alert-danger section-backend-errors d-none mb-4" role="alert"></div>

                <fieldset @if(!empty($section['locked'])) disabled @endif class="border-0 p-0 m-0">

                @if(!empty($section['locked']))
                  <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="ti ti-lock"></i>
                    {{ __('landing.Section_Locked_Hint') }}
                  </div>
                @endif

                {{-- Save button, top-right of each section --}}
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <h5 class="mb-0">
                    {{ $l == 'ar' ? $section['label_ar'] : $section['label_en'] }}
                  </h5>
                  <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                    <i class="ti ti-device-floppy"></i>
                    {{ __('landing.Save') }}
                  </button>
                </div>

                <div class="row g-4">
                  @foreach ($section['fields'] as $field)
                    @php
                      $fieldName  = $field['name_attr'];
                      $fieldId    = $field['id_attr'];
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
                              {{ __('landing.Max') }} {{ $field['maxlength'] }}
                              {{ __('landing.characters') }}
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
                          <div class="image-field d-flex align-items-center gap-3">
                            @if(!empty($field['value']))
                              <img
                                src="{{ $field['value'] }}"
                                alt="{{ $fieldLabel }}"
                                class="rounded"
                                style="width: 64px; height: 64px; object-fit: cover;"
                              >
                            @endif
                            <div class="flex-grow-1">
                              {{-- The browser renders its own "Choose File" / "No file chosen"
                                   chrome on a bare file input, and that text follows the browser
                                   locale, not the app locale. Hide the native control and drive a
                                   localized button + filename label instead. --}}
                              <div class="localized-file-input form-control d-flex align-items-center gap-2 @error($fieldName) is-invalid @enderror">
                                <input
                                  type="file"
                                  id="{{ $fieldId }}"
                                  name="{{ $fieldName }}"
                                  accept="{{ $field['accept'] ?? 'image/*' }}"
                                  @if(!empty($field['required']) && empty($field['value'])) required @endif
                                  class="localized-file-input-native"
                                >
                                <button type="button" class="btn btn-sm btn-label-secondary localized-file-input-button">
                                  {{ __('landing.Choose_File') }}
                                </button>
                                <span class="localized-file-input-name text-muted text-truncate"
                                      data-placeholder="{{ __('landing.No_File_Chosen') }}">{{ __('landing.No_File_Chosen') }}</span>
                              </div>
                              @if(!empty($field['accept']) || !empty($field['max_size_mb']))
                                <div class="form-text">
                                  @if(!empty($field['accept']))
                                    {{ strtoupper(str_replace(['.', ','], ['', ', '], trim($field['accept']))) }}
                                  @endif
                                  @if(!empty($field['max_size_mb']))
                                    &mdash; {{ __('landing.Max') }} {{ $field['max_size_mb'] }}MB
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
                      {{ __('landing.Cards') }}
                    </h6>
                    <button
                      type="button"
                      class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                      id="aboutAddCard"
                    >
                      <i class="ti ti-plus"></i>
                      {{ __('landing.Add_Card') }}
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
                        {{ __('landing.Programs_Selection') }}
                      </h6>
                      <small class="text-muted">
                        {{ __('landing.choose_programs') }}
                      </small>
                    </div>
                  </div>
                  <div class="row g-3 mb-3">
                    <div class="col-12 col-lg-5 col-xl-4">
                      <label class="form-label mb-1" for="programsSearchInput">
                        {{ __('landing.Search') }}
                      </label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                        <input
                          type="search"
                          id="programsSearchInput"
                          class="form-control"
                          placeholder="{{ __('landing.Search_Programs_Placeholder') }}"
                          aria-label="{{ __('landing.Search_Programs_Aria') }}"
                        >
                      </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 col-xl-3">
                      <label class="form-label mb-1" for="programsVisibilityFilter">
                        {{ __('landing.Visibility') }}
                      </label>
                      <select class="form-select select2" id="programsVisibilityFilter" data-allow-clear="true">
                        <option value=""></option>
                        <option value="all">{{ __('landing.All') }}</option>
                        <option value="visible">{{ __('landing.Visible') }}</option>
                        <option value="hidden">{{ __('landing.Hidden') }}</option>
                      </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 col-xl-3">
                      <label class="form-label mb-1" for="programsOrganizationFilter">
                        {{ __('landing.Organization') }}
                      </label>
                      <select class="form-select select2" id="programsOrganizationFilter" data-allow-clear="true">
                        <option value=""></option>
                        <option value="all">{{ __('landing.All') }}</option>
                        @foreach($programOrganizations as $org)
                          <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                      <label class="form-label mb-1" for="programsTypeFilter">
                        {{ __('landing.Program_Type') }}
                      </label>
                      <select class="form-select select2" id="programsTypeFilter" data-allow-clear="true">
                        <option value=""></option>
                        <option value="all">{{ __('landing.All') }}</option>
                        <option value="Hackathon">{{ __('cycles.Hackathon') }}</option>
                        <option value="General_Program">{{ __('cycles.General_Program') }}</option>
                      </select>
                    </div>
                  </div>
                  <div class="table-responsive border rounded">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>{{ __('landing.Program') }}</th>
                          <th>{{ __('landing.Type') }}</th>
                          <th>{{ __('landing.Organization') }}</th>
                          <th>{{ __('landing.Visible') }}</th>
                        </tr>
                      </thead>
                      <tbody id="programsListBody">
                        @foreach($cycles as $program)
                          <tr
                            class="programs-list-row @if($program->is_expired) program-row-ended @endif"
                            data-program-id="{{ $program->id }}"
                            data-search-title="{{ $program->title }} {{ $program->title_ar }} {{ $program->organization->name ?? '' }}"
                            data-program-visibility="{{ $program->is_expired ? 'hidden' : ($program->landing_visible ? 'visible' : 'hidden') }}"
                            data-program-organization="{{ $program->organization_id }}"
                            data-program-type="{{ $program->type }}"
                          >
                            <td>
                              <div class="d-flex align-items-center gap-3">
                                <i class="ti ti-grip-vertical program-drag-handle text-muted" title="{{ __('landing.Drag_to_reorder') }}"></i>
                                <div class="avatar avatar-sm">
                                  <img src="{{ $program->logo }}" alt="{{ $program->name }}" class="rounded-circle">
                                </div>
                                <div class="flex-grow-1" style="min-width: 0;">
                                  <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <a
                                      href="{{ url('dashboard/programlist/show/' . $program->id) }}"
                                      target="_blank"
                                      rel="noopener"
                                      class="fw-medium d-inline-flex align-items-center gap-1"
                                    >
                                      {{ $program->name }}
                                      <i class="ti ti-external-link text-muted"></i>
                                    </a>
                                    @if($program->is_expired)
                                      <span
                                        class="badge bg-label-secondary"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="{{ __('landing.Program_ended_hint') }}"
                                      >
                                        {{ __('landing.Ended') }}
                                      </span>
                                    @elseif(!$program->is_public || empty($program->public_slug))
                                      <span
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="{{ __('landing.Program_not_public_hint') }}"
                                      >
                                        <i class="ti ti-alert-triangle text-warning"></i>
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
                              @if(($program->steps_count ?? 0) == 0)
                                <a href="{{ url('dashboard/programlist/show/' . $program->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                                  <span
                                    class="badge bg-label-secondary"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ __('landing.No_steps_hint') }}"
                                  >
                                    {{ __('landing.No_steps') }}
                                  </span>
                                </a>
                              @endif
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
                                  @checked(!$program->is_expired)
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
                      {{ __('landing.No_Programs_Found') }}
                    </h5>
                    <h5 class="text-muted mb-1 d-none" id="programsNoResultsFilters">
                      {{ __('landing.No_Programs_Match') }}
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
                      {{ __('landing.Process_Steps') }}
                    </h6>
                    <button
                      type="button"
                      class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                      id="howAddStep"
                    >
                      <i class="ti ti-plus"></i>
                      {{ __('landing.Add_Step') }}
                    </button>
                  </div>
                  <div class="row g-4" id="howStepsContainer"></div>
                @endif

                @if($section['key'] === 'custom')
                  <hr class="my-4">
                  <div class="d-flex align-items-center justify-content-end mb-3 gap-2">
                    <span id="customSectionCount" class="badge bg-label-primary rounded-pill" style="display:none">0 / 5</span>
                    <button
                      type="button"
                      class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                      id="customAddSection"
                    >
                      <i class="ti ti-plus"></i>
                      {{ __('landing.Add_Custom_Section') }}
                    </button>
                  </div>
                  <div id="customSectionsContainer"></div>
                  <div id="customSectionMaxHint" class="alert alert-warning py-2 small d-none" role="alert">
                    {{ __('landing.Custom_Sections_Max_Hint') }}
                  </div>
                  <div id="customSectionsEmpty" class="text-center py-5 border border-dashed rounded">
                    <i class="ti ti-layout-grid text-muted fs-1 mb-2 d-block"></i>
                    <h5 class="text-muted mb-1">
                      {{ __('landing.No_custom_sections_yet') }}
                    </h5>
                    <p class="text-muted small mb-0">
                      {{ __('landing.Add_Custom_Sections_Hint') }}
                    </p>
                  </div>
                @endif

                </fieldset>
              </form>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  
  {{-- Button-link modal for the custom section rich-text content --}}
  <div class="modal fade" id="customButtonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            {{ __('landing.Add_Button') }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" for="customButtonText">
              {{ __('landing.Button_text') }}
            </label>
            <input type="text" class="form-control" id="customButtonText" maxlength="35">
          </div>
          <div class="mb-3">
            <label class="form-label" for="customButtonLink">
              {{ __('landing.Button_link') }}
            </label>
            <input type="text" class="form-control" id="customButtonLink" placeholder="https://">
            <div class="text-danger small mt-1" id="customButtonLinkError" style="display:none"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            {{ __('landing.Cancel') }}
          </button>
          <button type="button" class="btn btn-primary" id="customButtonSave">
            {{ __('landing.Add') }}
          </button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endsection

@section('pagescripts')
  <script>
    $(function () {
      // Localized file input: forward clicks to the hidden native input and
      // mirror the chosen filename into the localized label. The click is
      // dispatched natively and clicks originating from the native input are
      // ignored, otherwise the forwarded click bubbles back to this handler
      // and recurses until the stack blows.
      $(document).on('click', '.localized-file-input', function (e) {
        const native = this.querySelector('.localized-file-input-native');
        if (!native || e.target === native) {
          return;
        }
        native.click();
      });

      $(document).on('change', '.localized-file-input-native', function () {
        const $imageField = $(this).closest('.image-field');
        const $name = $(this).closest('.localized-file-input').find('.localized-file-input-name');
        const files = this.files;

        $name.text(files && files.length ? files[0].name : $name.data('placeholder'));

        if (!$imageField.length) {
          return;
        }

        let $preview = $imageField.children('img');
        const originalSrc = $preview.data('original-src');

        if (files && files.length) {
          if (!$preview.length) {
            $preview = $('<img class="rounded application-image-preview" alt="" style="width:64px;height:64px;object-fit:cover;">');
            $imageField.prepend($preview);
          }
          if (originalSrc === undefined) {
            $preview.data('original-src', $preview.attr('src') || '');
          }
          const previousUrl = $preview.data('object-url');
          if (previousUrl) {
            URL.revokeObjectURL(previousUrl);
          }
          const objectUrl = URL.createObjectURL(files[0]);
          $preview
            .data('object-url', objectUrl)
            .attr('src', objectUrl)
            .attr('alt', files[0].name || '');
          return;
        }

        if (!$preview.length || originalSrc === undefined) {
          return;
        }

        const objectUrl = $preview.data('object-url');
        if (objectUrl) {
          URL.revokeObjectURL(objectUrl);
        }
        $preview.removeData('object-url');
        if (originalSrc) {
          $preview.attr('src', originalSrc);
        } else {
          $preview.remove();
        }
      });

      const urlTab = new URLSearchParams(window.location.search).get('tab');
      if (urlTab) {
        const $targetBtn = $('button[data-bs-target="#' + urlTab + '"]').filter(':visible');
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

      const isArabic = {{ $l == 'ar' ? 'true' : 'false' }};

      // ─────────────────────────────────────────────────────────────
      // Section forms — FormValidation (client-side)
      // Validation errors only block the save; they never reset the form.
      // ─────────────────────────────────────────────────────────────
      const maxImageSize = 5 * 1024 * 1024; // 5 MB

      const validationMessages = {
        pageTitle:          "{{ __('landing.validation.page_title_required') }}",
        pageTitleMax:       "{{ __('landing.validation.page_title_max', ['max' => \App\LandingPage::TITLE_MAX_LENGTH]) }}",
        logoRequired:       "{{ __('landing.validation.logo_required') }}",
        logoUnsupported:    "{{ __('landing.validation.image_format_invalid') }}",
        logoSize:           "{{ __('landing.validation.logo_size_exceeded') }}",
        description:        "{{ __('landing.validation.description_required') }}",
        descriptionArabic:  "{{ __('landing.validation.description_ar_required') }}",
        heroTitle:          "{{ __('landing.validation.title_required') }}",
        heroSubtitle:       "{{ __('landing.validation.subtitle_required') }}",
        heroTitleArabic:    "{{ __('landing.validation.title_ar_required') }}",
        heroSubtitleArabic: "{{ __('landing.validation.subtitle_ar_required') }}",
        heroImageRequired:  "{{ __('landing.validation.hero_image_required') }}",
        heroImageFormat:    "{{ __('landing.validation.image_format_invalid') }}",
        heroImageSize:      "{{ __('landing.validation.hero_image_size_exceeded') }}",
        aboutTitle:         "{{ __('landing.validation.section_title_required') }}",
        aboutSubtitle:      "{{ __('landing.validation.section_subtitle_required') }}",
        aboutDescription:   "{{ __('landing.validation.section_description_required') }}",
        aboutTitleArabic:   "{{ __('landing.validation.section_title_ar_required') }}",
        aboutSubtitleArabic: "{{ __('landing.validation.section_subtitle_ar_required') }}",
        aboutDescriptionArabic: "{{ __('landing.validation.section_description_ar_required') }}",
        programsRequired:       "{{ __('landing.validation.field_required') }}",
        howTitle:               "{{ __('landing.validation.section_title_required') }}",
        howTitleArabic:         "{{ __('landing.validation.section_title_ar_required') }}",
        howSubtitle:            "{{ __('landing.validation.section_subtitle_required') }}",
        howSubtitleArabic:      "{{ __('landing.validation.section_subtitle_ar_required') }}",
        howDescription:         "{{ __('landing.validation.section_description_required') }}",
        howDescriptionArabic:   "{{ __('landing.validation.section_description_ar_required') }}",
        howStepTitle:           "{{ __('landing.validation.step_title_required') }}",
        howStepTitleArabic:     "{{ __('landing.validation.step_title_ar_required') }}",
        howStepIcon:            "{{ __('landing.validation.icon_required') }}",
        howStepDescription:     "{{ __('landing.validation.step_description_required') }}",
        howStepDescriptionArabic: "{{ __('landing.validation.step_description_ar_required') }}",
        customRequired:         "{{ __('landing.validation.custom_section_field_required') }}",
        customMaxLength:        "{{ __('landing.validation.custom_section_field_max', ['max' => 225]) }}",
        customUnsupportedType:  "{{ __('landing.validation.custom_image_type_invalid') }}",
        customImageSize:        "{{ __('landing.validation.custom_image_size_exceeded') }}",
        customButtonLink:       "{{ __('landing.validation.button_link_invalid') }}",
        cardIcon:           "{{ __('landing.validation.icon_required') }}",
        cardTitle:          "{{ __('landing.validation.card_title_required') }}",
        cardDescription:    "{{ __('landing.validation.card_description_required') }}",
        cardTitleArabic:    "{{ __('landing.validation.card_title_ar_required') }}",
        cardDescriptionArabic: "{{ __('landing.validation.card_description_ar_required') }}",
      };

      const successMessages = {
        general: "{{ __('landing.success.general_saved') }}",
        hero:    "{{ __('landing.success.hero_saved') }}",
        about:   "{{ __('landing.success.about_saved') }}",
        programs: "{{ __('landing.success.programs_saved') }}",
        how:      "{{ __('landing.success.how_saved') }}",
        custom:   "{{ __('landing.success.custom_saved') }}",
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
          'general[title]': {
            validators: {
              notEmpty: { message: validationMessages.pageTitle },
              stringLength: { max: {{ \App\LandingPage::TITLE_MAX_LENGTH }}, message: validationMessages.pageTitleMax },
            },
          },
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
        linkedin:  "{{ __('landing.validation.linkedin_url_invalid') }}",
        facebook:  "{{ __('landing.validation.facebook_url_invalid') }}",
        instagram: "{{ __('landing.validation.instagram_url_invalid') }}",
        x:         "{{ __('landing.validation.x_url_invalid') }}",
        tiktok:    "{{ __('landing.validation.tiktok_url_invalid') }}",
        youtube:   "{{ __('landing.validation.youtube_url_invalid') }}",
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
        { value: 'ti-rocket', label: "{{ __('landing.icons.rocket') }}" },
        { value: 'ti-bulb', label: "{{ __('landing.icons.bulb') }}" },
        { value: 'ti-target', label: "{{ __('landing.icons.target') }}" },
        { value: 'ti-chart-line', label: "{{ __('landing.icons.chart_line') }}" },
        { value: 'ti-users', label: "{{ __('landing.icons.users') }}" },
        { value: 'ti-trophy', label: "{{ __('landing.icons.trophy') }}" },
        { value: 'ti-shield', label: "{{ __('landing.icons.shield') }}" },
        { value: 'ti-heart', label: "{{ __('landing.icons.heart') }}" },
        { value: 'ti-star', label: "{{ __('landing.icons.star') }}" },
        { value: 'ti-book', label: "{{ __('landing.icons.book') }}" },
        { value: 'ti-code', label: "{{ __('landing.icons.code') }}" },
        { value: 'ti-cpu', label: "{{ __('landing.icons.cpu') }}" },
        { value: 'ti-world', label: "{{ __('landing.icons.world') }}" },
        { value: 'ti-flag', label: "{{ __('landing.icons.flag') }}" },
        { value: 'ti-crown', label: "{{ __('landing.icons.crown') }}" },
        { value: 'ti-gift', label: "{{ __('landing.icons.gift') }}" },
        { value: 'ti-briefcase', label: "{{ __('landing.icons.briefcase') }}" },
        { value: 'ti-cloud', label: "{{ __('landing.icons.cloud') }}" },
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
                <h6 class="mb-0 about-card-label">${"{{ __('landing.Card') }}"}</h6>
                <button type="button" class="btn btn-label-danger btn-sm d-flex align-items-center gap-1 remove-about-card">
                  <i class="ti ti-trash"></i>
                  ${"{{ __('landing.Remove') }}"}
                </button>
              </div>
              <div class="row g-4">
                <div class="col-md-4">
                  <label class="form-label">${"{{ __('landing.Icon') }}"}</label>
                  <select name="about[cards][${id}][icon]" class="form-select" data-card-field>
                    <option value="">${"{{ __('landing.Select_an_icon') }}"}</option>
                    ${iconOptions}
                  </select>
                </div>
                <div class="col-md-8">
                  <label class="form-label">${"{{ __('landing.Title') }}"}</label>
                  <input type="text" name="about[cards][${id}][title]" maxlength="{{ App\LandingPage::TITLE_MAX_LENGTH }}" class="form-control" data-card-field>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${"{{ __('landing.Title_ar') }}"}</label>
                  <input type="text" name="about[cards][${id}][title_ar]" maxlength="{{ App\LandingPage::TITLE_MAX_LENGTH }}" class="form-control" data-card-field>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${"{{ __('landing.Description') }}"}</label>
                  <textarea name="about[cards][${id}][description]" maxlength="{{ App\LandingPage::STEPS_DESCRIPTION_MAX_LENGTH }}" rows="4" class="form-control" data-card-field></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${"{{ __('landing.Description_ar') }}"}</label>
                  <textarea name="about[cards][${id}][description_ar]" maxlength="{{ App\LandingPage::STEPS_DESCRIPTION_MAX_LENGTH }}" rows="4" class="form-control" data-card-field></textarea>
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
          $(this).find('.about-card-label').text(("{{ __('landing.Card') }}") + (i + 1));
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

      // For the section enable switches: main fields are only mandatory
      // while the section is enabled. The state is applied both on toggle
      // and on initial load, so saving a disabled section never surfaces
      // required-field messages for its (empty) main fields.
      const sectionRequiredFields = {
        about:    ['title', 'title_tag', 'description', 'title_ar', 'title_tag_ar', 'description_ar'],
        how:      ['title', 'title_tag', 'description', 'title_ar', 'title_tag_ar', 'description_ar'],
        programs: ['title', 'title_tag', 'description', 'title_ar', 'title_tag_ar', 'description_ar'],
      };

      const setSectionRequiredState = function (sectionKey) {
        const fv = sectionValidations[sectionKey];
        if (!fv) return;
        const $switch = $('#' + sectionKey + '_is_enabled');
        const enabled = $switch.is(':checked');
        (sectionRequiredFields[sectionKey] || []).forEach(function (name) {
          const fieldName = sectionKey + '[' + name + ']';
          if (enabled) {
            fv.enableValidator(fieldName, 'notEmpty');
          } else {
            fv.disableValidator(fieldName, 'notEmpty');
          }
          fv.revalidateField(fieldName);
        });
      };

      $('#about_is_enabled').on('change', function () { setSectionRequiredState('about'); });
      $('#how_is_enabled').on('change', function () { setSectionRequiredState('how'); });
      $('#programs_is_enabled').on('change', function () { setSectionRequiredState('programs'); });
      ['about', 'how', 'programs'].forEach(setSectionRequiredState);

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
                <h6 class="mb-0 how-step-label">${"{{ __('landing.Step') }}"}</h6>
                <button type="button" class="btn btn-label-danger btn-sm d-flex align-items-center gap-1 remove-how-step">
                  <i class="ti ti-trash"></i>
                  ${"{{ __('landing.Remove') }}"}
                </button>
              </div>
              <div class="row g-4">
                <div class="col-md-4">
                  <label class="form-label">${"{{ __('landing.Icon') }}"}</label>
                  <select name="how[steps][${id}][icon]" class="form-select" data-step-field>
                    <option value="">${"{{ __('landing.Select_an_icon') }}"}</option>
                    ${iconOptions}
                  </select>
                </div>
                <div class="col-md-8">
                  <label class="form-label">${"{{ __('landing.Step_Title') }}"}</label>
                  <input type="text" name="how[steps][${id}][title]" maxlength="{{ App\LandingPage::TITLE_MAX_LENGTH }}" class="form-control" data-step-field>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${"{{ __('landing.Step_Title_ar') }}"}</label>
                  <input type="text" name="how[steps][${id}][title_ar]" maxlength="{{ App\LandingPage::TITLE_MAX_LENGTH }}" class="form-control" data-step-field>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${"{{ __('landing.Step_Description') }}"}</label>
                  <textarea name="how[steps][${id}][description]" maxlength="{{ App\LandingPage::STEPS_DESCRIPTION_MAX_LENGTH }}" rows="4" class="form-control" data-step-field></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${"{{ __('landing.Step_Description_ar') }}"}</label>
                  <textarea name="how[steps][${id}][description_ar]" maxlength="{{ App\LandingPage::STEPS_DESCRIPTION_MAX_LENGTH }}" rows="4" class="form-control" data-step-field></textarea>
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
          $(this).find('.how-step-label').text(("{{ __('landing.Step') }}") + (i + 1));
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

      // ─────────────────────────────────────────────────────────────
      // Programs selection list — client-side search + filters +
      // pagination (10 rows/page). Filtering only hides/shows rows so
      // any pending visibility switch changes are never lost.
      // ─────────────────────────────────────────────────────────────
      (function () {
        const $search = $('#programsSearchInput');
        const $visibility = $('#programsVisibilityFilter');
        const $org = $('#programsOrganizationFilter');
        const $type = $('#programsTypeFilter');
        const $rows = $('#programsListBody .programs-list-row');
        const $noResults = $('#programsNoResults');
        const $noResultsSearch = $('#programsNoResultsSearch');
        const $noResultsFilters = $('#programsNoResultsFilters');
        const $paginationInfo = $('#programsPaginationInfo');
        const $paginationList = $('#programsPaginationList');
        const perPage = 10;
        let currentPage = 1;

        if (!$rows.length) return;

        if (typeof $.fn.select2 === 'function') {
          $('#programsVisibilityFilter, #programsOrganizationFilter, #programsTypeFilter').select2({
            width: '100%',
            placeholder: "{{ __('landing.All') }}",
            allowClear: true,
            minimumResultsForSearch: 5,
          });
        }

        function noFilterValue(value) {
          return !value || value === 'all';
        }

        function getMatchingRows() {
          const search = ($search.val() || '').trim().toLowerCase();
          const visibility = $visibility.val();
          const org = $org.val();
          const type = $type.val();

          return $rows.filter(function () {
            const $row = $(this);
            const matchesSearch = !search || String($row.data('search-title')).includes(search);
            const matchesOrg = noFilterValue(org) || String($row.data('program-organization')) === String(org);
            const matchesType = noFilterValue(type) || String($row.data('program-type')) === String(type);
            // "Visible" = currently checked (selected for this landing page);
            // "Hidden" = currently unchecked. Read live so toggling a switch
            // updates the filter without needing a page reload.
            const isChecked = $row.find('.program-visibility-switch').is(':checked');
            const matchesVisibility = noFilterValue(visibility) || (visibility === 'visible' ? isChecked : !isChecked);
            return matchesSearch && matchesOrg && matchesType && matchesVisibility;
          });
        }

        function renderPagination(totalPages, total, start, shown) {
          $paginationList.empty();

          if (total === 0) {
            $paginationInfo.text('');
          } else {
            const from = start + 1;
            const to = start + shown;
            $paginationInfo.text(
              "{{ __('landing.Programs_Pagination_Info') }}"
                .replace(':from', from)
                .replace(':to', to)
                .replace(':total', total)
            );
          }

          if (totalPages <= 1) return;

          for (let page = 1; page <= totalPages; page++) {
            const $link = $('<button type="button" class="page-link"></button>').text(page);
            const $li = $('<li class="page-item"></li>').toggleClass('active', page === currentPage).append($link);
            $link.on('click', function () {
              currentPage = page;
              applyProgramsFilter();
            });
            $paginationList.append($li);
          }
        }

        function applyProgramsFilter() {
          const $matches = getMatchingRows();
          const total = $matches.length;
          const totalPages = Math.max(1, Math.ceil(total / perPage));
          if (currentPage > totalPages) currentPage = totalPages;

          $rows.hide();

          const start = (currentPage - 1) * perPage;
          const $pageRows = $matches.slice(start, start + perPage);
          $pageRows.show();

          const anyFilterActive = !noFilterValue($visibility.val()) || !noFilterValue($org.val()) || !noFilterValue($type.val());
          const searchActive = !!($search.val() || '').trim();

          $noResults.toggleClass('d-none', total > 0);
          $noResultsSearch.toggleClass('d-none', !(total === 0 && searchActive && !anyFilterActive));
          $noResultsFilters.toggleClass('d-none', !(total === 0 && (anyFilterActive || !searchActive)));

          renderPagination(totalPages, total, start, $pageRows.length);
        }

        $search.on('input', function () { currentPage = 1; applyProgramsFilter(); });
        $visibility.on('change', function () { currentPage = 1; applyProgramsFilter(); });
        $org.on('change', function () { currentPage = 1; applyProgramsFilter(); });
        $type.on('change', function () { currentPage = 1; applyProgramsFilter(); });

        // Re-apply when a switch is toggled so the Visible/Hidden filter
        // (driven by the switch's checked state) stays accurate live.
        $('#programsListBody').on('change', '.program-visibility-switch', function () {
          const $switch = $(this);
          const $row = $switch.closest('.programs-list-row');
          const programId = $row.data('program-id');

          if (!$switch.is(':checked')) {
            if (!noFilterValue($visibility.val())) applyProgramsFilter();
            return;
          }

          // Ask the server for the program's CURRENT availability state, so
          // a program fixed in another tab doesn't keep triggering the
          // warning from stale row data.
          const visibilityCheckUrl = "{{ url('dashboard/programlist') }}/" + programId + '/visibility-check';
          $.getJSON(visibilityCheckUrl, function (data) {
            if (data.is_public) {
              if (!noFilterValue($visibility.val())) applyProgramsFilter();
              return;
            }

            if (!$switch.is(':checked')) return; // user changed their mind while checking

            $switch.prop('checked', false);
            const programSettingsUrl = "{{ url('dashboard/programlist/edit') }}/" + programId + '#program-settings-visibility';
            releaseWarningAlert({
              title: "{{ __('admin.Warning') }}",
              message: "{{ __('landing.Program_not_public_hint') }}",
              confirmText: "{{ __('landing.Go_to_program_visibility') }}",
              cancelText: "{{ __('landing.Cancel') }}",
              confirmColor: 'primary',
              onConfirm: function () {
                window.open(programSettingsUrl, '_blank');
              },
            });
          });
        });

        applyProgramsFilter();
      })();

      // ─────────────────────────────────────────────────────────────
      // Programs selection list — drag-to-reorder (AJAX, client-side)
      // Sortable moves the actual <tr> nodes; on drop we read the new
      // DOM order straight off the row `data-program-id` attributes and
      // fire it to the backend immediately, independent of the section's
      // own Save button. Only rows visible on the current page/filter
      // are valid drop targets, so this reorders within what's on screen.
      // On the create screen the page does not exist until General is
      // saved, so the reorder URL starts null and is wired up from the
      // store response (res.routes.programs + '/reorder') once the page
      // has been created.
      // ─────────────────────────────────────────────────────────────
      let programsReorderUrl = null;
      let programsReorderInFlight = null;

      const programsReorderErrorText = isArabic
        ? 'تعذر حفظ الترتيب الجديد. يرجى المحاولة مرة أخرى.'
        : 'Could not save the new order. Please try again.';

      const sendProgramsReorder = function () {
        if (!programsReorderUrl) return;

        const order = $('#programsListBody .programs-list-row').map(function () {
          return $(this).attr('data-program-id');
        }).get();

        // Cancel any reorder request already in flight so a rapid second
        // drag doesn't race an earlier one and overwrite it with a stale order.
        if (programsReorderInFlight && programsReorderInFlight.abort) {
          programsReorderInFlight.abort();
        }

        programsReorderInFlight = $.ajax({
          url: programsReorderUrl,
          method: 'POST',
          data: {
            _token: "{{ csrf_token() }}",
            order: order,
          },
          dataType: 'json',
        })
          .fail(function (xhr) {
            if (xhr.statusText === 'abort') return;
            const message = (xhr && xhr.responseJSON && xhr.responseJSON.message) || programsReorderErrorText;
            releaseFailureAlert({
              title: "{{ __('admin.Error') }}",
              message: message,
            });
          })
          .always(function () {
            programsReorderInFlight = null;
          });
      };

      if (typeof Sortable !== 'undefined') {
        Sortable.create(document.getElementById('programsListBody'), {
          handle: '.program-drag-handle',
          animation: 150,
          ghostClass: 'program-drag-ghost',
          chosenClass: 'program-drag-chosen',
          onEnd: function (evt) {
            if (evt.oldIndex === evt.newIndex) return;
            sendProgramsReorder();
          },
        });
      }

      // ─────────────────────────────────────────────────────────────
      // Custom sections — block repeater with rich-text (Quill) editors
      // A block can stay fully empty; once any required field of a
      // block is filled, every required field becomes mandatory.
      // ─────────────────────────────────────────────────────────────
      const customQuills = new Map();
      const customRequiredFields = ['title', 'title_tag', 'content', 'title_ar', 'title_tag_ar', 'content_ar'];
      const customFieldMaxLengths = { title: 225, title_ar: 225, title_tag: 225, title_tag_ar: 225 };
      const customMaxSections = 5;
      let customSectionCounter = 0;

      const customSectionHtml = function (id) {
        return `
          <div class="card mb-3 border shadow-sm custom-section-card" data-section-id="${id}">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
              <h6 class="mb-0 custom-section-label">${"{{ __('landing.Custom_Section') }}"}</h6>
              <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-custom-section" title="${"{{ __('landing.Remove') }}"}">
                <i class="ti ti-trash"></i>
              </button>
            </div>
            <div class="card-body">
              <div class="row g-4">
                <div class="col-12 custom-field-group" data-field="visibility">
                  <label class="form-label d-block">${"{{ __('landing.Visibility') }}"}</label>
                  <div class="form-check form-switch">
                    <input type="hidden" name="custom_sections[${id}][visibility]" value="0">
                    <input type="checkbox" name="custom_sections[${id}][visibility]" value="1" class="form-check-input custom-visibility-switch" checked>
                  </div>
                </div>
                <div class="col-md-6 custom-field-group" data-field="title">
                  <label class="form-label">${"{{ __('landing.Title') }}"}</label>
                  <input type="text" name="custom_sections[${id}][title]" maxlength="{{ App\LandingPage::TITLE_MAX_LENGTH }}" class="form-control custom-section-input" data-custom-field>
                  <div class="form-text">${"{{ __('landing.Max') }}"} {{ App\LandingPage::TITLE_MAX_LENGTH }} ${"{{ __('landing.characters') }}"}</div>
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                <div class="col-md-6 custom-field-group" data-field="title_ar">
                  <label class="form-label">${"{{ __('landing.Title_ar') }}"}</label>
                  <input type="text" name="custom_sections[${id}][title_ar]" maxlength="{{ App\LandingPage::TITLE_MAX_LENGTH }}" class="form-control custom-section-input" data-custom-field>
                  <div class="form-text">${"{{ __('landing.Max') }}"} {{ App\LandingPage::TITLE_MAX_LENGTH }} ${"{{ __('landing.characters') }}"}</div>
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                <div class="col-md-6 custom-field-group" data-field="title_tag">
                  <label class="form-label">${"{{ __('landing.Title_tag') }}"}</label>
                  <input type="text" name="custom_sections[${id}][title_tag]" maxlength="{{ App\LandingPage::TITLE_MAX_LENGTH }}" class="form-control custom-section-input" data-custom-field>
                  <div class="form-text">${"{{ __('landing.Max') }}"} {{ App\LandingPage::TITLE_MAX_LENGTH }} ${"{{ __('landing.characters') }}"}</div>
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                <div class="col-md-6 custom-field-group" data-field="title_tag_ar">
                  <label class="form-label">${"{{ __('landing.Title_tag_ar') }}"}</label>
                  <input type="text" name="custom_sections[${id}][title_tag_ar]" maxlength="{{ App\LandingPage::TITLE_MAX_LENGTH }}" class="form-control custom-section-input" data-custom-field>
                  <div class="form-text">${"{{ __('landing.Max') }}"} {{ App\LandingPage::TITLE_MAX_LENGTH }} ${"{{ __('landing.characters') }}"}</div>
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                <div class="col-md-6 custom-field-group" data-field="content">
                  <label class="form-label">${"{{ __('landing.Content') }}"}</label>
                  <div class="custom-quill-editor" id="custom-quill-content-${id}" style="min-height: 160px"></div>
                  <input type="hidden" name="custom_sections[${id}][content]" class="custom-hidden-content" value="">
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                <div class="col-md-6 custom-field-group" data-field="content_ar">
                  <label class="form-label">${"{{ __('landing.Content_ar') }}"}</label>
                  <div class="custom-quill-editor" id="custom-quill-content-ar-${id}" style="min-height: 160px"></div>
                  <input type="hidden" name="custom_sections[${id}][content_ar]" class="custom-hidden-content" value="">
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div>
                {{-- <div class="col-md-6">
                  <label class="form-label">${"{{ __('landing.Button_text') }}"} <span class="text-muted">(${"{{ __('landing.Optional') }}"})</span></label>
                  <input type="text" name="custom_sections[${id}][button_text]" maxlength="35" class="form-control">
                </div>
                <div class="col-md-6 custom-field-group" data-field="button_link">
                  <label class="form-label">${"{{ __('landing.Button_link') }}"} <span class="text-muted">(${"{{ __('landing.Optional') }}"})</span></label>
                  <input type="url" name="custom_sections[${id}][button_link]" maxlength="250" class="form-control" placeholder="https://" data-custom-field>
                  <div class="custom-error-msg text-danger small mt-1" style="display:none"></div>
                </div> --}}
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
          const $group = $card.find('.custom-field-group[data-field="' + field + '"]');
          const value = customFieldValue($card, field);
          const max = customFieldMaxLengths[field];

          if (value === '') {
            setCustomFieldError($group, validationMessages.customRequired);
            valid = false;
            return;
          }

          if (max && value.length > max) {
            setCustomFieldError($group, validationMessages.customMaxLength);
            valid = false;
            return;
          }

          setCustomFieldError($group, '');
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
        $('#customSectionCount').text(count + ' / ' + customMaxSections).toggle(count > 0);
        $('#customAddSection').prop('disabled', count >= customMaxSections);
        $('#customSectionMaxHint').toggleClass('d-none', count < customMaxSections);
      };

      const updateCustomSectionLabels = function () {
        $('#customSectionsContainer .custom-section-card').each(function (i) {
          $(this).find('.custom-section-label').text(("{{ __('landing.Custom_Section') }}") + (i + 1));
        });
      };

      const quillTooltipMap = {
        '.ql-bold':          "{{ __('landing.quill_bold') }}",
        '.ql-italic':        "{{ __('landing.quill_italic') }}",
        '.ql-underline':     "{{ __('landing.quill_underline') }}",
        '.ql-ordered':       "{{ __('landing.quill_ordered_list') }}",
        '.ql-bullet':        "{{ __('landing.quill_bullet_list') }}",
        '.ql-link':          "{{ __('landing.quill_insert_link') }}",
        '.ql-image':         "{{ __('landing.quill_insert_image') }}",
        '.ql-clean':         "{{ __('landing.quill_clear_formatting') }}",
        '.ql-custom-button': "{{ __('landing.quill_insert_button') }}",
      };

      const applyQuillTooltips = function (quill) {
        if (!quill || !quill.container) return;
        const toolbar = quill.container.previousElementSibling;
        if (!toolbar) return;
        Object.entries(quillTooltipMap).forEach(function ([selector, label]) {
          const btn = toolbar.querySelector(selector);
          if (!btn || btn.hasAttribute('title')) return;
          btn.setAttribute('data-bs-toggle', 'tooltip');
          btn.setAttribute('data-bs-placement', 'top');
          btn.setAttribute('title', label);
          if (typeof bootstrap !== 'undefined') {
            bootstrap.Tooltip.getOrCreateInstance(btn);
          }
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
            placeholder: "{{ __('landing.Enter_Content_Placeholder') }}",
            direction: isArabic ? 'rtl' : 'ltr',
          });

          customQuills.set(el, quill);
          applyQuillTooltips(quill);

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

      const markCustomButtonAnchors = function (quill, html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        const matched = tmp.querySelectorAll('a[href]');
        const anchors = quill.root.querySelectorAll('a[href]');
        for (let m = 0; m < matched.length; m++) {
          const src = matched[m];
          if (!/(?:^|\s)btn(?:\s|$)/.test(src.getAttribute('class') || '')) continue;
          const href = src.getAttribute('href');
          const text = src.textContent.trim();
          let target = null;
          for (let i = 0; i < anchors.length; i++) {
            if (anchors[i].getAttribute('href') === href && anchors[i].textContent.trim() === text) {
              target = anchors[i];
            }
          }
          if (target) {
            target.setAttribute('class', 'btn btn-primary');
            target.setAttribute('target', '_blank');
            target.setAttribute('rel', 'noopener noreferrer');
          }
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
          markCustomButtonAnchors(activeButtonQuill, html);
          activeButtonQuill.setSelection(index + 1);
        }
        const modalEl = document.getElementById('customButtonModal');
        if (modalEl) {
          bootstrap.Modal.getInstance(modalEl).hide();
        }
      });

      const addCustomSection = function () {
        if ($('#customSectionsContainer .custom-section-card').length >= customMaxSections) {
          return;
        }
        const id = ++customSectionCounter;
        $('#customSectionsContainer').append(customSectionHtml(id));
        initCustomQuill($('#customSectionsContainer'));
        updateCustomSectionsEmpty();
        updateCustomSectionLabels();
        setTimeout(function () {
          $('#customSectionsContainer .custom-section-card').last().find('.custom-section-input').first().trigger('focus');
        }, 100);
      };

      $('#customAddSection').on('click', addCustomSection);

      $('#customSectionsContainer')
        .on('input change', '[data-custom-field]', function () {
          const $group = $(this).closest('.custom-field-group');
          if ($(this).val().trim()) setCustomFieldError($group, '');
        })
        .on('click', '.remove-custom-section', function () {
          const $card = $(this).closest('.custom-section-card');

          releaseWarningAlert({
            title: "{{ __('landing.Delete_Custom_Section') }}",
            message: "{{ __('landing.Delete_Custom_Section_Confirm') }}",
            confirmText: "{{ __('landing.Delete') }}",
            cancelText: "{{ __('landing.Cancel') }}",
            confirmColor: 'danger',
            onConfirm: function () {
              $card.find('.custom-quill-editor').each(function () {
                customQuills.delete(this);
              });
              $card.remove();
              updateCustomSectionsEmpty();
              updateCustomSectionLabels();
              releaseSuccessAlert({
                title: "{{ __('admin.success') }}",
                message: "{{ __('landing.success.custom_deleted') }}",
              });
            },
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
          const $backendErrorsBox = $form.find('.section-backend-errors');

          const clearBackendFieldErrors = function () {
            $form.find('.backend-error-feedback').remove();
            $form.find('.backend-error-input').removeClass('is-invalid backend-error-input');
          };

          // Backend error keys are dot-notation ("general.title",
          // "about.cards.0.icon"), so match them to this section by the
          // prefix before the first dot ("custom" submits "custom_sections").
          const sectionPrefixes = sectionKey === 'custom' ? ['custom_sections'] : [String(sectionKey)];

          const showBackendErrors = function (errors, message) {
            clearBackendFieldErrors();

            const items = [];
            let $firstInvalid = null;

            $.each(errors || {}, function (key, msgs) {
              const parts = String(key).split('.');
              if (sectionPrefixes.indexOf(parts[0]) === -1) return;

              const msgList = (Array.isArray(msgs) ? msgs : [msgs]).map(function (msg) {
                return String(msg);
              });
              msgList.forEach(function (msg) {
                items.push(msg);
              });

              // "about.cards.0.icon" -> input name "about[cards][0][icon]"
              const fieldName = parts.reduce(function (name, part, i) {
                return i === 0 ? part : name + '[' + part + ']';
              }, '');
              const $inputs = $form.find('[name="' + fieldName + '"]');
              if (!$inputs.length) return;

              $inputs.addClass('is-invalid backend-error-input');
              $inputs.last().after(
                $('<div class="invalid-feedback d-block backend-error-feedback"></div>').text(msgList[0])
              );

              // Prefer a focusable control; fall back to the Quill editor
              // for hidden rich-text backing inputs.
              const $visible = $inputs.filter(':visible').not('[type="hidden"]').first();
              const $target = $visible.length
                ? $visible
                : $inputs.closest('.custom-field-group').find('.ql-editor').first();
              if (!$firstInvalid && $target.length) {
                $firstInvalid = $target;
              }
            });

            if (!items.length) return false;

            // Prefer the backend's overall message; every errored field
            // also gets its own inline message below it.
            $backendErrorsBox.text(message || items[0]).removeClass('d-none');

            if ($firstInvalid) {
              $firstInvalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
              try {
                $firstInvalid[0].focus({ preventScroll: true });
              } catch (err) {
                $firstInvalid[0].focus();
              }
            } else if ($backendErrorsBox[0] && typeof $backendErrorsBox[0].scrollIntoView === 'function') {
              $backendErrorsBox[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return true;
          };

          $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
          clearBackendFieldErrors();
          $backendErrorsBox.addClass('d-none').empty();

          $.ajax({
            url: $form.attr('action'),
            method: 'POST', // method-spoofed to PUT via @method('PUT') hidden field
            data: new FormData(this),
            processData: false,
            contentType: false,
          })
            .done(function (res) {
              $btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>' + "{{ __('landing.Save') }}");
              clearBackendFieldErrors();
              $backendErrorsBox.addClass('d-none').empty();

              if (res.success && res.redirect) {
                window.location.href = res.redirect;
                return;
              }
              
              if (successMessages[sectionKey]) {
                releaseSuccessAlert({
                  title: "{{ __('admin.success') }}",
                  message: (res && res.message) || successMessages[sectionKey],
                });
              }
              // Saving General creates the page; rewire every tab's form
              // to the real per-page routes (backend-computed) so the rest
              // of the sections save like a normal edit, without leaving
              // the create screen.
              if (res && res.routes) {
                $('.section-form').each(function () {
                  const key = $(this).data('section');
                  if (res.routes[key]) {
                    $(this).attr('action', res.routes[key]);
                  }
                  $(this).find('fieldset').prop('disabled', false);
                  $(this).find('.alert-warning').addClass('d-none');
                });
                $('[data-bs-target^="#navs-"]').prop('disabled', false).removeAttr('title');
              }
              // Wire up the programs drag-to-reorder URL once the page
              // exists, so drops after the initial General save persist.
              if (res && res.routes && res.routes.programs) {
                programsReorderUrl = res.routes.programs + '/reorder';
              }
              // Same unlock for the "Custom Landing Page URL" card's Save
              // button, which lives outside the tabbed sections.
              if (res && res.url_save_route) {
                lpUrlSaveRoute = res.url_save_route;
                $('#lpUrlSaveBtn').prop('disabled', false).removeAttr('title');
                $('#lpUrlSaveHint').addClass('d-none');
              }
            })
            .fail(function (xhr) {
              $btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>' + "{{ __('landing.Save') }}");
              const res = xhr.responseJSON || {};

              // Show section-specific errors in the alert-danger at the top
              // of this section; fall back to the toast otherwise.
              if (showBackendErrors(res.errors, res.message)) return;

              let errorMessage = sectionKey === 'programs'
                ? ("{{ __('landing.Something_Went_Wrong') }}")
                : "{{ __('admin.unexpected_error') }}";
              if (res.message) {
                errorMessage = res.message;
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

        // Sections with an Enable switch are not required to have content
        // while disabled: submit straight away so no required-field
        // messages surface for their empty main fields.
        if (sectionRequiredFields[sectionKey]) {
          const $sectionSwitch = $('#' + sectionKey + '_is_enabled');
          if ($sectionSwitch.length && !$sectionSwitch.is(':checked')) {
            submitViaAjax();
            return;
          }
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
