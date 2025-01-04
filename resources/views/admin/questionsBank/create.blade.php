@php
	$json_organizations = $organizations->map(function ($item) {
		$cats = $item->questionsCategories
				->map(function ($cat) {
						return $cat->only(['id', 'title']);
				})
				->toArray();
		$item->questionsCategories = $cats;
		return $item->only(['id', 'name_ar', 'name_en', 'questionsCategories']);
	});
@endphp
@extends('layouts.admin')

@section('title', __('admin.Questions Bank'))
@section('index_url', route('questions-bank.index'))
@section('subTitle', __('admin.Add new Question'))

@section('style')
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />

	<style type="text/css">
		/* .btn i.fa {
			top: 0px !important;
		}

		.btn {
			padding: 0px 10px !important;
		} */

		.custom-container-ar {
			padding-left: 8rem;
			padding-right: 2rem;
		}

		.custom-container-en {
			padding-left: 2rem;
			padding-right: 8rem;
		}

		#quizAnswers .input-group:focus-within
		{
			box-shadow: none !important;
		}
		[dir="rtl"] .ajs-header { text-align: left !important; }
		[dir="ltr"] .ajs-header { text-align: right !important; }

		@media screen and (max-width: 768px) {

			.custom-container-ar,
			.custom-container-en {
				padding: 0;
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
							<div class="card {{ $l == 'ar' ? 'custom-container-ar' : 'custom-container-en' }}">
								<div class="card-header border-bottom mb-4"> <h4 class="card-title">{{ __('admin.Add new Question') }}</h4> </div>
								<div class="card-content">
									<div class="card-body">
										<!-- Alert -->
										@if( session()->has('msg') )
											<div class="row">
												<div class="col-md-12">
													<div class="alert alert-danger alert-dismissible mb-2" role="alert">
														{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}
														<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
													</div>
												</div>
											</div>
										@endif
										<!-- / Alert -->

										<!-- Validation messages -->
										@if( count($errors) > 0 )
											<div class="row">
												<div class="col-md-12">
													<div class="alert alert-danger mb-2" role="alert">
														<ul>
															@foreach ($errors->all() as $error)
																<li>{{ $error }} </li>
															@endforeach
														</ul>
													</div>
												</div>
											</div>
										@endif

										<!-- Create Question Form -->
										<form method="post" action="{{ route('questions-bank.store') }}">
											{!! csrf_field() !!}
											<fieldset class="form-group mb-4">
												<label class="fs-6 form-label mb-2" for ="organization_id">{{ __('admin.Organization') }}</label>
												<select class="form-control form-select select2" name="organization_id" id="organization_id" required>
													@foreach ($organizations as $organization)
														<option value="{{ $organization->id }}" {{ !empty(old('organization_id')) && old('organization_id') == $organization->id ? 'selected' : '' }}>
															{{ $organization->{'name_' . $l} }}
														</option>
													@endforeach
												</select>
											</fieldset>
											<fieldset class="form-group mb-4">
												<label class="fs-6 form-label mb-2" for ="question">{{ __('admin.question') }}</label>
												<input value="{{ old('question') }}" name="question" type="text" class="form-control" id="question" placeholder="{{ __('admin.question') }}" required />
											</fieldset>

											<div class="row">
												<div class="col-lg-6 col-md-6 col-sm-12">
													<fieldset class="form-group mb-4">
														<label class="fs-6 form-label mb-2" for="question_group">{{ __('admin.questionGroup') }}</label>
														<select class="form-control form-select select2" name="question_group" id="question_group" required>
															<option value="quiz" {{ !empty(old('question_group')) && old('question_group') == 'quiz' ? 'selected' : '' }}> {{ __('admin.question_type_quiz') }} </option>
															<option value="survey" {{ !empty(old('question_group')) && old('question_group') == 'survey' ? 'selected' : '' }}> {{ __('admin.question_type_survey') }}</option>
															<option value="assessment" {{ !empty(old('question_group')) && old('question_group') == 'assessment' ? 'selected' : '' }}> {{ __('admin.question_type_assessment') }}</option>
														</select>
													</fieldset>
												</div>

												<div class="col-lg-3 col-md-3 col-sm-12 assessment-type">
													<label class="form-label fs-6">
													  {{ __('admin.Category') }}
													  
													</label>
													<select class="ascategory_id form-select" name="ascategory_id">
													  <option value="" disabled selected>
														{{ $l == 'en' ? 'Choose category' : 'اختر القسم' }}
													  </option>
													  @foreach ($categories as $category)
														<option value="{{ $category->id }}"  >{{ $category->{'category_name_'. $l} }}</option>
													  @endforeach
													</select>
												  </div>

												<div class="col-lg-3 col-md-3 col-sm-12 assessment-type">
													<label class="form-label fs-6">
													  {{ __('admin.SubName') }}
													  
													</label>
													<select class="subcategory_id form-select" name="subcategory_id">
													  <option value="" disabled selected>
														{{ $l == 'en' ? 'Choose category' : 'اختر القسم الفرعي' }}
													  </option>
													  @foreach ($subcategories as $subcategory)
													  
														<option value="{{ $subcategory->id }}"  >{{ $subcategory->{'name_'. $l} }} {{ $subcategory->id }}</option>
													  @endforeach
													</select>
												  </div>

												  <fieldset class="col-lg-6 col-md-6 col-sm-12 assessment-type">
													<label class="fs-6 form-label mb-2" for ="explanation_QA_ar">{{ __('admin.explanation_QA_ar') }}</label>
													<textarea class="form-control" id="explanation_QA_ar" name="explanation_QA_ar" rows="2" name="explanation_QA_ar">{{ old('explanation_QA_ar') }}</textarea>
												</fieldset>

												<fieldset class="col-lg-6 col-md-6 col-sm-12 assessment-type">
													<label class="fs-6 form-label mb-2" for ="explanation_QA_en">{{ __('admin.explanation_QA_en') }}</label>
													<textarea class="form-control" id="explanation_QA_en" name="explanation_QA_en" rows="2" name="explanation_QA_en">{{ old('explanation_QA_en') }}</textarea>
												</fieldset>

												<fieldset class="col-lg-6 col-md-6 col-sm-12 assessment-type">
													<label class="fs-6 form-label mb-2" for ="explanation_AA_ar">{{ __('admin.explanation_AA_ar') }}</label>
													<textarea class="form-control" id="explanation_AA_ar" name="explanation_AA_ar" rows="2" name="explanation_AA_ar">{{ old('explanation_AA_ar') }}</textarea>
												</fieldset>

												<fieldset class="col-lg-6 col-md-6 col-sm-12 assessment-type">
													<label class="fs-6 form-label mb-2" for ="explanation_AA_en">{{ __('admin.explanation_AA_en') }}</label>
													<textarea class="form-control" id="explanation_AA_en" name="explanation_AA_en" rows="2" name="explanation_AA_en">{{ old('explanation_AA_en') }}</textarea>
												</fieldset>

												<div class="col-lg-6 col-md-6 col-sm-12">
													<fieldset class="form-group mb-4 questions-type">
														<label class="fs-6 form-label mb-2" for="category_id">{{ __('admin.questionCategory') }}</label>
														<select class="form-control form-select select2" name="category_id" id="category_id" required>
															@foreach ($organizations->first()->questionsCategories as $category)
																<option value="{{ $category['id'] }}" {{ !empty(old('category_id')) && old('category_id') == $category['id'] ? 'selected' : '' }}>
																	{{ $category['title'] }}
																</option>
															@endforeach
														</select>
													</fieldset>

													

													<fieldset class="form-group mb-4 survey-type">
														<label class="fs-6 form-label mb-2" for="survey_type">{{ __('admin.surveyType') }}</label>
														<select class="form-control form-select select2" name="survey_type" id="survey_type" required>
															<option value="" selected disabled> {{ __('admin.Choose') }} </option>
															<option value="stars"> {{ __('admin.surveyTypeStarRating') }} </option>
															<option value="radio"> {{ __('admin.surveyTypeRadioButtons') }} </option>
															<option value="answers"> {{ __('admin.surveyQuestionAndAnswer') }} </option>
														</select>
													</fieldset>

												</div>
											</div>

											<div class="row questions-type">
												<div class="col-lg-6 col-md-6 col-sm-12">
													<fieldset class="form-group mb-4">
														<label class="fs-6 form-label mb-2" for ="type">{{ __('admin.question_Type') }}</label>
														<select class="form-control form-select select2" name="type" id="type" required>
															<option value="mcq" {{ !empty(old('type')) && old('type') == 'mcq' ? 'selected' : '' }}> {{ __('admin.type_mcq') }}</option>
															<option value="tfq" {{ !empty(old('type')) && old('type') == 'tfq' ? 'selected' : '' }}> {{ __('admin.type_tfq') }}</option>
														</select>
													</fieldset>
												</div>

												<div class="col-lg-6 col-md-6 col-sm-12">
													<fieldset class="form-group mb-4">
														<label class="fs-6 form-label mb-2" for ="difficulty">{{ __('admin.difficultyLevel') }}</label>
														<select class="form-control form-select select2" name="difficulty" id="difficulty" required>
															<option value="easy" {{ !empty(old('difficulty')) && old('difficulty') == 'easy' ? 'selected' : '' }}>
																{{ __('admin.difficulty_easy') }}
															</option>
															<option value="medium" {{ !empty(old('difficulty')) && old('difficulty') == 'medium' ? 'selected' : '' }}>
																{{ __('admin.difficulty_medium') }}
															</option>
															<option value="hard" {{ !empty(old('difficulty')) && old('difficulty') == 'hard' ? 'selected' : '' }}>
																{{ __('admin.difficulty_hard') }}
															</option>
														</select>
													</fieldset>
												</div>
											</div>

											<div class="row">
												<div class="col-lg-6 col-md-6 col-sm-12">
													<fieldset id="tfq-block" class="form-group d-none questions-type">
														<label class="fs-6 form-label mb-2" for ="correct">{{ __('admin.answerType') }}</label>
														<select class="form-control form-select select2" name="correct" id="correct">
															<option value="tfq_true" {{ !empty(old('correct')) && old('correct') == 'tfq_true' ? 'selected' : '' }}>
																{{ __('admin.tfq_statement_true') }}
															</option>
															<option value="tfq_false" {{ !empty(old('correct')) && old('correct') == 'tfq_false' ? 'selected' : '' }}>
																{{ __('admin.tfq_statement_false') }}
															</option>
														</select>
													</fieldset>
												</div>
											</div>

											<fieldset id="mcq-block" class="form-group mb-4 mt-3 " style="max-width: 400px;">
												<label class="fs-6 form-label mb-2" class ="w-100" for="answers">{{ __('admin.answers') }}
													<button id="addNewAnswer" style="padding: 0px 10px !important;" class="btn btn-xs btn-label-success float-right rounded-pill py-1">
														<i class="ti ti-plus"></i> {{ __('admin.addNewAnswer') }}
													</button>
												</label>
												<div class="mt-1" id="quizAnswers">
													<div class="input-group mb-1">
														<input placeholder="{{ $l == 'en' ? 'Answer text' : 'اكتب نص الاجابة' }}" class="form-control" id="quiz_answer_0_text" name="quiz_answer_text[]" style="max-width:300px">
														<input placeholder="{{ $l == 'en' ? 'Answer Suggestion' : 'اكتب المقترح ' }}" class="form-control assessment-type" id="quiz_answer_0_score" name="quiz_answer_score[]" style="max-width:100px">
														<select class="form-control form-select questions-type" id="quiz_answer_0_status" name="quiz_answer_status[]">
															<option value="true">{{ __('admin.quizAnswerCorrect') }}</option>
															<option value="false">{{ __('admin.quizAnswerWrong') }}</option>
														</select>
														<button class="btn btn-label-danger remove-answer-btn"> <i class="ti ti-x"></i> </button>
													</div>
												</div>
											</fieldset>

											<fieldset class="form-group mb-4 questions-type">
												<label class="fs-6 form-label mb-2" for ="tags">{{ __('admin.questionTags') }}</label>
												<input value="{{ old('tags') }}" name="tags" type="text" class="form-control" id="tags" placeholder="{{ __('admin.questionTagsPlaceholder') }}" />
											</fieldset>

											<fieldset class="form-group mb-4 questions-type">
												<label class="fs-6 form-label mb-2" for ="explanation">{{ __('admin.explanation') }}</label>
												<textarea class="form-control" id="explanation" name="explanation" rows="5" name="desc_en">{{ old('explanation') }}</textarea>
											</fieldset>

											<div class="row">
												<div class="col-12 d-flex justify-content-end">
													<button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('admin.Save') }}</button>
												</div>
											</div>
										</form>
										<!-- Create Question Form -->
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
	<!-- Page JS -->
	<script src="/vuexy/assets/js/forms-selects.js"></script>
	<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>
	<script type="text/javascript">
		alertify.defaults.transition = 'zoom';
		alertify.defaults.theme = {
			ok: 'btn btn-sm btn-success',
			cancel: 'btn btn-sm btn-danger',
			input: 'form-control'
		};
		var orgnizations = @json($json_organizations);

		function handleChangeQuestionType() {
			$('.questions-type').addClass('d-none');
			$('.survey-type').addClass('d-none');
			if( $('#question_group').val() == 'quiz' ) { $('.questions-type').removeClass('d-none');$('.assessment-type').addClass('d-none');  }
			else if( $('#question_group').val() == 'survey' ) { $('.survey-type').removeClass('d-none'); $('#type').val('mcq').trigger('change');$('.assessment-type').addClass('d-none'); }
			else if( $('#question_group').val() == 'assessment' ) { $('.survey-type').addClass('d-none');$('.questions-type').addClass('d-none');$('.assessment-type').removeClass('d-none'); }
		}

		function handleChangeType() {
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

		$(document).on('change', '#type', function(e) { handleChangeType(); });
		$(document).on('change', '#question_group', function(e) { handleChangeQuestionType(); });
		$(document).on('change', '#organization_id', function(e) {
			var id = $('#organization_id').val();
			var _orgnization = orgnizations.filter(org => org.id == id)[0];
			if( typeof(_orgnization) !== 'undefined' )
			{
				var categories = _orgnization.questionsCategories;
				var categoriesOptions = "";
				categories.forEach(category => { categoriesOptions += "<option value=" + category['id'] + ">" + category['title'] + "</option>"; });
				$("#category_id").val("");
				$("#category_id").html(categoriesOptions);
			} else { $("#category_id").html(""); }
		});

		$(document).on('change', '#survey_type', function (e) {
			if( $("#survey_type").val() == 'stars' )
			{
				var start = 1;
				$("input[name='quiz_answer_text[]']").each(function(key, el) {
					if( start > 5 ) { $(el).parent().remove(); }
					start++;
				});
			}
			
		});

		$(document).on('click', '#addNewAnswer', function(e) {
			e.preventDefault();
			var count = $("#quizAnswers > div.input-group").length;
			var is_quiz = $('#question_group').val() == 'quiz' ? true : false;
			var class_control = is_quiz ? 'questions-type' : 'questions-type d-none';
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
			var answer =
				`
			<div class="input-group mb-1">
				<input placeholder="{{ $l == 'en' ? 'Answer text' : 'اكتب نص الاجابة' }}" class="form-control " style="max-width:300px" id="quiz_answer_` +
				count + `_text" name="quiz_answer_text[]">` +
				`<input placeholder="{{ $l == 'en' ? 'Answer Suggestion' : 'اكتب المقترحات ' }}" class="form-control assessment-type" id="quiz_answer_`+count+`_score" name="quiz_answer_score[]" style="max-width:100px">
				<select class="form-control form-select select2 ` + class_control + `" id="quiz_answer_` + count + `_status" name="quiz_answer_status[]">
					<option value="true">{{ __('admin.quizAnswerCorrect') }}</option>
					<option value="false">{{ __('admin.quizAnswerWrong') }}</option>
				</select>
				<button class="btn btn-label-danger remove-answer-btn"> <i class="ti ti-x"></i> </button>
			</div>`;
			$('#quizAnswers').append(answer);

			if($('#question_group').val()  != 'assessment' ){
				$('.assessment-type').addClass('d-none');
			}
		});

		$(document).on('click', '.remove-answer-btn', function(e) {
			e.preventDefault();
			$(this).parent().remove();
			$("#quizAnswers > div.input-group").each(function(key, item) {
				$(item).find('input').attr('id', 'quiz_answer_' + key + '_text');
				$(item).find('select').attr('id', 'quiz_answer_' + key + '_status');
			});
		});

		$(document).ready(function() { handleChangeType(); handleChangeQuestionType(); });

		$(document).on('change', '#survey_type', function() {
    let Type = $(this).val(); // استخدم let لتعريف المتغير
    if (Type == 'stars') {
        $('#mcq-block').addClass('d-none'); // إخفاء العنصر
    } else {
        $('#mcq-block').removeClass('d-none'); // إظهار العنصر في الحالات الأخرى
    }
});


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
