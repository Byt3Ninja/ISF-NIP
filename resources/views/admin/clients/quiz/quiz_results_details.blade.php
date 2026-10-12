@extends('layouts.admin')

@section('style')
<style type="text/css">
	.result-summary {
		padding: 1.5rem;
		border-radius: 10px;
		background: linear-gradient(to right, var(--bs-primary), color-mix(in srgb, var(--bs-primary) 70%, #fff));
		color: white;
		margin-bottom: 1.5rem;
		box-shadow: 0 4px 10px rgba(var(--bs-primary-rgb), 0.4);
	}
</style>
@stop

@section('content')
<div class="">
	<div class="">
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

		@if($quizResult->isNotEmpty())
			@php
				$result = $quizResult->first();
				$score = $result->qr_result_score;
				$passingCriteria = $result->q_quiz_passing_criteria;
				$isPassed = $score >= $passingCriteria;
				$status = $result->qr_result_status;
			@endphp

			<!-- Quiz Result Summary -->
			<div class="card card-body bg-gradient-primary text-white mb-4">
				<div class="row align-items-center">
					<div class="col-md-8">
						<h3 class="text-white fw-bold mb-3">{{ $result->q_quiz_title }}</h3>
						<div class="row">
							<div class="col-md-4">
								<div class="fw-bold fs-3 {{ $isPassed ? 'text-white' : 'text-warning' }}">
									{{ $score }}%
								</div>
								<div class="fs-5 text-white-50">
									{{ __('admin.passingCriteria') }}: {{ $passingCriteria }}%
								</div>
							</div>
							<div class="col-md-8">
								<div class="d-flex align-items-center mb-2">
									<i class="ti ti-calendar me-2"></i>
									<span>{{ __('admin.quizStartedAt') }}: {{ $result->qr_result_started_at }}</span>
								</div>
								@if($result->qr_result_ended_at)
									<div class="d-flex align-items-center mb-2">
										<i class="ti ti-clock me-2"></i>
										<span>{{ __('admin.quizEndsAt') }}: {{ $result->qr_result_ended_at }}</span>
									</div>
								@endif
								<div class="d-flex align-items-center">
									<i class="ti ti-user me-2"></i>
									<span>{{ __('admin.Status') }}: 
										@if($status == 1)
											<span class="badge bg-label-light text-dark ms-2">{{ __('admin.Completed') }}</span>
										@else
											<span class="badge bg-label-light text-dark ms-2">{{ __('admin.Pending') }}</span>
										@endif
									</span>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-4 text-md-end">
						<div class="progress mb-3" style="height: 10px;">
							<div class="progress-bar bg-white" role="progressbar" 
								style="width: {{ $score }}%;" aria-valuenow="{{ $score }}" 
								aria-valuemin="0" aria-valuemax="100"></div>
						</div>
						@if($isPassed)
							<div class="d-flex align-items-center justify-content-md-end">
								<i class="ti ti-circle-check text-success me-2" style="font-size: 1.5rem;"></i>
								<span class="fw-bold">{{ __('admin.quizPassed') }}</span>
							</div>
						@else
							<div class="d-flex align-items-center justify-content-md-end">
								<i class="ti ti-circle-x text-danger me-2" style="font-size: 1.5rem;"></i>
								<span class="fw-bold">{{ __('admin.quizFailed') }}</span>
							</div>
						@endif
					</div>
				</div>
			</div>

			<!-- Time Analysis Card -->
			@if($result->qr_result_started_at && $result->qr_result_ended_at)
				@php
					$startTime = \Carbon\Carbon::parse($result->qr_result_started_at);
					$endTime = \Carbon\Carbon::parse($result->qr_result_ended_at);
					$timeTaken = $startTime->diffInMinutes($endTime);
					$timeLimit = $result->q_quiz_time_limit;
					$timeEfficiency = $timeLimit > 0 ? round(($timeTaken / $timeLimit) * 100, 2) : 0;
				@endphp
				
				<div class="card mb-4">
					<div class="card-header">
						<h5 class="card-title mb-0">
							<i class="ti ti-clock text-info me-1"></i>
							{{ $l == 'en' ? 'Time Analysis' : 'تحليل الوقت' }}
						</h5>
					</div>
					<div class="card-body">
						<div class="row text-center">
							<div class="col-md-3">
								<div class="border rounded p-3">
									<i class="ti ti-player-play text-primary mb-2" style="font-size: 2rem;"></i>
									<h6>{{ $l == 'en' ? 'Start Time' : 'وقت البدء' }}</h6>
									<p class="mb-0">{{ $startTime->format('H:i:s') }}</p>
								</div>
							</div>
							<div class="col-md-3">
								<div class="border rounded p-3">
									<i class="ti ti-player-stop text-success mb-2" style="font-size: 2rem;"></i>
									<h6>{{ $l == 'en' ? 'End Time' : 'وقت الانتهاء' }}</h6>
									<p class="mb-0">{{ $endTime->format('H:i:s') }}</p>
								</div>
							</div>
							<div class="col-md-3">
								<div class="border rounded p-3">
									<i class="ti ti-hourglass text-warning mb-2" style="font-size: 2rem;"></i>
									<h6>{{ $l == 'en' ? 'Time Taken' : 'الوقت المستغرق' }}</h6>
									<p class="mb-0">{{ $timeTaken }} {{ __('admin.minutes') }}</p>
								</div>
							</div>
							<div class="col-md-3">
								<div class="border rounded p-3">
									<i class="ti ti-percentage text-info mb-2" style="font-size: 2rem;"></i>
									<h6>{{ $l == 'en' ? 'Time Efficiency' : 'كفاءة الوقت' }}</h6>
									<p class="mb-0">{{ $timeEfficiency }}%</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			@endif

			<!-- Quiz Information Card -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="ti ti-info-circle text-primary me-1"></i>
						{{ $l == 'en'? 'Quiz & Training Information' : 'معلومات الاختبار والتدريب' }}
					</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="card card-body bg-label-light border shadow-sm">
								<h6 class="fw-bold mb-3 text-primary">{{ $l == 'en'? 'Quiz Details' : 'تفاصيل الاختبار' }}</h6>
								
								<div class="d-flex justify-content-between align-items-center py-2 border-bottom">
									<span class="fw-semibold text-secondary">{{ __('admin.Title') }}:</span>
									<span class="fw-semibold text-dark">{{ $result->q_quiz_title }}</span>
								</div>
								
								@if($result->q_quiz_description)
									<div class="d-flex justify-content-between align-items-center py-2 border-bottom">
										<span class="fw-semibold text-secondary">{{ __('admin.Description') }}:</span>
										<span class="fw-semibold text-dark">{{ Str::limit($result->q_quiz_description, 50) }}</span>
									</div>
								@endif
								
								<div class="d-flex justify-content-between align-items-center py-2 border-bottom">
									<span class="fw-semibold text-secondary">{{ __('admin.TimeLimit') }}:</span>
									<span class="fw-semibold text-dark">{{ $result->q_quiz_time_limit }} {{ __('admin.minutes') }}</span>
								</div>
								
								<div class="d-flex justify-content-between align-items-center py-2 border-bottom">
									<span class="fw-semibold text-secondary">{{ $l == 'en' ? 'Max Attempts' : 'عدد المحاولات' }}:</span>
									<span class="fw-semibold text-dark">{{ $result->q_quiz_attempts }}</span>
								</div>
								
								<div class="d-flex justify-content-between align-items-center py-2 border-bottom">
									<span class="fw-semibold text-secondary">{{ __('admin.passingCriteria') }}:</span>
									<span class="fw-semibold text-dark">{{ $result->q_quiz_passing_criteria }}%</span>
								</div>
								
								<div class="d-flex justify-content-between align-items-center py-2">
									<span class="fw-semibold text-secondary">{{ __('admin.shuffleQuestions') }}:</span>
									<span class="fw-semibold text-dark">
										@if($result->q_quiz_shuffle)
											<span class="badge bg-label-success">{{ __('admin.Yes') }}</span>
										@else
											<span class="badge bg-label-secondary">{{ __('admin.No') }}</span>
										@endif
									</span>
								</div>
							</div>
						</div>
						
						<div class="col-md-6">
							<div class="card card-body bg-label-light border shadow-sm">
								<h6 class="fw-bold mb-3 text-success">{{ $l == 'en'? 'Training Information' : 'معلومات التدريب' }}</h6>
								
								<div class="d-flex justify-content-between align-items-center py-2 border-bottom">
									<span class="fw-semibold text-secondary">{{ __('admin.Training') }}:</span>
									<span class="fw-semibold text-dark">{{ $result->t_training_title }}</span>
								</div>
								
								@if($result->t_training_description)
									<div class="d-flex justify-content-between align-items-center py-2 border-bottom">
										<span class="fw-semibold text-secondary">{{ __('admin.Description') }}:</span>
										<span class="fw-semibold text-dark">{{ Str::limit($result->t_training_description, 50) }}</span>
									</div>
								@endif
								
								<div class="d-flex justify-content-between align-items-center py-2 border-bottom">
									<span class="fw-semibold text-secondary">{{ __('admin.Organization') }}:</span>
									<span class="fw-semibold text-dark">{{ $result->{'o_org_name_' . $l} }}</span>
								</div>
								
								<div class="d-flex justify-content-between align-items-center py-2 border-bottom">
									<span class="fw-semibold text-secondary">{{ $l == 'en' ? 'Quiz Period' : 'فترة الاختبار' }}:</span>
									<span class="fw-semibold text-dark">
										<small>{{ \Carbon\Carbon::parse($result->q_quiz_start)->format('Y-m-d H:i') }} - {{ \Carbon\Carbon::parse($result->q_quiz_end)->format('Y-m-d H:i') }}</small>
									</span>
								</div>
								
								<div class="d-flex justify-content-between align-items-center py-2">
									<span class="fw-semibold text-secondary">{{ __('admin.quizStatus') }}:</span>
									<span class="fw-semibold text-dark">
										@if($result->q_quiz_status == 'published')
											<span class="badge bg-label-success">{{ __('admin.Published') }}</span>
										@else
											<span class="badge bg-label-warning">{{ __('admin.Draft') }}</span>
										@endif
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		@else
			<div class="card">
				<div class="card-body text-center py-5">
					<i class="ti ti-alert-circle text-warning" style="font-size: 4rem;"></i>
					<h4 class="mt-3">{{ __('admin.NoResultFound') }}</h4>
					<p class="text-muted">{{ __('admin.TheRequestedQuizResultCouldNotBeFound') }}</p>
					<a href="{{ route('quizzes.quiz_Results_view') }}" class="btn btn-primary">
						<i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>
						{{ __('admin.BackToResults') }}
					</a>
				</div>
			</div>
		@endif
	</div>
</div>
@stop

@section('pagescripts')
<script type="text/javascript">
	// Initialize tooltips
	$(function () {
		$('[data-bs-toggle="tooltip"]').tooltip();
	});
</script>
@stop