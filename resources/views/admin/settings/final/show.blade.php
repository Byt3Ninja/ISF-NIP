@extends('layouts.admin')

@section('vendorstyle')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/tagify/tagify.css" />
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
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Alerts -->
    @if (session()->has('message'))
      <x-alert type="success" message="{{ __('admin.' . session()->get('message')) }} {{ Session::forget('message') }}" />
    @endif
    @if (session()->has('message2'))
      <x-alert type="danger" message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" />
    @endif
    <!--/ Alerts -->

    {{-- <div class="d-flex flex-column mb-4">
      <h4 class="mb-1">{{ __('admin.screening_step_details') }}</h4>
      <p class="mb-0">{{ __('admin.screening_details_description') }}</p>
    </div> --}}

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
            <div class="d-flex align-items-center gap-3">
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

              @if (isset($form->id))
                <div class="mb-3 px-3 py-2 bg-card rounded-2 border border-warning">
                  <a href="{{ route('forms.render_view', $form->id) }}" class="d-flex align-items-start gap-3">
                  <div class="avatar">
                      <div class="avatar-initial rounded-circle bg-label-warning">
                        <i class="ti ti-file"></i>
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <div class="text-secondary small mb-1">{{ __('builder.Form') }}</div>
                      <div class="text-warning fw-semibold">
                        {!! $form->title ?? __('cycles.NoForm') !!}
                      </div>
                    </div>
                  </a>
                </div>
              @endif
            </div>

            <!-- Judges -->
            <div class="col-lg-6">
              <div class="border-start border-dark border-4 ps-3 mb-3">
                <h6 class="text-dark fw-bold small mb-0">
                  <i class="ti ti-user-check me-2"></i>{{ __('cycles.Assigned Judges') }}
                </h6>
              </div>
              <div class="p-3 bg-light-subtle rounded-2 border">
                <div class="d-flex flex-wrap gap-2">
                  @php
                    $allJudges = collect($judgers_list ?? [])
                        ->pluck('judgers')
                        ->flatten()
                        ->merge($judgersList ?? [])
                        ->unique()
                        ->filter();
                  @endphp

                  @forelse ($allJudges as $judgeId)
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
                <div class="d-flex flex-wrap gap-2">
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
                  @endphp

                  @forelse ($allCompanies as $companyId)
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
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ Screening Step Details Card -->

      <!-- Navigation Pills -->
      <nav class="nav-align-top mb-6">
        <ul class="nav nav-pills" role="tablist">
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
          <!-- Old Logic (should be removed) Once new logic done -->
          {{-- <div class="d-flex flex-column">
            @if (isset($cstep->data) && array_key_exists($cstep->data, $types))
              <h5 class="card-title primary">{{ __('cycles.' . $types[$cstep->data]) }}</h5>
            @endif
            <p class="text-muted">{{ __('cycles.Select judges from the menu') }}:</p>
            <form action="/dashboard/screening/update" method="POST" enctype="multipart/form-data">
              {!! csrf_field() !!}
              <input type="hidden" value="" name="judges" id="judges">
              <input type="hidden" value="{{ $cstep->id }}" name="step">
              <input type="hidden" name="fid" value="{{ $form->id ?? '' }}">
              @if ($cstep->data == 'grouptogroup')
                <div class="text-end py-3"> <button id="addPanelBtn" type="button" class="btn btn-label-primary"> <i class="fas fa-fw fa-sm fa-plus me-2"></i> {{ __('admin.Add_New') }} </button> </div>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th style="width: 20% !important;" scope="col">{{ __('admin.Panel') }}</th>
                        <th style="width: 40% !important;" scope="col">{{ __('admin.Judges') }}</th>
                        <th style="width: 40% !important;" scope="col">{{ __('admin.Users') }}</th>
                      </tr>
                    </thead>
                    <tbody id="panels-list-body">
                      @foreach( $judgers_list as $key => $list )
                        <tr data-panel="{{ $key }}">
                          <td>
                            <i title="Remove panel" class="remove-panel-btn cursor-pointer fas fa-fw fa-sm fa-minus-circle text-danger"></i> 
                            {{ __('admin.Panel_list') }} {{ $key }}
                          </td>
                          <td>
                            <select name="judges[{{ $key }}][]" class="select2 form-control panel judgers-selectors" multiple="multiple">
                              @foreach( $judges as $judge )
                                <option {{ in_array( $judge->id, $list['judgers'] ) ? 'selected' : '' }} value="{{ $judge->id }}">{{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }} ({{ $judge->email }})</option>
                              @endforeach
                            </select>
                          </td>
                          <td>
                            <select name="users[{{ $key }}][]" class="select2 form-control panel users-selectors" multiple="multiple">
                              @foreach( $companies as $company )
                                <option {{ in_array( $company->id, $list['companies'] ) ? 'selected' : '' }} value="{{ $company->id }}">{{ $company->{'name_' . $l} }}</option>
                              @endforeach
                            </select>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <div class="form-group mt-6">
                  <label class="form-label">{{ __('cycles.Judges list') }}</label>
                  <select 
                    id="judges-select" 
                    name="judges[]" 
                    class="select2 form-select" 
                    {{ $cstep->data == 'onetoall' ? '' : 'multiple="multiple"' }}
                    data-placeholder="{{ __('cycles.Select') }}..."  
                  >
                    <option value="">{{ __('cycles.Select') }}...</option>
                    @foreach ($judges as $judge)
                    <option value="{{ $judge->id }}" {{ in_array( intval( $judge->id ) , $judgersList->toArray()) ? 'selected' : '' }}>
                      {{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }} ({{ $judge->email }})
                    </option>
                    @endforeach
                  </select>
                </div>
              @endif
              <div class="form-check my-4">
                <input class="form-check-input" type="checkbox" id="customSwitch1"  name="notify" />
                <label class="form-check-label" for="customSwitch1">{{ __('cycles.Notify them') }}</label>
              </div>
              <div class="row">
                <div class="col-12 d-flex justify-content-end">
                  <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('admin.Update') }}</button>
                </div>
              </div>
            </form>
          </div> --}}
          <!-- Old Logic (should be removed) Once new logic done -->
        </div>
        <!--/ Judging Panels Tab -->

        <!-- Screening Guidelines Tab -->
        <div class="tab-pane fade" id="screening_guidelines" role="tabpanel" aria-labelledby="screening_guidelines-tab">
          <div class="d-flex flex-column">
            <h5 class="mb-1">{{ __('admin.screening_guidelines') }}</h5>
            <p class="mb-6 text-muted">{{ __('admin.screening_guidelines_description') }}</p>
            <div id="model-validation-summary" style="display: none;"></div>
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
          <div class="form-group mb-3">
            <label class="form-label">{{ __('admin.screening.screening_type') }}</label>
            <select class="form-select" name="ScreeningSelect" id="screening-type" required>
              <option value="onetoall">{{ __('admin.screening.onetoall') }}</option>
              <option value="grouptoall">{{ __('admin.screening.grouptoall') }}</option>
              <option value="grouptogroup">{{ __('admin.screening.grouptogroup') }}</option>
            </select>
          </div>
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
<script src="/vuexy/assets/vendor/libs/tagify/tagify.js"></script>
@stop

@section('pagescripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Get initial content before initializing Summernote
      const initialContent = $('#screening-guidelines-editor').val() || '';

      // Initialize Summernote editor
      $('#screening-guidelines-editor').summernote({
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

        if (stepData.data) {
          if (stepData.data == 'onetoall') {
            $('#screening-type').val('onetoall');
          } else if (stepData.data == 'grouptoall') {
            $('#screening-type').val('grouptoall');
          } else {
            $('#screening-type').val('grouptogroup');
          }
        }
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
        const isScreeningType = $select.attr('id') === 'ScreeningSelect';
        let placeHolder = '';
        if( $select.attr('id') === 'ScreeningSelect' ) { placeHolder = "{{ __('cycles.Choose screening type') }}"; }
        else if( $select.hasClass('judgers-selectors') ) { placeHolder = "{{ __('cycles.Select judges') }}"; }
        else if( $select.hasClass('users-selectors') ) { placeHolder = "{{ __('cycles.Select Company') }}"; }

        $select.select2({
          placeholder: placeHolder,
          // allowClear: isScreeningType,
          dropdownParent: isScreeningType ? $('#stepForm') : null,
          width: '100%'
        });
      });

      // Reinitialize ScreeningSelect when modal is shown to ensure interactivity
      $('#stepForm').on('shown.bs.modal', function() {
        $('#ScreeningSelect').select2('destroy').select2({
          placeholder: '{{ __('cycles.Choose screening type') }}',
          // allowClear: true,
          dropdownParent: $('#stepForm'),
          width: '100%'
        });
        console.log('ScreeningSelect reinitialized in modal');
        // Ensure dropdown is not disabled
        $('#ScreeningSelect').prop('disabled', false);
      });

      // Function to update hidden judges input
      function updateJudgesInput() {
        let allJudges = [];

        @if (optional($cstep)->data == 'grouptogroup')
          // Collect judges from all panel selects
          $('select[class*="panel"]').each(function() {
            const selectedValues = $(this).val() || [];
            console.log('Panel select:', $(this).attr('class'), 'Values:', selectedValues);
            allJudges = allJudges.concat(selectedValues);
          });
        @else
          // Collect judges from judges-select for onetoall or grouptoall
          const selectedValues = $('#judges-select').val() || [];
          console.log('Judges select:', selectedValues);
          allJudges = selectedValues;
        @endif

        // Update hidden input with unique judge IDs as JSON string
        allJudges = [...new Set(allJudges)].map(id => parseInt(id));
        console.log('All judges:', allJudges);
        $('#judges').val(JSON.stringify(allJudges));
      }

      // Initialize on page load
      updateJudgesInput();

      // Update on change for all judge selects
      @if (optional($cstep)->data == 'grouptogroup')
        $(document).on('change', 'select[class*="panel"]', updateJudgesInput);
      @else
        $('#judges-select').on('change', updateJudgesInput);
      @endif

      $('form[action="/dashboard/final/update"]').on('submit', function(e) {
        const judgesVal = $('#judges').val();
        const judges = judgesVal ? JSON.parse(judgesVal) : [];
        console.log('Submitting judges:', judges);
        if (!judges || judges.length === 0) {
          e.preventDefault();
          alert('{{ __('Please_Choose_Judges') }}');
          return false;
        }
      });

      $('#stepForm form').on('submit', function(e) {
        const screeningType = $('#ScreeningSelect').val();
        console.log('Submitting screening type:', screeningType);
        if (!screeningType) {
          e.preventDefault();
          alert('{{ __('Please select a screening type') }}');
          return false;
        }
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
              showAlert($('#model-validation-summary')[0], 'Screening guidelines saved successfully', 'success', 3000);
            } else {
              showAlert($('#model-validation-summary')[0], response.message, 'danger');
            }
          },
          error: function(xhr) {
            const errorMessage = xhr.responseJSON?.message || 'An error occurred while saving screening guidelines';
            showAlert($('#model-validation-summary')[0], errorMessage, 'danger');
          }
        });
      });

      // Helper function to show dismissible alerts
      function showAlert(element, message, type = 'info', timeout = null) {
        const alertTypes = {
          'success': 'alert-success',
          'danger': 'alert-danger',
          'warning': 'alert-warning',
          'info': 'alert-info'
        };

        const alertClass = alertTypes[type] || 'alert-info';

        element.innerHTML = `
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        element.className = `alert ${alertClass} alert-dismissible`;
        element.style.display = 'block';

        // Initialize Bootstrap alert dismiss functionality
        const dismissButton = element.querySelector('.btn-close');
        if (dismissButton) {
          dismissButton.addEventListener('click', function() {
            element.style.display = 'none';
            element.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'alert-info');
          });
        }

        // Auto-dismiss after timeout if specified
        if (timeout) {
          setTimeout(() => {
            if (element.classList.contains(alertClass)) {
              element.style.display = 'none';
              element.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'alert-info');
            }
          }, timeout);
        }
      }
    });

    setTimeout(() => {
      const alerts = document.querySelectorAll('.alert-dismissible');
      alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
      });
    }, 20000);
  </script>
@stop
