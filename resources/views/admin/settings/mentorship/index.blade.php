@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/form-validation/form-validation.css" />
@stop

@section('content')
<div>
  {{-- Alert Messages --}}
  @if (session()->has('message'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ __('admin.' . session()->get('message')) }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if (session()->has('message2'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ __('admin.' . session()->get('message2')) }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  {{-- Cycle Selection Card --}}
  <div class="card mb-6">
    <div class="card-header border-bottom mb-6">
      <h5 class="card-title mb-0">{{ __('settings.navigation.mentorship') }}</h5>
    </div>

    <div class="card-body overflow-x-hidden">
      <div class="row align-items-md-center gy-3">

        {{-- Select Cycle --}}
        <div class="col-12 col-lg-6">
          <div class="row align-items-lg-center g-2">
            <div class="col-12 col-lg-auto">
              <label class="form-label fs-6 mb-0">
                {{ __('cycles.Select_Cycle') }}:
              </label>
            </div>
            <div class="col">
              <select id="cstep" class="form-select select2"
                onchange="window.location.href='/dashboard/mentorship/view/'+this.value">
                @forelse ($cycles as $cycle)
                <option value="{{ $cycle->id }}" {{ $cycle->id == $id ? 'selected' : '' }}>
                  {{ $cycle->title }}
                  ({{ __('cycles.from') }} {{ $cycle->start?->format('d-m-Y') ?? '-' }}
                  {{ __('cycles.to') }} {{ $cycle->end?->format('d-m-Y') ?? '-' }})
                  - {{ $cycle->organization?->name }}
                </option>
                @empty
                <option disabled>
                  {{ __('cycles.No records saved') }} - {{ __('cycles.Manage Cycle') }}
                </option>
                @endforelse
              </select>
            </div>
          </div>
        </div>

        {{-- Action Buttons --}}
        <div class="col-12 col-lg-6 text-lg-end">
          <div class="row g-2 justify-content-lg-end">
            <div class="col-12 col-sm-auto">
              <a class="btn btn-primary w-100" href="/dashboard/cycles/{{ $id }}">
                <i class="ti ti-adjustments me-1"></i>
                {{ __('cycles.Manage Cycle') }}
              </a>
            </div>

            <div class="col-12 col-sm-auto">
              <a class="btn btn-label-primary w-100" href="/dashboard/mentorship/view/{{ $id }}">
                <i class="ti ti-settings me-1"></i>
                {{ __('cycles.Manage sessions') }}
              </a>
            </div>

            <div class="col-12 col-sm-auto">
              <a class="btn btn-outline-primary w-100" href="/dashboard/mentorship/{{ $id }}/surveys">
                <i class="ti ti-question-mark me-1"></i>
                {{ __('cycles.Manage Surveys') }}
              </a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Mentors Section --}}
  <div class="card mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center mb-6">
      <h5 class="card-title mb-0">{{ __('cycles.Mentors') }}</h5>
      <a class="btn btn-primary" href="/dashboard/mentorship/assign/{{ $id }}">
        <i class="ti ti-user me-2"></i>{{ __('cycles.Manage Cycle mentors') }}
      </a>
    </div>

    <div class="card-body">
      @forelse ($mentors as $mentor)
      @if ($loop->first)
      <div class="d-flex flex-wrap gap-2 mb-3">
        @endif
        <label class="btn btn-outline-primary" for="mentor{{ $mentor->user_id }}">
          <div class="d-flex align-items-center gap-2">
            <img class="rounded-circle" src="{{ $mentor->user?->image_url }}"
              alt="{{ $mentor->user?->{'first_name_' . $l} }}" height="24" width="24" />
            <span>{{ $mentor->user?->id }} - {{ $mentor->user?->{'first_name_' . $l} }} {{ $mentor->user?->{'last_name_'
              . $l} }}</span>
            <input type="radio" value="{{ $mentor->user_id }}" id="mentor{{ $mentor->user_id }}" name="mentor"
              class="mentor-radio"
              data-name="{{ $mentor->user?->{'first_name_' . $l} }} {{ $mentor->user?->{'last_name_' . $l} }}"
              data-rating="{{ $mentor->mrating() }}" data-id="{{ $mentor->user_id }}" />
          </div>
        </label>
        @if ($loop->last)
      </div>
      @endif
      @empty
      <p class="text-muted mb-0">
        {{ __('cycles.Add mentors to this cycle from') }}
        <a href="/dashboard/mentorship/assign/{{ $id }}">{{ __('cycles.here') }}</a>.
      </p>
      @endforelse
    </div>
  </div>

  {{-- Registered Users Section --}}
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">{{ __('cycles.Cycle Registered Users') }}</h5>
    </div>

    {{-- Users Table --}}
    <div class="card-datatable table-responsive">
      <table class="table" id="usersTable">
        <thead class="table-light">
          <tr>
            <th>{{ __('cycles.Company') }}</th>
            <th>{{ __('cycles.User/Title') }}</th>
            <th>{{ __('cycles.Session') }}</th>
            <th>{{ __('cycles.Current Step') }}</th>
            <th class="text-center">{{ __('cycles.Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($companies as $company)
          <tr>
            <td data-search="{{ $company->name }}">
              <div class="d-flex align-items-center">
                <a href="/dashboard/companyProfile/{{ $company->id }}"
                  class="d-flex align-items-center text-decoration-none">
                  <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="rounded-circle me-2"
                    height="40" width="40">
                  <span>{{ $company->name }}</span>
                </a>
              </div>
            </td>
            <td data-search="{{ $company->user?->{'first_name_' . $l} }} {{ $company->user?->{'last_name_' . $l} }}">
              <div class="d-flex align-items-center">
                <a href="/dashboard/users/{{ $company->user?->id }}/edit"
                  class="d-flex align-items-center text-decoration-none">
                  <img src="/191014/{{ $company->user?->img_url ?? '/icons/accound.svg' }}"
                    onerror="this.onerror=null; this.src='/icons/account.svg';"
                    alt="{{ $company->user?->{'first_name_' . $l} }}" class="rounded-circle me-2" height="40"
                    width="40">
                  <div>
                    <h6 class="mb-0">{{ $company->user?->{'first_name_' . $l} }} {{ $company->user?->{'last_name_' .
                      $l} }}</h6>
                    <small class="text-muted">{{ $company->user?->{'title_' . $l} }}</small>
                  </div>
                </a>
              </div>
            </td>
            <td>
              @if ($company->user?->mentorship?->count() > 0)
              <div class="d-flex flex-wrap gap-1" style="max-width: 300px;">
                @foreach ($company->user->mentorship as $session)
                @if ($session->approved == 'Yes')
                <span class="badge bg-label-primary">M#{{ $session->mentor_id }} ({{ $session->zoom_date }})</span>
                @endif
                @endforeach
              </div>
              @else
              <span class="badge bg-label-secondary">{{ __('cycles.No sessions') }}</span>
              @endif
            </td>
            <td data-search="{{ $company->stepdata?->title }}">
              <span class="text-nowrap">{{ $company->stepdata?->title }}</span>
            </td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-label-primary btn-icon rounded-pill add-session-btn"
                data-user-id="{{ $company->user?->id }}" data-org-id="{{ $company->cycledata?->organization_id }}"
                data-cycle-id="{{ $company->cycle }}">
                <i class="ti ti-plus"></i>
              </button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@include('partials.book-session-modal')
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
@stop

@section('pagescripts')
<script>
$(function () {
  // Initialize DataTable
  const dtConfig = {
    responsive: true,
    searching: true,
    lengthChange: true,
    pageLength: 25,
    paging: true,
    info: true,
    order: [[0, 'asc']],
    dom:
      '<"row"' +
      '<"col-12 col-lg-9 d-flex flex-wrap align-items-center ps-0 ps-lg-6"l>' +
      '<"col-12 col-lg-3 dt-action-buttons d-flex justify-content-lg-end pe-0 pe-lg-6"f>' +
      '>' +
      't' +
      '<"row px-6"' +
      '<"col-lg-6"i>' +
      '<"col-lg-6 d-flex justify-content-end"p>' +
      '>',
    columnDefs: [
      { orderable: false, targets: [2, 4] }
    ],
    language: {
      url:
        '{{ app()->getLocale() }}' === 'ar'
          ? 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
          : 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json',
      sLengthMenu: '_MENU_',
      search: '',
      searchPlaceholder: '{{ __('admin.Search') }}...'
    },
    initComplete() {
      const api = this.api();
      const container = $('<div class="d-flex gap-3 ms-3"></div>')
        .insertAfter('.dataTables_length');

      const addFilter = (col, id, label, width = 180) => {

        const select = $(`
          <select id="${id}" class="form-select filter-select2">
            <option value="">${label}</option>
          </select>
        `)
          .appendTo(container)
          .on('change', function () {
            const val = $.fn.dataTable.util.escapeRegex($(this).val());

            api
              .column(col)
              .search(val ? `^${val}$` : '', true, false)
              .draw();
          });

        // 🔥 IMPORTANT: use search data instead of raw HTML
        const uniqueValues = new Set();

        api
          .column(col, { search: 'applied' })
          .nodes()
          .each(function (cell) {
            const value = $(cell).data('search') || $(cell).text();
            const clean = value.replace(/\s+/g, ' ').trim();

            if (clean) {
              uniqueValues.add(clean);
            }
          });

        Array.from(uniqueValues)
          .sort((a, b) => a.localeCompare(b))
          .forEach(value => {
            select.append(
              `<option value="${value}">${value}</option>`
            );
          });
      };

      // Company filter
      addFilter(0, 'startupFilter', '@lang('admin.all_startups')');

      // Founder filter
      addFilter(1, 'founderFilter', '@lang('admin.all_founders')');

      // Step filter
      addFilter(3, 'stepFilter', '@lang('admin.All_Steps')');

      setTimeout(() => {
        $('.form-control-sm, .form-select-sm')
          .removeClass('form-control-sm form-select-sm');
        $('#usersTable_wrapper .filter-select2, #usersTable_wrapper .dataTables_length select').each(function () {
          const $this = $(this);
          if (!$this.hasClass('select2-hidden-accessible')) {
            const isLength = $this.closest('.dataTables_length').length > 0;
            $this.select2({
              dropdownParent: $this.parent(),
              width: 'resolve',
              minimumResultsForSearch: Infinity,
              allowClear: false
            });
            $this.next('.select2-container').css({
              'min-width': isLength ? '70px' : '150px',
              'max-width': isLength ? '90px' : '200px'
            });
          }
        });
      }, 300);
    }
  };

  $(document).ready(function() {
    if ($('#cstep').length && !$('#cstep').hasClass('select2-hidden-accessible')) {
      $('#cstep').select2({
        width: '100%'
      });
    }
  });

  const table = $('#usersTable').DataTable(dtConfig);

  // Add Session Button Handler - Updated to use fetchSlots from the new modal
  $('.add-session-btn').on('click', function() {
    const selectedMentor = $('input[name="mentor"]:checked');
    
    if (!selectedMentor.length) {
      alert("{{ __('cycles.Please select mentor first, and try again') }}");
      return;
    }

    const userId = $(this).data('user-id');
    const orgId = $(this).data('org-id');
    const cycleId = $(this).data('cycle-id');
    const mentorId = selectedMentor.data('id');
    
    // Call the fetchSlots function from the new booking modal
    if (typeof fetchSlots === 'function') {
      fetchSlots(mentorId, cycleId, orgId, userId);
    } else {
      console.error('fetchSlots function not found. Make sure partials.book-session-modal is included.');
    }
  });
});
</script>
@stop
