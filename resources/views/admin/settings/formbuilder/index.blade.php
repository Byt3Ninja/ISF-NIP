@extends('layouts.admin')

@section('title', __('admin.Builder'))
@section('subTitle', __('admin.Edit form'))

@section('style')
<style>
  /* ── Section chrome ── */
  .section-wrapper {
    position: relative;
  }
  .section-questions {
    border-left: 3px solid var(--bs-primary);
    min-height: 48px; /* ensures empty sections can be drop targets */
    transition: background-color 0.2s ease;
  }

  /* ── Drag states ── */
  .dragging {
    opacity: 0.4;
    box-shadow: 0 8px 24px rgba(0,0,0,.18) !important;
  }
  .drag-handle {
    cursor: grab;
    touch-action: none;
  }
  .drag-handle:active {
    cursor: grabbing;
  }

  /* Drop-placeholder that shows insertion point */
  .drop-placeholder {
    border: 2px dashed var(--bs-primary);
    border-radius: .375rem;
    background: rgba(var(--bs-primary-rgb), .06);
    pointer-events: none;
    transition: height .15s ease;
  }

  /* Item cards: smooth lift on hover */
  .item-card {
    transition: box-shadow .15s ease, transform .15s ease;
  }
  .item-card:not(.dragging):hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.09);
  }

  /* Section questions zone highlight while something hovers over it */
  .section-questions.drag-active {
    background: rgba(var(--bs-primary-rgb), .04);
    border-radius: .375rem;
  }
</style>
@stop

@php
  $user = Auth::user();
  if ($user->hasRole('Admin')) {
    $organizations   = App\Organization::all();
    $allBuilderForms = App\BuilderForm::with('organization', 'cycles')->get();
  } elseif ($user->hasRole('organizationManager') || $user->isSubAdmin()) {
    $orgIds          = $user->organizations_ids;
    $organizations   = App\Organization::whereIn('id', $orgIds)->get();
    $allBuilderForms = App\BuilderForm::whereIn('organization_id', $orgIds)->with('organization', 'cycles')->get();
  } else {
    $organizations   = collect();
    $allBuilderForms = collect();
  }

  $mode        = !request()->segment(3) ? 'ADD' : 'EDIT';
  $orgIdParam  = request()->get('org_id') ?? $title->organization_id ?? null;
@endphp

@section('content')
<div class="">
  <div class="">

    {{-- ── Card header ── --}}
    <div class="card mb-4">
      <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div class="mb-4 mb-md-0">
          <h5 class="card-title mb-0">{{ __('builder.Form_Builder') }}</h5>
          <div class="text-muted">{{ __('builder.Build_custom_forms') }}</div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-outline-primary" id="new-form-btn">
            <i class="ti ti-plus"></i>
            <span class="ms-2">{{ __('builder.New_Form') }}</span>
          </button>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="ti ti-switch-horizontal"></i>
            <span class="ms-2">{{ __('builder.switch_form') }}</span>
          </button>
        </div>
      </div>
    </div>

    {{-- ── Form header card ── --}}
    <div class="card mb-4">
      <div class="bg-primary rounded-top" style="height:8px;"></div>
      <div class="card-body">

        @if($mode === 'EDIT')
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="card-title mb-0">{{ $title->form_name }}</h5>
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="ti ti-dots-vertical"></i>
              <span class="d-none d-md-inline ms-2">{{ __('builder.Actions') }}</span>
            </button>
            <ul class="dropdown-menu">
              <li><button class="dropdown-item d-flex align-items-center gap-2"
                  onclick="showForm(0,{{ $title->id }},'{{ $title->form_name }}',{{ $title->organization_id }})">
                  <i class="ti ti-pencil"></i> {{ __('builder.Edit') }}
              </button></li>
              <li><button class="dropdown-item d-flex align-items-center gap-2"
                  onclick="showForm(1,{{ $title->id }},'{{ $title->form_name }} {{ __("builder.Copy.") }}',{{ $title->organization_id }})">
                  <i class="ti ti-copy"></i> {{ __('builder.Copy') }}
              </button></li>
              <li><button class="dropdown-item d-flex align-items-center gap-2 text-danger"
                  onclick="deleteForm(3,{{ $title->id }},null)">
                  <i class="ti ti-trash"></i> {{ __('builder.Delete') }}
              </button></li>
              <li><a class="dropdown-item d-flex align-items-center gap-2"
                  href="/dashboard/show-form/{{ $title->id }}">
                  <i class="ti ti-eye"></i> {{ __('builder.Show_Saved_Form') }}
              </a></li>
            </ul>
          </div>
        </div>
        @endif

        <div class="form-group">
          <label class="form-label">{{ __('builder.Form_Title') }}</label>
          <input type="text" name="title" id="formTitle" class="form-control"
            placeholder="{{ __('builder.Untitled_form') }}"
            value="{{ old('title', optional($title)->form_name ?? '') }}">
        </div>

        @php
          $authUser    = auth()->user();
          $userOrgIds  = $authUser->organizations_ids;
          $showOrgDrop = $authUser->hasRole('admin') || ($authUser->isSubAdmin() && count($userOrgIds) > 1);
        @endphp

        @if ($showOrgDrop)
        <div class="form-group mt-4">
          <label class="form-label">{{ __('builder.Organization') }}</label>
          <select class="select2 form-control form-org-select" id="organization_id" name="organization_id"
            data-placeholder="{{ __('builder.Select_Organization') }}" required>
            <option value="" disabled {{ !$orgIdParam ? 'selected' : '' }}>
              {{ __('builder.Select_Organization') }}
            </option>
            @if($authUser->hasRole('admin'))
              @foreach (\App\Organization::all() as $org)
              <option value="{{ $org->id }}" {{ $org->id == $orgIdParam ? 'selected' : '' }}>
                {{ $org->name }}
              </option>
              @endforeach
            @else
              @foreach ($authUser->organizations as $org)
              <option value="{{ $org->id }}" {{ $org->id == $orgIdParam ? 'selected' : '' }}>
                {{ $org->name }}
              </option>
              @endforeach
            @endif
          </select>
        </div>
        @else
        <input type="hidden" id="organization_id" name="organization_id"
          value="{{ $userOrgIds[0] ?? '' }}">
        @endif

      </div>
    </div>

    {{-- ── Form builder ── --}}
    <form id="builder-form">
      {{-- ── Items container ── --}}
      <div id="itemsContainer"></div>

      {{-- ── Footer actions ── --}}
      <div class="card card-body">
        <div class="d-flex align-items-center gap-4">
          <button type="button"
            class="btn btn-lg btn-outline-secondary d-flex align-items-center gap-2"
            onclick="addSection()">
            <i class="ti ti-plus"></i>
            {{ __('builder.Add_Section') }}
          </button>
          <button type="button" class="btn btn-lg btn-primary" onclick="saveForm()">
            <span class="text-nowrap">
              {{ $mode === 'EDIT' ? __('builder.Update_Form') : __('builder.Add_Form') }}
            </span>
            <i class="ti ti-check ms-2"></i>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ── Modify-form modal ── --}}
<div class="modal fade" id="AddForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="AddFormTitle">{{ __('builder.Add_New_Form') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        @php
          $authUserM   = auth()->user();
          $userOrgIdsM = $authUserM->organizations_ids;
          $showOrgM    = $authUserM->hasRole('admin') || ($authUserM->isSubAdmin() && count($userOrgIdsM) > 1);
        @endphp
        @if ($showOrgM)
        <div class="mb-4">
          <label class="form-label">{{ __('builder.Select_Organization') }}:</label>
          <select class="select2 form-control" id="modal_organization_id" name="organization_id"
            data-placeholder="{{ __('builder.Select_Organization') }}" required>
            @if($authUserM->hasRole('admin'))
              @foreach (\App\Organization::all() as $org)
              <option value="{{ $org->id }}" {{ $org->id == request('org_id') ? 'selected' : '' }}>
                {{ $org->name }}
              </option>
              @endforeach
            @else
              @foreach ($authUserM->organizations as $org)
              <option value="{{ $org->id }}" {{ $org->id == request('org_id') ? 'selected' : '' }}>
                {{ $org->name }}
              </option>
              @endforeach
            @endif
          </select>
        </div>
        @else
        <input type="hidden" id="modal_organization_id" name="organization_id"
          value="{{ $userOrgIdsM[0] ?? '' }}">
        @endif
        <div class="form-group">
          <label class="form-label">{{ __('builder.Form_Title') }}:</label>
          <input id="mtitle" name="title" type="text"
            placeholder="{{ __('builder.Form_Title') }}" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer mt-4">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          {{ __('cycles.Close') }}
        </button>
        <button type="button" class="btn btn-primary" id="modelSubmit">
          {{ __('cycles.Save') }}
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ── Filter modal ── --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('builder.switch_form') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="switch-form" method="GET" action="/dashboard/show-builder">
          <div id="organizationFilterContainer" class="form-group mb-4">
            <label class="form-label">{{ __('builder.Select_Organization') }}</label>
            <select class="form-select select2" id="organizationFilter" name="org_id"
              data-placeholder="{{ __('builder.Select_Organization') }}">
              <option value="">{{ __('admin.All_Organizations') }}</option>
              @foreach ($organizations as $organization)
              <option value="{{ $organization->id }}"
                {{ $organization->id == request('org_id') ? 'selected' : '' }}>
                {{ $organization->{'name_' . $l} }}
              </option>
              @endforeach
            </select>
            <div class="invalid-feedback" id="organizationFilterError" style="display: none">{{ __('admin.Required_Field') }}</div>
          </div>
          <div class="form-group">
            <label class="form-label">{{ __('builder.Select_Form') }}</label>
            <select class="form-select select2" id="formFilter" name="form_id">
              <option value="">{{ __('builder.All_Forms') }}</option>
            </select>
            <div class="invalid-feedback" id="formFilterError" style="display: none">{{ __('admin.Required_Field') }}</div>
          </div>
          <div class="d-flex justify-content-end align-items-center gap-3 mt-10">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              {{ __('cycles.Close') }}
            </button>
            <button type="submit" class="btn btn-primary">{{ __('builder.switch') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script>
  window.FB_CONFIG = {
    lang: '{{ $l }}',
    isOrganizationManager: @json(auth()->user()->hasRole('organizationManager')),
    mode: @json($mode),
    builderData: @json($builder_data),
    formData: @json($title),
    allForms: @json($allBuilderForms),
    routes: {
      saveForm:         '{{ route("forms.design_save",       ["id" => isset($title->id) ? $title->id : "0"]) }}',
      reorderSections:  '{{ route("forms.reorder_sections",  ["id" => isset($title->id) ? $title->id : "0"]) }}',
      reorderQuestions: '{{ route("forms.reorder_questions", ["id" => isset($title->id) ? $title->id : "0"]) }}'
    },
    translations: {
      /* ── structural ── */
      section:                      '{{ __("builder.Section") }}',
      option:                       '{{ __("builder.Option") }}',
      go_to:                        '{{ __("builder.Go_to") }}',
      if:                           '{{ __("builder.If") }}',
      /* ── section card ── */
      drag_to_reorder:              '{{ __("builder.Drag_to_reorder") }}',
      section_title_optional:       '{{ __("builder.Section_title_optional") }}',
      section_description_optional: '{{ __("builder.Section_description_optional") }}',
      remove_section:               '{{ __("builder.Remove_Section") }}',
      after_this_section:           '{{ __("builder.After_this_section") }}',
      continue_next_section:        '{{ __("builder.Continue_next_section") }}',
      submit_form:                  '{{ __("builder.Submit_form") }}',
      add_question_section:         '{{ __("builder.Add_Question_Section") }}',
      delete_section_questions:     '{{ __("builder.Delete_section_questions") }}',
      /* ── question card ── */
      question:                     '{{ __("builder.Question") }}',
      required:                     '{{ __("builder.Required") }}',
      /* ── question types ── */
      short_answer:                 '{{ __("builder.Short_answer") }}',
      paragraph:                    '{{ __("builder.Paragraph") }}',
      multiple_choice:              '{{ __("builder.Multiple_choice") }}',
      checkboxes:                   '{{ __("builder.Checkboxes") }}',
      dropdown:                     '{{ __("builder.Dropdown") }}',
      linear_scale:                 '{{ __("builder.Linear_scale") }}',
      number:                       '{{ __("builder.Number") }}',
      email:                        '{{ __("builder.Email") }}',
      url:                          '{{ __("builder.URL") }}',
      date:                         '{{ __("builder.Date") }}',
      time:                         '{{ __("builder.Time") }}',
      file_upload:                  '{{ __("builder.File_upload") }}',
      /* ── question option previews ── */
      short_answer_text:            '{{ __("builder.Short_answer_text") }}',
      long_answer_text:             '{{ __("builder.Long_answer_text") }}',
      option_1:                     '{{ __("builder.Option_1") }}',
      option_2:                     '{{ __("builder.Option_2") }}',
      add_option:                   '{{ __("builder.Add_option") }}',
      or:                           '{{ __("builder.or") }}',
      add_other:                    '{{ __("builder.Add_Other") }}',
      other_value:                  '{{ __("builder.Other_value") }}',
      option_1:                     '{{ __("builder.Option_1") }}',
      option_2:                     '{{ __("builder.Option_2") }}',
      /* ── number type ── */
      number_placeholder:           '{{ __("builder.Number_placeholder") }}',
      min_value:                    '{{ __("builder.Min_value") }}',
      min_optional:                 '{{ __("builder.Min_optional") }}',
      max_value:                    '{{ __("builder.Max_value") }}',
      max_optional:                 '{{ __("builder.Max_optional") }}',
      step:                         '{{ __("builder.Step") }}',
      /* ── email type ── */
      email_placeholder:            '{{ __("builder.Email_placeholder") }}',
      require_email_confirmation:   '{{ __("builder.Require_email_confirmation") }}',
      confirm_email_placeholder:    '{{ __("builder.Confirm_email_placeholder") }}',
      /* ── url type ── */
      url_placeholder:              '{{ __("builder.URL_placeholder") }}',
      allowed_domains_optional:     '{{ __("builder.Allowed_domains_optional") }}',
      allowed_domains_placeholder:  '{{ __("builder.Allowed_domains_placeholder") }}',
      allowed_domains_hint:         '{{ __("builder.Allowed_domains_hint") }}',
      /* ── linear scale ── */
      to:                           '{{ __("builder.to") }}',
      label_low:                    '{{ __("builder.Label_low") }}',
      label_high:                   '{{ __("builder.Label_high") }}',
      low:                          '{{ __("builder.Low") }}',
      high:                         '{{ __("builder.High") }}',
      /* ── logic panel ── */
      response_logic:               '{{ __("builder.Response_Logic") }}',
      configure:                    '{{ __("builder.Configure") }}',
      hide:                         '{{ __("builder.Hide") }}',
      /* ── save / alerts ── */
      form_title_required:          '{{ __("builder.Form_title_required") }}',
      organization_required:        '{{ __("builder.Organization_required") }}',
      form_saved_successfully:      '{{ __("builder.Form_saved_successfully") }}',
      form_updated_successfully:    '{{ __("builder.Form_updated_successfully") }}',
      error_saving_form:            '{{ __("builder.Error_saving_form") }}',
      empty_section_error:          '{{ __("builder.empty_section_error") }}',
      error_occurred:               '{{ __("builder.Error_Occurred") }}',
      /* ── modal / delete ── */
      edit_form:                    '{{ __("builder.Edit_Form") }}',
      copy_form:                    '{{ __("builder.Copy_Form") }}',
      add_new_form:                 '{{ __("builder.Add_New_Form") }}',
      are_you_sure:                 '{{ __("Are you sure?") }}',
      deleting_form:                '{{ __("builder.deleting form") }}',
      yes:                          '{{ __("admin.DYes") }}',
      no:                           '{{ __("admin.DNo") }}',
      confirm_switch:               '{{ __("builder.confirm_switch") }}',
      confirm_switch_message:       '{{ __("builder.confirm_switch_message") }}',
      unsaved_changes_msg:          '{{ __("builder.Unsaved_Changes_New_Form") }}',
      discard_create:               '{{ __("builder.Discard_Create") }}',
      cancel:                       '{{ __("builder.Confirm_cancel") }}'
    }
  };
</script>
@stop

@section('pagescripts')
<script src="/vuexy/assets/js/app-form-builder.js"></script>
@stop