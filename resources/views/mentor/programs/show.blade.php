@extends('layouts.admin')

@section('content')
<div class="">
  <div class="">
    {{-- <ul class="nav nav-pills flex-nowrap mb-6" role="tablist" style="max-width: 100%; overflow-x: auto;">
      <li class="nav-item me-2" role="presentation">
        <a class="nav-link active" id="enrolled-programs-tab" data-bs-toggle="tab" href="#enrolled-programs" role="tab" aria-controls="enrolled-programs" aria-selected="true">
          <i class="ti ti-components me-2"></i>{{ __('mentor.enrolled_programs') }}
        </a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="mentorship-sessions-tab" data-bs-toggle="tab" href="#mentorship-sessions-tab-content" role="tab" aria-controls="mentorship-sessions-tab-content" aria-selected="false">
          <i class="ti ti-versions me-2"></i>{{ __('admin.Mentorship Sessions') }}
        </a>
      </li>
    </ul> --}}
    <div class="tab-content p-0 shadow-none">
      <div class="tab-pane fade show active" id="enrolled-programs" role="tabpanel" aria-labelledby="enrolled-programs-tab">
        @foreach($cycles as $cycle)
          @php
            $cycleHasImage = (bool) $cycle->img_url
              || ($cycle->organization && (bool) $cycle->organization->img_url);
          @endphp
          <div class="card card-body mb-4">
            <div class="d-flex flex-column flex-md-row align-items-start gap-3">
              <div class="avatar avatar-lg flex-shrink-0">
                <span class="avatar-initial rounded bg-label-primary" style="cursor: default;">
                  <i class="ti ti-rocket text-primary ti-2xl"></i>
                </span>
                @if ($cycleHasImage)
                  <img src="{{ $cycle->logo }}" alt="program-image"
                       class="position-absolute top-0 start-0 w-100 h-100 object-fit-contain rounded"
                       style="padding: 4px; background-color: #fff; cursor: default;"
                       onerror="this.onerror=null; this.remove();">
                @endif
              </div>
              <div class="flex-grow-1">
                <h5 class="fw-semibold line-clamp-2 mb-3 w-100 w-md-75">
                  {{ $cycle->title }}
                </h5>
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-4 mt-3">
                  <div class="d-flex align-items-center gap-1">
                    <i class="ti ti-building-skyscraper text-primary"></i>
                    <span>{{ __('admin.Startups') }}</span>
                    <span class="fw-bold ms-1">{{ $cycle->companies->count() }}</span>
                  </div>
                  <div class="d-flex align-items-center gap-1">
                    <i class="ti ti-picture-in-picture-top text-primary"></i>
                    <span>{{ __('admin.Upcoming Sessions') }}</span>
                    <span class="fw-bold ms-1">{{ $cycle->mentorshipRequests()->where('zoom_date', '>', \App\Services\TimezoneService::platformNow()->format('Y-m-d H:i:s'))->count() }}</span>
                  </div>
                  <div class="d-flex align-items-center gap-1">
                    <i class="ti ti-archive text-primary"></i>
                    <span>{{ __('admin.Sessions Without Tasks') }}</span>
                    <span class="fw-bold ms-1">{{ $cycle->sessionsWithoutTasks }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-4 p-3 bg-label-light mb-4 rounded">
              <h6 class="fw-bold mb-1">
                <i class="ti ti-message-2 text-primary me-1"></i>
                {{ __('admin.Description') }}
              </h6>
              <p class="text-muted mb-0">
                {{ $cycle->description }}
              </p>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
              <div class="d-flex gap-3">
                <div class="d-flex align-items-center gap-2">
                  <i class="ti ti-calendar text-primary"></i>
                  <strong>{{ __('admin.From') }}:</strong>
                  <strong>{{ $cycle->start }}</strong>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <i class="ti ti-calendar text-primary"></i>
                  <strong>{{ __('admin.To') }}:</strong>
                  <strong>{{ $cycle->end }}</strong>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2">
              <small class="text-muted me-2">{{ round($cycle->progressPercentage) }}%</small>
              <div class="progress flex-grow-1 me-2" style="height: 8px;">
                <div class="progress-bar progress-bar-animated bg-primary" 
                     style="width: {{ $cycle->progressPercentage }}%;"></div>
              </div>
              <div class="text-end">
                <small class="text-muted">{{ $cycle->totalStepsFinish }}/{{ $cycle->totalSteps }}</small>
              </div>
            </div>
          </div>
        @endforeach
      </div>
      
      <div class="tab-pane fade" id="mentorship-sessions-tab-content" role="tabpanel" aria-labelledby="mentorship-sessions-tab">
        <div class="card">
          <div class="card-body">
            <h5 class="mb-3">{{ __('admin.Mentorship Sessions') }}</h5>
            <div class="table-responsive">
              <table id="mentorship-sessions-table" class="table table-bordered">
                <thead class="table-light">
                  <tr>
                    <th>{{ __('admin.Company') }}</th>
                    <th>{{ __('admin.User') }}</th>
                    <th>{{ __('admin.Date / Time') }}</th>
                    <th>{{ __('admin.Admin Notes') }}</th>
                    <th>{{ __('admin.Meeting Link') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($upcomingSessions as $session)
                    <tr>
                      <td>
                        <span class="text-dark line-clamp-2">{{ $session?->user?->company?->{'name_' . $l} }}</span>
                      </td>
                      <td>
                        <span class="text-dark line-clamp-2">{{ $session?->user?->first_name_en }} {{ $session?->user?->last_name_en }}</span>
                      </td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <i class="ti ti-calendar-event text-primary"></i>
                          <span>{{ optional(\App\Services\TimezoneService::forViewer($session->zoom_date, $_user))->format('Y-m-d H:i:s') }}</span>
                          <small class="text-muted">
                            @php
                              $period = $session->period;
                              if ($period >= 60) {
                                $hours = floor($period / 60);
                                $minutes = $period % 60;
                                echo $hours . ' ' . __('admin.Hour') . ($hours > 1 ? 's' : '');
                                if ($minutes > 0) {
                                  echo ' ' . $minutes . ' ' . __('admin.Minute') . ($minutes > 1 ? 's' : '');
                                }
                              } else {
                                echo $period . ' ' . __('admin.Minute') . ($period > 1 ? 's' : '');
                              }
                            @endphp
                          </small>
                        </div>
                      </td>
                      <td>{{ $session->admin_comment }}</td>
                      <td>
                        @if($session->meeting_status === 'joinable')
                            <a href="{{ $session->start_url }}" target="_blank">
                              <button title="Start Meeting" type="button" data-value="{{ $session->id }}"
                                      class="btn btn-label-primary rounded">
                                <i class="ti ti-link me-2"></i> {{ __('admin.Join Meeting') }}
                              </button>
                            </a>
                        @elseif($session->meeting_status === 'expired')
                            <span class="text-danger">
                              <i class="ti ti-alarm me-2"></i> {{ __('admin.Meeting_Expired') }}
                            </span>
                        @else
                          <i class="ti ti-alarm me-2"></i> {{ __('admin.Meeting_will_start_at_time') }}
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
$(document).ready(function() {
  $('#mentorship-sessions-table').DataTable({
    responsive: true,
    pageLength: 10,
    order: [[2, 'desc']]
    @if (app()->getLocale() == 'ar')
    ,language: {
      search: 'ابحث:',
      lengthMenu: 'أظهر _MENU_ مدخلات',
      info: 'إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل',
      paginate: { previous: 'السابق', next: 'التالي' },
      emptyTable: 'لا توجد بيانات'
    }
    @endif
  });
});
</script>
@stop
