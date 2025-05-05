@php
	$quiz_questions = !empty( $quiz->questions ) ? $quiz->questions->pluck('id')->toArray() : [];
@endphp
@extends('layouts.admin')
@section('title', __('admin.quizzes') )
@section('index_url', route('quizzes.index') )
@section('sub_index_url', route('quizzes.edit', ['quiz' => $quiz->id]) )
@section('subTitle', __('admin.manageQuestions'))
@section('style')
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
	<style type="text/css">
		.custom-px-1 { padding-inline: 0.25rem; }
		.custom-py-1 { padding-block: 0.25rem; }
		.custom-px-2 { padding-inline: 0.5rem; }
		.custom-py-2 { padding-block: 0.5rem; }
		.btn i.fa { top: 0px !important; }
		.btn { padding: 5px 10px !important; }
		#previewArea, #modelPreviewArea { border: 1px solid #a8a3a3; border-radius: 5px; }
	</style>
@stop
@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="content-overlay"></div>
		
		<div class="content-header row">
			@if( session()->has('success') )
				<div class="col-md-12">
					<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						<div class="d-flex align-items-center"> <i class="ti ti-thumb-up me-3 ti-md"></i> <span> {{ __('admin.'. session()->get('success')) }} </span> </div>
					</div>
				</div>
			@endif
			@if( session()->has('error') )
				<div class="col-md-12">
					<div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						<div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span>{{ __('admin.'.session()->get('error')) }} </span> </div>
					</div>
				</div>
			@endif
		</div>
		<div class="content-body">
			<!-- BuilderForm start -->
			<section id="basic-input">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header row">
								<div class="col-md-8 col-lg-8 col-sm-8"> <h4 class="card-title">{{__('admin.manageQuestions')}}</h4> </div>
								<div class="col-md-4 col-lg-4 col-sm-4 text-right">
									<button id="addQuizQuestions" class="btn btn-md btn-dark"> <i class="pt-1 pe-2 fa fa-plus-circle"></i> {{ __('admin.addQuestion') }} </button>
								</div>
							</div>
							<div class="card-content">
								<div class="card-body">
									@if( session()->has('msg') )
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-danger mb-2" role="alert">
												<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
												<div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span> {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
											</div>
										</div>
									</div>
									@endif
									<!-- Validation messages -->
									@if(count($errors) >0)
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-danger mb-2" role="alert">
												<ul>
													@foreach($errors->all() as $error)
													<li>{{ $error }} </li>
													@endforeach
												</ul>
											</div>
										</div>
									</div>
									@endif
									<form method="post" action="{{ route('quizzes.questions_update', ['quiz' => $quiz->id]) }}">
										{!! csrf_field() !!}
										<div class="row">
											<div class="col-lg-8 col-md-8 col-sm-12">
												<table class="table table-bordered table-hover">
													<thead>
														<th class="w-75">{{ __('admin.Questions') }}</th>
														<th class="w-25 text-center"> {{ __('admin.Actions') }} </th>
													</thead>
													<tbody id="questions_table">
														@foreach( $quiz->questions as $question )
														<tr>
															<td> <i class="fa fa-sort"></i> {{ $question->question }} <input type="hidden" name="questions_ids[]" value="{{ $question->id }}" /> </td>
															<td class="px-1">
																<div class="btn-group w-100">
																	<button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> {{ __('admin.Actions') }} </button>
																	<div class="dropdown-menu">
																		<a href="#" data-id="{{ $question->id }}" class="preview-question dropdown-item"> <i class="fa fa-eye"></i> {{ __('admin.Preview') }} </a>
																		<a href="{{ route('questions-bank.edit', ['questions_bank' => $question->id]) }}" target="_blank" data-id="{{ $question->id }}" class="edit-question dropdown-item"> <i class="fa fa-edit"></i> {{ __('admin.Edit') }} </a>
																		<a href="#" data-id="{{ $question->id }}" class="remove-question dropdown-item"> <i class="fa fa-trash-o"></i> {{ __('admin.Delete') }} </a>
																	</div>
																</div>
															</td>
														</tr>
														@endforeach
													</tbody>
												</table>
											</div>

											<div class="col-lg-4 col-md-4 col-sm-12">
												<div class="py-1 px-2" id="previewArea">
													<div class="text-center"> {{ __('admin.previewAreaPlaceholder') }} </div>
												</div>
											</div>
										</div>
										<div class="row mt-2">
											<div class="col-12 d-flex justify-content-end">
 												<button type="submit" class="btn btn-primary mr-1 mb-1">{{__('admin.Save')}}</button>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- BuilderForm end -->
		</div>
	</div>
</div>
<!-- END: Content-->
<div class="modal fade" id="questionsModal" tabindex="-1" role="dialog" aria-labelledby="modalScrollableTitle" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalScrollableTitle"> {{ __('admin.questionPicker') }} </h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-sm-3">
							<div class="form-group m-0">
								<label for="filter_keywords"> {{ __('admin.Keywords') }} </label>
								<input class="form-control" type="text" value="" name="filter_keywords" id="filter_keywords" />
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group m-0">
								<label for="filter_difficulty"> {{ __('admin.question_difficulty') }} </label>
								<select class="form-control form-select" id="filter_difficulty" name="filter_difficulty">
									<option value="all"> {{ __('admin.All') }} </option>
									<option value="easy"> {{ __('admin.difficulty_easy') }} </option>
									<option value="medium"> {{ __('admin.difficulty_medium') }} </option>
									<option value="hard"> {{ __('admin.difficulty_hard') }} </option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group m-0">
								<label for="filter_type"> {{ __('admin.question_Type') }} </label>
								<select class="form-control form-select" id="filter_type" name="filter_type">
									<option value="all"> {{ __('admin.All') }} </option>
									<option value="mcq"> {{ __('admin.type_mcq') }} </option>
									<option value="tfq"> {{ __('admin.type_tfq') }} </option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group m-0">
								<label for="filter_tags"> {{ __('admin.questionTags') }} </label>
								<select class="form-control form-select" id="filter_tags" name="filter_tags">
									<option value="all"> {{ __('admin.All') }} </option>
									@foreach($tags as $tag)
										<option value="{{ trim( $tag ) }}"> {{ trim( $tag ) }} </option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-8 col-lg-8 col-sm-8">
							<div class="table-responsive">
								<table class="table table-bordered table-hover">
									<thead>
										<tr>
											<th> {{__('admin.question')}} </th>
											<th class="text-center px-0"> {{__('admin.question_difficulty')}} </th>
											<th class="text-center px-0"> {{__('admin.question_Type')}} </th>
											<th class="text-center px-0"> {{__('admin.Actions')}} </th>
										</tr>
									</thead>
									<tbody id="question-picker-tbody">
										@foreach( $questions as $question )
										<tr class="question-picker-row" data-qid="{{ $question->id }}">
											<td> {{ $question->question }} </td>
											<td class="text-center">
												@if( $question->difficulty == 'easy' )
												<span class="badge text-bg-success rounded-pill"> {{ __('admin.difficulty_easy') }}</span>
												@elseif( $question->difficulty == 'medium' )
												<span class="badge text-bg-warning rounded-pill">{{ __('admin.difficulty_medium') }}</span>
												@elseif( $question->difficulty == 'hard' )
												<span class="badge text-bg-danger rounded-pill">{{ __('admin.difficulty_hard') }}</span>
												@endif
											</td>
											<td class="text-center">
												<span class="badge text-bg-secondary rounded-pill">
													{{ $question->type == 'mcq' ? __('admin.type_mcq') : ( $question->type == 'tfq' ? __('admin.type_tfq') : '-') }}
												</span>
											</td>
											<td class="text-center">
												<button data-id="{{ $question->id }}" class="btn btn-sm btn-success add-quiz-question"> {{ __('admin.Add') }} </button>
											</td>
										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div class="col-md-4 col-lg-4 col-sm-4">
							<div class="py-1 px-2" id="modelPreviewArea">
								<div class="text-center"> {{ __('admin.previewAreaPlaceholder') }} </div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-md btn-dark" data-bs-dismiss="modal">
					
					<span class="d-none d-sm-block">{{__('messages.Close')}}</span>
				</button>
			</div>
		</div>
	</div>
</div>
@stop
@section('pagescripts')
	<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>
	<script src="https://code.jquery.com/ui/1.14.0/jquery-ui.min.js" integrity="sha256-Fb0zP4jE3JHqu+IBB9YktLcSjI1Zc6J2b6gTjB0LpoM=" crossorigin="anonymous"></script>
	<script type="text/javascript">
		var questions = @json( $questions );
		var selected_questions = @json( $quiz_questions );
		$("#questions_table").sortable();

		$(document).on('click', '#addQuizQuestions', function(e) { e.preventDefault(); handleFilterTable(); $("#questionsModal").modal('show'); });
		$(document).on('click', '.preview-question', function(e) {
			e.preventDefault();
			var questionId = $(this).data('id');
			var question = questions.filter(e => e.id == questionId)[0];
			if( typeof( question ) !== 'undefined' )
			{
				html = questionPreviewLoader( question );
				$("#previewArea").html(html);
			} else { $("#previewArea").html('<div class="text-center"> <i class="fa fa-times-circle"></i> {{ __("admin.questionNotFound") }} </div>'); }
		});
		$(document).on('click', '.remove-question', function(e) { e.preventDefault(); });

		$(document).on('click', '.question-picker-row', function(e){
			e.preventDefault();
			var questionId = $(this).data('qid');
			var question = questions.filter(e => e.id == questionId)[0];
			if( typeof( question ) !== 'undefined' )
			{
				html = questionPreviewLoader( question );
				$("#modelPreviewArea").html( html );
			} else { $("#modelPreviewArea").html('<div class="text-center"> <i class="fa fa-times-circle"></i> {{ __("admin.questionNotFound") }} </div>'); }
		});

		$(document).on('input', '#filter_keywords', function(e) { handleFilterTable(); });
		$(document).on('input', '#filter_difficulty', function(e) { handleFilterTable(); });
		$(document).on('input', '#filter_type', function(e) { handleFilterTable(); });
		$(document).on('input', '#filter_tags', function(e) { handleFilterTable(); });

		$(document).on('click', '.add-quiz-question', function(e){
			e.preventDefault();
			var questionId = $(this).data('id');
			var question = questions.filter(e => e.id == questionId)[0];
			if( typeof( question ) !== 'undefined' )
			{
				selected_questions.push( questionId );
				var base_url = window.location.origin;
				var question_row = `<tr>
					<td> <i class="fa fa-sort"></i> ` + question.question + ` <input type="hidden" name="questions_ids[]" value="` + question.id + `" /> </td>
					<td class="px-1">
						<div class="btn-group w-100">
							<button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> {{ __('admin.Actions') }} </button>
							<div class="dropdown-menu">
								<a href="#" data-id="` + question.id + `" class="preview-question dropdown-item"> <i class="fa fa-eye"></i> {{ __('admin.Preview') }} </a>
								<a href="` + base_url + `/dashboard/questions-bank/` + question.id + `/edit" target="_blank" data-id="` + question.id + `" class="edit-question dropdown-item"> <i class="fa fa-edit"></i> {{ __('admin.Edit') }} </a>
								<a href="#" data-id="` + question.id + `" class="remove-question dropdown-item"> <i class="fa fa-trash-o"></i> {{ __('admin.Delete') }} </a>
							</div>
						</div>
					</td>	
				</tr>`;
				$("#questions_table").append( question_row );
				$(this).parent().parent().remove();
				if( $("#question-picker-tbody > tr").length <= 0 )
				{
					$("#question-picker-tbody").html('<tr> <td colspan=4 class="text-center"> {{ __("admin.noResultsMatch") }} </td></tr>');
				}
			}
		});
		function questionPreviewLoader( question )
		{
			var html = '<div class="fs-5 fw-bold">' + question.question + '</div>';
			if( question.type == 'mcq' )
			{
				var answers = question.answers;
				question.answers.forEach( function(item, key){
					var count = key + 1;
					var type = item.status == true ? 'btn-outline-success' : 'btn-outline-secondary';
					html += '<div class="custom-py-1"> <button class="w-100 text-left btn ' + type + '">' + count + '. ' + item.text + '</button> </div>';
				});
			}
			else if( question.type == 'tfq' )
			{
				var type_true = question.correct == true ? 'btn-outline-success' : 'btn-outline-secondary';
				var type_false = question.correct == false ? 'btn-outline-success' : 'btn-outline-secondary';
				html += '<div class="custom-py-1"> <button class="w-100 text-left btn ' + type_true + '">1. {{ __('admin.tfq_statement_true') }} </button> </div>';
				html += '<div class="custom-py-1"> <button class="w-100 text-left btn ' + type_false + '">2. {{ __('admin.tfq_statement_false') }} </button> </div>';
			}
			return html;
		}

		function handleFilterTable()
		{
			var keyword = $.trim( $('#filter_keywords').val() );
			var difficulty = $('#filter_difficulty').val();
			var type = $('#filter_type').val();
			var tags = $.trim( $('#filter_tags').val() );

			var filtered_questions = questions.filter(question => {
				var match = true;
				if( keyword != '' && question.question.toLowerCase().indexOf(keyword.toLowerCase()) == -1 ) { match = false; }
				if( difficulty != 'all' && question.difficulty != difficulty ) { match = false; }
				if( type != 'all' && question.type != type ) { match = false; }
				if( tags != 'all' && question.tags.indexOf( tags ) == -1 ) { match = false; }
				return match;
			});
			
			var html = '';
			filtered_questions.forEach(function(question, key) {
				if( selected_questions.includes( question.id ) ) { /* */ }
				else
				{
					var question_type = '';
					if( question.type == 'mcq' ) { question_type = '{{ __("admin.type_mcq") }}'; }
					if( question.type == 'tfq' ) { question_type = '{{ __("admin.type_tfq") }}'; }
					var difficulty = '';
					if( question.difficulty == 'easy' ) { difficulty = '<span class="badge text-bg-success rounded-pill"> {{ __("admin.difficulty_easy") }}</span>'; }
					if( question.difficulty == 'medium' ) { difficulty = '<span class="badge text-bg-warning rounded-pill">{{ __("admin.difficulty_medium") }}</span>'; }
					if( question.difficulty == 'hard' ) { difficulty = '<span class="badge text-bg-danger rounded-pill">{{ __("admin.difficulty_hard") }}</span>'; }
	
					html += `<tr class="question-picker-row" data-qid="` + question.id + `">
								<td> ` + question.question + ` </td>
								<td class="text-center"> ` + difficulty + ` </td>
								<td class="text-center"> <span class="badge text-bg-secondary rounded-pill"> ` + question_type + ` </span> </td>
								<td class="text-center">
									<button data-id="` + question.id + `" class="btn btn-sm btn-success add-quiz-question"> {{ __('admin.Add') }} </button>
								</td>
							</tr>`;
				}
			});
			if( filtered_questions.length > 0 ) { $("#question-picker-tbody").html( html ); }
			else { $("#question-picker-tbody").html('<tr> <td colspan=4 class="text-center"> {{ __("admin.noResultsMatch") }} </td></tr>'); }
		}
	</script>
@stop
