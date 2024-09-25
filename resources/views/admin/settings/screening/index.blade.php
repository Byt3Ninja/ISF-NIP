@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
  @php $types = ['onetoall'=>'Assign One to all users','grouptoall'=>'Assign Group to all users','grouptogroup'=>'Assign Group to group of users']
  @endphp
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        @if ($cstep)
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
            <div class="row match-height">
              <div class="col-xl-12 col-md-12 col-sm-12">
                <div class="card mb-4">
                  <div class="card-header" style="padding-bottom: 15px;">
                    <h4 class="card-title">{{ __('cycles.Portal screening list') }}</h4>
                  </div>
                  <hr>
                  <div class="card-body">
                    <label class="form-label fs-5">{{ __('cycles.Select step from list to manage') }}</label>
                    <fieldset class="d-flex flex-column flex-md-row gap-3">
                      <select 
                        id="cstep" 
                        class="select2 form-control rounded"
                        style="margin-left: {{ $l == 'ar' ? '10px' : '0' }}; margin-right: {{ $l == 'ar' ? '0' : '10px' }}; width: 60%;"
                        onchange="window.open('/dashboard/'+this.options[this.selectedIndex].value+'/screening','_self');"
                      >
                        @foreach ($steps as $step)
                          <option value="{{ $step->id }}" {{ $step->id == $id ? 'selected' : '' }}>{{ $step->title }}
                            ({{ __('cycles.Cycle') }} "{{ $step->cycle->title }}" -
                            {{ __('cycles.Start') }} {{ $step->from->format('d-m') }}) -
                            {{ optional(optional($step->cycle)->organization)->name }}</option>
                        @endforeach
                      </select>
                      <div class="input-group-append">
                        <button class="btn btn-label-secondary"
                        onclick="window.location.href='/dashboard/screening/results/{{ $id }}'">
                        {{-- <i class="ti ti-user"></i>  --}}
                        <span class="mx-1">{{ __('cycles.Results') }}</span>
                      </button>
                      <a class="btn btn-label-success" href="/dashboard/cycles/{{ $cstep->cycle_id }}">
                        {{-- <i class="ti ti-eye"></i>  --}}
                        <span class="mx-1">{{ __('cycles.Cycle') }}</span>
                      </a>
                      <button class="btn btn-label-primary" onclick="DoChange(1)">
                        <i class="ti ti-pencil"></i> <span class="mx-1">{{ __('cycles.Edit') }}</span>
                      </button>
                      <button class="btn btn-label-danger" onclick="DoChange(2)">
                        <i class="ti ti-trash"></i> <span class="mx-1">{{ __('cycles.Delete') }}</span>
                      </button>
                      </div>
                    </fieldset>
                    <br />
                  </div>
                </div>
              </div>
            </div>
          </section>
          <section class="row">
            <div class="col-xl-12 col-md-12 col-sm-12">
              <div class="card mb-4">
                <div class="card-header">
                  <h4 class="card-title success">{{ $cstep->title }}</h4>
                  <p>{{ $cstep->description }}</p>
                  <small class="text-muted">
                    {{ __('cycles.from') }}
                     {{ $cstep->from->format('d-m') }} {{ __('cycles.to') }} {{ $cstep->to->format('d-m-Y') }}
                  </small>
                </div>
                <hr style="margin:-10px 0 5px 0;">
                <div class="card-body">
                  <div class="card-text">
                    <dl class="row">
                      <dt class="col-sm-3 text-right">{{ __('cycles.Cycle') }}</dt>
                      <dd class="col-sm-9">{{ $cstep->cycle->title }} {{ __('cycles.from') }}
                        {{ $cstep->cycle->start->format('d-m') }} {{ __('cycles.to') }}
                        {{ $cstep->cycle->end->format('d-m-Y') }}</dd>
                    </dl>
                    <dl class="row">
                      <dt class="col-sm-3 text-right">{{ __('cycles.Form title') }}</dt>
                      <dd class="col-sm-9">{!! $form->title ?? '<span class="danger">' . __('cycles.NoForm') . '</span>' !!}</dd>
                    </dl>
                    <dl class="row">
                      <dt class="col-sm-3 text-right">{{ __('cycles.Screening type') }}</dt>
                      <dd class="col-sm-9">
                        {{ __('cycles.' . $types[$cstep->data]) }}
                      </dd>
                    </dl>
                    <dl class="row">
                      <dt class="col-sm-3 text-right">{{ __('cycles.Total registered') }}</dt>
                      <dd class="col-sm-9">{{ \App\Company::whereIn('step', [$form->id ?? '', $cstep->id])->count() }}</dd>
                    </dl>
                    <dl class="row">
                      <dt class="col-sm-3 text-right">{{ __('cycles.Registered Judges') }}</dt>
                      <dd class="col-sm-9">{{ $judges->count() }}</dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-12 col-md-12 col-sm-12">
              <div class="card">
                <div class="card-header border-bottom border-light mb-4">
                  <h4 class="card-title primary">{{ __('cycles.' . $types[$cstep->data]) }}</h4>
                  <p>{{ __('cycles.Select judges from the menu') }}:</p>
                </div>
                <div class="card-body">
                  <div class="">
                    <form action="/dashboard/screening/update" method="POST" enctype="multipart/form-data">
                      {!! csrf_field() !!}
                      <input type="hidden" value="{{ $cstep->id }}" name="step">
                      <input type="hidden" name="fid" value="{{ $form->id ?? '' }}">
                      <div class="form-group">
                        @if ($cstep->data == 'grouptogroup')
                          @foreach (json_decode($cstep->list) ?? [] as $key => $clist)
                            <div class="form-row">
                              <p style="margin:5px;">{{ __('cycles.panel list') . ' ' . $key }}</p>
                              <div class="row col-md-12">
                                <div class="col-md-11">
                                  <select name="judges[{{ $key }}][]"
                                    class="select2 form-control panel{{ $key }}" multiple="multiple">
                                    @foreach ($judges as $judge)
                                      <option value="{{ $judge->id }}"
                                        {{ in_array($judge->id, $clist ?? []) ? 'selected' : '' }}>{{ $judge->name }}
                                        ({{ $judge->email }})</option>
                                    @endforeach
                                  </select>
                                </div>
                                @php
                                  $panel = \App\Screening::where('step_id', $cstep->id)
                                      ->where('panel', $key)
                                      ->first();
                                  if ($panel && $panel->users) {
                                      $panel = json_encode($panel->users);
                                  } else {
                                      $panel = '';
                                  }

                                @endphp
                                <input type="hidden" value="{{ $panel }}" name="panel{{ $key }}">
                                <div class="col-md-1"><button type="button" class="btn btn-warning"
                                    onclick="openusers({{ $key }})"><i class="ti ti-edit"></i></button></div>
                              </div>
                            </div>
                          @endforeach
                          @php($key = ($key ?? 0) + 1)
                          <div class="form-row">
                            <p style="margin: 5px">{{ __('cycles.panel list') . ' ' . $key }}</p>
                            <div class="row col-md-12">
                              <div class="col-md-11">
                                <select name="judges[{{ $key }}][]"
                                  class="select2 form-control panel{{ $key }}" multiple="multiple">
                                  @foreach ($judges as $judge)
                                    <option value="{{ $judge->id }}">{{ $judge->{'first_name_' . $l} }}
                                      {{ $judge->{'last_name_' . $l} }} ({{ $judge->email }})</option>
                                  @endforeach
                                </select>
                              </div>

                              <div class="col-md-1 float-right"><button class="btn btn-primary" type="submit"><i
                                    class="ti ti-plus"></i></button></div>
                            </div>
                          </div>
                        @else
                          <label class="form-label fs-5">{{ __('cycles.Judges list') }}</label>
                          <select name="judges[]" class="select2 form-control"
                            {{ $cstep->data == 'onetoall' ? '' : 'multiple="multiple"' }}>
                            {!! $cstep->data == 'onetoall'
                                ? '<option selected disabled>' . __('cycles.Please select one name') . '</option>'
                                : '' !!}
                            @foreach ($judges as $judge)
                              <option value="{{ $judge->id }}"
                                {{ in_array($judge->id, json_decode($cstep->list) ?? []) ? 'selected' : '' }}>
                                {{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }} ({{ $judge->email }})
                              </option>
                            @endforeach
                          </select>
                        @endif
                      </div>
                      <div class="custom-control custom-switch custom-control-inline mt-4">
                        <span>{{ __('cycles.Notify them') }}</span>&nbsp;
                        <input name="notify" type="checkbox" class="custom-control-input" id="customSwitch1">
                        <label class="custom-control-label mr-1" for="customSwitch1">
                        </label>
                        <br><br>
                      </div>
                      <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                          <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('admin.Update') }}</button>
                        </div>
                      </div>

                    </form>
                  </div>
                </div>
              </div>
            </div>
          </section>
        @else
          <div class="card">
            <div class="card-header border-bottom">
              <h4 class="card-title">{{ __('cycles.Error') }}</h4>
            </div>
            <div class="card-body">
              {{ __('cycles.No records screening from') }} 
              <a href="/dashboard/cycles">{{ __('cycles.Cycles') }}</a>.
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
  <!-- END: Content-->

  @if ($cstep)
    <!--Screening form Modal -->
    <div class="modal fade text-left" id="stepForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <form class="form" action= "{{ URL::to('dashboard/cycles/modify') }}" method="POST"
            enctype="multipart/form-data">
            {!! csrf_field() !!}
            <div class="modal-body">
              <div id="menu2">
                <div class="form-group mb-3">
                  <label class="form-label fs-5">{{ __('cycles.Choose screening type') }}: </label>
                  <select class="select2 form-control" name="ScreeningSelect" required>
                    @foreach ($types as $k => $type)
                      <option value="{{ $k }}" {{ $cstep->data == $k ? 'selected' : '' }}>
                        {{ __('cycles.' . $type) }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <input type="hidden" name="sid" value="{{ $cstep->id }}">
              <input type="hidden" name="type" value="8">
              <div class="form-group mb-3">
                <label class="form-label fs-5">{{ __('cycles.Title') }}: </label>
                <input value="{{ $cstep->title }}" name='title' type="text"
                  placeholder="{{ __('cycles.Title') }}" class="form-control" required>
              </div>
              <div class="form-group mb-3">
                <label class="form-label fs-5">{{ __('cycles.Start From') }}: </label>
                <input value="{{ $cstep->from->format('Y-m-d') }}" name='fromDate' type="text"
                  class="form-control date" required>
              </div>
              <div class="form-group mb-3">
                <label class="form-label fs-5">{{ __('cycles.to') }}: </label>
                <input value="{{ $cstep->to->format('Y-m-d') }}" name='toDate' type="text"
                  class="form-control date" required>
              </div>
              <div class="form-group mb-3">
                <label class="form-label fs-5">{{ __('cycles.Description') }}: </label>
                <textarea name='description' placeholder="{{ __('cycles.Description') }}" class="form-control">{{ $cstep->description }}</textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
              </button>
              <button type="submit" value="submit" class="btn btn-primary ml-1">
                <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @if ($cstep->data == 'grouptogroup')
      <!--Users form -->
      <div class="modal fade text-left" id="usersForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <form class="form" action= "{{ URL::to('dashboard/screening/add') }}" method="POST"
              enctype="multipart/form-data">
              {!! csrf_field() !!}
              <div class="modal-body">
                <div class="form-group mb-0">
                  <select class="select2 form-control" name="panelusers[]" id="panelusers" multiple>

                  </select>
                </div>
                <input type="hidden" name="sid" value="{{ $cstep->id }}">
                <input type="hidden" name="pid" value="0" id="pid">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                  <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
                </button>
                <button type="submit" value="submit" class="btn btn-primary ml-1">
                  <span class="d-none d-sm-block">{{ __('cycles.Save') }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    @endif
  @endif
@stop

@section('pagescripts')
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <script>
    $('.date').datetimepicker({
      timepicker: false,
      format: 'Y-m-d'
    });

    function DoChange(set) {
      switch (set) {
        case 1:
          $('#stepForm').modal('show');
          break;
        case 2:
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          });
          if (confirm('{{ __('cycles.Are you sure, Delete current step and all its contents?') }}')) {
            $.ajax({
              type: "POST",
              url: "/dashboard/cycles/modify",
              data: {
                "stepid": {{ $cstep->id ?? 0 }},
                "type": 6,
              },
              success: function(response) {
                console.log(response);
                location.href = '/dashboard/screening';
              },
              error: function(error) {
                console.log(error);
                alert("Error Occurred, Try again!");
              }
            });
          }
      }
    }

    function openusers(panel) {
      $('#pid').val(panel);
      $('#cpanel').text(panel);
      let theurl = "/dashboard/screening/get/{{ $form->id ?? 0 }}/{{ $cstep->id ?? 0 }}/" + panel;
      $.ajax({
        type: "GET",
        url: theurl,
        success: function(response) {
          console.log(response);
          $('#panelusers').html(response);
          $('#usersForm').modal('show');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
  </script>
@stop
