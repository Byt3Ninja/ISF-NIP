@extends('layouts.admin')

@section('title', __('admin.surveys') )
@section('subTitle', __('admin.surveysList'))

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-header row">
      <!-- Navigation Tabs -->
      <nav class="nav-align-top mb-6">
        @php
          $tabs = [
            ['title' => __('admin.surveys'), 'url' => 'dashboard/surveys', 'icon' => 'device-imac-search'],
            ['title' => __('admin.surveys_results'), 'url' => 'dashboard/Totalsurveys', 'icon' => 'report-search'],
          ]
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
    </div>

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
                <a href="{{ route('organizations.edit', $survey?->organization?->id ?? 0) }}">
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
  </div>
</div>
@stop

@section('scripts')
<script>
  // Initialize DataTable with newest to oldest ordering
  $(document).ready(function() {
    var table = $('.surveys-table').DataTable({
      responsive: true,
      searching: true,
      lengthChange: true,
      pageLength: 10,
      order: [[ 4, "desc" ]],
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
@stop