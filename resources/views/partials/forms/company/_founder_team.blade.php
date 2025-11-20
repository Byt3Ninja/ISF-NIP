<!-- Founders Column -->
<div class="col-md-12 mb-3">
  <div class="card shadow-sm">
    <div class="p-4">
      <h5 class="card-title border-bottom pb-2 mb-6">{{ __('admin.Founders') }}</h5>
      <form 
        id="founder-form" 
        method="post" 
        {{-- action="{{ route('company.profile_teams_add') }}"  --}}
        enctype="multipart/form-data"
      >
        @csrf
        <div class="show_Founders row">
          <div class="col-12">
            <div class="form-group mb-4">
              <label 
                class="fs-6 fw-semibold mb-2 form-label" 
                for="founder_name"
              >
                {{ __('admin.founder_name') }}
              </label>
              <div class="input-group input-group-merge">
                <input 
                  type="text" 
                  class="form-control" 
                  id="founder_name" 
                  placeholder="{{ __('admin.founder_name') }}" 
                  value="{{ $client->company ? $client->company->founder_name : old('founder_name') }}" 
                  name="founder_name" 
                  autocomplete="founder_name" 
                  oninput="this.value = this.value.trimStart()"
                >
                <div class="input-group-text">
                  <i class="ti ti-user"></i>
                </div>
              </div>
              <div id="founder-error1" class="invalid-feedback">{{__('admin.founder_name_is_required')}}</div>
              <div id="founder-error2" class="invalid-feedback">{{__('admin.please_enter_valid_name')}}</div>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group mb-4">
              <label 
                class="fs-6 fw-semibold mb-2 form-label" 
                for="founder_email"
              >
                {{ __('admin.founder_email') }}
              </label>
              <div class="input-group input-group-merge">
                <input 
                  type="email" 
                  class="form-control" 
                  id="founder_email" 
                  placeholder="{{ __('admin.founder_email') }}" 
                  value="{{ $client->company ? $client->company->founder_email : old('founder_email') }}" 
                  name="founder_email" 
                  autocomplete="founder_email" 
                  oninput="this.value = this.value.trimStart()"
                >
                <div class="input-group-text">
                  <i class="ti ti-mail"></i>
                </div>
              </div>
              <div id="email-error" class="invalid-feedback">{{__('admin.please_enter_valid_email')}}</div>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group mb-4">
              <label 
                class="fs-6 fw-semibold mb-2 form-label" 
                for="founder_linkedin"
              >
                {{ __('admin.founder_linkedin') }}
              </label>
              <div class="input-group input-group-merge">
                <input 
                  type="url" 
                  class="form-control" 
                  id="founder_linkedin" 
                  value="{{ $client->company ? $client->company->founder_linkedin : old('founder_linkedin') }}" 
                  name="founder_linkedin" 
                  autocomplete="founder_linkedin" 
                  placeholder="e.g. https://www.linkedin.com/in/username" 
                  oninput="this.value = this.value.trimStart()"
                />
                <div class="input-group-text">
                  <i class="ti ti-brand-linkedin"></i>
                </div>
              </div>
              <div id="linkedin-error" class="invalid-feedback">{{__('admin.please_enter_valid_linkedin')}}</div>
            </div>
          </div>
        </div>
        <div class="col-12 text-end mt-3" style="height: 40px;">
          <input type="hidden" name="TeamsNo" value="Founders" />
          <button 
            type="button"  
            class="btn btn-outline-primary text-black fw-bold rounded-pill AddTeams" 
            do="Founders" 
            company="{{ optional($client->company)->id }}"
          >
            <i class="ti ti-plus me-3"></i>
            {{ $l == 'en' ? 'Add new founder' : 'إضافة مؤسس جديد' }}
          </button>
        </div>
      </form>
      @if($client->company)
      <div class="ShowTableFounders mt-4">
        @foreach ($fonders as $fonder)
        <div class="card shadow-sm mb-4 position-relative">
          <span 
            class="position-absolute cursor-pointer top-0 end-0 p-4" 
            onclick="togglePopup({{ $fonder->id }})"
          >
            <i class="ti ti-dots-vertical fs-5"></i>
          </span>
          <div class="p-3">
            <div class="d-flex align-items-center">
              <div 
                class="border d-flex radius-15 flex-shrink-0" 
                style="width: 80px; height: 80px;"
              >
                <img 
                  src="{{optional($client->company)->logo_url}}" 
                  class="img-fluid m-auto" 
                  alt="logo" 
                />
              </div>
              <div 
                class="mx-3 d-flex flex-column justify-content-center gap-1" 
                style="height: 76px;"
              >
                <div class="text-black fs-6">{{ $fonder->Name }}</div>
                <div class="d-flex align-items-center gap-2">
                  <i class="ti ti-mail"></i>
                  <span class="text-muted text-xs">{{ $fonder->Email }}</span>
                </div>
                @if ($fonder->LinkedIn)
                <div class="d-flex align-items-center gap-2">
                  <img src="/icons/linkedin.svg" width="18" height="18" />
                  <span class="text-muted text-xs">{{ $fonder->LinkedIn == null ? __('admin.No_LinkedIn') : $fonder->LinkedIn }}</span>
                </div>
                @endif
              </div>
              <div class="ms-auto position-relative">
                <div 
                  id="popup-{{ $fonder->id }}" 
                  class="popup-menu z-3 position-absolute bg-white shadow-sm p-2 rounded" 
                  style="display: none; {{$l == 'en' ? 'right: 0.5rem;' : 'left: 0.5rem;'}} top: -1rem;"
                >
                  <div 
                    class="d-flex align-items-center gap-2 p-2 cursor-pointer" 
                    data-bs-toggle="modal" 
                    data-bs-target="#EditTeams{{ $fonder->id }}"
                    id="edit-founder-link"
                  >
                    <i class="ti ti-pencil text-primary fs-6"></i>
                    <span style="font-size: 11px;" class="fw-bold">{{ __('admin.Edit') }}</span>
                  </div>
                  @if(Auth::user()->hasanyRole('Admin|organizationManager'))
                  <form 
                    action="{{ route('company.profile_teams_delete', $client->company->id) }}" 
                    method="POST" 
                    style="display: inline-block;"
                  >
                    @else
                    <form 
                      action="{{ route('company.profile_teams_delete') }}" 
                      method="POST" 
                      style="display: inline-block;"
                    >
                      @endif
                      @csrf
                      <input type="hidden" value="{{ $fonder->id }}" name="id">
                      <button 
                        class="d-flex align-items-center gap-2 p-2 cursor-pointer border-0 bg-transparent w-100 text-start" 
                        {{--  type="submit"   --}}
                        onclick="return confirm('Are you sure you want to delete this ?')"
                      >
                        <i class="ti ti-trash text-danger fs-6"></i>
                        <span style="font-size: 11px;" class="fw-bold">{{ __('admin.Delete') }}</span>
                      </button>
                    </form>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Customer Modal -->
        <div 
          class="modal fade" 
          id="EditTeams{{ $fonder->id }}" 
          tabindex="-1" 
          aria-hidden="true"
        >
          <div class="modal-dialog modal-md modal-simple modal-edit-user">
            <div class="modal-content">
              <div class="modal-body">
                <button 
                  type="button" 
                  class="btn-close" 
                  data-bs-dismiss="modal" 
                  aria-label="Close"
                ></button>
                <div class="mb-6">
                  <h4 class="mb-2"> ({{ $fonder->Name }})</h4>
                </div>
                <form 
                  id="edit-founder-form" 
                  class="ecommerce-customer-add-basic mb-4"
                >
                  @csrf
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label 
                        class="form-label mb-2 fs-6 fw-semibold"
                      >
                        {{ __('admin.founder_name') }}
                      </label>
                      <div class="input-group input-group-merge">
                        <input 
                          type="text" 
                          class="form-control founder_name" 
                          id="founder_name{{ $fonder->id }}" 
                          placeholder="{{ __('admin.founder_name') }}" 
                          value="{{ $fonder->Name ? $fonder->Name : old('founder_name') }}" 
                          name="founder_name" 
                          autocomplete="founder_name" 
                          oninput="this.value = this.value.trimStart()"
                        >
                        <div class="input-group-text">
                          <i class="ti ti-user"></i>
                        </div>
                      </div>
                      <div id="founder-error1" class="invalid-feedback">{{__('admin.founder_name_is_required')}}</div>
                      <div id="founder-error2" class="invalid-feedback">{{__('admin.please_enter_valid_name')}}</div>
                    </div>
                    <div class="form-group mb-4">
                      <label 
                        class="form-label mb-2 fs-6 fw-semibold"
                      >
                        {{ __('admin.founder_email') }}
                      </label>
                      <div class="input-group input-group-merge">
                        <input 
                          type="email" 
                          class="form-control founder_email" 
                          id="founder_email{{ $fonder->id }}" 
                          placeholder="{{ __('admin.founder_email') }}" 
                          value="{{ $fonder->Email ? $fonder->Email : old('founder_email') }}" 
                          name="founder_email" 
                          autocomplete="founder_email" 
                          oninput="this.value = this.value.trimStart()"
                        >
                        <div class="input-group-text">
                          <i class="ti ti-mail"></i>
                        </div>
                      </div>
                      <div id="email-error" class="invalid-feedback">{{__('admin.please_enter_valid_email')}}</div>
                    </div>
                    <div class="form-group mb-4">
                      <label 
                        class="form-label mb-2 fs-6 fw-semibold"
                      >
                        {{ __('admin.founder_linkedin') }}
                      </label>
                      <div class="position-relative">
                        <div class="input-group input-group-merge">
                          <input 
                            type="url" 
                            class="form-control founder_linkedin" 
                            id="founder_linkedin{{ $fonder->id }}" 
                            value="{{ $fonder->LinkedIn ? $fonder->LinkedIn : old('founder_linkedin') }}" 
                            name="founder_linkedin" 
                            autocomplete="founder_linkedin" 
                            placeholder="e.g. https://www.linkedin.com/in/username"
                            oninput="this.value = this.value.trimStart()"
                          />
                          <div class="input-group-text">
                            <i class="ti ti-brand-linkedin"></i>
                          </div>
                        </div>
                        <div id="linkedin-error" class="invalid-feedback">{{__('admin.please_enter_valid_linkedin')}}</div>
                      </div>
                    </div>
                  </div>
                  <div class="mt-6">
                    <button 
                      {{-- type="submit" --}}
                      class="btn btn-primary me-3 data-submit EditTeams" 
                      do="{{ $fonder->id }}" 
                      data-id="Founders"
                      company="{{ optional($client->company)->id }}"
                    >
                      {{ __('admin.submit') }}
                    </button>
                    <button 
                      type="reset" 
                      class="btn btn-label-secondary" 
                      data-bs-dismiss="modal"
                    >
                      {{ __('admin.Cancel') }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!--/ Edit JO Modal -->
        @endforeach
      </div>
      @endif
    </div>
  </div>
</div>
