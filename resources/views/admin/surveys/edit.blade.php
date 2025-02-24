@extends('layouts.admin')
@section('title', __('admin.surveys') )
@section('index_url', route('surveys.index') )
@section('subTitle', __('admin.EditSurvey'))

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div>
      <div class="w-100 d-flex justify-content-between align-items-center px-0 px-md-6">
        <h3 class="fw-bold mb-0">{{ __('admin.EditSurvey') }}</h3>
        <nav>
          <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item me-3">
              <a class="nav-link active" href="#survey-info" data-bs-toggle="tab" role="tab" aria-controls="survey-info"
                aria-selected="true"> {{ __('admin.Survey_Info') }} </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#survey-questions" data-bs-toggle="tab" role="tab"
                aria-controls="survey-questions" aria-selected="false"> {{ __('admin.SurveyQuestions') }} </a>
            </li>
          </ul>
        </nav>
      </div>

      <div class="row px-0 px-md-6">
        @if( session()->has('success') )
        <div class="col-md-12">
          <div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
            {{ __('admin.'. session()->get('success')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
        @endif
        @if( session()->has('error') )
        <div class="col-md-12">
          <div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
            {{ __('admin.'.session()->get('error')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
        @endif
        @if( session()->has('msg') )
        <div class="row">
          <div class="col-md-12">
            <div class="alert alert-danger mb-2" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              <div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span>
                  {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
            </div>
          </div>
        </div>
        @endif
        @if( count( $errors ) > 0 )
        <div class="row">
          <div class="col-md-12">
            <div class="alert alert-danger">
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
      <div class="tab-content px-0 px-md-6">
        <div class="tab-pane fade show active" id="survey-info">
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

        <div class="tab-pane fade" id="survey-questions">
          <form method="post" action="{{ route('surveys.update', ['survey' => $survey->id]) }}">
            {!! csrf_field() !!}
            <input name="_method" type="hidden" value="PATCH" />
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
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@stop