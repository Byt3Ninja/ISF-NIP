@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
      {{--
       <h5 class="content-header-title float-left pr-1 mb-0">
        {{ __('cycles.all online training steps') }}
      </h5> 
      --}}
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
          <div 
            class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
            role="alert"
          >
            {{ __('admin.' . session()->get('message')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
      @endif
      @php
      $totalAttendance = $attendanceData->count();
      $attendCount = $attendanceData->where('attendance', 1)->count();
      $notAttendCount = $attendanceData->where('attendance', 0)->count();
      $attendPercentage = $totalAttendance > 0 ? ($attendCount / $totalAttendance) * 100 : 0;
      $notAttendPercentage = $totalAttendance > 0 ? ($notAttendCount / $totalAttendance) * 100 : 0;
      @endphp
        </section>
          <section class="row">
            <div class="col-xl-12 col-md-12 col-sm-12">
              <div class="card">
                <div class="card-header">
                </div>
                <div class="card-body">
                  <div class="card-text">
                    <div class="table-responsive">
                      <table class="table mb-0" id="trainingtable">
                        <thead class="bg-label-light">
                          <tr>
                            <th>#</th>
                            <th>{{ __('admin.Traning_title') }}</th>
                            <th>{{ __('admin.Trainer_name') }}</th>
                            <th>{{ __('admin.Program_Name') }}</th>
                            <th>{{ __('admin.StartUp_name') }}</th>                            
                            <th>{{ __('admin.Attendance') }} ( {{ round($attendPercentage, 2) . "%" }} )</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($attendanceData as $key => $attendance)
                            <tr>
                              <td>{{ $key + 1 }}</td>
                              <td>{{ $attendance->trainingSession->title  ?? 'غير متوفر' }}
                                <br>
                                {{-- GV-9566: timestamps shown in the viewer's own timezone --}}
                                {{ __('admin.Start') }}:( {{ optional($attendance->trainingSession?->startsAt($_user))->format('Y-m-d h:i A') ?? 'غير متوفر' }} ) 
                                <br>
                                {{ __('admin.End') }}:( {{ optional($attendance->trainingSession?->endsAt($_user))->format('Y-m-d h:i A') ?? 'غير متوفر' }} )
                              </td>
                              <td>{{ $attendance->trainingSession->trainer_name ?? 'غير متوفر' }}</td>
                              <td>{{ $attendance->cycle->title ?? 'غير متوفر' }}</td>
                              <td>{{ $attendance->company->name ?? 'غير متوفر' }}</td>
                              @if ($attendance->attendance == 1)
                              <td class="text-center text-success">{{ $attendance->created_at ?? 'غير متوفر' }}</td>
                              @else
                              {{-- GV-9566: compare the instant; the raw timestamp string vs now() always read as past --}}
                              @if (optional($attendance->trainingSession?->endsAt())->isPast())
                              <td class="text-center text-danger">{{ __('admin.Absent') }}</td>
                              @else
                              <td class="text-center text-warning">{{ __('admin.Pending') }}</td>
                              @endif
                              @endif
                              <td> </td>
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
    </div>
  </div>



@stop
@section('pagescripts')
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#trainingtable').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": false,
        "paging": true,
        "bInfo": false,
        "columnDefs": [{
          "orderable": false,
          "targets": [6]
        }, ],
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
  </script>
  <script>
 




    function Attendance(training){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/training/Attendance-offline",
        data: {
          "id": training,
        },
        success: function(response) {
          let attendanceHtml = "";
                response.attendances.forEach(function(att) {
                  attendanceHtml += `<tr>
        <td class="text-center">${att?.training_session?.title ?? 'غير متوفر'}</td> 
        <td class="text-center">${att?.training_session?.trainer_name ?? 'غير متوفر'}</td> 
        <td class="text-center">${att?.cycle?.title ?? 'غير متوفر'}</td> 
        <td class="text-center">${att?.user?.first_name_en +" "+att?.user?.last_name_en +" <br> ( "+ att?.company?.name_en +" ) " ?? 'غير متوفر'}</td> 
        <td class="text-center text-success">${formatDateTime(att?.created_at) ?? 'غير متوفر'}</td>
    </tr>`;
                });

                $(".Attendance_body").html(attendanceHtml);

          $('#Attendance').modal('show');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }



  </script>
@stop
