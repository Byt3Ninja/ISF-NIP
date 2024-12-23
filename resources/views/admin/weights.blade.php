@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-header mb-4">
        <h4 class="card-title">{{ __('admin.all Weights') }}</h4>
      </div>
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div 
              class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible" 
              role="alert"
            >
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        </section>

        <section class="row">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
              <div class="card-header">
                <button 
                  class="btn btn-primary" 
                  data-bs-toggle="modal" data-bs-target="#CreateWeights">
                  {{ __('admin.Add new Weights') }}
                  <i class="ti ti-plus mx-2"></i>
                </button>
              </div>
              <div class="card-body">
                <div class="card-text">
                  <div class="table-responsive">
                    <table class="table mb-0" id="userstable">
                      <thead class="thead-dark">
                        <tr>
                          <th>#</th>
                          <th>{{ __('admin.StageName') }}</th>
                          <th>{{ __('admin.Category') }}</th>
                          <th>{{ __('admin.subCategory_Name') }}</th>
                          <th>{{ __('admin.Weight') }}</th>
                          <th>{{ __('admin.Created at') }}</th>
                          <th>{{ __('cycles.Edit/Delete') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php
                        $c=1;
                        @endphp
                        @foreach ($weights as $weight)
                        <tr>
                          <td>{{$c++}}</td>
                          <td>{{$weight->stage->{'name_'.$l} }}</td>
                          <td>{{$weight->category->{'category_name_' . $l} }}</td>
                          <td>{{$weight->subcategory->{'name_' . $l} }}</td>
                          <td>{{$weight->weight}}</td>
                          <td>{{$weight->created_at}}</td>
                          <td>
                            <div class="btn btn-info" data-bs-toggle="modal" data-bs-target="#ShowWeight{{ $weight->id }}" >{{__('admin.Show')}}</div>
                            <div class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#EditWeight{{ $weight->id }}">{{__('admin.Edit')}}</div>
                            <form action="{{ route('weights.destroy', $weight->id) }}" method="POST" style="display: inline-block;">
                              @csrf
                              @method('DELETE')
                              <button class="btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this post?')">{{__('admin.Delete')}}</button>
                          </form>

                          </td>
                        </tr>
                        <!-- Edit Customer Modal -->
                        <div class="modal fade" id="EditWeight{{ $weight->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                              <div class="modal-content">
                                  <div class="modal-body">
                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      <div class="text-center mb-6">
                                          <h4 class="mb-2">{{__('admin.Edit')}} ({{$weight->name}})</h4>
                                      </div>

                                      <form class="add-new-user pt-0" action="{{ route('weights.update', $weight->id) }}" method="POST">
                                          @csrf
                                          @method('PUT')

                                          <div class="ecommerce-customer-add-basic mb-4" >

                                              <div class="col-md-12">

                                                <div class="mb-6 width49">
                                                    <label class="form-label fs-6">
                                                      {{ __('admin.StageName') }}
                                                      
                                                    </label>
                                                    <select class=" form-select" name="stage_id">
                                                      <option value="" disabled selected>
                                                        {{ $l == 'en' ? 'Choose Stage' : 'اختر المرحلة' }}
                                                      </option>
                                                      @foreach ($stages as $stage)
                                                        <option value="{{ $stage->id }}" {{ $stage->id == $weight->stage_id ? 'selected' : '' }} >{{ $stage->{'name_'. $l} }}</option>
                                                      @endforeach
                                                    </select>
                                                  </div>
                        
                        
                        
                                                  <div class="mb-6 width49">
                                                    <label class="form-label fs-6">
                                                      {{ __('admin.Category') }}
                                                      
                                                    </label>
                                                    <select class="category_id form-select" name="category_id">
                                                      <option value="" disabled selected>
                                                        {{ $l == 'en' ? 'Choose category' : 'اختر القسم' }}
                                                      </option>
                                                      @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"  {{ $category->id == $weight->category_id ? 'selected' : '' }} >{{ $category->{'category_name_'. $l} }}</option>
                                                      @endforeach
                                                    </select>
                                                  </div>
                        
                        
                                                  <div class="mb-6 width49">
                                                    <label class="form-label fs-6">
                                                      {{ __('admin.subCategory_Name') }}
                                                      
                                                    </label>
                                                    <select class="subcategory_id form-select" name="subcategory_id">
                                                      <option value="" disabled selected>
                                                        {{ $l == 'en' ? 'Choose subcategory' : 'اختر القسم الفرعي' }}
                                                      </option>
                                                      @foreach ($subcategories as $subcategory)
                                                        <option value="{{ $subcategory->id }}" {{ $subcategory->id == $weight->subcategory_id ? 'selected' : '' }} >{{ $subcategory->{'name_'. $l} }}</option>
                                                      @endforeach
                                                    </select>
                                                  </div>
                        
                                                  {{-- <div class="mb-6 width49">
                                                    <label class="form-label fs-6">
                                                      {{ __('admin.Question') }}
                                                      
                                                    </label>
                                                    <select class=" form-select" name="question_id">
                                                      <option value="" disabled selected>
                                                        {{ $l == 'en' ? 'Choose Question' : 'اختر السؤال ' }}
                                                      </option>
                                                      @foreach ($questions as $question)
                                                        <option value="{{ $question->id }}"  {{ $question->id == $weight->question_id ? 'selected' : '' }}  >{{ $question->question }}</option>
                                                      @endforeach
                                                    </select>
                                                  </div> --}}
                        
                        
                                                  <div class="mb-6 width49">
                                                      <div>
                                                          <label for="weights" class="form-label" >{{__('admin.Weight')}}</label>
                                                      </div>
                                                      <input
                                                          id="weights"
                                                          class="form-control"
                                                          type="number"
                                                          name="weights"
                                                          autocomplete="off"
                                                          value="{{ $weight->weight }}" />
                                                  </div>



                                              </div>
                                          </div>

                                          <div class="text-center">
                                              <button type="submit" class="btn btn-primary me-3 data-submit">{{__('admin.submit')}}</button>
                                              <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{__('admin.Cancel')}}</button>
                                          </div>
                                      </form>



                                  </div>
                              </div>
                          </div>
                      </div>
                      <!--/ Edit JO Modal -->
                      <!-- Show Customer Modal -->
                      <div class="modal fade" id="ShowWeight{{ $weight->id }}" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                      <div class="modal-content" dir="ltr">
                          <div class="modal-body">
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              <div class="text-center mb-6">
                                  <h4 class="mb-2">{{__('admin.Show')}} ({{$weight->weight}})</h4>
                              </div>


                              <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                                  <div class="d-flex gap-4 align-items-center">
                                      <h6 class="m-0">{{ $weight->stage->{'name_'.$l} }}</h6>
                                  </div>
                                  <p class="m-0 d-none d-sm-block">{{__('admin.StageName')}}</p>
                              </div>

                              <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                                <div class="d-flex gap-4 align-items-center">
                                    <h6 class="m-0">{{ $weight->category->{'category_name_' . $l} }}</h6>
                                </div>
                                <p class="m-0 d-none d-sm-block">{{__('admin.Category')}}</p>
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                              <div class="d-flex gap-4 align-items-center">
                                  <h6 class="m-0">{{ $weight->subcategory->{'name_' . $l} }}</h6>
                              </div>
                              <p class="m-0 d-none d-sm-block">{{__('admin.subCategory_Name')}}</p>
                          </div>

                          <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <h6 class="m-0">{{ $weight->weight }}</h6>
                            </div>
                            <p class="m-0 d-none d-sm-block">{{__('admin.Weight')}}</p>
                        </div>

                              <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                                  <div class="d-flex gap-4 align-items-center">
                                      <h6 class="m-0">{{ $weight->created_at}}</h6>
                                  </div>
                                  <p class="m-0 d-none d-sm-block">{{__('admin.Created At')}}</p>
                              </div>

                          </div>
                      </div>
                      </div>
                      </div>
                      <!--/ Show Customer Modal -->
                        @endforeach

                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </section>

 <!-- Create JobOrder Modal -->
 <div class="modal fade" id="CreateWeights" tabindex="-1" aria-hidden="true" dir="rtl">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
      <div class="modal-content">
          <div class="modal-body">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <div class="text-center mb-6">
                  <h4 class="mb-2">{{__('admin.Weight')}}</h4>
              </div>

              <form method="POST" action="{{route('weights.store')}}" class="pt-0" >
                  @csrf

                  <div class="ecommerce-customer-add-basic mb-4" >

                      <div class="col-md-12">


                        <div class="mb-6 width49">
                            <label class="form-label fs-6">
                              {{ __('admin.StageName') }}
                              
                            </label>
                            <select class=" form-select" name="stage_id">
                              <option value="" disabled selected>
                                {{ $l == 'en' ? 'Choose Stage' : 'اختر المرحلة' }}
                              </option>
                              @foreach ($stages as $stage)
                                <option value="{{ $stage->id }}" >{{ $stage->{'name_'. $l} }}</option>
                              @endforeach
                            </select>
                          </div>



                          <div class="mb-6 width49">
                            <label class="form-label fs-6">
                              {{ __('admin.Category') }}
                              
                            </label>
                            <select class="category_id form-select" name="category_id">
                              <option value="" disabled selected>
                                {{ $l == 'en' ? 'Choose category' : 'اختر القسم' }}
                              </option>
                              @foreach ($categories as $category)
                                <option value="{{ $category->id }}" >{{ $category->{'category_name_'. $l} }}</option>
                              @endforeach
                            </select>
                          </div>


                          <div class="mb-6 width49">
                            <label class="form-label fs-6">
                              {{ __('admin.subCategory_Name') }}
                              
                            </label>
                            <select class="subcategory_id form-select" name="subcategory_id">
                              <option value="" disabled selected>
                                {{ $l == 'en' ? 'Choose subcategory' : 'اختر القسم الفرعي' }}
                              </option>
                              @foreach ($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}"  >{{ $subcategory->{'name_'. $l} }}</option>
                              @endforeach
                            </select>
                          </div>

                          {{-- <div class="mb-6 width49">
                            <label class="form-label fs-6">
                              {{ __('admin.Question') }}
                              
                            </label>
                            <select class=" form-select" name="question_id">
                              <option value="" disabled selected>
                                {{ $l == 'en' ? 'Choose Question' : 'اختر السؤال ' }}
                              </option>
                              @foreach ($questions as $question)
                                <option value="{{ $question->id }}"  >{{ $question->question }}</option>
                              @endforeach
                            </select>
                          </div> --}}


                          <div class="mb-6 width49">
                              <div>
                                  <label for="weights" class="form-label" >{{__('admin.Weight')}}</label>
                              </div>
                              <input
                                  id="weights"
                                  class="form-control"
                                  type="number"
                                  name="weights"
                                  autocomplete="off" />
                          </div>

                      </div>
                  </div>



                  <div>
                      <button type="submit" class="btn btn-primary me-sm-4 data-submit">{{__('admin.submit')}}</button>
                      <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{__('admin.Cancel')}}</button>
                  </div>
              </form>

          </div>
      </div>
  </div>
</div>
<!--/ Create JobOrder Modal -->


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
                    {"orderable": false, "targets":  [3]},
                ],
                @if ($l == 'ar')
                "language": {
                    "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول",
                    "sLoadingRecords": "جارٍ التحميل...", "sProcessing":   "جارٍ التحميل...",
                    "sLengthMenu":   "أظهر _MENU_ مدخلات", "sZeroRecords":  "لم يعثر على أية سجلات",
                    "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                    "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل", "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                    "sInfoPostFix":  "", "sSearch":       "ابحث:", "sUrl":          "",
                    "oPaginate": {"sFirst":    "الأول", "sPrevious": "السابق", "sNext":     "التالي", "sLast":     "الأخير"},
                    "oAria": {"sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}} @endif
                });


                $('.category_id').change(function() {
	var category_id = $(this).val();
	var $state = $('.subcategory_id');
	$.ajax({
		url: "{{ route('get_subcategory_by_category') }}",
		method: 'GET', // إضافة طريقة الطلب
		data: {
			category_id: category_id
		},
		success: function(data) {
			$state.html('<option value="">{{ __('admin.SubName') }}</option>'); // إعادة تعيين الخيارات

			$.each(data, function(id, value) {
				 // تعيين الخيار إذا كان مطابقًا
				$state.append('<option value="' + id + '" >' + value + '</option>');
			});

		},
		error: function(xhr, status, error) {
			console.error('Error:', error); // معالجة الأخطاء
		}
	});
});


            });
        </script>
            
@stop
