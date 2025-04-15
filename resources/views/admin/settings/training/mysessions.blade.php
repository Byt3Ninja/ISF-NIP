@extends('layouts.admin')

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
    <div class="content-body">
      <section id="multiple-column-form">
        @if (session()->has('message'))
        <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
          role="alert">
          {{ __('admin.' . session()->get('message')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
      </section>
      @if (count($sessions) > 0)
      <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card px-4">
            <div class="card-header border-bottom mb-4 col-md-10" style="height: 70px;">
              <h4 class="card-title">{{ __('cycles.My training courses & sessions') }}</h4>
            </div>
            <div class="card-body">
              <div class="card-text">
                <div class="table-responsive">
                  <table class="table mb-0" id="trainingtable">
                    <thead class="table-light">
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
                      @foreach ($sessions as $key => $session)
                      <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ __('cycles.' . $session->type) }}</td>
                        <td>{!! $session->dsc
                          ? '<a title="Click to show details" href="" onclick="ShowDetails(' .
                                      $session->id .
                                      ');return false;">' .
                            $session->title .
                            '</a>'
                          : $session->title !!}</td>
                        <td>{{ $session->datetime }}</td>
                        <td>{{ $session->enddatetime ?? '-' }}</td>
                        <td>
                          @if ($session->type == 'sessions')
                          {{ __('cycles.Duration') }}: {{ $session->duration }} {{ __('cycles.Minutes') }} :
                          <a title="Click to join" href="{{ $session->join_url }}" target="_blank">
                            <button title="Join only" type="button"
                              class="btn btn-icon rounded-circle btn-label-primary">
                              <i class="ti ti-brand-zoom"></i></button></a>
                          @elseif ($session->type == 'offline')
                          <b>{{ __('cycles.trainer name') }}:</b>
                          {{ $session->trainer_name }}<br><b>{{ __('cycles.training location') }}:</b>
                          {{ $session->location }} <br>
                          @else
                          <a title="Show course contents" href="/dashboard/training/my/{{ $session->training_Address }}">{{
                            __('cycles.Display course contents') }}
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
      @else
      <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card px-4">
            <div class="card-header border-bottom mb-4">
              <h4 class="card-title">{{ __('cycles.nosubscripe') }}</h4>
            </div>

            <div class="card-body">
              {{ __('cycles.No training sessions added yet') }}, <a href="/dashboard">{{ __('cycles.Back') }}</a>.
            </div>
          </div>
        </div>
      </section>
      @endif
    </div>
  </div>
</div>
<!-- END: Content-->
<div class="modal fade text-left" id="dscModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="dsctitle"> </h4>
      </div>
      <div class="modal-body">
        <p id="dsc">

        </p>
      </div>
    </div>
  </div>
</div>
@stop
@section('pagescripts')
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
<script>
  $(document).ready(function() {
      $('#trainingtable').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": true,
        "paging": true,
        "bInfo": true,
        @if ($l == 'ar')
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
        @endif
      });
    });

    function ShowDetails(training) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/trainings/getdsc/" + training,
        success: function(response) {
          $('#dsctitle').html(response.title);
          $('#dsc').html(response.dsc);
          $('#dscModal').modal('show');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
</script>
@stop