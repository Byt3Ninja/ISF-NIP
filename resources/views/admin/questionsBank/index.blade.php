@extends('layouts.admin')
@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('title', __('admin.Questions Bank') )
@section('subTitle', __('admin.Questions Bank List'))
@section('content')
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="content-overlay"></div>
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
						<div class="alert alert-danger alert-dismissible mb-2" role="alert">
              {{ __('admin.'.session()->get('error')) }}
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					</div>
				@endif
			</div>

			<div class="content-body">
				<section class="organizations-list-wrapper">
					<div class="row mb-4">
						<div class="col-12 col-sm-3 col-lg-3 col-mg-3 offset-sm-9 offset-lg-9 offset-md-9 d-flex align-items-center">
							<a 
                href="{{ route('questions-bank.create') }}"
                class="btn btn-primary">
                <i class="ti ti-plus me-3"></i>
                {{ __('admin.Add New')}} 
              </a>
						</div>
					</div>
					<div class="organizations-list-table">
						<div class="card">
							<div class="card-content">
                <div class="card-header mb-2 border-bottom">
                  <h4 class="card-title">{{__('admin.Questions Bank List')}}</h4>
                </div>
								<div class="card-body">
									<!-- datatable start -->
									<div class="table-responsive">
										<table id="organizations-list-datatable" class="table">
											<thead>
											<tr class="table-light">
												<th>#</th>
												<th>{{__('admin.question')}}</th>
												<th class="text-center">{{__('admin.Organization')}}</th>
												<th class="text-center">{{__('admin.question_Type')}}</th>
												<th class="text-center">{{__('admin.question_difficulty')}}</th>
												<th class="text-center">{{__('admin.Actions')}}</th>
											</tr>
											</thead>
											<tbody>
												@foreach( $questions as $question )
													<tr>
														<td><a href="{{ URL::to('dashboard/questions-bank/' . $question->id . '/edit') }}">{{ $question->id }}</a></td>
														<td>{{ $question->question }}</td>
														<td><a href="{{ URL::to('dashboard/organizations/' . optional($question->organization)->id . '/edit') }}"> {{ optional($question->organization)->{'name_'.$l} }}</a></td>
														<td class="text-center">
															@if( $question->group == 'quiz' )
															<span class="badge text-bg-secondary rounded-pill">
																{{ $question->type == 'mcq' ? __('admin.type_mcq') : ( $question->type == 'tfq' ? __('admin.type_tfq') : '-') }}
															</span>
															@else
															<span class="badge text-bg-success rounded-pill">
																{{ $question->survey_type == 'stars' ? __('admin.survey_type_stars') : ( $question->survey_type == 'radio' ? __('admin.survey_type_radio') : '-') }}
															</span>
															@endif
														</td>
														<td class="text-center">
															@if( $question->group == 'quiz' )
																@if( $question->difficulty == 'easy' )
																<span class="badge text-bg-success rounded-pill"> {{ __('admin.difficulty_easy') }}</span>
																@elseif( $question->difficulty == 'medium' )
																<span class="badge text-bg-warning rounded-pill">{{ __('admin.difficulty_medium') }}</span>
																@elseif( $question->difficulty == 'hard' )
																<span class="badge text-bg-danger rounded-pill">{{ __('admin.difficulty_hard') }}</span>
																@endif
															@else
																-
															@endif
														</td>
														<td class="text-right">
															<a href="{{ route('questions-bank.edit', ['questions_bank' => $question->id]) }}" class="btn btn-primary btn-sm"> <i class="bx bx-edit"></i> {{ __('admin.Edit') }}</a>
															<button id='deleteAction' data-id='{{ $question->id }}' data-url="{{ route('questions-bank.destroy', ['questions_bank' => $question->id]) }}" class="btn btn-danger btn-sm"> <i class="ti ti-trash text-danger"></i> {{ __('admin.Delete') }}</button>
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
