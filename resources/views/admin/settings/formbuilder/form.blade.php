@extends('layouts.admin')
<?php
$title=App\BuilderForm::findOrFail($id);
?>

@section('title', __('builder.Forms builder') )
@section('subTitle', __('builder.Preview'))

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-body">
      <section>
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header pb-0">
                <h5 class="card-title mb-0">
                  <strong>{{ __('builder.Form') }}:</strong> {{ $title->form_name }},
                  <strong>{{ __('builder.ID') }}:</strong> {{ $title->id }}
                  <a href="/dashboard/show-builder/{{ $id }}" class="ms-1">({{ __('builder.Edit') }})</a>
                </h5>
              </div>
              <hr class="my-2" />
              <div class="card-body">
                @if(count($fields) > 0)
                <form action="{{ route('forms.submissions_create') }}" method="POST" class="mt-3">
                  @csrf
                  <input type="hidden" name="builder_form_id" value="{{ $id }}" />
                  <input type="hidden" name="field_ids" value="{{ $field_ids }}" />

                  <div class="row">
                    @foreach($fields as $field)
                    @php
                      $options = $field->pivot->options ? json_decode($field->pivot->options) : null;
                      $field_name = 'field_' . $field->pivot->id;
                      $id_for = 'input-fld-'. $loop->iteration;
                    @endphp

                    <div class="col-md-6 mb-3">
                      @if($options->label)
                        <label for="{{ $id_for }}" class="form-label fw-bold">
                          {{ $options->label }}
                        </label>
                      @endif

                      @switch($field->field_type)
                        @case('select')
                          <select id="{{ $id_for }}" name="{{ $field_name }}"
                            class="form-select @error($field_name) is-invalid @enderror">
                            <option value="">{{ $l == 'ar' ? 'اختر' : 'Choose' }}...</option>
                            @foreach(explode(',', $options->values) as $value)
                              <option value="{{ trim($value) }}" {{ old($field_name) == trim($value) ? 'selected' : '' }}>
                                {{ trim($value) }}
                              </option>
                            @endforeach
                          </select>
                          @break

                        @case('textarea')
                          <textarea id="{{ $id_for }}" name="{{ $field_name }}" rows="{{ $options->rows }}"
                            class="form-control @error($field_name) is-invalid @enderror">{{ old($field_name) }}</textarea>
                          @break

                        @default
                          <input type="{{ trim($options->type) === 'date' ? 'text' : $options->type }}"
                            class="form-control {{ trim($options->type) === 'date' ? 'datepicker' : '' }} @error($field_name) is-invalid @enderror"
                            id="{{ $id_for }}" name="{{ $field_name }}" value="{{ old($field_name) }}" />
                      @endswitch

                      @error($field_name)
                      <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                    @endforeach
                  </div>

                  <div class="d-flex justify-content-start gap-2 mt-4">
                    @if ($_user->hasAnyRole('Registered'))
                    <button type="submit" class="btn btn-primary">
                      <i class="ti ti-send me-2 ti-sm"></i> {{ $l == 'ar' ? 'ارسال' : 'Submit' }}
                    </button>
                    @endif
                    <button type="button" class="btn btn-outline-secondary" onclick="location.href='/dashboard/show-builder/{{ $id }}'">
                      <i class="ti ti-edit me-2 ti-sm"></i> {{ $l == 'ar' ? 'تحرير' : 'Edit' }}
                    </button>
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

@section('scripts')
<script>
  // Initialize Date Picker
  flatpickr(".datepicker", {
    dateFormat: "Y-m-d", 
    allowInput: true, 
  });
</script>
@stop
