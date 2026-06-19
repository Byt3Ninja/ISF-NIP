@extends('layouts.admin')

@php
  $form = $data;
  $sections = collect($form['build_data'] ?? []);
  $totalSections = $sections->count();
  $totalQuestions = $sections->sum(fn($section) => count($section['questions'] ?? []));
  // Previously this flag forced the whole form into a read-only view after
  // first submission, which blocked GV-2134's "founder can edit a
  // submitted form" flow. The form is now always editable; we only keep
  // the flag to drive the "Submitted" badge and the CTA label.
  $hasSubmission = $answers->count() > 0;
  $isReadOnly = false;
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
                @if($hasSubmission)
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
                          // old($fieldName) is the single source of truth: the
                          // controller flashInput()s DB answers on GET, and
                          // withInput() re-flashes the submission on a validation
                          // redirect. The value can be an array (POST of field_X[])
                          // or a JSON string (flashed DB answer) — normalize both.
                          $checkboxAnswers = old($fieldName, []);
                          if (is_string($checkboxAnswers)) {
                              $decoded = json_decode($checkboxAnswers, true);
                              $checkboxAnswers = is_array($decoded) ? $decoded : array_filter([$checkboxAnswers]);
                          }
                          $checkboxAnswers = is_array($checkboxAnswers) ? $checkboxAnswers : [];

                          // Detect a custom "Other" answer: any saved value that is
                          // not one of the predefined options is the free text the
                          // user typed (the __OTHER__ marker was replaced on save).
                          $predefinedOptions = $question['options'] ?? [];
                          $checkboxOtherValue = collect($checkboxAnswers)
                              ->first(fn($v) => $v !== '__OTHER__' && !in_array($v, $predefinedOptions, true));

                          // Same detection for scalar fields (dropdown / multiple_choice):
                          // a stored value that matches no predefined option is an
                          // "Other" free-text answer. Used to re-select __OTHER__,
                          // reveal the box, and prefill it on reload.
                          $scalarOld = old($fieldName);
                          $scalarOtherValue = ($scalarOld !== null && $scalarOld !== '' && $scalarOld !== '__OTHER__' && !in_array($scalarOld, $predefinedOptions, true))
                              ? $scalarOld
                              : null;
                          $scalarIsOther = $scalarOld === '__OTHER__' || $scalarOtherValue !== null;
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
                                          {{ ($scalarOld == $option || ($option === '__OTHER__' && $scalarIsOther)) ? 'selected' : '' }}
                                        >
                                          {{ $option === '__OTHER__' ? __('builder.Other') : $option }}
                                        </option>
                                      @endforeach
                                    </select>
                                  </div>
                                  <div id="{{ $fieldId }}_other_wrapper" class="mt-2 {{ $scalarIsOther ? '' : 'd-none' }}">
                                    <div class="input-group">
                                      <span class="input-group-text bg-label-primary border-end-0">
                                        <i class="ti ti-edit"></i>
                                      </span>
                                      <input
                                        type="text"
                                        class="form-control other-field"
                                        name="{{ $fieldName }}_other"
                                        id="{{ $fieldId }}_other"
                                        placeholder="{{ __('builder.Other_value') }}"
                                        value="{{ old($fieldName . '_other', $scalarOtherValue) }}"
                                        {{ $isReadOnly ? 'disabled' : '' }}
                                      />
                                    </div>
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
                                          {{ ($scalarOld == $option || ($option === '__OTHER__' && $scalarIsOther)) ? 'checked' : '' }}
                                          {{ $question['is_required'] ? 'required' : '' }}
                                          {{ $isReadOnly ? 'disabled' : '' }}
                                        />
                                        <label class="form-check-label w-100 ps-1" for="{{ $fieldId }}_{{ $optionIndex }}">
                                          {{ $option === '__OTHER__' ? __('builder.Other') : $option }}
                                        </label>
                                      </div>
                                    @endforeach
                                    <div id="{{ $fieldId }}_other_wrapper" class="mt-2 {{ $scalarIsOther ? '' : 'd-none' }}">
                                      <div class="input-group">
                                        <span class="input-group-text bg-label-primary border-end-0">
                                          <i class="ti ti-edit"></i>
                                        </span>
                                        <input
                                          type="text"
                                          class="form-control other-field"
                                          name="{{ $fieldName }}_other"
                                          id="{{ $fieldId }}_other"
                                          placeholder="{{ __('builder.Other_value') }}"
                                          value="{{ old($fieldName . '_other', $scalarOtherValue) }}"
                                          {{ $isReadOnly ? 'disabled' : '' }}
                                        />
                                      </div>
                                    </div>
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
                                          {{ (in_array($option, $checkboxAnswers, true) || ($option === '__OTHER__' && $checkboxOtherValue !== null)) ? 'checked' : '' }}
                                          data-logic="{{ $question['logic'][$option] ?? 'continue' }}"
                                          {{ $question['is_required'] ? 'required' : '' }}
                                          {{ $isReadOnly ? 'disabled' : '' }}
                                        />
                                        <label class="form-check-label w-100 ps-1" for="{{ $fieldId }}_{{ $optionIndex }}">
                                          {{ $option === '__OTHER__' ? __('builder.Other') : $option }}
                                        </label>
                                      </div>
                                    @endforeach
                                    <div id="{{ $fieldId }}_other_wrapper" class="mt-2 {{ $checkboxOtherValue !== null ? '' : 'd-none' }}">
                                      <div class="input-group">
                                        <span class="input-group-text bg-label-primary border-end-0">
                                          <i class="ti ti-edit"></i>
                                        </span>
                                        <input
                                          type="text"
                                          class="form-control other-field"
                                          name="{{ $fieldName }}_other"
                                          id="{{ $fieldId }}_other"
                                          placeholder="{{ __('builder.Other_value') }}"
                                          value="{{ old($fieldName . '_other', $checkboxOtherValue) }}"
                                          {{ $isReadOnly ? 'disabled' : '' }}
                                        />
                                      </div>
                                    </div>
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
                                  @php
                                    $existingFile = trim(old($fieldName) ?? '');
                                    // If the founder already uploaded a file for this field in a
                                    // previous submission we keep showing the download link and
                                    // drop the native `required` attribute + the hidden sentinel
                                    // tells the client-side validator that an existing file
                                    // already satisfies the rule. Uploading a new file replaces
                                    // the old one; leaving it untouched keeps it.
                                    $hasExistingFile = $existingFile !== '';
                                  @endphp
                                  @if($hasExistingFile)
                                    <input type="hidden" name="{{ $fieldName }}_existing" value="{{ $existingFile }}" />
                                  @endif
                                  <input
                                    type="file"
                                    class="form-control @error($fieldName) is-invalid @enderror"
                                    id="{{ $fieldId }}"
                                    name="{{ $fieldName }}"
                                    data-file-input="1"
                                    data-has-existing="{{ $hasExistingFile ? '1' : '0' }}"
                                    accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.pdf,.jpg,.jpeg,.png,.webp,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/pdf,image/jpeg,image/png,image/webp"
                                    {{ ($question['is_required'] && !$hasExistingFile) ? 'required' : '' }}
                                  />
                                  <div class="file-validation-error invalid-feedback d-none mt-1" id="{{ $fieldId }}-error"></div>
                                  <div class="form-text mt-2">
                                    {{ $l == 'ar'
                                      ? 'الحد الأقصى 10 ميجابايت. الأنواع المسموح بها: Word, Excel, PowerPoint, PDF, JPG, JPEG, PNG, WEBP.'
                                      : 'Max 10 MB. Allowed types: Word, Excel, PowerPoint, PDF, JPG, JPEG, PNG, WEBP.' }}
                                  </div>
                                  @if($hasExistingFile)
                                    <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                                      <span class="text-muted small">
                                        <i class="ti ti-paperclip me-1"></i>{{ $l == 'ar' ? 'الملف الحالي:' : 'Current file:' }}
                                      </span>
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
                  <div class="card-footer border-top bg-body px-4 px-lg-5 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="text-muted small">
                        {{ $sectionIndex + 1 }} / {{ count($form['build_data']) }} {{ $l == 'en' ? 'Sections' : 'اقسام' }}
                      </div>
                      <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-prev d-none" data-section-id="{{ $sectionIndex }}">
                          <i class="ti ti-arrow-left me-2"></i>{{ $l == 'en' ? 'Back' : 'السابق' }}
                        </button>
                        <button type="button" class="btn btn-primary btn-next" data-section-id="{{ $sectionIndex }}">
                          <span class="btn-next-text">{{ $l == 'en' ? 'Next' : 'التالي' }}</span>
                          <i class="ti ti-arrow-right ms-2 btn-next-icon"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach

            @if(!$isReadOnly)
              <div class="card border shadow-none" id="submit-step">
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
                    <div class="d-flex flex-column flex-sm-row gap-2">
                      <button type="button" class="btn btn-outline-secondary btn-lg btn-prev-submit">
                        <i class="ti ti-arrow-left me-2"></i>{{ $l == 'en' ? 'Back' : 'السابق' }}
                      </button>
                      <button type="submit" class="btn btn-primary btn-lg">
                        <i class="ti ti-send me-2"></i>{{ __('admin.submit') }}
                      </button>
                    </div>
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
        file_size: 'File size must not exceed 10 MB.',
        file_type: 'Supported file types are Word, Excel, PowerPoint, PDF, JPG, JPEG, PNG, and WEBP.',
      },
      ar: {
        required: 'هذا الحقل مطلوب.',
        email: 'يرجى إدخال بريد إلكتروني صحيح.',
        phone: 'يرجى إدخال رقم هاتف صالح.',
        url: 'يرجى إدخال رابط صالح يبدأ بـ http:// أو https://.',
        file_size: 'يجب ألا يتجاوز حجم الملف 10 ميجابايت.',
        file_type: 'أنواع الملفات المدعومة هي Word وExcel وPowerPoint وPDF وJPG وJPEG وPNG وWEBP.',
      }
    };

    const ALLOWED_FILE_EXTENSIONS = ['doc','docx','xls','xlsx','ppt','pptx','pdf','jpg','jpeg','png','webp'];
    const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024;

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
            // A previously-submitted file satisfies the required rule even
            // though the native <input type="file"> is empty on the client.
            if (field.dataset.hasExisting === '1') return true;
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

      if (field.type === 'file') {
        validators.callback_file_type = {
          message: t.file_type,
          callback: function () {
            if (!field.files || field.files.length === 0) return true;
            const name = field.files[0].name || '';
            const ext = name.split('.').pop().toLowerCase();
            return ALLOWED_FILE_EXTENSIONS.indexOf(ext) !== -1;
          }
        };
        validators.callback_file_size = {
          message: t.file_size,
          callback: function () {
            if (!field.files || field.files.length === 0) return true;
            return field.files[0].size <= MAX_FILE_SIZE_BYTES;
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

    window._formValidation = fv;

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
    // MUST target the application form by id. `document.querySelector("form")`
    // returns the FIRST form in the DOM — which is the header's
    // #stop-impersonation-form (when impersonating) or #logout-form (normal
    // login), both rendered before this one. Submitting that wrong form is what
    // logged the user out / stopped impersonation instead of saving answers.
    const form = document.getElementById("cycleApplicationForm");
    const submitBtn = form?.querySelector('button[type="submit"]');
    const submitStep = document.getElementById("submit-step");
    const logicInputs = document.querySelectorAll(".question-logic");
    const nextBtns = document.querySelectorAll(".btn-next");
    const prevBtns = document.querySelectorAll(".btn-prev");
    const prevSubmitBtn = document.querySelector(".btn-prev-submit");
    const sectionHistory = [0];

    // Enable/disable inputs in a section
    function setSectionInputsEnabled(section, enabled) {
      const inputs = section.querySelectorAll('input, select, textarea');
      inputs.forEach(input => {
        if (input.name === '_token' || input.name === 'builder_form_id' || input.name === 'field_ids' || input.name === 'cycle' || input.name === 'id') {
          return;
        }
        input.disabled = !enabled;
      });
    }

    function getNextLogic(section) {
      let logic = "continue";
      const inputs = section.querySelectorAll(".question-logic");

      inputs.forEach(input => {
        let questionLogic = "continue";
        if (input.tagName === "SELECT") {
          const selectedOption = input.options[input.selectedIndex];
          questionLogic = selectedOption ? (selectedOption.dataset.logic || "continue") : "continue";
        } else if (input.type === "radio" && input.checked) {
          questionLogic = input.dataset.logic || "continue";
        } else if (input.type === "checkbox" && input.checked) {
          const jumpLogic = input.dataset.logic || "continue";
          if (jumpLogic !== "continue") questionLogic = jumpLogic;
        }
        if (questionLogic !== "continue") {
          logic = questionLogic;
        }
      });

      if (logic === "continue") {
        logic = section.dataset.sectionLogic || "continue";
      }

      return logic;
    }

    function getActivePath() {
      const path = [0];
      let current = 0;
      // Guard against cyclic section logic (a "section-N" jump that points back
      // to an already-visited section). Without this the while loop never
      // terminates and freezes the browser ("a script is slowing down the page").
      const visited = new Set([0]);
      while (current < sections.length) {
        const section = sections[current];
        if (!section) break;
        const logic = getNextLogic(section);

        let nextIndex;
        if (logic === "submit") {
          path.push("submit");
          break;
        } else if (logic.startsWith("section-")) {
          const targetId = parseInt(logic.replace("section-", ""));
          nextIndex = sections[targetId] ? targetId : current + 1;
        } else {
          nextIndex = current + 1;
        }

        if (!sections[nextIndex]) {
          path.push("submit");
          break;
        }
        // A jump to an already-visited section would loop forever; stop here.
        if (visited.has(nextIndex)) {
          path.push("submit");
          break;
        }
        visited.add(nextIndex);
        path.push(nextIndex);
        current = nextIndex;
      }
      return path;
    }

    function updateNavButton(section) {
      const nextBtn = section.querySelector(".btn-next");
      if (!nextBtn) return;

      const logic = getNextLogic(section);
      const textSpan = nextBtn.querySelector(".btn-next-text");
      const iconSpan = nextBtn.querySelector(".btn-next-icon");

      if (logic === "submit") {
        if (textSpan) textSpan.textContent = document.documentElement.lang === 'ar' ? 'ذهاب للتسليم' : 'Go to submit';
        if (iconSpan) iconSpan.className = "ti ti-arrow-right ms-2 btn-next-icon";
      } else {
        if (textSpan) textSpan.textContent = document.documentElement.lang === 'ar' ? 'التالي' : 'Next';
        if (iconSpan) iconSpan.className = "ti ti-arrow-right ms-2 btn-next-icon";
      }
    }

    function showSection(index) {
      sections.forEach(s => { 
        s.style.display = "none"; 
        setSectionInputsEnabled(s, false);
      });
      if (submitStep) submitStep.style.display = "none";

      if (index === "submit") {
        if (submitStep) {
          submitStep.style.display = "block";
          submitStep.scrollIntoView({ behavior: "smooth", block: "start" });
        }
        return;
      }

      const section = sections[index];
      if (section) {
        section.style.display = "block";
        setSectionInputsEnabled(section, true);
        updateNavButton(section);

        const prevBtn = section.querySelector(".btn-prev");
        if (prevBtn) {
          prevBtn.classList.toggle("d-none", sectionHistory.length <= 1);
        }

        section.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    }

    function validateSection(section) {
      var fv = window._formValidation;
      if (!fv) return Promise.resolve(true);

      const inputs = section.querySelectorAll('input[name], select[name], textarea[name]');
      const promises = [];
      const validatedNames = new Set();

      inputs.forEach(input => {
        let name = input.name;
        if (name && fv.getFields()[name] && !validatedNames.has(name)) {
          validatedNames.add(name);
          promises.push(fv.validateField(name));
        }
      });

      if (promises.length === 0) {
        return Promise.resolve(true);
      }

      return Promise.all(promises).then(results => {
        return results.every(status => status === 'Valid');
      });
    }

    function doNavigate(currentSection) {
      const activePath = getActivePath();
      const currentIndex = parseInt(currentSection.dataset.sectionId);
      const idxInPath = activePath.indexOf(currentIndex);

      if (idxInPath !== -1 && activePath[idxInPath + 1] !== undefined) {
        const next = activePath[idxInPath + 1];
        if (!sectionHistory.includes(next)) {
          sectionHistory.push(next);
        }
        showSection(next);
      }
    }

    function navigateNext(currentSection) {
      validateSection(currentSection).then(function (isValid) {
        if (isValid) {
          doNavigate(currentSection);
        } else {
          var firstError = currentSection.querySelector(".is-invalid");
          if (firstError) firstError.focus();
        }
      });
    }

    function navigatePrev(currentSection) {
      if (sectionHistory.length <= 1) return;
      sectionHistory.pop();
      var prev = sectionHistory[sectionHistory.length - 1];
      showSection(prev);
    }

    // Initialize: show Section 1
    if (sections.length > 0) {
      showSection(0);
    }

    // Auto-update button labels when options change
    logicInputs.forEach(function (input) {
      input.addEventListener("change", function () {
        var section = this.closest(".section-wrapper");
        if (!section || section.style.display === "none") return;
        updateNavButton(section);

        // Truncate future visited sections from history as choice changed
        const currentIndex = parseInt(section.dataset.sectionId);
        const historyIndex = sectionHistory.indexOf(currentIndex);
        if (historyIndex !== -1) {
          sectionHistory.splice(historyIndex + 1);
        }
      });
    });

    // Handle next/prev button clicks
    nextBtns.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var section = this.closest(".section-wrapper");
        if (section) navigateNext(section);
      });
    });

    prevBtns.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var section = this.closest(".section-wrapper");
        if (section) navigatePrev(section);
      });
    });

    if (prevSubmitBtn) {
      prevSubmitBtn.addEventListener("click", function () {
        navigatePrev(null);
      });
    }

    // Toggle Other Fields helper logic
    function toggleOtherField(input) {
      let show = false;
      if (input.type === 'select-one') {
        show = input.value === '__OTHER__';
      } else if (input.type === 'radio') {
        const checked = document.querySelector(`input[name="${CSS.escape(input.name)}"]:checked`);
        show = checked && checked.value === '__OTHER__';
      } else if (input.type === 'checkbox') {
        const checkboxes = document.querySelectorAll(`input[name="${CSS.escape(input.name)}"]`);
        show = Array.from(checkboxes).some(cb => cb.checked && cb.value === '__OTHER__');
      }
      const wrapper = input.type === 'select-one'
        ? document.getElementById(input.id + '_other_wrapper')
        : input.closest('.application-question-card')?.querySelector('[id$="_other_wrapper"]');
      if (wrapper) {
        wrapper.classList.toggle('d-none', !show);
      }
    }

    // Restore "__OTHER__" selections after a validation-failure redirect. On
    // submit the JS normalizes __OTHER__ into the typed free-text value (see the
    // submit handler), so old($fieldName) comes back as the custom text and no
    // longer matches the __OTHER__ option. We detect this via the still-present
    // old() value of the `_other` text box: if it has content, the user picked
    // Other — so re-select the __OTHER__ option and reveal its input.
    document.querySelectorAll('.other-field').forEach(otherInput => {
      if (!otherInput.value.trim()) return;

      const card = otherInput.closest('.application-question-card');
      if (!card) return;

      // Re-select the __OTHER__ option on the source field(s).
      const select = card.querySelector('select.question-logic');
      if (select) {
        select.value = '__OTHER__';
      }
      card.querySelectorAll('input.question-logic[value="__OTHER__"]').forEach(input => {
        if (input.type === 'radio' || input.type === 'checkbox') {
          input.checked = true;
        }
      });

      // Reveal the wrapper holding the free-text box.
      const wrapper = card.querySelector('[id$="_other_wrapper"]');
      if (wrapper) {
        wrapper.classList.remove('d-none');
      }
    });

    document.querySelectorAll('.question-logic').forEach(input => {
      if (['select-one', 'radio', 'checkbox'].includes(input.type)) {
        toggleOtherField(input);
      }
    });

    document.addEventListener('change', function (e) {
      if (e.target.matches('.question-logic')) {
        if (e.target.type === 'radio') {
          document.querySelectorAll(`input[name="${CSS.escape(e.target.name)}"]`).forEach(r => toggleOtherField(r));
        } else {
          toggleOtherField(e.target);
        }
      }
    });

    function findOtherInput(field) {
      if (field.type === 'select-one') {
        return document.getElementById(field.id + '_other');
      }
      return field.closest('.application-question-card')?.querySelector('[id$="_other"]');
    }

    // Re-enable every input on the active path and refresh the hidden
    // `field_ids` value so the POST body contains the answers. The stepper
    // disables hidden sections (see showSection), and disabled inputs are
    // excluded from the submission — without this, the form would post empty,
    // failing server validation and bouncing the user back to login.
    // Replace any selected "__OTHER__" marker with the typed free-text value so
    // the server stores the real answer, not the literal "__OTHER__" sentinel.
    // MUST run on every submit path — including FormValidation's programmatic
    // form.submit(), which does NOT fire the form's 'submit' event. (Previously
    // this lived only in the submit-event handler, so FV submissions saved the
    // raw "__OTHER__" string and lost the typed text.)
    function normalizeOtherFields() {
      const handle = field => {
        // Skip already-normalized fields so calling this twice (submit event +
        // FV core.form.valid both run prepareSubmission) doesn't append a
        // duplicate hidden input.
        if (field.disabled || field.value !== '__OTHER__') return;
        const otherInput = findOtherInput(field);
        if (otherInput && otherInput.value.trim()) {
          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = field.name;
          hidden.value = otherInput.value.trim();
          form.appendChild(hidden);
          field.disabled = true; // exclude the raw "__OTHER__" value from the POST
        }
      };

      form.querySelectorAll('select.question-logic, input[type="radio"].question-logic:checked, input[type="checkbox"].question-logic:checked')
        .forEach(handle);
    }

    function prepareSubmission() {
      const activePath = getActivePath();

      // 1. Enable inputs in active sections, disable all others (so they are excluded from POST)
      sections.forEach((s, idx) => {
        setSectionInputsEnabled(s, activePath.includes(idx));
      });

      // 2. Normalize "__OTHER__" selections into their typed free-text values
      //    (after enabling active sections so their fields are reachable).
      normalizeOtherFields();

      // 3. Gather active field IDs and update the hidden 'field_ids' field
      const activeFieldIds = [];
      activePath.forEach(idx => {
        if (typeof idx !== 'number' || !sections[idx]) return;
        sections[idx].querySelectorAll('input[name], select[name], textarea[name]').forEach(input => {
          const match = input.name.match(/^field_(\d+)/);
          if (match) {
            activeFieldIds.push(match[1]);
          }
        });
      });
      const fieldIdsInput = form.querySelector('input[name="field_ids"]');
      if (fieldIdsInput) {
        fieldIdsInput.value = [...new Set(activeFieldIds)].join(',');
      }
    }

    // Form submission interception: dynamic enabled/disabled path and active field_ids setting
    if (form) {
      form.addEventListener('submit', function (e) {
        // Re-enable the active path and normalize "__OTHER__" on EVERY submit,
        // regardless of whether FormValidation is active. This is the path used
        // when FV is NOT created (no field has a validator); when FV IS active,
        // its core.form.valid handler calls prepareSubmission() instead.
        prepareSubmission();
      });
    }

    // Hook FormValidation success event to do final path enabling/disabling,
    // refresh field_ids, then submit natively (avoids the FormValidation loop).
    if (window._formValidation) {
      window._formValidation.on('core.form.valid', function () {
        prepareSubmission();
        form.submit();
      });
    }
  });
</script>
@stop
