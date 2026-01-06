
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
        @if($data_evi['start']?->format('Y-m-d H:i:s') < date('Y-m-d H:i:s') && $data_evi['end']?->format('Y-m-d H:i:s') > date('Y-m-d H:i:s'))
          <h5 class="card-title">
            {{ $l == 'en' ? 'Project Submission' : 'رفع المشاريع والحلول' }}
          </h5>
          <p>
            {{ $l == 'en' ? 'Please upload your team\'s project before the hackathon deadline. Make sure to include
            all required files and a clear presentation.' : 'يرجى رفع مشروع فريقك قبل انتهاء وقت الهاكثون. تأكد من
            تضمين جميع الملفات المطلوبة وعرض تقديمي واضح.' }}
          </p>
          <div id="submission-status-message" class="alert alert-info">
            {{ $l == 'en' ? 'You can upload your project now. Deadline: '. $data_evi['end']->format('F j, Y,') .'.'
            : 'يمكنك رفع مشروعك الآن. الموعد النهائي: '. $data_evi['end']->format('F j, Y,') .'.' }}
          </div>
          {{-- <div id="already-submitted-message" class="alert alert-success" style="display:none;">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ $l == 'en' ? 'Your project has been uploaded successfully! You can update it until the deadline.' :
            'تم رفع مشروعكم بنجاح! يمكنكم تحديثه حتى الموعد النهائي.' }}
            <p class="mt-2">
              <strong>{{ $l == 'en' ? 'Uploaded Project:' : 'المشروع المرفوع:' }}</strong>
              MyAwesomeSolution.zip (2.5MB) -
              {{ $l == 'en' ? 'Upload Date: March 16, 2024, 05:30 PM' : 'تاريخ الرفع: 16 مارس 2024, 05:30 مساءً' }}
            </p>
          </div> --}}
          @if($data_evi['stype'] == 5)
            @if(count($data_evi['fields'] ) > 0)
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
                      name='{{ $field_name }}' required>
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
                      name="{{ $field_name }}" rows="{{ $options->rows ?? 4 }}">{{ old($field_name) }}{{$answer}}</textarea>
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
                        name="{{ $field_name }}" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
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
                    />
                    @error($field_name)
                    <div class="invalid-feedback">{{ $errors->first($field_name) }}</div>
                    @enderror
                  </div>
                  @endswitch
                </div>
                @endforeach

                <div class="d-flex justify-content-end mt-4">
                  <button type="submit" class="btn btn-primary">
                    {{ __('admin.submit') }}
                  </button>
                </div>
              </form>
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

          {{-- <form id="projectSubmissionForm" class="mt-3">
            <div class="mb-3">
              <label for="projectName" class="form-label">
                {{ $l == 'en' ? 'Project Name' : 'اسم المشروع' }}
                <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="projectName" required>
            </div>
            <div class="mb-3">
              <label for="projectDescription" class="form-label">
                {{ $l == 'en' ? 'Brief Project Description' : 'وصف مختصر للمشروع' }}
                <span class="text-danger">*</span>
              </label>
              <textarea class="form-control" id="projectDescription" rows="3" required></textarea>
            </div>
            <div class="mb-3">
              <label for="projectFiles" class="form-label">
                {{ $l == 'en' ? 'Project Files (single compressed file: zip, rar)' : 'ملفات المشروع (ملف مضغوط واحد:
                zip, rar)' }}
                <span class="text-danger">*</span>
              </label>
              <input class="form-control" type="file" id="projectFiles" accept=".zip,.rar" required>
              <small class="form-text text-muted">
                {{ $l == 'en' ? 'Maximum size: 25MB' : 'الحد الأقصى للحجم: 25MB' }}
              </small>
            </div>
            <div class="mb-3">
              <label for="projectLink" class="form-label">
                {{ $l == 'en' ? 'Presentation Link (Google Slides, YouTube, etc.) (Optional)' : 'رابط العرض التقديمي
                (Google Slides, YouTube, etc.) (اختياري)' }}
              </label>
              <input type="url" class="form-control" id="projectLink" placeholder="https://">
            </div>
            <div class="mb-3">
              <label for="projectRepo" class="form-label">
                {{ $l == 'en' ? 'Code Repository Link (GitHub, GitLab, etc.) (Optional)' : 'رابط مستودع الكود
                (GitHub, GitLab, etc.) (اختياري)' }}
              </label>
              <input type="url" class="form-control" id="projectRepo" placeholder="https://">
            </div>
            <div class="progress mb-3" style="display:none;" id="uploadProgressBarContainer">
              <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%
              </div>
            </div>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-cloud-upload-fill me-2"></i>
              {{ $l == 'en' ? 'Upload Project' : 'رفع المشروع' }}
            </button>
            <button type="button" class="btn btn-outline-secondary ms-2" onclick="saveDraft()">
              {{ $l == 'en' ? 'Save as Draft' : 'حفظ كمسودة' }}
            </button>
          </form> --}}
          @else
          <div class="text-center">
            <h5 class="alert alert-danger">
              {{ $l == 'en' ? 'You can not submit projects now' : ' لا يمكنك تقديم المشاريع الان' }},
              {{ $l == 'en' ? 'will be opening on '. $data_evi['start']?->format('F j, Y') : 'سوف يتم فتحها' .
              $data_evi['start']?->format('F j, Y') }}
            </h5>
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
      {{ $l == 'en' ? 'You can not submit projects now' : ' لا يمكنك تقديم المشاريع الان' }}
      @endif
    </h5>
  </div>
@endif
