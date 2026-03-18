@extends('layouts.admin')

@section('vendorstyle')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
<style>
/* Center Summernote modals */
.note-modal .modal-dialog {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    margin: 0;
}

/* Center buttons in Summernote modals */
.note-modal .modal-footer {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.note-modal .modal-footer .btn {
    min-width: 100px;
}
</style>
@stop

@php
$status = strtolower($evaluationForm?->status);
if( optional($cstep)->data == 'grouptogroup' )
{
  //
}
else
{
  $judgersList = collect( json_decode(optional($cstep)->list) ?? [] );
  $judgersList = $judgersList->map(function($judger) { return intval($judger); });
}
$types = ['onetoall'=>'Assign One to all users','grouptoall'=>'Assign Group to all users','grouptogroup'=>'Assign Group to group of users'];

$pills = [
  ['title' => 'judging_panels', 'icon' => 'user-check'],
  ['title' => 'screening_guidelines' , 'icon' => 'help-circle'],
  ['title' => 'evaluation_model' , 'icon' => 'chart-dots'],
]
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
                <i class="ti ti-components me-2"></i> {{ __('cycles.Manage Cycle') }}
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

              <div class="mb-3 px-3 py-2 bg-card rounded-2 border border-success">
                <a href="{{ route('organizations.edit', $cstep->cycle->organization->id) }}" class="d-flex align-items-start gap-3">
                  <div class="avatar">
                    <div class="avatar-initial rounded-circle bg-label-success">
                      <i class="ti ti-building"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <div class="text-secondary small mb-1">{{ __('admin.Organization') }}</div>
                    <div class="text-success fw-semibold">{{ optional($cstep->cycle->organization)->name ?? '-' }}</div>
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
                    <select id="step-form-select" class="form-select" data-step-id="{{ $cstep->id }}">
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
            <div class="col-lg-6">
              <div class="border-start border-dark border-4 ps-3 mb-3">
                <h6 class="text-dark fw-bold small mb-0">
                  <i class="ti ti-user-check me-2"></i>{{ __('cycles.Assigned Judges') }}
                </h6>
              </div>
              <div class="p-3 bg-light-subtle rounded-2 border">
                <div id="assigned-judges" class="d-flex flex-wrap gap-2">
                  @php
                    $maxVisible = 10;
                    $allJudges = collect($judgers_list ?? [])
                        ->pluck('judgers')
                        ->flatten()
                        ->merge($judgersList ?? [])
                        ->unique()
                        ->filter();
                    $visibleJudges = $allJudges->take($maxVisible);
                    $hiddenJudges = $allJudges->slice($maxVisible);
                  @endphp

                  @forelse ($visibleJudges as $judgeId)
                    @php $judge = $judges->find($judgeId); @endphp

                    @if ($judge)
                      <a
                        href="/dashboard/users/{{ $judge->id }}/edit"
                        class="badge text-dark bg-card border px-3 py-2 rounded-pill d-flex align-items-center"
                      >
                        <div class="avatar avatar-xs me-2">
                          @if ($judge->img_url)
                            <img src="{{ $judge->img_url }}" alt="img" class="rounded-circle object-fit-cover">
                          @else
                            <div class="avatar-initial rounded-circle bg-label-secondary">
                              <i class="ti ti-user ti-xs"></i>
                            </div>
                          @endif
                        </div>
                        {{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }}
                      </a>
                    @endif
                  @empty
                    <span class="badge text-dark bg-card border px-3 py-2 rounded-pill">
                      <i class="ti ti-mood-puzzled me-1"></i>
                      {{ __('admin.no_judges_assigned') }}
                    </span>
                  @endforelse

                  @if ($hiddenJudges->count() > 0)
                    @php
                      $hiddenJudgeNames = $hiddenJudges->map(fn($jid) => $judges->find($jid))
                        ->filter()
                        ->map(fn($j) => $j->{'first_name_'.$l} . ' ' . $j->{'last_name_'.$l})
                        ->implode("\n");
                    @endphp
                    <span
                      class="badge bg-label-primary px-3 py-2 rounded-pill fw-semibold"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      data-bs-custom-class="tooltip-wide"
                      title="{{ $hiddenJudgeNames }}"
                    >+{{ $hiddenJudges->count() }}</span>
                  @endif
                </div>
              </div>
            </div>

            <!-- Companies -->
            <div class="col-lg-6">
              <div class="border-start border-dark border-4 ps-3 mb-3">
                <h6 class="text-dark fw-bold small mb-0">
                  <i class="ti ti-building-skyscraper me-2"></i>{{ __('admin.assigned_startups') }}
                </h6>
              </div>
              <div class="p-3 bg-light-subtle rounded-2 border">
                <div id="assigned-companies" class="d-flex flex-wrap gap-2">
                  @php
                    $allCompanies = collect($judgers_list ?? [])
                        ->pluck('companies')
                        ->flatten()
                        ->merge(
                            \App\Company::whereIn('step', array_filter([$form->id ?? null, $cstep->id ?? null]))
                                ->pluck('id')
                        )
                        ->unique()
                        ->filter();

                    $companiesById = $companies->keyBy('id');
                    $visibleCompanies = $allCompanies->take($maxVisible);
                    $hiddenCompanies = $allCompanies->slice($maxVisible);
                  @endphp

                  @forelse ($visibleCompanies as $companyId)
                    @php $company = $companiesById[$companyId] ?? null; @endphp

                    @if ($company)
                      <a
                        href="/dashboard/companyProfile/{{ $company->id }}"
                        class="badge text-dark bg-card border px-3 py-2 rounded-pill d-flex align-items-center"
                      >
                        <div class="avatar avatar-xs me-2">
                          @if ($company->logo)
                            <img src="{{ $company->logo }}" alt="logo" class="rounded-circle object-fit-cover">
                          @else
                            <div class="avatar-initial rounded-circle bg-label-secondary">
                              <i class="ti ti-building-skyscraper ti-xs"></i>
                            </div>
                          @endif
                        </div>
                        {{ $company->{'name_' . $l} }}
                      </a>
                    @endif
                  @empty
                    <span class="badge text-dark bg-card border px-3 py-2 rounded-pill">
                      <i class="ti ti-mood-puzzled me-1"></i>
                      {{ __('admin.no_startups_assigned') }}
                    </span>
                  @endforelse

                  @if ($hiddenCompanies->count() > 0)
                    @php
                      $hiddenCompanyNames = $hiddenCompanies->map(fn($cid) => $companiesById[$cid] ?? null)
                        ->filter()
                        ->map(fn($c) => $c->{'name_'.$l})
                        ->implode("\n");
                    @endphp
                    <span
                      class="badge bg-label-primary px-3 py-2 rounded-pill fw-semibold"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      data-bs-custom-class="tooltip-wide"
                      title="{{ $hiddenCompanyNames }}"
                    >+{{ $hiddenCompanies->count() }}</span>
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
      <div class="tab-content bg-card shadow">
        <!-- Judging Panels Tab -->
        <div class="tab-pane fade show active" id="judging_panels" role="tabpanel" aria-labelledby="judging_panels-tab">
          @include('partials.judging-panels')
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
          @if( $_user->ableTo('screening.evaluation_show', $permissions) )
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
<!-- Step Form Modal -->
<div class="modal fade" id="step-form-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title mb-0" id="myModalLabel33">{{ __('admin.screening.modify_step') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="stepForm" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="">
        <div class="modal-body">
          <input type="hidden" name="sid" id="step-id-value">
          <input type="hidden" name="type" value="8">
          <div class="form-group mb-3">
            <label class="form-label">{{ __('cycles.Title') }}</label>
            <input name="title" id="step-title" type="text" placeholder="{{ __('cycles.Title') }}"
              class="form-control" required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label">{{ __('admin.start_date') }}</label>
            <input id="step-from-date" name="fromDate" type="text" class="form-control date"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label">{{ __('admin.end_date') }}</label>
            <input id="step-to-date" name="toDate" type="text" class="form-control date"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label">{{ __('cycles.Description') }}</label>
            <textarea id="step-description" name="description" placeholder="{{ __('cycles.Description') }}"
              class="form-control" style="min-height: 80px; resize: vertical;"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
          </button>
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!--/ Step Form Modal -->

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
@stop

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
@if(app()->getLocale() == 'ar')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-ar-AR.min.js"></script>
@endif
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
@stop

@section('pagescripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Get initial content before initializing Summernote
      const initialContent = $('#screening-guidelines-editor').val() || '';

      // Initialize Summernote editor
      const locale = '{{ app()->getLocale() }}';
      
      $('#screening-guidelines-editor').summernote({
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
          ['view', ['fullscreen', 'codeview', 'help']]
        ],
        dialogsInBody: true,
      });

      // Set the initial content after Summernote is initialized
      if (initialContent.trim() !== '') {
        setTimeout(() => {
          $('#screening-guidelines-editor').summernote('code', initialContent);
        }, 100);
      }

      // Initialize date pickers
      $('.date').flatpickr({
        enableTime: false,
        dateFormat: 'Y-m-d'
      });
    });

    $('.edit-step-btn').on('click', function () {
      const stepData = $(this).data('step');

      console.log('stepData', stepData)

      if (stepData) {
        $('#stepForm input[name="_token"]').val($('meta[name="csrf-token"]').attr('content'));
        $('#step-form-modal').modal('show');
        $('#step-id-value').val(stepData.id);
        $('#step-title').val(stepData.title);
        $('#step-from-date').val(stepData.to);
        $('#step-to-date').val(stepData.from);
        $('#step-description').val(stepData.description);

      }
    });

    function handleDeleteStep(stepId) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      Swal.fire({
        title: "{{ __('cycles.Are you sure, Delete current step and all its contents?') }}",
        text: "{{ __('admin.Recover') }}",
        icon: "warning",
        showCancelButton: true,
        customClass: {
          confirmButton: 'btn btn-danger me-2',
          cancelButton: 'btn btn-label-secondary',
        },
        confirmButtonText: "{{ __('admin.DYes') }}",
        cancelButtonText: "{{ __('admin.No') }}",
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            type: "POST",
            url: "/dashboard/cycles/modify",
            data: {
              "stepid": stepId,
              "type": 6,
            },
            success: function(response) {
              $('[data-step-id="' + stepId + '"]').closest('.col-xl-4').remove();
              releaseSuccessAlert({
                title: '{{__('Deleted!')}}',
                message: '{{  $l == 'en' ? 'The screening step has been deleted successfully.' : 'تم حذف مرحلة التصفية بنجاح.' }}',
              });
              window.location.href = "/dashboard/final";
            },
            error: function(error) {
              releaseFailureAlert({
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
              releaseSuccessAlert({
                title: '{{ __('admin.success') }}',
                message: response.message ? response.message : 'admin.Screening guidelines saved successfully',
              });
            } else {
              releaseFailureAlert({
                title: '{{ __('admin.Error') }}',
                message: response.message ? response.message : '{{ __('admin.unexpected_error') }}',
              });
            }
          },
          error: function(xhr) {
            const errorMessage = xhr.responseJSON?.message || '{{ __('admin.unexpected_error') }}';
            releaseFailureAlert({
              title: '{{ __('admin.Error') }}',
              message: errorMessage,
            });
          }
        });
      });
      // Form selection change handler
      $('#step-form-select').on('change', function() {
        const stepId = $(this).data('step-id');
        const formId = $(this).val();
        $.ajax({
          url: '{{ route("screening.update_step_form") }}',
          type: 'POST',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: { step_id: stepId, form_id: formId || null },
          success: function(response) {
            if (response.success) {
              releaseSuccessAlert({ title: '{{ __("admin.success") }}', message: response.message });
            }
          },
          error: function(xhr) {
            releaseFailureAlert({ title: '{{ __("admin.Error") }}', message: xhr.responseJSON?.message || '{{ __("admin.unexpected_error") }}' });
          }
        });
      });

      // Initialize tooltips
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    });
  </script>
@stop
