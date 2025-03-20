@extends('layouts.admin')
@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.My Dashboard'))
@section('vendorstyle')
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/apex-charts/apex-charts.css" />
@stop
@section('style')
	<link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
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
		<div class="d-none">{!! redirect()->route('my_profile')->with(['msg' => 'WaitingYourRedirect', 'class' => 'danger']) !!}</div>
	@endif
	<!-- Content wrapper -->
	<div class="content-wrapper">
		<div class="container-xxl flex-grow-1 container-p-y">
			<div class="content-body">
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
							<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2" role="alert">
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
							<div class="d-flex justify-content-between align-items-center">
								<div class="d-flex align-items-center">
								<i class="ti ti-{{ session()->get('class') == 'success' ? 'like' : 'error' }}"></i>
								<span> {{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
							</div>
							</div>
						</div>
					</div>
				@endif
			</div>
			<div class="row g-6">
				<!-- Count cards -->
				@foreach( $countCards as $card )
				@if( array_intersect($card['permissions'], $permissions) == $card['permissions'] )
				<a href="{{ $card['href'] }}" class="col-lg-3 col-sm-6">
					<div class="card card-border-shadow-{{ $card['color'] }} h-100">
						<div class="card-body">
							<div class="d-flex justify-content-center align-items-center mb-2">
								<div class="avatar me-4">
									<span class="avatar-initial rounded bg-label-primary">
										<i class="ti {{ $card['icon'] }} text-{{ $card['color'] }} ti-28px"></i>
									</span>
								</div>
								<h4 class="mb-0 text-center">{{ $card['count'] }}</h4>
							</div>
							<p class="mb-1 text-center">{{ $card['text'] }}</p>
						</div>
					</div>
				</a>
				@endif
				@endforeach
				<!-- / Count cards -->
			</div>
			<div class="row mt-6">
				@foreach( $graphsCards as $card )
				@if( array_intersect($card['permissions'], $permissions) == $card['permissions'] )
				<div class="{{ $card['div-class'] }}">
				  <div class="card">
					<div class="card-header d-flex justify-content-between align-items-center">
					  <div class="d-flex align-items-center">
						<h5 class="card-title mb-0"> {{ $card['card-title'] }} </h5>
					  </div>
					</div>
					<div class="card-body">
						@if( $card['type'] == 'div' ) <div id="{{ $card['id'] }}" class="chartjs" data-height="345"></div> @endif
						@if( $card['type'] == 'canvas' ) <canvas id="{{ $card['id'] }}" class="chartjs" data-height="345"></canvas> @endif
					</div>
				  </div>
				</div>
				@endif
				@endforeach
			</div>
			<div class="row mt-6">
				@if( array_intersect(['dashboard_tables.latest_services'], $permissions) )
					<div class="col-sm-6 col-12 mb-6">
						<div class="card">
							<div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom" style="width: '100%">
								<div class="d-flex align-items-center py-2">
									<i class="ti ti-devices-question ti-24px"></i>
									<h5 class="card-title mb-0 mx-2">{{ __('dashboard.Latest services') }}</h5>
								</div>
							</div>
							<div class="card-body">
								@if( count($latest_services) > 0 )
									@foreach( $latest_services as $service )
										<div class="d-flex justify-content-between align-items-center my-2">
											<div class="widget-todo-title-area d-flex align-items-center">
												<a href="/dashboard/directory?find={{ $service->name }}">
													<span class="widget-todo-title ml-50 text-secondary">
													<i class="ti ti-link"></i>
													{{ $service->name }}</span>
												</a>
											</div>
											<div class="widget-todo-item-action d-flex align-items-center">
												<div class="btn btn-label-primary py-1 rounded d-flex align-items-center justify-content-center" style="width: 200px; height: 30px">
													<a href="/dashboard/directory?type={{ $service->category->id }}">
														{{ $service->category->name }}
													</a>
												</div>
											</div>
										</div>
									@endforeach
								@else
									<div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
										<div class="widget-todo-title-area d-flex align-items-center">
											<span class="widget-todo-title ml-50"> <i class="bx bxs-bell-ring text-danger"></i> {{ __('dashboard.No services added') }}.</span>
										</div>
									</div>
								@endif
							</div>
						</div>
					</div>
				@endif
				@if( array_intersect(['dashboard_tables.available_surveys'], $permissions) )
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

												@if( $survey->triggered_by == 'due' ) {{ __('dashboard.surveyStepName' . ucFirst($survey->triggered_on) ) }} @endif
												@if( $survey->triggered_on == 'mentorship' )
													@if( $survey->triggered_by == 'session' )
														{{ __('dashboard.with') }} 
														@php $mentor = $survey->middleware->mentor; @endphp
														{{ $mentor->name }}
													@elseif( $survey->triggered_by == 'due' )
														- {{ $survey->middleware->title }}
													@else
														-
													@endif
												@elseif( $survey->triggered_on == 'training' )
													@if( $survey->triggered_by == 'session' )
														{{ '(' . __('cycles.' . $survey->middleware->type ) . ')' }} {{ $survey->middleware->title }}
													@elseif( $survey->triggered_by == 'due' )
														- {{ $survey->middleware->title }}
													@elseif( $survey->triggered_by == 'quiz' )
														- {{ $survey->middleware->title }}
													@else
														-
													@endif
												@endif
											</div>
											<div class="col-3 text-end">
												<a href="{{ route('surveys.taking', ['id' => $survey->id]) }}" class="btn btn-sm btn-label-primary">{{ __('dashboard.startSurvey') }}</a>
											</div>
									  </div>
									@endforeach
								</div>
							</div>
						</div>
					</div>
				@endif
				@if( array_intersect(['dashboard_tables.latest_events'], $permissions) )
					<div class="col-12">
						<section>
							<div class="row">
								<div class="col-md-12">
									<div class="card mt-10">
										<div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom">
											<div class="d-flex align-items-center py-2">
												<i class="ti ti-calendar-event ti-24px"></i>
												<h5 class="card-title mb-0 mx-2">{{ __('dashboard.Latest Events') }}</h5>
											</div>
										</div>
										<div class="card-body">
											@if( count( $latest_events ) > 0 )
												@foreach ($latest_events as $event)
													<div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
														<div class="widget-todo-title-area d-flex align-items-center">
															<a href="/dashboard/calendar">
																<span class="widget-todo-title ml-50 text-secondary"> <i class="ti ti-link"></i> {{ $event->title }} </span>
															</a>
														</div>
														<div class="btn btn-label-success text-success py-1 rounded d-flex align-items-center justify-content-center" style="width: 200px; height: 30px;">
															{{ date('d-m-Y', strtotime($event->start)) }}
														</div>
													</div>
												@endforeach
											@else
												<div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
													<div class="widget-todo-title-area d-flex align-items-center">
														<span class="widget-todo-title ml-50">
															<i class="bx bxs-bell-ring text-danger"></i>
															{{ __('dashboard.No Events added') }} 
															'<a href="/dashboard/calendar">{{ __('dashboard.Add yours now') }}</a>'.
														</span>
													</div>
												</div>
											@endif
										</div>
									</div>
								</div>
							</div>
						</section>
					</div>
				@endif
				@if( array_intersect(['dashboard_tables.current_enrolled_program'], $permissions) )
					<div class="col-12">
						<section>
							<div class="row">
								<div class="col-md-12">
									@if( $company && $cycle_id != 0 )
										<div class="card mt-10">
											<div class="card-header border-bottom">
												<h5 style="text-transform: capitalize">
													<i class="ti ti-briefcase ti-24px"></i>
													<span class="mx-2">{{ __('dashboard.My progress') }} : {{ $cycleData->title }}</span>
												</h5>
												<span class="text-capitalize">{{ __('dashboard.start from') . ': ' . $cycleData->start_format . ' ' . __('dashboard.to') . ': ' . $cycleData->end_format }}</span>
												<div class="d-flex align-items-center mt-3">
													@if( !is_null( $organization ) )
														<img src="{{ $organization->image_url }}" alt="avatar" class="rounded-circle" height="35" width="35">
													@else
														<img src="/images/logo/{{ $setting->Logo }}" alt="avatar" class="logo-img">
													@endif
													<span class="mx-3 text-secondary">{{ $organization->name }}</span>
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
															<tr>
																<td>
																	@if( $step->stype == 1 )
																		@if( $step->arr == 'Completed' || $step->arr == 'انتهت' )
																			@if( $company->approved && $_user->ableTo('cycles.my', $permissions) )
																				<a href="/dashboard/cycles/{{ $step->fLink }}"> {{ $step->title }} </a>
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
																<td><span class="badge bg-{{ $step->color }} text-{{ $step->textColor }}">{{ $step->status }}</span></td>
																<td>{{ $step->from }}</td>
																<td>{{ $step->to }}</td>
																<td style="height: 50px;">
																	<span style="width: 350px; height: 100%; display: inline-block; white-space: normal;"> {{ $step->description ? $step->description : '...' }} </span>
																</td>
															</tr>	
														@endforeach
													</tbody>
												</table>
											</div>
										</div>
									@else
										<div class="card mt-10">
											<div class="card-header border-bottom">
												<h4 class="card-title d-flex align-items-center">
													<i class="ti ti-clipboard-list ti-28px"></i> <span class="mx-2">{{__('dashboard.IncubationList')}}</span>
												</h4>
											</div>
											<div class="card-body">
												@if( count( $workingCycles ) > 0 )
													@foreach( $workingCycles as $cycle )
														<div class="border-bottom">
															<div class="card-header">
																<h4 class="primary"><i class="bx bxs-bell"></i> {{ $cycle->title }}</h4>
																<div class="d-flex align-items-center mt-2">
																	<div class="media-left">
																		@if (!is_null( $cycle->organization->image_url ) )
																			<img src="{{ $cycle->organization->image_url }}" alt="avatar" class="rounded-circle" height="40" width="40">
																		@endif
																	</div>
																	<div class="mx-2"> {{ $cycle->organization->name }} </div>
																</div>
															</div>
															<div class="card-body bg-rgba-light">
																<p class="card-text"> {{ $cycle->description }} </p>
																<div class="d-flex align-items-center justify-content-between" style="width:100%">
																	<small class="text-muted" style="text-transform: capitalize"> {{__('dashboard.start from')}} {{ $cycle->start }} </small>
																	<a href="/dashboard/cycles/view/{{ $cycle->id}}" class="btn btn-primary">
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
															<div class="avatar bg-rgba-warning m-0 mr-75"> <div class="avatar-content"> <i class="bx bx-stats text-warning"></i> </div> </div>
															{{__('dashboard.No Cycles started yet')}}...
														</h5>
													</div>
												@endif
											</div>
										</div>
									@endif
								</div>
							</div>
						</section>
					</div>
				@endif
				
				@if( $cType == 5 && array_intersect(['dashboard_tables.my_final_session'], $permissions) )
					<div class="col-12">
						<section>
							<div class="row">
								<div class="col-md-12">
									<div class="card mt-10">
										<div class="card-header border-bottom">
											<h4 class="card-title align-items-center">
												<i class='bx bx-card font-medium-4 mr-1'></i> {{ __("admin.Final_Session") }}
											</h4>
										</div>
										<div class="card-body">
											@if( !is_null( $myFinal ) && $myFinal->join_url )
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
																		<a class="media-left" href="/dashboard/users/{{ $myFinal->company->user->id }}/edit">
																			<img src="{{ $myFinal->company->user->image_url }}" alt="avatar" class="rounded-circle me-3" height="40" width="40">
																		</a>
																		<a href="/dashboard/users/{{ $myFinal->company->user->id }}/edit">
																			<div class="media-body">
																				<h6 class="media-heading mb-0 text-nowrap">{{ $myFinal->company->user->name }}</h6>
																				<span class="font-small-2 text-nowrap">{{ $myFinal->company->user->title }}</span>
																			</div>
																		</a>
																	</div>
																</td>
																<td>
																	@foreach( $myFinal->judges as $judger )
																		@if(!empty($judge))
																			<div style="cursor: pointer;" class="d-flex align-items-center">
																				<div class="chip-body">
																					<div class="avatar">
																						<img class="img-fluid rounded-circle me-3" src="{{ $judger->img_url }}" alt="photo" height="20" width="20">
																					</div>
																					<span class="chip-text text-nowrap"> {{ $judger->name }} </span>
																				</div>
																			</div>
																		@endif
																	@endforeach
																</td>
																<td> {{ $myFinal->datetime }} <br>({{ $myFinal->duration }}M) </td>
																<td> {{ $myFinal->datetime < now() ? __('Yes') : __('No') }} </td>
																<td> <a href="{{ $myFinal->join_url }}" target="_blank">({{__('dashboard.Join now')}})</a> </td>
															</tr>
														</tbody>
													</table>
												</div>
											@else
												<div class="text-center">
													<h3>{{__('dashboard.Your final session will start soon')}}.</h3>
												</div>
											@endif
										</div>
									</div>
								</div>
							</div>
						</section>
					</div>
				@endif
				@if( $cType != 5 && array_intersect(['dashboard_tables.my_last_training'], $permissions) && $sessions->count() > 0 )
					<div class="col-12">
						<section>
							<div class="row">
								<div class="col-md-12">
									<div class="card mt-10">
										<div class="card-header border-bottom">
											<h5 style="text-transform: capitalize">
												<i class="ti ti-clipboard-list ti-28px"></i>
												<span class="mx-2"> {{ __('dashboard.My Latest training') }} ::
													<a href="/dashboard/training/my">{{ __('dashboard.Show all') }}</a>
												</span>
											</h5>
										</div>
										<div class="card-body">
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
														@foreach( $sessions as $key => $session )
															<tr>
																<td>{{ $key + 1 }}</td>
																<td>{{ __('cycles.' . $session->type) }}</td>
																<td>{{ $session->title }}</td>
																<td>{{ $session->datetime }}</td>
																<td>{{ $session->end_datetime ?? '-' }}</td>
																<td>
																	@if( $session->type == 'sessions' )
																		{{ __('cycles.Duration') }}: {{ $session->duration }} {{ __('cycles.Minutes') }} :
																		<a href="{{ $session->join_url }}" target="_blank">
																			<button title="Join only" type="button" class="btn btn-icon rounded-circle btn-light-primary"> <i class="bx bx-desktop"></i> </button>
																		</a>
																	@elseif ($session->type == 'offline')
																		<b>{{ __('cycles.trainer name') }}:</b> {{ $session->trainer_name }}<br>
																		<b>{{ __('cycles.training location') }}:</b>
																		{{ $session->location }} <br>
																	@else
																		<a href="/dashboard/training/my/{{ $session->location }}">{{ __('cycles.Display course contents') }} </a>
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
						</section>
					</div>
				@endif
				@if( array_intersect(['dashboard_tables.mentors_sessions'], $permissions) )
					<div class="col-12">
						<section>
							<div class="row">
								<div class="col-md-12">
									<div class="card mt-10">
										<div class="card-header border-bottom">
											<h4 class="card-title align-items-center">
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
										<div class="card-body">
											<div class="table-responsive">
												<table class="table mb-0 sessonsTable" >
													<thead class="bg-label-light">
													<tr>
														<th>{{__('cycles.User/Title')}}</th>
														<th>{{__('cycles.Mentor')}}</th>
														@if ($_user->hasRole('Admin'))<th>{{__('cycles.Rate/Comment')}}</th>@endif
														<th>{{__('cycles.Date/Time')}}</th>
														<th>{{__('cycles.Approved')}}</th>
														<th>{{__('cycles.Done')}}</th>
														<th>{{__('cycles.Actions')}}</th>
													</tr>
													</thead>
													<tbody>
														@foreach($nextSessions as $session)
															<tr>
																<td>
																	<div class="d-flex align-items-center">
																		<a class="media-left" href="/dashboard/users/{{ $session->user_id }}/edit">
																			<img src="{{ $session->user->image_url }}" alt="avatar" class="rounded-circle me-3" height="40" width="40">
																		</a>
																		<a href="/dashboard/users/{{ $session->user_id }}/edit">
																			<div class="media-body">
																				<h6 class="media-heading mb-0 text-nowrap">{{ $session->user->name }}</h6>
																				<span class="font-small-2 text-nowrap">{{$session->user->title }}</span>
																			</div>
																		</a>
																	</div>
																</td>
																<td>
																	<div style="cursor: pointer;" class="d-flex align-items-center" onclick="mentor({{ $session->mentor_id }}, '{{ $session->mentor->name }}', '{{ $session->mentor->ratings }}')">
																		<div class="chip-body">
																			<div class="avatar">
																				<img class="img-fluid rounded-circle me-3" src="{{$session->mentor->image_url}}" alt="Mentor photo" height="20" width="20">
																			</div>
																			<span class="chip-text text-nowrap"> {{$session->mentor->name}} </span>
																		</div>
																	</div>
																</td>
																@if ($_user->hasRole('Admin'))
																<td> <small> {!! $session->rate_comment ? str_repeat('<i class="bx bxs-star" style="color: #fc960f;"></i>', $session->rating) . '<br>' . $session->rate_comment : $session->comment !!} </small> </td>
																@endif
																<td> {{ $session->zoom_date }} <br> ({{ $session->period }}M) </td>
																<td> <div class="d-flex align-items-center gap-1"> {!!  __( $session->approved ) ??'<span class="warning">'.__('Waiting').'</span>'  !!} </div> </td>
																<td> {{ $session->done ?__('Yes') : __('No') }} </td>
																<td> <a href="{{ $session->join_url }}" target="_blank">({{ __('dashboard.Join now') }})</a> </td>
															</tr>
														@endforeach
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</section>
					</div>
				@endif
				@if( array_intersect(['dashboard_tables.mentors'], $permissions) && !is_null($cycle_id) )
					<div class="col-12">
						<div class="card mt-10">
							<section>
								<div class="row">
									<div class="col-md-12">
										<div class="card">
											<div class="card-header border-bottom mb-4">
												<h4 class="card-title align-items-center">
													<i class="ti ti-school ti-28px"></i>
													<span class="mx-2">{{ __("dashboard.Mentors") }}</span>
												</h4>
											</div>
											<div class="card-body">
												<div class="table-responsive">
													<table class="table" id="mentorstable">
														<thead class="bg-label-light">
															<tr>
																<th>#</th>
																<th>{{__('dashboard.Name/Title')}}</th>
																<th>{{__('dashboard.Bio')}}</th>
																<th>{{__('dashboard.Specialization')}}</th>
																<th>{{__('dashboard.Linkedin')}}</th>
																@if( $_user->ableTo('mentors.modify', $permissions) )<th>{{__('dashboard.Request')}}</th>@endif
															</tr>
														</thead>
														<tbody>
															@foreach($mentors->filter(function($mentor) use ($cycle_id) { return in_array($cycle_id, $mentor->mentor_cycles); })->values() as $key => $mentor)
																<tr>
																	<td>{{$key+1}}</td>
																	<td>
																		<div class="d-flex align-items-center gap-1">
																			<a class="media-left"> <img src="{{ $mentor->image_url }}" alt="avatar" class="rounded-circle me-3" height="40" width="40"> </a>
																			@if( $_user->ableTo('show_mentor_profile', $permissions) )<a target="_blank" href="{{route('show_mentor_profile', $mentor->id)}}">@endif
																				<div class="media-body">
																					<h6 class="media-heading mb-0 text-nowrap">{{ $mentor->name ?? '-' }}</h6>
																					<span class="font-small-2 text-nowrap">{{ $mentor->title ?? '-' }}</span>
																				</div>
																			@if( $_user->ableTo('show_mentor_profile', $permissions) )</a>@endif
																		</div>
																	</td>
																	<td> {{ $mentor->bio ?? '-' }} </td>
																	<td> {{ $mentor->specialization ?? '-' }} </td>
																	<td>
																		@if( $mentor->linkedin ?? 0 )
																			<a title="Linkedin URL" target="_blank" href="{{ $mentor->linkedin }}" class="btn btn-icon rounded-circle btn-label-primary"> <i class="fa-brands fa-linkedin-in"></i></a>
																		@endif
																	</td>
																	@if( $_user->ableTo('mentors.modify', $permissions) )
																	<td>
																		<button onclick="request({{ $mentor->id }},'{{ $mentor->name }}', {{ $mentor->organization_id }}, {{ $mentor->cycle_id }})" title="Add new request" type="button" class="btn btn-icon rounded-circle btn-label-success">
																			<i class="fa fa-plus"></i>
																		</button>
																	</td>
																	@endif
																</tr>
															@endforeach
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
							</section>
						</div> 
					</div>
				@endif
			</div>
		</div>
	</div>
	@if( $_user->ableTo('mentors.modify', $permissions) && array_intersect(['dashboard_tables.mentors'], $permissions) && !is_null( $cycle_id ) )
	<!--  Edit SP Modal -->
  		@include('admin.clients.startup.partials._session_modal')
  	<!--/ Edit SP Modal -->
	@endif
	<!-- / Content Wrapper -->
@stop
@section('scripts')
	<script src="/vuexy/assets/vendor/libs/apex-charts/apexcharts.js"></script>
	<script src="/vuexy/assets/vendor/libs/chartjs/chartjs.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
	<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
	<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
	<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
	<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
	<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
	<script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
	<script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
	<script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
	<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
	<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
	<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
	<script src="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
	<script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
@stop
@section('pagescripts')
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
	<script>
		// User distribution by country
		const userDistributionData = @json($countries);

		// Sector Breakdown Chart Data
		const sectorBreakdownData = @json($sector_data);

		// Project Distribution Pie Chart Data
		const projectsPieData = @json($project_types);

		// Development Stage Chart Data
		const devStageData = @json($development_stages);

		// Target Market Analysis Chart Data
		const targetMarketData = @json($target_market_data);

		// Project Status Over Time Chart Data
		const projectStatusData = @json($project_status);

		// Total jobs created
		const totalJobsCreated = @json($total_jobs_created);

		// Total Founders
		const totalFounders = @json($total_founders);

		// Companies by SDG Chart Data
		const sdgData = @json($sdg_data);

		// Companies by sector
		const companiesBySectorData = @json($sector_distribution);

		// Horizontal Bar Chart
		const seriesName = '{{ __("admin.Registered") }}';
		const barChartCategories = {!! json_encode($cycles_labels) !!};
		const seriesData = {!! json_encode($cycles_startups_count) !!};

		// Vertical Bar Chart 
		const verticalBarLabels = {!! json_encode($users_gov->pluck('state_' . $l)) !!};
		const verticalBarData = {!! json_encode($users_gov->pluck('total')) !!};

		// Pie Chart
		const pieLabels = ['{{ __("admin.Female") }}', '{{ __("admin.Male") }}'];
		const pieData = {!! json_encode($users_gender->pluck('total')) !!};

		const internshipSeriesName = '';
		const mentorshipStatisticsLabels = null;
		const mentorshipStatisticsData = null;
		const internshipBarChartCategories = null;
		const internshipSeriesData = null;
		const cyclesStartupName = '';
		const cyclesStartupLabels = null;
		const cyclesStartupData = null;
	</script>
	<script src="/app-assets/vendors/js/extensions/jquery.rateyo.min.js"></script>
	<script src="/vuexy/assets/js/app-admin-dashboard.js"></script>
	<script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>
	<script src="/vuexy/assets/js/charts-apex.js"></script>
	<script src="/vuexy/assets/js/charts-chartjs.js"></script>
	<script src="/vuexy/assets/js/mentorship-session.js"></script>
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
			if( instance.length ) { instance.rateYo({rtl: isRtl}).on('rateyo.set', function (e, data) { $('#rate').val(data.rating); }); }
		});
	</script>
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
	</script>
@stop