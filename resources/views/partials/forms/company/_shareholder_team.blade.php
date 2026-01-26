<!-- Shareholders Column -->
<div class="col-12 mb-3">
  <div class="card shadow-sm">
    <div class="p-4">
      <h5 class="card-title border-bottom pb-2 mb-6">{{ __('admin.shareholders') }}</h5>
      <form
        id="shareholder-form"
        method="post"
        action="{{ route('company.profile_teams_add') }}"
        enctype="multipart/form-data"
        onsubmit="resetForm(this); return validateForm();"
      >
        @csrf
        <div class="show_Shareholders row">
          <div class="col-12">
            <div class="form-group mb-4">
              <label class="fs-6 form-label mb-2 fw-semibold" for="shareholder_name">{{ __('admin.shareholder_name') }}</label>
              <div class="input-group input-group-merge">
                <input
                  type="text"
                  class="form-control shareholder_name"
                  id="shareholder_name"
                  placeholder="{{ __('admin.shareholder_name') }}"
                  value="{{ old('shareholder_name') }}"
                  name="shareholder_name"
                  autocomplete="shareholder_name"
                  oninput="this.value = this.value.trimStart()"
                />
                <div class="input-group-text">
                  <i class="ti ti-user"></i>
                </div>
              </div>
              <div id="shareholder-error1" class="invalid-feedback">{{ __('admin.shareholder_name_required') }}</div>
              <div id="shareholder-error2" class="invalid-feedback">{{ __('admin.please_enter_valid_name') }}</div>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group mb-4">
              <label class="fs-6 form-label mb-2 fw-semibold" for="shareholder_role">{{ __('admin.shareholder_role') }}</label>
              <div class="input-group input-group-merge">
                <input
                  type="text"
                  class="form-control"
                  id="shareholder_role"
                  placeholder="{{ __('admin.shareholder_role') }}"
                  value="{{ old('shareholder_role') }}"
                  name="shareholder_role"
                  autocomplete="shareholder_role"
                  oninput="this.value = this.value.trimStart()"
                />
                <div class="input-group-text">
                  <i class="ti ti-briefcase"></i>
                </div>
              </div>
              <div id="role-error" class="invalid-feedback">{{ __('admin.please_enter_valid_role') }}</div>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group mb-4">
              <label class="fs-6 form-label mb-2 fw-semibold" for="shareholder_ownership_percentage">{{ __('admin.shareholder_ownership_percentage') }}</label>
              <div class="input-group input-group-merge">
                <input
                  type="text"
                  class="form-control shareholder_ownership_percentage"
                  id="shareholder_ownership_percentage"
                  placeholder="{{ __('admin.shareholder_ownership_percentage') }}"
                  value="{{ old('shareholder_ownership_percentage') }}"
                  name="shareholder_ownership_percentage"
                  autocomplete="shareholder_ownership_percentage"
                  oninput="this.value = this.value.trimStart()"
                />
                <div class="input-group-text">
                  <i class="ti ti-percentage"></i>
                </div>
              </div>
              <div id="ownership-error" class="invalid-feedback">
                {{ __('admin.percentage_must_be_between_1_and_100') }}
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 text-end mt-3" style="height: 40px;">
          <input type="hidden" name="TeamsNo" value="Shareholders" />
          <button
             type="button" 
            class="btn btn-outline-primary text-black fw-bold rounded-pill AddTeams"
            do="Shareholders"
            company="{{ optional($client->company)->id }}"
          >
            <i class="ti ti-plus me-3"></i>
            {{ $l == 'en' ? 'Add new shareholder' : 'إضافة مساهم جديد' }}
          </button>
        </div>
      </form>
      @if($client->company && $client->company->shareholders)
      <div class="ShowTableShareholders mt-4">
        <div class="row">
          @foreach ($Shareholders as $Shareholder)
          <div class="col-md-6">
            <div class="card shadow-sm mb-4 position-relative">
              <span class="position-absolute cursor-pointer top-0 end-0 p-4" onclick="togglePopup({{ $Shareholder->id }})">
                <i class="ti ti-dots-vertical fs-5"></i>
              </span>
              <div class="p-3">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="mx-3 d-flex flex-column justify-content-center gap-1" style="height: 76px;">
                    <div class="text-black fs-6">{{ $Shareholder->Name }}</div>
                    <button class="btn btn-outline-primary btn-xs rounded-pill mt-1 fw-bold" style="height: 22px; width: fit-content">
                      {{ $Shareholder->RoleShereholder }}
                    </button>
                  </div>
                  <div class="mx-3 d-flex align-items-center gap-3" style="height: 76px;">
                    <i class="ti ti-chevrons-right ti-28px;"></i>
                    <!-- Circular Progress -->
                    <div
                      class="progress-circle p-3 position-relative d-flex align-items-center justify-content-center"
                      style="width: 54px; height: 54px;"
                    >
                      <span class="position-absolute text-black fw-bold" style="font-size: 13px;">{{ $Shareholder->PresentagShereholder }}%</span>
                      <svg class="position-absolute" width="54" height="54" viewBox="0 0 36 36">
                        <path
                          class="circle-bg"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none"
                          stroke="#e6e6e6"
                          stroke-width="2.5"
                        />
                        <path
                          class="circle"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none"
                          stroke="#007bff"
                          stroke-width="2.5"
                          stroke-dasharray="{{ $Shareholder->PresentagShereholder }}, 100"
                        />
                      </svg>
                    </div>
                    <!-- Circular Progress -->
                  </div>
                  <div class="ms-auto position-relative">
                    <div
                      id="popup-{{ $Shareholder->id }}"
                      class="popup-menu z-3 position-absolute bg-white shadow-sm p-2 rounded"
                      style="display: none; {{$l == 'en' ? 'right: 0.5rem;' : 'left: 0.5rem;'}} top: -1rem;"
                    >
                      <div
                        class="d-flex align-items-center gap-2 p-2 cursor-pointer"
                        data-bs-toggle="modal"
                        data-bs-target="#EditTeams{{ $Shareholder->id }}"
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
                          <input type="hidden" value="{{ $Shareholder->id }}" name="id">
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

            <!-- Edit shareholder Modal -->
            <div class="modal fade" id="EditTeams{{ $Shareholder->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-md modal-simple modal-edit-user">
                <div class="modal-content">
                  <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-6">
                      <h4 class="mb-2"> ({{ $Shareholder->Name }})</h4>
                    </div>
                    <form id="edit-shareholder-form" class="ecommerce-customer-add-basic mb-4">
                      <div class="col-12">
                        <div class=" form-group mb-4">
                          <label class="form-label mb-2 fs-6 fw-semibold">
                            {{ __('admin.shareholder_name') }}
                          </label>
                          <div class="input-group input-group-merge">
                            <input
                              type="text"
                              class="form-control shareholder_name"
                              id="shareholder_name{{ $Shareholder->id }}"
                              placeholder="{{ __('admin.shareholder_name') }}"
                              value="{{ $Shareholder->Name ? $Shareholder->Name : old('shareholder_name') }}"
                              name="shareholder_name"
                              autocomplete="shareholder_name"
                              oninput="this.value = this.value.trimStart()"
                            />
                            <div class="input-group-text">
                              <i class="ti ti-user"></i>
                            </div>
                          </div>
                          <div id="shareholder-error1" class="invalid-feedback">
                            {{ __('admin.shareholder_name_required') }}
                          </div>
                          <div id="shareholder-error2" class="invalid-feedback">
                            {{ __('admin.please_enter_valid_name') }}
                          </div>
                        </div>
                        <div class=" form-group mb-4">
                          <label class="form-label mb-2 fs-6 fw-semibold">
                            {{ __('admin.shareholder_role') }}
                          </label>
                          <div class="input-group input-group-merge">
                            <input
                              type="text"
                              class="form-control shareholder_role"
                              id="shareholder_role{{ $Shareholder->id }}"
                              placeholder="{{ __('admin.shareholder_role') }}"
                              value="{{ $Shareholder->RoleShereholder ? $Shareholder->RoleShereholder : old('shareholder_role') }}"
                              name="shareholder_role"
                              autocomplete="shareholder_role"
                              oninput="this.value = this.value.trimStart()"
                            />
                            <div class="input-group-text">
                              <i class="ti ti-briefcase"></i>
                            </div>
                          </div>
                          <div id="role-error" class="invalid-feedback">
                            {{ __('admin.please_enter_valid_role') }}
                          </div>
                        </div>
                        <div class=" form-group mb-4">
                          <label class="form-label mb-2 fs-6 fw-semibold">
                            {{ __('admin.shareholder_ownership_percentage') }}
                          </label>
                          <div class="input-group input-group-merge">
                            <input
                              type="text"
                              class="form-control shareholder_ownership_percentage"
                              id="shareholder_ownership_percentage{{ $Shareholder->id }}"
                              placeholder="{{ __('admin.shareholder_ownership_percentage') }}"
                              value="{{ $Shareholder->PresentagShereholder ? $Shareholder->PresentagShereholder : old('shareholder_ownership_percentage') }}"
                              name="shareholder_ownership_percentage"
                              autocomplete="shareholder_ownership_percentage"
                              oninput="this.value = this.value.trimStart()"
                            />
                            <div class="input-group-text">
                              <i class="ti ti-percentage"></i>
                            </div>
                          </div>
                          <div id="ownership-error" class="invalid-feedback">
                            {{ __('admin.percentage_must_be_between_1_and_100') }}
                          </div>
                        </div>
                      </div>
                      <div class="mt-6">
                        <button
                          type="button" 
                          class="btn btn-primary me-3 data-submit EditTeams"
                          do="{{ $Shareholder->id }}"
                          data-id="Shareholders"
                          company="{{ optional($client->company)->id }}"
                        >
                          {{ __('admin.submit') }}
                        </button>
                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <!--/ Edit Shareholder Modal -->
          </div>
          @endforeach
        </div>
      </div>
      @endif
    </div>
  </div>
</div>