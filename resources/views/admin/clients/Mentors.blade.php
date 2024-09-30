@extends('layouts.admin')

@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.My Dashboard'))

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/css{{ $l == 'ar' ? '-rtl' : '' }}/pages/widgets.css">
  <link rel="stylesheet" type="text/css" href="/app-assets/css{{ $l == 'ar' ? '-rtl' : '' }}/pages/dashboard-ecommerce.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/jquery.rateyo.min.css">
@stop

@section('content')
  @if (!Auth::user()->first_name_ar)
    {!! redirect()->route('my_profile')->with(['msg' => 'FailedStore', 'class' => 'danger']) !!}
  @endif

  <!-- Content wrapper -->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        @if (session()->has('msg'))
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
                role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <div class="d-flex align-items-center">
                  <i class="ti ti-error"></i>
                  <span> {{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }} </span>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>

      <!-- My Mentors -->

      <section>
        <div class="row">
          <div class="col-md-12">
            <div class="">
              <div class="card-header mb-4">
                <h3 class="card-title align-items-center">
                  {{ __('dashboard.Mentors') }}
                </h3>
              </div>
              <div class="card-body">
                <div class="card" style="border-radius: 20px;">
                  <div class="table-responsive">
                    <table class="table" id="mentorstable">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th class="text-nowrap text-center">{{ __('dashboard.Name/Title') }}</th>
                          <th class="text-nowrap text-center">{{ __('dashboard.Bio') }}</th>
                          <th class="text-nowrap text-center">{{ __('dashboard.Specialization') }}</th>
                          <th class="text-nowrap text-center">{{ __('cycles.ProgramCount') }}</th>
                          <th class="text-nowrap text-center">{{ __('dashboard.Linkedin') }}</th>
                          <th class="text-nowrap text-center">{{ __('dashboard.Request') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($mentors as $key => $mentor)
                          @php
                            $getcount = \App\CyclesMentors::where('user_id', $mentor->id ?? 0)->get();
                            $m_count = count($getcount);
                            $getid = \App\CyclesMentors::where('user_id', $mentor->id ?? 0)->first();
                          @endphp
                          <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                              <div class="d-flex align-items-center">
                                <a class="media-left">
                                  <img src="{{ $mentor->image_url }}" alt="avatar" class="rounded-circle"
                                    height="40" width="40">
                                </a>
                                <a target="_blank" href="{{ route('show_mentor_profile', $mentor->id) }}">
                                  <div class="mx-2">
                                    <h6 class="media-heading mb-0">{{ $mentor->name }}</h6>
                                    <span class="font-small-2">{{ $mentor->title }}</span>
                                  </div>
                                </a>
                              </div>
                            </td>
                            <td> {{ $mentor->bio->{'bio_' . $l} ?? '-' }} </td>
                            <td> {{ $mentor->bio->{'specialization_' . $l} ?? '-' }} </td>
                            <td class="text-center"> {{ $m_count }} </td>
                            <td>
                              @if ($mentor->bio->linkedin ?? 0)
                                <a title="Linkedin URL" target="_blank" href="{{ $mentor->bio->linkedin }}"
                                  class="btn btn-icon rounded-circle btn-label-primary"> <i
                                    class="fa-brands fa-linkedin-in"></i></a>
                              @endif
                            </td>
                            <td>
                              @if ($getid->user ?? 0)
                                <button 
                                  type="button" 
                                  class="btn btn-icon rounded-circle btn-label-primary"
                                  data-bs-toggle="modal"
                                  data-bs-target="#SendRequest"
                                  title="Add new request" 
                                  onclick="request({{ $mentor->id}},'{{optional($getid->user)->{'first_name_'.$l} }} {{optional($getid->user)->{'last_name_'.$l} }}', {{ $getid->cycle->organization_id }},{{ $getid->cycle_id }})"
                                >
                                  <i class="ti ti-plus"></i>
                              </button>
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
        </div>
      </section>
      <!-- / My Mentors -->
    </div>
  </div>

  <!--  Edit SP Modal -->
  <div class="modal fade" id="SendRequest" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">
            {{ __('dashboard.Add mentorship session with') }} 
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="post" action="/dashboard/mentorship/addwait" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-body">
              @csrf
              <input type="hidden" name="mentorid" value="0" id="mentorid">
              <input type="hidden" name="organization_id" id="addOrganizationID">
              <input type="hidden" name="cycle_id" id="addCycleID">
              <div class="border-bottom mb-4 pb-2">
                <h5 class="modal-title">{{ __('dashboard.Mentor schedule') }}</h5>
              </div>
              <div class="form-group mb-3" id="showdates">
                <label class="fs-6 mb-2">{{ __('dashboard.Start meeting Date/ Time') }}:</label>
                <div class="position-relative">
                  <input 
                    id="date" 
                    name="date" 
                    type="text" 
                    class="date width-250 form-control pr-5" 
                    placeholder="{{ __('dashboard.Start meeting Date/ Time') }}"
                    value="" 
                    required 
                    onkeydown="return false"
                  />
                  <i 
                    class="ti ti-calendar position-absolute" 
                    style="{{ $l == 'ar' ? 'left: 10px;' : 'right: 10px;' }} top: 50%; transform: translateY(-50%);">
                  </i>
                </div>
              </div>
              <div class="form-group mb-3">
                <label class="fs-6 mb-2">{{ __('dashboard.Meeting duration') }}:</label>
                <select name="period" class="select form-control width-150">
                  <option value="30">30 {{ __('dashboard.Minutes') }}</option>
                  <option value="60" selected>1 {{ __('dashboard.Hour') }}</option>
                  <option value="120">2 {{ __('dashboard.Hours') }}</option>
                  <option value="180">3 {{ __('dashboard.Hours') }}</option>
                  <option value="240">4 {{ __('dashboard.Hours') }}</option>
                  <option value="300">5 {{ __('dashboard.Hours') }}</option>
                </select>
              </div>
              <div class="form-group mb-3">
                <label class="fs-6 mb-2" for="basicInputFile">{{ __('dashboard.Send file with your request') }}</label>
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="inputGroupFile01" name="file_url"
                    accept="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf, image/*">
                  {{-- <label class="custom-file-label" for="inputGroupFile01">{{ __('dashboard.Choose file') }}</label> --}}
                </div>
              </div>
              <label class="fs-6 mb-2">{{ __('dashboard.Add note') }}:</label>
              <div class="form-group mb-3">
                <textarea class="form-control" id="notes" name='notes' rows="4"
                  placeholder="{{ __('dashboard.Write your note here') }}"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary ml-1">
                {{ __('dashboard.Submit') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- / Edit SP Modal -->
@stop

@section('scripts')
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
  {{-- <script src="/vuexy/src/js/scripts/extensions/ext-component-ratings.js"></script> --}}
@stop
  
@section('pagescripts')
  <!-- Page JS -->
  <script src="/vuexy/assets/js/pages-account-settings-account.js"></script>

  <script>
    $(document).ready(function() {
      $('#dfrom, #dto').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
      });
    });
  </script>

  <script>
    $(document).ready(function() {
        $("#mentorstable").DataTable({
            "responsive": true,
            "searching": true,
            "lengthChange": false,
            "paging": false,
            "bInfo": false,
            "columnDefs": [{"orderable": false, "targets": [3]}],
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

        $(".sessonsTable").DataTable({
            "responsive": true,
            "searching": true,
            "lengthChange": false,
            "paging": false,
            "bInfo": false,
            "columnDefs": [{"orderable": false, "targets": [3]}],
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

        $("#date").datetimepicker({
            format: 'Y-m-d H:i',
            onGenerate: function(ct) {
                jQuery(this).find('.xdsoft_date').toggleClass('xdsoft_disabled');
            },
            maxDate: '{{ date('Y/m/d', strtotime('-1 days')) }}',
            timepicker: true
        });

        function request(id, mentor, organization_id = null, cycle_id = null) {
            const modal = $('#SendRequest');

            modal.style.display = "block";

            $('#mentorid').val(id);
            $('#rname').html(mentor);
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
                    "rtype": 5
                },
                success: function(response) {
                    $('#SendRequest li').remove();
                    $('#name').html(name);
                    if (response.length > 0) {
                        $.each(response, function(i, item) {
                            let note = '';
                            let time = item.tfrom + " {{ __('dashboard.to') }} " + item.tto;
                            if (item.notes) {
                                note = item.notes;
                            }
                            switch (item.type) {
                                case 'OneDay':
                                    $('<li>').html("<b>" + item.dfrom + "</b> (" + time + ").").appendTo('#schedule');
                                    break;
                                case 'Weekly':
                                    $('<li>').html("<b> {{ __('dashboard.Weekly') }} </b>(" + item.days + ") - (" + time + ").").appendTo('#schedule');
                                    break;
                                case 'WorkingDays':
                                    $('<li>').html("<b>{{ __('dashboard.All working days') }} </b>(" + time + ").").appendTo('#schedule');
                                    break;
                                case 'Period':
                                    $('<li>').html("<b> {{ __('dashboard.from') }} " + item.dfrom + " {{ __('dashboard.to') }} " + item.dto + " </b> (" + time + ").").appendTo('#schedule');
                                    break;
                                default:
                            }
                        });
                    } else {
                        $('<li>').html("{{ __('dashboard.No records saved') }}.</li>").appendTo('#schedule');
                    }
                    $('#addOrganizationID').val(organization_id);
                    $('#addCycleID').val(cycle_id);
                    $('#SendRequest').modal('show');
                },
                error: function(error) {
                    console.log(error);
                    alert("Error Occurred, Try again!");
                }
            });
        }
    });
  </script>
  <script>
    function modify(type, id, stat) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      if (type === 3) {
        //delete
        if (confirm("{{ __('dashboard.sure del schedule') }}")) {
          $.ajax({
            type: "POST",
            url: "/dashboard/mentors/modify",
            data: {
              "id": id,
              "rtype": type,
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
      } else if (type === 2) {
        //Enable-disable
        $.ajax({
          type: "POST",
          url: "/dashboard/mentors/modify",
          data: {
            "id": id,
            "rtype": type,
            "stat": stat
          },
          success: function(response) {
            location.reload();
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      } else if (type === 1) {
        // Edit current
        $.ajax({
          type: "POST",
          url: "/dashboard/mentors/modify",
          data: {
            "id": id,
            "rtype": type,
          },
          success: function(response) {
            switchtypes(response.type);
            $('#sid').val(response.id);
            $('#rtype').val(4);
            $('#type').val(response.type);
            $('#days').val(response.days);
            $('#dfrom').val(response.dfrom);
            $('#dto').val(response.dto);
            $('#tfrom').val(response.tfrom);
            $('#tto').val(response.tto);
            $('#notes').val(response.notes);
            $('#SendRequest').modal('show');
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      } else {
        //show from as new
        $('#sid, #type, #days, #dfrom, #dto, #tfrom, #tto, #notes').val();
        $('#rtype').val('0');
        switchtypes(type);
        $('#SendRequest').modal('show');
      }
    }

    function SelectType(type) {
      switchtypes(type);
    }

    function switchtypes(type) {
      switch (type) {
        case 'OneDay':
          $('#showdates').show();
          $('#showtto, #showdays').hide();
          $("#dfrom").prop('required', true);
          $("#days, #dto").prop('required', false);
          break;
        case 'Weekly':
          $('#showdates').hide();
          $('#showdays').show();
          $("#days").prop('required', true);
          $("#dfrom, #dto").prop('required', false);
          break;
        case 'WorkingDays':
          $('#showdates, #showdays').hide();
          $("#dfrom, #dto, #days").prop('required', false);
          break;
        case 'Period':
          $('#showtto,#showdates').show();
          $('#showdays').hide();
          $("#dfrom, #dto").prop('required', true);
          $("#days").prop('required', false);
          break;
      }
    }

    $('.start_meeting').click(function() {
      $.ajax({
        type: "GET",
        url: "/dashboard/mentor/meetingRedirect/" + $(this).data('value'),
        success: function(response) {
          alert(response['message']);
          window.open(response['join_url'], '_blank');
          window.location.href = '{{ route('mentorshipTasks.index') }}' + '?mentorship_id=' + response[
            'mentorship_id'];
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    });
  </script>
@stop
