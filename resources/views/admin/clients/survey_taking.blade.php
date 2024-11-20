@extends('layouts.admin')
@section('style')
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
	<style type="text/css">
		#timeRemain { font-size: 16px; }
		.survey-option-label { cursor: pointer; }
		div.stars { display: inline-block; text-align: end; direction: ltr; padding-block: 2px !important;}
		div.stars .reviewFaceFeedBack { font-size: 12px; padding-top: 3px; }
		input.star { display: none; }
		label.star { padding: 0 3px; font-size: 16px; color: #fcc100; transition: all .2s }
		input.star:checked~label.star:before { content: '\f006'; color: #FD4; transition: all .25s }
		input.star-5:checked~label.star:before { text-shadow: 0 0 8px #090503; }
		input.star-5:checked~label.star:before, span.feedback-star-5 { color: #198754 !important; font-weight: 900; }
		input.star-4:checked~label.star:before, span.feedback-star-4 { color: #7ace4c !important; font-weight: 900; }
		input.star-3:checked~label.star:before, span.feedback-star-3 { color: #343a40 !important; font-weight: 900; }
		input.star-2:checked~label.star:before, span.feedback-star-2 { color: #ffc107 !important; font-weight: 900; }
		input.star-1:checked~label.star:before, span.feedback-star-1 { color: #dc3545 !important; font-weight: 900; }
		label.star:hover { transform: rotate(-15deg) scale(1.3) }
		label.star:before { content: '\f006'; font-family: 'Font Awesome 6 Free' }
		.block-danger { box-shadow: 0px 0px 20px 3px #ff4c51 !important; }
		div.card .survey-question-block { display: none; }
		div.block-danger .survey-question-block { display: block; }
	</style>
@endsection
@php
	$missingQuestions = [];
	if( session()->has('missingQuestions') ) { $missingQuestions = session()->get('missingQuestions'); }
@endphp
@section('content')
	<div class="content-wrapper">
		<div class="container-xxl flex-grow-1 container-p-y">
			<div class="content-overlay"></div>
			<div class="content-header row">
				@if( session()->has('success') )
					<div class="col-md-12">
						<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
							{{ __('dashboard.'. session()->get('success')) }} 
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					</div>
				@endif
				@if( session()->has('error') )
					<div class="col-md-12">
						<div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
							{{ __('dashboard.'.session()->get('error')) }}
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					</div>
				@endif
			</div>
			<div class="content-body">
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
					<div class="card my-10 {{ in_array( 'question_' . $response['id'], $missingQuestions) ? 'block-danger' : '' }}" id="survey-question-{{ $question_index }}">
						<div class="card-body">
							<div class="container-fluid">
								<div class="row">
									<div class="col-lg-12 col-md-12 col-sm-12 pt-2">
										<h5> {{ ($question_index + 1) . '. ' . $response['question'] }} </h5>
										<div class="my-3">
											@if( $response['type'] == 'stars' )
												@php
													$answers = $response['answers'];
													for( $i = $answers->count(); $i < 5; $i++ ) { $answers->push(['text' => null]); }
													foreach( $answers as $index => $answer ) { $answers[$index] = $answer['text'] ?? ''; }
												@endphp
												<div class='stars form-control px-1' id="review_stars_{{$question_index}}">
													@for( $i = 5; $i > 0; $i-- )
													<input class="star star-{{$i}}" id="star-{{$question_index}}-{{$i}}" type="radio" name="question_{{ $response['id'] }}" value="{{$i}}" data-text="{{ $answers[$i-1] }}" />
													<label class="star star-{{$i}}" for="star-{{$question_index}}-{{$i}}"></label>
													@endfor
													<span class='float-end reviewFaceFeedBack px-3'></span>
												</div>
											@elseif( $response['type'] == 'radio' )
												@foreach( $response['answers'] as $index => $answer )
												<div class="form-group my-2">
													<input type="radio" class="d-none survey-option" name="question_{{ $response['id'] }}" id="question_{{ $question_index }}_answer_{{ $index }}" value="{{ $index }}" autocomplete="off" />
													<label class="btn btn-outline-dark w-50 text-left survey-option-label" for="question_{{ $question_index }}_answer_{{ $index }}">{{ ( $index + 1 ) . '. ' . $answer['text'] }}</label>
												</div>
												@endforeach
											@endif
										</div>
										<div class="text-danger survey-question-block">{{ __('dashboard.surveyQuestionRequired') }}</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					@endforeach
					<div class="form-group mt-6">
						<button id="submitBtn" type="submit" class="btn btn-dark"> {{__('admin.Save')}}</button>
					</div>
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
		$(document).on('change', '.survey-option', function(){
			$(this).parent().parent().find('.survey-option:checked').parent().find('.survey-option-label').removeClass('btn-outline-dark');
			$(this).parent().parent().find('.survey-option:checked').parent().find('.survey-option-label').addClass('btn-dark');

			$(this).parent().parent().find('.survey-option:not(:checked)').parent().find('.survey-option-label').removeClass('btn-dark');
			$(this).parent().parent().find('.survey-option:not(:checked)').parent().find('.survey-option-label').addClass('btn-outline-dark');
			$(this).parent().parent().parent().parent().parent().parent().parent().removeClass('block-danger');
			answer = $(this).val();
			$('#answer').val( answer );
		});

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
