@extends('layouts.admin')
@section('title', __('admin.surveys'))
@section('index_url', route('surveys.index'))
@section('subTitle', __('admin.CreateSurvey'))

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y" style="padding: 2rem 4rem !important;">
        <div>
            <div class="w-100 d-flex flex-wrap justify-content-between align-items-center mb-6">
                <h3 class="fw-bold mb-0">{{ __('admin.CreateSurvey') }}</h3>
            </div>

            <!-- Alerts -->
            @if(session()->has('success'))
            <div class="col-md-12 mt-4">
                <div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
                    {{ __('admin.' . session()->get('success')) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            @endif
            @if(session()->has('error'))
            <div class="col-md-12 mt-4">
                <div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
                    {{ __('admin.' . session()->get('error')) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            @endif
            @if(session()->has('msg'))
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-alert-triangle me-3 ti-md"></i>
                            <span>{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if(count($errors) > 0)
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissible fade show mb-2">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <ul>
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <form class="card mb-6" method="post" action="{{ route('surveys.store') }}">
                {!! csrf_field() !!}
                <div class="card-body">
                    <legend class="mb-6 fs-5 border-bottom pb-3 text-dark fw-semibold">{{ __('admin.Survey_Info') }}</legend>
                    <div class="form-group mb-4">
                        <label class="form-label fs-6 mb-1 fw-semibold" for="organization_id">{{ __('admin.Organization') }}</label>
                        <select class="form-select" name="organization_id" id="organization_id" required>
                            @foreach($organizations as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->{'name_' . $l} }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label fs-6 mb-1 fw-semibold" for="title">{{ __('admin.Title') }}</label>
                        <input name="title" type="text" class="form-control" id="title" placeholder="{{ __('admin.Title') }}" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label fs-6 mb-1 fw-semibold" for="description">{{ __('admin.Description') }}</label>
                        <input name="description" type="text" class="form-control" id="description" placeholder="{{ __('admin.Description') }}" />
                    </div>
                </div>

                <div class="card-body">
                    <legend class="mb-6 fs-5 border-bottom pb-3 text-dark fw-semibold">{{ __('admin.SurveyQuestions') }}</legend>
                    <div class="row my-3">
                        <div class="col-6">
                            <button type="button" class="btn btn-label-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#addQuestionsModal">
                                {{ __('admin.AddQuestions') }}
                                <i class="ti ti-plus ms-2"></i>
                            </button>
                        </div>
                    </div>
                      <div id="selected-questions-container"></div>
                      <div id="hidden-inputs-container"></div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            {{ __('admin.Save') }}
                            <i class="ti ti-circle-check ms-2"></i>
                        </button>
                    </div>
                </div>
            </form>

            <div class="modal fade" id="addQuestionsModal" tabindex="-1" aria-labelledby="addQuestionsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addQuestionsModalLabel">{{ __('admin.SelectQuestions') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                            <div class="modal-body">
                              @foreach($questions as $question)
                              <div class="form-check mb-2">
                                  <input class="form-check-input question-checkbox" type="checkbox" value="{{ $question->id }}" id="question-{{ $question->id }}">
                                  <label class="form-check-label" for="question-{{ $question->id }}">
                                      {{ $question->question }}
                                </label>
                              </div>
                              @endforeach
                            </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.Close') }}</button>
                            <button type="button" class="btn btn-primary" id="save-questions-btn">{{ __('admin.Save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('pagescripts')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
$(document).ready(function() {
    const hiddenInputsContainer = $('#hidden-inputs-container');
    let selectedQuestions = [];
    const questions = @json($questions);

    function renderSelectedQuestions() {
        const container = $('#selected-questions-container');
        container.empty();
        hiddenInputsContainer.empty();
        selectedQuestions.forEach((questionId, index) => {
            const question = questions.find(q => q.id == questionId);
            if (question) {
                container.append(`
                    <div class="card border mb-4 question-item" data-id="${question.id}">
                        <div class="card-header">
                            <div class="d-flex align-items-center border-bottom pb-2">
                                <span class="badge badge-center bg-primary rounded-circle d-flex justify-content-center align-items-center" style="width: 30px; height: 30px;">
                                    ${index + 1}
                                </span>
                                <h5 class="card-title ms-2 mb-0 fs-semibold">${question.question}</h5>
                                <button type="button" class="btn btn-sm btn-danger ms-auto remove-question" data-id="${question.id}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            ${renderQuestionBody(question)}
                        </div>
                    </div>
                `);
                // Add hidden input for each question
                hiddenInputsContainer.append(`
                    <input type="hidden" name="selected_questions[]" value="${questionId}" />
                `);
            }
        });
    }

    function renderQuestionBody(question) {
        if (question.survey_type === 'radio') {
            return question.answers.map((answer, idx) => `
                <div class="ps-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer_${question.id}" id="answer-${question.id}-${idx}" value="${idx}" />
                        <label class="form-check-label" for="answer-${question.id}-${idx}">${answer.text}</label>
                    </div>
                </div>
            `).join('');
        } else if (question.survey_type === 'stars') {
            return `
                <ul class="d-flex align-items-center gap-2 list-unstyled mb-0 ps-4">
                    ${'<li><i class="fas fa-star fs-5 text-warning"></i></li>'.repeat(5)}
                </ul>
            `;
        } else if (question.survey_type === 'answers') {
            return `
                <div class="form-group ps-4">
                    <textarea class="form-control" name="answer_${question.id}" rows="3"></textarea>
                </div>
            `;
        }
        return '';
    }

    $('#save-questions-btn').click(function() {
        $('.question-checkbox:checked').each(function() {
            const questionId = $(this).val();
            if (!selectedQuestions.includes(questionId)) {
                selectedQuestions.push(questionId);
            }
        });
        $('.question-checkbox').prop('checked', false);
        renderSelectedQuestions();
        $('#addQuestionsModal').modal('hide');
    });

    $(document).on('click', '.remove-question', function() {
        const questionId = $(this).data('id');
        selectedQuestions = selectedQuestions.filter(id => id != questionId);
        renderSelectedQuestions();
    });

    $('#selected-questions-container').sortable({
        items: '.question-item',
        update: function() {
            selectedQuestions = [];
            $('.question-item').each(function() {
                selectedQuestions.push($(this).data('id'));
            });
            renderSelectedQuestions();
        }
    });

    renderSelectedQuestions();
});
</script>
@stop