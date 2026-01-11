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
								<h5 class="card-title mb-0">{{ $l == 'en' ? 'All Sub-categories' : 'جميع الفئات الفرعية' }}</h5>
								@if( $_user->ableToAny(['subcategories.create', 'subcategories.store'], $permissions) )
									<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CreateCategory">
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
													<th>{{ __('admin.Name') }}</th>
													<th>{{ __('admin.SubName') }}</th>
													<th>{{ __('admin.Description') }}</th>
													<th>{{ __('admin.Type') }}</th>
													<th>{{ __('cycles.Created at') }}</th>
													@if( $_user->ableToAny(['subcategories.show', 'subcategories.edit', 'subcategories.update', 'subcategories.destroy'], $permissions) )<th>{{ __('admin.Actions') }}</th>@endif
												</tr>
											</thead>
											<tbody>
												@php $c=1; @endphp
												@foreach ($subCategories as $SubCategory)
												<tr>
													<td>{{$c++}}</td>
													<td>{{$SubCategory->category->{'category_name_' . $l} }}</td>
													<td>{{$SubCategory->{'name_' . $l} }}</td>
													<td>{{$SubCategory->description }}</td>
													<td>{{$SubCategory->category->category_type }}</td>
													<td>{{$SubCategory->created_at}}</td>
													@if( $_user->ableToAny(['subcategories.show', 'subcategories.edit', 'subcategories.update', 'subcategories.destroy'], $permissions) )
													<td>
														@if( $_user->ableTo('subcategories.show', $permissions) )<div class="btn btn-info" data-bs-toggle="modal" data-bs-target="#ShowCategory{{ $SubCategory->id }}" >{{__('admin.Show')}}</div>@endif
														@if( $_user->ableToAny(['subcategories.edit', 'subcategories.update'], $permissions) )<div class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#EditCategory{{ $SubCategory->id }}">{{__('admin.Edit')}}</div>@endif
														@if( $_user->ableTo('subcategories.destroy', $permissions) )
														<form action="{{ route('subcategories.destroy', $SubCategory->id) }}" method="POST" style="display: inline-block;">
															@csrf
															@method('DELETE')
															<button class="btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this post?')">{{__('admin.Delete')}}</button>
														</form>
														@endif
													</td>
													@endif
												</tr>
												@if( $_user->ableToAny(['subcategories.edit', 'subcategories.update'], $permissions) )
												<!-- Edit Customer Modal -->
												<div class="modal fade" id="EditCategory{{ $SubCategory->id }}" tabindex="-1" aria-hidden="true">
													<div class="modal-dialog modal-lg modal-simple modal-edit-user">
														<div class="modal-content">
															<div class="modal-body">
																<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
																<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.Edit')}} ({{$SubCategory->{'name_'.$l }	 }})</h4> </div>
																<form class="add-new-user pt-0" action="{{ route('subcategories.update', $SubCategory->id) }}" method="POST">
																	@csrf
																	@method('PUT')
																	<input type="hidden" value="{{ $SubCategory->id }}" name="subcategory_id" >
																	<div class="ecommerce-customer-add-basic mb-4" >
																		<div class="col-md-12">
																			<div class="mb-6 width49">
																				<div> <label for="subcategory_name_ar" class="form-label" >{{__('admin.Category Name')}} ( {{__('admin.Arabic')}} )</label> </div>
																				<input class="form-control @error('subcategory_name_ar') is-invalid @enderror"
																					id="subcategory_name_ar"
																					type="text"
																					name="subcategory_name_ar"
																					autocomplete="off"
																					value="{{$SubCategory->name_ar}}"
																				/>
																			</div>
																			<div class="mb-6 width49">
																				<div> <label for="subcategory_name_en" class="form-label" >{{__('admin.Category Name')}} ( {{__('admin.English')}} )</label> </div>
																				<input class="form-control @error('subcategory_name_en') is-invalid @enderror"
																					id="subcategory_name_en"
																					type="text"
																					name="subcategory_name_en"
																					autocomplete="off"
																					value="{{$SubCategory->name_en}}"
																				/>
																			</div>
																			<div class="mb-6 width49">
																				<label class="form-label fs-6"> {{ __('admin.Category') }} </label>
																				<select class=" form-select" name="category">
																					<option value="" disabled selected> {{ $l == 'en' ? 'Choose category' : 'اختر القسم' }} </option>
																					@foreach ($categories as $category)
																					<option value="{{ $category->id }}" {{ $category->id == $SubCategory->category_id ? 'selected' : '' }} >{{ $category->{'category_name_'. $l} }}</option>
																					@endforeach
																				</select>
																			</div>
																			<div class="mb-6 width49">
																				<div> <label for="subcategory_description" class="form-label" >{{__('admin.Category_Description')}}</label> </div>
																				<textarea id="subcategory_description" class="form-control @error('subcategory_description') is-invalid @enderror"
																					type="text"
																					name="subcategory_description"
																					autocomplete="off">{{$SubCategory->description}}</textarea>
																			</div>
																		</div>
																	</div>
																	<div class="text-center">
																		@if( $_user->ableToAny(['subcategories.edit', 'subcategories.update'], $permissions) )
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
												@if( $_user->ableTo('subcategories.show', $permissions) )
												<!-- Show Customer Modal -->
												<div class="modal fade" id="ShowCategory{{ $SubCategory->id }}" tabindex="-1" aria-hidden="true">
													<div class="modal-dialog modal-lg modal-simple modal-edit-user">
														<div class="modal-content" dir="ltr">
															<div class="modal-body">
																<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
																<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.Show')}} ( {{ $SubCategory->{'name_' . $l} }} )</h4> </div>
																<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																	<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{ $SubCategory->{'name_' . $l} }}</h6> </div>
																	<p class="m-0 d-none d-sm-block">{{__('admin.Category Name')}}</p>
																</div>
																<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																	<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{$SubCategory->description}}</h6> </div>
																	<p class="m-0 d-none d-sm-block">{{__('admin.Category_Description')}}</p>
																</div>
																<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																	<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{$SubCategory->category->{'category_name_'. $l} }}</h6> </div>
																	<p class="m-0 d-none d-sm-block">{{ __('admin.Category') }}</p>
																</div>
																<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																	<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{$SubCategory->Type}}</h6> </div>
																	<p class="m-0 d-none d-sm-block">{{__('admin.Category_Type')}}</p>
																</div>
																<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																	<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{ $SubCategory->created_at}}</h6> </div>
																	<p class="m-0 d-none d-sm-block">{{__('admin.Created At')}}</p>
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

				@if( $_user->ableToAny(['Category.create', 'Category.store'], $permissions) )
				<!-- Create JobOrder Modal -->
				<div class="modal fade" id="CreateCategory" tabindex="-1" aria-hidden="true" dir="rtl">
					<div class="modal-dialog modal-lg modal-simple modal-edit-user">
						<div class="modal-content">
							<div class="modal-body">
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.SubName')}}</h4> </div>
								<form method="POST" action="{{route('subcategories.store')}}" class="pt-0" >
									@csrf
									<div class="ecommerce-customer-add-basic mb-4" >
										<div class="col-md-12">
											<div class="mb-6 width49">
												<div> <label for="subcategory_name_ar" class="form-label" >{{ __('admin.subCategory_Name') }} ( {{ __('admin.Arabic') }} )</label> </div>
												<input class="form-control @error('subcategory_name_ar') is-invalid @enderror"
													id="subcategory_name_ar"
													type="text"
													name="subcategory_name_ar"
													autocomplete="off"
												/>
											</div>
											<div class="mb-6 width49">
												<div> <label for="subcategory_name_en" class="form-label" >{{ __('admin.subCategory_Name') }} ( {{ __('admin.English') }} )</label> </div>
												<input class="form-control @error('subcategory_name_en') is-invalid @enderror"
													id="subcategory_name_en"
													type="text"
													name="subcategory_name_en"
													autocomplete="off"
												/>
											</div>
											<div class="mb-6 width49">
												<label class="form-label fs-6"> {{ __('admin.Category') }} </label>
												<select class=" form-select" name="category">
													<option value="" disabled selected> {{ $l == 'en' ? 'Choose category' : 'اختر القسم' }} </option>
													@foreach ($categories as $category)
													<option value="{{ $category->id }}">{{ $category->{'category_name_'. $l} }}</option>
													@endforeach
												</select>
											</div>
											<div class="mb-6 width49">
												<div> <label for="subcategory_description" class="form-label" >{{__('admin.subCategory_Description')}}</label> </div>
												<textarea id="subcategory_description" class="form-control @error('subcategory_description') is-invalid @enderror"
													type="text"
													name="subcategory_description"
													autocomplete="off"></textarea>
											</div>
										</div>
									</div>
									<div>
										@if( $_user->ableToAny(['Category.create', 'Category.store'], $permissions) )
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
	<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
	<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
	<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
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
					"sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
					"sLoadingRecords": "جارٍ التحميل...",
					"sProcessing": "جارٍ التحميل...",
					"sLengthMenu": "أظهر _MENU_ مدخلات",
					"sZeroRecords": "لم يعثر على أية سجلات",
					"sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
					"sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
					"sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
					"sInfoPostFix": "",
					"sSearch": "ابحث:",
					"sUrl": "",
					"oPaginate": {
						"sFirst": "الأول",
						"sPrevious": "السابق",
						"sNext": "التالي",
						"sLast": "الأخير"
					},
					"oAria": {
						"sSortAscending": ": تفعيل لترتيب العمود تصاعدياً",
						"sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
					}
				}
				@endif
			});
		});
	</script>
@stop
