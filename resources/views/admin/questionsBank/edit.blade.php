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


@section('content')
<!-- BEGIN: Content-->
<div class="">
	<div class="">
		<div class="content-overlay"></div>
		
		<div class="content-body">
			<!-- Question Edit Form start -->
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-8 pb-4">
								<h5 class="card-title mb-0">
									{{__('admin.Edit Question')}}
								</h5>
								<p class="card-text text-muted mt-1 mb-0">
									{{ $l == 'en' ? 'Update the form below to edit the question' : 'حدث النموذج أدناه لتعديل السؤال' }}
								</p>
							</div>
							
							@if( session()->has('success') )
							<div class="alert alert-success d-flex align-items-center mb-4" role="alert">
								<i class="ti ti-check-circle me-3"></i>
								<div>
									{{ __('admin.'.session()->get('success')) }} 
								</div>
								<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							@endif
							
							@if( session()->has('error') )
							<div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
								<i class="ti ti-alert-triangle me-3"></i>
								<div>
									{{ __('admin.'.session()->get('error')) }} 
								</div>
								<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							@endif
							
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
							<form method="post" action="{{ route('questions-bank.update', ['questions_bank' => $question->id]) }}" class="needs-validation">
								{!! csrf_field() !!}
								<input name="_method" type="hidden" value="PATCH" />
								
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
														{{ in_array($organization->id, $selectedOrganizations) ? 'selected' : '' }}>
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
												<option value="quiz" {{ $question->group == 'quiz' ? 'selected' : '' }}>{{ __('admin.question_type_quiz') }}</option>
												<option value="survey" {{ $question->group == 'survey' ? 'selected' : '' }}>{{ __('admin.question_type_survey') }}</option>
												@if($_user->ableTo('Assessment.Questions.Manage', $permissions))
												<option value="assessment" {{ $question->group == 'assessment' ? 'selected' : '' }}>{{ __('admin.question_type_assessment') }}</option>
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
												<option value="stars" {{ $question->survey_type == 'stars' ? 'selected' : '' }}> {{ __('admin.surveyTypeStarRating') }} </option>
												<option value="radio" {{ $question->survey_type == 'radio' ? 'selected' : '' }}> {{ __('admin.surveyTypeRadioButtons') }} </option>
												<option value="answers" {{ $question->survey_type == 'answers' ? 'selected' : '' }}> {{ __('admin.surveyQuestionAndAnswer') }} </option>
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
													<option value="{{ $category->id }}" {{ $category->id == $question->ascategory_id ? 'selected' : '' }}>{{ $category->{'category_name_'. $l} }}</option>
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
													<option value="{{ $subcategory->id }}" {{ $subcategory->id == $question->subcategory_id ? 'selected' : '' }}>{{ $subcategory->{'name_'. $l} }}</option>
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
												<option value="">{{ $l == 'en' ? 'Choose question category...' : 'اختر فئة السؤال...' }}</option>
												@foreach( $organizations->first()->questionsCategories as $category )
													<option value="{{ $category['id'] }}" {{ $question->category_id == $category['id'] ? 'selected' : '' }}>{{ $category['title'] }}</option>
												@endforeach
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select a question category.' : 'يرجى اختيار فئة السؤال.' }}</div>
										</div>
									</div>
									
									<!-- Question Text - Full Width -->
									<div class="col-12">
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="question">{{ __('admin.question')}}</label>
											<textarea name="question" class="form-control" id="question" placeholder="{{ __('admin.question') }}" rows="3" required>{{ $question->question }}</textarea>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please enter the question.' : 'يرجى إدخال السؤال.' }}</div>
										</div>
									</div>

									<!-- Question Type - Only for quiz -->
									<div class="col-lg-6">
										<div class="form-group mb-4 quiz-section" style="display: none;">
											<label class="form-label mb-1" for="type">{{ __('admin.question_Type')}}</label>
											<select class="form-control form-select" name="type" id="type">
												<option value="">{{ $l == 'en' ? 'Choose question type...' : 'اختر نوع السؤال...' }}</option>
												<option value="mcq" {{ $question->type == 'mcq' ? 'selected' : '' }}>{{ __('admin.type_mcq') }}</option>
												<option value="tfq" {{ $question->type == 'tfq' ? 'selected' : '' }}>{{ __('admin.type_tfq') }}</option>
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
												<option value="easy" {{ $question->difficulty == 'easy' ? 'selected' : '' }}>{{ __('admin.difficulty_easy') }}</option>
												<option value="medium" {{ $question->difficulty == 'medium' ? 'selected' : '' }}>{{ __('admin.difficulty_medium') }}</option>
												<option value="hard" {{ $question->difficulty == 'hard' ? 'selected' : '' }}>{{ __('admin.difficulty_hard') }}</option>
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select a difficulty level.' : 'يرجى اختيار مستوى الصعوبة.' }}</div>
										</div>
									</div>

									<!-- Tags -->
									<div class="col-12">
										<div class="form-group mb-4 quiz-survey-section" style="display: none;">
											<label class="form-label mb-1" for="tags">{{ __('admin.questionTags')}}</label>
											<input value="{{ optional($question->tags)->implode(',') }}" name="tags" type="text" class="form-control" id="tags" placeholder="{{ __('admin.questionTagsPlaceholder') }}" />
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
											@if( !empty( $question->answers ) )
											@foreach($question->answers as $key => $answer)
											<div class="answer-item mb-3">
												<div class="d-flex align-items-center gap-2">
													@php
													if($question['group'] == 'quiz') {
														$status = $answer['status'] ?? false;
													} else {
														$status = '';
													}
													@endphp
													<input type="text" name="quiz_answer_text[]" class="form-control" placeholder="{{ $l == 'en' ? 'Answer text' : 'نص الإجابة' }}" value="{{ $answer['text'] }}">
													<!-- Status select for quiz only - rendered dynamically -->
													<button type="button" class="btn btn-outline-danger btn-sm remove-answer-btn">
														<i class="ti ti-trash"></i>
													</button>
												</div>
											</div>
											@endforeach
											@else
											<div class="answer-item mb-3">
												<div class="d-flex align-items-center gap-2">
													<input type="text" name="quiz_answer_text[]" class="form-control" placeholder="{{ $l == 'en' ? 'Answer text' : 'نص الإجابة' }}">
													<!-- Status select for quiz only - rendered dynamically -->
													<button type="button" class="btn btn-outline-danger btn-sm remove-answer-btn" style="display: none;">
														<i class="ti ti-trash"></i>
													</button>
												</div>
											</div>
											@endif
										</div>
									</div>

									<!-- Explanation for Quiz/Survey -->
									<div class="col-12 mb-4 quiz-survey-section" style="display: none;">
										<div class="form-group">
											<label class="form-label mb-1" for="explanation">{{ __('admin.explanation')}}</label>
											<textarea class="form-control" id="explanation" name="explanation" rows="4" placeholder="{{ $l == 'en' ? 'Optional explanation for the question' : 'شرح اختياري للسؤال' }}">{{ $question->explanation }}</textarea>
										</div>
									</div>
									
									<!-- Explanation for Assessment (Arabic) -->
									<div class="col-lg-6 mb-4 assessment-section" style="display: none;">
										<div class="form-group">
											<label class="form-label mb-1" for="explanation_QA_ar">{{ __('admin.explanation_QA_ar') }}</label>
											<textarea class="form-control" id="explanation_QA_ar" name="explanation_QA_ar" rows="3" placeholder="{{ $l == 'en' ? 'Arabic explanation' : 'الشرح بالعربية' }}">{{ $question->explanation_QA_ar }}</textarea>
										</div>
									</div>
									
									<!-- Explanation for Assessment (English) -->
									<div class="col-lg-6 mb-4 assessment-section" style="display: none;">
										<div class="form-group">
											<label class="form-label mb-1" for="explanation_QA_en">{{ __('admin.explanation_QA_en') }}</label>
											<textarea class="form-control" id="explanation_QA_en" name="explanation_QA_en" rows="3" placeholder="{{ $l == 'en' ? 'English explanation' : 'الشرح بالإنجليزية' }}">{{ $question->explanation_QA_en }}</textarea>
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
			<!-- Question Edit Form end -->
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

	// Remove required attributes and disable conditional fields
	const surveyTypeField = document.querySelector('#survey_type');
	const ascategoryField = document.querySelector('#ascategory_id');
	const subcategoryField = document.querySelector('#subcategory_id');
	const categoryField = document.querySelector('#category_id');
	const typeField = document.querySelector('#type');
	const difficultyField = document.querySelector('#difficulty');
	
	// Disable all conditional fields first
	surveyTypeField.removeAttribute('required');
	surveyTypeField.disabled = true;
	ascategoryField.removeAttribute('required');
	ascategoryField.disabled = true;
	subcategoryField.removeAttribute('required');
	subcategoryField.disabled = true;
	categoryField.removeAttribute('required');
	categoryField.disabled = true;
	typeField.removeAttribute('required');
	typeField.disabled = true;
	difficultyField.removeAttribute('required');
	difficultyField.disabled = true;

	// Show relevant sections based on question group
	if (questionGroup === 'quiz') {
		document.querySelectorAll('.quiz-section, .quiz-survey-section').forEach(s => s.style.display = 'block');
		// Enable and add required attributes for quiz fields
		categoryField.removeAttribute('disabled');
		categoryField.setAttribute('required', 'required');
		typeField.removeAttribute('disabled');
		typeField.setAttribute('required', 'required');
		difficultyField.removeAttribute('disabled');
		difficultyField.setAttribute('required', 'required');
		updateQuestionCategories();
		showQuizInputs();
	} else if (questionGroup === 'survey') {
		document.querySelector('#survey-type-section').style.display = 'block';
		document.querySelectorAll('.quiz-survey-section').forEach(s => s.style.display = 'block');
		// Enable and add required attribute for survey type
		surveyTypeField.removeAttribute('disabled');
		surveyTypeField.setAttribute('required', 'required');
	} else if (questionGroup === 'assessment') {
		document.querySelectorAll('.assessment-section').forEach(s => s.style.display = 'block');
		document.querySelector('#answers-section').style.display = 'block';
		// Enable and add required attributes for assessment fields
		ascategoryField.removeAttribute('disabled');
		ascategoryField.setAttribute('required', 'required');
		subcategoryField.removeAttribute('disabled');
		subcategoryField.setAttribute('required', 'required');
	}
}

function updateQuestionCategories() {
	const orgSelect = document.querySelector('#organization_id');
	const catSelect = document.querySelector('#category_id');
	const selectedOrgs = Array.from(orgSelect.selectedOptions).map(opt => parseInt(opt.value));
	const currentValue = catSelect.value;
	
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
			if (currentValue && category.id == currentValue) option.selected = true;
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

	// Trigger type change on page load to ensure proper display
	if (typeSelect.value) {
		typeSelect.dispatchEvent(new Event('change'));
	}

	organizationSelect.addEventListener('change', function() {
		updateQuestionCategories();
	});

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

	// Add event listeners for existing remove buttons
	answersContainer.querySelectorAll('.remove-answer-btn').forEach(btn => {
		btn.addEventListener('click', function() {
			btn.closest('.answer-item').remove();
			updateRemoveButtons();
		});
	});

	// Category/Subcategory AJAX
	document.querySelector('#ascategory_id')?.addEventListener('change', function() {
		const subcategorySelect = document.querySelector('#subcategory_id');
		subcategorySelect.innerHTML = '<option value="">{{ $l == 'en' ? 'Choose subcategory...' : 'اختر القسم الفرعي...' }}</option>';
		
		if (this.value) {
			fetch("{{ route('subcategories.list_by_category') }}?category_id=" + this.value)
				.then(response => response.json())
				.then(data => {
					const currentValue = subcategorySelect.dataset.currentValue || '';
					Object.keys(data).forEach(id => {
						const option = document.createElement('option');
						option.value = id;
						option.textContent = data[id];
						if (id === currentValue) option.selected = true;
						subcategorySelect.appendChild(option);
					});
				});
		}
	});

	// Initialize
	toggleSections(questionGroupSelect.value);
	updateRemoveButtons();
	
	// Show answers section if question has existing answers and it's not a TFQ question
	const hasAnswers = {{ !empty($question->answers) ? 'true' : 'false' }};
	const questionType = '{{ $question->type ?? '' }}';
	const questionGroup = questionGroupSelect.value;
	
	if (hasAnswers && questionType !== 'tfq') {
		document.querySelector('#answers-section').style.display = 'block';
		
		// Show appropriate input types based on question group
		if (questionGroup === 'quiz') {
			showQuizInputs();
		}
	}
	
	// Set current subcategory value for AJAX loading
	const subcategorySelect = document.querySelector('#subcategory_id');
	if (subcategorySelect && subcategorySelect.value) {
		subcategorySelect.dataset.currentValue = subcategorySelect.value;
	}
});
</script>
@stop
