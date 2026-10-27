@extends('layouts.admin')

@section('content')
@php
$tabs = [
  ['title' => __('admin.Questions Bank'), 'url' => 'dashboard/questions-bank', 'icon' => 'question-mark'],
  ['title' => __('admin.Categories'), 'url' => 'dashboard/question-categories', 'icon' => 'category-2'],
  ['title' => __('admin.weights'), 'url' => 'dashboard/weights', 'icon' => 'weight'],
];

// Permissions resolved once instead of on every row
$canCreate = $_user->ableToAny(['weights.create', 'weights.store'], $permissions);
$canShow = $_user->ableTo('weights.show', $permissions);
$canEdit = $_user->ableToAny(['weights.edit', 'weights.update'], $permissions);
$canDelete = $_user->ableTo('weights.destroy', $permissions);
$hasActions = $_user->ableToAny(['weights.show', 'weights.edit', 'weights.update', 'weights.destroy'], $permissions);
@endphp

<style>
	/* Search (full width on mobile) */
	#userstable_wrapper .dt-toolbar__search,
	#userstable_wrapper .dt-toolbar__search .dataTables_filter,
	#userstable_wrapper .dt-toolbar__search .dataTables_filter label { width: 100%; }
	#userstable_wrapper .dt-toolbar__search .dataTables_filter input { width: 100% !important; margin-left: 0; }

	/* Paging */
	#userstable_wrapper .dt-paging .pagination { flex-wrap: wrap; justify-content: center; margin-bottom: 0; }

	/* Filters: sized by CSS from the first paint */
	#userstable_wrapper .dt-toolbar-left { flex: 1 1 auto; min-width: 0; }
	#userstable_wrapper .dt-filter-container { flex: 1 1 100%; min-width: 0; }
	#userstable_wrapper .dt-filter-item { flex: 1 1 150px; min-width: 130px; max-width: 200px; }
	#userstable_wrapper .dt-filter-item .form-select,
	#userstable_wrapper .dt-filter-item .select2-container { width: 100% !important; }

	/* Length control */
	#userstable_wrapper .dataTables_length { margin: 0; }
	#userstable_wrapper .dataTables_length .form-select { width: auto; }
	#userstable_wrapper .dataTables_length .select2-container { min-width: 70px; max-width: 90px; }

	/* Hide the table chrome until initComplete, to avoid a flash of unstyled controls */
	#userstable_wrapper:not(.dt-ready) .card-header,
	#userstable_wrapper:not(.dt-ready) .card-footer { visibility: hidden; }

	@media (min-width: 992px) {
		#userstable_wrapper .dt-toolbar__search,
		#userstable_wrapper .dt-toolbar__search .dataTables_filter,
		#userstable_wrapper .dt-toolbar__search .dataTables_filter label { width: auto; }
		#userstable_wrapper .dt-toolbar__search .dataTables_filter input { width: auto !important; }

		/* one row: length + all filters, search pinned to the end */
		#userstable_wrapper .dt-toolbar-left,
		#userstable_wrapper .dt-filter-container { flex-wrap: nowrap !important; }
		#userstable_wrapper .dt-filter-container { flex: 1 1 auto; }
		#userstable_wrapper .dt-filter-item { flex: 1 1 0; min-width: 110px; max-width: 200px; }
		#userstable_wrapper .dt-toolbar__search { flex: 0 0 auto; }
	}
</style>

<div>
	<div>
		<x-navigation-pills :tabs="$tabs" />

		@if (session()->has('message'))
			<div class="col-md-12 mb-6">
				<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible" role="alert">
					{{ __('admin.' . session()->get('message')) }}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			</div>
		@endif

		<div class="content-body">
			<section class="row">
				<div class="col-xl-12 col-md-12 col-sm-12">
					<div class="card">
						<div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
							<h5 class="card-title mb-0">{{ __('admin.all Weights') }}</h5>
							<div class="d-flex flex-wrap align-items-center gap-2 gap-md-3">
								<div class="dropdown">
									<button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
									</button>
									<ul class="dropdown-menu" aria-labelledby="exportDropdown">
										<li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
										<li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
									</ul>
								</div>
								@if ($canCreate)
									<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CreateWeights">
										<i class="ti ti-plus me-2"></i> {{ __('cycles.Add New') }}
									</button>
								@endif
							</div>
						</div>

						<div class="card-datatable">
							<table class="table mb-0 w-100" id="userstable">
								<thead class="table-light">
									<tr>
										<th>#</th>
										<th>{{ __('admin.StageName') }}</th>
										<th>{{ __('admin.Category') }}</th>
										<th>{{ __('admin.subCategory_Name') }}</th>
										<th>{{ __('admin.Weight') }}</th>
										<th>{{ __('admin.Created at') }}</th>
										@if ($hasActions)<th>{{ __('admin.Actions') }}</th>@endif
									</tr>
								</thead>
								<tbody>
									@foreach ($weights as $weight)
										<tr>
											<td>{{ $loop->iteration }}</td>
											<td>{{ $weight->stage->{'name_' . $l} }}</td>
											<td>{{ $weight->category->{'category_name_' . $l} }}</td>
											<td>{{ $weight->subcategory->{'name_' . $l} }}</td>
											<td>{{ $weight->weight }}</td>
											<td>{{ $weight->created_at }}</td>
											@if ($hasActions)
												<td class="text-nowrap">
													@if ($canShow)
														<button type="button" class="btn btn-icon btn-xm rounded-circle btn-label-info"
															data-bs-placement="top" title="{{ __('admin.Show') }}" data-bs-toggle="modal"
															data-bs-target="#ShowWeight{{ $weight->id }}">
															<i class="ti ti-eye"></i>
														</button>
													@endif
													@if ($canEdit)
														<button type="button" class="btn btn-icon btn-xm rounded-circle btn-label-primary"
															data-bs-placement="top" title="{{ __('admin.Edit') }}" data-bs-toggle="modal"
															data-bs-target="#EditWeight{{ $weight->id }}">
															<i class="ti ti-edit"></i>
														</button>
													@endif
													@if ($canDelete)
														<button type="button" class="btn btn-icon btn-xm rounded-circle btn-label-danger delete-weight-action"
															data-bs-placement="top" title="{{ __('admin.Delete') }}"
															data-id="{{ $weight->id }}"
															data-url="{{ route('weights.destroy', $weight->id) }}">
															<i class="ti ti-trash"></i>
														</button>
													@endif
												</td>
											@endif
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>

				{{-- Modals live outside the table so DataTables/Responsive can manage rows freely --}}
				@foreach ($weights as $weight)
					@if ($canEdit)
						<!-- Edit Weight Modal -->
						<div class="modal fade" id="EditWeight{{ $weight->id }}" tabindex="-1" aria-hidden="true">
							<div class="modal-dialog modal-md">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title mb-0">{{ __('admin.Edit') }} {{ __('admin.Weight') }}</h4>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">
										<form class="add-new-user pt-0" action="{{ route('weights.update', $weight->id) }}" method="POST">
											@csrf
											@method('PUT')
											<div class="form-group mb-4">
												<label class="form-label" for="stage_id_{{ $weight->id }}">{{ __('admin.StageName') }}</label>
												<select class="form-select" name="stage_id" id="stage_id_{{ $weight->id }}">
													<option value="" disabled>{{ $l == 'en' ? 'Choose Stage' : 'اختر المرحلة' }}</option>
													@foreach ($stages as $stage)
														<option value="{{ $stage->id }}" @selected($stage->id == $weight->stage_id)>
															{{ $stage->{'name_' . $l} }}
														</option>
													@endforeach
												</select>
											</div>
											<div class="form-group mb-4">
												<label class="form-label" for="category_id_{{ $weight->id }}">{{ __('admin.Category') }}</label>
												<select class="category_id form-select" name="category_id" id="category_id_{{ $weight->id }}">
													<option value="" disabled>{{ $l == 'en' ? 'Choose category' : 'اختر القسم' }}</option>
													@foreach ($categories as $category)
														<option value="{{ $category->id }}" @selected($category->id == $weight->category_id)>
															{{ $category->{'category_name_' . $l} }}
														</option>
													@endforeach
												</select>
											</div>
											<div class="form-group mb-4">
												<label class="form-label" for="subcategory_id_{{ $weight->id }}">{{ __('admin.subCategory_Name') }}</label>
												<select class="subcategory_id form-select" name="subcategory_id" id="subcategory_id_{{ $weight->id }}">
													<option value="" disabled>{{ $l == 'en' ? 'Choose subcategory' : 'اختر القسم الفرعي' }}</option>
													@foreach ($subcategories as $subcategory)
														<option value="{{ $subcategory->id }}" @selected($subcategory->id == $weight->subcategory_id)>
															{{ $subcategory->{'name_' . $l} }}
														</option>
													@endforeach
												</select>
											</div>
											<div class="form-group mb-8">
												<label class="form-label" for="weights_{{ $weight->id }}">{{ __('admin.Weight') }}</label>
												<input class="form-control" id="weights_{{ $weight->id }}" type="number" name="weights"
													value="{{ $weight->weight }}" placeholder="0" min="0" step="0.01" />
											</div>
											<div class="d-flex gap-2 w-100 justify-content-end">
												<button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
												<button type="submit" class="btn btn-primary">{{ __('admin.submit') }}</button>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
						<!--/ Edit Weight Modal -->
					@endif

					@if ($canShow)
						<!-- Show Weight Modal -->
						<div class="modal fade" id="ShowWeight{{ $weight->id }}" tabindex="-1" aria-hidden="true">
							<div class="modal-dialog modal-md">
								<div class="modal-content">
									<div class="modal-header border-0 pb-0">
										<h5 class="modal-title px-3">{{ __('admin.Weight Details') }}</h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">
										<ul class="list-group list-group-flush">
											<li class="list-group-item d-flex justify-content-between align-items-center">
												<span class="fw-semibold">{{ __('admin.StageName') }}</span>
												<span>{{ $weight->stage->{'name_' . $l} }}</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center">
												<span class="fw-semibold">{{ __('admin.Category') }}</span>
												<span>{{ $weight->category->{'category_name_' . $l} }}</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center">
												<span class="fw-semibold">{{ __('admin.subCategory_Name') }}</span>
												<span>{{ $weight->subcategory->{'name_' . $l} }}</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center">
												<span class="fw-semibold">{{ __('admin.Weight') }}</span>
												<span>{{ $weight->weight }}</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center">
												<span class="fw-semibold">{{ __('admin.Created At') }}</span>
												<span>{{ $weight->created_at }}</span>
											</li>
										</ul>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Close') }}</button>
									</div>
								</div>
							</div>
						</div>
						<!--/ Show Weight Modal -->
					@endif
				@endforeach

				@if ($canCreate)
					<!-- Create Weight Modal -->
					<div class="modal fade" id="CreateWeights" tabindex="-1" aria-hidden="true">
						<div class="modal-dialog modal-md">
							<div class="modal-content">
								<div class="modal-header">
									<h4 class="modal-title">{{ __('admin.Add') }} {{ __('admin.Weight') }}</h4>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<form method="POST" action="{{ route('weights.store') }}" class="pt-0">
										@csrf
										<div class="mb-4">
											<label class="form-label">{{ __('admin.StageName') }}</label>
											<select class="form-select" name="stage_id">
												<option value="" disabled selected>{{ $l == 'en' ? 'Choose Stage' : 'اختر المرحلة' }}</option>
												@foreach ($stages as $stage)
													<option value="{{ $stage->id }}">{{ $stage->{'name_' . $l} }}</option>
												@endforeach
											</select>
										</div>
										<div class="mb-4">
											<label class="form-label">{{ __('admin.Category') }}</label>
											<select class="category_id form-select" name="category_id">
												<option value="" disabled selected>{{ $l == 'en' ? 'Choose category' : 'اختر القسم' }}</option>
												@foreach ($categories as $category)
													<option value="{{ $category->id }}">{{ $category->{'category_name_' . $l} }}</option>
												@endforeach
											</select>
										</div>
										<div class="mb-4">
											<label class="form-label">{{ __('admin.subCategory_Name') }}</label>
											<select class="subcategory_id form-select" name="subcategory_id">
												<option value="" disabled selected>{{ $l == 'en' ? 'Choose subcategory' : 'اختر القسم الفرعي' }}</option>
												@foreach ($subcategories as $subcategory)
													<option value="{{ $subcategory->id }}">{{ $subcategory->{'name_' . $l} }}</option>
												@endforeach
											</select>
										</div>
										{{-- <div class="mb-4">
											<label class="form-label"> {{ __('admin.Question') }} </label>
											<select class="form-select" name="question_id">
												<option value="" disabled selected> {{ $l == 'en' ? 'Choose Question' : 'اختر السؤال ' }}</option>
												@foreach ($questions as $question)
													<option value="{{ $question->id }}">{{ $question->question }}</option>
												@endforeach
											</select>
										</div> --}}
										<div class="mb-12">
											<label for="weights" class="form-label">{{ __('admin.Weight') }}</label>
											<input class="form-control" id="weights" type="number" name="weights" autocomplete="off" />
										</div>
										<div class="d-flex justify-content-end align-items-center gap-2">
											<button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
											<button type="submit" class="btn btn-primary data-submit">{{ __('admin.submit') }}</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<!--/ Create Weight Modal -->
				@endif
			</section>
		</div>
	</div>
</div>
<!-- END: Content-->
@stop

@section('scripts')
<script>
	$(document).ready(function () {
		const isArabic = "{{ $l }}" === "ar";
		const $wrapper = () => $('#userstable_wrapper');

		const table = $('#userstable').DataTable({
			...getSharedDataTableConfig({
				title: isArabic ? 'الأوزان' : 'Weights',
				descIndex: 5,
				actionsIndex: @json($hasActions ? 6 : null),
				withoutFilter: true
			}),
			responsive: true,
			autoWidth: false,
			pageLength: 10,
			lengthChange: true,
			lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
			order: [[5, "desc"]],
			columnDefs: [
				{ orderable: true, targets: [1, 2, 3, 4, 5] },
				{ orderable: false, targets: [0, @json($hasActions ? 6 : -1)] },
				// Which columns survive longest when the screen shrinks
				{ responsivePriority: 1, targets: 1 },
				{ responsivePriority: 2, targets: @json($hasActions ? 6 : 4) },
				{ responsivePriority: 3, targets: 4 }
			],
			dom:
				'<"card-header border-bottom d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-2 py-3 px-4"' +
					'<"dt-toolbar-left d-flex flex-wrap align-items-center gap-2"l<"dt-filter-container d-flex flex-wrap gap-2">>' +
					'<"dt-toolbar__search"f>' +
				'>' +
				't' +
				'<"card-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 py-2 px-4"' +
					'<"dt-info text-center text-md-start"i>' +
					'<"dt-paging"p>' +
				'>',
			initComplete: function () {
				const api = this.api();
				const $w = $wrapper();

				buildTableFilters(api);

				// Tidy native controls
				$w.find('.dataTables_filter .form-control').removeClass('form-control-sm').css('width', '');
				$w.find('.dataTables_length').each(function () {
					$(this).find('label').not(':first').remove();
				});
				$w.find('.dataTables_length .form-select').removeClass('form-select-sm');

				// select2 synchronously (no setTimeout) so layout is final before Responsive measures
				initSelect2($w.find('.filter-select2, .dataTables_length select'));

				$w.addClass('dt-ready');
				api.columns.adjust();
				api.responsive.recalc();
			}
		});

		// Export buttons
		$('#exportExcel').on('click', () => table.button('.buttons-excel').trigger());
		$('#exportCsv').on('click', () => table.button('.buttons-csv').trigger());

		function initSelect2($els) {
			$els.each(function () {
				const $this = $(this);
				if ($this.hasClass('select2-hidden-accessible')) return;
				$this.select2({
					dropdownParent: $this.parent(),
					width: '100%',
					minimumResultsForSearch: Infinity,
					allowClear: false
				});
			});
		}

		function buildTableFilters(api) {
			const container = $wrapper().find('.dt-filter-container').empty();

			function addFilter(colIdx, id, label) {
				const $item = $('<div class="dt-filter-item"></div>').appendTo(container);
				const select = $(`<select id="${id}" class="form-select filter-select2"><option value="">${label}</option></select>`)
					.appendTo($item)
					.on('change', function () {
						api.column(colIdx).search(this.value).draw();
					});

				const seen = new Set();
				api.column(colIdx).data().each(function (html) {
					const div = document.createElement('div');
					div.innerHTML = html;
					const text = div.textContent.replace(/\s+/g, ' ').trim();
					if (text) seen.add(text);
				});

				Array.from(seen).sort((a, b) => {
					if (!isNaN(a) && !isNaN(b)) return parseFloat(a) - parseFloat(b);
					return a.localeCompare(b);
				}).forEach(v => select.append(`<option value="${v}">${v}</option>`));

				return select;
			}

			addFilter(1, 'filterStage', '{{ __("admin.all Stages") }}');
			addFilter(2, 'filterCategory', '{{ __("admin.all Category") }}');
			addFilter(3, 'filterSubcategory', '{{ __("admin.all SupCategory") }}');
			addFilter(4, 'filterWeight', '{{ __("admin.all Weights") }}');
		}

		// Keep Responsive layout correct after every redraw (filtering, paging, deleting)
		table.on('draw', () => table.responsive.recalc());

		// Subcategory loader (unchanged logic)
		$('.category_id').on('change', function () {
			const category_id = $(this).val();
			const $state = $('.subcategory_id');
			$.ajax({
				url: "{{ route('subcategories.list_by_category') }}",
				method: 'GET',
				data: { category_id: category_id },
				success: function (data) {
					$state.html('<option value="">{{ __('admin.SubName') }}</option>');
					$.each(data, function (id, value) {
						$state.append('<option value="' + id + '">' + value + '</option>');
					});
				},
				error: function (xhr, status, error) { console.error('Error:', error); }
			});
		});

		// Delete with confirmation (delegated, so it works for responsive child rows too)
		$(document).on('click', '.delete-weight-action', function (e) {
			e.preventDefault();
			const $btn = $(this);
			const url = $btn.data('url');
			const token = '{{ csrf_token() }}';

			releaseAlert('warning', {
				title: "{{ __('admin.SureDelete') }}",
				message: "{{ __('admin.Recover') }}",
				confirmText: "{{ __('admin.DYes') }}",
				cancelText: "{{ __('admin.No') }}",
				confirmColor: 'danger'
			}).then((ok) => {
				if (!ok) return;

				$.post(url, { _method: 'DELETE', _token: token }, function () {
					releaseAlert('success', {
						title: "{{ __('admin.Deleted') }}!",
						message: "{{ __('admin.DeletedSuccessfully') }}.",
					});
					table.row($btn.closest('tr')).remove().draw();

					// Rebuild filters (was populateFilters(), which doesn't exist) and re-attach select2
					buildTableFilters(table);
					initSelect2($wrapper().find('.filter-select2'));
				}).fail(function (error) {
					releaseAlert('danger', {
						title: "{{ __('admin.Error') }}!",
						message: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
					});
				});
			});
		});
	});
</script>
@stop
