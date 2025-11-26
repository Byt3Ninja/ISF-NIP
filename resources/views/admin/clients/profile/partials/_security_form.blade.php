@php
$securityTabs = [
  'change_password' => __('admin.Change_Password'),
  'activity_log' => __('admin.Activity_Log'),
];

$passwordRules = [
  $l == 'en' ? 'Should be between 8 and 20 characters' : 'يجب أن تكون بين 8 و 20 حرفًا',
  $l == 'en' ? 'At least one uppercase character' : 'على الأقل حرف كبير واحد',
  $l == 'en' ? 'At least one number or symbol' : 'على الأقل رقم أو رمز',
];
@endphp

<div id="securitySection">
  <div class="d-flex align-items-center mb-4">
    <ul class="nav nav-pills">
      @foreach ($securityTabs as $key => $tab)
      <li class="nav-item me-2">
        <a class="nav-link {{ $key === 'change_password' ? 'active' : '' }}" href="#{{ $key }}" data-bs-toggle="tab">{{
          $tab }}</a>
      </li>
      @endforeach
    </ul>
  </div>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="change_password">
      <!-- Change Password -->
      {{-- <h5 class="mb-3">{{ __('admin.Change_Password') }}</h5> --}}
      <form id="changePasswordForm" method="post" action="{{ route('user.profile_update_my_password') }}"
        enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="row">
          <!-- Password Field -->
          <div class="mb-6 col-md-6 form-password-toggle">
            <div class="form-group">
              <label class="form-label fs-6 fw-bold" for="password">{{ __('admin.Password') }}</label>
              <div class="input-group input-group-merge">
                <input type="password" class="form-control" id="password" placeholder="{{ __('admin.Password') }}"
                  name="password" required autocomplete="new-password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,20}"
                  title="{{ __('admin.Password_should_be_strong') }}" />
                <span class="input-group-text cursor-pointer toggle-password"><i class="ti ti-eye-off"></i></span>
              </div>
              <div class="invalid-feedback">{{ __('admin.Password_should_be_strong') }}</div>
            </div>
          </div>

          <!-- Confirm Password Field -->
          <div class="mb-6 col-md-6 form-password-toggle">
            <div class="form-group">
              <label class="form-label fs-6 fw-bold" for="password-confirm">{{ __('admin.Confirm') }}</label>
              <div class="input-group input-group-merge">
                <input type="password" class="form-control" id="password-confirm"
                  placeholder="{{ __('admin.Confirm') }}" name="password_confirmation" required
                  autocomplete="new-password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,20}"
                  title="{{ __('admin.Password_should_be_strong') }}" />
                <span class="input-group-text cursor-pointer toggle-password"><i class="ti ti-eye-off"></i></span>
              </div>
              <div class="invalid-feedback">{{ __('admin.Passwords_do_not_match') }}</div>
            </div>
          </div>
        </div>

        <!-- Password Requirements -->
        <h6 class="text-body">{{ __('admin.Password_Requirements') }}:</h6>
        <ul class="ps-4 mb-0">
          @foreach ($passwordRules as $key => $rule)
          <li class="{{ $key != array_key_last($passwordRules) ? 'mb-4' : '' }}">{{ $rule }}</li>
          @endforeach
        </ul>

        <!-- Form Buttons -->
        <div class="mt-6">
          <button type="submit" class="btn btn-primary me-3">{{ __('admin.Save_Changes') }}</button>
          <button type="reset" class="btn btn-label-secondary" onclick="location.reload()">
            {{ __('admin.Reset') }}
          </button>
        </div>
      </form>
      <!--/ Change Password -->
    </div>
    <div class="tab-pane fade" id="activity_log">
      <!-- Activity Log Table -->
      @if ($activities->isNotEmpty())
      <table class="table mt-4">
        <thead class="table-light">
          <tr>
            <th>@lang('admin.browser')</th>
            <th>@lang('admin.device')</th>
            <th>@lang('admin.platform')</th>
            <th>@lang('admin.IP_Address')</th>
            <th>@lang('admin.Location')</th>
            <th>@lang('admin.Login_Time')</th>
          </tr>
        </thead>
        <tbody>
          @foreach($activities as $activity)
          <tr>
            <td>{{ $activity->browser }}</td>
            <td>{{ $activity->device }}</td>
            <td>{{ $activity->platform }}</td>
            <td>{{ $activity->ip_address }}</td>
            <td>{{ $activity->location }}</td>
            <td>{{ $activity->activity_time->format('d M Y, H:i') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @else
      <p class="text-muted">@lang('admin.No_activities_logged')</p>
      @endif
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password-confirm');
    const form = document.getElementById('changePasswordForm');

    // Real-time validation for matching passwords
    confirmPassword.addEventListener('input', () => {
      if (confirmPassword.value !== password.value) {
        confirmPassword.setCustomValidity("{{ __('admin.Passwords_do_not_match') }}");
      } else {
        confirmPassword.setCustomValidity('');
      }
    });

    // Show feedback on invalid fields
    form.addEventListener('submit', (e) => {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        form.classList.add('was-validated');
      } else {
        confirmPassword.setCustomValidity(''); // Clear custom validity if valid
      }
    });
  });
</script>