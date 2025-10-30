@extends('layouts.admin')
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
@endphp
@section('style')
<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
<style>
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
</style>
@stop

@section('content')
@php
  $types = ['onetoall'=>'Assign One to all users','grouptoall'=>'Assign Group to all users','grouptogroup'=>'Assign Group to group of users'];
@endphp
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
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
              <h5 class="card-title success">{{ $cstep->title }}</h5>
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
              <h5 class="card-title primary">{{ __('cycles.' . $types[$cstep->data]) }}</h5>
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
        @include('admin.settings.screening-evaluation')
      @endif
      @else
      <div class="card card-body shadow-none h-100 d-flex justify-content-center align-items-center">
        <div class="d-flex flex-column">
          <img src="/icons/not-found.svg" width="250" height="250" alt="No Result">
          <h5 class="mb-0 text-center text-dark fw-bold">
            {{ __('admin.No Result') }}            
          </h5>
          <p class="text-center text-muted">
            {{ $l == 'en' ? 'You should add a screening from programs' : 'يجب عليك إضافة مرحلة تصفية من البرامج' }}
          </p>
          <div class="text-center">
            <a class="btn btn-lg btn-primary" href="/dashboard/cycles">
              <i class="ti ti-plus me-2"></i>
              {{ $l == 'en' ? 'Add Screening' : 'إضافة مرحلة تصفية' }}
            </a>
          </div>
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

@section('scripts')
@stop

@section('pagescripts')
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
      let theurl = "/dashboard/screening/get/{{ optional($form)->id ?? 0 }}/{{ optional($cstep)->id ?? 0 }}/" + panel;
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