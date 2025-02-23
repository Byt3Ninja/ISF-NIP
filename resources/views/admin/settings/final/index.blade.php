@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
@stop

@php
$types = ['onetoall'=>'Assign One to all users','grouptoall'=>'Assign Group to all users','grouptogroup'=>'Assign Group
to group of users'];
@endphp

@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
    <div class="content-body">
      <section id="multiple-column-form">
        @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible mb-2" role="alert">
          {{ __('admin.' . session()->get('message')) }}
          <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if (session()->has('message2'))
        <div class="alert alert-danger alert-dismissible mb-2" role="alert">
          {{ __('admin.' . session()->get('message2')) }}
          <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        <div class="row match-height">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card mb-4">
              <div class="card-header" style="padding-bottom: 15px;">
                <h4 class="card-title">{{ __('cycles.Portal Final Screening list') }}</h4>
              </div>
              <hr>
              <div class="card-body">
                <label class="fs-6">{{ __('cycles.Select step from list to manage') }}</span>
                  <fieldset class="d-flex flex-column flex-md-row gap-3 mt-2">
                    <select id="cstep" class="select form-control"
                      style="margin-left: {{ $l == 'ar' ? '10px' : '0' }}; margin-right: {{ $l == 'ar' ? '0' : '10px' }}; width: 50%;"
                      onchange="window.open('/dashboard/'+this.options[this.selectedIndex].value+'/final','_self');">
                      @foreach ($steps as $step)
                      <option value="{{ $step->id }}" {{ $step->id == $id ? 'selected' : '' }}>{{ $step->title }}
                        ({{ __('cycles.Cycle') }} "{{ $step->cycle->title }}" - {{ __('cycles.Start') }}
                        {{ $step->from->format('d-m-Y') }}) - {{ optional(optional($step->cycle)->organization)->name }}
                      </option>
                      @endforeach
                    </select>
                    <div class="input-group-append mx-auto">
                      <button type="button" class="btn btn-label-info" onclick="DoChange(1)">
                        <i class="ti ti-edit"></i>
                        <span class="mx-1">{{ __('cycles.Edit') }}</span>
                      </button>
                      <button type="button" class="btn btn-label-danger" onclick="DoChange(2)">
                        <i class="ti ti-trash"></i>
                        <span class="mx-1">{{ __('cycles.Delete') }}</span>
                      </button>
                      <button type="button" class="btn btn-label-secondary"
                        onclick="window.location.href='/dashboard/final/results/{{ $id }}'">
                        <i class="ti ti-user"></i> <span class="mx-1">{{ __('cycles.Results') }}</span>
                      </button>
                      <a class="btn btn-label-success" href="/dashboard/cycles/{{ $cstep->cycle_id ?? null }}">
                        <i class="ti ti-pencil"></i>
                        <span class="mx-1">{{ __('cycles.Cycle') }}</span>
                      </a>
                    </div>
                  </fieldset>
                  <br />
              </div>
            </div>
            @if (!$cstep)
            <div class="card">
              <div class="card-header border-bottom">
                <h4 class="card-title">{{ __('cycles.Error') }}</h4>
              </div>
              <div class="card-body">
                {{ __('cycles.No records Add final') }}
                <a href="/dashboard/cycles">{{ __('cycles.Cycle') }}</a>.
              </div>
            </div>
            @endif
          </div>
        </div>
      </section>
      <section class="row">
        @if (isset($cstep))
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card">
            <form action="/dashboard/final/update" method="POST" enctype="multipart/form-data">
              {!! csrf_field() !!}
              <div class="card-header border-bottom">
                <h4>{{ $cstep->title }} </h4>
                <h4 class="card-title primary">{{ __('cycles.Select judges from the menu') }}.</h4>
                <small class="text-muted"> {{ __('cycles.from') }}
                  {{ $cstep->from->format('d-m-Y') }} {{ __('cycles.to') }}
                  {{ $cstep->to->format('d-m-Y') }}
                </small>
              </div>
              <div class="card-body">
                <div class="card-text">
                  <input type="hidden" value="{{ $cstep->id }}" name="step">
                  <div class="form-group mb-3">
                    <div class="form-row mt-3">
                      <span style="fs-6 mb-2 form-label">{{ __('cycles.Judges list') }}</span>
                      <br />
                      <div class="row col-md-12 mt-2">
                        <div class="col-md-12">
                          <select name="judges[]" class="select2 form-control" multiple="multiple">
                            @foreach ($judges as $judge)
                            <option value="{{ $judge->id }}">{{ $judge->{'first_name_' . $l} }}
                              {{ $judge->{'last_name_' . $l} }} ({{ $judge->email }})</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                  <br>
                  <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-end">
                      <button type="submit" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>
                        {{ __('cycles.Assign to selected users') }}
                      </button>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table" id="mentorstable">
                      <thead class="table-light">
                        <tr>
                          <th>{{ __('cycles.Company') }}</th>
                          <th>{{ __('cycles.Users') }}</th>
                          <th>{{ __('cycles.Judges') }}</th>
                          <th>{{ __('cycles.Actions') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($companies as $company)
                        <tr>
                          <td>
                            <div class="checkbox">
                              <input type='checkbox' id="checkbox{{ $company->id }}" class="checkbox-input" {{
                                in_array($company->id, []) ? 'checked' : '' }} name='users[]'
                              value="{{ $company->id }}">
                              <label for="checkbox{{ $company->id }}">
                                <div class="d-flex align-items-center">
                                  <a class="media-left mr-50" href="/dashboard/companyProfile/{{ $company->id }}">
                                    <img 
                                      src="{{ $company->logo_url }}" alt="avatar" class="rounded-circle" height="40" width="40">
                                  </a>
                                  <a href="/dashboard/companyProfile/{{ $company->id }}">
                                    <div class="d-flex align-items-center">
                                      {{ $company->name }}
                                    </div>
                                  </a>
                                </div>
                              </label>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center gap-2">
                              <a class="" href="/dashboard/users/{{ $company->user->id }}/edit">
                                <img src="/191014/{{ $company->user->img_url ?? '../images/placeholder.jpg' }}"
                                  alt="avatar" class="rounded-circle" height="40" width="40">
                              </a>
                              <a href="/dashboard/users/{{ $company->user->id }}/edit">
                                <div class="media-body">
                                  <h6 class="media-heading mb-0">{{ $company->user->name }} </h6>
                                  <span class="font-small-2">{{ $company->user->title }}</span>
                                </div>
                              </a>
                            </div>
                          </td>
                          <td>
                            @foreach (\App\User::whereIn('id', $company->finals($cstep->id))->get() as $judge)
                            <div class="chip mr-1">
                              <div class="d-flex align-items-center gap-2">
                                <div class="avatar">
                                  <img class="img-fluid rounded-circle" src="{{ $judge->image_url }}" alt="click to view" height="20"
                                    width="20">
                                </div>
                                <span class="chip-text">{{ $judge->name }}</span>
                              </div>
                            </div>
                            @endforeach
                          </td>
                          <td>
                            @if (!$company->fsession($cstep->id))
                            {{ __('cycles.Add Judges first') }}
                            @elseif($company->fsession($cstep->id)->join_url)
                            <button type="button" onclick="edit({{ $company->fsession($cstep->id)->id }})"
                              class="btn btn-icon btn-label-info rounded-circle" data-bs-toggle="modal"
                              data-bs-target="#ModifyRequest">
                              <i class="ti ti-edit text-info"></i>
                            </button>
                            <button type="button" onclick="deleteme({{ $company->fsession($cstep->id)->id }})"
                              class="btn btn-icon rounded-circle btn-label-danger">
                              <i class="ti ti-trash text-danger"></i></button>
                            <a href="{{ $company->fsession($cstep->id)->start_url }}" target="_blank">
                              <button type="button" title="Start Meeting" class="btn btn-icon rounded-circle btn-label-success">
                                <i class="ti ti-brand-zoom"></i></button></a>
                            <a href="{{ $company->fsession($cstep->id)->join_url }}" target="_blank">
                              <button type="button" title="Join only" class="btn btn-icon rounded-circle btn-label-primary">
                                <i class="ti ti-user"></i></button></a>
                            @else
                            <button type="button" onclick="edit({{ $company->fsession($cstep->id)->id }})"
                              class="btn btn-icon btn-label-info rounded-circle" data-bs-toggle="modal"
                              data-bs-target="#ModifyRequest">
                              <i class="ti ti-edit text-info"></i>
                            </button>
                            <button type="button" onclick="deleteme({{ $company->fsession($cstep->id)->id }})"
                              class="btn btn-icon rounded-circle btn-label-danger">
                              <i class="ti ti-trash text-danger"></i></button>
                            <button type="button" onclick="addzoom({{ $company->id }})"
                              class="btn btn-icon rounded-circle btn-label-success">
                              <i class="ti ti-plus"></i></button>
                            @endif
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  <hr>
                  <div class="checkbox m-2">
                    <input type='checkbox' id="checkAll" class="checkbox-input">
                    <label for="checkAll">
                      {{ __('cycles.Select All') }}
                    </label>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
        @endif
      </section>
    </div>
  </div>
  <div class="content-backdrop fade"></div>
</div>
<!-- Content wrapper -->

<!--Screening form Modal -->
@if (isset($cstep))
<div class="modal fade" id="stepForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form class="form" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="sid" value="{{ $cstep->id }}">
          <input type="hidden" name="type" value="8">
          <br>
          <div class="form-group mb-3">
            <label class="fs-6 mb-1">{{ __('cycles.Title') }}: </label>
            <input value="{{ $cstep->title }}" name='title' type="text" placeholder="Title" class="form-control"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="fs-6 mb-1">{{ __('cycles.from') }}: </label>
            <input value="{{ $cstep->from->format('Y-m-d') }}" name='fromDate' type="text" class="form-control dateTime"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="fs-6 mb-1">{{ __('cycles.to') }}: </label>
            <input value="{{ $cstep->to->format('Y-m-d') }}" name='toDate' type="text" class="form-control dateTime"
              required>
          </div>
          <div class="form-group mb-0">
            <label class="fs-6 mb-1">{{ __('cycles.Description') }}: </label>
            <textarea name='description' placeholder="Description"
              class="form-control">{{ $cstep->description }}</textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary ml-1">
            <span>{{ __('cycles.Submit') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- create meeting -->
<div class="modal fade" id="addzoom" tabindex="-1" role="dialog" aria-labelledby="addzoom" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="/dashboard/final/addzoom" enctype="multipart/form-data" autocomplete="off">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title white">{{ __('cycles.Add new zoom session') }}</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          {{-- <p class="text-danger">{{ __('cycles.Before submit review') }}.</p> --}}
          <input type="hidden" name="step" value="{{ $cstep->id }}" id="sid">
          <input type="hidden" name="company" value="0" id="cid">
          <div class="form-group mb-3" id="showdates mb-3">
            <label class="fs-6 mb-1">{{ __('cycles.Date/Time') }}:</label>
            <input class='width-200 form-control dateTime' type="text" id="date" name="date" value="" required>
          </div>
          <div class="form-group mb-3">
            <label class="fs-6 mb-1">{{ __('cycles.Meeting period') }}:</label>
            <select name="period" class="select form-control">
              <option value="30">30 {{ __('cycles.Minutes') }}</option>
              <option value="60" selected>1 {{ __('cycles.Hour') }}</option>
              <option value="120">2 {{ __('cycles.Hours') }}</option>
              <option value="180">3 {{ __('cycles.Hours') }}</option>
              <option value="240">4 {{ __('cycles.Hours') }}</option>
              <option value="300">5 {{ __('cycles.Hours') }}</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary ml-1">
            <span>{{ __('cycles.Submit') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="ModifyRequest" tabindex="-1" role="dialog" aria-labelledby="sendrequest" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="/dashboard/final/editZoom" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title">{{ __('cycles.Modify Final Screen') }} </h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          {{-- <p class="text-danger">{{ __('cycles.Before submit review') }}.</p> --}}
          <input type="hidden" name="step" value="{{ $cstep->id }}" id="sid">
          <input type="hidden" name="company" value="0" id="cid">
          <div class="form-group mb-3" id="showdates mb-3">
            <label class="fs-6 mb-1">{{ __('cycles.Date/Time') }}:</label>
            <input class='date width-200 form-control dateTime' type="text" id="sdate" name="date" value="" required>
          </div>
          <div class="form-group mb-3">
            <label class="fs-6 mb-1">{{ __('cycles.Meeting period') }}:</label>
            <select name="period" class="select form-control" id="period">
              <option value="30">30 {{ __('cycles.Minutes') }}</option>
              <option value="60">1 {{ __('cycles.Hour') }}</option>
              <option value="120">2 {{ __('cycles.Hours') }}</option>
              <option value="180">3 {{ __('cycles.Hours') }}</option>
              <option value="240">4 {{ __('cycles.Hours') }}</option>
              <option value="300">5 {{ __('cycles.Hours') }}</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary ml-1">
            <span>{{ __('cycles.Submit') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
@stop

@section('pagescripts')
<script>
  // Date & TIme
  const dateTimePickr = $('.dateTime');
  if (dateTimePickr.length) {
    dateTimePickr.flatpickr({
      enableTime: true
    });
  }

    const headers = {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')};

    function edit(id) {
      $.ajaxSetup({headers});
      $.ajax({
        type: "POST",
        url: "/dashboard/finalScreening/session",
        data: {
          "id": id,
        },
        success: function(response) {
          $('#sid').val(id);
          $('#sdate').val(response.zoom_date);
          $('#period').val(response.period);
          // $('#ModifyRequest').modal('show');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }

    function DoChange(set) {
      switch (set) {
      case 1:
        $('#stepForm').modal('show');
        break;
      case 2:
        $.ajaxSetup({headers});
        Swal.fire({
        title: '{{ __('cycles.sure delete step') }}',
        text: "{{ __('admin.Recover') }}",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '{{ __('admin.DYes') }}',
        cancelButtonText: '{{ __('admin.No') }}'
        }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
          type: "POST",
          url: "/dashboard/cycles/modify",
          data: {
            "stepid": {{ $cstep->id ?? 0 }},
            "type": 6,
          },
          success: function(response) {
            console.log(response);
            location.href = '/dashboard/final';
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
          });
        }
        });
        break;
      }
    }

    $('#checkAll').click(function() {
      $("input[type=checkbox]").prop('checked', $(this).prop('checked'));
    });

    function addzoom(company) {
      $('#cid').val(company);
      $('#addzoom').modal('show');
    }

    function edit(company) {
      $('#cid').val(company);
      $('#ModifyRequest').modal('show');
    }

    function deleteme(id) {
      let url = '/dashboard/final/delsession/' + id;
      let token = '{{ csrf_token() }}';
      Swal.fire({
          title: "{{ __('admin.SureDelete') }}",
          text: "{{ __('admin.Recover') }}",
          icon: 'warning',
          showCancelButton: true,
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
                window.location.href = '/dashboard/final';
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