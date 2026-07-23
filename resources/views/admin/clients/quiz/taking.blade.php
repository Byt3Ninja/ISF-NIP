@extends('layouts.admin')
@section('content')
<div class="">
	<div class="">
		<div class="content-body">
			<section>
				<div class="container-fluid">
					<!-- Header Section -->
					<div class="row mb-4">
						<div class="col-12">
							<div class="card shadow-sm border-0">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center flex-wrap">
										<div>
											<h5 class="card-title mb-1">{{ $quiz->title ?? 'Quiz' }}</h5>
											<p class="text-muted mb-0">{{ $training->title ?? 'Training Session' }}</p>
										</div>
										<div class="text-end">
											<div class="d-flex align-items-center gap-2">
												<i class="fas fa-clock text-primary"></i>
												<div>
													<small class="text-muted d-block">{{ __('admin.Time Remaining') }}</small>
													<h5 class="mb-0 text-primary fw-bold" id="timeRemain" data-value="{{ $time_remaining }}">
														{{ $time_remaining }}
													</h5>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Question Section -->
					<div class="row">
						<div class="col-12">
							<div class="card shadow-sm border-0">
								<div class="card-header bg-label-secondary border-0">
									<div class="row align-items-center">
										<div class="col-md-8">
											<div class="d-flex align-items-center gap-2">
												<span class="badge bg-label-primary border border-primary fs-6">{{ __('admin.question')}} {{ $current_question ?? 1 }}</span>
												<div class="progress flex-fill" style="height: 8px;">
													<div class="progress-bar" role="progressbar"
														style="width: {{ ($answered_questions ?? 0) / ($total_questions ?? 1) * 100 }}%"></div>
												</div>
												<small class="fw-bold">{{ $answered_questions ?? 0 }}/{{ $total_questions ?? 1 }}</small>
											</div>
										</div>
										<div class="col-md-4 text-end">
											<div class="d-flex align-items-center justify-content-end gap-2">
												<span class="small">{{__('admin.question_difficulty')}}:</span>
												@if( $question->difficulty == 'easy' )
												<span class="badge bg-success">
													<i class="fas fa-leaf me-2"></i>{{ __('admin.difficulty_easy') }}
												</span>
												@elseif( $question->difficulty == 'medium' )
												<span class="badge bg-warning text-dark">
													<i class="fas fa-adjust me-2"></i>{{ __('admin.difficulty_medium') }}
												</span>
												@elseif( $question->difficulty == 'hard' )
												<span class="badge bg-danger">
													<i class="fas fa-fire me-2"></i>{{ __('admin.difficulty_hard') }}
												</span>
												@endif
											</div>
										</div>
									</div>
								</div>

								<div class="card-body p-4">
									<form id="submitQuestion" method="post"
										action="{{ route('quizzes.take_start', ['id' => $training->id, 'quiz_id' => $quiz->id]) }}">
										{!! csrf_field() !!}

										<!-- Question Text -->
										<div class="mb-4">
											<h4 class="question-text lh-base">{!! $question->question !!}</h4>
										</div>

										<!-- Answer Options -->
										<div class="answers-section">
											@if( $question->type == 'tfq' )
											<div class="row g-3">
												<div class="col-md-6">
													<div class="answer-option">
														<input type="radio" class="btn-check quiz-option" name="tfq_answer"
															id="question_answer_true" value="true" autocomplete="off">
														<label
															class="btn btn-outline-secondary btn-lg w-100 text-start quiz-option-label d-flex align-items-center"
															for="question_answer_true">
															<span class="option-number me-3">1</span>
															<i class="fas fa-check-circle me-2"></i>
															<span>{{ __('admin.tfq_statement_true') }}</span>
														</label>
													</div>
												</div>
												<div class="col-md-6">
													<div class="answer-option">
														<input type="radio" class="btn-check quiz-option" name="tfq_answer"
															id="question_answer_false" value="false" autocomplete="off">
														<label
															class="btn btn-outline-secondary btn-lg w-100 text-start quiz-option-label d-flex align-items-center"
															for="question_answer_false">
															<span class="option-number me-3">2</span>
															<i class="fas fa-times-circle me-2"></i>
															<span>{{ __('admin.tfq_statement_false') }}</span>
														</label>
													</div>
												</div>
											</div>
											@elseif( $question->type == 'mcq' )
											<div class="row g-3">
												@foreach( $question->answers->shuffle() as $index => $answer )
												<div class="col-12">
													<div class="answer-option">
														<input type="radio" class="btn-check quiz-option" name="mcq_answer"
															id="question_{{ $index }}_answer_{{ $index }}"
															value="{{ md5( $answer['text'] . '+' . $answer['status'] ) }}" autocomplete="off">
														<label
															class="btn btn-outline-primary btn-lg w-100 text-start quiz-option-label d-flex align-items-center"
															for="question_{{ $index }}_answer_{{ $index }}">
															<span class="option-number me-3">{{ chr(65 + $index) }}</span>
															<span class="flex-fill">{{ $answer['text'] }}</span>
														</label>
													</div>
												</div>
												@endforeach
											</div>
											@endif
										</div>

										<!-- Hidden Fields -->
										<input type="hidden" name="response" value="{{ $response->id }}" />
										<input type="hidden" id="answer" name="answer" value="" />

										<!-- Submit Section -->
										<div class="mt-5 pt-4 border-top">
											<div class="d-flex justify-content-between align-items-center">
												<div class="text-muted">
													<i class="fas fa-info-circle me-2"></i>
													<small>{{ __('admin.Select your answer and click Next to continue') }}</small>
												</div>
												<button id="submitBtn" type="submit" class="btn btn-primary btn-lg px-4">
													@if($current_question >= $total_questions)
													<i class="fas fa-check me-2"></i>
													{{ __('admin.quizComplete') }}
													@else
													<i class="fas fa-arrow-right me-2"></i>
													{{ __('admin.quizNextQuestion') }}
													@endif
												</button>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
</div>
@stop



@section('pagescripts')
<script type="text/javascript">
	var answer = null;

@if( $question->type == 'tfq' || $question->type == 'mcq' )
$(document).on('change', '.quiz-option', function(){
    // Remove all selected states
    // $('.quiz-option-label').removeClass('btn-primary btn-success btn-danger');
    // $('.quiz-option-label').addClass('btn-outline-primary');
    
    // Add selected state to current option
    var $label = $(this).next('.quiz-option-label');
    // $label.removeClass('btn-outline-primary btn-outline-success btn-outline-danger');
    
    @if( $question->type == 'tfq' )
    if($(this).val() === 'true') {
        // $label.addClass('btn-success');
    } else {
        // $label.addClass('btn-danger');
    }
    @else
    // $label.addClass('btn-primary');
    @endif
    
    answer = $(this).val();
    $('#answer').val(answer);
    
    // Update progress bar to reflect the upcoming progress
    var currentAnswered = {{ $answered_questions ?? 0 }};
    var totalQuestions = {{ $total_questions ?? 1 }};
    var nextProgress = ((currentAnswered + 1) / totalQuestions) * 100;
    
    $('.progress-bar').css('width', nextProgress + '%');
    $('.progress-bar').addClass('progress-bar-animated');
    
    // Add visual feedback
    $label.addClass('fade-in');
});
@endif

$(document).on('click', '#submitBtn', function(e) {
    e.preventDefault();
    
    if(answer == null || !$.trim($('#answer').val())) {
        Swal.fire({
            icon: 'warning',
            title: '<strong>{{ __("admin.cautionPlease") }}</strong>',
            html: '{!! __("admin.quizSubmitNoAnswer") !!}',
            confirmButtonText: "{!! __('admin.quizOk') !!}",
            customClass: {
                popup: 'border-0 shadow-lg',
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
    } else {
        // Add loading state
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
        $(this).prop('disabled', true);
        $('#submitQuestion').submit();
    }
});

$(document).ready(function(){
    // Add fade-in animation to the whole content
    $('.card').addClass('fade-in');
    
    // Add CSS for progress bar animation
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .progress-bar {
                transition: width 0.3s ease-in-out;
            }
            .progress-bar-animated {
                animation: progress-fill 0.5s ease-out;
            }
            @keyframes progress-fill {
                0% { opacity: 0.7; }
                100% { opacity: 1; }
            }
            .fade-in {
                animation: fadeIn 0.3s ease-in;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .time-warning {
                color: #ff9800 !important;
                animation: pulse-warning 2s infinite;
            }
            .time-danger {
                color: #f44336 !important;
                animation: pulse-danger 1s infinite;
            }
            @keyframes pulse-warning {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }
            @keyframes pulse-danger {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
        `)
        .appendTo('head');
    
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

        var timeString = ('0' + hours).slice(-2) + ":" + ('0' + minutes).slice(-2) + ":" + ('0' + seconds).slice(-2);
        timeRemain.html(timeString);
        
        // Add warning classes based on time remaining
        if(distance < 300000) { // 5 minutes
            timeRemain.addClass('time-danger');
        } else if(distance < 600000) { // 10 minutes
            timeRemain.addClass('time-warning');
        }
        
        if(distance < 0) {
            clearInterval(x);
            timeRemain.parent().parent().html('<div class="alert alert-success mb-0"><i class="fas fa-check-circle me-2"></i>{{ __('admin.availableNow') }}</div>');
        }
    }, 1000);
    @endif
    
    // Add keyboard navigation
    $(document).keydown(function(e) {
        @if( $question->type == 'tfq' )
        if(e.key === '1') {
            $('#question_answer_true').click();
        } else if(e.key === '2') {
            $('#question_answer_false').click();
        }
        @elseif( $question->type == 'mcq' )
        var optionKeys = ['1', '2', '3', '4', '5'];
        var keyIndex = optionKeys.indexOf(e.key);
        if(keyIndex !== -1) {
            $('.quiz-option').eq(keyIndex).click();
        }
        @endif
        
        if(e.key === 'Enter' && answer !== null) {
            $('#submitBtn').click();
        }
    });
});
</script>
@stop
