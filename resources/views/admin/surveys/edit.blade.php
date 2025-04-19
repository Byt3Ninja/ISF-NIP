@extends('layouts.admin')
@section('title', __('admin.surveys') )
@section('index_url', route('surveys.index') )
@section('subTitle', __('admin.EditSurvey'))

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y" style="padding: 2rem 4rem !important;">
    <div>
      <div class="w-100 d-flex flex-wrap justify-content-between align-items-center mb-6">
        <h3 class="fw-bold mb-0">{{ __('admin.EditSurvey') }}</h3>
      </div>

      <div class="row">
        @if( session()->has('success') )
        <div class="col-md-12 mt-4">
          <div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
            {{ __('admin.'. session()->get('success')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
        @endif
        @if( session()->has('error') )
        <div class="col-md-12 mt-4">
          <div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
            {{ __('admin.'.session()->get('error')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
        @endif
        @if( session()->has('msg') )
        <div class="row mt-4">
          <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              <div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span>
                  {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
            </div>
          </div>
        </div>
        @endif
        @if( count( $errors ) > 0 )
        <div class="row mt-4">
          <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible fade show mb-2">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }} </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
        @endif
      </div>

      <form class="card mb-6" method="post" action="{{ route('surveys.update', ['survey' => $survey->id]) }}">
        {!! csrf_field() !!}
        <input name="_method" type="hidden" value="PATCH" />
        <div class="card-body">
          <legend class="mb-6 fs-5 border-bottom pb-3 text-dark fw-semibold">{{ __('admin.Survey_Info') }}</legend>
          <div class="form-group mb-4">
            <label class="form-label fs-6 mb-1 fw-semibold"
              for="organization_id">{{ __('admin.Organization')}}</label>
            <select class="form-control form-select" name="organization_id" id="organization_id" required>
              @foreach( $organizations as $organization )
              <option value="{{ $organization->id }}"
                {{ $survey->organization_id == $organization->id ? 'selected' : '' }}>
                {{ $organization->{'name_' . $l} }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group mb-4">
            <label class="form-label fs-6 mb-1 fw-semibold" for="title">{{ __('admin.Title')}}</label>
            <input value="{{ $survey->title }}" name="title" type="text" class="form-control" id="title"
              placeholder="{{ __('admin.Title') }}" required />
          </div>
          <div class="form-group">
            <label class="form-label fs-6 mb-1 fw-semibold" for="description">{{ __('admin.Description')}}</label>
            <input value="{{ $survey->description }}" name="description" type="text" class="form-control"
              id="description" placeholder="{{ __('admin.Description') }}" required />
          </div>
        </div>

        <div class="card-body">
          <legend class="mb-6 fs-5 border-bottom pb-3 text-dark fw-semibold">{{ __('admin.SurveyQuestions') }}</legend>
          <div class="row my-3">
            <div class="col-6">
              <button type="button" class="btn btn-label-primary btn-sm mb-3" id="add-questions-btn">
                {{ __('admin.AddQuestions') }}
                <i class="ti ti-plus ms-2"></i>
              </button>
            </div>
            <div class="col-6">
              <div class="d-flex justify-content-end">
                <h6 id="select-title" class="fw-semibold text-muted mb-0 d-none">
                  {{ __('admin.SelectQuestions') }}
                  <i class="ti ti-circle-check ms-2"></i>
                </h6>
              </div>
            </div>
          </div>

          <input type="hidden" name="selected_questions" id="selected-questions" value="[{{ implode(',', array_map(fn($id) => "\"$id\"", $survey_questions)) }}]" />
          @foreach( $questions as $key => $question )
         
          <div class="col-12">
            <div class="card border mb-4">
              <div class="card-header">
                <div class="d-flex align-items-center border-bottom pb-2">
                  <span
                    class="badge badge-center bg-primary rounded-circle d-flex justify-content-center align-items-center"
                    style="width: 30px; height: 30px;">
                    {{ $key + 1 }}
                  </span>
                  <h5 class="card-title ms-2 mb-0 fs-semibold">{{ $question->question }}</h5>
                  <input type="checkbox" class="form-check-input ms-auto question-checkbox d-none"
                    value="{{ $question->id }}" {{ in_array($question->id, $survey_questions) ? 'checked' : '' }} />
                </div>
              </div>
              <div class="card-body">
                @if ($question->survey_type == 'radio')
                @foreach( $question->answers as $key => $answer )
                <div class="ps-4">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="answer_{{ $question['id'] }}"
                      id="answer-{{ $key }}" value="{{ $key }}" />
                    <label class="form-check-label" for="answer-{{ $key }}"
                      onclick="this.previousElementSibling.checked = true">
                      {{ $answer['text'] }}
                    </label>
                  </div>
                </div>
                @endforeach
                @elseif ($question->survey_type == 'stars')
                <ul class="d-flex align-items-center gap-2 list-unstyled mb-0 ps-4">
                  <li><i class="fas fa-star fs-5 text-warning"></i> </li>
                  <li><i class="fas fa-star fs-5 text-warning"></i> </li>
                  <li><i class="fas fa-star fs-5 text-warning"></i> </li>
                  <li><i class="fas fa-star fs-5 text-warning"></i> </li>
                  <li><i class="fas fa-star fs-5 text-warning"></i> </li>
                </ul>
                @else
                <div class="form-group ps-4">
                  <textarea class="form-control" name="answer_{{ $question['id'] }}" rows="3"
                    value="{{ $question['answers'][0]['text'] ?? '' }}"></textarea>
                </div>
                @endif
              </div>
            </div>
          </div>
          @endforeach
        </div>

        <div class="card-footer">
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
              {{__('admin.Save')}}
              <i class="ti ti-circle-check ms-2"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
const questions = @json($questions);
console.log('questions', questions);

document.getElementById('add-questions-btn').addEventListener('click', function() {
  document.querySelectorAll('.question-checkbox').forEach(function(checkbox) {
    checkbox.classList.toggle('d-none');
    document.querySelector('#select-title').classList.toggle('d-none');
  });
});

document.querySelectorAll('.question-checkbox').forEach(function(checkbox) {
  checkbox.addEventListener('change', function() {
    const selectedQuestions = JSON.parse(document.getElementById('selected-questions').value);
    if (this.checked) {
      selectedQuestions.push(this.value);
    } else {
      const index = selectedQuestions.indexOf(this.value);
      if (index > -1) {
        selectedQuestions.splice(index, 1);
      }
    }
    document.getElementById('selected-questions').value = JSON.stringify(selectedQuestions);
    console.log('values', document.getElementById('selected-questions').value);
  });
});
</script>
@stop