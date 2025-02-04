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
@section('subTitle', __('admin.Edit Question'))

@section('vendorstyle')
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
@stop

@section('style')
	<style>
		.content-body {
			padding: 0;
		}
		.form-group {
			margin-bottom: 1.5rem;
		}

		@media screen and (min-width: 768px) {
			.content-body {
				padding: 0 8rem;
			}
		}
	</style>
@stop
@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="content-overlay"></div>
		
		<div class="content-body">
			<!-- BuilderForm start -->
			<section id="basic-input">
				<div class="row">
					<div class="col-md-12">
						<div class="card p-4">
							<div class="card-header">
								<h4 class="card-title fw-bold border-bottom pb-3">{{__('admin.Edit Question')}}</h4>
							</div>
							<div class="card-content">
								<div class="card-body">
									<div class="row">
										@if( session()->has('success') )
											<div class="col-md-12 mb-4">
												<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
													<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
													<div class="d-flex align-items-center"> <i class="ti ti-thumb-up me-3 ti-md"></i> <span> {{ __('admin.'. session()->get('success')) }} </span> </div>
												</div>
											</div>
										@endif
										@if( session()->has('error') )
											<div class="col-md-12 mb-4">
												<div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
													<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
													<div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span>{{ __('admin.'.session()->get('error')) }} </span> </div>
												</div>
											</div>
										@endif
									</div>

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
									<form method="post" action="{{ route('questions-bank.update', ['questions_bank' => $question->id]) }}">
										{!! csrf_field() !!}
										<input name="_method" type="hidden" value="PATCH" />
										<div class="row">
											<div class="col-12">
												<fieldset class="form-group">
													<label class="fs-6 form-label mb-2">{{ __('admin.Organization') }}</label>
													<div class="row">
														@foreach ($organizations as $organization)
															<div class="col-md-4">
																<div class="form-check">
																	<input type="checkbox" 
																		   class="form-check-input" 
																		   name="organization_id[]" 
																		   id="organization_id_{{ $organization->id }}" 
																		   value="{{ $organization->id }}" 
																		   {{ in_array($organization->id, $selectedOrganizations) ? 'checked' : '' }}>
																	
																	<label class="form-check-label" for="organization_id_{{ $organization->id }}">
																		{{ $organization->{'name_' . $l} }}
																	</label>
																</div>
															</div>
														@endforeach
													</div>
												</fieldset>
											</div>
											<div class="col-12">
												<div class="form-group">
													<label class="fs-6 form-label mb-2 fw-bold" for="question_group">{{ __('admin.questionGroup')}}</label>
													<select class="select2 form-select" name="question_group" id="question_group" required>
														<option value="quiz" {{ $question->group == 'quiz' ? 'selected' : '' }}>{{ __('admin.question_type_quiz') }}</option>
														<option value="survey" {{ $question->group == 'survey' ? 'selected' : '' }}>{{ __('admin.question_type_survey') }}</option>
														<option value="assessment" {{ $question->group == 'assessment' ? 'selected' : '' }}>{{ __('admin.question_type_assessment') }}</option>
													</select>
												</div>
											</div>
											<div class="col-12">
												<fieldset class="form-group mb-4" id="survey-type-section" style="display: none;">
													<label class="fs-6 form-label mb-2 fw-bold" for="survey_type">{{ __('admin.surveyType') }}</label>
													<select class="select2 form-select" name="survey_type" id="survey_type" required>
														<option value="stars" {{ $question->survey_type == 'stars' ? 'selected' : '' }}> {{ __('admin.surveyTypeStarRating') }} </option>
														<option value="radio" {{ $question->survey_type == 'radio' ? 'selected' : '' }}> {{ __('admin.surveyTypeRadioButtons') }} </option>
														<option value="answers" {{ $question->survey_type == 'answers' ? 'selected' : '' }}> {{ __('admin.surveyQuestionAndAnswer') }} </option>
													</select>
												</fieldset>
											</div>
											<div class="col-12 assessment-type" style="display: none;">
												<div class="form-group" style="display: block;">
													<label class="form-label fs-6 mb-2 fw-bold">
														{{ __('admin.Category') }}
													</label>
													<select class="ascategory_id form-select select2" name="ascategory_id"
														data-placeholder="{{ $l == 'en' ? 'Choose category' : 'اختر القسم' }}"
													>
														<option value="" disabled selected>
															{{ $l == 'en' ? 'Choose category' : 'اختر القسم' }}
														</option>
														@foreach ($categories as $category)
															<option value="{{ $category->id }}" {{ $category->id == $question->ascategory_id ? 'selected' : '' }} >{{ $category->{'category_name_'. $l} }}</option>
														@endforeach
													</select>
												</div>
											</div>
											<div class="col-12">
												<div class="form-group assessment-type" style="display: none;">
													<label class="form-label fs-6 mb-2 fw-bold">
														{{ __('admin.SubName') }}
													</label>
													<select class="subcategory_id form-select select2" name="subcategory_id"
														data-placeholder="{{ $l == 'en' ? 'Choose Subcategory' : 'اختر القسم الفرعي' }}"
													>
														<option value="" disabled selected>
															{{ $l == 'en' ? 'Choose Subcategory' : 'اختر القسم الفرعي' }}
														</option>
														@foreach ($subcategories as $subcategory)
															<option value="{{ $subcategory->id }}" {{ $subcategory->id == $question->subcategory_id ? 'selected' : '' }} >{{ $subcategory->{'name_'. $l} }}</option>
														@endforeach
													</select>
												</div>
											</div>
											<div class="col-12">
												<fieldset class="form-group questions-type">
													<label class="fs-6 form-label mb-2 fw-bold" for="category_id">{{ __('admin.questionCategory')}}</label>
													<select class="select2 form-select" name="category_id" id="category_id" required>
														@foreach( $organizations->first()->questionsCategories as $category )
															<option value="{{ $category['id'] }}" {{ $question->category_id == $category['id'] ? 'selected' : '' }}>{{ $category['title'] }}</option>
														@endforeach
													</select>
												</fieldset>
											</div>
										</div>
										<div class="row">
											<div class="col-12">
												<fieldset class="form-group">
													<label class="fs-6 form-label mb-2 fw-bold" for="question">{{ __('admin.question')}}</label>
													<input value="{{ $question->question }}" name="question" type="text" class="form-control" id="question" placeholder="{{ __('admin.question') }}" required />
												</fieldset>
												<fieldset class="form-group questions-type">
													<label class="fs-6 form-label mb-2 fw-bold" for="type">{{ __('admin.question_Type')}}</label>
													<select class="select2 form-select" name="type" id="type" required>
														<option value="mcq" {{ $question->type == 'mcq' ? 'selected' : '' }}>{{ __('admin.type_mcq') }}</option>
														<option value="tfq" {{ $question->type == 'tfq' ? 'selected' : '' }}>{{ __('admin.type_tfq') }}</option>
													</select>
												</fieldset>
											</div>
											<div class="col-12">
												<fieldset class="form-group questions-type">
													<label class="fs-6 form-label mb-2 fw-bold" for="difficulty">{{ __('admin.difficultyLevel')}}</label>
													<select class="select2 form-select" name="difficulty" id="difficulty" required>
														<option value="easy" {{ $question->difficulty == 'easy' ? 'selected' : '' }}>{{ __('admin.difficulty_easy') }}</option>
														<option value="medium" {{ $question->difficulty == 'medium' ? 'selected' : '' }}>{{ __('admin.difficulty_medium') }}</option>
														<option value="hard" {{ $question->difficulty == 'hard' ? 'selected' : '' }}>{{ __('admin.difficulty_hard') }}</option>
													</select>
												</fieldset>
												<fieldset class="form-group questions-type">
													<label class="fs-6 form-label mb-2 fw-bold" for="correct">{{ __('admin.answerType')}}</label>
													<select class="select2 form-select" name="correct" id="correct">
														<option value="tfq_true" {{ $question->correct == true ? 'selected' : '' }}>{{ __('admin.tfq_statement_true') }}</option>
														<option value="tfq_false" {{ $question->correct != true ? 'selected' : '' }}>{{ __('admin.tfq_statement_false') }}</option>
													</select>
												</fieldset>
											</div>
											<div class="col-12" id="answers-section">
												<fieldset>
													<div class="w-100 d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
														<label class="fs-6 form-label mb-2 fw-bold w-100" for="answers">{{ __('admin.answers')}}</label>
														<button id="addNewAnswer" class="btn btn-light" data-bs-toggle="tooltip" data-bs-placement="top" title="Add a new answer" style="height: 35px;"> 
															<i class="ti ti-plus me-2"></i>
															<span class="text-nowrap">Add Answer</span>
														</button>
													</div>
													<div class="form-group" id="quizAnswers">
														@if( !empty( $question->answers ) )
														@foreach($question->answers as $key => $answer)
															<div class="d-flex flex-nowrap align-items-center gap-2 mb-3">
																@php
																if($question['group'] != 'assessment') {
																	$status = $answer['status'];
																}else{
																	$status = $answer['suggestion'];
																}
																@endphp
																<input value="{{ $answer['text'] }}" placeholder="{{ $l == 'en' ? 'Answer text' : 'اكتب نص الاجابة' }}" class="form-control" id="quiz_answer_{{ $key }}_text" name="quiz_answer_text[]">
																<input value="{{ $status }}" placeholder="{{ $l == 'en' ? 'Answer Suggestion' : 'اكتب المقترح ' }}" class="form-control assessment-type" id="quiz_answer_{{ $key }}_score" name="quiz_answer_score[]" style="display: none;">
																<select class="form-select questions-type" id="quiz_answer_{{ $key }}_status" name="quiz_answer_status[]">
																	<option value="true" {{ $status == true ? 'selected' : '' }}>{{ __('admin.quizAnswerCorrect') }}</option>
																	<option value="false" {{ $status == false ? 'selected' : '' }}>{{ __('admin.quizAnswerWrong') }}</option>
																</select>
																<button class="btn btn-icon btn-label-danger remove-answer-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove this answer" type="button">
																	<i class="ti ti-trash"></i> 
																</button>
															</div>
														@endforeach
														@endif
													</div>
												</fieldset>
											</div>
										</div>
										<div class="col-12">
											<fieldset class="form-group questions-type">
												<label class="fs-6 form-label mb-2 fw-bold" for="tags">{{ __('admin.questionTags')}}</label>
												<input value="{{ optional($question->tags)->implode(',') }}" name="tags" type="text" class="form-control" id="tags" placeholder="{{ __('admin.questionTagsPlaceholder') }}" />
											</fieldset>
										</div>
										<div class="row questions-type">
											<div class="col-12">
												<fieldset class="form-group">
													<label class="fs-6 form-label mb-2 fw-bold" for="explanation">{{ __('admin.explanation')}}</label>
													<textarea class="form-control" id="explanation" name="explanation" rows="5" name="desc_en">{{ $question->explanation }}</textarea>
												</fieldset>
											</div>
										</div>
										<div class="row assessment-type" style="display: none;">
											<div class="col-12">
												<fieldset class="form-group" id="explanation_QA" style="display: block;">
													<label class="fs-6 form-label mb-2 fw-bold" for ="explanation_QA_ar">{{ __('admin.explanation_QA_ar') }}</label>
													<textarea class="form-control" id="explanation_QA_ar" name="explanation_QA_ar" rows="2" name="explanation_QA_ar">{{ $question->explanation_QA_ar }}</textarea>
												</fieldset>
											</div>
											<div class="col-12">
												<fieldset class="form-group" id="explanation_QA" style="display: block;">
													<label class="fs-6 form-label mb-2 fw-bold" for ="explanation_QA_en">{{ __('admin.explanation_QA_en') }}</label>
													<textarea class="form-control" id="explanation_QA_en" name="explanation_QA_en" rows="2" name="explanation_QA_en">{{ $question->explanation_QA_en }}</textarea>
												</fieldset>
											</div>
										</div>
										<div class="row">
											<div class="col-12 d-flex justify-content-end">
												<button type="submit" class="btn btn-primary">{{__('admin.Save')}}</button>
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

@section('scripts')
	<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
	<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
@stop

@section('pagescripts')
	{{-- <script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script> --}}
	<script src="/vuexy/assets/js/forms-selects.js"></script>

	<script>
		$(document).ready(function() {
			handleGroupChange();
			handleSurveyTypeChange();
			// $('#answers-section').show();

			$('#question_group').on('change', handleGroupChange);
			$('#survey_type').on('change', handleSurveyTypeChange);
			
			function handleGroupChange() {
				const selectedGroupValue = $('#question_group').val();
	
				if (selectedGroupValue === 'survey') {
					$('#survey-type-section').show();
					$('.assessment-type').hide();
					$('#explanation_QA').hide();
					$('.questions-type').hide();
				} else if (selectedGroupValue === 'quiz') {
					$('#survey-type-section').hide();
					$('.assessment-type').hide();
					$('#explanation_QA').hide();
					$('.questions-type').show();
					$('#answers-section').show();
				} else if (selectedGroupValue === 'assessment') {
					$('#survey-type-section').hide();
					$('.assessment-type').show();
					$('#explanation_QA').show();
					$('.questions-type').hide();
					$('#answers-section').show();
				} else {
					$('#survey-type-section').hide();
					$('.assessment-type').show();
					$('#explanation_QA').show();
					$('.questions-type').show();
				}
			};

			function handleSurveyTypeChange() {
				const selectedSurveyTypeValue = $('#survey_type').val();
				console.log('selectedSurveyTypeValue', selectedSurveyTypeValue)
	
				if (selectedSurveyTypeValue === 'stars') {
					$('#answers-section').hide();
				} else {
					$('#answers-section').show();
				}
			};
		});

		alertify.defaults.transition = 'zoom';
		alertify.defaults.theme = {
			ok: 'btn btn-sm btn-success',
			cancel: 'btn btn-sm btn-danger',
			input: 'form-control'
		};
		
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
			var is_quiz = $('#question_group').val() == 'quiz' ? true : false;
			var is_assessment = $('#question_group').val() == 'assessment' ? true : false;
			var class_control = is_quiz ? 'questions-type' : 'questions-type d-none';
			var class_control2 = is_assessment ? 'assessment-type' : 'assessment-type d-none';
			if( !is_quiz )
			{
				if( $("#survey_type").val() == 'stars' )
				{
					if( $("input[name='quiz_answer_text[]']").length >= 5 )
					{
						alertify.alert('{{ __("admin.maxReached") }}', '{{ __("admin.noMoreFiveAllowed") }}');
						return false;
					}
				}

				if( $("#question_group").val() == 'assessment' )
				{
					if( $("input[name='quiz_answer_text[]']").length >= 3 )
					{
						alertify.alert('{{ __("admin.maxReached") }}', '{{ __("admin.noMoreThreeAllowed") }}');
						return false;
					}
				}

			}
			var answer = `
			<div class="d-flex flex-nowrap gap-4 mb-3">
				<input placeholder="Type the text of the answer" class="form-control" id="quiz_answer_` + count + `_text" name="quiz_answer_text[]">
				<input placeholder="{{ $l == 'en' ? 'Answer Suggestion' : 'اكتب المقترح ' }}" class="form-control ` + class_control2 + `" id="quiz_answer_` + count + `_score" name="quiz_answer_score[]">
				<select class="select2 form-select select2 ` + class_control + `" id="quiz_answer_` + count + `_status" name="quiz_answer_status[]">
					<option value="true">{{ __('admin.quizAnswerCorrect') }}</option>
					<option value="false">{{ __('admin.quizAnswerWrong') }}</option>
				</select>
				<button class="btn btn-icon btn-label-danger remove-answer-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove this answer">
					<i class="ti ti-trash"></i> 
				</button>
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

		$(document).ready(function() { handleChangeType(); handleChangeQuestionType(); });

		$(document).ready(function() {

$('#question_group').change(function() {
	var selectedValue = $(this).val(); // الحصول على القيمة المحددة في question_group
		if (selectedValue === 'assessment') {
			var start = 1;
			$("input[name='quiz_answer_text[]']").each(function(key, el) {
				if( start > 3 ) { $(el).parent().remove(); }
				start++;
			});		
		}
});

$('.ascategory_id').change(function() {
	var category_id = $(this).val();
	var $state = $('.subcategory_id');
	$.ajax({
		url: "{{ route('get_subcategory_by_category') }}",
		method: 'GET', // إضافة طريقة الطلب
		data: {
			category_id: category_id
		},
		success: function(data) {
			$state.html('<option value="">{{ __('admin.SubName') }}</option>'); // إعادة تعيين الخيارات

			$.each(data, function(id, value) {
				 // تعيين الخيار إذا كان مطابقًا
				$state.append('<option value="' + id + '" >' + value + '</option>');
			});

		},
		error: function(xhr, status, error) {
			console.error('Error:', error); // معالجة الأخطاء
		}
	});
});
});

	</script>
@stop
