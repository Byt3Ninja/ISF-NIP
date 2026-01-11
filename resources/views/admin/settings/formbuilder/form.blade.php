@extends('layouts.admin')

<?php
  // Fetch form info (example using Eloquent)
  // $data = App\BuilderForm::findOrFail($id);

  // Static form structure
  $form = $data;
?>

@section('title', __('builder.Forms builder'))
@section('subTitle', __('builder.Preview'))

@section('content')
<div class="">
  <div class="">
    <div class="content-body">
      <section>
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                <div>
                  <h4 class="card-title mb-1">{{ $form['title'] }}</h4>
                  <small class="text-muted">{{ __('builder.Form') }} ID: {{ $form['id'] }}</small>
                </div>
                <a href="/dashboard/show-builder/{{ $form['id'] }}" class="btn btn-outline-primary btn-sm">
                  <i class="ti ti-edit me-1"></i>{{ __('builder.Edit') }}
                </a>
              </div>
              <div class="card-body">
                @if($form && $form['build_data'] && count($form['build_data']) > 0)
                <form action="{{ route('forms.submissions_create') }}" method="POST" class="mt-3">
                  @csrf
                  <input type="hidden" name="builder_form_id" value="{{ $id }}" />

                  @foreach($form['build_data'] as $sectionIndex => $section)
                  <div class="section-wrapper mb-4" data-section-logic="{{ $section['logic'] ?? 'continue' }}" data-section-id="{{ $sectionIndex }}">
                    @if($section['title'] || $section['description'])
                    <div class="section-header mb-3">
                      @if($section['title'])
                      <h5 class="section-title text-primary mb-1">{{ $section['title'] }}</h5>
                      @endif
                      @if($section['description'])
                      <p class="section-description text-muted mb-0">{{ $section['description'] }}</p>
                      @endif
                    </div>
                    @endif
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
                                @case('dropdown')
                                  <select 
                                    id="{{ $id_for }}" 
                                    name="{{ $field_name }}"
                                    class="form-select question-logic @error($field_name) is-invalid @enderror"
                                    {{ $question['is_required'] ? 'required' : '' }}
                                  >
                                    <option value="">{{ $question['label'] }}</option>
                                    @foreach($question['options'] as $option)
                                      <option value="{{ $option }}" 
                                        data-logic="{{ $question['logic'][$option] ?? 'continue' }}"
                                        {{ old($field_name) == $option ? 'selected' : '' }}>
                                        {{ $option === '__OTHER__' ? __('builder.Other') : $option }}
                                      </option>
                                    @endforeach
                                  </select>
                                  @break

                                @case('paragraph')
                                  <textarea 
                                    id="{{ $id_for }}" 
                                    name="{{ $field_name }}" 
                                    rows="3"
                                    class="form-control @error($field_name) is-invalid @enderror"
                                    placeholder="{{ $question['label'] }}"
                                    {{ $question['is_required'] ? 'required' : '' }}
                                  >{{ old($field_name) }}</textarea>
                                  @break

                                @case('multiple_choice')
                                  <div class="options-group">
                                    @foreach($question['options'] as $optionIndex => $option)
                                    <div class="form-check mb-2">
                                      <input 
                                        class="form-check-input question-logic" 
                                        type="radio" 
                                        name="{{ $field_name }}" 
                                        id="{{ $id_for }}_{{ $optionIndex }}" 
                                        value="{{ $option }}"
                                        data-logic="{{ $question['logic'][$option] ?? 'continue' }}"
                                        {{ old($field_name) == $option ? 'checked' : '' }}
                                        {{ $question['is_required'] ? 'required' : '' }}
                                      />
                                      <label class="form-check-label" for="{{ $id_for }}_{{ $optionIndex }}">
                                        {{ $option === '__OTHER__' ? __('builder.Other') : $option }}
                                      </label>
                                    </div>
                                    @endforeach
                                  </div>
                                  @break

                                @case('checkboxes')
                                  <div class="options-group">
                                    @foreach($question['options'] as $optionIndex => $option)
                                    <div class="form-check mb-2">
                                      <input 
                                        class="form-check-input question-logic" 
                                        type="checkbox" 
                                        name="{{ $field_name }}[]" 
                                        id="{{ $id_for }}_{{ $optionIndex }}" 
                                        value="{{ $option }}"
                                        data-logic="{{ $question['logic'][$option] ?? 'continue' }}"
                                      />
                                      <label class="form-check-label" for="{{ $id_for }}_{{ $optionIndex }}">
                                        {{ $option === '__OTHER__' ? __('builder.Other') : $option }}
                                      </label>
                                    </div>
                                    @endforeach
                                  </div>
                                  @break

                                @case('linear_scale')
                                  <select 
                                    id="{{ $id_for }}" 
                                    name="{{ $field_name }}"
                                    class="form-select @error($field_name) is-invalid @enderror"
                                    {{ $question['is_required'] ? 'required' : '' }}
                                  >
                                    <option value="">Select a rating</option>
                                    @for($i = 1; $i <= 5; $i++)
                                      <option value="{{ $i }}" {{ old($field_name) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                  </select>
                                  @break

                                @case('date')
                                  <input 
                                    type="text"
                                    class="form-control datepicker @error($field_name) is-invalid @enderror"
                                    id="{{ $id_for }}" 
                                    name="{{ $field_name }}" 
                                    value="{{ old($field_name) }}"
                                    placeholder="{{ $question['label'] }}"
                                    {{ $question['is_required'] ? 'required' : '' }}
                                  />
                                  @break

                                @case('time')
                                  <input 
                                    type="text"
                                    class="form-control timepicker @error($field_name) is-invalid @enderror"
                                    id="{{ $id_for }}" 
                                    name="{{ $field_name }}" 
                                    value="{{ old($field_name) }}"
                                    placeholder="{{ $question['label'] }}"
                                    {{ $question['is_required'] ? 'required' : '' }}
                                  />
                                  @break

                                @case('file')
                                  <input 
                                    type="file"
                                    class="form-control @error($field_name) is-invalid @enderror"
                                    id="{{ $id_for }}" 
                                    name="{{ $field_name }}"
                                    {{ $question['is_required'] ? 'required' : '' }}
                                  />
                                  @break

                                @case('short_answer')
                                @default
                                  <input 
                                    type="text"
                                    class="form-control @error($field_name) is-invalid @enderror"
                                    id="{{ $id_for }}" 
                                    name="{{ $field_name }}" 
                                    value="{{ old($field_name) }}"
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

                  <div class="card border-0 mt-4">
                    <div class="card-body p-4 text-center">
                      <div class="d-flex justify-content-center gap-3">
                        @if(auth()->user()->hasAnyRole('registered'))
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                          <i class="ti ti-send me-2"></i>{{ __('builder.Submit_Form') }}
                        </button>
                        @endif
                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="location.href='/dashboard/show-builder/{{ $id }}'">
                          <i class="ti ti-edit me-2"></i>{{ __('builder.Edit_Form') }}
                        </button>
                      </div>
                    </div>
                  </div>
                </form>
                @else
                  <div class="alert alert-warning">
                    {{ __('builder.No_fields_available') }}
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
  // Initialize date/time pickers
  flatpickr(".datepicker", { dateFormat: "Y-m-d", allowInput: true });
  flatpickr(".timepicker", { enableTime: true, noCalendar: true, dateFormat: "H:i", allowInput: true });

  document.addEventListener("DOMContentLoaded", function () {
    const sections = document.querySelectorAll(".section-wrapper");
    const form = document.querySelector("form");
    const submitBtn = form?.querySelector('.btn[type="submit"]');
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
      } else if (element.type === "radio") {
        logic = element.dataset.logic || "continue";
      } else if (element.type === "checkbox") {
        const optionsGroup = element.closest(".options-group");
        if (optionsGroup) {
          const checkedBoxes = optionsGroup.querySelectorAll("input[type='checkbox']:checked");
          for (const box of checkedBoxes) {
            if (box.dataset.logic === "submit") {
              logic = "submit";
              break;
            } else if (box.dataset.logic.startsWith("section-")) {
              logic = box.dataset.logic;
              break;
            }
          }
        }
      }

      const currentSection = element.closest(".section-wrapper");
      if (!currentSection) return;
      const currentIndex = Array.from(sections).indexOf(currentSection);

      // --- Check section-level logic ---
      const sectionLogic = currentSection.dataset.sectionLogic || "continue";
      if (sectionLogic === "submit" && logic === "continue") {
        logic = "submit";
      }

      // --- Apply logic behavior ---
      if (logic === "continue") {
        const nextSection = sections[currentIndex + 1];
        if (nextSection) {
          nextSection.scrollIntoView({ behavior: "smooth", block: "start" });
        } else if (submitCard) {
          submitCard.scrollIntoView({ behavior: "smooth", block: "center" });
        }
      } else if (logic.startsWith("section-")) {
        const targetId = parseInt(logic.replace("section-", ""));
        if (sections[targetId]) {
          sections[targetId].scrollIntoView({ behavior: "smooth", block: "start" });
        }
      } else if (logic === "submit") {
        // Scroll to submit button smoothly, then trigger submit
        if (submitCard) {
          submitCard.scrollIntoView({ behavior: "smooth", block: "center" });
        }
        // setTimeout(() => {
        //   if (form) form.submit();
        // }, 600); // small delay for scroll animation
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
