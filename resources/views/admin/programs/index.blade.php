@extends('layouts.admin')

@section('style')
  {{-- <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/dragula.min.css"> --}}
  {{-- <link rel="stylesheet" type="text/css" href="/app-assets/css/plugins/extensions/drag-and-drop.css"> --}}
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
  <style>
    .program-card {
      height: auto;
    }

    @media screen and (min-width: 768px) {
      .program-card {
        height: 368px;
      }
    }
  </style>
@stop

@section('content')
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-header row">
        @if (session()->has('message'))
          <div class="alert alert-success alert-dismissible" role="alert">
            {{ __('admin.' . session()->get('message')) }} {{ Session::forget('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
        @if (session()->has('message2'))
          <div class="alert alert-danger alert-dismissible" role="alert">
            {{ __('admin.' . session()->get('message2')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
        <h3 class="content-header-title">{{__('admin.Cycles')}}</h3>
      </div>

      <div class="card mb-6" style="border-radius: 20px;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex">
              <div 
                class="bg-label-primary d-flex justify-content-center align-items-center rounded mx-1"
                style="width: 50px; height: 50px;"
              >
                <i class="ti ti-border-all" style="font-size: 35px; ti-24px"></i>
              </div>
              <div class="mx-4" style="height: 50px;">
                <p class="fw-medium fs-5" style="margin-bottom: -3px;">{{__('admin.Cycles')}}</p>
                <span class="text-primary fs-4">
                  <!-- Total Cycles Count -->
                  {{ count($cycles) }}
                </span>
              </div>
            </div>
            <div class="d-flex align-items-center">
              <!-- Filter -->
              <form method="GET" action="{{ route('programlist.index') }}">
              <div class="d-flex align-items-center mx-2">
                <div style="display: flex; align-items: center;">
                <button
                  class="btn bg-primary p-3 d-none d-md-flex justify-content-center align-items-center" 
                  style="border-radius: 17px; width: 40px; height: 40px;"
                   type="submit"
                >
                  <i class="ti ti-filter text-white ti-24px"></i>
                </button>
              </div>
              <!-- Search Bar -->
              <div class="position-relative me-3 d-none d-md-flex">
                <div class="position-absolute top-50 translate-middle-y search-icon" style="left: 1rem;">
                  <i class="ti ti-search text-primary ti-24px"></i>
                </div>

                <input
                  class="form-control search border rounded mx-1 bg-transparent text-primary px-10"
                  style="width: 366px;"
                  id="search-table" placeholder="Search"
                  name="search"
                  value="{{ request('search') }}" 
                />
              </div>
            </div>
            </form>

              <!-- Add New Program Button -->
              <button
                class="btn btn-primary btn-sm d-sm-inline-block d-md-none" style="border-radius: 15px;" onclick="DoChange(2)">
                  <span class="mx-2">{{__('admin.New Program')}}</span>
                  <i class="menu-icon tf-icons ti ti-components ti-24px"></i>
              </button>
              <button 
                class="btn btn-primary d-none d-md-inline-block"
                style="border-radius: 15px; text-wrap: nowrap;"
                 onclick="DoChange(2)"
              >
                <span class="mx-2">{{__('admin.New Program')}}</span>
                <i class="menu-icon tf-icons ti ti-components ti-24px"></i>
              </button>
            </div>
          </div>
          <!-- Search Bar (Mobile) -->
          <div class="d-flex align-items-center justify-content-between mx-2">
            <div class="position-relative me-3 mt-4 d-block d-md-none">
              <div class="position-absolute top-50 translate-middle-y search-icon" style="left: 1rem;">
                <i class="ti ti-search text-primary ti-24px"></i>
              </div>
              <input
                class="form-control search border rounded mx-1 bg-transparent text-primary px-10"
                style="width: 300px;"
                id="search-table" placeholder="Search"
              />
            </div>
            <button
              class="btn bg-primary p-3 d-flex d-md-none justify-content-center align-items-center mt-3" 
              style="border-radius: 17px; width: 40px; height: 40px;"
            >
              <i class="ti ti-filter text-white ti-24px"></i>
            </button>
          </div>
          <!-- / Search Bar (Mobile) -->
        </div>
      </div>

      <div class="content-body">
        <section class="organizations-list-wrapper">
          {{-- <div class="row pb-1">
            <div class="col-12 col-sm-3 col-lg-3 col-mg-3 offset-sm-9 offset-lg-9 offset-md-9 d-flex align-items-center"
              onclick="DoChange(2)">
              <span class="btn btn-block btn-success align-middle ml-25"><i class="bx bx-star ti-24px"></i>
                {{ __('cycles.New') }}</span>
            </div>

          </div> --}}
          @if (count($cycles) === 0)
            <div class="row d-flex justify-content-center align-items-center">
              <img 
                src="/icons/not-found.svg"
                width="337"
                height="234"
              />
              <h3 class="text-center mt-4">You don’t have any Program</h3>
              <h6 class="text-center text-secondary">Create a program for the success of companies</h6>
              <!-- Add New Program Button -->
              <button class="btn btn-primary btn-lg" style="border-radius: 15px; width: 214px;">
                <span class="mx-2">{{__('admin.New Program')}}</span>
                <i class="menu-icon tf-icons ti ti-components ti-24px"></i>
              </button>
            </div>
          @else
            <section>
              @foreach ($cycles as $cycle)
                <div style="border-radius: 30px;">
                  @php
                    $totalSteps = app(\App\Step::class)
                        ->where('cycle_id', '=', $cycle->id)
                        ->count();

                        $totalStepsFinish = app(\App\Step::class)
                        ->where('to', '>', Carbon\Carbon::now())
                        ->where('cycle_id', '=', $cycle->id)
                        ->count();


                    $totalcompany = app(\App\Company::class)
                        ->where('cycle', '=', $cycle->id)
                        ->count();
                  @endphp
                  @php
                            
                  // $orgid = App/Organization::where('user_id','=', auth()->user()->id)->first();
                  $orgid = App\Organization::where('id','=', $cycle->organization_id)->get();
                  foreach ($orgid as  $org) {
                    $mentortotal = App\User::where('id','=',$org->user_id)->get();
                    $mentor_Count = $mentortotal->count();
                  }
                 
                  @endphp
                  <div class="card mb-6" style="border-radius: 20px;">
                    <div 
                      class="d-flex program-card card-body"
                    >
                      <div 
                        style="width: fit-content; 
                        {{ $l == 'ar' ? 'margin-left: 20px;' : 'margin-right: 20px' }}"
                      >
                      <div 
                        class="border border-light align-items-center justify-content-center d-none d-md-flex h-100"
                        style="width: 180px; height: 180px; border-radius: 20px; overflow: hidden;"
                      >
                      @php
                      $fileN = null;

                       if (auth()->check() && auth()->user()->organization) {
                          $fileN = pathinfo(auth()->user()->organization->image_url, PATHINFO_BASENAME);
                       }
                      @endphp

                        <img 
                            src="{{ asset('dashboard/LFMGet/' . $fileN) }}"
                            alt="Logo"
                            class="img-fluid"
                            style="max-width: 100%; max-height: 100%; object-fit: contain;"
                        />
                      </div>
                      </div>
                      <div class="d-flex flex-column justify-content-between" style="flex: 1;">
                        <!-- Program Title -->
                        <div class="d-flex justify-content-between">
                          <a href="/dashboard/programlist/show/{{$cycle->id}}" class="text-primary fs-5 fw-bold">
                            {{ $cycle->title }}
                          </a>
                          <div 
                            class="badge d-flex align-items-center justify-content-center
                            bg-label-light rounded mx-1 rounded-pill"
                          >
                            <img src="/icons/star.svg" width="20" height="20" />
                            <span style="color: orange" class="fw-bold mt-1 mx-1">4.8</span>
                          </div>
                        </div>  
                        <!-- / Program Title -->
                        <!-- Company Name -->
                        @if(!empty($cycle->private))
                        <a 
                          href="cycles/view/{{ $cycle->private }}"
                          class="d-flex justify-content-between"
                          target="_blank"
                        >
                          <p class="fs-6 mb-2 card-title">
                            {{ $l == 'ar' ? $cycle->organization->name_ar : $cycle->organization->name_en }}
                          </p>
                          <div class="d-flex align-items-center">
                            <span style="font-size: 12px;" class="fw-medium mx-1 menu-item">{{__('cycles.Private Link')}}:</span>
                            <i class="ti ti-rosette-discount-check text-success"></i>
                          </div>
                        </a>
                        @endif
                        <!-- / Company Name -->
                        <!-- Count Row -->
                        <div class="row">
                          <div  style="width: fit-content">
                            <div class="d-flex align-items-center g-1" style="height: 55px;">
                              <div class="bg-label-primary p-2 d-flex align-items-center justify-content-center rounded mx-1">
                                <i class="ti ti-building-skyscraper ti-24px"></i>
                              </div>
                              <a target="_blank" href="/dashboard/cycles/{{$cycle->id}}/registered">
                              <div class="my-auto d-flex">
                                <p class="fw-medium fs-6 my-auto">{{__('admin.Company')}}</p>
                                <span class="badge bg-light rounded-pill text-secondary mx-2 fw-bold">
                                  {{ $totalcompany }}
                                </span>
                              </div>
                              </a>
                            </div>
                          </div>
                          <div  style="width: fit-content">
                            <div class="d-flex align-items-center g-1" style="height: 55px;">
                              
                              <div class="bg-label-info p-2 d-flex align-items-center justify-content-center rounded mx-1">
                                <i class="ti ti-stack-2 ti-24px"></i>
                              </div>
                              <a target="_blank" href="/dashboard/cycles/{{$cycle->id}}">
                              <div class="my-auto d-flex">
                                <p class="fw-medium fs-6 my-auto">{{__('cycles.steps')}}</p>
                                <span class="badge bg-light rounded-pill text-secondary mx-2 fw-bold">
                                  {{ $totalSteps }}
                                </span>
                              </div>
                              </a>
                            </div>
                          </div>
                          <div  style="width: fit-content">
                            <div class="d-flex align-items-center g-1" style="height: 55px;">
                              <div class="bg-label-secondary p-2 d-flex align-items-center justify-content-center rounded mx-1">
                                <i class="ti ti-user ti-24px"></i>
                              </div>
                              <div class="my-auto d-flex">
                                <p class="fw-medium fs-6 my-auto">{{__('cycles.Mentors')}}</p>
                                <span class="badge bg-light rounded-pill text-secondary mx-2 fw-bold">{{$mentor_Count}}</span>
                              </div>
                            </div>
                          </div>
                          <div  style="width: fit-content">
                            <div class="d-flex align-items-center g-1" style="height: 55px;">
                              <div class="bg-label-danger p-2 d-flex align-items-center justify-content-center rounded mx-1">
                                <i class="ti ti-stack-2 ti-24px"></i>
                              </div>
                              <div class="my-auto d-flex">
                                <p class="fw-medium fs-6 my-auto">{{__('admin.rating')}}</p>
                                <span class="badge bg-light rounded-pill text-secondary mx-2 fw-bold">5</span>
                              </div>
                            </div>
                          </div>
                        </div>
                        <!-- / Count Row -->
                        <!-- Mentors Row -->
                        <div 
                          class="d-flex justify-content-between align-items-end w-100 mb-2"
                        >
                          <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                              <i class="ti ti-user-circle text-primary ti-24px"></i>
                              <span class="fs-6 fw-bold mx-2">{{__('admin.Responsible')}}</span>
                            </div>
                            
                            @foreach($mentortotal as $mentor)
                            <div class="d-flex flex-wrap align-items-center px-4" style="width: 100%">
                              
                                <a class="btn btn-sm btn-outline-primary rounded-pill"
                                 style="margin: 2px;" 
                                 target="_blank"
                                 href="{{route('show_mentor_profile', $mentor->id)}}"
                                >
                                @if(!empty($mentor->image_url))
                                  <img src="{{$mentor->image_url}}" width="20" height="20" class="rounded-circle" />
@else
<img src="/icons/avatar.svg" width="20" height="20" class="rounded-circle" />
@endif
                                  <span class="mx-1">{{$mentor->name}}</span>
                                </a>
                             
                            </div>
                            @endforeach
                          </div>
                          <div class="d-none d-md-flex flex-column" style="margin-bottom: -60px;">
                            <span class="fs-6 fw-bold mx-2 mb-2">{{__('admin.Duration')}}</span>
                            <div 
                              class="px-1 py-2 d-flex mb-2"
                              style="background-color: #d9edee; border-radius: 15px;"
                            >
                              <i class="ti ti-calendar-clock ti-28px;" style="color: blue;"></i>
                              <span class="fw-bold text-black mx-2">{{ $cycle->start->format('Y-m-d') }}</span>
                            </div>
                            <div 
                              class="px-1 py-2 d-flex"
                              style="background-color: #d9edee; border-radius: 15px;"
                            >
                              <i class="ti ti-calendar-clock ti-28px;" style="color: blue;"></i>
                              <span class="fw-bold text-black mx-2">{{ $cycle->end->format('Y-m-d') }}</span>
                            </div>
                          </div>
                        </div>
                        <!-- / Mentors Row -->
                        <!-- Program Description -->
                       
                        <div class="d-flex flex-column">
                          <div class="d-flex align-items-center my-2">
                            <i class="ti ti-message text-primary ti-24px"></i>
                            <span class="fs-6 fw-bold mx-2">{{__('admin.Description')}}</span>
                          </div>
                          <div class="d-flex flex-wrap align-items-center px-4" style="width: 85%;">
                            <p class="text-secondary">
                              @if(!empty($cycle->description))
                              {{ $cycle->description }}
                              @else
{{__('cycles.NA')}}
                              @endif
                            </p>
                          </div>
                        </div>
                        <!-- / Program Description -->
                        <!-- Progress Bar -->
                        @php
    // تحقق من أن $totalSteps ليست صفرًا قبل عملية القسمة
    $presSteps = $totalSteps > 0 ? ($totalStepsFinish / $totalSteps) * 100 : 0;
@endphp


                        <div class="d-flex align-items-center">
                          <span class="fw-bold mx-1">{{ number_format($presSteps, 2) }}%</span>
                          <div style="width: 100%; height: 7px;" class="progress rounded-pill">
                            <div 
                              class="progress-bar bg-primary" 
                              role="progressbar" 
                              style="width: {{ number_format($presSteps, 2) }}%;" 
                              aria-valuenow="{{ number_format($presSteps, 2) }}" 
                              aria-valuemin="0" 
                              aria-valuemax="100"
                            ></div>
                          </div>
                          <span class="fw-bold mx-1">{{$totalSteps}}/{{$totalStepsFinish}}</span>
                          <div class="mx-2 position-relative">
                            <div class="dropdown"></div>
                             <button
                                class="bg-transparent border-none" 
                                type="button" id="dropdownMenuButton"   
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical ti-24px"></i>
                              </button>
                              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li>
                                  <button class="dropdown-item ccycle" set="1" do="{{ $cycle->id }}"
                                    onclick="DoChange(1, {{ $cycle->id }})">
                                    <i class="ti ti-pencil me-2"></i>
                                    {{ __('cycles.Edit') }}
                                  </button>
                                </li>
                                <li>
                                  @can('delete_user')
                                    <button class="dropdown-item" onclick="DoChange(3, {{ $cycle->id }})">
                                      <i class="ti ti-trash me-2"></i>
                                      {{ __('cycles.Delete') }}
                                    </button>
                                  @endcan
                                </li>
                              </ul>
                            </div>
                          </div>
                        </di>
                        <!-- / Progress Bar -->
                      </div>
                    {{-- <div
                      class="col-12 col-sm-3 col-lg-3 col-mg-3 offset-sm-9 offset-lg-9 offset-md-9 d-flex align-items-center">
                      <div class="btn btn-success ExportCycle">{{ __('cycles.Export') }}</div>
                    </div> --}}
                    <!-- datatable start -->
                    {{-- <div class="table-responsive">
                      <table id="users-list-datatable" class="table">
                        <thead>
                          <tr class="table-light">
                            <th>#</th>
                            <th>{{ __('cycles.Name') }}</th>
                            <th>{{ __('cycles.Program Start Date') }}</th>
                            <th>{{ __('cycles.Program End Date') }}</th>
                            <th>{{ __('cycles.Private Link') }}</th>
                            <th>{{ __('cycles.Number of joined Startup') }}</th>
                            <th>{{ __('cycles.Numbers of cycles') }}</th>
                            <th>{{ __('cycles.Organization Name') }}</th>
                            <th>{{ __('cycles.Organization logo') }}</th>
    
                            <th>
                              @can('edit_user')
                                {{ __('cycles.Edit') }}
                              @endcan
                            </th>
                            <th>
                              @can('delete_user')
                                {{ __('cycles.Delete') }}
                              @endcan
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($cycles as $cycle)
                            @php
                              $totalSteps = app(\App\Step::class)
                                  ->where('cycle_id', '=', $cycle->id)
                                  ->count();
                              $totalcompany = app(\App\Company::class)
                                  ->where('cycle', '=', $cycle->id)
                                  ->count();
                            @endphp
                            <tr>
                              <td>{{ $cycle->id }}</td>
                              <td style="font-size: small;">{{ $cycle->title }}</td>
                              <td style="font-size: small;">{{ $cycle->start }}</td>
                              <td style="font-size: small;">{{ $cycle->end }}</td>
                              <td style="font-size: small;">
                                @if ($cycle->private == null)
                                  {{ __('cycles.NA') }}
                                @else
                                  <!-- Where to Redirect OR Take Copied ? -->
                                  <a target="_blank" href="cycles/view/{{ $cycle->private }}">اضغط هنا</a>
                                @endif
                              </td>
                              <td style="font-size: small;"><a target="_blank"
                                  href="/dashboard/cycles/{{ $cycle->id }}/registered">
                                  {{ $totalcompany }}</a></td>
                              <td style="font-size: small;">{{ $totalSteps }}</td>
                              @if (app()->getLocale() == 'ar')
                                <td style="font-size: small;">{{ $cycle->organization->name_ar }}</td>
                              @else
                                <td style="font-size: small;">{{ $cycle->organization->name_en }}</td>
                              @endif
                              <td><img src="{{ $cycle->organization->image_url }}" alt="Logo" class=""
                                  height="40"></td>
    
    
    
                              <td style="font-size: small;">
                                <div class="btn btn-primary ccycle" set="1" do="{{ $cycle->id }}"
                                  onclick="DoChange(1, {{ $cycle->id }})">
                                  <span class="align-middle ml-25"><i class="bx bx-edit ti-24px"></i>{{ __('cycles.Edit') }}</span>
                                </div>
                              </td>
    
    
                              <td style="font-size: small;">
                                @can('delete_user')
                                  <div class="btn btn-danger" onclick="DoChange(3, {{ $cycle->id }})">
                                    <span class="align-middle ml-25"><i
                                        class="bx bx-trash ti-24px"></i>{{ __('cycles.Delete') }}</span>
                                  </div>
                                @endcan
                              </td>
    
    
    
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div> --}}
                  </div>
                </div>  
              @endforeach
            </section>
          @endif
        </section>
      </div>
    </div>
  </div>
  <!--Cycle Modal -->
  <div class="modal fade text-left" id="cycleForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <form class="form" action= "{{ URL::to('dashboard/cycles/modify') }}" method="POST"
          enctype="multipart/form-data" autocomplete="off">
          {!! csrf_field() !!}
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel33">{{ __('cycles.Cycle data') }}
            </h4>

            
          </div>
          <div class="modal-body">
            @if (is_null(auth()->user()->organization_id))
            <div class="form-group mb-3">
                <label class="form-label fs-6 mb-1">{{ __('builder.Choose Organization') }}: </label>
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
            <div class="form-group mb-3">
              <label class="form-label fs-6 mb-1">{{ __('cycles.Cycle title') }}: </label>
              <input type="hidden" name="type" id="type">
              <input type="hidden" name="cycleid" id="cycleid">
              <input id='title' name='title' type="text" placeholder="{{ __('cycles.Title') }}"
                class="form-control" required>
            </div>
            <div class="form-group mb-3">
              <label class="form-label fs-6 mb-1">{{ __('cycles.Valid from') }}: </label>
              <input id='start' name='start' type="text" placeholder="{{ now()->format('Y-m-d') }}"
                class="form-control date" required>
            </div>
            <div class="form-group mb-3">
              <label class="form-label fs-6 mb-1">{{ __('cycles.Valid to') }}: </label>
              <input id='end' name='end' type="text"
                placeholder="{{ now()->addDays(15)->format('Y-m-d') }}" class="form-control date" required>
            </div>
            <div class="form-group mb-3 mb-0">
              <label class="form-label fs-6 mb-1">{{ __('cycles.Description') }}: </label>
              <textarea id='description' name='description' placeholder="{{ __('cycles.Description') }}" class="form-control"></textarea>
            </div>
            <div class="form-group mb-3 mb-0 custom-switch">
              <label class="form-label fs-6 mb-1"> </label>
              <span>{{ __('settings.Private') }}</span>&nbsp;
              <input name="private" type="checkbox" class="custom-control-input" id="customSwitch3">
              <label class="custom-control-label mr-1" for="customSwitch3"></label>
              <a id="cyclelink" target="_blank"></a>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              <i class="bx bx-x d-block d-sm-none ti-24px"></i>
              <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
            </button>
            <button type="submit" value="submit" class="btn btn-primary ml-1">
              <i class="bx bx-check d-block d-sm-none ti-24px"></i>
              <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

@stop

@section('pagescripts')
  @php($ccycle = \App\Cycle::find(0))
  {{-- <script src="/app-assets/js/scripts/pages/page-users.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/app-assets/js/scripts/datatables/datatable.js"></script>
  <script src="/app-assets/js/scripts/modal/components-modal.js"></script>

  <script src="/app-assets/vendors/js/extensions/dragula.min.js"></script> --}}
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <script>
    $('.date').datetimepicker({
      timepicker: false,
      format: 'Y-m-d'
    });
    $('#fromDate,#toDate,#mfromDate,#mtoDate').datetimepicker({
      format: 'Y-m-d',
      minDate: '{{ !is_null($ccycle) ? $ccycle->start->format('Y/m/d') : now()->format('Y/m/d') }}',
      maxDate: '{{ !is_null($ccycle) ? $ccycle->end->format('Y/m/d') : now()->addDays(15)->format('Y/m/d') }}',
      timepicker: false
    });
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
            console.log(response);
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, arrange failed");
          }
        });
      })
    });

    const messages = {
      exportSuccess: "{{ __('cycles.export_willsuccess') }}",
    };

    function DoChange(set, cycle) {

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
              $('#start').val(response.start);
              $('#end').val(response.end);
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
          $('#start').val('');
          $('#end').val('');
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
                  console.log(response);
                  location.href = '/dashboard/programlist';
                  // location.reload();
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
          $('#stepForm').modal('show');
          break;
      }
    }

    function ModifyStep(set, id) {
      var cycle = $('.ccycle').attr('do');
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
              $('#mfromDate').val(response.from);
              $('#mtoDate').val(response.to);
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
            console.log(response);
            // location.href = '/dashboard/cycles/'+$('#ccycle').find(":selected").val();
            location.reload();
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
