@php
	$json_organizations = $organizations->map(function( $item ) {
		$cats = $item->questionsCategories->map(function( $cat ) { return $cat->only(['id', 'title']); })->toArray();
		$item->questionsCategories = $cats;
		return $item->only(['id', 'name_ar', 'name_en', 'questionsCategories']);
	});
@endphp
@extends('layouts.admin')
@section('title', __('admin.Questions Bank') )
@section('index_url', route('questions-bank.index') )
@section('subTitle', __('admin.Add new Question'))
@section('style')
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<style type="text/css">
		.btn i.fa { top: 0px !important; }
		.btn { padding: 0px 10px !important; }
	</style>
@stop
@section('content')
<!-- BEGIN: Content-->
<div class="app-content content">
	<div class="content-overlay"></div>
	<div class="content-wrapper">
		@include('admin.includes.heading')
		<div class="content-body">
			<!-- BuilderForm start -->
			<section id="basic-input">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">{{__('admin.Add new Question')}}</h4>
							</div>
							<div class="card-content">
								<div class="card-body">
									@if(session()->has('msg'))
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-warning alert-danger mb-2" role="alert">
												<button type="button" class="close" data-dismiss="alert"
													aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
												<div class="d-flex align-items-center">
													<i class="bx bx-error"></i>
													<span>
														{{ __('admin.'.session()->get('msg')) }}
														{{ Session::forget('msg') }}
													</span>
												</div>
											</div>
										</div>
									</div>
									@endif
									<!-- Validation messages -->
									@if(count($errors) >0)
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-danger">
												<ul>
													@foreach($errors->all() as $error)
													<li>{{ $error }} </li>
													@endforeach
												</ul>
											</div>
										</div>
									</div>
									@endif
									<form method="post" action="{{ route('questions-bank.store') }}">
										{!! csrf_field() !!}
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="organization_id">{{ __('admin.Organization')}}</label>
													<select class="custom-select" name="organization_id" id="organization_id" required>
														@foreach( $organizations as $organization )
															<option value="{{ $organization->id }}">{{ $organization->{'name_' . $l} }}</option>
														@endforeach
													</select>
												</fieldset>
												<fieldset class="form-group">
													<label for="question">{{ __('admin.question')}}</label>
													<input value="" name="question" type="text" class="form-control" id="question" placeholder="{{ __('admin.question') }}" required />
												</fieldset>
												<fieldset class="form-group">
													<label for="type">{{ __('admin.question_Type')}}</label>
													<select class="custom-select" name="type" id="type" required>
														<option value="mcq">{{ __('admin.type_mcq') }}</option>
														<option value="tfq">{{ __('admin.type_tfq') }}</option>
													</select>
												</fieldset>
											</div>

											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="category_id">{{ __('admin.questionCategory')}}</label>
													<select class="custom-select" name="category_id" id="category_id" required>
														@foreach( $organizations->first()->questionsCategories as $category )
															<option value="{{ $category['id'] }}">{{ $category['title'] }}</option>
														@endforeach
													</select>
												</fieldset>
												<fieldset class="form-group">
													<label for="difficulty">{{ __('admin.difficultyLevel')}}</label>
													<select class="custom-select" name="difficulty" id="difficulty" required>
														<option value="easy">{{ __('admin.difficulty_easy') }}</option>
														<option value="medium">{{ __('admin.difficulty_medium') }}</option>
														<option value="hard">{{ __('admin.difficulty_hard') }}</option>
													</select>
												</fieldset>
												<fieldset id="tfq-block" class="form-group d-none">
													<label for="correct">{{ __('admin.answerType')}}</label>
													<select class="custom-select" name="correct" id="correct">
														<option value="tfq_true">{{ __('admin.tfq_statement_true') }}</option>
														<option value="tfq_false">{{ __('admin.tfq_statement_false') }}</option>
													</select>
												</fieldset>
												<fieldset id="mcq-block" class="form-group">
													<label class="w-100" for="answers">{{ __('admin.answers')}}
														<button id="addNewAnswer" style="padding: 0px 10px !important;" class="btn btn-xs btn-success float-right"> <i style="padding-top: 5px;" class="fa fa-plus-circle fa-fw fa-sm align-middle"></i> Add Answer</button>
													</label>
													<div class="mt-1" id="quizAnswers">
														<div class="input-group mb-1">
															<input placeholder="Type the text of the answer" class="form-control" id="quiz_answer_0_text" name="quiz_answer_text[]">
															<select class="custom-select" id="quiz_answer_0_status" name="quiz_answer_status[]">
																<option value="true">Correct answer</option>
																<option value="false">Wrong answer</option>
															</select>
															<button class="btn btn-outline-danger remove-answer-btn"> <i class="fa fa-fw fa-sm fa-times align-middle"></i> </button>
														</div>
													</div>
												</fieldset>
											</div>
										</div>
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="explanation">{{ __('admin.explanation')}}</label>
													<textarea class="form-control" id="explanation" name="explanation" rows="5" name="desc_en"></textarea>
												</fieldset>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="tags">{{ __('admin.questionTags')}}</label>
													<input value="" name="tags" type="text" class="form-control" id="tags" placeholder="{{ __('admin.questionTagsPlaceholder') }}" />
												</fieldset>
											</div>
										</div>
										<div class="row">
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
@stop
@section('pagescripts')
	<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>
	<script type="text/javascript">
		var orgnizations = @json( $json_organizations );

		function handleChangeType()
		{
			var type = $('#type').val();
			if( type == 'mcq' )
			{
				$('#tfq-block').addClass('d-none');
				$('#mcq-block').removeClass('d-none');
			}
			else if( type == 'tfq' )
			{
				$('#mcq-block').addClass('d-none');
				$('#tfq-block').removeClass('d-none');
			}
		}

		$(document).on('change', '#type', function (e) { handleChangeType(); });
		$(document).on('change', '#organization_id', function(e) {
			var id = $('#organization_id').val();
			var _orgnization = orgnizations.filter( org => org.id == id )[0];
			if( typeof( _orgnization ) !== 'undefined' )
			{
				var categories = _orgnization.questionsCategories;
				var categoriesOptions = "";
				categories.forEach( category => { categoriesOptions += "<option value=" + category['id'] + ">" + category['title'] + "</option>"; });
				$("#category_id").val("");
				$("#category_id").html(categoriesOptions);
			} else { $("#category_id").html(""); }
			
		});
		$(document).on('click', '#addNewAnswer', function (e) {
			e.preventDefault();
			var count = $("#quizAnswers > div.input-group").length;
			var answer = `
			<div class="input-group mb-1">
				<input placeholder="Type the text of the answer" class="form-control" id="quiz_answer_` + count + `_text" name="quiz_answer_text[]">
				<select class="custom-select" id="quiz_answer_` + count + `_status" name="quiz_answer_status[]">
					<option value="true">Correct answer</option>
					<option value="false">Wrong answer</option>
				</select>
				<button class="btn btn-outline-danger remove-answer-btn"> <i class="fa fa-fw fa-sm fa-times align-middle"></i> </button>
			</div>`;
			$('#quizAnswers').append( answer );
		});
		$(document).on('click', '.remove-answer-btn', function(e) {
			e.preventDefault();
			$(this).parent().remove();
			$("#quizAnswers > div.input-group").each(function(key, item){
				$(item).find('input').attr('id', 'quiz_answer_' + key + '_text');
				$(item).find('select').attr('id', 'quiz_answer_' + key + '_status');
			});
		});

		$(document).ready(function() {
			handleChangeType();
		});
	</script>
@stop
