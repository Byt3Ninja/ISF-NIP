@extends('layouts.admin')

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/dragula.min.css">
<link rel="stylesheet" type="text/css" href="/app-assets/css/plugins/extensions/drag-and-drop.css">
<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<style>

  .daterangepicker {
    margin-top: 8rem !important;
  }

  #mob {
    display: flex;
    justify-content: flex-end;
  }

  @media screen and (max-width: 768px) {
    #mob {
      margin-top: 10px;
      justify-content: flex-start;
    }
  }
</style>
@stop

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
    <div class="content-body">
      @if (count($cycles) > 0)
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

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">
                  <i class="ti ti-circles ti-28px"></i>
                  <span class="mx-2">{{ __('cycles.Portal Cycles') }}</span>
                </h4>
              </div>
              <div class="card-body">
                <div class="row d-flex align-items-end">
                  <div class="col-12 col-md-6">
                    <div class="form-group">
                      <label class="form-label fs-5 fw-semibold mb-1">{{ __('cycles.Select cycle from list') }}</label>
                      <select id="ccycle" class="select2 form-select mb-4 md-mb-0"
                        onchange="window.open('/dashboard/cycles/'+this.options[this.selectedIndex].value,'_self');">
                        @foreach ($cycles as $cycle)
                        <option value="{{ $cycle->id }}" data-organization="{{ $cycle->organization_id }}" {{ $cycle->id
                          == $id ? 'selected' : '' }}>{{ $cycle->title }} ({{ __('cycles.Start') }}
                          {{ optional($cycle->start)->format('d-m-Y') ?? '' }} {{ __('cycles.to') }} {{ optional($cycle->end)->format('d-m-Y') ?? '' }})
                          - {{ optional($cycle->organization)->name }}
                        </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-12 col-md-6 mt-4 mt-md-0">
                    <div class="d-flex align-items-center gap-4">
                      <button class="btn btn-label-warning btn-sm py-2"
                        onclick="window.location.href='/dashboard/cycles/{{ $id }}/registered'">
                        <i class="ti ti-user"></i>
                        <span class="mx-1">{{ __('admin.Startups') }}</span>
                      </button>
                      <button class="btn btn-label-info btn-sm py-2" onclick="DoChange(1)">
                        <i class="ti ti-edit text-info"></i>
                        <span class="mx-1">{{ __('cycles.Edit') }}</span>
                      </button>
                      <button class="btn btn-label-primary btn-sm py-2" onclick="DoChange(2)">
                        <i class="ti ti-plus"></i>
                        <span class="mx-1">{{ __('cycles.New') }}</span>
                      </button>
                      <button class="btn btn-label-danger btn-sm py-2" onclick="DoChange(3)">
                        <i class="ti ti-trash text-danger"></i>
                        <span class="mx-1">{{ __('cycles.Delete') }}</span>
                      </button>
                    </div>
                  </div>
                </div>
                <br />
                <div class="row d-flex align-items-end">
                  <div class="col-12 col-md-6">
                    <div class="form-group">
                      <label class="form-label fs-5 fw-semibold mb-1">{{ __('cycles.Add cycle steps') }}</label>
                      <select class="select2 form-select mb-4 md-mb-0" id="StepSelect">
                        <option value="1">{{ __('cycles.Custom Form') }}</option>
                        <option value="2">{{ __('cycles.Screening') }}</option>
                        <option value="3">{{ __('cycles.Mentorship') }}</option>
                        <option value="4">{{ __('cycles.Training') }}</option>
                        <option value="5">{{ __('cycles.Final evaluation') }}</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-12 col-md-6 mt-4 mt-md-0">
                    <button class="btn btn-label-primary" onclick="DoChange(4)">
                      <i class="ti ti-plus"></i>
                      <span class="mx-2">{{ __('cycles.Add New step') }}</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Sortable lists section start -->
      <section id="sortable-lists" class="mt-6">
        <div class="row">
          <!-- Basic List Group -->
          <div class="col-sm-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">
                  <i class="ti ti-recycle ti-28px"></i>
                  <span class="mx-2">{{ __('cycles.Cycle steps') }}</span>
                </h4>
              </div>
              <div class="card-content">
                <div class="card-body">
                  <p>{{ __('cycles.Drag arrange') }}.</p>
                  <ul class="list-group" id="basic-list-group">
                    @foreach ($steps as $step)
                    <li class="list-group-item">
                      <div class="d-flex align-items-center justify-content-between"
                        style="flex-wrap: wrap; width: 100%;">
                        {{-- <i class="ti ti-plus ti-28px"></i> --}}
                        {{-- <i class="livicon-evo mr-2"
                          data-options="name: {{ $svgs[$step->stype - 1] }}.svg; size: 50px; style: original;">
                        </i> --}}
                        <div class="d-flex align-items-center">
                          <div class="bg-label-light rounded d-flex justify-content-center align-items-center"
                            style="width: 60px; height: 50px;">
                            <img src="/app-assets/fonts\LivIconsEvo/svg/loader-{{$step->stype}}.svg" alt="cycle-icon"
                              class="w-px-30 h-px-35" />
                          </div>
                          <div class="d-flex justify-content-between align-items-center mx-6" style="width: 100%">
                            <div>
                              <h5 class="mt-0">
                                <span id="{{ $step->id }}-step" class="carr">{{ $step->arr }}</span>-&nbsp;{{
                                $step->title }}
                                @if ($step->stype == 1 && !empty($step->form))
                                <small>( {{ __('cycles.Custom Form') }} &nbsp;-&nbsp; {{ $step->form->form_name }}
                                  )</small>
                                @endif
                              </h5>
                              {{ __('cycles.Start From') }}
                              {{ !empty($step->from) ? $step->from->format('d-m-Y') : '-' }}, {{ __('cycles.to') }}
                              {{ !empty($step->to) ? $step->to->format('d-m-Y') : '-' }}
                            </div>
                          </div>
                        </div>

                        <div class="d-flex align-items-center" id="mob">
                          <a href="javascript:void(0)" onclick="ModifyStep(1,{{ $step->id }})" id="{{ $step->id }}"
                            title="{{ __('cycles.Edit Title and dates') }}">
                            <span class="btn btn-icon btn-label-info rounded-circle">
                              <i class="ti ti-edit text-info"></i></span>
                          </a>&nbsp;
                          <a href="javascript:void(0)" onclick="deleteStep(this.id)" id="{{ $step->id }}"
                            title="{{ __('cycles.Delete current step') }}">
                            <span class="btn btn-label-danger btn-icon rounded-circle">
                              <i class="ti ti-trash text-danger"></i></span>
                          </a>&nbsp;
                          @php
                            if ($step->stype == 1) {
                               $link = 'show-form/' . $step->id; 
                               $set = 'show-builder';            
                                 } elseif ($step->stype == 2) {
                                  $link = $step->id . '/screening';
                                  $set = $step->id . '/screening';
                                  } elseif ($step->stype == 3) {
                                     $link = 'mentorship/sessions';
                                     $set = 'mentorship/assign/' . $step->cycle_id;
                                      } elseif ($step->stype == 4) {
                                          $link = 'training/sessions/' . $step->id;
                                          $set = 'training/sessions/' . $step->id;
                                    } else {
                                          $link = 'final/results/' . $step->id;
                                          $set = $step->id . '/final/';
                                      }
                                    @endphp
                           <a href="/dashboard/{{ $set }}" id="{{ $step->id }}">
                            <span class="btn btn-icon btn-label-warning rounded-circle">
                                <i class="ti ti-settings" title="{{ __('cycles.Step settings') }}"></i>
                            </span>
                        </a>
                        @if($step->stype == 1 && $step->form)
                        <a href="{{ route('forms.render_view', $step->form->id) }}" id="{{ $step->id }}">
                          <span class="btn btn-icon btn-label-primary rounded-circle">
                            <i class="ti ti-eye" title="{{ __('cycles.view step results') }}"></i>
                          </span>
                        </a>
                      @endif
                        </div>
                      </div>
                    </li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
          </div>

        </div>
      </section>
      <!-- // Sortable lists section end -->
      @else
      <div class="card">
        <div class="card-header border-bottom">
          <h4 class="card-title">{{ __('cycles.Error') }}</h4>
        </div>
        <div class="card-body">
          {{ __('cycles.No records ... Add cycle first') }}: <button class="btn btn-light-primary"
            onclick="DoChange(2)">{{ __('cycles.Add new') }}</button>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>
<!-- END: Content-->

<!-- Modals -->
<!--Cycle Modal -->
<div class="modal fade" id="cycleForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <form class="form" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data"
        autocomplete="off">
        {!! csrf_field() !!}
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel33">{{ __('cycles.Cycle data') }}</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @if (is_null(auth()->user()->organization_id))
          <div class="form-group mb-4">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('builder.Choose Organization') }}: </label>
            <select class="select form-control" id="organization_id" name="organization_id" required>
              @foreach (\App\Organization::all() as $org)
              <option value="{{ $org->id }}">{{ $org->name_ar }}</option>
              @endforeach
            </select>
          </div>
          @else
          <input type="hidden" name="organization_id" value="{{ auth()->user()->organization_id }}"
            id="organization_id">
          @endif
          <div class="col-12 mb-4">
            <label for="bs-rangepicker-basic" class="form-label fs-6 fw-semibold mb-1">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
            <input id='start' name='start' type="hidden" />
            <input id='end' name='end' type="hidden" />
            <input type="text" id="bs-rangepicker-basic" class="form-control" name="date_range" />
          </div>
          <div class="mb-4">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Cycle title') }}: </label>
            <input type="hidden" name="type" id="type">
            <input type="hidden" name="cycleid" id="cycleid">
            <input id='title' name='title' type="text" placeholder="{{ __('cycles.Title') }}" class="form-control"
              required>
          </div>
          {{-- <div class="mt-3">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Valid from') }}: </label>
            <input id='start' name='start' type="text" placeholder="{{ now()->format('Y-m-d') }}"
              class="form-control date" required>
          </div>
          <div class="mt-3">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Valid to') }}: </label>
            <input id='end' name='end' type="text" placeholder="{{ now()->addDays(15)->format('Y-m-d') }}"
              class="form-control date" required>
          </div> --}}
          <div class="mb-3">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Description') }}: </label>
            <textarea id='description' name='description' placeholder="{{ __('cycles.Description') }}"
              class="form-control"></textarea>
          </div>
          <div class="form-group mb-0 custom-switch mb-4">
            <label class="form-label fs-6 fw-semibold mb-1"> </label>
            <span>{{ __('settings.Private') }}</span>&nbsp;
            <input name="private" type="checkbox" class="custom-control-input" id="customSwitch3">
            <label class="custom-control-label mr-1" for="customSwitch3"></label>
            <a id="cyclelink" target="_blank"></a>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
            {{-- <i class="ti ti-x"></i> --}}
            <span class="">{{ __('cycles.Close') }}</span>
          </button>
          <button type="submit" value="submit" class="btn btn-primary ml-1">
            {{-- <i class="ti ti-check d-block d-sm-none"></i> --}}
            <span class="">{{ __('cycles.Submit') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!--Add step form Modal -->
<div class="modal fade" id="stepForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <form class="form" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data"
        autocomplete="off" onsubmit="disableSubmitButton()">
        {!! csrf_field() !!}
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel33">{{ __('cycles.New step data') }} - <span id="formtitle">{{
              __('cycles.Custom Form') }}</span></h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="cid" id="cid" value="{{ $id }}">
          <input type="hidden" name="type" id="type5" value="5">
          <input type="hidden" name="arr" id="sarr" value="{{ isset($step) ? $step->arr + 1 : 1 }}">
          <input type="hidden" name="stype" id="stype" value="1">
          <div class="mb-4" id="menu1">
            <div class="form-group mb-0">
              <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Choose form') }}: </label>
              <select class="select form-control" name="FormSelect" id="FormSelect" required>
                {{-- @foreach (\App\BuilderForm::all() as $form) --}}
                {{-- <option value="{{$form->id}}">{{$form->form_name}}</option> --}}
                {{-- @endforeach --}}
              </select>
            </div>
          </div>
          <div class="mb-4" id="menu2">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Choose screening type') }}: </label>
            <div class="form-group mb-0">
              <select class="select2 form-control" name="ScreeningSelect" required>
                <option selected disabled> {{ __('cycles.Select type') }}</option>
                <option value="onetoall">{{ __('cycles.Assign One to all users') }}</option>
                <option value="grouptoall">{{ __('cycles.Assign Group to all users') }}</option>
                <option value="grouptogroup">{{ __('cycles.Assign Group to group of users') }}</option>
              </select>
            </div>
          </div>
          <div class="col-12 mb-4">
            <label for="stepFormRamgePicker" class="form-label fs-6 fw-semibold mb-1">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
            <input type="hidden" id="fromDate" name="fromDate" />
            <input type="hidden" id="toDate" name="toDate" />
            <input type="text" id="stepFormRamgePicker" class="form-control" name="date_range" required />
          </div>
          <div class="form-group mb-4">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Step Title') }}: </label>
            <input id='title' name='title' type="text" placeholder="{{ __('cycles.Title') }}" class="form-control"
              required>
          </div>
          {{-- <div class="form-group mt-3">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Valid from') }}: </label>
            <input id='fromDate' name='fromDate' type="text" placeholder="{{ now()->format('Y-m-d') }}"
              class="form-control" required readonly>
          </div>
          <div class="form-group mt-3">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Valid to') }}: </label>
            <input id='toDate' name='toDate' type="text" placeholder="{{ now()->addDays(15)->format('Y-m-d') }}"
              class="form-control" required readonly>
          </div> --}}
          <div class="form-group mb-4">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Description') }}: </label>
            <textarea id='description' name='description' placeholder="{{ __('cycles.Description') }}"
              class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
            {{-- <i class="ti ti-x"></i> --}}
            <span class="">{{ __('cycles.Close') }}</span>
          </button>
          <button type="submit" id="submitBtn" value="submit" class="btn btn-primary ml-1">
            {{-- <i class="ti ti-check d-block d-sm-none"></i> --}}
            <span class="">{{ __('cycles.Submit') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!--Modify custom form step Modal -->
<div class="modal fade" id="ModifyStepForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <form class="form" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data"
        autocomplete="off">
        {!! csrf_field() !!}
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel33">{{ __('cycles.Modify current step') }}</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="sid" id="sid">
          <input type="hidden" name="type" id="type7" value="8">
          <div class="mb-2" id="mmenu1">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Choose form') }}: </label>
            <div class="form-group mb-4">
              <select class="select form-control" id="mFormSelect" name="mFormSelect" required>
                {{-- @foreach (\App\BuilderForm::all() as $form) --}}
                {{-- <option value="{{$form->id}}">{{$form->form_name}}</option> --}}
                {{-- @endforeach --}}
              </select>
            </div>
          </div>
          <div class="mb-4" id="mmenu2">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Choose screening type') }}: </label>
            <div>
              <select class="select2 form-control" id="mScreeningSelect" name="mScreeningSelect" required>
                <option selected disabled> {{ __('cycles.Select type') }}</option>
                <option value="onetoall">{{ __('cycles.Assign One to all users') }}</option>
                <option value="grouptoall">{{ __('cycles.Assign Group to all users') }}</option>
                <option value="grouptogroup">{{ __('cycles.Assign Group to group of users') }}</option>
              </select>
            </div>
          </div>
          <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Form title') }}: </label>
          <div class="mb-4">
            <input id='mtitle' name='title' type="text" placeholder="{{ __('cycles.Title') }}" class="form-control"
              required>
          </div>
          <div class="col-12 mb-4">
            <label for="step-range-picker" class="form-label fs-6 fw-semibold mb-1">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
            <input type="hidden" id="mfromDate" name="fromDate" />
            <input type="hidden" id="mtoDate" name="toDate" />
            <input type="text" id="step-range-picker" class="form-control" name="date_range" required />
          </div>
          {{-- <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Valid from') }}: </label>
          <div class="mb-4">
            <input id='mfromDate' name='fromDate' type="text" class="form-control" required readonly>
          </div>
          <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Valid to') }}: </label>
          <div class="mb-4">
            <input id='mtoDate' name='toDate' type="text" class="form-control" required readonly>
          </div> --}}
          <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Description') }}: </label>
          <div class="form-group mb-0">
            <textarea id='mdescription' name='description' placeholder="{{ __('cycles.Description') }}"
              class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            {{-- <i class="ti ti-x"></i> --}}
            <span class="">{{ __('cycles.Close') }}</span>
          </button>
          <button type="submit" value="submit" class="btn btn-primary ml-1">
            {{-- <i class="ti ti-check d-block d-sm-none"></i> --}}
            <span class="">{{ __('cycles.Save') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- / Modals -->

@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
{{-- <script src="/vuexy/assets/js/pages-account-settings-account.js"></script> --}}
<script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
@stop


@section('pagescripts')
@php($ccycle = \App\Cycle::find($id))
<script src="/vuexy/assets/js/forms-selects.js"></script>
<script src="/app-assets/vendors/js/extensions/dragula.min.js"></script>
<script src="/js/jquery.datetimepicker.full.min.js"></script>
<script>
  function disableSubmitButton() {
    document.getElementById("submitBtn").disabled = true;
}
    console.log('loaded');
    const stepFormRamgePicker = $('#stepFormRamgePicker'); // Change to jQuery selector
    // console.log('stepFormRamgePicker', stepFormRamgePicker);

    if (stepFormRamgePicker.length) {
      stepFormRamgePicker.daterangepicker({
        opens: isRtl ? 'left' : 'right',
      }).on('apply.daterangepicker', function(ev, picker) {
        $('#fromDate').val(picker.startDate.format('YYYY-MM-DD'));
        $('#toDate').val(picker.endDate.format('YYYY-MM-DD'));
      });
    };

    // $('.date').datetimepicker({
    //   timepicker: false,
    //   format: 'Y-m-d'
    // });
    // $('#fromDate,#toDate,#mfromDate,#mtoDate').datetimepicker({
    //   format: 'Y-m-d',
    //   minDate: '{{ !is_null($ccycle) ? $ccycle->start->format('Y/m/d') : now()->format('Y/m/d') }}',
    //   maxDate: '{{ !is_null($ccycle) ? $ccycle->end->format('Y/m/d') : now()->addDays(15)->format('Y/m/d') }}',
    //   timepicker: false
    // });
    $(document).ready(function() {
      let drake = dragula([document.getElementById('basic-list-group')]);
      drake.on('drop', function(el) {
        let badges = document.getElementsByClassName('carr');
        let ids = [];
        for (let i = 0; i < badges.length - 1; ++i) {
          badges[i].innerText = i + 1;
          let id = badges[i].id.split('-');
          ids.push(id[0]);
        }
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
        $.ajax({
          type: "POST",
          url: "/dashboard/cycles/modify",
          data: {
            "steps": ids,
            "type": 4,
          },
          success: function(response) {
            // console.log(response);
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, arrange failed");
          }
        });
      })
    });

    function DoChange(set) {
      var cycle = $('#ccycle').find(":selected").val();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      switch (set) {
        case 1:
          $.ajax({
            type: "POST",
            url: "/dashboard/cycles/modify",
            data: {
              "cycleid": cycle,
            },
            success: function(response) {
              $('#title').val(response.title);
              $('#description').val(response.description);
              $('#cycleid').val(cycle);
              $('#type').val(1);

              var bsRangePickerBasic = $('#bs-rangepicker-basic');
              // $('#start').val(response.start);
              // $('#end').val(response.end);

              if (bsRangePickerBasic.length) {
                bsRangePickerBasic.daterangepicker({
                  opens: isRtl ? 'left' : 'right',
                  startDate: response.start ? moment(response.start) : null,
                  endDate: response.end ? moment(response.end) : null,
                }).on('apply.daterangepicker', function(ev, picker) {
                  $('#start').val(picker.startDate.format('YYYY-MM-DD'));
                  $('#end').val(picker.endDate.format('YYYY-MM-DD'));
                });
              }

              $('#organization_id').val(response.organization_id)
              if (response.private) {
                $('#customSwitch3').attr('checked', 'checked');
                $('#cyclelink').html('{{ __('cycles.Join URL') }}');
                $('#cyclelink').attr('href', '/dashboard/cycles/view/' + response.id + '?id=' + response.private);
              }
              $('#cycleForm').modal('show');
            },
            error: function(error) {
              console.log(error);
              alert("Error Occurred, Try again!");
            }
          });
          break;
        case 2:
          $('#title').val('');
          $('#description').val('');
          $('#cycleid').val('');
          $('#type').val(2);

          var bsRangePickerBasic = $('#bs-rangepicker-basic');
          // $('#start').val(response.start);
          // $('#end').val(response.end);

          if (bsRangePickerBasic.length) {
            bsRangePickerBasic.daterangepicker({
              opens: isRtl ? 'left' : 'right',
            }).on('apply.daterangepicker', function(ev, picker) {
              $('#start').val(picker.startDate.format('YYYY-MM-DD'));
              $('#end').val(picker.endDate.format('YYYY-MM-DD'));
            });
          }

          $('#customSwitch3').removeAttr('checked');
          $('#cycleForm').modal('show');
          break;
        case 3:
          if (confirm('{{ __('cycles.sure delete cycle') }}?')) {
            if (confirm('{{ __('cycles.cant undo') }}')) {
              $.ajax({
                type: "POST",
                url: "/dashboard/cycles/modify",
                data: {
                  "cycleid": cycle,
                  "type": 3,
                },
                success: function(response) {
                  // console.log(response);
                  location.href = '/dashboard/cycles';
                },
                error: function(error) {
                  console.log(error);
                  alert("Error Occurred, Try again!");
                }
              });
            }
          }
          break;
        case 4:
          let step = $('#StepSelect').find(":selected").val();
          let steptxt = $('#StepSelect').find(":selected").text();
          let menu = $('#menu' + step);
          $('#menu1,#menu2').hide();
          if (menu !== null) {
            $.ajax({
              url: '{{ url('dashboard/builder/organization/') }}' + '/' + $("#ccycle option:selected").data(
                'organization'),
              success: function(response) {
                $('#FormSelect').empty()
                $('#FormSelect').append(response)
                menu.show();
              },
              error: function(error) {
                console.log(error);
                alert("Error Occurred, Try again!");
              }
            });

          }
          if (step === '5') {
            $.ajax({
              url: '{{ url('dashboard/builder/organization/') }}' + '/' + $("#ccycle option:selected").data(
                'organization'),
              success: function(response) {
                $('#FormSelect').empty()
                $('#FormSelect').append(response)
                $('#menu1').show();
              },
              error: function(error) {
                console.log(error);
                alert("Error Occurred, Try again!");
              }
            });
          }
          $('#stype').val(step);
          $('#formtitle').text(steptxt);
          // var bsRangePickerBasic = $('#bs-rangepicker-basic');
          // if (bsRangePickerBasic.length) {
          //   bsRangePickerBasic.daterangepicker({
          //     opens: isRtl ? 'left' : 'right',
          //   });
     
          $('#stepForm').modal('show');
          break;
      }
    }

    function ModifyStep(set, id) {
      var cycle = $('#ccycle').find(":selected").val();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      switch (set) {
        case 1:
          $.ajax({
            type: "POST",
            url: "/dashboard/cycles/modify",
            data: {
              "type": 7,
              "sid": id,
              "cycleid": 0,
            },
            success: function(response) {
              step = response.stype;
              let menu = $('#mmenu' + step);
              $('#mmenu1,#mmenu2').hide();
              if (response.data !== null) {
                if (step == 1 || step == 5) {
                  $('#mFormSelect').val(response.data);
                }
                if (step == 2) {
                  $('#mScreeningSelect').val(response.data);
                }
              }
              if (menu !== null) {
                $.ajax({
                  url: '{{ url('dashboard/builder/organization/') }}' + '/' + $("#ccycle option:selected")
                    .data('organization'),
                  success: function(response) {
                    $('#mFormSelect').empty()
                    $('#mFormSelect').append(response)
                    menu.show();
                  },
                  error: function(error) {
                    console.log(error);
                    alert("Error Occurred, Try again!");
                  }
                });

              }
              if (step == 5) {
                $.ajax({
                  url: '{{ url('dashboard/builder/organization/') }}' + '/' + $("#ccycle option:selected")
                    .data('organization'),
                  success: function(response) {
                    $('#mFormSelect').empty()
                    $('#mFormSelect').append(response)
                    $('#mmenu1').show();
                  },
                  error: function(error) {
                    console.log(error);
                    alert("Error Occurred, Try again!");
                  }
                });

              }

              $('#sid').val(response.id);
              $('#mtitle').val(response.title);
              $('#mdescription').val(response.description);
              
              $('#step-range-picker').daterangepicker({
                opens: isRtl ? 'left' : 'right',
                drops: 'up',
                startDate: response?.from ? moment(response?.from) : null,
                endDate: response?.to ? moment(response?.to) : null,
              }).on('apply.daterangepicker', function(ev, picker) {
                $('#mfromDate').val(picker.startDate.format('YYYY-MM-DD'));
                $('#mtoDate').val(picker.endDate.format('YYYY-MM-DD'));
              });

              // $('#mfromDate').val(response.from);
              // $('#mtoDate').val(response.to);
              $('#ModifyStepForm').modal('show');
            },
            error: function(error) {
              console.log(error);
              alert("Error Occurred, Try again!");
            }
          });
          break;
      }
    }

    function deleteStep(id) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      if (confirm('{{ __('cycles.sure delete step') }}?')) {
        let badges = document.getElementsByClassName('carr');
        let ids = [];
        for (let a = 0; a < badges.length; ++a) {
          badges[a].innerText = a + 1;
          let id = badges[a].id.split('-');
          ids.push(id[0]);
        }
        $.ajax({
          type: "POST",
          url: "/dashboard/cycles/modify",
          data: {
            "stepid": id,
            "steps": ids,
            "type": 6,
          },
          success: function(response) {
            // console.log(response);
            location.href = '/dashboard/cycles/' + $('#ccycle').find(":selected").val();
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      }
    }
</script>
@stop