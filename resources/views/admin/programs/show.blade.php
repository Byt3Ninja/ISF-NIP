@extends('layouts.admin')

@section('vendorstyle')
<!-- Vendors CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/node-waves/node-waves.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/pickr/pickr-themes.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/animate-css/animate.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
@stop

@section('style')
<style>
  .daterangepicker {
    margin-top: 8rem !important;
  }

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
 $totalSteps = app(\App\Step::class)->where('cycle_id', '=', $cycle->id)->count();
 $totalStepsFinish = app(\App\Step::class)->where('to', '>', Carbon\Carbon::now())->where('cycle_id', '=', $cycle->id)->count();
 $totalcompany = app(\App\Company::class)->where('cycle', '=', $cycle->id)->count();
 $orgid = App\Organization::where('id','=', $cycle->organization_id)->get();
        foreach ($orgid as  $org) {
          $mentortotals = App\User::where('id','=',$org->user_id)->get();
          $mentor_Count = $mentortotals->count();
        }

  $totalMentorRequest = app(\App\MentorshipRequest::class)->where('cycle_id', '=', $cycle->id)->count();
  $totalMentorperiod1 = app(\App\MentorshipRequest::class)->where('cycle_id', '=', $cycle->id)->sum('period');
  $hoursm = floor($totalMentorperiod1 / 60);
  $minutesm = $totalMentorperiod1 % 60;
  $totalMentorperiod = "{$hoursm} h {$minutesm} m";

  // $mentorrating = app(\App\MentorshipRequest::class)->where('cycle_id', '=', $cycle->id)->get();
  //       $company = app(\App\Company::class)->where('cycle', '=', $cycle->id)->count();
  //       $session_report = app(\App\SessionReport::class)->whereIn('mentorship_request_id', $mentorrating->pluck('id'))->count(); 

        $mentorshipRequests = \App\MentorshipRequest::where('cycle_id', $cycle->id)->get();
        $totalRequests = $mentorshipRequests->count();

        $companiesCount = \App\Company::where('cycle', $cycle->id)->count();

        $sessionReportsCount = \App\SessionReport::whereIn('mentorship_request_id', $mentorshipRequests->pluck('id'))->count();

        $totalMentorrating = 0;

        if ($totalRequests > 0) {
            $totalMentorrating = ($sessionReportsCount / $totalRequests) * 100;
        }
        
  $totalStepsIdsCount = 0;
  $totalDurationSum = 0;
  $totalDurationSum1 = 0;
  // $answerspinding = [];
  $answerspinding = 0;
  $answers=0;
foreach ($steps as  $step) {
  $TotalStepsIds = app(App\TrainingSession::class)->where('step_id', '=', $step->id)->count();
  $totalStepsIdsCount += $TotalStepsIds;

  $TotalStepsDuration = app(App\TrainingSession::class)->where('step_id', '=', $step->id)->sum('duration');
  $totalDurationSum1 += $TotalStepsDuration;

  $hours = floor($totalDurationSum1 / 60);
  $minutes = $totalDurationSum1 % 60;

  $totalDurationSum = "{$hours} h {$minutes} m";
 
  
  $otherCount = app(App\FinalData::class)->where('step_id', $step->id)->count();
  $answers += $otherCount;

          $answerspinding_q = app(App\FinalData::class)->where('step_id', $step->id)->whereNull('answer')->count();
          $answerspinding += $answerspinding_q;
          
          $answersdone = app(App\FinalData::class)
          ->where('step_id', $step->id)
          ->whereNotNull('answer') // الحقل ليس NULL
          ->count();

}
$mentors = $cycle->mentors()->distinct()->get();

if($totalStepsIdsCount > 0){
  $totalStepsIdsCount = $totalStepsIdsCount;
}else{
  $totalStepsIdsCount = 0;
}

if($answerspinding > 0){
  $answerspinding = $answerspinding;
}else{
  $answerspinding = 0;
}

$orgCollection = collect([
[__('admin.Startups'), 'building-skyscraper', $totalcompany, 'primary'],
[__('cycles.steps'), 'stack-2', $totalSteps, 'info'],
[__('admin.Mentors'), 'user', $mentors->count(), 'light'],
[__('cycles.All requests'), 'report', $totalMentorRequest, 'warning'],
[__('admin.Mentorship_sessions_duration_(hours)'), 'clock-hour-5', $totalMentorperiod, 'success'],
[__('admin.Company_Attendance_Rate'), 'heart-rate-monitor', number_format($totalMentorrating, 2) . ' %', 'danger'],
[__('admin.Training sessions'), 'layers-subtract', $totalStepsIdsCount, 'light'],
[__('admin.Startups_trainings_Hours'), 'clock-hour-5', $totalDurationSum, 'success'],
[__('admin.Traditional_Evaluation_pending'), 'transform', $answerspinding, 'warning'],
[__('admin.Evaluation'), 'presentation', $answers, 'danger']
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
    <!-- Alert -->
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
    <!-- / Alert -->
    <div class="row">
      <input type="hidden" id="ccycleOrg" value="{{ $cycle->organization_id }}" >
      <div class="col-12 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="d-flex gap-6">
              <div class="d--flex flex-shrink-0 w-px-100">
                <div class="w-px-70 h-px-100">
                  @if(!empty($cycle->organization->image_url))
                  @php
                  $imageUrl = $cycle->organization->image_url;
              
                   // استخراج المسار من الرابط
                   $imagePath = parse_url($imageUrl, PHP_URL_PATH);
              
                   // إزالة أي أجزاء غير ضرورية في البداية مثل `/dashboard/uploads/files/`
                   $fileN = ltrim(str_replace('/dashboard/uploads/files/', '', $imagePath), '/');
              @endphp
                  <img src="/{{ $imageUrl }}" class="w-100 h-100 border radius-15"
                    style="object-fit: contain;" alt="Program Image" />
                  @else
                  <img src="/images/background/logo.png" class="w-100 h-100 border radius-15"
                  style="object-fit: contain;" alt="Program Image" />

                  @endif

                </div>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex flex-column flex-md-row justify-content-between mb-3 mb-md-0">
                  <h4 class="text-primary mb-3 mb-md-0">{{ $cycle->title }}</h4>                 
                  <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                    @if ($cycle->cohort)
                      <div class="d-flex align-items-center">
                        <h6 class="mb-0">{{ $l == 'en' ? 'Cohort' : 'الدفعة' }}:</h6>
                        <span class="badge fs-6 bg-label-primary ms-2">{{ $cycle->cohort }}:</span>
                      </div>
                    @endif
                    @if(!empty($cycle->private))
                      <div class="btn btn-transparent" onclick="copyToClipboard('{{ url('dashboard/cycles/view/' . $cycle->private) }}')" >
                      {{__('cycles.Private Link')}}
                        <i class="ti ti-circle-check ms-2 text-success ti-24px"></i>
                      </div>
                    @endif
                  </div>
                </div>

                <h5 class="mb-3">{{ $cycle->organization['name_' . $l] }}</h5>

                <div class="row border radius-15 py-3 mb-3">
                  @foreach ($orgCountList as $item)
                    <div class="col-md-4 mb-2">
                      <div class="d-flex flex-nowrap shrink align-items-center">
                        <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                          <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                        </div>
                        <div class="d-flex flex-nowrap align-items-center">
                          <span class="me-2 fw-semibold text-capitalize ">{{ $item['label'] }}</span>
                          <small class="text-nowrap bg-light text-secondary py-1 px-2 rounded-pill commanums">
                            {{ $item['count'] }}
                          </small>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
                <div class="row">
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-user-circle text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Mentors')}}</span>
                      </div>
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($mentors as $mentor)
                        <a class="btn btn-sm btn-outline-primary rounded-pill" style="height: 30px;" target="_blank"
                          href="{{route('mentors.profile_view', $mentor->id)}}">
                          @if(!empty($mentor->image_url))
                          <img src="{{$mentor->image_url ?? '/icons/account.svg'}}"
                            onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}" width="20" height="20" class="rounded-circle" />
                          @else
                          <i class="ti ti-user"></i>
                          @endif
                          <span class="mx-1 fw-semibold">{{$mentor->name}}</span>
                        </a>
                        @endforeach
                       
                      </div>
                    </div>
                    <div class="d-flex flex-column my-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center ps-7">
                        <p class="text-muted">
                          @if(!empty($cycle->description))
                              {{ $cycle->description }}
                              @else
                              {{__('cycles.NA')}}
                              @endif
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-1" style="width: fit-content;">
                        <h6 class="mb-1 me-2 text-center fw-semibold">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-secondary">
                          <i class="ti ti-calendar ti-20px text-info"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($cycle->start, 'Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-secondary">
                          <i class="ti ti-calendar ti-20px text-danger"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($cycle->end, 'Y-m-d') }}</span>
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
                            <button id="editButton" class="dropdown-item text-dark">
                              <i class="ti ti-file-export me-2 text-warning"></i>
                              {{ __('cycles.Export_Data') }}
                            </button>
                          </li>
                          <!-- <li>
                            <button id="editButton" class="dropdown-item text-dark">
                              <i class="ti ti-stack-2 me-2 text-danger"></i>
                              {{ __('cycles.Edit_Step') }}
                            </button>
                          </li> -->
                          <li>
                            <a 
                              id="editButton" 
                              class="dropdown-item ccycle text-dark" 
                              {{-- onclick="onEditProgramClick({{ $cycle->id }})" --}}
                              href="/dashboard/programlist/edit/{{ $cycle->id }}"
                            >
                              <i class="ti ti-edit me-2 text-info"></i>
                              {{ __('cycles.Edit_Program') }}
                            </a>
                          </li>
                          <li>
                            @if( $_user->ableTo('delete_user', $permissions) )
                            <button class="dropdown-item text-dark" id="deleteButton">
                              <i class="ti ti-trash me-2 text-danger"></i>
                              {{ __('cycles.Delete') }}
                            </button>
                            @endif
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


        
        @endphp


      @if($step->stype == 1)
      <div class="col-12 mb-4">
        <div class="card">
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
                          <button onclick="ModifyStep(1,{{ $step->id }})"
                          id="{{ $step->id }}" class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @if( $_user->ableTo('delete_user', $permissions) )
                          <button class="dropdown-item text-danger" onclick="deleteStep(this.id)" id="{{ $step->id }}">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endif
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
                      <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Startups') }}</span>
                      <small>{{ $totalcompanystips }}</small>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-9">
                    {{-- <div class="d-flex align-items-center">
                      <i class="ti ti-notes text-primary ti-20px me-2"></i>
                      <div class="px-4 py-1 bg-light text-dark rounded-pill" style="max-width: 85%;">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias consequatur.
                      </div>
                    </div> --}}
                    <div class="d-flex flex-column my-3">
                      @if($step->description != '')
                        <div class="d-flex align-items-center my-2">
                          <i class="ti ti-message text-primary ti-20px me-2"></i>
                          <h6 class="mb-0">{{__('admin.Description')}}</span>
                        </div>
                        <div class="d-flex flex-wrap px-4 py-1 bg-light text-dark rounded-pill" style="max-width: 85%;">
                          {{ $step->description }}
                        </div>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex justify-content-between flex-md-column gap-1" style="width: fit-content;">
                        <h6 class="mb-1 me-2 text-center fw-semibold">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($step->from, 'Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($step->to, 'Y-m-d') }}</span>
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
          @php

        $countJudges = count(json_decode($step->list, true)??[]) ?? 0 ;///
        $scoped_judges = $judges->whereIn('id', json_decode($step->list, true) ?? []);
        
          @endphp
      <div class="col-12 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="row mt-4 mt-md-0">
              <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <a href="#" class="btn btn-outline-primary rounded-pill">
                    {{ __('cycles.Screening') }}
                  </a>
                  <div class="d-flex align-items-center gap-4">
                    <h6 class="mb-0">{{ __('cycles.steps') }} {{ $step->arr }}</h6>
                    <a href="/dashboard/screening/results/{{ $step->id }}" class="btn btn-primary">
                      <i class="ti ti-file-analytics me-2"></i> {{ __('admin.Results') }}
                    </a>
                    <a href="/dashboard/{{ $step->id }}/screening" class="btn btn-primary">
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
                          <button onclick="ModifyStep(1,{{ $step->id }})"
                            id="{{ $step->id }}" class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @if( $_user->ableTo('delete_user', $permissions) )
                          <button class="dropdown-item text-danger" onclick="deleteStep(this.id)" id="{{ $step->id }}">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endif
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
                      <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Judgers') }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ count($scoped_judges) }}
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-primary me-2 p-2">
                      <i class="ti ti-building-skyscraper text-primary ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Startups') }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $TotalscreenStepsIds }}
                      </small>
                    </div>
                  </div>
                  @php
                  $companys = \App\Company::where('step', $step->id)->get();
                  $totalfiltration = 0;
                  $totalfiltrationPinding = 0;
                  $totalfiltrationDone = 0;
                  foreach ($companys as $key => $company) {
                    $totalfiltration = app(\App\FinalData::class)->where('step_id', '=', $step->id)->where('company_id', '=', $company->id)->count();
                    $totalfiltrationPinding = app(\App\FinalData::class)->where('step_id', '=', $step->id)->where('company_id', '=', $company->id)->whereNull('answer')->count();
                    $totalfiltrationDone = app(\App\FinalData::class)->where('step_id', '=', $step->id)->where('company_id', '=', $company->id)->whereNotNull('answer')->count();
                  }
                    // $totalfiltration = app(\App\Formdata::class)->where('step_id', '=', $step->id)->where('company_id', '=', $step->id)->count();
                  @endphp
                
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-warning me-2 p-2">
                      <i class="ti ti-transform text-warning ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Filtration') }} </span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $totalfiltration }}
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-info me-2 p-2">
                      <i class="ti ti-rotate-clockwise-2 text-info ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Filtration_pending')  }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $totalfiltrationPinding }}
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-success me-2 p-2">
                      <i class="ti ti-checkbox text-success ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Filtration_done') }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $totalfiltrationDone }}
                      </small>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-between w-100">
                  <div class="d-flex align-items-center gap-3 mb-3">
                    @if (count($scoped_judges) > 0)
                      <div class="d-flex align-items-center">
                        <i class="ti ti-gavel text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">{{ __('admin.Judgers') }}</span>
                      </div>
                      @endif
                  </div>
                  <div class="ms-auto">
                    <form action="/dashboard/programlist/Email" method="post" id="myForm">
                      @csrf
                      <input type="hidden" value="{{ $step->id }}" name="step">
                      <a id="private-link" role="button" onclick="document.getElementById('myForm').submit(); return false;" class="text-nowrap fw-semibold">
                        {{ __('cycles.Notify them') }}
                        <i class="ti ti-circle-check text-success ti-24px"></i>
                      </a>
                    </form>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($scoped_judges as $judge)
                        <a class="btn btn-sm btn-outline-warning rounded-pill" style="height: 30px;" target="_blank"
                          href="{{route('mentors.profile_view', $judge->id)}}">
                          @if(!empty($judge->image_url))
                          <img src="{{$judge->image_url ?? '/icons/account.svg'}}"
                            onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}" width="20" height="20" class="rounded-circle" />
                          @else
                          <i class="ti ti-user"></i>
                          @endif
                          <span class="mx-1 fw-semibold">{{$judge->name}}</span>
                        </a>
                        @endforeach
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      @if(!empty($step->description))
                        <div class="d-flex align-items-center mb-1">
                          <i class="ti ti-message text-primary ti-20px me-2"></i>
                          <h6 class="mb-0">{{__('admin.Description')}}</span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center ps-7">
                          <p class="text-muted">
                            {{ $step->description }}
                          </p>
                        </div>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-1" style="width: fit-content;">
                        <h6 class="mb-1 me-2 text-center fw-semibold">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($step->from, 'Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($step->to, 'Y-m-d') }}</span>
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
         
        // $mentoreCountRequest = app(App\MentorshipRequest::class)
        // ->where('cycle_id', '=', $step->cycle_id)
        // ->distinct()
        // ->count('mentor_id');
        $mentoreCountRequest = App\CyclesMentors::where('cycle_id', $step->cycle_id)
        ->count();

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
        <div class="card">
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
                      <a id="mentors" href="/dashboard/mentorship/assign/{{ $step->cycle_id }}" class="btn btn-primary">
                        <i class="ti ti-user me-2"></i> {{ __('admin.Mentors') }}
                      </a>
                      <a id="manage" href="/dashboard/mentorship/view/{{ $step->cycle_id }}" class="btn btn-primary">
                        <i class="ti ti-settings me-2"></i> {{ __('admin.Manage') }}
                      </a>
                      <a id="sessions" href="/dashboard/mentorship/sessions?show=all&cycle_id={{ $step->cycle_id }}" class="btn btn-primary">
                        <i class="ti ti-cast me-2"></i> {{ __('admin.Sessions') }}
                      </a>
                      {{-- <a id="results" href="#" class="btn btn-primary">
                        <i class="ti ti-file-analytics me-2"></i> {{ __('admin.Results') }}
                      </a> --}}
                    </div>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <button onclick="ModifyStep(1,{{ $step->id }})"
                            id="{{ $step->id }}" class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @if( $_user->ableTo('delete_user', $permissions) )
                          <button class="dropdown-item text-danger" onclick="deleteStep(this.id)" id="{{ $step->id }}">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endif
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
                      <div class="badge bg-label-secondary me-2 p-2">
                        <i class="ti ti-user text-secondary ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Mentors') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $mentoreCountRequest }}
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
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Mentorship_sessions_duration_(hours)') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $totalsessionsMen }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-info me-2 p-2">
                        <i class="ti ti-database text-info ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Mentorship_done_requests_count') }}</span>
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
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('cycles.All requests') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $mentorship_requests }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-warning me-2 p-2">
                        <i class="ti ti-new-section text-warning ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('cycles.New requests only') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $new_requests }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-success me-2 p-2">
                        <i class="ti ti-checkbox text-success ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('cycles.approved requests') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $approved_requests }}
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-danger me-2 p-2">
                        <i class="ti ti-trash-x text-danger ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('cycles.disapproved requests') }}</span>
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
                        <span class="me-2 fw-medium text-capitalize fs-6 dark-black">mentorship pending requests count</span>
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
                          href="{{route('mentors.profile_view', $mentor->id)}}">
                          @if(!empty($mentor->image_url))
                          <img src="{{$mentor->image_url ?? '/icons/account.svg'}}"
                            onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}" width="30" height="30" class="rounded-circle" />
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
                    @if (!empty($step->description))
                      <div class="d-flex flex-column my-3">
                        <div class="d-flex align-items-center my-b-1">
                          <i class="ti ti-message text-primary ti-20px me-2"></i>
                          <h6 class="mb-0">{{__('admin.Description')}}</span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center ps-7">
                          <p class="text-muted">{{ $step->description }}</p>
                        </div>
                      </div>
                    @endif
                  </div>
                  <div class="col-md-3">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-1" style="width: fit-content;">
                        <h6 class="mb-1 me-2 text-center fw-semibold">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($step->from, 'Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($step->to, 'Y-m-d') }}</span>
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
        <div class="card">
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
                      <a id="results" href="/dashboard/training/results" class="btn btn-primary">
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
                          <button onclick="ModifyStep(1,{{ $step->id }})"
                            id="{{ $step->id }}" class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @if( $_user->ableTo('delete_user', $permissions) )
                          <button class="dropdown-item text-danger" onclick="deleteStep(this.id)" id="{{ $step->id }}">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endif
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
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Total_sessions') }}</span>
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
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Training sessions duration (hours)') }}</span>
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
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('cycles.sessions') }}</span>
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
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('cycles.online') }}</span>
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
                        <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('cycles.offline') }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $totaloffline }}
                        </small>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="row">
                  <div class="col-md-9">
                    @if (!empty($step->description))
                    <div class="d-flex flex-column my-3">
                      <div class="d-flex align-items-center mb-1">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center ps-7">
                        <p class="text-muted">{{ $step->description }}</p>
                      </div>
                    </div>
                    @endif
                  </div>
                  <div class="col-md-3">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-1" style="width: fit-content;">
                        <h6 class="mb-1 me-2 text-center fw-semibold">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($step->from, 'Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($step->to, 'Y-m-d') }}</span>
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

         // Filter Screening
        $finalScreenings = App\Finalscreening::where('step_id', '=', $step->id)->first();
       
        // حساب عدد السجلات لكل step_id
          // حساب عدد السجلات لكل step_id
          $totalFinalscreenCount = App\Finalscreening::where('step_id', '=', $step->id)->distinct()
              ->count();
          // حساب عدد company_id الفريدة لكل step_id
          $totalDistinctCompanies = app(App\Finalscreening::class)
              ->where('step_id', '=', $step->id)
              ->distinct()
              ->count('company_id');
              if(isset($finalScreenings->judges)){
          // حساب عدد المحكمين
            $finaljudges = is_string($finalScreenings->judges) ? json_decode($finalScreenings->judges, true) : $finalScreenings->judges;
              $judgeData = app(App\User::class)->whereIn('id', $finaljudges)->get();
              // إضافة بيانات المحكمين إلى المصفوفة
              $judgesDetails = $judgesDetails->merge($judgeData)->unique('id');
              }
          $answers = app(App\FinalData::class)->whereNotNull('answer')->where('step_id', $step->id)->count();
        
          $answerspinding = app(App\FinalData::class)->where('step_id', $step->id)->whereNull('answer')->count();

          $answersdone = $answers - $answerspinding;

  
        @endphp
        <div class="card">
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
                          <button onclick="ModifyStep(1,{{ $step->id }})"
                            id="{{ $step->id }}" class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @if( $_user->ableTo('delete_user', $permissions) )
                          <button class="dropdown-item text-danger" onclick="deleteStep(this.id)" id="{{ $step->id }}">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endif
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
                      <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Judgers') }}</span>
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
                      <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Startups') }}</span>
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
                      <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Evaluation') }}</span>
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
                      <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Traditional_Evaluation_pending') }} </span>
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
                      <span class="me-2 fw-medium text-capitalize fs-6 text-dark fw-semibold">{{ __('admin.Evaluation_done') }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $answersdone }}
                      </small>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-between w-100">
                  <div class="d-flex align-items-center gap-3 mb-3">
                    @if (count($judgesDetails) > 0)
                      <div class="d-flex align-items-center">
                        <i class="ti ti-gavel text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">{{ __('admin.Judgers') }}</span>
                      </div>
                      @endif
                  </div>
                  <div class="ms-auto">
                    <form action="/dashboard/programlist/Email" method="post" id="myForm">
                      @csrf
                      <input type="hidden" value="{{ $step->id }}" name="step">
                      <a id="private-link" role="button" onclick="document.getElementById('myForm').submit(); return false;" class="text-nowrap fw-semibold">
                        {{ __('cycles.Notify them') }}
                        <i class="ti ti-circle-check text-success ti-24px"></i>
                      </a>
                    </form>
                  </div>
                </div>
                <div class="row mt-2">
                  <div class="col-md-10">
                    <div class="d-flex flex-column">
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($judgesDetails as $judge)
                        <a class="btn btn-sm btn-outline-warning rounded-pill" style="height: 30px;" target="_blank"
                          href="{{route('mentors.profile_view', $judge->id)}}">
                          @if(!empty($judge->image_url))
                          <img src="{{$judge->image_url ?? '/icons/account.svg'}}"
                            onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}" width="20" height="20" class="rounded-circle" />
                          @else
                          <i class="ti ti-user"></i>
                          @endif
                          <span class="mx-1 fw-semibold">{{$judge->name}}</span>
                        </a>
                        @endforeach
                      </div>
                    </div>
                    @if (empty($step->description))
                      <div class="d-flex flex-column">
                        <div class="d-flex align-items-center mb-1">
                          <i class="ti ti-message text-primary ti-20px me-2"></i>
                          <h6 class="mb-0">{{__('admin.Description')}}</span>
                        </div>
                        <div class="d-flex flex-wrap ps-7 text-muted">
                          {{ $step->description }} 
                        </div>
                      </div>
                    @endif
                  </div>
                  <div class="col-md-2 mt-4 mt-md-0">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-1" style="width: fit-content;">
                        <h6 class="mb-1 me-2 text-center fw-semibold">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($step->from, 'Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($step->to, 'Y-m-d') }}</span>
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
  {{-- <div class="content-backdrop fade"></div> --}}
</div>

<!-- Custom Step Modal -->
  <div 
    class="modal fade" 
    id="customStepModal" 
    tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <form class="form" id="edit-step-form" action= "{{ URL::to('dashboard/cycles/modify') }}" method="POST"
          enctype="multipart/form-data" autocomplete="off">
          {!! csrf_field() !!}
          
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel33">{{ __('cycles.Modify current step') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

        <div class="modal-body">
          <input type="hidden" name="sid" id="sid">
          <input type="hidden" name="type" id="type7" value="8">

          <fieldset class="row">
            <div class="col-12 form-group mb-3">
              <label class="form-label" for="mFormSelect">{{ __('cycles.Choose form') }}: </label>
              <select class="select form-control" id="mFormSelect" name="mFormSelect" required>
                {{-- @foreach (\App\BuilderForm::all() as $form) --}}
                {{-- <option value="{{$form->id}}" >{{$form->form_name}}</option> --}}
                {{-- @endforeach --}}
              </select>
            </div>

            <div class="col-12 form-group mb-3 public-data-form-fields">
              <label class="form-label" for="is_public">{{ __('cycles.Step Visibility') }}: </label>
              <select class="select form-control" id="is_public" name="is_public">
                <option value="0">{{ __('cycles.Step Visible To Private') }}</option>
                <option value="1">{{ __('cycles.Step Visible To Public') }}</option>
              </select>
            </div>
            <div class="col-12 form-group mb-3 public-data-form-fields" id="public_slug_div">
              <label class="form-label" for="public_slug">{{ __('cycles.Public URL') }}: </label>
              <input type="text" id="public_slug" name="public_slug" class="form-control" value="" placeholder="{{ __('cycles.Public URL placeholder') }}" >
            </div>

            <div class="mb-2" id="mmenu2">
              <label class="form-label">{{ __('cycles.Choose screening type') }}: </label>
              <div>
                <select class="select2 form-control" id="mScreeningSelect" name="mScreeningSelect" required>
                  <option selected disabled> {{ __('cycles.Select type') }}</option>
                  <option value="onetoall">{{ __('cycles.Assign One to all users') }}</option>
                  <option value="grouptoall">{{ __('cycles.Assign Group to all users') }}</option>
                  <option value="grouptogroup">{{ __('cycles.Assign Group to group of users') }}</option>
                </select>
              </div>
            </div>


            <div class="col-12 form-group mb-3">
              <label class="form-label" for="step-name">{{ __('cycles.Form title') }}:</label>
              <input id='mtitle' name='title' type="text" placeholder="{{ __('cycles.Title') }}"
              class="form-control" required>
            </div>
            <div class="col-12 mb-4">
              <label for="step-range-picker" class="form-label">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
              <input type="hidden" name="fromDate" id="mfromDate">
              <input type="hidden" name="toDate" id="mtoDate">
              <input type="text" id="step-range-picker" class="form-control" name="date_range" />
            </div>
            {{-- <div class="col-md-6 form-group mb-3">
              <label class="form-label" for="flatpickr-date">{{ __('cycles.Valid from') }}: </label>
              <div class="position-relative">
                <input id='mfromDate' name='fromDate' type="text" class="form-control flatpickr-date" required>
                <div class="position-absolute top-0 end-0 mt-2 me-3">
                  <i class="ti ti-calendar ti-24px text-primary"></i>
                </div>
              </div>
            </div>
            <div class="col-md-6 form-group mb-3">
              <label class="form-label" for="flatpickr-date">{{ __('cycles.Valid to') }}:</label>
              <div class="position-relative">
                <input id='mtoDate' name='toDate' type="text" class="form-control flatpickr-date" required>
                <div class="position-absolute top-0 end-0 mt-2 me-3">
                  <i class="ti ti-calendar ti-24px text-primary"></i>
                </div>
              </div>
            </div> --}}
            <div class="col-12 form-group mb-5">
              <label class="form-label" form="step-desc">{{ __('cycles.Description') }}: </label>
              <textarea id='mdescription' name='description' placeholder="{{ __('cycles.Description') }}" class="form-control"></textarea>
          
            </div>
          </fieldset>
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

        </form>
      </div>
    </div>
  </div>
<!-- / Custom Step Modal -->
<!-- Screening Step Modal -->
  {{-- <div 
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
                  class="form-control" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
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
                    class="form-control" 
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
                  class="form-control" 
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
  </div> --}}
<!-- / Screening Step Modal -->
<!-- Mentorship Step Modal -->
  {{-- <div 
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
                  class="form-control" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
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
                    class="form-control" 
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
                  class="form-control" 
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
  </div> --}}
<!-- / Mentorship Step Modal -->
<!-- Training Step Modal -->
  {{-- <div 
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
                  class="form-control" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
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
                    class="form-control" 
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
                  class="form-control" 
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
  </div> --}}
<!-- / Training Step Modal -->
<!-- Final Screening Step Modal -->
  {{-- <div 
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
                  class="form-control" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
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
                    class="form-control" 
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
                  class="form-control" 
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
  </div> --}}
<!-- / Final Screening Step Modal -->

  {{-- @include('admin.programs.partials._edit_program_modal', ['mode' => 'edit']) --}}
@stop

@section('scripts')
<!-- Vendors JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
<script src="/app-assets/vendors/js/extensions/dragula.min.js"></script>
<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
@stop

@section('pagescripts')
  @php($ccycle = \App\Cycle::find($cycle->id))

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
  alert("{{ __('admin.Copied') }}");
}
    // Set Language
    const lang = '{{ $l }}';

    // document.addEventListener('DOMContentLoaded', () => {
    //   console.log('loaded');
    //   var bsRangePickerBasic = $('#bs-rangepicker-basic');
    //   if (bsRangePickerBasic.length) {
    //     bsRangePickerBasic.daterangepicker({
    //       opens: isRtl ? 'left' : 'right'
    //     });
    //   }
    // });

    // const editValidFrom = document.querySelector('#edit-program-modal .valid-from');
    // const editValidalidTo = document.querySelector('#edit-program-modal .valid-to');
  
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

    // Handle Append File
    // const appendFile = (mode, file, conainerId) => {
    //   console.log('file', file)
    //   if (file) {
    //     const fileName = file.split('/').pop();
    //     const content = (`
    //       <div class="d-flex align-items-center justify-content-between border p-2 rounded mb-2">
    //         <div class="d-flex align-items-center gap-2">
    //           <i class="ti ti-file fs-4 text-warning"></i>
    //           <span class="text-truncate" style="max-width: 200px;">${fileName}</span>
    //         </div> 
    //         <a class="btn btn-label-primary btn-icon btn-sm" href="/storage/${file}" target="_blank" download="${file}">
    //           <i class="ti ti-download fs-5"></i>
    //         </a>
    //       </div>`
    //     );
    //     $(`#${mode}-program-modal ${conainerId}`).append(content);
    //   }
    // };

    // Initialize Quill Editor
    // const editorConfig = { theme: 'snow' };
    // const editGuidelineEditor = new Quill('#edit-program-modal #guideline-editor', editorConfig);
    // const editFinancialEditor = new Quill('#edit-program-modal #financial-editor', editorConfig);
    // const editLegalEditor = new Quill('#edit-program-modal #legal-editor', editorConfig);

    // Clear Editor
    // editGuidelineEditor.root.innerHTML = '';
    // editFinancialEditor.root.innerHTML = '';
    // editLegalEditor.root.innerHTML = '';

    // Handle Editor Change
    // const handleEditorChange = (editor, input) => {
    //   editor.on('text-change', function() {
    //     $(input).val(editor.root.innerHTML);
    //   });
    // };

    // handleEditorChange(editGuidelineEditor, '#edit-program-modal #program_guideline');
    // handleEditorChange(editFinancialEditor, '#edit-program-modal #financial_policy');
    // handleEditorChange(editLegalEditor, '#edit-program-modal #legal_policy');

    // Handle Edit Program
    // const onEditProgramClick = (cycleId) => {
    //   if(cycleId){
    //     $('#edit-program-modal').modal('show');
      
    //     $.ajaxSetup({
    //       headers: {
    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //       }
    //     });

    //     $.ajax({
    //       type: "POST",
    //       url: "/dashboard/cycles/modify",
    //       data: {"cycleid": cycleId },
    //       success: function(response) {
    //         console.log(response); 

    //         $('#edit-program-modal #date-range-picker').daterangepicker({
    //           opens: isRtl ? 'left' : 'right',
    //           drops: 'up',
    //           startDate: response?.start ? moment(response?.start) : '',
    //           endDate: response?.end ? moment(response?.end) : '',
    //         }).on('apply.daterangepicker', function(ev, picker) {
    //           $('#edit-program-modal #start').val(picker.startDate.format('YYYY-MM-DD'));
    //           $('#edit-program-modal #end').val(picker.endDate.format('YYYY-MM-DD'));
    //         });

    //         $('#edit-program-modal #title').val(response.title ?? '');
    //         $('#edit-program-modal #description').val(response.description ?? '');
    //         $('#edit-program-modal #cycleid').val(cycleId); 
    //         $('#edit-program-modal #type').val(1);
    //         // $('#edit-program-modal #start').val(response.start ?? '');
    //         // $('#edit-program-modal #end').val(response.end ?? '');
    //         $('#edit-program-modal #organization').val(response.organization_id ?? '');
    //         $('#edit-program-modal #allow_non_company').prop('checked', response.allow_non_company == 1 ? true : false);
    //         $('#edit-program-modal #auto-approve-mentorship-requests').prop('checked', response.auto_approve_mentorship_requests == 1 ? true : false);

    //         if (response.private) {
    //           $('#edit-program-modal #private').prop('checked', true);
    //           $('#cyclelink').html('{{ __('cycles.Join URL') }}');
    //           $('#cyclelink').attr('href', '/dashboard/cycles/view/' + response.id + '?id=' + response.private);
    //         }

    //         if (editGuidelineEditor) {
    //           editGuidelineEditor.root.innerHTML = response.program_guideline ?? '';
    //         }

    //         $('#edit-program-modal #program_guideline').val(response.program_guideline ?? '');

    //         if (editFinancialEditor) {
    //           editFinancialEditor.root.innerHTML = response.financial_guideline ?? '';
    //         }
    //         $('#edit-program-modal #financial_policy').val(response.financial_guideline ?? '');
    //         $('#edit-program-modal #budget_allocation').val(response.budget_allocation ?? '');
    //         $('#edit-program-modal #currency_type').val(response.currency_type ?? '');

    //         if (editLegalEditor) {
    //           editLegalEditor.root.innerHTML = response.legal_guideline ?? '';
    //         }
    //         $('#edit-program-modal #legal_policy').val(response.legal_guideline ?? '');
    //         const guidelineFile = response?.program_guideline_file;
    //         const financialFile = response.financial_guideline_file;
    //         const legalFile = response?.legal_guideline_file;
    //         const delegationFile = response.delegation_file;
            
    //         appendFile('edit', guidelineFile, '#guideline-file-container');
    //         appendFile('edit', financialFile, '#financial-file-container');
    //         appendFile('edit', legalFile, '#legal-file-container');

    //         if (delegationFile) {
    //           appendFile('edit', delegationFile, '#delegation-file-container');
    //           $('#edit-program-modal delegation_option').prop('checked', true);
    //         }
    //       },
    //       error: function(error) {
    //         console.log(error);
    //         resetValues();
    //         alert("Error Occurred, Try again!");
    //       }
    //     });
    //   }
    // }; 
  
    // Initialize Quill editors
    const quillEditors = document.querySelectorAll('#text-editor');
    quillEditors.forEach(editor => {
      new Quill(editor, {
        theme: 'snow'
      });
    });
  </script>

  <script>
    (() => {
      console.log('loaded');
      const flatpickerData = document.querySelectorAll('.flatpickr-date');

      flatpickerData.forEach(flatpicker => {
        flatpickr(flatpicker, {
          altInput: true,
          altFormat: 'F j, Y',
          dateFormat: 'Y-m-d',
          defaultDate: '{{ now()->format('Y-m-d') }}',
          minDate: '{{ !is_null($ccycle) ? $ccycle->start->format('Y/m/d') : now()->format('Y/m/d') }}',
          maxDate: '{{ !is_null($ccycle) ? $ccycle->end->format('Y/m/d') : now()->addDays(15)->format('Y/m/d') }}',
        });
      });
    })();
  </script>

{{-- <script>
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
</script> --}}

  <script>
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
              url: '{{ url('dashboard/builder/organization/') }}' + '/' + $("#ccycle").val(),
              success: function(response) {
                $('#FormSelect').empty()
                $('#FormSelect').append(response.appear)
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
                $('#FormSelect').append(response.appear)
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
              console.log('response', response)
              step = response.stype;
              sData = response.data;
              let menu = $('#mmenu' + step);
              $('#mmenu1,#mmenu2').hide();
              $('.public-data-form-fields').hide();
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
                  url: '{{ url('dashboard/builder/organization/') }}' + '/' + $("#ccycleOrg").val(),
                  success: function(response) {
                    $('#mFormSelect').empty();
                    let appends = '';
                    response.data.forEach(function(form) {
                      appends += `<option value="${form.id}" ${form.id == sData ? 'selected' : ''}>${form.form_name}</option>`;
                    });
                    $('#mFormSelect').append(appends);
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
                  url: '{{ url('dashboard/builder/organization/') }}' + '/' + $("#ccycleOrg").val(),
                  success: function(response) {
                    $('#mFormSelect').empty();
                    let appends = '';
                    response.data.forEach(function(form) {
                      appends += `<option value="${form.id}" ${form.id == sData ? 'selected' : ''}>${form.form_name}</option>`;
                    });
                    $('#mFormSelect').append(appends);
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
              $('#customStepModal').modal('show');
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
  </script>
@stop