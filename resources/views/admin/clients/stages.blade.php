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
							<div class="card-header d-flex align-items-center justify-content-between border-bottom">
                <h5 class="card-title mb-0 text-capitalize">{{ __('admin.all Stages') }}</h5>
                @if( $_user->ableToAny(['stages.create', 'stages.store'], $permissions) )
                  <button 
                    class="btn btn-primary" 
                    data-bs-toggle="modal" data-bs-target="#CreateStage">
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
                          <th>{{ __('admin.Stage') }}</th>
                          <th>{{ __('admin.Description') }}</th>
                          <th>{{ __('cycles.Created at') }}</th>
                          @if( $_user->ableToAny(['stages.show', 'stages.edit', 'stages.update', 'stages.destroy'], $permissions) )<th>{{ __('admin.Actions') }}</th>@endif
                        </tr>
                      </thead>
                      <tbody>
                        @php
                        $c=1;
                        @endphp
                        @foreach ($stages as $stage)
                        <tr>
                          <td>{{$c++}}</td>
                          <td>{{$stage->{'name_' . $l} }}</td>
                          <td>{{$stage->{'description_' . $l} }}</td>
                          <td>{{$stage->created_at}}</td>
                          @if( $_user->ableToAny(['stages.show', 'stages.edit', 'stages.update', 'stages.destroy'], $permissions) )
                          <td>
                            @if( $_user->ableTo('stages.show', $permissions) )<div class="btn btn-info" data-bs-toggle="modal" data-bs-target="#ShowStage{{ $stage->id }}" >{{__('admin.Show')}}</div>@endif
                            @if( $_user->ableToAny(['stages.edit', 'stages.update'], $permissions) )<div class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#EditStage{{ $stage->id }}">{{__('admin.Edit')}}</div>@endif
                            @if( $_user->ableTo('stages.destroy', $permissions) )
                            <form action="{{ route('stages.destroy', $stage->id) }}" method="POST" style="display: inline-block;">
                              @csrf
                              @method('DELETE')
                              <button class="btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this post?')">{{__('admin.Delete')}}</button>
                            </form>
                            @endif
                          </td>
                          @endif
                        </tr>
                          @if( $_user->ableToAny(['stages.edit', 'stages.update'], $permissions) )
                          <!-- Edit Customer Modal -->
                          <div class="modal fade" id="EditStage{{ $stage->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        <div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.Edit')}} ({{$stage->{'name_'.$l }   }})</h4> </div>
                                        <form class="add-new-user pt-0" action="{{ route('stages.update', $stage->id) }}" method="POST">
                                          @csrf
                                          @method('PUT')
                                          <input type="hidden" value="{{ $stage->id }}" name="stage_id" >
                                          <div class="ecommerce-customer-add-basic mb-4" >
                                              <div class="col-md-12">
                                                <div class="mb-6 width49">
                                                  <div> <label for="stage_name_ar" class="form-label" >{{ __('admin.StageName') }} ( {{ __('admin.Arabic') }} )</label> </div>
                                                  <input
                                                      id="stage_name_ar"
                                                      class="form-control @error('stage_name_ar') is-invalid @enderror"
                                                      type="text"
                                                      name="stage_name_ar"
                                                      autocomplete="off"
                                                      value="{{$stage->name_ar}}"
                                                  />
                                                </div>
                                                <div class="mb-6 width49">
                                                  <div> <label for="stage_name_en" class="form-label" >{{ __('admin.StageName') }} ( {{ __('admin.English') }} )</label> </div>
                                                  <input
                                                      id="stage_name_en"
                                                      class="form-control @error('stage_name_en') is-invalid @enderror"
                                                      type="text"
                                                      name="stage_name_en"
                                                      autocomplete="off"
                                                      value="{{$stage->name_en}}"
                                                  />
                                                </div>
                                                <div class="mb-6 width49">
                                                    <div> <label for="stage_Description_ar" class="form-label" >{{__('admin.StageDescription')}} ( {{ __('admin.Arabic') }} )</label> </div>
                                                    <textarea id="stage_Description_ar" class="form-control @error('stage_Description_ar') is-invalid @enderror"
                                                      type="text"
                                                      name="stage_Description_ar"
                                                      autocomplete="off">{{$stage->description_ar}}
                                                    </textarea>
                                                </div>
                                                <div class="mb-6 width49">
                                                  <div> <label for="stage_Description_en" class="form-label" >{{__('admin.StageDescription')}} ( {{ __('admin.English') }} )</label> </div>
                                                  <textarea id="stage_Description_en"
                                                    class="form-control @error('stage_Description_en') is-invalid @enderror"
                                                    type="text"
                                                    name="stage_Description_en"
                                                    autocomplete="off">{{$stage->description_en}}
                                                  </textarea>
                                                </div>
                                              </div>
                                          </div>
                                          <div class="text-center">
                                            @if( $_user->ableToAny(['stages.edit', 'stages.update'], $permissions) )
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
                          @if( $_user->ableTo('stages.show', $permissions) )
                          <!-- Show Customer Modal -->
                          <div class="modal fade" id="ShowStage{{ $stage->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content" dir="ltr">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    <div class="text-center mb-6">
                                        <h4 class="mb-2">{{__('admin.Show')}} ( {{ $stage->{'name_' . $l} }} )</h4>
                                    </div>


                                    <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                                        <div class="d-flex gap-4 align-items-center">
                                            <h6 class="m-0">{{ $stage->{'name_ar'} }} <br> {{ $stage->{'name_en'} }}</h6>
                                        </div>
                                        <p class="m-0 d-none d-sm-block">{{ __('admin.Stage') }}</p>
                                    </div>


                                    <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                                        <div class="d-flex gap-4 align-items-center">
                                            <h6 class="m-0">{{$stage->{'description_ar'} }}<br>{{ $stage->{'description_en'} }}</h6>
                                        </div>
                                        <p class="m-0 d-none d-sm-block">{{__('admin.StageDescription')}}</p>
                                    </div>


                                    <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                                        <div class="d-flex gap-4 align-items-center">
                                            <h6 class="m-0">{{ $stage->created_at}}</h6>
                                        </div>
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

        @if( $_user->ableToAny(['stages.create', 'stages.store'], $permissions) )
        <!-- Create JobOrder Modal -->
        <div class="modal fade" id="CreateStage" tabindex="-1" aria-hidden="true" dir="rtl">
          <div class="modal-dialog modal-lg modal-simple modal-edit-user">
              <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.Stage')}}</h4> </div>
                    <form method="POST" action="{{route('stages.store')}}" class="pt-0" >
                        @csrf
                        <div class="ecommerce-customer-add-basic mb-4" >
                          <div class="col-md-12">
                            <div class="mb-6 width49">
                              <div> <label for="stage_name_ar" class="form-label" >{{ __('admin.StageName') }} ( {{ __('admin.Arabic') }} )</label> </div>
                              <input
                                  id="stage_name_ar"
                                  class="form-control @error('stage_name_ar') is-invalid @enderror"
                                  type="text"
                                  name="stage_name_ar"
                                  autocomplete="off"
                              />
                            </div>
                            <div class="mb-6 width49">
                              <div> <label for="stage_name_en" class="form-label" >{{ __('admin.StageName') }} ( {{ __('admin.English') }} )</label> </div>
                              <input
                                  id="stage_name_en"
                                  class="form-control @error('stage_name_en') is-invalid @enderror"
                                  type="text"
                                  name="stage_name_en"
                                  autocomplete="off"
                              />
                            </div>
                            <div class="mb-6 width49">
                              <div> <label for="stage_Description_ar" class="form-label" >{{__('admin.StageDescription')}} ( {{ __('admin.Arabic') }} )</label> </div>
                              <textarea id="stage_Description_ar"
                                class="form-control @error('stage_Description_ar') is-invalid @enderror"
                                type="text"
                                name="stage_Description_ar"
                                autocomplete="off">
                              </textarea>
                            </div>
                            <div class="mb-6 width49">
                              <div> <label for="stage_Description_en" class="form-label" >{{__('admin.StageDescription')}} ( {{ __('admin.English') }} )</label> </div>
                              <textarea id="stage_Description_en"
                                class="form-control @error('stage_Description_en') is-invalid @enderror"
                                type="text"
                                name="stage_Description_en"
                                autocomplete="off">
                              </textarea>
                            </div>
                          </div>
                        </div>
                        <div>
                          @if( $_user->ableToAny(['stages.create', 'stages.store'], $permissions) )
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
                    url: "{{ route('subcategories.list_by_category') }}",
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
