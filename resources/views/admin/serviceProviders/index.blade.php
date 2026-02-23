@extends('layouts.admin')

@section('title', __('admin.ServiceProviders') )
@section('subTitle', __('admin.ServiceProviders'))

@section('style')
	<link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify.min.css">
	<link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify-bootstrap.min.css">
	<style type="text/css">
		#action-reject_reason {
			resize: none !important;
			height: 125px !important;
		}
	</style>
@stop

@section('content')
	<div class="">
		<div class="">
			<div class="row">
				@if( session()->has('success') )
				<div class="col-md-12 mb-2">
					<div role="alert" class="alert alert-success alert-dismissible">
						{{ session()->get('success') }}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				</div>
				@endif
				@if( session()->has('error') )
				<div class="col-md-12 mb-2">
					<div role="alert" class="alert alert-danger alert-dismissible">
						{{ session()->get('error') }}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				</div>
				@endif
			</div>

			<div class="card">
				<div class="card-header border-bottom d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">{{__('admin.ServiceProviders')}}</h5>
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
						@if( $_user->ableTo('services_providers.requests', $permissions) )
							<div class="invoice-create-btn">
								<a href="{{ route('services_providers.requests') }}" class="btn btn-primary glow">
									{{ __('admin.serviceProvidersRequests') }}
								</a>
							</div>
						@endif
					</div>
				</div>
				<div class="card-body">
					<div class="row my-6">
						<div class="col-12 col-md-2">
							<div class="form-floating">
								<select class="form-select" name="filter-request_status" id="filter-request_status" aria-label="Status">
									<option value="">{{ __('admin.All') }}</option>
									<option>{{ __('admin.Active') }}</option>
									<option>{{ __('admin.Not Active') }}</option>
								</select>
								<label for="filter-request_status">Status</label>
							</div>
						</div>
						<div class="col-12 col-md-2 d-none align-items-end">
							<button id="clear-filters" class="btn btn-sm btn-outline-secondary">{{ __('admin.Clear') }}</button>
						</div>
					</div>
					<table class="table table-hover providers-datatable">
						<thead>
							<tr class="table-light">
								<th>#</th>
								<th>{{ __('auth.companyName') }}</th>
								<th>{{ __('auth.contactName') }}</th>
								<th>{{ __('auth.E-Mail') }}</th>
								<th>{{ __('admin.status') }}</th>
								<th style="width: 100px;" class="text-center">{{__('admin.Actions')}}</th>
							</tr>
						</thead>
						<tbody>
							@forelse( $providers as $provider )
								<tr>
									<td> {{ $loop->iteration }} </td>
									<td> {{ $provider->company_name }} </td>
									<td>
										<div class="d-flex align-items-center w-px-150">
											<a target="_blank" href="/dashboard/users/{{ $provider->user->id }}/edit">
												<img onerror="this.src='/images/logo-placeholder.png'" src="{{ $provider->user->img_url }}" alt="avatar" class="rounded-circle me-2 border bg-label-light" height="40" width="40">
											</a>
											<a target="_blank" href="/dashboard/users/{{ $provider->user->id }}/edit">
												{{ $provider->contact_name }}
											</a>
										</div>
									</td>
									<td> {{ $provider->email }} </td>
									<td>
										@if( $provider->status == 1 ) <span class="badge bg-label-success">{{ __('admin.Active') }}</span> @endif
										@if( $provider->status == 0 ) <span class="badge bg-label-danger">{{ __('admin.Not Active') }}</span> @endif
									</td>
									<td>
										<div class="d-flex gap-1 justify-content-center">
											@if($_user->ableTo('service_provider.profile', $permissions))
											<a href="{{ route('service_provider.profile', $provider->id) }}" class="btn btn-icon btn-sm btn-text-secondary" data-toggle="tooltip" title="{{ __('admin.View') }}">
												<i class="ti ti-eye"></i>
											</a>
											@endif
											@if($_user->ableTo('services_providers.edit', $permissions))
											<a href="{{ route('services_providers.edit_profile', $provider->id) }}" class="btn btn-icon btn-sm btn-text-secondary" data-toggle="tooltip" title="{{ __('admin.Edit') }}">
												<i class="ti ti-edit"></i>
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
	</div>
@stop

@section('scripts')
	<script src="/app-assets/plugins/alertify/alertify.min.js"></script>
	<script type="text/javascript">
		alertify.defaults.transition = 'zoom';
		alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
		alertify.defaults.theme.cancel = 'btn btn-sm btn-info';
		alertify.defaults.theme.input = 'form-control';
		function initTooltips() {
			$('[data-toggle="tooltip"]').tooltip();
		}
		var providers = @json($providers);
		function toggleClearButton() {
			const status = $('#filter-request_status').val();
			if (status) {
				$('#clear-filters').parent().removeClass('d-none').addClass('d-flex');
			} else {
				$('#clear-filters').parent().removeClass('d-flex').addClass('d-none');
			}
		}

		$(document).ready(function() {
			initTooltips();
			const observer = new MutationObserver(() => {
				initTooltips();
			});
			observer.observe(document.body, { childList: true, subtree: true });
			var table = $('.table.providers-datatable').DataTable({
				responsive: true,
				pageLength: 10,
				lengthMenu: [10, 25, 50, 100],
				buttons: [
					{
						extend: 'csv',
						text: 'CSV',
						className: 'd-none'
					},
					{
						extend: 'excel',
						text: 'Excel',
						className: 'd-none'
					}
				],
				@if( $l == 'ar' )
				'language': {
					'sEmptyTable': "ليست هناك بيانات متاحة في الجدول",
					'sLoadingRecords': "جارٍ التحميل...",
					'sProcessing': "جارٍ التحميل...",
					'sLengthMenu': "أظهر _MENU_ مدخلات",
					'sZeroRecords': "لم يعثر على أية سجلات",
					'sInfo': "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
					'sInfoEmpty': "يعرض 0 إلى 0 من أصل 0 سجل",
					'sInfoFiltered': "(منتقاة من مجموع _MAX_ مُدخل)",
					'sInfoPostFix': "",
					'sSearch': "ابحث:",
					'sUrl': "",
					'oPaginate': {
						'sFirst': "الأول",
						'sPrevious': "السابق",
						'sNext': "التالي",
						'sLast': "الأخير"
					},
					'oAria': {
						'sSortAscending': ": تفعيل لترتيب العمود تصاعدياً",
						'sSortDescending': ": تفعيل لترتيب العمود تنازلياً"
					}
				}
				@endif
			});

			// Export buttons
			$('#export-csv').on('click', function(e) {
				e.preventDefault();
				table.button(0).trigger();
			});

			$('#export-excel').on('click', function(e) {
				e.preventDefault();
				table.button(1).trigger();
			});

			$('#filter-request_status').on('change', function() {
				var val = this.value;
				table.column(4).search(val).draw();
				toggleClearButton();
			});

			$('#clear-filters').on('click', function() {
				$('#filter-request_status').val('');
				table.columns().search('').draw();
				$(this).parent().removeClass('d-flex').addClass('d-none');
			});
		});
	</script>
@stop