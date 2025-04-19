<!-- Send Message Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-simple">
    <div class="modal-content p-0">
      <div class="modal-header py-3 justify-content-between">
        <h5 class="modal-title text-body fs-5">{{ __('admin.Send_Message') }}</h5>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" data-bs-dismiss="modal"
            aria-label="Close">
            <i class="ti ti-x"></i>
          </button>
        </div>
      </div>
      <div class="modal-body flex-grow-1 pb-sm-0 p-5 py-2">
        <form id="send-message-form" class="email-compose-form" method="post" action="/dashboard/sendmessage" enctype="multipart/form-data">
          {!! csrf_field() !!}
          <input type="hidden" name="draftsNumber" id="draftsNumber" class="draftsNumber" value="0">
          <div class="form-group mb-4">
            <label class="form-label">{{ __('messages.SelectUsers') }}:</label>
            <select id="select-type" class="form-select typedraft" name="type">
              <option value="0">{{ __('messages.PortalManagers') }}</option>
              <option value="1">{{ __('messages.CycleUsers') }}</option>
              <option value="2">{{ __('messages.Mentors') }}</option>
              @if (Auth::user()->hasAnyRole('Admin|OrganizationManager') && $cycles->count())
                <option value="3">{{ __('admin.Organizations') }}</option>
              @endif
            </select>
          </div>

          <div class="mb-4" style="display: none;" id="users">
            @if (Auth::user()->hasAnyRole('Admin|OrganizationManager') && $cycles->count())
              <div class="mb-4" id="cycle-selection">
                <label class="form-label">{{ __('admin.SelectProgram') }}:</label>
                <select id="select-programs" class="form-control select2 cycle_id" name="cycle_id" onchange="filterUsersByCycle(this.value)">
                  @foreach ($cycles as $cycle)
                    <option value="{{ $cycle->id }}">{{ $cycle->title }}</option>
                  @endforeach
                </select>
              </div>
            @endif
            @if ($users)
              <label class="form-label"
                for="users">{{ $l == 'ar' ? 'اختر الشركات' : 'Select companies' }}:</label>
              <select id="select-companies" class="select2 form-select usersdraft" name="users[]"
                data-placeholder="{{ $l == 'ar' ? 'اختر الشركات' : 'Select companies' }}" multiple>
                @foreach ($users as $index => $user)
                  @if ($user->user)
                    <option value="{{ $user->user->id }}" {{ $index === 0 ? 'selected' : '' }}>
                      {{ $user->user->{'first_name_' . $l} . ' ' . $user->user->{'last_name_' . $l} . ' - (' . $user->{'name_' . $l} . ')' }}
                    </option>
                  @endif
                @endforeach
              </select>
            @endif
          </div>

          <div class="mb-4" style="display: none;" id="mentors">
            @if ($mentors)
              <label class="form-label"
                for="mentors">{{ $l == 'ar' ? 'اختر المدربين' : 'Select mentors' }}:</label>
              <select id="select-mentors" class="select2 form-select mentorsdraft" name="mentors[]"
                data-placeholder="{{ $l == 'ar' ? 'اختر المدربين' : 'Select mentors' }}" multiple>
                @foreach ($mentors as $index => $mentor)
                  <option value="{{ $mentor->id }}" {{ $index === 0 ? 'selected' : '' }}>
                    {{ $mentor->{'first_name_' . $l} . ' ' . $mentor->{'last_name_' . $l} }}
                  </option>
                @endforeach
              </select>
            @endif
          </div>

          @if (Auth::user()->hasAnyRole('Admin|OrganizationManager'))
            <div class="mb-4" style="display: none;" id="organizations">
              <label class="form-label">{{ __('admin.Select Organizations') }}:</label>
              <select id="select-organizations" class="select2 form-select organizationsdraft" name="organizations[]" multiple
                data-placeholder="{{ __('admin.Select Organizations') }}">
                @foreach ($organizations as $index => $organization)
                  <option value="{{ $organization->id }}" {{ $index === 0 ? 'selected' : '' }}>
                    {{ $organization->{'name_' . app()->getLocale()} }}
                  </option>
                @endforeach
              </select>
            </div>
          @endif

          <div class="mb-4">
            <label class="form-label">{{ __('messages.Subject') }}:</label>
            <input 
              id="send-message-subject"
              name="subject" 
              type="text" 
              placeholder="{{ __('messages.Subject') }}" 
              class="form-control SubjectDraft" 
            />
            <div class="invalid-feedback subject-error">
              {{ __('messages.Subject is required') }}
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">{{ __('messages.yourMessage') }}:</label>
            <input type="hidden" name="message" id="send-message-text">
            <div class="d-flex justify-content-start">
              <div class="send-message-toolbar border-0 w-100 ps-0 pb-4">
                <span class="ql-formats me-0">
                  <button class="ql-bold"></button>
                  <button class="ql-italic"></button>
                  <button class="ql-underline"></button>
                  <button class="ql-list" value="ordered"></button>
                  <button class="ql-list" value="bullet"></button>
                  <button class="ql-link"></button>
                  <button class="ql-image"></button>
                </span>
              </div>
            </div>
            <div class="send-message-editor rounded overflow-hidden"></div>
            <div class="invalid-feedback message-error">
              {{ __('messages.Message is required') }}
            </div>
          </div>

          <div class="email-compose-actions d-flex justify-content-between align-items-center my-4">
            <div class="d-flex align-items-center gap-3">
              <button type="submit" class="btn btn-primary">
                {{ __('messages.Send') }} <i class="ti ti-send ti-xs scaleX-n1-rtl ms-2"></i>
              </button>
              <button 
                type="button" 
                id="save-draft-button"
                class="btn btn-label-secondary"
                onclick="saveMessageAsDraft()"
              >
                {{ __('messages.Save as draft') }} 
                <i class="ti ti-edit ti-xs scaleX-n1-rtl ms-2"></i>
              </button>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button type="reset" class="btn btn-sm btn-icon btn-text-secondary rounded-pill DeleteDraft" data-bs-dismiss="modal"
                aria-label="Close">
                <i class="ti ti-trash"></i>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- /Send Message Modal -->
