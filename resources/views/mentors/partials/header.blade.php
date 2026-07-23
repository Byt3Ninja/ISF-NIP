<!-- Header -->
<div class="card">
  <div class="card-body py-4">
    <div class="row">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div class="d-flex bg-label-warning p-2 justify-content-center align-items-center rounded shadow">
          <div class="bg-white d-flex justify-content-center align-items-center rounded"
            style="width: 40px; height: 40px; shadow">
            <i class="ti ti-users ti-24px"></i>
          </div>
          <div class="mx-4" style="height: 50px;">
            <p class="text-black fw-medium fs-5" style="margin-bottom: -3px;">
              {{ __('dashboard.Mentors') }}
            </p>
            <span class="fs-5">
              <!-- Total Mentors Count -->
              {{ count($mentors) }}
            </span>
          </div>
        </div>
        <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center mt-4 mt-md-0">
          <!-- Search Bar -->
          <div class="input-group input-group-merge search-form" style="width: 300px;">
            <span class="input-group-text">
              <i class="ti ti-search text-muted"></i>
            </span>
            <input 
              name="search"
              type="search" 
              id="debounced-search" 
              class="form-control"
              value="{{ request('search') }}"
              placeholder="{{ __('admin.Search') }} {{ __('dashboard.Mentors') }}" 
            />
          </div>
          
          <!-- Reset Button (shown when filters are active) -->
          @php
            $hasActiveFilters = request()->hasAny(['programFilter', 'rateSearch', 'expertises', 'linkedin', 'enrolledInProgramFilter', 'search']) || 
                              (request()->has('expertises') && !empty(request('expertises')));
          @endphp
          @if($hasActiveFilters)
            <a href="{{ route('mentors.list') }}" class="btn btn-outline-secondary" title="{{ __('admin.Reset') }}">
              <i class="ti ti-refresh me-2"></i>
              {{ __('admin.Reset') }}
            </a>
          @endif
          
          <!-- Filter Button -->
          <button type="button" class="btn btn-label-secondary text-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="ti ti-filter me-2"></i>
            <span class="mx-1">{{ __('cycles.Show_Filters') }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- / Header -->