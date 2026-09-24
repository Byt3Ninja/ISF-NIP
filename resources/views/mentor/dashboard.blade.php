@extends('layouts.admin')

@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.My Dashboard'))

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
@stop

@section('content')
  @php
    $countCards = [
        [
            'color' => 'success',
            'href' => '/dashboard/directory',
            'icon' => 'ti ti-briefcase',
            'text' => __('dashboard.Services'),
            'count' => $services_count,
        ],
        [
            'color' => 'primary',
            'href' => '/dashboard/inbox',
            'icon' => 'ti ti-message',
            'text' => __('dashboard.newmessages'),
            'count' => $nm_count,
        ],
        [
            'color' => 'secondary',
            'href' => '#',
            'icon' => 'ti ti-chalkboard',
            'text' => __('dashboard.Coming sessions'),
            'count' => count($mysessions),
        ],
        [
            'color' => 'warning',
            'href' => '#',
            'icon' => 'ti ti-calendar',
            'text' => __('dashboard.Cycles'),
            'count' => count($mycycles),
        ],
        [
            'color' => 'info',
            'href' => '#',
            'icon' => 'ti ti-server',
            'text' => __('admin.Company Attendance Rate'),
            'count' => $attends_rate,
        ],
        [
            'color' => 'danger',
            'href' => '#',
            'icon' => 'ti ti-star',
            'text' => __('cycles.Rate'),
            'count' => auth()->user()->mrating(),
            'isRating' => true, // Only this card has the isRating key
        ],
        [
            'color' => 'warning',
            'href' => '#',
            'icon' => 'ti ti-select',
            'text' => __('mentor.mentorshipRequests.withoutTasks'),
            'count' => $sessionWithoutTasks,
        ],
        [
            'color' => 'success',
            'href' => '#',
            'icon' => 'ti ti-check',
            'text' => __('mentor.mentorshipTasks.completedTasks'),
            'count' => $completedTasks,
        ],
        [
            'color' => 'info',
            'href' => '#',
            'icon' => 'ti ti-archive',
            'text' => __('mentor.mentorshipTasks.nonCompletedTasks'),
            'count' => $nonCompletedTasks,
        ],
    ];

    $scheduleHeadArr = [
        '#',
        __('dashboard.Days'),
        __('dashboard.Time'),
        __('dashboard.Notes'),
        __('dashboard.Actions'),
    ];

    $sessionsHeadArr = [
        '#',
        __('dashboard.Company'),
        __('dashboard.User'),
        __('dashboard.Date'),
        __('dashboard.Admin Notes'),
        __('dashboard.Meeting Link'),
    ];
    

    $user_id = Auth::id();
    $circle_ids = $_user->mentorCycles()->get()->pluck('id');
    
    if( $_user->hasRole('Mentor') )
  {
    /// Rating
    // GV-9566: date_time / zoom_date are platform wall-clock, so "today" is the platform day.
    $platformToday = \App\Services\TimezoneService::platformToday();
    $notrated_ser = \App\ServicesRequests::where('user_id', $user_id)->where('approved','Yes')->where('date_time','<',$platformToday . ' 17:00:00')->whereNull('rate')->first();
    $notrated_men = \App\MentorshipRequest::where('user_id', $user_id)->where('zoom_date','<',$platformToday . ' 17:00:00')->where('approved','Yes')->whereNull('rating')->first();
    $available_surveys = collect([]);
    $surveyEligible = [];
    // load survey for mentor
    if( !$circle_ids->isEmpty() )
    {
        $myMentorships = \App\MentorshipRequest::where('mentor_id', $user_id)->whereIn('cycle_id', $circle_ids)->where('approved', 'Yes')->get();
        
  if( !empty($myMentorships) )
  {
    foreach( $myMentorships as $mentorship )
    {
      if( empty( $mentorship->zoom_date ) || empty( $mentorship->period ) ) { continue; }
      else
      {
        // GV-9566: zoom_date is the cast instant; compare instants, not strings.
        $endAt = $mentorship->zoom_date->copy()->addMinutes((int) $mentorship->period);
        
        if( now()->gt($endAt) )
        {
          $surveyEligible[] = [
            'triggered_on' => 'mentorship',
            'triggered_at' => $mentorship->id,
            'cycle_id' => $mentorship->cycle_id,
            'triggered_by' => 'session'
          ];
        }
      }
    }
  
    
    $surveyEligible = collect($surveyEligible)->groupBy('triggered_on')->toArray();
  }
      $surveysToAdd = [];
      
      // $surveys = \App\SurveyScopes::where('cycle_id', $circle_id)->whereIn('type', ['mentor', 'both'])->with(['myResponses'])->get();
      $surveys = \App\SurveyScopes::whereIn('cycle_id', $circle_ids)
        ->whereIn('type', ['mentor', 'both'])
        ->with(['myResponses'])
        ->get();

    // تنظيم الاستطلاعات حسب الدورة
    $surveysByCycle = $surveys->groupBy('cycle_id');

    
    if( !empty($myMentorships) ){
      foreach( $surveys as $survey_scope )
      {
        
        $my_responses = $survey_scope->myResponses;
        
        if( $survey_scope->scope == 'mentorship' ) 
        {
          $due = $survey_scope->trigger_by_due;
          $session = $survey_scope->trigger_by_session;
          $mentorship = collect($surveyEligible['mentorship']);
          
          $triggers = [];
          if( $due == true ) { $triggers[] = 'due';  }
          if( $session == true ) { $triggers[] = 'session'; }
          $triggers = array_unique( $triggers );
          
          if( count( $triggers ) == 0 ) { $mentorship = $mentorship->where('triggered_at', null); }
          
          else { $mentorship = $mentorship->whereIn('triggered_by', $triggers); }
          $mentorship = $mentorship->values();
          
          foreach( $mentorship as $conditions )
          {
            
            $checkResponse = $my_responses->where('triggered_on', $conditions['triggered_on'])->where('triggered_at', $conditions['triggered_at'])->where('triggered_by', $conditions['triggered_by'])->first();
           
            if( empty( $checkResponse ) )
            {
             
              
              $surveysToAdd[] = [
                'user_id' => $user_id,
                'survey_id' => $survey_scope->survey_id, 
                'cycle_id' => $conditions['cycle_id'],
                'scope_id' => $survey_scope->id,
                'triggered_on' => $conditions['triggered_on'],
                'triggered_at' => $conditions['triggered_at'],
                'triggered_by' => $conditions['triggered_by'],
                'responses' => Null
              ];
            
              
            }
            
          }
          
        }
        elseif( $survey_scope->scope == 'training' && array_key_exists('training', $surveyEligible) ) 
        {
          $due = $survey_scope->trigger_by_due;
          $quiz = $survey_scope->trigger_by_quiz;
          $session = $survey_scope->trigger_by_session;
          $training = collect($surveyEligible['training']);
          if( $due == true ) { $triggers[] = 'due';  }
          if( $quiz == true ) { $triggers[] = 'quiz'; }
          if( $session == true ) { $triggers[] = 'session'; }
          $triggers = array_unique( $triggers );
          if( count( $triggers ) == 0 ) { $training = $training->where('triggered_at', null); }
          else { $training = $training->whereIn('triggered_by', $triggers); }
          $training = $training->values();
          foreach( $training as $conditions )
          {
            $checkResponse = $my_responses->where('triggered_on', $conditions['triggered_on'])->where('triggered_at', $conditions['triggered_at'])->where('triggered_by', $conditions['triggered_by'])->first();
            if( empty( $checkResponse ) )
            {
              $surveysToAdd[] = [
                'user_id' => $user_id,
                'survey_id' => $survey_scope->survey_id, 
                'cycle_id' => $circle_id,
                'scope_id' => $survey_scope->id,
                'triggered_on' => $conditions['triggered_on'],
                'triggered_at' => $conditions['triggered_at'],
                'triggered_by' => $conditions['triggered_by'],
                'responses' => Null
              ];
            }
          }
        }
        
      }
    }
    
    
      if( !empty($surveysToAdd) ) { \App\SurveyResponses::insert( $surveysToAdd ); }
      
      $available_surveys = \App\SurveyResponses::where('user_id', $user_id)->whereNull('responses')->get();
    }

    

  }
  @endphp





  <!-- Content wrapper -->
  <div class="">
    <div class="">
      <div class="content-body">
        @if (session()->has('msg'))
          <div class="col-12">
            <div class="row">
              <div class="col-md-12">
                <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2" role="alert">
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                      <i class="ti ti-{{ session()->get('class') == 'success' ? 'like' : 'error' }}"></i>
                      <span>
                        {{ __('admin.' . session()->get('msg')) }}
                        {{ Session::forget('msg') }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>
      <!-- Count cards -->
      <div class="row g-6 mb-6">
        @foreach ($countCards as $card)
          <a href="{{ $card['href'] }}" class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-{{ $card['color'] }} h-100">
              <div class="card-body">
                <div class="d-flex justify-content-center align-items-center mb-2">
                  <div class="avatar me-4">
                    <span class="avatar-initial rounded bg-label-primary">
                      <i class="{{ $card['icon'] }} text-{{ $card['color'] }} ti-28px"></i>
                    </span>
                  </div>
                  @if (isset($card['isRating']) && $card['isRating'])
                    <h4 class="mb-0 text-center">{{ intVal($card['count']) }}</h4>
                  @else
                    <h4 class="mb-0 text-center">{{ $card['count'] }}</h4>
                  @endif
                </div>
                <p class="mb-1 text-center">{{ $card['text'] }}</p>
              </div>
            </div>
          </a>
        @endforeach
      </div>
      <!-- / Count cards -->

      <div class="row g-3">
        <!-- Latest Services -->
        <div class="col-sm-6 col-12 mb-6 rounded" style="max-height: 400px; overflow-y: auto;">
          <div class="card">
            <div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom" style="width: '100%">
              <div class="d-flex align-items-center py-2">
                <i class="ti ti-devices-question ti-24px"></i>
                <h5 class="card-title mb-0 mx-2">{{ __('dashboard.Latest services') }}</h5>
              </div>
            </div>
            <div class="card-body">
              @if (count($services_latest) > 0)
                @foreach ($services_latest as $key => $serv)
                  <div 
                    class="d-flex justify-content-between align-items-center mb-3 pb-2 
                    {{ $key !== count($services_latest) - 1 ? 'border-bottom' : '' }}
                    "
                  >
                    <div class="widget-todo-title-area d-flex align-items-center">
                      <a href="/dashboard/directory?find={{ $serv->{'name_' . $l} }}">
                        <span class="text-secondary">
                          <i class="ti ti-link me-2"></i>
                          {{ $serv->{'name_' . $l} }}
                        </span>
                      </a>
                    </div>
                    <div class="widget-todo-item-action d-flex align-items-center">
                      <button class="btn btn-sm btn-label-primary" style="width: 156px;">
                        @if ($serv->serviceCategory)
                          <a href="/dashboard/directory?type={{ $serv->serviceCategory->id }}">
                            {{ $serv->serviceCategory->{'name_' . $l} }}
                          </a>
                        @else
                          <span>{{ __('dashboard.Category not available') }}</span>
                        @endif
                      </button>
                    </div>
                  </div>
                @endforeach
              @else
                <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                  <div class="widget-todo-title-area d-flex align-items-center">
                    <span class="widget-todo-title ml-50">
                      <i class="bx bxs-bell-ring text-danger"></i>
                      {{ __('dashboard.No services added') }}.</span>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
        <!-- / Latest Services -->

        <!-- / Latest Survey -->
        @if( !empty( $available_surveys ) && $available_surveys->count() > 0 )
          @if( $_user->hasRole('Mentor') )
          @if( $_user->ableToAll(['surveys.take', 'surveys.submit'], $permissions) )
          <div class="col-sm-6 col-12 mb-6" style="max-height: 355px; overflow-y: auto; border-radius: 1rem;">
            <div class="card">
              <div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom" style="width: '100%">
                <div class="d-flex align-items-center py-2">
                  <i class="ti ti-user-question mr-1"></i>
                  <h5 class="card-title mb-0 mx-2">{{ __('dashboard.availableSurveysList') }}</h5>
                </div>
              </div>
              <div class="card-body py-6">
                <div class="container-fluid px-0">
                  @foreach( $available_surveys as $survey )
                  <div class="row border-bottom mb-2 pb-2 d-flex align-items-center">
                    <div class="col-9">
                      @if( $survey->triggered_on == 'mentorship' )
                      <i class="ti ti-bulb mx-1"></i> 
                      @elseif( $survey->triggered_on == 'training' )
                      <i class="ti ti-presentation mx-1"></i> 
                      @endif
                      {{ __('dashboard.surveyAbout') . ' ' . __('dashboard.surveyTriggerBy' . ucFirst($survey->triggered_on) . ucFirst($survey->triggered_by) ) }} 
                      @php
                        $middleware = collect([]);
                        if( $survey->triggered_on == 'mentorship' )
                        {
                          if( $survey->triggered_by == 'session' ) { $middleware = $survey->mentorship_session; }
                          if( $survey->triggered_by == 'due' ) { $middleware = $survey->mentorship_step; }
                        }
                        elseif( $survey->triggered_on == 'training' )
                        {
                          if( $survey->triggered_by == 'session' ) { $middleware = $survey->training_session; }
                          if( $survey->triggered_by == 'due' ) { $middleware = $survey->training_step; }
                          if( $survey->triggered_by == 'quiz' ) { $middleware = $survey->training_quiz; }
                        }
                      @endphp
                      @if( $survey->triggered_by == 'due' ) {{ __('dashboard.surveyStepName' . ucFirst($survey->triggered_on) ) }} @endif
                      @if( $survey->triggered_on == 'mentorship' )
                        @if( $survey->triggered_by == 'session' )
                        {{ __('dashboard.with') }} 
                          @php $mentor = $middleware->mentor; @endphp
                          {{ $survey->mentorship_session->user->company->{'name_' . $l} }}
                        @elseif( $survey->triggered_by == 'due' )
                          - {{ $middleware->title }}
                        @else
                          -
                        @endif
                      @elseif( $survey->triggered_on == 'training' )
                        @if( $survey->triggered_by == 'session' )
                          {{ '(' . __('cycles.' . $middleware->type ) . ')' }} {{ $middleware->title }}
                        @elseif( $survey->triggered_by == 'due' )
                          - {{ $middleware->title }}
                        @elseif( $survey->triggered_by == 'quiz' )
                          - {{ $middleware->title }}
                        @else
                          -
                        @endif
                      @endif
                    </div>
                    <div class="col-3 text-end">
                      <a 
                        href="{{ route('surveys.take', ['id' => $survey->id]) }}" 
                        class="btn btn-sm btn-label-primary"
                      >
                        {{ __('dashboard.startSurvey') }}
                      </a>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
          @endif
          @endif
        @endif
        <!-- Latest Survey -->
      </div>
      <!-- Latest Events -->
      <div class="col-12 mb-6">
        <div class="card">
          <div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom">
            <div class="d-flex align-items-center py-2">
              <i class="ti ti-calendar-event ti-24px"></i>
              <h5 class="card-title mb-0 mx-2">{{ __('dashboard.Latest Events') }}</h5>
            </div>
          </div>
          <div class="card-body">
            @if (count($event_latest) > 0)
              @foreach ($event_latest as $event)
                <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                  <div class="widget-todo-title-area d-flex align-items-center">
                    <a href="/dashboard/calendar">
                      <span class="widget-todo-title ml-50 text-secondary">
                        <i class="ti ti-link"></i>
                        {{ $event->title }}
                      </span>
                    </a>
                  </div>
                  <div
                    class="btn btn-label-success text-success py-1 rounded d-flex align-items-center justify-conetnt-center"
                    style="width: 200px; height: 30px;">
                    {{ date('d-m-Y', strtotime($event->start)) }}
                  </div>
                </div>
              @endforeach
            @else
              <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                <div class="widget-todo-title-area d-flex align-items-center">
                  <span class="widget-todo-title ml-50">
                    <i class="bx bxs-bell-ring text-danger"></i>
                    {{ __('dashboard.No Events added') }} '<a
                      href="/dashboard/calendar">{{ __('dashboard.Add yours now') }}</a>'.</span>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
      <!-- / Latest Events -->

      <!-- Schdule Meetings Tabls -->
      <div class="card mt-10">
        <h5 class="card-header d-flex justify-content-between align-items-center" style="width: 100%">
          <span style="text-transform: capitalize">
            <i class="ti ti-calendar-plus ti-24px"></i>
            {{ __('dashboard.My current schedule') }}
          </span>
          <button
            id="addSchedule" 
            onclick="
              this.querySelector('.spinner-border').classList.remove('d-none');
              modify(1, undefined, 0)" type="button" class="btn btn-sm btn-label-primary"
          >
            {{ __('dashboard.Schedule') }}
            <i class="ti ti-calendar ms-2"></i>
            <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
          </button>
        </h5>
        @if (count($myschedules))
          <div class="table-responsive text-nowrap">
            <table class="table">
              <thead>
                <tr>
                  @foreach ($scheduleHeadArr as $head)
                    <th>{{ $head }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @php
    $groupedSchedules = $myschedules->groupBy('days'); // تجميع الجداول حسب الأيام
@endphp

@foreach ($groupedSchedules as $day => $schedules)
    @foreach ($schedules as $schedule)
        <tr class="text-{{ $schedule->active ? 'primary' : 'danger' }}">
            <td>{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
            <td>{{ __('admin.Weekly') }} ({{ __('admin.'.$day) }})</td>
            <td>{{ __('dashboard.from') . ' ' . substr($schedule->tfrom, 0, 5) . ' ' . __('dashboard.to') . ' ' . substr($schedule->tto, 0, 5) }}</td>
            <td>{{ $schedule->notes ? $schedule->notes : '...' }}</td>
            <td>
                <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="ti ti-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu">
                        <button class="dropdown-item" onclick="modify(1, undefined, 0)" type="button">
                            <i class="ti ti-pencil me-2"></i> {{ __('admin.Edit') }}
                        </button>
                        <button class="dropdown-item" onclick="modify(2, {{ $schedule->id }}, {{ $schedule->active }})" type="button">
                            <i class="ti ti-{{ $schedule->active ? 'eye' : 'eye-off' }} me-2"></i>
                            {{ $schedule->active ? __('admin.Deactivate') : __('admin.Activate') }}
                        </button>
                        <button onclick="modify(3,{{ $schedule->id }},0)" type="button" class="dropdown-item">
                            <i class="ti ti-trash me-2"></i> {{ __('admin.Delete') }}
                        </button>
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
@endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
      <!--/ Schdule Meetings Tabls -->

      <!-- Mentor Sessions Table -->
      <div class="card mt-10">
        <h5 class="card-header" style="text-transform: capitalize">
          <i class="ti ti-calendar-stats ti-24px"></i>
          {{ __('dashboard.My next mentorship sessions') }}
        </h5>
        @if (count($mysessions))
          <div class="table-responsive text-nowrap">
            <table class="table">
              <thead>
                <tr>
                  @foreach ($sessionsHeadArr as $head)
                    <th>{{ $head }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach ($mysessions as $key => $session)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                      <a class="text-dark" href="/dashboard/companyProfile/{{ $session->user->company->id }}">
                        <img src="{{ $session->user->company->logo_url }}" alt="avatar" class="rounded-circle"
                          height="40" width="40">
                        <span class="fw-medium"> {{ $session->user->company->name }}</span>
                      </a>
                    </td>
                    <td>
                      <a class="text-dark d-flex align-items-center gap-2" {{-- href="/dashboard/sho/{{ $session->user->id }}" --}}>
                        <img src="{{ $session->user->image_url }}" alt="avatar" class="rounded-circle"
                        height="35" width="35" style="border: 2px solid #ddd;">
                        <div class="media-body">
                          <h6 class="media-heading mb-0" style="font-size: 14px;">{{ $session->user->name }}</h6>
                          <span class="font-small-2" style="font-size:11px;">{{ $session->user->title }}</span>
                        </div>
                      </a>
                    </td>
                    <td>
                      {{ optional(\App\Services\TimezoneService::forViewer($session->zoom_date, $_user))->format('Y-m-d H:i:s') }} ({{ $session->period }}M)
                    </td>
                    <td>
                      {{ $session->admin_comment ?? '...' }}
                    </td>
                    <td>
                      @if ($session->is_today)
                      <a href="{{ $session->start_url }}" target="_blank">
                        <button title="Start Meeting" type="button" data-value="{{ $session->id }}" type="button"
                          class="btn btn-label-primary rounded">
                          <i class="ti ti-user"></i>
                        </button>
                      </a>
                      @else
                        <i class="ti ti-alarm"></i> {{ __('admin.Meeting_will_start_at_time') }}
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="card-body">
            <div class="widget-todo-title-area d-flex align-items-center">
              <span class="widget-todo-title ml-50">
                {{ __('dashboard.No sessions added yet') }}.</span>
            </div>
          </div>
        @endif
      </div>
      <!--/ Mentor Sessions Table -->
    </div>

    @include('mentor.partials._availability_slots')

    <!-- Modal
    <div class="modal fade" id="SendRequest" tabindex="-1" aria-labelledby="sendrequest" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <form method="post" action="/dashboard/mentors/modify" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-header">
              <h5 class="modal-title">{{ __('dashboard.Add new') }} <span id="rname"></span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              @csrf
              <div class="mb-3">
                <label for="type" class="form-label">{{ __('dashboard.Choose type') }}:</label>
                <select name="type" class="select2 form-select" id="type" onchange="SelectType(this.value)"
                  required>
                  {{-- <option value="OneDay">{{ __('dashboard.Add one day') }}</option> --}}
                  <option value="Weekly">{{ __('dashboard.Select weekly days and times') }}</option>
                  {{--  <option value="WorkingDays">{{__('dashboard.Add working days times')}}</option>  --}}
                  {{-- <option value="Period">{{ __('dashboard.Add period') }}</option> --}}
                </select>
              </div>
              <input type="hidden" name="rtype" value="0" id="rtype">
              <input type="hidden" name="id" value="0" id="sid">
              <div id="showdays" class="mb-3" >
                <label for="days" class="form-label"
                  style="text-transform: capitalize">{{ $l === 'ar' ? 'اختر الايام' : 'Selecte days' }}:</label>
                <select name="days[]" class="select2 form-select" multiple id="days">
                  <option value="Sat">{{ __('dashboard.Saturday') }}</option>
                  <option value="Sun">{{ __('dashboard.Sunday') }}</option>
                  <option value="Mon">{{ __('dashboard.Monday') }}</option>
                  <option value="Tue">{{ __('dashboard.Tuesday') }}</option>
                  <option value="Wed">{{ __('dashboard.Wednesday') }}</option>
                  <option value="Thu">{{ __('dashboard.Thursday') }}</option>
                  <option value="Fri">{{ __('dashboard.Friday') }}</option>
                </select>
              </div>
              {{-- <div class="mb-3" id="showdates">
                <label for="dfrom" class="form-label">{{ __('dashboard.Date') }}:</label>
                <input class="form-control" type="date" id="dfrom" name="dfrom" value="" required>
                <div id="showtto" style="display: none">
                  <label for="dto" class="form-label">{{ __('dashboard.to') }}:</label>
                  <input class="form-control" type="date" id="dto" name="dto" value="">
                </div>
              </div> --}}
              <div class="mb-3">
                <label for="tfrom" class="form-label">{{ __('dashboard.Time from') }}:</label>
                <input class="form-control" type="time" id="tfrom" name="tfrom" value="" required>
                <label for="tto" class="form-label">{{ __('dashboard.to') }}:</label>
                <input class="form-control" type="time" id="tto" name="tto" value="" required>
              </div>
              <div class="mb-3">
                <label for="notes" class="form-label">{{ __('dashboard.Add a Note') }}:</label>
                <textarea class="form-control" id="notes" name="notes" rows="4"
                  placeholder="{{ __('dashboard.Write your note here') }}."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">
                
                <span class="d-none d-sm-block">{{ __('dashboard.Submit') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
     -->
    <!-- / Modal -->
  </div>
  <!-- / Content wrapper -->
@stop

@section('scripts')
  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
  <!-- Moment -->
  <script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>


  <!-- Page JS -->
  {{-- <script src="/vuexy/assets/js/pages-account-settings-account.js"></script> --}}
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>

  <script>
    function modify(type, id, stat) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
        type: "POST",
        url: "/dashboard/mentors/modify",
        data: {
          "id": id ? id : undefined,
          "rtype": type,
        },
        success: function(response) {
          // console.log('response', response);

          if (type === 1) {
            $('#availabilty-modal').modal('show'); // Show the modal
            // Hide the spinner
            $('#addSchedule').find('.spinner-border').addClass('d-none');

            const daysOfWeek = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            daysOfWeek.forEach(day => {
              // Filter the response to get the periods for the current day
              const dayPeriods = response.filter(item => item?.days === day) || [];
  
              if (dayPeriods.length > 0) {
                const dayRow = $(`#availabilty-modal #${day}-period-row`)[0];
                
                if (dayRow) {
                  const periodContainer = dayRow.closest('.periods-container');
                  const slotButton = periodContainer.closest('.day-section').querySelector('#add-slot');
                  if (slotButton) {
                    console.log('slotButton', slotButton);
                    slotButton.innerHTML = '{{ __('admin.Remove_Slot') }} <i class="ti ti-x ti-13px ms-2"></i>';
                  }
  
                  periodContainer.style.display = 'block'; // Show the row
                  periodContainer.innerHTML = ''; // Clear existing rows
  
                  for (let i = 0; i < dayPeriods.length; i++) {
                    console.log('dayPeriods', dayPeriods[i])
                    const fromValue = moment(dayPeriods[i].tfrom, 'HH:mm:ss').format('HH:mm');
                    const toValue = moment(dayPeriods[i].tto, 'HH:mm:ss').format('HH:mm');

                    console.log('fromValue', fromValue)
                    console.log('toValue', toValue)

                    @php
                      $timeOptions = '';
                      for ($i = 0; $i < 24; $i++) {
                        for ($j = 0; $j < 60; $j += 30) {
                          $hour = $i % 12 == 0 ? 12 : $i % 12;
                          $period = $i < 12 ? 'AM' : 'PM';
                          $timeValue = str_pad($i, 2, '0', STR_PAD_LEFT) . ':' . str_pad($j, 2, '0', STR_PAD_LEFT);
                          $timeOptions .= "<option value=\"$timeValue\">".str_pad($hour, 2, '0', STR_PAD_LEFT).":".str_pad($j, 2, '0', STR_PAD_LEFT)." $period</option>";
                        }
                      }
                    @endphp
                    const timeOptions = `{!! $timeOptions !!}`;

                    const newPeriodRow = `
                      <div id="${day}-period-row" class="w-100 period-row d-flex align-items-center mb-2 my-2 rounded">
                      <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="d-flex align-items-center gap-4 w-75">
                          <div class="row w-100">
                            <div class="col-md-6">
                              <select class=" form-select w-100" id="${day}-from" type="time" name="periods[${day}][from][]" data-placeholder="{{ __('admin.From') }}">
                                <option value="">{{ __('admin.From') }}</option>
                                ${timeOptions}
                              </select>
                            </div>
                            <div class="col-md-6">
                              <select class=" form-select w-100" id="${day}-to" type="time" name="periods[${day}][to][]" data-placeholder="{{ __('admin.To') }}">
                                <option value="">{{ __('admin.To') }}</option>
                                ${timeOptions}
                              </select>
                            </div>
                          </div>
                        </div>
                        <div class="d-flex align-items-center gap-4 w-25">
                          <span class="total-time fw-bold text-dark text-nowrap me-2 mb-2 mb-md-0">0 {{ __('admin.Min') }}</span>
                          <div class="d-flex flex-nowrap">
                            <button 
                              type="button" 
                              class="btn btn-xs rounded-circle btn-icon me-2 add-period"
                              bs-toggle="tooltip"
                              title="{{ __('admin.Add_Period') }}"
                            >
                              <i class="ti ti-plus text-success"></i>
                            </button>
                            <div id="remove-period">
                              <button 
                                type="button" 
                                class="btn btn-xs rounded-circle btn-icon remove-period"
                                bs-toggle="tooltip"
                                title="{{ __('admin.Remove_Period') }}"
                              >
                              <i class="ti ti-trash text-danger"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    `;
                    periodContainer.innerHTML += newPeriodRow; // Append the new rows

                    // Set the selected values
                    document.querySelector(`#${day}-from`).value = fromValue;
                    document.querySelector(`#${day}-to`).value = toValue;
                  }
                }
              }
            });
          } else if (type === 2) {
            location.reload();
            } else if (type === 3) {
              Swal.fire({
                icon: 'success',
                title: "{{ __('admin.Deleted') }}!",
                html: "{{ __('admin.DeletedSuccessfully') }}.",
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: "{{ __('admin.OK') }}",
                customClass: {
                  confirmButton: 'btn btn-primary',
                },
                buttonsStyling: false,
            }).then((result) => {
              if (result.isConfirmed) {
                location.reload();
              }
            });
          }
        },
        error: function(error) {
          console.log(error);
          // Hide the spinner
          $('#addSchedule').find('.spinner-border').addClass('d-none');
          alert("Error Occurred, Try again!"); // Show an alert
        }
      });
    }
  </script>

  <script>
    // $(document).ready(function() {
    //   $('#dfrom, #dto').datepicker({
    //     format: 'yyyy-mm-dd',
    //     autoclose: true,
    //     todayHighlight: true
    //   });
    // });

    // function modify(type, id, stat) {
    //   $.ajaxSetup({
    //     headers: {
    //       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //     }
    //   });
    //   if (type === 3) {
    //     //delete
    //     if (confirm("{{ __('dashboard.sure del schedule') }}")) {
    //       $.ajax({
    //         type: "POST",
    //         url: "/dashboard/mentors/modify",
    //         data: {
    //           "id": id,
    //           "rtype": type,
    //         },
    //         success: function(response) {
    //           location.reload();
    //         },
    //         error: function(error) {
    //           console.log(error);
    //           alert("Error Occurred, Try again!");
    //         }
    //       });
    //     }
    //   } else if (type === 2) {
    //     //Enable-disable
    //     $.ajax({
    //       type: "POST",
    //       url: "/dashboard/mentors/modify",
    //       data: {
    //         "id": id,
    //         "rtype": type,
    //         "stat": stat
    //       },
    //       success: function(response) {
    //         location.reload();
    //       },
    //       error: function(error) {
    //         console.log(error);
    //         alert("Error Occurred, Try again!");
    //       }
    //     });
    //   } else if (type === 1) {
    //     // Edit current
    //     $.ajax({
    //       type: "POST",
    //       url: "/dashboard/mentors/modify",
    //       data: {
    //         "id": id,
    //         "rtype": type,
    //       },
    //       success: function(response) {
    //         switchtypes(response.type);
    //         console.log(response);
    //         $('#sid').val(response.id);
    //         $('#rtype').val(4);
    //         $('#type').val(response.type);
    //         $('#days').val(response.days);
    //         $('#dfrom').val(response.dfrom);
    //         $('#dto').val(response.dto);
    //         $('#tfrom').val(response.tfrom);
    //         $('#tto').val(response.tto);
    //         $('#notes').val(response.notes);
    //         $('#availabilty-modal').modal('show');
    //       },
    //       error: function(error) {
    //         console.log(error);
    //         alert("Error Occurred, Try again!");
    //       }
    //     });
    //   } else {
    //     //show from as new
    //     $('#sid, #type, #days, #dfrom, #dto, #tfrom, #tto, #notes').val();
    //     $('#rtype').val('0');
    //     switchtypes(type);
    //     $('#availabilty-modal').modal('show');
    //   }
    // }

    // function SelectType(type) {
    //   switchtypes(type);
    // }

    // function switchtypes(type) {
    //   switch (type) {
    //     case 'OneDay':
    //       $('#showdates').show();
    //       $('#showtto, #showdays').hide();
    //       $("#dfrom").prop('required', true);
    //       $("#days, #dto").prop('required', false);
    //       break;
    //     case 'Weekly':
    //       $('#showdates').hide();
    //       $('#showdays').show();
    //       $("#days").prop('required', true);
    //       $("#dfrom, #dto").prop('required', false);
    //       break;
    //     case 'WorkingDays':
    //       $('#showdates, #showdays').hide();
    //       $("#dfrom, #dto, #days").prop('required', false);
    //       break;
    //     case 'Period':
    //       $('#showtto,#showdates').show();
    //       $('#showdays').hide();
    //       $("#dfrom, #dto").prop('required', true);
    //       $("#days").prop('required', false);
    //       break;
    //   }
    // }

    $('.start_meeting').click(function () {
        $.ajax({
            type: "GET", url: "/dashboard/mentor/meetingRedirect/"+$(this).data('value'),
            success: function (response) {
                // alert(response['message']);
                window.open(response['join_url'], '_blank');
{{--                window.location.href ='{{ route('mentorshipTasks.index') }}'+'?mentorship_id='+response['mentorship_id'];--}}
            },
            error: function (error) {
                console.log(error);
                alert("Error Occurred, Try again!");
            }
        });
    });
  </script>
@stop
