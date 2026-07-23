@extends('layouts.admin')

@section('title', __('admin.ServiceProviders') )
@section('subTitle', __('admin.ServiceProviders'))

@section('style')
@stop

@php
$tabs = [
  ['id' => 'enrolled', 'title' => __('admin.ServiceProviders'), 'icon' => 'user-bolt'],
  ['id' => 'pending',  'title' => __('admin.pending_requests'), 'icon' => 'hourglass-low', 'count' => $pending_requests_count],
  ['id' => 'history',  'title' =>  __('admin.date_requests'), 'icon' => 'server-bolt'],
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

	{{-- Tabs Navigation --}}
	<br />
	<x-navigation-pills :tabs="$tabs" />

	{{-- Tab Content --}}
	<div class="tab-content bg-card p-0">

		{{-- ═══════════════════════════════════════════════════════════════
		     Tab 1: Enrolled Service Providers
		     ═══════════════════════════════════════════════════════════════ --}}
		<div class="tab-pane fade show active" id="enrolled" role="tabpanel">
			<div class="card">
				<div class="card-header border-bottom d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">{{ __('admin.ServiceProviders') }}</h5>
					<div class="d-flex align-items-center gap-3">
						<div class="dropdown">
							<button class="btn btn-label-secondary dropdown-toggle" type="button" id="enrolledExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="ti ti-file-export me-2"></i> {{ __('admin.Export') }}
							</button>
							<ul class="dropdown-menu" aria-labelledby="enrolledExportDropdown">
								<li><a class="dropdown-item export-btn" href="javascript:void(0)" data-table="providersTable" data-btn="0"><i class="ti ti-file-text me-1"></i> CSV</a></li>
								<li><a class="dropdown-item export-btn" href="javascript:void(0)" data-table="providersTable" data-btn="1"><i class="ti ti-file-spreadsheet me-1"></i> Excel</a></li>
							</ul>
						</div>
					</div>
				</div>

				<div class="card-datatable table-responsive">
					<table id="providersTable" class="table">
						<thead class="table-light">
							<tr>
								<th>#</th>
								<th>{{ __('auth.companyName') }}</th>
								<th>{{ __('auth.contactName') }}</th>
								<th>{{ __('auth.E-Mail') }}</th>
								<th>{{ __('admin.status') }}</th>
								<th style="width: 100px;" class="text-center">{{ __('admin.Actions') }}</th>
							</tr>
						</thead>
						<tbody>
							@forelse( $providers as $provider )
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td>{{ $provider->company_name }}</td>
									<td>
										<div class="d-flex align-items-center w-px-150">
											<a target="_blank" href="/dashboard/users/{{ $provider->user->id }}/edit">
												<img
													onerror="this.src='/images/logo-placeholder.png'"
													src="{{ $provider->user->img_url }}"
													alt="avatar"
													class="rounded-circle me-2 border bg-label-light"
													height="40"
													width="40"
												>
											</a>
											<a target="_blank" href="/dashboard/users/{{ $provider->user->id }}/edit">
												<span class="text-dark">{{ $provider->contact_name }}</span>
											</a>
										</div>
									</td>
									<td><small>{{ $provider->email }}</small></td>
									<td>
										@if( $provider->status == 1 )
											<span class="badge bg-label-primary">{{ __('admin.Active') }}</span>
										@endif
										@if( $provider->status == 0 )
											<span class="badge bg-label-secondary">{{ __('admin.Inactive') }}</span>
										@endif
									</td>
									<td>
										<div class="d-flex gap-1 justify-content-center">
											@if($_user->ableTo('service_provider.profile', $permissions))
											<a href="{{ route('service_provider.profile', $provider->id) }}" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill" data-toggle="tooltip" title="{{ __('admin.View') }}">
												<i class="ti ti-eye ti-md"></i>
											</a>
											@endif
											@if($_user->ableTo('services_providers.edit', $permissions))
											<a href="{{ route('services_providers.edit_profile', $provider->id) }}" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill" data-toggle="tooltip" title="{{ __('admin.Edit') }}">
												<i class="ti ti-pencil ti-md"></i>
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

		{{-- ═══════════════════════════════════════════════════════════════
		     Tab 2: Pending Requests
		     ═══════════════════════════════════════════════════════════════ --}}
		<div class="tab-pane fade" id="pending" role="tabpanel">
			<div class="card">
				<div class="card-header border-bottom d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">{{ __('admin.pending_requests') }}</h5>
					<div class="d-flex align-items-center gap-3">
						<div class="dropdown">
							<button class="btn btn-label-secondary dropdown-toggle" type="button" id="pendingExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="ti ti-file-export me-2"></i> {{ __('admin.Export') }}
							</button>
							<ul class="dropdown-menu" aria-labelledby="pendingExportDropdown">
								<li><a class="dropdown-item export-btn" href="javascript:void(0)" data-table="pendingRequestsTable" data-btn="0"><i class="ti ti-file-text me-1"></i> CSV</a></li>
								<li><a class="dropdown-item export-btn" href="javascript:void(0)" data-table="pendingRequestsTable" data-btn="1"><i class="ti ti-file-spreadsheet me-1"></i> Excel</a></li>
							</ul>
						</div>
					</div>
				</div>

				<div class="card-datatable table-responsive">
					<table id="pendingRequestsTable" class="table">
						<thead class="table-light">
							<tr>
								<th>#</th>
								<th>{{ __('auth.companyName') }}</th>
								<th>{{ __('auth.contactName') }}</th>
								<th>{{ __('auth.E-Mail') }}</th>
								<th style="width: 100px;" class="text-center">{{ __('admin.Actions') }}</th>
							</tr>
						</thead>
						<tbody>
							@forelse( $pendingRequests as $request )
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td>{{ $request->company_name }}</td>
									<td>{{ $request->contact_name }}</td>
									<td><small>{{ $request->email }}</small></td>
									<td>
										<div class="d-flex gap-1 justify-content-center">
											<a href="javascript:void(0);" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill load-details" data-id="{{ $request->id }}" data-toggle="tooltip" title="{{ __('admin.View') }}">
												<i class="ti ti-eye"></i>
											</a>
											<a href="javascript:void(0);" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill approve-request" data-id="{{ $request->id }}" data-toggle="tooltip" title="{{ __('admin.approve') }}">
												<i class="ti ti-circle-check"></i>
											</a>
											<a href="javascript:void(0);" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill reject-request" data-id="{{ $request->id }}" data-toggle="tooltip" title="{{ __('admin.reject') }}">
												<i class="ti ti-circle-x"></i>
											</a>
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

		{{-- ═══════════════════════════════════════════════════════════════
		     Tab 3: Request History
		     ═══════════════════════════════════════════════════════════════ --}}
		<div class="tab-pane fade" id="history" role="tabpanel">
			<div class="card">
				<div class="card-header border-bottom d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">{{ __('admin.Requests') }} {{ __('admin.History') }}</h5>
					<div class="d-flex align-items-center gap-3">
						<div class="dropdown">
							<button class="btn btn-label-secondary dropdown-toggle" type="button" id="historyExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="ti ti-file-export me-2"></i> {{ __('admin.Export') }}
							</button>
							<ul class="dropdown-menu" aria-labelledby="historyExportDropdown">
								<li><a class="dropdown-item export-btn" href="javascript:void(0)" data-table="historyRequestsTable" data-btn="0"><i class="ti ti-file-text me-1"></i> CSV</a></li>
								<li><a class="dropdown-item export-btn" href="javascript:void(0)" data-table="historyRequestsTable" data-btn="1"><i class="ti ti-file-spreadsheet me-1"></i> Excel</a></li>
							</ul>
						</div>
					</div>
				</div>

				<div class="card-datatable table-responsive">
					<table id="historyRequestsTable" class="table">
						<thead class="table-light">
							<tr>
								<th>#</th>
								<th>{{ __('auth.companyName') }}</th>
								<th>{{ __('auth.contactName') }}</th>
								<th>{{ __('auth.E-Mail') }}</th>
								<th>{{ __('admin.status') }}</th>
								<th style="width: 100px;" class="text-center">{{ __('admin.Actions') }}</th>
							</tr>
						</thead>
						<tbody>
							@forelse( $allRequests as $request )
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td>{{ $request->company_name }}</td>
									<td>{{ $request->contact_name }}</td>
									<td><small>{{ $request->email }}</small></td>
									<td>
										@if( is_null( $request->status ) )
											<span data-toggle="tooltip" data-placement="top" title="{{ $request->submitted_at }}" class="badge bg-label-secondary">{{ __('admin.Pending') }}</span>
										@elseif( $request->status == 1 )
											<span class="badge bg-label-success">{{ __('admin.Approved') }}</span>
										@elseif( $request->status == 0 )
											<span class="badge bg-label-danger">{{ __('admin.Rejected') }}</span>
										@endif
									</td>
									<td>
										<div class="d-flex gap-1 justify-content-center">
											<a href="javascript:void(0);" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill load-details" data-id="{{ $request->id }}" data-toggle="tooltip" title="{{ __('admin.View') }}">
												<i class="ti ti-eye"></i>
											</a>
											@if( is_null( $request->status ) )
												<a href="javascript:void(0);" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill approve-request" data-id="{{ $request->id }}" data-toggle="tooltip" title="{{ __('admin.approve') }}">
													<i class="ti ti-circle-check"></i>
												</a>
												<a href="javascript:void(0);" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill reject-request" data-id="{{ $request->id }}" data-toggle="tooltip" title="{{ __('admin.reject') }}">
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

	</div>{{-- /.tab-content --}}
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     Shared Modals (used by Pending & History tabs)
     ═══════════════════════════════════════════════════════════════════ --}}
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
				<h5 class="modal-title" id="rejectRequestModalLabel">{{ __('admin.Confirm Action') }}</h5>
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

		/* ── Shared utilities ────────────────────────────────────────── */
		function initTooltips() {
			$('[data-toggle="tooltip"]').tooltip();
		}

		function stripHtml(html) {
			const div = document.createElement('div');
			div.innerHTML = html;
			return div.textContent.replace(/\s+/g, ' ').trim();
		}

		/* Request data for modals */
		var allRequests = @json($allRequests);

		/* ── DataTable shared config ─────────────────────────────────── */
		const isAr = '{{ $l }}' === 'ar';

		const dtLanguage = isAr
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
			};

		const dtDom =
			'<"row"' +
			'<"col-12 col-lg-9 d-flex flex-wrap flex-md-nowrap align-items-center gap-3 ps-4 ps-lg-6"l>' +
			'<"col-12 col-lg-3 dt-action-buttons d-flex justify-content-lg-end pe-0 pe-lg-6"f>' +
			'>t<"row px-6"<"col-lg-6"i><"col-lg-6 d-flex justify-content-end"p>>';

		/* ================================================================
		 * Document Ready
		 * ============================================================== */
		$(document).ready(function () {

			initTooltips();

			const observer = new MutationObserver(initTooltips);
			observer.observe(document.body, { childList: true, subtree: true });

			/* Store DataTable instances keyed by table ID */
			var dtInstances = {};

			/* ── Tab 1: Enrolled Providers ────────────────────────────── */
			dtInstances['providersTable'] = $('#providersTable').DataTable({
				responsive:  true,
				pageLength:  10,
				lengthMenu:  [10, 25, 50, 100],
				order:       [[0, 'asc']],
				columnDefs: [
					{
						targets:  [2, 4],
						render(data, type) {
							if (type === 'filter' || type === 'sort') return stripHtml(data);
							return data;
						}
					},
					{ targets: -1, orderable: false, searchable: false }
				],
				buttons: [
					{ extend: 'csv',   text: 'CSV',   className: 'd-none' },
					{ extend: 'excel', text: 'Excel', className: 'd-none' }
				],
				dom: dtDom,
				language: dtLanguage,
				initComplete() {
					buildEnrolledFilters(this.api());
					setTimeout(() => $('.form-control-sm, .form-select-sm').removeClass('form-control-sm form-select-sm'), 300);
				}
			});

			function buildEnrolledFilters(api) {
				if ($('#filter-enrolled-company').length) return;
				const $toolbar = $('#providersTable').closest('.dataTables_wrapper').find('.dataTables_length').parent();

				function addFilter(colIndex, id, allLabel, exact = false) {
					const $select = $(
						`<select id="${id}" class="form-select" style="min-width:155px">` +
						`<option value="">${allLabel}</option></select>`
					).appendTo($toolbar).on('change', function () {
						const val = $(this).val();
						api.column(colIndex).search(
							val ? (exact ? `^${$.fn.dataTable.util.escapeRegex(val)}$` : val) : '', exact, !exact
						).draw();
					});
					const seen = new Set();
					api.column(colIndex).data().unique().sort().each(function (d) {
						const text = stripHtml(String(d));
						if (text && !seen.has(text)) { seen.add(text); $select.append(`<option value="${text}">${text}</option>`); }
					});
				}

				addFilter(1, 'filter-enrolled-company', '{{ __("admin.all_companies") }}');
				addFilter(2, 'filter-enrolled-contact', '{{ __("admin.all_contacts") }}');
				addFilter(4, 'filter-enrolled-status',  '{{ __("admin.All_statuses") }}', true);
			}

			/* ── Tab 2: Pending Requests ─────────────────────────────── */
			dtInstances['pendingRequestsTable'] = $('#pendingRequestsTable').DataTable({
				responsive:  true,
				pageLength:  10,
				lengthMenu:  [10, 25, 50, 100],
				order:       [[0, 'asc']],
				columnDefs: [
					{ targets: -1, orderable: false, searchable: false }
				],
				buttons: [
					{ extend: 'csv',   text: 'CSV',   className: 'd-none' },
					{ extend: 'excel', text: 'Excel', className: 'd-none' }
				],
				dom: dtDom,
				language: dtLanguage,
				initComplete() {
					buildPendingFilters(this.api());
					setTimeout(() => $('#pending .form-control-sm, #pending .form-select-sm').removeClass('form-control-sm form-select-sm'), 300);
				}
			});

			function buildPendingFilters(api) {
				if ($('#filter-pending-company').length) return;
				const $toolbar = $('#pendingRequestsTable').closest('.dataTables_wrapper').find('.dataTables_length').parent();

				function addFilter(colIndex, id, allLabel) {
					const $select = $(
						`<select id="${id}" class="form-select" style="min-width:155px">` +
						`<option value="">${allLabel}</option></select>`
					).appendTo($toolbar).on('change', function () {
						const val = $(this).val();
						api.column(colIndex).search(val || '').draw();
					});
					const seen = new Set();
					api.column(colIndex).data().unique().sort().each(function (d) {
						const text = stripHtml(String(d));
						if (text && !seen.has(text)) { seen.add(text); $select.append(`<option value="${text}">${text}</option>`); }
					});
				}

				addFilter(1, 'filter-pending-company', '{{ __("admin.all_companies") }}');
				addFilter(2, 'filter-pending-contact',  '{{ __("admin.all_contacts") }}');
			}

			/* ── Tab 3: Request History ──────────────────────────────── */
			dtInstances['historyRequestsTable'] = $('#historyRequestsTable').DataTable({
				responsive:  true,
				pageLength:  10,
				lengthMenu:  [10, 25, 50, 100],
				order:       [[0, 'asc']],
				columnDefs: [
					{
						targets:  [4],
						render(data, type) {
							if (type === 'filter' || type === 'sort') return stripHtml(data);
							return data;
						}
					},
					{ targets: -1, orderable: false, searchable: false }
				],
				buttons: [
					{ extend: 'csv',   text: 'CSV',   className: 'd-none' },
					{ extend: 'excel', text: 'Excel', className: 'd-none' }
				],
				dom: dtDom,
				language: dtLanguage,
				initComplete() {
					buildHistoryFilters(this.api());
					setTimeout(() => $('#history .form-control-sm, #history .form-select-sm').removeClass('form-control-sm form-select-sm'), 300);
				}
			});

			function buildHistoryFilters(api) {
				if ($('#filter-history-company').length) return;
				const $toolbar = $('#historyRequestsTable').closest('.dataTables_wrapper').find('.dataTables_length').parent();

				function addFilter(colIndex, id, allLabel, exact = false) {
					const $select = $(
						`<select id="${id}" class="form-select" style="min-width:155px">` +
						`<option value="">${allLabel}</option></select>`
					).appendTo($toolbar).on('change', function () {
						const val = $(this).val();
						api.column(colIndex).search(
							val ? (exact ? `^${$.fn.dataTable.util.escapeRegex(val)}$` : val) : '', exact, !exact
						).draw();
					});
					const seen = new Set();
					api.column(colIndex).data().unique().sort().each(function (d) {
						const text = stripHtml(String(d));
						if (text && !seen.has(text)) { seen.add(text); $select.append(`<option value="${text}">${text}</option>`); }
					});
				}

				addFilter(1, 'filter-history-company', '{{ __("admin.all_companies") }}');
				addFilter(2, 'filter-history-contact',  '{{ __("admin.all_contacts") }}');
				addFilter(4, 'filter-history-status',   '{{ __("admin.All_statuses") }}', true);
			}

			/* ── Recalculate DataTable columns on tab switch ─────────── */
			$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
				$.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
			});

			/* ── Export wiring (shared) ──────────────────────────────── */
			$(document).on('click', '.export-btn', function (e) {
				e.preventDefault();
				var tableId = $(this).data('table');
				var btnIndex = $(this).data('btn');
				if (dtInstances[tableId]) {
					dtInstances[tableId].button(btnIndex).trigger();
				}
			});

		}); // end document.ready

		/* ================================================================
		 * Details Modal Handler
		 * ============================================================== */
		$(document).on('click', '.load-details', function(e) {
			e.preventDefault();
			var requestId = $(this).data('id');
			var request = allRequests.find(function(r) { return r.id == requestId; });
			if( typeof request !== 'undefined' )
			{
				$('#details_company_name').html( request.company_name );
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
				releaseFailureAlert({title: "{{ __('admin.Error') }}", message: "{{ __('admin.serviceProviderRequestNotFound') }}" });
			}
		});

		/* ================================================================
		 * Approve Request Handler
		 * ============================================================== */
		$(document).on('click', '.approve-request', function(e) {
			e.preventDefault();
			var requestId = $(this).data('id');
			var request = allRequests.find(function(r) { return r.id == requestId; });
			if( typeof request !== 'undefined' )
			{
				if( request.status === null )
				{
					releaseWarningAlert({
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
					releaseFailureAlert({title: "{{ __('admin.Error') }}", message: "{{ __('auth.serviceProviderRequestHaveActionTakenAlready') }}"});
				}
			} else {
				releaseFailureAlert({title: "{{ __('admin.Error') }}", message: "{{ __('admin.serviceProviderRequestNotFound') }}"});
			}
		});

		/* ================================================================
		 * Reject Request Handler
		 * ============================================================== */
		$(document).on('click', '.reject-request', function(e) {
			e.preventDefault();
			var requestId = $(this).data('id');
			var request = allRequests.find(function(r) { return r.id == requestId; });
			if( typeof request !== 'undefined' )
			{
				if( request.status === null )
				{
					$("#request_id").val(request.id);
					$("#request_action").val('reject');
					$("#rejectRequestModal").modal('show');
				} else {
					releaseFailureAlert({title: "{{ __('admin.Error') }}", message: "{{ __('auth.serviceProviderRequestHaveActionTakenAlready') }}" });
				}
			} else {
				releaseFailureAlert({title: "{{ __('admin.Error') }}", message: "{{ __('admin.serviceProviderRequestNotFound') }}"});
			}
		});

		/* ================================================================
		 * Submit Reject Form Handler
		 * ============================================================== */
		$(document).on('click', '#approve-rejectFormBtn', function(e) {
			e.preventDefault();
			var reason = $("#action-reject_reason").val().trim();
			if( reason )
			{
				$("#request_reason").val( reason );
				$("#approve-reject-form").submit();
			} else {
				releaseFailureAlert({title: "{{ __('admin.Error') }}", message: "{{ __('admin.ReasonIsRequired') }}" });
			}
		});
	</script>
@stop
