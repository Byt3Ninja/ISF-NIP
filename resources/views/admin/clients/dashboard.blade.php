@extends('layouts.admin')

@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.My Dashboard'))

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
@stop

@section('content')
  @if (!Auth::user()->first_name_ar)
    {!! redirect()->route('my_profile')->with(['msg' => 'FailedStore', 'class' => 'danger']) !!}
  @endif

  @php
    $s_latest = \App\Service::orderBy('created_at', 'DESC')->take(5)->get();
    $e_latest = \App\Calendar::where('start', '>=', now()->format('Y-m-d'))
        ->where(function ($q) {
            $q->orWhere('user_id', Auth::id())->orWhereNull('isPrivate');
        })
        ->orderBy('start')
        ->take(5)
        ->get();
    $s_count = \App\Service::count();
    $s_r_count = \App\ServicesRequests::where('user_id', Auth::id())->count();
    $nm_count = \App\Messenger::where('receiver', Auth::id())->whereNull('read')->count();
    $mentors = \App\CyclesMentors::where('cycle_id', Auth::user()->company->cycle ?? 0)->get();
    $m_count = count($mentors);
    /// Next session
    $nextsession = \App\MentorshipRequest::where('user_id', Auth::id())
        ->where('approved', 'Yes')
        ->where('zoom_date', '>=', date('Y-m-d 00:00:00'))
        ->orderBy('zoom_date', 'ASC')
        ->first();
    /// Rating
    $notrated_ser = \App\ServicesRequests::where('user_id', Auth::id())
        ->where('approved', 'Yes')
        ->where('date_time', '<', date('Y-m-d 17:00:00'))
        ->whereNull('rate')
        ->first();
    $notrated_men = \App\MentorshipRequest::where('user_id', Auth::id())
        ->where('zoom_date', '<', date('Y-m-d 17:00:00'))
        ->where('approved', 'Yes')
        ->whereNull('rating')
        ->first();
    /// My sessions
    if (!empty(Auth::user()->company)) {
        $sessions = \App\TrainingSession::where('step_id', Auth::user()->company->step)
            ->orderBy('datetime', 'ASC')
            ->take(3)
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
            'href' => '/dashboard/mentorship/mine',
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
  @endphp

  <!-- Content wrapper -->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-body">
        @if (session()->has('msg'))
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-{{ session()->get('class') }} mb-2" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span
                    aria-hidden="true">&times;</span> </button>
                <div class="d-flex align-items-center">
                  <i class="bx bx-error"></i>
                  <span> {{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }} </span>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>
      <!-- Count cards -->
      <div class="row g-6">
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

      <div class="row g-3 mt-6">
        <!-- Latest Services -->
        <div class="col-sm-6 col-12">
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
                        <a href="/dashboard/directory?type={{ $serv->serviceCategory->id }}">
                          {{ $serv->serviceCategory->{'name_' . $l} }}
                        </a>
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
		<!-- هناااااااااااااااااااااااااااااااااااااااااااااااا-->
		
		@if( $notrated_men or $notrated_ser )
		<div class="col-sm-6 col-12">
			<div class="card">
				<div class="card-header border-bottom">
					@if( $notrated_ser )
					<h4 class="card-title"><i class="bx bx-calendar mr-1"></i>{{__('dashboard.Rate')}} {{__('dashboard.service')}}: "{{$notrated_ser->service->{'name_'.$l} }}" <span class="primary" id="rname"></span></h4>
					@else
					<h4 class="card-title"><i class="bx bx-calendar mr-1"></i>{{__('dashboard.Rate mentorship session with')}} : {{$notrated_men->mentor->{'first_name_'.$l} }} {{$notrated_men->mentor->{'last_name_'.$l} }}<span class="primary" id="rname"></span></h4>
					@endif
					<div class="heading-elements">
						<ul class="list-inline mb-0">
							<li> <a data-action="close"> <i class="bx bx-x"></i> </a> </li>
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
											<i class="bx bx-check d-block d-sm-none"></i>
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
									<th style="font-size: 0.6rem">{{__('dashboard.Your session was in')}}</th>
									<th style="font-size: 0.6rem">{{__('dashboard.Did you attend this session?')}}</th>
									<th style="font-size: 0.6rem">{{ __('dashboard.Rate from 1 star "bad" to 5 stars "best"') }}</th>
									<th style="font-size: 0.6rem">{{ __('dashboard.Your opinion important kindly write any notes about this session') }}</th>
									<th style="font-size: 0.6rem">{{__('dashboard.Submit')}}</th>
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
											<i class="bx bx-check d-block d-sm-none"></i>
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
		<!-- هناااااااااااااااااااااااااااااااااااااااااااااااا-->
        <!-- / Latest Services -->

        <!-- Latest Events -->
        <div class="col-sm-6 col-12">
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
      </div>

      <!-- My Progress -->
      @if (Auth::user()->company && Auth::user()->company->cycle)
        <div class="card mt-10">
          <div class="card-header border-bottom">
            <h5 style="text-transform: capitalize">
              <i class="ti ti-briefcase ti-24px"></i>
              <span class="mx-2">{{ __('dashboard.My progress') }} :
                {{ Auth::user()->company->cycledata->title }}</span>
            </h5>
            <span
              class="text-capitalize">{{ __('dashboard.start from') . ': ' . Auth::user()->company->cycledata->start_format . ' ' . __('dashboard.to') . ': ' . Auth::user()->company->cycledata->end_format }}</span>
            <div class="d-flex align-items-center mt-3">
              <img src="{{ Auth::user()->company->cycledata->organization->image_url }}" alt="avatar"
                class="rounded-circle" height="35" width="35">
              <span class="mx-3 text-secondary">{{ Auth::user()->company->cycledata->organization->name }}</span>
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
                @foreach (\App\Step::where('cycle_id', Auth::user()->company->cycle)->orderBy('arr')->get() as $step)
                  @php
                    $status = $l == 'ar' ? 'لم تبدأ' : 'Not started';
                    $color = 'label-light';
                    $textColor = 'light';
                    if (Auth::user()->company->stepdata->arr > $step->arr) {
                        $status = $l == 'ar' ? 'انتهت' : 'Completed';
                        $color = 'label-success';
                        $textColor = 'success';
                    } elseif (Auth::user()->company->stepdata->arr == $step->arr) {
                        $status = $l == 'ar' ? 'بدأت' : 'Started';
                        $color = 'label-primary';
                        $textColor = 'primary';
                    }

                    $trtype = $flink = null;
                    if (Auth::user()->company->approved) {
                        $flink = 'my/' . $step->id;
                    } else {
                        $flink = 'view/' . Auth::user()->company->cycle;
                    }
                    $links = [
                        '',
                        '<a href="/dashboard/cycles/' . $flink . '">',
                        '',
                        '<a href="/dashboard/mentorship/mine">',
                        '<a href="/dashboard/training/my">',
                        '',
                    ];
                  @endphp
                  <tr>
                    <td>
                      @if (Auth::user()->company->approved)
                        <a href="/dashboard/cycles/{{ $flink }}">
                        @else
                          <a href="/dashboard/cycles/{{ $flink }}">
                      @endif
                      {{ $step->title }}
                      </a>
                    </td>
                    <td><span class="badge bg-{{ $color }} text-{{ $textColor }}">{{ $status }}</span>
                    </td>
                    <td>{{ Carbon\Carbon::parse($step->from)->format('m-d') }}</td>
                    <td>{{ Carbon\Carbon::parse($step->to)->format('d-m') }}</td>
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
      @endif
      <!-- / My Progress -->

      <!-- My last training -->
      @if (count($sessions) > 0)
        <div class="card">
          <div class="card-header border-bottom">
            <h5 style="text-transform: capitalize">
              <i class='bx bxs-megaphone font-medium-4 mr-1'></i>{{ __('dashboard.My Latest training') }} ::
              <a href="/dashboard/training/my">{{ __('dashboard.Show all') }}</a>
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
                        {{ __('cycles.Duration') }}: {{ $session->duration }} {{ __('cycles.Minutes') }} :
                        <a href="{{ $session->join_url }}" target="_blank">
                          <button title="Join only" type="button"
                            class="btn btn-icon rounded-circle btn-light-primary">
                            <i class="bx bx-desktop"></i></button>
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
      @if (Auth::user()->company->approved ?? null && count($mentors))
        <div class="card mt-10">
          <div class="card-header border-bottom">
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
          </div>

          <div class="table-responsive">
            <table class="table" id="mentorstable">
              <thead class="bg-label-light">
                <tr>
                  <th>#</th>
                  <th>{{ __('dashboard.Name/Title') }}</th>
                  <th>{{ __('dashboard.Bio') }}</th>
                  <th>{{ __('dashboard.Specialization') }}</th>
                  <th>{{ __('dashboard.Linkedin') }}</th>
                  <th>{{ __('dashboard.Request') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($mentors as $key => $mentor)
                  <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>
                      <div style="display: flex; align-items: center; width: 200px">
                        <a style="margin-right: 10px;">
                          <img src="{{ $mentor->user->image_url }}" alt="avatar" class="rounded-circle"
                            height="40" width="40">
                        </a>
                        <div>
                          <h6 style="margin-bottom: 0;">{{ $mentor->user->{'first_name_' . $l} }}
                            {{ $mentor->user->{'last_name_' . $l} }}</h6>
                          <span style="font-size: small;">{{ $mentor->user->{'title_' . $l} }}</span>
                        </div>
                      </div>
                    </td>
                    <td> {{ $mentor->user->bio->{'bio_' . $l} ?? '-' }} </td>
                    <td> {{ $mentor->user->bio->{'specialization_' . $l} ?? '-' }} </td>
                    <td>
                      @if ($mentor->user->bio->linkedin ?? 0)
                        <a title="Linkedin URL" target="_blank" href="{{ $mentor->user->bio->linkedin }}"
                          class="btn btn-icon rounded-circle btn-label-primary"> <i
                            class="fa-brands fa-linkedin-in"></i></a>
                      @endif
                    </td>
                    <td>
                      <button
                        onclick="request({{ $mentor->user_id }},'{{ $mentor->user->{'first_name_' . $l} }} {{ $mentor->user->{'last_name_' . $l} }}', {{ $mentor->cycle->organization_id }},{{ $mentor->cycle_id }})"
                        title="Add new request" type="button" class="btn btn-icon rounded-circle btn-light-primary">
                        <i class="bx bxs-conversation"></i>
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
      <!-- / My Mentors -->
    </div>
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

  <!-- Page JS -->
  <script src="/vuexy/assets/js/pages-account-settings-account.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>

  <script>
    $(document).ready(function() {
      $('#dfrom, #dto').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
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
