@extends('layouts.admin')

@php
  $form = $data;
  $sections = collect($form['build_data'] ?? []);
  $totalSections = $sections->count();
  $totalQuestions = $sections->sum(fn($section) => count($section['questions'] ?? []));
  $isReadOnly = $answers->count() > 0;
  $typeMeta = [
      'short_answer' => ['icon' => 'ti ti-pencil', 'label' => __('builder.Short_answer')],
      'paragraph' => ['icon' => 'ti ti-align-left', 'label' => __('builder.Paragraph')],
      'multiple_choice' => ['icon' => 'ti ti-circle-dot', 'label' => __('builder.Multiple_choice')],
      'checkboxes' => ['icon' => 'ti ti-checkbox', 'label' => __('builder.Checkboxes')],
      'dropdown' => ['icon' => 'ti ti-selector', 'label' => __('builder.Dropdown')],
      'linear_scale' => ['icon' => 'ti ti-chart-dots-3', 'label' => __('builder.Linear_scale')],
      'date' => ['icon' => 'ti ti-calendar-event', 'label' => __('builder.Date')],
      'time' => ['icon' => 'ti ti-clock-hour-4', 'label' => __('builder.Time')],
      'file' => ['icon' => 'ti ti-paperclip', 'label' => __('builder.File_upload')],
  ];
@endphp

@section('title', __('cycles.Cycle'))
@section('subTitle', __('cycles.join Cycle'))

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
@endsection

@section('style')
<style>
  .application-hero {
    background:
      radial-gradient(circle at top right, rgba(var(--bs-success-rgb), 0.18), transparent 28%),
      linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.14), rgba(var(--bs-info-rgb), 0.08) 58%, rgba(var(--bs-body-bg-rgb), 0.1));
  }
</style>
@endsection

@section('content')
<section>
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm overflow-hidden">
        <div class="card-body application-hero p-4 p-lg-5 border-bottom">
          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-4">
            <div>
              <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="badge bg-label-primary rounded-pill px-3 py-2">
                  <i class="ti ti-rocket me-1"></i>{{ __('cycles.join Cycle') }}
                </span>
                @if($isReadOnly)
                  <span class="badge bg-label-success rounded-pill px-3 py-2">
                    <i class="ti ti-check me-1"></i>
                    {{ $l == 'en' ? 'Submitted' : 'تم الإرسال' }}
                  </span>
                @else
                  <span class="badge bg-label-secondary rounded-pill px-3 py-2">
                    <i class="ti ti-edit-circle me-1"></i>
                    {{ $l == 'en' ? 'Open for application' : 'مفتوح للتقديم' }}
                  </span>
                @endif
              </div>

              <h2 class="mb-2">{{ $form['title'] }}</h2>
              <p class="text-muted mb-0 mw-75">
                {{ $form['description'] }}
              </p>
            </div>
          </div>
        </div>

        <div class="card-body p-4 p-lg-5">
          @if(session()->has('msg'))
            <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show rounded-3 mb-4" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              <div class="d-flex align-items-center">
                <i class="bx bx-{{ Session::get('class') == 'success' ? 'check-circle' : 'error' }} me-2"></i>
                <span>{{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }}</span>
              </div>
            </div>
          @endif

          @if($form && $form['build_data'] && count($form['build_data']) > 0)
            @if(!$isReadOnly)
              <form id="cycleApplicationForm" method="post" action="/dashboard/cycles/save" enctype="multipart/form-data" class="d-flex flex-column gap-4" novalidate>
                @csrf
                <input type="hidden" name="cycle" value="{{ $c }}" />
                <input type="hidden" name="id" value="{{ $step->id }}" />
                <input type="hidden" name="field_ids" value="{{ $field_ids }}" />
            @endif

            @foreach($form['build_data'] as $sectionIndex => $section)
              <div class="section-wrapper" data-section-logic="{{ $section['logic'] ?? 'continue' }}" data-section-id="{{ $sectionIndex }}">
                <div class="card border shadow-sm overflow-hidden">
                  <div class="card-header bg-body border-0 px-4 px-lg-5 pt-4 pt-lg-5">
                    <div class="d-flex align-items-start gap-3">
                      <span class="avatar avatar-md">
                        <span class="avatar-initial rounded bg-label-primary fw-semibold">{{ $sectionIndex + 1 }}</span>
                      </span>
                      <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                          <span class="badge bg-label-primary rounded-pill">
                            {{ __('builder.Section') }} {{ $sectionIndex + 1 }}
                          </span>
                          <span class="badge bg-label-secondary rounded-pill">
                            {{ count($section['questions'] ?? []) }} {{ __('admin.Questions') }}
                          </span>
                        </div>
                        @if($section['title'])
                          <h4 class="mb-1">{{ $section['title'] }}</h4>
                        @endif
                        @if($section['description'])
                          <p class="text-muted mb-0">{{ $section['description'] }}</p>
                        @endif
                      </div>
                    </div>
                  </div>

                  <div class="card-body p-4 p-lg-5">
                    <div class="row g-4">
                      @foreach($section['questions'] as $question)
                        @php
                          $fieldName = 'field_' . $question['id'];
                          $fieldId = 'input-fld-' . $question['id'];
                          $meta = $typeMeta[$question['type']] ?? ['icon' => 'ti ti-help', 'label' => ucfirst(str_replace('_', ' ', $question['type']))];
                          $fullWidthTypes = ['paragraph', 'multiple_choice', 'checkboxes', 'linear_scale', 'file'];
                          $columnClass = in_array($question['type'], $fullWidthTypes, true) ? 'col-12' : 'col-12 col-xl-6';
                          $checkboxAnswers = collect($answers)->where('field_id', $question['id'])->first()?->answer ?? '[]';
                          $checkboxAnswers = json_decode($checkboxAnswers, true);
                        @endphp

                        <div class="{{ $columnClass }}">
                          <div class="card application-question-card border shadow-none h-100">
                            <div class="card-body p-4">
                              <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                <div class="d-flex align-items-start gap-3">
                                  <span class="avatar avatar-sm mt-1">
                                    <span class="avatar-initial rounded bg-label-primary">
                                      <i class="{{ $meta['icon'] }} ti-sm"></i>
                                    </span>
                                  </span>
                                  <div>
                                    @if($question['label'])
                                      <label for="{{ $fieldId }}" class="form-label fw-semibold mb-1">
                                        {{ $question['label'] }}
                                        @if($question['is_required'])
                                          <span class="text-danger ms-1">*</span>
                                        @endif
                                      </label>
                                    @endif
                                    <div class="text-muted small">
                                      {{ $question['is_required'] ? 'Required field' : 'Optional field' }}
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div class="application-field-control">
                              @switch($question['type'])
                                @case('dropdown')
                                  <div class="input-group">
                                    <span class="input-group-text bg-label-primary border-end-0">
                                      <i class="{{ $meta['icon'] }}"></i>
                                    </span>
                                    <select
                                      id="{{ $fieldId }}"
                                      name="{{ $fieldName }}"
                                      class="form-select question-logic @error($fieldName) is-invalid @enderror"
                                      {{ $question['is_required'] ? 'required' : '' }}
                                      {{ $isReadOnly ? 'disabled' : '' }}
                                    >
                                      <option value="">{{ $question['label'] }}</option>
                                      @foreach($question['options'] as $option)
                                        <option
                                          value="{{ $option }}"
                                          data-logic="{{ $question['logic'][$option] ?? 'continue' }}"
                                          {{ old($fieldName) == $option ? 'selected' : '' }}
                                        >
                                          {{ $option === '__OTHER__' ? __('builder.Other') : $option }}
                                        </option>
                                      @endforeach
                                    </select>
                                  </div>
                                  @break

                                @case('paragraph')
                                  <textarea
                                    id="{{ $fieldId }}"
                                    name="{{ $fieldName }}"
                                    rows="4"
                                    class="form-control @error($fieldName) is-invalid @enderror"
                                    placeholder="{{ $question['label'] }}"
                                    {{ $question['is_required'] ? 'required' : '' }}
                                    {{ $isReadOnly ? 'disabled' : '' }}
                                  >{{ old($fieldName) }}</textarea>
                                  @break

                                @case('multiple_choice')
                                  <div class="vstack gap-2 options-group">
                                    @foreach($question['options'] as $optionIndex => $option)
                                      <div class="form-check m-0 py-4 ps-10 border rounded-3">
                                        <input
                                          class="form-check-input question-logic"
                                          type="radio"
                                          name="{{ $fieldName }}"
                                          id="{{ $fieldId }}_{{ $optionIndex }}"
                                          value="{{ $option }}"
                                          data-logic="{{ $question['logic'][$option] ?? 'continue' }}"
                                          {{ old($fieldName) == $option ? 'checked' : '' }}
                                          {{ $question['is_required'] ? 'required' : '' }}
                                          {{ $isReadOnly ? 'disabled' : '' }}
                                        />
                                        <label class="form-check-label w-100 ps-1" for="{{ $fieldId }}_{{ $optionIndex }}">
                                          {{ $option === '__OTHER__' ? __('builder.Other') : $option }}
                                        </label>
                                      </div>
                                    @endforeach
                                  </div>
                                  @break

                                @case('checkboxes')
                                  <div class="vstack gap-2 options-group">
                                    @foreach($question['options'] as $optionIndex => $option)
                                      <div class="form-check m-0 py-4 ps-10 border rounded-3">
                                        <input
                                          class="form-check-input question-logic"
                                          type="checkbox"
                                          name="{{ $fieldName }}[]"
                                          id="{{ $fieldId }}_{{ $optionIndex }}"
                                          value="{{ $option }}"
                                          {{ is_array($checkboxAnswers) && in_array($option, $checkboxAnswers, true) ? 'checked' : '' }}
                                          data-logic="{{ $question['logic'][$option] ?? 'continue' }}"
                                          {{ $question['is_required'] ? 'required' : '' }}
                                          {{ $isReadOnly ? 'disabled' : '' }}
                                        />
                                        <label class="form-check-label w-100 ps-1" for="{{ $fieldId }}_{{ $optionIndex }}">
                                          {{ $option === '__OTHER__' ? __('builder.Other') : $option }}
                                        </label>
                                      </div>
                                    @endforeach
                                  </div>
                                  @break

                                @case('linear_scale')
                                  <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                                    <select
                                      id="{{ $fieldId }}"
                                      name="{{ $fieldName }}"
                                      class="form-select w-px-250 @error($fieldName) is-invalid @enderror"
                                      {{ $question['is_required'] ? 'required' : '' }}
                                      {{ $isReadOnly ? 'disabled' : '' }}
                                    >
                                      <option value="">Select a rating</option>
                                      @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old($fieldName) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                      @endfor
                                    </select>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                      @for($i = 1; $i <= 5; $i++)
                                        <span class="badge bg-label-primary rounded-pill px-3 py-2">{{ $i }}</span>
                                      @endfor
                                    </div>
                                  </div>
                                  <div class="d-flex justify-content-between text-muted small mt-2">
                                    <span>Low</span>
                                    <span>High</span>
                                  </div>
                                  @break

                                @case('date')
                                  <div class="input-group">
                                    <span class="input-group-text bg-label-primary border-end-0">
                                      <i class="{{ $meta['icon'] }}"></i>
                                    </span>
                                    <input
                                      type="text"
                                      class="form-control datepicker @error($fieldName) is-invalid @enderror"
                                      id="{{ $fieldId }}"
                                      name="{{ $fieldName }}"
                                      value="{{ old($fieldName) }}"
                                      placeholder="{{ $question['label'] }}"
                                      {{ $question['is_required'] ? 'required' : '' }}
                                      {{ $isReadOnly ? 'disabled' : '' }}
                                    />
                                  </div>
                                  @break

                                @case('time')
                                  <div class="input-group">
                                    <span class="input-group-text bg-label-primary border-end-0">
                                      <i class="{{ $meta['icon'] }}"></i>
                                    </span>
                                    <input
                                      type="text"
                                      class="form-control timepicker @error($fieldName) is-invalid @enderror"
                                      id="{{ $fieldId }}"
                                      name="{{ $fieldName }}"
                                      value="{{ old($fieldName) }}"
                                      placeholder="{{ $question['label'] }}"
                                      {{ $question['is_required'] ? 'required' : '' }}
                                      {{ $isReadOnly ? 'disabled' : '' }}
                                    />
                                  </div>
                                  @break

                                @case('file')
                                  <input
                                    type="file"
                                    class="form-control @error($fieldName) is-invalid @enderror"
                                    id="{{ $fieldId }}"
                                    name="{{ $fieldName }}"
                                    data-file-input="1"
                                    {{ $question['is_required'] ? 'required' : '' }}
                                    {{ $isReadOnly ? 'disabled' : '' }}
                                  />
                                  <div class="file-validation-error invalid-feedback d-none mt-1" id="{{ $fieldId }}-error"></div>
                                  @php
                                    $existingFile = trim(old($fieldName));
                                  @endphp
                                  @if($existingFile !== '')
                                    <div class="mt-2">
                                      <a href="{{ URL::to($existingFile) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-download me-1"></i>{{ __('admin.Download') }}
                                      </a>
                                    </div>
                                  @endif
                                  @break

                                @case('short_answer')
                                @default
                                  <div class="input-group">
                                    <span class="input-group-text bg-label-primary border-end-0">
                                      <i class="{{ $meta['icon'] }}"></i>
                                    </span>
                                    <input
                                      type="text"
                                      class="form-control application-short-answer @error($fieldName) is-invalid @enderror"
                                      id="{{ $fieldId }}"
                                      name="{{ $fieldName }}"
                                      value="{{ old($fieldName) }}"
                                      placeholder="{{ $question['label'] }}"
                                      {{ $question['is_required'] ? 'required' : '' }}
                                      {{ $isReadOnly ? 'disabled' : '' }}
                                    />
                                  </div>
                              @endswitch
                              </div>

                              @error($fieldName)
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                              @enderror
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>
            @endforeach

            @if(!$isReadOnly)
              <div class="card border shadow-none">
                <div class="card-body p-4 p-lg-5">
                  <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                      <h5 class="mb-1">
                        {{ $l == 'en' ? 'Complete your application' : 'اكمل تقديمك' }}
                      </h5>
                      <p class="text-muted mb-0">
                        {{ $l == 'en' 
                          ? 'Double-check the required answers and submit once everything looks ready.' 
                          : 'تحقق من الاجابات المطلوبة وقم بالتقديم بمجرد انتهاء التحقق' }}
                      </p>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">
                      <i class="ti ti-send me-2"></i>{{ __('admin.submit') }}
                    </button>
                  </div>
                </div>
              </div>
              </form>
            @endif
          @else
            <div class="alert alert-warning border border-warning rounded-3 mb-0">
              {{ $l == 'en' ? 'The form has no questions yet.' : 'لا يوجد أسئلة في هذا النموذج' }}
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
@endsection

@section('pagescripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('cycleApplicationForm');
    const submitMode = 'native';

    if (!form || typeof FormValidation === 'undefined') {
      return;
    }

    const locale = document.documentElement.lang === 'ar' ? 'ar' : 'en';
    const translations = {
      en: {
        required: 'This field is required.',
        email: 'Please enter a valid email address.',
        phone: 'Please enter a valid phone number.',
        url: 'Please enter a valid URL starting with http:// or https://.',
      },
      ar: {
        required: 'هذا الحقل مطلوب.',
        email: 'يرجى إدخال عنوان بريد إلكتروني صالح.',
        phone: 'يرجى إدخال رقم هاتف صالح.',
        url: 'يرجى إدخال رابط صالح يبدأ بـ http:// أو https://.',
      }
    };

    const t = translations[locale];
    const fields = {};
    const processedNames = new Set();
    let isSubmitting = false;

    const getLabel = field => {
      const questionCard = field.closest('.application-question-card');
      const label = questionCard?.querySelector('.form-label');
      return (label?.textContent || field.getAttribute('placeholder') || field.name || 'This field')
        .replace(/\*/g, '')
        .trim();
    };

    const addRequiredValidator = (validators, field, label) => {
      validators.callback = {
        message: t.required,
        callback: function () {
          const group = form.querySelectorAll(`[name="${field.name.replace(/"/g, '\\"')}"]`);

          if (field.type === 'radio') {
            return Array.from(group).some(input => input.checked);
          }

          if (field.type === 'checkbox') {
            return Array.from(group).some(input => input.checked);
          }

          if (field.type === 'file') {
            return field.files.length > 0 || field.value.trim() !== '';
          }

          return field.value.trim() !== '';
        }
      };
    };

    form.querySelectorAll('input[name], select[name], textarea[name]').forEach(field => {
      if (field.disabled || processedNames.has(field.name) || field.type === 'hidden') {
        return;
      }

      const label = getLabel(field);
      const validators = {};
      const labelText = label.toLowerCase();
      const isRequired = field.required || field.getAttribute('aria-required') === 'true';

      if (isRequired) {
        addRequiredValidator(validators, field, label);
      }

      if (labelText.includes('email')) {
        validators.emailAddress = {
          message: t.email
        };
      }

      if (labelText.includes('phone')) {
        validators.callback_phone = {
          message: t.phone,
          callback: function () {
            const value = field.value.trim();
            return value === '' || /^[+]?[\d\s().-]{6,20}$/.test(value);
          }
        };
      }

      if (labelText.includes('url') || labelText.includes('linkedin') || labelText.includes('portfolio') || labelText.includes('website')) {
        validators.callback_url = {
          message: t.url,
          callback: function () {
            const value = field.value.trim();
            return value === '' || /^(https?:\/\/).+/i.test(value);
          }
        };
      }

      if (Object.keys(validators).length > 0) {
        fields[field.name] = { validators };
      }

      processedNames.add(field.name);
    });

    if (Object.keys(fields).length === 0) {
      return;
    }

    const fv = FormValidation.formValidation(form, {
      fields,
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          rowSelector: '.application-question-card',
          eleInvalidClass: 'is-invalid',
          eleValidClass: ''
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      },
      init: instance => {
        instance.on('plugins.message.placed', function (e) {
          if (e.element.closest('.application-field-control')) {
            e.element.closest('.application-field-control').insertAdjacentElement('afterend', e.messageElement);
            return;
          }

          if (e.element.parentElement.classList.contains('input-group')) {
            e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
          }
        });
      }
    });

    fv.on('plugins.message.placed', function (e) {
      if (e.element.closest('.application-field-control')) {
        e.element.closest('.application-field-control').insertAdjacentElement('afterend', e.messageElement);
        return;
      }

      if (e.element.closest('.input-group')) {
        e.element.closest('.input-group').insertAdjacentElement('afterend', e.messageElement);
        return;
      }

      if (e.element.closest('.options-group')) {
        e.element.closest('.options-group').insertAdjacentElement('afterend', e.messageElement);
      }
    });

    form.querySelectorAll('input[name], select[name], textarea[name]').forEach(field => {
      if (field.disabled || !field.name || !fields[field.name]) {
        return;
      }

      ['change', 'input'].forEach(eventName => {
        field.addEventListener(eventName, function () {
          fv.revalidateField(field.name);
        });
      });
    });
  });
</script>

<script>
  flatpickr(".datepicker", { dateFormat: "Y-m-d", allowInput: true });
  flatpickr(".timepicker", { enableTime: true, noCalendar: true, dateFormat: "H:i", allowInput: true });

  (function () {
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB
    const ALLOWED_TYPES = [
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.ms-powerpoint',
      'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'application/pdf',
      'image/jpeg',
      'image/png',
      'image/webp',
    ];
    const ALLOWED_EXTS = /\.(doc|docx|xls|xlsx|ppt|pptx|pdf|jpg|jpeg|png|webp)$/i;

    const locale = document.documentElement.lang === 'ar' ? 'ar' : 'en';
    const messages = {
      en: {
        size: 'File size must not exceed 10 MB.',
        type: 'Supported file types are Word, Excel, PPT, PDF, JPG, JPEG, PNG, and WEBP.',
      },
      ar: {
        size: 'يجب ألا يتجاوز حجم الملف 10 ميجابايت.',
        type: 'أنواع الملفات المدعومة هي  Word وExcel وPPT وPDF وJPG وJPEG وPNG وWEBP.',
      },
    };
    const t = messages[locale];

    function validateFileInput(input) {
      const errorEl = document.getElementById(input.id + '-error');
      if (!errorEl) return true;

      const file = input.files[0];
      if (!file) {
        clearError(input, errorEl);
        return true;
      }

      const typeOk = ALLOWED_TYPES.includes(file.type) || ALLOWED_EXTS.test(file.name);
      if (!typeOk) {
        showError(input, errorEl, t.type);
        return false;
      }

      if (file.size > MAX_FILE_SIZE) {
        showError(input, errorEl, t.size);
        return false;
      }

      clearError(input, errorEl);
      return true;
    }

    function showError(input, errorEl, msg) {
      input.classList.add('is-invalid');
      errorEl.textContent = msg;
      errorEl.classList.remove('d-none');
      errorEl.classList.add('d-block');
    }

    function clearError(input, errorEl) {
      input.classList.remove('is-invalid');
      errorEl.textContent = '';
      errorEl.classList.add('d-none');
      errorEl.classList.remove('d-block');
    }

    document.addEventListener('DOMContentLoaded', function () {
      const fileInputs = document.querySelectorAll('input[data-file-input]');
      const form = document.getElementById('cycleApplicationForm');

      function allFileInputsValid() {
        let valid = true;
        fileInputs.forEach(function (input) {
          if (!validateFileInput(input)) valid = false;
        });
        return valid;
      }

      function scrollToFirstFileError() {
        const firstInvalid = form && form.querySelector('input[data-file-input].is-invalid');
        if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }

      fileInputs.forEach(function (input) {
        input.addEventListener('change', function () {
          validateFileInput(input);
        });
      });

      if (form) {
        // Override form.submit() so programmatic calls (FormValidation DefaultSubmit,
        // logic handler) are also blocked when a file error exists.
        const nativeSubmit = HTMLFormElement.prototype.submit.bind(form);
        form.submit = function () {
          if (!allFileInputsValid()) {
            scrollToFirstFileError();
            return;
          }
          nativeSubmit();
        };

        // Also block the submit event (covers button clicks not going through FV).
        form.addEventListener('submit', function (e) {
          if (!allFileInputsValid()) {
            e.preventDefault();
            e.stopImmediatePropagation();
            scrollToFirstFileError();
          }
        }, true);
      }
    });
  })();

  document.addEventListener("DOMContentLoaded", function () {
    const sections = document.querySelectorAll(".section-wrapper");
    const form = document.querySelector("form");
    const submitBtn = form?.querySelector('button[type="submit"]');
    const submitCard = submitBtn?.closest('.card');
    const logicInputs = document.querySelectorAll(".question-logic");

    sections.forEach(section => {
      section.style.display = "block";
    });

    function handleLogic(element) {
      if (!element) return;
      let logic = "continue";

      if (element.tagName === "SELECT") {
        const selectedOption = element.options[element.selectedIndex];
        logic = selectedOption ? selectedOption.dataset.logic : "continue";
      }

      if (logic === "submit") {
        if (submitCard) {
          submitCard.scrollIntoView({ behavior: "smooth", block: "center" });
        }
        setTimeout(() => {
          if (form) form.submit();
        }, 600);
      }
    }

    logicInputs.forEach(input => {
      input.addEventListener("change", function () {
        handleLogic(this);
      });
    });
  });
</script>
@stop
