@extends('layouts.admin')

@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.My Dashboard'))

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<link rel="stylesheet" type="text/css" href="/app-assets/css{{$l=='ar'?'-rtl':''}}/pages/widgets.css">
	<link rel="stylesheet" type="text/css" href="/app-assets/css{{$l=='ar'?'-rtl':''}}/pages/dashboard-ecommerce.css">
	<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/jquery.rateyo.min.css">

  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/pickr/pickr-themes.css" />
@stop

@section('content')
  @if (!$_user->first_name_ar)
  <div class="d-none">
    {!! redirect()->route(route: 'user.profile_view')->with(['msg' => 'WaitingYourRedirect', 'class' => 'danger']) !!}
  </div>
  @endif
  @php $setting = \App\Settings::where('id', 1)->first(); @endphp
  @php
    $user_id = Auth::id();
    $s_latest = \App\Service::orderBy('created_at', 'DESC')->take(5)->get();
    $e_latest = \App\Calendar::where('start', '>=', now()->format('Y-m-d'))->where(function ($q) use ($user_id) {
            $q->orWhere('user_id', $user_id)->orWhereNull('isPrivate');
        })
        ->orderBy('start')
        ->take(5)
        ->get();
    $circle_id = $_user->company->cycle ?? 0;
    $organization_id = $_user->organization_id ?? 0;
    if( $organization_id == 0 ) { $organization_id = $_user->company->cycledata->organization_id ?? 0; }
    $s_count = \App\Service::whereHas('organizations')->count();
    $s_r_count = \App\ServicesRequests::join('services','service_id','services.id')
    ->where('user_id', $user_id)
    ->count();
    $nm_count = \App\Messenger::where('receiver', $user_id)->whereNull('read')->count();
    $mentors = \App\User::role('Mentor');
    $mentorIds = \App\CyclesMentors::where('cycle_id', $circle_id)
    ->pluck('user_id')
    ->toArray();
    $mentors = $mentors->whereIn('id', $mentorIds);
    $m_count = $mentors->count();
    $surveyEligible = [];
    if( $_user->company && $circle_id != 0 )
    {
      $steps = \App\Step::where('cycle_id', $circle_id)->orderBy('arr')->get();
      foreach( $steps as $step )
      {
        if( optional($step)->stype == 3 || optional($step)->stype == 4 )
        {
          if( now() > $step->to->format('Y-m-d H:i:s') )
          {
            $surveyEligible[] = [
              'triggered_on' => $step->stype == 3 ? 'mentorship' : 'training',
              'triggered_at' => $step->id,
              'triggered_by' => 'due'
            ];
          }
        }
      }
    }
    /// Next session
    $nextsession = \App\MentorshipRequest::where('user_id', $user_id)
        ->where('approved', 'Yes')
        ->where('zoom_date', '>=', date('Y-m-d 00:00:00'))
        ->orderBy('zoom_date', 'ASC')
        ->first();
    /// Rating
    $notrated_ser = \App\ServicesRequests::where('user_id', $user_id)
        ->where('approved', 'Yes')
        ->where('date_time', '<', date('Y-m-d 17:00:00'))
        ->whereNull('rate')
        ->first();
    $notrated_men = \App\MentorshipRequest::where('user_id', $user_id)
        ->where('zoom_date', '<', date('Y-m-d 17:00:00'))
        ->where('approved', 'Yes')
        ->whereNull('rating')
        ->first();
    $myMentorships = \App\MentorshipRequest::where('user_id', $user_id)->where('approved', 'Yes')->where('cycle_id', $circle_id)->get();
    $myTrainings = \App\TrainingSession::whereHas('step', function( $query ) use ( $circle_id ) {
      $query->where('cycle_id', $circle_id);
    })->get();
    $quizzes = \App\Quizzes::where('status', 'published')->where('organization_id', $organization_id)->with(['myAttempts'])->get();
    foreach( $myMentorships as $mentorship )
    {
      if( empty( $mentorship->zoom_date ) || empty( $mentorship->period ) ) { continue; }
      else
      {
        $endAt = Carbon\carbon::parse($mentorship->zoom_date)->addMinutes($mentorship->period)->format('Y-m-d H:i:s');
        if( now() > $endAt )
        {
          $surveyEligible[] = [
            'triggered_on' => 'mentorship',
            'triggered_at' => $mentorship->id,
            'triggered_by' => 'session'
          ];
        }
      }
    }
    
    foreach( $myTrainings as $trainingSession )
    {
      if( $trainingSession->type == 'sessions' )
      {
        if( empty( $trainingSession->datetime ) || empty( $trainingSession->duration ) ) { continue; }
        else
        {
          $endAt = Carbon\carbon::parse($trainingSession->datetime)->addMinutes($trainingSession->duration)->format('Y-m-d H:i:s');
          if( now() > $endAt )
          {
            $surveyEligible[] = [
              'triggered_on' => 'training',
              'triggered_at' => $trainingSession->id,
              'triggered_by' => 'session'
            ];
          }
        }
      }
      
      elseif( in_array($trainingSession->type, ['online', 'offline','sessions']) )
      {
        
        if( empty( $trainingSession->datetime ) ) { continue; }
        else
        {
          $endAt = FormatDate($trainingSession->datetime, 'Y-m-d H:i:s');
          if( now() > $endAt )
          {
            $surveyEligible[] = [
              'triggered_on' => 'training',
              'triggered_at' => $trainingSession->id,
              'triggered_by' => 'session'
            ];
          }
        }
      }
    }
    
    foreach( $quizzes as $myQuiz )
    {
      $attempts = $myQuiz->myAttempts;
      if( $attempts->count() > 0 )
      {
        if (
          $attempts->where('score', '>=', $myQuiz->passing_criteria)->count() >= 1 || 
          $attempts->where('score', '<', $myQuiz->passing_criteria)->count() >= $myQuiz->attempts || 
          now()->format('Y-m-d H:i:s') > $myQuiz->end->format('Y-m-d H:i:s')
        )
        {
          $surveyEligible[] = [
            'triggered_on' => 'training',
            'triggered_at' => $myQuiz->id,
            'triggered_by' => 'quiz'
          ];
        }
      }
    }
    $surveyEligible = collect($surveyEligible)->groupBy('triggered_on')->toArray();
    /// My sessions
    if (!empty($_user->company)) {
        $sessions = \App\TrainingSession::where('step_id', $_user->company->step)
            ->orderBy('datetime', 'ASC')
            ->take(5)
            ->get();
    } else {
        $sessions = [];
    }

    $countCards = [
        [
            'color' => 'success',
            'href' => '/dashboard/directory',
            'icon' => 'ti ti-briefcase',
            'text' => __('dashboard.Services'),
            'count' => $s_count,
        ],
        [
            'color' => 'primary',
            'href' => '/dashboard/requests/mine',
            'icon' => 'ti ti-speakerphone',
            'text' => __('dashboard.My Requests'),
            'count' => $s_r_count,
        ],
        [
            'color' => 'secondary',
            'href' => '/dashboard/mentors',
            'icon' => 'ti ti-school',
            'text' => __('dashboard.Mentors'),
            'count' => $m_count,
        ],
        [
            'color' => 'warning',
            'href' => '/dashboard/inbox',
            'icon' => 'ti ti-message',
            'text' => __('dashboard.newmessages'),
            'count' => $nm_count,
        ],
    ];

		$s_latest = \App\Service::orderBy('created_at','DESC')->take(5)->get();
		$e_latest = \App\Calendar::where('start','>=',now()->format('Y-m-d'))->where(function ($q) use ($user_id) {
    	$q->orWhere('user_id', $user_id)->orWhereNull('isPrivate');
		})->orderBy('start')->take(5)->get();
		// $s_count = \App\Service::count();
	
		$nm_count = \App\Messenger::where('receiver', $user_id)->whereNull('read')->count();
		// $mentors = \App\CyclesMentors::where('cycle_id', $circle_id)->get();
		// $m_count = count($mentors);
		/// Next session
		// $nextsession = \App\MentorshipRequest::where('user_id', $user_id)->where('approved','Yes')->where('zoom_date','>=',date("Y-m-d 00:00:00"))->orderBy('zoom_date','ASC')->first();
		$days = request()->query('days', 14);
	
	$nextSessions = \App\MentorshipRequest::where('user_id', $user_id)
    ->where(function ($query) {
        $query->where('approved', 'Yes')
              ->orWhereNull('approved');
    })
    ->whereBetween('zoom_date', [
        Carbon\Carbon::now()->startOfDay(), // بداية اليوم الحالي
        Carbon\Carbon::now()->addDays($days)->endOfDay() // نهاية اليوم الـ 14 القادم
    ])
    ->orderBy('zoom_date', 'ASC')
    ->get();
	
	
	
  if( $_user->hasRole('Registered') )
  {
    /// Rating
    $notrated_ser = \App\ServicesRequests::where('user_id', $user_id)->where('approved','Yes')->where('date_time','<',date("Y-m-d 17:00:00"))->whereNull('rate')->first();
    $notrated_men = \App\MentorshipRequest::where('user_id', $user_id)->where('zoom_date','<',date("Y-m-d 17:00:00"))->where('approved','Yes')->whereNull('rating')->first();
    $available_surveys = collect([]);
    // load survey for startup
    if( $_user->company && $circle_id != 0 )
    {
      $surveysToAdd = [];
      $surveys = \App\SurveyScopes::where('cycle_id', $circle_id)->whereIn('type', ['startup', 'both'])->orWhereNull('type')->with(['myResponses'])->get();
      
      foreach( $surveys as $survey_scope )
      {
        
        $my_responses = $survey_scope->myResponses;
       
        if( $survey_scope->scope == 'mentorship' && array_key_exists('mentorship', $surveyEligible) ) 
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
      if( !empty($surveysToAdd) ) { \App\SurveyResponses::insert( $surveysToAdd ); }
      $attendance = \App\AttendanceTrainingOffline::where('user_id', $user_id)->where('cycle_id', $circle_id)->where('attendance', 0)->get();
      if($attendance->count()>0){
      
      $deleted_surveys = \App\SurveyResponses::where('user_id', $user_id)->where('cycle_id', $circle_id)->where('responses', null)->where('triggered_on', 'training')->whereIn('triggered_at', $attendance->pluck('training_session_id'))->delete();
      }
      $available_surveys = \App\SurveyResponses::where('user_id', $user_id)->where('cycle_id', $circle_id)->whereNull('responses')->get();
    }
  }
	/// My sessions
	if( !empty( $_user->company ) ) { $sessions = \App\TrainingSession::where("step_id", $_user->company->step)->orderBy('datetime','ASC')->take(1)->get(); }
	else { $sessions = []; }
  @endphp

@section('content')
  @if (!$_user->first_name_ar)
  <div class="d-none">
    {!! redirect()->route('user.profile_view')->with(['msg' => 'WaitingYourRedirect', 'class' => 'danger']) !!}
  </div>
  @endif

  <!-- Content wrapper -->
  <div class="">
    <div class="">
      @if( session()->has('success') )
        <div class="row">
          <div class="col-md-12">
            <div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
              {{ __('dashboard.'. session()->get('success')) }} 
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          </div>
        </div>
      @endif
      @if( session()->has('error') )
        <div class="row">
          <div class="col-md-12">
            <div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
              {{ __('dashboard.'.session()->get('error')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          </div>
        </div>
      @endif
      @if( session()->has('msg') )
        <div class="row">
          <div class="col-md-12">
            <div role="alert" class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2">
              {{ __('admin.' . session()->get('msg')) }}
              {{ Session::forget('msg') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          </div>
        </div>
      @endif
      
      <div class="content-body">
        @if (session()->has('msg'))
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <div class="d-flex align-items-center">
                  <i class="ti ti-error"></i>
                  <span> {{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }} </span>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>
      <!-- Count cards -->
      <div class="row mb-6">
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

      <div class="row">
        <!-- Latest Services -->
        <div class="col-sm-6 col-12 mb-6">
          <div class="card">
            <div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom" style="width: '100%">
              <div class="d-flex align-items-center py-2">
                <i class="ti ti-devices-question ti-24px"></i>
                <h5 class="card-title mb-0 mx-2">{{ __('dashboard.Latest services') }}</h5>
              </div>
            </div>
            <div class="card-body">
              @if (count($s_latest) > 0)
                @foreach ($s_latest as $serv)
                  <div class="d-flex justify-content-between align-items-center my-2">
                    <div class="widget-todo-title-area d-flex align-items-center">
                      <a href="/dashboard/directory?find={{ $serv->{'name_' . $l} }}">
                        <span class="widget-todo-title ml-50 text-secondary">
                          <i class="ti ti-link"></i>
                          {{ $serv->{'name_' . $l} }}</span>
                      </a>
                    </div>
                    <div class="widget-todo-item-action d-flex align-items-center">
                      <div class="btn btn-label-primary py-1 rounded d-flex align-items-center justify-conetnt-center"
                        style="width: 200px; height: 30px">
                        @if ($serv->serviceCategory)
                          <a href="/dashboard/directory?type={{ $serv->serviceCategory->id }}">
                            {{ $serv->serviceCategory->{'name_' . $l} }}
                          </a>
                        @else
                          <span>{{ __('dashboard.No Category') }}</span>
                        @endif
                      </div>
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
		@if( 1 == 2 )
    @if( $notrated_men or $notrated_ser )
		<div class="col-sm-6 col-12">
      <div class="card">
				<div class="card-header border-bottom">
					@if( $notrated_ser )
					<h5 class="card-title"><i class="bx bx-calendar mr-1"></i>{{__('dashboard.Rate')}} {{__('dashboard.service')}}: "{{$notrated_ser->service->{'name_'.$l} }}" <span class="primary" id="rname"></span></h5>
					@else
					<h5 class="card-title"><i class="bx bx-calendar mr-1"></i>{{__('dashboard.Rate mentorship session with')}} : {{$notrated_men->mentor->{'first_name_'.$l} }} {{$notrated_men->mentor->{'last_name_'.$l} }}<span class="primary" id="rname"></span></h5>
					@endif
					<div class="heading-elements">
						<ul class="list-inline mb-0">
							<li> <a data-action="close">  </a> </li>
						</ul>
					</div>
				</div>
				<div class="card-body">
					@if( $notrated_ser )
					<form method="post" action="/dashboard/directory/{{$notrated_ser->id}}/rate" enctype="multipart/form-data" autocomplete="off">
						@csrf
						<table class="table ">
							<thead>
								<tr>
									<th>{{__('dashboard.Your request was in')}}</th>
									<th>{{__('dashboard.Did you get this service?')}}</th>
									<th>{{__('dashboard.Rate from 1 star "bad" to 5 stars "best"')}}</th>
									<th>{{__('dashboard.Your opinion important kindly write any notes about this service')}}</th>
									<th>{{__('dashboard.Submit')}}</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<input type="hidden" name="rate" value="0" id="rate">
										{{$notrated_ser->date_time}}
									</td>
									<td>
										<ul class="list-unstyled mb-0">
											<li class="d-inline-block mr-2 mb-1">
												<fieldset>
													<div>
														<input type="radio" name="done" id="doneyes" checked="">
														<label for="doneyes">{{__('Yes')}}</label>
													</div>
												</fieldset>
											</li>
											<li class="d-inline-block mr-2 mb-1">
												<fieldset>
													<div>
														<input type="radio" name="done" id="doneno">
														<label for="doneno">{{__('No')}}</label>
													</div>
												</fieldset>
											</li>
										</ul>
									</td>
									<td><div class="full-star-ratings" data-rateyo-full-star="true"></div></td>
									<td>
										<textarea class="form-control" id="comment" name='comment' rows="4" required placeholder="{{__('dashboard.Write your notes here')}}" ></textarea>
									</td>
									<td>
										<button type="submit" class="btn btn-primary ml-1" >
											
											<span class="d-none d-sm-block">{{__('dashboard.Submit')}}</span>
										</button>	
									</td>
								</tr>
							</tbody>
						</table>
					</form>
					@else
					<form method="post" action="/dashboard/mentorship/rate" enctype="multipart/form-data" autocomplete="off">
						@csrf
						<input type="hidden" name="id" value="{{$notrated_men->id}}">
						<input type="hidden" name="rate" value="0" id="rate">
						<table class="table ">
							<thead>
								<tr>
									<th style="font-size: 0.7rem">{{__('dashboard.Your session was in')}}</th>
									<th style="font-size: 0.7rem">{{__('dashboard.Did you attend this session?')}}</th>
									<th style="font-size: 0.7rem">{{ __('dashboard.Rate from 1 star "bad" to 5 stars "best"') }}</th>
									<th style="font-size: 0.7rem">{{ __('dashboard.Your opinion important kindly write any notes about this session') }}</th>
									<th style="font-size: 0.7rem">{{__('dashboard.Submit')}}</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										{{$notrated_men->zoom_date}}
									</td>
									<td>
										<ul class="list-unstyled">
											<li class="d-inline-block ">
												<fieldset>
													<div class="radio">
														<input type="radio" name="done" id="doneyes2" checked="">
														<label for="doneyes2">{{__('Yes')}}</label>
													</div>
												</fieldset>
											</li>
											<li class="d-inline-block">
												<fieldset>
													<div class="radio">
														<input type="radio" name="done" id="doneno2">
														<label for="doneno2">{{__('No')}}</label>
													</div>
												</fieldset>
											</li>
										</ul>
									</td>
									<td><div class="full-star-ratings" data-rateyo-full-star="true"></div></td>
									<td>
										<textarea class="form-control" id="comment" name='comment' rows="4" required placeholder="{{ __('dashboard.Write your notes here') }}." ></textarea>
									</td>
									<td>
										<button type="submit" class="btn btn-primary ml-1">
											
											<span class="d-none d-sm-block">{{__('dashboard.Submit')}}</span>
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
					@endif
				</div>
			</div>
		</div>
		@endif
    @endif
        <!-- / Latest Services -->

        <!-- Survey -->

      @if( !empty( $available_surveys ) && $available_surveys->count() > 0 )
    @if( $_user->hasRole('Registered') )
    @if( $_user->ableToAll(['surveys.take', 'surveys.submit'], $permissions) )
    <div class="col-sm-6 col-12 mb-6" style="height: 300px; overflow-y: auto; border-radius: 1rem;">
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
              <div class="col-9 py-1 line-clamp-1">
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
                  if( is_null( $middleware ) || empty( $middleware ) ) { continue; }
                  elseif( !$middleware->count() ) { continue; }
                @endphp
                @if( $survey->triggered_by == 'due' ) {{ __('dashboard.surveyStepName' . ucFirst($survey->triggered_on) ) }} @endif
                @if( $survey->triggered_on == 'mentorship' )
                  @if( $survey->triggered_by == 'session' )
                  {{ __('dashboard.with') }} 
                    @php $mentor = $middleware->mentor; @endphp
                    {{ $mentor->name ?? '' }}
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
                <a href="{{ route('surveys.take', ['id' => $survey->id]) }}" class="btn btn-sm btn-label-primary">{{ __('dashboard.startSurvey') }}</a>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    @endif
    @endif
    
    {{-- @if( $_user->hasRole('Mentor') )
    <div class="col-12" style="max-height: 500px; overflow-y: auto;">
      <div class="card">
        <div class="card-header border-bottom py-3">
          <h5 class="card-title m-0">
            <i class="ti ti-user-question mr-1"></i> {{ __('dashboard.availableSurveysList') }}
          </h5>
        </div>
        <div class="card-body py-6">
          <div class="container-fluid px-0">
            @foreach( $available_surveys as $survey )
            <div class="row my-3 border-bottom pb-3">
              <div class="col-9 py-1">
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
                    {{ $mentor->name }}
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
                <a href="{{ route('surveys.take', ['id' => $survey->id]) }}" class="btn btn-sm btn-label-dark rounded w-100">{{ __('dashboard.startSurvey') }}</a>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    @endif --}}
    @endif
      <!-- Survey -->

        
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
              @if (count($e_latest) > 0)
                @foreach ($e_latest as $event)
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

      
        @php
          $svgs = ['notebook','search','idea','comments','trophy'];
          $colors = ['#39da8a','#99a2ac','#fdac41'];
          $s = 1;
          $stat = "Completed";
          $ctype = 0;
          if( !empty( $_user->company ) )
          {
            $ctype = $_user->company->stepdata->stype ?? '0';
          }
          $color= $colors[0];

        @endphp
        
    
      
      
      <!-- My Progress -->
      @if( $_user->company && $circle_id != 0 )
        <div class="card">
          <div class="card-header border-bottom">
            <h5 style="text-transform: capitalize">
              <i class="ti ti-briefcase ti-24px"></i>
              <span class="mx-2">{{ __('dashboard.My progress') }} :
                {{ $_user->company->cycledata->title }}</span>
            </h5>
            <span
              class="text-capitalize">{{ __('dashboard.start from') . ': ' . $_user->company->cycledata->start_format . ' ' . __('dashboard.to') . ': ' . $_user->company->cycledata->end_format }}</span>
            <div class="d-flex align-items-center mt-3">
              @if (!is_null($_user->company->cycledata->organization))
              @php
              $imageUrl = $_user->company->cycledata->organization->image_url;
          
               // استخراج المسار من الرابط
               $imagePath = parse_url($imageUrl, PHP_URL_PATH);
          
               // إزالة أي أجزاء غير ضرورية في البداية مثل `/dashboard/uploads/files/`
               $fileN = ltrim(str_replace('/dashboard/uploads/files/', '', $imagePath), '/');
              @endphp
              <img src="{{ $imageUrl }}" alt="avatar"
                class="rounded-circle" height="35" width="35">
                @else
                <img src="/images/logo/{{ $setting->Logo }}" alt="avatar" class="logo-img">
            @endif

              <span class="mx-3 text-secondary">{{ optional($_user->company->cycledata->organization)->name ?? __('dashboard.No Organization') }}</span>
            </div>
          </div>
          <div class="table-responsive text-nowrap">
            <table class="table">
              <thead class="bg-label-light">
                <tr>
                  <th>{{ $l == 'ar' ? 'المرحلة' : 'Step' }}</th>
                  <th>{{ $l == 'ar' ? 'الحالة' : 'Status' }}</th>
                  <th>{{ $l == 'ar' ? 'بدأت في' : 'Start At' }}</th>
                  <th>{{ $l == 'ar' ? 'انتهت في' : 'End at' }}</th>
                  <th>{{ $l == 'ar' ? 'الوصف' : 'Description' }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach( $steps as $step )
                  @php
                    $status = $l == 'ar' ? 'لم تبدأ' : 'Not started';
                    $color = 'label-light';
                    $textColor = 'light';
                    if ($_user->company->stepdata->arr > $step->arr) {
                        $status = $l == 'ar' ? 'انتهت' : 'Completed';
                        $color = 'label-success';
                        $textColor = 'success';
                    } elseif ($_user->company->stepdata->arr == $step->arr) {
                        $status = $l == 'ar' ? 'بدأت' : 'Started';
                        $color = 'label-primary';
                        $textColor = 'primary';
                    }

                    $trtype = $flink = null;
                    if ($_user->company->approved) {
                        $flink = 'my/' . $step->id;
                    } else {
                        $flink = 'view/' . $circle_id;
                    }
                  @endphp
                  <tr>
                    <td>
                      @if($step->stype == 1)
                      @if($step->arr == 'Completed' || $step->arr == 'انتهت')
                        @if( $_user->company->approved && $_user->ableTo('cycles.list_mine', $permissions) )
                          <a href="/dashboard/cycles/{{ $flink }}"> {{ $step->title }} </a>
                        @else 
                          {{ $step->title }}
                        @endif
                        @else
                        {{ $step->title }}
                      @endif
                      @else
                        {{ $step->title }}
                      @endif
                      
                    </td>
                    <td><span class="badge bg-{{ $color }} text-{{ $textColor }}">{{ $status }}</span></td>
                    <td>{{ FormatDate($step->from, 'Y-m-d') ?? '-' }}</td>
                    <td>{{ FormatDate($step->to, 'Y-m-d') ?? '-' }}</td>
                    <td style="height: 50px;">
                      <span style="width: 350px; height: 100%; display: inline-block; white-space: normal;">
                        {{ $step->description ? $step->description : '...' }}
                      </span>
                    </td>
                  </tr>

                  
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        @else
				<section class="mt-10">
					<div class="row">
						<div class="col-md-12">
							<div class="card">
								<div class="card-header border-bottom">
									<h5 class="card-title d-flex align-items-center">
										<i class="ti ti-clipboard-list ti-28px"></i>
                    <span class="mx-2">{{__('dashboard.IncubationList')}}</span>
                  </h5>
								</div>
								<div class="card-body">
									@php
                  $workingcycles = App\Cycle::whereNull('private')->whereRaw('? between start and end', [now()->format('Y-m-d')])->get();
                  @endphp
									@if (count($workingcycles)>0)
										@foreach($workingcycles as $cycle)
                    
											<div class="border-bottom">
												<div class="card-header">
													<h4 class="primary"><i class="bx bxs-bell"></i>  {{$cycle->title}}</h4>
													<div class="d-flex align-items-center mt-2">
														<div class="media-left">
                              @if (!is_null($cycle->organization->image_url))
                    @php
                    $fileN = pathinfo($cycle->organization->image_url, PATHINFO_BASENAME);
                    @endphp
    
    
															<img src="{{ $cycle->organization->image_url }}" alt="avatar" class="rounded-circle" height="40" width="40">
                              @endif
														</div>
														<div class="mx-2">
															{{$cycle->organization->name }}
														</div>
													</div>
												</div>
												<div class="card-body bg-rgba-light">
													<p class="card-text">
														{{$cycle->description}}
													</p>
                          <div class="d-flex align-items-center justify-content-between" style="width:100%">
                            <small class="text-muted" style="text-transform: capitalize">
                              {{__('dashboard.start from')}} {{$cycle->start->format('Y-m-d')}}
                            </small>
                            <a href="/dashboard/cycles/view/{{$cycle->id}}" class="btn btn-primary">
                              <span class="mx-2">{{__('dashboard.Join')}}</span>
                              <i class="ti ti-arrows-join"></i>
                            </a>
                          </div>
												</div>
											</div>
										@endforeach
									@else
										<div>
										<h5 class="m-75 p-75">
											<div class="avatar bg-rgba-warning m-0 mr-75">
												<div class="avatar-content">
													<i class="bx bx-stats text-warning"></i>
												</div>
											</div>  {{__('dashboard.No Cycles started yet')}}...
										</h5>
										</div>
									@endif
								</div>
							</div>
						</div>
					</div>
				</section>
      @endif
      <!-- / My Progress -->
     
     
    @if( $ctype == 5 )
      @php
      $myfinal = \App\Finalscreening::where('company_id', $_user->company->id )->where('step_id', $_user->company->step )->first();
      @endphp
      

        <section class="mt-10">
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header border-bottom">
                  <h5 class="card-title align-items-center">
                    <i class='bx bx-card font-medium-4 mr-1'></i> {{ __("admin.Final_Session") }}
                  </h4>
                </div>
                @if( !is_null($myfinal)&&$myfinal->join_url )
                  <div class="">
                    <div class="">
                      <div class="table-responsive">
                        <table class="table mb-0 sessonsTable" >
                          <thead class="bg-label-light">
                            <tr>
                            <th>{{__('cycles.User/Title')}}</th>
                            <th>{{__('admin.Judger')}}</th>
                            <th>{{__('cycles.Date/Time')}}</th>
                            <th>{{__('cycles.Done')}}</th>
                            <th>{{__('cycles.Actions')}}</th>
                          </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>
                                <div class="d-flex align-items-center">
                                  <a class="media-left" href="/dashboard/users/{{$myfinal->company->user->id}}/edit">
                                    <img src="{{$myfinal->company->user->image_url}}" alt="avatar" class="rounded-circle me-3" height="40" width="40">
                                  </a>
                                  <a href="/dashboard/users/{{$myfinal->company->user->id}}/edit">
                                    <div class="media-body">
                                      <h6 class="media-heading mb-0 text-nowrap">{{$myfinal->company->user->{'first_name_'.$l} }} {{$myfinal->company->user->{'last_name_'.$l} }}</h6>
                                      <span class="font-small-2 text-nowrap">{{$myfinal->company->user->{'title_'.$l} }}</span>
                                    </div>
                                  </a>
                                </div>
                              </td>
                              <td>
                                @foreach ($myfinal->judges as $judgeid )
                                @php
                                $judge = \App\User::find($judgeid);
                                $name=optional($judge)->{'first_name_'.$l}.' '.optional($judge)->{'last_name_'.$l};
                                @endphp
                               @if(!empty($judge))
                                <div style="cursor: pointer;" class="d-flex align-items-center">
                                  <div class="chip-body">
                                    <div class="avatar">
                                      <img class="img-fluid rounded-circle me-3" src="{{asset(optional($judge)->image_url) }}" alt="photo" height="20" width="20">
                                    </div>
                                    <span class="chip-text text-nowrap">
                                      {{$name}}
                                    </span>
                                  </div>
                                </div>
                                @endif
                                @endforeach
                              </td>
                              <td>
                                {{$myfinal->datetime}}
                                <br>({{$myfinal->duration}}M)
                              </td>
                              <td>
                                {{$myfinal->datetime < now() ?__('Yes'):__('No')}}
                              </td>
                              <td>
                                @php
                                  $endTime = \Carbon\Carbon::parse($myfinal->datetime)->addMinutes($myfinal->duration);
                                @endphp
                                @if(now() > $endTime)
                                  <span class="text-muted">{{ $l == 'ar' ? 'انتهت الجلسة' : 'Session Completed' }}</span>
                                @else
                                  <a href="{{$myfinal->join_url}}" target="_blank">({{__('dashboard.Join now')}})</a>
                                @endif
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  @else
                  <div class="">
                    <div class="">
                      <div class="text-center">
          <h3>{{__('dashboard.Your final session will start soon')}}.</h3>
                      </div>
                    </div>
                  </div>
        
      @endif

              </div>
            </div>
          </div>
        </section>
      
    @endif
    @if( $ctype != 5 )
      <!-- My last training -->
      @if (count($sessions) > 0)
        <div class="card mt-10">
          <div class="card-header border-bottom">
            <h5 style="text-transform: capitalize">
              <i class="ti ti-clipboard-list ti-28px"></i>
              <span class="mx-2">
                {{ __('dashboard.My Latest training') }}
                <a href="/dashboard/training/my">{{ __('dashboard.Show all') }}</a>
              </span>
            </h5>
          </div>
          <div class="table-responsive">
            <table class="table mb-0" id="trainingtable">
              <thead class="bg-label-light">
                <tr>
                  <th>#</th>
                  <th>{{ __('cycles.Type') }}</th>
                  <th>{{ __('cycles.Title') }}</th>
                  <th>{{ __('cycles.Start') }}</th>
                  <th>{{ __('cycles.End') }}</th>
                  <th>{{ __('cycles.details') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($sessions as $key => $session)
                  <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ __('cycles.' . $session->type) }}</td>
                    <td>{{ $session->title }}</td>
                    <td>{{ $session->datetime }}</td>
                    <td>{{ $session->enddatetime ?? '-' }}</td>
                    <td>
                      @if ($session->type == 'sessions')
                        {{ __('cycles.Duration') }}: {{ $session->duration }} {{ __('cycles.Minutes') }}
                        <a href="{{ $session->join_url }}" target="_blank">
                          <button title="{{ __('cycles.Join_Meeting') }}" type="button"
                            class="btn btn-icon rounded-circle btn-light-primary">
                            <i class="ti ti-brand-zoom"></i></button>
                        </a>
                      @elseif ($session->type == 'offline')
                        <b>{{ __('cycles.trainer name') }}:</b>
                        {{ $session->trainer_name }}<br><b>{{ __('cycles.training location') }}:</b>
                        {{ $session->location }} <br>
                      @else
                        <a href="/dashboard/training/my/{{ $session->location }}">{{ __('cycles.Display course contents') }}
                        </a>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
      <!-- / My last training -->
      
      <!-- My Mentors -->
      @if ($_user->company->approved ?? null && count($mentors))
        <div class="card mt-10">
          {{-- <div class="card-header border-bottom">
            <h5 style="text-transform: capitalize">
              <i class="ti ti-school ti-24px"></i>
              <span class="mx-2">{{ __('dashboard.Mentors') }}
                @if ($nextsession)
                  : <h2 class="text-danger">
                    {{ __('dashboard.Your next mentorship session with') }}
                    <a href="{{ $nextsession->join_url }}"
                      target="_blank">{{ $nextsession->mentor->{'first_name_' . $l} }}
                      {{ $nextsession->mentor->{'last_name_' . $l} }}</a>
                    {{ __('dashboard.will start') }} {{ $nextsession->zoom_date }} <a
                      href="{{ $nextsession->join_url }}" target="_blank">({{ __('dashboard.Join now') }})</a>.
                  </h2>
                @endif
              </span>
            </h5>
          </div> --}}

          

          <section>
			<div class="row">
				<div class="col-md-12">
					<div class="card">
						<div class="card-header border-bottom">
							<h5 class="card-title align-items-center">
								<i class='bx bx-card font-medium-4 mr-1'></i> {{ __("dashboard.MentorsSession") }} :
								@if($days!=14)
								<div onclick="redirectWithDays('14')" class="btn btn-label-success" id="forteenDays"> 14 {{__('cycles.days')}}</div>
								@else
								<div onclick="redirectWithDays('14')" class="btn btn-label-warning" id="forteenDays"> 14 {{__('cycles.days')}}</div>
								@endif
								@if($days!=7)
								<div onclick="redirectWithDays('7')" class="btn btn-label-success" id="sevenDays"> 7 {{__('cycles.days')}}</div>
								@else
								<div onclick="redirectWithDays('7')" class="btn btn-label-warning" id="sevenDays"> 7 {{__('cycles.days')}}</div>
								@endif
								<a href="/dashboard/mentorship/mine"><div class="btn btn-label-info">{{__('cycles.showAll')}}</div></a>
								
							</h4>
						</div>

							<div class="">
								<div class="">
									<div class="table-responsive">
										<table class="table mb-0 sessonsTable" >
                      <thead class="bg-label-light">
                        <tr>
												{{-- <th>{{__('cycles.Company')}}</th> --}}
												<th>{{__('cycles.User/Title')}}</th>
												<th>{{__('cycles.Mentor')}}</th>
                      @if ($_user->hasRole('Admin'))
												<th>{{__('cycles.Rate/Comment')}}</th>
                      @endif
												<th>{{__('cycles.Date/Time')}}</th>
												<th>{{__('cycles.Approved')}}</th>
												<th>{{__('cycles.Done')}}</th>
												<th>{{__('cycles.Actions')}}</th>
											</tr>
											</thead>
											<tbody>
											@foreach($nextSessions as $session)
												<tr>
													{{-- <td>
														<div class="d-flex align-items-center gap-1">
															<a class="media-left mr-50" href="/dashboard/companyProfile/{{$session->user->company->id}}">
																<img src="{{$session->user->company->logo_url}}" alt="avatar" class="rounded-circle" height="40" width="40">
															</a>
															<a href="/dashboard/companyProfile/{{$session->user->company->id}}">
																<div class="d-flex align-items-center gap-1">
																	{{$session->user->company->{'name_'.$l} }}
																</div>
															</a>
														</div>
													</td> --}}
													<td>
														<div class="d-flex align-items-center">
															<a class="media-left" href="/dashboard/users/{{$session->user->company->user->id}}/edit">
																<img src="{{$session->user->image_url}}" alt="avatar" class="rounded-circle me-3" height="40" width="40">
															</a>
															<a href="/dashboard/users/{{$session->user_id}}/edit">
																<div class="media-body">
																	<h6 class="media-heading mb-0 text-nowrap">{{$session->user->{'first_name_'.$l} }} {{$session->user->{'last_name_'.$l} }}</h6>
																	<span class="font-small-2 text-nowrap">{{$session->user->{'title_'.$l} }}</span>
																</div>
															</a>
														</div>
													</td>
													<td>
														<div style="cursor: pointer;" class="d-flex align-items-center" onclick="mentor({{$session->mentor_id}},'{{$session->mentor->{'first_name_'.$l} .' '. $session->mentor->{'last_name_'.$l} }}','{{$session->mentor->mrating()}}')">
															<div class="chip-body">
																<div class="avatar">
																	<img class="img-fluid rounded-circle me-3" src="{{$session->mentor->image_url}}" alt="Mentor photo" height="20" width="20">
																</div>
                                <span class="chip-text text-nowrap">
                                  {{$session->mentor->name}}
                                </span>
															</div>
														</div>
													</td>
                          @if ($_user->hasRole('Admin'))
													<td>
														<small>{!!  $session->rate_comment?
															 str_repeat('<i class="bx bxs-star" style="color: #fc960f;"></i>',$session->rating).
														'<br>'.$session->rate_comment:$session->comment  !!}</small>
													</td>
                          @endif
													<td>
														{{$session->zoom_date}}
														<br>({{$session->period}}M)
													</td>
													<td>
														<div class="d-flex align-items-center gap-1">
															{!!  __($session->approved) ??'<span class="warning">'.__('Waiting').'</span>'  !!}
														</div>
													</td>
													<td>
														{{$session->done?__('Yes'):__('No')}}
													</td>
													<td>
														@php
															$endTime = \Carbon\Carbon::parse($session->zoom_date)->addMinutes($session->period);
														@endphp
														@if(now() > $endTime)
															<span class="text-muted">{{ $l == 'ar' ? 'انتهت الجلسة' : 'Session Completed' }}</span>
														@else
															<a href="{{$session->join_url}}" target="_blank">({{__('dashboard.Join now')}})</a>
														@endif
													</td>
												</tr>
											@endforeach
					
											</tbody>
										</table>
									</div>
								</div>
							</div>
							
             

						
					</div>
				</div>
			</div>
		</section>

        </div>

        @if( $ctype != 1 && $ctype != 2 )
		<div class="card mt-10">
		<section>
			<div class="row">
				<div class="col-md-12">
					<div class="card">
						<div class="card-header border-bottom mb-4">
							<h5 class="card-title align-items-center">
                <i class="ti ti-school ti-28px"></i>
                <span class="mx-2">{{ __("dashboard.Mentors") }}</span>
							</h4>
						</div>
						<div class="">
							<div class="">
								<div class="table-responsive">
									<table class="table" id="mentorstable">
                    <thead class="bg-label-light">
											<tr>
												<th>#</th>
												<th>{{__('dashboard.Name/Title')}}</th>
												<th>{{__('dashboard.Bio')}}</th>
												<th>{{__('dashboard.Specialization')}}</th>
												<th>{{__('dashboard.Linkedin')}}</th>
												<th>{{__('dashboard.Request')}}</th>
											</tr>
										</thead>
										<tbody>
											@foreach($mentors as $key=>$mentor)
												<tr>
													<td>{{$key+1}}</td>
													<td>
                            
														<div class="d-flex align-items-center gap-1">
															<a class="media-left">
																<img src="{{ optional($mentor->user)->image_url}}" alt="avatar" class="rounded-circle me-3" height="40" width="40">
															</a>
                              @if( $_user->ableTo('mentors.profile_view', $permissions) )
                              @if ($mentor->user != null)
                              <a target="_blank" href="{{route('mentors.profile_view', ($mentor->user)->id)}}">
                              @endif
                              
                                @endif
															<div class="media-body">
																<h6 class="media-heading mb-0 text-nowrap">{{optional($mentor->user)->name }}</h6>
																<span class="font-small-2 text-nowrap">{{optional($mentor->user)->title }}</span>
															</div>
                              @if( $_user->ableTo('mentors.profile_view', $permissions) )</a>@endif
														</div>
                          
													</td>
													<td> {{optional($mentor->user)->bio->{'bio_'.$l} ?? '-' }} </td>
													<td> {{optional($mentor->user)->bio->{'specialization_'.$l} ?? '-' }} </td>
													<td>
														@if (optional($mentor->user)->bio->linkedin ??0)
															<a title="Linkedin URL" target="_blank" href="{{optional($mentor->user)->bio->linkedin}}" class="btn btn-icon rounded-circle btn-label-primary"> <i class="fa-brands fa-linkedin-in"></i></a>
														@endif
													</td>
													<td>
														<button onclick="request({{$mentor->user_id}},'{{optional($mentor->user)->{'first_name_'.$l} }} {{optional($mentor->user)->{'last_name_'.$l} }}', {{ $mentor->cycle->organization_id }},{{ $mentor->cycle_id }})" title="Add new request" type="button" class="btn btn-icon rounded-circle btn-label-success">
															<i class="fa fa-plus"></i>
														</button>
													</td>
												</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	  </div>
      @endif
	
      @endif
      <!-- / My Mentors -->
    @endif
    </div>
  </div>

  <!--  Edit SP Modal -->
  @include('admin.clients.startup.partials._session_modal')
  <!--/ Edit SP Modal -->

  <!-- / Content wrapper -->
  {{-- <div class="modal fade text-left" id="SendRequest" tabindex="-1" role="dialog" aria-labelledby="sendrequest" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <form method="post" action="/dashboard/mentorship/addwait" enctype="multipart/form-data" autocomplete="off">
  
          <div class="modal-header">
            <h4 class="modal-title">{{__('dashboard.Add mentorship session with')}} <span class="primary" id="rname"></span></h4>
          </div>
          <div class="modal-body">
            @csrf
            <input type="hidden" name="mentorid" value="0" id="mentorid">
            <input type="hidden" name="organization_id" id="addOrganizationID">
            <input type="hidden" name="cycle_id" id="addCycleID">
            <div>
              <h5 class="text-primary"><i class="bx bx-time text-secondary"></i> {{__('dashboard.Mentor schedule')}}</h5>
              <span class="d-block bg-info p-1 white"> <ul class="mb-0" id="schedule"></ul> </span>
            </div>
            <hr>
            <div class="form-group" id="showdates">
              <label>{{__('dashboard.Start meeting Date/ Time')}}:</label>
              <input class="date width-250 form-control" type="text" id="date" name="date" value="" required onkeydown="return false">
            </div>
            <div class="form-group">
              <label>{{__('dashboard.Meeting duration')}}:</label>
              <select name="period" class="select form-control width-150">
                <option value="30">30 {{__('dashboard.Minutes')}}</option>
                <option value="60" selected>1 {{__('dashboard.Hour')}}</option>
                <option value="120">2 {{__('dashboard.Hours')}}</option>
                <option value="180">3 {{__('dashboard.Hours')}}</option>
                <option value="240">4 {{__('dashboard.Hours')}}</option>
                <option value="300">5 {{__('dashboard.Hours')}}</option>
              </select>
            </div>
            <div class="form-group">
              <label for="basicInputFile">{{__('dashboard.Send file with your request')}}</label>
              <div class="custom-file">
                <input type="file" class="custom-file-input" id="inputGroupFile01" name="file_url" accept="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf, image/*">
                <label class="custom-file-label" for="inputGroupFile01">{{__('dashboard.Choose file')}}</label>
              </div>
            </div>
            <label>{{__('dashboard.Add note')}}:</label>
            <div class="form-group">
              <textarea class="form-control" id="notes" name='notes' rows="4" placeholder="{{__('dashboard.Write your note here')}}" ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary ml-1" >
              
              <span class="d-none d-sm-block">{{__('dashboard.Submit')}}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div> --}}
  
@stop

@section('scripts')
{{-- <script src="{{ mix('js/app.js') }}"></script> --}}
  <!-- Vendors JS -->
  <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
  {{-- <script src="/vuexy/src/js/scripts/extensions/ext-component-ratings.js"></script> --}}


  
  <script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
  @include('partials.flatpickr-locale')
  <script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
  <script src="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
  <script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>


  <!-- Page JS -->
  {{-- <script src="/vuexy/assets/js/pages-account-settings-account.js"></script> --}}
  {{-- <script src="/vuexy/assets/js/forms-pickers.js"></script> --}}
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>
  <script src="/vuexy/assets/js/mentorship-session.js"></script>

  <script>
    $(document).ready(function() {
      var today = new Date().toISOString().split('T')[0]; // الحصول على تاريخ اليوم بصيغة yyyy-mm-dd
      $('#dfrom, #dto').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
      });

     
      $('.DatePicker').datepicker({
        format: 'yyyy-m-d H:i',
        onGenerate: function( ct ) { jQuery(this).find('.xdsoft_date').toggleClass('xdsoft_disabled'); },
        autoclose: true,
        todayHighlight: true,
        startDate: today,
        maxDate: '{{date('Y/m/d',strtotime("-1 days"))}}',
		  timepicker: true
      });

    });
  </script>

  @if( $notrated_men or $notrated_ser )
  <script src="/app-assets/vendors/js/extensions/jquery.rateyo.min.js"></script>
  <script>

function redirectWithDays(showValue) {
// الحصول على قيم التواريخ
const forteendays = document.getElementById('forteenDays').value;
const sevendays = document.getElementById('sevenDays').value;

// التحقق من وجود تواريخ والتحقق من تنسيقها لضمان التوافق
let url = `/dashboard?days=${showValue}`;

// إعادة التوجيه إلى الرابط المناسب
window.location.href = url;
}


	  $(function () {
		  var isRtl = $('html').attr('data-textdirection') === 'rtl';
		  var fullStar = $('.full-star-ratings');
		  var instance = fullStar.rateYo({rtl: isRtl});
		  // Rating onSet Event
		  if( instance.length ) { instance.rateYo({rtl: isRtl}).on('rateyo.set', function (e, data) { $('#rate').val(data.rating); }); } });
  </script>
@endif

<script>

	$(document).ready(function() {
		$("#mentorstable").DataTable({
			"responsive": true,
			"searching": true,
			"lengthChange": false,
			"paging": false,
			"bInfo": false,
			"columnDefs": [ {"orderable": false, "targets":  [3]}, ],
			@if( $l == 'ar' )
			"language": {
				"sEmptyTable":	 "ليست هناك بيانات متاحة في الجدول",
				"sLoadingRecords": "جارٍ التحميل...", "sProcessing":   "جارٍ التحميل...",
				"sLengthMenu":   "أظهر _MENU_ مدخلات", "sZeroRecords":  "لم يعثر على أية سجلات",
				"sInfo":		 "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
				"sInfoEmpty":	"يعرض 0 إلى 0 من أصل 0 سجل", "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
				"sInfoPostFix":  "", "sSearch":	   "ابحث:", "sUrl":		  "",
				"oPaginate": {"sFirst":	"الأول", "sPrevious": "السابق", "sNext":	 "التالي", "sLast":	 "الأخير"},
				"oAria": {"sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}
			}
			@endif
		});
		@if ($notrated_men or $notrated_ser)
		//Open rate popup
		$("#RequestRate").modal("show");
		@endif
	});

	$(document).ready(function() {
		$(".sessonsTable").DataTable({
			"responsive": true,
			"searching": true,
			"lengthChange": false,
			"paging": false,
			"bInfo": false,
			"columnDefs": [ {"orderable": false, "targets":  [3]}, ],
			@if( $l == 'ar' )
			"language": {
				"sEmptyTable":	 "ليست هناك بيانات متاحة في الجدول",
				"sLoadingRecords": "جارٍ التحميل...", "sProcessing":   "جارٍ التحميل...",
				"sLengthMenu":   "أظهر _MENU_ مدخلات", "sZeroRecords":  "لم يعثر على أية سجلات",
				"sInfo":		 "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
				"sInfoEmpty":	"يعرض 0 إلى 0 من أصل 0 سجل", "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
				"sInfoPostFix":  "", "sSearch":	   "ابحث:", "sUrl":		  "",
				"oPaginate": {"sFirst":	"الأول", "sPrevious": "السابق", "sNext":	 "التالي", "sLast":	 "الأخير"},
				"oAria": {"sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}
			}
			@endif
		});
		
	});	
</script>

  <script>
    function modify(type, id, stat) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      if (type === 3) {
        //delete
        if (confirm("{{ __('dashboard.sure del schedule') }}")) {
          $.ajax({
            type: "POST",
            url: "/dashboard/mentors/modify",
            data: {
              "id": id,
              "rtype": type,
            },
            success: function(response) {
              location.reload();
            },
            error: function(error) {
              console.log(error);
              alert("Error Occurred, Try again!");
            }
          });
        }
      } else if (type === 2) {
        //Enable-disable
        $.ajax({
          type: "POST",
          url: "/dashboard/mentors/modify",
          data: {
            "id": id,
            "rtype": type,
            "stat": stat
          },
          success: function(response) {
            location.reload();
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      } else if (type === 1) {
        // Edit current
        $.ajax({
          type: "POST",
          url: "/dashboard/mentors/modify",
          data: {
            "id": id,
            "rtype": type,
          },
          success: function(response) {
            switchtypes(response.type);
            $('#sid').val(response.id);
            $('#rtype').val(4);
            $('#type').val(response.type);
            $('#days').val(response.days);
            $('#dfrom').val(response.dfrom);
            $('#dto').val(response.dto);
            $('#tfrom').val(response.tfrom);
            $('#tto').val(response.tto);
            $('#notes').val(response.notes);
            $('#SendRequest').modal('show');
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      } else {
        //show from as new
        $('#sid, #type, #days, #dfrom, #dto, #tfrom, #tto, #notes').val();
        $('#rtype').val('0');
        switchtypes(type);
        $('#SendRequest').modal('show');
      }
    }

    function SelectType(type) {
      switchtypes(type);
    }

    function switchtypes(type) {
      switch (type) {
        case 'OneDay':
          $('#showdates').show();
          $('#showtto, #showdays').hide();
          $("#dfrom").prop('required', true);
          $("#days, #dto").prop('required', false);
          break;
        case 'Weekly':
          $('#showdates').hide();
          $('#showdays').show();
          $("#days").prop('required', true);
          $("#dfrom, #dto").prop('required', false);
          break;
        case 'WorkingDays':
          $('#showdates, #showdays').hide();
          $("#dfrom, #dto, #days").prop('required', false);
          break;
        case 'Period':
          $('#showtto,#showdates').show();
          $('#showdays').hide();
          $("#dfrom, #dto").prop('required', true);
          $("#days").prop('required', false);
          break;
      }
    }

    $('.start_meeting').click(function() {
      $.ajax({
        type: "GET",
        url: "/dashboard/mentor/meetingRedirect/" + $(this).data('value'),
        success: function(response) {
          alert(response['message']);
          window.open(response['join_url'], '_blank');
          window.location.href = '{{ route('mentorshipTasks.index') }}' + '?mentorship_id=' + response[
            'mentorship_id'];
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    });
  </script>
@stop
