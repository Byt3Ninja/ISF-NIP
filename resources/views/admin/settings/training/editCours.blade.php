@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" href="/vuexy/libs/tagify/tagify.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
  <style>
    .custom-container-ar {
      padding-left: 8rem;
      padding-right: 2rem;
    }

    .custom-container-en {
      padding-left: 2rem;
      padding-right: 8rem;
    }

    @media screen and (max-width: 768px) {
      .custom-container-ar,
      .custom-container-en
       {
        padding: 0;
      }
    }
  </style>
@stop

@php
  $trainingTags = App\Training::with('tags')->find($course->id);
  $tags = $trainingTags->tags;

  $trainingCategories = App\Training::with('categories')->find($course->id);
  $Categories = $trainingCategories->categories;
  $StatusDraft = $l == 'ar' ? 'محفوظة' : 'Draft';
  $Statuspublished = $l == 'ar' ? 'تم النشر' : 'Published';

  $editCourse = $l == 'ar' ? 'تعديل الدورة' : 'Edit Course';
  $visibilityStatus = $l == 'ar' ? 'حالة الظهور' : 'Visibility Status';
@endphp

@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="row  d-flex justify-content-center">
        <div class="col-12 col-md-8 col-lg-9">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title pb-4 border-bottom">{{ $editCourse }}</h5>
            </div>
            <div class="card-body">
              <div id="multiple-column-form">
                @if (session()->has('message'))
                  <div 
                    class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
                    role="alert">
                    {{ __('admin.' . session()->get('message')) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                @endif
              </div>
              <form method="post" action="/dashboard/training/store" enctype="multipart/form-data" class="row">
                @csrf
                <input type="hidden" name="id" value="{{ $course->id }}" id="id">
                  <div class="form-group mb-4">
                    <label class="form-label font-semibold fs-6 mb-1">{{ __('cycles.Image') }}</label>
                    <div class="custom-file">
                      <input type="file" class="form-control" name="image" id="image" accept='image/*'>
                    </div>
                  </div>
                  <div class="form-group mb-4">
                    <label class="form-label font-semibold fs-6 mb-1">{{ __('cycles.Course Name') }}</label>
                    <input id="title" name="title" type="text" value="{{ $course->title }}"
                      placeholder="Title" class="form-control" required>
                  </div>
                  <div class="form-group mb-4">
                    <label class="form-label font-semibold fs-6 mb-1">{{ __('cycles.Code') }}:</label>
                    <input class="form-control" value="{{ $course->code }}" name='code'
                      placeholder="{{ __('cycles.Code') }}." readonly="readonly">
                  </div>
                  <div class="form-label-group">
                    <label class="form-label font-semibold fs-6 mb-1" for="CoursCategories">{{ __('cycles.CoursCategories') }}</label>
                    <div class="form-group mb-4">
                      <select name="CoursCategories[]" class="select2 form-select" multiple="multiple"
                        id="CoursCategories">
                        @foreach ($expertises as $expertise_group)
                          <optgroup label="{{ $expertise_group->title }}">
                            @foreach ($expertise_group->children as $expertise)
                              <option value="{{ $expertise->id }}" @if (in_array($expertise->id, $course->categories()->pluck('expertises.id')->toArray())) selected @endif>
                                {{ $expertise->title }}
                              </option>
                            @endforeach
                          </optgroup>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="form-group mb-4">
                    <label class="form-label font-semibold fs-6 mb-1" for="TagifyBasic" class="form-label">{{ __('cycles.Tags') }}</label>
                    <input id="TagifyBasic" name="Tags[]" class="form-control" placeholder="select technologies"
                      value="@foreach ($tags as $tag){{ $tag->name }} @endforeach" />
                  </div>
                  <div class="form-group mb-4">
                    <label class="form-label font-semibold fs-6 mb-1" class="form-label">{{ __('cycles.CoursLevel') }}</label>
                    <select class="form-select select2" name="CoursLevel">
                      <option value="Beginner" @if ($course->CoursLevel == 'Beginner') selected @endif>
                        {{ __('cycles.Beginner') }}</option>
                      <option value="Intermediate" @if ($course->CoursLevel == 'Intermediate') selected @endif>
                        {{ __('cycles.Intermediate') }}</option>
                      <option value="Advanced" @if ($course->CoursLevel == 'Advanced') selected @endif>
                        {{ __('cycles.Advanced') }}</option>
                    </select>
                  </div>
                  <div class="form-group mb-4">
                    <label class="form-label font-semibold fs-6 mb-1" class="form-label">{{ __('cycles.Quizs') }}</label>
                    <select class="form-select select2" name="Quizs">
                      <option value="0" {{ $course->Quizs == 0 || is_null( $course->Quizs ) || empty( $course->Quizs ) ? 'selected' : '' }}>{{ __('admin.noQuiz') }}</option>
                      @foreach ($Quizzes as $Quizz)
                        <option value="{{ $Quizz->id }}" @if ($course->Quizs == $Quizz->id) selected @endif>
                          {{ $Quizz->title }}</option>
                      @endforeach
                    </select>
                  </div>
                 
                  <div class="row mb-3">
                    <span class="form-label font-semibold fs-6 mb-1">{{ $visibilityStatus }}</span>
                    <div class="col-md-4">
                      <div class="form-check d-flex align-items-center custom-option custom-option-basic">
                        <label class="form-check-label custom-option-content fs-6 mb-1" for="customRadioTemp1">
                          <input name="Status" class="form-check-input me-2" type="radio" value="0" id="customRadioTemp1" @if ($course->Status == 0) checked @endif>
                            <span class="custom-option-header">
                                <span class="h6 mb-0">{{ __('cycles.Draft') }}</span>
                            </span>
                        </label>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-check d-flex align-items-center custom-option custom-option-basic">
                        <label class="form-check-label custom-option-content fs-6 mb-1" for="customRadioTemp2">
                          <input name="Status" class="form-check-input me-2" type="radio" value="1" id="customRadioTemp2" @if ($course->Status == 1) checked @endif>
                            <span class="custom-option-header">
                                <span class="h6 mb-0">{{ __('cycles.Published') }}</span>
                            </span>
                        </label>
                      </div>
                    </div>
                  </div>

                  @if(isset($organizations) && $organizations->count() > 0)
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label class="form-label font-semibold fs-6 mb-1">
                            {{ $l == 'en' ? 'Organization' : 'المنظمة' }}
                          </label>
                          <select name="organization_id" class="select2 form-select">
                            <option value="">{{ $l == 'en' ? 'Platform-wide (No Organization)' : 'على مستوى المنصة (بدون منظمة)' }}</option>
                            @foreach($organizations as $org)
                              <option value="{{ $org->id }}" @if($course->organization_id == $org->id) selected @endif>{{ $org->{"name_{$l}"} }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                          <input class="form-check-input" type="checkbox" name="is_public" value="1" id="isPublicToggle" @if($course->is_public) checked @endif>
                          <label class="form-check-label" for="isPublicToggle">
                            {{ $l == 'en' ? 'Visible to all organizations' : 'مرئي لجميع المنظمات' }}
                          </label>
                        </div>
                      </div>
                    </div>
                  @endif

                  <div class="form-group mb-4">
                    <label class="form-label font-semibold fs-6 mb-1">{{ __('cycles.Add a description') }}:</label>
                    <textarea class="form-control" id="dsc" name='dsc' rows="4"
                      placeholder="{{ __('cycles.Write your description here') }}.">{{ $course->dsc }}</textarea>
                  </div>
                <hr><br>
                <div class="modal-footer">
                 <button type="submit" class="btn btn-primary">
                    {{ __('cycles.SaveProgram') }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- END: Content-->
@stop

@section('scripts')
  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
  <script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
  @include('partials.flatpickr-locale')
  <script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>

  <!-- Page JS -->
  <script src="/vuexy/assets/js/pages-account-settings-account.js"></script>
  <script src="/vuexy/assets/js/forms-tagify.js"></script>
@stop

@section('pagescripts')
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/vuexy/assets/js/forms-pickers.js"></script>

  <script>
    $(document).ready(function() {
        $('#userstable').DataTable({
            "responsive": true, "searching": true, "lengthChange": false, "paging": true, "bInfo": false,
            "columnDefs": [
                {"orderable": false, "targets":  [4]},
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
      });
  </script>   
@stop
