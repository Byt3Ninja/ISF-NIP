@extends('layouts.admin')

@section('style')
<link rel="stylesheet" type="text/css"
	href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
@stop

@section('content')
<div class="">
	<div class="">

		<div class="row mb-6">
			<div class="col-12 col-md-8">
				<h4 class="fw-bold mb-2">{{ $training->title }}</h4>
				<p class="card-text text-muted line-clamp-2 text-truncate">{{ $training->dsc }}</p>
			</div>
		</div>

		@if(session()->has('msg'))
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-3 shadow-sm"
						role="alert">
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						<div class="d-flex align-items-center">
							<i class="ti ti-alert-triangle me-3 ti-md"></i>
							<span> {{ session()->get('msg') }} {{ Session::forget('msg') }} </span>
						</div>
					</div>
				</div>
			</div>
		@endif

		<div class="card card-body mb-6">
			<div class="row g-0">
				<!-- Quiz Illustration (Left Side) -->
				<div class="col-md-4">
					<div class="quiz-image p-4">
						<img class="img-fluid rounded" src="/images/quiz-illustration.svg" alt="Quiz Image" />
					</div>
				</div>

				<!-- Quiz Information (Right Side) -->
				<div class="col-md-8">
					<div class="p-10 h-100 d-flex flex-column justify-content-between">
						<div class="d-flex justify-content-between align-items-center mb-2">
							<h3 class="mb-0">{{ $quiz->title }}</h3>
							@if( $quiz_status == 'passed' )
								<span class="badge px-4 py-2 rounded-pill bg-label-success"> {{ __('admin.quizPassed') }} </span>
							@elseif( $quiz_status == 'notTaken' )
								<span class="badge px-4 py-2 rounded-pill bg-label-danger"> {{ __('admin.quizNotTaken') }} </span>
							@elseif( $quiz_status == 'failed' )
								<span class="badge px-4 py-2 rounded-pill bg-label-danger"> {{ __('admin.quizFailed') }} </span>
							@elseif( $quiz_status == 'notStartedYet' )
								<span class="badge px-4 py-2 rounded-pill bg-secondary"> {{ __('admin.quizNotStartedYet') }} </span>
							@elseif( $quiz_status == 'inProgress' )
								<span class="badge px-4 py-2 rounded-pill bg-label-primary"> {{ __('admin.quizInProgress') }} </span>
							@elseif( $quiz_status == 'available' )
								<span class="badge px-4 py-2 rounded-pill bg-label-primary"> {{ __('admin.quizAvailable') }} </span>
							@endif
						</div>

						<p class="text-muted mb-4">
							{{ $quiz->description }}
						</p>

						<div class="row mb-4">
							<div class="col-md-6">
								<div class="p-2">
									<i class="ti text-primary ti-list-check"></i>
									<span class="text-dark fw-semibold">
										{{ $questions->count() }}
										{{ __('admin.Questions') }}
									</span>
								</div>
								<div class="p-2">
									<i class="ti text-primary ti-calendar me-1"></i>
									<span class="text-dark fw-semibold">
										{{ __('admin.Start at') }}: {{ $quiz->start->format('jS M. Y h:i A') }}
									</span>
								</div>
								<div class="p-2">
									<i class="ti text-primary ti-calendar-event me-1"></i>
									<span class="text-dark fw-semibold">
										{{ __('admin.End at') }}: {{ $quiz->end->format('jS M. Y h:i A') }}
									</span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="p-2">
									<i class="ti text-primary ti-clock me-1"></i>
									<span class="text-dark fw-semibold">
										{{ __('admin.TimeLimit') }}: 
										{{ $quiz->time_limit > 60 ? intval($quiz->time_limit / 60) . ' ' . __('admin.hours') . ' ' .
										($quiz->time_limit % 60 > 0 ? intval($quiz->time_limit % 60) . ' ' . __('admin.minutes') : '') :
										$quiz->time_limit . ' ' . __('admin.minutes') }}
									</span>
								</div>
								<div class="p-2">
									<i class="ti text-primary ti-refresh me-1"></i>
									<span class="text-dark fw-semibold">{{ __('admin.quizAttempts') }}: {{ $quiz->attempts }}</span>
								</div>
								<div class="p-2">
									<i class="ti text-primary ti-chart-pie me-1"></i>
									<span class="text-dark fw-semibold">{{ __('admin.passingCriteria') }}: {{ $quiz->passing_criteria }}%</span>
								</div>
							</div>
						</div>

						@if( $quiz_status == 'inProgress' )

						<div class="progress" style="height: 20px !important;">
							<div class="progress-bar bg-primary" role="progressbar" style="width: {{ $completion_percentage }}%" aria-valuenow="{{ $completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
								<div class="progress-bar text-bg-primary" style="width: {{ $completion_percentage }}%">{{ $completion_percentage }}%</div>
							</div>
						</div>
						@endif

						@if($available || !is_null( $active_now ))
							<form 
								id="startQuizForm" 
								class="text-end mt-auto"
								method="post"
								action="{{ route('quizzes.take_start', ['id' => $training->id, 'quiz_id' => $quiz->id]) }}"
							>
								{!! csrf_field() !!}
								<button type="submit" id="quizStartBtn" class="btn btn-primary btn-lg shadow-sm">
									<i class="ti ti-player-play me-2"></i>
									@if( !is_null( $active_now ) )
									{{ __('admin.quizResume') }}
									@elseif( now()->format('Y-m-d H:i:s') < $quiz->start->format('Y-m-d H:i:s') || $attempts->count() == 0 )
									{{ __('admin.quizStart') }}
									@else
									{{ __('admin.quizResume') }}
									@endif
								</button>
							</form>
						@endif
					</div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-header">
				<h5 class="card-title border-bottom pb-2">
					<i class="ti text-primary ti-report-analytics ti-lg"></i>
					{{ __('admin.quizResults') }}
				</h5>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-hover align-middle">
						<thead class="table-light">
							<tr>
								<th class="text-center" style="width: 5%;"> # </th>
								<th class="text-center" style="width: 10%;"> {{ __('admin.quizScore') }} </th>
								<th class="text-center" style="width: 12%;"> {{ __('admin.quizStatus') }} </th>
								<th> {{ __('admin.quizStartedAt') }} </th>
								<th class="text-center"> {{ __('admin.quizTimeTook') }} </th>
								@if($allowActions)
								<th class="text-center" style="width: 18%;"> {{ __('admin.Actions') }} </th>
								@endif
							</tr>
						</thead>
						<tbody>
							@if(empty($attempts) || (!empty($attempts) && $attempts->count() <= 0)) <tr>
								<td colspan="{{ $allowActions == true ? 6 : 5 }}" class="text-center py-4 text-muted">
									<i class="ti text-primary ti-file-x fs-2 d-block mb-2"></i>
									{{ __('admin.quizNoResults') }}
								</td>
								</tr>
								@else
								@foreach($attempts as $index => $attempt)
								<tr>
									<td class="text-center fw-bold"> {{ $index + 1 }} </td>
									<td class="text-center">
										<span
											class="fw-bold fs-6 {{ $attempt->score >= $quiz->passing_criteria ? 'text-success' : 'text-danger' }}">
											{{ $attempt->score }}%
										</span>
									</td>
									<td class="text-center">
										@if($attempt->score >= $quiz->passing_criteria)
										<span class="badge bg-success rounded-pill">
											<i class="ti ti-check me-1"></i>
											{{ __('admin.quizPassed') }}
										</span>
										@elseif($active_now == $attempt->id)
										<span class="badge bg-info rounded-pill">
											<i class="ti ti-clock me-1"></i>
											{{ __('admin.quizInProgress') }}
										</span>
										@else
										<span class="badge bg-danger rounded-pill">
											<i class="ti ti-x me-1"></i>
											{{ __('admin.quizFailed') }}
										</span>
										@endif
									</td>
									<td>
										<i class="ti text-primary ti-calendar-time me-1"></i>
										{{ $attempt->created_at?->format('jS M. Y h:i A') }}
									</td>
									<td class="text-center">
										<i class="ti text-primary ti-hourglass me-1"></i>
										@if($attempt->status)
										{{ $attempt->started_at->diffInMinutes($attempt->ended_at) . ' ' . __('admin.minutes') }}
										@else
										{{ __('admin.quizInProgress') }}
										@endif
									</td>
									@if($allowActions == true)
									<td class="text-center">
										<a href="{{ route('quizzes.view_response_mine', ['id' => $training->id, 'quiz_id' => $quiz->id, 'response_id' => $attempt->id]) }}"
											class="btn btn-label-primary btn-sm rounded-pill shadow-sm">
											<i class="ti ti-eye me-1"></i>
											{{ __('admin.quizViewResults') }}
										</a>
									</td>
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
@stop
@section('pagescripts')
<script type="text/javascript">
	@if( $available )
		$(document).on('click', '#quizStartBtn', function (e) {
			e.preventDefault();
			Swal.fire({
				icon: 'warning',
				title: '<strong> {{ __("admin.cautionPlease") }} </strong>',
				html: '{!! __("admin.startQuizMsg") !!}',
				buttonsStyling: true,
				showCancelButton: true,
				confirmButtonText: "<i class='ti ti-check me-1'></i> {!! __('admin.startQuizOk') !!}",
				cancelButtonText: "<i class='ti ti-x me-1'></i> {!! __('admin.startQuizCancel') !!}",
				confirmButtonClass: 'btn btn-primary',
				cancelButtonClass: 'btn btn-danger ms-1',
				customClass: {
					confirmButton: 'btn btn-primary',
					cancelButton: 'btn btn-label-danger'
				},
				reverseButtons: true,
				// focusConfirm: false,
				backdrop: true,
				showClass: {
					popup: 'animate__animated animate__fadeInDown'
				},
				hideClass: {
					popup: 'animate__animated animate__fadeOutUp'
				}
			}).then(function (result) {
				if (result.value) { 
					// Add loading state to button
					const btn = $('#quizStartBtn');
					btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> {{ __("admin.loading") }}');
					btn.attr('disabled', true);
					
					// Submit form
					$("#startQuizForm").submit(); 
				}
			});
		});
		@endif

		$(document).ready(function(){
			// Enhanced countdown timer
			@if( !empty( $time_remaining ) )
			const timeRemain = $('#timeRemain');
			const time = timeRemain.data('value').split(':');
			const countDownDate = new Date().getTime() + 
				(parseInt(time[0]) * 60 * 60 * 1000) + 
				(parseInt(time[1]) * 60 * 1000) + 
				(parseInt(time[2]) * 1000);
				
			const x = setInterval(function() {
				const now = new Date().getTime();
				const distance = countDownDate - now;

				// Time calculations
				const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
				const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
				const seconds = Math.floor((distance % (1000 * 60)) / 1000);

				// Update countdown display
				timeRemain.html(('0' + hours).slice(-2) + ":" + ('0' + minutes).slice(-2) + ":" + ('0' + seconds).slice(-2));
				
				// Apply visual effect for last minute
				if (hours === 0 && minutes < 1) {
					timeRemain.addClass('text-danger fw-bold');
					if (seconds % 2 === 0) {
						timeRemain.css('opacity', '0.7');
					} else {
						timeRemain.css('opacity', '1');
					}
				}

				// When countdown is finished
				if (distance < 0) {
					clearInterval(x);
					timeRemain.parent().html('<span class="badge bg-success"> <i class="ti text-primary ti-check me-1"></i> {{ __("admin.availableNow") }} </span>');
					
					// Optional: Add notification or sound
					if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
						new Notification('{{ __("admin.quizAvailable") }}', {
							body: '{{ __("admin.quizAvailableMessage") }}',
							icon: '/favicon.ico'
						});
					}
					
					// Optional: Reload page to show updated state
					setTimeout(() => {
						location.reload();
					}, 1500);
				}
			}, 1000);
			@endif
		});
</script>
@stop