@extends('layouts.admin')

@php
$pills = ['analysis-assessment', 'question-assessment', 'history'];
@endphp

@section('content')
<div class="">
  <div class="">
    <!-- Pills (Mobile) -->
    <div class="row mb-4 d-block d-md-none">
      <div class="col-12">
        <ul class="nav nav-pills">
          @foreach ($pills as $key => $pill)
          <li class="nav-item me-1">
            <a href="/dashboard/{{ $key == 2 ? 'history-assessment' : $pill }}" class="nav-link {{ $key == 1 ? 'active' : '' }}">
              <span>
                @if ($pill == 'analysis-assessment')
                  {{ __('admin.Analysis_Assessment') }}
                  @elseif($pill == 'question-assessment')
                  {{ __('admin.Question_assessment') }}
                  @else
                  {{ __('admin.History') }}
                  @endif
              </span>
            </a>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
    <!-- Pills (Mobile) -->
    <!-- Pills -->
    <div class="row mb-4 d-none d-md-block">
      <div class="col-12">
        <div class="card shadow-sm">
          <ul class="nav nav-pills flex-nowrap px-2 py-3">
            @foreach ($pills as $key => $pill)
            <li class="nav-item me-1">
              <a href="/dashboard/{{ $key == 2 ? 'history-assessment' : $pill }}" class="nav-link {{ $key == 1 ? 'active' : '' }}">
                <span>
                  @if ($pill == 'analysis-assessment')
                    {{ __('admin.Analysis_Assessment') }}
                  @elseif($pill == 'question-assessment')
                    {{ __('admin.Question_assessment') }}
                  @else
                    {{ __('admin.History') }}
                  @endif
                </span>
              </a>
            </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
    <!-- / Pills -->

    @if(!$assessment_user)
      <div class="card">
        <div class="card-body">
          <div class="d-flex flex-column align-items-center justify-content-center gap-3">
            <h4 class="text-center mb-0">{{ __('admin.No_Assessment') }}</h4>
            <h6 class="text-center text-secondary">{{ __('admin.No_Assessment_right_now') }}</h6>

            <div class="w-px-250">
              <button 
                id="startAssessmentBtn"
                class="btn btn-lg btn-primary w-100"
                data-loading-text="<span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span>">
                <i class="ti ti-plus me-2"></i>
                {{ __('admin.new_assessment') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="row px-4">
        <div class="col-md-3 col-12 position-relative" style="height: fit-content;">
          @foreach ($user_scores as $key => $assessment)
          @php
          
          if($assessment != null){
          $totalQuestions = $assessment->question->where('assessment_id', $assessment_user->id)->count();
          $totalAnswered = $assessment->question->where('Result_assessment', '!=', null)
                                        ->where('assessment_id', $assessment_user->id)
                                        ->count();
          $totalNotAnswered = $assessment->question->where('Result_assessment', '=', null)
                                        ->where('assessment_id', $assessment_user->id)
                                        ->count();
          }else{
            $totalQuestions = 0;
            $totalAnswered =0;
            $totalNotAnswered =0;
          }
          // حساب التقدم الإجمالي عبر جميع الأقسام
          $progress = $totalQuestions > 0 ? ($totalAnswered / $totalQuestions) * 100 : 0;

          $status = $progress == 100 ? 'completed' : ($progress > 0 ? 'in-progress' : 'not-started');
          @endphp

          <div 
            id="assessmentTab"
            class="CategoryGetId assessment-tab card shadow-sm mb-5 position-relative cursor-pointer"
            {{-- style="border: {{ $key == 0 ? '1px solid black;' : 'none' }};" --}}
            onclick="changeAssessment({{ $key }})"
          >
            <div class="d-flex justify-content-between align-items-center p-5">
              <div class="d-flex flex-column">
                <h6 class="mb-0 text-capitalize">
                  @if($assessment != null)
                  {{ str_replace('-', ' ', $assessment['category_name_' . $l]) }}
                  @endif
                </h6>
                {{-- @if ($totalAnswered && $totalAnswered == $totalQuestions) --}}
                <span id="category-completed" class="text-success fw-bold small mt-2" style="display: {{ $totalAnswered > 0 && $totalAnswered == $totalQuestions ? 'block' : 'none' }};">
                  {{ __('admin.Done_All') }}
                </span>
                {{-- @else --}}
                <span id="assessmentTotalCount" class="text-secondary mt-2" style="display: {{ $totalAnswered < $totalQuestions ? 'block' : 'none' }}; font-size: 10px;">
                  {{ __('admin.Questions_answered') }} {{ $totalAnswered }}/{{ $totalQuestions }}
                </span>
                {{-- @endif --}}
              </div>
              <x-circular-progress size="40" color="#28c76f" progress="{{ $progress }}" />
            </div>
            {{-- @if ($status == 'completed' || $progress == 100) --}}
              <div id="completedIcon" style="position: absolute; {{ $l == 'en' ? 'left' : 'right' }}: -1.5rem; top: 50%; transform: translateY(-50%); z-index: 1;
                display: {{ $progress == 100 ? 'block' : 'none' }};"
                class="w-px-20 h-px-20 rounded-circle bg-success justify-content-center align-items-center d-flex">
                <i class="ti ti-check text-white fs-6"></i>
              </div>
            {{-- @else --}}
              <div id="notCompletedIcon" style="position: absolute; {{ $l == 'en' ? 'left' : 'right' }}: -1.5rem; top: 50%; transform: translateY(-50%); z-index: 1; display: {{ $progress < 100 ? 'block' : 'none' }};"
                class="w-px-20 h-px-20 rounded-circle bg-light border border-secondary">
              </div>
            {{-- @endif --}}
          </div>
          @endforeach
          <div
            style="position: absolute; {{ $l == 'en' ? 'left' : 'right' }}: -0.15rem; top: 5px; height: 97%; border-left: 0.12rem dashed rgb(120, 108, 163);"
          >
          </div>
        </div>
        @php
        $num = 1;
        @endphp
        <div class="col-md-9 col-12">
          @foreach ($user_scores as $key => $assessment)
          @if($assessment != null)
          @php
          $categoryId = $assessment['id'];
          @endphp
          @endif
          <section id="assessmentContent"
            style="{{ $key == 0 ? 'display: block;' : 'display: none;' }}">
            <div class="d-flex flex-column gap-6">
              <div class="d-flex justify-content-md-between flex-wrap gap-3 gap-md-0">
                @if($assessment != null)
                @foreach ($assessment['subcategories'] as $key => $subcategory)
                @php
                $totalQuestionsSub = Count($subcategory['questions']);
                $totalAnsweredSub = Count($subcategory['questions']->where('Result_assessment', '!=', null));
                $totalNotAnsweredSub = Count($subcategory['questions']->where('Result_assessment', '=', null));
                @endphp

                <div 
                  class="shadow-sm cursor-pointer p-3 rounded bg-white d-flex flex-fill me-5 justify-content-center align-items-center" 
                  style="width: 140px; height: 80px; {{ $key == 0 ? 'border: 1px solid black;' : '' }}"
                  {{-- style="{{ $key == 0 ? 'border: 1px solid black;' : '' }}" --}}
                  id="category-tab" 
                  onclick="changeCategory({{ $key }})"
                >
                  <div class="d-flex flex-column gap-2 text-center" style="width: 100%;">
                    <span class="text-capitalize fw-bold small line-clamp-2">
                      {{ str_replace('-', ' ', $subcategory['name_' . $l]) }}
                    </span>
                    {{-- @if ($totalAnsweredSub > 0 && $totalQuestionsSub == $totalAnsweredSub) --}}
                    <span id="subcategory-completed" class="text-success fw-bold" style="font-size: 0.8rem; display: {{ $totalAnsweredSub == $totalQuestionsSub ? 'block' : 'none' }};">
                      {{ __('admin.subcategory_Done_All') }}
                    </span>
                    {{-- @else --}}
                    <span id="subCategory-count" class="text-secondary" style="font-size: 10px; display: {{ $totalAnsweredSub < $totalQuestionsSub ? 'block' : 'none' }};">
                      {{ __('admin.Questions_answered') }} {{ $totalAnsweredSub }}/{{ $totalQuestionsSub }}
                    </span>
                    {{-- @endif --}}
                  </div>
                </div>
                @endforeach
                @endif
              </div>
              @if($assessment != null)
              @foreach ($assessment['subcategories'] as $key => $subcategory)
              <div class="row" style="display: {{ $key == 0 ? 'block' : 'none' }};" id="questionsContainer">
                @if ($subcategory['questions']->isEmpty())
                <div class="col-12">
                  <div class="card shadow-sm">
                    <div class="card-body text-center text-secondary">
                      {{ __('admin.No_Questions_Available') }}
                    </div>
                  </div>
                </div>
                @else
                @foreach ($subcategory['questions'] as $key => $question)

                <div class="col-12 question-card-bus-con mb-3 " data-category="question-{{ $key }}" >
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <div class="d-flex align-items-center gap-3 mb-2 pb-2 border-bottom">
                        <span class="badge bg-primary rounded-circle d-flex justify-content-center align-items-center"
                          style="width: 30px; height: 30px;">
                          {{ $num++ }}
                        </span>
                        <h6 class="fw-bold mb-0">{{ $question['QuestionBank'] }}</h6>
                        <input type="hidden" name="question_id" class="question_id{{ $question['id'] }}" value="{{ $question['id'] }}">
                      </div>
                      <div class="mt-3">
                        @php
                          $options = json_decode($question['Answers']);
                        @endphp
                        @foreach ($options as $key => $option)
                          <div class="form-check AnswerQuestion" do="{{ $question['id'] }}">
                            <input 
                              class="form-check-input"
                              {{-- do="{{ $question['id'] }}"  --}}
                              type="radio" 
                              name="answer_{{ $question['id'] }}" 
                              id="answer-{{ $key }}"
                              value="{{ $key }}" 
                              {{ $question['score'] ==  $option->score ? 'checked' : '' }} 
                            />
                            <label 
                              class="form-check-label"
                              for="answer-{{ $key }}"
                              onclick="this.previousElementSibling.checked = true"
                            >
                              {{ $option->text }}
                            </label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
                @endif
              </div>
              @endforeach
              @endif
            </div>
          </section>
            
          @endforeach
          <div>
            @if($assessment != null)
            @foreach ($assessment['subcategories'] as $key => $subcategory)
            <div id="submitContainer" style="{{ $key == 0 ? 'display: flex;' : 'display: none;' }}">
              <div class="w-100 d-flex justify-content-end">
                <button id="submitButton" class="btn btn-primary" {{ $subcategory['questions']->isEmpty() ? 'disabled' : '' }}>{{ __('admin.Next') }}</button>
              </div>
            </div>
            @endforeach
            @endif
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
@stop

@section('pagescripts')
<script>
  // Handle assessment creation
  $(document).on('click', '#startAssessmentBtn', function() {
    const $btn = $(this);
    const originalText = $btn.html();
    
    // Show loading state
    $btn.prop('disabled', true).html($btn.data('loading-text'));
    
    // Make AJAX request to create assessment
    $.ajax({
      url: '{{ route("assessments.assign") }}',
      type: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        _method: 'POST'
      },
      dataType: 'json',
      success: function(response) {
        if (!response.success) {
          releaseFailureAlert({
            title: '{{ __('admin.Error') }}',
            message: response.message ? response.message : '{{ __('admin.unexpected_error') }}',
          });          
          return;
        }

        // Redirect to assessment page on success
        window.location.href = response.redirect;
      },
      error: function(xhr) {
        let errorMessage = xhr.responseJSON.message ? xhr.responseJSON.message : '{{ __("admin.unexpected_error") }}';
        releaseFailureAlert({
          title: '{{ __('admin.Error') }}',
          message: errorMessage
        })
        $btn.prop('disabled', false).html(originalText);
      }
    });
  });
  
 // Get the total count of current subcategory
const getSubCategoryTotalCount = (userScores) => {
  if (!userScores || userScores.length === 0) return;

  const activeCategoryTab = parseInt(localStorage.getItem('activeCategoryTab') || 0);
  const activeSubCategoryTab = parseInt(localStorage.getItem('activeSubCategoryTab') || 0);
  
  const currentAssessment = userScores[activeCategoryTab];
  if (!currentAssessment || !currentAssessment.subcategories) return;
  
  const currentSubCategory = currentAssessment.subcategories[activeSubCategoryTab];
  if (!currentSubCategory) return;

  // Find the active assessment content section
  const activeAssessmentContent = document.querySelectorAll('#assessmentContent')[activeCategoryTab];
  if (!activeAssessmentContent) return;

  const subCategoryTabs = activeAssessmentContent.querySelectorAll('#category-tab');
  const subCategoryTab = subCategoryTabs[activeSubCategoryTab];
  if (!subCategoryTab) return;

  const subCategoryTotalCount = subCategoryTab.querySelector('#subCategory-count');
  const subcategoryCompleted = subCategoryTab.querySelector('#subcategory-completed');
  
  if (!subCategoryTotalCount || !subcategoryCompleted) return;

  // Calculate totals - handle both property name formats
  const totalAnswered = currentSubCategory.contAnswered || currentSubCategory.answered || 0;
  const totalNotAnswered = currentSubCategory.contNotAnswered || currentSubCategory.not_answered || 0;
  const totalQuestions = totalAnswered + totalNotAnswered;
  
  if (totalQuestions === 0) return;

  const progressPercentage = Math.round((totalAnswered / totalQuestions) * 100);
  const subCategoryCountText = `{{ __('admin.Questions_answered') }} ${totalAnswered}/${totalQuestions}`;
  
  subCategoryTotalCount.textContent = subCategoryCountText;

  // Update visibility based on completion
  if (progressPercentage === 100) {
    subcategoryCompleted.style.display = 'block';
    subCategoryTotalCount.style.display = 'none';
  } else {
    subcategoryCompleted.style.display = 'none';
    subCategoryTotalCount.style.display = 'block';
  }
};

// Get the total count of current assessment
const getAssessmentTotalCount = (userScores) => {
  if (!userScores || userScores.length === 0) return;

  const activeCategoryTab = parseInt(localStorage.getItem('activeCategoryTab') || 0);
  const assessmentTabs = document.querySelectorAll('#assessmentTab');
  
  if (!assessmentTabs[activeCategoryTab]) return;
  
  const currentAssessment = userScores[activeCategoryTab];
  if (!currentAssessment) return;

  const assessmentTab = assessmentTabs[activeCategoryTab];
  
  // Calculate totals - handle both property name formats
  const totalAnswered = currentAssessment.contAnswered || currentAssessment.answered || 0;
  const totalNotAnswered = currentAssessment.contNotAnswered || currentAssessment.not_answered || 0;
  const assessmentTotalQuestions = totalAnswered + totalNotAnswered;
  
  if (assessmentTotalQuestions === 0) return;

  const progressPercentage = Math.round((totalAnswered / assessmentTotalQuestions) * 100);

  // Update completion icons
  const completedIcon = assessmentTab.querySelector('#completedIcon');
  const notCompletedIcon = assessmentTab.querySelector('#notCompletedIcon');
  const categoryCompleted = assessmentTab.querySelector('#category-completed');
  const assessmentTotalCount = assessmentTab.querySelector('#assessmentTotalCount');

  if (completedIcon && notCompletedIcon) {
    if (progressPercentage === 100) {
      completedIcon.style.display = 'block';
      notCompletedIcon.style.display = 'none';
    } else {
      completedIcon.style.display = 'none';
      notCompletedIcon.style.display = 'block';
    }
  }

  // Update progress circle
  updateProgressCircle(assessmentTab, progressPercentage);
  
  // Update total count text
  updateAssessmentTotalCount({
    assessmentTab, 
    totalAnswered, 
    assessmentTotalQuestions,
    progressPercentage
  });
};

// Update the progress circle
const updateProgressCircle = (assessmentTab, progressPercentage) => {
  if (!assessmentTab) return;
  
  const progressCircle = assessmentTab.querySelector('.progress-circle');
  if (!progressCircle) return;
  
  const assessmentTabProgress = progressCircle.querySelector('span');
  const pathProgress = progressCircle.querySelector('svg path:nth-child(2)');
  
  if (assessmentTabProgress) {
    assessmentTabProgress.textContent = `${progressPercentage?.toFixed(1)}%`;
  }
  
  if (pathProgress) {
    const dashArrayValue = `${progressPercentage}, 100`;
    pathProgress.setAttribute('stroke-dasharray', dashArrayValue);
  }
};

// Update the total count of answered questions
const updateAssessmentTotalCount = (payload) => {
  const { assessmentTab, totalAnswered, assessmentTotalQuestions, progressPercentage } = payload;
  
  if (!assessmentTab) return;
  
  const assessmentTotalCount = assessmentTab.querySelector('#assessmentTotalCount');
  const categoryCompleted = assessmentTab.querySelector('#category-completed');
  
  if (!assessmentTotalCount || !categoryCompleted) return;
  
  const textContentValue = `{{ __('admin.Questions_answered') }} ${totalAnswered}/${assessmentTotalQuestions}`;
  assessmentTotalCount.textContent = textContentValue;

  // Handle completion state
  if (progressPercentage === 100) {
    categoryCompleted.style.display = 'block';
    assessmentTotalCount.style.display = 'none';
    
    // Auto-navigate to next assessment after delay
    setTimeout(() => {
      const activeCategoryTab = parseInt(localStorage.getItem('activeCategoryTab') || 0);
      const nextAssessment = activeCategoryTab + 1;
      const assessmentTabs = document.querySelectorAll('#assessmentTab');
      
      // Only navigate if there's a next assessment
      if (nextAssessment < assessmentTabs.length) {
        changeAssessment(nextAssessment);
        changeCategory(0);
      }
    }, 1500);
  } else {
    categoryCompleted.style.display = 'none';
    assessmentTotalCount.style.display = 'block';
  }
};

// Handle the answer question
const handleAnswerQuestion = (questionId, answer) => {
  $.ajax({
    url: '/dashboard/answer-assessment',
    method: 'post',
    data: {
      'questionId': questionId,
      'answer': answer,
      _token: $('meta[name="csrf-token"]').attr('content'),
    },
    success: function(response) {
      // Check if assessment is fully completed
      if (response.message == 1) {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: '{{ __('admin.Assessment_completed_successfully') }}',
          showCancelButton: false,
          showConfirmButton: true,
          confirmButtonText: "{{ __('admin.OK') }}",
          customClass: {
            confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
          timer: 2000,
        }).then(() => {
          // Reset to first assessment and category
          localStorage.setItem('activeCategoryTab', 0);
          localStorage.setItem('activeSubCategoryTab', 0);
          location.reload();
        });
        return;
      }

      // Update counts and progress if user_scores are provided
      if (response?.user_scores) {
        const userScores = response.user_scores;
        
        // Update both assessment and subcategory counts
        getAssessmentTotalCount(userScores);
        getSubCategoryTotalCount(userScores);
        
        // Check if current subcategory is complete
        const activeCategoryTab = parseInt(localStorage.getItem('activeCategoryTab') || 0);
        const activeSubCategoryTab = parseInt(localStorage.getItem('activeSubCategoryTab') || 0);
        const currentAssessment = userScores[activeCategoryTab];
        
        if (currentAssessment && currentAssessment.subcategories) {
          const currentSubCategory = currentAssessment.subcategories[activeSubCategoryTab];
          
          if (currentSubCategory) {
            const totalAnswered = currentSubCategory.contAnswered || currentSubCategory.answered || 0;
            const totalNotAnswered = currentSubCategory.contNotAnswered || currentSubCategory.not_answered || 0;
            const totalQuestions = totalAnswered + totalNotAnswered;
            
            // Auto-navigate to next subcategory if current is complete
            if (totalQuestions > 0 && totalAnswered === totalQuestions) {
              setTimeout(() => {
                const activeCategoryTabs = document.querySelectorAll('#assessmentContent:not([style*="display: none"]) #category-tab');
                const nextSubCategory = activeSubCategoryTab + 1;
                
                // Navigate to next subcategory if it exists
                if (nextSubCategory < activeCategoryTabs.length) {
                  changeCategory(nextSubCategory);
                }
              }, 1000);
            }
          }
        }
      }
    },
    error: function(error) {
      console.error('Error submitting answer:', error);
      
      // Show error message to user
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '{{ __('admin.An_error_occurred_please_try_again') }}',
        confirmButtonText: "{{ __('admin.OK') }}",
        customClass: {
          confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
      });
    }
  });
};

// Handle the change assessment
$('.AnswerQuestion').click(function() {
  const questionId = $(this).attr('do');
  const answer = $('input[name="answer_' + questionId + '"]:checked').val();
  
  if (answer !== undefined) {
    handleAnswerQuestion(questionId, answer);
  }
});
</script>
<script>
  const assessmentTabs = document.querySelectorAll('.assessment-tab');
  const assessmentContainers = document.querySelectorAll('#assessmentContent'); 
  const questionsContainers = document.querySelectorAll('#questionsContainer');
  const categoryTabs = document.querySelectorAll('#category-tab');
  const submitContainers = document.querySelectorAll('#submitContainer');

  function changeAssessment(index) {
    assessmentContainers.forEach((container, i) => {
      if (i === index) {
        container.style.display = 'block';
      } else {
        container.style.display = 'none';
      }
    });

    assessmentTabs.forEach((tab, i) => {
      if (i === index) {
        localStorage.setItem('activeCategoryTab', i);
        localStorage.setItem('activeSubCategoryTab', 0);
        tab.style.border = '1px solid black';
      } else {
        tab.style.border = 'none';
      }
    });

    // Filter category tabs related to the current active section
    const activeCategoryTabs = document.querySelectorAll(`#assessmentContent:nth-child(${index + 1}) #category-tab`);
  };

  function changeCategory(index) {
    const activeCategoryTabs = document.querySelectorAll('#assessmentContent:not([style*="display: none"]) #category-tab');
    const activeQuestionsContainers = document.querySelectorAll('#assessmentContent:not([style*="display: none"]) #questionsContainer');

    activeCategoryTabs.forEach((tab, i) => {
      if (i === index) {
        tab.style.border = '1px solid black';
        localStorage.setItem('activeSubCategoryTab', i);
      } else {
        tab.style.border = 'none';
      }
    });

    activeQuestionsContainers.forEach((container, i) => {
      if (i === index) {
        container.style.display = 'block';
      } else {
        container.style.display = 'none';
      }
    });
  }

  submitContainers.forEach((submitContainer, i) => {
    submitContainer.addEventListener('click', () => {
      const button = submitContainer.querySelector('#submitButton');
      if (button && button.disabled) return;

      const activeCategoryTabs = document.querySelectorAll('#assessmentContent:not([style*="display: none"]) #category-tab');
      const activeIndex = Array.from(activeCategoryTabs).findIndex(tab => tab.style.border === '1px solid black');
      const nextIndex = (activeIndex + 1) % activeCategoryTabs.length;

      changeCategory(nextIndex); // Increase index by 1
    });
  });

  document.addEventListener('DOMContentLoaded', () => {
    const activeCategoryTab = localStorage.getItem('activeCategoryTab');
    const activeSubCategoryTab = localStorage.getItem('activeSubCategoryTab');

    if (activeCategoryTab) {
      changeAssessment(parseInt(activeCategoryTab || 0));
    }

    if (activeSubCategoryTab) {
      changeCategory(parseInt(activeSubCategoryTab || 0));
    }
  });
</script>
@stop