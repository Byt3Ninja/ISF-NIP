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
      'url' => ['icon' => 'ti ti-link', 'label' => __('builder.URL')],
      'link' => ['icon' => 'ti ti-link', 'label' => __('builder.Link')],
      'number' => ['icon' => 'ti ti-numbers', 'label' => __('builder.Number')],
      'email' => ['icon' => 'ti ti-mail', 'label' => __('builder.Email')],
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
            @if($errors->has('upload'))
              <div class="alert alert-danger border border-danger rounded-3 mb-0">
                <i class="ti ti-alert-triangle me-2"></i>{{ $errors->first('upload') }}
              </div>
            @endif

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
                                        {{ $question['is_required'] ? __('admin.Required field') : __('admin.Optional field') }}
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
                                    <div id="{{ $fieldId }}_other_wrapper" class="mt-2 d-none">
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
                                          value="{{ old($fieldName . '_other') }}"
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
                                      placeholder="{{ form_field_placeholder($question) }}"
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
                                      <div id="{{ $fieldId }}_other_wrapper" class="mt-2 d-none">
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
                                            value="{{ old($fieldName . '_other') }}"
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
                                      <div id="{{ $fieldId }}_other_wrapper" class="mt-2 d-none">
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
                                            value="{{ old($fieldName . '_other') }}"
                                          />
                                        </div>
                                      </div>
                                    </div>
                                    @break

                                  @case('linear_scale')
                                    @php
                                      // GV-8349: render the scale itself, matching the
                                      // founder-facing form. See the note in
                                      // admin/settings/cycles/form.blade.php.
                                      $scaleMin = is_numeric($question['min'] ?? null) ? (int) $question['min'] : 1;
                                      $scaleMax = is_numeric($question['max'] ?? null) ? (int) $question['max'] : 5;
                                      if ($scaleMax <= $scaleMin) {
                                          $scaleMin = 1;
                                          $scaleMax = 5;
                                      }
                                      $scaleLow = trim((string) ($question['label_low'] ?? ''));
                                      $scaleHigh = trim((string) ($question['label_high'] ?? ''));
                                      $scaleValue = old($fieldName);
                                    @endphp
                                    <div class="linear-scale-group d-flex align-items-center flex-wrap gap-2">
                                      @if($scaleLow !== '')
                                        <span class="text-muted small me-1">{{ $scaleLow }}</span>
                                      @endif
                                      @for($i = $scaleMin; $i <= $scaleMax; $i++)
                                        <input
                                          type="radio"
                                          class="btn-check"
                                          name="{{ $fieldName }}"
                                          id="{{ $fieldId }}_{{ $i }}"
                                          value="{{ $i }}"
                                          autocomplete="off"
                                          {{ (string) $scaleValue === (string) $i ? 'checked' : '' }}
                                          {{ ($question['is_required'] && $i === $scaleMin) ? 'required' : '' }}
                                        />
                                        <label class="btn btn-outline-primary rounded-pill px-3" for="{{ $fieldId }}_{{ $i }}">{{ $i }}</label>
                                      @endfor
                                      @if($scaleHigh !== '')
                                        <span class="text-muted small ms-1">{{ $scaleHigh }}</span>
                                      @endif
                                    </div>
                                    @error($fieldName)
                                      <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
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
                                        placeholder="{{ form_field_placeholder($question) }}"
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
                                        placeholder="{{ form_field_placeholder($question) }}"
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
                                      accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.pdf,.jpg,.jpeg,.png,.webp,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/pdf,image/jpeg,image/png,image/webp"
                                      {{ $question['is_required'] ? 'required' : '' }}
                                    />
                                    <div class="form-text mt-2">
                                      {{ $l == 'ar'
                                        ? 'الحد الأقصى 10 ميجابايت. الأنواع المسموح بها: Word, Excel, PowerPoint, PDF, JPG, JPEG, PNG, WEBP.'
                                        : 'Max 10 MB. Allowed types: Word, Excel, PowerPoint, PDF, JPG, JPEG, PNG, WEBP.' }}
                                    </div>
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
                                        placeholder="{{ form_field_placeholder($question) }}"
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
                                        placeholder="{{ form_field_placeholder($question) }}"
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
                                        placeholder="{{ form_field_placeholder($question) }}"
                                        data-qtype="url"
                                        data-allowed-domains="{{ $question['allowed_domains'] ?? '' }}"
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
                                        placeholder="{{ form_field_placeholder($question) }}"
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

               <div class="card border shadow-none" id="submit-step" style="display: none;">
                 <div class="card-body p-4 p-lg-5">
                   <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                     <div>
                       <h5 class="mb-1">
                         {{ $l == 'en' ? 'Ready to submit this application?' : 'جاهز لإرسال هذا النموذج؟' }}
                       </h5>
                       <p class="text-muted mb-0">
                         {{ $l == 'en' 
                           ? 'Please review your answers before submitting.' 
                           : 'يرجى مراجعة إجاباتك قبل الإرسال.'}}
                       </p>
                     </div>
 
                     <div class="d-flex flex-column flex-sm-row gap-2">
                       <button type="button" class="btn btn-outline-secondary btn-prev-submit btn-lg">
                         <i class="ti ti-arrow-left me-2"></i>{{ $l == 'en' ? 'Back' : 'السابق' }}
                       </button>
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
        allowed_domains: 'The URL must belong to one of these domains: ',
        file_size: 'File size must not exceed 10 MB.',
        file_type: 'Supported file types are Word, Excel, PowerPoint, PDF, JPG, JPEG, PNG, and WEBP.',
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
        allowed_domains: 'يجب أن ينتمي الرابط إلى أحد هذه النطاقات: ',
        file_size: 'يجب ألا يتجاوز حجم الملف 10 ميجابايت.',
        file_type: 'أنواع الملفات المدعومة هي Word وExcel وPowerPoint وPDF وJPG وJPEG وPNG وWEBP.',
        confirm_submit_title: 'إرسال النموذج؟',
        confirm_submit_message: 'بمجرد الإرسال، لا يمكن استرجاع الملفات المرفوعة تلقائياً في حال حدوث خطأ من الخادم. ستحتاج إلى إعادة إرفاقها. هل تريد المتابعة؟',
        confirm_yes: 'نعم، إرسال',
        confirm_no: 'إلغاء',
      }
    };

    const ALLOWED_FILE_EXTENSIONS = ['doc','docx','xls','xlsx','ppt','pptx','pdf','jpg','jpeg','png','webp'];
    const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024;

    const t = translations[locale];

    // URL "allowed domains" matching — mirrors App\Traits\ValidatesUrlDomains so
    // client and server enforce the same rule (host equals the domain or is a
    // subdomain of it). Empty list = no restriction.
    const parseAllowedDomains = raw => (raw || '')
      .split(',')
      .map(d => d.trim().toLowerCase().replace(/^https?:\/\//, '').replace(/^www\./, '').replace(/\/+$/, ''))
      .filter(Boolean);

    const urlHostAllowed = (url, allowedDomains) => {
      if (!allowedDomains.length) return true;
      let host;
      try { host = new URL(url).hostname.toLowerCase().replace(/^www\./, ''); }
      catch (e) { return false; }
      return allowedDomains.some(d => host === d || host.endsWith('.' + d));
    };

    const fields = {};
    const processedNames = new Set();

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
          const section = field.closest('.section-wrapper');
          if (section && section.style.display === 'none') {
            return true;
          }

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
        const allowedDomains = parseAllowedDomains(field.dataset.allowedDomains);
        validators.callback_url = {
          message: t.url,
          callback: function () {
            const value = (field.value || '').trim();
            if (value === '') return true;
            if (!/^(https?:\/\/).+/i.test(value)) {
              return { valid: false, message: t.url };
            }
            if (!urlHostAllowed(value, allowedDomains)) {
              return { valid: false, message: t.allowed_domains + allowedDomains.join(', ') };
            }
            return true;
          }
        };
      }

      if (qtype === 'file') {
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

    const pluginConfig = {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        rowSelector: '.application-question-card',
        eleInvalidClass: 'is-invalid',
        eleValidClass: ''
      }),
      autoFocus: new FormValidation.plugins.AutoFocus()
    };

    const fv = FormValidation.formValidation(form, {
      fields,
      plugins: pluginConfig,
      init: instance => {
        window._formValidation = fv;
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

    // The `callback_*` validator names used in the `fields` map above are not
    // part of the popular bundle. FormValidation silently treats an unregistered
    // validator name as always-valid (it short-circuits to `Valid`), so the URL,
    // numeric, time and file rules never actually ran. Register them as aliases
    // of the built-in `callback` validator so they execute.
    ['callback_numeric', 'callback_time', 'callback_url', 'callback_file_type', 'callback_file_size'].forEach(function (name) {
      fv.registerValidator(name, function () {
        return {
          validate: function (input) {
            const response = typeof input.options.callback === 'function'
              ? input.options.callback(input)
              : true;
            return typeof response === 'boolean' ? { valid: response } : response;
          },
        };
      });
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

  document.addEventListener("DOMContentLoaded", function () {
    const sections = document.querySelectorAll(".section-wrapper");
    const submitStep = document.getElementById("submit-step");
    const logicInputs = document.querySelectorAll(".question-logic");
    const nextBtns = document.querySelectorAll(".btn-next");
    const prevBtns = document.querySelectorAll(".btn-prev");
    const sectionHistory = [0];

    // A stored target is only meaningful if it is "submit" or "section-<n>".
    // Older saves wrote an empty string when the builder lost a target
    // (GV-8351), and anything unrecognised must fall through to "continue"
    // rather than be treated as a jump.
    function normalizeLogic(value) {
      const logic = (value || "").trim();
      if (logic === "submit") return "submit";
      if (/^section-\d+$/.test(logic)) return logic;
      return "continue";
    }

    function getNextLogic(section) {
      let logic = "continue";
      const inputs = section.querySelectorAll(".question-logic");

      inputs.forEach(input => {
        let questionLogic = "continue";
        if (input.tagName === "SELECT") {
          const selectedOption = input.options[input.selectedIndex];
          questionLogic = selectedOption ? normalizeLogic(selectedOption.dataset.logic) : "continue";
        } else if (input.type === "radio" && input.checked) {
          questionLogic = normalizeLogic(input.dataset.logic);
        } else if (input.type === "checkbox" && input.checked) {
          const jumpLogic = normalizeLogic(input.dataset.logic);
          if (jumpLogic !== "continue") questionLogic = jumpLogic;
        }
        if (questionLogic !== "continue") {
          logic = questionLogic;
        }
      });

      if (logic === "continue") {
        logic = normalizeLogic(section.dataset.sectionLogic);
      }

      return logic;
    }

    function updateNavButton(section) {
      const nextBtn = section.querySelector(".btn-next");
      if (!nextBtn) return;

      const logic = getNextLogic(section);
      const textSpan = nextBtn.querySelector(".btn-next-text");
      const iconSpan = nextBtn.querySelector(".btn-next-icon");

      if (nextBtn.dataset.action === "submitting") return;

      if (logic === "submit") {
        if (textSpan) textSpan.textContent = "{{ $l == 'en' ? 'Submit' : 'إرسال' }}";
        if (iconSpan) iconSpan.className = "ti ti-send ms-2 btn-next-icon";
        nextBtn.dataset.action = "submit";
      } else {
        if (textSpan) textSpan.textContent = "{{ $l == 'en' ? 'Next' : 'التالي' }}";
        if (iconSpan) iconSpan.className = "ti ti-arrow-right ms-2 btn-next-icon";
        nextBtn.dataset.action = "next";
      }
    }

    function showSection(index) {
      sections.forEach(s => { s.style.display = "none"; });
      if (submitStep) submitStep.style.display = "none";
 
      if (index === "submit" || index < 0 || index >= sections.length) {
        if (submitStep) {
          submitStep.style.display = "block";
          submitStep.scrollIntoView({ behavior: "smooth", block: "start" });
        }
        return;
      }
 
      const section = sections[index];
      section.style.display = "block";
      updateNavButton(section);
 
      const prevBtn = section.querySelector(".btn-prev");
      if (prevBtn) {
        prevBtn.disabled = (sectionHistory.length <= 1);
        prevBtn.classList.remove("d-none");
      }
 
      section.scrollIntoView({ behavior: "smooth", block: "start" });
    }
 
    function doNavigate(currentSection) {
      const logic = getNextLogic(currentSection);
      const currentIndex = parseInt(currentSection.dataset.sectionId);
 
      let next;
      if (logic === "submit") {
        next = "submit";
      } else if (logic.startsWith("section-")) {
        var targetId = parseInt(logic.replace("section-", ""));
        if (!sectionHistory.includes(targetId) && targetId > currentIndex && sections[targetId]) {
          next = targetId;
        } else if (sections[currentIndex + 1]) {
          next = currentIndex + 1;
        } else {
          next = "submit";
        }
      } else {
        next = sections[currentIndex + 1] ? currentIndex + 1 : "submit";
      }
 
      if (next === "submit") {
        sections.forEach(function (s) { s.style.display = "none"; });
        if (submitStep) {
          submitStep.style.display = "block";
          submitStep.scrollIntoView({ behavior: "smooth", block: "start" });
        }
        return;
      }
 
      if (!sectionHistory.includes(next)) {
        sectionHistory.push(next);
      }
 
      showSection(next);
    }
 
    function navigateNext(currentSection) {
      const fv = window._formValidation;
      if (fv) {
        // Validate only fields in the current section
        const fieldsInSection = currentSection.querySelectorAll('input[name], select[name], textarea[name]');
        const promises = [];
        fieldsInSection.forEach(field => {
          if (field.name && fv.getFields()[field.name]) {
            promises.push(fv.validateField(field.name));
          }
        });
        
        Promise.all(promises).then(results => {
          if (!results.includes('Invalid')) {
            doNavigate(currentSection);
          } else {
            const firstError = currentSection.querySelector(".is-invalid");
            if (firstError) firstError.focus();
          }
        });
      } else {
        doNavigate(currentSection);
      }
    }
 
    function navigatePrev(currentSection) {
      if (sectionHistory.length <= 1) return;
      sectionHistory.pop();
      var prev = sectionHistory[sectionHistory.length - 1];
      showSection(prev);
    }

    // Toggle Other Fields helper logic
    function toggleOtherField(input) {
      let show = false;
      if (input.tagName === 'SELECT' || input.type === 'select-one') {
        show = input.value === '__OTHER__';
      } else if (input.type === 'radio') {
        const checked = document.querySelector(`input[name="${CSS.escape(input.name)}"]:checked`);
        show = checked && checked.value === '__OTHER__';
      } else if (input.type === 'checkbox') {
        const checkboxes = document.querySelectorAll(`input[name="${CSS.escape(input.name)}"]`);
        show = Array.from(checkboxes).some(cb => cb.checked && cb.value === '__OTHER__');
      }
      const card = input.closest('.application-field-control') || input.closest('.card-body');
      const wrapper = input.type === 'select-one' || input.tagName === 'SELECT'
        ? document.getElementById(input.id + '_other_wrapper')
        : (card ? card.querySelector('[id$="_other_wrapper"]') : null);
      if (wrapper) {
        wrapper.classList.toggle('d-none', !show);
        if (!show) {
          const otherInput = wrapper.querySelector('.other-field');
          if (otherInput) otherInput.value = '';
        }
      }
    }

    // Initialize toggleOtherField on all question-logic inputs
    document.querySelectorAll('.question-logic').forEach(input => {
      if (input.tagName === 'SELECT' || ['select-one', 'radio', 'checkbox'].includes(input.type)) {
        toggleOtherField(input);
      }
    });

    // Toggle Other field text box when options change
    document.addEventListener('change', function (e) {
      if (e.target.matches('.question-logic')) {
        if (e.target.type === 'radio') {
          document.querySelectorAll(`input[name="${CSS.escape(e.target.name)}"]`).forEach(r => toggleOtherField(r));
        } else {
          toggleOtherField(e.target);
        }
      }
    });
 
    // Initialize: show first section
    if (sections.length > 0) {
      showSection(0);
    }
 
    function hasConfiguredLogic(input) {
      if (input.tagName === "SELECT") {
        return Array.from(input.options).some(function (opt) {
          return opt.dataset.logic && opt.dataset.logic !== "continue";
        });
      }
      if (input.type === "radio" || input.type === "checkbox") {
        var group = document.querySelectorAll('[name="' + input.name.replace(/"/g, '\\"') + '"]');
        return Array.from(group).some(function (el) {
          return el.dataset.logic && el.dataset.logic !== "continue";
        });
      }
      return false;
    }
 
    // Auto-navigate when a conditional-logic input changes
    logicInputs.forEach(function (input) {
      input.addEventListener("change", function () {
        var section = this.closest(".section-wrapper");
        if (!section || section.style.display === "none") return;
 
        updateNavButton(section);
 
        if (hasConfiguredLogic(this)) {
          doNavigate(section);
        }
      });
    });
 
    // Handle next button clicks
    nextBtns.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var section = this.closest(".section-wrapper");
        if (section) navigateNext(section);
      });
    });
 
    // Handle previous button clicks
    prevBtns.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var section = this.closest(".section-wrapper");
        if (section) navigatePrev(section);
      });
    });

    // Handle submit step Back button click
    const prevSubmitBtn = document.querySelector(".btn-prev-submit");
    if (prevSubmitBtn) {
      prevSubmitBtn.addEventListener("click", function () {
        if (sectionHistory.length > 0) {
          const prev = sectionHistory[sectionHistory.length - 1];
          showSection(prev);
        }
      });
    }

    // Intercept form submit to validate active path only and set correct active field_ids
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      const fv = window._formValidation;
      if (fv) {
        const promises = [];
        sectionHistory.forEach(secIdx => {
          const section = sections[secIdx];
          if (section) {
            const fields = section.querySelectorAll('input[name], select[name], textarea[name]');
            fields.forEach(field => {
              if (field.name && fv.getFields()[field.name]) {
                promises.push(fv.validateField(field.name));
              }
            });
          }
        });
        
        Promise.all(promises).then(results => {
          if (!results.includes('Invalid')) {
            // Update field_ids hidden input to only contain fields from active sections
            const activeFieldIds = [];
            sectionHistory.forEach(secIdx => {
              const section = sections[secIdx];
              if (section) {
                section.querySelectorAll('.application-question-card').forEach(qCard => {
                  const input = qCard.querySelector('input[name], select[name], textarea[name]');
                  if (input) {
                    const match = input.name.match(/field_(\d+)/);
                    if (match) {
                      activeFieldIds.push(match[1]);
                    }
                  }
                });
              }
            });
            const fieldIdsInput = document.querySelector('input[name="field_ids"]');
            if (fieldIdsInput) {
              fieldIdsInput.value = activeFieldIds.join(',');
            }
            form.submit();
          } else {
            // Navigate to first section containing error
            for (let i = 0; i < sectionHistory.length; i++) {
              const secIdx = sectionHistory[i];
              const section = sections[secIdx];
              const firstError = section.querySelector('.is-invalid');
              if (firstError) {
                showSection(secIdx);
                setTimeout(() => { firstError.focus(); }, 100);
                break;
              }
            }
          }
        });
      } else {
        form.submit();
      }
    });
  });
</script>
@stop
