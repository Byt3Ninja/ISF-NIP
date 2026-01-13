@extends('layouts.admin')
@section('title', __('admin.surveys'))
@section('index_url', route('surveys.index'))
@section('subTitle', __('admin.EditSurvey'))

@section('content')
<div class="">
    <div class="" style="padding: 2rem 4rem !important;">
        <div>
            <div class="w-100 d-flex flex-wrap justify-content-between align-items-center mb-6">
                <h3 class="fw-bold mb-0">{{ __('admin.EditSurvey') }}</h3>
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

            <form class="card mb-6" method="post" action="{{ route('surveys.update', $survey->id) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <legend class="mb-6 fs-5 border-bottom pb-3 text-dark fw-semibold">{{ __('admin.Survey_Info') }}</legend>
                    <div class="form-group mb-4">
                        <label class="form-label fs-6 mb-1 fw-semibold" for="organization_id">{{ __('admin.Organization') }}</label>
                        <select class="form-select" name="organization_id" id="organization_id" required>
                            @foreach($organizations as $organization)
                            <option value="{{ $organization->id }}" {{ $survey->organization_id == $organization->id ? 'selected' : '' }}>
                                {{ $organization->{'name_' . $l} }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label fs-6 mb-1 fw-semibold" for="title">{{ __('admin.Title') }}</label>
                        <input name="title" type="text" class="form-control" id="title" value="{{ $survey->title }}" placeholder="{{ __('admin.Title') }}" required />
                    </div>
                    <div class="form-group mb-6">
                        <label class="form-label fs-6 mb-1 fw-semibold" for="description">{{ __('admin.Description') }}</label>
                        <input name="description" type="text" class="form-control" id="description" value="{{ $survey->description }}" placeholder="{{ __('admin.Description') }}" />
                    </div>
                    <div class="form-group mb-4">
                        <div class="form-check">
                            <label class="form-check-label fs-6 mb-1 fw-semibold" for="required">
                                {{ __('admin.Is_Required') }}
                                <input name="is_required" type="checkbox" value="1" class="form-check-input" id="required" {{ $survey->is_required ? 'checked' : '' }} />
                            </label>
                        </div>
                        <small class="text-muted">{{ __('admin.Is_Required_Survey_Description') }}</small>
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

            <!-- Add Questions Modal -->
            <div class="modal fade" id="addQuestionsModal" aria-labelledby="addQuestionsModalLabel" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addQuestionsModalLabel">
                                <i class="ti ti-plus me-2"></i>{{ __('admin.AddQuestions') }}
                            </h5>
                            <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                        </div>
                        <form id="add-questions-form">
                            <div class="modal-body mt-4 p-4" style="height: 600px; overflow-y: auto;">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="alert alert-info d-flex align-items-center">
                                            <i class="ti ti-info-circle me-2"></i>
                                            <span>{{ __('admin.SelectQuestionsToAdd') }} <small class="text-muted">{{ __('admin.AlreadyAddedQuestionsAreHighlighted') }}</small></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="ti ti-search"></i>
                                            </span>
                                            <input class="form-control" id="question-search" type="text" placeholder="{{ __('admin.SearchQuestions') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-select" id="question-type-filter">
                                            <option value="">{{ __('admin.AllTypes') }}</option>
                                            <option value="radio">{{ __('admin.RadioQuestion') }}</option>
                                            <option value="stars">{{ __('admin.StarsQuestion') }}</option>
                                            <option value="text">{{ __('admin.TextQuestion') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive mt-8">
                                    <table class="table-hover table" id="questions-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 50px;">
                                                    <div class="form-check" style="margin-bottom: -10px;">
                                                        <input class="form-check-input" id="select-all-questions" type="checkbox">
                                                        <label class="form-check-label" for="select-all-questions"></label>
                                                    </div>
                                                </th>
                                                <th>{{ __('admin.Question') }}</th>
                                                <th class="text-center" style="width: 120px;">{{ __('admin.Type') }}</th>
                                                <th class="text-center" style="width: 100px;">{{ __('admin.Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($availableQuestions as $question)
                                            @php
                                                $isAlreadyAdded = in_array($question->id, $survey->questions->pluck('id')->toArray());
                                            @endphp
                                            <tr class="{{ $isAlreadyAdded ? 'table-success' : '' }}" data-question-type="{{ $question->survey_type ?? 'text' }}" data-question-text="{{ strtolower($question->question) }}">
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input modal-question-checkbox" id="question-{{ $question->id }}" name="questions_ids[]" data-already-added="{{ $isAlreadyAdded ? 'true' : 'false' }}" type="checkbox" value="{{ $question->id }}" {{ $isAlreadyAdded ? 'checked' : '' }} {{ $isAlreadyAdded ? 'title="This question is already added to the survey"' : '' }}>
                                                        <label class="form-check-label" for="question-{{ $question->id }}"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="question-preview">
                                                        <h6 class="text-truncate mb-1" title="{{ $question->question }}" style="max-width: 400px;">
                                                            {{ $question->question }}
                                                        </h6>
                                                        @if ($question->survey_type == 'radio' && isset($question->answers))
                                                        <small class="text-muted">
                                                            <i class="ti ti-list me-1"></i>
                                                            {{ count($question->answers) }} {{ __('admin.Options') }}
                                                        </small>
                                                        @elseif ($question->survey_type == 'stars')
                                                        <small class="text-muted">
                                                            <i class="ti ti-star me-1"></i>
                                                            {{ __('admin.StarRating') }}
                                                        </small>
                                                        @else
                                                        <small class="text-muted">
                                                            <i class="ti ti-file-text me-1"></i>
                                                            {{ __('admin.TextAnswer') }}
                                                        </small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if ($question->survey_type == 'radio')
                                                    <span class="badge bg-label-info border-info border">
                                                        <i class="ti ti-list me-1"></i>
                                                        {{ __('admin.RadioQuestion') }}
                                                    </span>
                                                    @elseif ($question->survey_type == 'stars')
                                                    <span class="badge bg-label-warning border-warning border">
                                                        <i class="ti ti-star me-1"></i>
                                                        {{ __('admin.StarsQuestion') }}
                                                    </span>
                                                    @else
                                                    <span class="badge bg-label-secondary border-secondary border">
                                                        <i class="ti ti-file-text me-1"></i>
                                                        {{ __('admin.TextQuestion') }}
                                                    </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($isAlreadyAdded)
                                                    <span class="badge bg-label-success">
                                                        <i class="ti ti-check me-1"></i>
                                                        {{ __('admin.Added') }}
                                                    </span>
                                                    @else
                                                    <span class="badge bg-light text-dark">
                                                        <i class="ti ti-plus me-1"></i>
                                                        {{ __('admin.Available') }}
                                                    </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if (count($availableQuestions) == 0)
                                <div class="py-4 text-center">
                                    <i class="ti ti-inbox fs-1 text-muted mb-3"></i>
                                    <h6 class="text-muted">{{ __('admin.NoQuestionsAvailable') }}</h6>
                                </div>
                                @endif
                                <div class="d-none py-4 text-center" id="no-results">
                                    <i class="ti ti-search-off fs-1 text-muted mb-3"></i>
                                    <h6 class="text-muted">{{ __('admin.NoQuestionsFound') }}</h6>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="d-flex justify-content-between w-100 h-px-50 align-items-center mt-6">
                                    <div class="text-muted">
                                        <i class="ti ti-info-circle me-1"></i>
                                        <span id="selected-count">0</span>
                                        {{ __('admin.NewQuestionsSelected') }}
                                    </div>
                                    <div>
                                        <button class="btn btn-label-secondary me-4" data-bs-dismiss="modal" type="button">
                                            <i class="ti ti-x me-1"></i>{{ __('admin.Close') }}
                                        </button>
                                        <button class="btn btn-primary" type="button" id="save-questions-btn">
                                            <i class="ti ti-check me-1"></i>{{ __('admin.AddSelected') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
    let selectedQuestions = @json($survey->questions->pluck('id')->toArray());
    const questions = @json($availableQuestions);

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

    // Enhanced modal functionality
    const selectAllCheckbox = $('#select-all-questions');
    const modalCheckboxes = $('.modal-question-checkbox');

    // Select All functionality
    selectAllCheckbox.on('change', function() {
        modalCheckboxes.each(function() {
            if ($(this).data('already-added') !== 'true') {
                $(this).prop('checked', selectAllCheckbox.prop('checked'));
            }
        });
        updateSelectedCount();
    });

    // Update selected count
    function updateSelectedCount() {
        const selectedCount = $('.modal-question-checkbox:checked').not('[data-already-added="true"]').length;
        $('#selected-count').text(selectedCount);

        // Update select all checkbox state
        const totalAvailable = modalCheckboxes.not('[data-already-added="true"]').length;
        const selectedAvailable = $('.modal-question-checkbox:checked').not('[data-already-added="true"]').length;

        selectAllCheckbox.prop('checked', selectedAvailable > 0 && selectedAvailable === totalAvailable);
        selectAllCheckbox.prop('indeterminate', selectedAvailable > 0 && selectedAvailable < totalAvailable);
    }

    // Add event listeners to individual checkboxes
    modalCheckboxes.on('change', function() {
        if ($(this).data('already-added') === 'true') {
            $(this).prop('checked', true);
        }
        updateSelectedCount();
    });

    // Prevent already added questions from being unchecked
    modalCheckboxes.filter('[data-already-added="true"]').on('click', function(e) {
        $(this).prop('checked', true);
        e.preventDefault();
    });

    // Initialize count on modal show
    $('#addQuestionsModal').on('shown.bs.modal', function() {
        updateSelectedCount();
    });

    // Search and filter functionality
    const searchInput = $('#question-search');
    const typeFilter = $('#question-type-filter');
    const tableBody = $('#questions-table tbody');
    const noResultsDiv = $('#no-results');

    function filterQuestions() {
        const searchTerm = searchInput.val().toLowerCase();
        const selectedType = typeFilter.val();
        const rows = tableBody.find('tr');
        let visibleRows = 0;

        rows.each(function() {
            const questionText = $(this).data('question-text') || '';
            const questionType = $(this).data('question-type') || '';

            const matchesSearch = questionText.includes(searchTerm);
            let matchesType = false;
            
            if (!selectedType) {
                matchesType = true;
            } else if (selectedType === 'text') {
                // For text questions, match both 'text' and 'answers' types
                matchesType = questionType === 'text' || questionType === 'answers' || questionType === '' || questionType === null;
            } else {
                matchesType = questionType === selectedType;
            }

            if (matchesSearch && matchesType) {
                $(this).show();
                visibleRows++;
            } else {
                $(this).hide();
            }
        });

        // Show/hide no results message
        if (visibleRows === 0 && rows.length > 0) {
            noResultsDiv.removeClass('d-none');
        } else {
            noResultsDiv.addClass('d-none');
        }

        // Update counts after filtering
        updateSelectedCount();
    }

    searchInput.on('input', filterQuestions);
    typeFilter.on('change', filterQuestions);

    $('#save-questions-btn').click(function() {
        const selectedCheckboxes = $('.modal-question-checkbox:checked').not('[data-already-added="true"]');
        
        if (selectedCheckboxes.length === 0) {
            // Show alert if no questions selected
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: "{{ __('admin.NoQuestionsSelected') }}",
                    text: "{{ __('admin.PleaseSelectQuestions') }}",
                    icon: "warning",
                    confirmButtonText: "{{ __('admin.Ok') }}",
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            } else {
                alert('{{ __('admin.PleaseSelectQuestions') }}');
            }
            return;
        }

        selectedCheckboxes.each(function() {
            const questionId = $(this).val();
            if (!selectedQuestions.includes(questionId)) {
                selectedQuestions.push(questionId);
            }
        });
        
        $('.modal-question-checkbox').prop('checked', false);
        // Re-check already added questions
        $('.modal-question-checkbox[data-already-added="true"]').prop('checked', true);
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