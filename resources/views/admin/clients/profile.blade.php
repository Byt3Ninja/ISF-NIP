@extends('layouts.admin')
@section('title', __('admin.Profile'))
@section('subTitle', __('admin.Edit your profile information'))

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-profile.css" />
@stop

@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
      <!-- Header -->
      <div class="row">
        <div class="col-12">
          <div class="card mb-6">
            <div class="user-profile-header-banner">
              <img src="/vuexy/assets/img/pages/profile-banner.png" alt="Banner image" class="rounded-top" />
            </div>
            <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-5">
              <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                <img src="{{ $client->image_url }}" alt="user image" class="d-block ms-0 ms-sm-6 rounded user-profile-img"
                  style="height: 130px" />
              </div>
              <div class="flex-grow-1 mt-3 mt-lg-5">
                <div
                  class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                  <div class="user-profile-info">
                    <h4 class="mb-2 mt-lg-6">
                      {{ 
                        $l == 'ar' ? $client->first_name_ar . ' ' . $client->last_name_ar : $client->first_name_en . ' ' . $client->last_name_en
                      }}
                      @role('Mentor')
                      <a target="_blank" href="{{ $client->bio->linkedin }}">
                        <i class="ti ti-brand-linkedin ti-lg"></i>
                        </a>
                        @endrole
                        @php
                        // جلب الأدوار كمجموعة
                        $roles = $client->getRoleNames();
                    
                        // ترجمة الأدوار وإزالة الأقواس والدبل كوت
                        $translatedRoles = $roles->map(function ($role) {
                            return __('admin.' . strtolower($role)); // ترجمة الدور
                        })->implode(', '); // دمج الأدوار في سلسلة واحدة
                    
                        // إزالة أي أقواس أو دبل كوت
                        $cleanedRoles = str_replace(['[', ']', '"'], '', $translatedRoles);
                    @endphp
                                            <br><h5 class="fw-medium"><i class="ti ti-user ti-lg"></i> {{ $cleanedRoles }} </h5>
                    </h4>
                    <ul
                      class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                      <li class="list-inline-item d-flex gap-2 align-items-center">
                        <i class="ti ti-palette ti-lg"></i><span class="fw-medium">
                          {{ $l == 'ar' ? $client->title_ar : $client->title_en }}</span>
                        </span>
                      </li>
                      <li class="list-inline-item d-flex gap-2 align-items-center">
                        <i class="ti ti-map-pin ti-lg"></i><span
                          class="fw-medium">
                          @if($client->city != null)
                          {{  $client->city->{'name_' . $l}  }}
                          @endif
                        </span>
                      </li>
                      <li class="list-inline-item d-flex gap-2 align-items-center">
                        <i class="ti ti-calendar ti-lg"></i><span class="fw-medium"> {{ __('admin.Joined') }} {{ $client->created_at->locale($l)->translatedFormat('M-Y') }}</span>
                      </li>
                      
                    </ul>
                  </div>
                  {{-- <a href="javascript:void(0)" class="btn btn-primary mb-1">
                    <i class="ti ti-user-check ti-xs me-2"></i>Connected
                  </a> --}}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ Header -->

      <!-- Navbar pills -->
      <div class="row">
        <div class="col-md-12">
          <div class="nav-align-top">
            <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0">
              <li class="nav-item">
                <a class="nav-link active" id="Profile-tab" data-bs-toggle="tab" href="#Profile" role="tab" aria-controls="Profile" aria-selected="false">
                  <i class="ti-sm ti ti-user-check me-1_5"></i>
                  {{__('admin.Profile')}}
                </a>
            </li>

              <li class="nav-item">
                <a class="nav-link" id="Programms-tab" data-bs-toggle="tab" href="#Programms" role="tab" aria-controls="Programms" aria-selected="false">
                  <i class="ti-sm ti ti-layout-grid me-1_5"></i>
                  {{__('admin.Programms')}}
                </a>
            </li>

             
             
            </ul>
          </div>
        </div>
      </div>
      <!-- Start Tabs Data -->
      <div class="tab-content" id="settingsTabsContent">
        <!-- general settings tab -->
        <div class="tab-pane fade show active" id="Profile" role="tabpanel" aria-labelledby="Profile-tab">
      <!--/ Navbar pills -->
      @role('Mentor')
      <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12">
          <div class="card mb-6">
            <div class="card-body">
              
              <small class="card-text text-uppercase text-muted small">
          {{__('admin.ProfileSummary')}}
              </small><br><br>
          {{ $client->bio->bio }}
          {{ 
            $l == 'ar' 
            ? $client->bio->bio_ar
            : $client->bio->bio_en 
            }}
           
        </div>
    
      </div>
      <!-- Count cards -->

 @if (auth()->user()->id == $client->id)
 <div class="col-lg-3 col-sm-6" style="display: inline-block">
 <a href="" class="col-lg-3 col-sm-6">
   <div class="card card-border-shadow-primary h-100">
     <div class="card-body">
       <div class="d-flex justify-content-center align-items-center mb-2">
         <div class="avatar me-4">
           <span class="avatar-initial rounded bg-label-primary">
             <i class="ti-brand-my-oppo text-primary ti-28px"></i>
           </span>
         </div>
           <h4 class="mb-0 text-center">{{ $mentrequests_count }}</h4>
       </div>
       <p class="mb-1 text-center">{{ __('admin.Mentorship requests') }}</p>
     </div>
   </div>
 </a>
</div>
<div class="col-lg-3 col-sm-6" style="display: inline-block">
 <a href="" class="col-lg-3 col-sm-6">
   <div class="card card-border-shadow-primary h-100">
     <div class="card-body">
       <div class="d-flex justify-content-center align-items-center mb-2">
         <div class="avatar me-4">
           <span class="avatar-initial rounded bg-label-primary">
             <i class="ti-calendar-event text-primary ti-28px"></i>
           </span>
         </div>
           <h4 class="mb-0 text-center">{{ $mentrequests_period }}</h4>
       </div>
       <p class="mb-1 text-center">{{ __('admin.Mentorship requests Duration') }}</p>
     </div>
   </div>
 </a>
</div>
<div class="col-lg-3 col-sm-6" style="display: inline-block">
 <a href="" class="col-lg-3 col-sm-6">
   <div class="card card-border-shadow-primary h-100">
     <div class="card-body">
       <div class="d-flex justify-content-center align-items-center mb-2">
         <div class="avatar me-4">
           <span class="avatar-initial rounded bg-label-primary">
             <i class="ti-calendar-event text-primary ti-28px"></i>
           </span>
         </div>
           <h4 class="mb-0 text-center">{{ number_format($average_rating ?? 0, 1) }}</h4>
       </div>
       <p class="mb-1 text-center">{{ __('admin.Mentorship Avr Rating') }}</p>
     </div>
   </div>
 </a>
</div>
@endif
</div>
<!-- / Count cards -->
<br><br>
      @endrole

      
      <!-- User Profile Content -->
      <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-5">
          <!-- About User -->
          <div class="card mb-6">
            <div class="card-body">
              <small class="card-text text-uppercase text-muted small">
                {{ $l == 'ar' ? 'المعلومات الشخصية' : 'About' }}
              </small>
              <ul class="list-unstyled my-3 py-1">
                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-user ti-lg"></i><span class="fw-medium mx-2">{{ $l == 'ar' ? 'الاسم:' : 'Full Name:' }}</span>
                  <span>{{ 
                  $l == 'ar' 
                  ? $client->first_name_ar . ' ' . $client->last_name_ar 
                  : $client->first_name_en . ' ' . $client->last_name_en
                  }}</span>
                </li>
                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-check ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar' ? 'الحالة' : 'Status' }}:
                  </span>
                  <span>{{ $l == 'ar' ? 'نشط' : 'Active' }}</span>
                </li>
                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-crown ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar' ? 'المسمى الوظيفي' : 'Role:' }}:
                  </span>
                  <span>
                    {{ $l == 'ar' ? $client->title_ar : $client->title_en }}
                  </span>
                </li>
                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-flag ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar' ? 'الدولة:' : 'Country:' }}
                  </span>
                  <span>
                    {{ $client->country->{'name_' . $l} }}
                  </span>
                </li>
                <li class="d-flex align-items-center mb-2">
                  <i class="ti ti-language ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar' ? 'اللغات:' : 'Languages:' }}
                  </span>
                  <span>
                    {{ $l == 'ar' ? 'العربية, الإنجليزية' : 'Arabic, English' }}
                  </span>
                </li>
              </ul>
              <small class="card-text text-uppercase text-muted small">
                {{ $l == 'ar' ? 'التواصل' : 'Contact' }}
              </small>
              <ul class="list-unstyled my-3 py-1">
                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-phone-call ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar' ? 'الهاتف:' : 'Phone:' }}
                  </span>
                  <span>{{ $client->phone }}</span>
                </li>
                

                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-mail ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar' ? 'البريد الإلكتروني:' : 'Email:' }}
                  </span>
                  <span>{{ $client->email }}</span>
                </li>
              </ul>

              {{-- <small class="card-text text-uppercase text-muted small">{{__('admin.Orgnizations')}}</small>
              <ul class="list-unstyled mb-0 mt-3 pt-1">
                @foreach ($mentors as $cycleOrg) 
                @php
                  $Organizations = \App\Organization::where('id', $cycleOrg->cycle->organization_id ?? 0)->get();
                  @endphp
                @foreach ($Organizations as $org) 
                <li class="d-flex flex-wrap mb-4">

                  - <span class="fw-medium me-2">{{ $org->{'name_' . $l} }}</span>

                </li>
                @endforeach
                @endforeach
              </ul> --}}
            </div>
          </div>
          <!--/ About User -->
          <!-- Profile Overview -->
          {{-- <div class="card mb-6">
            <div class="card-body">
              <small class="card-text text-uppercase text-muted small">Overview</small>
              <ul class="list-unstyled mb-0 mt-3 pt-1">
                <li class="d-flex align-items-end mb-4">
                  <i class="ti ti-check ti-lg"></i><span class="fw-medium mx-2">Task Compiled:</span>
                  <span>13.5k</span>
                </li>
                <li class="d-flex align-items-end mb-4">
                  <i class="ti ti-layout-grid ti-lg"></i><span class="fw-medium mx-2">Projects Compiled:</span>
                  <span>146</span>
                </li>
                <li class="d-flex align-items-end">
                  <i class="ti ti-users ti-lg"></i><span class="fw-medium mx-2">Connections:</span>
                  <span>897</span>
                </li>
              </ul>
            </div>
          </div> --}}
          <!--/ Profile Overview -->
        </div>

        <div class="col-xl-8 col-lg-7 col-md-7">
          
          <!-- Projects table -->
          <div class="card mb-4">
            <div class="card-datatable table-responsive">
              <table class="zero-configuration table border-top">
                <thead>
                  <tr>
                    <th class="text-center">{{__('cycles.User/Title')}}</th>
                                                <th class="text-center">{{__('cycles.Company')}}</th>
                                                <th class="text-center">{{__('mentor.mentorshipTasks.title')}}</th>
                                                <th class="text-center">{{__('cycles.Done')}}</th>
                  </tr>

                  
                  <tbody>
                    @foreach($mentorshipTasks as $task)
                                                <tr @if(is_null($task->title)) class="btn-light-warning" @endif id="row_{{ $task->id }}">
                                                    <td class="text-center">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <a class="media-left mr-50" href="{{ route('show_mentor_profile', $task->user_id) }}">
                                                                <img src="{{$task->user->image_url}}" alt="avatar" class="rounded-circle" height="40" width="40">
                                                            </a>
                                                            <a href="{{ route('show_mentor_profile', $task->user_id) }}">
                                                                <div class="media-body">
                                                                    <h6 class="media-heading mb-0">{{$task->user->name }} </h6>
                                                                    <span class="font-small-2">{{$task->user->title}}</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="text-center">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <a class="media-left mr-50" href="/dashboard/companyProfile/{{$task->company_id}}">
                                                                <img src="{{$task->company->logo_url}}" alt="avatar" class="rounded-circle" height="40" width="40">
                                                            </a>
                                                            <a href="/dashboard/companyProfile/{{$task->company->id}}">
                                                                <div class="d-flex align-items-center gap-1">
                                                                    {{$task->company->name }}
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="text-center">
                                                        {{ $task->title??"-----" }}
                                                    </td>
                                                    <td class="text-center">
                                                     {{ $task->status =='pending'?__('Uncompleted'):__('Completed') }}
                                                    </td>
                                                    
                                                </tr>
                                            @endforeach
                  </tbody>


                </thead>
              </table>
            </div>
          </div>
          <!--/ Projects table -->
        </div>
      </div>
      <!--/ User Profile Content -->
    </div>
  </div>

<!-- Programms tab -->
<div class="tab-pane fade" id="Programms" role="tabpanel" aria-labelledby="Programms-tab">

  <div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12">
      <div class="card mb-6">
        <div class="card-body">
          
          <small class="card-text text-uppercase text-muted small">
      {{__('admin.Programms')}}
          </small>
          <br><br>
          
           
          <table class="table zero-configuration">
            <thead>
            <tr>
                <th>{{__('admin.Title')}}</th>
                <th>{{__('admin.Description')}}</th>
                <th>{{__('admin.Start')}}</th>
                <th>{{__('admin.End')}}</th>
            </tr>
            </thead>
            <tbody>
              @if (!@empty(Auth::user()->company->cycledata->title))
              <tr>
                <td>{{ Auth::user()->company->cycledata->title }}</td>
                <td>{{ Auth::user()->company->cycledata->description }}</td>
                <td>{{ Auth::user()->company->cycledata->start }}</td>
                <td>{{ Auth::user()->company->cycledata->end }}</td>
              </tr>
              @else
              @foreach ($mentorshipTasks as $item)
              <tr>
                <td>{{ $item->title }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->start }}</td>
                <td>{{ $item->end }}</td>
              </tr>
              @endforeach
              @endif
        </tbody>
      </table>


    </div>
  
  </div>
  

</div>
</div>


<!-- Programms tab -->



    </div>
  </div>
    <!-- / Content -->
 
    <div class="content-backdrop fade"></div>
  
  </div>
  <!-- Content wrapper -->
@stop

<!-- Page JS -->
@section('pagescripts')
  <script src="/vuexy/assets/js/pages-profile.js"></script>

  <script>
    $(document).ready(function() {

      $('.zero-configuration').DataTable({
                    "language": {
                        "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول",
                        "sLoadingRecords": "جارٍ التحميل...",
                        "sProcessing":   "جارٍ التحميل...",
                        "sLengthMenu":   "أظهر _MENU_ مدخلات",
                        "sZeroRecords":  "لم يعثر على أية سجلات",
                        "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                        "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل",
                        "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                        "sInfoPostFix":  "",
                        "sSearch":       "ابحث:",
                        "sUrl":          "",
                        "oPaginate": {
                            "sFirst":    "الأول",
                            "sPrevious": "السابق",
                            "sNext":     "التالي",
                            "sLast":     "الأخير"
                        },
                        "oAria": {
                            "sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً",
                            "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
                        }
                    }
                } );



      getStates($('#country_id').val());
      $('#country_id').change(function() {
        getStates($(this).val());
      });
      $('#state_id').change(function() {
        getCities($(this).val());
      });
    });

    function getStates(country_id) {
      var $state = $('#state_id');
      var currentState = '{{ $client->state_id }}';
      var selected = '';
      $.ajax({
        url: "{{ route('get_states_by_country') }}",
        data: {
          country_id: country_id
        },
        success: function(data) {
          $state.html('<option value="" >{{ __('admin.State') }}</option>');
          $.each(data, function(id, value) {
            // selected = '';
            if (currentState === id) {
              selected = 'selected';
              getCities(id)
            } else {
              selected = '';
            }
            $state.append('<option value="' + id + '" ' + selected + '>' + value + '</option>');
            $('#city_id').html('<option value="" >{{ __('admin.City') }}</option>');
          });
          $('#state').show(150);
        }
      });
    }

    function getCities(state_id) {
      var $city = $('#city_id');
      var currentCity = '{{ $client->city_id }}';
      var selected = '';
      $.ajax({
        url: "{{ route('cities.index') }}",
        data: {
          state_id: state_id
        },
        success: function(data) {
          $city.html('<option value="" >{{ __('admin.City') }}</option>');
          $.each(data, function(id, value) {
            if (currentCity == id) {
              selected = 'selected';
            } else {
              selected = '';
            }
            $city.append('<option value="' + id + '" ' + selected + ' >' + value + '</option>');
          });
          $('#city').show(150);
        }
      });
    }
  </script>
  @role('Mentor')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      var useDarkMode = window.matchMedia('(prefers-color-scheme: light)').matches;

      tinymce.init({
        selector: '#basicTextarea',
        plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount textpattern noneditable help charmap quickbars emoticons',
        imagetools_cors_hosts: ['picsum.photos'],
        menubar: 'edit view insert format tools table help',
        toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview  print | insertfile image media template link anchor codesample | ltr rtl',
        toolbar_sticky: true,
        autosave_ask_before_unload: true,
        autosave_interval: '30s',
        autosave_prefix: '{path}{query}-{id}-',
        autosave_restore_when_empty: false,
        autosave_retention: '2m',
        image_advtab: true,
        importcss_append: true,
        template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
        template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
        height: 400,
        image_caption: true,
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
        noneditable_noneditable_class: 'mceNonEditable',
        toolbar_mode: 'sliding',
        contextmenu: 'link image imagetools table',
        skin: useDarkMode ? 'oxide-dark' : 'oxide',
        content_css: useDarkMode ? 'dark' : 'default',
        content_style: 'body { font-family:Times New Roman,Arial,sans-serif; font-size:14px }',
        image_title: true,
        automatic_uploads: true,
        media_live_embeds: true,
        relative_urls: false,
        remove_script_host: false,
        images_upload_url: "{{ route('training.upload', ['_token' => csrf_token()]) }}",
        file_picker_types: 'image',
        convert_urls: true,
        file_picker_callback: function(cb, value, meta) {
          var input = document.createElement('input');
          input.setAttribute('type', 'file');
          input.setAttribute('accept', 'image/*');
          input.onchange = function() {
            var file = this.files[0];
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function() {
              var id = 'blobid' + (new Date()).getTime();
              var blobCache = tinymce.activeEditor.editorUpload.blobCache;
              var base64 = reader.result.split(',')[1];
              var blobInfo = blobCache.create(id, file, base64);
              blobCache.add(blobInfo);
              cb(blobInfo.blobUri(), {
                title: file.name
              });
            };
          };
          input.click();
        }
      });
      @if ($l == 'ar')
        tinyMCE.settings['directionality'] = 'rtl';
      @endif
    </script>
    <script>
      $(document).ready(function() {
        @if ($client->bio)
          switch ('{{ $client->bio->entrepreneurial_experience }}') {
            case '1':
              $('#entrepreneurial_experience_brief').show();
              break;
            case '0':
              $('#entrepreneurial_experience_brief').hide();
              break;
            default:
              $('#entrepreneurial_experience_brief').hide();
              break;
          }
        @endif

        $('.entrepreneurial_experience').change(function() {
          var value = $(this).val();
          switch (value) {
            case '1':
              $('#entrepreneurial_experience_brief').show();
              break;
            case '0':
              $('#entrepreneurial_experience_brief').hide();
              break;
            default:
              $('#entrepreneurial_experience_brief').hide();
              break;
          }
        });
      });
    </script>
  @endrole
@stop
