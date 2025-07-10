<div class="row" id="2faSection">
  <div class="col-md-8">
    <div class="">
      <div class="card-header"><strong>{{ __('admin.Two_Factor_Authentication') }}</strong></div>
      <div class="card-body">
        <p>{{ __('admin.Two_Factor_Authentication_Description') }}</p>

        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        @if (session('success'))
          <div class="alert alert-success">
            {{ session('success') }}
          </div>
        @endif

        @if ($data['user']->google2fa_secret == null)
          <form class="form-horizontal" method="POST" action="{{ route('security.2fa_generate_secret') }}">
            {{ csrf_field() }}
            <div class="form-group">
              <button type="submit" class="btn btn-primary">
                {{ __('admin.Generate_Secret_Key_to_Enable_2FA') }}
              </button>
            </div>
          </form>
        @elseif(!$data['user']->two_factor_enabled)
          1. {{ __('admin.Scan_this_QR_code_with_your_Google_Authenticator_App') }}
          <code>{{ $data['secret'] }}</code><br />
          <img src="{{ $data['google2fa_url'] }}" alt="">
          <br /><br />
          2. {{ __('admin.Enter_the_pin_from_Google_Authenticator_app') }}<br /><br />
          <form class="form-horizontal" method="POST" action="{{ route('security.2fa_enable') }}">
            {{ csrf_field() }}
            <div class="form-group{{ $errors->has('verify-code') ? ' has-error' : '' }}">
              <label for="secret" class="control-label">{{ __('admin.Authenticator_Code') }}</label>
              <input id="secret" type="password" class="form-control col-md-4" name="secret" required>
              @if ($errors->has('verify-code'))
                <span class="help-block">
                  <strong>{{ $errors->first('verify-code') }}</strong>
                </span>
              @endif
            </div>
            <br>
            <button type="submit" class="btn btn-primary">
              {{ __('admin.Enable_2FA') }}
            </button>
          </form>
        @elseif($data['user']->two_factor_enabled)
          <div class="alert alert-success">
            {{ __('admin.2FA_is_currently') }} <strong>{{ __('admin.enabled') }}</strong>
            {{ __('admin.on_your_account') }}.
          </div>
          <p>{{ __('admin.If_you_are_looking_to_disable_Two_Factor_Authentication') }}</p>
          <form class="form-horizontal" method="POST" action="{{ route('security.2fa_disable') }}">
            {{ csrf_field() }}
            <div class="form-group{{ $errors->has('current-password') ? ' has-error' : '' }}">
              <label for="change-password" class="control-label">{{ __('admin.Current_Password') }}</label>
              <input id="current-password" type="password" class="form-control col-md-4" name="current-password"
                required>
              @if ($errors->has('current-password'))
                <span class="help-block">
                  <strong>{{ $errors->first('current-password') }}</strong>
                </span>
              @endif
            </div>
            <br>
            <button type="submit" class="btn btn-primary mt-4">{{ __('admin.Disable_2FA') }}</button>
          </form>
        @endif
      </div>
    </div>
  </div>
</div>
