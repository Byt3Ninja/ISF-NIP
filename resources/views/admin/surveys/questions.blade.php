@php
$survey_questions = !empty( $survey->questions ) ? $survey->questions->pluck('id')->toArray() : [];
@endphp
@extends('layouts.admin')
@section('title', __('admin.surveys') )
@section('index_url', route('surveys.index') )
@section('sub_index_url', route('surveys.edit', ['survey' => $survey->id]) )
@section('subTitle', __('admin.manageQuestions'))

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>

    <div class="content-header row mb-6 mt-3 px-0 px-md-6">
			<h3 class="fw-bold mb-0">{{ __('admin.manageQuestions') }}</h3>

      @if( session()->has('success') )
      <div class="col-md-12">
        <div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          <div class="d-flex align-items-center"> <i class="ti ti-thumb-up me-3 ti-md"></i> <span>
              {{ __('admin.'. session()->get('success')) }} </span> </div>
        </div>
      </div>
      @endif
      @if( session()->has('error') )
      <div class="col-md-12">
        <div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          <div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i>
            <span>{{ __('admin.'.session()->get('error')) }} </span>
          </div>
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
      @if(count($errors) >0)
      <div class="row">
        <div class="col-md-12">
          <div class="alert alert-danger mb-2" role="alert">
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

    <div class="px-0 px-md-6">
      <div class="row mb-3">
        <div class="col-6">
          <button type="button" class="btn btn-label-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#addQuestionsModal">
                  {{ __('admin.AddQuestions') }}
                  <i class="ti ti-plus ms-2"></i>
          </button>
          <button type="button" class="btn btn-label-secondary btn-sm mb-3" id="select-delete-btn">
            {{ __('admin.SelectForDelete') }}
            <i class="ti ti-checkbox ms-2"></i>
        </button>
        <button type="button" class="btn btn-label-danger btn-sm mb-3 d-none" id="bulk-delete-btn">
            {{ __('admin.BulkDelete') }}
            <i class="ti ti-trash ms-2"></i>
        </button>
        </div>
        <div class="col-6 d-none" id="select-title">
          <div class="d-flex justify-content-end">
            <h6  class="fw-semibold text-muted mb-0">
              {{ __('admin.SelectQuestions') }}
              <i class="ti ti-circle-check ms-2"></i>
            </h6>
          </div>
        </div>
      </div>
      <form method="post" action="{{ route('surveys.questions_update', ['survey' => $survey->id]) }}">
        {!! csrf_field() !!}
        <input type="hidden" name="selected_questions" id="selected-questions" value="[]" />
        @foreach( $survey->questions as $key => $question )
        <div class="col-12">
          <div class="card border mb-4">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-4">
								<div class="d-flex align-items-center">
									<span
										class="badge badge-center bg-primary rounded-circle d-flex justify-content-center align-items-center"
										style="width: 30px; height: 30px;">
										{{ $key + 1 }}
									</span>
									<h5 class="card-title ms-2 mb-0 fs-semibold">{{ $question->question }}</h5>
								</div>
								<div class="d-flex align-items-center gap-4">
									<input type="checkbox" class="form-check-input ms-auto question-checkbox d-none"
										name="question_ids[]" value="{{ $question->id }}" />
									<div class="ms-2">
										<button class="btn border-none" type="button" data-bs-toggle="dropdown" aria-haspopup="true"
											aria-expanded="false">
											<i class="ti ti-dots-vertical"></i>
										</button>
										<div class="dropdown-menu">
											<a href="{{ route('questions-bank.edit', ['questions_bank' => $question->id]) }}"
												data-id="{{ $question->id }}" class="edit-question dropdown-item">
												<i class="ti ti-edit text-info"></i> {{ __('admin.Edit') }} </a>
											  <a href="#" data-id="{{ $question->id }}" class="delete-question dropdown-item">
                          <i class="ti ti-trash text-danger"></i> {{ __('admin.Delete') }}
                        </a>
										</div>
									</div>
								</div>
							</div>

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
 <!-- Add Questions Modal -->
    <div class="modal fade" id="addQuestionsModal" tabindex="-1" aria-labelledby="addQuestionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addQuestionsModalLabel">{{ __('admin.AddQuestions') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('surveys.questions_update', ['survey' => $survey->id]) }}" id="add-questions-form">
                    @csrf
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin.Question') }}</th>
                                        <th class="text-center">{{ __('admin.Select') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($questions as $question)
                                        <tr>
                                            <td>{{ $question->question }}</td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input modal-question-checkbox" name="questions_ids[]" value="{{ $question->id }}" {{ in_array($question->id, $survey_questions) ? 'checked ' : '' }} />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@stop

@section('pagescripts')
<script>

const questions = @json($questions);
const surveyQuestions = @json($survey_questions);

document.getElementById('select-delete-btn').addEventListener('click', function() {
    document.querySelectorAll('.question-checkbox').forEach(function(checkbox) {
        checkbox.classList.toggle('d-none');
    });
    document.querySelector('#select-title').classList.toggle('d-none');
});

document.querySelectorAll('.question-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        const anyChecked = Array.from(document.querySelectorAll('.question-checkbox')).some(cb => cb.checked);
        document.querySelector('#bulk-delete-btn').classList.toggle('d-none', !anyChecked);
    });
});

document.getElementById('add-questions-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const selectedQuestions = Array.from(document.querySelectorAll('.modal-question-checkbox:checked'))
        .map(checkbox => parseInt(checkbox.value));
    
    const currentQuestions = JSON.parse(document.getElementById('selected-questions').value);
    const updatedQuestions = [...new Set([...currentQuestions, ...selectedQuestions])];
    document.getElementById('selected-questions').value = JSON.stringify(updatedQuestions);

    this.submit();
});

document.getElementById('bulk-delete-btn').addEventListener('click', function() {
  Swal.fire({
    title: "{{__('admin.SureDelete')}}",
      text: "{{__('admin.Recover')}}",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "{{ __('admin.DYes') }}",
      cancelButtonText: "{{ __('admin.No') }}",
      reverseButtons: true,
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-label-secondary',
      },
      buttonsStyling: false,
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('surveys.questions_delete', ['survey' => $survey->id]) }}';
            form.style.display = 'none';

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = '{{ csrf_token() }}';
            form.appendChild(token);

            document.querySelectorAll('.question-checkbox:checked').forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'question_ids[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    });
});

$(document).on('click', '.delete-question', function(e) {
    e.preventDefault();
    const questionId = $(this).data('id');
    Swal.fire({
        title: "{{ __('admin.SureDelete') }}",
        text: "{{ __('admin.Recover') }}",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "{{ __('admin.DYes') }}",
        cancelButtonText: "{{ __('admin.No') }}",
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('surveys.questions_delete', ['survey' => $survey->id]) }}';
            form.style.display = 'none';

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = '{{ csrf_token() }}';
            form.appendChild(token);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'question_ids[]';
            input.value = questionId;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }
    });
});


</script>
@stop
