@extends('layouts.admin')

@section('style')
  <style>
    .radioLabel {
      font-size: 14px;
      font-weight: 500;
      color: #000;
      margin-left: 5px;
    }
  </style>
@stop


@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      {{-- <div class="content-header row">
        <div class="content-header-left col-12 mb-2 mt-1">
          <div class="row breadcrumbs-top">
            <div class="col-12">
              <h5 class="content-header-title float-left pr-1 mb-0">{{ __('messages.NewMessage') }}</h5>
              <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb p-0 mb-0">
                  <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a>
                  </li>
                  <li class="breadcrumb-item active">{{ __('messages.SendMessages') }}
                  </li>
                </ol>
              </div>
            </div>
          </div>
        </div>
      </div> --}}
      <div class="content-body">
        <section>
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header border-bottom">
                  <h4 class="card-title d-flex align-items-center">
                    <i class='ti ti-message-dots ti-28px'></i>
                    <span class="mx-2">{{ __('messages.SendMessages') }}</span>
                  </h4>
                  <div class="heading-elements">
                    <ul class="list-inline mb-0">
                      <li>
                        <a data-action="close">
                          
                        </a>
                      </li>
                    </ul>
                  </div>
                  <div class="heading-elements">
                    <ul class="list-inline mb-0">
                      <li>
                        <a data-action="close">
                          
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="card-body">
                  <form method="post" action="/dashboard/sendmessage" enctype="multipart/form-data">
                    <div class="modal-body mb-6 mt-3">
                      {!! csrf_field() !!}
                      <h5 class="card-title d-flex align-items-center mb-3">
                        <i class="ti ti-user-plus ti-24px"></i>
                        <span class="mx-2">{{ __('messages.SelectUsers') }}</span>
                      </h5>
                      <ul class="list-unstyled d-flex align-items-center gap-6">
                        <li class="d-inline-block mr-2 mb-1">
                          <fieldset>
                            <div class="radio radio-shadow">
                              <input value="0" type="radio" id="radioshadow1" name="type" checked=""
                                onclick="menus(0)">
                              <label class="radioLabel" for="radioshadow1">{{ __('messages.PortalManagers') }}</label>
                            </div>
                          </fieldset>
                        </li>
                        @if ($users)
                          <li class="d-inline-block mr-2 mb-1">
                            <fieldset>
                              <div class="radio radio-shadow">
                                <input value="1" type="radio" id="radioshadow2" name="type" onclick="menus(1)">
                                <label class="radioLabel" for="radioshadow2">{{ __('messages.CycleUsers') }}</label>
                              </div>
                            </fieldset>
                          </li>
                        @endif
                        @if ($mentors)
                          <li class="d-inline-block mr-2 mb-1">
                            <fieldset>
                              <div class="radio radio-shadow">
                                <input value="2" type="radio" id="radioshadow3" name="type" onclick="menus(2)">
                                <label class="radioLabel" for="radioshadow3">{{ __('messages.Mentors') }}</label>
                              </div>
                            </fieldset>
                          </li>
                        @endif
                      </ul>
                      <div class="mb-4" style="display: none;" id="users">
                        @if ($users)
                          <label class="form-label"
                            for="users">{{ $l == 'ar' ? 'اختر الشركات' : 'Select companies' }}</label>
                          <select class="select2 form-select" name="users[]"
                            data-placeholder="{{ $l == 'ar' ? 'اختر الشركات' : 'Select companies' }}" multiple>
                            @foreach ($users as $index => $user)
                              <option value="{{ $user->user->id }}" {{ $index === 0 ? 'selected' : '' }}>
                                {{ $user->user->{'first_name_' . $l} . ' ' . $user->user->{'last_name_' . $l} . ' - (' . $user->{'name_' . $l} . ')' }}
                              </option>
                            @endforeach
                          </select>
                        @endif
                      </div>

                      <div class="mb-4" style="display: none;" id="mentors">
                        @if ($mentors)
                          <label class="form-label"
                            for="mentors">{{ $l == 'ar' ? 'اختر المدربين' : 'Select mentors' }}</label>
                          <select class="select2 form-select" name="mentors[]"
                            data-placeholder="{{ $l == 'ar' ? 'اختر المدربين' : 'Select mentors' }}" multiple>
                            @foreach ($mentors as $index => $mentor)
                              <option value="{{ $mentor->id }}" {{ $index === 0 ? 'selected' : '' }}>
                                {{ $mentor->{'first_name_' . $l} . ' ' . $mentor->{'last_name_' . $l} }}
                              </option>
                            @endforeach
                          </select>
                        @endif
                      </div>
                      <div class="mb-4">
                        <label class="form-label">{{ __('messages.Subject') }}</label>
                        <input name="subject" type="text" placeholder="{{ __('messages.Subject') }}"
                          class="form-control" required="">
                      </div>
                      <div class="form-group">
                        <label class="form-label">{{ __('messages.yourMessage') }}:</label>
                        <textarea class="form-control" name='message' rows="8" placeholder="{{ __('messages.Write your message') }}"
                          required></textarea>
                      </div>
                    </div>

                    <button type="submit" class="btn btn-primary send-btn">
                      <i class="ti ti-send"></i>
                      <span class="d-none d-sm-inline mx-3">{{ __('messages.Send') }}</span>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
    <!-- / Content -->
  </div>
  <!-- / Content wrapper -->
@stop

@section('scripts')
  <script>
    function menus(menu) {
      if (menu === 1) {
        $('#users').show();
        $('#mentors').hide();
      } else if (menu === 2) {
        $('#users').hide();
        $('#mentors').show();
      } else {
        $('#users').hide();
        $('#mentors').hide();
      }
    }
  </script>

  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/pages-account-settings-account.js"></script>
@stop
