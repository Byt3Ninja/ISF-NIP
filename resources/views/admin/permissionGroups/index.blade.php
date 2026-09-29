@extends('layouts.admin')

@section('title', __('Users Permissions') )
@section('subTitle', __('Permissions list'))

@section('content')
<!-- BEGIN: Content-->
<div class="">
  <div class="">
	
		<div class="content-body">
		  <section id="basic-datatable">
			<div class="row">
			  <div class="col-12">
				<div class="card">
				  <div class="card-header border-bottom">
						<div class="d-flex flex-wrap justify-content-between align-items-center">
							<h5 class="card-title mb-3 mb-md-0">{{ $l == 'en' ? 'Permission Groups List' : 'قائمة مجموعات الصلاحيات' }}</h5>
							<a href="{{ route('permission-groups.create') }}" class="btn btn-primary glow">
								<i class="ti ti-plus me-2"></i>
								{{ __('admin.Add New')}}
							</a>
						</div>
					</div>
							<div class="card-content">
								<div class="card-body card-dashboard">
									@if(session()->has('msg'))
										<div class="row">
											<div class="col-md-12">
												<div class="alert alert-success alert-dismissible mb-2" role="alert">
													<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
													<div class="d-flex align-items-center">
														<i class="ti ti-check"></i>
														<span>
															{{ __('admin.'.session()->get('msg')) }}
															{{ Session::forget('msg') }}
														</span>
													</div>
												</div>
											</div>
										</div>
									@endif
									<div class="table-responsive">
										<table class="table permissions-groups-table">
											<thead class="table-light">
												<tr>
													<th>{{ __('admin.ID')}}</th>
													<th>{{ __('admin.Title')}}</th>
													<th>{{ $l == 'en' ? 'No. of Permissions' : 'عدد الأذونات' }}</th>
													<th>{{ __('admin.Actions')}}</th>
												</tr>
											</thead>
											<tbody>
											@foreach($groups as $key => $group)
												<tr>
													<td> {{ $group->id ?? '' }} </td>
													<td> {{ $group->name  ?? '' }} </td>
													<td> {{ $group->permissions->count() }} </td>
													<td>
														<a href="{{ route('permission-groups.edit', ['permission_group' => $group->id]) }}" class="btn btn-icon rounded-circle btn-label-info">
															<i class="ti ti-edit"> </i>
														</a>
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
				</div>
			</div>
		</section>
	</div>
</div>
@stop

@section('scripts')
<script>
	$(document).ready(function() {
    const isRtl = {{ $l == 'ar' ? 'true' : 'false' }};

    // Initialize DataTable
    var table = $('.permissions-groups-table').DataTable({
      responsive: true,
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
      language: isRtl ? { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' } : {},
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7 filters"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    });

    // Handle delete action
    $(document).on("click", "#deleteAction", function (e) {
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
              // Remove the row from DataTable
              table.row('#row_' + id).remove().draw();

              releaseSuccessAlert({
                title: "{{ __('admin.Deleted') }}!",
                message: "{{ __('admin.DeletedSuccessfully') }}.",
              });
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
