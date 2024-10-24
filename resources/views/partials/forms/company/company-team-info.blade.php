<style>
  .cursor-pointer {
    cursor: pointer;
  }
</style>

<div class="row TapPageTeam">
  <!-- Founders Column -->
  <div class="col-md-12 mb-3 px-10">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title">{{ __('admin.Founders') }}</h5>
        {{-- <form method="post" action="{{ route('AddTeams_Comp') }}" enctype="multipart/form-data" onsubmit="resetForm(this)">
          {!! csrf_field() !!} --}}
        <div class="show_Founders row">
          <div class="col-12">
            <div class="form-group position-relative">
              <input type="text"
                class="border-light form-control mt-2 founder_name light-shadow px-10"
                id="founder_name" placeholder="{{ __('admin.founder_name') }}"
                value="{{ $client->company ? $client->company->founder_name : old('founder_name') }}"
                name="founder_name" autocomplete="founder_name" required>
                 <i class="ti ti-user position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>

            </div>
          </div>
          <div class="col-12">
            <div class="form-group position-relative">
              <input type="text"
                class="border-light form-control mt-2 founder_email light-shadow px-10"
                id="founder_email" placeholder="{{ __('admin.founder_email') }}"
                value="{{ $client->company ? $client->company->founder_email : old('founder_email') }}"
                name="founder_email" autocomplete="founder_email" required>
                 <i class="ti ti-mail position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group position-relative">
              <input type="text"
                class="border-light form-control my-2 founder_linkedin light-shadow px-10"
                id="founder_linkedin"
                placeholder="{{ __('admin.founder_linkedin') }}"
                value="{{ $client->company ? $client->company->founder_linkedin : old('founder_linkedin') }}"
                name="founder_linkedin" autocomplete="founder_linkedin"
                required>
                 <i class="ti ti-brand-linkedin position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
            </div>
          </div>
        </div>
        <div class="col-12 text-end mt-3" style="height: 40px;">
          <div
            class="btn btn-outline-primary text-black fw-bold rounded-pill AddTeams"
            do="Founders">
            <i class="ti ti-plus me-3"></i>
            {{ $l == 'en' ? 'Add new founder' : 'إضافة مؤسس جديد' }}
          </div>
        </div>
        {{-- </form> --}}
        @if($client->company)
        <div class="ShowTableFounders mt-4">
          @foreach ($fonders as $fonder)
            <div class="card shadow-sm mb-4 position-relative"
              style="border-radius: 15px;">
              <span class="position-absolute cursor-pointer top-0 end-0 p-4"
                onclick="togglePopup({{ $fonder->id }})">
                <i class="ti ti-dots-vertical fs-5"></i>
              </span>
              <div class="p-3">
                <div class="d-flex align-items-center">
                  <div class="border d-flex radius-15 flex-shrink-0"
                    style="width: 80px; height: 80px;">
                    <img src="/images/background/logo.png"
                      class="img-fluid m-auto" alt="logo" />
                  </div>
                  <div
                    class="mx-3 d-flex flex-column justify-content-center gap-1"
                    style="height: 76px;">
                    <div class="text-black fs-6">{{ $fonder->Name }}</div>
                    <div class="d-flex align-items-center gap-2">
                      <i class="ti ti-mail text-primary"></i>
                      <span
                        class="text-muted text-xs">{{ $fonder->Email }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <img src="/icons/linkedin.svg" width="18"
                        height="18" />
                      <span
                        class="text-muted text-xs">{{ $fonder->LinkedIn }}</span>
                    </div>
                  </div>
                  <div class="ms-auto position-relative">
                    <div id="popup-{{ $fonder->id }}"
                      class="popup-menu z-3 position-absolute bg-white shadow-sm p-2 rounded"
                      style="display: none; {{$l == 'en' ? 'right: 0.5rem;' : 'left: 0.5rem;'}} top: -1rem;">
                      <div
                        class="d-flex align-items-center gap-2 p-2 cursor-pointer"
                        data-bs-toggle="modal"
                        data-bs-target="#EditTeams{{ $fonder->id }}">
                        <i class="ti ti-pencil text-primary fs-6"></i>
                        <span style="font-size: 11px;"
                          class="fw-bold">{{ __('admin.Edit') }}</span>
                      </div>
                      <form action="{{ route('DeleteTeams_Comp') }}"
                        method="POST" style="display: inline-block;">
                        @csrf
                        <input type="hidden" value="{{ $fonder->id }}"
                          name="id">
                        <button
                          class="d-flex align-items-center gap-2 p-2 cursor-pointer border-0 bg-transparent w-100 text-start"
                          type="submit"
                          onclick="return confirm('Are you sure you want to delete this ?')">
                          <i class="ti ti-trash text-danger fs-6"></i>
                          <span style="font-size: 11px;"
                            class="fw-bold">{{ __('admin.Delete') }}</span>
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Edit Customer Modal -->
            <div class="modal fade" id="EditTeams{{ $fonder->id }}"
              tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                <div class="modal-content">
                  <div class="modal-body">
                    <button type="button" class="btn-close"
                      data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-6">
                      <h4 class="mb-2"> ({{ $fonder->Name }})</h4>

                    </div>

                    <div class="ecommerce-customer-add-basic mb-4">

                      <div class="col-md-12">


                        <div class="mb-6 width49">
                          <label class="form-label mb-1">
                            {{ __('admin.founder_name') }}
                          </label>
                          <input type="text"
                            class="border-light form-control mt-2 founder_name{{ $fonder->id }} light-shadow px-10"
                            id="founder_name"
                            placeholder="{{ __('admin.founder_name') }}"
                            value="{{ $fonder->Name ? $fonder->Name : old('founder_name') }}"
                            name="founder_name" autocomplete="founder_name"
                            required>
                             <i class="ti ti-user position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
                        </div>


                        <div class="mb-6 width49">
                          <label class="form-label mb-1">
                            {{ __('admin.founder_email') }}
                          </label>
                          <input type="text"
                            class="border-light form-control mt-2 founder_email{{ $fonder->id }} light-shadow px-10"
                            id="founder_email"
                            placeholder="{{ __('admin.founder_email') }}"
                            value="{{ $fonder->Email ? $fonder->Email : old('founder_email') }}"
                            name="founder_email" autocomplete="founder_email"
                            required>
                             <i class="ti ti-mail position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
                        </div>



                        <div class="mb-6 width49">
                          <label class="form-label mb-1">
                            {{ __('admin.founder_linkedin') }}
                          </label>
                          <div class="position-relative">
                            <input type="text"
                              class="border-light form-control my-2 founder_linkedin{{ $fonder->id }} light-shadow px-10"
                              id="founder_linkedin"
                              placeholder="{{ __('admin.founder_linkedin') }}"
                              value="{{ $fonder->LinkedIn ? $fonder->LinkedIn : old('founder_linkedin') }}"
                              name="founder_linkedin"
                              autocomplete="founder_linkedin"
                            >
                              <i class="ti ti-brand-linkedin position-absolute top-50 translate-middle-y me-3 text-primary"
                                style="left: 7px;"></i>
                          </div>
                        </div>

                      </div>
                    </div>


                    <div class="text-center">
                      <div type="submit"
                        class="btn btn-primary me-3 data-submit EditTeams"
                        do="{{ $fonder->id }}">{{ __('admin.submit') }}
                      </div>
                      <button type="reset" class="btn btn-label-secondary"
                        data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
                    </div>
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

  <!-- Shareholders Column -->
  <div class="col-md-12 mb-3 px-10">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title">{{ __('admin.shareholders') }}</h5>
        <form method="post" action="{{ route('AddTeams_Comp') }}"
          enctype="multipart/form-data" onsubmit="resetForm(this)">
          {!! csrf_field() !!}
          <div class="show_Shareholders row m-2" px-10>

            <div class="col-12">
              <div class="form-group position-relative">
                <input type="text"
                  class="border-light form-control mt-2 shareholder_name light-shadow px-10"
                  id="shareholder_name"
                  placeholder="{{ __('admin.shareholder_name') }}"
                  value="{{ old('shareholder_name') }}"
                  name="shareholder_name" autocomplete="shareholder_name"
                  required>
                   <i class="ti ti-user position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group position-relative">
                <input type="text"
                  class="border-light form-control mt-2 shareholder_role light-shadow px-10"
                  id="shareholder_role"
                  placeholder="{{ __('admin.shareholder_role') }}"
                  value="{{ old('shareholder_role') }}"
                  name="shareholder_role" autocomplete="shareholder_role"
                  required>
                    <i class="ti ti-briefcase position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group position-relative">
                <input type="number"
                  class="border-light form-control my-2 shareholder_ownership_percentage light-shadow px-10"
                  id="shareholder_ownership_percentage"
                  placeholder="{{ __('admin.shareholder_ownership_percentage') }}"
                  value="{{ old('shareholder_ownership_percentage') }}"
                  name="shareholder_ownership_percentage"
                  autocomplete="shareholder_ownership_percentage" required>
                  <i class="ti ti-percentage position-absolute top-50 translate-middle-y me-3 text-primary" style="left: 7px;"></i>
              </div>
            </div>
          </div>
          <div class="col-12 text-end mt-3" style="height: 40px;">
            <div type="submit"
              class="btn btn-outline-primary text-black fw-bold rounded-pill AddTeams"
              do="Shareholders">
              <i class="ti ti-plus me-3"></i>
              {{ $l == 'en' ? 'Add new shareholder' : 'إضافة مساهم جديد' }}
            </div>
          </div>
        </form>

        @if($client->company)
        <div class="ShowTableShareholders mt-4">
          <div class="row">
            @foreach ($Shareholders as $Shareholder)
              <div class="col-md-6">
                <div class="card shadow-sm mb-4 position-relative" style="border-radius: 15px;">
                  <span class="position-absolute cursor-pointer top-0 end-0 p-4" onclick="togglePopup({{ $Shareholder->id }})">
                    <i class="ti ti-dots-vertical fs-5"></i>
                  </span>
                  <div class="p-3">
                    <div class="d-flex align-items-center justify-content-between">
                      <div class="mx-3 d-flex flex-column justify-content-center gap-1" style="height: 76px;">
                        <div class="text-black fs-6">{{ $Shareholder->Name}}</div>
                        <button class="btn btn-outline-primary btn-xs rounded-pill mt-1 fw-bold" style="height: 22px; width: fit-content">
                          {{ $Shareholder->RoleShereholder }}
                        </button>
                      </div>
                      <div class="mx-3 d-flex align-items-center gap-3" style="height: 76px;">
                        <i class="ti ti-chevrons-right ti-28px;"></i>
                        <!-- Circular Progress -->
                        <div class="progress-circle p-3 position-relative d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                          <span class="position-absolute text-black fw-bold" style="font-size: 13px;">{{ $Shareholder->PresentagShereholder }}%</span>
                          <svg class="position-absolute" width="54" height="54" viewBox="0 0 36 36">
                            <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e6e6e6" stroke-width="2.5" />
                            <path class="circle" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#007bff" stroke-width="2.5" stroke-dasharray="{{ $Shareholder->PresentagShereholder }}, 100" />
                          </svg>
                        </div>
                        <!-- Circular Progress -->
                      </div>
                      <div class="ms-auto position-relative">
                        <div id="popup-{{ $Shareholder->id }}" class="popup-menu z-3 position-absolute bg-white shadow-sm p-2 rounded" style="display: none; {{$l == 'en' ? 'right: 0.5rem;' : 'left: 0.5rem;'}} top: -1rem;">
                          <div class="d-flex align-items-center gap-2 p-2 cursor-pointer" data-bs-toggle="modal" data-bs-target="#EditTeams{{ $Shareholder->id }}">
                            <i class="ti ti-pencil text-primary fs-6"></i>
                            <span style="font-size: 11px;" class="fw-bold">{{ __('admin.Edit') }}</span>
                          </div>
                          <form action="{{ route('DeleteTeams_Comp') }}" method="POST" style="display: inline-block;">
                            @csrf
                            <input type="hidden" value="{{ $Shareholder->id }}" name="id">
                            <button class="d-flex align-items-center gap-2 p-2 cursor-pointer border-0 bg-transparent w-100 text-start" type="submit" onclick="return confirm('Are you sure you want to delete this ?')">
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
                <div class="modal fade" id="EditTeams{{ $Shareholder->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                    <div class="modal-content">
                      <div class="modal-body">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="text-center mb-6">
                          <h4 class="mb-2"> ({{ $Shareholder->Name }})</h4>
                        </div>
                        <div class="ecommerce-customer-add-basic mb-4">
                          <div class="col-md-12">
                            <div class="mb-6 width49">
                              <label class="form-label mb-1">
                                {{ __('admin.shareholder_name') }}
                              </label>
                              <div class="position-relative">
                                <input type="text" class="border-light form-control my-2 shareholder_name{{ $Shareholder->id }} light-shadow" id="shareholder_name" placeholder="{{ __('admin px-10.shareholder_name') }}" value="{{ $Shareholder->Name ? $Shareholder->Name : old('shareholder_name') }}" name="shareholder_name" autocomplete="shareholder_name" required>
                                 <i class="ti ti-user position-absolute top-50 translate-middle-y me-3 text-primary"
                    style="left: 7px;"></i>
                              </div>

                              </div>
                            <div class="mb-6 width49">
                              <label class="form-label mb-1">
                                {{ __('admin.shareholder_role') }}
                              </label>
                              <div class="position-relative">
                                <input type="text" class="border-light form-control mt-2 shareholder_role{{ $Shareholder->id }} light-shadow" id="shareholder_role" placeholder="{{ __('admin px-10.shareholder_role') }}" value="{{ $Shareholder->RoleShereholder ? $Shareholder->RoleShereholder : old('shareholder_role') }}" name="shareholder_role" autocomplete="shareholder_role" required>
                                 <i class="ti ti-briefcase
                                 position-absolute top-50 translate-middle-y me-3 text-primary"
                    style="left: 7px;"></i>

                              </div>
                            </div>
                            <div class="mb-6 width49">
                              <label class="form-label mb-1">
                                {{ __('admin.shareholder_ownership_percentage') }}
                              </label>
                              <div class="position-relative">
                                <input type="text" class="border-light form-control mt-2 shareholder_ownership_percentage{{ $Shareholder->id }} light-shadow" px-10 id="shareholder_ownership_percentage" placeholder="{{ __('admin.shareholder_ownership_percentage') }}" value="{{ $Shareholder->PresentagShereholder ? $Shareholder->PresentagShereholder : old('shareholder_ownership_percentage') }}" name="shareholder_ownership_percentage" autocomplete="shareholder_ownership_percentage" required>
                                 <i class="ti ti-percentage
                                 position-absolute top-50 translate-middle-y me-3 text-primary"
                    style="left: 7px;"></i>

                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="text-center">
                          <div type="submit" class="btn btn-primary me-3 data-submit EditTeams" do="{{ $Shareholder->id }}">
                            {{ __('admin.submit') }}
                          </div>
                          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!--/ Edit JO Modal -->
              </div>
            @endforeach
          </div>
        </div>
        @endif

      </div>
    </div>
  </div>

  <!-- Investors Column -->
  <div class="col-md-12 mb-3 px-10">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title">{{ __('admin.investors') }}</h5>

        <div class="show_investor row m-2" px-10>
          <div class="col-12">
            <div class="form-group position-relative">
              <input type="text"
                class="border-light form-control mt-2 investor_name light-shadow px-10"
                id="investor_name"
                placeholder="{{ __('admin.investor_name') }}"
                value="{{ $client->company ? $client->company->investor_name : old('investor_name') }}"
                name="investor_name" autocomplete="investor_name" required>
                 <i class="ti ti-user position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group my-2">
              <select
                class="form-select border-light form-control my-2 investment_type light-shadow px-10"
                id="investment_type" name="investment_type"
                aria-placeholder="{{ __('admin.investment_type') }}"
                autocomplete="investment_type" required>
                 <i class="ti ti-world position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
                <option value="{{ __('admin.investment_type') }}" selected disabled> {{ __('admin.investment_type') }} </option>
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
            </div>
          </div>
        </div>
        <div class="col-12 text-end mt-3" style="height: 40px;">
          <div type="submit"
            class="btn btn-outline-primary text-black fw-bold rounded-pill AddTeams"
            do="Investors">
            <i class="ti ti-plus me-3"></i>
            {{ $l == 'en' ? 'Add new investor' : 'إضافة مستثمر جديد' }}
          </div>
        </div>

        @if($client->company)
        <div class="ShowTableInvestors">
          <div class="row px-4 mt-4">
            @foreach ($Investors as $Investor)
              <div class="col-md-6">
                <div class="card border-light shadow-sm mb-4 position-relative"
                  style="border-radius: 15px;">
                  <span
                    class="position-absolute cursor-pointer top-0 end-0 p-4"
                    onclick="togglePopup({{ $Investor->id }})">
                    <i class="ti ti-dots-vertical fs-5"></i>
                  </span>
                  <div class="p-3">
                    <div class="d-flex align-items-center">
                      <div class="border d-flex radius-15 d-flex flex-shrink-0"
                        style="width: 80px; height: 80px;">
                        <img src="/images/background/logo.png"
                          class="img-fluid m-auto" alt="logo" />
                      </div>
                      <div
                        class="mx-3 d-flex flex-column justify-content-center gap-1"
                        style="height: 76px;">
                        <div class="text-black fs-6">{{ $Investor->Name }}
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          <i class="ti ti-drone text-info"></i>
                          <button
                            class="btn btn-outline-primary btn-xs rounded-pill fw-bold"
                            style="height: 18px;">
                            {{ $Investor->investorType }}
                          </button>
                        </div>
                      </div>
                      <div class="ms-auto position-relative">
                        <div id="popup-{{ $Investor->id }}"
                          class="popup-menu z-3 position-absolute bg-white shadow-sm p-2 rounded"
                          style="display: none; {{$l == 'en' ? 'right: 0.5rem;' : 'left: 0.5rem;'}} top: -1rem;">
                          <div
                            class="d-flex align-items-center gap-2 p-2 cursor-pointer"
                            data-bs-toggle="modal"
                            data-bs-target="#EditTeams{{ $Investor->id }}">
                            <i class="ti ti-pencil text-primary fs-6"></i>
                            <span style="font-size: 11px;"
                              class="fw-bold">{{ __('admin.Edit') }}</span>
                          </div>
                          <form action="{{ route('DeleteTeams_Comp') }}"
                            method="POST" style="display: inline-block;">
                            @csrf
                            <input type="hidden" value="{{ $Investor->id }}"
                              name="id">
                            <button
                              class="d-flex align-items-center gap-2 p-2 cursor-pointer border-0 bg-transparent w-100 text-start"
                              type="submit"
                              onclick="return confirm('Are you sure you want to delete this ?')">
                              <i class="ti ti-trash text-danger fs-6"></i>
                              <span style="font-size: 11px;"
                                class="fw-bold">{{ __('admin.Delete') }}</span>
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Edit Customer Modal -->
              <div class="modal fade" id="EditTeams{{ $Investor->id }}"
                tabindex="-1" aria-hidden="true">
                <div
                  class="modal-dialog modal-lg modal-simple modal-edit-user">
                  <div class="modal-content">
                    <div class="modal-body">
                      <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                      <div class="text-center mb-6">
                        <h4 class="mb-2"> ({{ $Investor->Name }})</h4>
                      </div>
                      <div class="ecommerce-customer-add-basic mb-4">
                        <div class="col-md-12">
                          <div class="mb-6 width49">
                            <label class="form-label mb-1">
                              {{ __('admin.investor_name') }}
                            </label>
                            <input type="text"
                              class="border-light form-control my-2 investor_name{{ $Investor->id }} light-shadow px-10"
                              id="founder_linkedin"
                              placeholder="{{ __('admin.founder_linkedin') }}"
                              value="{{ $Investor->Name ? $Investor->Name : old('founder_linkedin') }}"
                              name="founder_linkedin"
                              autocomplete="founder_linkedin" required>
                               <i class="ti ti-user position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
                          </div>
                          <div class="mb-6 width49">
                            <label class="form-label mb-1">
                              {{ __('admin.investment_type') }}
                            </label>
                            <select
                            class="form-select border-light form-control my-2 investment_type light-shadow px-10"
                            id="investment_type" name="investment_type"
                            aria-placeholder="{{ __('admin.investment_type') }}"
                            autocomplete="investment_type" required>
                             <i class="ti ti-world position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
                            <option value="{{ __('admin.investment_type') }}" selected
                              disabled>
                              {{ __('admin.investment_type') }}
                            </option>
                            <option value="type1">
                              Type 1</option>
                            <option value="type2">
                              Type 2</option>
                            <option value="type3">
                              Type 3</option>
                            <!-- Add more options as needed -->
                          </select>
                          </div>

                        </div>
                      </div>
                      <div class="text-center">
                        <div type="submit"
                          class="btn btn-primary me-3 data-submit EditTeams"
                          do="{{ $Investor->id }}">{{ __('admin.submit') }}
                        </div>
                        <button type="reset" class="btn btn-label-secondary"
                          data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
                      </div>
                    </div>
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
</div>

<script>
  function togglePopup(id) {
    const popup = document.getElementById(`popup-${id}`);
    popup.style.display = popup.style.display === 'none' ? 'block' : 'none';
  }

  // Close the popup if clicked outside
  document.addEventListener('click', function(event) {
    const popups = document.querySelectorAll('.popup-menu z-3');
    popups.forEach(popup => {
      if (!popup.contains(event.target) && !popup.previousElementSibling
        .contains(event.target)) {
        popup.style.display = 'none';
      }
    });
  });

  function resetForm(form) {
    form.reset();
  }
</script>
