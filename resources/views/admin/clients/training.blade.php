@extends('layouts.admin')

@section('style')
  <style>
    .inner-container {
      display: flex;
      flex-flow: column-reverse;
      gap: 1rem;
    }

    @media (min-width: 992px) {
     .show-lg {
        display: block !important;
      }
      .inner-container {
        flex-flow: row;
      }
    }
    .radius-15 {
      border-radius: 20px;
    }
    .progress-circle {
      position: relative;
      display: inline-block;
    }

    .circle-bg {
      stroke: #e6e6e6;
    }

    .circle {
      stroke: rgba(115, 103, 240, 0.9);
      /* Progress color */
      transition: stroke-dasharray 0.3s ease;
    }
    .content-container img {
      max-width: 100%;
      height: auto;
      object-fit: contain;
    }
    .content-container figure {
      margin: 0;
      max-width: 100%;
    }
    .content-container iframe,
    .content-container video {
      max-width: 100%;
      width: 100%;
      aspect-ratio: 16/9;
    }
    .video-wrapper {
      position: relative;
      padding-bottom: 56.25%; /* 16:9 aspect ratio */
      height: 0;
      overflow: hidden;
      max-width: 100%;
    }
    .video-wrapper iframe,
    .video-wrapper video {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
    }
  </style>
@stop

@section('content')
  <!-- Content wrapper -->
  <div class="">
    <!-- Content -->

    <div class="">
      <div class="row">
        <div class="col-lg-8">
          <div class="card shadow-sm mb-4" style="border-radius: 1rem;">
            <div class="card-header">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="card-title mb-0">{{ $training->title }}</h5>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-label-danger">{{ $training->CoursLevel }}</span>
                  <span class="badge bg-label-primary"><i class="ti ti-calendar ti-16px"></i> {{ $training->start_date }}</span>
                </div>
              </div>
              <p class="card-text text-muted mb-0">
                {{ $training->dsc }}
              </p>
            </div>

            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-2">{{ __('admin.Progress') }}</h6>
                <div class="d-flex align-items-center">
                  <span class="badge bg-label-primary me-2">{{ $progressPercentage }} %</span>
                </div>
              </div>
              <div style="width: 100%; height: 7px;" class="progress rounded-pill">
                <div class="progress-bar bg-primary" role="progressbar"
                  style="width: {{ number_format($progressPercentage, 2) }}%;"
                  aria-valuenow="{{ number_format($progressPercentage, 2) }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          </div>

          <div class="card" style="border-radius: 1rem;">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-6 gap-2">
                  <div class="me-1">
                    <h5 class="mb-0">{{ $ccontent->title }}</h5>
                  </div>
                  <div class="d-flex align-items-center">
                    {{-- <span class="badge bg-label-danger">Course type</span> --}}
                    <button 
                      id="shareButton"
                      class="btn btn-transparent btn-icon btn-sm shadow-none ms-2"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="Share"
                    >
                      <i class="ti ti-share ti-lg"></i>
                    </button>
                    <button 
                      id="copyUrlButton"
                      class="btn btn-transparent btn-icon btn-sm shadow-none"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="Copy URL"
                    >
                      <i id="copy-icon" class="ti ti-bookmarks ti-lg"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="card academy-content shadow-none border">
                <div class="p-2">
                  <div class="cursor-pointer content-container" style="max-width: 100%;">
                    @php
                      // Convert any standalone video tags to responsive containers
                      $content = preg_replace('/<video(.*?)>(.*?)<\/video>/is', '<div class="video-wrapper"><video$1>$2</video></div>', $ccontent->content);
                    @endphp
                    {!! $content ?? $ccontent->content !!}
                  </div>
                </div>
                <div class="card-body pt-4">
                  @if ($ccontent->desc)
                    <h5>{{ $l == 'en' ? 'Description' : 'الوصف' }}</h5>
                    <p class="mb-6">
                      {{ $ccontent->desc }}
                    </p>
                  @endif
                  {{-- <div id="instructorsContainer">
                    <hr class="my-6" />
                    <h5>{{ $l == 'en' ? 'Trainer' : 'المدرب' }}</h5>
                    <div class="d-flex justify-content-start align-items-center user-name">
                      <div class="avatar-wrapper">
                        <div class="avatar me-4">
                          <img src="{{ asset('/191014/'. $training->img_url) ?? '/images/placeholder.jpg' }}" alt="Avatar" class="rounded-circle" />
                        </div>
                      </div>
                      <div class="d-flex flex-column">
                        <h6 class="mb-1">{{ $training->{'name_'.$l} }}</h6>
                        <small>{{ $training->email }}</small>
                      </div>
                    </div>
                  </div> --}}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="accordion stick-top accordion-custom-button" id="courseContent">
            @foreach($contents as $key=>$content)
              <div 
                class="card bg-{{$ccontent->id==$content->id?'label-light':'white' }} shadow-sm mb-3"
                style="border-radius: 1rem;"
              >
                <div class="card-body">
                  <a
                    class="d-flex align-items-center gap-4 text-black"
                    href="/dashboard/training/my/{{$content->training_id}}?c={{$content->id}}"
                  >
                    <div class="w-15">
                      @if ($content->done())
                        <span 
                          class="w-px-30 h-px-30 bg-primary rounded-circle text-white"
                          style="display: grid; place-items: center;"
                        >
                          <i class='ti ti-check ti-28px;'></i>
                        </span>
                      @else
                        <span 
                          class="w-px-30 h-px-30 bg-light rounded-circle text-white"
                          style="display: grid; place-items: center;"
                        >
                          <i class='ti ti-check ti-28px;'></i>
                        </span>
                        <!-- Circular Progress -->
                        {{-- <div
                          class="progress-circle p-3 position-relative d-flex align-items-center justify-content-center"
                          style="width: 40px; height: 40px;">
                          <span
                            class="position-absolute text-black fw-bold"
                            style="font-size: 13px;">  {{ $progressPercentage }}%

                             </span>
                          <svg class="position-absolute" width="54"
                            height="54" viewBox="0 0 36 36">
                            <path class="circle-bg"
                              d="M18 2.0845
                              a 15.9155 15.9155 0 0 1 0 31.831
                              a 15.9155 15.9155 0 0 1 0 -31.831" fill="none"
                              stroke="#e6e6e6" stroke-width="2.5" />
                            <path class="circle"
                              d="M18 2.0845
                              a 15.9155 15.9155 0 0 1 0 31.831
                              a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#007bff"
                              stroke-width="2.5"
                              stroke-dasharray="{{ $progressPercentage }}, 100" />
                          </svg>
                        </div> --}}
                        <!-- Circular Progress -->
                      @endif
                    </div>
                    <span class="d-flex flex-column">
                      @if ($key != 0)
                        <span class="text-body fw-normal text-muted tex-sm">Step {{ $key }}</span>
                      @endif
                      <span class="mb-0 fs-6 fw-bold">{{ $content->title }}</span>
                    </span>
                  </a>
                </div>
              </div>
            @endforeach
            @if ($ccontent->done())
              <div 
                class="card bg-white shadow-sm mb-3"
                style="border-radius: 1rem;"
              >
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center w-100">
                    {{ $l == 'en' ? 'Quizzes' : 'الاختبارات' }}
                    <div class="w-px-100">
                      <a href="/dashboard/quizzes/my" class="btn btn-primary">
                        <i class="ti ti-pointer-question me-2"></i>
                        {{ $l == 'en' ? 'View' : 'عرض' }}
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
    <!-- / Content -->
    <div class="content-backdrop fade"></div>
  </div>
  <!-- Content wrapper -->
@stop

@section('pagescripts')
 {{-- <script>
    console.log(@json($ccontent))
  </script> --}}
  <script>
    const url = window.location.href;
    const urlArr = url.split('/');
    
    if (urlArr && urlArr.length > 0) {
      const latestItem = urlArr[urlArr.length - 1];

      const trainingContent = latestItem.includes('c');

      const instructorsContainer = document.getElementById('instructorsContainer');

      if (trainingContent) {
        instructorsContainer.style.display = 'none';
      }
    }
  </script>

  <script>
    const contentTitle = @json($ccontent->title);
    const contentDesc = @json($ccontent->desc);

    const copyUrlButton = document.getElementById('copyUrlButton');
    const shareButton = document.getElementById('shareButton');
    const copyIcon = document.getElementById('copy-icon');

    // Copy Training URL
    copyUrlButton.addEventListener('click', () => {
      if (copyIcon.classList.contains('text-primary')) {
        navigator.clipboard.writeText('');
        copyIcon.classList.remove('text-primary');
      } else {
        navigator.clipboard.writeText(window.location.href);
        copyIcon.classList.add('text-primary');
      }
    });

    // Share Training
    if (contentTitle) {
      shareButton.addEventListener('click', () => {
        if (navigator.share) {
          navigator.share({
            title: contentTitle,
            text: contentDesc ? contentDesc : '',
            url: window.location.href
          })
            .then(() => console.log('Successful share'))
            .catch((error) => console.log('Error sharing', error));
        }
      });
    }


    // خزن وقت بداية الجلسة
    let startTime = Date.now();

    // ID الدورة التدريبية الحالية
    const trainingId = "{{ $content->training_id ?? 0 }}"; // غيّرها حسب متغيرك
    const pageUrl = window.location.href;
  
    // دالة لحساب المدة بالدقائق
    function getDurationMinutes() {
        const now = Date.now();
        const diffMs = now - startTime;
        const minutes = Math.floor(diffMs / 60000); // 1 دقيقة = 60000ms
        return minutes > 0 ? minutes : 1;
    }

    // دالة لإرسال الوقت للسيرفر
    function sendTrainingTime() {
        const duration = getDurationMinutes();
        fetch("{{ route('trainings.time_tracking') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: {
                trainingId: trainingId,
                page_url: pageUrl,
                duration_minutes: duration,
            }
        });
    }

    window.addEventListener('beforeunload', (e) => {
      const duration = getDurationMinutes();
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('trainingId', trainingId);
        formData.append('page_url', pageUrl);
        formData.append('duration_minutes', duration);

        navigator.sendBeacon("{{ route('trainings.time_tracking') }}", formData);
    });
   

    // كمان ممكن تبعت تحديث كل 5 دقايق لو المستخدم قاعد فترة طويلة
    setInterval(sendTrainingTime, 5 * 60 * 1000);


  </script>
@stop

{{-- 
@section('style')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
@stop
@section('content')
@php($con = \App\Training::where('id',$contents->pluck('training_id')[0])->first())
<!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="">
            <div class="content-header row">
                <div class="content-header-left col-12 mb-2 mt-1">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h5 class="content-header-title float-left pr-1 mb-0">{{__('settings.View training content')}}</h5>
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb p-0 mb-0">
                                    <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a>
                                    </li>
									<li class="breadcrumb-item active">{{$ccontent->title}}
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<div class="content-body">
                <section id="multiple-column-form">
                    <div class="row match-height">
                        <div class="col-md-3 col-sm-12">
                            <div class="card ">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <br>
                                        <img width="250px" src="/191014/{{$con->image}}">
                                        <br><br>
                                        <h1>{{ $con->title  }}</h1>
                                        <br>
                                        {{ $con->dsc  }}
                                    </h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <ul class="widget-timeline mb-0">
                                            @foreach($contents as $key=>$content)
                                                <li class="timeline-items timeline-icon-{{$ccontent->id==$content->id?'primary':'secondary' }} active">
                                                    <a href="/dashboard/training/my/{{$content->training_id}}?c={{$content->id}}">
                                                        <div class="timeline-time">
                                                            @if ($content->done())
                                                                <i title="Completed {{$content->done()}}" class="bx bxs-check-circle primary"></i>
                                                            @endif
                                                        </div>
                                                    <h6 class="timeline-title {{$ccontent->id==$content->id?'text-bold-600 primary':''}}">{{$key+1}} - {{$content->title}}</h6>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9 col-sm-12">
                            <div class="card ">
                                <div class="card-header">
                                    <h4 class="card-title">

                                    </h4>
                                </div>
                                <div class="card-content p-75">
                                    {!! $ccontent->content !!}
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
@section('pagescripts')
    <script>
        // document.body.classList.add("menu-collapsed");
    </script>
@stop --}}
