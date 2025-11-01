@extends('layouts.admin')

<?php
  // Static form structure for cycles
  $form = [
    'id' => $step->id,
    'title' => $step->title,
    'description' => $step->description,
    'build_data' => [
      [
        'title' => $step->title,
        'description' => $step->description,
        'logic' => 'submit',
        'questions' => collect($fields)->map(function($field, $index) use ($answers) {
          $options = $field->pivot->options ? json_decode($field->pivot->options) : null;
          $get_answer = collect($answers)->where('field_id', $field->pivot->id)->first();
          $answer = $get_answer ? $get_answer->answer : null;
          
          $question = [
            'id' => $field->pivot->id,
            'label' => $options->label ?? 'Field ' . ($index + 1),
            'is_required' => true,
            'type' => $field->field_type,
            'options' => [],
            'logic' => [],
            'answer' => $answer
          ];
          
          if ($field->field_type === 'select' && isset($options->values)) {
            $question['options'] = array_map('trim', explode(',', $options->values));
            $question['logic'] = array_fill_keys($question['options'], 'continue');
          }
          
          if ($field->field_type === 'textarea' && isset($options->rows)) {
            $question['rows'] = $options->rows;
          }
          
          return $question;
        })->toArray()
      ]
    ]
  ];
?>

@section('title', __('cycles.Cycle'))
@section('subTitle', __('cycles.join Cycle'))

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-body">
      <section>
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                <div>
                  <h4 class="card-title mb-1">{{ $form['title'] }}</h4>
                  <small class="text-muted">{{ $form['description'] }}</small>
                </div>
              </div>
              <div class="card-body">
                @if(session()->has('msg'))
                <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show" role="alert">
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  <div class="d-flex align-items-center">
                    <i class="bx bx-{{Session::get('class')=='success'?'check-circle':'error'}} me-2"></i>
                    <span>{{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }}</span>
                  </div>
                </div>
                @endif

                @if($form && $form['build_data'] && count($form['build_data']) > 0)
                <form method="post" action="/dashboard/cycles/save" enctype="multipart/form-data" class="mt-3">
                  @csrf
                  <input type="hidden" name="cycle" value="{{$c}}" />
                  <input type="hidden" name="id" value="{{$step->id}}" />
                  <input type="hidden" name="field_ids" value="{{ $field_ids }}" />

                  @foreach($form['build_data'] as $sectionIndex => $section)
                  <div class="section-wrapper mb-4" data-section-logic="{{ $section['logic'] ?? 'continue' }}" data-section-id="{{ $sectionIndex }}">
                    <div class="card border shadow-sm">
                      <div class="card-body p-4">
                        <div class="row g-4">
                          @foreach($section['questions'] as $question)
                          @php
                            $field_name = 'field_' . $question['id'];
                            $id_for = 'input-fld-' . $question['id'];
                          @endphp

                          <div class="col-12">
                            <div class="form-group">
                              @if($question['label'])
                                <label for="{{ $id_for }}" class="form-label fw-semibold mb-2">
                                  {{ $question['label'] }}
                                  @if($question['is_required'])
                                    <span class="text-danger ms-1">*</span>
                                  @endif
                                </label>
                              @endif

                              @switch($question['type'])
                                @case('select')
                                  <select 
                                    id="{{ $id_for }}" 
                                    name="{{ $field_name }}"
                                    class="form-select question-logic @error($field_name) is-invalid @enderror"
                                    {{ $question['is_required'] ? 'required' : '' }}
                                  >
                                    <option value="">{{$l=='ar'?'اختر':'Choose'}}...</option>
                                    @foreach($question['options'] as $option)
                                      <option value="{{ $option }}" 
                                        data-logic="{{ $question['logic'][$option] ?? 'continue' }}"
                                        {{ (old($field_name) == $option || $question['answer'] == $option) ? 'selected' : '' }}>
                                        {{ $option }}
                                      </option>
                                    @endforeach
                                  </select>
                                  @break

                                @case('textarea')
                                  <textarea 
                                    id="{{ $id_for }}" 
                                    name="{{ $field_name }}" 
                                    rows="{{ $question['rows'] ?? 4 }}"
                                    class="form-control @error($field_name) is-invalid @enderror"
                                    placeholder="{{ $question['label'] }}"
                                    {{ $question['is_required'] ? 'required' : '' }}
                                  >{{ old($field_name) ?? $question['answer'] }}</textarea>
                                  @break

                                @case('file')
                                  <div class="d-flex flex-column">
                                    <input 
                                      type="file"
                                      class="form-control @error($field_name) is-invalid @enderror"
                                      id="{{ $id_for }}" 
                                      name="{{ $field_name }}"
                                      accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                                      {{ $question['is_required'] ? 'required' : '' }}
                                    />
                                    @if($question['answer'])
                                      <div class="mt-4">
                                        <a href="/{{ $question['answer'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                          <i class="ti ti-file me-2"></i> {{ __('admin.View File') }}
                                        </a>
                                      </div>
                                    @endif
                                  </div>
                                  @break

                                @default
                                  <input 
                                    type="text"
                                    class="form-control datepicker @error($field_name) is-invalid @enderror"
                                    id="{{ $id_for }}" 
                                    name="{{ $field_name }}" 
                                    value="{{ old($field_name) ?? $question['answer'] }}"
                                    placeholder="{{ $question['label'] }}"
                                    {{ $question['is_required'] ? 'required' : '' }}
                                  />
                              @endswitch

                              @error($field_name)
                              <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                            </div>
                          </div>
                          @endforeach
                        </div>
                      </div>
                    </div>
                  </div>
                  @endforeach

                  <button type="submit" class="btn btn-primary btn-lg mt-4">
                    <i class="ti ti-send me-2"></i>{{ __('admin.submit') }}
                  </button>
                </form>
                @else
                  <div class="alert alert-warning">
                    {{ __('cycles.No Fields') }}
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
  // Initialize date pickers
  flatpickr(".datepicker", { dateFormat: "Y-m-d", allowInput: true });

  document.addEventListener("DOMContentLoaded", function () {
    const sections = document.querySelectorAll(".section-wrapper");
    const form = document.querySelector("form");
    const submitBtn = form?.querySelector('button[type="submit"]');
    const submitCard = submitBtn?.closest('.card');
    const logicInputs = document.querySelectorAll(".question-logic");

    // All sections visible
    sections.forEach(section => {
      section.style.display = "block";
    });

    function handleLogic(element) {
      if (!element) return;
      let logic = "continue";

      // --- Determine logic type ---
      if (element.tagName === "SELECT") {
        const selectedOption = element.options[element.selectedIndex];
        logic = selectedOption ? selectedOption.dataset.logic : "continue";
      }

      // --- Apply logic behavior ---
      if (logic === "submit") {
        // Scroll to submit button smoothly, then trigger submit
        if (submitCard) {
          submitCard.scrollIntoView({ behavior: "smooth", block: "center" });
        }
        setTimeout(() => {
          if (form) form.submit();
        }, 600);
      }
    }

    // --- Attach listeners ---
    logicInputs.forEach(input => {
      input.addEventListener("change", function () {
        handleLogic(this);
      });
    });
  });
</script>
@stop