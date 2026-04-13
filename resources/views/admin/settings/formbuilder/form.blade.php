@extends('layouts.admin')

@php
  $form = $data;
  $sections = collect($form['build_data'] ?? []);
  $totalSections = $sections->count();
  $totalQuestions = $sections->sum(fn($section) => count($section['questions'] ?? []));
  $fieldIds = $sections
      ->flatMap(fn($section) => collect($section['questions'] ?? [])->pluck('id'))
      ->implode(',');
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

@section('title', __('builder.Forms builder'))
@section('subTitle', __('builder.Preview'))

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
      <div class="card border shadow-sm overflow-hidden">
        <div class="card-body application-hero p-4 p-lg-5 border-bottom">
          <div class="d-flex justify-content-between align-items-center">
            <span class="badge bg-label-primary border border-primary rounded-pill px-3 py-2">
              {{ __('builder.Form') }} ID: {{ $form['id'] }}
            </span>
            <a href="/dashboard/show-builder/{{ $form['id'] }}" class="btn btn-primary">
              <i class="ti ti-edit me-2"></i>{{ __('builder.Edit') }}
            </a>
          </div>
          <div>
            <h2 class="mb-2">{{ $form['title'] }}</h2>
            <p class="text-muted mb-0 mw-75">
              {{ $l == 'en' 
                ? 'Review the full application form.' 
                : 'مراجعة النموذج الكامل.' }}
            </p>
          </div>
          <div class="d-flex flex-wrap gap-3 mt-4">
            <div class="border bg-body rounded-4 px-8 py-2">
              <div class="text-muted small text-uppercase mb-1">
                {{ $l == 'en' ? 'Sections' : 'الاقسام' }}
              </div>
              <div class="fw-semibold fs-5">{{ $totalSections }}</div>
            </div>
            <div class="border bg-body rounded-4 px-8 py-2">
              <div class="text-muted small text-uppercase mb-1">
                {{ $l == 'en' ? 'Questions' : 'الاسئلة' }}
              </div>
              <div class="fw-semibold fs-5">{{ $totalQuestions }}</div>
            </div>
          </div>
        </div>

        <div class="card-body p-4 p-lg-5">
          @if($form && $form['build_data'] && count($form['build_data']) > 0)
            <form id="applicationPreviewForm" action="{{ route('forms.submissions_create') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-4" novalidate>
              @csrf
              <input type="hidden" name="builder_form_id" value="{{ $id }}" />
              <input type="hidden" name="field_ids" value="{{ $fieldIds }}" />

              @foreach($form['build_data'] as $sectionIndex => $section)
                <div class="section-wrapper" data-section-logic="{{ $section['logic'] ?? 'continue' }}" data-section-id="{{ $sectionIndex }}">
                  <div class="card border shadow-sm overflow-hidden">
                    <div class="card-header bg-body border-0 px-4 px-lg-5 pt-4 pt-lg-5">
                      <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
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
                            $checkboxOld = old($fieldName, []);
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

                                  <span class="badge bg-label-secondary rounded-pill">{{ $meta['label'] }}</span>
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
                                        data-qtype="dropdown"
                                        {{ $question['is_required'] ? 'required' : '' }}
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
                                      data-qtype="paragraph"
                                      {{ $question['is_required'] ? 'required' : '' }}
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
                                            data-qtype="multiple_choice"
                                            {{ old($fieldName) == $option ? 'checked' : '' }}
                                            {{ $question['is_required'] ? 'required' : '' }}
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
                                            data-logic="{{ $question['logic'][$option] ?? 'continue' }}"
                                            data-qtype="checkboxes"
                                            {{ in_array($option, is_array($checkboxOld) ? $checkboxOld : [], true) ? 'checked' : '' }}
                                            {{ $question['is_required'] ? 'required' : '' }}
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
                                        data-qtype="date"
                                        {{ $question['is_required'] ? 'required' : '' }}
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
                                        data-qtype="time"
                                        {{ $question['is_required'] ? 'required' : '' }}
                                      />
                                    </div>
                                    @break

                                  @case('file')
                                    <input
                                      type="file"
                                      class="form-control @error($fieldName) is-invalid @enderror"
                                      id="{{ $fieldId }}"
                                      name="{{ $fieldName }}"
                                      data-qtype="file"
                                      {{ $question['is_required'] ? 'required' : '' }}
                                    />
                                    <div class="form-text mt-2">Accepted file is attached as part of the application package.</div>
                                    @break

                                  @case('number')
                                    <div class="input-group">
                                      <span class="input-group-text bg-label-primary border-end-0">
                                        <i class="{{ $meta['icon'] }}"></i>
                                      </span>
                                      <input
                                        type="number"
                                        inputmode="decimal"
                                        class="form-control @error($fieldName) is-invalid @enderror"
                                        id="{{ $fieldId }}"
                                        name="{{ $fieldName }}"
                                        value="{{ old($fieldName) }}"
                                        placeholder="{{ $question['label'] }}"
                                        data-qtype="number"
                                        {{ $question['is_required'] ? 'required' : '' }}
                                      />
                                    </div>
                                    @break

                                  @case('email')
                                    <div class="input-group">
                                      <span class="input-group-text bg-label-primary border-end-0">
                                        <i class="{{ $meta['icon'] }}"></i>
                                      </span>
                                      <input
                                        type="email"
                                        class="form-control @error($fieldName) is-invalid @enderror"
                                        id="{{ $fieldId }}"
                                        name="{{ $fieldName }}"
                                        value="{{ old($fieldName) }}"
                                        placeholder="{{ $question['label'] }}"
                                        data-qtype="email"
                                        {{ $question['is_required'] ? 'required' : '' }}
                                      />
                                    </div>
                                    @break

                                  @case('url')
                                    <div class="input-group">
                                      <span class="input-group-text bg-label-primary border-end-0">
                                        <i class="{{ $meta['icon'] }}"></i>
                                      </span>
                                      <input
                                        type="url"
                                        class="form-control @error($fieldName) is-invalid @enderror"
                                        id="{{ $fieldId }}"
                                        name="{{ $fieldName }}"
                                        value="{{ old($fieldName) }}"
                                        placeholder="{{ $question['label'] }}"
                                        data-qtype="url"
                                        {{ $question['is_required'] ? 'required' : '' }}
                                      />
                                    </div>
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
                                        data-qtype="short_answer"
                                        {{ $question['is_required'] ? 'required' : '' }}
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

              <div class="card border shadow-none">
                <div class="card-body p-4 p-lg-5">
                  <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                      <h5 class="mb-1">
                        {{ $l == 'en' ? 'Ready to review this application?' : 'جاهز للمراجعة؟' }}
                      </h5>
                      <p class="text-muted mb-0">
                        {{ $l == 'en' 
                          ? 'Use the preview to validate content, required fields, and flow before publishing or submitting.' 
                          : 'استخدم المعاينة للتحقق من المحتوى، الحقول المطلوبة، والتدفق قبل النشر أو الإرسال'}}
                      </p>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2">
                      @if(auth()->user()->hasAnyRole('registered'))
                        <button type="submit" class="btn btn-primary btn-lg">
                          <i class="ti ti-send me-2"></i>{{ __('builder.Submit_Form') }}
                        </button>
                      @endif
                      <button type="button" class="btn btn-outline-secondary btn-lg" onclick="location.href='/dashboard/show-builder/{{ $id }}'">
                        <i class="ti ti-edit me-2"></i>{{ __('builder.Edit_Form') }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </form>
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
    const form = document.getElementById('applicationPreviewForm');
    const submitMode = 'native';

    if (!form || typeof FormValidation === 'undefined') {
      return;
    }

    const locale = document.documentElement.lang === 'ar' ? 'ar' : 'en';
    const translations = {
      en: {
        required: 'This field is required.',
        email: 'Please enter a valid email address.',
        numeric: 'Please enter numbers only.',
        time: 'Please enter a valid time.',
        url: 'Please enter a valid URL starting with http:// or https://.',
        confirm_submit_title: 'Submit application?',
        confirm_submit_message: 'Once you submit, uploaded files cannot be recovered automatically if a server-side error occurs. You will need to re-attach them. Continue?',
        confirm_yes: 'Yes, submit',
        confirm_no: 'Cancel',
      },
      ar: {
        required: 'هذا الحقل مطلوب.',
        email: 'يرجى إدخال بريد إلكتروني صحيح.',
        numeric: 'يرجى إدخال أرقام فقط.',
        time: 'يرجى إدخال وقت صحيح.',
        url: 'يرجى إدخال رابط صالح يبدأ بـ http:// أو https://.',
        confirm_submit_title: 'إرسال النموذج؟',
        confirm_submit_message: 'بمجرد الإرسال، لا يمكن استرجاع الملفات المرفوعة تلقائياً في حال حدوث خطأ من الخادم. ستحتاج إلى إعادة إرفاقها. هل تريد المتابعة؟',
        confirm_yes: 'نعم، إرسال',
        confirm_no: 'إلغاء',
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

    const timeRegex = /^([01]?\d|2[0-3]):[0-5]\d$/;

    form.querySelectorAll('input[name], select[name], textarea[name]').forEach(field => {
      if (field.disabled || processedNames.has(field.name) || field.type === 'hidden') {
        return;
      }

      const label = getLabel(field);
      const validators = {};
      const qtype = field.dataset.qtype || '';
      const isRequired = field.required || field.getAttribute('aria-required') === 'true';

      if (isRequired) {
        addRequiredValidator(validators, field, label);
      }

      if (qtype === 'email') {
        validators.emailAddress = { message: t.email };
      }

      if (qtype === 'number' || qtype === 'linear_scale') {
        validators.callback_numeric = {
          message: t.numeric,
          callback: function () {
            const value = (field.value || '').trim();
            if (value === '') return true;
            return !isNaN(value) && isFinite(value);
          }
        };
      }

      if (qtype === 'time') {
        validators.callback_time = {
          message: t.time,
          callback: function () {
            const value = (field.value || '').trim();
            return value === '' || timeRegex.test(value);
          }
        };
      }

      if (qtype === 'url') {
        validators.callback_url = {
          message: t.url,
          callback: function () {
            const value = (field.value || '').trim();
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

    const hasFileField = form.querySelector('input[type="file"]') !== null;

    const pluginConfig = {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        rowSelector: '.application-question-card',
        eleInvalidClass: 'is-invalid',
        eleValidClass: ''
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    };
    // Only auto-submit when there are no file fields. With file fields we
    // intercept with a confirmation dialog below (all-or-nothing submission).
    if (!hasFileField) {
      pluginConfig.defaultSubmit = new FormValidation.plugins.DefaultSubmit();
    }

    const fv = FormValidation.formValidation(form, {
      fields,
      plugins: pluginConfig,
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

    // All-or-nothing submit confirmation for forms containing file uploads.
    // Files cannot be repopulated by `old()` after a failed submission, so we
    // warn the user once upfront: either the submission completes fully, or
    // they cancel and nothing is sent.
    if (hasFileField) {
      fv.on('core.form.valid', function () {
        const ok = window.confirm(t.confirm_submit_title + '\n\n' + t.confirm_submit_message);
        if (ok) {
          HTMLFormElement.prototype.submit.call(form);
        }
      });
    }
  });
</script>

<script>
  flatpickr(".datepicker", { dateFormat: "Y-m-d", allowInput: true });
  flatpickr(".timepicker", { enableTime: true, noCalendar: true, dateFormat: "H:i", allowInput: true });

  document.addEventListener("DOMContentLoaded", function () {
    const sections = document.querySelectorAll(".section-wrapper");
    const form = document.querySelector("form");
    const submitBtn = form?.querySelector('.btn[type="submit"]');
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
      } else if (element.type === "radio") {
        logic = element.dataset.logic || "continue";
      } else if (element.type === "checkbox") {
        const optionsGroup = element.closest(".options-group");
        if (optionsGroup) {
          const checkedBoxes = optionsGroup.querySelectorAll("input[type='checkbox']:checked");
          for (const box of checkedBoxes) {
            if (box.dataset.logic === "submit") {
              logic = "submit";
              break;
            } else if (box.dataset.logic.startsWith("section-")) {
              logic = box.dataset.logic;
              break;
            }
          }
        }
      }

      const currentSection = element.closest(".section-wrapper");
      if (!currentSection) return;
      const currentIndex = Array.from(sections).indexOf(currentSection);

      const sectionLogic = currentSection.dataset.sectionLogic || "continue";
      if (sectionLogic === "submit" && logic === "continue") {
        logic = "submit";
      }

      if (logic === "continue") {
        const nextSection = sections[currentIndex + 1];
        if (nextSection) {
          nextSection.scrollIntoView({ behavior: "smooth", block: "start" });
        } else if (submitCard) {
          submitCard.scrollIntoView({ behavior: "smooth", block: "center" });
        }
      } else if (logic.startsWith("section-")) {
        const targetId = parseInt(logic.replace("section-", ""));
        if (sections[targetId]) {
          sections[targetId].scrollIntoView({ behavior: "smooth", block: "start" });
        }
      } else if (logic === "submit") {
        if (submitCard) {
          submitCard.scrollIntoView({ behavior: "smooth", block: "center" });
        }
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
