@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/pickr/pickr-themes.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
{{--
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> --}}
{{--
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css"> --}}
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
{{--
<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css"> --}}
{{--
<link rel="stylesheet" href="/Datepicker/dist/css/datepicker.material.css"> --}}
{{--
<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet"> --}}
<style>
  #mentorstable tr td {
    padding: 0.15rem 2rem;
  }
</style>
@stop

@section('content')
<!-- BEGIN: Content-->
<div class="">
  <div class="">
    <div class="content-overlay"></div>
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
    <div class="content-body">
      <section id="multiple-column-form" class="card mb-4">
        <div class="card-header border-bottom mb-4">
          <h4 class="card-title">{{ __('cycles.Portal Cycles Users') }}</h4>
        </div>
        <div class="card-body">
          <div class="row mb-4">
            <div class="col-12 col-md-6 d-flex-align-items-center">
              <label class="form-label fs-5 mb-2">{{ __('cycles.Select cycle from list') }}</label>
              <fieldset class="d-flex flex-column flex-md-row gap-3">
                <select id="cstep" class="select2 form-select"
                    onchange="window.open('/dashboard/mentorship/view/'+this.options[this.selectedIndex].value,'_self');">
                  @foreach ($cycles as $cycle)
                  <option value="{{ $cycle->id }}" {{ $cycle->id == $id ? 'selected' : '' }}>{{ $cycle->title }}
                    ({{ __('cycles.from') }} {{ $cycle->start ? $cycle->start->format('d-m-Y') : '-' }} {{ __('cycles.to') }}
                    {{ $cycle->end ? $cycle->end->format('d-m-Y') : '-' }})
                    - {{ optional($cycle->organization)->name }}
                  </option>
                  @endforeach
                  @if (!$id)
                  <option> {{ __('cycles.No records saved') }} -
                    ({{ __('cycles.Manage Cycle') }}).
                  </option>
                  @endif
                </select>
              </fieldset>
            </div>
          </div>
          <div class="row">
            <div class="col-12 d-flex flex-wrap gap-2">
              <a class="btn btn-label-success mb-2" href="/dashboard/cycles/{{ $id }}">
                <i class="ti ti-pencil"></i> <span class="mx-1">{{ __('cycles.Manage Cycle') }}</span>
              </a>
              <a class="btn btn-label-primary mb-2" href="/dashboard/mentorship/view/{{ $id }}">
                <i class="ti ti-settings"></i> <span class="mx-1">{{ __('cycles.Manage sessions') }}</span>
              </a>
              <a class="btn btn-label-dark mb-2" href="/dashboard/mentorship/{{ $id }}/surveys">
                <i class="ti ti-question-mark"></i> <span class="mx-1">{{ __('cycles.Manage Surveys') }}</span>
              </a>
            </div>
          </div>
        </div>
      </section>
      <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card mb-4">
            <div class="card-header border-bottom border-light mb-6">
              <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">{{ __('cycles.Mentors') }}</h4>
                <a class="btn btn-primary" href="/dashboard/mentorship/assign/{{ $id }}">
                  <i class="ti ti-user me-2 ti-16px"></i>
                  {{ __('cycles.Manage Cycle mentors') }}
                </a>
              </div>
            </div>
            <div class="card-body">
              @if (count($mentors) == 0)
              <span class="small">
                {{ __('cycles.Add mentors to this cycle from') }} <a href="/dashboard/mentorship/assign/{{ $id }}">{{
                  __('cycles.here') }} </a>.
              </span>
              @endif
              <div class="d-flex flex-wrap gap-2">
                @foreach ($mentors as $mentor)
                <label class="btn btn-outline-primary mentor-label" id="label-mentor{{ $mentor->user_id }}">
                  <div class="d-flex align-items-center">
                    <img class="rounded-circle me-2" src="{{ $mentor->user->image_url ?? '' }}" alt="click to view"
                      height="20" width="20" />
                    <div class="d-flex align-items-center">
                      <span class="mx-2">
                        @if($mentor->user != null)
                        {{ $mentor->user->id }}-
                        {{ $thementor = $mentor->user->{'first_name_' . $l} . ' ' . $mentor->user->{'last_name_' . $l}
                        }}
                        @endif
                      </span>
                      <input
                        type="radio"
                        value="{{ $mentor->user_id }}"
                        id="mentor{{ $mentor->user_id }}"
                        data-name="{{ $thementor }}"
                        data-rating="{{ $mentor->mrating() }}"
                        data-id="{{ $mentor->user_id }}"
                        name="mentor"
                        class="mentor_radio"
                        onclick="mentor({{ $mentor->user_id }}, '{{ $thementor }}', '{{ $mentor->mrating() }}')"
                      />
                    </div>
                  </div>
                </label>
                @endforeach
              </div>
            </div>
            <div class="card-body">
              <div id="mentors" style="display: none;" class="mb-3">
                <hr class="my-3">
                <h5 class="card-title mb-4">
                  <b id="name"></b>
                  {{ __('cycles.Schedule') }}:
                </h5>
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
          <br>
                  <!-- Filter -->
              <form method="GET" action="{{ route('mentorships.requests_view', $id) }}">
                <div class="d-flex align-items-center mx-2">
                  <div style="display: flex; align-items: center;">
                  <button
                    class="btn bg-primary p-3 d-none d-md-flex justify-content-center align-items-center mx-3" 
                    style="border-radius: 17px; width: 40px; height: 40px;"
                     type="submit"
                  >
                    <i class="ti ti-filter text-white ti-24px"></i>
                  </button>
                </div>
                <!-- Search Bar -->
                
                <div class="position-relative me-3 d-none d-md-flex">
                  <select name="step_id" class="form-control">
                    <option value="0">All</option>
                    @php
                    $steps = App\Step::where('cycle_id', $id)->get();
                    @endphp
                    @foreach ($steps as $step)
                    <option value="{{ $step->id }}" {{ $step->id == request('step_id') ? 'selected' : '' }}>{{ $step->title }}</option>
                    @endforeach
                  </select>
                </div>
                
  
                

              </div>
              </form>
              <hr>
          <div class="table-responsive card-body">
            <table class="table mb-0" id="userstable">
              <thead class="bg-label-light text-nowrap">
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
                        <img src="{{ $company->logo_url }}" alt="avatar" class="rounded-circle" height="40" width="40">
                      </a>
                      <a href="/dashboard/companyProfile/{{ $company->id }}">
                        <div class="d-flex align-items-center">
                          {{ $company->name }}
                        </div>
                      </a>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center" style="width: 150px;">
                      <a class="me-2" href="/dashboard/users/{{ $company?->user?->id }}/edit">
                        <img src="/191014/{{ $company?->user?->img_url ?? '../images/placeholder.jpg' }}" alt="avatar"
                          class="rounded-circle" height="40" width="40">
                      </a>
                      <a href="/dashboard/users/{{ $company?->user?->id }}/edit">
                        <div>
                          <h6 class="mb-0">{{ $company?->user?->{'first_name_' . $l} }}
                            {{ $company?->user?->{'last_name_' . $l} }}</h6>
                          <span class="small">{{ $company?->user?->{'title_' . $l} }}</span>
                        </div>
                      </a>
                    </div>
                  </td>
                  <td>
                    @if ( optional($company->user)->mentorship && $company->user->mentorship->count() > 0 )
                      <div class="d-flex align-items-center bg-light border rounded p-2" style="height: 100px; width: 300px; overflow-x: auto;">
                        @foreach ($company?->user?->mentorship as $session)
                          @if ($session->approved == 'Yes')
                          <span class="text-wrap badge bg-white text-dark m-1" style="min-width: 100px;">
                            M#{{ $session->mentor_id }} ({{ $session->zoom_date }})
                          </span>
                          @endif
                        @endforeach
                      </div>
                    @else
                      <div class="d-flex align-items-center justify-content-center">
                        <div class="card-body">
                          <span class="badge bg-white text-dark text-nowrap fs-5">{{ __('cycles.No sessions') }}</span>
                        </div>
                      </div>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex align-items-center text-nowrap">
                      {{ $company->stepdata->title }}
                    </div>
                  </td>
                  <td>
                    <button
                      {{-- onclick="request({{ $mentor->id }},'{{ $mentor->name }}', {{ $mentor->organization_id }}, {{ $mentor->cycle_id }})" --}}
                      onclick="add({{ $company?->user?->id }},{{ $company?->cycledata?->organization_id }},{{ $company?->cycle }})"
                      type="button" class="btn btn-label-primary btn-icon rounded-circle">
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
@include('admin.clients.startup.partials._mutiple_session_modal')

@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
<script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
{{-- <script src="/js/jquery.datetimepicker.full.min.js"></script> --}}
{{-- <script src="/Datepicker/dist/datepicker.js"></script> --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/flatpickr">
  </scrit> --}}
{{-- @if ($l == 'ar')
  <script src="https://npmcdn.com/flatpickr/dist/l10n/ar.js">
</script>
@endif --}}
@stop

@section('pagescripts')
{{-- <script src="/vuexy/assets/js/forms-selects.js"></script> --}}
<script src="/vuexy/assets/js/mentorship-mutiple-session.js"></script>
@php
  $addedSession = session()->has('add');
  $notAddedSession = session()->has('notAdd');
@endphp

<script>
  // const addedSession = '{{  $addedSession }}';
  // const notAddedSession = '{{  $notAddedSession }}';

  // console.log('notAddedSession', notAddedSession);
  // console.log('addedSession', addedSession);
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const sessionForm = document.getElementById('sessionForm');
    const flatpickerDataTime = document.getElementById('flatpickr-date-time');
    const dateError = document.getElementById('dateError');
    const submitBtn = document.getElementById('submitBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    if (flatpickerDataTime) {
      flatpickr(flatpickerDataTime, {
      enableTime: true,
      minDate: "today",
      mode: "multiple",
      onChange: function(selectedDates, dateStr, instance) {
        dateError.innerText = '';
        // remove is-invalid class
        flatpickerDataTime.classList.remove('is-invalid');
      }
      });
    }
    
    sessionForm.addEventListener('submit', function(e) {
      let loading = false;
      e.preventDefault();
      console.log('Form submitted');


      let date = flatpickerDataTime.value;
      if (date) {
        this.submit();
        loading = true;
        submitBtn.disabled = true;
        loadingSpinner.classList.remove('d-none');
        flatpickerDataTime.classList.remove('is-invalid');
      } else {
        dateError.innerText = '{{ __('admin.Required_Field') }}';
        flatpickerDataTime.focus();
        flatpickerDataTime.classList.add('is-invalid');
      }
    });
  });
</script>

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

    function add(id, organization_id, cycle_id) {
      let mentor = $('input[name="mentor"]:checked').val();

      
      if (mentor) {
        let mentorName = $('input[name="mentor"]:checked').data('name');
        let mentorRating = $('input[name="mentor"]:checked').data('rating');
        let mentorId = $('input[name="mentor"]:checked').data('id');
        const userId = $('#addUserID').val(id);
        // // $('#mentorid').val(mentor);
        // const orgId = $('#addOrganizationID').val(organization_id);
        // const cycleId = $('#addCycleID').val(cycle_id);
        
        // $('#SendRequest').modal('show');
        request(mentorId, mentorName, organization_id, cycle_id)
      } else {
        alert("{{ __('cycles.Please select mentor first, and try again') }}!");
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

//     $(document).on('click', '.mentor_radio', function() {
//     var mentor_id = $(this).val();
//     $.ajax({
//         url: '/dashboard/mentorship/getDates', // الرoutes المناسب
//         type: 'POST',
//         data: {
//           mentor_id: mentor_id,
//             _token: '{{ csrf_token() }}'
//         },
//         success: function(response) {
//           console.log(response.dates);
            
//         },
//         error: function(xhr) {
//         console.error('Error:', xhr.responseText);
//     }
//     });
// });
</script>
@stop