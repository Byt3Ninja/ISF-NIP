@extends('layouts.admin')

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/dragula.min.css">
<link rel="stylesheet" type="text/css" href="/app-assets/css/plugins/extensions/drag-and-drop.css">
<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<style>

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
<div class="">
  <div class="">
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
        @if (session()->has('failure'))
        <div class="alert alert-danger alert-dismissible" role="alert">
          {{ session()->get('failure') }}
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
                @php
                  $selectedCycle = \App\Cycle::find($id);
                @endphp
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
                        @if ($selectedCycle && $selectedCycle->type === 'Hackathon' && $_user->hasAnyRole(['Admin', 'organizationManager']))
                            <option value="6">{{ __('admin.Team Formation') }}</option>
                        @endif
                        {{-- <option value="7">{{ __('admin.Almuni') }}</option> --}}
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
                          <a href="javascript:void(0)" onclick="ModifyStep(1,{{ $step->id }},{{ \App\Formdata::where('step_id', $step->id)->distinct('company_id')->count('company_id') }})" id="{{ $step->id }}"
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
                                  $link = 'screening/' . $step->id;
                                  $set = 'screening/' . $step->id;
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
                                    @if($step->stype != 6)
                           <a href="/dashboard/{{ $set }}" id="{{ $step->id }}">
                            <span class="btn btn-icon btn-label-warning rounded-circle">
                                <i class="ti ti-settings" title="{{ __('cycles.Step settings') }}"></i>
                            </span>
                        </a>
                        @endif
                        @if($step->stype == 1 && $step->form)
                        <a href="{{ route('forms.render_view', $step->form->id) }}" id="{{ $step->id }}" class="mx-1">
                          <span class="btn btn-icon btn-label-primary rounded-circle">
                            <i class="ti ti-eye" title="{{ __('cycles.view step results') }}"></i>
                          </span>
                        </a>
                        @if( !empty( $step->join_url ) && !is_null( $step->join_url ) )
                        <a href="javascript:void(0)" class="mx-1 copy-public-link" data-clipboard="{{ $step->join_url }}">
                          <span class="btn btn-icon btn-label-dark rounded-circle">
                            <i class="ti ti-copy" title="{{ __('cycles.copy public link') }}"></i>
                          </span>
                        </a>
                        @endif
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
      <div class="row">
        <div class="col-12">
          <div class="card card-body h-100 d-flex justify-content-center align-items-center">
            <div class="d-flex flex-column align-items-center">
              <img src="/icons/not-found.svg" width="250" height="250" alt="No Result">
              <h6 class="text-center text-secondary w-75">
                {{ __('admin.No_Programs_Message') }}
              </h6>
              <div class="text-center">
                <button onclick="DoChange(2)" class="btn btn-primary">
                  <i class="ti ti-plus ms-2"></i>
                  <span class="mx-1">{{ __('admin.Add_New') }}</span>
                </button>
              </div>
            </div>
          </div>
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
          @php
            $authUser = auth()->user();
            $userOrgIds = $authUser->organizations_ids;
            $showOrgDropdown = $authUser->hasRole('admin') || ($authUser->isSubAdmin() && count($userOrgIds) > 1);
          @endphp
          @if ($showOrgDropdown)
          <div class="form-group mb-4">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('builder.Choose Organization') }}: </label>
            <select class="select form-control" id="organization_id" name="organization_id" required>
              @if($authUser->hasRole('admin'))
                @foreach (\App\Organization::all() as $org)
                <option value="{{ $org->id }}">{{ $org->name }}</option>
                @endforeach
              @else
                @foreach ($authUser->organizations as $org)
                <option value="{{ $org->id }}">{{ $org->name }}</option>
                @endforeach
              @endif
            </select>
          </div>
          @else
          <input type="hidden" name="organization_id" value="{{ $userOrgIds[0] ?? '' }}"
            id="organization_id">
          @endif
          <div class="col-12 mb-4">
            <label for="bs-rangepicker-basic" class="form-label fs-6 fw-semibold mb-1">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
            <input id='start' name='start' type="hidden" />
            <input id='end' name='end' type="hidden" />
            <input type="text" id="bs-rangepicker-basic" class="form-control" name="date_range"
              placeholder="YYYY-MM-DD - YYYY-MM-DD" />
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
      <form id="addStepForm" class="form" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data"
        autocomplete="off"
        {{-- Native validation dies with "invalid form control ... is not focusable"
             when a required field sits behind a hidden menu; validateStepForm()
             owns every rule instead. --}}
        novalidate
        {{-- onsubmit="disableSubmitButton()" --}}
        >
        {!! csrf_field() !!}
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel33">{{ __('cycles.New step data') }} - <span id="formtitle">{{
              __('cycles.Custom Form') }}</span></h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="cid" id="cid" value="{{ $id }}">
          <input type="hidden" name="type" id="type5" value="{{ \App\Enums\CycleModifyAction::ADD_STEP }}">
          <input type="hidden" name="arr" id="sarr" value="{{ isset($step) ? $step->arr + 1 : 1 }}">
          <input type="hidden" name="stype" id="stype" value="1">
          <div class="mb-4" id="menu1">
            <div class="form-group mb-0">
              <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Choose form') }}: </label>
              @if( $_user->ableTo('forms.builder_view', $permissions) )
              <span><a href="{{ route('forms.builder_view') }}" target="_blank">{{ __('admin.Create_form') }}</a></span>
              @endif
              <select class="select form-control" name="FormSelect" id="FormSelect">
                {{-- @foreach (\App\BuilderForm::all() as $form) --}}
                {{-- <option value="{{$form->id}}">{{$form->form_name}}</option> --}}
                {{-- @endforeach --}}
              </select>
            </div>
          </div>
          {{--  <div class="mb-4" id="menu2">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Choose screening type') }}: </label>
            <div class="form-group mb-0">
              <select class="form-select" name="ScreeningSelect">
                <option selected disabled> {{ __('cycles.Select type') }}</option>
                <option value="onetoall">{{ __('cycles.Assign One to all users') }}</option>
                <option value="grouptoall">{{ __('cycles.Assign Group to all users') }}</option>
                <option value="grouptogroup">{{ __('cycles.Assign Group to group of users') }}</option>
              </select>
            </div>
          </div>  --}}
          <div class="col-12 mb-4">
            <label for="stepFormRamgePicker" class="form-label fs-6 fw-semibold mb-1">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
            <input type="hidden" id="fromDate" name="fromDate" />
            <input type="hidden" id="toDate" name="toDate" />
            <input type="text" id="stepFormRamgePicker" class="form-control" name="date_range"
              placeholder="YYYY-MM-DD - YYYY-MM-DD" required />
          </div>
          <div class="form-group mb-4">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Step Title') }}: </label>
            <input id='title' name='title' type="text" placeholder="{{ __('cycles.Title') }}" class="form-control"
              >
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

          <div class="form-group mb-4" id="team_number">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('admin.maxInTeam') }}: </label>
             <input 
                id='count_team_number' 
                name='count_team_number' 
                type="number" 
                placeholder="{{ __('admin.maxInTeam') }}" 
                class="form-control"
              >
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
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form class="form" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data"
        autocomplete="off" id="editStepForm" novalidate>
        {!! csrf_field() !!}
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel33">{{ __('cycles.Modify current step') }}</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="sid" id="sid">
          <input type="hidden" name="confirm_form_change" id="confirm_form_change" value="0">
          <input type="hidden" name="type" id="type7" value="{{ \App\Enums\CycleModifyAction::UPDATE_STEP }}">
          <input type="hidden" name="stype" id="stype" value="1">
          <div class="mb-2" id="mmenu1">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Choose form') }}: </label>
            <div class="form-group mb-4">
              <select class="select form-control" id="mFormSelect" name="mFormSelect">
                {{-- @foreach (\App\BuilderForm::all() as $form) --}}
                {{-- <option value="{{$form->id}}">{{$form->form_name}}</option> --}}
                {{-- @endforeach --}}
              </select>
            </div>
          </div>
          <div class="form-group mb-4 public-data-form-fields">
            <label class="form-label" for="is_public">{{ __('cycles.Step Visibility') }}: </label>
            <select class="select form-control" id="is_public" name="is_public">
              <option value="0">{{ __('cycles.Step Visible To Private') }}</option>
              <option value="1">{{ __('cycles.Step Visible To Public') }}</option>
            </select>
          </div>
          <div class="form-group mb-4 public-data-form-fields" id="public_slug_div">
            <label class="form-label" for="public_slug">{{ __('cycles.Public URL') }}: </label>
            <input type="text" id="public_slug" name="public_slug" class="form-control" value="" placeholder="{{ __('cycles.Public URL placeholder') }}" >
          </div>
          {{--  <div class="mb-4" id="mmenu2">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Choose screening type') }}: </label>
            <div>
              <select class="form-select" id="mScreeningSelect" name="mScreeningSelect" required>
                <option selected disabled> {{ __('cycles.Select type') }}</option>
                <option value="onetoall">{{ __('cycles.Assign One to all users') }}</option>
                <option value="grouptoall">{{ __('cycles.Assign Group to all users') }}</option>
                <option value="grouptogroup">{{ __('cycles.Assign Group to group of users') }}</option>
              </select>
            </div>
          </div>  --}}
          <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Form title') }}: </label>
          <div class="mb-4">
            <input id='mtitle' name='title' type="text" placeholder="{{ __('cycles.Title') }}" class="form-control"
              >
          </div>
          <div class="col-12 mb-4">
            <label for="step-range-picker" class="form-label fs-6 fw-semibold mb-1">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
            <input type="hidden" id="mfromDate" name="fromDate" />
            <input type="hidden" id="mtoDate" name="toDate" />
            <input type="text" id="step-range-picker" class="form-control" name="date_range"
              placeholder="YYYY-MM-DD - YYYY-MM-DD" required />
          </div>
          {{-- <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Valid from') }}: </label>
          <div class="mb-4">
            <input id='mfromDate' name='fromDate' type="text" class="form-control" required readonly>
          </div>
          <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Valid to') }}: </label>
          <div class="mb-4">
            <input id='mtoDate' name='toDate' type="text" class="form-control" required readonly>
          </div> --}}
          <div class="form-group mb-4">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Description') }}: </label>
            <textarea id='mdescription' name='description' placeholder="{{ __('cycles.Description') }}"
              class="form-control"></textarea>
          </div>

          <div class="form-group mb-4" id="mteam_number" style="display: none;">
            <label class="form-label fs-6 fw-semibold mb-1">{{ __('admin.maxInTeam') }}: </label>
             <input id='mcount_team_number' name='count_team_number' type="number" min="0" placeholder="{{ __('admin.maxInTeam') }}" class="form-control">
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
@stop


@section('pagescripts')
@php($ccycle = \App\Cycle::find($id))
<script src="/vuexy/assets/js/forms-selects.js"></script>
<script src="/app-assets/vendors/js/extensions/dragula.min.js"></script>
<script src="/js/jquery.datetimepicker.full.min.js"></script>

<script>
  const currentLang = '{{ $l }}';
  var minimumMembers = 1; // Default minimum members
  const errors = {
    title: currentLang == 'en' ? 'Step title is required.' : 'عنوان الخطوة مطلوب.',
    description: currentLang == 'en' ? 'Step description is required.' : 'وصف الخطوة مطلوب.',
    descriptionMaxLength: currentLang == 'en' ? 'Description must not exceed 100 characters.' : 'يجب ألا يتجاوز الوصف 100 حرف.', 
    teamNumber: currentLang == 'en' ? 'Team number must be at least 1.' : 'عدد الفرق يجب أن يكون على الأقل 1.',
    dateRange: currentLang == 'en' ? 'Start and end dates are required.' : 'تاريخ البداية والنهاية مطلوب.',
    formSelect: currentLang == 'en' ? 'Please select a form.' : 'يرجى اختيار النموذج.'
  };

 function validateStepForm(form, mode = 'add') {
  const stepType = form.querySelector('#stype');
  const stepTitle = form.querySelector(`${mode === 'add' ? '#title' : '#mtitle'}`);
  const stepDescription = form.querySelector(`${mode === 'add' ? '#description' : '#mdescription'}`);
  const teamNumberInput = form.querySelector(`${mode === 'add' ? '#count_team_number' : '#mcount_team_number'}`);
  const formSelect = form.querySelector(`${mode === 'add' ? '#FormSelect' : '#mFormSelect'}`);
  const screeningSelect = form.querySelector(`${mode === 'add' ? '#ScreeningSelect' : '#mScreeningSelect'}`);
  const dateRange = form.querySelector('[name="date_range"]');

  // Remove any previous error messages
  const existingErrors = form.querySelectorAll('.invalid-feedback');
  existingErrors.forEach(error => error.remove());

  // Helper function to show error
  const showError = (element, message) => {
    element.focus();
    element.classList.add('is-invalid');

    const errorSpan = document.createElement('span');
    errorSpan.classList.add('invalid-feedback');
    errorSpan.textContent = message;

    element.insertAdjacentElement('afterend', errorSpan);
  };

  // A control behind a hidden menu is not focusable, so the browser can neither
  // report nor clear an error on it. Only ever validate what is on screen.
  const isShown = (element) => !!element && element.offsetParent !== null;

  // Validation checks
  if (!stepTitle.value.trim()) {
    showError(stepTitle, errors.title);
    return false;
  }

  if (!stepDescription.value.trim()) {
    showError(stepDescription, errors.description);
    return false;
  }

  if (stepDescription.value.length > 100) {
    showError(stepDescription, errors.descriptionMaxLength);
    return false;
  }

  if (isShown(dateRange) && !dateRange.value.trim()) {
    showError(dateRange, errors.dateRange);
    return false;
  }

  // The form picker is required only while it is visible (add flow sets the
  // attribute once #menu1 is shown); the edit flow allows "— No form —".
  if (isShown(formSelect) && formSelect.required && !formSelect.value) {
    showError(formSelect, errors.formSelect);
    return false;
  }

  // Check for conditional required fields based on step type
  // The edit flow may intentionally have no form ("— No form —"); the add flow
  // requires one only while its picker is on screen (checked above).

  /* if (stepType.value === '2' && screeningSelect && screeningSelect.hasAttribute('required') && !screeningSelect.value) {
    showError(screeningSelect, currentLang == 'en' ? 'Please select a screening type.' : 'يرجى اختيار نوع الفحص.');
    return false;
  } */

  if (stepType.value === '6' && teamNumberInput && (!teamNumberInput.value || teamNumberInput.value < minimumMembers)) {
    showError(teamNumberInput, errors.teamNumber);
    return false;
  }

  // Clear any invalid styling
  [stepTitle, stepDescription, teamNumberInput, formSelect, screeningSelect, dateRange].forEach(input => {
    if (input) input.classList.remove('is-invalid');
  });

  return true; // All validations passed
}

function disableFormWithLoading(form) {
  const submitBtn = form.querySelector('button[type="submit"]');
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
  }
}

const addStepForm = document.querySelector('#addStepForm');
const editStepForm = document.querySelector('#editStepForm');

if (addStepForm) {
  addStepForm.addEventListener('submit', function (event) {
    event.preventDefault();
    if (validateStepForm(addStepForm, 'add')) {
      disableFormWithLoading(addStepForm);
      addStepForm.submit();
    }
  });
}

if (editStepForm) {
  // Tracks the form value the dialog was opened with, so we only warn when the
  // manager actually changed the selected form.
  let editStepOriginalFormId = null;
  $('#ModifyStepForm').on('shown.bs.modal', function () {
    editStepOriginalFormId = $('#mFormSelect').val();
    $('#confirm_form_change').val('0');
  });

  editStepForm.addEventListener('submit', function (event) {
    event.preventDefault();
    if (!validateStepForm(editStepForm, 'edit')) {
      return;
    }

    const mFormSelect = document.getElementById('mFormSelect');
    const stepHasSubmissions = $('#ModifyStepForm').data('has-submissions') ? Number($('#ModifyStepForm').data('has-submissions')) : 0;
    const formChanged = mFormSelect && editStepOriginalFormId !== null && mFormSelect.value !== editStepOriginalFormId;

    if (formChanged && stepHasSubmissions > 0 && $('#confirm_form_change').val() !== '1') {
      releaseAlert('warning', {
        title: '{{ __("admin.step_form_change_warning_title") }}',
        message: '{{ __("admin.step_form_change_confirm") }}'.replace(':count', stepHasSubmissions),
        confirmText: '{{ __("admin.Yes") }}',
        cancelText: '{{ __("admin.cancel") }}',
        confirmColor: 'danger',
        onConfirm: function () {
          $('#confirm_form_change').val('1');
          disableFormWithLoading(editStepForm);
          editStepForm.submit();
        }
      });
      return;
    }

    disableFormWithLoading(editStepForm);
    editStepForm.submit();
  });
}

function clearFieldError(input) {
  if (!input) return;
  input.classList.remove('is-invalid');

  // Remove the next sibling if it's an error message
  const next = input.nextElementSibling;
  if (next && next.classList.contains('invalid-feedback')) {
    next.remove();
  }
}

function clearValidationOnChange(form, mode = 'add') {
  const fields = [
    `${mode === 'add' ? '#title' : '#mtitle'}`,
    `${mode === 'add' ? '#description' : '#mdescription'}`,
    `${mode === 'add' ? '#count_team_number' : '#mcount_team_number'}`,
    `${mode === 'add' ? '#FormSelect' : '#mFormSelect'}`,
    `${mode === 'add' ? '#ScreeningSelect' : '#mScreeningSelect'}`,
    '[name="date_range"]'
  ];

  fields.forEach(selector => {
    const input = form.querySelector(selector);
    if (!input) return;

    input.addEventListener('input', () => clearFieldError(input));

    // Also listen for change events on select elements
    if (input.tagName === 'SELECT') {
      input.addEventListener('change', () => clearFieldError(input));
    }
  });
}

if (addStepForm) {
  clearValidationOnChange(addStepForm, 'add');
}

if (editStepForm) {
  clearValidationOnChange(editStepForm, 'edit');
}
</script>

<script>

  const CYCLE_MODIFY_ACTION = {
    EDIT_CYCLE: '{{ \App\Enums\CycleModifyAction::EDIT_CYCLE }}',
    SAVE_CYCLE_NOOP: '{{ \App\Enums\CycleModifyAction::SAVE_CYCLE_NOOP }}',
    DELETE_CYCLE: '{{ \App\Enums\CycleModifyAction::DELETE_CYCLE }}',
    ARRANGE_STEPS: '{{ \App\Enums\CycleModifyAction::ARRANGE_STEPS }}',
    DELETE_STEP: '{{ \App\Enums\CycleModifyAction::DELETE_STEP }}',
    FETCH_STEP: '{{ \App\Enums\CycleModifyAction::FETCH_STEP }}',
  };

  // $('#count_team_number, #mcount_team_number').on('input', function () {
  //   let val = $(this).val();
  //   if (val < 1) {
  //     $(this).val(1);
  //   }
  // });

//   function disableSubmitButton() {
//     document.getElementById("submitBtn").disabled = true;
// }
    // console.log('loaded');
    // Every date range on this page is a flatpickr range picker writing the picked
    // range into a visible "date_range" field plus the two hidden fields the
    // controller reads (start/end for a program, fromDate/toDate for a step).
    // The visible value must stay "m/d/Y - m/d/Y" because
    // CyclesController::modify() explodes on ' - ' and parses each part as m/d/Y.
    const RANGE_PICKER_CONFIG = {
      mode: 'range',
      monthSelectorType: 'static',
      dateFormat: 'm/d/Y',
      // flatpickr's Arabic locale uses " إلى " as the range separator.
      locale: { rangeSeparator: ' - ' },
    };

    function syncRangeToHiddenFields(picker, fromSelector, toSelector) {
      // flatpickr writes the visible value itself and never fires an input event,
      // so drop any "dates required" error the last submit attempt left behind.
      clearFieldError(picker.input);
      const dates = picker.selectedDates;
      if (dates.length < 2) return;
      $(fromSelector).val(picker.formatDate(dates[0], 'Y-m-d'));
      $(toSelector).val(picker.formatDate(dates[1], 'Y-m-d'));
    }

    // flatpickr replaces the instance when the input is already picked, so calling
    // this again (as the edit flows do) hands back a usable instance for the input.
    function initRangePicker(selector, fromSelector, toSelector) {
      const input = document.querySelector(selector);
      if (!input) return null;

      return flatpickr(input, Object.assign({}, RANGE_PICKER_CONFIG, {
        onChange: function (selectedDates, dateStr, instance) {
          syncRangeToHiddenFields(instance, fromSelector, toSelector);
        },
        onClose: function (selectedDates, dateStr, instance) {
          syncRangeToHiddenFields(instance, fromSelector, toSelector);
        }
      }));
    }

    // Model dates arrive as "Y-m-d"; build a local-midnight Date so the calendar
    // shows the stored day instead of shifting it by the browser timezone.
    function toLocalDate(value) {
      if (!value) return null;
      const parts = String(value).slice(0, 10).split('-');
      if (parts.length !== 3) return null;
      return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    }

    function setRangePickerValue(picker, fromValue, toValue, fromSelector, toSelector) {
      if (!picker) return;

      const from = toLocalDate(fromValue);
      const to = toLocalDate(toValue);

      if (from && to) {
        picker.setDate([from, to], false);
      } else if (from || to) {
        picker.setDate(from || to, false);
      } else {
        picker.clear();
        $(fromSelector).val('');
        $(toSelector).val('');
        return;
      }

      syncRangeToHiddenFields(picker, fromSelector, toSelector);
    }

    initRangePicker('#stepFormRamgePicker', '#fromDate', '#toDate');

    // $('.date').datetimepicker({
    //   timepicker: false,
    //   format: 'Y-m-d'
    // });
    // $('#fromDate,#toDate,#mfromDate,#mtoDate').datetimepicker({
    //   format: 'Y-m-d',
    //   minDate: '{{ !is_null($ccycle) ? $ccycle->start ? $ccycle->start->format('Y/m/d') : null : now()->format('Y/m/d') }}',
    //   maxDate: '{{ !is_null($ccycle) ? $ccycle->end ? $ccycle->end->format('Y/m/d') : null : now()->addDays(15)->format('Y/m/d') }}',
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
            "type": CYCLE_MODIFY_ACTION.ARRANGE_STEPS,
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
              $('#type').val(CYCLE_MODIFY_ACTION.EDIT_CYCLE);

              const cycleRangePicker = initRangePicker('#bs-rangepicker-basic', '#start', '#end');
              setRangePickerValue(cycleRangePicker, response.start, response.end, '#start', '#end');

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
          $('#type').val(CYCLE_MODIFY_ACTION.SAVE_CYCLE_NOOP);

          const newCycleRangePicker = initRangePicker('#bs-rangepicker-basic', '#start', '#end');
          setRangePickerValue(newCycleRangePicker, null, null, '#start', '#end');

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
                  "type": CYCLE_MODIFY_ACTION.DELETE_CYCLE,
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
          $('#menu1,#menu2,#team_number').hide();
          
          // Remove required attributes from all fields first
          $('#FormSelect').removeAttr('required');
          $('#ScreeningSelect').removeAttr('required');
          
          if(step === '6'){
            $('#team_number').show();
          }
          // jQuery returns an empty set (never null) for #menu2/#menu3/#menu4/#menu6,
          // which don't exist any more. Only require FormSelect when its picker is
          // actually on screen, otherwise native validation blocks the submit with
          // "An invalid form control with name='FormSelect' is not focusable."
          if (menu.length) {
            $.ajax({
              url: '{{ url('dashboard/builder/organization/') }}' + '/' + $("#ccycle option:selected").data(
                'organization'),
              success: function(response) {
                $('#FormSelect').empty()
                $('#FormSelect').append(response.appear)
                menu.show();
                // Add required attribute only once the picker is visible
                $('#FormSelect').attr('required', 'required');
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
                $('#FormSelect').append(response.appear)
                $('#menu1').show();
                // Add required attribute for step 5 once the picker is visible
                $('#FormSelect').attr('required', 'required');
              },
              error: function(error) {
                console.log(error);
                alert("Error Occurred, Try again!");
              }
            });
          }
          if (step === '2') {
            // Add required attribute for screening select
            $('#ScreeningSelect').attr('required', 'required');
            $('#menu2').show();
          }
          $('#stype').val(step);
          $('#formtitle').text(steptxt);
          $('#stepForm').modal('show');
          break;
      }
    }

    function ModifyStep(set, id, submissionsCount) {
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
              "type": CYCLE_MODIFY_ACTION.FETCH_STEP,
              "sid": id,
              "cycleid": 0,
            },
            success: function(response) {
              step = response.stype;
              sData = response.data;
              console.log(step);
              $('#editStepForm #stype').val(step);
              let menu = $('#mmenu' + step);
              $('#mmenu1,#mmenu2').hide();
              $('.public-data-form-fields').hide();
              if( step == 6 )
              {
                minimumMembers = response.minimumMembers;
                var minimumMembers = 1; // Default minimum members
                errors.teamNumber = currentLang == 'en' ? 'Team number must be at least ' + minimumMembers + '.' : 'عدد الفرق يجب أن يكون على الأقل ' + minimumMembers + '.';

                $('#mcount_team_number').attr('min', response.minimumMembers);
                $('#mteam_number').show();
              }
              else { $('#mteam_number').hide(); }

              if( response.data !== null )
              {
                if( step == 1 )
                {
                  $('.public-data-form-fields').show();
                  $('#is_public').val(response.is_public);
                  $('#public_slug').val(response.public_slug);
                  if( response.is_public == 1 ) { $('#public_slug_div').show(); } else { $('#public_slug_div').hide(); }
                }
                if( step == 1 || step == 5 ) { $('#mFormSelect').val(response.data); }
                if( step == 2 ) { $('#mScreeningSelect').val(response.data); }
              }
              if (menu !== null) {
                $.ajax({
                  url: '{{ url('dashboard/builder/organization/') }}' + '/' + $("#ccycle option:selected")
                    .data('organization'),
                  success: function(response) {
                    const $mFormSelect = $('#mFormSelect').empty();
                    $mFormSelect.append($('<option>').val('').text('{{ __('admin.no_form_option') }}'));
                    let appends = '';
                    response.data.forEach(function(form) {
                      appends += `<option value="${form.id}" ${form.id == sData ? 'selected' : ''}>${form.form_name}</option>`;
                    });
                    $mFormSelect.append(appends);
                    if (!sData) { $mFormSelect.val(''); }
                    $mFormSelect.trigger('change.select2');
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
                    const $mFormSelect = $('#mFormSelect').empty();
                    $mFormSelect.append($('<option>').val('').text('{{ __('admin.no_form_option') }}'));
                    let appends = '';
                    response.data.forEach(function(form) {
                      appends += `<option value="${form.id}" ${form.id == sData ? 'selected' : ''}>${form.form_name}</option>`;
                    });
                    $mFormSelect.append(appends);
                    if (!sData) { $mFormSelect.val(''); }
                    $mFormSelect.trigger('change.select2');
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
              $('#mcount_team_number').val(response.count_team_number);
              
              const stepRangePicker = initRangePicker('#step-range-picker', '#mfromDate', '#mtoDate');
              setRangePickerValue(stepRangePicker, response?.from, response?.to, '#mfromDate', '#mtoDate');

              // $('#mfromDate').val(response.from);
              // $('#mtoDate').val(response.to);
              $('#ModifyStepForm').attr('data-has-submissions', submissionsCount || 0);
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
            "type": CYCLE_MODIFY_ACTION.DELETE_STEP,
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

    $(document).on('change', '#is_public', function() {
      let _public = $(this).val();
      if( _public == 1 ) { $('#public_slug_div').show(); } else { $('#public_slug_div').hide(); }
    });

    $(document).on('click', '.copy-public-link', function(e) {
      e.preventDefault();
      let url = $(this).data('clipboard');
      const $tempInput = $("<input>");
      $("body").append($tempInput);
      $tempInput.val(url).select();
      try {
        const success = document.execCommand("copy");
        if (success) {
          alert("Public Link Copied!");
        } else {
          console.warn("Copy command failed.");
        }
      }
      catch (err) {
        console.error("Copy error: ", err);
      }
      $tempInput.remove();
    });
</script>
@stop
