@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">

  <link rel="stylesheet" href="/vuexy/libs/tagify/tagify.css" />

  <style>
    input[type="file"] {
      display: none;
    }

    .label-container {
      width: 100%;
      display: flex;
    }

    #browse-button {
      position: absolute;
      right: 0;
      top: 0;
    }

    #levelButton {
      cursor: pointer;
      height: 35px;
      width: 150px;
      font-size: 14px;
    }

    @media screen and (max-width: 768px) {
      #levelButton {
        width: 90px;
        height: 30px;
        font-size: 11px;
        text-wrap: nowrap;
      }
    }

    .light-input-shadow {
      box-shadow: 0px 2px 0px 0px #F2F1F3;
    }
  </style>
@stop

@section('content')
  @php
    $title = $l == 'en' ? 'Create Course' : 'إنشاء دورة';
    $visibilityStatus = $l == 'en' ? 'Visibility Status' : 'حالة الرؤية';
  @endphp

  <!-- Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        <!-- Alert -->
        @if (session()->has('message'))
          <div 
            class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible"
            role="alert"
          >
            {{ __('admin.' . session()->get('message')) }} </span> </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
        <!-- / Alert -->

        <section class="row">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
              <div class="card-header border-bottom border-light" style="height: 70px;">
                <h5 class="card-title">{{ $title }}</h5>
              </div>
              <div class="card-body">
                <div class="py-4">
                  <!-- Add Course Form -->
                  <form method="post" action="/dashboard/training/store" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-3">
                      <span class="form-label">{{ __('cycles.Image') }}</span>
                      <label 
                        for="file-upload" 
                        class="form-control mt-2 border-light light-input-shadow"
                        style="position: relative;"
                      >
                        <span id="file-name" class="text-muted fs-sm">Browse</span>
                        <div 
                          id="browse-button"
                          class="btn btn-label-primary border-primary"
                          style="width: 80px; font-size: 14px;" 
                        >
                          Browse
                        </div>
                      </label>
                      <input type="file" name="image" id="file-upload" accept='image/*' />
                    </div>
                    <div class="form-group mb-3">
                      <label class="form-label">{{ __('cycles.Course Name') }}</label>
                      <input 
                        id="title" 
                        name="title" 
                        type="text"
                        placeholder="Title"
                        class="form-control border-light light-input-shadow" 
                        required
                      />
                    </div>
                    <div class="form-group mb-3">
                      <label class="form-label">
                        {{ __('cycles.Code') }} 
                        <span class="text-secondary">
                          {{ $l == 'en' ? ' ( Auto-generated )' : ' (تلقائي)' }}
                        </span>
                      </label>
                      <input
                        name='code'
                        value="{{ $randomNumber }}"
                        placeholder="{{ __('cycles.Code') }}."
                        class="form-control border-light light-input-shadow" 
                        style="width: 100px;"
                        readonly="readonly"
                      />
                    </div>
                    <div class="form-group mb-3">
                      <label class="form-label" for="CoursCategories">
                        {{ __('cycles.CoursCategories') }}
                      </label>
                      <select 
                        name="CoursCategories[]" 
                        class="select2 form-control"
                        multiple="multiple"
                        id="CoursCategories"
                      >
                        @foreach ($expertises as $index => $expertise_group)
                          <optgroup label="{{ $expertise_group->title }}">
                            @foreach ($expertise_group->children as $expertise)
                              <option 
                                value="{{ $expertise->id }}"
                                 @if (in_array($expertise->id,  $client->mentorExpertises()->pluck('expertises.id')->toArray())) 
                                 @elseif($index == 0 && $loop->first)
                                  selected 
                                @endif
                              >
                                {{ $expertise->title }}
                              </option>
                            @endforeach
                          </optgroup>
                        @endforeach
                      </select>
                    </div>
                    <div class="form-group mb-3">
                      <label for="TagifyBasic" class="form-label">{{ __('cycles.Tags') }}</label>
                      <select 
                        id="TagifyBasic" 
                        name="Tags[]" 
                        class="select2 form-control"
                        multiple="multiple"
                      >
                        <option value="css" selected>css</option>
                        <option value="html" selected>html</option>
                        <option value="javascript" selected>javascript</option>
                      </select>
                    </div>
                    <div class="form-group mb-3">
                      <label class="form-label">{{ __('cycles.CoursLevel') }}</label>
                      <div 
                        style="height: 45px; width: fit-content; background-color: #F5F3FF; padding: 0 0.5rem;" 
                        class="row align-items-center rounded-pill"
                      >
                        <button
                          type="button"
                          id="levelButton"
                          class="bg-white rounded-pill border-none text-primary fw-bold"
                        >
                          {{ __('cycles.Beginner') }}
                        </button>
                        <button
                          type="button"
                          id="levelButton"
                          class="bg-white rounded-pill border-none text-primary fw-bold mx-1"
                        >
                          {{ __('cycles.Intermediate') }}
                        </button>
                        <button
                          type="button"
                          id="levelButton"
                          class="bg-white rounded-pill border-none text-primary fw-bold"
                        >
                          {{ __('cycles.Advanced') }}
                        </button>
                      </div>
                    </div>

                    {{-- @if(count($Quizzes && $Quizzes > 0))
                      <div class="form-group mb-3">
                        <label class="form-label">{{__('cycles.Quizs')}}</label>
                          <select  class="select2 form-select" name="Quizs" >
                            @foreach ($Quizzes as $Quizz)
                            <option value="{{$Quizz->id}}">{{$Quizz->title}}</option>
                            @endforeach
                        </select>
                      </div>
                    @endif --}}

                    <div class="form-group mb-3">
                      <label class="form-label">
                        {{ __('cycles.Instractor') }}
                        <span class="text-secondary">
                          {{ $l == 'en' ? 'Choode one or more' : 'اختر واحد أو أكثر' }}
                        </span>
                      </label>
                      <select class="select2 form-select" name="Instractor">
                        <option value="" disabled selected>
                          {{ $l == 'en' ? 'Choose Instructors' : 'اختر المدرب' }}
                        </option>
                        @foreach ($instructors as $instructor)
                          <option value="{{ $instructor->id }}">
                            {{ $instructor->first_name_ar . ' ' . $instructor->last_name_ar }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-7 col-12 mb-6">
                      <label for="flatpickr-date" class="form-label">{{ __('cycles.CoursStart') }}</label>
                      <input type="text" class="form-control flatpickr-input active" name="start_date"
                        placeholder="YYYY-MM-DD">
                    </div>


                    <div class="col-md-7 col-12 mb-6">
                      <label for="flatpickr-date" class="form-label">{{ __('cycles.CoursEnd') }}</label>
                      <input type="text" class="form-control flatpickr-input active" name="end_date"
                        placeholder="YYYY-MM-DD" id="flatpickr-date">
                    </div>

                    <div class="form-group mb-3">
                      <label class="form-label">{{ $visibilityStatus }}</label>
                      <div 
                        style="height: 45px; width: fit-content; background-color: #F5F3FF; padding: 0 0.5rem;" 
                        class="row align-items-center rounded-pill"
                      >
                        <button
                          type="button"
                          {{-- id="levelButton" --}}
                          style="width: 140px; height: 30px; font-size: 14px;"
                          class="bg-white rounded-pill border-none text-primary fw-bold mx-1"
                        >
                          {{ __('cycles.Intermediate') }}
                        </button>
                        <button
                          type="button"
                          {{-- id="levelButton" --}}
                          style="width: 140px; height: 30px; font-size: 14px;"
                          class="bg-white rounded-pill border-none text-primary fw-bold"
                        >
                          {{ __('cycles.Advanced') }}
                        </button>
                      </div>
                    </div>

                    {{-- <div class="row">
                      <div class="col-md mb-md-0 mb-5">
                        <div class="form-check custom-option custom-option-basic checked">
                          <label class="form-check-label custom-option-content" for="customRadioTemp1">
                            <input name="Status" class="form-check-input" type="radio" value="0"
                              id="customRadioTemp1" checked="">
                            <span class="custom-option-header">
                              <span class="h6 mb-0">{{ __('cycles.Draft') }}</span>
                            </span>

                          </label>
                        </div>
                      </div>
                      <div class="col-md">
                        <div class="form-check custom-option custom-option-basic">
                          <label class="form-check-label custom-option-content" for="customRadioTemp2">
                            <input name="Status" class="form-check-input" type="radio" value="1"
                              id="customRadioTemp2">
                            <span class="custom-option-header">
                              <span class="h6 mb-0">{{ __('cycles.Published') }}</span>
                            </span>
                          </label>
                        </div>
                      </div>
                    </div> --}}
                    <div class="form-group mb-3">
                      <label class="form-label">{{ __('cycles.Add a description') }}</label>
                      <textarea 
                        class="form-control border-light light-input-shadow" 
                        id="dsc" 
                        name='dsc' 
                        rows="4"
                        placeholder="{{ __('cycles.Write your description here') }}."
                        style="height: 220px;"
                      >
                      </textarea>
                    </div>

                    <hr> <br>
                    <div class="modal-footer">
                      <div class="col-md-3">

                        <button type="submit" class="btn btn-primary ml-1">
                          <i class="bx bx-check d-block d-sm-none"></i>
                          <span class="d-none d-sm-block">{{ __('cycles.SaveProgram') }}</span>
                        </button>
                      </div>
                    </div>
                  </form>
                  <!-- Add Course Form -->
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
  <!-- / Content-->
@stop

@section('scripts')
  <script>
    // Access uploaded files from the file input
    const fileInput = document.querySelector('#file-upload');
    const label = document.querySelector('#file-name');

    // Listen for the change event on the file input
    fileInput.addEventListener('change', (e) => {
      const fileName = e.target.value.split('\\').pop();
      if (fileName) {
        // console.log('fileName', fileName)
        label.innerText = fileName;
      } else {
        label.innerText = 'Browse';
      }
    });
  </script>

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
