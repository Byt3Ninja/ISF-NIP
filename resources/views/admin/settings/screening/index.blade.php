@extends('layouts.admin')
@php
  $status = strtolower($evaluationForm->status);
  if( $cstep->data == 'grouptogroup' )
  {
    //
  }
  else
  {
    $judgersList = collect( json_decode($cstep->list) ?? [] );
    $judgersList = $judgersList->map(function($judger) { return intval($judger); });
  }
@endphp
@section('style')
<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
<link rel="stylesheet" href="/path/to/select2.min.css">
<style>
  .remove-panel-btn { cursor: pointer; }
  /* Custom fade animation for tab content */
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  .tab-content {
    display: none;
    animation: fadeIn 0.3s ease;
  }
  .tab-content.active {
    display: block;
  }
  /* Input validation feedback */
  .is-invalid {
    border-color: #dc3545 !important;
  }
  .is-valid {
    border-color: #28a745 !important;
  }
</style>
@stop

@section('content')
@php
  $types = ['onetoall'=>'Assign One to all users','grouptoall'=>'Assign Group to all users','grouptogroup'=>'Assign Group to group of users'];
@endphp
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
    <div class="content-body">
      @if ($cstep)
      <section id="multiple-column-form">
        @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ __('admin.' . session()->get('message')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if (session()->has('message2'))
        <div class="alert alert-danger alert-dismissible" role="alert">
          {{ __('admin.' . session()->get('message2')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <div class="row match-height">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card mb-4">
              <div class="card-header" style="padding-bottom: 15px;">
                <h4 class="card-title">{{ __('cycles.Portal screening list') }}</h4>
              </div>
              <hr>
              <div class="card-body">
                <label class="form-label fs-5">{{ __('cycles.Select step from list to manage') }}</label>
                <fieldset class="d-flex flex-column flex-md-row gap-3">
                  <select id="cstep" class="select2 form-control rounded"
                    style="margin-left: {{ $l == 'ar' ? '10px' : '0' }}; margin-right: {{ $l == 'ar' ? '0' : '10px' }}; width: 60%;"
                    onchange="window.open('/dashboard/'+this.options[this.selectedIndex].value+'/screening','_self');">
                    @foreach ($steps as $step)
                    <option value="{{ $step->id }}" {{ $step->id == $id ? 'selected' : '' }}>{{ $step->title }}
                      ({{ __('cycles.Cycle') }} "{{ $step->cycle->title }}" -
                      {{ __('cycles.Start') }} {{ $step->from->format('d-m-Y') ?? '-' }}) -
                      {{ optional(optional($step->cycle)->organization)->name }}</option>
                    @endforeach
                  </select>
                  <div class="input-group-append">
                    <button class="btn btn-label-secondary"
                      onclick="window.location.href='/dashboard/screening/results/{{ $id }}'">
                      <span class="mx-1">{{ __('cycles.Results') }}</span>
                    </button>
                    <a class="btn btn-label-success" href="/dashboard/cycles/{{ $cstep->cycle_id }}">
                      <span class="mx-1">{{ __('cycles.Cycle') }}</span>
                    </a>
                    <button class="btn btn-label-primary" onclick="DoChange(1)">
                      <i class="ti ti-pencil"></i> <span class="mx-1">{{ __('cycles.Edit') }}</span>
                    </button>
                    <button class="btn btn-label-danger" onclick="DoChange(2)">
                      <i class="ti ti-trash text-danger"></i> <span class="mx-1">{{ __('cycles.Delete') }}</span>
                    </button>
                  </div>
                </fieldset>
                <br />
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card mb-4">
            <div class="card-header">
              <h4 class="card-title success">{{ $cstep->title }}</h4>
              <p>{{ $cstep->description }}</p>
              <small class="text-muted">
                {{ __('cycles.from') }}
                {{ $cstep->from->format('d-m-Y') ?? '-' }} {{ __('cycles.to') }} {{ $cstep->to->format('d-m-Y') ?? '-' }}
              </small>
            </div>
            <hr style="margin:-10px 0 5px 0;">
            <div class="card-body">
              <div class="card-text">
                <dl class="row">
                  <dt class="col-sm-3 text-right">{{ __('cycles.Cycle') }}</dt>
                  <dd class="col-sm-9">{{ $cstep->cycle->title }} {{ __('cycles.from') }}
                    {{ $cstep->cycle->start->format('d-m-Y') ?? '-' }} {{ __('cycles.to') }}
                    {{ $cstep->cycle->end->format('d-m-Y') ?? '-' }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-3 text-right">{{ __('cycles.Form title') }}</dt>
                  <dd class="col-sm-9">{!! $form->title ?? '<span class="danger">' . __('cycles.NoForm') . '</span>' !!}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-3 text-right">{{ __('cycles.Screening type') }}</dt>
                  <dd class="col-sm-9">
                    {{ __('cycles.' . $types[$cstep->data]) }}
                  </dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-3 text-right">{{ __('cycles.Total registered') }}</dt>
                  <dd class="col-sm-9">{{ \App\Company::whereIn('step', [$form->id ?? '', $cstep->id])->count() }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-3 text-right">{{ __('cycles.Registered Judges') }}</dt>
                  <dd class="col-sm-9">{{ $judgers_count }} </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card">
            <div class="card-header border-bottom border-light mb-0">
              <h4 class="card-title primary">{{ __('cycles.' . $types[$cstep->data]) }}</h4>
              <p>{{ __('cycles.Select judges from the menu') }}:</p>
            </div>
            <div class="card-body">
              <div class="">
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
                    {{-- <div class="form-group">
                      @foreach (json_decode($cstep->list) ?? [] as $key => $clist)
                        <div class="form-row">
                          <p style="margin:5px;">{{ __('cycles.panel list') . ' ' . $key }}</p>
                          <div class="row col-md-12">
                            <div class="col-md-11">
                              <select name="judges[{{ $key }}][]" class="select2 form-control panel{{ $key }}"
                                multiple="multiple">
                                @foreach ($judges as $judge)
                                <option value="{{ $judge->id }}" {{ in_array($judge->id, is_array($clist) ? $clist : [], true) ? 'selected' : '' }}>
                                  {{ $judge->name }} ({{ $judge->email }})
                                </option>
                                @endforeach
                              </select>
                            </div>
                            @php
                            $panel = \App\Screening::where('step_id', $cstep->id)
                                ->where('panel', $key)
                                ->first();
                            if ($panel && $panel->users) {
                                $panel = json_encode($panel->users);
                            } else {
                                $panel = '';
                            }
                            @endphp
                            <input type="hidden" value="{{ $panel }}" name="panel{{ $key }}">
                            <div class="col-md-1">
                              <button type="button" class="btn btn-warning" onclick="openusers({{ $key }})">
                                <i class="ti ti-edit text-info"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                      @endforeach
                      @php($key = ($key ?? 0) + 1)
                      <div class="form-row">
                        <p style="margin: 5px">{{ __('cycles.panel list') . ' ' . $key }}</p>
                        <div class="row col-md-12">
                          <div class="col-md-11">
                            <select name="judges[{{ $key }}][]" class="select2 form-control panel{{ $key }}"
                              multiple="multiple">
                              @foreach ($judges as $judge)
                              <option value="{{ $judge->id }}">{{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }} ({{ $judge->email }})</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-1 float-right">
                            <button class="btn btn-primary" type="submit"><i class="ti ti-plus"></i></button>
                          </div>
                        </div>
                      </div>
                    </div> --}}
                  @else
                    <div class="form-group mt-6">
                      <label class="form-label fs-5">{{ __('cycles.Judges list') }}</label>
                      <select 
                        id="judges-select" 
                        name="judges[]" 
                        class="select2 form-select" 
                        {{ $cstep->data == 'onetoall' ? '' : 'multiple="multiple"' }}
                        data-placeholder="{{ __('cycles.Select') }}..."  
                      >
                        {{-- {!! $cstep->data == 'onetoall' ? '<option disabled value="">' . __('cycles.Please select one name') . '</option>' : '' !!} --}}
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
              </div>
            </div>
          </div>
        </div>
      </section>
      @if( $_user->ableTo('screening.evaluation_show', $permissions) )
      <section>
        <!-- Evaluation Model CRUD Tab -->
        <div id="evaluation-model" class="card card-body tab-content active mt-6">
          <h4>{{ __('cycles.ManageEvaluationModel') }}</h4>
          <p class="text-muted">{{ __('cycles.EvaluationModelDescription') }}</p>
          
          <!-- Screamer Warning -->
          @if( $_user->ableTo('screening.evaluation_publish', $permissions) )
            <div class="alert alert-info alert-dismissible fade show border border-info" role="alert">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>{{ __('cycles.Important Notice') }}:</strong><br>
              <span dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}">
                @if($l == 'ar')
                  يرجى الملاحظة أنه عند حفظ معايير الشركات المقدمة للانضمام إلى البرنامج، لن تتمكن من تعديلها لاحقًا. تأكد من مراجعة المعايير بعناية قبل الحفظ.
                @else
                  Please note that once you save the criteria for companies applying to join the program, you will not be able to modify them later. Please review the criteria carefully before saving.
                @endif
              </span>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          
          <div>
            <div class="d-flex align-items-center gap-4 mb-4">
              @if( $_user->ableTo('screening.evaluation_save_draft', $permissions) && $status == 'draft' )
              <button id="draft-model-btn" class="btn btn-light">
                <i class="fas fa-save me-2"></i> {{ __('cycles.SaveDraft') }}
              </button>
              @endif
              @if( $_user->ableTo('screening.evaluation_publish', $permissions) )
                @if( $status == 'draft' )
                  <button id="save-model-btn" class="btn btn-primary">
                    <i class="ti ti-world me-2"></i> {{ __('cycles.Publish') }}
                  </button>
                @else
                  <button onclick="event.preventDefault();" class="btn btn-primary disabled" disabled>
                    <i class="ti ti-check me-2"></i> {{ __('cycles.Published') }}
                  </button>
                @endif
              @endif
            </div>
            <div id="model-validation-summary" style="display: none;"></div>
          </div>
          <div id="model-editor" class="mt-4"></div>
          @if( $_user->ableTo('screening.evaluation_add_criteria') && $status == 'draft' )
          <button id="add-category-btn" class="btn btn-label-primary">
            <i class="fas fa-plus me-2"></i> {{ __('cycles.AddNewCriteria') }}
          </button>
          @endif
        </div>
      </section>
      @endif
      @else
      <div class="card">
        <div class="card-header border-bottom">
          <h4 class="card-title">{{ __('cycles.Error') }}</h4>
        </div>
        <div class="card-body">
          {{ __('cycles.No records screening from') }}
          <a href="/dashboard/cycles">{{ __('cycles.Cycles') }}</a>.
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
<!-- END: Content-->

@if ($cstep)
<!-- Screening form Modal -->
<div class="modal fade text-left" id="stepForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form class="form" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="modal-body">
          <div id="menu2">
            <div class="form-group mb-3">
              <label class="form-label fs-5">{{ __('cycles.Choose screening type') }}: </label>
              <select class="select2 form-control" name="ScreeningSelect" id="ScreeningSelect" required>
                <option value="" disabled {{ !$cstep->data ? 'selected' : '' }}>{{ __('cycles.Choose screening type') }}</option>
                @foreach ($types as $k => $type)
                <option value="{{ $k }}" {{ $cstep->data == $k ? 'selected' : '' }}>
                  {{ __('cycles.' . $type) }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <input type="hidden" name="sid" value="{{ $cstep->id }}">
          <input type="hidden" name="type" value="8">
          <div class="form-group mb-3">
            <label class="form-label fs-5">{{ __('cycles.Title') }}: </label>
            <input value="{{ $cstep->title }}" name='title' type="text" placeholder="{{ __('cycles.Title') }}"
              class="form-control" required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fs-5">{{ __('cycles.Start From') }}: </label>
            <input value="{{ $cstep->from->format('Y-m-d') }}" name='fromDate' type="text" class="form-control date"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fs-5">{{ __('cycles.to') }}: </label>
            <input value="{{ $cstep->to->format('Y-m-d') }}" name='toDate' type="text" class="form-control date"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fs-5">{{ __('cycles.Description') }}: </label>
            <textarea name='description' placeholder="{{ __('cycles.Description') }}"
              class="form-control" style="min-height: 80px; resize: vertical;">{{ $cstep->description }}</textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
          </button>
          <button type="submit" value="submit" class="btn btn-primary ml-1">
            <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@if ($cstep->data == 'grouptogroup')
<!-- Users form -->
<div class="modal fade text-left" id="usersForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form class="form" action="{{ URL::to('dashboard/screening/add') }}" method="POST" enctype="multipart/form-data">
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

@section('pagescripts')
  <script src="/path/to/select2.min.js"></script>
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <script type="text/javascript">
    $('.date').datetimepicker({
      timepicker: false,
      format: 'Y-m-d'
    });

    function DoChange(set) {
      switch (set) {
        case 1:
          $('#stepForm').modal('show');
          break;
        case 2:
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          });
          if (confirm("{{ __('cycles.Are you sure, Delete current step and all its contents?') }}")) {
            $.ajax({
              type: "POST",
              url: "/dashboard/cycles/modify",
              data: {
                "stepid": {{ $cstep->id ?? 0 }},
                "type": 6,
              },
              success: function(response) {
                console.log(response);
                location.href = '/dashboard/screening';
              },
              error: function(error) {
                console.log(error);
                alert("Error Occurred, Try again!");
              }
            });
          }
      }
    }

    function openusers(panel) {
      $('#pid').val(panel);
      $('#cpanel').text(panel);
      let theurl = "/dashboard/screening/get/{{ $form->id ?? 0 }}/{{ $cstep->id ?? 0 }}/" + panel;
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
          allowClear: isScreeningType,
          dropdownParent: isScreeningType ? $('#stepForm') : null,
          width: '100%'
        });
      });

      // Reinitialize ScreeningSelect when modal is shown to ensure interactivity
      $('#stepForm').on('shown.bs.modal', function() {
        $('#ScreeningSelect').select2('destroy').select2({
          placeholder: '{{ __('cycles.Choose screening type') }}',
          allowClear: true,
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

        @if ($cstep->data == 'grouptogroup')
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
      @if ($cstep->data == 'grouptogroup')
        $(document).on('change', 'select[class*="panel"]', updateJudgesInput);
      @else
        $('#judges-select').on('change', updateJudgesInput);
      @endif

      $('form[action="/dashboard/screening/update"]').on('submit', function(e) {
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

      // Debug screening type changes
      $('#ScreeningSelect').on('change', function() {
        console.log('Screening type changed to:', $(this).val());
      });
    });

    document.addEventListener('DOMContentLoaded', () => {
      // --- Tab Navigation ---
      const navLinks = document.querySelectorAll('.nav-link');
      const tabContents = document.querySelectorAll('.main-content .tab-content');

      function setActiveTab(targetId) {
        navLinks.forEach(l => {
          l.classList.toggle('active', l.getAttribute('href') === `#${targetId}`);
        });
        tabContents.forEach(t => {
          t.classList.toggle('active', t.id === targetId);
        });
      }

      navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          const targetId = link.getAttribute('href').substring(1);
          setActiveTab(targetId);
          if (targetId === 'evaluation-model') {
            renderModelEditor();
          }
        });
      });

      const initialTab = window.location.hash ? window.location.hash.substring(1) : 'evaluation-model';
      setActiveTab(initialTab);

      // --- Chart Initialization ---
      const revenueChartCanvas = document.getElementById('revenueChart');
      if (revenueChartCanvas) {
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
        const ctx = revenueChartCanvas.getContext('2d');
        new Chart(ctx, {
          type: 'line',
          data: { /* ... chart data ... */ },
          options: { /* ... chart options ... */ }
        });
      }

      // --- Evaluation Model CRUD Logic ---
      const modelEditorContainer = document.getElementById('model-editor');
      @if( $status == 'draft' )
        @if( $_user->ableTo('screening.evaluation_add_criteria') )
        const addCategoryBtn = document.getElementById('add-category-btn');
        @endif
        @if( $_user->ableTo('screening.evaluation_publish') )
        const saveModelBtn = document.getElementById('save-model-btn');
        @endif
        @if( $_user->ableTo('screening.evaluation_save_draft') )
        const draftModelBtn = document.getElementById('draft-model-btn');
        @endif
      @endif

      const modelValidationSummary = document.getElementById('model-validation-summary');
      let evaluationModelData = @json( $evaluationForm );

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
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('cycles.Close') }}"></button>
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

      function renderModelEditor() {
        if (!modelEditorContainer) return;
        modelEditorContainer.innerHTML = '';

        evaluationModelData.categories.forEach((category, categoryIndex) => {
          const categoryDiv = document.createElement('div');
          categoryDiv.className = 'card shadow-sm border mb-4';
          categoryDiv.setAttribute('data-category-id', category.id);

          let subCriteriaHTML = '<div class="mt-3 ps-3 border-start border-primary border-3">';
          category.subCriteria.forEach((sub, subIndex) => {
            subCriteriaHTML += `
              <div class="d-flex gap-3 align-items-center py-3 border-bottom" data-subcriterion-id="${sub.id}">
                <input type="text" class="form-control subcriterion-name flex-grow-1" placeholder="{{ __('cycles.Sub Criteria Name') }}" value="${sub.name}" data-category-index="${categoryIndex}" data-sub-index="${subIndex}" pattern="[A-Za-z\\s\\u0600-\\u06FF]*" title="{{ __('cycles.Only letters and spaces allowed') }}">
                <div class="input-group" style="width: 150px;">
                  <input type="number" class="form-control text-center subcriterion-weight" placeholder="{{ __('cycles.Weight (%)') }}" value="${sub.weight * 100}" min="0" max="100" step="0.1" data-category-index="${categoryIndex}" data-sub-index="${subIndex}" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                  <span class="input-group-text bg-light">%</span>
                </div>
                @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
                <button class="btn btn-outline-danger btn-sm delete-subcriterion-btn" data-category-index="${categoryIndex}" data-sub-index="${subIndex}" title="{{ __('cycles.Delete Sub Criteria') }}">
                  <i class="fas fa-trash-alt"></i>
                </button>
                @endif
              </div>
            `;
          });
          subCriteriaHTML += '</div>';

          categoryDiv.innerHTML = `
            <div class="card-body">
              <div class="d-flex flex-nowrap justify-content-between align-items-center border-bottom pb-3 mb-3">
                <div class="w-100 d-flex justify-content-between align-items-center">
                  <input type="text" class="form-control category-name me-3 fw-bold fs-5" placeholder="{{ __('cycles.Category Name') }}" value="${category.name}" data-category-index="${categoryIndex}" pattern="[A-Za-z\\s\\u0600-\\u06FF]*" title="{{ __('cycles.Only letters and spaces allowed') }}">
                  <div class="d-flex align-items-center">
                    <div class="input-group" style="width: 150px;">
                      <input type="number" class="form-control text-center category-weight" placeholder="{{ __('cycles.Weight (%)') }}" value="${category.weight * 100}" min="0" max="100" step="0.1" data-category-index="${categoryIndex}" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                      <span class="input-group-text bg-light">%</span>
                    </div>
                    @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
                    <button class="btn btn-outline-danger btn-sm delete-category-btn text-nowrap ms-3" data-category-index="${categoryIndex}" title="{{ __('cycles.Delete Criteria') }}">
                      <i class="fas fa-trash-alt me-2"></i> {{ __('cycles.Delete Criteria') }}
                    </button>
                    @endif
                  </div>
                </div>
              </div>
              <div class="validation-message category-validation" style="display: none;"></div>
              ${subCriteriaHTML}
              @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
              <div class="mt-3 d-flex justify-content-end">
                <button class="btn btn-outline-secondary btn-sm add-subcriterion-btn" data-category-index="${categoryIndex}">
                  <i class="fas fa-plus me-2"></i> {{ __('cycles.Add Sub-criteria') }}
                </button>
              </div>
              @endif
            </div>
          `;
          modelEditorContainer.appendChild(categoryDiv);
        });

        attachEventListeners();
        validateModel(false);
      }

      function attachEventListeners() {
        @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
        document.querySelectorAll('.delete-category-btn').forEach(btn => {
          btn.replaceWith(btn.cloneNode(true));
        });
        document.querySelectorAll('.delete-category-btn').forEach(btn => {
          btn.addEventListener('click', handleDeleteCategory);
        });
        @endif

        @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
        document.querySelectorAll('.delete-subcriterion-btn').forEach(btn => {
          btn.replaceWith(btn.cloneNode(true));
        });
        document.querySelectorAll('.delete-subcriterion-btn').forEach(btn => {
          btn.addEventListener('click', handleDeleteSubCriterion);
        });
        @endif

        @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
        document.querySelectorAll('.add-subcriterion-btn').forEach(btn => {
          btn.replaceWith(btn.cloneNode(true));
        });
        document.querySelectorAll('.add-subcriterion-btn').forEach(btn => {
          btn.addEventListener('click', handleAddSubCriterion);
        });
        @endif

        document.querySelectorAll('.category-name, .category-weight, .subcriterion-name, .subcriterion-weight').forEach(input => {
          input.replaceWith(input.cloneNode(true));
        });
        document.querySelectorAll('.category-name, .category-weight, .subcriterion-name, .subcriterion-weight').forEach(input => {
          input.addEventListener('input', handleInputChange);
          input.addEventListener('change', () => validateModel(false));
          
          // Add specific input validation
          if (input.classList.contains('category-name') || input.classList.contains('subcriterion-name')) {
            // Text fields - only letters, spaces, and Arabic characters
            input.addEventListener('keypress', function(e) {
              const regex = /^[A-Za-z\s\u0600-\u06FF]*$/;
              if (!regex.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
                e.preventDefault();
              }
            });
            
            input.addEventListener('paste', function(e) {
              e.preventDefault();
              const paste = (e.clipboardData || window.clipboardData).getData('text');
              const regex = /^[A-Za-z\s\u0600-\u06FF]*$/;
              if (regex.test(paste)) {
                this.value = paste;
                handleInputChange({ target: this });
              }
            });
          }
          
          if (input.classList.contains('category-weight') || input.classList.contains('subcriterion-weight')) {
            // Number fields - only numbers and decimal point
            input.addEventListener('keypress', function(e) {
              const regex = /^[0-9.]$/;
              if (!regex.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight' && e.key !== 'Tab') {
                e.preventDefault();
              }
              
              // Prevent multiple decimal points
              if (e.key === '.' && this.value.includes('.')) {
                e.preventDefault();
              }
              
              // Prevent values over 100
              const newValue = this.value + e.key;
              if (parseFloat(newValue) > 100) {
                e.preventDefault();
              }
            });
            
            input.addEventListener('blur', function() {
              // Ensure value doesn't exceed 100
              if (parseFloat(this.value) > 100) {
                this.value = 100;
                handleInputChange({ target: this });
              }
              // Ensure value is not negative
              if (parseFloat(this.value) < 0) {
                this.value = 0;
                handleInputChange({ target: this });
              }
            });
          }
        });
      }

      function handleAddCategory() {
        evaluationModelData.categories.push({
          id: null,
          name: "",
          weight: 0,
          subCriteria: []
        });
        renderModelEditor();
      }

      @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
      function handleAddSubCriterion(event) {
        const categoryIndex = parseInt(event.target.closest('button').dataset.categoryIndex);
        if (isNaN(categoryIndex) || !evaluationModelData.categories[categoryIndex]) return;

        evaluationModelData.categories[categoryIndex].subCriteria.push({
          id: null,
          name: "",
          weight: 0
        });
        renderModelEditor();
      }
      @endif

      @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
      function handleDeleteCategory(event) {
        const categoryIndex = parseInt(event.target.closest('button').dataset.categoryIndex);
        if (isNaN(categoryIndex)) return;

        const categoryName = evaluationModelData.categories[categoryIndex]?.name || '{{ __('cycles.this category') }}';
        if (confirm(`{{ __('cycles.Are you sure you want to delete') }} "${categoryName}" {{ __('cycles.and all its sub-criteria?') }}`)) {
          evaluationModelData.categories.splice(categoryIndex, 1);
          renderModelEditor();
        }
      }
      @endif

      @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
      function handleDeleteSubCriterion(event) {
        const btn = event.target.closest('button');
        const categoryIndex = parseInt(btn.dataset.categoryIndex);
        const subIndex = parseInt(btn.dataset.subIndex);
        if (isNaN(categoryIndex) || isNaN(subIndex)) return;

        const subName = evaluationModelData.categories[categoryIndex]?.subCriteria[subIndex]?.name || '{{ __('cycles.this sub-criterion') }}';
        if (confirm(`{{ __('cycles.Are you sure you want to delete') }} "${subName}"?`)) {
          evaluationModelData.categories[categoryIndex].subCriteria.splice(subIndex, 1);
          renderModelEditor();
        }
      }
      @endif

      function handleInputChange(event) {
        const input = event.target;
        const categoryIndex = parseInt(input.dataset.categoryIndex);
        const subIndex = input.dataset.subIndex !== undefined ? parseInt(input.dataset.subIndex) : null;

        if (isNaN(categoryIndex)) return;

        const category = evaluationModelData.categories[categoryIndex];
        if (!category) return;

        if (input.classList.contains('category-name')) {
          category.name = input.value;
        } else if (input.classList.contains('category-weight')) {
          category.weight = parseFloat(input.value) / 100 || 0;
        } else if (subIndex !== null) {
          const subCriterion = category.subCriteria[subIndex];
          if (!subCriterion) return;
          if (input.classList.contains('subcriterion-name')) {
            subCriterion.name = input.value;
          } else if (input.classList.contains('subcriterion-weight')) {
            subCriterion.weight = parseFloat(input.value) / 100 || 0;
          }
        }
      }

      @if( $status == 'draft' )
        @if( $_user->ableTo('screening.evaluation_publish') )
        function handleSaveModel() {
          if (validateModel(true, true)) {  // Use strict validation for publishing
            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
            });
            $.ajax({
              type: "POST",
              url: "{{ route('screening.evaluation_publish') }}",
              data: {
                "action": "publish",
                "criteria": evaluationModelData.categories,
                "stepId": {{ $cstep->id }},
              },
              success: function(response) {
                console.log(response);
                if (response.status) {
                  evaluationModelData.categories = response.data;
                  showAlert(modelValidationSummary, '{{ __('cycles.Model published successfully!') }}', 'success', 3000);
                  // Refresh page to update status
                  setTimeout(() => {
                    window.location.reload();
                  }, 2000);
                } else {
                  showAlert(modelValidationSummary, response.message, 'danger');
                }
              },
              error: function(error) {
                console.error("Error publishing model:", error);
                showAlert(modelValidationSummary, '{{ __('cycles.Error publishing model. Please try again.') }}', 'danger');
              }
            });
          } else {
            alert("{{ __('cycles.Please fix all validation errors before publishing.') }}");
          }
        }
        @endif

        @if( $_user->ableTo('screening.evaluation_save_draft') )
        function handleDraftModel() {
          // Save draft without validation - allow incomplete forms
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          });
          $.ajax({
            type: "POST",
            url: "{{ route('screening.evaluation_save_draft') }}",
            data: {
              "action": "draft",
              "criteria": evaluationModelData.categories,
              "stepId": {{ $cstep->id }},
            },
            success: function(response) {
              console.log(response);
              if (response.status) {
                evaluationModelData.categories = response.data;
                showAlert(modelValidationSummary, '{{ __('cycles.Draft saved successfully!') }}', 'success', 3000);
              } else {
                showAlert(modelValidationSummary, response.message, 'danger');
              }
            },
            error: function(error) {
              console.error("Error saving draft:", error);
              showAlert(modelValidationSummary, '{{ __('cycles.Error saving draft. Please try again.') }}', 'danger');
            }
          });
        }
        @endif
      @endif

      function validateModel(showGlobalSummary, isPublishing = false) {
        let isValid = true;
        let totalCategoryWeight = 0;
        let globalErrorMessages = [];

        evaluationModelData.categories.forEach((category, categoryIndex) => {
          const categoryElement = modelEditorContainer.querySelector(`.card[data-category-id="${category.id}"]`);
          const categoryValidationDiv = categoryElement ? categoryElement.querySelector('.category-validation') : null;
          let categoryErrorMessage = '';

          totalCategoryWeight += category.weight;

          // Only apply strict validation when publishing
          if (isPublishing) {
            // Check if category name is empty
            if (!category.name || category.name.trim() === '') {
              isValid = false;
              categoryErrorMessage = `{{ __('cycles.Category name is required') }}`;
              globalErrorMessages.push(`{{ __('cycles.Category') }} ${categoryIndex + 1}: ${categoryErrorMessage}`);
            }
            
            // Check if category has weight but no sub-criteria
            if (category.weight > 0 && category.subCriteria.length === 0) {
              isValid = false;
              categoryErrorMessage = `{{ __('cycles.Category with weight must have sub-criteria') }}`;
              globalErrorMessages.push(`{{ __('cycles.Category') }} "${category.name}": ${categoryErrorMessage}`);
            }
            
            // Check sub-criteria names
            category.subCriteria.forEach((sub, subIndex) => {
              if (!sub.name || sub.name.trim() === '') {
                isValid = false;
                globalErrorMessages.push(`{{ __('cycles.Category') }} "${category.name}", {{ __('cycles.Sub-criterion') }} ${subIndex + 1}: {{ __('cycles.Sub-criterion name is required') }}`);
              }
            });
          }

          let totalSubCriterionWeight = 0;
          if (category.subCriteria.length > 0) {
            category.subCriteria.forEach(sub => {
              totalSubCriterionWeight += sub.weight;
            });

            // Only enforce weight validation when publishing
            if (isPublishing && Math.abs(totalSubCriterionWeight - 1.0) > 0.001) {
              isValid = false;
              categoryErrorMessage = `{{ __('cycles.Sub-criterion weights must sum to 100%') }} ({{ __('cycles.currently') }} ${(totalSubCriterionWeight * 100).toFixed(1)}%).`;
              globalErrorMessages.push(`{{ __('cycles.Category') }} "${category.name}": ${categoryErrorMessage}`);
            } else if (!isPublishing && totalSubCriterionWeight > 1.0) {
              // Just show warning for drafts when over 100%
              categoryErrorMessage = `{{ __('cycles.Warning: Sub-criterion weights exceed 100%') }} ({{ __('cycles.currently') }} ${(totalSubCriterionWeight * 100).toFixed(1)}%).`;
            }
          } else if (category.weight > 0 && !isPublishing) {
            categoryErrorMessage = `{{ __('cycles.Warning: Category has weight but no sub-criteria. It won\'t contribute to score.') }}`;
          }

          if (categoryValidationDiv) {
            if (categoryErrorMessage && categoryErrorMessage.startsWith('Warning:')) {
              showAlert(categoryValidationDiv, categoryErrorMessage, 'warning');
            } else if (categoryErrorMessage) {
              showAlert(categoryValidationDiv, categoryErrorMessage, 'danger');
            } else {
              categoryValidationDiv.style.display = 'none';
            }
          }
        });

        // Only enforce total weight validation when publishing
        if (isPublishing && Math.abs(totalCategoryWeight - 1.0) > 0.001) {
          isValid = false;
          globalErrorMessages.push(`{{ __('cycles.Total category weights must sum to 100%') }} ({{ __('cycles.currently') }} ${(totalCategoryWeight * 100).toFixed(1)}%).`);
        }

        if (showGlobalSummary) {
          if (!isValid) {
            showAlert(modelValidationSummary, '<strong>{{ __('cycles.Validation Errors:') }}</strong><br>' + globalErrorMessages.join('<br>'), 'danger');
          } else {
            const message = isPublishing ? '{{ __('cycles.Model structure is valid and ready for publishing.') }}' : '{{ __('cycles.Model structure is valid.') }}';
            showAlert(modelValidationSummary, message, 'success', 3000);
          }
        } else {
          if (!modelValidationSummary.classList.contains('alert-danger') && !modelValidationSummary.classList.contains('alert-success')) {
            modelValidationSummary.style.display = 'none';
          }
        }

        return isValid;
      }

      @if( $status == 'draft' )
        @if( $_user->ableTo('screening.evaluation_add_criteria') )
        addCategoryBtn.addEventListener('click', handleAddCategory);
        @endif
        @if( $_user->ableTo('screening.evaluation_publish') )
        saveModelBtn.addEventListener('click', handleSaveModel);
        @endif
        @if( $_user->ableTo('screening.evaluation_save_draft') )
        draftModelBtn.addEventListener('click', handleDraftModel);
        @endif
      @endif

      if (document.getElementById('evaluation-model').classList.contains('active')) {
        renderModelEditor();
      }
    });

    $(document).on('click', '#addPanelBtn', function(e) {
      e.preventDefault();
      // let tBody = $('#panels-list-body');
      // let lastTr = tBody.children('tr').last();
      // let lastTrData = $(lastTr).data('panel');
      lastTrData = $('#panels-list-body tr').length ?? 0;
      let panel = lastTrData + 1;
      let html = `<tr data-panel="${panel}">
        <td>
          <i title="{{ __('cycles.Remove panel') }}" class="remove-panel-btn fas fa-fw fa-sm fa-minus-circle text-danger"></i> 
          {{ __('cycles.Panel list') }} ${panel}
        </td>
        <td>
          <select name="judges[${panel}][]" class="select2 form-control panel judgers-selectors" multiple="multiple">
            @foreach( $judges as $judge )
              <option value="{{ $judge->id }}">{{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }} ({{ $judge->email }})</option>
            @endforeach
          </select>
        </td>
        <td>
          <select name="users[${panel}][]" class="select2 form-control panel users-selectors" multiple="multiple">
            @foreach( $companies as $company )
              <option value="{{ $company->id }}">{{ $company->{'name_' . $l} }}</option>
            @endforeach
          </select>
        </td>
      </tr>`;

      $('#panels-list-body').append( html );
      $('.judgers-selectors').each(function() {
        const $select = $(this);
        $select.select2({
          placeholder: "{{ __('cycles.Select judges') }}",
          allowClear: false,
          dropdownParent: null,
          width: '100%'
        });
      });
      $('.users-selectors').each(function() {
        const $select = $(this);
        $select.select2({
          placeholder: "{{ __('cycles.Select Company') }}",
          allowClear: false,
          dropdownParent: null,
          width: '100%'
        });
      });
    });

    $(document).on('click', '.remove-panel-btn', function(e) {
      e.preventDefault();
      $(this).closest('tr').remove();
      let index = 1;
      $('#panels-list-body tr').each(function() {
        const $tr = $(this);
        let line = `
            <i title="{{ __('cycles.Remove panel') }}" class="remove-panel-btn fas fa-fw fa-sm fa-minus-circle text-danger"></i> 
            {{ __('cycles.Panel list') }} ${index}
        `;
        index++
        $($tr).find('td').eq(0).html(line);
        $($tr).find('td').eq(1).find('select').attr('name', `judges[${index}][]`);
        $($tr).find('td').eq(2).find('select').attr('name', `users[${index}][]`);
      });
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