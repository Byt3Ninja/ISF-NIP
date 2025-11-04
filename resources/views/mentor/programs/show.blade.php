@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <h5 class="fw-bold mb-4">{{ __('admin.Program Details') }}</h5>
    @foreach($cycles as $cycle)
    @if (!is_null($cycle->organization))
       @php
          $imageUrl = $cycle->organization->image_url;
          if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) { $imageUrl = asset( $imageUrl ); }
          $imagePath = parse_url($imageUrl, PHP_URL_PATH);

          $fileN = ltrim(str_replace('/dashboard/uploads/files/', '', $imagePath), '/');

      @endphp
  @endif

    <div class="card card-body mb-4">
      <div class="d-flex flex-column flex-md-row align-items-start gap-3">
        <img src="{{ $imageUrl }}" class="rounded object-fit-contain border shadow-xs"
          style="width: 110px; height: 110px;" alt="Logo">
        <div class="flex-grow-1">
          <h5 class="fw-semibold line-clamp-2 mb-3 w-100 w-md-75">
            {{ $cycle->title }}
          </h5>
          <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-4 mt-3">
            <div class="d-flex align-items-center gap-1">
              <i class="ti ti-building-skyscraper text-primary"></i>
              <span>{{ __('admin.Startups') }}</span><span class="fw-bold ms-1">{{ $cycle->companies->count() }}</span>
            </div>
            <div class="d-flex align-items-center gap-1">
              <i class="ti ti-picture-in-picture-top text-primary"></i>
              
              <span>{{ __('admin.Upcoming Sessions') }}</span><span class="fw-bold ms-1">{{ $cycle->mentorshipRequests()->where('zoom_date' , '>', now())->count() }}</span>
            </div>
            <div class="d-flex align-items-center gap-1">
              <i class="ti ti-archive text-primary"></i>
              <span>{{ __('admin.Sessions Without Tasks') }}</span>
              <span class="fw-bold ms-1">{{ $cycle->sessionsWithoutTasks }}</span>
            </div>
          </div>
        </div>
        {{-- <div class="text-start text-md-end">
          <span class="badge bg-label-secondary text-dark rounded-pill">
            <i class="ti ti-star-filled text-warning me-1"></i> 4.8
          </span>
        </div> --}}
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
          <div class="progress-bar progress-bar-animated bg-primary" style="width: {{ $cycle->progressPercentage }}%;"></div>
        </div>
        <div class="text-end">
          <small class="text-muted">{{ $cycle->totalStepsFinish }}/{{ $cycle->totalSteps }}</small>
        </div>
      </div>
    </div>
    @endforeach

    <div class="card mt-4">
      <div class="card-header border-bottom">
        <ul class="nav nav-pills">
          <li class="nav-item">
            <a class="nav-link active" id="mentorship-tab" data-bs-toggle="tab" href="#mentorship-sessions">{{ __('admin.Mentorship Sessions') }}
              </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="guideline-tab" data-bs-toggle="tab" href="#program-guideline">{{ __('admin.Program Guideline') }}</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="policy-tab" data-bs-toggle="tab" href="#financial-policy">{{ __('admin.Financial Policy') }}</a>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content px-0">
          <div class="tab-pane fade show active" id="mentorship-sessions" role="tabpanel"
            aria-labelledby="mentorship-tab">
            <h5 class="fw-bold mb-3">{{ __('admin.Mentorship Sessions') }}</h5>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead class="bg-label-light">
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
                        <span>{{ $session->zoom_date }}</span>
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
                      @php
                        $now = date('Y-m-d H:i');
                        $start_time = date('Y-m-d H:i', strtotime($session->zoom_date . '-15 minutes'));
                        $end_time = date('Y-m-d H:i', strtotime($session->zoom_date . '+' . $session->period .' minutes'));
                      @endphp
                      {{-- <a href="{{ $session->join_url }}" class="btn btn-sm btn-light">
                        <i class="ti ti-video me-2"></i>
                        <span>{{ __('admin.Join Meeting') }}</span>
                      </a> --}}
                      @if( $now >= $start_time )
																@if( $now <= $end_time )
																<a href="{{ $session->start_url }}" target="_blank">
																	<button title="Start Meeting" type="button" data-value="{{ $session->id }}" type="button" class="btn btn-label-primary rounded">
																		<i class="ti ti-link"></i> {{ __('admin.Join Meeting') }}
																	</button>
																</a>
																@else
																	<span class="text-danger"> <i class="ti ti-alarm"></i> {{ __('admin.Meeting_Expired') }} </span>
																@endif
															@else
																<i class="ti ti-alarm"></i> {{ __('admin.Meeting_will_start_at_time') }}
															@endif

                    </td>
                  </tr>
                </tbody>
                @endforeach
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="program-guideline" role="tabpanel" aria-labelledby="guideline-tab">
            <div class="mb-6 pb-4 border-bottom">
              <h5 class="fw-bold mb-3">{{ __('admin.Program Guideline') }}
                
              </h5>
              @foreach($cycles as $cycle)
              @if ($cycle->program_guideline || $cycle->program_guideline_file)
                @if ($cycle->program_guideline)
                  <p class="text-muted mb-0">
                    {{ $cycle->program_guideline }}
                  </p>
                @endif
                @if ($cycle->program_guideline_file)
                <div>
                  <h5 class="fw-bold mb-3">{{ __('admin.Documents') }}</h5>
                  <ul class="list-group">
                    <li class="list-group-item d-flex align-items-center gap-3">
                      <i class="ti ti-file-text text-primary"></i>
                      <a href="{{ Storage::disk('public_uploads')->url($cycle->program_guideline_file) }}" class="text-dark" target="_blank" download>
                        {{ __('admin.Download') }}
                      </a>
                    </li>
                  </ul>
                </div>
              @endif
            @endif
          @endforeach
            </div>
            
          </div>
          <div class="tab-pane fade" id="financial-policy" role="tabpanel" aria-labelledby="policy-tab">
            <div class="mb-6 pb-4 border-bottom">
              <h5 class="fw-bold mb-3">
                {{ __('admin.Financial Policy') }}
               <span class="badge bg-label-primary text-white ms-2">
                    ${{ collect($cycles)->pluck('budget_allocation')->filter()->join(', ') ?: 'N/A' }}
                </span>
              </h5>
              @foreach($cycles as $cycle)
              <p class="text-muted mb-0">{{ $cycle->financial_guideline }}</p>
              @if ($cycle->financial_guideline_file)
                <div>
                  <h5 class="fw-bold mb-3">{{ __('admin.Documents') }}</h5>
                  <ul class="list-group">
                    <li class="list-group-item d-flex align-items-center gap-3">
                      <i class="ti ti-file-text text-primary"></i>
                      <a href="{{ Storage::disk('public_uploads')->url($cycle->financial_guideline_file)}}" class="text-dark" target="_blank" download>
                      {{ __('admin.Download') }}
                      </a>
                    </li>
                  </ul>
                </div>
              @endif
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop