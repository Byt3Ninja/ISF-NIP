@extends('layouts.admin')
@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('title', __('admin.quizzes') )
@section('subTitle', __('admin.quizzes List'))
@section('content')
	<!-- BEGIN: Content-->
	<div class="content-wrapper">
		<div class="container-xxl flex-grow-1 container-p-y">
			<div class="content-overlay"></div>
			<div class="content-header">
        <h4 class="content-title">{{__('admin.quizzes')}}</h4>
			</div>
			<div class="content-header row">
				@if( session()->has('success') )
					<div class="col-md-12">
						<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
              {{ __('admin.'. session()->get('success')) }} 
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					</div>
				@endif
				@if( session()->has('error') )
					<div class="col-md-12">
						<div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
              {{ __('admin.'.session()->get('error')) }}
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					</div>
				@endif
			</div>

			<div class="content-body">
				<section class="organizations-list-wrapper">
					<div class="organizations-list-table">
						<div class="card">
							<div class="card-content">
								<div class="card-body">
									@if($_user->ableToAny(['quizzes.create', 'quizzes.store'], $permissions))
									<div class="row pb-1">
										<div class="col-12 d-flex justify-content-end">
											<a href="{{ route('quizzes.create') }}" class="btn btn-block btn-primary"> 
												<i class="ti ti-plus me-1"></i> {{ __('admin.Add New')}}
											</a>
										</div>
									</div>
									@endif
									<!-- datatable start -->
									<div class="table-responsive">
										<br>
										<form method="GET" action="/dashboard/quizzes">
										  <div class="d-flex align-items-center mx-2">
											<div style="display: flex; align-items: center;">
											<button
											  class="btn bg-primary p-3 d-none d-md-flex justify-content-center align-items-center me-3" 
											  style="border-radius: 17px; width: 40px; height: 40px;"
											   type="submit"
											>
											  <i class="ti ti-filter text-white ti-24px"></i>
											</button>
										  </div>
										  <!-- Search Bar -->
										 
							
										  <div class="position-relative me-3 d-none d-md-flex">
											<select name="org_id" class="form-control">
											  <option value="0">{{ __('admin.All_Organizations') }}</option>
											  @php
											  $organizations = App\Organization::all();
											  
											  @endphp
											  @foreach ($organizations as $organization) 
											  <option value="{{$organization->id}}" {{ $organization->id == request('org_id') ? 'selected' : '' }}> {{ $organization->{'name_' . $l} }}</option>
											@endforeach
											</select>
										  </div>
					  
										  {{-- <div class="position-relative me-3 d-none d-md-flex">
											<select name="program_id" class="form-control">
											  <option value="0">All</option>
											  @php
											  $Services = App\Service::all();
											  
											  @endphp
											  @foreach ($Services as $Service) 
											  <option value="{{$Service->id}}" {{ $Service->id == request('Service_id') ? 'selected' : '' }}> {{ $Service->{'name_' . $l} }}</option>
											@endforeach
											</select>
										  </div> --}}
							
										  <div class="position-relative me-3 d-none d-md-flex">
											<select name="status" class="form-control">
											  <option value="0">{{ __('admin.All_statuses') }}</option>
											  <option value="draft" {{ 'draft' == request('status') ? 'selected' : '' }}>{{ __('admin.Draft') }}</option>
											  <option value="published" {{ 'published' == request('status') ? 'selected' : '' }}>{{ __('admin.Published') }}</option>
											</select>
										  </div>

										  <div class="position-relative me-3 d-none d-md-flex">
											<select name="Program_id" class="form-control">
											  <option value="0">{{ __('admin.All_Programs') }}</option>
											  @php
											  if(!empty(request('org_id'))){
											  	$cycles = App\Cycle::where('organization_id',request('org_id'))->get();
											  }else{
												$cycles = App\Cycle::all();
											  }
											  @endphp
											  @foreach ($cycles as $cycle)
											  <option value="{{ $cycle->id }}" {{  $cycle->id == request('Program_id') ? 'selected' : '' }}>{{ $cycle->title }}</option>
											  @endforeach
											</select>
										  </div>
										  
										</div>
										</form>
										<hr>
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
												@if($_user->ableToAny(['quizzes.questions_manage', 'quizzes.questions_update', 'quizzes.edit', 'quizzes.update', 'quizzes.destroy'], $permissions))<th class="text-center">{{__('admin.Actions')}}</th>@endif
											</tr>
											</thead>
											<tbody>
												@foreach( $quizzes as $key => $quiz )
													@php
														$participates = collect($quiz->results)->groupBy('user_id');
														$passed_participates = $participates->filter(function( $item ) use ( $quiz ) {
															$passed = $item->where('score', '>=', $quiz->passing_criteria);
															$failed = $item->where('score', '<', $quiz->passing_criteria);
															if( $passed->count() >= 1 ) { return true; }
															else
															{
																if( $failed->count() >= $quiz->attempts ) { return false; }
																if( now()->format('Y-m-d H:i:s') >= \Carbon\Carbon::parse($quiz->end)->format('Y-m-d H:i:s') ) { return false; }
																return true;
															}
														});
													@endphp
													<tr>
														<td><a href="{{ URL::to('dashboard/quizzes/' . $quiz->id . '/edit') }}">{{ $key + 1 }}</a></td>
														<td>{!! $quiz->title !!}</td>
														<td class="text-center"><a href="{{ URL::to('dashboard/organizations/' . $quiz->organization->id . '/edit') }}"> {{ $quiz->organization->{'org_name_'.$l} }}</a></td>
														<td class="text-center"><a href="{{ URL::to('dashboard/training/all/' . $quiz->training->id) }}"> {{ $quiz->training->title }}</a></td>
														<td class="text-center">
															@if( $quiz->status == 'draft' )
																<span class="badge bg-label-secondary">{{__('admin.Draft')}}</span>
															@elseif( $quiz->status == 'published' )
																<span class="badge bg-label-success">{{__('admin.Published')}}</span>
															@endif
														</td>
														<td class="text-center">
															<span title="{{ $quiz->start }}" class="badge bg-label-primary"> {{ __('admin.From') }} {{ FormatDate($quiz->start, 'Y-m-d') }} </span> <br />
															<span title="{{ $quiz->end }}" class="badge bg-label-secondary mt-1"> {{ __('admin.To') }} {{ FormatDate($quiz->end, 'Y-m-d') }}</span>
														</td>
														<td class="text-center"> {{ $participates->count() }} </td>
														<td class="text-center">
															@if( $participates->count() >= 1 )
																{{ ( round( floatval( $passed_participates->count() / $participates->count() ), 2 ) * 100 ) . '%' }}
															@else
																-
															@endif
														</td>
														@if($_user->ableToAny(['quizzes.questions_manage', 'quizzes.questions_update', 'quizzes.edit', 'quizzes.update', 'quizzes.destroy'], $permissions))
														<td class="text-center">
															<div class="btn-group w-100">
																<button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> {{ __('admin.Actions') }} </button>
																<div class="dropdown-menu">
																	@if($_user->ableToAny(['quizzes.questions_manage', 'quizzes.questions_update'], $permissions))
																	<a href="{{ route('quizzes.questions_manage', ['quiz' => $quiz->id]) }}" class="dropdown-item"> <i class="bx bx-list-ul"></i> {{ __('admin.manageQuestions') }} </a>
																	@endif
																	@if($_user->ableToAny(['quizzes.edit', 'quizzes.update'], $permissions))
																	<a href="{{ route('quizzes.edit', ['quiz' => $quiz->id]) }}" class="dropdown-item"> <i class="bx bx-edit"></i> {{ __('admin.Edit') }}</a>
																	@endif
																	@if($_user->ableTo('quizzes.destroy', $permissions))
																	<a id='deleteAction' data-id='{{ $quiz->id }}' data-url="{{ route('quizzes.destroy', ['quiz' => $quiz->id]) }}" href="{{ route('quizzes.destroy', ['quiz' => $quiz->id]) }}" class="dropdown-item"> <i class="ti ti-trash text-danger"></i> {{ __('admin.Delete') }} </a>
																	@endif
																</div>
															</div>
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
	</div>
	<!-- END: Content-->
@stop
@section('scripts')
	<script src="/app-assets/js/scripts/pages/page-organizations.js"></script>
	<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
	<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
	<script src="/app-assets/js/scripts/datatables/datatable.js"></script>
	<script src="/app-assets/js/scripts/modal/components-modal.js"></script>

	<script type="text/javascript">
		$(document).on("click", "#deleteAction", function (e) {
			e.preventDefault();
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
