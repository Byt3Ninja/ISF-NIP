@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/tagify/tagify.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />

  <style>
   .custom-container-ar {
    padding-left: 8rem;
    padding-right: 2rem;
    }

    .custom-container-en {
      padding-left: 2rem;
      padding-right: 8rem;
    }

    input[type="file"] {
      display: none;
    }

    .label-container {
      width: 100%;
      display: flex;
    }

    #browse-button {
      position: absolute;
      top: 0;
    }

    #levelButton {
      cursor: pointer;
      height: 35px;
      width: 150px;
      font-size: 14px;
      background-color: white;
    }

    #levelButton:hover {
      background-color: #e9e7fd;
    }

    #statusButton {
      background-color: white;
    }

    #statusButton:hover {
      background-color: #e9e7fd;
    }

    #dateInputContainer {
      position: relative;
    }

    #dateInputContainer img {
      position: absolute;
      top: 2rem;
    }

    @media screen and (max-width: 768px) {
      .custom-container {
        padding: 0;
      }

      .custom-container-ar,
      .custom-container-en
       {
        padding: 0;
      }

      #levelButton {
        width: 90px;
        height: 30px;
        font-size: 12px;
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
  <div class="">
    <div class="">
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
            <div class="card {{ $l == 'ar' ? 'custom-container-ar' : 'custom-container-en' }}">
              <div class="card-header border-bottom border-light" style="height: 70px;">
                <h4 class="card-title">{{ $title }}</h4>
              </div>
              <div class="card-body">
                <div class="py-4">
                  <!-- Add Course Form -->
                  <form method="post" action="/dashboard/training/store" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-3">
                      <span class="form-label fs-6">{{ __('cycles.Image') }}</span>
                      <label 
                        for="file-upload" 
                        class="form-control mt-2 border-light light-input-shadow"
                        style="position: relative;"
                      >
                        <span id="file-name" class="text-muted fs-sm">
                          {{ $l == 'en' ? 'Browse' : 'تصفح' }}
                        </span>
                        <div 
                          id="browse-button"
                          class="btn btn-label-primary border-primary"
                          style="width: 80px; font-size: 14px; {{$l == 'ar' ? 'left: 0;' : 'right: 0;'}}" 
                        >
                          {{ $l == 'en' ? 'Browse' : 'تصفح' }}
                        </div>
                      </label>
                      <input type="file" name="image" id="file-upload" accept='image/*' />
                    </div>
                    <div class="form-group mb-3">
                      <label class="form-label fs-6">{{ __('cycles.Course Name') }}</label>
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
                      <label class="form-label fs-6">
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
                      <label class="form-label fs-6" for="CoursCategories">
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
                     <!-- Custom Suggestions: Inline -->
                     <div class="col-md-6 mb-6">
                      <label for="TagifyCustomInlineSuggestion" class="form-label">{{ __('cycles.Tags') }}</label>
                      <input
                        id="TagifyCustomInlineSuggestion"
                        name="Tags[]"
                        class="form-control TagsCreateKey"
                        placeholder="select technologies"
                        value="" />
                    </div>
                    {{-- <div class="form-group mb-3">
                      <label for="TagifyBasic" class="form-label fs-6">{{ __('cycles.Tags') }}</label>
                      <select 
                        id="TagifyBasic" 
                        name="Tags[]" 
                        class="select2 form-control"
                        multiple="multiple"
                      >
                      @foreach ($Tags as $Tag)
                        <option value="{{ $Tag->id }}" >{{ $Tag->name }}</option>
                        @endforeach
                      </select>
                    </div> --}}
                    <div class="form-group mb-3">
                      <label class="form-label fs-6">{{ __('cycles.CoursLevel') }}</label>
                      <div 
                        style="height: 45px; width: fit-content; background-color: #F5F3FF; padding: 0 0.5rem;" 
                        class="row align-items-center rounded-pill"
                      >
                        <button
                          type="button"
                          id="levelButton"
                          class="rounded-pill border-none vallevel0 text-primary fw-bold"
                        >
                          {{ __('cycles.Beginner') }}
                        </button>
                        <button
                          type="button"
                          id="levelButton"
                          class="rounded-pill border-none vallevel1 text-primary fw-bold mx-1"
                        >
                          {{ __('cycles.Intermediate') }}
                        </button>
                        <button
                          type="button"
                          id="levelButton"
                          class="rounded-pill border-none vallevel2 text-primary fw-bold"
                        >
                          {{ __('cycles.Advanced') }}
                        </button>
                      </div>
                    </div>
                    <input class="getValLevel" name="CoursLevel" type="hidden" >
                    
                      <div class="form-group mb-3">
                        <label class="form-label fs-6">{{__('cycles.Quizs')}}</label>
                          <select  class="select2 form-select" name="Quizs" >
                            <option value="0">{{ __('admin.noQuiz') }}</option>
                            @foreach ($Quizzes as $Quizz)
                            <option value="{{$Quizz->id}}">{{$Quizz->title}}</option>
                            @endforeach
                        </select>
                      </div>
              
                     <div class="row mt-3">
                      <span class="form-label font-semibold fs-6 mt-4">{{ __('admin.visability_status') }}</span>
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
                    </div>

                    @if(isset($organizations) && $organizations->count() > 0)
                      <div class="row mt-3">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="form-label font-semibold fs-6">
                              {{ $l == 'en' ? 'Organization' : 'المنظمة' }}
                            </label>
                            <select name="organization_id" class="select2 form-select">
                              <option value="">{{ $l == 'en' ? 'Platform-wide (No Organization)' : 'على مستوى المنصة (بدون منظمة)' }}</option>
                              @foreach($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->{"name_{$l}"} }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                          <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_public" value="1" id="isPublicToggle" checked>
                            <label class="form-check-label" for="isPublicToggle">
                              {{ $l == 'en' ? 'Visible to all organizations' : 'مرئي لجميع المنظمات' }}
                            </label>
                          </div>
                        </div>
                      </div>
                    @endif

                    <div class="form-group mt-4">
                      <label class="form-label fs-6">{{ __('cycles.Add a description') }}</label>
                      <textarea
                        class="form-control border-light light-input-shadow"
                        id="dsc"
                        name='dsc'
                        rows="4"
                        placeholder="{{ __('cycles.Write your description here') }}."
                        style="height: 220px;"
                      ></textarea>
                    </div>

                    <hr> <br>
                    <div class="d-flex align-items-center">
                      <button 
                        type="submit" 
                        class="btn btn-primary rounded-pill fw-bold"
                        style="font-size: 14px;"
                      >
                        <i class="ti ti-world"></i>
                        <span class="mx-1">
                          {{ $l == 'en' ? 'Publish Program' : 'نشر البرنامج' }}
                        </span>
                      </button>
                     
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
    
    $lang = '{{ $l }}';

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
        label.innerText = $lang === 'en' ? 'Browse' : 'تصفح';
      }
    });
  </script>

  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/tagify/tagify.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
  <script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
  <script src="/vuexy/assets/vendor/libs/bloodhound/bloodhound.js"></script>

  <script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
@stop

@section('pagescripts')
  <!-- Page JS -->
  <script src="/vuexy/assets/js/forms-selects.js"></script>
  {{-- <script src="/vuexy/assets/js/forms-tagify.js"></script> --}}
  <script src="/vuexy/assets/js/forms-typeahead.js"></script>
  <script src="/js/jquery.datetimepicker.full.min.js"></script>

  <script>
     

    const statusButton = document.querySelectorAll('#statusButton');
    const levelButton = document.querySelectorAll('#levelButton');

    statusButton.forEach((button) => {
      button.addEventListener('click', () => {
        statusButton.forEach((btn) => {
          btn.classList.remove('bg-primary', 'text-white');
          btn.classList.add('bg-white', 'text-primary');
        });
        button.classList.remove('bg-white', 'text-primary');
        button.classList.add('bg-primary', 'text-white');
      });
    });

    levelButton.forEach((button) => {
      button.addEventListener('click', () => {
        levelButton.forEach((btn) => {
          btn.classList.remove('bg-primary', 'text-white');
          btn.classList.add('bg-white', 'text-primary');
        });
        button.classList.remove('bg-white', 'text-primary');
        button.classList.add('bg-primary', 'text-white');
      });
    });

    const TagifyCustomInlineSuggestionEl = document.querySelector('#TagifyCustomInlineSuggestion');
    TagifyCustomInlineSuggestionEl.addEventListener('keyup', function(event) {
        if (event.key === "Enter") {
            var gettag = this.value;  // جلب القيمة
            alert(gettag);  // عرض القيمة
        }
    });
  
    const whitelist = @json($Tags->pluck('name')->toArray());

    // Inline
    let TagifyCustomInlineSuggestion = new Tagify(TagifyCustomInlineSuggestionEl, {
      whitelist: whitelist,
      maxTags: 10,
      dropdown: {
        maxItems: 20,
        classname: 'tags-inline',
        enabled: 0,
        closeOnSelect: false
      }
    });
  </script>

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

      // Date Time Picker
      $('.date').datetimepicker({
        timepicker: true,
        format: 'Y-m-d H:i'
      });
      $('.datewithouttime').datetimepicker({
        format: 'Y-m-d'
      });

      $('.StatusDraft').click(function() {
        $('#Status').val(0);
      });

      $('.StatusPublished').click(function() {
        $('#Status').val(1);
      });



  </script>
@stop
