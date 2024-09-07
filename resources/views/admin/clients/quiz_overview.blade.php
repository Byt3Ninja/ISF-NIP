@extends('layouts.admin')
@section('style')
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
	<style type="text/css">
		.training-image { border-radius: 50%; border: 3px solid #ccc; padding: 0.2rem; }
		.w-15 { width: 15%; }
		.custom-tag { padding-block: 0.125rem; padding-inline: 0.5rem; cursor: pointer; }
	</style>
@endsection
@section('content')
	<div class="app-content content">
		<div class="content-overlay"></div>
		<div class="content-wrapper">
			<div class="content-header row">
				<div class="content-header-left col-12 mb-2 mt-1">
					<div class="row breadcrumbs-top">
						<div class="col-12">
							<h5 class="content-header-title float-left pr-1 mb-0">{{__('settings.View training content')}}</h5>
							<div class="breadcrumb-wrapper col-12">
								<ol class="breadcrumb p-0 mb-0">
									<li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a></li>
									<li class="breadcrumb-item"> <a href="/dashboard/training/my"> {{__('admin.My courses')}} </a> </li>
									<li class="breadcrumb-item"> <a href="/dashboard/training/my/{{ $training->id }}"> {{ $training->title }} </a> </li>
									<li class="breadcrumb-item active"> {{ $quiz->title }} </li>
								</ol>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="content-body">
				@if( session()->has('msg') )
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-{{ session()->get('class') ?? 'danger' }} mb-2" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
							<div class="d-flex align-items-center">
								<i class="bx bx-error"></i>
								<span> {{ session()->get('msg') }} {{ Session::forget('msg') }} </span>
							</div>
						</div>
					</div>
				</div>
				@endif
				<section id="multiple-column-form">
					<div class="row match-height">
						<div class="col-md-3 col-sm-12">
							<div class="card">
								<div class="card-content">
									<div class="text-center my-2"> <img class="training-image w-75" src="/191014/{{ $training->image }}" /> </div>
									<h3 class="text-center"> {{ $training->title }} </h3>
									<div class="card-body text-justify"> {{ $training->dsc }} </div>
								</div>
							</div>
						</div>
						<div class="col-md-9 col-sm-12">
							<div class="card">
								<div class="card-header">
									<span class="card-title"> {{ __('admin.quizDetails') }} </span>
									@if( $available || !is_null( $active_now ) )
										<span class="float-right">
											<form id="startQuizForm" method="post" action="{{ route('quiz.start', ['id' => $training->id, 'quiz_id' => $quiz->id]) }}">
												{!! csrf_field() !!}
												<button type="submit" id="quizStartBtn" class="btn btn-primary"> @if( !is_null( $active_now ) ) {{ __('admin.quizResume') }} @else {{ __('admin.quizStart') }} @endif </button>
											</form>
										</span>
									@endif
								</div>
								<div class="card-content px-2">
									<div class="container">
										<div class="table-responsive">
											<table class="table table-bordered table-hover table-striped">
												<tbody>
													<tr>
														<td class="w-15"> {{ __('admin.quizTitle') }} </td>
														<td>{{ $quiz->title }}</td>
													</tr>
													<tr>
														<td class="w-15">  {{ __('admin.Description') }} </td>
														<td>{{ $quiz->description }}</td>
													</tr>
													<tr>
														<td class="w-15"> {{ __('admin.quizNumOfQuestions') }} </td>
														<td>{{ $questions->count() }}</td>
													</tr>
													<tr>
														<td class="w-15"> {{ __('admin.quizTopics') }} </td>
														<td>
															@foreach( $tags as $tag)
																<span class="btn custom-tag btn-sm btn-outline-dark"> {{ $tag }} </span>
															@endforeach
														</td>
													</tr>
													<tr>
														<td class="w-15"> {{ __('admin.quizStartsAt') }} </td>
														<td>{{ $quiz->start->format('jS M. Y h:i A') }}</td>
													</tr>
													<tr>
														<td class="w-15"> {{ __('admin.quizEndsAt') }} </td>
														<td>{{ $quiz->end->format('jS M. Y h:i A') }}</td>
													</tr>
													<tr>
														<td class="w-15"> {{ __('admin.quizAvailableAfter') }} </td>
														<td>
															@if( $available == true )
																<span class="badge badge-success"> {{ __('admin.availableNow') }} </span>
															@elseif( !is_null( $active_now )  )
																{{ __('admin.quizInProgress') }}
															@elseif( !empty( $time_remaining ) )
																{{ __('admin.willAvailableAt') }} <span class="mx-2" id="timeRemain" data-value="{{ $time_remaining }}"> {{ $time_remaining }} </span>
															@elseif( $completion_percentage < 100 )
																{{ __('admin.quizAvailableAfter100') }}
															@elseif( $quiz->start > now() )
																{{ __('admin.willAvailableAt') }} <span class="mx-2" id="timeRemain" data-value="{{ $time_remaining }}"> {{ $time_remaining }} </span>
															@else
																<span class="badge badge-danger"> {{ __('admin.notAvailable') }} </span>
															@endif
														</td>
													</tr>
													<tr>
														<td class="w-15"> {{ __('admin.TimeLimit') }} </td>
														<td>{{ $quiz->time_limit > 60 ? intval($quiz->time_limit / 60) . ' ' . __('admin.hours') . ' ' . ($quiz->time_limit % 60) . ' ' . __('admin.minutes') : $quiz->time_limit . ' ' . __('admin.minutes') }}</td>
													</tr>
													<tr>
														<td class="w-15"> {{ __('admin.quizAttempts') }} </td>
														<td>{{ $quiz->attempts }}</td>
													</tr>
													<tr>
														<td class="w-15"> {{ __('admin.passingCriteria') }} </td>
														<td>{{ $quiz->passing_criteria . '%' }}</td>
													</tr>
												</tbody>
											</table>
										</div>
										<hr class="hr" />
										<div class="table-responsive">
											<div class="card-title my-1"> {{ __('admin.quizResults') }} </div>
											<table class="table table-bordered table-hover">
												<thead>
													<th class="text-center" style="width: 6%;"> # </th>
													<th class="text-center" style="width: 9%;"> {{ __('admin.quizScore') }} </th>
													<th class="text-center" style="width: 11%;"> {{ __('admin.quizStatus') }} </th>
													<th> {{ __('admin.quizStartedAt') }} </th>
													<th class="text-center"> {{ __('admin.quizTimeTook') }} </th>
													@if( $allowActions ) <th class="text-center" style="width: 18%;"> {{ __('admin.Actions') }} </th> @endif
												</thead>
												<tbody>
													@if( empty( $attempts ) )
														<tr>
															<td colspan="{{ $allowActions == true ? 6 : 5 }}" class="text-center">
																{{ __('admin.quizNoResults') }}
															</td>
														</tr>
													@else
														@foreach( $attempts as $index => $attempt)
															<tr>
																<td class="text-center"> {{ $index + 1 }} </td>
																<td class="text-center"> {{ $attempt->score . '%' }} </td>
																<td class="text-center">
																	@if( $attempt->score >= $quiz->passing_criteria )
																		<span class="badge badge-success"> {{ __('admin.quizPassed') }} </span>
																	@elseif( $active_now == $attempt->id )
																		<span class="badge badge-info"> {{ __('admin.quizInProgress') }} </span>
																	@else
																		<span class="badge badge-danger"> {{ __('admin.quizFailed') }} </span>
																	@endif
																</td>
																<td> {{ $attempt->created_at->format('jS M. Y h:i A') }} </td>
																<td class="text-center">
																	@if( $attempt->status )
																		{{ $attempt->started_at->diffInMinutes( $attempt->ended_at ) . ' ' . __('admin.minutes') }}
																	@else
																		{{ __('admin.quizInProgress') }}
																	@endif
																</td>
																@if( $allowActions == true )
																<td> <a href="{{ route('quiz.response', ['id' => $training->id, 'quiz_id' => $quiz->id, 'response_id' => $attempt->id]) }}" class="btn btn-primary btn-sm"> {{ __('admin.quizViewResults') }} </a> </td>
																@endif
															</tr>
														@endforeach
													@endif
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
		</div>
	</div>
@endsection
@section('pagescripts')
	<script type="text/javascript">
		@if( $available )
		$(document).on('click', '#quizStartBtn', function (e) {
			e.preventDefault();
			Swal.fire({
				type: 'warning',
				title: '<strong> {{ __("admin.cautionPlease") }} </strong>',
				html: '{!! __("admin.startQuizMsg") !!}',
				buttonsStyling: false,
				showCancelButton: true,
				confirmButtonText: "{!! __('admin.startQuizOk') !!}",
				cancelButtonText: "{!! __('admin.startQuizCancel') !!}",
				confirmButtonClass: 'btn btn-primary',
				cancelButtonClass: 'btn btn-danger ml-1',
			}).then(function ( result ) {
				if( result.value ) { $("#startQuizForm").submit(); }
			});
		});
		@endif

		$(document).ready(function(){
			@if( !empty( $time_remaining ) )
			var timeRemain = $('#timeRemain');
			var time = timeRemain.data('value').split(':');
			var countDownDate = new Date().getTime() + (parseInt(time[0]) * 60 * 60 * 1000) + (parseInt(time[1]) * 60 * 1000) + (parseInt(time[2]) * 1000);
			var x = setInterval(function() {
				var now = new Date().getTime();
				var distance = countDownDate - now;

				var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
				var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
				var seconds = Math.floor((distance % (1000 * 60)) / 1000);

				timeRemain.html(('0' + hours).slice(-2) + ":" + ('0' + minutes).slice(-2) + ":" + ('0' + seconds).slice(-2));
				if( distance < 0 )
				{
					clearInterval(x);
					timeRemain.parent().html('<span class="badge badge-success"> {{ __('admin.availableNow') }} </span>');
				}
			}, 1000);
			@endif
		});
	</script>
@endsection
