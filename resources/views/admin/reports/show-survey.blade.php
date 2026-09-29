@extends('layouts.admin')

@section('content')
<div class="">
  <div class="">
    <h3 class="fw-bold mb-4">{{ __('admin.Survey_Report') }}</h3>
    <div class="card mb-4">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered mb-0">
            <tbody>
              <tr>
                <th>{{ __('admin.Organization Name') }}</th>
                <td>{{ $organizationName }}</td>
              </tr>
              <tr>
                <th>{{ __('admin.Program Name') }}</th>
                <td>{{ $programName }}</td>
              </tr>
              <tr>
                <th>{{ __('admin.Startup Name') }}</th>
                <td>{{ $startupName }}</td>
              </tr>
              <tr>
                <th>{{ __('admin.Survey Submitter Name') }}</th>
                <td>{{ $submitterName }}</td>
              </tr>
              <tr>
                <th>{{ __('admin.Submit Date') }}</th>
                <td>{{ $submitDate }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <div id="survey-questions">
      @if(isset($available_surveys['responses']) && !empty($available_surveys['responses']))
        @foreach($available_surveys['responses'] as $key => $question)
        <div class="card border mb-4">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-4">
              <div class="d-flex align-items-center">
                <span
                  class="badge badge-center bg-primary rounded-circle d-flex justify-content-center align-items-center"
                  style="width: 30px; height: 30px;">
                  {{ $key + 1 }}
                </span>
                <h5 class="card-title ms-2 mb-0 fs-semibold">{{ $question['question'] }}</h5>
              </div>
            </div>

            @if ($question['type'] == 'radio')
              @foreach($question['answers'] as $key => $answer)
              <div class="ps-4">
                <div class="form-check">
                  <input 
                  class="form-check-input" 
                  type="radio" 
                  name="answer_{{ $question['id'] }}" 
                  id="answer-{{ $key }}" 
                  value="{{ $key }}" 
                  @if ($question['answer'] == '1' && $answer['text'] == 'Yes') 
                    checked
                  @elseif ($question['answer'] == '0' && $answer['text'] == 'No')
                    checked
                  @endif
                  />
                  <label class="form-check-label" for="answer-{{ $key }}">
                    {{ $answer['text'] }}
                  </label>
                </div>
              </div>
              @endforeach
            @elseif ($question['type'] == 'stars')
            <ul class="d-flex align-items-center gap-2 list-unstyled mb-0 ps-4">
              @for ($i = 1; $i <= 5; $i++) <li>
                <i
                  class="fas fa-star fs-5 {{ $i <= $question['answer'] ? 'text-warning' : 'text-muted' }}"></i>
                </li>
              @endfor
            </ul>
            @else
            <div class="form-group ps-4">
              <textarea class="form-control" name="answer_{{ $question['id'] }}">{{ $question['answer']?? '' }}</textarea>
            </div>
            @endif
          </div>
        </div>
        @endforeach
      @else
        @include('includes.no-result')
      @endif
    </div>
  </div>
</div>
@stop