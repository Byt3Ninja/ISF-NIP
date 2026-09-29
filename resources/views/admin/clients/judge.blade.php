@extends('layouts.admin')
@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.My Dashboard'))

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
            'count' => $messenger_count,
        ],
        [
            'color' => 'secondary',
            'href' => '#',
            'icon' => 'ti ti-chalkboard',
            'text' => __('dashboard.First screening'),
            'count' => count($csteps),
        ],
        [
            'color' => 'warning',
            'href' => '#',
            'icon' => 'ti ti-calendar',
            'text' => __('dashboard.Final screening'),
            'count' => count($fcompanies),
        ],
    ];
  @endphp

  <!-- Content wrapper -->
  <div class="">
    <div class="">
      <div class="content-body">
        @if (session()->has('msg'))
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-success alert-dismissible" role="alert">
                {{ __('admin.' . session()->get('msg')) }}
                {{ Session::forget('msg') }} <button type="button" class="btn-close" data-bs-dismiss="alert"
                  aria-label="Close"></button>
              </div>
            </div>
          </div>
        @elseif(session()->has('message2'))
          <div class="alert alert-danger alert-dismissible" role="alert">
            {{ __('admin.' . session()->get('message2')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
        <section>
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
                <div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom"
                  style="width: '100%">
                  <div class="d-flex align-items-center py-2">
                    <i class="ti ti-devices-question ti-24px"></i>
                    <h5 class="card-title mb-0 mx-2">{{ __('dashboard.Latest services') }}</h5>
                  </div>
                </div>
                <div class="card-body">
                  @if (count($services_latest) > 0)
                    @foreach ($services_latest as $serv)
                      <div class="d-flex justify-content-between align-items-center my-2">
                        <div class="widget-todo-title-area d-flex align-items-center">
                          @if( $_user->ableTo('users.directory_view', $permissions) )<a href="/dashboard/directory?find={{ $serv->{'name_' . $l} }}">
                            <span class="widget-todo-title ml-50 text-secondary"> @endif
                              <i class="ti ti-link"></i>
                              {{ $serv->{'name_' . $l} }}</span>
                          @if( $_user->ableTo('users.directory_view', $permissions) )</a> @endif
                        </div>
                        <div class="widget-todo-item-action d-flex align-items-center">
                          <div class="btn btn-label-primary py-1 rounded d-flex align-items-center justify-conetnt-center"
                            style="width: 200px; height: 30px">
                            @if( $_user->ableTo('users.directory_view', $permissions) && $serv->serviceCategory ) <a href="/dashboard/directory?type={{ $serv->serviceCategory->id }}"> @endif
                              {{ $serv->serviceCategory ? $serv->serviceCategory->{'name_' . $l} : __('dashboard.No category') }}
                              @if( $_user->ableTo('users.directory_view', $permissions) && $serv->serviceCategory ) 
                            </a> @endif
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
                <div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom">
                  <div class="d-flex align-items-center py-2">
                    <i class="ti ti-calendar-event ti-24px"></i>
                    <h5 class="card-title mb-0 mx-2">{{ __('dashboard.Latest Events') }}</h5>
                  </div>
                </div>
                <div class="card-body">
                  @if (count($events_latest) > 0)
                    @foreach ($events_latest as $event)
                      <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                        <div class="widget-todo-title-area d-flex align-items-center">
                          @if( $_user->ableTo('calendar.events_list', $permissions) )<a href="/dashboard/calendar"> @endif
                            <span class="widget-todo-title ml-50 text-secondary">
                              <i class="ti ti-link"></i>
                              {{ $event->title }}
                            </span>
                          @if( $_user->ableTo('calendar.events_list', $permissions) )</a>@endif
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
        </section>

        <section class="mt-5">
          <div class="card">
            <div class="card-header border-bottom d-flex align-items-center">
              <i class="ti ti-device-desktop-analytics ti-24px"></i>
              <h5 class="card-title mx-3">{{ __('dashboard.Your Judging List') }}</h5>
            </div>
            <div class="card-body">
              @if (count($csteps) > 0)
                @foreach ($csteps as $step)
                  <div class="mt-6">
                    <div class="card-header bg-label-light p-3 mb-2">
                      <h5 class="mb-0">
                        <i class="ti ti-bell ti-24px"></i>
                        <span class="mx-2">
                          {{ $step->title }} - ({{ $step->cycle->title }})
                          - {{ __('dashboard.start from') }} {{ $step->from->format('d-m-Y') ?? '-' }}
                          {{ __('dashboard.to') }} {{ $step->to->format('d-m-Y') ?? '-' }}
                        </span>
                      </h5>
                    </div>
                    <div class="card-body">
                      <p class="card-text">{{ $step->description }}</p>
                      <small class="text-muted">{{ __('dashboard.This Cycle Start from') }}
                        {{ $step->cycle->start->format('d-m-Y') ?? '-' }} {{ __('dashboard.to') }}
                        {{ $step->cycle->end->format('d-m-Y') ?? '-' }}</small>
                    </div>
                    <div class="table-responsive">
                      <table class="table">
                        <thead>
                          <tr>
                            <th>{{ __('dashboard.Company') }}</th>
                            <th>{{ __('dashboard.User/Title') }}</th>
                            <th>{{ __('dashboard.Screening completed') }}</th>
                            <th>{{ __('dashboard.AVG degree') }}</th>
                          </tr>
                        </thead>
                        <tbody>
                          @php
                            $form = \App\Step::where('cycle_id', $step->cycle_id)
                              ->where('stype', 1)
                              ->where('arr', $step->arr - 1)
                              ->first();

                            $groups = App\Screening::where('user_id', Auth::id())
                              ->where('step_id', $step->id)
                              ->pluck('users');
                            $groups = json_decode('[' . str_replace(['[', ']'], ' ', json_encode($groups)) . ']');
                            if ($mysteps->where('step_id', $step->id)->pluck('panel')->first() == 0) {
                              $companies = \App\Company::where('step', $step->id)->get();
                            } else {
                              $companies = \App\Company::where('step', $step->id)
                                ->whereIN('id', $groups ?? [])
                                ->get();
                            }
                          @endphp
                          @foreach ($companies as $company)
                            <tr>
                              <td>
                                <div class="d-flex align-items-center">
                                  <img src="{{ $company->logo_url }}" alt="avatar" class="rounded-circle me-2" height="40" width="40">
                                  @if( $_user->ableTo('screening.show', $permissions) )<a href="/dashboard/screening/{{ $form->id }}/{{ $company->id }}" class="text-decoration-none"> @endif
                                    {{ $company->name }}
                                  @if( $_user->ableTo('screening.show', $permissions) ) </a> @endif
                                </div>
                              </td>
                              <td>
                                <div class="d-flex align-items-center">
                                  <img src="{{ $company->user->image_url }}" alt="avatar" class="rounded-circle me-2" height="40" width="40">
                                  <div>
                                    <h6 class="mb-0">{{ $company->user->name }}</h6>
                                    <small class="text-muted">{{ $company->user->title }}</small>
                                  </div>
                                </div>
                              </td>
                              @php
                                $weight = $company->companyWeight($company->id, $form->id, Auth::id());
                                $judgepercentage = $company->companyJudge($company->id, $form->id, Auth::id());
                              @endphp
                              <td>
                                <div class="progress">
                                  <div class="progress-bar bg-{{ $judgepercentage < 50 ? 'danger' : ($judgepercentage < 75 ? 'warning' : 'success') }}" role="progressbar" style="width: {{ $judgepercentage }}%;" aria-valuenow="{{ $judgepercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                              </td>
                              <td>
                                <span class="badge bg-{{ $weight < 25 ? 'danger' : ($weight < 50 ? 'warning' : 'success') }}">{{ $weight }}%</span>
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                @endforeach
              @else
                <div class="d-flex flex-column justify-content-center align-items-center p-5">
                  <img src="/icons/not-found.svg" width="130" height="130" alt="No screening started yet">
                  <h6 class="text-center text-secondary mt-3">{{ __('dashboard.No screening started yet') }}</h6>
                </div>
              @endif
            </div>
          </div>
        </section>

        <section class="mt-5">
          <div class="card">
            <div class="card-header border-bottom d-flex align-items-center">
              <i class="ti ti-device-laptop ti-24px"></i>
              <h5 class="card-title mx-3">{{ __('dashboard.Cycle final screening') }}</h4>
            </div>
            <div class="card-body">
              @php
                $today = date('Y-m-d');
                    $finalScreenings = App\Finalscreening::whereJsonContains('judges', strval(Auth::id())) ->with('step')->get();
                  $stepIds = $finalScreenings->pluck('step_id')->unique()->toArray();
                    $csteps = App\Step::where('stype', 5)
                      ->whereIn('id', $stepIds) 
                      ->get();
                      $finalScreeningsByStep = $finalScreenings->groupBy('step_id');
              @endphp
              @if (count($fcompanies) > 0)
                @foreach ($csteps as $step)
                @php
                $fcompanies = $finalScreeningsByStep[$step->id] ?? collect();
               
                @endphp
                  <div class="mt-4">
                    <div class="card-header bg-label-light p-3 mb-2">
                      <h5 class="mb-0">
                        <i class="ti ti-bell ti-24px"></i>
                        <span class="mx-2">
                          {{ $step->title }} - ({{ $step->cycle->title }})
                          - {{ __('dashboard.start from') }} {{ $step->from->format('d-m-Y') ?? '-' }}
                          {{ __('dashboard.to') }} {{ $step->to->format('d-m-Y') ?? '-' }}
                        </span>
                      </h5>
                    </div>
                    <div class="card-body">
                      <p class="card-text">{{ $step->description }}</p>
                      <small class="text-muted">{{ __('dashboard.This Cycle Start from') }}
                        {{ $step->cycle->start->format('d-m-Y') ?? '-' }} {{ __('dashboard.to') }}
                        {{ $step->cycle->end->format('d-m-Y') ?? '-' }}</small>
                    </div>
                    <div class="table-responsive">
                      <table class="table">
                        <thead>
                          <tr>
                            <th>{{ __('dashboard.Company') }}</th>
                            <th>{{ __('dashboard.User/Title') }}</th>
                            <th>{{ __('dashboard.Screening completed') }}</th>
                            <th>{{ __('dashboard.Meeting') }}</th>
                            <th>{{ __('dashboard.Actions') }}</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($fcompanies as $fcompany)
                            <tr>
                              <td>
                                <div class="d-flex align-items-center">
                                  <img src="{{ optional($fcompany->company)->logo_url }}" alt="avatar" class="rounded-circle me-2" height="40" width="40">
                                  @if( $_user->ableTo('company.profile_view', $permissions) )<a href="/dashboard/companyProfile/{{ $fcompany->company_id }}" class="text-decoration-none">@endif
                                    {{ optional($fcompany->company)->name }}
                                  @if( $_user->ableTo('company.profile_view', $permissions) )</a>@endif
                                </div>
                              </td>
                              <td>
                                <div class="d-flex align-items-center">
                                  <img src="{{ $fcompany->company->user->image_url ?? '' }}" alt="avatar" class="rounded-circle me-2" height="40" width="40">
                                  <div>
                                    <h6 class="mb-0">{{ $fcompany->company->user->name ?? '' }}</h6>
                                    <small class="text-muted">{{ $fcompany->company->user->title ?? '' }}</small>
                                  </div>
                                </div>
                              </td>
                              <td>
                                @php
                                
                                $formFieldsCount = App\Formfield::where('step_id', $step->id)->where('builder_forms_id', $step->data)->count();
                                $finalDataCount =  App\FinalData::where('step_id', $step->id)->where('judge_id', Auth::id())->where('company_id', $fcompany->company_id)->whereNotNull('answer')->count();
                              
                                $judgepercentage = $formFieldsCount > 0 ? ($finalDataCount / $formFieldsCount) * 100 : 0;
                                
                                @endphp
                                <div class="progress">
                                  <div class="progress-bar bg-{{ $judgepercentage < 50 ? 'danger' : ($judgepercentage < 75 ? 'warning' : 'success') }}" role="progressbar" style="width: {{ $judgepercentage }}%;" aria-valuenow="{{ $judgepercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                
                                </div>
                                {{ $judgepercentage . ' %'}}
                              </td>
                              <td>
                                {{ $fcompany->datetime ?? __('dashboard.Not added yet') }}
                              </td>
                              <td>
                                @if ($fcompany->join_url)
                                  <a href="{{ $fcompany->join_url }}" target="_blank" class="btn btn-primary btn-sm">
                                    <i class="ti ti-user"></i> {{ __('dashboard.Join') }}
                                  </a>
                                  @if( $_user->ableTo('screening.form_view', $permissions) )
                                  <a href="/dashboard/final/{{ $step->id }}/{{ $fcompany->company_id }}" target="_blank" class="btn btn-success btn-sm">
                                    <i class="ti ti-file"></i> {{ __('dashboard.Open form') }}
                                  </a>
                                  @endif
                                @else
                                  {{ __('dashboard.Not started') }}
                                @endif
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                @endforeach
              @else
                <div class="d-flex flex-column justify-content-center align-items-center p-5">
                  <img src="/icons/not-found.svg" width="130" height="130" alt="No screening started yet">
                  <h6 class="text-center text-secondary mt-3">{{ __('dashboard.No Final screening started yet') }}</h6>
                </div>
              @endif
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
  <!-- / Content Wrapper -->
@stop

@section('pagescripts')
  <script>
    $(document).ready(function() {
      $('.table').DataTable({
        "responsive": true,
        "searching": false,
        "lengthChange": false,
        "paging": false,
        "bInfo": false,
        "columnDefs": [{
          "orderable": false,
          "targets": [2]
        }, ]
      });
    });
  </script>
@stop
