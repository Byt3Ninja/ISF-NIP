@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
@stop

@section('style')
  <style>
    .form-card {
      width: 70%;
      margin: 2rem auto 4rem auto;
    }

    @media (max-width: 768px) {
      .form-card {
        width: 100%;
      }
    }
  </style>
@stop

@section('content')
  <!-- Content wrapper -->
  <div class="">
    <!-- Content -->
    <div class="">
      <div class="card form-card">
        <div class="card-header">
          <h3 class="card-title border-bottom fw-bold pb-3">
            {{ __('messages.SendMessages') }}
          </h3>
        </div>
        <div class="card-body">
          <form method="post" action="/dashboard/sendmessage" enctype="multipart/form-data">
            {!! csrf_field() !!}
            <div class="form-group mb-4">
              <label class="form-label fw-semibold fs-6 mb-1">{{ __('messages.SelectUsers') }}:</label>
              <select id="select-type" class="form-select" name="type">
                <option value="0">{{ __('messages.PortalManagers') }}</option>
                <option value="1">{{ __('messages.CycleUsers') }}</option>
                <option value="2">{{ __('messages.Mentors') }}</option>
                @if (Auth::user()->hasAnyRole('Admin|OrganizationManager') && $cycles->count())
                 <option value="3">{{ __('admin.Organizations') }}</option>
                @endif
              </select>
            </div>
            <div class="mb-4" style="display: none;" id="users">
              @if (Auth::user()->hasAnyRole('Admin|OrganizationManager') && $cycles->count())
                <div class="mb-4" id="cycle-selection">
                  <label class="form-label fs-6 fw-semibold mb-1">{{ __('admin.SelectProgram') }}:</label>
                  <select class="form-control select2" name="cycle_id" onchange="filterUsersByCycle(this.value)">
                    @foreach ($cycles as $cycle)
                      <option value="{{ $cycle->id }}">{{ $cycle->title }}</option>
                    @endforeach
                  </select>
                </div>
              @endif
              @if ($users)
                <label class="form-label fs-6 fw-semibold mb-1" for="users">{{ __('admin.select_startups') }}:</label>
                <select class="select2 form-select" name="users[]"
                  data-placeholder="{{ __('admin.select_startups') }}" multiple>
                  @foreach ($users as $index => $user)
                    @if ($user->user)
                      <option value="{{ $user->user->id }}" {{ $index === 0 ? 'selected' : '' }}>
                        {{ $user->user->{'first_name_' . $l} . ' ' . $user->user->{'last_name_' . $l} . ' - (' . $user->{'name_' . $l} . ')' }}
                      </option>
                    @endif
                  @endforeach
                </select>
              @endif
            </div>

            <div class="mb-4" style="display: none;" id="mentors">
              @if ($mentors)
                <label class="form-label fs-6 fw-semibold mb-1" for="mentors">{{ $l == 'ar' ? 'اختر المدربين' : 'Select mentors' }}:</label>
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

            @if (Auth::user()->hasAnyRole('Admin|OrganizationManager'))
              <div class="mb-4" style="display: none;" id="organizations">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('admin.Select Organizations') }}:</label>
                <select class="select2 form-select" name="organizations[]" multiple
                  data-placeholder="{{ __('admin.Select Organizations') }}">
                  @foreach ($organizations as $index => $organization)
                    <option value="{{ $organization->id }}" {{ $index === 0 ? 'selected' : '' }}>
                      {{ $organization->{'name_' . app()->getLocale()} }}
                    </option>
                  @endforeach
                </select>
              </div>
            @endif
            <div class="mb-4">
              <label class="form-label fs-6 fw-semibold mb-1">{{ __('messages.Subject') }}:</label>
              <input name="subject" type="text" placeholder="{{ __('messages.Subject') }}" class="form-control"
                required>
            </div>
            <div class="form-group">
              <label class="form-label fs-6 fw-semibold mb-1">{{ __('messages.yourMessage') }}:</label>
              <textarea class="form-control" name='message' rows="4" placeholder="{{ __('messages.Write your message') }}"
                required>
              </textarea>
            </div>
            <div class="w-100 d-flex justify-content-end align-items-center mt-6">
              <button type="submit" class="btn btn-primary send-btn">
                {{ __('messages.Send') }}
                <i class="ti ti-send ti-16px ms-2"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- / Content -->
  </div>
  <!-- / Content wrapper -->
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/forms-selects.js"></script>
  <script>
    const typeSelect = document.getElementById('select-type');
    typeSelect.addEventListener('change', function() {
      let type = +this.value;
      console.log('type', type);
      menus(type);
    });

    function menus(menu) {
      if (menu === 0) {
        $('#users, #mentors, #organizations').hide();
      } else if (menu === 1) {
        $('#users').show();
        $('#mentors, #organizations').hide();
      } else if (menu === 2) {
        $('#mentors').show();
        $('#users, #organizations').hide();
      } else if (menu === 3) {
        $('#organizations').show();
        $('#users, #mentors').hide();
      } else {
        $('#users, #mentors, #organizations').hide();
      }
    }

    function filterUsersByCycle(cycleId) {
      if (!cycleId) return null;
      $.ajax({
        url: "{{ route('messages.recipients_list_by_program') }}",
        method: "GET",
        data: {
          cycle_id: cycleId
        },
        success: function(response) {
          let userSelect = $('select[name="users[]"]');
          userSelect.empty();
          response.users.forEach(user => {
            userSelect.append(`<option value="${user.id}">${user.name}</option>`);
          });
          userSelect.trigger('change');
        }
      });
    }
  </script>
@stop
