@extends('layouts.admin')

@section('title', __('admin.ServiceProviders') )
@section('subTitle', __('admin.ServiceProviders'))

@section('style')
@stop

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

	{{-- Tabs Navigation --}}
	<br />
	<x-navigation-pills :tabs="$tabs" />

	<div class="card">
		<div class="card-header border-bottom d-flex justify-content-between align-items-center">
			<h5 class="card-title mb-0">{{ __('admin.ServiceProviders') }}</h5>
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
@stop

@section('scripts')
	<script type="text/javascript">

		/* ── Tooltip initialiser (re-called after every DataTable redraw) ── */
		function initTooltips() {
			$('[data-toggle="tooltip"]').tooltip();
		}

		/* ── Strip HTML → plain text (shared utility for sort/filter/options) ── */
		function stripHtml(html) {
			const div = document.createElement('div');
			div.innerHTML = html;
			return div.textContent.replace(/\s+/g, ' ').trim();
		}

		/* ================================================================
		 * DataTable
		 * ============================================================== */
		$(document).ready(function () {

			initTooltips();

			/* Reinitialise tooltips whenever the DOM changes (pagination, search) */
			const observer = new MutationObserver(initTooltips);
			observer.observe(document.body, { childList: true, subtree: true });

			const isAr = '{{ $l }}' === 'ar';

			const table = $('#providersTable').DataTable({
				responsive:  true,
				pageLength:  10,
				lengthMenu:  [10, 25, 50, 100],
				order:       [[0, 'asc']],

				/* ── Column definitions ──────────────────────────────── */
				columnDefs: [
					{
						/* Contact (col 2) contains anchor + img; Status (col 4)
						   contains a badge — extract plain text for accurate
						   filter matching and column sorting.                  */
						targets:  [2, 4],
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

			/* ============================================================
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

				/* Status  — col 4, exact match so "Active" won't catch "Not Active" */
				addColumnFilter(4, 'filter-status', '{{ __("admin.All_statuses") }}', true);
			}

			/* ============================================================
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

	</script>
@stop