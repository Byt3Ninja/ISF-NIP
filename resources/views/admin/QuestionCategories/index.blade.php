@extends('layouts.admin')
@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('title', __('admin.Questions Categories') )
@section('subTitle', __('admin.Questions Categories` List'))
@section('content')
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="content-overlay"></div>
			<div class="content-header row">
				@if( session()->has('success') )
					<div class="col-md-12">
						<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
							<div class="d-flex align-items-center"> <i class="ti ti-thumb-up me-3 ti-md"></i> <span> {{ __('admin.'. session()->get('success')) }} </span> </div>
						</div>
					</div>
				@endif
				@if( session()->has('error') )
					<div class="col-md-12">
						<div class="alert alert-danger alert-dismissible mb-2" role="alert">
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
							<div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span>{{ __('admin.'.session()->get('error')) }} </span> </div>
						</div>
					</div>
				@endif
			</div>

			<div class="content-body">
				<section class="organizations-list-wrapper">
					<div class="row mb-4">
						<div class="col-12 col-sm-3 col-lg-3 col-mg-3 offset-sm-9 offset-lg-9 offset-md-9 d-flex align-items-center">
							<a href="{{ route('question-categories.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-3"></i>
                {{ __('admin.Add New')}} 
              </a>
						</div>
					</div>
					<div class="organizations-list-table">
						<div class="card">
							<div class="card-content">
                <div class="card-header border-bottom mb-4">
                  <h4 class="card-title">{{__('admin.Questions Categories` List')}}</h4>
                </div>
								<div class="card-body">
									<!-- datatable start -->
									<div class="table-responsive">
										<table id="organizations-list-datatable" class="table">
											<thead>
											<tr class="table-light">
												<th>#</th>
												<th>{{__('admin.categoryTitle')}}</th>
												<th class="text-center">{{__('admin.Organization')}}</th>
												<th class="text-center">{{__('admin.categoryQuestions')}}</th>
												<th class="text-center">{{__('admin.Actions')}}</th>
											</tr>
											</thead>
											<tbody>
												@foreach( $categories as $category )
												<tr>
													<td><a href="{{ route('question-categories.edit', ['question_category' => $category->id]) }}">{{ $category->id }}</a></td>
													<td>{{ $category->title }}</td>
													<td><a href="{{ route('organizations.edit', ['organization' => $category->organization->id]) }}"> {{ $category->organization->{'name_'.$l} }}</a></td>
													<td> {{ $category->questions->count() }} </td>
													<td class="text-right">
														<a href="{{ route('question-categories.edit', ['question_category' => $category->id]) }}" class="btn btn-primary btn-sm"> <i class="bx bx-edit"></i> {{ __('admin.Edit') }}</a>
														<button id='deleteAction' data-id='{{ $category->id }}' data-url="{{ route('question-categories.destroy', ['question_category' => $category->id]) }}" class="btn btn-danger btn-sm"> <i class="ti ti-trash text-danger"></i> {{ __('admin.Delete') }}</button>
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

	<script type="text/javascript">
		$(document).on("click", "#deleteAction", function (e) {
			var url = $(this).data('url');
			var token = '{{ csrf_token() }}';
			Swal.fire({
				title: "{{__('admin.SureDelete')}}",
				text: "{{__('admin.Recover')}}",
				icon: "warning",
				buttonsStyling: true,
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
								icon: 'success',
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
