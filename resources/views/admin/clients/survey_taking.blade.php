@extends('layouts.admin')
@section('style')
<link rel="stylesheet" type="text/css"
	href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
<style type="text/css">
	#timeRemain {
		font-size: 16px;
	}

	.survey-option-label {
		cursor: pointer;
	}

	div.stars {
		display: inline-block;
		text-align: end;
		direction: ltr;
		padding-block: 2px !important;
	}

	div.stars .reviewFaceFeedBack {
		font-size: 12px;
		padding-top: 3px;
	}

	input.star {
		display: none;
	}

	label.star {
		padding: 0 3px;
		font-size: 22px;
		color: #ff9f43;
		transition: all .2s
	}

	input.star:checked~label.star:before {
		content: '\f006';
		color: #FD4;
		transition: all .25s
	}

	input.star-5:checked~label.star:before {
		/* text-shadow: 0 0 8px #090503; */
	}

	input.star-5:checked~label.star:before,
	span.feedback-star-5 {
		color: #198754 !important;
		font-weight: 900;
	}

	input.star-4:checked~label.star:before,
	span.feedback-star-4 {
		color: #7ace4c !important;
		font-weight: 900;
	}

	input.star-3:checked~label.star:before,
	span.feedback-star-3 {
		color: #343a40 !important;
		font-weight: 900;
	}

	input.star-2:checked~label.star:before,
	span.feedback-star-2 {
		color: #ffc107 !important;
		font-weight: 900;
	}

	input.star-1:checked~label.star:before,
	span.feedback-star-1 {
		color: #dc3545 !important;
		font-weight: 900;
	}

	label.star:hover {
		transform: rotate(-15deg) scale(1.3)
	}

	label.star:before {
		content: '\f006';
		font-family: 'Font Awesome 6 Free'
	}

	.block-danger {
		box-shadow: 0px 0px 20px 3px #ff4c51 !important;
	}

	div.card .survey-question-block {
		display: none;
	}

	div.block-danger .survey-question-block {
		display: block;
	}
</style>
@endsection

@php
	$missingQuestions = [];
	if( session()->has('missingQuestions') ) { $missingQuestions = session()->get('missingQuestions'); }
@endphp

@section('content')
<div class="">
	<div class="">
		<div class="content-overlay"></div>
		<div class="content-header row">
			{{-- <h2 class="content-header-title"> {{ __('dashboard.Survey') }}</h2> --}}
			@if( session()->has('success') )
				<x-alert type="success" message="{{ __('dashboard.'. session()->get('success')) }}" />
			@endif
			@if( session()->has('error') )
				<x-alert type="danger" message="{{ __('dashboard.'. session()->get('error')) }}" />
			@endif
		</div>
		<div class="content-body" style="margin-bottom: 4rem;">
			@if( $is_responded == false )
			<div class="card">
				<div class="card-header">
					<h3 class="card-title"> {{ $survey->title }} </h3>
					<small class="text-muted">{{ $survey->description }}</small>
				</div>
			</div>
			<form id="submitQuestion" method="post" action="{{ route('surveys.submit', ['id' => $responses->id]) }}">
				{!! csrf_field() !!}
				@foreach( $my_responses as $question_index => $response )
				<div class="card my-4 {{ in_array( 'question_' . $response['id'], $missingQuestions) ? 'block-danger' : '' }}"
					id="survey-question-{{ $question_index }}">
					<div class="card-body" style="direction: {{ $l == 'ar' ? 'rtl' : 'ltr' }}">
						<div class="d-flex align-items-center gap-3 pb-2 border-bottom">
							<span class="badge bg-primary rounded-circle d-flex justify-content-center align-items-center"
								style="width: 30px; height: 30px;">
								{{ $question_index + 1 }}
							</span>
							<h5 class="fw-semibold mb-0">{{ $response['question'] }}</h5>
						</div>
						<div class="my-3">
							@if( $response['type'] == 'stars' )
								@php
									$answers = $response['answers'];
									for( $i = $answers->count(); $i < 5; $i++ ) { $answers->push(['text' => null]); }
									foreach( $answers as $index => $answer ) { $answers[$index] = $answer['text'] ?? ''; }
								@endphp
								<div class="h-auto d-flex justify-content-center align-items-center py-4" style="width: fit-content;">
									<div class='stars form-control bg-label-secondary d-flex justify-content-center align-items-center' id="review_stars_{{$question_index}}">
										@for ($i = 5; $i > 0; $i--)
											<input 
												class="star star-{{ $i }}" 
												id="star-{{ $question_index }}-{{ $i }}" 
												type="radio"
												name="question_{{ $response['id'] }}" 
												value="{{ $i }}" 
												data-text="{{ $answers[$i - 1] }}" 
											/>
											<label 
												class="star star-{{ $i }}" 
												for="star-{{ $question_index }}-{{ $i }}"
											></label>
										@endfor
										<span class='float-end reviewFaceFeedBack text-nowrap ms-3'></span>
									</div>
								</div>
								@elseif( $response['type'] == 'radio' )
									@foreach( $response['answers'] as $index => $answer )
										<div class="form-check mb-3">
											<input 
												type="radio" 
												class="form-check-input survey-option" 
												name="question_{{ $response['id'] }}"
												id="question_{{ $question_index }}_answer_{{ $index }}" 
												value="{{ $index }}"
												autocomplete="off" 
											/>
											<label 
												class="form-check-label survey-option-label"
												for="question_{{ $question_index }}_answer_{{ $index }}"
											>
												{{ $answer['text'] }}
											</label>
										</div>
									@endforeach
								@elseif( $response['type'] == 'answers' )
									<div class="row">
										<div class="col-12 col-md-6">
											<textarea 
												name="question_{{ $response['id'] }}"
												id="question_{{ $question_index }}_answer_1"
												class="form-control">
											</textarea>
										</div>
									</div>
								@endif
						</div>
						<div class="text-danger survey-question-block">{{ __('dashboard.surveyQuestionRequired') }}</div>
					</div>
				</div>
				@endforeach
				
				@if (count($my_responses) > 0)
					<div class="form-group mt-6 mb-16 w-100 d-flex justify-content-end">
						<button id="submitBtn" type="submit" class="btn btn-primary w-px-100"> {{__('admin.Save')}}</button>
					</div>
				@endif
			</form>
			@else
			<div class="card">
				<div class="card-header text-center">
					<h3 class="card-title"> {{ __('dashboard.respondedAlready') }} </h3>
				</div>
			</div>
			@endif
		</div>
	</div>
</div>
@endsection

@section('pagescripts')
<script type="text/javascript">
	var answer = null;
		$('input.star').prop('checked', false);
		// $(document).on('change', '.survey-option', function(){
		// 	$(this).parent().parent().find('.survey-option:checked').parent().find('.survey-option-label').removeClass('btn-outline-dark');
		// 	$(this).parent().parent().find('.survey-option:checked').parent().find('.survey-option-label').addClass('btn-dark');

		// 	$(this).parent().parent().find('.survey-option:not(:checked)').parent().find('.survey-option-label').removeClass('btn-dark');
		// 	$(this).parent().parent().find('.survey-option:not(:checked)').parent().find('.survey-option-label').addClass('btn-outline-dark');
		// 	$(this).parent().parent().parent().parent().parent().parent().parent().removeClass('block-danger');
		// 	answer = $(this).val();
		// 	$('#answer').val( answer );
		// });

		function handleValue( r, el ) {
			var text = el.data('text');
			if( r == 1 ) { el.parent().find('.reviewFaceFeedBack').html("<span style='color: #dc3545 !important;'> " + text + " <i class='fas fa-fw fa-angry'></i> </span>"); }
			if( r == 2 ) { el.parent().find('.reviewFaceFeedBack').html("<span style='color: #ffc107 !important;'> " + text + " <i class='fas fa-fw fa-frown'></i> </span>"); }
			if( r == 3 ) { el.parent().find('.reviewFaceFeedBack').html("<span style='color: #343a40 !important;'> " + text + " <i class='fas fa-fw fa-meh'></i> </span>"); }
			if( r == 4 ) { el.parent().find('.reviewFaceFeedBack').html("<span style='color: #7ace4c !important;'> " + text + " <i class='fas fa-fw fa-smile'></i> </span>"); }
			if( r == 5 ) { el.parent().find('.reviewFaceFeedBack').html("<span style='color: #198754 !important;'> " + text + " <i class='fas fa-fw fa-laugh'></i> </span>"); }
		}

		$(document).on('change', 'input.star', function(e) {
			var r = parseInt( $(this).val() );
			handleValue( r, $(this) );
			$(this).parent().parent().parent().parent().parent().parent().parent().removeClass('block-danger');
		});
</script>
@endsection