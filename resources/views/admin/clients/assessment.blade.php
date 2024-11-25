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

$assessments = [
['title' => 'business-concept', 'progress' => 50, 'status' => 'in-progress'],
['title' => 'organization', 'progress' => 75, 'status' => 'in-progress'],
['title' => 'operations', 'progress' => 100, 'status' => 'completed'],
['title' => 'customer-relation', 'progress' => 20, 'status' => 'not-started'],
];

$busConcCategories = [
['title' => 'business-idea', 'answered' => 1, 'status' => 'completed'],
['title' => 'product-portfolio', 'answered' => 2, 'status' => 'in-progress'],
['title' => 'revenue-modal', 'answered' => 3, 'status' => 'not-started'],
['title' => 'customer-portfolio', 'answered' => 4, 'status' => 'not-started'],
['title' => 'market-segment', 'answered' => 5, 'status' => 'not-started'],
]
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Pills (Mobile) -->
    <div class="row mb-4 d-block d-md-none">
      <div class="col-12">
        <ul class="nav nav-pills">
          @foreach($mobilePills as $key => $pill)
          <li class="nav-item me-1">
            <a href="/dashboard/{{ $pill }}" class="nav-link {{ $key == 1 ? 'active' : '' }}">
              <span>{{ $pill }}</span>
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
            @foreach($pills as $key => $pill)
            <li class="nav-item me-1">
              <a href="/dashboard/{{ $pill }}" class="nav-link {{ $key == 1 ? 'active' : '' }}">
                <span class="fs-6">{{ str_replace('-', ' ', $pill) }}</span>
              </a>
            </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
    <!-- / Pills -->
    <div class="row">
      <div class="col-md-3 col-12">
        @foreach($assessments as $key => $assessment)
        <div id="tab{{ $key + 1 }}" class="assessment-tab card shadow-sm mb-3">
          <div class="d-flex justify-content-between align-items-center p-4">
            <div class="d-flex flex-column">
              <h6 class="mb-0 text-capitalize">
                {{ str_replace('-', ' ', $assessment['title']) }}
              </h6>
              <span class="text-xs text-secondary">20/25 questions answered</span>
            </div>
            <x-circular-progress size="40" color="success" />
          </div>
        </div>
        @endforeach
      </div>
      <div class="col-md-9 col-12">
        @foreach($assessments as $key => $assessment)
        <section id="tab-content{{ $key + 1 }}" class="{{ $key == 0 ? 'd-block' : 'd-none' }}">
          @if ($assessment['title'] === 'business-concept')
          <div class="d-flex flex-column gap-6">
            <div class="d-flex flex-wrap gap-2">
              @foreach($busConcCategories as $key => $category)
              <div class="card shadow-sm cursor-pointer">
                <div class="px-3 py-5 d-flex flex-column gap-2">
                  <span class="text-capitalize fw-bold text-sm">
                    {{ str_replace('-', ' ', $category['title']) }}
                  </span>
                  <span class="text-secondary" style="font-size: 10px;">
                    {{ $category['answered'] }}/5 questions answered
                  </span>
                </div>
              </div>
              @endforeach
            </div>
            <div id="row" class="bus-content-cat1">
              <div class="col-12">
                <div class="card shadow-sm mb-3">
                  <div class="card-body">
                    <div class="d-flex align-items-center gap-2 border-bottom mb-4 pb-3">
                      <span class="badge bg-primary rounded-circle w-px-30 h-px-30 d-flex justify-content-center align-items-center">1</span>
                      <span class="fw-bold text-sm">
                        Question title
                      </span>
                    </div>
                    <div class="d-flex flex-column">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <form class="card shadow-sm mb-3">
                  <div class="card-body">
                    <div class="d-flex align-items-center gap-2 border-bottom mb-4 pb-3">
                      <span class="badge bg-primary rounded-circle w-px-30 h-px-30 d-flex justify-content-center align-items-center">2</span>
                      <span class="fw-bold text-sm">
                        Question title
                      </span>
                    </div>
                    <div class="d-flex flex-column">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              <div class="col-12">
                <form class="card shadow-sm mb-3">
                  <div class="card-body">
                    <div class="d-flex align-items-center gap-2 border-bottom mb-4 pb-3">
                      <span class="badge bg-primary rounded-circle w-px-30 h-px-30 d-flex justify-content-center align-items-center">3</span>
                      <span class="fw-bold text-sm">
                        Question title
                      </span>
                    </div>
                    <div class="d-flex flex-column">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              <div class="col-12">
                <form class="card shadow-sm mb-3">
                  <div class="card-body">
                    <div class="d-flex align-items-center gap-2 border-bottom mb-4 pb-3">
                      <span class="badge bg-primary rounded-circle w-px-30 h-px-30 d-flex justify-content-center align-items-center">4</span>
                      <span class="fw-bold text-sm">
                        Question title
                      </span>
                    </div>
                    <div class="d-flex flex-column">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              <div class="col-12">
                <div class="card shadow-sm mb-3">
                  <div class="card-body">
                    <div class="d-flex align-items-center gap-2 border-bottom mb-4 pb-3">
                      <span class="badge bg-primary rounded-circle w-px-30 h-px-30 d-flex justify-content-center align-items-center">5</span>
                      <span class="fw-bold text-sm">
                        Question title
                      </span>
                    </div>
                    <div class="d-flex flex-column">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="question">
                        <label class="form-check-label" for="question">
                          label
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                  <button type="submit" class="btn btn-sm btn-primary mt-4">Submit</button>
                </div>
              </div>
            </div>
          </div>
          @endif
        </section>
        @endforeach
      </div>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
  $(document).ready(async () => {
    // Handle assessment tab click
    $('.assessment-tab').click(() => {
      const tabId = $(this).attr('id').replace('tab', '');
      $('.assessment-tab').removeClass('light-shadow');
      $(this).addClass('light-shadow');
      $('[id^="tab-content"]').addClass('d-none');
      $(`#tab-content${tabId}`).removeClass('d-none');
    });
  });
</script>
@stop