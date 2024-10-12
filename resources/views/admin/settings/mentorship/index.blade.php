@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
  <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
  {{-- <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css"> --}}
  {{-- <link rel="stylesheet" href="/Datepicker/dist/css/datepicker.material.css"> --}}
  {{-- <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet"> --}}
  <style>
    #mentorstable tr td {
      padding: 0.15rem 2rem;
    }
  </style>
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
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
          <div class="card-header mb-4">
            <h4 class="card-title">{{ __('cycles.Portal Cycles Users') }}</h4>
          </div>
          <div class="row match-height">
            <div class="col-xl-12 col-md-12 col-sm-12">
              <div class="card mb-4 py-4">
                <div class="card-body">
                  <label class="form-label fs-5 mb-2">{{ __('cycles.Select cycle from list') }}</label>
                  <fieldset class="d-flex flex-column flex-md-row gap-3">
                    <select id="cstep" class="select2 form-control"
                      style="margin-left: {{ $l == 'ar' ? '10px' : '0' }}; margin-right: {{ $l == 'ar' ? '0' : '10px' }}; width: 60%;"
                      onchange="window.open('/dashboard/mentorship/view/'+this.options[this.selectedIndex].value,'_self');">
                      @foreach ($cycles as $cycle)
                        <option value="{{ $cycle->id }}" {{ $cycle->id == $id ? 'selected' : '' }}>{{ $cycle->title }}
                          ({{ __('cycles.from') }} {{ $cycle->start->format('d-M') }} {{ __('cycles.to') }}
                          {{ $cycle->end->format('d-M-Y') }})
                          - {{ optional($cycle->organization)->name }}</option>
                      @endforeach
                      @if (!$id)
                        <option> {{ __('cycles.No records saved') }} -
                          ({{ __('cycles.Manage Cycle') }}).
                        </option>
                      @endif
                    </select>
                    <div class="input-group-append">
                      <a class="btn btn-label-success" href="/dashboard/cycles/{{ $id }}">
                        <i class="ti ti-pencil"></i> <span class="mx-1">{{ __('cycles.Manage Cycle') }}</span>
                      </a>
                      <a class="btn btn-label-primary" href="/dashboard/mentorship/view/{{ $id }}">
                        <i class="ti ti-settings"></i> <span class="mx-1">{{ __('cycles.Manage sessions') }}</span>
                      </a>
                    </div>
                  </fieldset>
                </div>
              </div>
            </div>
          </div>
        </section>
        <section class="row">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card mb-4">
              <div class="card-header border-bottom border-light mb-6">
                <h4 class="card-title">{{ __('cycles.Mentors') }}</h4>
              </div>
              <div class="card-body">
                @if (count($mentors) == 0)
                  <span class="small">
                    {{ __('cycles.Add mentors to this cycle from') }} <a
                      href="/dashboard/mentorship/assign/{{ $id }}">{{ __('cycles.here') }} </a>.
                  </span>
                @endif
                <div class="d-flex flex-wrap gap-2">
                  @foreach ($mentors as $mentor)
                    <label class="btn btn-outline-primary mentor-label" id="label-mentor{{ $mentor->user_id }}">
                      <div class="d-flex align-items-center">
                        <img class="rounded-circle me-2" src="{{ $mentor->user->image_url }}" alt="click to view"
                          height="20" width="20" />
                        <div class="d-flex align-items-center">
                          <span class="mx-2">
                            {{ $mentor->user->id }}-
                            {{ $thementor = $mentor->user->{'first_name_' . $l} . ' ' . $mentor->user->{'last_name_' . $l} }}
                          </span>
                          <input type="radio" value="{{ $mentor->user_id }}" id="mentor{{ $mentor->user_id }}"
                            name="mentor"
                            onclick="mentor({{ $mentor->user_id }},'{{ $thementor }}','{{ $mentor->mrating() }}')">
                        </div>
                      </div>
                    </label>
                  @endforeach
                </div>
              </div>
              <div class="card-body">
                <div id="mentors" style="display: none;" class="mb-3">
                  <hr class="my-3">
                  <h4 class="card-title mb-4">
                    <b id="name"></b>
                    {{ __('cycles.Schedule') }}:
                  </h4>
                  <div class="table-responsive">
                    <table class="table" id="mentorstable">
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
        <section>
          <div class="card">
            <div class="card-header border-bottom mb-4">
              <h4 class="card-title">{{ __('cycles.Cycle registered users') }}</h4>
            </div>
            <div class="table-responsive card-body">
              <table class="table mb-0" id="userstable">
                <thead class="bg-label-light">
                  <tr>
                    <th>{{ __('cycles.Company') }}</th>
                    <th>{{ __('cycles.User/Title') }}</th>
                    <th>{{ __('cycles.Session') }}</th>
                    <th>{{ __('cycles.Current Step') }}</th>
                    <th>{{ __('cycles.Actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($companies as $company)
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <a class="me-2" href="/dashboard/companyProfile/{{ $company->id }}">
                            <img src="{{ $company->logo_url }}" alt="avatar" class="rounded-circle" height="40"
                              width="40">
                          </a>
                          <a href="/dashboard/companyProfile/{{ $company->id }}">
                            <div class="d-flex align-items-center">
                              {{ $company->name }}
                            </div>
                          </a>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <a class="me-2" href="/dashboard/users/{{ $company->user->id }}/edit">
                            <img src="/191014/{{ $company->user->img_url ?? '../images/placeholder.jpg' }}"
                              alt="avatar" class="rounded-circle" height="40" width="40">
                          </a>
                          <a href="/dashboard/users/{{ $company->user->id }}/edit">
                            <div>
                              <h6 class="mb-0">{{ $company->user->{'first_name_' . $l} }}
                                {{ $company->user->{'last_name_' . $l} }}</h6>
                              <span class="small">{{ $company->user->{'title_' . $l} }}</span>
                            </div>
                          </a>
                        </div>
                      </td>
                      <td>
                        @if ($company->user->mentorship)
                          @foreach ($company->user->mentorship as $session)
                            @if ($session->approved == 'Yes')
                              M#{{ $session->mentor_id }} ({{ $session->zoom_date }})
                              <br>
                            @endif
                          @endforeach
                        @endif
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          {{ $company->stepdata->title }}
                        </div>
                      </td>
                      <td>
                        <button
                          onclick="add({{ $company->user->id }},{{ $company->cycledata->organization_id }},{{ $company->cycle }})"
                          type="button" class="btn btn-success btn-sm rounded-circle">
                          <i class="ti ti-plus"></i>
                        </button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
  <!-- END: Content-->
  <div class="modal fade text-left" id="SendRequest" tabindex="-1" role="dialog" aria-labelledby="sendrequest"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <form method="post" action="/dashboard/mentorship/add" enctype="multipart/form-data" autocomplete="off">
          <div class="modal-header">
            <h4 class="modal-title">{{ __('cycles.Add new mentorship session') }} <span id="rname"></span>
            </h4>
            
          </div>
          <div class="modal-body">
            @csrf
            <input type="hidden" name="mentorid" value="0" id="mentorid">
            <input type="hidden" name="userid" value="0" id="userid">
            <input type="hidden" name="organization_id" id="addOrganizationID">
            <input type="hidden" name="cycle_id" id="addCycleID">
            <div class="form-group" id="showdates">
              <label>{{ __('cycles.Date/Time') }}:</label>
              <p id="selectSubtitle">{{ __('admin.SelectDateTime') }}.</p>
              <div class="flatpickr input-group">
                <a class="input-button" title="toggle" style="margin-left: 3px;margin-top: 3px;" data-toggle>
                  <i class="fa fa-calendar fa-2x primary    "></i>
                </a>

                <input type="text" name="date" placeholder="{{ __('admin.selectDate') }}"
                  class="form-control mr-1" data-input> <!-- input is mandatory -->


                <a class="input-button" title="clear" style="margin-top: 3px" data-clear>
                  <i class="fa fa-trash-o fa-2x danger"></i>
                </a>
              </div>
              {{--                            <input type="hidden" class="date form-control" name="date" id="constrained" --}}
              {{--                                   data-inline="true" data-multiple="true" data-open-on="today" data-time="true"> --}}

              {{--                        <input class='date width-200 form-control' type="text" id="date" name="date" value="" required> --}}
            </div>
            <div class="form-group">
              <label>{{ __('cycles.Meeting period') }}:</label>
              <select name="period" class="select form-control">
                <option value="30">30 {{ __('cycles.Minutes') }}</option>
                <option value="60" selected>1 {{ __('cycles.Hour') }}</option>
                <option value="120">2 {{ __('cycles.Hour') }}</option>
                <option value="180">3 {{ __('cycles.Hour') }}</option>
                <option value="240">4 {{ __('cycles.Hour') }}</option>
                <option value="300">5 {{ __('cycles.Hour') }}</option>
              </select>
            </div>
            <label>{{ __('cycles.Add a session note') }}:</label>
            <div class="form-group">
              <textarea class="form-control" id="notes" name='notes' rows="4"
                placeholder="{{ __('cycles.Write your note here') }}."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary ml-1">
              
              <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@stop
@section('pagescripts')
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  {{--    <script src="/js/jquery.datetimepicker.full.min.js"></script> --}}
  {{--    <script src="/Datepicker/dist/datepicker.js"></script> --}}
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  @if ($l == 'ar')
    <script src="https://npmcdn.com/flatpickr/dist/l10n/ar.js"></script>
  @endif


  <script type="text/javascript">
    $(document).ready(function() {
      $('#userstable').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": false,
        "paging": true,
        "bInfo": false,
        "columnDefs": [{
          "orderable": false,
          "targets": [2, 4]
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
    // $('.date').datetimepicker({
    //     timepicker:true,
    //     multiple: true,
    //     format:'Y-m-d H:i'
    // });
    // var constrained = new Datepicker('.date', {
    //     min: (function () {
    //         var date = new Date();
    //         date.setDate(date.getDate());
    //         return date;
    //     })(),
    // });
    $(".flatpickr").flatpickr({
      inline: false,
      enableTime: true,
      dateFormat: "d-m-Y h:i a",
      minDate: "today",
      mode: "multiple",
      locale: "ar",
      wrap: true
    });

    function add(id, organization_id, cycle_id) {
      let mentor = $('input[name="mentor"]:checked').val();
      if (mentor) {
        $('#userid').val(id);
        $('#mentorid').val(mentor);
        $('#addOrganizationID').val(organization_id);
        $('#addCycleID').val(cycle_id);
        $('#SendRequest').modal('show');
      } else {
        alert("{{ 'cycles.Please select mentor first, and try again' }}!");
      }
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
          let rate = '<i class="bx bxs-star" style="color: #fc960f;"></i>';
          $('<tr>').html(" <td><b>#</b></td> <td><b>Days</b></td> <td><b>Time</b></td> <td><b>Notes</b></td>")
            .appendTo('#mentorstable');
          $('#name').html(name + "<span>" + rate.repeat(rating) + "</span>");
          if (response.length > 0) {
            $.each(response, function(i, item) {
              let days = '';
              let note = '';
              let time = "From " + item.tfrom + " to " + item.tto;
              let color = "primary";
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
                  color = "danger";
                  break;
              }
              let num = i + 1;
              $('<tr class="' + color + '">').html("<td>" + num + "</td><td>" + days + "</td><td>" + time +
                "</td><td>" + note + "</td>").appendTo('#mentorstable');
            });
          } else {
            $('<tr class="warning">').html("<td colspan='5'> {{ __('cycles.No records saved') }}.</td>").appendTo(
              '#mentorstable');
          }
          $('#mentors').show();
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
  </script>
@stop
