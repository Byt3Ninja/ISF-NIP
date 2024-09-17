@extends('layouts.admin')
@section('style')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop
@section('content')

<!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-12 mb-2 mt-1">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h5 class="content-header-title float-left pr-1 mb-0">{{__('cycles.all courses')}}</h5>
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb p-0 mb-0">
                                    <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a>
                                    </li>
									<li class="breadcrumb-item active">{{__('cycles.all Training courses')}}
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<div class="content-body">
    <section id="multiple-column-form">
        @if(session()->has('message'))
            <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <div class="d-flex align-items-center"> <i class="bx bx-like"></i> <span> {{ __('admin.'.session()->get('message')) }} </span> </div>
            </div>
        @endif
    </section>
    
    <section class="row">
    <div class="col-xl-12 col-md-12 col-sm-12">
    <div class="card">
        <div class="card-header">
            
        </div>
            <div class="card-body">
            <div class="card-text">
                

<form method="post" action="/dashboard/training/store" enctype="multipart/form-data">
                
                    @csrf
                    <div class="col-md-7">
                    <div class="form-group">
                        <label>{{__('cycles.Image')}}</label>
                        <div class="custom-file">
                            <input type="file" class="form-control" name="image" id="image" accept='image/*'>
                            
                        </div>
                    </div>
                    </div>


                    <br>
                 <div class="col-md-7">
                    <label>{{__('cycles.Course Name')}}</label>
                    <div class="form-group">
                        <input id="title" name="title" type="text" placeholder="Title" class="form-control" required>
                    </div>
                </div>

                    
                        
                
                <br>
                        <div class="col-md-7">
                            <label>{{__('cycles.Code')}}:</label>
                            <div class="form-group">
                                    <input class="form-control" value="{{$randomNumber}}" name='code' placeholder="{{__('cycles.Code')}}." readonly="readonly" >
                            </div>
                            </div>

                           

                            <br>
<div class="col-md-7 col-12 mt-4">
    <div class="form-label-group">
      <h6 for="CoursCategories">{{ __('cycles.CoursCategories') }}</h6>
      <div class="form-group">
        <select name="CoursCategories[]" class="select2 form-control" multiple="multiple"
          id="CoursCategories">
          @foreach ($expertises as $expertise_group)
            <optgroup label="{{ $expertise_group->title }}">
              @foreach ($expertise_group->children as $expertise)
                <option value="{{ $expertise->id }}"
                  @if (in_array($expertise->id, $client->mentorExpertises()->pluck('expertises.id')->toArray())) selected @endif>{{ $expertise->title }}
                </option>
              @endforeach
            </optgroup>
          @endforeach
        </select>
      </div>
    </div>
  </div>

                            
                            <div class="col-md-7 mb-6">
                                <label for="TagifyBasic" class="form-label">{{__('cycles.Tags')}}</label>
                                <tags class="tagify form-control" tabindex="-1">
                                <input id="TagifyBasic" class="form-control" name="Tags" value="Tag1, Tag2, Tag3" tabindex="-1">
                              </div>

                            <div class="col-md-7 mb-6">
                                <label class="form-label">{{__('cycles.CoursLevel')}}</label>
                                    <select  class="form-select" name="CoursLevel" multiple="" >
                                    <option value="AK">Alaska</option>
                                </select>
                            </div>

                            <div class="col-md-7 mb-6">
                                <label class="form-label">{{__('cycles.Quizs')}}</label>
                                    <select  class="form-select" name="Quizs" multiple="" >
                                    <option value="AK">Alaska</option>
                                </select>
                            </div>

                            <div class="col-md-7 mb-6">
                                <label class="form-label">{{__('cycles.Instractor')}}</label>
                                    <select  class="form-select" name="Instractor" multiple="" >
                                    <option value="AK">Alaska</option>
                                </select>
                            </div>

                            <div class="col-md-7 col-12 mb-6">
                                <label for="flatpickr-date" class="form-label">{{__('cycles.CoursStart')}}</label>
                                <input type="text" class="form-control flatpickr-input active" placeholder="YYYY-MM-DD" >
                              </div>


                              <div class="col-md-7 col-12 mb-6">
                                <label for="flatpickr-date" class="form-label">{{__('cycles.CoursEnd')}}</label>
                                <input type="text" class="form-control flatpickr-input active" placeholder="YYYY-MM-DD" id="flatpickr-date" readonly="readonly">
                              </div>

                              <div class="row">
                                <div class="col-md mb-md-0 mb-5">
                                  <div class="form-check custom-option custom-option-basic checked">
                                    <label class="form-check-label custom-option-content" for="customRadioTemp1">
                                      <input name="customRadioTemp" class="form-check-input" type="radio" value="" id="customRadioTemp1" checked="">
                                      <span class="custom-option-header">
                                        <span class="h6 mb-0">{{__('cycles.Draft')}}</span>
                                      </span>
                                      
                                    </label>
                                  </div>
                                </div>
                                <div class="col-md">
                                  <div class="form-check custom-option custom-option-basic">
                                    <label class="form-check-label custom-option-content" for="customRadioTemp2">
                                      <input name="customRadioTemp" class="form-check-input" type="radio" value="" id="customRadioTemp2">
                                      <span class="custom-option-header">
                                        <span class="h6 mb-0">{{__('cycles.Published')}}</span>
                                      </span>
                                    </label>
                                  </div>
                                </div>
                              </div>


                              <br>
                              <div class="col-md-7">
                                  <label>{{__('cycles.Add a description')}}:</label>
                                  <div class="form-group">
                                          <textarea class="form-control" id="dsc" name='dsc' rows="4"
                                                    placeholder="{{__('cycles.Write your description here')}}." ></textarea>
                                  </div>
                                  </div>



                <div class="modal-footer">
                <div class="col-md-3">

                    <button type="submit" class="btn btn-primary ml-1" >
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">{{__('cycles.SaveProgram')}}</span>
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-info ml-1" >
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">{{__('cycles.Cancel')}}</span>
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success ml-1" >
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">{{__('cycles.PublishProgram')}}</span>
                    </button>
                </div>
                </div>
            </form>





            </div>

        </div>
    </div>
    </div>
    </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->
<div class="modal fade text-left" id="edittraining" tabindex="-1" role="dialog" aria-labelledby="sendrequest" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form method="post" action="/dashboard/training/store" enctype="multipart/form-data">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('cycles.Modify training course')}} </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    @csrf
                  	<input type="hidden" name="id" value="" id="id">
                    <div style="text-align: center;" class="form-group" id="showimage">
                    </div>
                    <label>{{__('cycles.Course Title')}}</label>
                    <div class="form-group">
                        <input id="title" name="title" type="text" placeholder="Title" class="form-control" required>
                    </div>
                    <label>{{__('cycles.Add a description')}}:</label>
                    <div class="form-group">
                            <textarea class="form-control" id="dsc" name='dsc' rows="4"
                                      placeholder="{{__('cycles.Write your description here')}}." ></textarea>
                    </div>
                        <div class="form-group">
                            <label>{{__('cycles.Image')}}</label>
                            <div class="custom-file">
                                <input type="file" class="form-control" name="image" id="image" accept='image/*'>
                                <label class="custom-file-label" for="image">{{__('cycles.Browse')}}</label>
                            </div>
                        </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary ml-1" >
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">{{__('Submit')}}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                    {"orderable": false, "targets":  [4]},
                ],
                @if ($l=='ar')
                "language": {
                    "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول",
                    "sLoadingRecords": "جارٍ التحميل...", "sProcessing":   "جارٍ التحميل...",
                    "sLengthMenu":   "أظهر _MENU_ مدخلات", "sZeroRecords":  "لم يعثر على أية سجلات",
                    "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                    "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل", "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                    "sInfoPostFix":  "", "sSearch":       "ابحث:", "sUrl":          "",
                    "oPaginate": {"sFirst":    "الأول", "sPrevious": "السابق", "sNext":     "التالي", "sLast":     "الأخير"},
                    "oAria": {"sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}}
                @endif
            });
        });
    </script>
        
@stop