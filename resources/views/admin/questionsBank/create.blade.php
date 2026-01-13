@extends('layouts.admin')
@section('title', __('admin.Questions Bank'))
@section('index_url', route('questions-bank.index'))
@section('subTitle', __('admin.Add new Question'))

@section('content')
<!-- BEGIN: Content-->
<div class="">
	<div class="">
		<div class="content-overlay"></div>
		
		<div class="content-body">
			<!-- Question Creation Form start -->
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-8 pb-4">
								<h5 class="card-title mb-0">
									{{__('admin.Add new Question')}}
								</h5>
								<p class="card-text text-muted mt-1 mb-0">
									{{ $l == 'en' ? 'Fill out the form below to create a new question' : 'أكمل النموذج أدناه لإنشاء سؤال جديد' }}
								</p>
							</div>
							
							@if( session()->has('msg') )
							<div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
								<i class="ti ti-alert-triangle me-3"></i>
								<div>
									{{ __('admin.'.session()->get('msg')) }} 
									{{ Session::forget('msg') }}
								</div>
								<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							@endif
							
							<!-- Validation messages -->
							@if(count($errors) >0)
							<div class="alert alert-danger d-flex align-items-start mb-4" role="alert">
								<i class="ti ti-alert-circle me-3 mt-1"></i>
								<div class="flex-grow-1">
									<ul class="mb-0">
										@foreach($errors->all() as $error)
										<li>{{ $error }}</li>
										@endforeach
									</ul>
								</div>
								<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							@endif
							
							<form method="post" action="{{ route('questions-bank.store') }}" class="needs-validation">
								{!! csrf_field() !!}
								
								<!-- Single Container Row for All Form Elements -->
								<div class="row">
									<!-- Organization Selection - Full Width -->
									<div class="col-12">
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="organization_id">
												{{ $l == 'en' ? 'Organizations' : 'المنظمات' }}
											</label>
											<select class="form-control form-select select2" name="organization_id[]" id="organization_id" multiple required>
												@foreach ($organizations as $organization)
													<option value="{{ $organization->id }}" 
														{{ collect(old('organization_id', []))->contains($organization->id) ? 'selected' : '' }}>
														{{ $organization->{'name_' . $l} }}
													</option>
												@endforeach
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select at least one organization.' : 'يرجى اختيار منظمة واحدة على الأقل.' }}</div>
										</div>
									</div>

									<!-- Question Group and Survey Type -->
									<div class="col-lg-6">
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="question_group">{{ __('admin.questionGroup')}}</label>
											<select class="form-control form-select" name="question_group" id="question_group" required>
												<option value="">{{ $l == 'en' ? 'Choose question group...' : 'اختر مجموعة السؤال...' }}</option>
												<option value="quiz" {{ old('question_group') == 'quiz' ? 'selected' : '' }}>{{ __('admin.question_type_quiz') }}</option>
												<option value="survey" {{ old('question_group') == 'survey' ? 'selected' : '' }}>{{ __('admin.question_type_survey') }}</option>
												@if($_user->ableTo('Assessment.Questions.Manage', $permissions))
												<option value="assessment" {{ old('question_group') == 'assessment' ? 'selected' : '' }}>{{ __('admin.question_type_assessment') }}</option>
												@endif
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select a question group.' : 'يرجى اختيار مجموعة السؤال.' }}</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="form-group mb-4" id="survey-type-section" style="display: none;">
											<label class="form-label mb-1" for="survey_type">{{ __('admin.surveyType') }}</label>
											<select class="form-control form-select" name="survey_type" id="survey_type">
												<option value="">{{ $l == 'en' ? 'Choose survey type...' : 'اختر نوع الاستبيان...' }}</option>
												<option value="stars" {{ old('survey_type') == 'stars' ? 'selected' : '' }}> {{ __('admin.surveyTypeStarRating') }} </option>
												<option value="radio" {{ old('survey_type') == 'radio' ? 'selected' : '' }}> {{ __('admin.surveyTypeRadioButtons') }} </option>
												<option value="answers" {{ old('survey_type') == 'answers' ? 'selected' : '' }}> {{ __('admin.surveyQuestionAndAnswer') }} </option>
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select a survey type.' : 'يرجى اختيار نوع الاستبيان.' }}</div>
										</div>
									</div>

									<!-- Assessment Categories - Only shown for assessment type -->
									<div class="col-lg-6 assessment-section" style="display: none;">
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="ascategory_id">{{ __('admin.Category') }}</label>
											<select class="form-control form-select" name="ascategory_id" id="ascategory_id">
												<option value="">{{ $l == 'en' ? 'Choose category...' : 'اختر القسم...' }}</option>
												@foreach ($categories as $category)
													<option value="{{ $category->id }}" {{ old('ascategory_id') == $category->id ? 'selected' : '' }}>{{ $category->{'category_name_'. $l} }}</option>
												@endforeach
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select a category.' : 'يرجى اختيار قسم.' }}</div>
										</div>
									</div>
									<div class="col-lg-6 assessment-section" style="display: none;">
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="subcategory_id">{{ __('admin.SubName') }}</label>
											<select class="form-control form-select" name="subcategory_id" id="subcategory_id">
												<option value="">{{ $l == 'en' ? 'Choose subcategory...' : 'اختر القسم الفرعي...' }}</option>
												@foreach ($subcategories as $subcategory)
													<option value="{{ $subcategory->id }}" {{ old('subcategory_id') == $subcategory->id ? 'selected' : '' }}>{{ $subcategory->{'name_'. $l} }}</option>
												@endforeach
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select a subcategory.' : 'يرجى اختيار قسم فرعي.' }}</div>
										</div>
									</div>

									<!-- Question Category - For quiz type only -->
									<div class="col-12">
										<div class="form-group mb-4 quiz-section" style="display: none;">
											<label class="form-label mb-1" for="category_id">{{ __('admin.questionCategory')}}</label>
											<select class="form-control form-select" name="category_id" id="category_id">
												<option value="">{{ $l == 'en' ? 'Please select an organization first' : 'يرجى اختيار منظمة أولاً' }}</option>
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select a question category.' : 'يرجى اختيار فئة السؤال.' }}</div>
										</div>
									</div>
									
									<!-- Question Text - Full Width -->
									<div class="col-12">
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="question">{{ __('admin.question')}}</label>
											<textarea name="question" class="form-control" id="question" placeholder="{{ __('admin.question') }}" rows="3" required>{{ old('question') }}</textarea>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please enter the question.' : 'يرجى إدخال السؤال.' }}</div>
										</div>
									</div>

									<!-- Question Type - Only for quiz -->
									<div class="col-lg-6">
										<div class="form-group mb-4 quiz-section" style="display: none;">
											<label class="form-label mb-1" for="type">{{ __('admin.question_Type')}}</label>
											<select class="form-control form-select" name="type" id="type">
												<option value="">{{ $l == 'en' ? 'Choose question type...' : 'اختر نوع السؤال...' }}</option>
												<option value="mcq" {{ old('type') == 'mcq' ? 'selected' : '' }}>{{ __('admin.type_mcq') }}</option>
												<option value="tfq" {{ old('type') == 'tfq' ? 'selected' : '' }}>{{ __('admin.type_tfq') }}</option>
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select a question type.' : 'يرجى اختيار نوع السؤال.' }}</div>
										</div>
									</div>

									<!-- Difficulty Level - Only for quiz -->
									<div class="col-lg-6">
										<div class="form-group mb-4 quiz-section" style="display: none;">
											<label class="form-label mb-1" for="difficulty">{{ __('admin.difficultyLevel')}}</label>
											<select class="form-control form-select" name="difficulty" id="difficulty">
												<option value="">{{ $l == 'en' ? 'Choose difficulty...' : 'اختر الصعوبة...' }}</option>
												<option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>{{ __('admin.difficulty_easy') }}</option>
												<option value="medium" {{ old('difficulty') == 'medium' ? 'selected' : '' }}>{{ __('admin.difficulty_medium') }}</option>
												<option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>{{ __('admin.difficulty_hard') }}</option>
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select a difficulty level.' : 'يرجى اختيار مستوى الصعوبة.' }}</div>
										</div>
									</div>

									<!-- Tags -->
									<div class="col-12">
										<div class="form-group mb-4 quiz-survey-section" style="display: none;">
											<label class="form-label mb-1" for="tags">{{ __('admin.questionTags')}}</label>
											<input value="{{ old('tags') }}" name="tags" type="text" class="form-control" id="tags" placeholder="{{ __('admin.questionTagsPlaceholder') }}" />
											<small class="form-text text-muted">{{ $l == 'en' ? 'Separate tags with commas' : 'افصل الكلمات المفتاحية بفاصلة' }}</small>
										</div>
									</div>
									
									<!-- Answers Section -->
									<div class="col-12 g-4 mb-4" id="answers-section" style="display: none;">
										<div class="d-flex justify-content-between align-items-center mb-3">
											<label class="form-label mb-0 fw-bold">{{ __('admin.answers')}}</label>
											<button type="button" id="addNewAnswer" class="btn btn-outline-primary btn-sm">
												<i class="ti ti-plus me-1"></i>
												{{ __('admin.Add Answer') }}
											</button>
										</div>
										<div id="answersContainer">
											<div class="answer-item mb-3">
												<div class="d-flex align-items-center gap-2">
													<input type="text" name="quiz_answer_text[]" class="form-control" placeholder="{{ $l == 'en' ? 'Answer text' : 'نص الإجابة' }}" value="{{ old('quiz_answer_text.0') }}">
													
													<!-- Score input for assessment -->
                                                    
													<!-- Status select for quiz -->
													<select name="quiz_answer_status[]" class="form-select quiz-input" style="display: none;">
														<option value="true" {{ old('quiz_answer_status.0') == 'true' ? 'selected' : '' }}>{{ __('admin.quizAnswerCorrect') }}</option>
														<option value="false" {{ old('quiz_answer_status.0') == 'false' ? 'selected' : '' }}>{{ __('admin.quizAnswerWrong') }}</option>
													</select>
													
													<button type="button" class="btn btn-outline-danger btn-sm remove-answer-btn" style="display: none;">
														<i class="ti ti-trash"></i>
													</button>
												</div>
											</div>
										</div>
									</div>

									<!-- Explanation for Quiz/Survey -->
									<div class="col-12 mb-4 quiz-survey-section" style="display: none;">
										<div class="form-group">
											<label class="form-label mb-1" for="explanation">{{ __('admin.explanation')}}</label>
											<textarea class="form-control" id="explanation" name="explanation" rows="4" placeholder="{{ $l == 'en' ? 'Optional explanation for the question' : 'شرح اختياري للسؤال' }}">{{ old('explanation') }}</textarea>
										</div>
									</div>
									
									<!-- Explanation for Assessment (Arabic) -->
									<div class="col-lg-6 mb-4 assessment-section" style="display: none;">
										<div class="form-group">
											<label class="form-label mb-1" for="explanation_QA_ar">{{ __('admin.explanation_QA_ar') }}</label>
											<textarea class="form-control" id="explanation_QA_ar" name="explanation_QA_ar" rows="3" placeholder="{{ $l == 'en' ? 'Arabic explanation' : 'الشرح بالعربية' }}">{{ old('explanation_QA_ar') }}</textarea>
										</div>
									</div>
									
									<!-- Explanation for Assessment (English) -->
									<div class="col-lg-6 mb-4 assessment-section" style="display: none;">
										<div class="form-group">
											<label class="form-label mb-1" for="explanation_QA_en">{{ __('admin.explanation_QA_en') }}</label>
											<textarea class="form-control" id="explanation_QA_en" name="explanation_QA_en" rows="3" placeholder="{{ $l == 'en' ? 'English explanation' : 'الشرح بالإنجليزية' }}">{{ old('explanation_QA_en') }}</textarea>
										</div>
									</div>

									<!-- Form Actions -->
									<div class="col-12 mt-12">
										<div class="d-flex justify-content-end gap-3">
											<a href="{{ route('questions-bank.index') }}" class="btn btn-outline-secondary">
												<i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-1"></i>
												{{__('admin.Cancel')}}
											</a>
											<button type="submit" class="btn btn-primary">
												<i class="ti ti-device-floppy me-1"></i>
												{{__('admin.Save')}}
											</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<!-- Question Creation Form end -->
		</div>
	</div>
</div>
<!-- END: Content-->
@stop

@section('pagescripts')
<script>
// Question Bank Form Logic
const organizations = {!! json_encode(collect($organizations)->map(function($org) {
	return [
		'id' => is_array($org) ? $org['id'] : $org->id, 
		'questionsCategories' => collect(is_array($org) ? ($org['questionsCategories'] ?? $org['questions_categories'] ?? []) : $org->questionsCategories)->map(function($cat) {
			return [
				'id' => is_array($cat) ? $cat['id'] : $cat->id, 
				'title' => is_array($cat) ? $cat['title'] : $cat->title
			];
		})->toArray()
	];
})->toArray()) !!};

function toggleSections(questionGroup) {
	// Hide all sections
	document.querySelectorAll('.quiz-section, .survey-section, .assessment-section, .quiz-survey-section')
		.forEach(section => section.style.display = 'none');
	document.querySelector('#survey-type-section').style.display = 'none';
	document.querySelector('#answers-section').style.display = 'none';

	// Remove required attributes from conditional fields
	document.querySelector('#survey_type').removeAttribute('required');
	document.querySelector('#ascategory_id').removeAttribute('required');
	document.querySelector('#subcategory_id').removeAttribute('required');
	document.querySelector('#category_id').removeAttribute('required');
	document.querySelector('#type').removeAttribute('required');
	document.querySelector('#difficulty').removeAttribute('required');

	// Show relevant sections based on question group
	if (questionGroup === 'quiz') {
		document.querySelectorAll('.quiz-section, .quiz-survey-section').forEach(s => s.style.display = 'block');
		// Add required attributes for quiz fields
		document.querySelector('#category_id').setAttribute('required', 'required');
		document.querySelector('#type').setAttribute('required', 'required');
		document.querySelector('#difficulty').setAttribute('required', 'required');
		updateQuestionCategories();
	} else if (questionGroup === 'survey') {
		document.querySelector('#survey-type-section').style.display = 'block';
		document.querySelectorAll('.quiz-survey-section').forEach(s => s.style.display = 'block');
		// Add required attribute for survey type
		document.querySelector('#survey_type').setAttribute('required', 'required');
	} else if (questionGroup === 'assessment') {
		document.querySelectorAll('.assessment-section').forEach(s => s.style.display = 'block');
		document.querySelector('#answers-section').style.display = 'block';
		// Add required attributes for assessment fields
		document.querySelector('#ascategory_id').setAttribute('required', 'required');
		document.querySelector('#subcategory_id').setAttribute('required', 'required');
	}
}

function updateQuestionCategories() {
	const orgSelect = document.querySelector('#organization_id');
	const catSelect = document.querySelector('#category_id');
	const selectedOrgs = Array.from(orgSelect.selectedOptions).map(opt => parseInt(opt.value));
	const oldValue = {{ old('category_id') ? old('category_id') : 'null' }};
	
	catSelect.innerHTML = '<option value="">{{ $l == 'en' ? 'Choose question category...' : 'اختر فئة السؤال...' }}</option>';
	
	if (selectedOrgs.length > 0) {
		const allCategories = [];
		selectedOrgs.forEach(orgId => {
			const org = organizations.find(o => o.id === orgId);
			if (org?.questionsCategories) {
				org.questionsCategories.forEach(cat => {
					if (!allCategories.find(c => c.id === cat.id)) {
						allCategories.push(cat);
					}
				});
			}
		});
		
		allCategories.forEach(category => {
			const option = document.createElement('option');
			option.value = category.id;
			option.textContent = category.title;
			if (oldValue && category.id == oldValue) option.selected = true;
			catSelect.appendChild(option);
		});
	}
}

function showQuizInputs() {
	document.querySelectorAll('.quiz-input').forEach(input => input.style.display = 'block');
}

// Initialize form when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
	const questionGroupSelect = document.querySelector('#question_group');
	const surveyTypeSelect = document.querySelector('#survey_type');
	const typeSelect = document.querySelector('#type');
	const organizationSelect = document.querySelector('#organization_id');

	// Event listeners
	questionGroupSelect.addEventListener('change', function() {
		toggleSections(this.value);
	});

	surveyTypeSelect.addEventListener('change', function() {
		document.querySelector('#answers-section').style.display = this.value === 'stars' ? 'none' : 'block';
	});

	typeSelect.addEventListener('change', function() {
		const answersSection = document.querySelector('#answers-section');
		
		if (this.value === 'tfq') {
			answersSection.style.display = 'none';
		} else if (this.value === 'mcq') {
			answersSection.style.display = 'block';
			showQuizInputs();
		}
	});

	organizationSelect.addEventListener('change', function() {
		updateQuestionCategories();
	});

	// Auto-select single organization
	if (organizationSelect.options.length === 2) {
		organizationSelect.options[1].selected = true;
		organizationSelect.dispatchEvent(new Event('change'));
	}

	// Answer management
	const addAnswerBtn = document.querySelector('#addNewAnswer');
	const answersContainer = document.querySelector('#answersContainer');

	addAnswerBtn?.addEventListener('click', function(e) {
		e.preventDefault();
		const questionGroup = document.querySelector('#question_group').value;
		const currentAnswers = answersContainer.querySelectorAll('.answer-item').length;
		
		if ((questionGroup === 'survey' && currentAnswers >= 5) || 
			(questionGroup === 'assessment' && currentAnswers >= 3)) {
			alert('Maximum answers reached.');
			return;
		}
		
		addAnswerItem(questionGroup);
	});

	function addAnswerItem(questionGroup) {
		const answerItem = document.createElement('div');
		answerItem.className = 'answer-item mb-3';
		var statusSelect = questionGroup === 'quiz' ? `<select name="quiz_answer_status[]" class="form-select quiz-input">
				<option value="true">{{ __('admin.quizAnswerCorrect') }}</option>
				<option value="false">{{ __('admin.quizAnswerWrong') }}</option>
			</select>` : '';
		answerItem.innerHTML = `
			<div class="d-flex align-items-center gap-2">
				<input type="text" name="quiz_answer_text[]" class="form-control" placeholder="{{ $l == 'en' ? 'Answer text' : 'نص الإجابة' }}">
				${statusSelect}
				<button type="button" class="btn btn-outline-danger btn-sm remove-answer-btn">
					<i class="ti ti-trash"></i>
				</button>
			</div>
		`;
		
		answersContainer.appendChild(answerItem);
		
		answerItem.querySelector('.remove-answer-btn').addEventListener('click', function() {
			answerItem.remove();
			updateRemoveButtons();
		});

		updateRemoveButtons();
	}

	function updateRemoveButtons() {
		const removeButtons = answersContainer.querySelectorAll('.remove-answer-btn');
		removeButtons.forEach(btn => {
			btn.style.display = removeButtons.length > 1 ? 'block' : 'none';
		});
	}

	// Category/Subcategory AJAX
	document.querySelector('#ascategory_id')?.addEventListener('change', function() {
		const subcategorySelect = document.querySelector('#subcategory_id');
		subcategorySelect.innerHTML = '<option value="">{{ $l == 'en' ? 'Choose subcategory...' : 'اختر القسم الفرعي...' }}</option>';
		
		if (this.value) {
			fetch("{{ route('subcategories.list_by_category') }}?category_id=" + this.value)
				.then(response => response.json())
				.then(data => {
					Object.keys(data).forEach(id => {
						const option = document.createElement('option');
						option.value = id;
						option.textContent = data[id];
						subcategorySelect.appendChild(option);
					});
				});
		}
	});

	// Initialize
	toggleSections(questionGroupSelect.value);
	updateRemoveButtons();
});
</script>
@stop
