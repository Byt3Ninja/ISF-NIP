@extends('layouts.admin')

@section('content')
<div class="">
	<div class="">
		<div class="content-body">

			@if (session()->has('message'))
			<div class="alert alert-{{ session()->get('class') ?? 'success' }} alert-dismissible" role="alert">
				{{ __('admin.' . session()->get('message')) }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
			@endif

			@php
			// Calculate job counts from the actual data
			$jobCounts = $jobs->groupBy('status')->map->count();
			$countCards = [
				['count' => $jobCounts->get('Completed', 0), 'text' => 'admin.Completed', 'icon' => 'ti-circle-check', 'color' => 'success', 'status' => 'Completed'],
				['count' => $jobCounts->get('Pending', 0), 'text' => 'admin.Pending', 'icon' => 'ti-clock', 'color' => 'secondary', 'status' => 'Pending'],
				['count' => $jobCounts->get('Processing', 0), 'text' => 'admin.Processing', 'icon' => 'ti-loader', 'color' => 'info', 'status' => 'Processing'],
				['count' => $jobCounts->get('Failed', 0), 'text' => 'admin.Failed', 'icon' => 'ti-circle-x', 'color' => 'danger', 'status' => 'Failed'],
			];
			@endphp
		
			<!-- Metrics Cards -->
			<div class="row g-4 mb-4">
				@foreach( $countCards as $card )
				<a href="javascript:;" class="col-lg-3 col-sm-6 metric-card" data-status="{{ $card['status'] }}">
					<div class="card card-border-shadow-{{ $card['color'] }} h-100">
						<div class="card-body">
							<div class="d-flex justify-content-center align-items-center mb-2">
								<div class="avatar me-4">
									<span class="avatar-initial rounded bg-label-{{ $card['color'] }}">
										<i class="ti {{ $card['icon'] }} text-{{ $card['color'] }} ti-28px"></i>
									</span>
								</div>
								<h4 class="mb-0 text-center">{{ $card['count'] }}</h4>
							</div>
							<p class="mb-1 text-center">{{ __($card['text']) }}</p>
						</div>
					</div>
				</a>
				@endforeach
			</div>
			<!-- / Metrics Cards -->


			<!-- Jobs Table Card -->
			<div class="card">
				<div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center">
					<h5 class="card-title mb-3 mb-md-0">{{ __('admin.Job_List') }}</h5>
					<div class="d-flex flex-wrap align-items-center gap-3">
						<a href="{{ route('queue.run.once') }}" class="btn btn-primary">
							<i class="ti ti-mobiledata me-2"></i>
							{{ __('admin.RunQueueOnce') }}
						</a>
						<div class="dropdown">
							<button class="btn btn-label-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
								aria-expanded="false">
								<i class="ti ti-download me-1"></i> {{ __('admin.Export') }}
							</button>
							<ul class="dropdown-menu">
								<li>
									<a class="dropdown-item" href="javascript:void(0)" id="export-excel">
										<i class="ti ti-file-type-xls me-1"></i> 
										Excel
									</a>
								</li>
								<li>
									<a class="dropdown-item" href="javascript:void(0)" id="export-csv">
										<i class="ti ti-file-type-csv me-1"></i> 
										CSV
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>

				<div class="card-datatable table-responsive">

					<div id="jobsTable-filter-row" class="row px-4 mb-6 align-items-end">
						<div class="col-md-2">
							<select id="status-filter" class="form-select form-select-sm w-100 mb-3 mb-md-0">
								<option value="" selected>{{ __('admin.All_statuses') }}</option>
								<option value="Pending">{{ __('admin.Pending') }}</option>
								<option value="Processing">{{ __('admin.Processing') }}</option>
								<option value="Completed">{{ __('admin.Completed') }}</option>
								<option value="Failed">{{ __('admin.Failed') }}</option>
							</select>
						</div>
						<div class="col-md-2">
							<select id="job-name-filter" class="form-select form-select-sm w-100 mb-3 mb-md-0">
								<option value="" selected>{{ __('admin.All_Jobs') }}</option>
								@foreach($jobs->pluck('displayName')->unique()->filter() as $jobName)
								<option value="{{ $jobName }}">{{ $jobName }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-2">
							<input type="text" id="date-range-filter" class="form-control form-control-sm w-100 mb-3 mb-md-0"
								placeholder="{{ __('admin.Date') }}" />
						</div>
						<div class="col-md-auto ms-auto">
							<button id="clear-filters-btn" class="btn btn-sm btn-outline-secondary w-100" style="display: none;">
								<i class="ti ti-eraser me-1"></i>{{ __('admin.Clear') }}
							</button>
						</div>
					</div>
					@if( $jobs->count() > 0 )
					<table class="table table-hover" id="jobsTable">
					@else
					<table class="table table-hover">
					@endif
						<thead class="table-light">
							<tr>
								<th>{{ __('admin.ID') }}</th>
								<th>{{ __('admin.Job_Name') }}</th>
								<th>{{ __('admin.Queue') }}</th>
								<th>{{ __('admin.Status') }}</th>
								<th>{{ __('admin.Attempts') }}</th>
								<th>{{ __('admin.CreatedAt') }}</th>
								<th>{{ __('admin.FinishedAt') }}</th>
								<th>{{ __('admin.Actions') }}</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($jobs as $job)
							@php
							$status_classes = [ 'Pending' => 'bg-label-secondary', 'Processing' => 'bg-label-info', 'Completed' =>
							'bg-label-success', 'Failed' => 'bg-label-danger' ];
							$status_class = $status_classes[$job->status] ?? 'bg-label-secondary';
							@endphp
							<tr id="row_{{ $job->id }}">
								<td><span class="fw-medium">{{ Str::limit($job->id, 8) }}</span></td>
								<td>{{ $job->displayName ?? 'N/A' }}</td>
								<td><span class="badge bg-label-primary">{{ $job->queue }}</span></td>
								<td><span class="badge {{ $status_class }}">{{ $job->status }}</span></td>
								<td>{{ $job->attempts }}</td>
								<td data-date="{{ $job->created_at->toIso8601String() }}">{{ $job->created_at->diffForHumans() }}</td>
								<td>{{ $job->finished_at ? $job->finished_at->diffForHumans() : 'N/A' }}</td>
								<td>
									<div class="d-flex align-items-center">
										<button class="btn btn-sm btn-icon me-2 view-job-details" data-bs-toggle="modal"
											data-bs-target="#jobDetailsModal" data-job-id="{{ $job->id }}"
											data-job-name="{{ $job->displayName }}" data-job-status="{{ $job->status }}"
											data-job-queue="{{ $job->queue }}" data-job-attempts="{{ $job->attempts }}"
											data-job-duration="{{ 'N/A' }}"
											data-job-created-at="{{ $job->created_at->toIso8601String() }}"
											data-job-finished-at="{{ $job->finished_at ? $job->finished_at->toIso8601String() : 'N/A' }}"
											data-job-payload="{{ htmlspecialchars($job->payload) }}"
											data-job-exception="{{ htmlspecialchars($job->exception ?? '') }}">
											<i class="ti ti-eye"></i>
										</button>
										@if ($job->status === 'Failed')
										<button class="btn btn-sm btn-icon me-2 item-retry" data-id="{{ $job->id }}" title="Retry Job"><i
												class="ti ti-refresh"></i></button>
										@endif
										<button class="btn btn-sm btn-icon item-delete" data-id="{{ $job->id }}" title="Delete Job">
											<i class="ti ti-trash"></i>
										</button>
									</div>
								</td>
							</tr>
							@empty
							<tr>
								<td colspan="8" class="text-center">{{ __('admin.No_jobs_found') }}</td>
							</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>

		</div>
	</div>
</div>

<!-- ENHANCED: Job Details Modal -->
<div class="modal fade" id="jobDetailsModal" tabindex="-1" aria-labelledby="jobDetailsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title me-4" id="jobDetailsModalLabel">{{ __('admin.JobDetails') }}</h5>
				<span id="modal-job-status-badge" class="badge"></span>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<dl class="row">
					<dt class="col-sm-3">{{ __('admin.ID') }}</dt>
					<dd class="col-sm-9"><span id="modal-job-id"></span></dd>

					<dt class="col-sm-3">{{ __('admin.Job_Name') }}</dt>
					<dd class="col-sm-9"><span id="modal-job-name"></span></dd>

					<dt class="col-sm-3">{{ __('admin.Queue') }}</dt>
					<dd class="col-sm-9"><span id="modal-job-queue" class="badge bg-label-primary"></span></dd>

					<dt class="col-sm-3">{{ __('admin.Attempts') }}</dt>
					<dd class="col-sm-9"><span id="modal-job-attempts"></span></dd>

					<dt class="col-sm-3">{{ __('admin.Runtime') }}</dt>
					<dd class="col-sm-9"><span id="modal-job-duration"></span></dd>

					<dt class="col-sm-3">{{ __('admin.CreatedAt') }}</dt>
					<dd class="col-sm-9"><span id="modal-job-created-at"></span></dd>

					<dt class="col-sm-3">{{ __('admin.FinishedAt') }}</dt>
					<dd class="col-sm-9"><span id="modal-job-finished-at"></span></dd>
				</dl>

				<hr>

				<div class="d-flex justify-content-between align-items-center mb-2">
					<h5>{{ __('admin.Payload') }}</h5>
					<button class="btn btn-sm btn-outline-secondary copy-btn" data-clipboard-target="#modal-job-payload-code">
						<i class="ti ti-copy me-1"></i> {{ __('admin.Copy') }}
					</button>
				</div>
				<pre class="bg-dark text-white p-3 rounded"
					style="max-height: 300px;"><code id="modal-job-payload-code"></code></pre>

				<div id="modal-exception-details" style="display: none;">
					<hr>
					<div class="d-flex justify-content-between align-items-center mb-2">
						<h5>{{ __('admin.Exception') }}</h5>
						<button class="btn btn-sm btn-outline-secondary copy-btn" data-clipboard-target="#modal-job-exception-code">
							<i class="ti ti-copy me-1"></i> {{ __('admin.Copy') }}
						</button>
					</div>
					<pre class="bg-danger-light text-danger p-3 rounded"
						style="max-height: 300px;"><code id="modal-job-exception-code"></code></pre>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Close') }}</button>
			</div>
		</div>
	</div>
</div>
<!-- END: Content-->
@stop

@section('pagescripts')
<script>
	$(document).ready(function() {
    // Helper function for success toasts
		const showSuccessToast = (message) => {
			const Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3000,
				timerProgressBar: true,
				customClass: {
					popup: 'swal2-toast swal2-toast-sm'
				},
				didOpen: (toast) => {
					toast.addEventListener('mouseenter', Swal.stopTimer);
					toast.addEventListener('mouseleave', Swal.resumeTimer);
				}
			});
			Toast.fire({ icon: 'success', title: message });
		};

		// Helper function for error toasts
		const showErrorToast = (message) => {
			const Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3000,
				timerProgressBar: true,
				customClass: {
					popup: 'swal2-toast swal2-toast-sm'
				},
				didOpen: (toast) => {
					toast.addEventListener('mouseenter', Swal.stopTimer);
					toast.addEventListener('mouseleave', Swal.resumeTimer);
				}
			});
			Toast.fire({ icon: 'error', title: message });
		};

		// Add small font size for toast via CSS if not already present
		if (!$('style#swal2-toast-sm-style').length) {
			$('<style id="swal2-toast-sm-style">.swal2-toast.swal2-toast-sm .swal2-title{font-size:1rem!important;}</style>').appendTo('head');
		}

    // Date Range Filter
    var dateRangePicker = $('#date-range-filter').flatpickr({
        mode: 'range', dateFormat: 'Y-m-d',
        onClose: function(selectedDates, dateStr, instance) { 
            table.draw();
            toggleClearButton();
        }
    });

    $.fn.dataTable.ext.search.push(
        function( settings, data, dataIndex ) {
            var min = dateRangePicker.selectedDates[0];
            var max = dateRangePicker.selectedDates[1];
            var date = new Date( $(settings.aoData[dataIndex].nTr).find('td:eq(5)').data('date') );
            if ((min === undefined && max === undefined) || (min === undefined && date <= max) || (min <= date && max === undefined) || (min <= date && date <= max)) {
                return true;
            }
            return false;
        }
    );

    var table = $('#jobsTable').DataTable({
        responsive: true,
        lengthChange: true,
        paging: true,
        bInfo: true,
        order: [[5, "desc"]],
        dom:
            "<'row'<'col-sm-12 col-md-6'lB><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'<'#jobsTable-filter-placeholder'>>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            { extend: 'csv', className: 'd-none', exportOptions: { columns: ':visible:not(:last-child)' } },
            { extend: 'excel', className: 'd-none', exportOptions: { columns: ':visible:not(:last-child)' } }
        ],
       @if ($l == 'ar')
				"language": {
					"sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
					"sLoadingRecords": "جارٍ التحميل...",
					"sProcessing": "جارٍ التحميل...",
					"sLengthMenu": "أظهر _MENU_ مدخلات",
					"sZeroRecords": "لم يعثر على أية سجلات",
					"sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
					"sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
					"sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
					"sInfoPostFix": "",
					"sSearch": "ابحث:",
					"sUrl": "",
					"oPaginate": {
						"sFirst": "الأول",
						"sPrevious": "السابق",
						"sNext": "التالي",
						"sLast": "الأخير"
					},
					"oAria": {
						"sSortAscending": ": تفعيل لترتيب العمود تصاعدياً",
						"sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
					}
				}
			@endif
    });

    $('#jobsTable-filter-row').appendTo('#jobsTable-filter-placeholder');

    function toggleClearButton() {
        var statusFilter = $('#status-filter').val();
        var jobNameFilter = $('#job-name-filter').val();
        var dateFilter = dateRangePicker.selectedDates.length;
        if (statusFilter || jobNameFilter || dateFilter > 0) {
            $('#clear-filters-btn').show();
        } else {
            $('#clear-filters-btn').hide();
        }
    }

    // -- Event Handlers --
    $('#status-filter, #job-name-filter').on('change', function() {
        var statusVal = $('#status-filter').val();
        var jobNameVal = $('#job-name-filter').val();
        table.column(3).search(statusVal ? '^' + statusVal + '$' : '', true, false);
        table.column(1).search(jobNameVal).draw();
        toggleClearButton();
    });
    
    $('.metric-card').on('click', function() {
        $('#status-filter').val($(this).data('status')).trigger('change');
    });

    $('#clear-filters-btn').on('click', function() {
        $('#status-filter').val('').trigger('change');
        $('#job-name-filter').val('');
        dateRangePicker.clear();
        table.search('').columns().search('').draw();
        toggleClearButton();
    });

    $('#export-excel').on('click', e => { e.preventDefault(); table.button('.buttons-excel').trigger(); });
    $('#export-csv').on('click', e => { e.preventDefault(); table.button('.buttons-csv').trigger(); });

    // ENHANCED: Modal population
    $('#jobsTable tbody').on('click', '.view-job-details', function() {
        var data = $(this).data();
        var status_classes = { 'Pending': 'bg-label-secondary', 'Processing': 'bg-label-info', 'Completed': 'bg-label-success', 'Failed': 'bg-label-danger' };
        
        $('#modal-job-id').text(data.jobId);
        $('#modal-job-name').text(data.jobName);
        $('#modal-job-queue').text(data.jobQueue);
        $('#modal-job-attempts').text(data.jobAttempts);
        $('#modal-job-duration').text(data.jobDuration);
        $('#modal-job-created-at').text(new Date(data.jobCreatedAt).toLocaleString());
        $('#modal-job-finished-at').text(data.jobFinishedAt !== 'N/A' ? new Date(data.jobFinishedAt).toLocaleString() : 'N/A');

        var statusBadge = $('#modal-job-status-badge');
        statusBadge.text(data.jobStatus).removeClass().addClass('badge ' + (status_classes[data.jobStatus] || 'bg-label-secondary'));
        
        try {
            var payload = JSON.parse(data.jobPayload);
            $('#modal-job-payload-code').text(JSON.stringify(payload, null, 2));
        } catch (e) {
            $('#modal-job-payload-code').text(data.jobPayload);
        }

        if (data.jobException && data.jobException.length > 0) {
            $('#modal-job-exception-code').text(data.jobException);
            $('#modal-exception-details').show();
        } else {
            $('#modal-exception-details').hide();
        }
    });
    
    // NEW: Copy to clipboard functionality for modal
    $('.modal-body').on('click', '.copy-btn', function() {
        var target = $($(this).data('clipboard-target'));
        navigator.clipboard.writeText(target.text()).then(() => {
            var originalText = $(this).html();
            $(this).html('<i class="ti ti-check me-1"></i> {{ __("admin.Copied") }}!');
            setTimeout(() => $(this).html(originalText), 2000);
        });
    });

    // NEW: Simulate delete action with SweetAlert
    $('#jobsTable tbody').on('click', '.item-delete', function() {
        var jobId = $(this).data('id');
        var row = table.row('#row_' + jobId);

        Swal.fire({
            title: "{{__('admin.SureDelete')}}",
						text: "{{__('admin.Recover')}}",
						icon: "warning",
						showCancelButton: true,
						confirmButtonText: "{{ __('admin.DYes') }}",
						cancelButtonText: "{{ __('admin.No') }}",
						reverseButtons: true,
						customClass: {
							confirmButton: 'btn btn-danger',
							cancelButton: 'btn btn-label-secondary',
						},
						buttonsStyling: false,
        }).then((result) => {
					if (!result.isConfirmed) {
						showErrorToast('{{ __("admin.Job_Deletion_Cancelled") }}');
						return;
					}
						// Simulate AJAX call
						setTimeout(() => {
								row.remove().draw(false); // Use false to stay on the same page
								showSuccessToast('{{ __("admin.Job_Deleted_Successfully") }}');
						}, 500);
        }).catch((error) => {
            console.error('Error deleting job:', error);
            showErrorToast(error?.message || '{{ __("admin.Failed_to_delete_job") }}');
        });
    });

    // NEW: Simulate retry logic functionality
    $('#jobsTable tbody').on('click', '.item-retry', function() {
        var button = $(this);
        var jobId = button.data('id');
        var row = table.row('#row_' + jobId);
        var rowNode = row.node();
        
				Swal.fire({
						icon: 'info',
						title: '{{ __("admin.Retrying_job") }}',
						text: '{{ __("admin.Please_wait_while_the_job_is_being_re_queued") }}',
						// didOpen: () => { Swal.showLoading() },
						allowOutsideClick: false,
						showCancelButton: false,
						showConfirmButton: false,
						buttonsStyling: false
				});

        // Simulate AJAX call to retry endpoint
        setTimeout(() => {
            // Update the data in the table visually
            $(rowNode).find('td:eq(3) span').removeClass('bg-label-danger').addClass('bg-label-secondary').text('Pending');
            button.remove(); // Remove the retry button
            
            Swal.close();
            showSuccessToast('{{ __("admin.Job_has_been_re_queued_successfully") }}');
        }, 1500);
    });
});
</script>
@stop