@extends('layouts.admin')
@section('style')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
    
    <link rel="stylesheet" href="/vuexy/libs/tagify/tagify.css" />
@stop
@php
                               $trainingTags = App\Training::with('tags')->find($course->id);
                               $tags = $trainingTags->tags;

                               $trainingCategories = App\Training::with('categories')->find($course->id);
                               $Categories = $trainingCategories->categories;
                               $StatusDraft = $l == 'ar' ? 'محفوظة' : 'Draft';
                               $Statuspublished = $l == 'ar' ? 'تم النشر' : 'Published';
                            @endphp
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
                        <input id="title" name="title" type="text" value="{{$course->title}}" placeholder="Title" class="form-control" required>
                    </div>
                </div>

                    
                        
                
                <br>
                        <div class="col-md-7">
                            <label>{{__('cycles.Code')}}:</label>
                            <div class="form-group">
                                    <input class="form-control" value="{{$course->code}}" name='code' placeholder="{{__('cycles.Code')}}." readonly="readonly" >
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
                  @if (in_array($expertise->id, $course->categories()->pluck('expertises.id')->toArray())) selected @endif>{{ $expertise->title }}
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
                          <input
                            id="TagifyBasic"
                            name="Tags[]"
                            class="form-control"
                            placeholder="select technologies"
                            value="@foreach($tags as $tag){{$tag->name}}@endforeach" />
                        </div>

                            <div class="col-md-7 mb-6">
                                <label class="form-label">{{__('cycles.CoursLevel')}}</label>
                                    <select  class="form-select" name="CoursLevel"  >
                                    <option value="Beginner" @if ($course->CoursLevel== "Beginner") selected @endif >{{__('cycles.Beginner')}}</option>
                                    <option value="Intermediate" @if ($course->CoursLevel== "Intermediate") selected @endif>{{__('cycles.Intermediate')}}</option>
                                    <option value="Advanced" @if ($course->CoursLevel== "Advanced") selected @endif>{{__('cycles.Advanced')}}</option>
                                </select>
                            </div>



                            <div class="col-md-7 mb-6">
                                <label class="form-label">{{__('cycles.Quizs')}}</label>
                                    <select  class="form-select" name="Quizs" >
                                        @foreach ($Quizzes as $Quizz)
                                    <option value="{{$Quizz->id}}" @if ($course->Quizs== $Quizz->id) selected @endif>{{$Quizz->title}}</option>
                                    @endforeach
                                </select>
                            </div>




                            <div class="col-md-7 mb-6">
                                <label class="form-label">{{__('cycles.Instractor')}}</label>
                                    <select  class="form-select" name="Instractor" >
                                        @foreach ($instructors as $instructor)
                                        <option value="{{$instructor->id}}" @if ($course->Instractor== $instructor->id) selected @endif >{{$instructor->first_name_ar ." ". $instructor->last_name_ar }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-7 col-12 mb-6">
                                <label for="flatpickr-date" class="form-label">{{__('cycles.CoursStart')}}</label>
                                <input type="text" class="form-control flatpickr-input active" value="{{$course->start_date}}" name="start_date" placeholder="YYYY-MM-DD" >
                              </div>


                              <div class="col-md-7 col-12 mb-6">
                                <label for="flatpickr-date" class="form-label">{{__('cycles.CoursEnd')}}</label>
                                <input type="text" class="form-control flatpickr-input active" value="{{$course->end_date}}" name="end_date" placeholder="YYYY-MM-DD" id="flatpickr-date">
                              </div>

                              <div class="row">
                                <div class="col-md mb-md-0 mb-5">
                                  <div class="form-check custom-option custom-option-basic checked">
                                    <label class="form-check-label custom-option-content" for="customRadioTemp1">
                                      <input name="Status" class="form-check-input" type="radio" value="0" id="customRadioTemp1" @if ($course->Status== 0) checked @endif  >
                                      <span class="custom-option-header">
                                        <span class="h6 mb-0">{{__('cycles.Draft')}}</span>
                                      </span>
                                      
                                    </label>
                                  </div>
                                </div>
                                <div class="col-md">
                                  <div class="form-check custom-option custom-option-basic">
                                    <label class="form-check-label custom-option-content" for="customRadioTemp2">
                                      <input name="Status" class="form-check-input" type="radio" value="1" id="customRadioTemp2" @if ($course->Status== 1) checked @endif>
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
                                                    placeholder="{{__('cycles.Write your description here')}}." >{{$course->dsc}}</textarea>
                                  </div>
                                  </div>


                                  <hr><br>
                <div class="modal-footer">
                <div class="col-md-3">

                    <button type="submit" class="btn btn-primary ml-1" >
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">{{__('cycles.SaveProgram')}}</span>
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
<script src="/vuexy/assets/js/forms-tagify.js"></script>
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