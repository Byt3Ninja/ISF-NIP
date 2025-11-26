@extends('layouts.admin')

@section('title', __('admin.serviceProvidersRequests') )
@section('subTitle', __('admin.serviceProvidersRequests'))

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
	<div class="content-wrapper">
		<div class="container-xxl flex-grow-1 container-p-y">
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
					<h5 class="card-title mb-0">{{__('admin.serviceProvidersRequests')}}</h5>
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
				<div class="card-body">
					<div class="row my-6">
						<div class="col-12 col-md-2">
							<div class="form-floating">
								<select class="form-select" name="filter-request_status" id="filter-request_status" aria-label="Status">
									<option value="">{{ __('admin.All') }}</option>
									<option>{{ __('admin.Pending') }}</option>
									<option>{{ __('admin.Approved') }}</option>
									<option>{{ __('admin.Rejected') }}</option>
								</select>
								<label for="filter-request_status">Status</label>
							</div>
						</div>
						<div class="col-12 col-md-2 d-none align-items-end">
							<button id="clear-filters" class="btn btn-sm btn-outline-secondary">{{ __('admin.Clear') }}</button>
						</div>
					</div>
					<table id="table table-hover providers-requests-datatable" class="table providers-requests-datatable">
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
							@forelse( $requests as $provider )
								<tr>
									<td> {{ $loop->iteration }} </td>
									<td> {{ $provider->company_name }} </td>
									<td> {{ $provider->contact_name }} </td>
									<td> {{ $provider->email }} </td>
									<td>
										@if( is_null( $provider->status ) )
											<span data-toggle="tooltip" data-placement="top" title="{{ $provider->submitted_at }}" class="badge bg-label-secondary">{{ __('admin.pending') }}</span>
										@else
											@if( $provider->status == 1 ) <span class="badge bg-label-success">{{ __('admin.Approved') }}</span> @endif
											@if( $provider->status == 0 ) <span class="badge bg-label-danger">{{ __('admin.Rejected') }}</span> @endif
										@endif
									</td>
									<td>
										<div class="dropdown">
											<button class="btn btn-xs btn-dark" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
												<i class="ti ti-list-details me-2"></i> {{ __('admin.Actions') }}
											</button>
											<ul class="dropdown-menu">
												<li class="dropdown-item">
													<a class="text-dark load-details" data-id="{{ $provider->id }}" href="javascript: void(0);"> <i class="fas fa-sm fa-fw fa-info"></i> Details</a>
												</li>
												@if( is_null( $provider->status ) )
												<li class="dropdown-item">
													<a class="text-dark approve-request" data-id="{{ $provider->id }}" href="javascript: void(0);"> <i class="fas fa-sm fa-fw fa-check"></i> Approve</a>
												</li>
												<li class="dropdown-item">
													<a class="text-dark reject-request" data-id="{{ $provider->id }}" href="javascript: void(0);"> <i class="fas fa-sm fa-fw fa-times"></i> Reject</a>
												</li>
												@endif
											</ul>
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
								<textarea class="form-control" name="action-reject_reason" id="action-reject_reason"></textarea>
								<label style="padding-left: 25px;" for="action-reject_reason">Reason</label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.Close') }}</button>
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
	<script src="/app-assets/plugins/alertify/alertify.min.js"></script>
	<script type="text/javascript">
		alertify.defaults.transition = 'zoom';
		alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
		alertify.defaults.theme.cancel = 'btn btn-sm btn-info';
		alertify.defaults.theme.input = 'form-control';
		function initTooltips() {
			$('[data-toggle="tooltip"]').tooltip();
		}
		var requests = @json($requests);
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
			var table = $('.table.providers-requests-datatable').DataTable({
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
					_status = `<span data-toggle="tooltip" data-placement="top" title="${request.submitted_at}" class="badge bg-label-secondary">{{ __('admin.pending') }}</span>`;
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
			} else { alertify.error("{{ __('admin.serviceProviderRequestNotFound') }}"); }
		});

		$(document).on('click', '.approve-request', function(e) {
			e.preventDefault();
			var requestId = $(this).data('id');
			var request = requests.find(function(request) { return request.id == requestId; });
			if( typeof request !== 'undefined' )
			{
				if( request.status === null )
				{
					alertify.confirm(
						"{{ __('admin.Confirm Action') }}",
						"{!! __('admin.ConfirmApproveMessage') !!}",
						function() {
							$("#request_id").val(request.id);
							$("#request_action").val('approve');
							$("#approve-reject-form").submit();
						},
						function(){}
					).set('labels', {ok: "<i class='fa fa-check'></i> {{ __('admin.Yes') }}", cancel: "{{ __('admin.cancel') }}"});

				} else { alertify.error("{{ __('auth.serviceProviderRequestHaveActionTakenAlready') }}"); }
			} else { alertify.error("{{ __('admin.serviceProviderRequestNotFound') }}"); }
		});

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
				} else { alertify.error("{{ __('auth.serviceProviderRequestHaveActionTakenAlready') }}"); }
			} else { alertify.error("{{ __('admin.serviceProviderRequestNotFound') }}"); }
		});

		$(document).on('click', '#approve-rejectFormBtn', function(e) {
			e.preventDefault();
			var reason = $("#action-reject_reason").val().trim();
			if( reason )
			{
				$("#request_reason").val( reason );
				$("#approve-reject-form").submit();
			} else { alertify.error("{{ __('admin.ReasonIsRequired') }}"); }
		});
	</script>
@stop