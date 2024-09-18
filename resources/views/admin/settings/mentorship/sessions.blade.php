@extends('layouts.admin')


@section('style')
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
  <style>
    #mob-extract-button {
      display: flex;
      justify-content: flex-end;
    }
    
    @media screen and (max-width: 768px) {
      #mob {
        margin-top: 10px;
      }
      #mob-extract-button {
        justify-content: flex-start;
        width: 100%;
      }
    }
  </style>
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
  <style>
    #mob-extract-button {
      display: flex;
      justify-content: flex-end;
    }
    
    @media screen and (max-width: 768px) {
      #mob {
        margin-top: 10px;
      }
      #mob-extract-button {
        justify-content: flex-start;
        width: 100%;
      }
    }
  </style>
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-header row">
        {{-- <div class="content-header-left col-12 mb-2 mt-1">
          <div class="row breadcrumbs-top">
            <div class="col-12">
              <h5 class="content-header-title float-left pr-1 mb-0">{{ __('cycles.Mentorship requests') }}</h5>
              <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb p-0 mb-0">
                  <li class="breadcrumb-item"><a href="/dashboard"><i class="ti ti-home-alt"></i></a>
                  </li>
                  <li class="breadcrumb-item active">

                    @if (Request()->show == 'all')
                      {{ __('cycles.All requests') }}
                    @elseif(!Request()->show)
                      {{ __('cycles.New requests only') }}
                    @elseif(Request()->show == 'approved')
                      {{ __('cycles.approved requests') }}
                    @elseif(Request()->show == 'disapproved')
                      {{ __('cycles.disapproved requests') }}
                    @endif

                  </li>
                </ol>
              </div>
            </div>
          </div>
        </div> --}}
      </div>
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible" role="alert">
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible" role="alert">
              {{ __('admin.' . session()->get('message2')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        </section>
        <section class="row">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
              <div class="card-header">
                <div class="card-title">
                  {{-- @if (Request()->show) --}}
                  <!-- إدخال تاريخ النهاية -->

                  <!-- الروابط المعدلة لإرسال التاريخ -->
                  <div onclick="redirect('all')"
                    class="btn btn-{{ request()->show == 'all' ? 'primary' : 'label-secondary' }}">
                    {{ __('cycles.All requests') }}
                  </div>

                  <div href="#" onclick="redirect('')"
                    class="btn btn-{{ !request()->show ? 'primary' : 'label-secondary' }}">
                    {{ __('cycles.New requests only') }}
                  </div>

                  <div href="#" onclick="redirect('approved')" id="mob"
                    class="btn btn-{{ request()->show == 'approved' ? 'primary' : 'label-secondary' }}">

                    {{ __('cycles.approved requests') }}
                  </div>

                  <div href="#" onclick="redirect('disapproved')" id="mob"
                    class="btn btn-{{ request()->show == 'disapproved' ? 'primary' : 'label-secondary' }}">
                    {{ __('cycles.disapproved requests') }}
                  </div>
                  {{-- @else --}}
                  {{-- @endif --}}
                </div>
              </div>

              <div class="card-body">
                <div class="card-text">
                  <div class="table-responsive">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">

                      <div class="d-flex align-items-center">
                        <!-- إدخال تاريخ البداية -->
                        <!-- we get value from Select box Ely fo2 class name stepsExcel -->
                        <div class="col-md-3" style="display: inline-block">
                          <input type="text" class="datewithouttime form-control" id="start_date"
                            value="{{ request('start_date') }}" name="start_date" placeholder="Start Date">
                        </div>
                        <div class="col-md-3 mx-2" style="display: inline-block">
                          <input type="text" class="datewithouttime form-control" id="end_date"
                            value="{{ request('end_date') }}" name="end_date" placeholder="End Date">
                        </div>
                        <div class="col-md-3" style="display: inline-block">
                          <select class="form-control " id="showValueSelect">
                            <option value="all" {{ request()->show == 'all' ? 'selected' : '' }}>
                              {{ __('cycles.All requests') }}</option>
                            <option value="" {{ !request()->show ? 'selected' : '' }}>
                              {{ __('cycles.New requests only') }}</option>
                            <option value="approved" {{ request()->show == 'approved' ? 'selected' : '' }}>
                              {{ __('cycles.approved requests') }}</option>
                            <option value="disapproved" {{ request()->show == 'disapproved' ? 'selected' : '' }}>
                              {{ __('cycles.disapproved requests') }}</option>
                          </select>
                        </div>
                        <div class="btn btn-label-primary mx-3" id="searchBot">
                          <i class="ti ti-search"></i>
                          <span class="mx-2">{{ __('cycles.search') }} </span>
                        </div>
                      </div>
                      <div id="mob-extract-button">
                        <div class="btn btn-label-secondary ExportExcelMentor mx-8" id="mob">{{ __('cycles.Export') }}</div>
                      </div>
                    </div>


                    <input type="hidden" class="Request" value="{{ Request()->show }}">

                    <table class="table mt-3" id="userstable">
                      <thead class="bg-label-light">
                        <tr>
                          <th>{{ __('cycles.Company') }}</th>
                          <th>{{ __('cycles.User/Title') }}</th>
                          <th>{{ __('cycles.Mentor') }}</th>
                          <th>{{ __('cycles.Rate/Comment') }}</th>
                          <th>{{ __('cycles.Date/Time') }}</th>
                          <th>{{ __('cycles.Approved') }}</th>
                          <th>{{ __('cycles.Done') }}</th>
                          <th>{{ __('cycles.Actions') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($sessions as $session)
                          <tr>
                            <td>
                              <div class="d-flex align-items-center">
                                <a class="media-left" href="/dashboard/companyProfile/{{ $session->user->company->id }}">
                                  <img src="{{ $session->user->company->logo_url }}" alt="avatar"
                                    class="rounded-circle" height="40" width="40">
                                </a>
                                <a href="/dashboard/companyProfile/{{ $session->user->company->id }}">
                                  <div class="d-flex align-items-center">
                                    {{ $session->user->company->{'name_' . $l} }}
                                  </div>
                                </a>
                              </div>
                            </td>
                            <td>
                              <div class="d-flex align-items-center">
                                <a class="media-left mr-50"
                                  href="/dashboard/users/{{ $session->user->company->user->id }}/edit">
                                  <img src="{{ $session->user->image_url }}" alt="avatar" class="rounded-circle"
                                    height="40" width="40">
                                </a>
                                <a href="/dashboard/users/{{ $session->user_id }}/edit">
                                  <div class="mx-2">
                                    <h6 class="media-heading mb-0">{{ $session->user->{'first_name_' . $l} }}
                                      {{ $session->user->{'last_name_' . $l} }}</h6>
                                    <span class="font-small-2">{{ $session->user->{'title_' . $l} }}</span>
                                  </div>
                                </a>
                              </div>
                            </td>
                            <td>
                              <div style="cursor: pointer;" class="chip mr-1"
                                onclick="mentor({{ $session->mentor_id }},'{{ $session->mentor->{'first_name_' . $l} . ' ' . $session->mentor->{'last_name_' . $l} }}','{{ $session->mentor->mrating() }}')">
                                <div class="d-flex align-items-center">
                                  <div class="avatar">
                                    <img src="{{ $session->mentor->image_url }}" alt="pic" class="rounded-circle"
                                      height="40" width="40" />
                                  </div>
                                  <span class="mx-2">
                                    {{ $session->mentor->name }}
                                  </span>
                                </div>
                              </div>
                            </td>
                            <td>
                              <div style="width: 60px;">
                                <small>{!! $session->rate_comment
                                  ? str_repeat('<i class="ti tis-star" style="color: #fc960f;"></i>', $session->rating) .
                                      '<br>' .
                                      $session->rate_comment
                                  : $session->comment !!}
                                </small>
                              </div>
                            </td>
                            <td>
                              <small>
                                {{ $session->zoom_date }}
                                {{-- <br> --}}
                                ({{ $session->period }}M)
                              </small>
                            </td>
                            <td>
                              <div class="d-flex align-items-center" style="width: 30px;">
                                {!! __($session->approved) ?? '<span class="warning">' . __('Waiting') . '</span>' !!}
                              </div>
                            </td>
                            <td>
                              <div style="width: 30px;">
                                {{ $session->done ? __('Yes') : __('No') }}
                              </div>
                            </td>
                            <td>
                              <button title="Edit" onclick="edit({{ $session->id }})" type="button"
                                class="btn btn-icon rounded-circle btn-label-info">
                                <i class="ti ti-edit"></i></button>
                              <button title="Delete" onclick="deleteme({{ $session->id }})" type="button"
                                class="btn btn-icon rounded-circle btn-label-danger">
                                <i class="ti ti-trash"></i></button>
                              @if (!$session->approved)
                                <button title="Approve" onclick="approve({{ $session->id }},'Yes')" type="button"
                                  class="btn btn-icon rounded-circle btn-label-success">
                                  <i class="ti ti-check"></i></button>
                                <button title="Dispprove" onclick="approve({{ $session->id }}, 'No')" type="button"
                                  class="btn btn-icon rounded-circle btn-label-danger">
                                  <i class="ti ti-x"></i></button>
                              @endif
                              @if ($session->join_url)
                                <a href="{{ $session->start_url }}" target="_blank">
                                  <button title="Start Meeting" type="button"
                                    class="btn btn-icon rounded-circle btn-label-secondary">
                                    <i class="ti ti-device-computer-camera"></i></button></a>
                                <a href="{{ $session->join_url }}" target="_blank">
                                  <button title="Join only" type="button"
                                    class="btn btn-icon rounded-circle btn-label-primary">
                                    <i class="ti ti-user"></i></button></a>
                              @endif
                              @if ($session->fileurl)
                                <a href="/dashboard/private/{{ $session->fileurl }}" target="_blank"
                                  title="Download attched" class="btn btn-icon rounded-circle btn-label-primary">
                                  <i class="ti ti-download"></i></a>
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
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
              <div id="mentors" style="display: none;">
                <div class="card-header">
                  <h4 class="card-title"><b id="name"></b> {{ __('cycles.Schedule') }}: </h4>
                </div>
                <div class="table-responsive">
                  <table class="table" id="mentorstable">
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
            <input type="hidden" name="mentorid" value="0" id="mentorid">
            <input type="hidden" name="userid" value="0" id="userid">
            <div class="form-group mt-4" id="showdates">
              <label class="mb-2">{{ __('cycles.Date/Time') }}:</label>
              <input class='date width-200 form-control' type="text" id="sdate" name="date" value=""
                required>
            </div>
            <div class="form-group mt-4">
              <label class="mb-2">{{ __('cycles.Meeting period') }}:</label>
              <select name="period" class="select form-control" id="period">
                <option value="30">30 {{ __('cycles.Minutes') }}</option>
                <option value="60">1 {{ __('cycles.Hour') }}</option>
                <option value="120">2 {{ __('cycles.Hours') }}</option>
                <option value="180">3 {{ __('cycles.Hours') }}</option>
                <option value="240">4 {{ __('cycles.Hours') }}</option>
                <option value="300">5 {{ __('cycles.Hours') }}</option>
              </select>
            </div>
            <div class="form-group mt-4">
              <label class="mb-2">{{ __('cycles.Add a session note') }}:</label>
              <textarea class="form-control" id="notes" name='notes' rows="4"
                placeholder="{{ __('cycles.Write your note here') }}"></textarea>
            </div>
            <div class="checkbox mt-4">
              <label for="checkbox1">{{ __('cycles.Approve & notify users') }}.</label>
              <input type="checkbox" class="checkbox-input" id="checkbox1" name="approved">
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-label-secondary ml-1">
              <i class="ti ti-check d-block d-sm-none"></i>
              <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
            </button>
            <button type="submit" class="btn btn-primary ml-1">
              <i class="ti ti-check d-block d-sm-none"></i>
              <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

@stop
@section('pagescripts')
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <script>
    $(document).ready(function() {
      $('.table').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": false,
        "paging": true,
        "bInfo": false,
        "order": [], // ترتيب الجدول حسب العمود الأول تصاعديًا
        "columnDefs": [

          {
            "orderable": true,
            "targets": 7
          }, // تفعيل الفرز للعمود الأول فقط
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
    $('.date').datetimepicker({
      timepicker: true,
      format: 'Y-m-d H:i'
    });
    $('.datewithouttime').datetimepicker({
      format: 'Y-m-d'
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
          $('#sdate').val(response.zoom_date);
          $('#period').val(response.period);
          $('#notes').val(response.notes);
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

      const showValue = document.getElementById('showValueSelect').value;

      // التحقق من وجود تواريخ والتحقق من تنسيقها لضمان التوافق
      let url = `/dashboard/mentorship/sessions?show=${showValue}`;

      // إذا كانت start_date و end_date موجودة، أضفها إلى الرابط
      if (startDate && endDate) {
        const formattedStartDate = formatDate(startDate);
        const formattedEndDate = formatDate(endDate);
        url += `&start_date=${encodeURIComponent(formattedStartDate)}&end_date=${encodeURIComponent(formattedEndDate)}`;
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
      // alert('Please Wait ! Sending Request ?')
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
      Swal.fire({
          title: "{{ __('admin.SureDelete') }}",
          text: "{{ __('admin.Recover') }}",
          type: "warning",
          showCancelButton: true,
          confirmButtonClass: "btn-label-danger",
          confirmButtonText: "{{ __('admin.DYes') }}",
          cancelButtonText: "{{ __('admin.No') }}!",
        })
        .then((result) => {
          if (result.value) {
            $.post(url, {
                '_method': 'POST',
                '_token': token
              },
              function(response) {
                Swal.fire(
                  '{{ __('admin.Deleted') }}!',
                  '{{ __('admin.Session Deleted') }}.',
                  'success');
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
