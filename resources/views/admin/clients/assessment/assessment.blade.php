@extends('layouts.admin')

@section('style')
<style>
  .rounded,
  .card,
  .nav-link {
    border-radius: 1rem !important;
  }

  .text-sm {
    font-size: 0.85rem;
  }

  .text-xs {
    font-size: 0.65rem;
  }

  .light-shadow {
    box-shadow: 0px 2px 0px 0px #F2F1F3;
  }

  .assessment-tab:hover {
    background-color: #F2F1F3;
    border: 1px solid #000;
    cursor: pointer;
  }

  .active-cat {
    background-color: #F2F1F3;
    border: 1px solid #000;
  }

  .line-clamp-2-elipsis {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
  }
</style>
@stop

@php
$pills = ['analysis-assessment', 'question-assessment', 'history'];
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
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
      <div class="row mt-8 d-flex justify-content-center">
        <div class="col-12 col-md-6">
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column align-items-center justify-content-center gap-3">
                <h4 class="text-center mb-0">{{ __('admin.No_Assessment') }}</h4>
                <h6 class="text-center text-secondary">{{ __('admin.No_Assessment_right_now') }}</h6>
                <button 
                  id="startAssessmentBtn"
                  class="btn btn-lg btn-primary"
                  data-loading-text="<span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span> {{ __('admin.Creating_Assessment') }}...">
                  {{ __('admin.Make_a_new_assessment') }}
                </button>
              </div>
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
            class="CategoryGetId assessment-tab card shadow-sm mb-3 position-relative"
            {{-- style="border: {{ $key == 0 ? '1px solid black;' : 'none' }};" --}}
            onclick="changeAssessment({{ $key }})"
          >
            <div class="d-flex justify-content-between align-items-center p-4">
              <div class="d-flex flex-column">
                <h6 class="mb-0 text-capitalize">
                  @if($assessment != null)
                  {{ str_replace('-', ' ', $assessment['category_name_' . $l]) }}
                  @endif
                </h6>
                {{-- @if ($totalAnswered && $totalAnswered == $totalQuestions) --}}
                <span id="category-completed" class="text-success fw-bold text-sm mt-2" style="display: {{ $totalAnswered > 0 && $totalAnswered == $totalQuestions ? 'block' : 'none' }};">
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
                  class="shadow-sm cursor-pointer p-3 bg-white d-flex justify-content-center align-items-center" 
                  style="width: 140px; height: 80px; border-radius: 1rem; {{ $key == 0 ? 'border: 1px solid black;' : '' }}"
                  {{-- style="{{ $key == 0 ? 'border: 1px solid black;' : '' }}" --}}
                  id="category-tab" 
                  onclick="changeCategory({{ $key }})"
                >
                  <div class="d-flex flex-column gap-2 text-center" style="width: 100%;">
                    <span class="text-capitalize fw-bold text-sm line-clamp-2-elipsis">
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
                <button id="submitButton" class="btn btn-primary">{{ __('admin.Next') }}</button>
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
        if (response.success) {
          // Redirect to assessment page on success
          window.location.href = response.redirect;
        } else {
          // Show error message
          showToast('error', response.message || '{{ __("An error occurred. Please try again.") }}');
          if (response.redirect) {
            // Redirect to profile if needed
            setTimeout(() => {
              window.location.href = response.redirect;
            }, 2000);
          } else {
            // Re-enable button
            $btn.prop('disabled', false).html(originalText);
          }
        }
      },
      error: function(xhr) {
        let errorMessage = '{{ __("An error occurred. Please try again.") }}';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }
        showToast('error', errorMessage);
        $btn.prop('disabled', false).html(originalText);
      }
    });
  });
  
  // Helper function to show toast messages
  function showToast(type, message) {
    const toast = `
      <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      </div>
    `;
    
    $('.toast-container').remove(); // Remove any existing toasts
    $('body').append(toast);
    $('.toast').toast({ autohide: true, delay: 5000 }).toast('show');
  }

  // Get the total count of current category
  const getSubCategoryTotalCount = (userScores) => {
    if (!userScores || userScores.length === 0) return;

    const activeCategoryTab = localStorage.getItem('activeCategoryTab');
    const activeSubCategoryTab = localStorage.getItem('activeSubCategoryTab');
    const currentAssessment = userScores[activeCategoryTab];
    const currentSubCategory = currentAssessment?.subcategories[activeSubCategoryTab];
    const subCategoryTabs = document.querySelectorAll('#assessmentContent:nth-child(' + (parseInt(activeCategoryTab) + 1) + ') #category-tab');
    const subCategoryTab = subCategoryTabs[activeSubCategoryTab];
    const subCategoryTotalCount = subCategoryTab.querySelector('#subCategory-count');
    const totalQuestions = currentSubCategory?.contNotAnswered + currentSubCategory?.contAnswered;
    const subCategoryCountText = `{{ __('admin.Questions_answered') }} ${currentSubCategory?.contAnswered}/${totalQuestions}`;
    
    subCategoryTotalCount.textContent = subCategoryCountText;

    const progressPercentage = Math.floor((currentSubCategory?.contAnswered / totalQuestions) * 100);

    const subcategoryCompleted = subCategoryTab.querySelector('#subcategory-completed');

    if (progressPercentage === 100) {
      subcategoryCompleted.style.display = 'block';
      subCategoryTotalCount.style.display = 'none';
    } else {
      subcategoryCompleted.style.display = 'none';
      subCategoryTotalCount.style.display = 'block';
    }
  }

  // Get the total count of current assessment
  const getAssessmentTotalCount = (userScores) => {
    if (!userScores || userScores.length === 0) return;

    const activeCategoryTab = localStorage.getItem('activeCategoryTab');
    const assessmentTabs = document.querySelectorAll('#assessmentTab');
    const currentAssessment = userScores[activeCategoryTab];
    const assessmentTotalQuestions = currentAssessment?.contNotAnswered + currentAssessment?.contAnswered;
    const assessmentTab = assessmentTabs[activeCategoryTab];
    const progressPercentage = Math.floor((currentAssessment?.contAnswered / assessmentTotalQuestions) * 100);

    const completedIcon = assessmentTab.querySelector('#completedIcon');
    const notCompletedIcon = assessmentTab.querySelector('#notCompletedIcon');

    if (progressPercentage === 100) {
      completedIcon.style.display = 'block';
      notCompletedIcon.style.display = 'none';
    } else {
      completedIcon.style.display = 'none';
      notCompletedIcon.style.display = 'block';
    }

    updateProgressCircle(assessmentTab, progressPercentage);
    updateAssessmentTotalCount({assessmentTab, currentAssessment, assessmentTotalQuestions});
  };

  // Update the progress circle
  const updateProgressCircle = (assessmentTab, progressPercentage) => {
    const assessmentTabProgress = assessmentTab.querySelector('.progress-circle span');
    const pathProgress = assessmentTab.querySelector('.progress-circle svg path:nth-child(2)');
    const dashArrayValue = `${progressPercentage}, 100`;

    assessmentTabProgress.textContent = `${progressPercentage}%`;
    pathProgress.setAttribute('stroke-dasharray', dashArrayValue);
  };

  // Update the total count of answered questions
  const updateAssessmentTotalCount = (payload) => {
    const { assessmentTab, currentAssessment, assessmentTotalQuestions } = payload;
    if (!assessmentTab) return;
    
    const assessmentTotalCount = assessmentTab.querySelector('#assessmentTotalCount');
    if (!assessmentTotalCount) return;
    
    const textContentValue = `{{ __('admin.Questions_answered') }} ${currentAssessment?.contAnswered}/${assessmentTotalQuestions}`;
    assessmentTotalCount.textContent = textContentValue;

    if (currentAssessment?.contAnswered === assessmentTotalQuestions) {
      const categoryCompleted = assessmentTab.querySelector('#category-completed');
      categoryCompleted.style.display = 'block';
      assessmentTotalCount.style.display = 'none';
      setTimeout(() => {
        changeAssessment(parseInt(localStorage.getItem('activeCategoryTab')) + 1);
        changeCategory(0);
      }, 1000);
    } else {
      const categoryCompleted = assessmentTab.querySelector('#category-completed');
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
        // console.log(response);
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
          });

          localStorage.setItem('activeCategoryTab', 0);
          localStorage.setItem('activeSubCategoryTab', 0);
          if (response?.user_scores) {
            const userScores = response.user_scores;
            getAssessmentTotalCount(userScores);
            getSubCategoryTotalCount(userScores);
          }
          setTimeout(() => {
            location.reload();
          }, 1000);
        }

        if (response?.user_scores) {
          const userScores = response.user_scores;
          getAssessmentTotalCount(userScores);
          getSubCategoryTotalCount(userScores);
        }
      },
      error: function(error) {
        console.log(error.responseText);
      }
    });
  };

  // Handle the change assessment
  $('.AnswerQuestion').click(function() {
    const questionId = $(this).attr('do');
    const answer = $('input[name="answer_' + questionId + '"]:checked').val();
    handleAnswerQuestion(questionId, answer);
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

      // submitContainers.forEach((submitContainer, i) => {
      //   if (i === index) {
      //     submitContainer.classList.replace('d-none', 'd-flex');
      //   } else {
      //     submitContainer.classList.replace('d-flex', 'd-none');
      //   }
      // });
    });
  }

  submitContainers.forEach((submitContainer, i) => {
    submitContainer.addEventListener('click', () => {
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

  // const submitButton = document.querySelector('#submitButton');
  // submitButton.addEventListener('click', () => {
  //   // console.log('Submit button clicked');
  //   // const activeCategoryTabs = document.querySelectorAll('#assessmentContent:not([style*="display: none"]) #category-tab');
  //   // const activeIndex = Array.from(activeCategoryTabs).findIndex(tab => tab.style.border === '1px solid black');
  //   // const nextIndex = (activeIndex + 1) % activeCategoryTabs.length;
  //   let currentIndex = 0;

  //   currentIndex += 1;

  //   changeCategory(currentIndex); // Increase index by 1
  // });
</script>
@stop