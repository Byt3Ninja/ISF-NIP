@extends('layouts.admin')
@section('title', __('admin.surveys') )
@section('index_url', route('surveys.index') )
@section('sub_index_url', route('surveys.edit', ['survey' => $survey->id]) )
@section('subTitle', __('admin.manageQuestions'))

@section('content')
<div class="">
	<div class="">
		<div class="content-body">
			<div class="row">
				<div class="col-md-12">
					<div class="card">
						<div class="card-header border-bottom mb-6">
							<div class="row align-items-center">
								<div class="col-md-6">
									<div class="d-flex align-items-center gap-2">
										<h5 class="card-title mb-0">{{__('admin.surveyResults')}}</h5>
									</div>
								</div>
								<div class="col-md-6 text-end">
									<!-- Export Dropdown Button -->
									<div class="dropdown d-inline-block">
										<button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="ti ti-file-export me-2"></i> {{ __('admin.Export') }}
										</button>
										<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
											<li>
												<a class="dropdown-item" href="#" id="exportCSV">
													<i class="ti ti-file-text me-1"></i>{{ __('admin.exportCSV') }}
												</a>
											</li>
											<li>
												<a class="dropdown-item" href="#" id="exportExcel">
													<i class="ti ti-file-spreadsheet me-1"></i>{{ __('admin.exportExcel') }}
												</a>
											</li>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<div class="card-datatable">
							<div class="row mb-1 px-2">
								<div class="col-md-3">
									<select class="form-select form-select-sm" id="statusFilter">
										<option value="">{{__('admin.allStatus')}}</option>
										<option value="answered">{{__('admin.answered')}}</option>
										<option value="not_answered">{{__('admin.notAnswered')}}</option>
									</select>
								</div>
								<div class="col-md-3">
									<select class="form-select form-select-sm" id="mentorFilter">
										<option value="">{{__('admin.allMentors')}}</option>
									</select>
								</div>
								<div class="col-md-3">
									<select class="form-select form-select-sm" id="programFilter">
										<option value="">{{__('admin.allPrograms')}}</option>
									</select>
								</div>
								<div class="col-12 col-md-3 d-none align-items-end">
									<button id="clear-filters" class="btn btn-sm btn-outline-secondary">{{ __('admin.Clear') }}</button>
								</div>
							</div>
							<table class="table table-hover" id="surveyResultsTable">
								<thead class="table-light">
									<tr>
										<th class="text-center" style="width: 5%;">#</th>
										<th>{{__('admin.user')}}</th>
										<th>{{__('admin.Startup')}}</th>
										<th>{{__('admin.Cycle informations')}}</th>
										<th>{{__('admin.surveyAbout')}}</th>
										<th class="text-center" style="width: 10%;">{{__('admin.Status')}}</th>
										<th>{{__('admin.submitDate')}}</th>
									</tr>
								</thead>
								<tbody>
									@foreach( $responses as $index => $response )
									<tr data-status="{{ is_null( $response->responses ) || count( $response->responses ) == 0 ? 'not_answered' : 'answered' }}"
										data-mentor="{{ $response->triggered_on == 'mentorship' && $response->triggered_by == 'session' ? $response->mentorship_session?->mentor?->name : '' }}"
										data-program="{{ $response->cycle->title }}">
										<td class="text-center">{{ $index + 1 }}</td>
										<td>
											<a target="_blank" class="text-dark d-flex align-items-center gap-2">
												<img src="{{ $response->user->image_url }}" alt="avatar" class="rounded-circle"
													height="35" width="35" style="border: 2px solid #ddd;"
														onerror="this.onerror=null;this.src='/icons/account.svg';"
													>
												<div class="media-body">
													<h6 class="media-heading mb-0" style="font-size: 14px;">{{ $response?->user?->name }}
													</h6>
												</div>
											</a>
										</td>
										<td>
											<a class="text-dark d-flex align-items-center gap-2"
												href="/dashboard/companyProfile/{{ $response?->user?->company?->id }}">
												<img src="{{ $response?->user->company?->logo_url }}" alt="avatar"
													class="rounded-circle" height="35" width="35" style="border: 2px solid #ddd;"
													onerror="this.onerror=null;this.src='/images/logo-placeholder.png';"
													>
												<span class="fw-medium"> {{ $response?->user?->company?->{'name_'.$l} }}</span>
											</a>
										</td>
										<td>
											<a target="_blank" href="/dashboard/programlist/show/{{ $response->cycle->id }}/"> {{
												$response->cycle->title }} </a>
										</td>
										<td>
											<div class="d-flex flex-column">
												<div class="mb-1 d-flex flex-column">
													<span class="fw-semibold">
														@if($response->triggered_on == 'mentorship')
															<i class="ti ti-bulb text-warning me-2"></i>
														@elseif($response->triggered_on == 'training')
															<i class="ti ti-presentation text-info me-2"></i>
														@endif
														{{ __('dashboard.surveyAbout') }}
													</span>
													<span class="badge {{ $response->triggered_by == 'session' ? 'bg-label-primary' : 'bg-label-warning' }}">
														{{ __('dashboard.surveyTriggerBy' . $target . ucfirst($response->triggered_by)) }}
													</span>
												</div>
												
												@php
												$middleware = collect([]);
												if($response->triggered_on == 'mentorship') {
													if($response->triggered_by == 'session') { 
														$middleware = $response->mentorship_session;
													}
													if($response->triggered_by == 'due') { 
														$middleware = $response->mentorship_step; 
													}
												} elseif($response->triggered_on == 'training') {
													if($response->triggered_by == 'session') { 
														$middleware = $response->training_session; 
													}
													if($response->triggered_by == 'due') { 
														$middleware = $response->training_step; 
													}
													if($response->triggered_by == 'quiz') { 
														$middleware = $response->training_quiz; 
													}
												}
												@endphp
												
												<div class="text-muted small">
													@if($response->triggered_by == 'due')
														<span class="fw-medium">{{ __('dashboard.surveyStepName' . ucFirst($response->triggered_on)) }}</span>
													@endif
													
													@if($response->triggered_on == 'mentorship')
														@if($response->triggered_by == 'session')
															{{ __('dashboard.with') }}
															@php $mentor = $middleware?->mentor; @endphp
															<span class="fw-medium text-primary">{{ $mentor?->name }}</span>
														@elseif($response->triggered_by == 'due')
															- <span class="fw-medium">{{ $middleware->title }}</span>
														@else
															-
														@endif
													@elseif($response->triggered_on == 'training')
														@if($response->triggered_by == 'session')
															@if($middleware)
																<span class="text-dark">{{ '(' . __('cycles.' . $middleware->type) . ')' }}</span>
																<span class="fw-medium">{{ $middleware->title }}</span>
															@else
																<span class="text-muted">-</span>
															@endif
														@elseif($response->triggered_by == 'due' || $response->triggered_by == 'quiz')
															- <span class="fw-medium">{{ $middleware->title }}</span>
														@else
															-
														@endif
													@endif
												</div>
											</div>
										</td>
										<td class="text-center">
											@if( is_null( $response->responses ) || count( $response->responses ) == 0 )
											<button class="btn btn-sm btn-label-danger text-nowrap"> {{ __('admin.notAnswered') }} </button>
											@else
											<button data-id="{{ $response->id }}" class="btn btn-sm btn-label-success text-nowrap">
												{{ __('admin.answered') }} </button>
											@endif
										</td>
										<td class="text-center">
											{{ $response->created_at}}
										</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Response Details Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title" id="responseModalLabel">
					<i class="ti ti-chart-pie me-2"></i>{{__('admin.surveyResponses')}}
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div id="responseContent">
					<!-- Response content will be loaded here -->
				</div>
			</div>
		</div>
	</div>
</div>
@stop

@section('pagescripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#surveyResultsTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[6, 'desc']], // Sort by submit date (descending)
        language: {
            @if($l == 'ar')
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
            @else
            search: "{{ __('admin.Search') }}:",
            lengthMenu: "{{ __('admin.show') }} _MENU_ {{ __('admin.Entries') }}",
            info: "{{ __('admin.showing') }} _START_ {{ __('admin.To') }} _END_ {{ __('admin.of') }} _TOTAL_ {{ __('admin.Entries') }}",
            infoEmpty: "{{ __('admin.showing') }} 0 {{ __('admin.To') }} 0 {{ __('admin.of') }} 0 {{ __('admin.Entries') }}",
            infoFiltered: "({{ __('admin.filteredFrom') }} _MAX_ {{ __('admin.totalEntries') }})",
            paginate: {
                first: "{{ __('admin.First') }}",
                last: "{{ __('admin.Last') }}",
                next: "{{ __('admin.Next') }}",
                previous: "{{ __('admin.Previous') }}"
            },
            emptyTable: "{{ __('admin.noDataAvailable') }}",
            zeroRecords: "{{ __('admin.noMatchingRecords') }}"
            @endif
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        drawCallback: function() {
            // Re-initialize tooltips and other UI elements after table redraw
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Populate filter dropdowns with unique values
    populateFilters();

		// Status Filter
		$('#statusFilter').on('change', function() {
			const value = $(this).val();
			table.draw();
		});

		// Custom search function for status filter
		$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
			const statusFilter = $('#statusFilter').val();
			if (statusFilter === '') {
				return true; // Show all rows when no filter is selected
			}
			
			const row = table.row(dataIndex).node();
			const status = $(row).data('status');
			
			return status === statusFilter;
		});

    // Mentor Filter
    $('#mentorFilter').on('change', function() {
        const value = $(this).val();
        table.column(4).search(value, true, false).draw();
    });

    // Program Filter  
    $('#programFilter').on('change', function() {
        const value = $(this).val();
        table.column(3).search(value, true, false).draw();
    });

    // Global Search
    $('#globalSearch').on('keyup', function() {
        table.search($(this).val()).draw();
    });

    // Export CSV
    $('#exportCSV').on('click', function() {
        exportTableToCSV('survey_results.csv');
    });

    // Export Excel
    $('#exportExcel').on('click', function() {
        exportTableToExcel('survey_results.xlsx');
    });

    // Open response modal
    $(document).on('click', '.open-responses', function() {
        const responseId = $(this).data('id');
        loadResponseDetails(responseId);
    });

    function populateFilters() {
        const mentors = new Set();
        const programs = new Set();

        // Extract unique mentors and programs from table data
        table.rows().every(function() {
            const rowData = this.node();
            const mentor = $(rowData).data('mentor');
            const program = $(rowData).data('program');
            
            if (mentor && mentor.trim() !== '') {
                mentors.add(mentor);
            }
            if (program && program.trim() !== '') {
                programs.add(program);
            }
        });

        // Populate mentor filter
        const mentorSelect = $('#mentorFilter');
        mentors.forEach(mentor => {
            mentorSelect.append(`<option value="${mentor}">${mentor}</option>`);
        });

        // Populate program filter
        const programSelect = $('#programFilter');
        programs.forEach(program => {
            programSelect.append(`<option value="${program}">${program}</option>`);
        });
    }

    function exportTableToCSV(filename) {
        const csv = [];
        const rows = document.querySelectorAll("#surveyResultsTable tr:not(.child)");
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll("td, th");
            
            for (let j = 0; j < cols.length; j++) {
                let cellText = cols[j].innerText;
                // Clean up the cell text
                cellText = cellText.replace(/"/g, '""'); // Escape quotes
                cellText = cellText.replace(/\s+/g, ' ').trim(); // Clean whitespace
                row.push('"' + cellText + '"');
            }
            csv.push(row.join(","));
        }

        // Download CSV file
        const csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
        const downloadLink = document.createElement("a");
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    function exportTableToExcel(filename) {
        // Simple Excel export using table HTML
        const table = document.getElementById('surveyResultsTable');
        const tableHTML = table.outerHTML.replace(/ /g, '%20');
        
        const downloadLink = document.createElement("a");
        downloadLink.href = 'data:application/vnd.ms-excel,' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }

    function loadResponseDetails(responseId) {
        // Show loading state
        $('#responseContent').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">{{ __('admin.loading') }}...</span>
                </div>
                <p class="mt-2">{{ __('admin.loadingResponses') }}...</p>
            </div>
        `);
        
        // Show modal
        $('#responseModal').modal('show');

        // Load response details via AJAX
        $.ajax({
            url: `/admin/survey-responses/${responseId}/details`,
            method: 'GET',
            success: function(response) {
                $('#responseContent').html(response);
            },
            error: function(xhr, status, error) {
                $('#responseContent').html(`
                    <div class="alert alert-danger">
                        <i class="ti ti-alert-circle me-2"></i>
                        {{ __('admin.errorLoadingResponses') }}
                    </div>
                `);
            }
        });
    }

    // Function to toggle clear button visibility
    function toggleClearButton() {
        const status = $('#statusFilter').val();
        const mentor = $('#mentorFilter').val();
        const program = $('#programFilter').val();
        const search = table.search();
        if (status || mentor || program || search) {
            $('#clear-filters').parent().removeClass('d-none').addClass('d-flex');
        } else {
            $('#clear-filters').parent().removeClass('d-flex').addClass('d-none');
        }
    }

    // Add change events to filters
    $('#statusFilter, #mentorFilter, #programFilter').on('change', toggleClearButton);

    // Hook into DataTable search event
    table.on('search.dt', toggleClearButton);

    // Clear filters functionality
    $('#clear-filters').on('click', function() {
        $('#statusFilter').val('');
        $('#mentorFilter').val('');
        $('#programFilter').val('');
        table.column(4).search('', true, false);
        table.column(3).search('', true, false);
        table.search('');
        table.draw();
        toggleClearButton();
    });

    // Initial check
    toggleClearButton();
});
</script>
@stop