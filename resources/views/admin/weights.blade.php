@extends('layouts.admin')

@section('content')
<div class="">
	<div class="">
		@php
			$tabs = [
				['title' => __('admin.Questions Bank'), 'url' => 'dashboard/questions-bank', 'icon' => 'question-mark'],
				['title' => __('admin.Categories'), 'url' => 'dashboard/question-categories', 'icon' => 'category-2'],
				['title' => __('admin.weights'), 'url' => 'dashboard/weights', 'icon' => 'weight'],
			]
		@endphp
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
						<div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center">
							<h5 class="card-title mb-3 mb-md-0">{{ __('admin.all Weights') }}</h5>
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
								@if( $_user->ableToAny(['weights.create', 'weights.store'], $permissions) )
								<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CreateWeights">
									<i class="ti ti-plus me-2"></i> {{ __('cycles.Add New') }}
								</button>
								@endif
							</div>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<!-- Filter Controls -->
								<div class="row mb-8 mt-8">
									<div class="col-md-1 mb-3 mb-md-0">
										<label class="form-label">{{ __('admin.Show') }}</label>
										<select id="showEntries" class="form-select form-select-sm">
											<option value="10">10</option>
											<option value="25">25</option>
											<option value="50">50</option>
											<option value="100">100</option>
											<option value="-1">{{ __('admin.All') }}</option>
										</select>
									</div>
									<div class="col-md-2 mb-3 mb-md-0">
										<label class="form-label">{{ __('admin.StageName') }}</label>
										<select id="stageFilter" class="form-select form-select-sm">
											<option value="">{{ __('admin.All') }}</option>
										</select>
									</div>
									<div class="col-md-2 mb-3 mb-md-0">
										<label class="form-label">{{ __('admin.Category') }}</label>
										<select id="categoryFilter" class="form-select form-select-sm">
											<option value="">{{ __('admin.All') }}</option>
										</select>
									</div>
									<div class="col-md-2 mb-3 mb-md-0">
										<label class="form-label">{{ __('admin.subCategory_Name') }}</label>
										<select id="subcategoryFilter" class="form-select form-select-sm">
											<option value="">{{ __('admin.All') }}</option>
										</select>
									</div>
									<div class="col-md-2 mb-3 mb-md-0">
										<label class="form-label">{{ __('admin.Weight') }}</label>
										<select id="weightFilter" class="form-select form-select-sm">
											<option value="">{{ __('admin.All') }}</option>
										</select>
									</div>
									<div class="col-md-3 ps-md-12 mb-3 mb-md-0">
										<label class="form-label">{{ __('admin.Search') }}</label>
										<div class="input-group input-group-sm">
											<input type="text" id="globalSearch" class="form-control" placeholder="{{ __('admin.Search') }}...">
										</div>
									</div>
								</div>
								<table class="table mb-0" id="userstable">
									<thead class="table-light">
										<tr>
											<th>#</th>
											<th>{{ __('admin.StageName') }}</th>
											<th>{{ __('admin.Category') }}</th>
											<th>{{ __('admin.subCategory_Name') }}</th>
											<th>{{ __('admin.Weight') }}</th>
											<th>{{ __('admin.Created at') }}</th>
											@if( $_user->ableToAny(['weights.show', 'weights.edit', 'weights.update', 'weights.destroy'],
											$permissions) )<th>{{ __('admin.Actions') }}</th>@endif
										</tr>
									</thead>
									<tbody>
										@php $c = 1; @endphp
										@foreach ($weights as $weight)
										<tr>
											<td>{{$c++}}</td>
											<td>{{$weight->stage->{'name_'.$l} }}</td>
											<td>{{$weight->category->{'category_name_' . $l} }}</td>
											<td>{{$weight->subcategory->{'name_' . $l} }}</td>
											<td>{{$weight->weight}}</td>
											<td>{{$weight->created_at}}</td>
											@if( $_user->ableToAny(['weights.show', 'weights.edit', 'weights.update', 'weights.destroy'],
											$permissions) )
											<td>
												@if( $_user->ableTo('weights.show', $permissions) )
												<button type="button" class="btn btn-icon btn-xm rounded-circle btn-label-info"
													data-bs-placement="top" title="{{ __('admin.Show') }}" data-bs-toggle="modal"
													data-bs-target="#ShowWeight{{ $weight->id }}">
													<i class="ti ti-eye"></i>
												</button>
												@endif
												@if( $_user->ableToAny(['weights.edit', 'weights.update'], $permissions) )
												<button type="button" class="btn btn-icon btn-xm rounded-circle btn-label-primary"
													data-bs-placement="top" title="{{ __('admin.Edit') }}" data-bs-toggle="modal"
													data-bs-target="#EditWeight{{ $weight->id }}">
													<i class="ti ti-edit"></i>
												</button>
												@endif
												@if( $_user->ableTo('weights.destroy', $permissions) )
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
										@if( $_user->ableToAny(['weights.edit', 'weights.update'], $permissions) )
										<!-- Edit Weight Modal -->
										<div class="modal fade" id="EditWeight{{ $weight->id }}" tabindex="-1" aria-hidden="true">
											<div class="modal-dialog modal-md">
												<div class="modal-content">
													<div class="modal-header">
														<h4 class="modal-title mb-0">
															{{__('admin.Edit')}} {{__('admin.Weight')}}
														</h4>
														<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
													</div>
													<div class="modal-body">
														<form class="add-new-user pt-0" action="{{ route('weights.update', $weight->id) }}"
															method="POST">
															@csrf
															@method('PUT')
															<div class="form-group mb-4">
																<label class="form-label" for="stage_id_{{ $weight->id }}">{{ __('admin.StageName')
																	}}</label>
																<select class="form-select" name="stage_id" id="stage_id_{{ $weight->id }}">
																	<option value="" disabled>{{ $l == 'en' ? 'Choose Stage' : 'اختر المرحلة' }}
																	</option>
																	@foreach ($stages as $stage)
																	<option value="{{ $stage->id }}" {{ $stage->id == $weight->stage_id ? 'selected' :
																		'' }}>
																		{{ $stage->{'name_'. $l} }}
																	</option>
																	@endforeach
																</select>
															</div>
															<div class="form-group mb-4">
																<label class="form-label" for="category_id_{{ $weight->id }}">{{ __('admin.Category')
																	}}</label>
																<select class="category_id form-select" name="category_id"
																	id="category_id_{{ $weight->id }}">
																	<option value="" disabled>{{ $l == 'en' ? 'Choose category' : 'اختر القسم' }}
																	</option>
																	@foreach ($categories as $category)
																	<option value="{{ $category->id }}" {{ $category->id == $weight->category_id ?
																		'selected' : '' }}>
																		{{ $category->{'category_name_'. $l} }}
																	</option>
																	@endforeach
																</select>
															</div>
															<div class="form-group mb-4">
																<label class="form-label" for="subcategory_id_{{ $weight->id }}">{{
																	__('admin.subCategory_Name')}}</label>
																<select class="subcategory_id form-select" name="subcategory_id"
																	id="subcategory_id_{{ $weight->id }}">
																	<option value="" disabled>{{ $l == 'en' ? 'Choose subcategory' : 'اختر القسم
																		الفرعي' }}</option>
																	@foreach ($subcategories as $subcategory)
																	<option value="{{ $subcategory->id }}" {{ $subcategory->id ==
																		$weight->subcategory_id ? 'selected' : '' }}>
																		{{ $subcategory->{'name_'. $l} }}
																	</option>
																	@endforeach
																</select>
															</div>
															<div class="form-group mb-8">
																<label class="form-label" for="weights_{{ $weight->id }}">{{__('admin.Weight')}}</label>
																<input class="form-control" id="weights_{{ $weight->id }}" type="number" name="weights"
																	value="{{ $weight->weight }}" placeholder="0" min="0" step="0.01" />
															</div>

															<div class="d-flex gap-2 w-100 justify-content-end">
																<button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
																	{{__('admin.Cancel')}}
																</button>
																@if( $_user->ableToAny(['weights.edit', 'weights.update'], $permissions) )
																<button type="submit" class="btn btn-primary">
																	{{__('admin.submit')}}
																</button>
																@endif
															</div>
														</form>
													</div>
												</div>
											</div>
										</div>
										<!--/ Edit JO Modal -->
										@endif
										@if( $_user->ableTo('weights.show', $permissions) )
										<!-- Show Customer Modal -->
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
																<span>{{ $weight->stage->{'name_'.$l} }}</span>
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
														<button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
															{{ __('admin.Close') }}
														</button>
													</div>
												</div>
											</div>
										</div>
										@endif
										<!--/ Show Customer Modal -->
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

				@if( $_user->ableToAny(['weights.create', 'weights.store'], $permissions) )
				<!-- Create weight Modal -->
				<div class="modal fade" id="CreateWeights" tabindex="-1" aria-hidden="true">
					<div class="modal-dialog modal-md">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title">{{__('admin.Add')}} {{__('admin.Weight')}}</h4>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<form method="POST" action="{{route('weights.store')}}" class="pt-0">
									@csrf
									<div class="mb-4">
										<label class="form-label"> {{ __('admin.StageName') }} </label>
										<select class=" form-select" name="stage_id">
											<option value="" disabled selected> {{ $l == 'en' ? 'Choose Stage' : 'اختر المرحلة' }} </option>
											@foreach ($stages as $stage)
											<option value="{{ $stage->id }}">{{ $stage->{'name_'. $l} }}</option>
											@endforeach
										</select>
									</div>
									<div class="mb-4">
										<label class="form-label"> {{ __('admin.Category') }} </label>
										<select class="category_id form-select" name="category_id">
											<option value="" disabled selected> {{ $l == 'en' ? 'Choose category' : 'اختر القسم' }}
											</option>
											@foreach ($categories as $category)
											<option value="{{ $category->id }}">{{ $category->{'category_name_'. $l} }}</option>
											@endforeach
										</select>
									</div>
									<div class="mb-4">
										<label class="form-label"> {{ __('admin.subCategory_Name') }} </label>
										<select class="subcategory_id form-select" name="subcategory_id">
											<option value="" disabled selected> {{ $l == 'en' ? 'Choose subcategory' : 'اختر القسم الفرعي'
												}} </option>
											@foreach ($subcategories as $subcategory)
											<option value="{{ $subcategory->id }}">{{ $subcategory->{'name_'. $l} }}</option>
											@endforeach
										</select>
									</div>
									{{-- <div class="mb-4">
										<label class="form-label"> {{ __('admin.Question') }} </label>
										<select class=" form-select" name="question_id">
											<option value="" disabled selected> {{ $l == 'en' ? 'Choose Question' : 'اختر السؤال ' }}
											</option>
											@foreach ($questions as $question)
											<option value="{{ $question->id }}">{{ $question->question }}</option>
											@endforeach
										</select>
									</div> --}}
									<div class="mb-12">
										<label for="weights" class="form-label">{{__('admin.Weight')}}</label>
										<input class="form-control" id="weights" type="number" name="weights" autocomplete="off" />
									</div>
									<div class="d-flex justify-content-end align-items-center gap-2">
										<button type="reset" class="btn btn-label-secondary"
											data-bs-dismiss="modal">{{__('admin.Cancel')}}</button>
										@if( $_user->ableToAny(['weights.create', 'weights.store'], $permissions) )
										<button type="submit" class="btn btn-primary data-submit">{{__('admin.submit')}}</button>
										@endif
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<!--/ Create wight Modal -->
				@endif
		</div>
	</div>
</div>
<!-- END: Content-->
@stop

@section('scripts')
<script>
	$(document).ready(function() {
		const isArabic = "{{ $l }}" === "ar";
		// Initialize DataTable
		var table = $('#userstable').DataTable({
			"responsive": true,
			"searching": true,
			"lengthChange": false,
			"paging": true,
			"bInfo": true,
			"pageLength": 10,
			"order": [[5, "desc"]], // Sort by Created at column (newest to oldest)
			"columnDefs": [
				{"orderable": true, "targets": [1, 2, 3, 4, 5]},
				{"orderable": false, "targets": [0, 6]}
			],
			"dom": '<"row"<"col-sm-12"t>><"row"<"col-sm-5"i><"col-sm-7"p>>',
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
			},
			@endif
			buttons: [
				{
					extend: 'excel',
					title: isArabic ? 'الأوزان' : 'Weights',
					exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
				},
				{
					extend: 'csv',
					title: isArabic ? 'الأوزان' : 'Weights',
					exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
				},
			],
		});

		// Export button handlers
		$('#exportExcel').on('click', () => table.button('.buttons-excel').trigger());
		$('#exportCsv').on('click', () => table.button('.buttons-csv').trigger());

		// Populate filter dropdowns based on current table data
		function populateFilters() {
			var stageOptions = [];
			var categoryOptions = [];
			var subcategoryOptions = [];
			var weightOptions = [];

			// Get unique values from table
			table.rows().every(function() {
				var rowNode = this.node();
				var stage = $(rowNode).find('td:eq(1)').text().trim();
				var category = $(rowNode).find('td:eq(2)').text().trim();
				var subcategory = $(rowNode).find('td:eq(3)').text().trim();
				var weight = $(rowNode).find('td:eq(4)').text().trim();

				if (stage && stageOptions.indexOf(stage) === -1) {
					stageOptions.push(stage);
				}
				if (category && categoryOptions.indexOf(category) === -1) {
					categoryOptions.push(category);
				}
				if (subcategory && subcategoryOptions.indexOf(subcategory) === -1) {
					subcategoryOptions.push(subcategory);
				}
				if (weight && weightOptions.indexOf(weight) === -1) {
					weightOptions.push(weight);
				}
			});

			// Sort options
			stageOptions.sort();
			categoryOptions.sort();
			subcategoryOptions.sort();
			weightOptions.sort(function(a, b) { return parseFloat(a) - parseFloat(b); });

			// Populate dropdowns
			var stageSelect = $('#stageFilter');
			var categorySelect = $('#categoryFilter');
			var subcategorySelect = $('#subcategoryFilter');
			var weightSelect = $('#weightFilter');

			stageSelect.empty().append('<option value="">{{ __('admin.All') }}</option>');
			categorySelect.empty().append('<option value="">{{ __('admin.All') }}</option>');
			subcategorySelect.empty().append('<option value="">{{ __('admin.All') }}</option>');
			weightSelect.empty().append('<option value="">{{ __('admin.All') }}</option>');

			$.each(stageOptions, function(index, value) {
				stageSelect.append('<option value="' + value + '">' + value + '</option>');
			});

			$.each(categoryOptions, function(index, value) {
				categorySelect.append('<option value="' + value + '">' + value + '</option>');
			});

			$.each(subcategoryOptions, function(index, value) {
				subcategorySelect.append('<option value="' + value + '">' + value + '</option>');
			});

			$.each(weightOptions, function(index, value) {
				weightSelect.append('<option value="' + value + '">' + value + '</option>');
			});
		}

		// Initialize filters
		populateFilters();

		// Show entries change
		$('#showEntries').on('change', function() {
			var value = $(this).val();
			table.page.len(value).draw();
		});

		// Filter functionality
		$('#stageFilter').on('change', function() {
			var value = $(this).val();
			table.column(1).search(value).draw();
		});

		$('#categoryFilter').on('change', function() {
			var value = $(this).val();
			table.column(2).search(value).draw();
		});

		$('#subcategoryFilter').on('change', function() {
			var value = $(this).val();
			table.column(3).search(value).draw();
		});

		$('#weightFilter').on('change', function() {
			var value = $(this).val();
			table.column(4).search(value).draw();
		});

		// Clear all filters
		$('#clearSearch').on('click', function() {
			$('#globalSearch').val('');
			table.search('').draw();
		});

		// Global search functionality
		$('#globalSearch').on('keyup', function() {
			table.search(this.value).draw();
		});

		// Existing subcategory functionality
		$('.category_id').change(function() {
			var category_id = $(this).val();
			var $state = $('.subcategory_id');
			$.ajax({
				url: "{{ route('subcategories.list_by_category') }}",
				method: 'GET',
				data: {
					category_id: category_id
				},
				success: function(data) {
					$state.html('<option value="">{{ __('admin.SubName') }}</option>');
					$.each(data, function(id, value) { $state.append('<option value="' + id + '" >' + value + '</option>'); });
				},
				error: function(xhr, status, error) { console.error('Error:', error); }
			});
		});

		// Handle delete action with SweetAlert
		$(document).on("click", ".delete-weight-action", function (e) {
			e.preventDefault();
			var url = $(this).data('url');
			var id = $(this).data('id');
			var token = '{{ csrf_token() }}';
			
			Swal.fire({
				title: "{{__('admin.SureDelete')}}",
				text: "{{__('admin.Recover')}}",
				icon: "warning",
				showCancelButton: true,
				confirmButtonText: "{{ __('admin.DYes') }}",
				cancelButtonText: "{{ __('admin.No') }}",
				reverseButtons: true,
				customClass: {
					confirmButton: 'btn btn-danger me-2',
					cancelButton: 'btn btn-label-secondary',
				},
				buttonsStyling: false,
			})
			.then((result) => {
				if (result.value) {
					$.post(url, {'_method': 'DELETE' , '_token': token},
						function(response) {
							releaseSuccessAlert({
								title: "{{ __('admin.Deleted') }}!",
								message: "{{ __('admin.DeletedSuccessfully') }}.",
							});
							// Remove the row from DataTable
							table.row($(e.target).closest('tr')).remove().draw();
							// Repopulate filters after row removal
							populateFilters();
						}
					).fail(function(error) { 
						releaseFailureAlert({
							title: "{{ __('admin.Error') }}!",
							message: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
						});
					});
				}
			});
		});
	});
</script>
@stop