@extends('layouts.admin')

@section('title', __('admin.Organizations') )
@section('subTitle', __('admin.Organizations`s List'))

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop

@section('content')
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="content-overlay"></div>
		<div class="content-header row">
			@if(session()->has('msg'))
			<div class="alert alert-success alert-dismissible" role="alert">
				{{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
			@endif
			@if(session()->has('message2'))
			<div class="alert alert-danger alert-dismissible" role="alert">
				{{ __('admin.'.session()->get('message2')) }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		</div>
		@endif
	</div>

	@if( $_user->ableToAny(['organizations.create', 'organizations.store'], $permissions) )
	<div class="row mb-6">
		<div class="d-flex align-items-start w-100">
		<a href="{{ route('organizations.create') }}" class="btn btn-primary">
			<i class="ti ti-plus"></i></i>                 
			<span class="mx-2">{{ __('admin.Add New')}} </span>
		</a>
		</div>
	</div>
	@endif
	<div class="content-body">
		<section class="organizations-list-wrapper">
			<div class="organizations-list-table">
				<div class="card">
					<div class="card-header"> <h4 class="card-title mb-0">{{__('admin.Organizations')}}</h4> </div>
					<div class="card-content">
						<div class="card-body">
							<!-- datatable start -->
							<div class="table-responsive">
								<table id="organizations-list-datatable" class="table">
									<thead>
										<tr class="table-light">
											<th>#</th>
											<th>{{__('admin.Name')}}</th>
											<th>{{__('admin.Image')}}</th>
											<th>{{__('admin.Email')}}</th>
											<th>{{__('admin.Phone')}}</th>
											<th>{{__('admin.Startup')}}</th>
											<th>{{__('admin.Startups')}}</th>
											<th>{{__('admin.Cycles')}}</th>
											@if( $_user->ableToAny(['organizations.show', 'organizations.edit', 'organizations.update', 'organizations.destroy'], $permissions) )<th>{{ __('admin.Actions') }}</th>@endif
										</tr>
									</thead>
									<tbody>
									@foreach( $organizations as $organization )
									<tr>
										@if( $_user->ableToAny(['organizations.edit', 'organizations.update'], $permissions) )
										<td> <a href="{{ route('organizations.edit', ['organization' => $organization->id]) }}">{{ $organization->id }}</a> </td>
										<td style="font-size: small;"> <a href="{{route('organizations.edit', ['organization' => $organization->id])}}"> {{ $organization->{'name_'.$l} }}</a></td>
										@else
										<td> {{ $organization->id }} </td>
										<td style="font-size: small;"> {{ $organization->{'name_'.$l} }} </td>
										@endif
										<td>
											<div class="d-flex align-items-center">
												<a class="media-left mr-50" href="{{route('organizations.edit', ['organization' => $organization->id])}}">
													<img src="{{$organization->image_url ?? '../app-assets/images/logo_placeholder.png'}}" alt="avatar" class="" height="40" >
												</a>
											</div>
										</td>
										<td style="font-size: small;">{{$organization->user->email}}</td>
										<td style="font-size: small;">{{$organization->user->phone}}</td>
										<td style="font-size: small;">{{ $organization->user->name }}</td>
										<td style="font-size: small;">{{ $organization->total_startups }}</td>
										<td style="font-size: small;">{{ $organization->cycles->count()}}</td>
										@if( $_user->ableToAny(['organizations.show', 'organizations.edit', 'organizations.update', 'organizations.destroy'], $permissions) )
										<td>
											@if( $_user->ableToAny(['organizations.edit', 'organizations.update'], $permissions) )<a class="btn btn-label-primary btn-sm" href="{{ route('organizations.edit', ['organization' => $organization->id]) }}"> <i class="ti ti-edit"></i></a>@endif
											@if( $_user->ableTo('organizations.destroy', $permissions) )
											<button id='deleteAction' data-id='{{ $organization->id }}' data-url="{{ route('organizations.destroy', ['organization' => $organization->id] ) }}" type="button" class="btn btn-label-danger btn-sm">
												<i class="ti ti-trash text-danger"></i>
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
				</div>
			</div>
		</section>
	</div>
</div>
@stop

@section('scripts')
	<script src="/app-assets/js/scripts/pages/page-organizations.js"></script>
	<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
	<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
	<script src="/app-assets/js/scripts/datatables/datatable.js"></script>
	<script src="/app-assets/js/scripts/modal/components-modal.js"></script>
	@if( $_user->ableTo('organizations.destroy', $permissions) )
	<script>
		$(document).on("click", "#deleteAction", function (e) {
			var url = $(this).data('url');
			var token = '{{ csrf_token() }}';
			Swal.fire({
				title: "{{__('admin.SureDelete')}}",
				text: "{{__('admin.Recover')}}",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-label-danger",
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
	@endif
@stop
