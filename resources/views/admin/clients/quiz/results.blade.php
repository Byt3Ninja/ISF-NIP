@extends('layouts.admin')

@section('style')
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
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
@endsection

@section('content')
	<div class="">
		<div class="">
			<div class="content-body">
				<section id="multiple-column-form">
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-12 col-lg-12 col-sm-12">
								<!-- Quiz Summary -->
								@php
									$correctCount = $results->where('status', true)->count();
									$totalCount = $results->count();
									$scorePercentage = $totalCount > 0 ? ($correctCount / $totalCount) * 100 : 0;
									$scorePercentageRounded = round($scorePercentage);
									$passThreshold = 70; // Set your pass threshold here
									$isPassed = $scorePercentage >= $passThreshold;
								@endphp
								<div class="result-summary">
									<div class="row align-items-center">
										<div class="col-md-6">
											<h4 class="text-white fw-bold">{{ __('admin.quizResults') }}</h4>
											<div class="summary-score">{{ $correctCount }} / {{ $totalCount }}</div>
											<p class="mb-0">
												{{ $scorePercentageRounded }}% 
												{{-- {{ $isPassed ? __('admin.quizPassed') : __('admin.quizFailed') }} --}}
											</p>
											<div class="progress mt-4">
												<div class="progress-bar bg-white" style="width: {{ $scorePercentageRounded }}%;"></div>
											</div>
										</div>
										<div class="col-md-6 text-md-end">
											<h5 class="text-white fw-semibold">{{ $quiz->title }}</h5>
											<p class="mb-0">
												<i class="ti ti-calendar-off"></i>
												{{ __('admin.Completed on') }}: {{ now()->format('M d, Y') }}
											</p>
										</div>
									</div>
								</div>
								
								<!-- Quiz Results -->
								<div class="row">
									<div class="col-12">
										<div class="" style="direction: ltl;">
											<div id="quizResultsContainer">
												@foreach($results as $key => $response)
													@php $question = $response->question; @endphp
													<div class="card my-0 mb-3">
														<div class="card-header h-px-40">
															<div class="d-flex align-items-center justify-content-between">
																<div>
																	<span class="question-number fw-bold">#{{ $key+1 }}</span>
																	<span class="question-text px-3 fw-bold">{{ $question->question }}</span>
																</div>
																<div class="question-status">
																	@if($response->status == true)
																		<span class="result-icon correct text-success" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ __('admin.quizCorrectAnswer') }}" title="{{ __('admin.quizCorrectAnswer') }}">
																			<i class="ti ti-circle-check"></i>
																		</span>
																	@else
																		<span class="result-icon incorrect text-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ __('admin.quizWrongAnswer') }}" title="{{ __('admin.quizWrongAnswer') }}">
																			<i class="ti ti-circle-x"></i>
																		</span>
																	@endif
																</div>
															</div>
														</div>
														<div class="card-body py-3">
															<ul class="widget-timeline mb-0">
																@if($question->type == 'tfq')
																	@php
																		$correct = $question->correct;
																		$answer = $response->response === 1 || $response->response === "1" || $response->response === "true" || $response->response === true ? true : false;
																		$true_class = 'btn-outline-primary';
																		$false_class = 'btn-outline-primary';
																		$true_icon = '';
																		$false_icon = '';
																		
																		if($correct == true) {
																			$true_class = 'btn-outline-success';
																			$true_icon = '<i class="ti ti-check ms-2"></i>';
																			if($correct == $answer) { 
																				$true_class = 'btn-success'; 
																				$true_icon = '<i class="ti ti-check-circle ms-2"></i>';
																			} else { 
																				$false_class = 'btn-danger';
																				$false_icon = '<i class="ti ti-x-circle ms-2"></i>';
																			}
																		}
																		if($correct == false) {
																			$false_class = 'btn-outline-success';
																			$false_icon = '<i class="ti ti-check ms-2"></i>';
																			if($correct == $answer) { 
																				$false_class = 'btn-success';
																				$false_icon = '<i class="ti ti-check-circle ms-2"></i>';
																			} else { 
																				$true_class = 'btn-danger';
																				$true_icon = '<i class="ti ti-x-circle ms-2"></i>';
																			}
																		}
																	@endphp
																	<div class="form-group my-2">
																		<input type="radio" class="d-none quiz-option" name="tfq_answer_{{ $key }}" id="question_{{ $key }}_answer_true" value="true" {{ $answer ? 'checked' : '' }} disabled>
																		<label class="btn {{ $true_class }} w-100 d-flex justify-content-between align-items-center quiz-option-label" for="question_{{ $key }}_answer_true">
																			<span>{{ '1. '. __('admin.tfq_statement_true') }}</span>
																			{!! $true_icon !!}
																		</label>
																	</div>
																	<div class="form-group my-2">
																		<input type="radio" class="d-none quiz-option" name="tfq_answer_{{ $key }}" id="question_{{ $key }}_answer_false" value="false" {{ !$answer ? 'checked' : '' }} disabled>
																		<label class="btn {{ $false_class }} w-100 d-flex justify-content-between align-items-center quiz-option-label" for="question_{{ $key }}_answer_false">
																			<span>{{ '2. '. __('admin.tfq_statement_false') }}</span>
																			{!! $false_icon !!}
																		</label>
																	</div>
																@elseif($question->type == 'mcq')
																	@foreach($question->answers as $index => $answer)
																		@php
																			$btn_class = 'btn-outline-primary';
																			$icon = '';
																			$value = md5($answer['text'] . '+' . $answer['status']);
																			$_answer = $response->response;
																			$is_selected = $_answer == $value;
																			
																			if($answer['status'] == true) { 
																				$btn_class = 'btn-outline-success'; 
																				$icon = '<i class="ti ti-check ms-2"></i>';
																			}
																			
																			if($is_selected && $answer['status'] == true) { 
																				$btn_class = 'btn-success';
																				$icon = '<i class="ti ti-check-circle ms-2"></i>';
																			} else if($is_selected) { 
																				$btn_class = 'btn-danger'; 
																				$icon = '<i class="ti ti-x-circle ms-2"></i>';
																			}
																		@endphp
																		<div class="form-group my-2">
																			<input type="radio" class="d-none quiz-option" name="mcq_answer_{{ $key }}" id="question_{{ $key }}_answer_{{ $index }}" value="{{ $value }}" {{ $is_selected ? 'checked' : '' }} disabled>
																			<label class="btn {{ $btn_class }} w-100 d-flex justify-content-between align-items-center quiz-option-label" for="question_{{ $key }}_answer_{{ $index }}">
																				<span>{{ ($index + 1) . '. ' . $answer['text'] }}</span>
																				{!! $icon !!}
																			</label>
																		</div>
																	@endforeach
																@endif
																
																<div class="explanation mt-3 p-3 rounded" style="background-color: #f8f9fa;">
																	<h6 class="mb-1">{{ __('admin.Explanation') }}:</h6>
																	<p class="mb-0">{{ $question->explanation ?? __('admin.No explanation provided') }}</p>
																</div>
															</ul>
														</div>
													</div>
												@endforeach
											</div>
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
		$(function () {
			// Initialize tooltips
			$('[data-bs-toggle="tooltip"]').tooltip();
			
			// Prevent changes to quiz options
			$(document).on('change', '.quiz-option', function(e) { 
				e.preventDefault();
				return false;
			});
			
			// Add animation when collapse elements are shown
			$('.collapse').on('show.bs.collapse', function () {
				$(this).prev('.card-header').find('.result-icon').addClass('animate-pulse');
				setTimeout(() => {
					$(this).prev('.card-header').find('.result-icon').removeClass('animate-pulse');
				}, 1000);
			});
			
			// Smooth scroll to clicked question
			$(document).on('click', '.quiz-question-btn', function() {
				setTimeout(() => {
					const isOpen = $(this).attr('aria-expanded') === 'true';
					if (isOpen) {
						const targetOffset = $(this).offset().top - 100;
						$('html, body').animate({
							scrollTop: targetOffset
						}, 500);
					}
				}, 300);
			});
			
			// Auto-expand first incorrect answer (if any)
			setTimeout(() => {
				const firstIncorrect = $('.result-icon.incorrect').first();
				if (firstIncorrect.length) {
					firstIncorrect.closest('.quiz-question-btn').trigger('click');
				}
			}, 500);
		});
	</script>
@endsection
