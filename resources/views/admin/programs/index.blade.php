@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
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
    <div class="card mb-8">
      <div class="card-body">
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
                <span class="fs-5">{{ $cycles->count() }}</span>
              </div>
            </div>
            <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center mt-4 mt-md-0">
              <!-- Search Bar -->
              <div class="input-group input-group-merge search-form" style="width: 300px;">
                <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                <input type="text" id="search-programs" class="form-control"
                  placeholder="{{ __('admin.Search') }} {{ __('admin.Cycles') }}" />
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
              
              <!-- Filter Button -->
              
              <button type="button" class="btn btn-label-secondary text-primary" data-bs-toggle="modal" data-bs-target="#filterProgramsModal">
                <i class="ti ti-filter me-2"></i>
                <span class="mx-1">{{ __('cycles.Show_Filters') }}</span>
              </button>
              <a class="btn btn-primary" href="{{ route('programlist.create') }}">
                <i class="ti ti-plus me-1"></i>
                {{ __('admin.New Program') }}
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- / Header -->

    <div class="row gy-6">
      @foreach ($cycles as $cycle)
        @php
          $totalSteps = app(\App\Step::class)->where('cycle_id', '=', $cycle->id)->count();

          $totalStepsFinish = app(\App\Step::class)
            ->where('cycle_id', '=', $cycle->id)
            ->where('from', '<=', Carbon\Carbon::now())
            ->where('to', '<=', Carbon\Carbon::now())
            ->count();
          $presSteps = $totalSteps > 0 ? ($totalStepsFinish / $totalSteps) * 100 : 0;
        @endphp
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
              <img 
                src="{{ $cycle->img_url }}"
                alt="Logo"
                class="img-fluid rounded me-2 border shadow-xs"
                style="height: 50px; width: 50px; object-fit: cover;"
              />

              <div class="d-flex align-items-center">
                <span class="badge bg-label-{{ $cycle->type == 'Hackathon' ? 'primary' : 'secondary' }} me-3">
                  {{ $cycle->type == 'Hackathon' ? __('cycles.' . $cycle->type) : __('cycles.general') }}
                </span>
                <div class="dropdown">
                  <button type="button" class="btn btn-sm btn-icon rounded-circle shadow-xs border dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="ti ti-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="/dashboard/programlist/{{ $cycle->id }}">
                        <i class="ti ti-eye me-1"></i> {{ __('admin.View_Details') }}
                      </a>
                    </li>
                    @if ($cycle->join_url)
                      <li>
                        <a 
                          class="dropdown-item"
                          href="javascript:void(0);"
                          onclick="copyToClipboard('{{ $cycle->join_url }}');"
                        >
                          <i class="ti ti-link me-1"></i> {{ __('cycles.copy public link') }}
                        </a>
                      </li>
                    @endif
                    <li>
                      <a 
                        class="dropdown-item" 
                        href="/dashboard/programlist/edit/{{ $cycle->id }}"
                      >
                        <i class="ti ti-pencil me-1"></i> {{ __('cycles.Edit_Program') }}
                      </a>
                    </li>
                    @if ($cycle->type === 'Hackathon')
                      <li>
                        <a class="dropdown-item" href="/dashboard/hackathon/dashboard/{{ $cycle->id }}">
                          <i class="ti ti-settings me-1"></i> {{ __('admin.manage_hackathon') }}
                        </a>
                      </li>
                    @else
                      <li>
                        <a class="dropdown-item" href="{{ route('programs.report',$cycle->id) }}">
                          <i class="ti ti-file-text me-1"></i> {{ __('admin.View_Report') }}
                        </a>
                      </li>
                    @endif
                    <li>
                      <a class="dropdown-item" href="/dashboard/cycles/{{ $cycle->id }}">
                        <i class="ti ti-settings me-1"></i> {{ __('admin.manage_steps') }}
                      </a>
                    </li>
                     <li>
                      <a class="dropdown-item" href="/dashboard/cycles/{{ $cycle->id }}/registered">
                        <i class="ti ti-settings me-1"></i> {{ __('admin.manage_startups') }}
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card-body d-flex flex-column">
              <a href="/dashboard/programlist/{{ $cycle->id }}">
                <h5 class="mb-1 fw-semibold">{{ $cycle->title }}</h5>
              </a>
              <p class="text-muted line-clamp-3">{{ $cycle->description }}</p>

              {{-- Stats --}}
              <div class="d-flex justify-content-between align-items-center border-bottom pb-6 mb-6">
                <div class="d-flex flex-column text-center">
                  <h5 class="mb-0 fw-semibold">
                    {{ app(\App\Company::class)->where('cycle', '=', $cycle->id)->count() }}
                  </h5>
                  <span>{{ __('admin.Startups') }}</span>
                </div>
                <div class="d-flex flex-column text-center">
                  <h5 class="mb-0 fw-semibold">
                    {{ app(\App\Step::class)->where('cycle_id', '=', $cycle->id)->count() }}
                  </h5>
                  <span>{{ __('cycles.steps') }}</span>
                </div>
                <div class="d-flex flex-column text-center">
                  <h5 class="mb-0 fw-semibold">
                    {{ $cycle->mentors()->distinct()->get()->count() }}
                  </h5>
                  <span>{{ __('admin.Mentors') }}</span>
                </div>
              </div>

              {{-- Progress --}}
              <div class="d-flex flex-column">
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
              </div>

                {{-- Date --}}
              <p class="text-muted mb-6 d-flex align-items-center">
                <i class="ti ti-calendar"></i> 
                {{ $cycle->start ? $cycle->start->format('Y-m-d') : '' }} - 
                {{ $cycle->end ? $cycle->end->format('Y-m-d') : '' }}
              </p>

                {{-- Footer Actions --}}
              <div class="mt-auto">
                <a
                  @if ($cycle->type === 'Hackathon')                   
                    href="/dashboard/hackathon/details/{{ $cycle->id }}" 
                  @else
                    href="/dashboard/programlist/{{ $cycle->id }}" 
                  @endif
                  class="btn btn-primary w-100">
                  {{ __('admin.View_Details') }}
                </a>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>

<!-- Modal -->
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
                  if(auth()->user()->hasAnyRole(['organizationManager'])) {
                    $organizations = App\organization::where('id',auth()->user()->organization_id)->get();
                  } else {
                    $organizations = App\organization::all();
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
@stop

@section('pagescripts')
  <script>
    function copyToClipboard(text) {
      const tempInput = document.createElement("input");
      tempInput.value = text;
      document.body.appendChild(tempInput);
      tempInput.select();
      tempInput.setSelectionRange(0, 99999);
      document.execCommand("copy");
      document.body.removeChild(tempInput);
      releaseSuccessAlert({
        title: "{{ __('admin.Copied') }}",
        message: "{{ __('admin.Copied') }}"
      });
    }

    // Initialize Date Picker
    flatpickr(".datepicker", { dateFormat: "Y-m-d" });

    $('#search-programs').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      console.log('value', value)
      $('.row .card').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
    });
  </script>
@stop
