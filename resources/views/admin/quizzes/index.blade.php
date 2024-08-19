@extends('layouts.admin')
@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('title', __('admin.quizzes') )
@section('subTitle', __('admin.quizzes List'))
@section('content')
	<div class="app-content content">
		<div class="content-overlay"></div>
		<div class="content-wrapper">
			<div class="content-header row">
				<div class="content-header-left col-12 mb-2 mt-1">
					<div class="row breadcrumbs-top">
						<div class="col-12">
							<h5 class="content-header-title float-left pr-1 mb-0">{{__('admin.quizzes')}}</h5>
							<div class="breadcrumb-wrapper col-12">
								<ol class="breadcrumb p-0 mb-0">
									<li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a></li>
									<li class="breadcrumb-item active">{{__('admin.quizzes List')}}</li>
								</ol>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="content-header row">
				@if( session()->has('success') )
					<div class="col-md-12">
						<div class="alert alert-success alert-dismissible mb-2" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
							<div class="d-flex align-items-center"> <i class="bx bx-like"></i> <span> {{ __('admin.'. session()->get('success')) }} </span> </div>
						</div>
					</div>
				@endif
				@if( session()->has('error') )
					<div class="col-md-12">
						<div class="alert alert-danger alert-dismissible mb-2" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
							<div class="d-flex align-items-center"> <i class="bx bx-error"></i> <span>{{ __('admin.'.session()->get('error')) }} </span> </div>
						</div>
					</div>
				@endif
			</div>

			<div class="content-body">
				<section class="organizations-list-wrapper">
					<div class="row pb-1">
						<div class="col-12 col-sm-3 col-lg-3 col-mg-3 offset-sm-9 offset-lg-9 offset-md-9 d-flex align-items-center">
							<a href="{{ route('quizzes.create') }}" class="btn btn-block btn-success"> {{ __('admin.Add New')}} </a>
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
												<th>{{__('admin.quizTitle')}}</th>
												<th class="text-center">{{__('admin.Organization')}}</th>
												<th class="text-center">{{__('admin.quizTraining')}}</th>
												<th class="text-center">{{__('admin.quizStatus')}}</th>
												<th class="text-center">{{__('admin.quizDuration')}}</th>
												<th class="text-center">{{__('admin.quizParticipants')}}</th>
												<th class="text-center">{{__('admin.quizPassPercentage')}}</th>
												<th class="text-center">{{__('admin.Actions')}}</th>
											</tr>
											</thead>
											<tbody>
												@foreach( $quizzes as $quiz )
													<tr>
														<td><a href="{{ URL::to('dashboard/quizzes/' . $quiz->id . '/edit') }}">{{ $quiz->id }}</a></td>
														<td>{!! $quiz->title !!}</td>
														<td class="text-center"><a href="{{ URL::to('dashboard/organizations/' . $quiz->organization->id . '/edit') }}"> {{ $quiz->organization->{'name_'.$l} }}</a></td>
														<td class="text-center"><a href="{{ URL::to('dashboard/training/all/' . $quiz->training->id) }}"> {{ $quiz->training->title }}</a></td>
														<td class="text-center">
															@if( $quiz->status == 'draft' )
																<span class="badge badge-secondary">{{__('admin.Draft')}}</span>
															@elseif( $quiz->status == 'published' )
																<span class="badge badge-success">{{__('admin.Published')}}</span>
															@endif
														</td>
														<td class="text-center">
															<span title="{{ $quiz->start }}" class="badge badge-primary"> {{ __('admin.From') }} {{ $quiz->start->format('Y-m-d') }} </span> <br />
															<span title="{{ $quiz->end }}" class="badge badge-dark"> {{ __('admin.To') }} {{ $quiz->end->format('Y-m-d') }} </span>
														</td>
														<td class="text-center"> {{ $quiz->results->count() }} </td>
														<td class="text-center"> - </td>
														<td class="text-center">
															<div class="btn-group w-100">
																<button class="btn btn-dark btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> {{ __('admin.Actions') }} </button>
																<div class="dropdown-menu">
																	<a href="{{ route('quizzes.questions', ['quiz' => $quiz->id]) }}" class="dropdown-item"> <i class="bx bx-list-ul"></i> {{ __('admin.manageQuestions') }} </a>
																	<a href="{{ route('quizzes.edit', ['quiz' => $quiz->id]) }}" class="dropdown-item"> <i class="bx bx-edit"></i> {{ __('admin.Edit') }}</a>
																	<a id='deleteAction' data-id='{{ $quiz->id }}' data-url="{{ route('quizzes.destroy', ['quiz' => $quiz->id]) }}" href="{{ route('quizzes.destroy', ['quiz' => $quiz->id]) }}" class="dropdown-item"> <i class="bx bx-trash"></i> {{ __('admin.Delete') }} </a>
																</div>
															</div>
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
