@php
$passwordRules = [
  $l == 'en' ? 'Should be between 8 and 20 characters' : 'يجب أن تكون بين 8 و 20 حرفًا',
  $l == 'en' ? 'At least one uppercase character' : 'على الأقل حرف كبير واحد',
  $l == 'en' ? 'At least one number or symbol' : 'على الأقل رقم أو رمز',
];
@endphp

<div id="securitySection">
  <!-- Change Password -->
  <h5 class="card-header">{{ __('admin.Change_Password') }}</h5>
  <div class="card-body pt-1">
    <form
      id="changePasswordForm"
      method="post"
      action="{{ route('update_my_profile_password') }}"
      enctype="multipart/form-data"
    >
      {!! csrf_field() !!}
      <div class="row">
        <!-- Password Field -->
        <div class="mb-6 col-md-6 form-password-toggle">
          <div class="form-group">
            <label class="form-label fs-6 fw-bold" for="password">{{ __('admin.Password') }}</label>
            <div class="input-group input-group-merge">
              <input
                type="password"
                class="form-control"
                id="password"
                placeholder="{{ __('admin.Password') }}"
                name="password"
                required
                autocomplete="new-password"
                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,20}"
                title="{{ __('admin.Password_should_be_strong') }}"
              />
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
              <input
                type="password"
                class="form-control"
                id="password-confirm"
                placeholder="{{ __('admin.Confirm') }}"
                name="password_confirmation"
                required
                autocomplete="new-password"
                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,20}"
                title="{{ __('admin.Password_should_be_strong') }}"
              />
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
  </div>
  <!--/ Change Password -->
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
