@extends('layouts.admin')

@section('style')
	<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
	<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
	<div class="">
		<div class="">
			<div class="content-body">
				<section id="multiple-column-form">
					@if (session()->has('message'))
						<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible" role="alert">
							{{ __('admin.' . session()->get('message')) }}
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					@endif
				</section>

				<section class="row">
					<div class="col-xl-12 col-md-12 col-sm-12">
						<div class="card">
							<div class="card-header d-flex align-items-center justify-content-between border-bottom">
								<h5 class="card-title mb-0">{{ __('admin.Labels') }}</h5>
								@if( $_user->ableToAny(['Labels.create', 'Labels.store'], $permissions) )
								<button	class="btn btn-primary"	data-bs-toggle="modal" data-bs-target="#createLabels">
									<i class="ti ti-plus me-2"></i>
									{{ __('admin.Add_New') }}
								</button>
								@endif
							</div>
							<div class="card-body">
								<div class="card-text">
									<div class="table-responsive">
										<table class="table mb-0" id="userstable">
											<thead class="thead-dark">
												<tr>
													<th>#</th>
													<th>{{ __('cycles.Name') }}</th>
													<th>{{ __('cycles.Created at') }}</th>
													@if( $_user->ableToAny(['Labels.show', 'Labels.edit', 'Labels.update', 'Labels.destroy'], $permissions) )<th>{{ __('admin.Actions') }}</th>@endif
												</tr>
											</thead>
											<tbody>
												@php $c=1; @endphp
												@foreach ($labels as $label)
												<tr>
													<td>{{$c++}}</td>
													<td>{{$label->name}}</td>
													<td>{{$label->created_at}}</td>
													@if( $_user->ableToAny(['Labels.show', 'Labels.edit', 'Labels.update', 'Labels.destroy'], $permissions) )
													<td>
														@if( $_user->ableTo('Labels.show', $permissions) )<div class="btn btn-info" data-bs-toggle="modal" data-bs-target="#showLabels{{ $label->id }}" >{{__('admin.Show')}}</div>@endif
														@if( $_user->ableToAny(['Labels.edit', 'Labels.update'], $permissions) )<div class="btn btn-primary"	data-bs-toggle="modal" data-bs-target="#editLabels{{ $label->id }}">{{__('admin.Edit')}}</div>@endif
														@if( $_user->ableTo('Labels.destroy', $permissions) )
														<form action="{{ route('Labels.destroy', $label->id) }}" method="POST" style="display: inline-block;">
															@csrf
															@method('DELETE')
															<button class="btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this post?')">{{__('admin.Delete')}}</button>
														</form>
														@endif
													</td>
													@endif
												</tr>
												@if( $_user->ableToAny(['Labels.edit', 'Labels.update'], $permissions) )
												<!-- Edit Customer Modal -->
												<div class="modal fade" id="editLabels{{ $label->id }}" tabindex="-1" aria-hidden="true">
													<div class="modal-dialog modal-lg modal-simple modal-edit-user">
														<div class="modal-content">
															<div class="modal-body">
																<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
																<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.Edit')}} ({{$label->name}})</h4> </div>
																<form class="add-new-user pt-0" action="{{ route('Labels.update', $label->id) }}" method="POST">
																	@csrf
																	@method('PUT')
																	<div class="ecommerce-customer-add-basic mb-4" >
																		<div class="col-md-12">
																			<div class="mb-6 width49">
																				<div> <label for="labor_name" class="form-label" >{{__('admin.labels_Name')}}</label> </div>
																				<input
																					id="labor_name"
																					class="form-control @error('labels_name') is-invalid @enderror"
																					type="text"
																					name="labels_name"
																					autocomplete="off"
																					value="{{$label->name}}"
																				/>
																			</div>
																		</div>
																	</div>
																	<div class="text-center">
																		@if( $_user->ableToAny(['Labels.edit', 'Labels.update'], $permissions) )
																		<button type="submit" class="btn btn-primary me-3 data-submit">{{__('admin.submit')}}</button>
																		@endif
																		<button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{__('admin.Cancel')}}</button>
																	</div>
																</form>
															</div>
														</div>
													</div>
												</div>
												<!--/ Edit JO Modal -->
												@endif
												@if( $_user->ableTo('Labels.show', $permissions) )
												<!-- Show Customer Modal -->
												<div class="modal fade" id="showLabels{{ $label->id }}" tabindex="-1" aria-hidden="true">
													<div class="modal-dialog modal-lg modal-simple modal-edit-user">
														<div class="modal-content" dir="ltr">
															<div class="modal-body">
																<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
																<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.Show')}} ({{$label->name}})</h4> </div>
																<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																	<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{ $label->name }}</h6> </div>
																	<p class="m-0 d-none d-sm-block">{{__('admin.Tags Name')}}</p>
																</div>
																<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																	<div class="d-flex gap-4 align-items-center">
																		<h6 class="m-0">
																			@foreach ($label->trainings as $training) 
																			<a title="view" href="/dashboard/training/viewCourse/{{$training->id}}">	{{ $training->title ." - " }} </a>
																			@endforeach
																		</h6>
																	</div>
																	<p class="m-0 d-none d-sm-block">{{__('admin.Training')}}</p>
																</div>
																<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																	<div class="d-flex gap-4 align-items-center">
																		@if ($label->user)
																		<h6 class="m-0">{{ $label->user->name }}</h6>
																		@else
																		<h6 class="m-0">{{ __('cycles.NA') }}</h6>	<!-- أو أي نص آخر مناسب -->
																		@endif
																	</div>
																	<p class="m-0 d-none d-sm-block">{{__('admin.Tags Created By')}}</p>
																</div>
																<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																	<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{ $label->created_at}}</h6> </div>
																	<p class="m-0 d-none d-sm-block">{{__('admin.Tags Created At')}}</p>
																</div>
															</div>
														</div>
													</div>
												</div>
												<!--/ Show Customer Modal -->
												@endif
												@endforeach
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>

				@if( $_user->ableToAny(['Labels.create', 'Labels.store'], $permissions) )
				<!-- Create JobOrder Modal -->
				<div class="modal fade" id="createLabels" tabindex="-1" aria-hidden="true" dir="rtl">
					<div class="modal-dialog modal-lg modal-simple modal-edit-user">
						<div class="modal-content">
							<div class="modal-body">
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.labels')}}</h4></div>
								<form method="POST" action="{{route('Labels.store')}}" class="pt-0" >
									@csrf
									<div class="ecommerce-customer-add-basic mb-4" >
										<div class="col-md-12">
											<div class="mb-6 width49">
												<div> <label for="labels_name" class="form-label" >{{__('admin.labels_Name')}}</label> </div>
												<input
													id="labels_name"
													class="form-control"
													type="text"
													name="labels_name"
													autocomplete="off"
												/>
											</div>
										</div>
									</div>
									<div>
										@if( $_user->ableToAny(['Labels.create', 'Labels.store'], $permissions) )
										<button type="submit" class="btn btn-primary me-sm-4 data-submit">{{__('admin.submit')}}</button>
										@endif
										<button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{__('admin.Cancel')}}</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<!--/ Create JobOrder Modal -->
				@endif
			</div>
		</div>
	</div>
	<!-- END: Content-->
@stop
@section('scripts')
	<!-- Vendors JS -->
	<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
	<script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
	<script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
	<script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
	<script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
	<script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
	<script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
	<!-- Page JS -->
	<script src="/vuexy/assets/js/pages-account-settings-account.js"></script>
@stop
@section('pagescripts')
	<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
	<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
	<script src="/js/jquery.datetimepicker.full.min.js"></script>
	<script>
		$(document).ready(function() {
			$('#userstable').DataTable({
				"responsive": true, "searching": true, "lengthChange": false, "paging": true, "bInfo": false,
				"columnDefs": [
					{"orderable": false, "targets":	[3]},
				],
				@if ($l == 'ar')
				"language": {
					"sEmptyTable":		 "ليست هناك بيانات متاحة في الجدول",
					"sLoadingRecords": "جارٍ التحميل...", "sProcessing":	 "جارٍ التحميل...",
					"sLengthMenu":	 "أظهر _MENU_ مدخلات", "sZeroRecords":	"لم يعثر على أية سجلات",
					"sInfo":				 "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
					"sInfoEmpty":		"يعرض 0 إلى 0 من أصل 0 سجل", "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
					"sInfoPostFix":	"", "sSearch":			 "ابحث:", "sUrl":					"",
					"oPaginate": {"sFirst":		"الأول", "sPrevious": "السابق", "sNext":		 "التالي", "sLast":		 "الأخير"},
					"oAria": {"sSortAscending":	": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}
				}
				@endif
			});
		});
	</script>	 
@stop
