@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        <!-- Zero configuration table -->
        <section id="basic-datatable">
          <div class="row">
            @if (count($sessions) > 0)
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4 class="content-title">{{ __('admin.Mentorship requests') }}</h4>
                  </div>
                  <div class="card-content">
                    <div class="card-body card-dashboard">
                      <div class="table-responsive">
                        <table class="table zero-configuration">
                          <thead class="table-light">
                            <tr>
                              <th>#</th>
                              <th>{{ __('admin.Mentor') }}</th>
                              <th>{{ __('admin.Date') }}</th>
                              <th>{{ __('admin.Notes') }}</th>
                              <th>{{ __('admin.Rate') }}</th>
                              <th>{{ __('dashboard.Meeting Link') }}</th>
                              {{--                                                        <th>{{__('mentor.mentorshipTasks.all')}}</th> --}}
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($sessions as $n => $session)
                              <tr>
                                <td>{{ $n + 1 }}</td>
                                <td>

                                  <div class="d-flex align-items-center">
                                    <a class="media-left">
                                      <img src="{{ $session->mentor?->image_url }}" alt="avatar" class="rounded-circle me-3"
                                        height="40" width="40">
                                    </a>
                                    <div class="media-body">

                                      <a href="{{ route('mentors.profile_view', $session->mentor->id ?? '0') }}">

                                        <h6 class="media-heading mb-0 text-nowrap">{{ $session->mentor?->{'first_name_' . $l} }}
                                          {{ $session->mentor?->{'last_name_' . $l} }}</h6>
                                        <span class="font-small-2 text-nowrap">{{ $session->mentor?->{'title_' . $l} }}</span>
                                      </a>
                                    </div>
                                  </div>

                                </td>
                                <td>{{ $session->zoom_date }} ({{ $session->period }}M)</td>
                                <td>
                                  <p class="line-clamp-3" style="max-width: 300px;">
                                    {{ $session->admin_comment ?? $session->comment }}
                                  </p>
                                </td>


                                <td>
                                  @if ($session->rating)
                                    <span title="{{ $session->rate_comment }}">
                                      {!! str_repeat('<i class="bx bxs-star" style="color: #fc960f;"></i>', $session->rating) !!}
                                    </span>
                                  @endif
                                </td>
                                @if($session->zoom_date > now())
                                @if ($session->approved == 'Yes')
                                  <td class="text-success py-1">
                                    @if ($session->join_url)
                                      <a href="{{ $session->join_url }}" target="_blank">
                                        <button title="Join meeting" type="button"
                                          class="btn btn-icon rounded-circle btn-label-primary">
                                          <i class="ti ti-brand-zoom"></i></button></a>
                                    @endif
                                  @elseif($session->approved == 'No')
                                  <td class="text-danger py-1">
                                    {{ __('No') }}
                                  @else
                                  <td class="text-warning py-1">
                                    {!! '<span class="warning">' . __('Waiting') . '</span>' !!}
                                @endif
                                  </td>
                                @else
                                <td class="text-danger py-1">
                                    {{ __('admin.expired') }}
                                  </td>
                                @endif
                                
                                {{--                                                                <td class="text-center"> --}}
                                {{--                                                                    <a href="{{ route('startup.mentorshipTasks.index',['mentorship_id'=>$session->id]) }}" title="{{ __('mentor.mentorshipTasks.tasks') }}" class="btn btn-icon rounded-circle btn-info"> --}}
                                {{--                                                                        <i class="bx bxs-tag"></i> {{ __('mentor.mentorshipTasks.tasks') }}</a> --}}
                                {{--                                                                </td> --}}
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
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
