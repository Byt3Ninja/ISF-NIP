@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify.min.css">
  <link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify-bootstrap.min.css">
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-header">
        <div class="col">
          <h3 class="fw-bold">{{ __('cycles.Mentorship Sessions') }}</h3>
        </div>
      </div>
      <div class="content-body">
        <section id="alerts">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ __('admin.' . session()->get('message2')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        </section>

        <section class="row">
          <div class="col-12">
            <div class="card">
             

              <div class="card-body">
                <div class="row g-3 mb-4">
                  <div class="col-12 col-md-10">
                    <div class="row">
                      <div class="col-12 col-md-6 col-lg-2">
                        <input type="text" class="form-control datewithouttime" id="start_date" value="{{ request('start_date') }}" placeholder="Start Date">
                      </div>
                      <div class="col-12 col-md-6 col-lg-2">
                        <input type="text" class="form-control datewithouttime" id="end_date" value="{{ request('end_date') }}" placeholder="End Date">
                      </div>
                      <div class="col-12 col-md-6 col-lg-3">
                        <select class="form-select select2" id="showValueSelect">
                          <option value="all" {{ request()->show == 'all' ? 'selected' : '' }}>{{ __('cycles.All requests') }}</option>
                          <option value="" {{ !request()->show ? 'selected' : '' }}>{{ __('cycles.New requests only') }}</option>
                          <option value="approved" {{ request()->show == 'approved' ? 'selected' : '' }}>{{ __('cycles.approved requests') }}</option>
                          <option value="disapproved" {{ request()->show == 'disapproved' ? 'selected' : '' }}>{{ __('cycles.disapproved requests') }}</option>
                        </select>
                      </div>
                      
                    </div>
                  </div>
                  
                </div>

                <div class="table-responsive">
                  <table class="table" id="userstable">
                    <thead class="table-light">
                      <tr>
                        <th>{{ __('cycles.Value') }}</th>
                        <th>{{ __('cycles.Active') }}</th>
                        <th>{{ __('cycles.Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($scahedules as $scahedule)
                        <tr>
                          <td>
                            <small>{{ $scahedule->value }} {{ $scahedule->unit }}</small>
                          </td>
                          <td>
                              @if( $scahedule?->is_active == 1 )
                              <span class="badge bg-label-success" >
                              {{ __('admin.Yes') }}
                              </span>
                              @else
                              <span class="badge bg-label-danger" >
                              {{ __('admin.No') }}
                              </span>
                              @endif
                             
                            
                          </td>
                         
                          <td>
                            <div class="btn-group">
                              <button class="me-1 btn-icon rounded-circle btn btn-sm btn-label-info" onclick="edit({{ $session->id }})" title="Edit">
                                <i class="ti ti-edit"></i>
                              </button>
                              <button class="me-1 btn-icon rounded-circle btn btn-sm btn-label-danger" onclick="deleteme({{ $session->id }})" title="Delete">
                                <i class="ti ti-trash"></i>
                              </button>
                              
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
  <!-- END: Content-->
  <div class="modal fade text-left" id="ModifyRequest" tabindex="-1" role="dialog" aria-labelledby="sendrequest"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <form method="post" action="/dashboard/mentorship/modify" enctype="multipart/form-data">
          <div class="modal-header">
            <h4 class="modal-title">{{ __('cycles.Modify mentorship session') }} </h4>
          </div>
          <div class="modal-body">
            @csrf
            <input type="hidden" name="id" value="0" id="sid">
            <div class="form-group mt-4" id="showdates">
              <label class="mb-2">{{ __('cycles.Date/Time') }}:</label>
             
            <div class="form-group mt-4">
              <label class="mb-2">{{ __('cycles.Value') }}:</label>
              <select name="period" class="select form-control" id="period">
                @for ($i = 1; $i <= 24; $i += 24)
                  <option value="{{ $i }}">{{ $i }} {{ __('cycles.Hours') }}</option>
                @endfor
              </select>
            </div>

            <div class="form-group mt-4">
              <label class="mb-2">{{ __('cycles.Unit') }}:</label>
              <select name="unit" class="select form-control" id="unit">
              
                  <option value="hours">{{ __('cycles.Hours') }}</option>
              
              </select>
            </div>
            
          <div class="modal-footer">
            <div class="btn btn-label-secondary ml-1" data-bs-dismiss="modal" aria-label="Close">
              {{-- <i class="ti ti-check"></i> --}}
              <span class="">{{ __('cycles.Close') }}</span>
            </div>
            <button type="submit" class="btn btn-primary ml-1">
              {{-- <i class="ti ti-check"></i> --}}
              <span class="">{{ __('cycles.Submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@stop

@section('scripts')
  <script src="/app-assets/plugins/alertify/alertify.min.js"></script>
@stop

@section('pagescripts')
  <script>
    $(document).on('click', '.show-passcode', function(e) {
      e.preventDefault();
      let passcode = $(this).data('passcode');
      alertify.defaults.transition = 'zoom';
      alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
      alertify.defaults.theme.cancel = 'btn btn-sm btn-info';
      alertify.defaults.theme.input = 'form-control';

      alertify.alert('Meeting Passcode', `The passcode for this meeting is: <strong>${passcode}</strong>`, function() {});
    });
    $(document).ready(function() {
    $('#userstable').DataTable().destroy();
    $('#userstable').DataTable({
    "responsive": true,
    "searching": true,
    "lengthChange": false,
    "paging": true,
    "bInfo": false,
    "order": [],
    "columnDefs": [
      {
        "orderable": false,
        "targets": [0, 1, 2, 3, 4, 5, 6, 7]
      }
    ],
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
    // $('#sdate').flatpickr({
    //   enableTime: true,
    //   dateFormat: "Y-m-d h:i K",
    //   minDate: "today",
    //   time_24hr: false,
    //   allowInput: true,
    //   onClose: function(selectedDates, dateStr, instance) {
    //     // Ensure the date format matches what Carbon expects (Y-m-d h:i a)
    //     if (selectedDates.length > 0) {
    //       const formattedDate = instance.formatDate(selectedDates[0], "Y-m-d h:i K");
    //       instance.setDate(formattedDate, true, "Y-m-d h:i K");
    //     }
    //   }
    // });

    $('.datewithouttime').flatpickr({
      enableTime: false,
      dateFormat: "Y-m-d",
    });

    function edit(id) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/mentorship/session",
        data: {
          "id": id,
        },
        success: function(response) {
          $('#sid').val(id);
          $('#mentorid').val(response.mentor_id);
          $('#userid').val(response.user_id);
          // $('#sdate').val(response.zoom_date);
          $('#sdate').flatpickr({
            enableTime: true,
            dateFormat: "Y-m-d h:i K",
            // minDate: "today",
            time_24hr: false,
            allowInput: true,
            defaultDate: response.zoom_date ? response.zoom_date : '',
            onClose: function(selectedDates, dateStr, instance) {
              // Ensure the date format matches what Carbon expects (Y-m-d h:i a)
              if (selectedDates.length > 0) {
                const formattedDate = instance.formatDate(selectedDates[0], "Y-m-d h:i K");
                instance.setDate(formattedDate, true, "Y-m-d h:i K");
              }
            }
          });
          $('#period').val(response.period);
          $('#notes').val(response.admin_comment);
          $('#ModifyRequest').modal('show');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
    const messages = {
      exportSuccess: "{{ __('cycles.export_willsuccess') }}",
    };

    document.getElementById('searchBot').addEventListener('click', redirectWithDates);

    function redirectWithDates() {
      // الحصول على قيم التواريخ
      const startDate = document.getElementById('start_date').value;
      const endDate = document.getElementById('end_date').value;
      const CycleId = document.getElementById('cycle_id').value;

      const showValue = document.getElementById('showValueSelect').value;

      // التحقق من وجود تواريخ والتحقق من تنسيقها لضمان التوافق
      let url = `/dashboard/mentorship/sessions?show=${showValue}`;

      // إذا كانت start_date و end_date موجودة، أضفها إلى الرابط
      if (startDate && endDate) {
        const formattedStartDate = formatDate(startDate);
        const formattedEndDate = formatDate(endDate);
        url += `&start_date=${encodeURIComponent(formattedStartDate)}&end_date=${encodeURIComponent(formattedEndDate)}`;
      }
      if (CycleId && CycleId !== 'all') {
        url += `&cycle_id=${CycleId}`;
      }

      // إعادة التوجيه إلى الرابط المناسب
      window.location.href = url;
    }

    function redirect(showValue) {


      // التحقق من وجود تواريخ والتحقق من تنسيقها لضمان التوافق
      let url = `/dashboard/mentorship/sessions?show=${showValue}`;

      // إذا كانت start_date و end_date موجودة، أضفها إلى الرابط


      // إعادة التوجيه إلى الرابط المناسب
      window.location.href = url;
    }

    // دالة لتحويل صيغة التاريخ إلى 'YYYY-MM-DD' لضمان الثبات
    function formatDate(date) {
      const dateObj = new Date(date);
      const year = dateObj.getFullYear();
      const month = String(dateObj.getMonth() + 1).padStart(2, '0');
      const day = String(dateObj.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }

    function mentor(id, name, rating) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/mentors/modify",
        data: {
          "id": id,
          "rtype": 5,
        },
        success: function(response) {
          $('#mentorstable tr').remove();
          let rate = '<i class="ti tis-star" style="color: #fc960f;"></i>';
          $('<tr>').html(" <td><b>#</b></td> <td><b>Days</b></td> <td><b>Time</b></td> <td><b>Notes</b></td>")
            .appendTo('#mentorstable');
          $('#name').html(name + "<span>" + rate.repeat(rating) + "</span>");
          if (response.length > 0) {
            $.each(response, function(i, item) {
              let days = '';
              let note = '';
              let time = "From " + item.tfrom + " to " + item.tto;
              if (item.notes) {
                note = item.notes;
              }
              switch (item.type) {
                case 'OneDay':
                  days = item.dfrom;
                  break;
                case 'Weekly':
                  days = 'Weekly (' + item.days + ')';
                  break;
                case 'WorkingDays':
                  days = 'All working days';
                  break;
                case 'Period':
                  days = 'from ' + item.dfrom + ' to ' + item.dto;
                  break;
                case 'Session':
                  days = new Date(item.dfrom);
                  time = 'From ' + days.toLocaleTimeString('pt-BR') + " (" + item.tfrom + "Minutes)";
                  days = days.toLocaleDateString('pt-BR') + " Busy";
                  break;
              }
              let num = i + 1;
              $('<tr>').html("<td>" + num + "</td><td>" + days + "</td><td>" + time + "</td><td>" + note +
                "</td>").appendTo('#mentorstable');
            });
          } else {
            $('<tr>').html("<td colspan='5'> No records saved.</td>").appendTo('#mentorstable');
          }
          $('#mentors').show();
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }

    function approve(id, stat) {
      if(stat == 'Yes'){
      alert('{{ __('admin.Accepting_request') }}')
      }else{
        alert('{{ __('admin.Rejecting_request') }}')
      }
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/mentors/modify",
        data: {
          "id": id,
          "stat": stat,
          'rtype': 6,
        },
        success: function(response) {
          location.reload();
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }

    function deleteme(id) {
      let url = '/dashboard/mentorship/delsession/' + id;
      let token = '{{ csrf_token() }}';
      releaseAlert('warning', {
        title: "{{ __('admin.SureDelete') }}",
        message: "{{ __('admin.Recover') }}",
        confirmText: "{{ __('admin.DYes') }}",
        cancelText: "{{ __('admin.No') }}!",
        confirmColor: 'label'
      })
        .then((ok) => {
          if (ok) {
            $.post(url, {
                '_method': 'POST',
                '_token': token
              },
              function(response) {
                releaseAlert('success', {
                  title: '{{ __('admin.Deleted') }}!',
                  message: '{{ __('admin.Session Deleted') }}.'
                });
                window.location.href = '/dashboard/mentorship/sessions?show=all';
              }).fail(function(error) {
              console.log(error);
            });
          } else {
            console.log('Canceled')
          }
        });
    }
  </script>
@stop
