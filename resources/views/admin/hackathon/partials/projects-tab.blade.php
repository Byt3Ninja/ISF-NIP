
@if($data_evi != null)
  @if($user->hasRole('Admin') || $user->hasRole('organizationmanager'))
    <div class="text-center">
      <h5 class="alert alert-danger">
          {{ $l == 'en' ? 'You are Admin':  'انت مسئول' }}
      </h5>
    </div>
  @else

    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          {{ $l == 'en' ? 'Project Submission' : 'رفع المشاريع والحلول' }}
        </h5>
        <p>
          {{ $l == 'en' ? 'Please upload your team\'s project before the hackathon deadline. Make sure to include
          all required files and a clear presentation.' : 'يرجى رفع مشروع فريقك قبل انتهاء وقت الهاكثون. تأكد من
          تضمين جميع الملفات المطلوبة وعرض تقديمي واضح.' }}
        </p>

        @if ($hasSubmission)
          <div id="submission-status-message" class="alert alert-success d-flex align-items-center mb-4">
            <i class="ti ti-circle-check-filled me-2" style="font-size: 1.25rem;"></i>
            <div>
              {{ $l == 'en' ? 'Project submitted' : 'تم إرسال المشروع.' }}
            </div>
          </div>
        @elseif ($isOpen)
          <div id="submission-status-message" class="alert alert-info d-flex align-items-center mb-4">
            <i class="ti ti-info-circle-filled me-2" style="font-size: 1.25rem;"></i>
            <div>
              {{ $l == 'en' 
                  ? 'Submissions are open until ' . ($end ? $end->locale($l)->translatedFormat('F j, Y, g:i A') : '') . '.' 
                  : 'الإرسال متاح حتى ' . ($end ? $end->locale($l)->translatedFormat('F j, Y, g:i A') : '') . '. .' }}
            </div>
          </div>
        @else
          <div id="submission-status-message" class="alert alert-danger d-flex align-items-center mb-4">
            <i class="ti ti-circle-x-filled me-2" style="font-size: 1.25rem;"></i>
            <div>
              {{ $l == 'en' ? 'Submissions are closed.' : 'تم إغلاق استقبال المشاريع.' }}
            </div>
          </div>
        @endif

        @if($data_evi['stype'] == 5)
          @if(count($data_evi['fields'] ) > 0)
            @if ($isOpen || $hasSubmission)
              <form method="post" action="/dashboard/cycles/save" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <input type="hidden" name="cycle" value="{{ $data_evi['cycle_id'] }}" />
                <input type="hidden" name="id" value="{{ $data_evi['form_id'] }}" />
                <input type="hidden" name="field_ids" value="{{ $data_evi['field_ids']  }}" />

                @foreach($data_evi['fields'] as $field)
                @php
                $options = $field->pivot->options? json_decode($field->pivot->options) : null;
                $field_name = 'field_' . $field->pivot->id;
                $id_for = 'input-fld-'. $loop->iteration;
                $get_answer = $answers->where('field_id', $field->pivot->id)->first();
                $answer = $get_answer ? $get_answer->answer : null;
                @endphp

                <div class="mb-3">
                  @switch($field->field_type)
                  @case("select")
                  <div class="form-group mb-6">
                    @if($options->label)
                    <label for="{{ $id_for }}" class="form-label fw-bold">{{ $options->label }}</label>
                    @endif
                    <select class="form-select @error($field_name) is-invalid @enderror" id="{{ $id_for }}"
                      name='{{ $field_name }}' required {{ !$isOpen ? 'disabled' : '' }}>
                      <option value="">{{$l=='ar'?'اختر':'Choose'}}...</option>
                      @foreach(explode(",", $options->values) as $value)
                      <option value="{{ trim($value) }}" {{ old($field_name)==trim($value)? "selected" : "" }}
                        {{trim($value)==$answer?'selected':''}}>
                        {{ trim($value) }}
                      </option>
                      @endforeach
                    </select>
                    @error($field_name)
                    <div class="invalid-feedback">{{ $errors->first($field_name) }}</div>
                    @enderror
                  </div>
                  @break

                  @case("textarea")
                  <div class="form-group mb-6">
                    @if($options->label)
                    <label for="{{ $id_for }}" class="form-label fw-bold">{{ $options->label }}</label>
                    @endif
                    <textarea class="form-control @error($field_name) is-invalid @enderror" id="{{ $id_for }}"
                      name="{{ $field_name }}" rows="{{ $options->rows ?? 4 }}" {{ !$isOpen ? 'disabled' : '' }}>{{ old($field_name) }}{{$answer}}</textarea>
                    @error($field_name)
                    <div class="invalid-feedback">{{ $errors->first($field_name) }}</div>
                    @enderror
                  </div>
                  @break

                  @case("file")
                  <div class="form-group mb-6">
                    @if($options->label)
                    <label class="form-label fw-bold">{{ $options->label }}</label>
                    @endif
                    <div class="d-flex flex-column">
                      <input type="file" class="form-control @error($field_name) is-invalid @enderror" id="{{ $id_for }}"
                        name="{{ $field_name }}" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" {{ !$isOpen ? 'disabled' : '' }}>
                      @if($answer)
                      <div class="mt-4">
                        <a href="/{{ $answer }} " target="_blank" class="btn btn-sm btn-outline-primary">
                          <i class="ti ti-file me-2"></i> {{ __('admin.View File') }}
                        </a>
                      </div>
                      @endif
                    </div>
                    @error($field_name)
                    <div class="invalid-feedback">{{ $errors->first($field_name) }}</div>
                    @enderror
                  </div>
                  @break

                  @default
                  <div class="form-group mb-6">
                    @if($options->label)
                    <label for="{{$id_for}}" class="form-label fw-bold">{{ $options->label }}</label>
                    @endif
                    <input type="{{$options->type==" date"?"text":$options->type}}"
                    class="form-control {{ $options->type == "date"? "datepicker" : "" }} @error($field_name) is-invalid
                    @enderror"
                    id="{{$id_for}}"
                    name="{{ $field_name }}"
                    value="{{ !empty( $answer ) ? $answer : ( !empty( old($field_name) ) ? old($field_name) : '' ) }}"
                    {{ !$isOpen ? 'disabled' : '' }}
                    />
                    @error($field_name)
                    <div class="invalid-feedback">{{ $errors->first($field_name) }}</div>
                    @enderror
                  </div>
                  @endswitch
                </div>
                @endforeach

                @if ($isOpen)
                  <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                      {{ __('admin.submit') }}
                    </button>
                  </div>
                @endif
              </form>
            @endif
          @else
            <div class="text-center">
              <h5 class="text-muted">{{ __('cycles.No Fields') }}</h5>
            </div>
          @endif
        @else
          <div class="text-center">
            <h5 class="text-muted">{{ __('admin.You are not allowed to submit answers') }}</h5>
          </div>
        @endif
      </div>
    </div>
  @endif
@else
  <div class="text-center">
    <h5 class="alert alert-danger">
      @if($user->hasRole('Admin') || $user->hasRole('organizationmanager'))
      {{ $l == 'en' ? 'Add Final Evaluation Step First' : ' لا يمكنك تقديم المشاريع الان' }}
      @else
      {{ $l == 'en' ? 'Submissions are closed.' : 'تم إغلاق استقبال المشاريع.' }}
      @endif
    </h5>
  </div>
@endif
