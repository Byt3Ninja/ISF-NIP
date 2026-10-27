{{-- <h5 class="content-header-title float-left pr-1 mb-0">{{__('settings.eventsettings')}}</h5> --}}
@extends('layouts.admin')
@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-overlay"></div>

      <div class="invoice-create-btn mb-4">
        <a href="/dashboard/addevent" class="btn btn-primary glow"><i
            class="ti ti-plus me-2"></i>{{ __('settings.AddNew') }}</a>
        <a href="/dashboard/calendar" class="btn btn-success glow">
          <i class="ti ti-calendar me-2"></i>
          {{ __('admin.Calendar') }}
        </a>
      </div>
      <div class="content-body">
        <section id="basic-datatable">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible mb-2" role="alert">
              <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <div class="d-flex align-items-center">
                <i class="ti ti-check"></i>
                <span>
                  {{ __('admin.' . session()->get('message')) }}
                </span>
              </div>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible mb-2" role="alert">
              <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <div class="d-flex align-items-center">
                <i class="ti ti-error"></i>
                <span>
                  {{ __('admin.' . session()->get('message2')) }}
                </span>
              </div>
            </div>
          @endif

          @if (count($getevents) > 0)
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4 class="card-title">
                      {{ $l == 'en' ? 'Events' : 'الفعاليات' }}
                    </h4>
                  </div>
                  <div class="card-content">
                    <div class="card-body card-dashboard">
                      <div class="table-responsive">
                        <table class="table zero-configuration">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th>{{ __('settings.Title') }}</th>
                              <th>{{ __('settings.Registered') }}</th>
                              <th>{{ __('settings.URL') }}</th>
                              <th>{{ __('settings.DownEditDelete') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($getevents as $event)
                              <tr style="font-weight:{{ $event->published ? 'bold' : 'normal' }};">
                                <td>{{ $event->id }}</td>
                                <td>{{ $event->{'title_' . $l} }}</td>
                                <td>{{ App\EventsUser::where('event_id', $event->id)->count() }}</td>
                                <td>{!! $event->published
                                    ? '<a target="_blank" href="/event/' . $event->id . '">/event/' . $event->id . '</a>'
                                    : __('admin.NotPublished') !!}</td>
                                <td>
                                  <a class="btn btn-sm btn-label-secondary" href="/reports/events/{{ $event->id }}"><i
                                      class="ti ti-download text-secondary"> </i></a> |
                                  <a class="btn btn-sm btn-label-info" href="/dashboard/editevent/{{ $event->id }}"><i
                                      class="ti ti-edit text-info"> </i></a>
                                  |
                                  @include('partials.forms.confirm-action', ['formId' => 'delete-event-' . $event->id, 'action' => route('events.delete', $event->id), 'message' => 'Are you sure, delete event & all its registered users?', 'label' => '<i class="ti ti-trash text-danger"> </i>', 'class' => 'btn btn-sm btn-label-danger'])
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
          @else
            <h3>
              {{ __('settings.NoData') }}
            </h3>
          @endif
      </div>
      </section>
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
  <script src="/app-assets/js/scripts/modal/components-modal.js"></script>
@stop
