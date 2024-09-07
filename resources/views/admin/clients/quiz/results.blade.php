@extends('layouts.admin')
@section('style')
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
	<style type="text/css">
		.training-image { border-radius: 50%; border: 3px solid #ccc; padding: 0.2rem; }
		.w-15 { width: 15%; }
		.text-sm { font-size: 18px !important; }
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
									<li class="breadcrumb-item"> <a href="/dashboard/training/my/{{ $training->id }}/quiz/{{ $quiz->id }}"> {{ $quiz->title }} </a> </li>
									<li class="breadcrumb-item active"> {{ __('admin.Results') }} </li>
								</ol>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="content-body">
				<section id="multiple-column-form">
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-12 col-lg-12 col-sm-12">
								<div class="card">
									<div class="card-content">
										<div class="card-body">
											<div class="accordion" id="resultsControllers">
												@foreach( $results as $key => $response )
												@php $question = $response->question; @endphp
												<div class="card my-0">
													<div class="card-header px-0">
														<h2 class="mb-0">
															<button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#element_{{ $key }}_container" aria-expanded="false" aria-controls="contentsContainer">
																{{ $question->question }}
															</button>
															<span class="float-right text-sm px-2">
																@if( $response->status == true )
																	<i data-toggle="tooltip" data-placement="top" title="{{ __('admin.quizCorrectAnswer') }}" class="bx bxs-check-circle success"></i>
																@else
																	<i data-toggle="tooltip" data-placement="top" title="{{ __('admin.quizWrongAnswer') }}" class="bx bxs-x-circle danger"></i>
																@endif
															</span>
														</h2>
													</div>
													<div id="element_{{ $key }}_container" class="collapse" data-parent="#resultsControllers">
														<div class="card-body py-1">
															<ul class="widget-timeline mb-0">
																@if( $question->type == 'tfq' )
																	@php
																		$correct = $question->correct;
																		$answer = $response->response === 1 || $response->response === "1" || $response->response === "true" || $response->response === true ? true : false;
																		$true_class = 'btn-outline-primary';
																		$false_class = 'btn-outline-primary';
																		if( $correct == true )
																		{
																			$true_class = 'btn-outline-success';
																			if( $correct == $answer ) { $true_class = 'btn-success'; } else { $false_class = 'btn-danger'; }
																		}
																		if( $correct == false )
																		{
																			$false_class = 'btn-outline-success';
																			if( $correct == $answer ) { $false_class = 'btn-success'; } else { $true_class = 'btn-danger'; }
																		}
																	@endphp
																	<div class="form-group my-1">
																		<input type="radio" class="d-none quiz-option" name="tfq_answer" id="question_answer_true" value="true" autocomplete="off">
																		<label class="btn {{ $true_class }} w-75 text-left quiz-option-label" for="question_answer_true">{{ '1. '. __('admin.tfq_statement_true') }}</label>
																	</div>
																	<div class="form-group my-1">
																		<input type="radio" class="d-none quiz-option" name="tfq_answer" id="question_answer_false" value="false" autocomplete="off">
																		<label class="btn {{ $false_class }} w-75 text-left quiz-option-label" for="question_answer_false">{{ '2. '. __('admin.tfq_statement_false') }}</label>
																	</div>
																@elseif( $question->type == 'mcq' )
																	@foreach( $question->answers as $index => $answer )
																		@php
																			$btn_class = 'btn-outline-primary';
																			$value = md5( $answer['text'] . '+' . $answer['status'] );
																			$_answer = $response->response;
																			if( $answer['status'] == true ) { $btn_class = 'btn-outline-success'; }
																			if( $_answer == $value && $answer['status'] == true ) { $btn_class = 'btn-success'; }
																			else
																			{
																				if( $_answer == $value ) { $btn_class = 'btn-danger'; }
																			}
																		@endphp
																		<div class="form-group my-1">
																			<input type="radio" class="d-none quiz-option" name="mcq_answer" id="question_{{ $index }}_answer_{{ $index }}" value="{{ $value }}" autocomplete="off">
																			<label class="btn {{ $btn_class }} w-75 text-left quiz-option-label" for="question_{{ $index }}_answer_{{ $index }}">{{ ( $index + 1 ) . '. ' . $answer['text'] }}</label>
																		</div>
																	@endforeach
																@endif
															</ul>
														</div>
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
		$(function () { $('[data-toggle="tooltip"]').tooltip() });
		$(document).on('change', '.quiz-option', function(e) { e.preventDefault(); return false; });
	</script>
@endsection
