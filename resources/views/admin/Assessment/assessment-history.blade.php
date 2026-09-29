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
</style>
@stop

@php
$pills = ['analysis-assessment'];
$mobilePills = ['analysis'];

@endphp

@section('content')
<div class="">
  <div class="">
    <!-- Pills (Mobile) -->
    <div class="row mb-4 d-block d-md-none">
      <div class="col-12">
        <ul class="nav nav-pills">
          @foreach ($mobilePills as $key => $pill)
          <li class="nav-item me-1">
            <a href="/assessment/show/{{ $assessment_user->id }}" class="nav-link {{ $key == 1 ? 'active' : '' }}">
              <span>
                @if ($pill == 'analysis-assessment')
                {{ __('admin.Analysis_Assessment') }}
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
              <a href="{{ route('assessments.show', $assessment_user->id) }}"
                class="nav-link {{ $key == 1 ? 'active' : '' }}">
                <span class="fs-6">
                  @if ($pill == 'analysis-assessment')
                  {{ __('admin.Analysis_Assessment') }}
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
                {{-- Admin-only view of a founder's assessment. Only the founder can
                     start one (MakeAssessment() requires the Registered role), so no
                     CTA is offered here. --}}
                <h6 class="text-center text-secondary">{{ __('admin.No_Assessment_right_now') }}</h6>
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
          $totalQuestions = $assessment->question->where('assessment_id', $assessment_user->id)->count();
          $totalAnswered = $assessment->question->where('Result_assessment', '!=', null)
                                        ->where('assessment_id', $assessment_user->id)
                                        ->count();
          $totalNotAnswered = $assessment->question->where('Result_assessment', '=', null)
                                        ->where('assessment_id', $assessment_user->id)
                                        ->count();
          // حساب التقدم الإجمالي عبر جميع الأقسام
          $progress = $totalQuestions > 0 ? ($totalAnswered / $totalQuestions) * 100 : 0;

          $status = $progress == 100 ? 'completed' : ($progress > 0 ? 'in-progress' : 'not-started');
          @endphp

          <div 
            id="assessmentTab"
            class="CategoryGetId assessment-tab card shadow-sm mb-3 position-relative"
            style="border: {{ $key == 0 ? '1px solid black;' : 'none' }};"
            onclick="changeAssessment({{ $key }})"
          >
            <div class="d-flex justify-content-between align-items-center p-4">
              <div class="d-flex flex-column">
                <h6 class="mb-0 text-capitalize">
                  {{ str_replace('-', ' ', $assessment['category_name_' . $l]) }}
                </h6>
                @if ($totalAnswered && $totalAnswered == $totalQuestions)
                <span class="text-success fw-bold text-sm">
                  {{ __('admin.Done_All') }}
                </span>
                @else
                <span class="text-secondary" style="font-size: 10px;">
                  {{ __('admin.Questions_answered') }} {{ $totalAnswered }}/{{ $totalQuestions }}
                </span>
                @endif
              </div>
              <x-circular-progress size="40" color="#28c76f" progress="{{ $progress }}" />
            </div>
            @if ($status == 'completed' || $progress == 100)
              <div style="position: absolute; left: -1rem; top: 50%; transform: translateY(-50%); z-index: 1;"
                class="w-px-20 h-px-20 rounded-circle bg-success justify-content-center align-items-center d-flex">
                <i class="ti ti-check text-white fs-6"></i>
              </div>
            @else
              <div style="position: absolute; {{ $l == 'en' ? 'left' : 'right' }}: -1.5rem; top: 50%; transform: translateY(-50%); z-index: 1;"
                class="w-px-20 h-px-20 rounded-circle bg-light border border-secondary">
              </div>
            @endif
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
          @php
          $categoryId = $assessment['id'];
          @endphp
          <section id="assessmentContent"
            style="{{ $key == 0 ? 'display: block;' : 'display: none;' }}">
            <div class="d-flex flex-column gap-6">
              <div class="d-flex flex-wrap gap-2">
                @foreach ($assessment['subcategories'] as $key => $subcategory)
                @php
                $totalQuestionsSub = Count($subcategory['questions']);
                $totalAnsweredSub = Count($subcategory['questions']->where('Result_assessment', '!=', null));
                $totalNotAnsweredSub = Count($subcategory['questions']->where('Result_assessment', '=', null));
                @endphp

                <div class="card shadow-sm cursor-pointer" style="{{ $key == 0 ? 'border: 1px solid black;' : '' }}"
                  id="category-tab" onclick="changeCategory({{ $key }})">
                  <div class="px-3 py-5 d-flex flex-column gap-2 text-center">
                    <span class="text-capitalize fw-bold text-sm">
                      {{ str_replace('-', ' ', $subcategory['name_' . $l]) }}
                    </span>
                    @if ($totalAnsweredSub > 0 && $totalQuestionsSub == $totalAnsweredSub)
                    <span class="text-success fw-bold">
                      {{ __('admin.Done_All') }}
                    </span>
                    @else
                    <span id="subCategory-count" class="text-secondary" style="font-size: 10px;">
                      {{ __('admin.Questions_answered') }} {{ $totalAnsweredSub }}/{{ $totalQuestionsSub }}
                    </span>
                    @endif
                  </div>
                </div>
                @endforeach
              </div>
              
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
                        <input type="hidden" disabled name="question_id" class="question_id{{ $question['id'] }}" value="{{ $question['id'] }}">
                      </div>
                      <div class="mt-3">
                        @php
                          $options = json_decode($question['Answers']);
                        @endphp
                        @foreach ($options as $key => $option)
                          <div class="form-check">
                            <input 
                              class="form-check-input AnswerQuestion"
                              do="{{ $question['id'] }}" 
                              type="radio" 
                              name="answer_{{ $question['id'] }}" 
                              id="answer-{{ $key }}"
                              value="{{ $key }}" 
                              {{ $question['score'] ==  $option->score ? 'checked' : '' }} 
                              disabled
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
            </div>
          </section>
            
          @endforeach
          <div>
            @foreach ($assessment['subcategories'] as $key => $subcategory)
            <div id="submitContainer" style="{{ $key == 0 ? 'display: flex;' : 'display: none;' }}">
              <div class="w-100 d-flex justify-content-end">
                <button id="submitButton" class="btn btn-primary">{{ __('admin.Next') }}</button>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
@stop

@section('pagescripts')
<script>
  // On answer question
  $('.AnswerQuestion').click(function() {
    const questionId = $(this).attr('do');
    const answer = $('input[name="answer_' + questionId + '"]:checked').val();

    $.ajax({
      url: '/dashboard/answer-assessment', 
      method: 'post',
      data: {
        'questionId': questionId,
        'answer': answer,
        _token: $('meta[name="csrf-token"]').attr('content'),
      },
      success: function(response) {
        if(response.message == 1){
          location.reload();
        }

        // if (response.user_scores) {
        //     const userScores = response.user_scores;
        //     const activeCategoryTab = localStorage.getItem('activeCategoryTab');
        //     const activeSubCategoryTab = localStorage.getItem('activeSubCategoryTab');

        //     const currentAssessment = userScores[activeCategoryTab];
        //     const currentSubCategory = currentAssessment.subcategories[activeSubCategoryTab];
        //     console.log('currentSubCategory', currentSubCategory)

        //     const subCategoryCounts = document.querySelectorAll(`#assessmentContent:nth-child(${parseInt(activeCategoryTab) + 1}) #subCategory-count`);
        //     subCategoryCounts.forEach((countElement, index) => {
        //       countElement.textContent = `{{ __('admin.Questions_answered') }} ${currentSubCategory?.contAnswered}/${currentSubCategory?.questions?.length}`;
        //     });
          
        // }

        
        // $('.refreshDiv').load(location.href + ' .refreshDiv');
        console.log(response.message);
        console.log(response);
        // location.reload();
      },
      error: function(error) {
        console.log(error.responseText);
      }
    });
  });

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
      changeAssessment(parseInt(activeCategoryTab));
    }

    if (activeSubCategoryTab) {
      changeCategory(parseInt(activeSubCategoryTab));
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