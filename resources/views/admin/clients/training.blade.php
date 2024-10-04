@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css"
    href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
@stop

@section('content')
  @php($con = \App\Training::where('id', $contents->pluck('training_id')[0])->first())
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-header row">
        <h4 class="content-header-title">{{ __('settings.View training content') }}</h4>
      </div>
      <div class="content-body">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">
              <img width="250px" src="/191014/{{ $con->image }}" class="img-fluid">
              <br>
              <h5>{{ $con->title }}</h5>
              <span class="text-secondary fs-5">{{ $con->dsc }}</span>
            </h4>
          </div>
          <div class="pt-2 px-2">
            <div class="accordion" id="trainingControllers">
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                  <button class="accordion-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#contentsContainer" aria-expanded="true" aria-controls="contentsContainer">
                    {{ __('admin.Training courses') }}
                  </button>
                </h2>
                <div id="contentsContainer" class="accordion-collapse collapse show" aria-labelledby="headingOne"
                  data-bs-parent="#trainingControllers">
                  <div class="accordion-body mt-3">
                    <ul class="list-group">
                      @foreach ($contents as $key => $content)
                        <li
                          class="list-group-item {{ $ccontent->id == $content->id ? 'list-group-item-secondary' : 'list-group-item-light' }}">
                          <a href="/dashboard/training/my/{{ $content->training_id }}?c={{ $content->id }}">
                            <div class="d-flex justify-content-between align-items-center">
                              <div>
                                @if ($content->done())
                                  <i title="Completed {{ $content->done() }}"
                                    class="bi bi-check-circle-fill text-primary"></i>
                                @endif
                              </div>
                              <h6 class="mb-0 {{ $ccontent->id == $content->id ? 'fw-bold text-primary' : '' }}">
                                {{ $key + 1 }} - {{ $content->title }}
                              </h6>
                            </div>
                          </a>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#quizContainer" aria-expanded="false" aria-controls="quizContainer">
                    {{ __('admin.quizzes') }}
                  </button>
                </h2>
                <div id="quizContainer" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                  data-bs-parent="#trainingControllers">
                  <div class="accordion-body mt-3">
                    <ul class="list-group">
                      @foreach ($quizzes as $quiz)
                        @php($myAttempts = $quiz->myAttempts)
                        @php($failed_attempts = $myAttempts->where('score', '<', $quiz->passing_criteria)->count())
                        <li class="list-group-item list-group-item-light">
                          <a href="/dashboard/training/my/{{ $content->training_id }}/quiz/{{ $quiz->id }}">
                            <div class="d-flex justify-content-between align-items-center">
                              <div>
                                @if ($myAttempts->count() == 0)
                                  <i data-bs-toggle="tooltip" data-bs-placement="top" title="Not started"
                                    class="bi bi-info-circle-fill text-light"></i>
                                @elseif($myAttempts->where('score', '>=', $quiz->passing_criteria)->count() > 0)
                                  <i data-bs-toggle="tooltip" data-bs-placement="top" title="Passed"
                                    class="bi bi-check-circle-fill text-success"></i>
                                @elseif($failed_attempts >= $quiz->attempts)
                                  <i data-bs-toggle="tooltip" data-bs-placement="top" title="Failed"
                                    class="bi bi-x-circle-fill text-danger"></i>
                                @else
                                  <i data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Failed {{ $failed_attempts }} time(s) out of {{ $quiz->attempts }} attempts"
                                    class="bi bi-exclamation-circle-fill text-warning"></i>
                                @endif
                              </div>
                              <h6 class="mb-0 fw-bold text-primary">{{ $quiz->title }}</h6>
                            </div>
                          </a>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card mt-4">
          <div class="card-header">
            <h4 class="card-title"></h4>
          </div>
          <div class="card-body p-3">{!! $ccontent->content !!}</div>
        </div>
      </div>
    </div>
  </div>
  <!-- END: Content-->
@stop
@section('pagescripts')
  <script>
    // document.body.classList.add("menu-collapsed");
    $(function() {
      $('[data-toggle="tooltip"]').tooltip()
    });
  </script>
@stop
