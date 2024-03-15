@extends('layouts.admin')
@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('title', __('admin.Organizations') )
@section('subTitle', __('admin.Organizations`s List'))
@section('content')
	<div class="app-content content">
		<div class="content-overlay"></div>
		<div class="content-wrapper">
			<div class="content-header row">
				<div class="content-header-left col-12 mb-2 mt-1">
					<div class="row breadcrumbs-top">
						<div class="col-12">
							<h5 class="content-header-title float-left pr-1 mb-0">{{__('admin.Organizations')}}</h5>
							<div class="breadcrumb-wrapper col-12">
								<ol class="breadcrumb p-0 mb-0">
									<li class="breadcrumb-item"><a href="/admin"><i class="bx bx-home-alt"></i></a></li>
									<li class="breadcrumb-item active">{{__('admin.Organizations`s List')}}</li>
								</ol>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="content-header row">
				@if(session()->has('msg'))
					<div class="col-md-12">
						<div class="alert alert-success alert-dismissible mb-2" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
							<div class="d-flex align-items-center"> <i class="bx bx-like"></i> <span> {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
						</div>
					</div>
				@endif
			</div>
			<div class="content-body">
				<section class="organizations-list-wrapper">
					<div class="row pb-1">
						<div class="col-12 col-sm-3 col-lg-3 col-mg-3 offset-sm-9 offset-lg-9 offset-md-9 d-flex align-items-center">
							<a href="{{ route('organizations.create') }}" class="btn btn-block btn-success"> {{ __('admin.Add New')}} </a>
						</div>
					</div>
					<div class="organizations-list-table">
						<div class="card">
							<div class="card-content">
								<div class="card-body">
									<!-- datatable start -->
									<div class="table-responsive">
										<table id="organizations-list-datatable" class="table">
											<thead>
											<tr class="table-light">
												<th>#</th>
												<th>{{__('admin.Name')}}</th>
												<th>{{__('admin.Email')}}</th>
												<th>{{__('admin.Phone')}}</th>
												<th>@can('delete_user'){{__('admin.Delete')}}@endcan</th>
											</tr>
											</thead>
											<tbody>
											@foreach( $organizations as $organization )
											<tr>
												<td><a href="{{route('organizations.edit', ['id' => $organization->id])}}">{{$organization->id}}</a></td>
												<td style="font-size: small;"><a href="{{route('organizations.edit', ['id' => $organization->id])}}"> {{ $organization->{'name_'.$l} }}</a></td>
												<td style="font-size: small;">{{$organization->user->email}}</td>
												<td style="font-size: small;">{{$organization->user->phone}}</td>
												<td style="padding: 0px;font-size: small;">
													@can('delete_user')
														<button id='deleteAction' data-id='{{$organization->id}}' data-url="{{route('organizations.destroy', ['id' => $organization->id])}}" type="button" class="btn btn-outline-danger btn-sm"><i class="bx bx-trash"> </i></button>
													@endcan
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
				</section>
			</div>
		</div>
	</div>
@stop
@section('scripts')
<script src="/app-assets/js/scripts/pages/page-organizations.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
<script src="/app-assets/js/scripts/datatables/datatable.js"></script>
<script src="/app-assets/js/scripts/modal/components-modal.js"></script>

<script>
	$(document).on("click", "#deleteAction", function (e) {
		var url = $(this).data('url');
		var token = '{{ csrf_token() }}';
		Swal.fire({
			title: "{{__('admin.SureDelete')}}",
			text: "{{__('admin.Recover')}}",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "{{__('admin.DYes')}}",
			cancelButtonText: "{{__('admin.No')}}!",
		})
		.then((result) => {
			if (result.value)
			{
				$.post(url, {'_method': 'DELETE' , '_token': token},
					function(response)
					{
						Swal.fire({
							type: 'success',
							title: "{{ __('admin.Deleted') }}!",
							html: "{{ __('admin.FileDeleted') }}.",
						});
						location.reload();
					}
				).fail(function(error) { console.log(error); } );
			}
		})
	});
</script>
@stop
