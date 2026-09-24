@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
      <div class="content-body">
        <!-- Zero configuration table -->
        <section id="basic-datatable">
          <div class="row">
            @if (count($sessions) > 0)
              <div class="col-12">
                <div class="card">
                  <div class="card-header d-flex justify-content-between flex-wrap align-items-center border-bottom">
                    <h5 class="card-title mb-0">{{ __('startup.mentorship_requests.label') }}</h5>
                  </div>
                  <div class="card-content">
                    <div class="card-datatable table-responsive">
                      <table class="table zero-configuration">
                        <thead class="table-light">
                          <tr>
                            <th>#</th>
                            <th>{{ __('admin.Mentor') }}</th>
                            <th>{{ __('admin.Date') }}</th>
                            <th>{{ __('admin.Comment') }}</th>
                            <th>{{ __('admin.Rate') }}</th>
                            <th>{{ __('mentor.session_type') }}</th>
                            <th>{{ __('startup.mentorship_requests.Meeting_Link/Map') }}</th>
                            {{-- <th>{{__('mentor.mentorshipTasks.all')}}</th> --}}
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($sessions as $n => $session)
                            <tr>
                              <td>{{ $n + 1 }}</td>
                              <td>
                                <a 
                                  href="{{ $session->mentor->id ? route('mentors.profile_view', $session->mentor->id) : 'javascript:void(0)' }}" 
                                  class="d-flex align-items-center gap-2"
                                >
                                  <img 
                                    src="{{ $session->mentor?->image_url ?? '/icons/account.svg' }}" 
                                    alt="avatar" 
                                    class="rounded-circle"
                                    height="40" width="40"
                                  />
                                  <div>
                                    <h6 class="mb-0 text-nowrap">
                                      {{ $session->mentor?->{'first_name_' . $l} }}
                                      {{ $session->mentor?->{'last_name_' . $l} }}
                                    </h6>
                                    <small class="text-muted">{{ $session->mentor?->{'title_' . $l} }}</small>
                                  </div>
                                </a>
                              </td>
                              <td>{{ optional( \App\Services\TimezoneService::forViewer( \App\Services\TimezoneService::wallClock( $session->zoom_date, \App\Services\TimezoneService::platform( $session->organization_id ?? null ) ), $_user ) )->format('Y-m-d H:i') }} ({{ $session->period }}M)</td>
                              <td>
                                @php
                                  $comment = $session->admin_comment ?? $session->comment;
                                @endphp
                                @if($comment)
                                  <p class="small text-muted d-block">{!! $session->comment !!}</p>
                                @else
                                  <small class="text-muted block">{{ __('admin.No comment provided') }}</small>
                                @endif
                              </td>
                              <td>
                                <div class="d-flex align-items-center gap-1 mb-1">
                                  <strong>{{ number_format($session->rating ?? 0, 1) }}</strong>
                                  <i class="ti ti-star-filled ti-16px text-warning"></i>
                                </div>
                              </td>
                              <td style="max-width: 150px;">
                                @php
                                  $badgeColor = 'secondary';
                                  $badgeIcon = 'screen-share';
                                  if ($session->session_type == 'Online') {
                                    $badgeColor = 'success';
                                    $badgeIcon = 'video';
                                  } elseif ($session->session_type == 'Offline'){
                                    $badgeColor = 'warning';
                                    $badgeIcon = 'map';
                                  }
                                @endphp
                                <small class="badge bg-label-{{ $badgeColor }}">
                                  <i class="ti ti-{{ $badgeIcon }} ti-16px me-1"></i>
                                  {{ $session->session_type ?? __('admin.All') }}
                                </small>
                              </td>
                              <td>
                                @if (isset($session->session_type) && $session->session_type == 'Online')
                                  @if ($session->approved === 'Yes')
                                    @if ($session->meeting_status === 'before')
                                      <span class="text-warning">
                                          <i class="ti ti-alarm"></i> {{ __('admin.Meeting_will_start_at_time') }}
                                      </span>
                                    @elseif ($session->meeting_status === 'expired')
                                      <small class="text-danger">{{ __('admin.Meeting_Expired') }}</small>
                                    @else
                                      <a href="{{ $session?->join_url }}" target="_blank">
                                        <button type="button" class="btn btn-sm btn-primary">
                                          <i class="ti ti-brand-zoom"></i> {{ __('startup.mentorship_requests.join_now') }}
                                        </button>
                                      </a>
                                    @endif

                                  @elseif ($session->approved === 'No')
                                    <small class="text-muted">{{ __('startup.mentorship_requests.no_link') }}</small>
                                  @else
                                    <small class="text-info">
                                      {{ __('admin.Waiting_for_approval') }}
                                    </small>
                                  @endif
                                @else
                                  <span class="warning">
                                    <a class="btn btn-sm btn-label-success" href="{{ $session->google_map_url ?? '#' }}" target="_blank">
                                      <i class="ti ti-map me-2"></i>
                                      {{ __('admin.Location') }}
                                    </a>
                                    <div class="mt-1">
                                      <small> <i class="fas fa-map-marker-alt"></i> {{ $session->address }} </small>
                                    </div>
                                  </span>
                                @endif
                              </td>

                              {{-- <td class="text-center"> --}}
                              {{--     <a href="{{ route('startup.mentorshipTasks.index',['mentorship_id'=>$session->id]) }}" title="{{ __('mentor.mentorshipTasks.tasks') }}" class="btn btn-icon rounded-circle btn-info"> --}}
                              {{--         <i class="bx bxs-tag"></i> {{ __('mentor.mentorshipTasks.tasks') }}</a> --}}
                              {{-- </td> --}}
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="col-sm-12">
                <div class="card">
                  <div class="card-header border-bottom mb-4">
                    <h4 class="card-title">{{ __('admin.No Requests') }}</h4>
                  </div>
                  <div class="card-body">
                    <a href="/dashboard" class="btn btn-primary glow">{{ __('admin.Back to Dashboard') }}</a>
                  </div>
                </div>
              </div>
            @endif
          </div>
        </section>
      </div>
    </div>
  </div>

@stop
@section('scripts')
  @if ($l == 'ar')
    <script type="text/javascript">
      $(document).ready(function() {
        $('.zero-configuration').DataTable({
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
        });
      });
    </script>
  @endif
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/app-assets/js/scripts/datatables/datatable.js"></script>
@stop
