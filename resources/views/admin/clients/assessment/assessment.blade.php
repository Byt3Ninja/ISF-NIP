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
      <div class="row">
        <div class="col-md-3 col-12 position-relative" style="height: fit-content;">
          @foreach ($user_scores as $key => $assessment)
            @php
              $totalQuestions = Count($assessment->question);
              $totalAnswered = Count($assessment->question->where('Result_assessment', '!=', null));
              $totalNotAnswered = Count($assessment->question->where('Result_assessment', '=', null));
              // حساب التقدم الإجمالي عبر جميع الأقسام
              $progress = $totalQuestions > 0 ? ($totalAnswered / $totalQuestions) * 100 : 0;

              $status = $progress == 100 ? 'completed' : ($progress > 0 ? 'in-progress' : 'not-started');
            @endphp

            <div id="tab{{ intval($key) + 1 }}"
              class="CategoryGetId assessment-tab card shadow-sm mb-3 {{ $key == 0 ? 'border border-dark' : '' }} position-relative"
              do="{{ intval($key) }}">
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

        <div class="col-md-9 col-12">
          @foreach ($user_scores as $key => $assessment)
            <section id="tab-content-{{ intval($key) + 1 }}" class="{{ $key == 0 ? 'd-block' : 'd-none' }}">

              <div class="d-flex flex-column gap-6">
                <div class="d-flex flex-wrap gap-2">
                  @foreach ($assessment['subcategories'] as $key => $subcategory)
                    @php
                      $totalQuestionsSub = Count($subcategory['questions']);
                      $totalAnsweredSub = Count($subcategory['questions']->where('Result_assessment', '!=', null));
                      $totalNotAnsweredSub = Count($subcategory['questions']->where('Result_assessment', '=', null));
                    @endphp

                    <div class="card shadow-sm cursor-pointer {{ $key == 0 ? 'active-cat' : '' }}"
                      id="bus-category-tab-{{ $key }}">
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

                    {{-- <div class="row">
                      @foreach ($subcategory['questions'] as $key => $question)
                        <div class="col-12 question-card-bus-con mb-3" data-category="question-{{ $key }}"
                          >
                          <div class="card shadow-sm">
                            <div class="card-body">
                              <div class="d-flex align-items-center gap-3 mb-2 pb-2 border-bottom">
                                <span class="badge bg-primary rounded-circle d-flex justify-content-center align-items-center"
                                  style="width: 30px; height: 30px;">
                                  {{ $key + 1 }}
                                </span>
                                <h6 class="fw-bold mb-0">{{ $question['QuestionBank'] }}</h6>
                              </div>
                              <div class="mt-3">
                                @php
                                $options = json_decode($question['Answers']);
                                @endphp
                                @foreach ($options as $key => $option)
                                  <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer_{{ $key }}"
                                      id="{{ $key }}">
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
                    </div> --}}
                  @endforeach
                </div>


                <div class="d-flex justify-content-between">
                  {{-- <button class="btn btn-secondary btn-prev" onclick="changeCategory(-1)">Prev</button> --}}
                  <button class="btn btn-primary btn-next-bus-concept ms-auto" onclick="changeCategory(1)">Submit</button>
                </div>
              </div>

            </section>
          @endforeach
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
    $(document).ready(() => {
      // Handle assessment tab click
      $('.assessment-tab').click(function() {
        const tabId = $(this).attr('id').replace('tab', '');
        $('.assessment-tab').removeClass('border border-dark');
        $(this).addClass('border border-dark');
        $('[id^="tab-content"]').addClass('d-none');
        $(`#tab-content-${tabId}`).removeClass('d-none');
      });



    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const categoryTab = document.querySelectorAll('#category-tab');

      // Handle category tab click
      categoryTab.forEach(tab => {
        tab.addEventListener('click', () => {
          categoryTab.forEach(tab => {
            tab.classList.remove('active-cat');
          });

          tab.classList.add('active-cat');
        });
      })


    });
  </script>
@stop
