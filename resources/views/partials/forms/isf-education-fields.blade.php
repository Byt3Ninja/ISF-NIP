{{--
  GV-10436 / GV-10440 (ISF): Education Status -> Student Code | National ID -> Entity (-> referral).
  Shared by the Founder Profile and the program landing-page application; the dependent behaviour
  lives in public/vuexy/assets/js/isf-education-fields.js, the server rules in EducationEntity.

  $educationFields   EducationEntity::formConfig()
  $values            ['academic_stat', 'education_entity_id', 'national_id', 'student_code', 'referral_source']
  $wrapperClass      column classes for every field
  $labelClass        label classes
  $selectClass       select classes ("select2 form-select" on the profile)
  $entityRequiredKey lang key of the "Entity is missing" message (the two tickets word it differently)
  $withReferral      show "How did you hear about the program?"
  $referralRequired  ... and make it mandatory
  $withFeedback      render .invalid-feedback holders (the landing page's validator fills them)
--}}
@php
  $v = $values ?? [];
  $status = $v['academic_stat'] ?? null;
  $statusValid = in_array($status, $educationFields['statuses'], true);
  $idField = $statusValid ? $educationFields['identification'][$status] : null;
  $entityOptions = $statusValid ? ($educationFields['entities'][$status] ?? []) : [];
  $withFeedback = $withFeedback ?? false;
  $referralRequired = $referralRequired ?? false;
  $required = '<span class="text-danger ms-1">*</span>';
@endphp

<div class="{{ $wrapperClass }}">
  <label for="academic_stat" class="{{ $labelClass }}">{{ __('admin.Education_Status') }}{!! $required !!}</label>
  <select id="academic_stat" name="academic_stat" class="{{ $selectClass }}" required data-isf-education-status
    data-identification="{{ json_encode($educationFields['identification']) }}"
    data-required-message="{{ __('admin.education_status_required_msg') }}">
    <option value="">{{ __('admin.Select_Education_Status') }}</option>
    @foreach ($educationFields['statuses'] as $option)
      <option value="{{ $option }}" @selected($status === $option)>{{ __('admin.education_status_' . $option) }}</option>
    @endforeach
  </select>
  @if ($withFeedback)<div class="invalid-feedback"></div>@endif
</div>

<div class="{{ $wrapperClass }}">
  <label for="education_entity_id" class="{{ $labelClass }}">{{ __('admin.Entity') }}{!! $required !!}</label>
  <select id="education_entity_id" name="education_entity_id" class="{{ $selectClass }}" required data-isf-education-entity
    data-options="{{ json_encode($educationFields['entities']) }}"
    data-placeholder="{{ __('admin.Select_Entity') }}"
    data-placeholder-disabled="{{ __('admin.Select_Education_Status_First') }}"
    data-required-message="{{ __($entityRequiredKey) }}" @disabled(!$statusValid)>
    <option value="">{{ $statusValid ? __('admin.Select_Entity') : __('admin.Select_Education_Status_First') }}</option>
    @foreach ($entityOptions as $entity)
      <option value="{{ $entity['id'] }}" @selected((string) ($v['education_entity_id'] ?? '') === (string) $entity['id'])>{{ $entity['name'] }}</option>
    @endforeach
  </select>
  @if ($withFeedback)<div class="invalid-feedback"></div>@endif
</div>

{{-- Only the field the chosen status asks for is shown and posted; the other stays disabled. --}}
<div class="{{ $wrapperClass }} {{ $idField === 'national_id' ? '' : 'd-none' }}" data-isf-id-field="national_id">
  <label for="national_id" class="{{ $labelClass }}">{{ __('admin.national_id') }}{!! $required !!}</label>
  <input type="text" id="national_id" name="national_id" class="form-control" value="{{ $v['national_id'] ?? '' }}"
    inputmode="numeric" maxlength="14" autocomplete="off" placeholder="{{ __('admin.national_id') }}" required
    data-pattern="^\d{14}$" data-pattern-message="{{ __('admin.national_id_14_digits_msg') }}"
    data-required-message="{{ __('admin.national_id_enter_msg') }}"
    oninput="this.value = this.value.replace(/\D/g, '')" @disabled($idField !== 'national_id') />
  @if ($withFeedback)<div class="invalid-feedback"></div>@endif
</div>

<div class="{{ $wrapperClass }} {{ $idField === 'student_code' ? '' : 'd-none' }}" data-isf-id-field="student_code">
  <label for="student_code" class="{{ $labelClass }}">{{ __('admin.Student_Code') }}{!! $required !!}</label>
  <input type="text" id="student_code" name="student_code" class="form-control" value="{{ $v['student_code'] ?? '' }}"
    maxlength="50" autocomplete="off" placeholder="{{ __('admin.Student_Code') }}" required
    data-pattern="^[\p{L}\p{M}\p{N}_-]+$" data-pattern-message="{{ __('admin.student_code_invalid_msg') }}"
    data-required-message="{{ __('admin.student_code_required_msg') }}"
    oninput="this.value = this.value.trim()" @disabled($idField !== 'student_code') />
  @if ($withFeedback)<div class="invalid-feedback"></div>@endif
</div>

@if ($withReferral ?? false)
  <div class="{{ $wrapperClass }}">
    <label for="referral_source" class="{{ $labelClass }}">{{ __('admin.referral_source') }}@if ($referralRequired){!! $required !!}@endif</label>
    <select id="referral_source" name="referral_source" class="{{ $selectClass }}" @required($referralRequired)
      data-required-message="{{ __('admin.referral_source_required_msg') }}">
      <option value="">{{ __('admin.Select_Referral_Source') }}</option>
      @foreach ($educationFields['referral_sources'] as $source)
        <option value="{{ $source }}" @selected(($v['referral_source'] ?? null) === $source)>{{ __('admin.referral_source_' . $source) }}</option>
      @endforeach
    </select>
    @if ($withFeedback)<div class="invalid-feedback"></div>@endif
  </div>
@endif
