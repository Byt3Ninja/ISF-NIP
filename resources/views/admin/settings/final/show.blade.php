@extends('layouts.admin')

@section('vendorstyle')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/form-validation/form-validation.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
<style>
   /* Edit-step picker is positioned via JS using viewport-fixed coords so the
      month/year selectors stay fully visible inside the offcanvas (GV-3736). */
  .daterangepicker.drp-viewport-fixed {
    margin-top: 0 !important;
    position: fixed !important;
    z-index: 1080;
  }

  .note-modal {
    margin-top: 100px !important;
  }

  div.checkbox label {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
  }

  div.note-modal-footer input[type="button"] {
    margin-top: -20px !important;
  }
  
/* Editor inside the modal-lg */
#guidelines-modal .note-editor.note-frame { margin-bottom: 0; }
#guidelines-modal .note-editor .note-editing-area .note-editable { height: 60vh !important; }
</style>
@stop

@php
$status = strtolower($evaluationForm?->status);
$l = app()->getLocale();
$maxVisible = 10;

$pills = [
  ['title' => 'judging_panels', 'icon' => 'user-check'],
  ['title' => 'screening_guidelines', 'icon' => 'help-circle'],
  ['title' => 'evaluation_model', 'icon' => 'chart-dots'],
];

$companiesById = $companies instanceof \Illuminate\Support\Collection ? $companies->keyBy('id') : collect($companies)->keyBy('id');

$visibleJudgeIds = $assignedJudgeIds->take($maxVisible);
$hiddenJudgeIds = $assignedJudgeIds->slice($maxVisible);
$hiddenJudgeNames = $hiddenJudgeIds->map(fn($jid) => $judges->find($jid))
  ->filter()
  ->map(fn($j) => $j->{'first_name_' . $l} . ' ' . $j->{'last_name_' . $l})
  ->implode("\n");

$visibleCompanyIds = $assignedCompanyIds->take($maxVisible);
$hiddenCompanyIds = $assignedCompanyIds->slice($maxVisible);
$hiddenCompanyNames = $hiddenCompanyIds->map(fn($cid) => $companiesById[$cid] ?? null)
  ->filter()
  ->map(fn($c) => $c->{'name_' . $l})
  ->implode("\n");
@endphp

@section('content')
<div class="">
  <div class="">
    <!-- Alerts -->
    @if (session()->has('message'))
      <x-alert type="success" message="{{ __('admin.' . session()->get('message')) }} {{ Session::forget('message') }}" />
    @endif
    @if (session()->has('message2'))
      <x-alert type="danger" message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" />
    @endif
    <!--/ Alerts -->

    @if ($cstep)
       <!-- Screening Step Details Card -->
      <div class="card mb-6">
        <div class="card-body">
          <div class="d-flex flex-wrap justify-content-between align-items-lg-center">
            <div class="d-flex align-items-center gap-2 mb-4 mb-lg-0">
              <!-- Back To List -->
              <a 
                class="btn btn-icon btn-sm btn-text-secondary waves-effect waves-light rounded-pill" 
                href="/dashboard/final"
              >
                <i class="ti ti-chevron-left ti-lg"></i>
              </a>
              <h5 class="card-title fw-semibold mb-0">{{ $cstep->title }}</h5>
            </div>
            <div class="d-flex flex-wrap align-items-md-center gap-3">
              <a class="btn btn-outline-primary" href="/dashboard/final/results/{{ $id }}">
                <i class="ti ti-file-text me-2"></i> {{ __('admin.view_results') }}
              </a>
              <a class="btn btn-label-primary" href="/dashboard/cycles/{{ $cstep->cycle_id }}">
                <i class="ti ti-adjustments me-2"></i> {{ __('cycles.Manage Cycle') }}
              </a>
              <a class="btn btn-primary" href="/dashboard/show-builder{{ $cstep->cycle->organization->id ? '?' . 'org_id=' . $cstep->cycle->organization->id : '' }}">
                <i class="ti ti-plus me-2"></i> {{ __('builder.Add_New_Form') }}
              </a>
              <div class="dropdown">
                <button class="btn bg-card border shadow-xs rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="ti ti-dots-vertical"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <button class="dropdown-item edit-step-btn" data-step='@json($cstep)'>
                    <i class="ti ti-pencil me-1"></i> {{ __('admin.edit_step') }}
                  </button>
                  <div class="dropdown-divider"></div>
                  <button class="dropdown-item text-danger" onclick="handleDeleteStep({{ $cstep->id }})">
                    <i class="ti ti-trash me-1"></i> {{ __('admin.delete') }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <div class="w-75">
            <p class="text-muted">{{ $cstep->description }}</p>
          </div>

          <div class="row g-4">
            <!-- Program / Organization -->
            <div class="col-md-6">
              <div class="mb-3 px-3 py-2 bg-card rounded-2 border border-primary">
                <a href="{{ route('cycles.list', $cstep->cycle->id) }}" class="d-flex align-items-start gap-3">
                  <div class="avatar">
                    <div class="avatar-initial rounded-circle bg-label-primary">
                      <i class="ti ti-rocket"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <div class="text-secondary small mb-1">{{ __('admin.program') }}</div>
                    <div class="fw-semibold text-primary">{{ $cstep->cycle->title }}</div>
                  </div>
                </a>
              </div>

              <div class="mb-3 px-3 py-4 bg-card rounded-2 border border-success">
                <a href="{{ $_user->ableToAny(['organization.wizard.edit', 'organizations.edit'], $permissions) ? route('organization.wizard.edit', $cstep->cycle->organization->id) . '?step=1' : 'javascript:void(0);' }}" class="d-flex align-items-start gap-3">
                  <div class="avatar">
                    <div class="avatar-initial rounded-circle bg-label-success">
                      <i class="ti ti-building"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <div class="text-secondary small mb-1">{{ __('admin.Organization') }}</div>
                    <div class="text-success fw-semibold mb-1">{{ optional($cstep->cycle->organization)->name ?? '-' }}</div>
                  </div>
                </a>
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3 px-3 py-2 bg-card rounded-2 border border-info">
                <div class="d-flex align-items-start gap-3">
                  <div class="avatar">
                    <div class="avatar-initial rounded-circle bg-label-info">
                      <i class="ti ti-calendar"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <div class="text-secondary small mb-1">{{ __('admin.start_date') }} - {{ __('admin.end_date') }}</div>
                    <div class="text-info fw-semibold">
                      {{ $cstep->from?->format('d-m-Y') ?? '-' }} - {{ $cstep->to?->format('d-m-Y') ?? '-' }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-3 px-3 py-2 bg-card rounded-2 border border-warning">
                <div class="d-flex align-items-start gap-3">
                  <div class="avatar">
                    <div class="avatar-initial rounded-circle bg-label-warning">
                      <i class="ti ti-file"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <div class="text-secondary small mb-1">{{ __('builder.Form') }}</div>
                      <select id="step-form-select" class="form-select select2" data-step-id="{{ $cstep->id }}" data-placeholder="{{ __('admin.select_form') }}" data-allow-clear="true">
                      <option value="">{{ __('admin.select_form') }}</option>
                      @foreach($forms as $f)
                        <option value="{{ $f->id }}" @selected($form && $form->id == $f->id)>
                          {{ $f->form_name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Judges -->
            <div class="col-lg-6 d-flex flex-column">
              <div class="border-start border-dark border-4 ps-3 mb-3">
                <h6 class="text-dark fw-bold small mb-0">
                  <i class="ti ti-user-check me-2"></i>{{ __('cycles.Assigned Judges') }}
                </h6>
              </div>
              <div class="screening-assignees p-3 bg-light-subtle rounded-2 border flex-grow-1">
                <div id="assigned-judges" class="d-flex flex-wrap gap-2">
                  @forelse ($visibleJudgeIds as $judgeId)
                    @php $judge = $judges->find($judgeId); @endphp

                    @if ($judge)
                      <a
                        href="/dashboard/users/{{ $judge->id }}/edit"
                        class="badge text-dark bg-card border px-3 py-1_5 rounded-pill d-flex align-items-center mw-100"
                      >
                        <div class="avatar avatar-xs flex-shrink-0 me-2">
                          @if ($judge->img_url)
                            <img src="/191014/{{ $judge->img_url }}" alt="img" class="rounded-circle object-fit-cover">
                          @else
                            <div class="avatar-initial rounded-circle bg-label-secondary">
                              <i class="ti ti-user ti-xs"></i>
                            </div>
                          @endif
                        </div>
                        @php $judgeName = $judge->{'first_name_' . $l} . ' ' . $judge->{'last_name_' . $l}; @endphp
                        <span class="text-truncate" title="{{ $judgeName }}">{{ $judgeName }}</span>
                      </a>
                    @endif
                  @empty
                    <span class="badge text-dark bg-card border px-3 py-2 rounded-pill">
                      <i class="ti ti-mood-puzzled me-1"></i>
                      {{ __('admin.no_judges_assigned') }}
                    </span>
                  @endforelse

                  @if ($hiddenJudgeIds->count() > 0)
                    <span
                      class="badge badge-center bg-label-primary p-4 rounded-circle fw-semibold d-flex justify-content-center align-items-center mt-1"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      data-bs-custom-class="tooltip-wide"
                      title="{{ $hiddenJudgeNames }}"
                    >+{{ $hiddenJudgeIds->count() }}</span>
                  @endif
                </div>
              </div>
            </div>

            <!-- Companies -->
            <div class="col-lg-6 d-flex flex-column">
              <div class="border-start border-dark border-4 ps-3 mb-3">
                <h6 class="text-dark fw-bold small mb-0">
                  <i class="ti ti-building-skyscraper me-2"></i>{{ __('admin.assigned_startups') }}
                </h6>
              </div>
              <div class="screening-assignees p-3 bg-light-subtle rounded-2 border flex-grow-1">
                <div id="assigned-companies" class="d-flex flex-wrap gap-2">
                  @forelse ($visibleCompanyIds as $companyId)
                    @php $company = $companiesById[$companyId] ?? null; @endphp

                    @if ($company)
                      <a
                        href="/dashboard/companyProfile/{{ $company->id }}"
                        class="badge text-dark bg-card border px-3 py-1_5 rounded-pill d-flex align-items-center mw-100"
                      >
                        <div class="avatar avatar-xs flex-shrink-0 me-2">
                          @if ($company->logo)
                            <img src="{{ $company->logo }}" alt="logo" class="rounded-circle object-fit-cover">
                          @else
                            <div class="avatar-initial rounded-circle bg-label-secondary">
                              <i class="ti ti-building-skyscraper ti-xs"></i>
                            </div>
                          @endif
                        </div>
                        <span class="text-truncate" title="{{ $company->{'name_' . $l} }}">{{ $company->{'name_' . $l} }}</span>
                      </a>
                    @endif
                  @empty
                    <span class="badge text-dark bg-card border px-3 py-2 rounded-pill">
                      <i class="ti ti-mood-puzzled me-1"></i>
                      {{ __('admin.no_startups_assigned') }}
                    </span>
                  @endforelse

                  @if ($hiddenCompanyIds->count() > 0)
                    <span
                      class="badge bg-label-primary px-3 py-2 rounded-pill fw-semibold"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      data-bs-custom-class="tooltip-wide"
                      title="{{ $hiddenCompanyNames }}"
                    >+{{ $hiddenCompanyIds->count() }}</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ Screening Step Details Card -->

      <!-- Navigation Pills -->
      <nav class="nav-align-top mb-6">
        <ul class="nav nav-pills flex-column flex-md-row gap-2 gap-md-0" role="tablist">
          @foreach($pills as $key => $pill)
            <li class="nav-item">
              <a
                class="nav-link {{ $key == 0 ? 'active' : '' }}"
                id="{{ $pill['title'] }}-tab"
                data-bs-toggle="tab"
                href="#{{ $pill['title'] }}"
                role="tab"
                aria-controls="{{ $pill['title'] }}"
                aria-selected="{{ $key == 0 ? 'true' : 'false' }}"
              >
                <i class="ti-sm ti ti-{{ $pill['icon'] }} me-1_5"></i>
                {{ __('admin.' . $pill['title']) }}
              </a>
            </li>
          @endforeach
        </ul>
      </nav>
      <!--/ Navigation Pills -->

      <!-- Tab Content -->
      <div class="tab-content bg-card shadow p-4 p-sm-6">
        <!-- Judging Panels Tab -->
        <div class="tab-pane fade show active" id="judging_panels" role="tabpanel" aria-labelledby="judging_panels-tab">
          @include('partials.judging-panels', ['id' => $id, 'screening_type' => 'final'])
        </div>
        <!--/ Judging Panels Tab -->

        <!-- Screening Guidelines Tab -->
        <div class="tab-pane fade" id="screening_guidelines" role="tabpanel" aria-labelledby="screening_guidelines-tab">
          <div class="d-flex flex-column">
            <h5 class="mb-1">{{ __('admin.screening_guidelines') }}</h5>
            <p class="mb-6 text-muted">{{ __('admin.screening_guidelines_description') }}</p>
            <form id="screening-guidelines-form" action="{{ route('screening.evaluation_save_draft') }}" method="POST" enctype="multipart/form-data">
              {!! csrf_field() !!}
              <input type="hidden" name="action" value="draft">
              <input type="hidden" name="stepId" value="{{ $cstep->id }}">
              <textarea
                class="form-control"
                id="screening-guidelines-editor"
                name="screening_guidelines"
                rows="5">
                {!! $evaluationForm->screening_guidelines ?? '' !!}
              </textarea>
              <div class="row mt-4">
                <div class="col-12 d-flex justify-content-end">
                  <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('admin.Save') }}</button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <!--/ Screening Guidelines Tab -->

        <!-- Evaluation Model Tab -->
        <div class="tab-pane fade" id="evaluation_model" role="tabpanel" aria-labelledby="evaluation_model_tab">
          @if($_user->ableTo('screening.evaluation_show', $permissions))
            @include('admin.settings.screening-evaluation')
          @endif
        </div>
        <!--/ Evaluation Model Tab -->
      </div>
      <!--/ Tab Content -->
    @endif
  </div>
</div>

@if ($cstep)
<!-- Step Form Offcanvas -->
<div class="offcanvas offcanvas-end w-px-600" tabindex="-1" id="step-form-modal" aria-labelledby="myModalLabel33">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title mb-0" id="myModalLabel33">{{ __('admin.screening.modify_step') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <form id="stepForm" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data"
    class="d-flex flex-column flex-grow-1 overflow-hidden">
    <input type="hidden" name="_token" value="">
    <div class="offcanvas-body">
      <input type="hidden" name="sid" id="step-id-value">
      <input type="hidden" name="type" value="{{ \App\Enums\CycleModifyAction::UPDATE_STEP }}">
      <input type="hidden" name="confirm_form_change" id="confirm_form_change" value="0">
      <div class="form-group mb-3">
        <label class="form-label" for="mFormSelect">{{ __('cycles.Choose form') }}:</label>
        <select class="form-select select2" id="mFormSelect" name="mFormSelect">
          <option value="" @selected(!$form)>{{ __('admin.no_form_option') }}</option>
          @foreach($forms as $f)
            <option value="{{ $f->id }}" @selected($form && $form->id == $f->id)>{{ $f->form_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group mb-3">
        <label class="form-label">{{ __('cycles.Title') }}</label>
        <input name="title" id="step-title" type="text" placeholder="{{ __('cycles.Title') }}"
          class="form-control" required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label" for="step-range-picker">{{ __('admin.start_date') }} - {{ __('admin.end_date') }}:</label>
        <input type="hidden" name="fromDate" id="mfromDate">
        <input type="hidden" name="toDate" id="mtoDate">
        <input type="text" id="step-range-picker" class="form-control" name="date_range">
      </div>
      <div class="form-group mb-3">
        <label class="form-label">{{ __('cycles.Description') }}</label>
        <textarea id="step-description" name="description" placeholder="{{ __('cycles.Description') }}"
          class="form-control" style="min-height: 80px; resize: vertical;"></textarea>
      </div>
    </div>
    <div class="offcanvas-footer border-top p-3 d-flex justify-content-end gap-2">
      <button type="submit" value="submit" class="btn btn-primary">
        <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
      </button>
      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">
        <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
      </button>
    </div>
  </form>
</div>
<!--/ Step Form Offcanvas -->

@if ($cstep->data == 'grouptogroup')
<!-- Users form -->
<div class="modal fade text-left" id="usersForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form class="form" action="{{ URL::to('dashboard/final/add') }}" method="POST" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="modal-body">
          <div class="form-group mb-0">
            <select class="select2 form-control" name="panelusers[]" id="panelusers" multiple></select>
          </div>
          <input type="hidden" name="sid" value="{{ $cstep->id }}">
          <input type="hidden" name="pid" value="0" id="pid">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
          </button>
          <button type="submit" value="submit" class="btn btn-primary ml-1">
            <span class="d-none d-sm-block">{{ __('cycles.Save') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endif

<!-- Guidelines Editor Modal -->
<div class="modal fade" id="guidelines-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title mb-6">{{ __('admin.screening_guidelines') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0" id="guidelines-modal-body"></div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
@if(app()->getLocale() == 'ar')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-ar-AR.min.js"></script>
@endif
<script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
    <script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
@stop

@section('pagescripts')
  @include('admin.settings.screening-guidelines-scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const $editorTextarea = $('#screening-guidelines-editor');
      
      // Get initial content before initializing Summernote
      const initialContent = $('#screening-guidelines-editor').val() || '';

      // Initialize Summernote editor
      const locale = '{{ app()->getLocale() }}';
      
       // Custom "expand" button that replaces the built-in fullscreen
      const ExpandButton = function (context) {
        const ui = $.summernote.ui;
        return ui.button({
          contents: '<i class="ti ti-arrows-maximize"></i>',
          tooltip: '{{ __('admin.fullscreen') }}',
          click: function () {
            guidelinesModal.show();
          }
        }).render();
      };
      
      $editorTextarea.summernote({
        lang: locale === 'ar' ? 'ar-AR' : 'en-US',
        placeholder: '{{ __('admin.screening_guidelines_placeholder') }}',
        tabsize: 2,
        height: 200,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture', 'video']],
          ['view', ['expand', 'codeview', 'help']]   // 'fullscreen' swapped for 'expand'
        ],
        buttons: { expand: ExpandButton },
        dialogsInBody: true,
        callbacks: {
          onImageUpload: function(files) {
            uploadScreeningGuidelinesImage(files[0], $editorTextarea);
          }
        }
      });
      
      // Move the editor into the modal and back
      const guidelinesModalEl = document.getElementById('guidelines-modal');
      // focus:false so Bootstrap's focus trap doesn't block Summernote's link/picture dialogs
      const guidelinesModal = new bootstrap.Modal(guidelinesModalEl, { focus: false });

      guidelinesModalEl.addEventListener('show.bs.modal', function () {
        $('#guidelines-modal-body').append($editorTextarea.next('.note-editor.note-frame'));
      });

      guidelinesModalEl.addEventListener('hidden.bs.modal', function () {
        // the editor now lives in the modal body, so find it there and put it back after the textarea
        $editorTextarea.after($('#guidelines-modal-body > .note-editor.note-frame'));
      });

      // Set the initial content after Summernote is initialized
      if (initialContent.trim() !== '') {
        setTimeout(() => {
          $('#screening-guidelines-editor').summernote('code', initialContent);
        }, 100);
      }

    });

    $('.edit-step-btn').on('click', function () {
      const stepData = $(this).data('step');

      if (stepData) {
        $('#stepForm input[name="_token"]').val($('meta[name="csrf-token"]').attr('content'));
        $('#step-form-modal').offcanvas('show');
        $('#step-id-value').val(stepData.id);
        $('#step-title').val(stepData.title);
        $('#step-description').val(stepData.description);

        // Preselect the current form only when the step's data column holds a form id.
        // Legacy steps may have a stale/empty data column, so leave the server default in place.
        if (stepData.data) {
          $('#mFormSelect').val(stepData.data).trigger('change.select2');
        }

        // Seed hidden date inputs (correct mapping: from -> start, to -> end)
        $('#mfromDate').val(stepData.from || moment().format('YYYY-MM-DD'));
        $('#mtoDate').val(stepData.to || moment().format('YYYY-MM-DD'));

        // Combined range picker, mirroring the Steps Page dialog. The picker is
        // positioned via JS (viewport-fixed) instead of drops:'up', which clipped
        // it inside the offcanvas and hid the month/year selectors (GV-3736).
        const $stepRangeInput = $('#step-range-picker');
        $stepRangeInput.daterangepicker({
          opens: $('html').attr('dir') === 'rtl' ? 'left' : 'right',
          startDate: stepData.from ? moment(stepData.from) : moment(),
          endDate: stepData.to ? moment(stepData.to) : moment(),
        }).on('apply.daterangepicker', function(ev, picker) {
          $('#mfromDate').val(picker.startDate.format('YYYY-MM-DD'));
          $('#mtoDate').val(picker.endDate.format('YYYY-MM-DD'));
        });

        positionStepRangePicker($stepRangeInput);
      }
    });

    // Viewport-fixed positioning now lives in /js/daterangepicker-viewport.js
    // (loaded globally by the admin layout) so every modal picker behaves the same.
    function positionStepRangePicker($input) {
      const drp = $input.data('daterangepicker');
      if (!drp) return;
      const $picker = drp.container;
      $picker.addClass('drp-viewport-fixed');

      const reposition = function () {
        if (!drp.isShowing) return;
        const inputRect = $input[0].getBoundingClientRect();
        const pickerHeight = $picker.outerHeight();
        const pickerWidth = $picker.outerWidth();
        const viewportH = window.innerHeight;
        const viewportW = window.innerWidth;
        const gap = 4;

        let top = inputRect.bottom + gap;
        if (top + pickerHeight > viewportH - gap) {
          const above = inputRect.top - pickerHeight - gap;
          top = above >= gap ? above : Math.max(gap, viewportH - pickerHeight - gap);
        }

        let left;
        if (drp.opens === 'left') {
          left = inputRect.right - pickerWidth;
        } else if (drp.opens === 'center') {
          left = inputRect.left + inputRect.width / 2 - pickerWidth / 2;
        } else {
          left = inputRect.left;
        }
        left = Math.max(gap, Math.min(left, viewportW - pickerWidth - gap));

        $picker.css({ top: top + 'px', left: left + 'px', right: 'auto', bottom: 'auto' });
      };

      $input.off('show.daterangepicker.viewport').on('show.daterangepicker.viewport', function () {
        setTimeout(reposition, 0);
      });
      const $modal = $('#step-form-modal');
      $modal.off('scroll.drpViewport').on('scroll.drpViewport', reposition);
      $modal.find('.offcanvas-body').off('scroll.drpViewport').on('scroll.drpViewport', reposition);
      $(window).off('scroll.drpViewport resize.drpViewport').on('scroll.drpViewport resize.drpViewport', reposition);

      if (drp.isShowing) reposition();
    }

    function handleDeleteStep(stepId) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      releaseAlert('warning', {
        title: "{{ __('cycles.Are you sure, Delete current step and all its contents?') }}",
        message: "{{ __('admin.Recover') }}",
        confirmText: "{{ __('admin.DYes') }}",
        cancelText: "{{ __('admin.No') }}",
        confirmColor: 'danger'
      }).then((ok) => {
        if (ok) {
          $.ajax({
            type: "POST",
            url: "/dashboard/cycles/modify",
            data: {
              "stepid": stepId,
              "type": "{{ \App\Enums\CycleModifyAction::DELETE_STEP }}",
            },
            success: function(response) {
              $('[data-step-id="' + stepId + '"]').closest('.col-xl-4').remove();
              releaseAlert('success', {
                title: '{{__('Deleted!')}}',
                message: '{{  $l == 'en' ? 'The screening step has been deleted successfully.' : 'تم حذف مرحلة التصفية بنجاح.' }}',
              });
              window.location.href = "/dashboard/final";
            },
            error: function(error) {
              releaseAlert('danger', {
                title: "{{ __('admin.Error') }}!",
                message: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
              });
            }
          });
        }
      });
    }

    function openusers(panel) {
      $('#pid').val(panel);
      $('#cpanel').text(panel);
      let theurl = "/dashboard/final/get/{{ optional($form)->id ?? 0 }}/{{ optional($cstep)->id ?? 0 }}/" + panel;
      $.ajax({
        type: "GET",
        url: theurl,
        success: function(response) {
          console.log(response);
          $('#panelusers').html(response);
          $('#usersForm').modal('show');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }

    $(document).ready(function() {
      // Initialize select2 for all selects
      $('.select2').each(function() {
        const $select = $(this);
        let placeHolder = '';
        if( $select.hasClass('judgers-selectors') ) { placeHolder = "{{ __('cycles.Select judges') }}"; }
        else if( $select.hasClass('users-selectors') ) { placeHolder = "{{ __('cycles.Select Company') }}"; }

        $select.select2({
          placeholder: placeHolder,
          width: '100%'
        });
      });


      // Handle screening guidelines form submission with AJAX
      $('#screening-guidelines-form').on('submit', function(e) {
        e.preventDefault();

        // Sync Summernote content to textarea
        $('#screening-guidelines-editor').val($('#screening-guidelines-editor').summernote('code'));

        const formData = new FormData(this);

        $.ajax({
          url: $(this).attr('action'),
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {
           if (response.status) {
              releaseAlert('success', {
                title: '{{ __('admin.success') }}',
                message: response.message ? response.message : 'admin.Screening guidelines saved successfully',
              });
            } else {
              releaseAlert('danger', {
                title: '{{ __('admin.Error') }}',
                message: response.message ? response.message : '{{ __('admin.unexpected_error') }}',
              });
            }
          },
          error: function(xhr) {
            releaseAlert('danger', {
              title: '{{ __('admin.Error') }}',
              message: screeningGuidelinesSaveErrorMessage(xhr),
            });
          }
        });
      });
      // Form selection change handler. The previous value is tracked so we can
      // revert the select2 if the manager cancels a destructive change.
      let lastFormSelectValue = $('#step-form-select').val();
      $('#step-form-select').on('change', function() {
        const $select = $(this);
        const stepId = $select.data('step-id');
        const formId = $select.val();

        const sendUpdate = function(confirmed) {
          $.ajax({
            url: '{{ route("screening.update_step_form") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { step_id: stepId, form_id: formId || null, confirm: confirmed ? 1 : 0 },
            success: function(response) {
              if (response.success) {
                lastFormSelectValue = formId;
                releaseAlert('success', { title: '{{ __("admin.success") }}', message: response.message });
              } else if (response.needs_confirmation) {
                const count = response.counts ? response.counts.total : 0;
                releaseAlert('warning', {
                  title: '{{ __("admin.step_form_change_warning_title") }}',
                  message: '{{ __("admin.step_form_change_confirm") }}'.replace(':count', count),
                  confirmText: '{{ __("admin.Yes") }}',
                  cancelText: '{{ __("admin.cancel") }}',
                  confirmColor: 'danger',
                  onConfirm: function() { sendUpdate(true); },
                  onCancel: function() {
                    $select.val(lastFormSelectValue).trigger('change.select2');
                  }
                });
              }
            },
            error: function(xhr) {
              $select.val(lastFormSelectValue).trigger('change.select2');
              releaseAlert('danger', { title: '{{ __("admin.Error") }}', message: xhr.responseJSON?.message || '{{ __("admin.unexpected_error") }}' });
            }
          });
        };

        sendUpdate(false);
      });

      // NOTE: select2 allow-clear (the x) already fires a native `change` after
      // emptying the element (AllowClear._handleClear), which drives the change
      // handler above with an empty value — so clearing routes through the same
      // needs_confirmation warning path with no extra binding. A manual
      // select2:clear → trigger('change') here would double-fire the AJAX.

      // Edit-step popup form change: warn before de-attaching the form (changed or
      // removed) when the step has submissions for the old form. Same rule as the
      // inline selector, but this dialog submits the full #stepForm to cycles/modify
      // (which otherwise redirects back with a flash). $stepFormSubmissions is the
      // founder form_data count for this step (server-embedded).
      let stepFormOriginalValue = null;
      $('#step-form-modal').on('shown.bs.offcanvas', function () {
        stepFormOriginalValue = $('#mFormSelect').val();
        $('#confirm_form_change').val('0');
      });
      $('#stepForm').on('submit', function (e) {
        const current = $('#mFormSelect').val();
        const changed = stepFormOriginalValue !== null && current !== stepFormOriginalValue;
        const submissions = Number('{{ $stepFormSubmissions ?? 0 }}') || 0;
        if (changed && submissions > 0 && $('#confirm_form_change').val() !== '1') {
          e.preventDefault();
          releaseAlert('warning', {
            title: '{{ __("admin.step_form_change_warning_title") }}',
            message: '{{ __("admin.step_form_change_confirm") }}'.replace(':count', submissions),
            confirmText: '{{ __("admin.Yes") }}',
            cancelText: '{{ __("admin.cancel") }}',
            confirmColor: 'danger',
            onConfirm: function () { $('#confirm_form_change').val('1'); $('#stepForm')[0].submit(); }
          });
          return false;
        }
      });

      // Initialize tooltips
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    });
  </script>
@stop
