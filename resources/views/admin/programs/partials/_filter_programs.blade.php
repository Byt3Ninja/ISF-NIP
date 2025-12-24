<div class="card mb-6" style="border-radius: 20px;">
  <div class="card-body">
    <div class="row">
      <div class="col-12 col-lg-8">
        <!-- Filter -->
        <form id="filter-programs-form" method="GET" action="{{ route('programlist.index') }}">
          <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-4">
              <select name="organization_id" class="form-select select2"
                data-placeholder="{{ __('admin.Organization') }}">
                <option value="">{{ __('admin.Organization') }}</option>
                @if(auth()->user()->hasAnyRole(['Admin']))
                <option value="0">{{ __('admin.All') }}</option>
                @endif
                @php
                  $user = auth()->user();
                  if($user->hasRole('Admin')) {
                    $organizations = App\Organization::all();
                  } elseif($user->hasRole('organizationManager') || $user->isSubAdmin()) {
                    $orgIds = $user->organizations_ids;
                    $organizations = App\Organization::whereIn('id', $orgIds)->get();
                  } else {
                    $organizations = collect();
                  }
                @endphp
                @foreach ($organizations as $organization)
                  <option value="{{ $organization->id }}"
                    {{ $organization->id == request('organization_id') ? 'selected' : '' }}>
                    {{ $organization->{'name_' . $l} }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
              <input class="form-control datepicker" placeholder="{{ __('admin.StartDate') }}" name="start_date"
                value="{{ request('start_date') }}" />
            </div>

            <div class="col-6 col-md-3 col-lg-2">
              <input class="form-control datepicker" placeholder="{{ __('admin.EndDate') }}" name="end_date"
                value="{{ request('end_date') }}" />
            </div>

            <div class="col-12 col-md-6 col-lg-2">
              <select name="private" class="form-select select2" data-placeholder="{{ __('admin.Private') }}">
                <option value="">{{ __('admin.Private') }}</option>
                <option value="0">{{ __('admin.All') }}</option>
                <option value="Yes" {{ 'Yes' == request('private') ? 'selected' : '' }}> {{ __('admin.Yes') }}
                </option>
                <option value="No" {{ 'No' == request('private') ? 'selected' : '' }}> {{ __('admin.No') }}
                </option>
              </select>
            </div>
             <div class="col-12 col-md-6 col-lg-2">
              <select name="program_type" class="form-select" data-placeholder="{{ __('admin.All') }}">
              <option value="">{{ __('admin.All') }}</option>
                @foreach (\App\Cycle::programTypes() as $value => $label)
                   <option value="{{ $value }}" {{ request('program_type') == $value ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                  @endforeach
               </select>
           </div>
          </div>
        </form>
      </div>
      <div class="col-12 col-lg-4 mt-3 mt-lg-0 d-flex justify-content-end">
        <div class="position-relative w-100 ps-md-12">
          <div class="position-absolute top-50 end-0 mx-4 translate-middle-y">
            <i class="ti ti-search"></i>
          </div>
          <input class="form-control" id="search-programs" placeholder="{{ __('admin.Search') }}" name="search"
            value="{{ request('search') }}" />
        </div>
      </div>
    </div>
  </div>
</div>
