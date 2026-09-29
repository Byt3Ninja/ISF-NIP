<div class="card">
  <div class="card-body">
    <h5 class="card-title">
      {{ $l == 'en' ? 'Mentorship & WorkShop' : 'الارشاد وورش العمل' }}
    </h5>
    @if( $cType != 5 && array_intersect(['dashboard.widgets_tables_my_last_training_view'], $permissions) &&
    $sessions->count() > 0 )
    <div class="col-12">
      <section class="border-bottom pb-4 mb-4">
        <div class="row">
          <div class="col-md-12">
            <div class="mt-10">
              <div class="border-bottom pb-2 mb-4">
                <h5 style="text-transform: capitalize">
                  <i class="ti ti-clipboard-list ti-28px"></i>
                  <span class="mx-2"> {{ __('dashboard.My Latest training') }}
                    <a href="/dashboard/training/my">{{ __('dashboard.Show all') }}</a>
                  </span>
                </h5>
              </div>
              <div class="">
                <div class="table-responsive">
                  <table class="table mb-0" id="trainingtable">
                    <thead class="bg-label-light">
                      <tr>
                        <th>#</th>
                        <th>{{ __('cycles.Type') }}</th>
                        <th>{{ __('cycles.Title') }}</th>
                        <th>{{ __('cycles.Start') }}</th>
                        <th>{{ __('cycles.End') }}</th>
                        <th>{{ __('cycles.details') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach( $sessions as $key => $session )
                      <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ __('cycles.' . $session->type) }}</td>
                        <td><a title="Click to show details" href="" onclick="ShowDetails('{{ $session->id }}'); return false;">{{ $session->title }}</a></td>
                        <td>{{ FormatDate($session->datetime, 'Y-m-d H:i A') ?? '-' }}</td>
                        <td>{{ FormatDate($session->end_datetime, 'Y-m-d H:i A') ?? '-' }}</td>
                        <td>
                          @if( $session->type == 'sessions' )
                          {{ __('cycles.Duration') }}: {{ $session->duration }} {{ __('cycles.Minutes') }} :
                          @php
                            $endTime = \Carbon\Carbon::createFromTimestamp($session->datetime)->addMinutes($session->duration);
                          @endphp
                          @if(now() > $endTime)
                            <span class="text-muted">{{ $l == 'ar' ? 'انتهت الجلسة' : 'Session Completed' }}</span>
                          @else
                          <a href="{{ $session->join_url }}" target="_blank">
                            <button title="Join only" type="button"
                              class="btn btn-icon rounded-circle btn-light-primary"> <i class="ti ti-brand-zoom"></i>
                            </button>
                          </a>
                          @endif
                          @elseif ($session->type == 'offline')
                          <b>{{ __('cycles.trainer name') }}:</b> {{ $session->trainer_name }}<br>
                          <b>{{ __('cycles.training location') }}:</b>
                          {{ $session->location }} <br>
                          @else
                          <a href="/dashboard/training/my/{{ $session->location }}">{{ __('cycles.Display course contents') }} </a>
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
      </section>
    </div>
    @else
    <div class="alert alert-info">
      {{ __('dashboard.No training sessions found') }}
    </div>
    @endif

    @if( array_intersect(['dashboard.widgets_tables_mentors_sessions_view'], $permissions) )
    <div class="col-12">
      <section>
        <div class="row">
          <div class="col-md-12">
            <div class="mt-10">
              <div class="border-bottom pb-2 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center">
                    <i class='ti ti-calendar-event me-2'></i>
                    <h5 class="card-title mb-0">
                      {{ __("dashboard.UpcomingMentorsSession") }}
                      {{-- @if($days!=14)
                      <div onclick="redirectWithDays('14')" class="btn btn-label-success" id="forteenDays"> 14
                        {{__('cycles.days')}}</div>
                      @else
                      <div onclick="redirectWithDays('14')" class="btn btn-label-warning" id="forteenDays"> 14
                        {{__('cycles.days')}}</div>
                      @endif
                      @if($days!=7)
                      <div onclick="redirectWithDays('7')" class="btn btn-label-success" id="sevenDays"> 7
                        {{__('cycles.days')}}</div>
                      @else
                      <div onclick="redirectWithDays('7')" class="btn btn-label-warning" id="sevenDays"> 7
                        {{__('cycles.days')}}</div>
                      @endif --}}
                    </h5>
                  </div>
                  <a class="btn btn-sm btn-primary" href="/dashboard/mentorship/mine">
                    <i class="ti ti-eye me-2 ti-16px"></i>
                    {{__('cycles.showAll')}}
                  </a>
                </div>
              </div>
              <div class="">
                <div class="table-responsive">
                  <table class="table mb-0 sessonsTable">
                    <thead class="bg-label-light">
                      <tr>
                        <th>{{__('cycles.User/Title')}}</th>
                        <th>{{__('cycles.Mentor')}}</th>
                        @if ($_user->hasRole('Admin'))<th>{{__('cycles.Rate/Comment')}}</th>@endif
                        <th>{{__('cycles.Date/Time')}}</th>
                        <th>{{__('cycles.Approved')}}</th>
                        <th>{{__('cycles.Done')}}</th>
                        <th>{{__('cycles.Actions')}}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($nextSessions as $session)
                      <tr>
                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <a class="media-left" href="/dashboard/users/{{ $session->user_id }}/edit">
                              <img src="{{ $session->user->image_url }}" alt="avatar" class="rounded-circle me-3"
                                height="40" width="40">
                            </a>
                            <a href="/dashboard/users/{{ $session->user_id }}/edit">
                              <div class="media-body">
                                <h6 class="media-heading mb-0 text-nowrap">{{ $session->user->name }}</h6>
                                <span class="font-small-2 text-nowrap">{{$session->user->title }}</span>
                              </div>
                            </a>
                          </div>
                        </td>
                        <td>
                          <div style="cursor: pointer;" class="d-flex align-items-center"
                            onclick="mentor({{ $session->mentor_id }}, '{{ $session->mentor->name }}', '{{ $session->mentor->ratings }}')">
                            <div class="avatar">
                              <img class="img-fluid rounded-circle me-3"
                                src="{{$session->mentor->image_url ?? '/icons/account.svg'}}" alt="image" height="20"
                                width="20" />
                            </div>
                            <span class="chip-text text-nowrap"> {{$session->mentor->name}} </span>
                          </div>
                        </td>
                        @if ($_user->hasRole('Admin'))
                        <td> <small> {!! $session->rate_comment ? str_repeat('<i class="bx bxs-star"
                              style="color: #fc960f;"></i>', $session->rating) . '<br>' . $session->rate_comment
                            :
                            $session->comment !!} </small> </td>
                        @endif
                        <td> {{ $session->zoom_date }} <br> ({{ $session->period }}M) </td>
                        <td>
                          <div class="d-flex align-items-center gap-1"> {!! __( $session->approved ) ??'<span
                              class="warning">'.__('Waiting').'</span>' !!} </div>
                        </td>
                        <td> {{ $session->done ?__('Yes') : __('No') }} </td>
                        <td>
                          @php
                            $endTime = \Carbon\Carbon::parse($session->zoom_date)->addMinutes($session->period);
                          @endphp
                          @if(now() > $endTime)
                            <span class="text-muted">{{ $l == 'ar' ? 'انتهت الجلسة' : 'Session Completed' }}</span>
                          @else
                          <a class="btn btn-sm btn-label-primary" href="{{ route('mentorship.join', $session->id) }}" target="_blank">
                            <i class="ti ti-brand-zoom me-2"></i>
                            {{ __('dashboard.Join now') }}
                          </a>
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
      </section>
    </div>
    @else
    <div class="alert alert-info">
      {{ __('dashboard.No mentorship sessions found') }}
    </div>
    @endif


  </div>
</div>