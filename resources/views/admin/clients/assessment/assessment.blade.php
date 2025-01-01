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
$pills = ['analysis-assessment', 'question-assessment', 'history'];
$mobilePills = ['analysis', 'questions', 'history'];

@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Pills (Mobile) -->
    <div class="row mb-4 d-block d-md-none">
      <div class="col-12">
        <ul class="nav nav-pills">
          @foreach ($mobilePills as $key => $pill)
          <li class="nav-item me-1">
            <a href="/dashboard/{{ $pill }}" class="nav-link {{ $key == 1 ? 'active' : '' }}">
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
              <a href="/dashboard/{{ $key == 2 ? 'history-assessment' : $pill }}"
                class="nav-link {{ $key == 1 ? 'active' : '' }}">
                <span class="fs-6">
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
    <div class="row ">
      <div class="col-md-3 col-12 position-relative " style="height: fit-content;">
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

        <div {{-- id="tab{{ intval($key) + 1 }}" --}} id="assessmentTab"
          class="CategoryGetId assessment-tab card shadow-sm mb-3 position-relative"
          style="border: {{ $key == 0 ? '1px solid black;' : 'none' }};"
          {{-- do="{{ intval($key) }}" --}} onclick="changeAssessment({{ $key }})">
          <div class="d-flex justify-content-between align-items-center p-4">
            <div class="d-flex flex-column">
              <h6 class="mb-0 text-capitalize">
                {{ str_replace('-', ' ', $assessment['category_name_' . $l]) }}
              </h6>
              @if ($totalAnswered == $totalQuestions)
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
          <div style="position: absolute; left: -1rem; top: 50%; transform: translateY(-50%); z-index: 1;"
            class="w-px-20 h-px-20 rounded-circle bg-light border border-secondary">
          </div>
          @endif
        </div>
        @endforeach
        <div
          style="position: absolute; left: 0.35rem; top: 0; height: 100%; border-left: 2px dashed rgb(120, 108, 163);">
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
        <section id="assessmentContent" {{-- id="tab-content-{{ intval($key) + 1 }}" --}}
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
                  @if ($totalQuestionsSub == $totalAnsweredSub)
                  <span class="text-success fw-bold">
                    {{ __('admin.Done_All') }}
                  </span>
                  @else
                  <span class="text-secondary" style="font-size: 10px;">
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
                      <input type="hidden" name="question_id" class="question_id{{ $question['id'] }}" value="{{ $question['id'] }}">
                    </div>
                    <div class="mt-3">
                      @php
                      $options = json_decode($question['Answers']);
                      @endphp
                      @foreach ($options as $key => $option)
                      <div class="form-check">
                        <input class="form-check-input AnswerQuestion"  do="{{ $question['id'] }}" type="radio" name="answer_{{ $question['id'] }}" id="{{ $key }}" value="{{ $key }}" {{ $question['score'] ==  $option->score ? 'checked' : '' }} >
                        <label class="form-check-label" for="{{ $key }}">
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
  </div>
</div>
@stop

@section('scripts')
<script>
  const Assments = @json($user_scores);
  console.log(Assments);
  console.log('dfsf');
</script>
@stop
@section('pagescripts')
<script>
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
                    'activeAssessment': 2
                },
                success: function(response) {
                  if(response.message == 1){
                    location.reload();
                  }
                    // $('.refreshDiv').load(location.href + ' .refreshDiv');
                    console.log(response.message);
                    console.log(response);
                    // location.reload();

                    if (i === index) {
                      container.style.display = 'block';
                    } else {
                      container.style.display = 'none';
                    }

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
    console.log('changeAssessment', index);
    assessmentContainers.forEach((container, i) => {
      if (i === index) {
        container.style.display = 'block';
      } else {
        container.style.display = 'none';
      }
    });

    assessmentTabs.forEach((tab, i) => {
      if (i === index) {
        tab.style.border = '1px solid black';
      } else {
        tab.style.border = 'none';
      }
    });

    // Filter category tabs related to the current active section
    const activeCategoryTabs = document.querySelectorAll(`#assessmentContent:nth-child(${index + 1}) #category-tab`);
    console.log('activeCategoryTabs', activeCategoryTabs);
  };

  function changeCategory(index) {
    console.log('changeCategory', index);

    const activeCategoryTabs = document.querySelectorAll('#assessmentContent:not([style*="display: none"]) #category-tab');
    const activeQuestionsContainers = document.querySelectorAll('#assessmentContent:not([style*="display: none"]) #questionsContainer');

    activeCategoryTabs.forEach((tab, i) => {
      if (i === index) {
        tab.style.border = '1px solid black';
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
      console.log('Submit button clicked');
      const activeCategoryTabs = document.querySelectorAll('#assessmentContent:not([style*="display: none"]) #category-tab');
      const activeIndex = Array.from(activeCategoryTabs).findIndex(tab => tab.style.border === '1px solid black');
      const nextIndex = (activeIndex + 1) % activeCategoryTabs.length;

      changeCategory(nextIndex); // Increase index by 1
    });
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