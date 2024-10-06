@extends('layouts.admin')

@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.My Dashboard'))

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/css{{ $l == 'ar' ? '-rtl' : '' }}/pages/widgets.css">
  <link rel="stylesheet" type="text/css" href="/app-assets/css{{ $l == 'ar' ? '-rtl' : '' }}/pages/dashboard-ecommerce.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/jquery.rateyo.min.css">

  <style>
    .radius-15 {
      border-radius: 15px;
    }
    .custom-shadow {
      box-shadow: 0px 2px 0px 0px #F2F1F3;
    }
    .line-clamp-4 {
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 4;
      overflow: hidden;
      text-oveflow: ellipsis;
    }

    .mentors-list-container {
      display: flex;
      flex-flow: row;
      flex-wrap: wrap;
      gap: 10px;
    }
    .mentor-card {
      width: 370px;
    }
    
    @media screen and (max-width: 768px) {
      .mentors-list-container {
        min-width: 100%;
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 10px;
      }
      .mentor-card {
        min-width: 370px;
      }
    }
  </style>
@stop

@section('content')
  @if (!Auth::user()->first_name_ar)
    {!! redirect()->route('my_profile')->with(['msg' => 'FailedStore', 'class' => 'danger']) !!}
  @endif

  <!-- Content wrapper -->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        <!-- Alert -->
        @if (session()->has('msg'))
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
                role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <div class="d-flex align-items-center">
                  <i class="ti ti-error"></i>
                  <span> {{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }} </span>
                </div>
              </div>
            </div>
          </div>
        @endif
        <!-- / Alert -->

        <!-- Header -->
        <h4 class="fw-bold text-primary">{{ __('dashboard.Mentors') }}</h4>
        <div class="card" style="border-radius: 20px;">
          <div class="p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center">
              <!-- Mentors Count -->
              <div class="d-flex bg-label-warning p-2 justify-content-center align-items-center radius-15 shadow">
                <div class="bg-white d-flex justify-content-center align-items-center radius-15"
                  style="width: 40px; height: 40px; shadow">
                  <i class="ti ti-users ti-24px"></i>
                </div>
                <div class="mx-4" style="height: 50px;">
                  <p class="text-black fw-medium fs-5" style="margin-bottom: -3px;">
                    {{ __('dashboard.Mentors') }}
                  </p>
                  <span class="fs-5">
                    <!-- Total Mentors Count -->
                    {{ count($mentors) }} 
                  </span>
                </div>
              </div>
              <!-- / Mentors Count -->
              <div class="d-flex flex-column justify-content-between align-items-end">
                <!-- Search Container -->
                <div class="d-flex align-items-center justify-content-end">
                  <!-- Filter -->
                  <form method="GET" action="{{ route('Mentors_Filter') }}">
                    <div class="d-flex align-items-center">
                      <div style="display: flex; align-items: center;" class="mx-2">
                        <button 
                          {{-- id="filterButton" --}}
                          type="submit"
                          class="btn bg-primary p-3 d-flex justify-content-center align-items-center custom-shadow"
                          style="border-radius: 17px; width: 40px; height: 40px;" type="submit">
                          <i class="ti ti-filter text-white ti-24px"></i>
                        </button>
                      </div>
                      <!-- Search Bar -->
                      <div class="position-relative d-none d-md-flex">
                        <div class="position-absolute top-50 translate-middle-y search-icon" style="left: 1rem;">
                          <i class="ti ti-search text-primary ti-24px"></i>
                        </div>
                        <div @if (request('ExpertiesSearch') !='') style="display: none;" @endif class="Search">
                        <input
                          class="Search form-control custom-shadow search border rounded mx-1 bg-transparent text-primary px-10 py-2"
                          style="width: 366px;" id="search-table" placeholder="{{ __('admin.Search') }}" name="search"
                          value="{{ request('search') }}"  />
                        </div>
<div @if (request('rateSearch') =='') style="display: none;" @endif class="RateSelect">
  <select name="rateSearch" class="rateSearch form-control custom-shadow search border rounded mx-1 bg-transparent text-primary px-10 py-2"
  style="width: 366px;">
    <option value="" {{ request('rateSearch') == '' ? 'selected':'' }} >{{ __('admin.empty') }}</option>
    <option value="1" {{ request('rateSearch') == '1' ? 'selected':'' }} >1</option>
    <option value="2" {{ request('rateSearch') == '2' ? 'selected':'' }}>2</option>
    <option value="3" {{ request('rateSearch') == '3' ? 'selected':'' }}>3</option>
    <option value="4" {{ request('rateSearch') == '4' ? 'selected':'' }}>4</option>
    <option value="5" {{ request('rateSearch') == '5' ? 'selected':'' }}>5</option>
  </select>
</div>
<div @if (request('ExpertiesSearch') =='') style="display: none;" @endif class="ExpertiesInput">
<input type="text" value="{{ request('ExpertiesSearch') }}" placeholder="{{ __('admin.Experties_Search') }}" name="ExpertiesSearch" class="ExpertiesSearch form-control custom-shadow search border rounded mx-1 bg-transparent text-primary px-10 py-2"
style="width: 366px;">
</div>

                      </div>
                    </div>


                    <div class="row">
                      <!-- Sorting -->
                      <div id="sortContainer" class="d-none d-md-flex align-items-center mt-4">
                        <div class="row d-flex align-items-center" style="gap: 10px;">
                          <div class="col-md-3 d-flex flex-nowrap align-items-center">
                            <span class="fw-bold mx-1 text-black d-none d-md-flex" style="font-size:13px;">{{ __('admin.Rate') }}</span>
                            <div class="btn btn-sm btn-label-primary rounded-pill"
                              style="height:25px; min-width:95px; font-size:13px;">
                              <i class="ti ti-star me-2 text-warning"></i>
                              <input type="checkbox" {{ request('rateSearch') !=''? 'checked': '' }} class="RateShowOrNot" >
                              
                            </div>
                          </div>
                        </div>
                        <div class="row d-flex align-items-center" style="gap: 10px;">
                          <div class="col-md-3 d-flex flex-nowrap align-items-center">
                            <span class="fw-bold mx-1 text-black d-none d-md-flex" style="font-size:13px;">{{ __('admin.Expertises') }}</span>
                            <div class="btn btn-sm btn-label-primary rounded-pill"
                              style="height:25px; min-width:95px; font-size:13px;">
                              <i class="ti-24px ti ti-brand-linkedin me-2 text-info"></i>
                              <input type="checkbox" {{ request('ExpertiesSearch') !=''? 'checked': '' }} class="ExpertiesShowOrNot" >
                            </div>
                          </div>
                        </div>
                        
                        <!-- Sort Content Here -->
                      </div>
                      <!-- Sorting -->
                    </div>


                  </form>
                </div>
                <!-- / Search Container -->
                
              </div>
            </div>
            <!-- Search Bar (Mobile) -->
            <div class="d-flex align-items-center justify-content-between">
              <div class="position-relative me-3 mt-4 d-block d-md-none w-100">
                <div class="position-absolute top-50 translate-middle-y search-icon" style="left: 1rem;">
                  <i class="ti ti-search text-primary ti-24px"></i>
                </div>
                <input
                  class="form-control custom-shadow search border rounded mx-1 bg-transparent text-primary px-10 py-2 w-100"
                  id="search-table" placeholder="Search" />
              </div>
            </div>
            <!-- / Search Bar (Mobile) -->
            <!-- Sorting (Mobile) -->
            <div class="d-flex align-items-center mt-2">
              <div 
                id="sortContainerMobile" 
                class="d-flex d-md-none align-items-center mt-2"
                style="gap: 3px;"
              >
                <!-- Sort Content Here -->
              </div>
            </div>
            <!-- Sorting (Mobile) -->
          </div>
        </div>
        <!-- / Header -->    
      </div>
    </div>
  </div>

  <!-- Mentors -->
  <section class="px-md-5 mb-10">
    <h5 class="border-bottom pb-2 mb-6 mx-4 mx-md-0">{{ __('admin.Mentors') }}</h5>
    <div class="mentors-list-container">
      <!-- Mentors List -->
      @foreach ($mentors as $key => $mentor)
        @php
          $getcount = \App\CyclesMentors::where('user_id', $mentor->id ?? 0)->get();
          $m_count = count($getcount);
          $getid = \App\CyclesMentors::where('user_id', $mentor->id ?? 0)->first();
        @endphp
        <!-- Mentor Card -->
        <div class="mentor-card radius-15">
          <div class="border radius-15"
            style="background-image: url('{{ $mentor->image_url }}'); 
            height:293px; background-size: cover;
            background-position: center;">
            
          </div>
          <div 
            class="card shadow-none mx-2 p-3" 
            style="height: 350px; z-index: 1; margin-top: -25px; border-radius: 15px;"
          >
            <div
              class="bg-white custom-shadow border radius-15 px-2 py-1 d-flex justify-content-between align-items-center mb-2"
              style="height: 55px;"
            >
              <a 
                target="_blank" 
                href="{{ route('show_mentor_profile', $mentor->id) }}" 
                class="d-flex flex-column"
              >
                <span class="text-black fw-bold fs-6">{{ $mentor->name }}</span>
                <small class="text-secondary">{{ $mentor->title }}</small>
              </a>
              @if ($mentor->bio->linkedin ?? 0)
                <a 
                  title="Linkedin URL" 
                  target="_blank" 
                  href="{{ $mentor->bio->linkedin }}">
                  <img src="/icons/linkedin.svg" width="30" height="30" />
                </a>
                @else
                <!-- Please Change this SVG To color #eee -->
                <a 
                  title="Linkedin URL" 
                  target="_blank" 
                  href="#">
                  <img src="/icons/linkedin.svg" width="30" height="30" />
                </a>
              @endif
            </div>
            <!-- Mentor Experience -->
            <div class="mb-3">
              <p class="fs-6 fw-bold text-black mb-2">{{ __('admin.Expertises') }}</p>
              <div class="d-flex flex-wrap" style="gap: 5px;">
                @php
                  if ($mentor) {
            $expertises = $mentor->mentorExpertises; // جلب جميع الخبرات المرتبطة بهذا الموجه
        }else {
            $expertises = [];
        }
                @endphp
                @if($mentor->mentorExpertises->isNotEmpty())
                @foreach ($expertises as $expert)
                <span class="btn btn-xs btn-outline-warning rounded-pill">{{ $expert->title }}</span>
                @endforeach
                @else
                <span class="btn btn-xs btn-outline-warning rounded-pill">{{ __('cycles.NA') }}</span>
                @endif
              </div>
            </div>
            <!-- / Mentor Experience -->
            <!-- Mentor Programs & Rating -->
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="bg-white custom-shadow p-2 border" style="border-radius: 15px;">
                <div class="d-flex flex-column justify-content-center align-items-center">
                  <small class="text-info fw-bold">{{ __('cycles.ProgramCount') }}</small>
                  <span class="text-black fw-bold fs-6">{{ $m_count }}</span>
                </div>
              </div>
              @php
                $rateMentorAVG = \App\MentorshipRequest::where('mentor_id', $mentor->id)
    ->where(function ($query) {
        $query->where('approved', 'Yes')
              ->orWhereNull('approved');
    })->avg('rating');
              @endphp
              <div class="badge bg-label-light rounded rounded-pill">
                <div class="d-flex align-items-center justify-content-center">
                  
                  <span style="color: orange" class="fw-bold mt-1 mx-1">{{ number_format($rateMentorAVG, 2, '.', '') }}</span>
                  <img src="/icons/star.svg" width="15" height="15" />
                </div>
              </div>
            </div>
            <!-- / Mentor Programs & Rating -->
            <!-- Mentor Bio -->
            @if($mentor->bio)
              <div class="mb-2 mt-auto">
                <p class="fs-6 fw-bold text-black mb-1">{{ __('dashboard.Bio') }}</p>
                <small class="text-muted line-clamp-4">
                  {{ $mentor->bio->{'bio_' . $l} }}
                </small>
              </div>
              @else
              <div class="mb-2 mt-auto">
                <p class="fs-6 fw-bold text-black mb-1">{{ __('dashboard.Bio') }}</p>
                <small class="text-muted line-clamp-4">
                  {{ __('cycles.NA') }}
                </small>
              </div>
            @endif
            <!-- / Mentor Bio -->
            @if ($getid->user ?? 0)
              <div class="row px-4 mt-auto">
                <button
                  type="button"
                  class="btn btn-sm btn-success rounded-pill custom-shadow"
                  data-bs-toggle="modal"
                  data-bs-target="#SendRequest"
                  title="Add new request" 
                  onclick="request({{ $mentor->id}},'{{optional($getid->user)->{'first_name_'.$l} }} {{optional($getid->user)->{'last_name_'.$l} }}', {{ $getid->cycle->organization_id }},{{ $getid->cycle_id }})"
                >
                {{ __('dashboard.Request') }} <i class="ti ti-square-plus mx-3"></i>
                </button>
              </div>
            @endif
          </div>
        </div>
        <!-- Mentor Card -->
      @endforeach
      <!-- / Mentors List -->
    </div>
  </section>

  <!--  Edit SP Modal -->
  <div class="modal fade" id="SendRequest" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">
            {{ __('dashboard.Add mentorship session with') }}
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="post" action="/dashboard/mentorship/addwait" enctype="multipart/form-data"
            autocomplete="off">
            <div class="modal-body">
              @csrf
              <input type="hidden" name="mentorid" value="0" id="mentorid">
              <input type="hidden" name="organization_id" id="addOrganizationID">
              <input type="hidden" name="cycle_id" id="addCycleID">
              <div class="border-bottom mb-4 pb-2">
                <h5 class="modal-title">{{ __('dashboard.Mentor schedule') }}</h5>
              </div>
              <div class="form-group mb-3" id="showdates">
                <label class="fs-6 mb-2">{{ __('dashboard.Start meeting Date/ Time') }}:</label>
                <div class="position-relative">
                  <input id="date" name="date" type="text" class="date width-250 form-control pr-5"
                    placeholder="{{ __('dashboard.Start meeting Date/ Time') }}" value="" required
                    onkeydown="return false" />
                  <i class="ti ti-calendar position-absolute"
                    style="{{ $l == 'ar' ? 'left: 10px;' : 'right: 10px;' }} top: 50%; transform: translateY(-50%);">
                  </i>
                </div>
              </div>
              <div class="form-group mb-3">
                <label class="fs-6 mb-2">{{ __('dashboard.Meeting duration') }}:</label>
                <select name="period" class="select form-control width-150">
                  <option value="30">30 {{ __('dashboard.Minutes') }}</option>
                  <option value="60" selected>1 {{ __('dashboard.Hour') }}</option>
                  <option value="120">2 {{ __('dashboard.Hours') }}</option>
                  <option value="180">3 {{ __('dashboard.Hours') }}</option>
                  <option value="240">4 {{ __('dashboard.Hours') }}</option>
                  <option value="300">5 {{ __('dashboard.Hours') }}</option>
                </select>
              </div>
              <div class="form-group mb-3">
                <label class="fs-6 mb-2" for="basicInputFile">{{ __('dashboard.Send file with your request') }}</label>
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="inputGroupFile01" name="file_url"
                    accept="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf, image/*">
                  {{-- <label class="custom-file-label" for="inputGroupFile01">{{ __('dashboard.Choose file') }}</label> --}}
                </div>
              </div>
              <label class="fs-6 mb-2">{{ __('dashboard.Add note') }}:</label>
              <div class="form-group mb-3">
                <textarea class="form-control" id="notes" name='notes' rows="4"
                  placeholder="{{ __('dashboard.Write your note here') }}"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary ml-1">
                {{ __('dashboard.Submit') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- / Edit SP Modal -->
@stop

@section('scripts')
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
  {{-- <script src="/vuexy/src/js/scripts/extensions/ext-component-ratings.js"></script> --}}
@stop
  
@section('pagescripts')
  <!-- Page JS -->
  <script src="/vuexy/assets/js/pages-account-settings-account.js"></script>

  <script>
    const sortContainer = document.getElementById('sortContainer');
    const sortContainerMobile = document.getElementById('sortContainerMobile');
    const filterButton = document.getElementById('filterButton');

    const sortLabel = `<span class="mx-2 fw-bold">Sorting:</span>`;

    const sortContent = `
      <div class="row d-flex align-items-center" style="gap: 10px;">
        <div class="col-md-3 d-flex flex-nowrap align-items-center">
          <span class="fw-bold mx-1 text-black d-none d-md-flex" style="font-size:13px;">Rate</span>
          <button class="btn btn-sm btn-label-primary rounded-pill"
            style="height:25px; min-width:95px; font-size:13px;">
            <i class="ti ti-star me-2 text-warning"></i>
            <span class="fw-bold text-primary">Default</span>
          </button>
        </div>
      </div>
      <div class="row d-flex align-items-center" style="gap: 10px;">
        <div class="col-md-3 d-flex flex-nowrap align-items-center">
          <span class="fw-bold mx-1 text-black d-none d-md-flex" style="font-size:13px;">Linkedin</span>
          <button class="btn btn-sm btn-label-primary rounded-pill"
            style="height:25px; min-width:95px; font-size:13px;">
            <i class="ti-24px ti ti-brand-linkedin me-2 text-info"></i>
            <span class="fw-bold text-primary">Default</span>
          </button>
        </div>
      </div>
      <div class="row d-flex align-items-center" style="gap: 10px;">
        <div class="col-md-3 d-flex flex-nowrap align-items-center">
          <span class="fw-bold mx-1 text-black d-none d-md-flex text-nowrap" 
          style="font-size:13px;">Program Count</span>
          <button class="btn btn-sm btn-label-primary rounded-pill"
            style="height:25px; min-width:95px; font-size:13px;">
            <i class="ti-24px ti ti-dashboard me-2 text-danger"></i>
            <span class="fw-bold text-primary">Default</span>
          </button>
        </div>
      </div>
    `;

    let isToggled = false;

    filterButton.addEventListener('click', () => {
      console.log('Filter Button Clicked');
      isToggled = !isToggled; // Toggle the state

      if (isToggled) {
        sortContainer.innerHTML = sortLabel + sortContent; // Set the content
        sortContainerMobile.innerHTML = sortContent; // Set the content
      } else {
        sortContainer.innerHTML = ''; // Clear the container
        sortContainerMobile.innerHTML = ''; // Clear the container
      }
    });
  </script>

  <script>
    $(document).ready(function() {
      $('#dfrom, #dto').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
      });
    });
  </script>

  <script>
    $(document).ready(function() {
        $("#mentorstable").DataTable({
            "responsive": true,
            "searching": true,
            "lengthChange": false,
            "paging": false,
            "bInfo": false,
            "columnDefs": [{"orderable": false, "targets": [3]}],
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

        $(".sessonsTable").DataTable({
            "responsive": true,
            "searching": true,
            "lengthChange": false,
            "paging": false,
            "bInfo": false,
            "columnDefs": [{"orderable": false, "targets": [3]}],
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
            } @endif
            });

            $(" #date").datetimepicker({ format: 'Y-m-d H:i' , onGenerate: function(ct) {
    jQuery(this).find('.xdsoft_date').toggleClass('xdsoft_disabled'); },
    maxDate: '{{ date('Y/m/d', strtotime('-1 days')) }}' , timepicker: true }); function request(id, mentor,
    organization_id=null, cycle_id=null) { const modal=$('#SendRequest'); modal.style.display="block" ;
    $('#mentorid').val(id); $('#rname').html(mentor); $.ajaxSetup({ headers: { 'X-CSRF-TOKEN' : $('meta[name="csrf-token"
    ]').attr('content') } }); $.ajax({ type: "POST" , url: "/dashboard/mentors/modify" , data: { "id" : id, "rtype" : 5 },
    success: function(response) { $('#SendRequest li').remove(); $('#name').html(name); if (response.length>
    0) {
      $.each(response, function(i, item) {
        let note = '';
        let time = item.tfrom + " {{ __('dashboard.to') }} " + item.tto;
        if (item.notes) {
          note = item.notes;
        }
        switch (item.type) {
          case 'OneDay':
            $('<li>').html("<b>" + item.dfrom + "</b> (" + time + ").").appendTo('#schedule');
            break;
          case 'Weekly':
            $('<li>').html("<b> {{ __('dashboard.Weekly') }} </b>(" + item.days + ") - (" + time + ").").appendTo(
              '#schedule');
            break;
          case 'WorkingDays':
            $('<li>').html("<b>{{ __('dashboard.All working days') }} </b>(" + time + ").").appendTo('#schedule');
            break;
          case 'Period':
            $('<li>').html("<b> {{ __('dashboard.from') }} " + item.dfrom + " {{ __('dashboard.to') }} " + item
              .dto + " </b> (" + time + ").").appendTo('#schedule');
            break;
          default:
        }
      });
    } else {
      $('<li>').html("{{ __('dashboard.No records saved') }}.</li>").appendTo('#schedule');
    }
    $('#addOrganizationID').val(organization_id);
    $('#addCycleID').val(cycle_id);
    $('#SendRequest').modal('show');
    },
    error: function(error) {
    console.log(error);
    alert("Error Occurred, Try again!");
    }
    });
    }
    });
  </script>
  <script>
    function modify(type, id, stat) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      if (type === 3) {
        //delete
        if (confirm("{{ __('dashboard.sure del schedule') }}")) {
          $.ajax({
            type: "POST",
            url: "/dashboard/mentors/modify",
            data: {
              "id": id,
              "rtype": type,
            },
            success: function(response) {
              location.reload();
            },
            error: function(error) {
              console.log(error);
              alert("Error Occurred, Try again!");
            }
          });
        }
      } else if (type === 2) {
        //Enable-disable
        $.ajax({
          type: "POST",
          url: "/dashboard/mentors/modify",
          data: {
            "id": id,
            "rtype": type,
            "stat": stat
          },
          success: function(response) {
            location.reload();
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      } else if (type === 1) {
        // Edit current
        $.ajax({
          type: "POST",
          url: "/dashboard/mentors/modify",
          data: {
            "id": id,
            "rtype": type,
          },
          success: function(response) {
            switchtypes(response.type);
            $('#sid').val(response.id);
            $('#rtype').val(4);
            $('#type').val(response.type);
            $('#days').val(response.days);
            $('#dfrom').val(response.dfrom);
            $('#dto').val(response.dto);
            $('#tfrom').val(response.tfrom);
            $('#tto').val(response.tto);
            $('#notes').val(response.notes);
            $('#SendRequest').modal('show');
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      } else {
        //show from as new
        $('#sid, #type, #days, #dfrom, #dto, #tfrom, #tto, #notes').val();
        $('#rtype').val('0');
        switchtypes(type);
        $('#SendRequest').modal('show');
      }
    }

    function SelectType(type) {
      switchtypes(type);
    }

    function switchtypes(type) {
      switch (type) {
        case 'OneDay':
          $('#showdates').show();
          $('#showtto, #showdays').hide();
          $("#dfrom").prop('required', true);
          $("#days, #dto").prop('required', false);
          break;
        case 'Weekly':
          $('#showdates').hide();
          $('#showdays').show();
          $("#days").prop('required', true);
          $("#dfrom, #dto").prop('required', false);
          break;
        case 'WorkingDays':
          $('#showdates, #showdays').hide();
          $("#dfrom, #dto, #days").prop('required', false);
          break;
        case 'Period':
          $('#showtto,#showdates').show();
          $('#showdays').hide();
          $("#dfrom, #dto").prop('required', true);
          $("#days").prop('required', false);
          break;
      }
    }

    $('.start_meeting').click(function() {
      $.ajax({
        type: "GET",
        url: "/dashboard/mentor/meetingRedirect/" + $(this).data('value'),
        success: function(response) {
          alert(response['message']);
          window.open(response['join_url'], '_blank');
          window.location.href = '{{ route('mentorshipTasks.index') }}' + '?mentorship_id=' + response[
            'mentorship_id'];
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    });
  </script>
@stop
