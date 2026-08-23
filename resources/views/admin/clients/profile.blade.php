@extends('layouts.admin')
@section('title', __('admin.Profile'))
@section('subTitle', __('admin.Edit your profile information'))

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-profile.css" />
@stop

@section('content')
  <div class="">
    <div class="">

      @if (session()->has('msg'))
        <x-alert
          type="{{ session()->get('class', 'success') }}"
          message="{{ __('admin.' . session()->get('msg'), ['email' => session()->get('pending_email', '')]) }}" />
      @endif

      {{-- Profile Header Section --}}
      <section class="row">
        <div class="col-12">
          <article class="card mb-6">
            <header class="user-profile-header-banner">
              <img 
                src="/vuexy/assets/img/pages/profile-banner.png" 
                alt="Banner image" 
                class="rounded-top" 
              />
            </header>
            
            <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-5">
              <figure class="flex-shrink-0 mx-sm-0 mx-auto">
                <img 
                  src="{{ $client->image_url ?: '/images/user-placeholder.jpeg' }}"
                  alt="{{ $client->first_name_en }} {{ $client->last_name_en }}" 
                  class="d-block ms-0 ms-sm-6 rounded user-profile-img object-fit-cover" 
                  style="height: 130px"
                  onerror="this.onerror=null; this.src='/images/user-placeholder.jpeg';" 
                />
              </figure>
              
              <div class="flex-grow-1 mt-3 mt-lg-5">
                <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                  <div class="user-profile-info">
                    <h1 class="mb-2 mt-lg-6 h4">
                      {{ $l == 'ar' ? $client->first_name_ar . ' ' . $client->last_name_ar : $client->first_name_en . ' ' . $client->last_name_en }}
                      
                      @role('Mentor')
                        <a target="_blank" 
                           href="{{ $client->bio->linkedin ?? '#' }}" 
                           class="ms-3"
                           aria-label="LinkedIn Profile">
                          <img src="/icons/linkedin.svg" width="24" height="24" alt="LinkedIn" />
                        </a>
                      @endrole
                    </h1>
                    
                    @php
                      $roles = $client->getRoleNames();
                      $translatedRoles = $roles->map(fn($role) => __('admin.' . strtolower($role)))->implode(', ');
                      if(!empty($client->bio->industries)) {
                        $industery = $client->bio->industry($client->bio->industries);
                      }else{
                        $industery = [];
                      }
                    @endphp
                    
                    <h2 class="fw-medium h5">
                      <i class="ti ti-user ti-lg"></i> {{ $translatedRoles }}
                    </h2>
                    
                    <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                      @if($client->title_en)
                        <li class="list-inline-item d-flex gap-2 align-items-center">
                          <i class="ti ti-palette ti-lg"></i>
                          <span class="fw-medium">{{ $client->title_en }}</span>
                        </li>
                      @endif
                      
                      @if($client->city)
                        <li class="list-inline-item d-flex gap-2 align-items-center">
                          <i class="ti ti-map-pin ti-lg"></i>
                          <span class="fw-medium">{{ $client->city->{'name_' . $l} }}</span>
                        </li>
                      @endif
                      
                      <li class="list-inline-item d-flex gap-2 align-items-center">
                        <i class="ti ti-calendar ti-lg"></i>
                        <span class="fw-medium">
                          {{ __('admin.Joined') }} {{ $client->created_at->locale($l)->translatedFormat('M-Y') }}
                        </span>
                      </li>
                    </ul>
                  </div>
                  
                  @if($_user->ableTo('user.profile_view', $permissions) && $_user->id == $client->id)
                    <a href="/dashboard/myProfile" class="btn btn-primary mb-1">
                      <i class="ti ti-settings me-3 ti-md"></i>
                      <span class="align-middle">{{ $l == 'ar' ? 'تعديل' : 'Edit' }}</span>
                    </a>
                  @endif
                  @if( $_user->ableTo('users.edit', $permissions) && $_user->id != $client->id )
                    <a href="{{ route('users.edit', $client->id) }}" class="btn btn-primary mb-1">
                      <i class="ti ti-settings me-3 ti-md"></i>
                      <span class="align-middle">{{ $l == 'ar' ? 'تعديل' : 'Edit' }}</span>
                    </a>
                  @endif
                </div>
              </div>
            </div>
          </article>
        </div>
      </section>

      {{-- Navigation Tabs --}}
      <nav class="row">
        <div class="col-md-12">
          <div class="nav-align-top">
            <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" 
                   id="Profile-tab" 
                   data-bs-toggle="tab" 
                   href="#Profile" 
                   role="tab"
                   aria-controls="Profile" 
                   aria-selected="true">
                  <i class="ti-sm ti ti-user-check me-1_5"></i>
                  {{ __('admin.Profile') }}
                </a>
              </li>
              
              @if($_user->hasAnyRole(['Mentor', 'Registered']))
                <li class="nav-item">
                  <a class="nav-link" 
                     id="Programms-tab" 
                     data-bs-toggle="tab" 
                     href="#Programms" 
                     role="tab"
                     aria-controls="Programms" 
                     aria-selected="false">
                    <i class="ti-sm ti ti-layout-grid me-1_5"></i>
                    {{ __('admin.Programms') }}
                  </a>
                </li>
              @endif
            </ul>
          </div>
        </div>
      </nav>

      {{-- Tab Content --}}
      <div class="tab-content p-0" id="settingsTabsContent">
        
        {{-- Profile Tab --}}
        <div class="tab-pane fade show active" id="Profile" role="tabpanel" aria-labelledby="Profile-tab">
          
          @role('Mentor')
            {{-- Profile Summary --}}
            <section class="row">
              <div class="col-12">
                <article class="card mb-6">
                  <div class="card-body">
                    <h3 class="h5 cart-title">{{ __('admin.ProfileSummary') }}</h3>
                    <p>
                      {{ $l == 'ar' 
                          ? optional($client->bio)->bio_ar ?? __('admin.No bio has been added yet') 
                          : optional($client->bio)->bio_en ?? __('admin.No bio has been added yet') }}
                    </p>
                  </div>
                </article>

                {{-- Statistics Cards --}}
                @if(auth()->user()->id == $client->id)
                  <div class="row mb-6">
                    <div class="col-6 col-md-4">
                      <article class="card card-border-shadow-primary h-100">
                        <div class="card-body">
                          <div class="d-flex justify-content-center align-items-center mb-2">
                            <div class="avatar me-4">
                              <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-brand-my-oppo text-primary ti-28px"></i>
                              </span>
                            </div>
                            <p class="mb-0 text-center h4">{{ $mentrequests_count }}</p>
                          </div>
                          <p class="mb-1 text-center">{{ __('admin.Mentorship requests') }}</p>
                        </div>
                      </article>
                    </div>

                    <div class="col-6 col-md-4">
                      <article class="card card-border-shadow-primary h-100">
                        <div class="card-body">
                          <div class="d-flex justify-content-center align-items-center mb-2">
                            <div class="avatar me-4">
                              <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-calendar-clock text-primary ti-28px"></i>
                              </span>
                            </div>
                            <p class="mb-0 text-center h4">{{ $mentrequests_period }}</p>
                          </div>
                          <p class="mb-1 text-center">{{ __('admin.Mentorship requests Duration') }}</p>
                        </div>
                      </article>
                    </div>

                    <div class="col-6 col-md-4">
                      <article class="card card-border-shadow-primary h-100">
                        <div class="card-body">
                          <div class="d-flex justify-content-center align-items-center mb-2">
                            <div class="avatar me-4">
                              <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-calendar-event text-primary ti-28px"></i>
                              </span>
                            </div>
                            <p class="mb-0 text-center h4">{{ number_format($average_rating ?? 0, 1) }}</p>
                          </div>
                          <p class="mb-1 text-center">{{ __('admin.Mentorship Avr Rating') }}</p>
                        </div>
                      </article>
                    </div>
                  </div>
                @endif
              </div>
            </section>
          @endrole

          {{-- User Information Section --}}
          <section class="row">
            <aside class="col-xl-4 col-lg-5 col-md-5">
              {{-- About User Card --}}
              <article class="card mb-6">
                <div class="card-body">
                  <h3 class="card-text text-uppercase text-muted small">
                    {{ $l == 'ar' ? 'المعلومات الشخصية' : 'About' }}
                  </h3>
                  
                  <ul class="list-unstyled my-3 py-1">
                    <li class="d-flex align-items-center mb-4">
                      <i class="ti ti-user ti-lg"></i>
                      <strong class="mx-2 text-nowrap">{{ $l == 'ar' ? 'الاسم:' : 'Full Name:' }}</strong>
                      <span>
                        {{ $l == 'ar' 
                            ? $client->first_name_ar . ' ' . $client->last_name_ar 
                            : $client->first_name_en . ' ' . $client->last_name_en }}
                      </span>
                    </li>
                    
                    <li class="d-flex align-items-center mb-4">
                      <i class="ti ti-check ti-lg"></i>
                      <strong class="mx-2 text-nowrap">{{ $l == 'ar' ? 'الحالة' : 'Status' }}:</strong>
                      <span>{{ $l == 'ar' ? 'نشط' : 'Active' }}</span>
                    </li>
                    
                    @if($client->title_en)
                      <li class="d-flex align-items-center mb-4">
                        <i class="ti ti-crown ti-lg"></i>
                        <strong class="mx-2 text-nowrap">
                          {{ $l == 'ar' ? 'المسمى الوظيفي' : 'Role' }}:
                        </strong>
                        <span class="text-nowrap">{{ $client->title_en }}</span>
                      </li>
                    @endif
                    
                    <li class="d-flex align-items-center mb-4">
                      <i class="ti ti-flag ti-lg"></i>
                      <strong class="mx-2 text-nowrap">{{ $l == 'ar' ? 'الدولة:' : 'Country:' }}</strong>
                      <span>
                        {{ $client->country_id ? $client->country?->{'name_' . $l} : __('admin.Not_Available') }}
                      </span>
                    </li>
                    
                    <li class="d-flex align-items-center mb-2">
                      <i class="ti ti-language ti-lg"></i>
                      <strong class="mx-2 text-nowrap">{{ $l == 'ar' ? 'اللغات:' : 'Languages:' }}</strong>
                      <span>
                        @if($client->language === 'ar')
                          {{ $l == 'ar' ? 'العربية' : 'Arabic' }}
                        @elseif($client->language === 'en')
                          {{ $l == 'ar' ? 'الإنجليزية' : 'English' }}
                        @else
                          {{ __('admin.Not_Available') }}
                        @endif
                      </span>
                    </li>
                  </ul>
                  
                  <h3 class="card-text text-uppercase text-muted small mt-4">
                    {{ $l == 'ar' ? 'التواصل' : 'Contact' }}
                  </h3>
                  
                  <ul class="list-unstyled my-3 py-1">
                    <li class="d-flex align-items-center mb-4">
                      <i class="ti ti-phone-call ti-lg"></i>
                      <strong class="mx-2 text-nowrap">{{ $l == 'ar' ? 'الهاتف:' : 'Phone:' }}</strong>
                      <span>{{ $client->phone }}</span>
                    </li>
                    
                    <li class="d-flex align-items-center mb-4">
                      <i class="ti ti-mail ti-lg"></i>
                      <strong class="mx-2 text-nowrap">{{ $l == 'ar' ? 'الايميل:' : 'Email:' }}</strong>
                      <span class="text-truncate">{{ $client->email }}</span>
                    </li>

                    @if ($client->new_email)
                      <li class="d-flex align-items-start mb-4">
                        <i class="ti ti-mail-forward ti-lg"></i>
                        <span class="mx-2">
                          <span class="badge bg-label-warning">{{ __('admin.PendingEmailChange') }}</span>
                          <span class="d-block small text-muted mt-1">
                            {{ __('admin.PendingEmailChangeNotice', ['email' => $client->new_email]) }}
                          </span>
                        </span>
                      </li>
                    @endif
                    
                    <li class="d-flex align-items-center mb-4">
                      <i class="ti ti-briefcase ti-lg"></i>
                      <strong class="mx-2 text-nowrap">{{ $l == 'ar' ? 'الصناعة:' : 'Industry:' }}</strong>
                      <span>
                        @foreach ($industery as $industry)
                    {{ $industry->{'name_' . $l} }}
                    @endforeach
                  </span>
                    </li>

                  </ul>
                </div>
              </article>
            </aside>

            <main class="col-xl-8 col-lg-7 col-md-7">
              {{-- Mentorship Tasks Table --}}
              <article class="card mb-4">
                <div class="card-datatable table-responsive">
                  <table class="zero-configuration table border-top">
                    <thead>
                      <tr>
                        <th class="text-center">{{ __('cycles.User/Title') }}</th>
                        <th class="text-center">{{ __('cycles.Company') }}</th>
                        <th class="text-center">{{ __('mentor.mentorshipTasks.title') }}</th>
                        <th class="text-center">{{ __('cycles.Done') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($mentorshipTasks as $task)
                        <tr @class(['btn-light-warning' => is_null($task->title)]) id="row_{{ $task->id }}">
                          <td>
                            <a
                              href="{{ $_user->ableTo('mentors.profile_view', $permissions) ? route('mentors.profile_view', $task->user_id) : 'javascript:void(0)' }}"
                              class="d-flex align-items-center gap-2"
                            >
                              <img src="{{ $task->user->image_url }}" 
                                    alt="{{ $task->user->name }}" 
                                    class="rounded-circle" 
                                    height="40" 
                                    width="40">
                              <div>
                                <h6 class="mb-0">{{ $task->user->name }}</h6>
                                <small>{{ $task->user->title }}</small>
                              </div>
                            </a>
                          </td>
                          
                          <td class="text-center">
                            <a 
                              href="{{ $_user->ableTo('company.profile_view', $permissions) ? "/dashboard/companyProfile/$task->company_id" : 'javascript:void(0)' }}"
                              class="d-flex align-items-center gap-2"
                            >
                              <img src="{{ $task->company->logo_url }}" 
                                    alt="{{ $task->company->name }}" 
                                    class="rounded-circle" 
                                    height="40" 
                                    width="40">
                              <span>{{ $task->company->name }}</span>
                            </a>
                          </td>
                          
                          <td class="text-center">{{ $task->title ?? '-----' }}</td>
                          <td class="text-center">
                            {{ $task->status == 'pending' ? __('Uncompleted') : __('Completed') }}
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </article>
            </main>
          </section>
        </div>

        {{-- Programs Tab --}}
        <div class="tab-pane fade" id="Programms" role="tabpanel" aria-labelledby="Programms-tab">
          <section class="row">
            <div class="col-xl-12">
              <article class="card mb-6">
                <div class="card-body">
                  <h3 class="card-text text-uppercase text-muted small">
                    {{ __('admin.Programms') }}
                  </h3>
                  
                  <div class="table-responsive mt-4">
                    <table class="table zero-configuration">
                      <thead>
                        <tr>
                          <th>{{ __('admin.Title') }}</th>
                          <th>{{ __('admin.Description') }}</th>
                          <th>{{ __('admin.Start') }}</th>
                          <th>{{ __('admin.End') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(!empty(Auth::user()->company->cycledata->title))
                          <tr>
                            <td>{{ Auth::user()->company->cycledata->title }}</td>
                            <td>{{ Auth::user()->company->cycledata->description }}</td>
                            <td>{{ Auth::user()->company->cycledata->start }}</td>
                            <td>{{ Auth::user()->company->cycledata->end }}</td>
                          </tr>
                        @else
                          @foreach($mentorshipTasks as $item)
                            <tr>
                              <td>{{ $item->title }}</td>
                              <td>{{ strip_tags($item->description) }}</td>
                              <td>{{ $item->start }}</td>
                              <td>{{ $item->end }}</td>
                            </tr>
                          @endforeach
                        @endif
                      </tbody>
                    </table>
                  </div>
                </div>
              </article>
            </div>
          </section>
        </div>
      </div>
    </div>

    <div class="content-backdrop fade"></div>
  </div>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/pages-profile.js"></script>

  <script>
    $(document).ready(function() {
      $('.zero-configuration').DataTable({
        language: {
          sEmptyTable: "{{ __('admin.No_data_available_in_table') }}",
          sLoadingRecords: "{{ __('admin.Loading') }}...",
          sProcessing: "{{ __('admin.Loading') }}...",
          sLengthMenu: "{{ __('admin.Show') }} _MENU_ {{ __('admin.Entries') }}",
          sZeroRecords: "{{ __('admin.No_matching_records_found') }}",
          sInfo: "{{ __('admin.Show') }} _START_ {{ __('admin.To') }} _END_ {{ __('admin.Of') }} _TOTAL_ {{ __('admin.Entries') }}",
          sInfoEmpty: "{{ __('admin.Showing_0_to_0_of_0_entries') }}",
          sInfoFiltered: "({{ __('admin.Filtered') }} _MAX_ {{ __('admin.Entries2') }})",
          sSearch: "{{ __('admin.Search') }}:",
          oPaginate: {
            sFirst: "{{ __('admin.First') }}",
            sPrevious: "{{ __('admin.Previous') }}",
            sNext: "{{ __('admin.Next') }}",
            sLast: "{{ __('admin.Last') }}"
          }
        }
      });

      getStates($('#country_id').val());
      
      $('#country_id').change(function() {
        getStates($(this).val());
      });
      
      $('#state_id').change(function() {
        getCities($(this).val());
      });
    });

    function getStates(country_id) {
      const $state = $('#state_id');
      const currentState = '{{ $client->state_id }}';
      
      $.ajax({
        url: "{{ route('utils.locations_list_states_by_country') }}",
        data: { country_id },
        success: function(data) {
          $state.html('<option value="">{{ __('admin.State') }}</option>');
          
          $.each(data, function(id, value) {
            const selected = currentState === id ? 'selected' : '';
            $state.append(`<option value="${id}" ${selected}>${value}</option>`);
            
            if (currentState === id) {
              getCities(id);
            }
          });
          
          $('#state').show(150);
          $('#city_id').html('<option value="">{{ __('admin.City') }}</option>');
        }
      });
    }

    function getCities(state_id) {
      const $city = $('#city_id');
      const currentCity = '{{ $client->city_id }}';
      
      $.ajax({
        url: "{{ route('cities.index') }}",
        data: { state_id },
        success: function(data) {
          $city.html('<option value="">{{ __('admin.City') }}</option>');
          
          $.each(data, function(id, value) {
            const selected = currentCity == id ? 'selected' : '';
            $city.append(`<option value="${id}" ${selected}>${value}</option>`);
          });
          
          $('#city').show(150);
        }
      });
    }
  </script>

  @role('Mentor')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      const useDarkMode = window.matchMedia('(prefers-color-scheme: light)').matches;

      tinymce.init({
        selector: '#basicTextarea',
        plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount textpattern noneditable help charmap quickbars emoticons',
        imagetools_cors_hosts: ['picsum.photos'],
        menubar: 'edit view insert format tools table help',
        toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen preview print | insertfile image media template link anchor codesample | ltr rtl',
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
        content_style: 'body { font-family: Times New Roman, Arial, sans-serif; font-size: 14px }',
        image_title: true,
        automatic_uploads: true,
        media_live_embeds: true,
        relative_urls: false,
        remove_script_host: false,
        images_upload_url: "{{ route('trainings.files_upload', ['_token' => csrf_token()]) }}",
        file_picker_types: 'image',
        convert_urls: true,
        file_picker_callback: function(cb, value, meta) {
          const input = document.createElement('input');
          input.setAttribute('type', 'file');
          input.setAttribute('accept', 'image/*');
          
          input.onchange = function() {
            const file = this.files[0];
            const reader = new FileReader();
            
            reader.readAsDataURL(file);
            reader.onload = function() {
              const id = 'blobid' + new Date().getTime();
              const blobCache = tinymce.activeEditor.editorUpload.blobCache;
              const base64 = reader.result.split(',')[1];
              const blobInfo = blobCache.create(id, file, base64);
              blobCache.add(blobInfo);
              cb(blobInfo.blobUri(), { title: file.name });
            };
          };
          
          input.click();
        }
      });

      @if($l == 'ar')
        tinyMCE.settings.directionality = 'rtl';
      @endif

      @if($client->bio)
        const entrepreneurialExp = '{{ $client->bio->entrepreneurial_experience }}';
        $('#entrepreneurial_experience_brief').toggle(entrepreneurialExp === '1');
      @endif

      $('.entrepreneurial_experience').change(function() {
        $('#entrepreneurial_experience_brief').toggle($(this).val() === '1');
      });
    </script>
  @endrole
@stop
