@php
  $quiz_questions = !empty($quiz->questions)
      ? $quiz->questions->pluck('id')->toArray()
      : [];
@endphp

@extends('layouts.admin')

@section('title', __('admin.quizzes'))
@section('index_url', route('quizzes.index'))
@section('sub_index_url', route('quizzes.edit', ['quiz' => $quiz->id]))
@section('subTitle', __('admin.manageQuestions'))

@section('content')
  <div class="">
    <div class="">

      <div class="row">
        @if (session()->has('success'))
          <div class="col-md-12">
            <div class="alert alert-success alert-dismissible fade show mb-2"
              role="alert">
              <button class="btn-close" data-bs-dismiss="alert" type="button"
                aria-label="Close"></button>
              <div class="d-flex align-items-center">
                <i class="ti ti-thumb-up ti-md me-3"></i>
                <span>{{ __('admin.' . session()->get('success')) }}</span>
              </div>
            </div>
          </div>
        @endif

        @if (session()->has('error'))
          <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible fade show mb-2"
              role="alert">
              <button class="btn-close" data-bs-dismiss="alert" type="button"
                aria-label="Close"></button>
              <div class="d-flex align-items-center">
                <i class="ti ti-alert-triangle ti-md me-3"></i>
                <span>{{ __('admin.' . session()->get('error')) }}</span>
              </div>
            </div>
          </div>
        @endif

        @if (session()->has('msg'))
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-danger mb-2" role="alert">
                <button class="btn-close" data-bs-dismiss="alert" type="button"
                  aria-label="Close"></button>
                <div class="d-flex align-items-center">
                  <i class="ti ti-alert-triangle ti-md me-3"></i>
                  <span>{{ __('admin.' . session()->get('msg')) }}
                    {{ Session::forget('msg') }}</span>
                </div>
              </div>
            </div>
          </div>
        @endif

        @if (count($errors) > 0)
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-danger mb-2" role="alert">
                <ul>
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            </div>
          </div>
        @endif
      </div>

      <div class="content-header row px-md-6 mb-6 mt-3 px-0">
        <div class="col-md-6 d-flex align-items-center">
          <h4 class="fw-bold mb-3 mb-md-0">{{ __('admin.manageQuestions') }}</h4>
        </div>
        <div class="col-md-6 d-flex flex-wrap justify-content-md-end align-items-md-center gap-2">
          <button class="btn btn-label-primary" data-bs-toggle="modal"
            data-bs-target="#addQuestionsModal" type="button">
            {{ __('admin.AddQuestions') }}
            <i class="ti ti-plus ms-2"></i>
          </button>
          <button class="btn btn-label-secondary" id="select-delete-btn"
            type="button">
            {{ __('admin.SelectForDelete') }}
            <i class="ti ti-checkbox ms-2"></i>
          </button>
          <button class="btn btn-label-danger d-none" id="bulk-delete-btn"
            type="button">
            {{ __('admin.Delete') }}
            <i class="ti ti-trash ms-2"></i>
          </button>
        </div>
      </div>

      <div class="px-md-6 px-0">
        <div class="row d-none mb-3" id="select-title">
          <div class="col-12">
            <div class="d-flex justify-content-end">
              <h6 class="fw-semibold text-muted mb-0">
                {{ __('admin.SelectQuestions') }}
                <i class="ti ti-circle-check ms-2"></i>
              </h6>
            </div>
          </div>
        </div>

        <form method="post"
          action="{{ route('quizzes.questions_update', ['quiz' => $quiz->id]) }}">
          @csrf
          <input id="selected-questions" name="selected_questions" type="hidden"
            value="[]">

          @foreach ($quiz->questions as $key => $question)
            <div class="col-12">
              <div class="card mb-4 border">
                <div class="card-body">
                  <div
                    class="d-flex justify-content-between align-items-center w-100 border-bottom mb-4 pb-2">
                    <div class="d-flex align-items-center">
                      <span
                        class="badge badge-center bg-primary rounded-circle d-flex flex-shrink-0 justify-content-center align-items-center"
                        style="width: 30px; height: 30px;">
                        {{ $key + 1 }}
                      </span>
                      <h6 class="card-title fs-semibold mb-0 ms-2">
                        {{ $question->question }}
                      </h6>
                    </div>
                    <div class="d-flex align-items-center gap-4">
                      <input
                        class="form-check-input question-checkbox d-none ms-auto"
                        name="question_ids[]" type="checkbox"
                        value="{{ $question->id }}">
                      <div class="ms-2">
                        <button class="btn border-none" data-bs-toggle="dropdown"
                          type="button" aria-haspopup="true"
                          aria-expanded="false">
                          <i class="ti ti-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu">
                          <a class="edit-question dropdown-item"
                            data-id="{{ $question->id }}"
                            href="{{ route('questions-bank.edit', ['questions_bank' => $question->id]) }}">
                            <i class="ti ti-edit text-info"></i>
                            {{ __('admin.Edit') }}
                          </a>
                          <a class="delete-question dropdown-item"
                            data-id="{{ $question->id }}" href="#">
                            <i class="ti ti-trash text-danger"></i>
                            {{ __('admin.Delete') }}
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>

                  @if ($question->type == 'mcq')
                    @foreach ($question->answers as $key => $answer)
                      <div class="ps-4">
                        <div class="form-check">
                          <input class="form-check-input"
                            id="answer-{{ $key }}"
                            name="answer_{{ $question['id'] }}" type="radio"
                            value="{{ $key }}"
                            {{ $answer['status'] ? 'checked' : '' }}>
                          <label class="form-check-label"
                            for="answer-{{ $key }}"
                            onclick="this.previousElementSibling.checked = true">
                            {{ $answer['text'] }}
                          </label>
                        </div>
                      </div>
                    @endforeach
                  @elseif ($question->type == 'tfq')
                    <div class="ps-4">
                      <div class="form-check">
                        <input class="form-check-input"
                          id="true-{{ $question->id }}"
                          name="answer_{{ $question['id'] }}" type="radio"
                          value="true"
                          {{ $question->correct == true ? 'checked' : '' }}>
                        <label class="form-check-label"
                          for="true-{{ $question->id }}">
                          {{ __('admin.tfq_statement_true') }}
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input"
                          id="false-{{ $question->id }}"
                          name="answer_{{ $question['id'] }}" type="radio"
                          value="false"
                          {{ $question->correct == false ? 'checked' : '' }}>
                        <label class="form-check-label"
                          for="false-{{ $question->id }}">
                          {{ __('admin.tfq_statement_false') }}
                        </label>
                      </div>
                    </div>
                  @else
                    <div class="form-group ps-4">
                      <textarea class="form-control" name="answer_{{ $question['id'] }}"
                        rows="3" placeholder="{{ __('admin.enterAnswer') }}"></textarea>
                    </div>
                  @endif
                  <input name="questions_ids[]" type="hidden"
                    value="{{ $question->id }}">
                </div>
              </div>
            </div>
          @endforeach
        </form>
      </div>
    </div>
  </div>
  <!-- Add Questions Modal -->
  <div class="modal fade" id="addQuestionsModal"
    aria-labelledby="addQuestionsModalLabel" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addQuestionsModalLabel">
            <i class="ti ti-plus me-2"></i>{{ __('admin.AddQuestions') }}
          </h5>
          <button class="btn-close" data-bs-dismiss="modal" type="button"
            aria-label="Close"></button>
        </div>
        <form id="add-questions-form" method="POST"
          action="{{ route('quizzes.questions_update', ['quiz' => $quiz->id]) }}">
          @csrf
          <div class="modal-body mt-4 p-4"
            style="height: 600px; overflow-y: auto;">
            <div class="row mb-3">
              <div class="col-12">
                <div class="alert alert-info d-flex align-items-center">
                  <i class="ti ti-info-circle me-2"></i>
                  <span>{{ __('admin.SelectQuestionsToAdd') }} <small
                      class="text-muted">{{ __('admin.AlreadyAddedQuestionsAreHighlighted') }}</small></span>
                </div>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="ti ti-search"></i>
                  </span>
                  <input class="form-control" id="question-search"
                    type="text"
                    placeholder="{{ __('admin.SearchQuestions') }}">
                </div>
              </div>
              <div class="col-md-6">
                <select class="form-select" id="question-type-filter">
                  <option value="">{{ __('admin.AllTypes') }}</option>
                  <option value="mcq">{{ __('admin.type_mcq') }}</option>
                  <option value="tfq">{{ __('admin.type_tfq') }}</option>
                  {{-- <option value="text">{{ __('admin.TextQuestion') }}</option> --}}
                </select>
              </div>
            </div>
            <div class="table-responsive mt-8">
              <table class="table-hover table" id="questions-table">
                <thead class="table-light">
                  <tr>
                    <th style="width: 50px;">
                      <div class="form-check" style="margin-bottom: -10px;">
                        <input class="form-check-input"
                          id="select-all-questions" type="checkbox">
                        <label class="form-check-label"
                          for="select-all-questions"></label>
                      </div>
                    </th>
                    <th>{{ __('admin.Question') }}</th>
                    <th class="text-center" style="width: 120px;">
                      {{ __('admin.Type') }}</th>
                    <th class="text-center" style="width: 100px;">
                      {{ __('admin.Status') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($questions as $question)
                    @php
                      $isAlreadyAdded = in_array(
                          $question->id,
                          $quiz_questions,
                      );
                    @endphp
                    <tr class="{{ $isAlreadyAdded ? 'table-success' : '' }}"
                      data-question-type="{{ $question->type }}"
                      data-question-text="{{ strtolower($question->question) }}">
                      <td>
                        <div class="form-check">
                          <input class="form-check-input modal-question-checkbox"
                            id="question-{{ $question->id }}"
                            name="questions_ids[]"
                            data-already-added="{{ $isAlreadyAdded ? 'true' : 'false' }}"
                            type="checkbox" value="{{ $question->id }}"
                            {{ $isAlreadyAdded ? 'checked' : '' }}
                            {{ $isAlreadyAdded ? 'title="This question is already added to the quiz"' : '' }}>
                          <label class="form-check-label"
                            for="question-{{ $question->id }}"></label>
                        </div>
                      </td>
                      <td>
                        <div class="question-preview">
                          <h6 class="text-truncate mb-1"
                            title="{{ $question->question }}"
                            style="max-width: 400px;">
                            {{ $question->question }}
                          </h6>
                          @if ($question->type == 'mcq' && isset($question->answers))
                            <small class="text-muted">
                              <i class="ti ti-list me-1"></i>
                              {{ count($question->answers) }}
                              {{ __('admin.Options') }}
                            </small>
                          @elseif ($question->type == 'tfq')
                            <small class="text-muted">
                              <i class="ti ti-toggle-left me-1"></i>
                              {{ __('admin.TrueFalse') }}
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
                        @if ($question->type == 'mcq')
                          <span class="badge bg-label-info border-info border">
                            <i class="ti ti-list me-1"></i>
                            {{ __('admin.type_mcq') }}
                          </span>
                        @elseif ($question->type == 'tfq')
                          <span
                            class="badge bg-label-warning border-warning border">
                            <i class="ti ti-toggle-left me-1"></i>
                            {{ __('admin.type_tfq') }}
                          </span>
                        @else
                          <span
                            class="badge bg-label-secondary border-secondary border">
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
            @if (count($questions) == 0)
              <div class="py-4 text-center">
                <i class="ti ti-inbox fs-1 text-muted mb-3"></i>
                <h6 class="text-muted">{{ __('admin.NoQuestionsAvailable') }}
                </h6>
              </div>
            @endif
            <div class="d-none py-4 text-center" id="no-results">
              <i class="ti ti-search-off fs-1 text-muted mb-3"></i>
              <h6 class="text-muted">{{ __('admin.NoQuestionsFound') }}</h6>
            </div>
          </div>
          <div class="modal-footer">
            <div
              class="d-flex justify-content-between w-100 h-px-50 align-items-center mt-6">
              <div class="text-muted">
                <i class="ti ti-info-circle me-1"></i>
                <span id="selected-count">0</span>
                {{ __('admin.NewQuestionsSelected') }}
              </div>
              <div>
                <button class="btn btn-label-secondary me-4"
                  data-bs-dismiss="modal" type="button">
                  <i class="ti ti-x me-1"></i>{{ __('admin.Close') }}
                </button>
                <button class="btn btn-primary" type="submit">
                  <i class="ti ti-check me-1"></i>{{ __('admin.AddSelected') }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
@section('pagescripts')
  <script>
    const questions = @json($questions);
    const quizQuestions = @json($quiz_questions);

    document.getElementById('select-delete-btn').addEventListener('click',
      function() {
        document.querySelectorAll('.question-checkbox').forEach(function(
          checkbox) {
          checkbox.classList.toggle('d-none');
        });
        document.querySelector('#select-title').classList.toggle('d-none');
      });

    document.querySelectorAll('.question-checkbox').forEach(function(checkbox) {
      checkbox.addEventListener('change', function() {
        const anyChecked = Array.from(document.querySelectorAll(
          '.question-checkbox')).some(cb => cb.checked);
        document.querySelector('#bulk-delete-btn').classList.toggle(
          'd-none', !anyChecked);
      });
    });

    document.getElementById('add-questions-form').addEventListener('submit',
      function(e) {
        e.preventDefault();

        // Add loading state
        this.classList.add('loading');
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML =
          '<i class="ti ti-loader-2 me-1 spinner-border spinner-border-sm"></i>{{ __('admin.Adding') }}...';

        const selectedQuestions = Array.from(document.querySelectorAll(
            '.modal-question-checkbox:checked'))
          .filter(checkbox => checkbox.dataset.alreadyAdded !== 'true')
          .map(checkbox => parseInt(checkbox.value));

        if (selectedQuestions.length === 0) {
          // Remove loading state if no questions selected
          this.classList.remove('loading');
          submitBtn.innerHTML = originalText;

          // Show alert
          releaseAlert('warning', {
            title: "{{ __('admin.NoQuestionsSelected') }}",
            message: "{{ __('admin.PleaseSelectQuestions') }}",
            confirmText: "{{ __('admin.Ok') }}",
            confirmColor: 'primary',
            hideCancel: true
          });
          return;
        }

        const currentQuestions = JSON.parse(document.getElementById(
          'selected-questions').value);
        const updatedQuestions = [...new Set([...currentQuestions, ...
          selectedQuestions
        ])];
        document.getElementById('selected-questions').value = JSON.stringify(
          updatedQuestions);

        this.submit();
      });

    // Enhanced modal functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Select All functionality
      const selectAllCheckbox = document.getElementById(
        'select-all-questions');
      const modalCheckboxes = document.querySelectorAll(
        '.modal-question-checkbox:not([data-already-added="true"])');

      if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
          modalCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
          });
          updateSelectedCount();
        });
      }

      // Update selected count
      function updateSelectedCount() {
        const selectedCount = document.querySelectorAll(
          '.modal-question-checkbox:checked:not([data-already-added="true"])'
          ).length;
        const countElement = document.getElementById('selected-count');
        if (countElement) {
          countElement.textContent = selectedCount;
        }

        // Update select all checkbox state
        if (selectAllCheckbox) {
          const totalAvailable = modalCheckboxes.length;
          const selectedAvailable = document.querySelectorAll(
            '.modal-question-checkbox:checked:not([data-already-added="true"])'
            ).length;

          selectAllCheckbox.checked = selectedAvailable > 0 &&
            selectedAvailable === totalAvailable;
          selectAllCheckbox.indeterminate = selectedAvailable > 0 &&
            selectedAvailable < totalAvailable;
        }
      }

      // Add event listeners to individual checkboxes
      modalCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
      });

      // Prevent already added questions from being unchecked
      document.querySelectorAll(
        '.modal-question-checkbox[data-already-added="true"]').forEach(
        checkbox => {
          checkbox.addEventListener('click', function(e) {
            // Always keep already added questions checked
            this.checked = true;
            e.preventDefault();
          });
        });

      // Initialize count on modal show
      const modal = document.getElementById('addQuestionsModal');
      if (modal) {
        modal.addEventListener('shown.bs.modal', function() {
          updateSelectedCount();
        });
      }

      // Search and filter functionality
      const searchInput = document.getElementById('question-search');
      const typeFilter = document.getElementById('question-type-filter');
      const tableBody = document.querySelector('#questions-table tbody');
      const noResultsDiv = document.getElementById('no-results');

      function filterQuestions() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedType = typeFilter.value;
        const rows = tableBody.querySelectorAll('tr');
        let visibleRows = 0;

        rows.forEach(row => {
          const questionText = row.dataset.questionText || '';
          const questionType = row.dataset.questionType || '';

          const matchesSearch = questionText.includes(searchTerm);
          const matchesType = !selectedType || questionType ===
            selectedType;

          if (matchesSearch && matchesType) {
            row.style.display = '';
            visibleRows++;
          } else {
            row.style.display = 'none';
          }
        });

        // Show/hide no results message
        if (noResultsDiv) {
          if (visibleRows === 0 && rows.length > 0) {
            noResultsDiv.classList.remove('d-none');
          } else {
            noResultsDiv.classList.add('d-none');
          }
        }

        // Update counts after filtering
        updateSelectedCount();
      }

      if (searchInput) {
        searchInput.addEventListener('input', filterQuestions);
      }

      if (typeFilter) {
        typeFilter.addEventListener('change', filterQuestions);
      }
    });

    document.getElementById('bulk-delete-btn').addEventListener('click',
      function() {
        releaseAlert('warning', {
          title: "{{ __('admin.SureDelete') }}",
          message: "{{ __('admin.Recover') }}",
          confirmText: "{{ __('admin.DYes') }}",
          cancelText: "{{ __('admin.No') }}",
          confirmColor: 'danger'
        }).then((ok) => {
          if (ok) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action =
              '{{ route('quizzes.questions_delete', ['quiz' => $quiz->id]) }}';
            form.style.display = 'none';

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = '{{ csrf_token() }}';
            form.appendChild(token);

            document.querySelectorAll('.question-checkbox:checked').forEach(
              checkbox => {
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
      releaseAlert('warning', {
        title: "{{ __('admin.SureDelete') }}",
        message: "{{ __('admin.Recover') }}",
        confirmText: "{{ __('admin.DYes') }}",
        cancelText: "{{ __('admin.No') }}",
        confirmColor: 'danger'
      }).then((ok) => {
        if (ok) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action =
            '{{ route('quizzes.questions_delete', ['quiz' => $quiz->id]) }}';
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
@endsection
