@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/tagify/tagify.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/pickr/pickr-themes.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/animate-css/animate.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
@stop

@section('style')
<style>
  .radius-15 {
    border-radius: 15px;
  }

  .light-shadow {
    box-shadow: 0px 2px 0px 0px #F2F1F3;
  }

  .btn {
    border-radius: 15px !important;
    box-shadow: 0px 2px 0px 0px #F2F1F3;
  }

  .badge {
    border-radius: 10px !important;
  }
  .text-xs {
    font-size: 12px;
  }
  .custom-pill {
    width: fit-content;
    padding: 0.25rem 0.5rem;
    margin-right: 0.5rem;
  }
</style>
@stop

@php
 $totalSteps = app(\App\Step::class)->where('cycle_id', '=', $program->id)->count();
 $totalStepsFinish = app(\App\Step::class)->where('to', '>', Carbon\Carbon::now())->where('cycle_id', '=', $program->id)->count();
 $totalcompany = app(\App\Company::class)->where('cycle', '=', $program->id)->count();
 $orgid = App\Organization::where('id','=', $program->organization_id)->get();
        foreach ($orgid as  $org) {
          $mentortotals = App\User::where('id','=',$org->user_id)->get();
          $mentor_Count = $mentortotals->count();
        }
  $totalMentorRequest = app(\App\MentorshipRequest::class)->where('cycle_id', '=', $program->id)->count();
  $totalMentorperiod = app(\App\MentorshipRequest::class)->where('cycle_id', '=', $program->id)->sum('period');
  $totalMentorrating = app(\App\MentorshipRequest::class)->where('cycle_id', '=', $program->id)->whereNotNull('rating')->avg('rating');
  $totalStepsIdsCount = 0;
  $totalDurationSum = 0;
foreach ($steps as  $step) {
  $TotalStepsIds = app(App\TrainingSession::class)->where('step_id', '=', $step->id)->count();
  $totalStepsIdsCount += $TotalStepsIds;

  $TotalStepsDuration = app(App\TrainingSession::class)->where('step_id', '=', $step->id)->sum('duration');
  $totalDurationSum += $TotalStepsDuration;
}
$mentors = $program->mentors()->distinct()->get();


$orgCollection = collect([
[__('admin.Startups'), 'building-skyscraper', $totalcompany, 'primary'],
[__('cycles.steps'), 'stack-2', $totalSteps, 'info'],
[__('admin.Mentors'), 'user', $mentor_Count, 'light'],
[__('cycles.All requests'), 'report', $totalMentorRequest, 'warning'],
[__('admin.Mentorship_sessions_duration_(hours)'), 'clock-hour-5', $totalMentorperiod, 'success'],
[__('admin.Company_Attendance_Rate'), 'heart-rate-monitor', '5 / '.number_format($totalMentorrating, 2), 'danger'],
[__('admin.Training sessions'), 'layers-subtract', $totalStepsIdsCount, 'light'],
[__('admin.Training sessions duration (hours)'), 'clock-hour-5', $totalDurationSum, 'success'],
['filtration', 'transform', '5', 'warning'],
['evaluation', 'presentation', '5', 'danger']
]);



  
  


// $screenCollection = collect([
// ['the judges', 'gavel', '5', 'warning'],
// ['startups', 'building-skyscraper', '5', 'primary'],
//   ['filtration', 'transform', '5', 'warning'],
// ['filtration pending', 'rotate-clockwise-2', '5', 'info'],
// ['filtration done', 'checkbox', '5', 'success'],
// ]);

// $mentorshipCollection = collect([
// ['mentors', 'user', '5', 'secondary'],
// ['mentors in program', 'users', '5', 'success'],
// ['mentorship sessions duration (hours)', 'clock', '5', 'danger'],
// ['mentorship done requests count', 'check', '5', 'warning'],
// ['mentorship requests', 'message', '5', 'info'],
// ['new requests only', 'plus', '5', 'light'],
// ['approved requests', 'checkbox', '5', 'dark'],
// ['disapproved requests', 'trash-x', '5', 'primary'],
// ['mentorship pending requests count', 'rotate-clockwise-2', '5', 'secondary'],
// ]);

// $trainingCollection = collect([
// ['training sessions', 'user-screen', $totalTrainingSessionCount, 'light'],
// ['training sessions duration (hours)', 'clock', $totaldurationTr, 'success'],
// ['educational content', 'device-desktop', '5', 'primary'],
// ['online training', 'world', '5', 'warning'],
// ['traditional training', 'home-2', '5', 'info'],
// ]);
// $totalFinalscreenCount = 0;
// $totalDistinctCompanies = 0;
 $judgesDetails = collect(); // لتخزين بيانات المحكمين

//   $finalScreenings = app(App\Finalscreening::class)->where('step_id', '=', $step->id)->get();
//    // حساب عدد السجلات لكل step_id
//     // حساب عدد السجلات لكل step_id
//     $totalFinalscreenCount = app(App\Finalscreening::class)
//         ->where('step_id', '=', $step->id)
//         ->count();

//     // حساب عدد company_id الفريدة لكل step_id
//     $totalDistinctCompanies += app(App\Finalscreening::class)
//         ->where('step_id', '=', $step->id)
//         ->distinct()
//         ->count('company_id');

// // حساب عدد المحكمين
// foreach ($finalScreenings as $screening) {
//   $finaljudges = is_string($screening->judges) ? json_decode($screening->judges, true) : $screening->judges;
//     $totalFinalscreenjudgeCount += is_array($finaljudges) ? count($finaljudges) : 0;

//     $judgeData = app(App\User::class)->whereIn('id', $finaljudges)->get();
//     // إضافة بيانات المحكمين إلى المصفوفة
//     $judgesDetails = $judgesDetails->merge($judgeData);
// }


// $finalScreenCollection = collect([
// ['the judges', 'gavel', $totalFinalscreenjudgeCount, 'warning'],
// ['startups', 'building-skyscraper', $totalDistinctCompanies, 'primary'],
// ['evaluation', 'presentation', '5', 'danger'],
// ['traditional pending', 'rotate-clockwise-2', '5', 'info'],
// ['evaluation done', 'checkbox', '5', 'success'],
// ]);

$convertToArray = function ($collection) {
return $collection->map(function ($item) {
return [
'label' => $item[0],
'icon' => $item[1],
'count' => $item[2],
'color' => $item[3],
];
});
};

$orgCountList = $convertToArray($orgCollection);
// $screenCountList = $convertToArray($screenCollection);
// $mentorshipCountList = $convertToArray($mentorshipCollection);
// $trainingCountList = $convertToArray($trainingCollection);
// $finalScreenCountList = $convertToArray($finalScreenCollection);

// $mentortotal = [
// (object) ['id' => 1, 'name' => $mentordata['name'.$l], 'image_url' => '/images/background/mentor1.jpg'],
// (object) ['id' => 2, 'name' => 'Mentor 2', 'image_url' => '/images/background/mentor2.jpg'],
// (object) ['id' => 3, 'name' => 'Mentor 3', 'image_url' => '/images/background/mentor3.jpg'],
// (object) ['id' => 4, 'name' => 'Mentor 4', 'image_url' => '/images/background/mentor4.jpg'],
// (object) ['id' => 5, 'name' => 'Mentor 5', 'image_url' => '/images/background/mentor5.jpg'],
// ];

$screeningTypes = ['assign-one-to-all-users', 'assign-group-to-all-users', 'assign-group-to-group-users'];


                   
                 
                  
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">


      <div class="col-12 mb-4">
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="d-flex gap-6">
              <div class="d-none d-md-flex flex-shrink-0 w-px-100">
                <div class="w-px-70 h-px-100">
                  @if(!empty($program->organization->image_url))
                  <img src="{{$program->organization->image_url}}" class="w-100 h-100 border radius-15"
                    style="object-fit: contain;" alt="Program Image" />
                  @else
                  <img src="/images/background/logo.png" class="w-100 h-100 border radius-15"
                  style="object-fit: contain;" alt="Program Image" />

                  @endif

                </div>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between mb-2">
                  <h4 class="text-primary mb-0">{{ $program->title }}</h4>
                  <a href="/dashboard/final/results/{{ $step->id }}" class="btn btn-primary">
                    <i class="ti ti-file-analytics me-2"></i> {{ __('admin.Results') }}
                  </a>
                </div>
                <div class="d-flex justify-content-end mb-2">
                  <div class="btn btn-label-light rounded-pill px-2 h-px-30">
                    <img src="/icons/star.svg" width="17" height="17" class="me-1" />
                    <span class="text-warning fw-bold">4.5</span>
                  </div>
                </div>
                <div class="d-flex justify-content-between mb-4">
                  <h5 class="mb-0">{{ $program->organization['name_' . $l] }}</h5>
                  
                  @if(!empty($program->private))
                        <a 
                          href="cycles/view/{{ $program->private }}"
                          class="d-flex align-items-center text-muted"
                          target="_blank"
                          id="private-link"
                        >
                        {{__('cycles.Private Link')}}
                          <i class="ti ti-circle-check ms-2 text-success ti-24px"></i>
                        </a>
                        @endif
                </div>
                <div class="row border radius-15 py-3 mb-3">
                  <div class="col-md-3">
                    @foreach ($orgCountList->slice(0, 2) as $item)
                    <div class="col-12 mb-2">
                      <div class="d-flex align-items-center">
                        <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                          <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <span class="me-2 fw-semibold text-capitalize ">{{ $item['label'] }}</span>
                          <small class="bg-light text-secondary py-1 px-2 rounded-pill commanums">
                            {{ $item['count'] }}
                          </small>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                  <div class="col-md-4">
                    @foreach ($orgCountList->slice(2, 4) as $item)
                    <div class="col-12 mb-2">
                      <div class="d-flex align-items-center">
                        <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                          <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <span class="me-2 fw-semibold text-capitalize">{{ $item['label'] }}</span>
                          <small class="bg-light text-secondary py-1 px-2 rounded-pill commanums">
                            {{ $item['count'] }}
                          </small>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                  <div class="col-md-5">
                    @foreach ($orgCountList->slice(6, 10) as $item)
                    <div class="col-12 mb-2">
                      <div class="d-flex align-items-center">
                        <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                          <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <span class="me-2 fw-semibold text-capitalize">{{ $item['label'] }}</span>
                          <small class="bg-light text-secondary py-1 px-2 rounded-pill commanums">
                            {{ $item['count'] }}
                          </small>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-user-circle text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Responsible')}}</span>
                      </div>
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($mentors as $mentor)
                        <a class="btn btn-sm btn-outline-primary rounded-pill" style="height: 30px;" target="_blank"
                          href="{{route('show_mentor_profile', $mentor->id)}}">
                          @if(!empty($mentor->image_url))
                          <img src="{{$mentor->image_url ?? '/icons/account.svg'}}"
                            onerror="this.src='/icons/account.svg'" width="20" height="20" class="rounded-circle" />
                          @else
                          <i class="ti ti-user"></i>
                          @endif
                          <span class="mx-1 fw-semibold">{{$mentor->name}}</span>
                        </a>
                        @endforeach
                       
                      </div>
                    </div>
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center px-4" style="width: 85%;">
                        <p class="text-muted">
                          @if(!empty($program->description))
                              {{ $program->description }}
                              @else
                              {{__('cycles.NA')}}
                              @endif
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3 mt-6">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $program->start->format('Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $program->end->format('Y-m-d') }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @php
                    // تحقق من أن $totalSteps ليست صفرًا قبل عملية القسمة
                    $presSteps = $totalSteps > 0 ? ($totalStepsFinish / $totalSteps) * 100 : 0;
                @endphp
                <div class="row mt-4 mt-md-0">
                  <div class="col-12">
                    <div class="d-flex align-items-center">
                      <span class="fw-bold mx-1">{{ number_format($presSteps, 2) }}%</span>
                      <div style="width: 100%; height: 7px;" class="progress rounded-pill">
                        <div role="progressbar" class="progress-bar" style="background: linear-gradient(to {{$l == 'en' ? 'right' : 'left'}},
                          rgb(164, 157, 233), #857ce6, #7367f0); width: {{ number_format($presSteps, 2) }}%;" aria-valuenow="{{ number_format($presSteps, 2) }}" aria-valuemin="0"
                          aria-valuemax="100">
                        </div>
                      </div>
                      <span class="fw-bold mx-1">{{$totalSteps}}/{{$totalStepsFinish}}</span>
                      <div class="mx-2 position-relative">
                        <div class="dropdown"></div>
                        <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                          data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="ti ti-dots-vertical ti-24px"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                          <li>
                            <button id="editButton" class="dropdown-item ccycle text-primary">
                              <i class="ti ti-edit me-2"></i>
                              {{ __('cycles.Edit') }}
                            </button>
                          </li>
                          <li>
                            @can('delete_user')
                            <button class="dropdown-item text-danger" id="deleteButton">
                              <i class="ti ti-trash me-2"></i>
                              {{ __('cycles.Delete') }}
                            </button>
                            @endcan
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      
      <!-- Custom Step -->
      @foreach ($steps as  $step)

      @php
  $TotalscreenStepsIds = \App\Company::where('step', $step->id)->count();
  $TrainingSession = app(App\TrainingSession::class)->where('step_id', '=', $step->id)->get();
// حساب عدد السجلات لكل step_id
$totalTrainingSessionCount = app(App\TrainingSession::class)
        ->where('step_id', '=', $step->id)
        ->count();

    // حساب عدد company_id الفريدة لكل step_id
    $totaldurationTr = app(App\TrainingSession::class)
        ->where('step_id', '=', $step->id)
        ->sum('duration');

// حساب عدد company_id الفريدة لكل step_id
$totalsessions = app(App\TrainingSession::class)
        ->where('step_id', '=', $step->id)
        ->where('type', 'sessions')
        ->count();
$totalonline = app(App\TrainingSession::class)
        ->where('step_id', '=', $step->id)
        ->where('type', 'online')
        ->count();
$totaloffline = app(App\TrainingSession::class)
        ->where('step_id', '=', $step->id)
        ->where('type', 'offline')
        ->count();


  // Filter Screening
  $finalScreenings = app(App\Finalscreening::class)->where('step_id', '=', $step->id)->get();
   // حساب عدد السجلات لكل step_id
    // حساب عدد السجلات لكل step_id
    $totalFinalscreenCount = app(App\Finalscreening::class)
        ->where('step_id', '=', $step->id)
        ->count();

    // حساب عدد company_id الفريدة لكل step_id
    $totalDistinctCompanies = app(App\Finalscreening::class)
        ->where('step_id', '=', $step->id)
        ->distinct()
        ->count('company_id');

// حساب عدد المحكمين
foreach ($finalScreenings as $screening) {
  $finaljudges = is_string($screening->judges) ? json_decode($screening->judges, true) : $screening->judges;
    $judgeData = app(App\User::class)->whereIn('id', $finaljudges)->get();
    // إضافة بيانات المحكمين إلى المصفوفة
    $judgesDetails = $judgesDetails->merge($judgeData)->unique('id');




}
@endphp


      @if($step->stype == 1)
      <div class="col-12 mb-4">
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="row mt-4 mt-md-0">
              <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <div>
                    
                    <a href="#" class="btn btn-outline-primary rounded-pill">
                      {{ __('cycles.Custom Form') }}
                    </a>
                   
                
                  </div>
                  <div class="d-flex align-items-center">
                    <h6 class="mb-0">{{ __('cycles.steps') }} {{ $step->arr }}</h6>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <button data-bs-toggle="modal" 
                          data-bs-target="#customStepModal" class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @can('delete_user')
                          <button class="dropdown-item text-danger" id="deleteButton">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endcan
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                @php
                  $totalcompanystips = app(\App\Company::class)->where('step', '=', $step->id)->count();
                @endphp
                <div class="d-flex flex-grow-1 justify-content-between mb-2">
                  <h5 class="text-primary mb-0">{{ $step->title }}</h5>
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-primary me-2 p-2">
                      <i class="ti ti-building-skyscraper text-primary ti-24px"></i>
                    </div>
                    <div class="d-flex flex-column align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Startups') }}</span>
                      <small>{{ $totalcompanystips }}</small>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-9">
                    {{-- <div class="d-flex align-items-center">
                      <i class="ti ti-notes text-primary ti-20px me-2"></i>
                      <div class="px-4 py-1 bg-light text-black rounded-pill" style="max-width: 85%;">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias consequatur.
                      </div>
                    </div> --}}
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      @if($step->description != '')
                      <div class="d-flex flex-wrap px-4 py-1 bg-light text-black rounded-pill" style="max-width: 85%;">
                        {{ $step->description }}
                      </div>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-3 mt-6">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $step->from->format('Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $step->to->format('Y-m-d') }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
      @if($step->stype == 2)
      <!-- Screening Step -->
          
      <div class="col-12 mb-4">
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="row mt-4 mt-md-0">
              <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <a href="/dashboard/{{ $step->id }}/screening" class="btn btn-outline-primary rounded-pill">
                    {{ __('cycles.Screening') }}
                  </a>
                  <div class="d-flex align-items-center gap-4">
                    <h6 class="mb-0">{{ __('cycles.steps') }} {{ $step->arr }}</h6>
                    <a href="/dashboard/final/results/{{ $step->id }}" class="btn btn-primary">
                      <i class="ti ti-file-analytics me-2"></i> {{ __('admin.Results') }}
                    </a>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <button data-bs-toggle="modal" 
                          data-bs-target="#screeningStepModal" class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @can('delete_user')
                          <button class="dropdown-item text-danger" id="deleteButton">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endcan
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-grow-1 justify-content-between mb-5">
                  <h5 class="text-primary mb-0">{{ $step->title }}</h5>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center border radius-15 p-3 mb-6">
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-warning me-2 p-2">
                      <i class="ti ti-gavel text-warning ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Judgers') }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $judges->count() }}
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-primary me-2 p-2">
                      <i class="ti ti-building-skyscraper text-primary ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Startups') }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $TotalscreenStepsIds }}
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-warning me-2 p-2">
                      <i class="ti ti-transform text-warning ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">filtration</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        5
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-info me-2 p-2">
                      <i class="ti ti-rotate-clockwise-2 text-info ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">filtration pending</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        5
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-success me-2 p-2">
                      <i class="ti ti-checkbox text-success ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">filtration done</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        5
                      </small>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-8 col-md-9">
                    <div class="d-flex align-items-center gap-3 mb-3">
                      <div class="d-flex align-items-center">
                        <i class="ti ti-gavel text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">{{ __('admin.Judgers') }}</span>
                      </div>
                      {{-- <small class="badge bg-primary fw-normal"> Assign one to all users </small> --}}
                    </div>
                  </div>
                  <div class="col-4 col-md-3">
                    {{-- <a id="private-link" href="#" class="d-flex align-items-center text-nowrap">
                      {{ __('cycles.Notify them') }} <i class="ti ti-circle-check ms-2 text-success ti-24px"></i>
                    </a> --}}
                    <form action="/dashboard/programlist/Email" method="post" >
                      @csrf
                      <input type="hidden" value="{{ $step->id }}" name="step">
                      <button id="private-link" type="submit" class="d-flex align-items-center text-nowrap">{{ __('cycles.Notify them') }} <i class="ti ti-circle-check ms-2 text-success ti-24px"></i></button>
                    </form>
                  </div>
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($judges as $judge)
                        <a class="btn btn-sm btn-outline-warning rounded-pill" style="height: 30px;" target="_blank"
                          href="{{route('show_mentor_profile', $judge->id)}}">
                          @if(!empty($judge->image_url))
                          <img src="{{$judge->image_url ?? '/icons/account.svg'}}"
                            onerror="this.src='/icons/account.svg'" width="20" height="20" class="rounded-circle" />
                          @else
                          <i class="ti ti-user"></i>
                          @endif
                          <span class="mx-1 fw-semibold">{{$judge->name}}</span>
                        </a>
                        @endforeach
                      </div>
                    </div>
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center px-4" style="width: 85%;">
                        <p class="text-muted">
                          {{ $step->description }}
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3 mt-6">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $step->from->format('Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $step->to->format('Y-m-d') }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- / Screening step -->

      @endif
      @if($step->stype == 3)

      <!-- Mentorship Step -->
      <div class="col-12 mb-4">

        @php
         
        $mentoreCountRequest = app(App\MentorshipRequest::class)
        ->where('cycle_id', '=', $step->cycle_id)
        ->distinct()
        ->count('mentor_id');


        $totalsessionsMen = app(App\MentorshipRequest::class)
        ->where('cycle_id', '=', $step->cycle_id)
        ->where('approved', 'Yes')
        ->sum('period');

        $totalsessionsDoneMen = app(App\MentorshipRequest::class)
        ->where('cycle_id', '=', $step->cycle_id)
        ->whereNotNull('done')
        ->count();

        $mentorship_requests = app(App\MentorshipRequest::class)
        ->where('cycle_id', '=', $step->cycle_id)
        ->count();
        $new_requests = app(App\MentorshipRequest::class)
        ->where('cycle_id', '=', $step->cycle_id)
        ->whereNull('approved')
        ->count();
        $approved_requests = app(App\MentorshipRequest::class)
        ->where('cycle_id', '=', $step->cycle_id)
        ->where('approved', 'Yes')
        ->count();
        $disapproved_requests = app(App\MentorshipRequest::class)
        ->where('cycle_id', '=', $step->cycle_id)
        ->where('approved', 'No')
        ->count();


        @endphp
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="row mt-md-0">
              <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between mb-4">
                  <a id="mentorship-step" href="#" class="btn btn-outline-primary rounded-pill mb-3 mb-md-0" style="width: fit-content;">
                    {{ __('cycles.Mentorship') }}
                  </a>
                  <div class="d-flex align-items-center gap-4">
                    <h6 class="mb-0 text-nowrap">{{ __('cycles.steps') }} {{ $step->arr }}</h6>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                      <a id="mentors" href="/dashboard/mentorship/sessions" class="btn btn-primary">
                        <i class="ti ti-user me-2"></i> {{ __('admin.Mentors') }}
                      </a>
                      <a id="manage" href="/dashboard/mentorship/assign/{{ $step->cycle_id }}" class="btn btn-primary">
                        <i class="ti ti-settings me-2"></i> {{ __('admin.Manage') }}
                      </a>
                      <a id="sessions" href="/dashboard/training/sessions/{{ $step->id }}" class="btn btn-primary">
                        <i class="ti ti-cast me-2"></i> {{ __('admin.Sessions') }}
                      </a>
                      <a id="results" href="/dashboard/final/results/{{ $step->id }}" class="btn btn-primary">
                        <i class="ti ti-file-analytics me-2"></i> {{ __('admin.Results') }}
                      </a>
                    </div>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <button data-bs-toggle="modal" 
                          data-bs-target="#mentorshipStepModal" class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @can('delete_user')
                          <button class="dropdown-item text-danger" id="deleteButton">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endcan
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-grow-1 justify-content-between mb-4">
                  <h5 class="text-primary mb-0">{{ $step->title }}</h5>
                </div>
                <div class="row border radius-15 p-3 mb-4">
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-success me-2 p-2">
                        <i class="ti ti-users text-success ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Mentors') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $mentoreCountRequest }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-danger me-2 p-2">
                        <i class="ti ti-clock text-danger ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Mentorship_sessions_duration_(hours)') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $totalsessionsMen }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-warning me-2 p-2">
                        <i class="ti ti-check text-warning ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Mentorship_done_requests_count') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $totalsessionsDoneMen }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-info me-2 p-2">
                        <i class="ti ti-message text-info ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('cycles.All requests') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $mentorship_requests }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-light me-2 p-2">
                        <i class="ti ti-plus text-light ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('cycles.New requests only') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $new_requests }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-dark me-2 p-2">
                        <i class="ti ti-checkbox text-dark ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('cycles.approved requests') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $approved_requests }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-primary me-2 p-2">
                        <i class="ti ti-trash-x text-primary ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('cycles.disapproved requests') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $disapproved_requests }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  {{-- <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-secondary me-2 p-2">
                        <i class="ti ti-rotate-clockwise-2 text-secondary ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">mentorship pending requests count</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          5
                        </small>
                      </div>
                    </div>
                  </div> --}}
                  
                </div>
                <div class="row">
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-bulb text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">{{ __('admin.Mentors') }}</span>
                      </div>
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($mentors as $mentor)
                        <a class="btn btn-outline-info rounded-pill" style="height: 40px;" target="_blank"
                          href="{{route('show_mentor_profile', $mentor->id)}}">
                          @if(!empty($mentor->image_url))
                          <img src="{{$mentor->image_url ?? '/icons/account.svg'}}"
                            onerror="this.src='/icons/account.svg'" width="30" height="30" class="rounded-circle" />
                          @else
                          <i class="ti ti-user ti-28px"></i>
                          @endif
                          <div class="d-flex flex-column gap-0">
                            <span style="font-size: 12px;" class="mx-1 fw-semibold">{{$mentor->name}}</span>
                            <small style="font-size: 10px;">{{$mentor->title}}</small>
                          </div>
                        </a>
                        @endforeach
                      </div>
                    </div>
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center px-4" style="width: 85%;">
                        <p class="text-muted">{{ $step->description }}</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3 mt-6">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $step->from->format('Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $step->to->format('Y-m-d') }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Mentorship step -->

      @endif
      @if($step->stype == 4)
      <!-- Training Step -->
      <div class="col-12 mb-4">
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="row mt-md-0">
              <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between mb-4">
                  <a id="training-step" href="#" class="btn btn-outline-primary rounded-pill mb-3 mb-md-0" style="width: fit-content;">
                    {{ __('cycles.Training') }}
                  </a>
                  <div class="d-flex align-items-center gap-4">
                    <h6 class="mb-0 text-nowrap">{{ __('cycles.steps') }} {{ $step->arr }}</h6>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                      <a id="sessions" href="/dashboard/training/sessions/{{ $step->id }}" class="btn btn-primary">
                        <i class="ti ti-cast me-2"></i> {{ __('admin.Sessions') }}
                      </a>
                      <a id="results" href="/dashboard/training/sessions/{{ $step->id }}" class="btn btn-primary">
                        <i class="ti ti-file-analytics me-2"></i> {{ __('admin.Results') }}
                      </a>
                    </div>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <button data-bs-toggle="modal" 
                          data-bs-target="#trainingStepModal" class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @can('delete_user')
                          <button class="dropdown-item text-danger" id="deleteButton">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endcan
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-grow-1 justify-content-between mb-4">
                  <h5 class="text-primary mb-0">{{ $step->title }}</h5>
                </div>
                <div class="row border radius-15 p-3 mb-4">
                 
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-light me-2 p-2">
                        <i class="ti ti-user-screen text-light ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Total_sessions') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $totalTrainingSessionCount }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-success me-2 p-2">
                        <i class="ti ti-clock text-success ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Training sessions duration (hours)') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $totaldurationTr }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-primary me-2 p-2">
                        <i class="ti ti-device-desktop text-primary ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('cycles.sessions') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $totalsessions }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-warning me-2 p-2">
                        <i class="ti ti-world text-warning ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('cycles.online') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $totalonline }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-info me-2 p-2">
                        <i class="ti ti-home-2 text-info ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('cycles.offline') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $totaloffline }}
                        </small>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="row">
                  <div class="col-md-9">
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center px-4" style="width: 85%;">
                        <p class="text-muted">{{ $step->description }}</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $step->from->format('Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $step->to->format('Y-m-d') }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Training step -->
      @endif
      @if($step->stype == 5)
      <!-- Final screening Step -->
      <div class="col-12 mb-4">

        @php

          

          $answers = app(App\Formdata::class)->where('step_id', $step->id)->count();
          $answerspinding = app(App\Formdata::class)->where('step_id', $step->id)->whereNull('screening')->orWhere('screening', '{}')->count();
          $answersdone = app(App\Formdata::class)
          ->where('step_id', $step->id)
          ->whereNotNull('screening') // الحقل ليس NULL
          ->where('screening', '!=', '{}') // الحقل ليس JSON فارغ
          ->count();

  
        @endphp
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="row mt-4 mt-md-0">
              <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <a href="#" class="btn btn-outline-primary rounded-pill">
                    {{ __('cycles.Final evaluation') }}
                  </a>
                 
                  <div class="d-flex align-items-center gap-4">
                    <h6 class="mb-0">{{ __('cycles.steps') }} {{ $step->arr }}</h6>
                    <a id="manage" href="/dashboard/{{ $step->id }}/final/" class="btn btn-primary">
                      <i class="ti ti-settings me-2"></i> {{ __('admin.Manage') }}
                    </a>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <button data-bs-toggle="modal" 
                          data-bs-target="#finalScreeningModal" class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @can('delete_user')
                          <button class="dropdown-item text-danger" id="deleteButton">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endcan
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-grow-1 justify-content-between mb-5">
                  <h5 class="text-primary mb-0">{{ $step->title }}</h5>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center border radius-15 p-3 mb-6">

                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-warning me-2 p-2">
                      <i class="ti ti-gavel text-warning ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Judgers') }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $judgesDetails->count() }}
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-primary me-2 p-2">
                      <i class="ti ti-building-skyscraper text-primary ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Startups') }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $totalDistinctCompanies }}
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-danger me-2 p-2">
                      <i class="ti ti-presentation text-danger ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Evaluation') }}evaluation</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $answers }}
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-info me-2 p-2">
                      <i class="ti ti-rotate-clockwise-2 text-info ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Traditional_Evaluation_pending') }} </span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $answerspinding }}
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-success me-2 p-2">
                      <i class="ti ti-checkbox text-success ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ __('admin.Evaluation_done') }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $answersdone }}
                      </small>
                    </div>
                  </div>
                </div>
                <div class="row">
                  {{-- <div class="col-12 mb-3">
                    <div class="d-flex align-items-center">
                      <i class="ti ti-notes text-primary ti-20px me-2"></i>
                      <div class="px-4 py-1 bg-light text-black rounded-pill" style="max-width: 85%;">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias consequatur.
                      </div>
                    </div>
                  </div> --}}
                  <div class="col-8 col-md-9">
                    <div class="d-flex align-items-center gap-3 mb-3">
                      <div class="d-flex align-items-center">
                        <i class="ti ti-gavel text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">{{ __('admin.Judgers') }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="col-4 col-md-3">
                    {{-- <a id="private-link" href="#" class="d-flex align-items-center text-nowrap">
                      {{ __('cycles.Notify them') }} <i class="ti ti-circle-check ms-2 text-success ti-24px"></i>
                    </a> --}}
                    <form action="/dashboard/programlist/Email" method="post" >
                      @csrf
                      <input type="hidden" value="{{ $step->id }}" name="step">
                      <button id="private-link" type="submit" class="d-flex align-items-center text-nowrap">{{ __('cycles.Notify them') }} <i class="ti ti-circle-check ms-2 text-success ti-24px"></i></button>
                    </form>


                  </div>
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($judgesDetails as $judge)
                        <a class="btn btn-sm btn-outline-warning rounded-pill" style="height: 30px;" target="_blank"
                          href="{{route('show_mentor_profile', $judge->id)}}">
                          @if(!empty($judge->image_url))
                          <img src="{{$judge->image_url ?? '/icons/account.svg'}}"
                            onerror="this.src='/icons/account.svg'" width="20" height="20" class="rounded-circle" />
                          @else
                          <i class="ti ti-user"></i>
                          @endif
                          <span class="mx-1 fw-semibold">{{$judge->name}}</span>
                        </a>
                        @endforeach
                      </div>
                    </div>
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      
                          <div class="d-flex flex-wrap px-4 py-1 bg-light text-black rounded-pill" style="max-width: 85%;">
                            {{ $step->description }}
                          </div>
                        
                      
                    </div>
                  </div>
                  <div class="col-md-3 mt-6">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $step->from->format('Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">{{ $step->to->format('Y-m-d') }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Final screening step -->
      @endif

      @endforeach
      <!-- / Custom step -->
      
      
      
      


    </div>
  </div>
  <div class="content-backdrop fade"></div>
</div>

<!-- Custom Step Modal -->
  <div 
    class="modal fade" 
    id="customStepModal" 
    tabindex="-1" 
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCenterTitle">Edit Step Information</h5>
          <button 
            type="button" 
            class="btn-close" 
            data-bs-toggle="modal" 
            data-bs-target="#customStepModal" 
            aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <form id="edit-step-form">
            <fieldset class="row">
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="select-step">Select Step</label>
                <select class="form-select border light-shadow">
                  <option>Step 1</option>
                  <option>Step 2</option>
                  <option>Step 3</option>
                </select>
              </div>
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="step-name">Step Name</label>
                <input 
                  type="text" 
                  class="form-control border light-shadow" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control border light-shadow" 
                    id="flatpickr-date"
                    placeholder="Start Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">End Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control border light-shadow" 
                    id="flatpickr-date"
                    placeholder="End Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-12 form-group mb-5">
                <label class="form-label" form="step-desc">Description</label>
                <textarea 
                  class="form-control border light-shadow" 
                  id="step-desc" 
                  rows="3" 
                  placeholder="Description"
                ></textarea>
              </div>
            </fieldset>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <i class="ti ti-circle-check ti-24px me-2"></i>
            {{ __('cycles.Submit') }}
          </button>
          <button 
            type="button" 
            data-bs-toggle="modal" 
            data-bs-target="#customStepModal" 
            class="btn btn-outline-primary ml-1">
            <i class="ti ti-circle-x ti-24px me-2"></i>
            {{ __('cycles.Close') }}
          </button>
        </div>
      </div>
    </div>
  </div>
<!-- / Custom Step Modal -->
<!-- Screening Step Modal -->
  <div 
    class="modal fade" 
    id="screeningStepModal" 
    tabindex="-1" 
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCenterTitle">Edit Step Information</h5>
          <button 
            type="button" 
            class="btn-close" 
            data-bs-toggle="modal" 
            data-bs-target="#screeningStepModal" 
            aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <form id="edit-step-form">
            <fieldset class="row">
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="select-step">Select Step</label>
                <select class="form-select border light-shadow">
                  <option>Step 1</option>
                  <option>Step 2</option>
                  <option>Step 3</option>
                </select>
              </div>
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="step-name">Step Name</label>
                <input 
                  type="text" 
                  class="form-control border light-shadow" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control border light-shadow" 
                    id="flatpickr-date"
                    placeholder="Start Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">End Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control border light-shadow" 
                    id="flatpickr-date"
                    placeholder="End Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-12 form-group mb-5">
                <label class="form-label" form="step-desc">Description</label>
                <textarea 
                  class="form-control border light-shadow" 
                  id="step-desc" 
                  rows="3" 
                  placeholder="Description"
                ></textarea>
              </div>
              <div class="col-12 form-group mb-5">
                <label class="form-label" form="screening-type">Choose screening type</label>
                <div class="d-flex flex-nowrap">
                  @foreach ($screeningTypes as $type)
                    <button 
                      type="button" 
                      class="custom-pill rounded-pill border light-shadow"
                      style="width: 170px; height: 30px;"
                      onclick="this.classList.toggle('btn-primary')"
                    >
                      <div style="font-size: 12px;" class="text-nowrap">{{ $type }}</div>
                    </button>
                  @endforeach
                </div>
              </div>
              <div class="col-12 form-group mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <label for="TagifyUserList" class="form-label">Choose judges</label>
                  <div class="d-flex form-check form-switch gap-12">
                    <label for="userSwitch">{{ __('cycles.Notify them') }}</label>
                    <input class="form-check-input" type="checkbox" id="userSwitch">
                  </div>
                </div>
                <input
                  id="TagifyUserList"
                  name="TagifyUserList"
                  class="form-control"
                  value="" 
                  placeholder="Choose users"
                />
              </div>
            </fieldset>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <i class="ti ti-circle-check ti-24px me-2"></i>
            {{ __('cycles.Submit') }}
          </button>
          <button 
            type="button" 
            data-bs-toggle="modal" 
            data-bs-target="#screeningStepModal" 
            class="btn btn-outline-primary ml-1">
            <i class="ti ti-circle-x ti-24px me-2"></i>
            {{ __('cycles.Close') }}
          </button>
        </div>
      </div>
    </div>
  </div>
<!-- / Screening Step Modal -->
<!-- Mentorship Step Modal -->
  <div 
    class="modal fade" 
    id="mentorshipStepModal" 
    tabindex="-1" 
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCenterTitle">Edit Step Information</h5>
          <button 
            type="button" 
            class="btn-close" 
            data-bs-toggle="modal" 
            data-bs-target="#mentorshipStepModal" 
            aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <form id="edit-step-form">
            <fieldset class="row">
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="step-name">Mentorship Name</label>
                <input 
                  type="text" 
                  class="form-control border light-shadow" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control border light-shadow" 
                    id="flatpickr-date"
                    placeholder="Start Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">End Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control border light-shadow" 
                    id="flatpickr-date"
                    placeholder="End Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-12 form-group mb-5">
                <label class="form-label" form="step-desc">Description</label>
                <textarea 
                  class="form-control border light-shadow" 
                  id="step-desc" 
                  rows="3" 
                  placeholder="Description"
                ></textarea>
              </div>
              <div class="col-12 form-group mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <label for="TagifyUserList" class="form-label">Choose Mentors</label>
                  <div class="d-flex form-check form-switch gap-12">
                    <label for="userSwitch">{{ __('cycles.Notify them') }}</label>
                    <input class="form-check-input" type="checkbox" id="userSwitch">
                  </div>
                </div>
                <input
                  id="TagifyUserList"
                  name="TagifyUserList"
                  class="form-control"
                  value="" 
                  placeholder="Choose users"
                />
              </div>
            </fieldset>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <i class="ti ti-circle-check ti-24px me-2"></i>
            {{ __('cycles.Submit') }}
          </button>
          <button 
            type="button" 
            data-bs-toggle="modal" 
            data-bs-target="#mentorshipStepModal" 
            class="btn btn-outline-primary ml-1">
            <i class="ti ti-circle-x ti-24px me-2"></i>
            {{ __('cycles.Close') }}
          </button>
        </div>
      </div>
    </div>
  </div>
<!-- / Mentorship Step Modal -->
<!-- Training Step Modal -->
  <div 
    class="modal fade" 
    id="trainingStepModal" 
    tabindex="-1" 
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCenterTitle">Edit Step Information</h5>
          <button 
            type="button" 
            class="btn-close" 
            data-bs-toggle="modal" 
            data-bs-target="#trainingStepModal" 
            aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <form id="edit-step-form">
            <fieldset class="row">
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="step-name">Training Name</label>
                <input 
                  type="text" 
                  class="form-control border light-shadow" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control border light-shadow" 
                    id="flatpickr-date"
                    placeholder="Start Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">End Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control border light-shadow" 
                    id="flatpickr-date"
                    placeholder="End Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-12 form-group mb-5">
                <label class="form-label" form="step-desc">Description</label>
                <textarea 
                  class="form-control border light-shadow" 
                  id="step-desc" 
                  rows="3" 
                  placeholder="Description"
                ></textarea>
              </div>
            </fieldset>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <i class="ti ti-circle-check ti-24px me-2"></i>
            {{ __('cycles.Submit') }}
          </button>
          <button 
            type="button" 
            data-bs-toggle="modal" 
            data-bs-target="#trainingStepModal" 
            class="btn btn-outline-primary ml-1">
            <i class="ti ti-circle-x ti-24px me-2"></i>
            {{ __('cycles.Close') }}
          </button>
        </div>
      </div>
    </div>
  </div>
<!-- / Training Step Modal -->
<!-- Final Screening Step Modal -->
  <div 
    class="modal fade" 
    id="finalScreeningModal" 
    tabindex="-1" 
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCenterTitle">Edit Step Information</h5>
          <button 
            type="button" 
            class="btn-close" 
            data-bs-toggle="modal" 
            data-bs-target="#finalScreeningModal" 
            aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <form id="edit-step-form">
            <fieldset class="row">
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="step-name">Final screening name</label>
                <input 
                  type="text" 
                  class="form-control border light-shadow" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control border light-shadow" 
                    id="flatpickr-date"
                    placeholder="Start Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">End Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control border light-shadow" 
                    id="flatpickr-date"
                    placeholder="End Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-12 form-group mb-5">
                <label class="form-label" form="step-desc">Description</label>
                <textarea 
                  class="form-control border light-shadow" 
                  id="step-desc" 
                  rows="3" 
                  placeholder="Description"
                ></textarea>
              </div>
              <div class="col-12 form-group mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <label for="TagifyUserList" class="form-label">Choose judges</label>
                  <div class="d-flex form-check form-switch gap-12">
                    <label for="userSwitch">{{ __('cycles.Notify them') }}</label>
                    <input class="form-check-input" type="checkbox" id="userSwitch">
                  </div>
                </div>
                <input
                  id="TagifyUserList"
                  name="TagifyUserList"
                  class="form-control"
                  value="" 
                  placeholder="Choose users"
                />
              </div>
            </fieldset>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <i class="ti ti-circle-check ti-24px me-2"></i>
            {{ __('cycles.Submit') }}
          </button>
          <button 
            type="button" 
            data-bs-toggle="modal" 
            data-bs-target="#finalScreeningModal" 
            class="btn btn-outline-primary ml-1">
            <i class="ti ti-circle-x ti-24px me-2"></i>
            {{ __('cycles.Close') }}
          </button>
        </div>
      </div>
    </div>
  </div>
<!-- / Final Screening Step Modal -->
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
<script src="/vuexy/assets/vendor/libs/tagify/tagify.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
<script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
@stop

@section('pagescripts')
<script src="/vuexy/assets/js/forms-pickers.js"></script>
<script src="/vuexy/assets/js/forms-selects.js"></script>
<script src="/vuexy/assets/js/forms-tagify.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const deleteButtons = document.querySelectorAll('#deleteButton');

    deleteButtons.forEach(deleteButton => {
      deleteButton.addEventListener('click', () => {
        Swal.fire({
          title: '{{ __('cycles.sure delete step') }}',
          text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: '{{ __('admin.DYes') }}',
          cancelButtonText: '{{ __('admin.No') }}'
        }).then(function (result) {
          if (result.value) {
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'Step has been removed.',
            });
          }
        });
      });
    });
  });
</script>
@stop