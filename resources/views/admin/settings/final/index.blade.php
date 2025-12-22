@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
   <!-- Navigation Tabs -->
    <nav class="nav-align-top mb-6">
      @php
        $tabs = [
          ['title' => __('admin.initial_screening'), 'url' => 'dashboard/screening', 'icon' => 'scale'],
          ['title' => __('admin.Final Screening'), 'url' => 'dashboard/final', 'icon' => 'gavel'],
        ];
      @endphp
      <ul class="nav nav-pills">
        @foreach($tabs as $tab)
          <li class="nav-item">
            <a href="/{{ $tab['url'] }}" class="nav-link {{ Request::is($tab['url'] . '*') ? 'active' : '' }}">
              <i class="ti ti-{{ $tab['icon'] }} me-2"></i>
              {{ $tab['title'] }}
            </a>
          </li>
        @endforeach
      </ul>
    </nav>
    <!--/ Navigation Tabs -->

    <!-- Alerts -->
    @if (session()->has('message'))
      <x-alert type="success" message="{{ __('admin.' . session()->get('message')) }} {{ Session::forget('message') }}" />
    @endif
    @if (session()->has('message2'))
      <x-alert type="danger" message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" />
    @endif
    <!--/ Alerts -->

    <div class="card">
      <div class="card-header border-bottom">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-1">{{ __('admin.screening.final_heading') }}</h5>
            <p class="text-muted mb-0">{{ __('admin.screening.final_subheading') }}</p>
          </div>
          <div class="d-flex align-items-center gap-3">
            <!-- Quick Stats -->
            <div class="d-flex align-items-center">
              <div class="badge bg-label-primary rounded-pill d-flex align-items-center px-3 py-2">
                <i class="ti ti-list me-1"></i>
                <span class="fw-semibold">
                  {{ count($finalScreeningSteps) }} {{ __('admin.screening.step') }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="card bg-light-subtle shadow-none rounded-0 border-bottom">
        <div class="card-body py-4">
          <form id="filter-form" method="GET" action="/dashboard/screening">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">{{ __('admin.screening.search_steps') }}</label>
                <input type="text" class="form-control bg-card" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.screening.search_steps_placeholder') }}">
              </div>
              <div class="col-md-3">
                <label class="form-label">{{ __('admin.screening.screening_type') }}</label>
                <select class="form-select bg-card" name="screening_type">
                  <option value="">{{ __('admin.screening.all_types') }}</option>
                  <option value="onetoall" {{ request('screening_type') == 'onetoall' ? 'selected' : '' }}>{{ __('admin.screening.onetoall') }}</option>
                  <option value="grouptoall" {{ request('screening_type') == 'grouptoall' ? 'selected' : '' }}>{{ __('admin.screening.grouptoall') }}</option>
                  <option value="grouptogroup" {{ request('screening_type') == 'grouptogroup' ? 'selected' : '' }}>{{ __('admin.screening.grouptogroup') }}</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">{{ __('admin.status') }}</label>
                <select class="form-select bg-card" name="status">
                  <option value="">{{ __('admin.All_statuses') }}</option>
                  <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('admin.In_Progress') }}</option>
                  <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>{{ __('admin.Upcoming') }}</option>
                  <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('admin.completed') }}</option>
                </select>
              </div>
              <div class="col-md-3">
                <div class="d-flex justify-content-md-end align-items-end h-100 gap-2">
                  <button type="submit" class="btn btn-primary" disabled>
                    <i class="ti ti-filter me-2"></i> {{ __('admin.Filter') }}
                  </button>
                  @if(request('search') || request('screening_type') || request('status'))
                    <a href="/dashboard/screening" class="btn btn-outline-secondary">
                      <i class="ti ti-refresh me-2"></i> {{ __('admin.Reset') }}
                    </a>
                  @endif
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="card-body">
        <!-- Steps Grid -->
        <div class="row g-4">
          @forelse($finalScreeningSteps as $step)
            <div class="col-xl-4 col-lg-6 col-md-6">
              <div class="card shadow-sm border h-100" data-step-id="{{ $step->id }}">
                <!-- Card Header with Status Indicator -->
                <div class="card-header d-flex align-items-center justify-content-between pb-2">
                  <div class="d-flex align-items-center gap-2">
                    <div class="badge bg-label-primary rounded-pill">#{{ $step->id }}</div>
                    <div class="dropdown">
                      <button class="btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="ti ti-dots-vertical"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="/dashboard/show-builder{{ $step->organization->id ? '?' . 'org_id=' . $step->organization->id : '' }}">
                          <i class="ti ti-plus me-1"></i> {{ __('builder.Add_New_Form') }}
                        </a>
                        <button class="dropdown-item edit-step-btn" data-step='@json($step)'>
                          <i class="ti ti-pencil me-1"></i> {{ __('admin.edit_step') }}
                        </button>
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item text-danger" onclick="handleDeleteStep({{ $step->id }})">
                          <i class="ti ti-trash me-1"></i> {{ __('admin.delete') }}
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    @php
                      $today = date('Y-m-d');
                      $status = ($step->end_date < $today) ? 'completed' : (($step->start_date > $today) ? 'upcoming' : 'in progress');
                      $statusColor = ($status == 'completed') ? 'success' : (($status == 'upcoming') ? 'warning' : 'primary');
                    @endphp
                    <span class="badge bg-label-{{ $statusColor }} text-capitalize">
                      {{ $status == 'in_progress' ? __('admin.In_Progress') : ($status == 'upcoming' ? __('admin.Upcoming') : __('admin.completed')) }}
                    </span>
                  </div>
                </div>

                <!-- Card Body -->
                <div class="card-body">
                  <!-- Title -->
                  <h6 class="card-title mb-2">
                    <a href="/dashboard/final/{{ $step->id }}" class="text-heading text-decoration-none">
                      {{ $step->title }}
                    </a>
                  </h6>

                  <!-- Description -->
                  <p class="card-text text-muted small mb-3">
                    {{ Str::limit($step->description, 120) }}
                  </p>

                  <!-- Program & Organization -->
                  <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="{{ route('cycles.list', $step->program->id) }}"
                      class="badge bg-label-primary rounded-pill text-decoration-none px-2 py-1">
                      <i class="ti ti-rocket ti-xs me-1"></i>{{ Str::limit($step->program->title, 30) }}
                    </a>
                    <a href="{{ route('organizations.edit', $step->organization->id) }}"
                      class="badge bg-label-secondary rounded-pill text-decoration-none px-2 py-1">
                      <i class="ti ti-building ti-xs me-1"></i>{{ Str::limit($step->organization->name, 20) }}
                    </a>
                  </div>

                  <!-- Date Range -->
                  <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="d-flex align-items-center">
                      <i class="ti ti-calendar me-1 text-muted"></i>
                      <small class="text-muted">{{ $step->start_date }}</small>
                    </div>
                    <div class="text-muted">—</div>
                    <div class="d-flex align-items-center">
                      <i class="ti ti-calendar-check me-1 text-muted"></i>
                      <small class="text-muted">{{ $step->end_date }}</small>
                    </div>
                  </div>

                  <!-- Screening Type -->
                  <div class="mb-3">
                    <span data-bs-toggle="tooltip" data-bs-title="{{ __('admin.screening.' . str_replace('-', '', $step->screening_type) . '_description') }}" class="badge bg-label-info text-capitalize" title="{{ __('admin.screening.' . str_replace('-', '', $step->screening_type) . '_description') }}">
                      {{ __('admin.screening.' . str_replace('-', '', $step->screening_type)) }}
                    </span>
                  </div>

                  <!-- Assignments Summary -->
                  <div class="row g-2 mb-3">
                    <div class="col-6">
                      <div class="d-flex align-items-center">
                        <i class="ti ti-user-check me-1 text-muted"></i>
                        <div class="text-muted small">
                          <strong class="text-dark">{{ count($step->judges) }}</strong>
                          {{ count($step->judges) != 1 ? __('admin.Judgers') : __('admin.Judger') }}
                        </div>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="d-flex align-items-center">
                        <i class="ti ti-building-skyscraper me-1 text-muted"></i>
                        <div class="text-muted small">
                          <strong class="text-dark">{{ count($step->companies) }}</strong>
                          {{ count($step->companies) != 1 ? __('admin.Startups') : __('admin.Startup') }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Assessment Form -->
                  @if (isset($step->form->id))
                    <a href="{{ route('forms.render_view', $step->form->id) }}" class="d-flex align-items-center mb-0">
                      <i class="ti ti-file me-1 text-muted"></i>
                      <small class="text-muted">{{ Str::limit($step->form->title, 25) }}</small>
                    </a>
                  @endif
                </div>

                <!-- Card Footer -->
                <div class="card-footer text-center">
                  <div class="d-flex gap-2">
                    <a href="/dashboard/final/{{ $step->id }}" class="btn btn-primary flex-fill">
                      <i class="ti ti-eye-cog me-2"></i> {{ __('admin.screening.manage_step') }}
                    </a>
                    <a href="/dashboard/final/results/{{ $step->id }}" class="btn btn-outline-primary flex-fill">
                      <i class="ti ti-file-text me-2"></i> {{ __('admin.view_results') }}
                    </a>
                  </div>
                </div>
              </div>
            </div>
          @empty
            @include('includes.no-result')
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Step Form Modal -->
<div class="modal fade" id="step-form-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title mb-0" id="myModalLabel33">{{ __('admin.screening.modify_step') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="stepForm" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="">
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="form-label">{{ __('admin.screening.screening_type') }}</label>
            <select class="form-select" name="ScreeningSelect" id="screening-type" required>
              <option value="onetoall">{{ __('admin.screening.onetoall') }}</option>
              <option value="grouptoall">{{ __('admin.screening.grouptoall') }}</option>
              <option value="grouptogroup">{{ __('admin.screening.grouptogroup') }}</option>
            </select>
          </div>
          <input type="hidden" name="sid" id="step-id-value">
          <input type="hidden" name="type" value="8">
          <div class="form-group mb-3">
            <label class="form-label">{{ __('cycles.Title') }}</label>
            <input name="title" id="step-title" type="text" placeholder="{{ __('cycles.Title') }}"
              class="form-control" required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label">{{ __('admin.start_date') }}</label>
            <input id="step-from-date" name="fromDate" type="text" class="form-control date"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label">{{ __('admin.end_date') }}</label>
            <input id="step-to-date" name="toDate" type="text" class="form-control date"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label">{{ __('cycles.Description') }}</label>
            <textarea id="step-description" name="description" placeholder="{{ __('cycles.Description') }}"
              class="form-control" style="min-height: 80px; resize: vertical;"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
          </button>
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!--/ Step Form Modal -->
@endsection

@section('pagescripts')
<script>
  // Initialize date pickers
  $('.date').flatpickr({
    enableTime: false,
    dateFormat: 'Y-m-d'
  });

  // Toggle submit button disable state on form change
  $('#filter-form select, #filter-form input').on('change input', function () {
    $('#filter-form button[type="submit"]').prop('disabled', $(this).val() ? false : true);
  });


  $('.edit-step-btn').on('click', function () {
    const stepData = $(this).data('step');

    if (stepData) {
      $('#stepForm input[name="_token"]').val($('meta[name="csrf-token"]').attr('content'));
      $('#step-form-modal').modal('show');
      $('#step-id-value').val(stepData.id);
      $('#step-title').val(stepData.title);
      $('#step-from-date').val(stepData.start_date);
      $('#step-to-date').val(stepData.end_date);
      $('#step-description').val(stepData.description);

      if (stepData.screening_type) {
        if (stepData.screening_type == 'one-to-all') {
          $('#screening-type').val('onetoall');
        } else if (stepData.screening_type == 'group-to-all') {
          $('#screening-type').val('grouptoall');
        } else {
          $('#screening-type').val('grouptogroup');
        }
      }
    }
  });

  function handleDeleteStep(stepId) {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    Swal.fire({
      title: "{{ __('cycles.Are you sure, Delete current step and all its contents?') }}",
      text: "{{ __('admin.Recover') }}",
      icon: "warning",
      showCancelButton: true,
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-label-secondary',
      },
      confirmButtonText: "{{ __('admin.DYes') }}",
      cancelButtonText: "{{ __('admin.No') }}",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: "/dashboard/cycles/modify",
          data: {
            "stepid": stepId,
            "type": 6,
          },
          success: function(response) {
            $('[data-step-id="' + stepId + '"]').closest('.col-xl-4').remove();
            releaseSuccessAlert({
              title: '{{__('Deleted!')}}',
              message: '{{  $l == 'en' ? 'The screening step has been deleted successfully.' : 'تم حذف مرحلة التصفية بنجاح.' }}',
            });
          },
          error: function(error) {
            releaseFailureAlert({
              title: "{{ __('admin.Error') }}!",
              message: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
            });
          }
        });
      }
    });
  }
</script>
@stop
