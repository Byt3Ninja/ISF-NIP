@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/tagify/tagify.css" />
<link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify.min.css">
<link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify-bootstrap.min.css">

<style>
  input[type="file"]::file-selector-button {
    background-color: #FFFF;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    border: 1px solid lightgray;
  }

  input[type="file"]::file-selector-button:hover {
    background-color: lightgray;
  }

  .program-card {
    height: auto;
  }

  .line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  @media screen and (min-width: 768px) {
    .program-card {
      height: auto;
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
        {{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if ($errors->any())
      <div class="alert alert-danger alert-dismissible" role="alert">
        @foreach ($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
      </div>
      @endif
      {{-- <h3 class="content-header-title">{{ __('admin.Cycles') }}</h3> --}}
    </div>

    <div class="card mb-6" style="border-radius: 20px;">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex">
            <div class="bg-label-primary d-flex justify-content-center align-items-center rounded mx-1"
              style="width: 50px; height: 50px;">
              <i class="ti ti-border-all" style="font-size: 35px; ti-24px"></i>
            </div>
            <div class="mx-4" style="height: 50px;">
              <p class="fw-medium fs-5" style="margin-bottom: -3px;">{{ __('admin.Cycles') }}</p>
              <span class="text-primary fs-4">
                <!-- Total Cycles Count -->
                {{ count($cycles) }}
              </span>
            </div>
          </div>
          <div class="d-flex align-items-center">
            <!-- Add New Program Button -->
            <a class="btn btn-primary btn-sm d-sm-inline-block d-md-none" style="border-radius: 15px;"
              href="/dashboard/programlist/create">
              <i class="ti ti-plus"></i>
            </a>
            <a class="btn btn-primary d-none d-md-inline-block" style="border-radius: 15px; text-wrap: nowrap;"
              href="/dashboard/programlist/create">
              <span>{{ __('admin.New Program') }}</span>
              <i class="ti ti-plus ms-2"></i>
            </a>
          </div>
        </div>
      </div>
    </div>

    @include('admin.programs.partials._filter_programs')

    <div class="content-body">
      <section class="organizations-list-wrapper">
        @if (count($cycles) === 0)
        <div class="row d-flex justify-content-center align-items-center">
          <img src="/icons/not-found.svg" width="337" height="234" />
          <h3 class="text-center mt-4">{{ __('admin.No_Cycles') }}</h3>
          <h6 class="text-center text-secondary">{{ __('admin.No_Cycles_Description') }}</h6>
          <!-- Add New Program Button -->
          <a id="add-btn-none" class="btn btn-primary" style="border-radius: 15px; width: 180px;"
            href="/dashboard/programlist/create">
            <span class="mx-2">{{ __('admin.New Program') }}</span>
            <i class="ti ti-plus"></i>
          </a>

          @if (request()->has('organization_id'))
          <button id="add-btn-none" class="btn btn-light ms-4" style="border-radius: 15px; width: 150px;"
            onclick="window.location.href='/dashboard/programlist'">
            <span class="mx-2">{{ __('admin.Reset') }}</span>
            <i class="ti ti-reload"></i>
          </button>
          @endif
        </div>
        @else
        <section id="program-list">
          @foreach ($cycles as $cycle)
          <div style="border-radius: 30px; height: fit-content;">
            @php
            $totalSteps = app(\App\Step::class)->where('cycle_id', '=', $cycle->id)->count();

            $totalStepsFinish = app(\App\Step::class)
            ->where('to', '>', Carbon\Carbon::now())
            ->where('cycle_id', '=', $cycle->id)
            ->count();

            $totalcompany = app(\App\Company::class)->where('cycle', '=', $cycle->id)->count();
            @endphp
            @php

            // $orgid = App/Organization::where('user_id','=', auth()->user()->id)->first();
            $orgid = App\Organization::where('id', '=', $cycle->organization_id)->get();
            $mentors = $cycle->mentors()->distinct()->get();
            $mentor_Count = $mentors->count();

            @endphp
            <div class="card mb-6" style="border-radius: 20px;">
              <div class="d-flex program-card py-4 py-md-5 px-0 px-md-4">
                <div class="position-relative" style="width: fit-content; 
                        {{ $l == 'ar' ? 'margin-left: 20px;' : 'margin-right: 20px' }}">
                  <div class="position-absolute top-0 start-0 d-none d-md-block">
                    <div class="badge bg-{{ $cycle->type == 'General_Program' ? 'success' : 'warning  ' }} rounded-lg p-2">
                      {{ __('cycles.' . $cycle->type) }}
                    </div>
                  </div>
                  <div class="border border-light align-items-center justify-content-center d-none d-md-flex h-100"
                    style="width: 180px; height: 180px; border-radius: 20px; overflow: hidden;">

                    {{-- <img src="{{ Storage::disk('public_uploads')->url($cycle->organization->image_url) }}"
                      alt="{{ $cycle->organization->name_en ?? 'Organization Logo' }}" class="img-fluid"
                      style="max-width: 100%; max-height: 100%; object-fit: contain;" /> --}}
                    @if (!is_null($cycle->organization))
                    @php
                    $imageUrl = $cycle->organization->image_url;
                    $imagePath = parse_url($imageUrl, PHP_URL_PATH);
                    $fileN = ltrim(str_replace('/dashboard/uploads/files/', '', $imagePath), '/');
                    @endphp
                    <img src="/{{ $imageUrl ?? '/images/background/logo.png' }}" alt="Logo" class="img-fluid"
                      style="max-width: 100%; max-height: 100%; object-fit: contain;" />
                    @else
                    @php $setting = \App\Settings::where('id', 1)->first(); @endphp
                    @if (!empty($setting->Logo))
                    <img src="/images/background/logo.png" alt="Logo" class="img-fluid"
                      style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    @else
                    <img src="/images/background/logo.png" alt="Logo" class="img-fluid"
                      style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    @endif
                    @endif
                  </div>
                </div>
                <div class="d-flex flex-column justify-content-between" style="flex: 1;">
                  <!-- Program Title -->
                  <div class="d-flex justify-content-between align-items-center w-100 ">
                    <div class="d-flex flex-column">
                      <a href="/dashboard/programlist/show/{{ $cycle->id }}" class="text-primary fs-5 fw-bold mb-4 mb-md-0">
                        {{ $cycle->title }}
                      </a>
                      <div class="d-flex d-md-none align-items-center gap-2 mb-3">
                        <div class="badge bg-{{ $cycle->type == 'General_Program' ? 'success' : 'warning  ' }} rounded-lg p-2">
                          {{ __('cycles.' . $cycle->type) }}
                        </div>
                         <a href="{{ route('programs.report',$cycle->id) }}" class="btn btn-sm btn-light">
                            {{ __('cycles.View Report') }}
                            <i class="ti ti-clipboard-data ti-sm ms-2"></i>
                          </a>
                      </div>
                    </div>


                    <div class="d-flex align-items-center gap-4">
                      @if (!empty($cycle->private))
                        <div class="badge text-center text-dark cursor-pointer" onclick="copyToClipboard('{{ url('dashboard/cycles/view/' . $cycle->private) }}')" >

                          <span style="font-size: 12px;" class="fw-medium mx-1 menu-item">
                            {{  __('cycles.Private Link') }}:
                          </span>
                          <i class="ti ti-rosette-discount-check text-success"></i>
                        </div>
                      @endif
                      <a href="{{ route('programs.report',$cycle->id) }}" class="btn btn-sm btn-light d-none d-md-inline-flex align-items-center">
                        {{ __('cycles.View Report') }}
                        <i class="ti ti-clipboard-data ti-sm ms-2"></i>
                      </a>
                    </div>

                    {{-- <div class="badge d-flex align-items-center justify-content-center
                            bg-label-light rounded mx-1 rounded-pill">
                      <img src="/icons/star.svg" width="20" height="20" />
                      <span style="color: orange" class="fw-bold mt-1 mx-1">4.8</span>
                    </div> --}}
                  </div>
                  <!-- / Program Title -->
                  <!-- Company Name -->
                  {{-- @if (!empty($cycle->private))
                  <a href="cycles/view/{{ $cycle->private }}" class="d-flex justify-content-between" target="_blank">
                    <p class="fs-6 mb-2 card-title">
                      {{ $cycle->organization ? ($l == 'ar' ? $cycle->organization->name_ar :
                      $cycle->organization->name_en) : __('admin.Organization_Not_Found') }}
                    </p>
                    <div class="d-flex align-items-center">
                      <span style="font-size: 12px;" class="fw-medium mx-1 menu-item">{{ __('cycles.Private Link')
                        }}:</span>
                      <i class="ti ti-rosette-discount-check text-success"></i>
                    </div>
                  </a>
                  @endif --}}
                  <!-- / Company Name -->
                  <!-- Count Row -->
                  <div class="row mt-1">
                    <div style="width: fit-content">
                      <div class="d-flex align-items-center g-1" style="height: 55px;">
                        <div class="bg-label-primary p-2 d-flex align-items-center justify-content-center rounded mx-1">
                          <i class="ti ti-building-skyscraper ti-24px"></i>
                        </div>
                        <a target="_blank" href="/dashboard/cycles/{{ $cycle->id }}/registered">
                          <div class="my-auto d-flex">
                            <p class="fw-medium fs-6 my-auto">{{ __('admin.Company') }}</p>
                            <span class="badge bg-light rounded-pill text-secondary mx-2 fw-bold">
                              {{ $totalcompany }}
                            </span>
                          </div>
                        </a>
                      </div>
                    </div>
                    <div style="width: fit-content">
                      <div class="d-flex align-items-center g-1" style="height: 55px;">
                        <div class="bg-label-info p-2 d-flex align-items-center justify-content-center rounded mx-1">
                          <i class="ti ti-stack-2 ti-24px"></i>
                        </div>
                        <a target="_blank" href="/dashboard/programlist/show/{{ $cycle->id }}">
                          <div class="my-auto d-flex">
                            <p class="fw-medium fs-6 my-auto">{{ __('cycles.steps') }}</p>
                            <span class="badge bg-light rounded-pill text-secondary mx-2 fw-bold">
                              {{ $totalSteps }}
                            </span>
                          </div>
                        </a>
                      </div>
                    </div>
                    <div style="width: fit-content">
                      <div class="d-flex align-items-center g-1" style="height: 55px;">
                        <div
                          class="bg-label-secondary p-2 d-flex align-items-center justify-content-center rounded mx-1">
                          <i class="ti ti-user ti-24px"></i>
                        </div>
                        <div class="my-auto d-flex">
                          <p class="fw-medium fs-6 my-auto">{{ __('cycles.Mentors') }}</p>
                          <span class="badge bg-light rounded-pill text-secondary mx-2 fw-bold">{{ $mentor_Count
                            }}</span>
                        </div>
                      </div>
                    </div>
                    @php
                    $steps = App\Step::where('cycle_id', $cycle->id)->get();
                    $answersPending = 0;
                    foreach ($steps as $step) {
                    $answersPending += App\FinalData::where('step_id', $step->id)
                    ->whereNull('answer')
                    ->count();
                    }
                    if ($answersPending > 0) {
                    $answersPending = $answersPending;
                    } else {
                    $answersPending = 0;
                    }
                    @endphp
                    <div style="width: fit-content">
                      <div class="d-flex align-items-center g-1" style="height: 55px;">
                        <div class="bg-label-danger p-2 d-flex align-items-center justify-content-center rounded mx-1">
                          <i class="ti ti-stack-2 ti-24px"></i>
                        </div>
                        <div class="my-auto d-flex">
                          <p class="fw-medium fs-6 my-auto">{{ $l == 'en' ? 'Pending Evaluation' : 'تقييم معلق' }}</p>
                          <span class="badge bg-light rounded-pill text-secondary mx-2 fw-bold">{{ $answersPending
                            }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- / Count Row -->
                  <!-- Mentors Row -->
                  <div class="d-flex justify-content-between w-100 mt-3 mb-3 mb-md-0">
                    @if ($mentor_Count > 0)
                    <div class="d-flex w-100 gap-2">
                      <i class="ti ti-user-circle text-primary ti-24px"></i>
                      <div class="d-flex flex-column">
                        <span class="fs-6 fw-bold mb-2">{{ __('admin.Mentors') }}</span>

                        <div class="d-flex flex-wrap gap-2 align-items-center" style="width: 90%">
                          @foreach ($mentors as $mentor)
                          <a class="btn btn-sm btn-outline-primary rounded-pill" target="_blank"
                            href="{{ route('mentors.profile_view', $mentor->id) }}">
                            @if (!empty($mentor->image_url))
                            <img src="{{ $mentor->image_url }}" width="20" height="20" class="rounded-circle" />
                            @else
                            <img src="/icons/avatar.svg" width="20" height="20" class="rounded-circle" />
                            @endif
                            <span class="mx-1 text-nowrap">{{ $mentor->name }}</span>
                          </a>
                          @endforeach
                        </div>
                      </div>
                    </div>
                    @else
                    <div class="flex-1"></div>
                    @endif

                    <div class="d-none d-md-flex flex-column align-items-end mb-2">
                      <span class="fs-6 fw-bold mb-2 w-100 text-start">{{ __('admin.Duration') }}</span>
                      <div class="px-2 py-1 d-flex mb-1 rounded justify-content-center align-items-center"
                        style="background-color: #d9edee; width: fit-content;">
                        <i class="ti ti-calendar text-info ti-16px"></i>
                        <div class="fw-semibold text-dark text-nowrap mx-2" style="font-size: 12px;">
                          {{ $cycle->start ? $cycle->start->format('Y-m-d') : '' }}
                        </div>
                      </div>
                      <div class="px-2 py-1 d-flex rounded justify-content-center align-items-center"
                        style="background-color: #d9edee; width: fit-content;">
                        <i class="ti ti-calendar text-danger ti-16px"></i>
                        <div class="fw-semibold text-dark text-nowrap mx-2" style="font-size: 12px;">
                          {{ $cycle->end ? $cycle->end->format('Y-m-d') : '' }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- / Mentors Row -->
                  <!-- Program Description -->
                  @if (!empty($cycle->description))
                  <div class="d-flex gap-2 mt-4">
                    <i class="ti ti-message text-primary ti-24px"></i>
                    <div class="d-flex flex-column">
                      <span class="fs-6 fw-bold mb-1">{{ __('admin.Description') }}</span>
                      <p class="text-secondary line-clamp-3">{{ $cycle->description }}</p>
                    </div>
                  </div>
                  @endif
                  <!-- / Program Description -->
                  <!-- Progress Bar -->
                  @php
                  // تحقق من أن $totalSteps ليست صفرًا قبل عملية القسمة
                  $presSteps = $totalSteps > 0 ? ($totalStepsFinish / $totalSteps) * 100 : 0;
                  @endphp
                  <div class="d-flex align-items-center">
                    <span class="fw-bold mx-1">{{ number_format($presSteps, 2) }}%</span>
                    <div style="width: 100%; height: 7px;" class="progress rounded-pill">
                      <div class="progress-bar bg-primary" role="progressbar"
                        style="width: {{ number_format($presSteps, 2) }}%;"
                        aria-valuenow="{{ number_format($presSteps, 2) }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="fw-bold mx-1">{{ $totalSteps }}/{{ $totalStepsFinish }}</span>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <a class="dropdown-item ccycle" href="/dashboard/programlist/edit/{{ $cycle->id }}">
                            <i class="ti ti-edit me-2 text-info"></i>
                            {{ __('cycles.Edit') }}
                          </a>
                          {{-- <button class="dropdown-item ccycle" set="1" do="{{ $cycle->id }}"
                            onclick="onEditProgramClick({{ $cycle->id }})">
                            <i class="ti ti-edit me-2 text-info"></i>
                            {{ __('cycles.Edit') }}
                          </button> --}}
                        </li>
                        @if( !empty( $cycle->join_url ) && !is_null( $cycle->join_url ) )
                          <li>
                            <a href="javascript:void(0)" class="dropdown-item copy-public-link" onclick="copyToClipboard('{{ $cycle->join_url }}');">
                              <i class="ti ti-copy me-2"></i> {{ __('cycles.copy public link') }}
                            </a>
                          </li>
                        @endif
                        <li>
                          @if ($_user->ableTo('delete_user', $permissions))
                          <button class="dropdown-item" onclick="DoChange(3, {{ $cycle->id }})">
                            <i class="ti ti-trash me-2 text-danger"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endif
                        </li>
                      </ul>
                    </div>
                  </div>
                  <!-- / Progress Bar -->
                  <div class="d-flex d-md-none mt-3 mt-md-0">
                    <span class="fs-6 fw-bold mx-2">{{ __('admin.Duration') }}</span>
                    <div class="px-2 py-1 me-3 d-flex rounded" style="background-color: #d9edee;">
                      <i class="ti ti-calendar text-info ti-16px"></i>
                      <span class="fw-semibold text-dark mx-2 text-nowrap" style="font-size: 12px;">
                        {{ $cycle->start ? $cycle->start->format('Y-m-d') : '' }}
                      </span>
                    </div>
                    <div class="px-2 py-1 d-flex rounded" style="background-color: #d9edee;">
                      <i class="ti ti-calendar text-danger ti-16px"></i>
                      <span class="fw-semibold text-dark mx-2 text-nowrap" style="font-size: 12px;">
                        {{ $cycle->end ? $cycle->end->format('Y-m-d') : '' }}
                      </span>
                    </div>
                  </div>
                </div>
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
                        @if ($_user->ableTo('edit_user', $permissions))
                        {{ __('cycles.Edit') }}
                        @endif
                      </th>
                      <th>
                        @if ($_user->ableTo('delete_user', $permissions))
                        {{ __('cycles.Delete') }}
                        @endif
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
                      <td><img src="{{ $cycle->organization->image_url }}" alt="Logo" class="" height="40"></td>



                      <td style="font-size: small;">
                        <div class="btn btn-primary ccycle" set="1" do="{{ $cycle->id }}"
                          onclick="DoChange(1, {{ $cycle->id }})">
                          <span class="align-middle ml-25"><i class="bx bx-edit ti-24px"></i>{{ __('cycles.Edit')
                            }}</span>
                        </div>
                      </td>


                      <td style="font-size: small;">
                        @if ($_user->ableTo('delete_user', $permissions))
                        <div class="btn btn-danger" onclick="DoChange(3, {{ $cycle->id }})">
                          <span class="align-middle ml-25"><i class="bx bx-trash ti-24px"></i>{{ __('cycles.Delete')
                            }}</span>
                        </div>
                        @endif
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
{{-- <div class="modal fade text-left" id="cycleForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form class="form" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data"
        autocomplete="off">
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
            <input id='title' name='title' type="text" placeholder="{{ __('cycles.Title') }}" class="form-control"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fs-6 mb-1">{{ __('cycles.Valid from') }}: </label>
            <input id='start' name='start' type="text" placeholder="{{ now()->format('Y-m-d') }}"
              class="form-control date" required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fs-6 mb-1">{{ __('cycles.Valid to') }}: </label>
            <input id='end' name='end' type="text" placeholder="{{ now()->addDays(15)->format('Y-m-d') }}"
              class="form-control date" required>
          </div>
          <div class="form-group mb-3 mb-0">
            <label class="form-label fs-6 mb-1">{{ __('cycles.Description') }}: </label>
            <textarea id='description' name='description' placeholder="{{ __('cycles.Description') }}"
              class="form-control"></textarea>
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
</div> --}}

{{-- @include('admin.programs.partials._edit_program_modal', ['mode' => 'create'])
@include('admin.programs.partials._edit_program_modal', ['mode' => 'edit']) --}}
@stop

{{-- @section('scripts')
<script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
<script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
@stop --}}

@section('pagescripts')
  <script src="/app-assets/plugins/alertify/alertify.min.js"></script>
  <script>
    function copyToClipboard(text) {
      // إنشاء عنصر مؤقت
      const tempInput = document.createElement("input");
      tempInput.value = text;
      document.body.appendChild(tempInput);
      tempInput.select();
      tempInput.setSelectionRange(0, 99999); // للأجهزة المحمولة
      document.execCommand("copy");
      document.body.removeChild(tempInput);
      alertify.success("{{ __('admin.Copied') }}");
    }
    // Initialize Date Picker
    flatpickr(".datepicker", {
      dateFormat: "Y-m-d", // تنسيق التاريخ المطلوب
      allowInput: true, // السماح للمستخدم بكتابة التاريخ يدويًا
      // locale: "ar", // دعم اللغة العربية (يمكنك تغييره)
    });

    $('#search-programs').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      console.log('value', value)
      $('#program-list .card').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
    });

    $('#filter-programs-form .form-select').on('change', function() {
      $('#filter-programs-form').submit();
    });

    $('#filter-programs-form .form-control').on('change', function() {
      $('#filter-programs-form').submit();
    });
  </script>

{{-- @php($ccycle = \App\Cycle::find(0))
<script>
  // Set Language
    const lang = '{{ $l }}';

    const validFrom = document.querySelector('#create-program-modal .valid-from');
    const validTo = document.querySelector('#create-program-modal .valid-to');
    const editValidFrom = document.querySelector('#edit-program-modal .valid-from');
    // const editValidalidTo = document.querySelector('#edit-program-modal .valid-to');
    flatpickr(".datepicker", {
      dateFormat: "Y-m-d", // تنسيق التاريخ المطلوب
      allowInput: true, // السماح للمستخدم بكتابة التاريخ يدويًا
      locale: "ar", // دعم اللغة العربية (يمكنك تغييره)
    });
    // const fpTo = flatpickr(validTo, {
    //   monthSelectorType: 'static',
    //   dateFormat: 'Y-m-d',
    //   minDate: 'today',
    // });

    // const fpFrom = flatpickr(validFrom, {
    //   monthSelectorType: 'static',
    //   dateFormat: 'Y-m-d',
    //   minDate: 'today',
    // });

    // const editFpTo = flatpickr(editValidalidTo, {
    //   monthSelectorType: 'static',
    //   dateFormat: 'Y-m-d',
    //   minDate: 'today',
    // });

    // const editFpFrom = flatpickr(editValidFrom, {
    //   monthSelectorType: 'static',
    //   dateFormat: 'Y-m-d',
    //   minDate: 'today',
    // });

    // Initialize Quill Editor
    const editorConfig = {
      theme: 'snow'
    };
    const guidelineEditor = new Quill('#create-program-modal #guideline-editor', editorConfig);
    const financialEditor = new Quill('#create-program-modal #financial-editor', editorConfig);
    const legalEditor = new Quill('#create-program-modal #legal-editor', editorConfig);
    const editGuidelineEditor = new Quill('#edit-program-modal #guideline-editor', editorConfig);
    const editFinancialEditor = new Quill('#edit-program-modal #financial-editor', editorConfig);
    const editLegalEditor = new Quill('#edit-program-modal #legal-editor', editorConfig);

    // Clear Editor
    guidelineEditor.root.innerHTML = '';
    financialEditor.root.innerHTML = '';
    legalEditor.root.innerHTML = '';
    editGuidelineEditor.root.innerHTML = '';
    editFinancialEditor.root.innerHTML = '';
    editLegalEditor.root.innerHTML = '';

    // Handle Editor Change
    const handleEditorChange = (editor, input) => {
      editor.on('text-change', function() {
        $(input).val(editor.root.innerHTML);
      });
    };
    handleEditorChange(guidelineEditor, '#create-program-modal #program_guideline');
    handleEditorChange(financialEditor, '#create-program-modal #financial_policy');
    handleEditorChange(legalEditor, '#create-program-modal #legal_policy');
    handleEditorChange(editGuidelineEditor, '#edit-program-modal #program_guideline');
    handleEditorChange(editFinancialEditor, '#edit-program-modal #financial_policy');
    handleEditorChange(editLegalEditor, '#edit-program-modal #legal_policy');

    // Handle Reset Values
    const resetValues = () => {
      console.log('Resetting Values');
      $('#create-program-modal #start').val('');
      $('#create-program-modal #end').val('');
      $('#create-program-modal #title').val('');
      $('#create-program-modal #description').val('');
      $('#create-program-modal #cycleid').val('');
      $('#create-program-modal #type').val('');
      $('#create-program-modal #program_guideline').val('');
      $('#create-program-modal #financial_policy').val('');
      $('#create-program-modal #legal_policy').val('');
      $('#create-program-modal #program-guideline-pdf').val();
      $('#create-program-modal #legal_policy_pdf').val();
      $('#create-program-modal #financial_policy_pdf').val();
      $('#create-program-modal #delegation_pdf').val();
      $('#create-program-modal #delegation_option').prop('checked', false);
      $('#create-program-modal #private').prop('checked', false);

      $('#create-program-modal #date-range-picker').daterangepicker({
        opens: isRtl ? 'left' : 'right',
        drops: 'up',
      }).on('apply.daterangepicker', function(ev, picker) {
        $('#create-program-modal #start').val(picker.startDate.format('YYYY-MM-DD'));
        $('#create-program-modal #end').val(picker.endDate.format('YYYY-MM-DD'));
      });
    };

    // Handle Append File
    const appendFile = (mode, file, conainerId) => {
      console.log('file', file)
      if (file) {
        const fileName = file.split('/').pop();
        const content = (`
          <div class="d-flex align-items-center justify-content-between border p-2 rounded mb-2">
            <div class="d-flex align-items-center gap-2">
              <i class="ti ti-file fs-4 text-warning"></i>
              <span class="text-truncate" style="max-width: 200px;">${fileName}</span>
            </div> 
            <a class="btn btn-label-primary btn-icon btn-sm" href="/storage/${file}" target="_blank" download="${file}">
              <i class="ti ti-download fs-5"></i>
            </a>
          </div>`);
        $(`#${mode}-program-modal ${conainerId}`).append(content);
      }
    };

    // Handle Create Program
    const onCreateProgramClick = () => {
      resetValues();
      $('#create-program-modal').modal('show');
    }

    // Handle Edit Program
    const onEditProgramClick = (cycleId) => {
      if (cycleId) {
        $('#edit-program-modal').modal('show');

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        $.ajax({
          type: "POST",
          url: "/dashboard/cycles/modify",
          data: {
            "cycleid": cycleId
          },
          success: function(response) {
            console.log(response);

            $('#edit-program-modal #date-range-picker').daterangepicker({
              opens: isRtl ? 'left' : 'right',
              drops: 'up',
              startDate: response?.start ? moment(response?.start) : null,
              endDate: response?.end ? moment(response?.end) : null,
            }).on('apply.daterangepicker', function(ev, picker) {
              $('#edit-program-modal #start').val(picker.startDate.format('YYYY-MM-DD'));
              $('#edit-program-modal #end').val(picker.endDate.format('YYYY-MM-DD'));
            });

            $('#edit-program-modal #start').val(response.start ?? '');
            $('#edit-program-modal #end').val(response.end ?? '');
            $('#edit-program-modal #title').val(response.title ?? '');
            $('#edit-program-modal #description').val(response.description ?? '');
            $('#edit-program-modal #cycleid').val(cycleId);
            $('#edit-program-modal #type').val(1);
            $('#edit-program-modal #organization').val(response.organization_id ?? '');
            $('#edit-program-modal #allow_non_company').prop('checked', response.allow_non_company == 1 ? true :false);
            $('#edit-program-modal #auto-approve-mentorship-requests').prop('checked', response.auto_approve_mentorship_requests == 1 ? true : false);

             
            if (response.private) {
              $('#edit-program-modal #private').prop('checked', true);
              $('#cyclelink').html('{{ __('cycles.Join URL') }}');
              $('#cyclelink').attr('href', '/dashboard/cycles/view/' + response.id + '?id=' + response.private);
            }

            if (editGuidelineEditor) {
              editGuidelineEditor.root.innerHTML = response.program_guideline ?? '';
            }

            $('#edit-program-modal #program_guideline').val(response.program_guideline ?? '');

            if (editFinancialEditor) {
              editFinancialEditor.root.innerHTML = response.financial_guideline ?? '';
            }
            $('#edit-program-modal #financial_policy').val(response.financial_guideline ?? '');
            $('#edit-program-modal #budget_allocation').val(response.budget_allocation ?? '');
            $('#edit-program-modal #currency_type').val(response.currency_type ?? '');

            if (editLegalEditor) {
              editLegalEditor.root.innerHTML = response.legal_guideline ?? '';
            }
            $('#edit-program-modal #legal_policy').val(response.legal_guideline ?? '');
            const guidelineFile = response?.program_guideline_file;
            const financialFile = response.financial_guideline_file;
            const legalFile = response?.legal_guideline_file;
            const delegationFile = response.delegation_file;

            appendFile('edit', guidelineFile, '#guideline-file-container');
            appendFile('edit', financialFile, '#financial-file-container');
            appendFile('edit', legalFile, '#legal-file-container');

            if (delegationFile) {
              appendFile('edit', delegationFile, '#delegation-file-container');
              $('#edit-program-modal delegation_option').prop('checked', true);
            }
          },
          error: function(error) {
            console.log(error);
            resetValues();
            alert("Error Occurred, Try again!");
          }
        });
      }
    };
</script>
<script>
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
          $('#type').val('');
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
</script> --}}
@stop