@extends('layouts.admin')

@section('title', __('admin.serviceProvidersRequests') )
@section('subTitle', __('admin.serviceProvidersRequests'))

@php
$pending_requests_count = 5;

$tabs = [
  ['title' => __('admin.ServiceProviders'), 'route' => 'services_providers.index', 'icon' => 'user-bolt'],
  ['title' => __('admin.pending') . ' ' . __('admin.Requests'), 'route' => 'services_providers.requests', 'params' => 'pending', 'icon' => 'hourglass-low', 'count' => $pending_requests_count],
  ['title' => __('admin.Requests') . ' ' . __('admin.History'), 'route' => 'services_providers.requests', 'params' => 'history', 'icon' => 'server-bolt'],
];
@endphp

@section('content')
<div>
	{{-- Alerts --}}
	<div class="row g-4">
		@if( session()->has('success') )
			<x-alert type="success" message="{{ session()->get('success') }}" />
		@endif
		@if( session()->has('error') )
			<x-alert type="danger" message="{{ session()->get('error') }}" />
		@endif
	</div>

	<br />

	{{-- Tabs Navigation --}}
	<x-navigation-pills :tabs="$tabs" />

	<div class="card">
		<div class="card-header border-bottom d-flex justify-content-between align-items-center">
			<h5 class="card-title mb-0">{{__('admin.serviceProvidersRequests')}}</h5>
			<div class="d-flex align-items-center gap-3">
				<div class="dropdown">
					<button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
						<i class="ti ti-file-export me-2"></i> {{ __('admin.Export') }}
					</button>
					<ul class="dropdown-menu" aria-labelledby="exportDropdown">
						<li><a class="dropdown-item" href="javascript:void(0)" id="export-csv"><i class="ti ti-file-text me-1"></i> CSV</a></li>
						<li><a class="dropdown-item" href="javascript:void(0)" id="export-excel"><i class="ti ti-file-spreadsheet me-1"></i> Excel</a></li>
					</ul>
				</div>
			</div>
		</div>

		<div class="card-datatable table-responsive">
			<table id="providersRequestsTable" class="table">
				<thead class="table-light">
					<tr>
						<th>#</th>
						<th>{{ __('auth.companyName') }}</th>
						<th>{{ __('auth.contactName') }}</th>
						<th>{{ __('auth.E-Mail') }}</th>
						<th>{{ __('admin.status') }}</th>
						<th style="width: 100px;" class="text-center">{{__('admin.Actions')}}</th>
					</tr>
				</thead>
				<tbody>
					@forelse( $requests as $provider )
						<tr>
							<td>{{ $loop->iteration }}</td>
							<td>{{ $provider->company_name }}</td>
							<td>{{ $provider->contact_name }}</td>
							<td><small>{{ $provider->email }}</small></td>
							<td>
								@if( is_null( $provider->status ) )
									<span data-toggle="tooltip" data-placement="top" title="{{ $provider->submitted_at }}" class="badge bg-label-secondary">{{ __('admin.Pending') }}</span>
								@else
									@if( $provider->status == 1 )
										<span class="badge bg-label-success">{{ __('admin.Approved') }}</span>
									@endif
									@if( $provider->status == 0 )
										<span class="badge bg-label-danger">{{ __('admin.Rejected') }}</span>
									@endif
								@endif
							</td>
							<td>
								<div class="d-flex gap-1 justify-content-center">
									<a href="javascript: void(0);" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill load-details" data-id="{{ $provider->id }}" data-toggle="tooltip" title="{{ __('admin.View') }}">
										<i class="ti ti-eye"></i>
									</a>
									@if( is_null( $provider->status ) )
										<a href="javascript: void(0);" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill approve-request" data-id="{{ $provider->id }}" data-toggle="tooltip" title="{{ __('admin.approve') }}">
											<i class="ti ti-circle-check"></i>
										</a>
										<a href="javascript: void(0);" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill reject-request" data-id="{{ $provider->id }}" data-toggle="tooltip" title="{{ __('admin.reject') }}">
											<i class="ti ti-circle-x"></i>
										</a>
									@endif
								</div>
							</td>
						</tr>
					@empty
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>
	
<div class="modal fade" id="requestsDetailsModal" tabindex="-1" aria-labelledby="requestsDetailsModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="requestsDetailsModalLabel">{{ __('admin.serviceProviderRequestDetails') }}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="container-fluid px-0">
					<table class="table table-bordered table-hover">
						<tbody>
							<tr>
								<td class="text-end"><strong>{{ __('auth.companyName') }}</strong></td>
								<td> <span id="details_company_name"></span> </td>
							</tr>
							<tr>
								<td class="text-end"><strong>{{ __('auth.contactName') }}</strong></td>
								<td> <span id="details_contact_name"></span> </td>
							</tr>
							<tr>
								<td class="text-end"><strong>{{ __('auth.E-Mail') }}</strong></td>
								<td> <span id="details_email"></span> </td>
							</tr>
							<tr>
								<td class="text-end"><strong>{{ __('auth.Phone') }}</strong></td>
								<td> <span id="details_phone"></span> </td>
							</tr>
							<tr>
								<td class="text-end"><strong>{{ __('auth.submittedAt') }}</strong></td>
								<td> <span id="details_submitted_at"></span> </td>
							</tr>
							<tr>
								<td class="text-end"><strong>{{ __('auth.ActionTaken') }}</strong></td>
								<td> <span id="details_action_taken"></span> </td>
							</tr>
							<tr id="details_action_by_tr">
								<td class="text-end"><strong>{{ __('auth.ActionTakenBy') }}</strong></td>
								<td> <span id="details_action_by"></span> </td>
							</tr>
							<tr id="details_rejection_reason_tr">
								<td class="text-end"><strong>{{ __('auth.rejectionReason') }}</strong></td>
								<td> <span id="details_rejection_reason"></span> </td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.Close') }}</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="rejectRequestModal" tabindex="-1" aria-labelledby="rejectRequestModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="requestsDetailsModalLabel">{{ __('admin.Confirm Action') }}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="container-fluid px-0">
					<div class="row mb-3">
						<div class="col-12">
							{!! __('admin.ConfirmRejectMessage') !!}
						</div>
					</div>
					<div class="row mb-3">
						<div class="form-floating">
							<textarea class="form-control" name="action-reject_reason" id="action-reject_reason" rows="5"></textarea>
							<label style="padding-left: 25px;" for="action-reject_reason">Reason</label>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Close') }}</button>
				<button class="btn btn-danger" id="approve-rejectFormBtn">{{ __('admin.RejectRequest') }}</button>
			</div>
		</div>
	</div>
</div>

<form id="approve-reject-form" action="{{ route('services_providers.takeAction') }}" method="POST">
	@csrf
	<input type="hidden" name="request_id" id="request_id" value="" />
	<input type="hidden" name="request_action" id="request_action" value="" />
	<input type="hidden" name="request_reason" id="request_reason" value="" />
</form>
@stop

@section('scripts')
	<script type="text/javascript">

		/**
		 * Tooltip initialiser (re-called after every DataTable redraw)
		 */
		function initTooltips() {
			$('[data-toggle="tooltip"]').tooltip();
		}

		/**
		 * Strip HTML → plain text (shared utility for sort/filter/options)
		 */
		function stripHtml(html) {
			const div = document.createElement('div');
			div.innerHTML = html;
			return div.textContent.replace(/\s+/g, ' ').trim();
		}

		var requests = @json($requests);

		/**
		 * ================================================================
		 * DataTable
		 * ============================================================== */
		$(document).ready(function () {

			initTooltips();

			/* Reinitialise tooltips whenever the DOM changes (pagination, search) */
			const observer = new MutationObserver(initTooltips);
			observer.observe(document.body, { childList: true, subtree: true });

			const isAr = '{{ $l }}' === 'ar';

			const table = $('#providersRequestsTable').DataTable({
				responsive:  true,
				pageLength:  10,
				lengthMenu:  [10, 25, 50, 100],
				order:       [[0, 'asc']],

				/* ── Column definitions ──────────────────────────────── */
				columnDefs: [
					{
						/* Status (col 4) contains a badge — extract plain text
						   for accurate filter matching and column sorting.     */
						targets:  [4],
						render(data, type) {
							if (type === 'filter' || type === 'sort') return stripHtml(data);
							return data;
						}
					},
					{
						/* Actions column — not sortable or searchable */
						targets:    -1,
						orderable:  false,
						searchable: false
					}
				],

				/* ── Hidden export buttons (triggered via card-header dropdown) ── */
				buttons: [
					{ extend: 'csv',   text: 'CSV',   className: 'd-none' },
					{ extend: 'excel', text: 'Excel', className: 'd-none' }
				],

				dom:
					'<"row"' +
					'<"col-12 col-lg-9 d-flex flex-wrap flex-md-nowrap align-items-center gap-3 ps-4 ps-lg-6"l>' +
					'<"col-12 col-lg-3 dt-action-buttons d-flex justify-content-lg-end pe-0 pe-lg-6"f>' +
					'>t<"row px-6"<"col-lg-6"i><"col-lg-6 d-flex justify-content-end"p>>',

				/* ── i18n ────────────────────────────────────────────── */
				language: isAr
					? {
						sEmptyTable:    'ليست هناك بيانات متاحة في الجدول',
						sLoadingRecords:'جارٍ التحميل...',
						sProcessing:    'جارٍ التحميل...',
						sLengthMenu:    'أظهر _MENU_ مدخلات',
						sZeroRecords:   'لم يعثر على أية سجلات',
						sInfo:          'إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل',
						sInfoEmpty:     'يعرض 0 إلى 0 من أصل 0 سجل',
						sInfoFiltered:  '(منتقاة من مجموع _MAX_ مُدخل)',
						sSearch:        'ابحث:',
						oPaginate: {
							sFirst:    'الأول',
							sPrevious: 'السابق',
							sNext:     'التالي',
							sLast:     'الأخير'
						},
						oAria: {
							sSortAscending:  ': تفعيل لترتيب العمود تصاعدياً',
							sSortDescending: ': تفعيل لترتيب العمود تنازلياً'
						}
					}
					: {
						sLengthMenu:       '_MENU_',
						search:            '',
						searchPlaceholder: '{{ __("admin.Search") }}...'
					},

				/* ── Inject filters once the table is ready ──────────── */
				initComplete() {
					buildFilters(this.api());

					/* Strip Bootstrap size-modifier classes DataTable injects */
					setTimeout(
						() => $('.form-control-sm, .form-select-sm').removeClass('form-control-sm form-select-sm'),
						300
					);
				}
			});

			/**
			 * ============================================================
			 * buildFilters
			 *   Appends filter <select> elements directly into the left
			 *   toolbar cell (alongside the length select) using gap-3 for
			 *   spacing. Guarded against double-init on responsive redraws.
			 * ========================================================== */
			function buildFilters(api) {

				if ($('#filter-company').length) return; // already built

				/* The left toolbar cell already holds the length select.
				   We append our filters into it so they share the same
				   flex row and inherit its gap-3 spacing.               */
				const $toolbar = $('.dataTables_length').parent();

				/**
				 * addColumnFilter
				 *
				 * @param {number}  colIndex  DataTable column index to search
				 * @param {string}  id        ID for the generated <select>
				 * @param {string}  allLabel  Default "show all" option text
				 * @param {boolean} exact     true → regex anchor (^value$) for strict match
				 *                            false → DataTables smart / partial search
				 */
				function addColumnFilter(colIndex, id, allLabel, exact = false) {
					const $select = $(
						`<select id="${id}" class="form-select" style="min-width:155px">` +
						`<option value="">${allLabel}</option>` +
						`</select>`
					)
						.appendTo($toolbar)
						.on('change', function () {
							const val = $(this).val();
							api
								.column(colIndex)
								.search(
									val ? (exact ? `^${$.fn.dataTable.util.escapeRegex(val)}$` : val) : '',
									exact,  /* regex  */
									!exact  /* smart  */
								)
								.draw();
						});

					/* Populate unique, sorted values from the live column data */
					const uniqueValues = new Set();
					api.column(colIndex).data().unique().sort().each(function (d) {
						const text = stripHtml(String(d));
						if (text && !uniqueValues.has(text)) {
							uniqueValues.add(text);
							$select.append(`<option value="${text}">${text}</option>`);
						}
					});
				}

				/* Company — col 1, partial match */
				addColumnFilter(1, 'filter-company', '{{ __("admin.all_companies") }}');

				/* Contact — col 2, partial match */
				addColumnFilter(2, 'filter-contact', '{{ __("admin.all_contacts") }}');

				/* Status — col 4, exact match so "Pending" won't catch "Approved" */
				addColumnFilter(4, 'filter-status', '{{ __("admin.All_statuses") }}', true);

				/* Check if URL contains 'pending' and auto-activate pending filter */
				const urlPath = window.location.pathname;
				if (urlPath.includes('/pending')) {
					const pendingText = '{{ __("admin.Pending") }}';
					// Find the actual option value for pending
					$('#filter-status option').each(function() {
						if ($(this).text().trim() === pendingText.trim()) {
							$('#filter-status').val($(this).val()).change();
							return false;
						}
					});
				}
			}

			/**
			 * ============================================================
			 * Export wiring
			 * ========================================================== */
			$('#export-csv').on('click', function (e) {
				e.preventDefault();
				table.button(0).trigger();
			});

			$('#export-excel').on('click', function (e) {
				e.preventDefault();
				table.button(1).trigger();
			});

		}); // end document.ready

		/**
		 * ================================================================
		 * Details Modal Handler
		 * ================================================================ */
		$(document).on('click', '.load-details', function(e) {
			e.preventDefault();
			var requestId = $(this).data('id');
			var request = requests.find(function(request) { return request.id == requestId; });
			if( typeof request !== 'undefined' )
			{
				$('#details_company_name').html( request.company_name )
				$('#details_contact_name').html( request.contact_name );
				$('#details_email').html( request.email );
				$('#details_phone').html( request.phone );
				$('#details_submitted_at').html(`
					<span data-toggle="tooltip" data-placement="top" title="${request.created_at}">
						<i class="far fa-fw fa-sm fa-clock"></i>
						${request.submitted_at}
					</span>
				`);

				var _status = '';
				$('#details_action_by_tr').addClass('d-none');
				$('#details_rejection_reason_tr').addClass('d-none');
				if( request.status === null )
				{
					_status = `<span data-toggle="tooltip" data-placement="top" title="${request.submitted_at}" class="badge bg-label-secondary">{{ __('admin.Pending') }}</span>`;
				}
				else
				{
					$('#details_action_by_tr').removeClass('d-none');
					if( request.status == 1 )
					{
						_status = `<span data-toggle="tooltip" data-placement="top" title="${request.action_since} | ${request.action_at}" class="badge bg-label-success">{{ __('admin.Approved') }}</span>`;
					}
					else if ( request.status == 0 )
					{
						$('#details_rejection_reason_tr').removeClass('d-none');
						_status = `<span data-toggle="tooltip" data-placement="top" title="${request.action_since} | ${request.action_at}" class="badge bg-label-danger">{{ __('admin.Rejected') }}</span>`;
					}
				}
				$('#details_action_taken').html(`${_status}`);
				$('#details_action_by').html( request.action_by );
				$('#details_rejection_reason').html( request.reason );
				$("#requestsDetailsModal").modal('show');
			} else { 
				releaseAlert('danger', {title: "{{ __('admin.Error') }}", message: "{{ __('admin.serviceProviderRequestNotFound') }}" });
			}
		});

		/**
		 * ================================================================
		 * Approve Request Handler
		 * ================================================================ */
		$(document).on('click', '.approve-request', function(e) {
			e.preventDefault();
			var requestId = $(this).data('id');
			var request = requests.find(function(request) { return request.id == requestId; });
			if( typeof request !== 'undefined' )
			{
				if( request.status === null )
				{
					releaseAlert('warning', {
						title: "{{ __('admin.Confirm Action') }}",
						message: "{!! __('admin.ConfirmApproveMessage') !!}",
						confirmText: "{{ __('admin.Confirm') }}",
						cancelText: "{{ __('admin.cancel') }}",
						onConfirm: () => {
							$("#request_id").val(request.id);
							$("#request_action").val('approve');
							$("#approve-reject-form").submit();
						},
						onCancel: () => {}
					});
				} else {
					releaseAlert('danger', {title: "{{ __('admin.Error') }}", message: "{{ __('auth.serviceProviderRequestHaveActionTakenAlready') }}"});
				}
			} else { 
				releaseAlert('danger', {title: "{{ __('admin.Error') }}", message: "{{ __('admin.serviceProviderRequestNotFound') }}"});
			}
		});

		/**
		 * ================================================================
		 * Reject Request Handler
		 * ================================================================ */
		$(document).on('click', '.reject-request', function(e) {
			e.preventDefault();
			var requestId = $(this).data('id');
			var request = requests.find(function(request) { return request.id == requestId; });
			if( typeof request !== 'undefined' )
			{
				if( request.status === null )
				{
					$("#request_id").val(request.id);
					$("#request_action").val('reject');
					$("#rejectRequestModal").modal('show');
				} else {
					releaseAlert('danger', {title: "{{ __('admin.Error') }}", message: "{{ __('auth.serviceProviderRequestHaveActionTakenAlready') }}" });
				}
			} else { 
				releaseAlert('danger', {title: "{{ __('admin.Error') }}", message: "{{ __('admin.serviceProviderRequestNotFound') }}"});
			}
		});

		/**
		 * ================================================================
		 * Submit Reject Form Handler
		 * ================================================================ */
		$(document).on('click', '#approve-rejectFormBtn', function(e) {
			e.preventDefault();
			var reason = $("#action-reject_reason").val().trim();
			if( reason )
			{
				$("#request_reason").val( reason );
				$("#approve-reject-form").submit();
			} else { 
				releaseAlert('danger', {title: "{{ __('admin.Error') }}", message: "{{ __('admin.ReasonIsRequired') }}" });
			}
		});
	</script>
@stop