@extends('layouts.admin')

@section('content')
<div class="">
  <div class="">
    <div class="content-header">
      @if (session()->has('message'))
        <x-alert 
          type="success" 
          message="{{ __('admin.' . session()->get('message')) }} {{ Session::forget('message') }}" 
        />
      @endif
      @if (session()->has('message2'))
        <x-alert 
          type="danger" 
          message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" 
        />
      @endif

      @if ($errors->any())
        @foreach ($errors->all() as $error)
          <x-alert type="danger" message="{{ $error }}" />
        @endforeach
      @endif
    </div>

    <!-- Header -->
    <div class="card shadow-sm mb-6">
      <div class="card-body py-4">
        <div class="row">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex bg-label-primary p-2 justify-content-center align-items-center rounded shadow">
              <div class="avatar w-px-40 h-px-40">
                <div class="avatar-initial rounded bg-white">
                  <i class="ti ti-components text-primary ti-md"></i>
                </div>
              </div>
              <div class="mx-4" style="height: 50px;">
                <p class="text-dark fw-semibold fs-5" style="margin-bottom: -3px;">{{ __('admin.Cycles') }}</p>
                <span class="fs-5">{{ $cycles->total() }}</span>
              </div>
            </div>
            <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center mt-4 mt-md-0">
              <!-- Search Bar -->
              <div class="input-group input-group-merge search-form" style="width: 300px;">
                <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                <input
                  type="search" 
                  id="debounced-search" 
                  class="form-control"
                  placeholder="{{ __('admin.Search') }} {{ __('admin.Cycles') }}" 
                  aria-label="Search programs"
                  aria-describedby="search-programs"
                  value="{{ request('search') ? request('search') : '' }}"
                />
              </div>
              
              <!-- Reset Button (shown when filters are active) -->
              @php
                $hasActiveFilters = request()->hasAny(['organization_id', 'start_date', 'end_date', 'private', 'program_type']);
              @endphp
              @if($hasActiveFilters)
                <a href="{{ route('programlist.index') }}" class="btn btn-outline-secondary" title="{{ __('admin.Reset') }}">
                  <i class="ti ti-refresh me-1"></i>
                  {{ __('admin.Reset') }}
                </a>
              @endif
              <button type="button" class="btn btn-label-secondary text-primary" data-bs-toggle="modal" data-bs-target="#filterProgramsModal">
                <i class="ti ti-filter me-2"></i>
                <span>{{ __('cycles.Show_Filters') }}</span>
              </button>

              @if ($_user->ableTo('programlist.create', $permissions))
                <a class="btn btn-primary" href="{{ route('programlist.create') }}">
                  <i class="ti ti-plus me-1"></i>
                  {{ __('admin.New Program') }}
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Header -->

    <!-- Cycles List -->
    <div class="row gy-6">
      @if ($cycles && $cycles->count() > 0)
        @foreach ($cycles as $cycle)
          @php
            $totalSteps = $cycle->steps->count();

            // GV-9566: a step is finished once its last (inclusive) day has passed
            // in the program's platform zone.
            $cycleToday = \App\Services\TimezoneService::platformToday(\App\Services\TimezoneService::platform($cycle->organization_id));
            $totalStepsFinish = $cycle->steps
              ->filter(fn ($s) => $s->from && $s->to
                && $s->from->format('Y-m-d') <= $cycleToday && $s->to->format('Y-m-d') < $cycleToday)
              ->count();
            $presSteps = $totalSteps > 0 ? ($totalStepsFinish / $totalSteps) * 100 : 0;
          @endphp
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-header d-flex align-items-center justify-content-between pb-3">
                <div class="w-px-52 h-px-52 flex-shrink-0 position-relative bg-white rounded overflow-hidden me-2 shadow-xs d-flex justify-content-center align-items-center">
                  <img 
                    src="{{ $cycle->logo }}"
                    alt="{{ __('admin.Logo') }}"
                    onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');"
                    class="w-100 h-100 object-fit-contain {{ $cycle->logo ? '' : 'd-none' }}"
                  />
                  <span class="avatar {{ $cycle->logo ? 'd-none' : '' }}">
                    <div class="avatar-initial avatar-initial bg-label-primary rounded">
                      <i class="ti ti-rocket ti-lg"></i>
                    </div>
                  </span>
                </div>

                <div class="d-flex align-items-center">
                  <span class="badge bg-label-{{ $cycle->type == 'Hackathon' ? 'primary' : 'secondary' }} me-3">
                    {{ $cycle->type == 'Hackathon' ? __('cycles.' . $cycle->type) : __('cycles.general') }}
                  </span>
                  <div class="dropdown">
                    <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                      <i class="ti ti-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      @if( $_user->ableTo('programs.view', $permissions) )
                      <li>
                        <a class="dropdown-item" href="/dashboard/programlist/{{ $cycle->id }}">
                          <i class="ti ti-eye me-1"></i> {{ __('admin.View_Details') }}
                        </a>
                      </li>
                      @endif
                      @if ($cycle->join_url)
                        <li>
                          <a 
                            class="dropdown-item text-capitalize"
                            href="javascript:void(0);"
                            onclick="copyToClipboard('{{ $cycle->join_url }}', 'public');"
                          >
                            <i class="ti ti-link me-1"></i> {{ __('cycles.copy public link') }}
                          </a>
                        </li>
                      @endif
                      @if ($cycle->private)
                        <li>
                          <a 
                            class="dropdown-item text-capitalize"
                            href="javascript:void(0);"
                            onclick="copyToClipboard('{{ url('dashboard/cycles/view/' . $cycle->private) }}', 'private')"
                          >
                            <i class="ti ti-link me-1"></i> {{ __('cycles.copy private link') }}
                          </a>
                        </li>
                      @endif
                      @if ($_user->ableTo('programlist.edit', $permissions))
                        <li>
                          <a 
                            class="dropdown-item" 
                            href="/dashboard/programlist/edit/{{ $cycle->id }}"
                          >
                            <i class="ti ti-pencil me-1"></i> {{ __('cycles.Edit_Program') }}
                          </a>
                        </li>
                        <li>
                          <a 
                            class="dropdown-item" 
                            href="/dashboard/programlist/edit/{{ $cycle->id }}?tab=landing"
                          >
                            <i class="ti ti-eye-check me-1"></i> {{ __('cycles.Landing Page Visibility') }}
                          </a>
                        </li>
                      @endif
                      @if ($_user->ableToAny(['programs.create', 'programs.edit'], $permissions))
                        <li>
                          <a 
                            class="dropdown-item" 
                            href="/dashboard/programlist/{{ $cycle->id }}/custom-pages"
                          >
                            <i class="ti ti-files me-1"></i> {{ __('cycles.Manage Custom Pages') }}
                          </a>
                        </li>
                      @endif
                      @if ($cycle->type === 'Hackathon')
                        <li>
                          <a class="dropdown-item" href="/dashboard/hackathon/dashboard/{{ $cycle->id }}">
                            <i class="ti ti-settings me-1"></i> {{ __('admin.manage_hackathon') }}
                          </a>
                        </li>
                      @endif
                      @if ($_user->ableTo('cycles.list', $permissions))
                        <li>
                          <a class="dropdown-item" href="/dashboard/cycles/{{ $cycle->id }}">
                            <i class="ti ti-adjustments-cog me-1"></i> {{ __('admin.manage_steps') }}
                          </a>
                        </li>
                      @endif
                      @if ($_user->ableTo('cycles.users_manage', $permissions))
                        <li>
                          <a class="dropdown-item" href="/dashboard/cycles/{{ $cycle->id }}/registered">
                            <i class="ti ti-settings-cog me-1"></i> {{ __('admin.manage_startups') }}
                          </a>
                        </li>
                      @endif
                      @if ($_user->ableTo('mentorships.requests_list', $permissions))
                      <li>
                        <a class="dropdown-item" href="{{ route('mentorships.requests_list', $cycle->id) }}">
                          <i class="ti ti-user-cog me-1"></i>  {{ __('admin.manage_mentors') }}
                        </a>
                      </li>
                      @endif
                    </ul>
                  </div>
                </div>
              </div>

              <div class="card-body d-flex flex-column">
                <a href="/dashboard/programlist/{{ $cycle->id }}">
                  <h5 class="mb-1 fw-semibold">{{ $cycle->title }}</h5>
                </a>
                <p class="text-muted small line-clamp-3">{{ $cycle->description }}</p>

                {{-- Stats --}}
                <div class="d-flex justify-content-between align-items-center border-bottom pb-6 mb-6 mt-2">
                  <a 
                    @if ($_user->ableTo('cycles.users_manage', $permissions))
                      href="/dashboard/cycles/{{ $cycle->id }}/registered"
                    @else
                      href="javascript:void(0);" 
                    @endif
                    class="d-flex flex-column text-center text-dark btn btn-text-info waves-effect waves-light"
                  >
                    <div class="avatar">
                      <div class="avatar-initial bg-label-info rounded-circle">
                        <i class="ti ti-building-skyscraper ti-lg text-info"></i>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                      <strong>{{ $cycle->companies->count() }}</strong>
                      <span>{{ __('admin.Startups') }}</span>
                    </div>
                  </a>
                  <a 
                    @if ($_user->ableTo('cycles.list', $permissions))
                      href="/dashboard/cycles/{{ $cycle->id }}"
                    @else
                      href="javascript:void(0);" 
                    @endif
                    class="d-flex flex-column text-center text-dark btn btn-text-success waves-effect waves-light"
                  >
                    <div class="avatar">
                      <div class="avatar-initial bg-label-success rounded-circle">
                        <i class="ti ti-recycle ti-lg text-success"></i>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                      <strong>{{ $cycle->steps->count() }}</strong>
                      <span>{{ __('cycles.steps') }}</span>
                    </div>
                  </a>
                  <a
                    @if ($_user->ableTo('mentorships.requests_list', $permissions))
                      href="{{ route('mentorships.requests_list', $cycle->id) }}"
                    @elseif ($_user->ableTo('mentors.list', $permissions))
                      href="/dashboard/mentors?programFilter={{ $cycle->id }}" 
                    @else
                      href="javascript:void(0);" 
                    @endif
                    class="d-flex flex-column text-center text-dark btn btn-text-warning waves-effect waves-light"
                  >
                    <div class="avatar">
                      <div class="avatar-initial bg-label-warning rounded-circle">
                        <i class="ti ti-users ti-lg text-warning"></i>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                      <strong>{{ $cycle->mentors->unique('id')->count() }}</strong>
                      <span>{{ __('admin.Mentors') }}</span>
                    </div>
                  </a>
                </div>

                {{-- Progress --}}
                {{-- <div class="d-flex flex-column mb-2">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>{{ __('admin.Progress') }}</span>
                    <strong>{{ number_format($presSteps, 0) }}%</strong>
                  </div>
                  <div class="progress mb-3" style="height: 8px;">
                    <div 
                      class="progress-bar" 
                      style="width: {{ number_format($presSteps, 0) }}%;"
                      aria-valuenow="{{ number_format($presSteps, 0) }}"
                      aria-valuemin="0" aria-valuemax="100">
                    </div>
                  </div>
                </div> --}}

                  {{-- Date --}}
                <p class="text-muted mb-6 d-flex align-items-center gap-2">
                  <i class="ti ti-calendar"></i> 
                  {{ $cycle->start ? $cycle->start->format('Y-m-d') : '' }} - 
                  {{ $cycle->end ? $cycle->end->format('Y-m-d') : '' }}
                </p>

                  {{-- Footer Actions --}}
                <div class="d-flex align-items-center gap-3 mt-auto">
                  <a
                    @if ($cycle->type === 'Hackathon')                   
                      href="/dashboard/hackathon/details/{{ $cycle->id }}" 
                    @else
                      href="/dashboard/programlist/{{ $cycle->id }}" 
                    @endif
                    class="btn btn-primary flex-grow-1 text-nowrap">
                    <i class="ti ti-eye me-1"></i> 
                    {{ $cycle->type === 'Hackathon' ? __('admin.view_hackathon') : __('admin.View_Details') }}
                  </a>
                  <a  class="btn btn-outline-primary flex-grow-1 text-nowrap" href="{{ route('programs.report', $cycle->id) }}">
                    <i class="ti ti-file-text me-1"></i> {{ __('admin.View_Report') }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        @endforeach
        <div class="mt-8">
          {{ $cycles->links('pagination::bootstrap-5') }}
        </div>
      @else 
        @include('includes.no-result', ['message' => __('admin.No_Programs_Found')])
      @endif
    </div>
  </div>

  <!-- Filter Modal -->
  <div class="modal fade" id="filterProgramsModal" tabindex="-1" aria-labelledby="filterProgramsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
  
        <!-- Header -->
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center" id="filterProgramsModalLabel">
            <i class="ti ti-filter me-2"></i>
            {{ __('admin.Filter') }} {{ __('admin.Cycles') }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
  
        <!-- Body -->
        <div class="modal-body">
          <form id="filter-programs-form" method="GET" action="{{ route('programlist.index') }}">
  
            <div class="row g-4">
              <!-- Organization -->
              <div class="col-12">
                <label for="organization_id" class="form-label">{{ __('admin.Organization') }}</label>
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
                    {{ $organization->{'name_' . $l} }}
                  </option>
                  @endforeach
                </select>
              </div>
  
              <!-- Start Date -->
              <div class="col-12 col-lg-6 mb-4">
                <label for="start_date" class="form-label">{{ __('admin.StartDate') }}</label>
                <input class="form-control datepicker" placeholder="{{ __('admin.StartDate') }}" name="start_date"
                  value="{{ request('start_date') }}" />
              </div>
  
              <!-- End Date -->
              <div class="col-12 col-lg-6 mb-4">
                <label for="end_date" class="form-label">{{ __('admin.EndDate') }}</label>
                <input class="form-control datepicker" placeholder="{{ __('admin.EndDate') }}" name="end_date"
                  value="{{ request('end_date') }}" />
              </div>
  
              <!-- Private -->
              <div class="col-12">
                <div class="form-group">
                  <label for="private" class="form-label mb-3">{{ __('cycles.Private Link') }}</label>
                  <div>
                    <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="private" value="" {{ '' == request('private') ? 'checked' : '' }}>
                    <label class="form-check-label" for="privateAll">{{ __('admin.All') }}</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="private" value="Yes" {{ 'Yes' == request('private') ? 'checked' : '' }}>
                    <label class="form-check-label" for="privateYes">{{ __('admin.Private') }}</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="private" value="No" {{ 'No' == request('private') ? 'checked' : '' }}>
                    <label class="form-check-label" for="privateNo">{{ __('admin.Not_Private') }}</label>
                  </div>
                  </div>
                </div>
              </div>
  
              <!-- Program Type -->
              <div class="col-12">
                <div class="form-group">
                  <label for="program_type" class="form-label mb-3">{{ __('admin.Program_Type') }}</label>
                  <div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="program_type" value="" {{ '' == request('program_type') ? 'checked' : '' }}>
                      <label class="form-check-label" for="program_typeAll">{{ __('admin.All') }}</label>
                    </div>
                    @foreach (\App\Cycle::programTypes() as $value => $label)
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="program_type" value="{{ $value }}" {{ request('program_type') == $value ? 'checked' : '' }}>
                      <label class="form-check-label" for="program_type{{ $value }}">{{ $label }}</label>
                    </div>
                    @endforeach
                  </div>
                </div>
            </div>
          </form>
        </div>
  
        <!-- Footer -->
        <div class="d-flex justify-content-end align-items-center mt-8">
          <button type="button" class="btn btn-label-secondary me-3" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
          <button type="submit" form="filter-programs-form" class="btn btn-primary">
            {{ __('cycles.Apply_Filters') }}
          </button>
        </div>
      </div>
    </div>
  </div>
  <!--/ Filter Modal -->
</div>

@stop

@section('pagescripts')
  <script>
    function copyToClipboard(text, type) {
      let successMsg = '{{ __('cycles.public_link_copied_successfully') }}';

      if (type == 'private') {
        successMsg = '{{ __('cycles.private_link_copied_successfully') }}';
      }

      const tempInput = document.createElement("input");
      tempInput.value = text;
      document.body.appendChild(tempInput);
      tempInput.select();
      tempInput.setSelectionRange(0, 99999);
      document.execCommand("copy");
      document.body.removeChild(tempInput);

      releaseSuccessAlert({
        title: "{{ __('admin.Copied') }}",
        message: successMsg,
      });
    }

    // Initialize Date Picker
    flatpickr(".datepicker", { dateFormat: "Y-m-d" });
  </script>
@stop
