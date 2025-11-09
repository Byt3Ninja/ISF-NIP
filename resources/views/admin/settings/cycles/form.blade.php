@extends('layouts.admin')

<?php
  // Static form structure
  $form = $data;

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