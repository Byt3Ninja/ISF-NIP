@extends('layouts.admin')

@section('title', __('admin.surveys') )
@section('subTitle', __('admin.surveysList'))

@php
// Sort surveys by status priority: Pending > Completed
if ($_user->hasAnyRole('mentor|Registered')) {
  $surveys = $surveys->sortBy(function($survey) {
    $statusPriority = [
      'Pending' => 1,
      'Completed' => 2,
    ];
    return $statusPriority[$survey->status] ?? 999;
  });
}
@endphp

@section('content')
<div class="">
  <div class="">
    @if ($_user->hasAnyRole('Admin|organizationManager|subAdmin'))
      @php
        $tabs = [
          ['title' => __('admin.surveys'), 'route' => 'surveys.index', 'icon' => 'device-imac-search'],
          ['title' => __('admin.surveys_results'), 'route' => 'surveys.dashboard_count_view', 'icon' => 'report-search'],
        ]
      @endphp
      
      <x-navigation-pills :tabs="$tabs" />
    @endif

    @if( session()->has('success') )
    <div class="col-md-12 mb-6">
      <div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
        {{ __('admin.'. session()->get('success')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
    @endif
    @if( session()->has('error') )
    <div class="col-md-12 mb-6">
      <div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
        {{ __('admin.'.session()->get('error')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
    @endif
    @if( session()->has('msg') )
    <div class="col-md-12 mb-6">
      <div class="alert alert-danger mb-2" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span>
            {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
      </div>
    </div>
    @endif

    @if( count( $errors ) > 0 )
      <div class="col-md-12 mb-6">
        <div class="alert alert-danger">
          <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }} </li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    @if($_user->hasAnyRole('admin|subadmin|organizationManager'))
      <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center border-bottom">
          <h5 class="card-title mb-3 mb-md-0">{{__('admin.surveys')}}</h5>
          <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
              <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
              </button>
              <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
              </ul>
            </div>
            <a href="{{ route('surveys.create') }}" class="btn btn-primary"> 
              <i class="ti ti-plus me-2"></i>
              {{ __('admin.Add_New')}} 
            </a>
          </div>
        </div>

        <div class="card-datatable table-responsive datatable-basic">
          <table class="table surveys-table">
            <thead>
              <tr class="table-light">
                <th>#</th>
                <th >{{__('admin.surveyTitle')}}</th>
                <th>{{__('admin.Organization')}}</th>
                <th>{{__('admin.Is_Required')}}</th>
                <th>{{__('admin.Created At')}}</th>
                {{-- <th>{{ __('admin.Tags') }}</th> --}}
                <th>{{__('admin.Actions')}}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($surveys as $survey)
              <tr id="survey-row-{{ $survey->id }}">
                <td><a href="{{ route('surveys.edit', $survey->id) }}">{{ $survey->id }}</a></td>
                <td>{!! $survey->title !!}</td>
                <td>
                  <a href="{{ $_user->ableToAny(['organization.wizard.edit', 'organizations.edit']) ? route('organization.wizard.edit', $survey?->organization?->id ?? 0) . '?step=1' : 'javascript:void(0);' }}">
                    {{ $survey?->organization?->{'name_' . $l} }}
                  </a>
                </td>
                <td>
                  @if($survey->is_required == 1)
                    <span class="badge bg-label-success">{{ __('admin.Yes') }}</span>
                  @else
                    <span class="badge bg-label-secondary">{{ __('admin.No') }}</span>
                  @endif
                </td>
                <td>{{ $survey->created_at->format('Y-m-d') }}</td>
                {{-- <td>
                  @foreach($survey->questions as $question)
                  @foreach($question->tags as $tag)
                  <span class="badge bg-primary me-1">{{ $tag }}</span>
                  @endforeach
                  @endforeach
                </td> --}}
                <td>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light btn-icon" type="button" data-bs-toggle="dropdown"
                      aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a href="{{ route('surveys.questions_manage', ['survey' => $survey->id]) }}"
                        class="dropdown-item">
                        <i class="ti ti-list-check"></i> {{ __('admin.manageQuestions') }}
                      </a>
                      <a href="{{ route('surveys.edit', ['survey' => $survey->id]) }}" class="dropdown-item">
                        <i class="ti ti-edit text-info"></i> {{ __('admin.Edit') }}
                      </a>
                      <a id="deleteAction" data-id="{{ $survey->id }}"
                        data-url="{{ route('surveys.destroy', ['survey' => $survey->id]) }}"
                        href="{{ route('surveys.destroy', ['survey' => $survey->id]) }}" class="dropdown-item">
                        <i class="ti ti-trash text-danger"></i> {{ __('admin.Delete') }}
                      </a>
                    </div>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif

    @if($_user->hasAnyRole('mentor|Registered'))
      <div class="card">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0 text-capitalize">
            {{ __("admin.Surveys") }}
          </h5>
        </div>
        <div class="card-datatable table-responsive">
          <table class="table surveys-table">
            <thead class="table-light">
              @if ($_user->hasRole('mentor'))
              <th> {{ __('admin.Company') }} </th>
              @endif
              <th> {{ $_user->hasRole('mentor') ? __('admin.Founder') : __('admin.Mentor') }} </th>
              <th> {{ __('admin.Title') }} </th>
              <th> {{ __('admin.Status') }} </th>
              <th> {{ __('admin.Priority') }} </th>
              <th> {{ __('dashboard.Actions') }} </th>
            </thead>
            <tbody>
              @foreach( $surveys as $key => $survey )
              <tr>
                @if ($_user->hasRole('mentor'))
                  <td style="width: 150px;">
                    <a 
                      class="d-flex align-items-center" style="width: 100px;"
                      @if ($_user->ableTo('company.profile_view', $permissions) && isset($survey->user?->company?->id))
                        href="/dashboard/companyProfile/{{ $survey->user?->company?->id }}" 
                      @else
                        href="javascript:void(0)"
                      @endif
                    >
                      <img 
                        class="rounded-circle me-2 border bg-label-light flex-shrink-0"
                        src="{{ $survey?->user?->company?->logo_url }}"
                        onerror="this.onerror=null;this.src='/images/logo-placeholder.png';"
                        height="36"
                        width="36"
                        alt="logo" 
                      />
                      <small class="text-dark">{{ $survey->user?->company?->name }}</small>
                    </a>
                  </td>
                @endif
                <td style="width: 150px;">
                  <a 
                    class="d-flex align-items-center" style="width: 100px;"
                    @if ($_user->ableTo('mentors.profile_view', $permissions) && isset($survey->user?->id ))
                      href="/dashboard/show/UserProfile/{{ $survey->user?->id }}"
                    @else
                      href="javascript:void(0)"
                    @endif
                  >
                    <img 
                      class="rounded-circle me-2 border bg-label-light flex-shrink-0"
                      src="{{ $survey->user?->image_url }}"
                      onerror="this.onerror=null;this.src='/icons/account.svg';"
                      height="36"
                      width="36"
                      alt="avatar" 
                    />
                    <small class="text-dark">{{ $survey?->user?->name }}</small>
                  </a>
                </td>
                <td>
                  <div class="d-flex flex-column">
                    <span class="fw-medium">{{ $survey->title }}</span>
                    @if ($survey->session)
                      <small class="text-muted">
                        <i class="ti ti-calendar ti-xs"></i>
                        {{ __('admin.Session') }}: {{ FormatDate($survey->session->zoom_date, 'Y-m-d H:i') }}
                      </small>
                    @endif
                  </div>
                </td>
                <td> 
                  @if ($survey->status == 'Pending')
                    <div class="badge bg-label-warning">{{ __('admin.Pending') }}</div>
                  @elseif ($survey->status == 'Completed')
                    <div class="badge bg-label-success">{{ __('admin.Completed') }}</div>
                  @endif
                </td>
                <td>
                  @if ($survey->is_required)
                    <span class="badge bg-label-danger">{{ __('admin.mandatory') }}</span>
                  @else 
                    <span class="badge bg-label-secondary">{{ __('admin.optional') }}</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex align-items-center flex-wrap text-nowrap gap-2">
                    @if ($survey->status == 'Pending')
                      <a href="/dashboard/surveys/start/{{ $survey->id }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-player-play ti-xs me-1"></i>
                        {{ __('admin.start_survey') }}
                      </a>
                    @elseif ($survey->status == 'Completed')
                      <a href="/dashboard/surveys/start/{{ $survey->id }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-eye ti-xs me-1"></i>
                        {{ __('admin.view_survey') }}
                      </a>
                    @endif
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif

  </div>
</div>
@stop

@section('scripts')

@if($_user->hasAnyRole('admin|subadmin|organizationManager'))
<script>
  // Initialize DataTable with newest to oldest ordering
  $(document).ready(function() {
    var table = $('.surveys-table').DataTable({
      responsive: true,
      searching: true,
      lengthChange: true,
      pageLength: 10,
      order: [[ {{ $_user->hasRole('mentor') ? 1 : 2 }}, "desc" ]],
      dom: '<"row"<"col-12 col-md-9 d-flex flex-wrap flex-md-nowrap align-items-center"l><"col-12 col-md-3 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
        buttons: [
        {
          extend: 'excel',
          title: '{{ $l == "en" ? "Surveys List" : "قائمة الاستطلاعات" }}',
          exportOptions: {
            columns: [0, 1, 2, 3, 4],
          }
        },
        {
          extend: 'csv',
          title: '{{ $l == "en" ? "Surveys List" : "قائمة الاستطلاعات" }}',
          exportOptions: {
            columns: [0, 1, 2, 3, 4],
          }
        },
      ],
      columnDefs: [
        { orderable: false, targets: [0, 5] },
      ],
      initComplete: function() {
       	var table = this.api();
				
				// Create filter container after length selector
				var filterContainer = $('<div class="d-flex flex-wrap gap-2 mt-3 mt-md-0 ms-md-8"></div>');

				// Organization Filter
				var orgSelect = $('<select class="form-select form-select-sm" style="width: auto;"><option value="">{{ __("admin.All_Organizations") }}</option></select>');
				var organizations = [];
        var organizations = [];
        table.column(2).nodes().each(function(cell) {
          var text = $(cell).text().trim();
          if (text && !organizations.includes(text)) {
            organizations.push(text);
          }
        });
        organizations.sort();
        organizations.forEach(function(org) {
          var option = $('<option></option>').val(org).text(org);
          orgSelect.append(option);
        });
				filterContainer.append(orgSelect);

        // Is Required Filter
        var requiredSelect = $('<select class="form-select form-select-sm" style="width: auto;"><option value="">{{ __("admin.All_Options") }}</option><option value="{{ __("admin.Yes") }}">{{ __("admin.Yes") }}</option><option value="{{ __("admin.No") }}">{{ __("admin.No") }}</option></select>');
        filterContainer.append(requiredSelect);
        

        $('.dataTables_length').after(filterContainer);

        // Apply filters
				orgSelect.on('change', function() {
          table.column(2).search(this.value).draw();
				});

        requiredSelect.on('change', function() {
            table.column(3).search(this.value).draw();
        });
      },
      @if ($l === 'ar')
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
        },
      @endif
    });

    // Export button click handlers
    $('#exportExcel').on('click', function() {
      table.button('.buttons-excel').trigger();
    });

    $('#exportCsv').on('click', function() {
      table.button('.buttons-csv').trigger();
    });

    // Delete action handler
    $(document).on("click", "#deleteAction", function(e) {
      e.preventDefault();
      var url = $(this).data('url');
      var token = '{{ csrf_token() }}';
      var id = $(this).data('id');
    
      Swal.fire({
        title: "{{ __('admin.SureDelete') }}",
        text: "{{ __('admin.Recover') }}",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "{{ __('admin.DYes') }}",
        cancelButtonText: "{{ __('admin.No') }}",
        reverseButtons: true,
        customClass: {
          confirmButton: 'btn btn-danger me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      })
      .then((result) => {
        if (result.value) {
          $.post(url, {
              '_method': 'DELETE',
              '_token': token
            },
            function(response) {
              const row = table.row($('#survey-row-' + id));
              row.remove().draw();
              releaseSuccessAlert({
                title: "{{ __('admin.Deleted') }}!",
                message: "{{ __('admin.DeletedSuccessfully') }}.",
              });
            }
          ).fail(function(error) {
            releaseFailureAlert({
              title: "{{ __('admin.Error') }}!",
              message: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
            });
          });
        }
      })
    });
  });
</script>
@endif

@if($_user->hasAnyRole('mentor|Registered'))
<script>
  $(function() {
    // Initialize surveys DataTable
    $('.surveys-table')?.DataTable({
      processing: true,
      pageLength: 10,
      order: [], // Remove default ordering to keep PHP sort order
      dom: '<"row"' +
        '<"col-12 col-lg-10 d-flex flex-wrap align-items-center ps-0 ps-lg-3"l>' +
        '<"col-12 col-lg-2 dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 pe-0 pe-lg-2"f>' +
        '>' +
        't' +
        '<"row px-6"' +
          '<"col-sm-12 col-lg-6"i>' +
          '<"col-sm-12 col-lg-6 d-flex justify-content-end"p>' +
        '>',
      language: {
        url: '{{ $l }}' === "ar"
          ? "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
          : "https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json",	
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: '{{ __('admin.Search') }}...',
      },
    
      initComplete: function () {
        const api = this.api();
    
        const filterContainer = $(
          '<div class="d-flex flex-wrap flex-md-nowrap gap-3 ms-3"></div>'
        );
    
        $('.dataTables_length').after(filterContainer);
    
        const addDropdownFilter = (colIndex, id, placeholder, width = 150) => {
          api.columns(colIndex).every(function () {
            const column = this;
    
            const select = $(`
              <select id="${id}"
                class="form-select text-capitalize"
                style="min-width:${width}px">
                <option value="">${placeholder}</option>
              </select>
            `)
              .appendTo(filterContainer)
              .on('change', function () {
                let val = $(this).val();
    
                if (!val) {
                  column.search('', true, false).draw();
                  return;
                }
    
                // Normalize input
                val = val.trim().toLowerCase();
    
                // Escape regex safely
                val = $.fn.dataTable.util.escapeRegex(val);
    
                // Match exact word ignoring extra spaces
                column
                  .search(`^\\s*${val}\\s*$`, true, false)
                  .draw();
              });
    
            // Populate unique values (clean text only)
            column
              .data()
              .unique()
              .sort()
              .each(function (d) {
                const text = $('<div>')
                  .html(d)
                  .text()
                  .replace(/\s+/g, ' ')
                  .trim();
    
                if (text) {
                  select.append(
                    `<option value="${text.toLowerCase()}">${text}</option>`
                  );
                }
              });
          });
        };
    
        // Company Filter
        @if ($_user->hasRole('mentor'))
          addDropdownFilter(
            0,
            'companyFilter',
            '{{ __("cycles.All Startups") }}',
            200
          );
        @endif

        // Status Filter
        addDropdownFilter(
          @if ($_user->hasRole('mentor')) 3 @else 2 @endif,
          'statusFilter',
          '{{ __("cycles.All Status") }}'
        );

        // Priority (Mandatory/Optional) Filter
        api.columns(@if ($_user->hasRole('mentor')) 4 @else 3 @endif).every(function () {
          const column = this;
    
          const select = $(`
            <select id="priorityFilter"
              class="form-select text-capitalize"
              style="min-width:170px">
              <option value="">
                {{ __("admin.All_Priorities") }}
              </option>
              <option value="{{ __('admin.mandatory') }}">
                {{ __('admin.mandatory') }}
              </option>

              <option value="{{ __('admin.optional') }}">
                {{ __('admin.optional') }}
              </option>
            </select>
          `)
          .appendTo(filterContainer)
          .on('change', function () {
            const val = $(this).val();
            column.search(val ? val : '', true, false).draw();
          });
        });
    
        setTimeout(() => {
          $('.dataTables_filter .form-control').removeClass('form-control-sm');
          $('.dataTables_length .form-select').removeClass('form-select-sm');
        }, 300);
      }
    });
  });
</script>
@endif

@stop
