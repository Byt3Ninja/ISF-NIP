@extends('layouts.admin')

@section('content')
<div class="">
	<div class="">
		@php
			$tabs = [
				['title' => __('admin.quizzes'), 'url' => 'dashboard/quizzes', 'icon' => 'clock-bolt'],
				['title' => __('admin.quizzes_results'), 'url' => 'dashboard/quizResults', 'icon' => 'checklist'],
			]
		@endphp
		<x-navigation-pills :tabs="$tabs" />

		@if(session()->has('msg'))
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-3 shadow-sm"
					role="alert">
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					<div class="d-flex align-items-center">
						<i class="ti ti-alert-triangle me-3 ti-md"></i>
						<span> {{ session()->get('msg') }} {{ Session::forget('msg') }} </span>
					</div>
				</div>
			</div>
		</div>
		@endif

		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center border-bottom">
				<h5 class="card-title mb-3 mb-md-0">{{ __('admin.quizzes_results') }}</h4>
				<div class="dropdown">
					<button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
						<i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
					</button>
					<ul class="dropdown-menu" aria-labelledby="exportDropdown">
						<li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
						<li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
					</ul>
				</div>
			</div>
			<div class="card-datatable table-responsive datatable-basic">
				<table class="table quizzes-results-table">
					<thead class="table-light">
						<tr>
							<th class="text-center" style="width: 5%;"> # </th>
							<th class="text-center" style="width: 10%;"> {{ __('admin.Name') }} </th>
							<th class="text-center"> {{ __('admin.Organization') }} </th>
							<th class="text-center"> {{ __('admin.Training') }} </th>
							<th class="text-center" style="width: 10%;"> {{ __('admin.Company') }} </th>
							<th class="text-center" style="width: 10%;"> {{ __('admin.User') }} </th>
							<th class="text-center" style="width: 10%;"> {{ __('admin.quizScore') }} </th>
							<th class="text-center" style="width: 12%;"> {{ __('admin.quizStatus') }} </th>
							<th> {{ __('admin.quizStartedAt') }} </th>
							<th class="text-center"> {{ __('admin.quizTimeTook') }} </th>
							<th class="text-center" style="width: 18%;"> {{ __('admin.Actions') }} </th>
						</tr>
					</thead>
					<tbody>

						@foreach($quizzesResults as $index => $quizzesResult)
						<tr>
							<td class="text-center fw-bold"> {{ $index + 1 }} </td>
							<td class="text-center">{{ $quizzesResult->q_quiz_title}}</td>
							<td class="text-center">{{ $quizzesResult->{'o_org_name_' . $l} }}</td>
							<td class="text-center">{{ $quizzesResult->t_training_title }}</td>
							<td class="text-center">
								<a class="text-dark d-flex align-items-center gap-2"
									href="/dashboard/companyProfile/{{ $quizzesResult->c_company_id }}">
									<img src="{{ $quizzesResult->c_company_logo }}" alt="avatar" class="rounded-circle" height="35"
										width="35" style="border: 2px solid #ddd;"
										onerror="this.onerror=null;this.src='/images/logo-placeholder.png';">
									<span class="fw-medium"> {{ $quizzesResult?->{'c_company_name_' . $l} }}</span>
								</a>
							</td>
							<td class="text-center">
								<a class="text-dark d-flex align-items-center gap-2"
									href="/dashboard/Profile/{{ $quizzesResult->u_user_id }}">
									<img src="{{ $quizzesResult->u_user_img_url }}" alt="avatar" class="rounded-circle" height="35"
										width="35" style="border: 2px solid #ddd;"
										onerror="this.onerror=null;this.src='/images/logo-placeholder.png';">
									<span class="fw-medium"> {{ $quizzesResult->{'u_user_first_name_' . $l} . ' ' .
										$quizzesResult->{'u_user_last_name_' . $l} }}</span>
								</a>
							</td>
							<td class="text-center">
								<span class="fw-bold fs-6 {{ $quizzesResult->qr_result_score >= 50 ? 'text-success' : 'text-danger' }}">
									{{ $quizzesResult->qr_result_score }}%
								</span>
							</td>
							<td class="text-center">
								@if ($quizzesResult->qr_result_status == 1)
									<span class="badge bg-label-success">{{ __('admin.Completed') }}</span>
								@else
									<span class="badge bg-label-warning">{{ __('admin.Pending') }}</span>
								@endif
							</td>
							<td>{{ $quizzesResult->qr_result_started_at }}</td>
							<td class="text-center">{{ $quizzesResult->qr_result_ended_at }}</td>

							<td class="text-center">
								<a href="quizResults/{{ $quizzesResult->qr_result_id }}"
									class="btn btn-label-primary btn-sm">
									<i class="ti ti-eye me-1"></i>
									{{ __('admin.quizViewResults') }}
								</a>
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
<script type="text/javascript">
	// Initialize DataTable with newest to oldest ordering
	$(document).ready(function() {
		var table = $('.quizzes-results-table').DataTable({
			responsive: true,
			searching: true,
			lengthChange: true,
			pageLength: 10,
			order: [[ 5, "desc" ]],
      language: {
        url:
          document.dir === 'rtl'
            ? 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
            : 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json',
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: '{{ __('admin.Search') }}...',
      },
			dom: '<"row"<"col-12 col-md-10 d-flex flex-wrap flex-md-nowrap align-items-center"l><"col-12 col-md-2 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
				buttons: [
				{
					extend: 'excel',
					title: '{{ $l == "en" ? "Quizzes Results" : "نتائج الاختبارات" }}',
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
					}
				},
				{
					extend: 'csv',
					title: '{{ $l == "en" ? "Quizzes Results" : "نتائج الاختبارات" }}',
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
					}
				},
			],
			initComplete: function() {
				var table = this.api();
				
				// Create filter container after length selector
				var filterContainer = $('<div class="d-flex flex-wrap gap-2 mt-3 mt-md-0 ms-md-3"></div>');

				// Organization Filter
				var orgSelect = $('<select class="form-select form-select-sm" style="width: auto; max-width: 120px;"><option value="">{{ __("admin.All_Organizations") }}</option></select>');
				var organizations = [];
				table.column(2).data().unique().sort().each(function(d) {
						if (d && !organizations.includes(d)) {
								organizations.push(d);
								orgSelect.append('<option value="' + d + '">' + d + '</option>');
						}
				});
				filterContainer.append(orgSelect);

				// Training Filter
				var trainingSelect = $('<select class="form-select form-select-sm" style="width: auto; max-width: 120px;"><option value="">{{ __("admin.All_Trainings") }}</option></select>');
				var trainings = [];
				table.column(3).data().unique().sort().each(function(d) {
						if (d && !trainings.includes(d)) {
								trainings.push(d);
								trainingSelect.append('<option value="' + d + '">' + d + '</option>');
						}
				});
				filterContainer.append(trainingSelect);

				// Startup/Company Filter
				var companySelect = $('<select class="form-select form-select-sm" style="width: auto; max-width: 120px;"><option value="">{{ __("cycles.All Startups") }}</option></select>');
				var companies = [];
				table.column(4).data().unique().sort().each(function(d) {
						var $cell = $(d);
						var companyName = $cell.find('.fw-medium').text().trim();
						if (companyName && !companies.includes(companyName)) {
								companies.push(companyName);
								companySelect.append('<option value="' + companyName + '">' + companyName + '</option>');
						}
				});
				filterContainer.append(companySelect);

				// User Filter
				var userSelect = $('<select class="form-select form-select-sm" style="width: auto; max-width: 120px;"><option value="">{{ __("admin.List All") }}</option></select>');
				var users = [];
				table.column(5).data().unique().sort().each(function(d) {
						var $cell = $(d);
						var userName = $cell.find('.fw-medium').text().trim();
						if (userName && !users.includes(userName)) {
								users.push(userName);
								userSelect.append('<option value="' + userName + '">' + userName + '</option>');
						}
				});
				filterContainer.append(userSelect);

				// Status Filter
				var statusSelect = $('<select class="form-select form-select-sm" style="width: auto; max-width: 120px;"><option value="">{{ __("admin.All_statuses") }}</option><option value="{{ __("admin.Completed") }}">{{ __("admin.Completed") }}</option><option value="{{ __("admin.Pending") }}">{{ __("admin.Pending") }}</option></select>');
				filterContainer.append(statusSelect);

				// Append filter container after length selector
				$('.dataTables_length').after(filterContainer);

				// Apply filters
				orgSelect.on('change', function() {
						table.column(2).search(this.value).draw();
				});

				trainingSelect.on('change', function() {
						table.column(3).search(this.value).draw();
				});

				companySelect.on('change', function() {
						table.column(4).search(this.value ? '^\\s*' + $.fn.dataTable.util.escapeRegex(this.value) + '\\s*$' : '', true, false).draw();
				});

				userSelect.on('change', function() {
						table.column(5).search(this.value ? '^\\s*' + $.fn.dataTable.util.escapeRegex(this.value) + '\\s*$' : '', true, false).draw();
				});

				statusSelect.on('change', function() {
						table.column(7).search(this.value).draw();
				});
        
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
        
        const $wrapper = $(this.api().table().container());
        $wrapper.find('.form-select, .dataTables_length select').each(function () {
          const $this = $(this);
          if ($this.hasClass('select2-hidden-accessible')) return;

          const isLength = $this.closest('.dataTables_length').length > 0;

          $this.wrap('<div class="position-relative' + (isLength ? ' d-inline-block' : '') + '"></div>');

          $this.select2({
            dropdownParent: $this.parent(),
            width: isLength ? '80px' : '100%',
            minimumResultsForSearch: Infinity,
          });

          if (!isLength) {
            $this.next('.select2-container').css({
              'min-width': '150px',
              'max-width': '200px'
            });
          }
        });
			},
		});

		// Export button click handlers
		$('#exportExcel').on('click', function() {
			table.button('.buttons-excel').trigger();
		});

		$('#exportCsv').on('click', function() {
			table.button('.buttons-csv').trigger();
		});
	});
</script>
@stop
