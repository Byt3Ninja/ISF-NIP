@extends('layouts.admin')
@section('title', __('admin.surveys') )
@section('index_url', route('surveys.index') )
@section('subTitle', __('admin.AddNewSurvey'))
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
		.custom-star-rating, .custom-star-rating-done { list-style: none; display: block ruby; }
		.custom-star-rating i.fas { cursor: pointer; }
		ul.custom-star-rating:hover li { color: #fd7e14; }
		ul.custom-star-rating:hover li:hover ~ li{ color: #a3a3a3; }
		ul.custom-star-rating li { color: #a3a3a3; }
	</style>
@stop
@section('content')
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="content-overlay"></div>
		
		<div class="content-body">
			<section id="basic-input">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">{{__('admin.AddNewSurvey')}}</h4>
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
									@if( count( $errors ) > 0 )
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
									<form method="post" action="{{ route('surveys.store') }}">
										{!! csrf_field() !!}
										<div class="row">
											<div class="col-lg-12 col-md-12 col-sm-12">
												<fieldset class="form-group">
													<label for="organization_id">{{ __('admin.Organization')}}</label>
													<select class="form-control form-select" name="organization_id" id="organization_id" required>
														@foreach( $organizations as $organization )
															<option value="{{ $organization->id }}">{{ $organization->{'name_' . $l} }}</option>
														@endforeach
													</select>
												</fieldset>
											</div>
										</div>
										<div class="row my-6">
											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="title">{{ __('admin.Title')}}</label>
													<input value="" name="title" type="text" class="form-control" id="title" placeholder="{{ __('admin.Title') }}" required />
												</fieldset>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="description">{{ __('admin.Description')}}</label>
													<input value="" name="description" type="text" class="form-control" id="description" placeholder="{{ __('admin.Description') }}" required />
												</fieldset>
											</div>
										</div>
										<hr class="hr my-3" />
										<div class="">
											<div class="card-header row">
												<div class="col-md-8 col-lg-8 col-sm-8"> <h4 class="card-title">{{__('admin.manageQuestions')}}</h4> </div>
												<div class="col-md-4 col-lg-4 col-sm-4 text-right">
													<button id="addSurveyQuestions" class="btn btn-md btn-dark"> <i class="pt-1 pe-2 fa fa-plus-circle"></i> {{ __('admin.addQuestion') }} </button>
												</div>
											</div>
											<div class="card-content">
												<div class="card-body">
													<div class="row">
														<div class="col-lg-8 col-md-8 col-sm-12">
															<table class="table table-bordered table-hover">
																<thead>
																	<th>{{ __('admin.Questions') }}</th>
																	<th class="text-center px-0"> {{__('admin.question_Type')}} </th>
																	<th class="w-25 text-center"> {{ __('admin.Actions') }} </th>
																</thead>
																<tbody id="questions_table"></tbody>
															</table>
														</div>
			
														<div class="col-lg-4 col-md-4 col-sm-12">
															<div class="py-1 px-2" id="previewArea">
																<div class="text-center"> {{ __('admin.previewAreaPlaceholder') }} </div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-12 d-flex justify-content-end">
												<button type="submit" class="btn btn-primary mr-1 mb-1">{{__('admin.Save')}}</button>
												<a href="{{ route('surveys.index') }}" class="btn btn-secondary mb-1 mx-2">{{__('admin.Cancel')}}</a>
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
								<label for="filter_type"> {{ __('admin.question_Type') }} </label>
								<select class="form-control form-select" id="filter_type" name="filter_type">
									<option value="all"> {{ __('admin.All') }} </option>
									<option value="stars"> {{ __('admin.surveyTypeStarRating') }} </option>
									<option value="radio"> {{ __('admin.surveyTypeRadioButtons') }} </option>
									<option value="answers"> {{ __('admin.surveyQuestionAndAnswer') }} </option>
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
											<th class="text-center px-0"> {{__('admin.question_Type')}} </th>
											<th class="text-center px-0"> {{__('admin.Actions')}} </th>
										</tr>
									</thead>
									<tbody id="question-picker-tbody">
										@foreach( $questions as $question )
										<tr class="question-picker-row" data-qid="{{ $question->id }}">
											<td> {{ $question->question }} </td>
											<td class="text-center">
												<span class="badge text-bg-secondary rounded-pill">
													{{ $question->survey_type == 'stars' ? __('admin.surveyTypeStarRating') : ( $question->survey_type == 'radio' ? __('admin.surveyTypeRadioButtons') : __('admin.surveyQuestionAndAnswer')) }}
												</span>
											</td>
											<td class="text-center">
												<button data-id="{{ $question->id }}" class="btn btn-sm btn-success add-survey-question"> {{ __('admin.Add') }} </button>
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
	<script src="/js/jquery.datetimepicker.full.min.js"></script>
	<script src="https://code.jquery.com/ui/1.14.0/jquery-ui.min.js" integrity="sha256-Fb0zP4jE3JHqu+IBB9YktLcSjI1Zc6J2b6gTjB0LpoM=" crossorigin="anonymous"></script>
	<script type="text/javascript">
		var questions = @json( $questions );
		var selected_questions = [];
		$("#questions_table").sortable();

		$(document).on('click', '#addSurveyQuestions', function(e) { e.preventDefault(); handleFilterTable(); $("#questionsModal").modal('show'); });
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
		$(document).on('click', '.remove-question', function(e) {
			e.preventDefault();
			var questionId = $(this).data('id');
			var index = selected_questions.indexOf(questionId);
			if( index > -1 ) { selected_questions.splice(index, 1); }
			$(this).parent().parent().parent().parent().remove();
		});

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
		$(document).on('input', '#filter_type', function(e) { handleFilterTable(); });

		$(document).on('click', '.add-survey-question', function(e){
			e.preventDefault();
			var questionId = $(this).data('id');
			var question = questions.filter(e => e.id == questionId)[0];
			if( typeof( question ) !== 'undefined' )
			{
				selected_questions.push( questionId );
				var base_url = window.location.origin;
				var survey_type = question.survey_type == 'stars' ? "{{ __('admin.surveyTypeStarRating') }}" : ( question.survey_type == 'radio' ? "{{ __('admin.surveyTypeRadioButtons') }}" : "{{ __('admin.surveyQuestionAndAnswer') }}")
				var question_row = `<tr>
					<td> <i class="fa fa-sort"></i> ` + question.question + ` <input type="hidden" name="questions_ids[]" value="` + question.id + `" /> </td>
					<td> ` + survey_type + ` </td>
					<td class="px-1">
						<div class="btn-group w-100">
							<button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> {{ __('admin.Actions') }} </button>
							<div class="dropdown-menu">
								<a href="#" data-id="` + question.id + `" class="preview-question dropdown-item"> <i class="ti ti-eye"></i> {{ __('admin.Preview') }} </a>
								<a href="` + base_url + `/dashboard/questions-bank/` + question.id + `/edit" target="_blank" data-id="` + question.id + `" class="edit-question dropdown-item"> <i class="ti ti-edit"></i> {{ __('admin.Edit') }} </a>
								<a href="#" data-id="` + question.id + `" class="remove-question dropdown-item bg-danger text-white"> <i class="ti ti-trash"></i> {{ __('admin.Delete') }} </a>
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
			if( question.survey_type == 'radio' )
			{
				question.answers.forEach( function(item, key){
					var count = key + 1;
					html += '<div class="custom-py-1"> <button class="w-100 text-left btn btn-outline-secondary">' + count + '. ' + item.text + '</button> </div>';
				});
			}
			else if( question.survey_type == 'stars' )
			{
				html += '<ul class="custom-star-rating p-0 my-1">';
					html += '<li><i style="font-size: 1rem !important;" class="fas fa-star text-orange"></i> </li>';
					html += '<li><i style="font-size: 1rem !important;" class="fas fa-star text-orange"></i> </li>';
					html += '<li><i style="font-size: 1rem !important;" class="fas fa-star text-orange"></i> </li>';
					html += '<li><i style="font-size: 1rem !important;" class="fas fa-star text-orange"></i> </li>';
					html += '<li><i style="font-size: 1rem !important;" class="fas fa-star text-orange"></i> </li>';
				html += '</ul>';
			}
			return html;
		}

		function handleFilterTable()
		{
			var keyword = $.trim( $('#filter_keywords').val() );
			var type = $('#filter_type').val();

			var filtered_questions = questions.filter(question => {
				var match = true;
				if( keyword != '' && question.question.toLowerCase().indexOf(keyword.toLowerCase()) == -1 ) { match = false; }
				if( type != 'all' && question.survey_type != type ) { match = false; }
				return match;
			});
			
			var html = '';
			filtered_questions.forEach(function(question, key) {
				if( selected_questions.includes( question.id ) ) { /* */ }
				else
				{
					var question_type = '';
					if( question.survey_type == 'stars' ) { question_type = '{{ __("admin.surveyTypeStarRating") }}'; }
					if( question.survey_type == 'radio' ) { question_type = '{{ __("admin.surveyTypeRadioButtons") }}'; }
					if( question.survey_type == 'answers' ) { question_type = '{{ __("admin.surveyQuestionAndAnswer") }}'; }
	
					html += `<tr class="question-picker-row" data-qid="` + question.id + `">
								<td> ` + question.question + ` </td>
								<td class="text-center"> <span class="badge text-bg-secondary rounded-pill"> ` + question_type + ` </span> </td>
								<td class="text-center">
									<button data-id="` + question.id + `" class="btn btn-sm btn-success add-survey-question"> {{ __('admin.Add') }} </button>
								</td>
							</tr>`;
				}
			});
			if( filtered_questions.length > 0 ) { $("#question-picker-tbody").html( html ); }
			else { $("#question-picker-tbody").html('<tr> <td colspan=4 class="text-center"> {{ __("admin.noResultsMatch") }} </td></tr>'); }
		}
	</script>
@stop
