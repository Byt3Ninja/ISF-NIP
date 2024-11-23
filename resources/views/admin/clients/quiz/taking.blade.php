@extends('layouts.admin')
@section('style')
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
	<style type="text/css">
		#timeRemain { font-size: 16px; }
		.quiz-option-label { cursor: pointer; }
	</style>
@endsection
@section('content')
	<div class="content-wrapper">
		<div class="container-xxl flex-grow-1 container-p-y">
			<div class="content-overlay"></div>
			<div class="content-header row">
				<div class="content-header-left col-12 mb-2 mt-1">
					<div class="row breadcrumbs-top">
						<div class="col-12">
							<h5 class="content-header-title float-left pr-1 mb-0">{{__('settings.View training content')}}</h5>
							<div class="breadcrumb-wrapper col-12">
								<ol class="breadcrumb p-0 mb-0">
									<li class="breadcrumb-item"><a href="/dashboard"><i class="ti ti-home"></i></a></li>
									<li class="breadcrumb-item"> <a href="/dashboard/training/my"> {{__('admin.My courses')}} </a> </li>
									<li class="breadcrumb-item"> <a href="/dashboard/training/my/{{ $training->id }}"> {{ $training->title }} </a> </li>
									<li class="breadcrumb-item"> <a href="/dashboard/training/my/{{ $training->id }}/quiz/{{ $quiz->id }}"> {{ $training->title }} </a> </li>
									<li class="breadcrumb-item active"> {{__('admin.quizResults')}} </li>
								</ol>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="content-body">
				<section>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12">
								<div class="float-right"> <h4>Time Remaining : <span id="timeRemain" data-value="{{ $time_remaining }}"> {{ $time_remaining }} </span> </h4> </div>
							</div>
							<div class="col-lg-12 col-md-12 col-sm-12 pt-2">
								<form id="submitQuestion" method="post" action="{{ route('quiz.start', ['id' => $training->id, 'quiz_id' => $quiz->id]) }}">
									{!! csrf_field() !!}
									<div class="container-fluid w-75">
										<div class="row">
											<div class="col-sm-10 col-md-10 col-lg-10"> <h3> {!! $question->question !!} </h3> </div>
											<div class="col-sm-2 col-md-2 col-lg-2 px-0">
												<div class="text-center">
													{{__('admin.question_difficulty')}} : 
													@if( $question->difficulty == 'easy' )
													<span class="badge text-bg-success"> {{ __('admin.difficulty_easy') }}</span>
													@elseif( $question->difficulty == 'medium' )
													<span class="badge text-bg-warning">{{ __('admin.difficulty_medium') }}</span>
													@elseif( $question->difficulty == 'hard' )
													<span class="badge text-bg-danger">{{ __('admin.difficulty_hard') }}</span>
													@endif
												</div>
											</div>
										</div>
										<div class="mt-2">
											@if( $question->type == 'tfq' )
												<div class="form-group my-1">
													<input type="radio" class="d-none quiz-option" name="tfq_answer" id="question_answer_true" value="true" autocomplete="off">
													<label class="btn btn-outline-primary w-75 text-left quiz-option-label" for="question_answer_true">{{ '1. '. __('admin.tfq_statement_true') }}</label>
												</div>
												<div class="form-group my-1">
													<input type="radio" class="d-none quiz-option" name="tfq_answer" id="question_answer_false" value="false" autocomplete="off">
													<label class="btn btn-outline-primary w-75 text-left quiz-option-label" for="question_answer_false">{{ '2. '. __('admin.tfq_statement_false') }}</label>
												</div>
											@elseif( $question->type == 'mcq' )
												@foreach( $question->answers->shuffle() as $index => $answer )
													<div class="form-group my-1">
														<input type="radio" class="d-none quiz-option" name="mcq_answer" id="question_{{ $index }}_answer_{{ $index }}" value="{{ md5( $answer['text'] . '+' . $answer['status'] ) }}" autocomplete="off">
														<label class="btn btn-outline-primary w-75 text-left quiz-option-label" for="question_{{ $index }}_answer_{{ $index }}">{{ ( $index + 1 ) . '. ' . $answer['text'] }}</label>
													</div>
												@endforeach
											@endif
											<input type="hidden" name="response" value="{{ $response->id }}" />
											<input type="hidden" id="answer" name="answer" value="" />
										</div>
										<div class="form-group mt-4">
											<button id="submitBtn" type="submit" class="btn btn-dark"> {{__('admin.quizNextQuestion')}}</button>
										</div>
									</div>
								</form>
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
		var answer = null;
		@if( $question->type == 'tfq' || $question->type == 'mcq' )
		$(document).on('change', '.quiz-option', function(){
			$(this).parent().parent().find('.quiz-option:checked').parent().find('.quiz-option-label').removeClass('btn-outline-primary');
			$(this).parent().parent().find('.quiz-option:checked').parent().find('.quiz-option-label').addClass('btn-primary');

			$(this).parent().parent().find('.quiz-option:not(:checked)').parent().find('.quiz-option-label').removeClass('btn-primary');
			$(this).parent().parent().find('.quiz-option:not(:checked)').parent().find('.quiz-option-label').addClass('btn-outline-primary');
			answer = $(this).val();
			$('#answer').val( answer );
		});
		@endif

		$(document).on('click', '#submitBtn', function(e) {
			e.preventDefault();
			if( answer == null || !$.trim( $('#answer').val() ) )
			{
				Swal.fire({
					icon: 'warning',
					title: '<strong> {{ __("admin.cautionPlease") }} </strong>',
					html: '{!! __("admin.quizSubmitNoAnswer") !!}',
					confirmButtonText: "{!! __('admin.quizOk') !!}",
				});
			} else { $('#submitQuestion').submit(); }
		})

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
					timeRemain.parent().html('<span class="badge text-bg-success"> {{ __('admin.availableNow') }} </span>');
				}
			}, 1000);
			@endif
		});
	</script>
@endsection
