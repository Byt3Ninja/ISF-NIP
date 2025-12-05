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
<link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify.min.css">
<link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify-bootstrap.min.css">
@stop
@section('content')
@if (!$_user->first_name_ar)
<div class="d-none">{!! redirect()->route('user.profile_view')->with(['msg' => 'WaitingYourRedirect', 'class' =>
	'danger']) !!}</div>
@endif
<!-- Content wrapper -->
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		@if( $_user->hasAnyRole('Registered', 'Admin') )
		@php
		
		@endphp
		@if(Auth::user()->hasRole('Registered') || Auth::user()->hasRole('Mentor'))
			@if( isset($newhackathon) && $newhackathon )
			{{-- @if(now()->diffInDays($newhackathon->created_at) < 300) --}}
			@include('admin.includes.banner_header', ['newhackathon' => $newhackathon])
			<br>
					@endif
		@endif
		@endif
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
					<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
						role="alert">
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						<div class="d-flex justify-content-between align-items-center">
							<div class="d-flex align-items-center">
								<i class="ti ti-{{ session()->get('class') == 'success' ? 'like' : 'error' }}"></i>
								<span> {{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }} </span>
							</div>
						</div>
					</div>
				</div>
			</div>
			@endif
		</div>

		@if( $_user->hasRole('Registered') )
		@if($_user->company?->cycle == null)
			<div class="col-md-12">
				<div class="alert alert-warning alert-dismissible fade show mb-2" role="alert">
					{{ __('admin.Welcome_StartUp_Registration_Please_Join_The_Program_The_First') }}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			</div>
		@endif
		@endif

		<!-- Demo Invitation Notification -->
		{{-- <div class="col-md-12">
			<div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
				<div class="d-flex justify-content-between align-items-center">
					<div class="d-flex align-items-center">
						<i class="ti ti-mail me-3 ti-24px"></i>
						<div>
							<h6 class="mb-1">{{ __('dashboard.New Invitation Received!') }}</h6>
							<small class="text-muted">{{ __('dashboard.You have been invited to participate in the Startup Pitch Competition 2025') }}</small>
						</div>
					</div>
					<div class="d-flex gap-2">
						<button type="button" class="btn btn-sm btn-outline-primary" onclick="openInvitationModal()">
							<i class="ti ti-eye me-1"></i>
							{{ __('dashboard.View Details') }}
						</button>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				</div>
			</div>
		</div> --}}
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
						<p class="mb-1 text-center"> @if( Str::contains($card['text'], '.') )
								{{ __($card['text']) ?: $card['text'] }}
							@else
								{{ __('admin.' . $card['text']) ?: $card['text'] }}
							@endif</p>
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
						@if( $card['type'] == 'canvas' ) <canvas id="{{ $card['id'] }}" class="chartjs" data-height="345"></canvas>
						@endif
					</div>
				</div>
			</div>
			@endif
			@endforeach
		</div>
		<div class="row mt-6">
			@if( array_intersect(['dashboard.widgets_tables_latest_services_view'], $permissions) )
			@if( $_user->hasRole('Registered') )
			@if($_user->company?->cycle != null)
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
								<a href="/dashboard/directory?find={{ $service->{'name_' . $l} }}">
									<span class="widget-todo-title ml-50 text-secondary">
										<i class="ti ti-link"></i>
										{{ $service->{'name_' . $l}  }}</span>
								</a>
							</div>
							<div class="widget-todo-item-action d-flex align-items-center">
								<div class="btn btn-label-primary py-1 rounded d-flex align-items-center justify-content-center"
									style="width: 200px; height: 30px">
									<a href="/dashboard/directory?type={{ $service->category->id }}">
										{{ $service->category->{'name_' . $l}  }}
									</a>
								</div>
							</div>
						</div>
						@endforeach
						@else
						<div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
							<div class="widget-todo-title-area d-flex align-items-center">
								<span class="widget-todo-title ml-50"> <i class="bx bxs-bell-ring text-danger"></i> 
								{{ __('dashboard.No services added') }}.
								</span>
							</div>
						</div>
						@endif
					</div>
				</div>
			</div>
			@endif
			@endif
			@endif
			@if( array_intersect(['dashboard.widgets_tables_available_surveys_view'], $permissions) )
			@if( $_user->hasRole('Registered') )
			@if($_user->company?->cycle != null)
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
									{{ __('dashboard.surveyAbout') . ' ' . __('dashboard.surveyTriggerBy' . ucFirst($survey->triggered_on)
									. ucFirst($survey->triggered_by) ) }}

									@if( $survey->triggered_by == 'due' ) {{ __('dashboard.surveyStepName' .
									ucFirst($survey->triggered_on) ) }} @endif
									@if( $survey->triggered_on == 'mentorship' )
									@if( $survey->triggered_by == 'session' )
									{{ __('dashboard.with') }}
									@php $mentor = $survey->middleware->mentor; @endphp
									{{ $mentor->name ?? '' }}
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
									<a href="{{ route('surveys.take', ['id' => $survey->id]) }}" class="btn btn-sm btn-label-primary">{{
										__('dashboard.startSurvey') }}</a>
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
			@if( array_intersect(['dashboard.widgets_tables_latest_events_view'], $permissions) )
			@if( $_user->hasRole('Registered') )
			@if($_user->company?->cycle != null)
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
												<span class="widget-todo-title ml-50 text-secondary"> <i class="ti ti-link"></i> {{
													$event->title }} </span>
											</a>
										</div>
										<div
											class="btn btn-label-success text-success py-1 rounded d-flex align-items-center justify-content-center"
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
			@endif
			@endif
			@if( array_intersect(['dashboard.widgets_tables_current_enrolled_program_view'], $permissions) )
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
									<span class="text-capitalize">{{ __('dashboard.start from') . ': ' . $cycleData->start_format . ' ' .
										__('dashboard.to') . ': ' . $cycleData->end_format }}</span>
									<div class="d-flex align-items-center mt-3">
										@if( !is_null( $organization ) )
										<img src="{{ $organization->image_url }}" alt="avatar" class="rounded-circle" height="35"
											width="35">
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
												<th>{{ $l == 'ar' ? 'الاجراءات' : 'Actions' }}</th>
											</tr>
										</thead>
										<tbody>
											@foreach( $steps as $step )
											<tr>
												<td>
													@if( $step->stype == 1 )
													@if( $step->status == 'Completed' || $step->status == 'انتهت' )
													@if( $company->approved && $_user->ableTo('cycles.list', $permissions) )
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
												<td><span class="badge bg-{{ $step->color }} text-{{ $step->textColor }}">{{ __('admin.'.$step->status)
														}}</span></td>
												<td>{{ $step->from }}</td>
												<td>{{ $step->to }}</td>
												<td style="height: 50px;">
													<span style="width: 350px; height: 100%; display: inline-block; white-space: normal;"> {{
														$step->description ? $step->description : '...' }} </span>
												</td>
												<td>
													@if( $step->stype == 1 || $step->stype == 5 )
													@if( $step->status == 'On going' )
														<a href="/dashboard/cycles/my/{{ $step->id }}" class="btn btn-sm btn-primary"
															title="{{ __('admin.Submit') }}"> 
															{{ __('admin.Submit') }} 
															<i class="ti ti-send ms-2 ti-16px"></i>
														</a>
													@endif
													@endif
													 @if($step->stype == 1 && isset($step->form) && $step->form)
															<a href="{{ route('forms.render_view', $step->form->id) }}" id="{{ $step->id }}">
																<button class="btn btn-sm btn-label-primary">
																	<i class="ti ti-eye me-2" title="{{ __('cycles.Show Form') }}"></i>
																	<span class="fw-semibold">{{ __('cycles.Show Form') }}</span>
																</button>
															</a>
														@endif
														{{-- @if ($step->data && $step->status == 'Started')  --}}
															{{-- <a href="/dashboard/show-form/{{ $step->data }}">
																<span class="btn btn-icon btn-label-primary rounded-circle">
																		<i class="ti ti-eye"></i>
																</span>
															</a> --}}
														{{-- @endif --}}
												</td>
											</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
							@else
							 <div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title d-flex align-items-center">
            <i class="ti ti-clipboard-list ti-28px"></i>
            <span class="mx-2">{{ __('dashboard.IncubationList') }}</span>
        </h5>
    </div>

    <div class="card-body">
        @if(count($workingCycles) > 0)
            @foreach($workingCycles as $cycle)
                <div class="border-bottom">
                    <div class="card-header">
                        <h4 class="primary">
                            <i class="bx bxs-bell"></i>
                            {{ $cycle->title }}
                        </h4>

                        <div class="d-flex align-items-center mt-2">
                            <div class="media-left">
                                @if(!empty($cycle->organization->image_url))
                                    <img
                                        src="{{ $cycle->organization->image_url }}"
                                        alt="{{ $cycle->organization->name }} avatar"
                                        class="rounded-circle"
                                        height="40"
                                        width="40"
                                    >
                                @endif
                            </div>

                            <div class="mx-2">{{ $cycle->organization->name }}</div>
                        </div>
                    </div>

                    <div class="card-body bg-rgba-light">
                        <p class="card-text mb-2">{{ $cycle->description }}</p>
                        @php $hasPreRequests = !empty($cycle->pre_requests) && count($cycle->pre_requests) > 0; @endphp
						

												<div class="alert alert-info alert-dismissible p-3 rounded mb-4 position-relative"
													style="display: {{ $hasPreRequests ? 'block' : 'none' }};">
													<h6 class="d-flex align-items-center">
														<i class="ti ti-info-circle text-info ti-24px me-2"></i>
														@if($_user->company && $_user->company->name)
														{{ __('dashboard.To join this program as a startup, please complete these requirements first:') }}
														@else
														{{ __('dashboard.To get started with this program, please complete your profile requirements:') }}
														@endif
													</h6>
													<ul class="list-unstyled mb-0 d-flex flex-wrap gap-2">
														@foreach($cycle->pre_requests as $pr)
														<li id="{{ $pr['id'] }}" class="pre-request-item">
															<a href="{{ $pr['url'] }}?action=complete-profile" class="btn btn-sm btn-outline-info d-flex align-items-center gap-2" role="button"
																aria-label="{{ __($pr['label']) }}">
																<i class="ti ti-external-link"></i>
																<span>{{ __($pr['label']) }}</span>
															</a>
														</li>
														@endforeach
													</ul>
													@if($_user->company && $_user->company->name)
													<p class="text-muted small mt-2 mb-0">
														<i class="ti ti-lightbulb me-1"></i>
														{{ __('dashboard.Completing these steps will help your startup get the most out of the program.') }}
													</p>
													@else
													<p class="text-muted small mt-2 mb-0">
														<i class="ti ti-lightbulb me-1"></i>
														{{ __('dashboard.Setting up your profile properly will ensure you have access to all program features.') }}
													</p>
													@endif
													<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="position: absolute; top: 0; {{ $l == 'en' ? 'right' : 'left' }}: 0.15rem;">
													</button>
												</div>

                        <div class="d-flex align-items-center justify-content-between" style="width:100%">
                            <small class="text-muted" style="text-transform: capitalize">
                                {{ __('dashboard.start from') }} {{ $cycle->start }}
                            </small>

                            <button
                                class="btn btn-primary"
                                onclick="handleJoinClick(this)"
                                data-cycle-id="{{ $cycle->id }}"
                                data-has-pre="{{ $hasPreRequests ? 'true' : 'false' }}"
                                data-join-url="{{ url('/dashboard/cycles/view/' . $cycle->id) }}"
                                data-pre-requests="{{ json_encode(collect($cycle->pre_requests ?? [])->map(function($pr) { return ['url' => $pr['url'] ?? '#', 'label' => __($pr['label'] ?? ''), 'action' => $pr['action'] ?? '']; })->toArray()) }}"
                            >
                                <span class="me-2">{{ __('dashboard.Join now') }}</span>
                                <i class="ti ti-arrows-join"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-4">
                <h5 class="m-75 p-75">
                    <div class="avatar bg-rgba-warning m-0 mr-75 d-inline-flex align-items-center justify-content-center">
                        <div class="avatar-content"> <i class="bx bx-stats text-warning"></i> </div>
                    </div>
                    {{ __('dashboard.No Cycles started yet') }}...
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

			@if( $cType == 5 && array_intersect(['dashboard.widgets_tables_my_final_session_view'], $permissions) )
			<div class="col-12">
				<section>
					<div class="row">
						<div class="col-md-12">
							<div class="card mt-10">
								<div class="card-header border-bottom">
									<h5 class="card-title align-items-center">
										<i class='bx bx-card font-medium-4 mr-1'></i> {{ __("admin.Final_Session") }}
									</h5>
								</div>
								<div class="card-body">
									@if( !is_null( $myFinal ) && $myFinal->join_url )
									<div class="table-responsive">
										<table class="table mb-0 sessonsTable">
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
																<img src="{{ $myFinal->company->user->image_url }}" alt="avatar"
																	class="rounded-circle me-3" height="40" width="40">
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
																	<img class="img-fluid rounded-circle me-3" src="{{ $judger->img_url }}" alt="photo"
																		height="20" width="20">
																</div>
																<span class="chip-text text-nowrap"> {{ $judger->name }} </span>
															</div>
														</div>
														@endif
														@endforeach
													</td>
													<td> {{ $myFinal->datetime }} <br>({{ $myFinal->duration }}M) </td>
													<td> {{ $myFinal->datetime < now() ? __('Yes') : __('No') }} </td>
													<td> 
														@php
															$endTime = \Carbon\Carbon::parse($myFinal->datetime)->addMinutes($myFinal->duration);
														@endphp
														@if(now() > $endTime)
															<span class="text-muted">{{ $l == 'ar' ? 'انتهت الجلسة' : 'Session Completed' }}</span>
														@else
															<a href="{{ $myFinal->join_url }}" target="_blank">({{__('dashboard.Join now')}})</a>
														@endif
													</td>
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
			@if( $cType != 5 && array_intersect(['dashboard.widgets_tables_my_last_training_view'], $permissions) &&
			$sessions->count() > 0 )
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
													<td>{{ FormatDate($session->datetime, 'Y-m-d H:i A') ?? '-' }} </td>
													<td>{{ FormatDate($session->end_datetime, 'Y-m-d H:i A') ?? '-' }}</td>
													<td>
														@if( $session->type == 'sessions' )
														{{ __('cycles.Duration') }}: {{ $session->duration }} {{ __('cycles.Minutes') }} :
														<a href="{{ $session->join_url }}" target="_blank">
															<button title="Join only" type="button"
																class="btn btn-icon rounded-circle btn-light-primary"> <i class="ti ti-brand-zoom"></i>
															</button>
														</a>
														@elseif ($session->type == 'offline')
														<b>{{ __('cycles.trainer name') }}:</b> {{ $session->trainer_name }}<br>
														<b>{{ __('cycles.training location') }}:</b>
														{{ $session->location }} <br>
														@else
														<a href="/dashboard/training/my/{{ $session->location }}">{{ __('cycles.Display course
															contents') }} </a>
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
			@if( array_intersect(['dashboard.widgets_tables_mentors_sessions_view'], $permissions) )
			<div class="col-12">
				<section>
					<div class="row">
						<div class="col-md-12">
							<div class="card mt-10">
								<div class="card-header border-bottom">
									<div class="d-flex justify-content-between align-items-center">
										<div class="d-flex align-items-center">
											<i class='ti ti-calendar-event me-2'></i> 
											<h5 class="card-title mb-0">
												{{ __("dashboard.UpcomingMentorsSession") }}
												{{-- @if($days!=14)
												<div onclick="redirectWithDays('14')" class="btn btn-label-success" id="forteenDays"> 14
													{{__('cycles.days')}}</div>
												@else
												<div onclick="redirectWithDays('14')" class="btn btn-label-warning" id="forteenDays"> 14
													{{__('cycles.days')}}</div>
												@endif
												@if($days!=7)
												<div onclick="redirectWithDays('7')" class="btn btn-label-success" id="sevenDays"> 7
													{{__('cycles.days')}}</div>
												@else
												<div onclick="redirectWithDays('7')" class="btn btn-label-warning" id="sevenDays"> 7
													{{__('cycles.days')}}</div>
												@endif --}}
											</h5>
										</div>
										<a class="btn btn-sm btn-primary" href="/dashboard/mentorship/mine">
											<i class="ti ti-eye me-2 ti-16px"></i>
											{{__('cycles.showAll')}}
										</a>
									</div>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table mb-0 sessonsTable">
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
														<div class="d-flex align-items-center gap-2">
															<a class="media-left" href="/dashboard/users/{{ $session->user_id }}/edit">
																<img src="{{ $session->user->image_url }}" alt="avatar" class="rounded-circle me-3"
																	height="40" width="40">
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
														<div style="cursor: pointer;" class="d-flex align-items-center"
															onclick="mentor({{ $session->mentor_id }}, '{{ $session->mentor->name }}', '{{ $session->mentor->ratings }}')">
															<div class="avatar">
																<img 
																	class="img-fluid rounded-circle me-3" 
																	src="{{$session->mentor->image_url ?? '/icons/account.svg'}}"
																	alt="image" height="20" width="20"
																/>
															</div>
															<span class="chip-text text-nowrap"> {{$session->mentor->name}} </span>
														</div>
													</td>
													@if ($_user->hasRole('Admin'))
													<td> <small> {!! $session->rate_comment ? str_repeat('<i class="bx bxs-star"
																style="color: #fc960f;"></i>', $session->rating) . '<br>' . $session->rate_comment :
															$session->comment !!} </small> </td>
													@endif
													<td> {{ $session->zoom_date }} <br> ({{ $session->period }}M) </td>
													<td>
														<div class="d-flex align-items-center gap-1"> {!! __( $session->approved ) ??'<span
																class="warning">'.__('Waiting').'</span>' !!} </div>
													</td>
													<td> {{ $session->done ?__('Yes') : __('No') }} </td>
													<td> 
														@php
															$now = date('Y-m-d H:i');
															$start_time = date('Y-m-d H:i', strtotime($session->zoom_date . '-15 minutes'));
															$end_time = date('Y-m-d H:i', strtotime($session->zoom_date . '+'. $session->period .' minutes'));
															@endphp
														@if ($session->approved == 'Yes')
															@if($now >= $start_time )
															@if($now <= $end_time)
															<span class="warning">
																<a class="btn btn-sm btn-label-primary" href="{{ $session->join_url }}" target="_blank">
																	<i class="ti ti-brand-zoom me-2"></i>
																	{{ __('dashboard.Join now') }}
																</a>
															</span>
															@else
																<span class="text-danger"> <i class="ti ti-alarm"></i> {{ $l == 'ar' ? 'انتهت الجلسة' : 'Session Completed' }} </span>
															@endif
															@else 
																<span class="text-warning"> <i class="ti ti-alarm"></i> {{ __('admin.Meeting_will_start_at_time') }} </span>
															@endif
														@elseif($session->approved == 'No')
															{{ __('admin.No') }}
														@else
															{!! '<span class="text-primary">' . __('admin.Waiting_for_approval') . '</span>' !!}
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
			@if( array_intersect(['dashboard.widgets_tables_mentors_view'], $permissions) && !is_null($cycle_id) )
			<div class="col-12">
				<div class="card mt-10">
					<section>
						<div class="row">
							<div class="col-md-12">
								<div class="card">
									<div class="card-header border-bottom mb-4">
										<h5 class="card-title align-items-center">
											<i class="ti ti-school ti-28px"></i>
											<span class="mx-2">{{ __("dashboard.Mentors") }}</span>
										</h5>
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
														@if( $_user->ableTo('mentors.settings_modify', $permissions) )<th>
															{{__('dashboard.Request')}}</th>@endif
													</tr>
												</thead>
												<tbody>
													@foreach($mentors->filter(function($mentor) use ($cycle_id) { return in_array($cycle_id,
													$mentor->mentor_cycles); })->values() as $key => $mentor)
													<tr>
														<td>{{$key+1}}</td>
														<td>
															<div class="d-flex align-items-center gap-1">
																<a class="media-left"> <img src="{{ $mentor->image_url }}" alt="avatar"
																		class="rounded-circle me-3" height="40" width="40"> </a>
																@if( $_user->ableTo('mentors.profile_view', $permissions) )<a target="_blank"
																	href="{{route('mentors.profile_view', $mentor->id)}}">@endif
																	<div class="media-body">
																		<h6 class="media-heading mb-0 text-nowrap">{{ $mentor->name ?? '-' }}</h6>
																		<span class="font-small-2 text-nowrap">{{ $mentor->title ?? '-' }}</span>
																	</div>
																	@if( $_user->ableTo('mentors.profile_view', $permissions) )
																</a>@endif
															</div>
														</td>
														<td> {{ $mentor->bio ?? '-' }} </td>
														<td> {{ $mentor->specialization ?? '-' }} </td>
														<td>
															@if( $mentor->linkedin ?? 0 )
															<a title="Linkedin URL" target="_blank" href="{{ $mentor->linkedin }}"
																class="btn btn-icon rounded-circle btn-label-primary"> <i
																	class="fa-brands fa-linkedin-in"></i></a>
															@endif
														</td>
														@if( $_user->ableTo('mentors.settings_modify', $permissions) )
														<td>
															<button
																onclick="request({{ $mentor->id }},'{{ $mentor->name }}', {{ $mentor->organization_id }}, {{ $mentor->cycle_id }})"
																title="Add new request" type="button"
																class="btn btn-icon rounded-circle btn-label-success">
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

			@if( array_intersect(['dashboard.widgets_tables_my_current_schedule_view'], $permissions) )
			<div class="col-12">
				<div class="card">
					<section>
						<div class="row">
							<div class="col-md-12">
								<div class="card">
									<div class="card-header">
										<div class="d-flex justify-content-between align-items-center">
											<h5 class="card-title mb-0">
												{{ __("mentor.my_availability_slots") }}
											</h5>

											@if ($_user->ableTo('slots.index', $permissions) )
												<a href="{{ route('slots.index') }}" class="btn btn-primary">
													<i class="ti ti-calendar-cog me-2"></i>
													{{ __("mentor.manage_availability") }}
												</a>
											@endif
										</div>
									</div>
									<div class="card-datatable table-responsive">
										<table class="datatables-basic table slots-table">
											<thead class="table-light">
												<th> {{ __('mentor.days_date') }} </th>
												<th> {{ __('dashboard.Weekly') }} </th>
												<th> {{ __('mentor.start_time') }} </th>
												<th> {{ __('mentor.end_time') }} </th>
												<th> {{ __('mentor.session_type') }} </th>
												<th> {{ __('dashboard.Actions') }} </th>
											</thead>
											<tbody>
												@foreach( $my_schedules as $key => $schedule )
												<tr>
													<td> {{ $schedule['weekly'] ? $schedule['weekday'] : $schedule['date'] }} </td>
													<td> 
														<span class="badge bg-label-{{ $schedule['weekly'] ? 'success' : 'warning' }}">
															{{ $schedule['weekly'] ? __('Yes') : __('No') }}
														</span>
													</td>
													<td> {{ $schedule['start_time'] }} </td>
													<td> {{ $schedule['end_time'] }} </td>
													<td>
														<span class="badge bg-label-{{ $schedule['session_type'] == 'Online' ? 'info' : ($schedule['session_type'] == 'Offline' ? 'secondary' : 'primary') }}">
															{{ $schedule['session_type'] == 'Online' ? __('mentor.online') : ($schedule['session_type'] == 'Offline' ? __('mentor.offline') : __('mentor.both')) }}
														</span>
													</td>
													<td>
														<button 
															type="button"
															class="btn btn-sm btn-label-danger delete-slot"
															data-value="{{ $schedule['id'] ?? 0 }}"
														>
															<i class="ti ti-trash ti-16px"></i>
														</button>
													</td>
												</tr>
												@endforeach
												@if( $my_schedules->count() == 0 )
												<tr>
													<td class="text-center" colspan="6">{{ __('cycles.No data available in table') }}</td>
												</tr>
												@endif
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
			@endif

			@if( array_intersect(['dashboard.widgets_tables.my_next_mentorship_sessions_view'], $permissions) )
			<div class="col-12">
				<div class="card mt-10">
					<section>
						<div class="row">
							<div class="col-md-12">
								<div class="card">
									<div class="card-header">
										<h5 class="card-title mb-0 text-capitalize">
											{{ __("dashboard.My next mentorship sessions") }}
										</h5>
									</div>
									<div class="card-datatable table-responsive">
										<table class="table">
											<thead class="table-light">
												<th> {{ '#' }} </th>
												<th> {{ __('dashboard.Company') }} </th>
												<th> {{ __('dashboard.User') }} </th>
												<th> {{ __('dashboard.Date') }} </th>
												<th> {{ __('dashboard.Admin Notes') }} </th>
												<th> {{ __('dashboard.Meeting Link') }} </th>
											</thead>
											<tbody>
												@foreach( $upcoming_sessions as $key => $session )
												<tr>
													<td> {{ $loop->iteration }} </td>
													<td>
														@if( $_user->ableTo('company.profile_view', $permissions) )<a class="text-dark"
															href="/dashboard/companyProfile/{{ $session->user->company->id }}">@endif
															<img src="{{ $session->user->company->logo_url }}" alt="avatar" class="rounded-circle"
																height="40" width="40">
															<span class="fw-medium"> {{ $session->user->company->name }}</span>
															@if( $_user->ableTo('company.profile_view', $permissions) )</a>@endif
													</td>
													<td>
														@if( $_user->ableTo('users.edit', $permissions) )<a
															class="text-dark d-flex align-items-center gap-2"
															href="/dashboard/users/{{ $session->user->id }}/edit">@endif
															<img src="{{ $session->user->image_url }}" alt="avatar" class="rounded-circle"
																height="35" width="35" style="border: 2px solid #ddd;">
															<div class="media-body">
																<h6 class="media-heading mb-0" style="font-size: 14px;">{{ $session->user->name }}
																</h6>
																<span class="font-small-2" style="font-size:11px;">{{ $session->user->title }}</span>
															</div>
															@if( $_user->ableTo('users.edit', $permissions) )
														</a>@endif
													</td>
													<td> {{ FormatDate($session->zoom_date, 'Y-m-d H:i A') }} ({{ $session->period }}M) </td>
													<td> {{ $session->admin_comment ?? '...' }} </td>
													<td>
														@php
															$now = date('Y-m-d H:i');
															$start_time = date('Y-m-d H:i', strtotime($session->zoom_date . '-15 minutes'));
															$end_time = date('Y-m-d H:i', strtotime($session->zoom_date . '+' . $session->period .' minutes'));
														@endphp
														
														@if( $now >= $start_time )
															@if( $now <= $end_time )
															<a href="{{ $session->start_url }}" target="_blank">
																<button title="Start Meeting" type="button" data-value="{{ $session->id }}" type="button" class="btn btn-label-primary rounded">
																	<i class="ti ti-link"></i> {{ __('admin.Join Meeting') }}
																</button>
															</a>
															@if( !empty( $session->passcode ) )
																 | <button title="Show Passcode" type="button" data-passcode="{{ $session->passcode }}" type="button" class="btn btn-label-warning show-passcode rounded">
																	<i class="ti ti-key"></i>
																</button>
															@endif
															@else
																<span class="text-danger"> <i class="ti ti-alarm"></i> {{ __('admin.Meeting_Expired') }} </span>
															@endif
														@else
															<i class="ti ti-alarm"></i> {{ __('admin.Meeting_will_start_at_time') }}
														@endif
													</td>
												</tr>
												@endforeach
												@if( $upcoming_sessions->count() == 0 )
												<tr>
													<td class="text-center" colspan="6">{{ __('cycles.No data available in table') }}</td>
												</tr>
												@endif
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
			@endif

			@if( array_intersect(['dashboard.widgets_tables_my_judging_list_view'], $permissions) )
			<div class="col-12">
				<div class="card mt-10">
					<section>
						<div class="row">
							<div class="col-md-12">
								<div class="card">
									<div class="card-header border-bottom mb-4">
										<h5 class="card-title align-items-center">
											<i class="ti ti-calendar-stats ti-24px"></i>
											<span class="mx-2">{{ __('dashboard.Your Judging List') }}</span>
										</h5>
									</div>
									<div class="card-body">
										@if( $initial_screening_steps->count() > 0)
										@foreach( $initial_screening_steps as $step )
										<div class="mt-6">
											<div class="card-header bg-label-light p-3 mb-2">
												<h5 class="mb-0">
													<i class="ti ti-bell ti-24px"></i>
													<span class="mx-2">
													  {{ $step->title }} - ({{ $step->cycle->title }})
													  - {{ __('dashboard.start from') }} {{ $step->from }}
													  {{ __('dashboard.to') }} {{ $step->to }}
													</span>
												  </h5>
											</div>
											<div class="card-body">
												<p class="card-text">{{ $step->description }}</p>
												<small class="text-muted">
													{{ __('dashboard.This Cycle Start from') }}
													{{ $step->cycle->start }}
													{{ __('dashboard.to') }}
													{{ $step->cycle->end }}
												</small>
											</div>
											<div class="table-responsive">
												<table class="table">
													<thead>
														<tr>
														  <th>{{ __('dashboard.Company') }}</th>
														  <th>{{ __('dashboard.User/Title') }}</th>
														  <th>{{ __('dashboard.Screening completed') }}</th>
														  {{--  <th>{{ __('dashboard.AVG degree') }}</th>  --}}
														</tr>
													  </thead>
													  <tbody>
														@foreach( $step->companies as $company )
														<tr>
															<td>
																<div class="d-flex align-items-center">
																	<img src="{{ $company->logo }}" alt="avatar" class="rounded-circle me-2" height="40" width="40">
																	@if( $_user->ableTo('screening.show', $permissions) )<a href="/dashboard/companyProfile/{{ $company->id }}" class="text-decoration-none"> @endif
																	{{ $company->name }}
																	@if( $_user->ableTo('screening.show', $permissions) ) </a> @endif
																</div>
															</td>
															<td>
																<div class="d-flex align-items-center">
																@if( $company->users->first() )
																	<img src="{{ $company->users->first()->logo }}" alt="avatar" class="rounded-circle me-2" height="40" width="40">
																	<div>
																		<h6 class="mb-0">{{ $company->users->first()->name }}</h6>
																		<small class="text-muted">{{ $company->users->first()->title }}</small>
																	</div>
																@else
																-
																@endif
																</div>
															</td>
															<td>
																@if( $company->evaluation_status == 'not_started' )
																<span class="badge bg-label-warning">
																	<i class="ti ti-clock me-2"></i> {{ __('dashboard.evaluation_not_started') }}
																</span>
																@elseif( $company->evaluation_status == 'in_progress' )
																<span class="badge bg-label-info">
																	<i class="ti ti-clock-bolt me-2"></i> {{ __('dashboard.evaluation_in_progress') }}
																</span>
																@elseif( $company->evaluation_status == 'evaluated' )
																<span class="badge bg-label-success">
																	<i class="ti ti-circle-check me-2"></i> {{ __('dashboard.evaluation_completed') }}
																</span>
																@elseif( $company->evaluation_status == 'not_evaluated' )
																<span class="badge bg-label-danger">
																	<i class="ti ti-exclamation-circle me-2"></i> {{ __('dashboard.evaluation_not_evaluated') }}
																</span>
																@endif
															</td>
														</tr>
														@endforeach
													  </tbody>
												</table>
											</div>
										</div>
										@endforeach
										@else
										<div class="d-flex flex-column justify-content-center align-items-center p-5">
											<img src="/icons/not-found.svg" width="130" height="130" alt="No screening started yet">
											<h6 class="text-center text-secondary mt-3">{{ __('dashboard.No screening started yet') }}</h6>
										</div>
										@endif
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
			@endif

			@if( array_intersect(['dashboard.widgets_tables_program_final_screening_view'], $permissions) )
			<div class="col-12">
				<div class="card mt-10">
					<section>
						<div class="row">
							<div class="col-md-12">
								<div class="card">
									<div class="card-header border-bottom mb-4">
										<h5 class="card-title align-items-center">
											<i class="ti ti-calendar-stats ti-24px"></i>
											<span class="mx-2">{{ __('dashboard.Cycle final screening') }}</span>
										</h5>
									</div>
									<div class="card-body">
										@foreach( $final_screening_steps as $step )
										<div class="card mt-4">
											<div class="card-header bg-label-light p-3 mb-4">
												<h5 class="mb-0">
													<i class="ti ti-bell ti-24px"></i>
													<span class="mx-2">
														{{ $step->title }} - ({{ $step->cycle->title }}) - {{ __('dashboard.start from') }} {{ $step->start ?? '-' }} {{ __('dashboard.to') }} {{ $step->end ?? '-' }}
													</span>
												</h5>
											</div>
											<div class="card-body">
												<div class="mb-4">
													<p class="card-text">{{ $step->description }}</p>
													<small class="text-muted">{{ __('dashboard.This Cycle Start from') }}
														{{ $step->cycle->start ?? '-' }} {{ __('dashboard.to') }}
														{{ $step->cycle->end ?? '-' }}
													</small>
												</div>
												<div class="table-responsive">
													<table class="table">
														<thead>
															<tr>
															<th>{{ __('dashboard.Company') }}</th>
															<th>{{ __('dashboard.User/Title') }}</th>
															<th>{{ __('dashboard.Screening completed') }}</th>
															<th>{{ __('dashboard.Meeting') }}</th>
															<th>{{ __('dashboard.Actions') }}</th>
															</tr>
														</thead>
														<tbody>
															@foreach( $step->companies as $company )
																<tr>
																	<td>
																		<div class="d-flex align-items-center">
																			<img src="{{ $company->logo }}" alt="avatar" class="rounded-circle me-2" height="40" width="40">
																			@if( $_user->ableTo('screening.show', $permissions) )<a href="/dashboard/companyProfile/{{ $company->id }}" class="text-decoration-none"> @endif
																			{{ $company->name }}
																			@if( $_user->ableTo('screening.show', $permissions) ) </a> @endif
																		</div>
																	</td>
																	<td>
																		<div class="d-flex align-items-center">
																			@if( $company->users->count() )
																				@foreach( $company->users as $company_user )
																					<img src="{{ $company_user->logo }}" alt="avatar" class="rounded-circle me-2" height="40" width="40">
																					<div>
																						<h6 class="mb-0">{{ $company_user->name }}</h6>
																						<small class="text-muted">{{ $company_user->title }}</small>
																					</div>
																				@endforeach
																			@else
																			-
																			@endif
																		</div>
																	</td>
																	<td>
																		@if( $company->evaluation_status == 'not_started' )
																		<span class="badge bg-label-warning">
																			<i class="ti ti-clock me-2"></i> {{ __('dashboard.evaluation_not_started') }}
																		</span>
																		@elseif( $company->evaluation_status == 'in_progress' )
																		<span class="badge bg-label-info">
																			<i class="ti ti-clock-bolt me-2"></i> {{ __('dashboard.evaluation_in_progress') }}
																		</span>
																		@elseif( $company->evaluation_status == 'evaluated' )
																		<span class="badge bg-label-success">
																			<i class="ti ti-circle-check me-2"></i> {{ __('dashboard.evaluation_completed') }}
																		</span>
																		@elseif( $company->evaluation_status == 'not_evaluated' )
																		<span class="badge bg-label-danger">
																			<i class="ti ti-exclamation-circle me-2"></i> {{ __('dashboard.evaluation_not_evaluated') }}
																		</span>
																		@endif
																	</td>
																	<td> {{ $company->datetime ?? __('dashboard.Not added yet') }} </td>
																	<td>
																		@if( $company->join_url )
																			<a href="{{ $company->join_url }}" target="_blank" class="btn btn-primary btn-sm">
																				<i class="ti ti-user"></i> {{ __('dashboard.Join') }}
																			</a>
																			@if( !empty( $company->passcode ) )
																				 | <button title="Show Passcode" type="button" data-passcode="{{ $company->passcode }}" type="button" class="btn btn-label-warning show-passcode rounded">
																					<i class="ti ti-key"></i>
																				</button>
																			@endif
																			{{--  @if( $_user->ableTo('screening.form_view', $permissions) )
																			<a href="/dashboard/final/{{ $step->id }}/{{ $company->id }}" target="_blank" class="btn btn-success btn-sm">
																				<i class="ti ti-file"></i> {{ __('dashboard.Open form') }}
																			</a>
																			@endif  --}}
																		@else
																			{{ __('dashboard.Not started') }}
																		@endif
																	</td>
																</tr>
															@endforeach
														</tbody>
													</table>
												</div>
											</div>
										</div>
										@endforeach
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
			@endif
		</div>
		
		<div class="row mt-6">
			@if( $_user->ableTo('mentorship.sessions_Rate', $permissions) && $notrated_mens)
			<div class="col-12">
				<div class="card shadow-sm">
					<div class="card-header py-3 d-flex justify-content-between align-items-center">
						<h5 class="card-title mb-0">
							{{__('dashboard.Rate mentorship session with')}}:
							<span class="fw-bold">{{$notrated_men->{'first_name_'.$l} ?? '' }} {{$notrated_men->{'last_name_'.$l} ?? ''
								}}</span>
						</h5>
					</div>
					<div class="card-body p-4">
						<div class="table-responsive">
							<table id="ratingsTable" class="table" style="width:100%">
								<thead class="table-light">
									<tr>
										<th class="fw-semibold">{{__('admin.Mentor')}}</th>
										<th class="fw-semibold">{{__('dashboard.Your session was in')}}</th>
										<th class="fw-semibold">{{__('dashboard.Did you attend this session?')}}</th>
										<th class="fw-semibold">{{ __('dashboard.Rate from 1 star "bad" to 5 stars "best"') }}</th>
										<th class="fw-semibold">{{ __('dashboard.Your opinion important kindly write any notes about this session') }}</th>
										<th class="fw-semibold text-center">{{__('dashboard.Submit')}}</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($notrated_mens as $notrated_men)
									<tr class="border-bottom">
										<form method="post" action="/dashboard/mentorship/rate" enctype="multipart/form-data" autocomplete="off"
											class="row g-3">
											@csrf
											<input type="hidden" name="id" value="{{$notrated_men->mr_id}}">
											<input type="hidden" name="rate" value="0" id="rate">
											<td class="align-middle">
												<div class="d-flex align-items-center">
													<div
														class="avatar avatar-sm me-2 bg-primary rounded-circle d-flex align-items-center justify-content-center">
														<span class="text-white fw-bold">{{substr($notrated_men->{'MentorName_'.$l}, 0, 1)}}</span>
													</div>
													<div>
														<p class="mb-0 fw-semibold">{{$notrated_men->{'MentorName_'.$l} }}</p>
														<small class="text-muted">ID: {{ $notrated_men->mr_id }}</small>
													</div>
												</div>
											</td>
											<td class="align-middle">
												<div class="d-flex align-items-center">
													<i class="bx bx-time-five me-2 text-muted"></i>
													<span>{{$notrated_men->zoom_date}}</span>
												</div>
											</td>
											<td class="align-middle">
												<div class="form-check form-check-inline">
													<input class="form-check-input" type="radio" name="done" id="doneyes-{{$notrated_men->mr_id}}"
														checked="">
													<label class="form-check-label" for="doneyes-{{$notrated_men->mr_id}}">{{__('Yes')}}</label>
												</div>
												<div class="form-check form-check-inline">
													<input class="form-check-input" type="radio" name="done" id="doneno-{{$notrated_men->mr_id}}">
													<label class="form-check-label" for="doneno-{{$notrated_men->mr_id}}">{{__('No')}}</label>
												</div>
											</td>
											<td class="align-middle">
												<div class="full-star-ratings" data-rateyo-full-star="true" data-rateyo-rating="0"
													data-rateyo-num-stars="5" data-rateyo-star-width="24px"></div>
											</td>
											<td class="align-middle">
												<textarea class="form-control form-control-sm border-1" id="comment-{{$notrated_men->mr_id}}"
													name="comment" rows="3" required
													placeholder="{{ __('dashboard.Write your notes here') }}..."></textarea>
											</td>
											<td class="align-middle text-center">
												<button type="submit" class="btn btn-primary btn-sm px-3">
													<i class="bx bx-check me-1"></i>
													<span>{{__('dashboard.Submit')}}</span>
												</button>
											</td>
										</form>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			@endif

			@if( $_user->ableTo('services.rate', $permissions) && $unrated_services_requests->count() > 0 )
			<div class="col-12 my-10">
				<div class="card shadow-sm">
					<div class="card-header py-3 d-flex justify-content-between align-items-center">
						<h5 class="card-title mb-0">
							{{ __('dashboard.Rate') . ' ' . __('dashboard.service') }} 
						</h5>
					</div>
					<div class="card-body p-4">
						<div class="accordion stick-top accordion-custom-button" id="rateServicesAccordion">
							@foreach($unrated_services_requests as $index => $service_request)
								<div class="accordion-item">
									<h2 class="accordion-header">
										<button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
											{{ $service_request->service->name }} By {{ $service_request->provider->name }}
										</button>
									</h2>
									<div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#rateServicesAccordion">
										<div class="accordion-body pt-4">
											<form method="POST" action="/dashboard/directory/{{ $service_request->id }}/rate" enctype="multipart/form-data" autocomplete="off">
												@csrf
												<div class="form-group mb-4 row">
													<div class="col-9">
														{{ __('dashboard.Your request was in') }}
													</div>
													<div class="col-3 text-end">
														<strong> {{ $service_request->date_time }} </strong>
													</div>
												</div>

												<fieldset class="form-group mb-4">
													<label class="form-label" for="is_service_done_{{ $service_request->id }}"> {{ __('dashboard.Did you get this service?') }} </label>
													<select class="select2 form-control" required id="is_service_done_{{ $service_request->id }}" name="is_service_done_{{ $service_request->id }}">
														<option value="Yes">{{ __('admin.Yes') }}</option>
														<option value="No">{{ __('admin.No') }}</option>
													</select>
												</fieldset>

												<fieldset class="form-group mb-4">
													<label class="form-label" for="service_rate_{{ $service_request->id }}"> {{ __('dashboard.Rate from 1 star "bad" to 5 stars "best"') }} </label>
													<input type="hidden" name="service_rate_{{ $service_request->id }}" value="0" id="service_rate_{{ $service_request->id }}">
													<div class="full-services-star-ratings" data-element="{{ $service_request->id }}" data-rateyo-full-star="true"></div>
												</fieldset>

												<fieldset class="form-group mb-4">
													<label class="form-label" for="service_comment_{{ $service_request->id }}"> {{ __('dashboard.Your opinion important kindly write any notes about this service') }} </label>
													<textarea class="form-control" id="service_comment_{{ $service_request->id }}" name='service_comment_{{ $service_request->id }}' rows="4" required placeholder="{{__('dashboard.Write your notes here')}}" ></textarea>
												</fieldset>

												<div class="row mt-4">
													<div class="col-12 text-end">
														<button type="submit" class="btn btn-primary ml-1"> {{ __('dashboard.Submit') }} </button>
													</div>
												</div>
											</form>
										</div>
									</div>
								</div>
							@endforeach
						</div>
					</div>
				</div>
			</div>
			@endif
		</div>
	</div>
</div>
<!-- / Content Wrapper -->

@if( $_user->ableTo('mentors.settings_modify', $permissions) &&
array_intersect(['dashboard.widgets_tables_mentors_view'], $permissions) && !is_null( $cycle_id ) )
<!--  Edit SP Modal -->
@include('admin.clients.startup.partials._session_modal')
<!--/ Edit SP Modal -->

<!-- Respond Invitation Modal -->
@if($team_member->count() > 0)
<div
	class="modal fade"
	id="teamInvitationModal"
	tabindex="-1"
	aria-labelledby="teamInvitationLabel"
	aria-modal="true"
	role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content overflow-hidden position-relative">

			<!-- Optional Background Gradient Overlay -->
			<div
				style="position: absolute; inset: 0; background: linear-gradient(135deg, #f5f7fa, #c3cfe2); opacity: 0.3;"
			></div>

			<div class="modal-body position-relative text-center py-12 px-12">
				@foreach($team_member as $join)
				<!-- Team & Inviter Avatar -->
				<div class="position-relative d-inline-block mb-4">
					<img
						src="{{ $join->tf_img_team ? asset($join->tf_img_team) : 'https://placehold.co/400x200?text='.$join->tf_name }}"
						alt="Team Avatar"
						class="rounded-circle border-4 shadow"
						style="width: 64px; height: 64px; object-fit: cover;"
					/>
				</div>
				
				<!-- Title -->
				<h5 id="teamInvitationLabel" class="fw-bold mb-2">{{ $join->tf_name }}</h5>

				<!-- Description -->
				<p class="mb-2">
					<strong>{{ $join->{'user_name_' . $l} }}</strong> {{ $l == 'en' ? 'invited you to join their team' : 'تم دعوتك للانضمام لفريقه' }}
				</p>
				<p class="fst-italic text-muted small">
					{{ $l == 'en' ? 'Joining this team will allow you to collaborate on projects, share resources, and participate in discussions.' : 'الانضمام للفريق سوف تسمح لك بالتعاون في المشاريع، والمشاركة في الموارد، والتشارك في النقاشات.' }}
				</p>

				<!-- Buttons -->
				<div class="d-flex flex-column flex-sm-row gap-2">

					<form action="{{ route('hackathon.acceptInvitation', $join->tm_id) }}" class="d-inline-block" method="post">
						@csrf
						@method('PATCH')
						<input type="hidden" name="cycle_id" value="{{ $join->tf_cycle_id }}">
						<button class="btn btn-success btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;">
							<i class="ti ti-users me-2 me-2"></i>
							{{ $l == 'en' ? 'Accept Invitation' : ' قبول الدعوة ' }}
						</button>
					</form>
					<form action="{{ route('hackathon.rejectInvitation', $join->tm_id) }}" class="d-inline-block" method="post">
						@csrf
						@method('PATCH')
						<input type="hidden" name="cycle_id" value="{{ $join->tf_cycle_id }}">
						<button class="btn btn-danger btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;">
							<i class="ti ti-users me-2 me-2"></i>
							{{ $l == 'en' ? 'Reject Invitation' : ' رفض الدعوة ' }}
						</button>
                	</form>

				</div>
				@endforeach
			</div>
		</div>
	</div>
</div>
@endif
<!--/ Respond Invitation Modal -->
@endif

@include('mentor.partials._availability_slots')

<!-- Program Prerequisites Modal -->
<div class="modal fade" id="prerequisitesModal" tabindex="-1" aria-labelledby="prerequisitesModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<div class="d-flex align-items-center">
					<i class="ti ti-alert-triangle me-2"></i>
					<h5 class="modal-title" id="prerequisitesModalLabel">
						{{ __('dashboard.Prerequisites_Required') }}
					</h5>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>{{ __('dashboard.To join this program, you must complete the following prerequisites first:') }}</p>
				<ul id="prerequisitesList" class="list-group list-group-flush">
					<!-- Prerequisites will be populated here -->
				</ul>
				<div class="alert alert-warning mt-3">
					<i class="ti ti-alert-triangle me-2"></i>
					{{ __('dashboard.Completion of all prerequisites is required before you can join the program.') }}
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
					<i class="ti ti-x me-2"></i>
					{{ __('admin.Cancel') }}
				</button>
				<a class="btn btn-primary" id="completePrerequisitesBtn">
					<i class="ti ti-external-link me-2"></i>
					{{ __('dashboard.Complete Prerequisites & Join') }}
				</a>
			</div>
		</div>
	</div>
</div>
<!-- / Program Prerequisites Modal -->
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
	$('#teamInvitationModal').modal('show');

	$('#acceptInvitationBtn').on('click', function() {
		var btn = $(this);
		btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Accepting...');
		// Simulate AJAX request
		setTimeout(function() {
			btn.prop('disabled', false).html('<i class="ti ti-circle-check"></i> Accept Invitation');
			$('#teamInvitationModal').modal('hide');
			$('body').prepend(`
				<div class="alert alert-success alert-dismissible fade show" role="alert" style="position:fixed;top:20px;right:20px;z-index:9999;">
					<i class="ti ti-check-circle me-2"></i>
					Invitation accepted! You have joined the team.
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			`);
		}, 1200);
	});

	$('#declineInvitationBtn').on('click', function() {
		var btn = $(this);
		btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Declining...');
		// Simulate AJAX request
		setTimeout(function() {
			btn.prop('disabled', false).html('<i class="ti ti-circle-x"></i> Decline');
			$('#teamInvitationModal').modal('hide');
			$('body').prepend(`
				<div class="alert alert-warning alert-dismissible fade show" role="alert" style="position:fixed;top:20px;right:20px;z-index:9999;">
					<i class="ti ti-info-circle me-2"></i>
					Invitation declined. The team organizer has been notified.
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			`);
		}, 1200);
	});
});
</script>

<script src="/vuexy/assets/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="/vuexy/assets/vendor/libs/chartjs/chartjs.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
<script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
<script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
<script src="/app-assets/plugins/alertify/alertify.min.js"></script>
@stop
@section('pagescripts')
@if( array_intersect(['dashboard.widgets_tables_my_current_schedule_view'], $permissions) )
	<script src="/vuexy/assets/js/mentor-schedule.js"></script>
@endif
<script>
	let completionCheckInterval = null;

	// -------------------------------------------------------
	// MAIN JOIN BUTTON HANDLER
	// -------------------------------------------------------
	window.handleJoinClick = function(button) {
		const hasPreRequests = button.getAttribute('data-has-pre');
		const preRequests = JSON.parse(button.getAttribute('data-pre-requests') || '[]');

		if (hasPreRequests === 'true') {
			populatePrerequisiteModal(preRequests);

			// Start polling ONLY while modal is open
			startCompletionStatusCheck();

			$('#prerequisitesModal').on('hidden.bs.modal', stopCompletionStatusCheck);
			$('#prerequisitesModal').modal('show');
		} else {
			window.location.href = button.getAttribute('data-join-url');
		}
	};

	// -------------------------------------------------------
	// POPULATE MODAL ITEMS
	// -------------------------------------------------------
	function populatePrerequisiteModal(preRequests) {
		console.log('Populating prerequisites modal with:', preRequests);
    const list = document.getElementById('prerequisitesList');
    list.innerHTML = '';

    preRequests.forEach(pr => {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex align-items-center';
        li.innerHTML = `
            <i class="ti ti-circle-check me-2 text-warning"></i>
            <a href="${pr.url}?action=complete-profile" 
               target="_blank" 
               class="text-decoration-none flex-grow-1">
               ${pr.label}
            </a>`;
        list.appendChild(li);
    });

    // ----------------------------------------
    // Attach modal action button link
    // ----------------------------------------
    const modalBtn = document.getElementById('completePrerequisitesBtn');

    if (preRequests.length > 0) {
			const firstUrl = `${preRequests[0].url}?action=complete-profile`;

			modalBtn.setAttribute('href', firstUrl);
			
			// Save state so page refreshes when returning
			modalBtn.onclick = function () {
				localStorage.setItem('prerequisitesOpened', 'true');
			};
    }
	}


	// -------------------------------------------------------
	// SINGLE CHECKER FUNCTION
	// -------------------------------------------------------
	function checkCompletionStatus() {
		return fetch('{{ route('cycles.completion_status_check') }}', {
			method: 'GET',
			headers: {
				'X-Requested-With': 'XMLHttpRequest',
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
			}
		})
		.then(res => res.json())
		.then(data => {
			updateUIWithCompletionStatus(data);

			// Auto-stop polling when prerequisites are completed
			if (!data.has_pre_requests) {
				stopCompletionStatusCheck();
				$('#prerequisitesModal').modal('hide');
			}
		})
		.catch(err => console.error("Error:", err));
	}

	// -------------------------------------------------------
	// UPDATE UI BASED ON COMPLETION STATUS
	// -------------------------------------------------------

	function updateUIWithCompletionStatus(data) {
    const hasPreRequests = data.has_pre_requests;
    const preRequests = data.pre_requests || [];

    // Update Join buttons
    const joinButtons = document.querySelectorAll('button[onclick*="handleJoinClick"]');
    joinButtons.forEach(joinButton => {
        joinButton.className = 'btn btn-primary';
        joinButton.innerHTML = '<span class="me-2">{{ __("dashboard.Join now") }}</span><i class="ti ti-arrows-join"></i>';

        joinButton.setAttribute('data-has-pre', hasPreRequests ? 'true' : 'false');
        joinButton.setAttribute('data-pre-requests', JSON.stringify(preRequests.map(pr => ({
            url: `${pr.url}?action=complete-profile`,
            label: pr.label,
        }))));
    });

    // Update alert boxes
    const alertDivs = document.querySelectorAll('.alert.alert-info');
    alertDivs.forEach(alertDiv => {
        if (hasPreRequests) {
            const listElement = alertDiv.querySelector('.list-unstyled');
            if (listElement) {
                listElement.innerHTML = '';
                preRequests.forEach(pr => {
                    const li = document.createElement('li');
                    li.className = 'pre-request-item';
                    li.innerHTML = `
                        <a href="${pr.url}?action=complete-profile"
                           class="btn btn-sm btn-outline-info d-flex align-items-center gap-2">
                           <i class="ti ti-external-link"></i>
                           <span>${pr.label}</span>
                        </a>`;
                    listElement.appendChild(li);
                });
            }
            alertDiv.style.display = 'block';
        } else {
            alertDiv.style.display = 'none';
        }
    });
	}

	// -------------------------------------------------------
	// START POLLING — ONLY WHEN NEEDED
	// -------------------------------------------------------
	function startCompletionStatusCheck() {
		if (completionCheckInterval) return; // Prevent duplicates

		completionCheckInterval = setInterval(checkCompletionStatus, 5000);
	}

	// -------------------------------------------------------
	// STOP POLLING
	// -------------------------------------------------------
	function stopCompletionStatusCheck() {
		if (completionCheckInterval) {
			clearInterval(completionCheckInterval);
			completionCheckInterval = null;
		}
	}

	// -------------------------------------------------------
	// REFRESH WHEN USER RETURNS TO PAGE
	// -------------------------------------------------------
	window.addEventListener('focus', function() {
		if (localStorage.getItem('prerequisitesOpened') === 'true') {
			localStorage.removeItem('prerequisitesOpened');
			window.location.reload();
		}
	});

</script>

	<script>
		// Initialize DataTable with pagination configuration
		$(document).on('click', '.show-passcode', function(e) {
		e.preventDefault();
		let passcode = $(this).data('passcode');
		alertify.defaults.transition = 'zoom';
		alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
		alertify.defaults.theme.cancel = 'btn btn-sm btn-info';
		alertify.defaults.theme.input = 'form-control';

		alertify.alert('Meeting Passcode', `The passcode for this meeting is: <strong>${passcode}</strong>`, function() {});
		});

		$(document).ready(function() {
			$("#ratingsTable").DataTable({
				"pageLength": 5,
				"lengthMenu": [5, 10, 25, 50],
				"searching": true,
				"ordering": true,
				"info": true,
				"responsive": true,
				@if( $l == 'ar' )
				"language": {
					"sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
					"sLoadingRecords": "جارٍ التحميل...", 
					"sProcessing": "جارٍ التحميل...",
					"sLengthMenu": "أظهر _MENU_ مدخلات", 
					"sZeroRecords": "لم يعثر على أية سجلات",
					"sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
					"sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل", 
					"sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
					"sInfoPostFix": "", 
					"sSearch": "ابحث:", 
					"sUrl": "",
					"oPaginate": {"sFirst": "الأول", "sPrevious": "السابق", "sNext": "التالي", "sLast": "الأخير"},
					"oAria": {"sSortAscending": ": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}
				}
				@endif
			});
		});
	</script>
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

			var fullServiceStar = $('.full-services-star-ratings');
			fullServiceStar.each(function (index, oneInstance) {
				var oneServiceInstance = $(oneInstance).rateYo({rtl: isRtl});
				if( oneServiceInstance.length )
				{
					oneServiceInstance.rateYo({rtl: isRtl}).on('rateyo.set', function (e, data) { $(oneInstance).parent().find('input').val(data.rating); });
				}
			})
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

		$('.delete-slot').click(function() {
		  const slotId = $(this).data('value');
		  const buttonElement = $(this);
			const rowElement = buttonElement.closest('tr');

			console.log('Deleting slot ID:', slotId);
			console.log('rowElement', rowElement);

		  Swal.fire({
				title: "{{__('mentor.delete_slot_confirm')}}",
				text: "{{__('admin.Recover')}}",
				showCancelButton: true,
				confirmButtonText: "{{ __('admin.DYes') }}",
				cancelButtonText: "{{ __('admin.No') }}",
				reverseButtons: true,
				customClass: {
					confirmButton: 'btn btn-danger me-2',
					cancelButton: 'btn btn-label-secondary',
				},
				buttonsStyling: false,
		  }).then((result) => {
				if (result.isConfirmed) {
					// Show loading state
					buttonElement.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i>');
					
					$.ajax({
						type: "DELETE",
						url: `/dashboard/availability/slots/${slotId}`,
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						success: function(response) {
							// console.log('response', response);
							buttonElement.prop('disabled', false).html('<i class="ti ti-trash ti-16px"></i>');
							rowElement.remove(); // Remove the row from the table immediately
							
							releaseSuccessAlert({
								title: response?.title || "{{ __('admin.Deleted') }}!",
								message:response?.message || "{{ __('admin.DeletedSuccessfully') }}.",
							});
						},
						error: function(jqXHR, statusText, error) {
							buttonElement.prop('disabled', false).html('<i class="ti ti-trash ti-16px"></i>');

							releaseFailureAlert({
								title: jqXHR.responseJSON?.title || "{{ __('admin.Error') }}!",
								message: jqXHR.responseJSON?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
							});
						}
					});
				}
		  });
		});
	</script>
@stop
