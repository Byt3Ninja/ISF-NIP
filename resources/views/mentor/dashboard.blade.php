@extends('layouts.admin')

@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.My Dashboard'))

@section('vendorstyle')
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
@stop


@section('content')
  @php
    $countCards = [
        [
            'color' => 'success',
            'href' => '/dashboard/directory',
            'icon' => 'ti ti-briefcase',
            'text' => __('dashboard.Services'),
            'count' => $services_count,
        ],
        [
            'color' => 'primary',
            'href' => '/dashboard/inbox',
            'icon' => 'ti ti-message',
            'text' => __('dashboard.newmessages'),
            'count' => $nm_count,
        ],
        [
            'color' => 'secondary',
            'href' => '#',
            'icon' => 'ti ti-chalkboard',
            'text' => __('dashboard.Coming sessions'),
            'count' => count($mysessions),
        ],
        [
            'color' => 'warning',
            'href' => '#',
            'icon' => 'ti ti-calendar',
            'text' => __('dashboard.Cycles'),
            'count' => count($mycycles),
        ],
        [
            'color' => 'info',
            'href' => '#',
            'icon' => 'ti ti-server',
            'text' => __('admin.Company Attendance Rate'),
            'count' => $attends_rate,
        ],
        [
            'color' => 'danger',
            'href' => '#',
            'icon' => 'ti ti-star',
            'text' => __('cycles.Rate'),
            'count' => auth()->user()->mrating(),
            'isRating' => true, // Only this card has the isRating key
        ],
        [
            'color' => 'warning',
            'href' => '#',
            'icon' => 'ti ti-select',
            'text' => __('mentor.mentorshipRequests.withoutTasks'),
            'count' => $sessionWithoutTasks,
        ],
        [
            'color' => 'success',
            'href' => '#',
            'icon' => 'ti ti-check',
            'text' => __('mentor.mentorshipTasks.completedTasks'),
            'count' => $completedTasks,
        ],
        [
            'color' => 'info',
            'href' => '#',
            'icon' => 'ti ti-archive',
            'text' => __('mentor.mentorshipTasks.nonCompletedTasks'),
            'count' => $nonCompletedTasks,
        ],
    ];
  @endphp

  <!-- Content wrapper -->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-body">
        @if (session()->has('msg'))
          <div class="col-12">
            <div class="row">
              <div class="col-md-12">
                <div class="alert alert-{{ session()->get('class') }} mb-2" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                  <div class="d-flex align-items-center">
                    <i class="bx bx-{{ session()->get('class') == 'success' ? 'like' : 'error' }}"></i>
                    <span>
                      {{ __('admin.' . session()->get('msg')) }}
                      {{ Session::forget('msg') }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>
      <!-- Count cards -->
      <div class="row g-6">
        @foreach ($countCards as $card)
          <a href="{{ $card['href'] }}" class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-{{ $card['color'] }} h-100">
              <div class="card-body">
                <div class="d-flex justify-content-center align-items-center mb-2">
                  <div class="avatar me-4">
                    <span class="avatar-initial rounded bg-label-primary">
                      <i class="{{ $card['icon'] }} text-{{ $card['color'] }} ti-28px"></i>
                    </span>
                  </div>
                  @if (isset($card['isRating']) && $card['isRating'])
                    <h4 class="mb-0 text-center">{{ intVal($card['count']) }}</h4>
                  @else
                    <h4 class="mb-0 text-center">{{ $card['count'] }}</h4>
                  @endif
                </div>
                <p class="mb-1 text-center">{{ $card['text'] }}</p>
              </div>
            </div>
          </a>
        @endforeach
      </div>
      <!-- / Count cards -->

      <div class="row g-3 mt-6">
        <!-- Latest Services -->
        <div class="col-sm-6 col-12">
          <div class="card">
            <div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom" style="width: '100%">
              <div class="d-flex align-items-center py-2">
                <i class="ti ti-devices-question ti-24px"></i>
                <h5 class="card-title mb-0 mx-2">{{ __('dashboard.Latest services') }}</h5>
              </div>
              {{-- <div class="heading-elements">
                <ul class="list-inline mb-0">
                  <li>
                    <a data-action="close">
                      <i class="ti ti-x ti-24px"></i>
                    </a>
                  </li>
                </ul>
              </div> --}}
            </div>
            <div class="card-body">
              @if (count($services_latest) > 0)
                @foreach ($services_latest as $serv)
                  <div class="d-flex justify-content-between align-items-center my-2">
                    <div class="widget-todo-title-area d-flex align-items-center">
                      <a href="/dashboard/directory?find={{ $serv->{'name_' . $l} }}">
                        <span class="widget-todo-title ml-50 text-secondary">
                          <i class="ti ti-link"></i>
                          {{ $serv->{'name_' . $l} }}</span>
                      </a>
                    </div>
                    <div class="widget-todo-item-action d-flex align-items-center">
                      <div class="btn btn-label-primary py-1 rounded d-flex align-items-center justify-conetnt-center"
                        style="width: 200px; height: 30px">
                        <a href="/dashboard/directory?type={{ $serv->serviceCategory->id }}">
                          {{ $serv->serviceCategory->{'name_' . $l} }}
                        </a>
                      </div>
                    </div>
                  </div>
                @endforeach
              @else
                <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                  <div class="widget-todo-title-area d-flex align-items-center">
                    <span class="widget-todo-title ml-50">
                      <i class="bx bxs-bell-ring text-danger"></i>
                      {{ __('dashboard.No services added') }}.</span>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
        <!-- / Latest Services -->

        <!-- Latest Events -->
        <div class="col-sm-6 col-12">
          <div class="card">
            <div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom" style="width: '100%">
              <div class="d-flex align-items-center py-2">
                <i class="ti ti-calendar-event ti-24px"></i>
                <h5 class="card-title mb-0 mx-2">{{ __('dashboard.Latest Events') }}</h5>
              </div>
              {{-- <div class="heading-elements">
                <ul class="list-inline mb-0">
                  <li>
                    <a data-action="close">
                      <i class="ti ti-x ti-24px"></i>
                    </a>
                  </li>
                </ul>
              </div> --}}
            </div>
            <div class="card-body">
              @if (count($event_latest) > 0)
                @foreach ($event_latest as $event)
                  <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                    <div class="widget-todo-title-area d-flex align-items-center">
                      <a href="/dashboard/calendar">
                        <span class="widget-todo-title ml-50 text-secondary">
                          <i class="ti ti-link"></i>
                          {{ $event->title }}
                        </span>
                      </a>
                    </div>
                    <div
                      class="btn btn-label-success text-success py-1 rounded d-flex align-items-center justify-conetnt-center"
                      style="width: 200px; height: 30px;">
                      {{ date('d-m-Y', strtotime($event->start)) }}
                    </div>
                  </div>
                @endforeach
              @else
                <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                  <div class="widget-todo-title-area d-flex align-items-center">
                    <span class="widget-todo-title ml-50">
                      <i class="bx bxs-bell-ring text-danger"></i>
                      {{ __('dashboard.No Events added') }} '<a
                        href="/dashboard/calendar">{{ __('dashboard.Add yours now') }}</a>'.</span>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
        <!-- / Latest Events -->
      </div>
    </div>
  </div>
@stop

@section('scripts')
@stop

@section('pagescripts')
  <script></script>

  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>
@stop
