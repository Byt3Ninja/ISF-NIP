@extends('layouts.admin')

@section('title', __('admin.Builder'))
@section('subTitle', __('admin.Edit form'))

@section('vendorstyle')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/vendors{{$l=='ar'?'-rtl':''}}.min.css">
@stop

@section('style')
  <style>
    .section {
      padding: 0 30px;
    }
    .section button.close {
      display: none;
    }

    #myModal {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: 9999;
    }

    #myModal .modal-dialog .modal-content form label {
      margin-bottom: 0.2rem;
    }
    #myModal .modal-dialog .modal-content button {
      margin: 0 0.2rem;
    }
    #myModal .modal-dialog .modal-content form input {
      margin-bottom: 1rem;
    }
    #myModal .modal-dialog .modal-content form .modal-body .form-group:nth-child(3) {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 2rem;
    }
    #myModal .modal-dialog .modal-content form .modal-body .form-group:nth-child(2) div {
      display: flex;
      flex-direction: row;
      gap: 0.5rem;
    }
  
    #myModal .modal-dialog .modal-content form .modal-body .form-group:nth-child(3) label {
      margin: 0 0.2rem;
    }

    #myModal .modal-dialog .modal-content .modal-header button {
      display: none;
    }
    #myModal .modal-dialog .modal-content .modal-header h5 {
      margin-bottom: 1rem;
    }
    #myModal .modal-dialog .modal-content {
      border: none;
      box-shadow: none;
      shadow: none;
    }
    #myModal .modal-dialog {
      background-color: #FFF;
      padding: 2rem;
      border-radius: 0.3rem;
      width: 90%;
      margin: auto; /* Center horizontally */
    }

    @media (min-width: 992px) {
      #myModal .modal-dialog {
        max-width: 50%;
      }
    }

    .bg-light {
      background-color: #92989e2e !important;
    }

    .bg-light .card-header,
    .bg-light .card-footer {
      background-color: #32181817;
      font-size: 1.2em;
    }

    hr {
      margin: 0 0 15px;
    }

    .card-header {
      color: #FFF;
    }
    .card-title {
      color: #000;
    }
  </style>
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        <section id="kanban-wrapper">
          <div class="row">
            <div class="col-xl-12 col-md-12 col-sm-12">
              <div class="card mb-4">
                <div class="card-header" style="padding-bottom: 15px;">
                  <h4 class="card-title">{{ __('builder.Forms builder') }}</h4>
                </div>
                <hr>
                <div class="card-body">
                  <br>

										<form method="GET" action="/dashboard/show-builder">
										  <div class="d-flex align-items-center mx-2">
											<div style="display: flex; align-items: center;">
											<button
											  class="btn bg-primary p-3 d-none d-md-flex justify-content-center align-items-center" 
											  style="border-radius: 17px; width: 40px; height: 40px;"
											   type="submit"
											>
											  <i class="ti ti-filter text-white ti-24px"></i>
											</button>
										  </div>
										  <!-- Search Bar -->
										  <div class="position-relative me-3 d-none d-md-flex">
                        <select name="org_id" class="form-control">
                          <option value="0">{{ __('admin.All') }}</option>
                          @php
                          $organizations = App\Organization::all();
                          @endphp
                          @foreach ($organizations as $organization) 
                          <option value="{{$organization->id}}" {{ $organization->id == request('org_id') ? 'selected' : '' }}> {{ $organization->{'name_' . $l} }}</option>
                        @endforeach
                        </select>
                        </div>

										</div>
										</form>
										<hr>
                  <label class="form-label mb-2 fs-6">{{ __('builder.Select from') }}:</label>
                  <fieldset class="form-group d-flex align-items-center w-100">
                    <div
                      style="width: 55%; float: {{ $l == 'ar' ? 'right' : 'left' }};
                      margin-left: {{ $l == 'ar' ? '10px' : '0' }}; margin-right: {{ $l == 'ar' ? '0' : '10px' }};
                    ">
                      <select class="select2 form-control" id="FormSelect"
                        onchange="window.open('/dashboard/show-builder/'+this.options[this.selectedIndex].value,'_self');">
                        @foreach ($builderForms as $form)
                          <option value="{{ $form->id }}" {{ $title->id == $form->id ? 'selected' : '' }}>
                            {{ $form->form_name }}
                            {{ !is_null($form->organization) ? ' - ' . $form->organization->name : '' }}
                            @if ($form->cycles->count())
                              @foreach ($form->cycles as $cycle)
                                {{ $cycle->title }} @if (!$loop->last)
                                  -
                                @endif
                              @endforeach
                            @endif
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div>
                      <button class="btn btn-label-primary"
                        onclick="showForm(0,{{ $title->id }},'{{ $title->form_name }}',{{ $title->organization_id }})">
                        <i class="ti ti-pencil"></i> <span class="mx-1">{{ __('builder.Edit') }}</span>
                      </button>
                      <button class="btn btn-label-info"
                        onclick="showForm(1,{{ $title->id }},'{{ $title->form_name }} {{ __('builder.Copy.') }}')">
                        <i class="mx-2 ti ti-copy"></i> <span class="mx-1">{{ __('builder.Copy') }}</span>
                      </button>

                      <button class="btn btn-label-success" onclick="showForm(2,0,null)">
                        <i class="mx-2 ti ti-plus"></i> <span class="mx-1">{{ __('builder.New') }}</span>
                      </button>
                      <button class="btn btn-label-danger" onclick="DoChange(3,{{ $title->id }},null)">
                        <i class="mx-2 ti ti-trash"></i> <span class="mx-1">{{ __('builder.Delete') }}</span>
                      </button>
                    </div>
                  </fieldset>
                </div>
              </div>
            </div>
            <div class="col-xl-12 col-md-12 col-sm-12">
              <div class="card">
                <div class="card-header" style="padding-bottom: 0px;">
                  <p>{{ __('builder.Drag') }}<a href="/dashboard/show-form/{{ $title->id }}">
                      ({{ __('builder.Preview') }})</a>.</p>
                </div>
                <hr>
                <div class="section">
                  <div class="row" id="builder-app"></div>
                  <div id="modal-root"></div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <!--Modify custom form step Modal -->
  <div class="modal fade text-left" id="AddForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-body">
          @if (is_null(auth()->user()->organization_id))
            <div class="mb-2" id="mmenu1">
              <label class="form-label">{{ __('builder.Choose Organization') }}: </label>
              <div class="form-group mb-0">
                <select class="select2 form-control" id="organization_id" name="organization_id" required>
                  @foreach (\App\Organization::all() as $org)
                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          @else
            <input type="hidden" name="organization_id" value="{{ auth()->user()->organization_id }}"
              id="organization_id">
          @endif

          <label class="form-label">{{ __('builder.new form') }}: </label>
          <div class="form-group">
            <input id='mtitle' name='title' type="text" placeholder="{{ __('cycles.Title') }}"
              class="form-control" required>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
          </button>
          <button type="button" class="btn btn-primary ml-1" id="modelSubmit">
            <span class="d-none d-sm-block">{{ __('cycles.Save') }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
@stop

@section('scripts')
  <script> 
    document.addEventListener('DOMContentLoaded', () => {
      const formBuilderContainer = document.getElementById('builder-app');
      const formBuilderButton = formBuilderContainer.querySelector('.btn-secondary');
      const secondColChild = formBuilderContainer.querySelectorAll('.col')[1];
      const secondColChildHeader = secondColChild.querySelector('.card-header');
            
      secondColChildHeader.classList.add('text-white');
      formBuilderButton.classList.replace('btn-secondary', 'btn-primary');
    })
  </script>
  
  <script>
    function showForm(set, id, title, organization_id) {
      $('#mtitle').val(title);
      if (typeof(organization_id) != "undefined") {
        $('#organization_id').val(organization_id)
      }
      $('#modelSubmit').attr('onclick', `DoChange(${set},${id},'${title}',${organization_id})`)
      $('#AddForm').modal('show')
    }

    function DoChange(set, id, title, organization_id = 0) {
      if (set === 0) {
        title = $('#mtitle').val();
        organization_id = $('#organization_id').val();
        {{-- title = prompt("{{__('builder.new title')}}",title); --}}
      } else if (set === 1) {
        title = $('#mtitle').val();
        organization_id = $('#organization_id').val();
        {{--                title = prompt("{{__('builder.new copied')}}:", title + ' {{__('builder.Copy.')}}'); --}}
      } else if (set === 2) {
        {{-- title = prompt("{{__('builder.new form')}}"); --}}
        title = $('#mtitle').val();
        organization_id = $('#organization_id').val();
      } else if (set === 3) {
        title = confirm("{{ __('builder.deleting form') }}?")
      }
      if (!title) {
        return false;
      }
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/builder/" + set,
        data: {
          "id": id,
          "title": title,
          "organization_id": organization_id
        },
        success: function(response) {
          // console.log(response);
          location.href = '/dashboard/show-builder/' + response;
        },
        error: function(error) {
          // console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
    window._rav = window._rav || {};
    _rav.save_route = "{{ '/dashboard/form/' . $title->id . '/save-form' }}";
    _rav.boardData = @json($builder_data);
  </script>
  <script src="{{ asset('js/formbuilder.js') }}"></script>
@stop
