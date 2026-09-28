@extends('layouts.admin')
@section('title', __('admin.Profile'))
@section('subTitle', __('admin.Edit your profile information'))

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-profile.css" />
@stop

@php
$user = auth()->user();
$company = $user->company;

$canRequestMentorship =
  $company &&
  $company->cycle !== 0 &&
  (
    $user->hasRole('Registered') ||
    (!$user->hasRole('Mentor') && !$user->hasRole('Admin'))
  );
  
if ($canRequestMentorship) {
  $cycle = auth()->user()->company->cycledata;
}else{
  $cycle = null;
}

 $schedules = App\Schedule::where('user_id', $client->id ?? auth()->id())
  ->where(function ($query) {$query
      ->whereNull('date')
      ->orWhere('date', '>', date('Y-m-d'))
      ->orWhere(function ($q) {
        $q->where('date', date('Y-m-d'))
        ->where('tto', '>', date('H:i:s'));
      });
  })
  ->get();
@endphp

@section('content')
  <div class="">
    {{-- Alert Messages --}}
    @if(session()->has('error'))
      <div class="row">
        <div class="col-md-12">
          <div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
            {{ __('dashboard.' . session()->get('error')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
      </div>
    @endif

    @if(session()->has('msg'))
      <div class="row">
        <div class="col-md-12">
          <div role="alert" class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2">
            {{ __('admin.' . session()->get('msg')) }}
            {{ Session::forget('msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
      </div>
    @endif

    <div class="">
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
              <figure class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
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

                      @if(!empty($client->bio->linkedin))
                        <a target="_blank" 
                           href="{{ $client->bio->linkedin }}"
                           aria-label="LinkedIn Profile">
                          <i class="ti ti-brand-linkedin ti-lg"></i>
                        </a>
                      @endif
                    </h1>

                    @php
                      $roles = $client->getRoleNames();
                      $translatedRoles = $roles->map(fn($role) => __('admin.' . strtolower($role)))->implode(', ');
                    @endphp

                    <h2 class="fw-medium h5">
                      <i class="ti ti-user ti-lg"></i> {{ $translatedRoles }}
                    </h2>

                    <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                      <li class="list-inline-item d-flex gap-2 align-items-center">
                        <i class="ti ti-palette ti-lg"></i>
                        <span class="fw-medium">{{ $l == 'ar' ? $client->title_ar : $client->title_en }}</span>
                      </li>

                      @if($client->city ?? 0)
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

                  <div>
                    <a href="javascript:void(0)" class="btn btn-primary disabled me-3 mb-3 mb-md-0">
                      <i class="ti ti-user-check ti-xs me-2"></i>{{ __('admin.Follow') }}
                    </a>

                    @if($schedules->isNotEmpty())
                      <button
                        type="button" 
                        class="btn btn-primary" 
                        onclick="if (typeof fetchSlots === 'function') { fetchSlots({{ $client->id }}, {{ isset($cycle) ? $cycle?->id : 0 }}, {{ isset($cycle) ? $cycle?->organization_id : 0 }}); }"
                      >
                      <i class="ti ti-calendar-plus me-2"></i>
                        {{ __('cycles.Request_Mentorship') }}
                      </button>
                    @else
                      <button type="button" class="btn btn-primary" disabled>
                        <i class="ti ti-calendar-plus me-2"></i>
                        {{ __('cycles.Not_Available_Request_Mentorship') }}
                      </button>
                    @endif
                  </div>
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
            </ul>
          </div>
        </div>
      </nav>

      {{-- Tab Content --}}
      <div class="tab-content p-0" id="settingsTabsContent">
        {{-- Profile Tab --}}
        <div class="tab-pane fade show active" id="Profile" role="tabpanel" aria-labelledby="Profile-tab">
          <section class="row">
            {{-- Profile Summary --}}
            <div class="col-xl-12">
              <article class="card mb-6">
                <div class="card-body">
                  <h3 class="h5 cart-title">{{ __('admin.ProfileSummary') }}</h3>
                  <p>
                    {{ $l == 'ar' 
                        ? optional($client->bio)->bio_ar ?? __('admin.N/A') 
                        : optional($client->bio)->bio_en ?? __('admin.No bio has been added yet') }}
                  </p>
                </div>
              </article>
            </div>

            {{-- Statistics Cards --}}
            @hasanyrole('Admin|organizationManager')
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
                            <i class="ti ti-calendar-event text-primary ti-28px"></i>
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
                            <i class="ti ti-star text-primary ti-28px"></i>
                          </span>
                        </div>
                        <p class="mb-0 text-center h4">{{ number_format($average_rating ?? 0, 1) }}</p>
                      </div>
                      <p class="mb-1 text-center">{{ __('admin.Mentorship Avr Rating') }}</p>
                    </div>
                  </article>
                </div>
              </div>
            @endhasanyrole
          </section>

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
                      <strong class="mx-2">{{ $l == 'ar' ? 'الاسم:' : 'Full Name:' }}</strong>
                      <span>
                        {{ $l == 'ar' 
                          ? $client->first_name_ar . ' ' . $client->last_name_ar 
                          : $client->first_name_en . ' ' . $client->last_name_en }}
                      </span>
                    </li>

                    <li class="d-flex align-items-center mb-4">
                      <i class="ti ti-check ti-lg"></i>
                      <strong class="mx-2">{{ $l == 'ar' ? 'الحالة' : 'Status' }}:</strong>
                      <span>{{ $l == 'ar' ? 'نشط' : 'Active' }}</span>
                    </li>

                    <li class="d-flex align-items-center mb-4">
                      <i class="ti ti-crown ti-lg"></i>
                      <strong class="mx-2">{{ $l == 'ar' ? 'المسمى الوظيفي' : 'Role' }}:</strong>
                      <span>{{ $l == 'ar' ? $client->title_ar : $client->title_en }}</span>
                    </li>

                    @if($client->city ?? 0)
                      <li class="d-flex align-items-center mb-4">
                        <i class="ti ti-flag ti-lg"></i>
                        <strong class="mx-2">{{ $l == 'ar' ? 'الدولة:' : 'Country:' }}</strong>
                        <span>{{ $client->country->{'name_' . $l} }}</span>
                      </li>
                    @endif

                    <li class="d-flex align-items-center mb-2">
                      <i class="ti ti-language ti-lg"></i>
                      <strong class="mx-2">{{ $l == 'ar' ? 'اللغات:' : 'Languages:' }}</strong>
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
                      <strong class="mx-2">{{ $l == 'ar' ? 'الهاتف:' : 'Phone:' }}</strong>
                      <span>{{ $client->phone }}</span>
                    </li>

                    <li class="d-flex align-items-center mb-4">
                      <i class="ti ti-mail ti-lg"></i>
                      <strong class="mx-2">{{ $l == 'ar' ? 'البريد الإلكتروني:' : 'Email:' }}</strong>
                      <span>{{ $client->email }}</span>
                    </li>
                  </ul>

                  <h3 class="card-text text-uppercase text-muted small mt-4">
                    {{ __('admin.Cycles') }}
                  </h3>

                  <ul class="list-unstyled mb-0 mt-3 pt-1">
                    @foreach($mentors as $mentor)
                      <li class="d-flex flex-wrap mb-4">
                        - <span class="fw-medium me-2">{{ $mentor->cycle->title ?? '' }}</span>
                      </li>
                    @endforeach
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
                              href="{{ route('mentors.profile_view', $task->user_id) }}"
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

                          <td>
                            <a 
                              href="/dashboard/companyProfile/{{ $task->company_id }}"
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
                          <th>#</th>
                          <th>{{ __('admin.Title') }}</th>
                          <th>{{ __('admin.Description') }}</th>
                          <th>{{ __('admin.Start') }}</th>
                          <th>{{ __('admin.End') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($mentors as $n => $mentor)
                          <tr>
                            <td>{{ $n + 1 }}</td>
                            <td>{{ $mentor->cycle->title ?? '' }}</td>
                            <td>{{ strip_tags($mentor->cycle->description) }}</td>
                            <td>{{ $mentor->cycle->start ?? '' }}</td>
                            <td>{{ $mentor->cycle->end ?? '' }}</td>
                          </tr>
                        @endforeach
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
  </div>

  @if(!empty($order))
    @include(
      'partials.book-session-modal',
      [
        'mentor_id' => $client->id,
        'user_id' => $order->user_id,
        'cycle_id' => $order->cycle_id,
        'organization_id' => $order->cycle->organization_id,
      ]
    )
  @endif
  {{-- Session Request Modal --}}
 {{-- @include('admin.clients.startup.partials._session_modal') --}}
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/pages-profile.js"></script>
  <script src="/vuexy/assets/js/mentorship-session.js"></script>  

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
