<!-- Investors Column -->
<div class="col-md-12 mb-3">
  <div class="card shadow-sm">
    <div class="p-4">
      <form id="investor-form">
        @csrf
        <h5 class="card-title border-bottom pb-2 mb-6">{{ __('admin.investors') }}</h5>
        <div class="form-group mb-4">
          <label class="form-label fw-semibole mb-2 fs-6" for="investor_name">{{ __('admin.investor_name') }}</label>
          <div class="input-group input-group-merge">
            <input
              type="text"
              class="form-control investor_name"
              id="investor_name"
              placeholder="{{ __('admin.investor_name') }}"
              value="{{ $client->company ? $client->company->investor_name : old('investor_name') }}"
              name="investor_name"
              autocomplete="investor_name"
              required
              oninput="this.value = this.value.trimStart()"
            />
            <div class="input-group-text">
              <i class="ti ti-user"></i>
            </div>
          </div>
          <div id="investor-error1" class="invalid-feedback">
            {{ __('admin.investor_name_required') }}
          </div>
          <div id="investor-error2" class="invalid-feedback">
            {{ __('admin.please_enter_valid_name') }}
          </div>
        </div>
        <div class="form-group mb-4">
          <label class="form-label fw-semibole mb-2 fs-6" for="investment_type">{{ __('admin.investment_type') }}</label>
          <div class="input-group input-group-merge">
            <select
              class="form-select form-control investment_type"
              id="investment_type"
              name="investment_type"
              aria-placeholder="{{ __('admin.investment_type') }}"
              autocomplete="investment_type"
              required
            >
              <option value="" selected disabled>
                {{ __('admin.investment_type') }}
              </option>
              <option value="Angel_Investor">{{ __('admin.Angel_Investor') }}</option>
              <option value="Angel_Group">{{ __('admin.Angel_Group') }}</option>
              <option value="Venture_Capital_Firm">{{ __('admin.Venture_Capital_Firm') }}</option>
              <option value="Accelerator">{{ __('admin.Accelerator') }}</option>
              <option value="Incubator">{{ __('admin.Incubator') }}</option>
              <option value="Corporate_Investor">{{ __('admin.Corporate_Investor') }}</option>
              <option value="Family_Office">{{ __('admin.Family_Office') }}</option>
              <option value="Family">{{ __('admin.Family') }}</option>
              <option value="Friends">{{ __('admin.Friends') }}</option>
              <!-- Add more options as needed -->
            </select>
            <div class="input-group-text">
              <i class="ti ti-world"></i>
            </div>
          </div>
          <div id="investment-error" class="invalid-feedback">
            {{ __('admin.investment_type_required') }}
          </div>
        </div>
        <div class="col-12 text-end mt-3" style="height: 40px;">
          <input type="hidden" name="TeamsNo" value="Investors" />
          <button
             type="button" 
            class="btn btn-outline-primary text-black fw-bold rounded-pill AddTeams"
            do="Investors"
            company="{{ optional($client->company)->id }}"
          >
            <i class="ti ti-plus me-3"></i>
            {{ $l == 'en' ? 'Add new investor' : 'إضافة مستثمر جديد' }}
          </button>
        </div>
      </form>
      
      @if($client->company && $client->company->investors)
      <div class="ShowTableInvestors">
        <div class="row px-4 mt-4">
          @foreach ($Investors as $Investor)
          <div class="col-md-6">
            <div class="card shadow-sm mb-4 position-relative">
              <span class="position-absolute cursor-pointer top-0 end-0 p-4" onclick="togglePopup({{ $Investor->id }})">
                <i class="ti ti-dots-vertical fs-5"></i>
              </span>
              <div class="p-3">
                <div class="d-flex align-items-center">
                  <div class="border d-flex rounded d-flex flex-shrink-0" style="width: 80px; height: 80px;">
                    <img src="{{optional($client->company)->logo_url}}" class="img-fluid m-auto" alt="logo" />
                  </div>
                  <div class="mx-3 d-flex flex-column justify-content-center gap-1" style="height: 76px;">
                    <div class="text-black fs-6">{{ $Investor->Name }}</div>
                    <div class="d-flex align-items-center gap-2">
                      <i class="ti ti-drone text-info"></i>
                      <button class="btn btn-outline-primary btn-xs rounded-pill fw-bold" style="height: 18px;">
                        {{ $Investor->investorType }}
                      </button>
                    </div>
                  </div>
                  <div class="ms-auto position-relative">
                    <div
                      id="popup-{{ $Investor->id }}"
                      class="popup-menu z-3 position-absolute bg-white shadow-sm p-2 rounded"
                      style="display: none; {{$l == 'en' ? 'right: 0.5rem;' : 'left: 0.5rem;'}} top: -1rem;"
                    >
                      <div
                        class="d-flex align-items-center gap-2 p-2 cursor-pointer"
                        data-bs-toggle="modal"
                        data-bs-target="#EditTeams{{ $Investor->id }}"
                      >
                        <i class="ti ti-pencil text-primary fs-6"></i>
                        <span style="font-size: 11px;" class="fw-bold">{{ __('admin.Edit') }}</span>
                      </div>
                      @if(Auth::user()->hasanyRole('Admin|organizationManager'))
                      <form action="{{ route('company.profile_teams_delete', $client->company->id) }}" method="POST" style="display: inline-block;">
                        @else
                        <form action="{{ route('company.profile_teams_delete') }}" method="POST" style="display: inline-block;">
                          @endif
                          @csrf
                          <input type="hidden" value="{{ $Investor->id }}" name="id">
                          <button
                            class="d-flex align-items-center gap-2 p-2 cursor-pointer border-0 bg-transparent w-100 text-start"
                            {{--  type="submit"  --}}
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
          </div>

          <!-- Edit Investor Modal -->
          <div class="modal fade" id="EditTeams{{ $Investor->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md modal-simple modal-edit-user">
              <div class="modal-content">
                <form id="edit-investor-form" class="modal-body">
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  <div class="text-center mb-6">
                    <h4 class="mb-2"> ({{ $Investor->Name }})</h4>
                  </div>
                  <div class="row mb-4">
                    <div class="col-12">
                      <div class="form-group mb-4">
                        <label class="form-label mb-2 fs-6 fw-semibold">
                          {{ __('admin.investor_name') }}
                        </label>
                        <div class="input-group input-group-merge">
                          <input
                            type="text"
                            class="form-control investor_name"
                            id="investor_name{{ $Investor->id }}"
                            placeholder="{{ __('admin.investor_name') }}"
                            value="{{ $Investor->Name ? $Investor->Name : old('investor_name') }}"
                            name="investor_name"
                            autocomplete="investor_name"
                            required
                            oninput="this.value = this.value.trimStart()"
                          />
                          <div class="input-group-text">
                            <i class="ti ti-user"></i>
                          </div>
                        </div>
                        <div id="investor-error1" class="invalid-feedback">
                          {{ __('admin.investor_name_required') }}
                        </div>
                        <div id="investor-error2" class="invalid-feedback">
                          {{ __('admin.please_enter_valid_name') }}
                        </div>
                      </div>
                      <div class="form-group mb-4">
                        <label class="form-label mb-2 fs-6 fw-semibold">
                          {{ __('admin.investment_type') }}
                        </label>
                        <div class="input-group input-group-merge">
                          <select
                            class="form-select form-control investment_type"
                            id="investment_type{{ $Investor->id }}"
                            name="investment_type"
                            aria-placeholder="{{ __('admin.investment_type') }}"
                            autocomplete="investment_type"
                            required
                          >
                            <option value="{{ __('admin.investment_type') }}" selected disabled>
                              {{ __('admin.investment_type') }}
                            </option>
                            <option value="Angel_Investor" {{ $Investor->investorType == 'Angel_Investor' ? 'selected' : '' }}>
                              {{ __('admin.Angel_Investor') }}
                            </option>
                            <option value="Angel_Group" {{ $Investor->investorType == 'Angel_Group' ? 'selected' : '' }}>
                              {{ __('admin.Angel_Group') }}
                            </option>
                            <option value="Venture_Capital_Firm" {{ $Investor->investorType == 'Venture_Capital_Firm' ? 'selected' : '' }}>
                              {{ __('admin.Venture_Capital_Firm') }}
                            </option>
                            <option value="Accelerator" {{ $Investor->investorType == 'Accelerator' ? 'selected' : '' }}>
                              {{ __('admin.Accelerator') }}
                            </option>
                            <option value="Incubator" {{ $Investor->investorType == 'Incubator' ? 'selected' : '' }}>
                              {{ __('admin.Incubator') }}
                            </option>
                            <option value="Corporate_Investor" {{ $Investor->investorType == 'Corporate_Investor' ? 'selected' : '' }}>
                              {{ __('admin.Corporate_Investor') }}
                            </option>
                            <option value="Family_Office" {{ $Investor->investorType == 'Family_Office' ? 'selected' : '' }}>
                              {{ __('admin.Family_Office') }}
                            </option>
                            <option value="Family" {{ $Investor->investorType == 'Family' ? 'selected' : '' }}>
                              {{ __('admin.Family') }}
                            </option>
                            <option value="Friends" {{ $Investor->investorType == 'Friends' ? 'selected' : '' }}>
                              {{ __('admin.Friends') }}
                            </option>
                            <!-- Add more options as needed -->
                          </select>
                          <div class="input-group-text">
                            <i class="ti ti-world"></i>
                          </div>
                        </div>
                        <div id="investment-error" class="invalid-feedback">
                          {{ __('admin.investment_type_required') }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="mt-6">
                    <button 
                      type="button"
                      class="btn btn-primary me-3 data-submit EditTeams" 
                      do="{{ $Investor->id }}"
                      data-id="Investors"
                      company="{{ optional($client->company)->id }}"
                    >
                      {{ __('admin.submit') }}
                    </button>
                    <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">
                      {{ __('admin.Cancel') }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!--/ Edit JO Modal -->
          @endforeach
        </div>
      </div>
      @endif
    </div>
  </div>
</div>