@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/summernote/summernote-bs5.min.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/dropzone/dropzone.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/pickr/pickr-themes.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
@stop

@section('content')
@php
  $orgs = $_user->hasRole('Admin') ? \App\Organization::all() : $_user->organizations;

  // Keep these in sync with ProgramController (DESCRIPTION_MAX) and app-edit-program.js
  $descMax = 100;

  $isHackathon = $ccycle->type === 'Hackathon';
  $dateRange = ($ccycle->start && $ccycle->end)
    ? $ccycle->start->format('Y-m-d') . ' to ' . $ccycle->end->format('Y-m-d')
    : '';
  $reminderValues = $ccycle->mentorshipSchedules
    ? array_map('intval', $ccycle->mentorshipSchedules->pluck('value')->all())
    : [];
  $pages = $ccycle->custom_pages ? $ccycle->custom_pages->sortBy('row_no') : collect();
  if ($pages->isEmpty()) {
    $pages = collect([null]); // one blank row so the repeater always has a template
  }
  $pageVisibilityMap = ['private' => 'participants', 'admins' => 'admins-only'];
  $logoUrl = $ccycle->img_url ? Storage::disk('public_uploads')->url($ccycle->img_url) : '';

  $wizardSteps = [
['step-general', 'ti-building',  'General Info',            'Organization & basics'],
    ['step-schedule', 'ti-calendar', 'Schedule & Media',        'Duration, logo & run type'],
    ['step-mentorship', 'ti-users',  'Mentorship & Visibility', 'Approve, reminders & access'],
    ['step-landing',    'ti-palette',   'Landing & Branding',     'Offline session & colors'],
    ['step-pages',      'ti-files',     'Custom Pages',            'Pages, visibility & review'],
  ];

  // Previous / Next (or Submit on the last step) footer of every step
  $navButtons = function (bool $first = false, bool $last = false) {
    $prev = e(__('cycles.Previous'));
    $next = e(__($last ? 'cycles.Submit' : 'cycles.Next'));
    $prevDisabled = $first ? ' disabled' : '';
    $nextClass = $last ? 'btn-success btn-submit' : 'btn-primary';
    $nextIcon = $last ? 'ti-check' : 'ti-arrow-right';
    return <<<HTML
<div class="col-12 d-flex justify-content-between">
  <button class="btn btn-label-secondary btn-prev" type="button"{$prevDisabled}>
    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
    <span class="align-middle d-sm-inline-block d-none">{$prev}</span>
  </button>
  <button class="btn {$nextClass} btn-next" type="button">
    <span class="align-middle d-sm-inline-block d-none me-sm-2">{$next}</span>
    <i class="ti {$nextIcon} ti-xs"></i>
  </button>
</div>
HTML;
  };

  // Toggle switch (id === name)
  $switchField = function (string $id, string $labelKey, ?string $helpKey = null, bool $checked = false, bool $required = false) {
    $star = $required ? '<span class="text-danger">*</span>' : '';
    $help = $helpKey ? '<small class="text-body d-block mt-1">' . e(__($helpKey)) . '</small>' : '';
    return '<div class="col-12"><div class="form-check form-switch my-1 ms-2">'
      . '<input class="form-check-input" type="checkbox" id="' . $id . '" name="' . $id . '"' . ($checked ? ' checked' : '') . ' />'
      . '<label class="switch-label" for="' . $id . '">' . e(__($labelKey)) . $star . '</label>' . $help
      . '</div></div>';
  };
@endphp

<!-- Program Edit header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-1">{{ __('cycles.Edit Program') }}</h4>
    <p class="mb-0 text-body">{{ __('cycles.Set up program details, schedule, mentoring, branding and custom pages.') }}</p>
  </div>
  <div class="d-flex align-content-center flex-wrap gap-4">
    <a href="{{ url('/dashboard/programlist') }}" class="btn btn-label-secondary">{{ __('cycles.Discard') }}</a>
    <button type="button" class="btn btn-primary" id="btnPublishProgram">{{ __('cycles.Publish Program') }}</button>
  </div>
</div>

<!-- Program Wizard -->
<div id="wizard-program" class="bs-stepper vertical mt-2"
  data-program-id="{{ $ccycle->id }}"
  data-base-url="{{ url('/dashboard/programs') }}"
  data-list-url="{{ url('/dashboard/programlist') }}">
  <div class="bs-stepper-header border-end">
    @foreach ($wizardSteps as [$sid, $icon, $title, $subtitle])
      @if (!$loop->first)<div class="line"></div>@endif
      <div class="step" data-target="#{{ $sid }}" data-url="{{ Str::after($sid, 'step-') }}">
        <button type="button" class="step-trigger">
          <span class="bs-stepper-circle"><i class="ti {{ $icon }} ti-sm"></i></span>
          <span class="bs-stepper-label">
            <span class="bs-stepper-title">{{ __('cycles.' . $title) }}</span>
            <span class="bs-stepper-subtitle">{{ __('cycles.' . $subtitle) }}</span>
          </span>
        </button>
      </div>
    @endforeach
  </div>

  <div class="bs-stepper-content">
    <form id="wizard-program-form" onSubmit="return false" novalidate>

      <!-- General Info -->
      <div id="step-general" class="content">
        <div class="content-header mb-4">
          <h6 class="mb-0">{{ __('cycles.General Information') }}</h6>
          <small>{{ __('cycles.Tell people which organization the program belongs to and its basic identity.') }}</small>
        </div>
        <div class="row g-6">
          <div class="col-sm-6">
            <label class="form-label" for="programOrganization">{{ __('cycles.Organization') }}<span class="text-danger">*</span></label>
            <select class="select2 form-select" id="programOrganization" name="programOrganization">
              <option label=" "></option>
              @foreach ($orgs as $org)
                <option value="{{ $org->id }}" {{ (int) $ccycle->organization_id === (int) $org->id ? 'selected' : '' }}>{{ $org->{'name_' . $l} ?? $org->name_en }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-6">
            <label class="form-label" for="programType">{{ __('cycles.Program Type') }}<span class="text-danger">*</span></label>
            <select class="select2 form-select" id="programType" name="programType">
              <option label=" "></option>
              <option value="hackathon" {{ $isHackathon ? 'selected' : '' }}>{{ __('cycles.Hackathon') }}</option>
              <option value="general" {{ !$isHackathon ? 'selected' : '' }}>{{ __('cycles.General') }}</option>
            </select>
          </div>
          <div class="col-sm-6">
            <label class="form-label" for="programTitle">{{ __('cycles.Program Title') }}<span class="text-danger">*</span></label>
            <input type="text" id="programTitle" name="programTitle" class="form-control" maxlength="50"
              placeholder="{{ __('cycles.Program Title placeholder') }}" value="{{ $ccycle->title }}" />
          </div>
          <div class="col-sm-6">
            <label class="form-label" for="programTitleAr">{{ __('cycles.Program Title (Arabic)') }}<span class="text-danger">*</span></label>
            <input type="text" id="programTitleAr" name="programTitleAr" class="form-control" dir="rtl" maxlength="50"
              placeholder="{{ __('cycles.Program Title (Arabic) placeholder') }}" value="{{ $ccycle->title_ar }}" />
          </div>
          <div class="col-sm-6">
            <label class="form-label" for="programCohort">{{ __('cycles.Cohort') }}</label>
            <input type="text" class="form-control" id="programCohort" name="programCohort" maxlength="20"
              placeholder="{{ __('cycles.Cohort placeholder') }}" value="{{ $ccycle->cohort }}" />
          </div>
          <div class="col-12">
            <label class="form-label" for="programDescription">{{ __('cycles.Description') }}<span class="text-danger">*</span></label>
            <textarea class="form-control" id="programDescription" name="programDescription" rows="5" maxlength="{{ $descMax }}"
              placeholder="{{ __('cycles.Describe the program') }}">{{ $ccycle->description }}</textarea>
          </div>
          <div class="col-12">
            <label class="form-label" for="programDescriptionAr">{{ __('cycles.Description (Arabic)') }}<span class="text-danger">*</span></label>
            <textarea class="form-control" id="programDescriptionAr" name="programDescriptionAr" rows="4" dir="rtl" maxlength="{{ $descMax }}"
              placeholder="{{ __('cycles.Description (Arabic) placeholder') }}">{{ $ccycle->description_ar }}</textarea>
          </div>
          {!! $navButtons(true) !!}
        </div>
      </div>

      <!-- Schedule & Media -->
      <div id="step-schedule" class="content">
        <div class="content-header mb-4">
          <h6 class="mb-0">{{ __('cycles.Schedule & Media') }}</h6>
          <small>{{ __('cycles.Set the program duration, upload a logo and choose how it runs.') }}</small>
        </div>
        <div class="row g-6">
          <div class="col-12">
            <label class="form-label" for="programDateRange">{{ __('cycles.Program Duration') }}<span class="text-danger">*</span></label>
            <input type="text" id="programDateRange" name="programDateRange" class="form-control" autocomplete="off"
              placeholder="{{ __('cycles.Program Duration placeholder') }}" value="{{ $dateRange }}" />
          </div>
          <div class="col-12">
            <label class="form-label d-block">{{ __('cycles.Run Type') }}<span class="text-danger">*</span></label>
            <div class="row g-6">
              @foreach ([['step', 'cycle', 'programStepTypeStep', 'ti-list-check', 'type_by_cycle'], ['time', 'time', 'programStepTypeTime', 'ti-clock', 'type_by_time']] as [$val, $dbVal, $rid, $ricon, $rkey])
                <div class="col-12 col-sm-6 mb-0">
                  <div class="form-check custom-option custom-option-icon mb-0">
                    <label class="form-check-label custom-option-content" for="{{ $rid }}">
                      <span class="custom-option-body">
                        <span class="custom-option-icon"><i class="ti {{ $ricon }} ti-2x"></i></span>
                        <span class="custom-option-title">{{ __('cycles.' . $rkey) }}</span>
                        <small>{{ __('cycles.' . $rkey . '_helper') }}</small>
                      </span>
                      <input name="programStepType" class="form-check-input" type="radio" value="{{ $val }}" id="{{ $rid }}"
                        {{ $ccycle->step_type === $dbVal ? 'checked' : '' }} />
                    </label>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
          <div class="col-12">
            <label class="form-label d-block">{{ __('cycles.Program Logo') }}</label>
            <div class="dropzone needsclick p-0" id="dropzone-program-logo">
              <div class="dz-message needsclick py-4">
                <p class="h4 needsclick pt-3 mb-2">{{ __('cycles.Drag and drop your logo here') }}</p>
                <p class="h6 text-muted d-block fw-normal mb-2">{{ __('cycles.or') }}</p>
                <span class="note needsclick btn btn-sm btn-label-primary" id="btnBrowseLogo">{{ __('cycles.Browse image') }}</span>
              </div>
              <div class="fallback"><input name="file" type="file" /></div>
            </div>
            <div id="programLogoError" class="invalid-feedback d-none"></div>
            <div class="mt-3 {{ $logoUrl ? '' : 'd-none' }}" id="currentLogoWrap">
              <small class="text-body d-block mb-2">{{ __('cycles.Current logo') }}</small>
              <img id="currentLogoImg" src="{{ $logoUrl }}" class="rounded img-thumbnail" style="max-height: 90px; max-width: 180px;" alt="{{ __('cycles.Current logo') }}" />
              <small class="text-body d-block mt-2">{{ __('cycles.Uploading a new image will replace the current logo.') }}</small>
            </div>
          </div>
          {!! $switchField('programNonCompanyParticipation', 'cycles.External_Participation_Label', 'cycles.External_Participation_Helper_Text', (bool) $ccycle->allow_non_company) !!}
          {!! $switchField('programPrivateLink', 'cycles.Enable private link', 'cycles.Only people holding the private link can apply to the program.', !empty($ccycle->private)) !!}
          {!! $navButtons() !!}
        </div>
      </div>

      <!-- Mentorship Settings -->
      <div id="step-mentorship" class="content">
        <div class="content-header mb-4">
          <h6 class="mb-0">{{ __('cycles.Mentorship & Visibility') }}</h6>
          <small>{{ __('cycles.Configure mentoring workflows and decide how the program is accessed.') }}</small>
        </div>
        <div class="row g-6">
          {!! $switchField('programAutoApproveMentorship', 'cycles.Auto_Approve_Mentorship_Requests', 'cycles.Auto_Approve_Helper_Text', (bool) $ccycle->auto_approve_mentorship_requests) !!}
          {!! $switchField('programMentorshipReminders', 'cycles.Enable_Mentorship_Reminders', 'cycles.Send periodic reminder notifications to mentors and mentees.', (bool) $ccycle->is_active_Rminder) !!}
          <div class="col-12{{ $ccycle->is_active_Rminder ? '' : ' d-none' }}" id="mentorshipReminderTimes">
            <small class="text-body d-block ms-2 mb-2">{{ __('cycles.Mentorship_Reminders_Help_Text') }}</small>
            <div class="row g-6 ms-2">
              {{-- name ends with [] so PHP receives ALL ticked values, not just the last one --}}
              @foreach ([24 => '24_hours_before', 6 => '6_hours_before', 1 => '1_hour_before'] as $hrs => $key)
                <div class="col-12 col-sm-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="programReminder{{ $hrs }}h"
                      name="programMentorshipReminderTimes[]" value="{{ $hrs }}"
                      {{ in_array($hrs, $reminderValues, true) ? 'checked' : '' }} />
                    <label class="form-check-label" for="programReminder{{ $hrs }}h">{{ __('cycles.' . $key) }}</label>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
          <div class="col-12"><div class="divider"><div class="divider-text">{{ __('cycles.Offline Session Location') }}</div></div></div>
          <div class="col-sm-6">
            <label class="form-label" for="programAddress">{{ __('cycles.Address') }}</label>
            <input type="text" id="programAddress" name="programAddress" class="form-control" maxlength="250"
              placeholder="{{ __('cycles.Street, City, Country') }}" value="{{ $ccycle->offline_location_address }}" />
          </div>
          <div class="col-sm-6">
            <label class="form-label" for="programGoogleMapsUrl">{{ __('cycles.Google Maps URL') }}</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="ti ti-map-pin"></i></span>
              <input type="text" id="programGoogleMapsUrl" name="programGoogleMapsUrl" class="form-control" maxlength="250"
                placeholder="{{ __('cycles.https://maps.google.com/?q=...') }}" value="{{ $ccycle->offline_location_map }}" />
            </div>
          </div>
          {!! $navButtons() !!}
        </div>
      </div>

      <!-- Landing & Branding -->
      <div id="step-landing" class="content">
        <div class="content-header mb-4">
          <h6 class="mb-0">{{ __('cycles.Landing & Branding') }}</h6>
          <small>{{ __('cycles.Add the offline session location and customize the program branding colors.') }}</small>
        </div>
        <div class="row g-6">
          <div class="col-sm-6">
            <label class="form-label" for="programVisibility">{{ __('cycles.Program Visibility') }}<span class="text-danger">*</span></label>
            <select class="select2 form-select" id="programVisibility" name="programVisibility">
              <option label=" "></option>
              <option value="public" {{ $ccycle->is_public ? 'selected' : '' }}>{{ __('cycles.Public') }}</option>
              <option value="private" {{ !$ccycle->is_public ? 'selected' : '' }}>{{ __('cycles.Private') }}</option>
            </select>
            <small class="text-body d-block mt-1">
              <strong>{{ __('cycles.Public') }}</strong> — {{ __('cycles.Program_Visibility_Public_Helper') }}<br>
              <strong>{{ __('cycles.Private') }}</strong> — {{ __('cycles.Program_Visibility_Private_Helper') }}
            </small>
          </div>
          <div class="col-sm-6 program-public-url-wrap">
            <label class="form-label" for="programPublicUrl">{{ __('cycles.Public URL') }}<span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="ti ti-link"></i></span>
              <input type="text" id="programPublicUrl" name="programPublicUrl" class="form-control" maxlength="250"
                placeholder="{{ __('cycles.Public URL input placeholder') }}" value="{{ $ccycle->public_slug }}" />
            </div>
            <small class="text-body d-block mt-1">{{ __('cycles.Public URL placeholder') }}</small>
          </div>
          <div class="col-12"><div class="divider"><div class="divider-text">{{ __('cycles.Branding_Colors') }}</div></div></div>
          @foreach ([['programColorGradientStart', 'cycles.Gradient_Start_Color', $ccycle->start_color], ['programColorGradientEnd', 'cycles.Gradient_End_Color', $ccycle->end_color], ['programColorMain', 'cycles.Main_Color', $ccycle->main_color]] as [$cid, $ckey, $cval])
            <div class="col-md-4 col-sm-6">
              <label class="form-label d-block">{{ __($ckey) }}</label>
              <div id="{{ $cid }}"></div>
              <input type="hidden" id="{{ $cid }}Val" name="{{ $cid }}Val" value="{{ $cval }}" />
            </div>
          @endforeach
          <div class="col-12">
            <button type="button" class="btn btn-label-secondary btn-sm" id="btnResetBrandingColors"
              data-defaults='@json(['start' => $_programBrandDefaults['start_color'], 'end' => $_programBrandDefaults['end_color'], 'main' => $_programBrandDefaults['main_color']])'>
              <i class="ti ti-refresh ti-14px me-1"></i>{{ __('cycles.Reset_To_Default') }}
            </button>
          </div>
          {!! $navButtons() !!}
        </div>
      </div>

      <!-- Custom Pages & Review -->
      <div id="step-pages" class="content">
        <div class="content-header mb-4">
          <h6 class="mb-0">{{ __('cycles.Custom Pages & Review') }}</h6>
          <small>{{ __('cycles.Manage custom pages for the program and confirm before publishing.') }}</small>
        </div>
        <div class="row g-6">
          <div class="col-12">
            <label class="form-label d-block">{{ __('cycles.Custom Pages') }}</label>
            <small class="text-body d-block mb-3">{{ __('cycles.Add pages such as Terms, Curriculum or FAQ. Each page has its own visibility and rich description.') }}</small>
            <div id="programPagesRepeater" class="form-repeater">
              <div data-repeater-list="programPages">
                @foreach ($pages as $page)
                  @php $pageVis = $pageVisibilityMap[$page->visibility ?? ''] ?? 'public'; @endphp
                  <div data-repeater-item>
                    <div class="card border shadow-none mb-3">
                      <div class="card-body">
                        <div class="row g-6">
                          <div class="col-sm-4">
                            <label class="form-label">{{ __('cycles.Page Name') }}</label>
                            <input type="text" class="form-control" name="programPageName" maxlength="150"
                              placeholder="{{ __('cycles.Page Name placeholder') }}" value="{{ $page->title ?? '' }}" />
                          </div>
                          <div class="col-sm-4">
                            <label class="form-label">{{ __('cycles.Page Visibility') }}</label>
                            <select class="form-select" name="programPageVisibility">
                              <option value="public" {{ $pageVis === 'public' ? 'selected' : '' }}>{{ __('cycles.Public') }}</option>
                              <option value="participants" {{ $pageVis === 'participants' ? 'selected' : '' }}>{{ __('cycles.Participants') }}</option>
                              <option value="admins-only" {{ $pageVis === 'admins-only' ? 'selected' : '' }}>{{ __('cycles.Admins only') }}</option>
                            </select>
                          </div>
                          <div class="col-sm-4">
                            <label class="form-label d-block">{{ __('cycles.Status') }}</label>
                            <div class="form-check form-switch my-2">
                              <input class="form-check-input page-status" type="checkbox" name="programPageStatus" value="1"
                                {{ !empty($page->is_active) ? 'checked' : '' }} />
                              <label class="form-check-label page-status-label">{{ !empty($page->is_active) ? __('cycles.Active') : __('cycles.Inactive') }}</label>
                            </div>
                          </div>
                          <div class="col-12">
                            <label class="form-label d-block">{{ __('cycles.Description') }}</label>
                            <textarea class="form-control summernote" name="programPageDescription" rows="5">{{ $page->content ?? '' }}</textarea>
                          </div>
                          <div class="col-12 d-flex justify-content-between align-items-center">
                            <span class="pages-drag-handle text-body d-inline-flex align-items-center gap-1"
                              style="cursor: grab" title="{{ __('cycles.Drag to reorder') }}">
                              <i class="ti ti-grip-vertical ti-lg"></i>{{ __('cycles.Drag to reorder') }}
                            </span>
                            <button type="button" class="btn btn-label-danger btn-sm" data-repeater-delete>
                              <i class="ti ti-trash ti-14px me-1"></i>{{ __('cycles.Remove page') }}
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
              <div class="mt-2">
                <button type="button" class="btn btn-label-primary btn-sm" data-repeater-create>
                  <i class="ti ti-plus ti-14px me-1"></i>{{ __('cycles.Add page') }}
                </button>
              </div>
            </div>
          </div>
          {!! $switchField('programConfirmed', 'cycles.I have reviewed all the program details and I am ready to publish.', null, false, true) !!}
          {!! $navButtons(false, true) !!}
        </div>
      </div>

    </form>
  </div>
</div>
@stop

@section('scripts')
<script>
  window.PROGRAM_FORM_LANG = @json(trans('cycles'));
</script>
<script src="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
<script src="/vuexy/assets/vendor/libs/summernote/summernote-bs5.min.js"></script>
<script src="/vuexy/assets/vendor/libs/dropzone/dropzone.js"></script>
<script src="/vuexy/assets/vendor/libs/jquery-repeater/jquery-repeater.js"></script>
<script src="/vuexy/assets/vendor/libs/sortablejs/sortable.js"></script>
<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
<script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
@stop

@section('pagescripts')
<script src="/vuexy/assets/js/app-edit-program.js?v={{ @filemtime(public_path('vuexy/assets/js/app-edit-program.js')) }}"></script>
@stop
