@extends('layouts.admin')
@section('title', __('admin.Profile'))
@section('subTitle', __('admin.Edit your profile information'))

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/animate-css/animate.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/form-validation/form-validation.css" />
@stop

@section('style')
<style>
  #phone, #whatsapp {
    width: 100%;
  }

  @media screen and (min-width: 768px) {
    #phone, #whatsapp {
      width: 350px;
    }
  }
  .iti__country-list {
    position: absolute;
    left: 1rem;
    /* right: 0; */
    z-index: 9999;
    list-style: none;
    text-align: left;
    padding: 0;
    margin: 0 0 0 -1px;
    box-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
    background-color: white;
    border: 1px solid #CCC;
    white-space: nowrap;
    max-height: 200px;
    width: 350px;
    overflow-y: scroll;
    overflow-x: auto -webkit-overflow-scrolling: touch;
    background-color: #fff;
  }
</style>
@stop

@php
  $phone_codes = App\Country::all();
  $save_redirect_url = $_user->hasRole('serviceProvider') ? '/dashboard/show/myProfile' : '/dashboard/services_providers';

  $tabs = [
    ['account', 'accountSection', 'users'],
    ['security', 'securitySection', 'lock'],
    ['notifications', 'notificationsSection', 'bell'],
  ];

  $serviceProviderSteps = [
    ['id' => 'basics', 'title' => __('admin.profile_basics'), 'subtitle' => __('admin.provider_basics_subtitle')],
    ['id' => 'booking', 'title' => __('admin.intro_booking'), 'subtitle' => __('admin.provider_booking_subtitle')],
    ['id' => 'stats', 'title' => __('admin.key_stats'), 'subtitle' => __('admin.provider_stats_subtitle')],
    ['id' => 'portfolio', 'title' => __('admin.portfolio'), 'subtitle' => __('admin.provider_portfolio_subtitle')],
    ['id' => 'clients', 'title' => __('admin.clients'), 'subtitle' => __('admin.provider_clients_subtitle')]
  ];

  if ($_user->hasRole('Mentor')) {
    array_splice($tabs, 2, 0, [['info_profile', 'InfoProfileSection', 'users']]);
  }

  $is_security_tab_forced = session('must_change_password') || (auth()->check() && auth()->user()->must_change_password) ? true : false;
  $tabsList = collect($tabs)->map(function ($tab) use ( $is_security_tab_forced ) {
    $is_active = false;
    if( $tab[0] == 'account' && !$is_security_tab_forced ) { $is_active = true; }
    if( $tab[0] == 'security' && $is_security_tab_forced ) { $is_active = true; }
    return ['label' => __('admin.' . $tab[0]), 'target' => $tab[1], 'icon' => $tab[2], 'active' => $is_active];
  })->toArray();

  // Debug: Log the tabs structure
  // dd($tabsList);
@endphp

@section('content')
<!-- Content wrapper -->
<div class="">
  <!-- Content -->
  <div class="">
    <div class="row">
      <div class="col-md-12">
        <!-- Tabs -->
        <nav class="nav-align-top">
          <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-2 gap-lg-0" role="tablist">
            @foreach ($tabsList as $key => $tab)
            <li class="nav-item">
              <a href="#{{ $tab['target'] }}" 
                class="nav-link {{ $tab['active'] ? 'active' : ''}}" 
                data-bs-toggle="tab"
                role="tab" 
                aria-controls="{{ $tab['target'] }}" 
                aria-selected="true" 
                @if (in_array($tab['target'], ['billingSection', 'connectionsSection']))
                  disabled="true"
                @endif
              >
                <i class="ti-sm ti ti-{{ $tab['icon'] }} me-1_5"></i>
                {{ $tab['label'] }}
              </a>
            </li>
            @endforeach
          </ul>
        </nav>
        <!-- /Tabs -->

        <!-- Alerts -->
        @if (session()->has('msg'))
          <x-alert 
            type="{{ session()->get('class') ?? 'danger' }}"
            message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" 
          />
        @endif
        
        @if (count($errors) > 0)
          @foreach ($errors->all() as $error)
            <x-alert type="danger" message="{{ $error }}" />
          @endforeach
        @endif
        @if (session('must_change_password') || (auth()->check() && auth()->user()->must_change_password))
          <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="ti ti-alert-triangle me-2"></i>
          <div>{{ __('admin.Please_change_your_password_after_first_login') }}</div>
        </div>
        @endif
        <!-- /Alerts -->

        <!-- Tab Content -->
        <div class="tab-content card rounded mb-6 {{ $_user->hasRole('serviceProvider') ? 'p-0 shadow-none' : '' }}">
          <!-- Account section -->
          <div class="tab-pane fade {{ !$is_security_tab_forced ? 'show active' : '' }}" id="accountSection">
            @if ($_user->hasRole('serviceProvider'))
              @include('admin.clients.profile.partials._service_provider_wizard')
            @else
              @include('admin.clients.profile.partials._account_info_form')
            @endif
          </div>
          <!-- /Account section -->

          <!-- Security section -->
          <div class="tab-pane fade {{ $is_security_tab_forced ? 'show active' : '' }}" id="securitySection">
            @include('admin.clients.profile.partials._security_form')
          </div>
          <!-- /Security section -->

          <!-- Security section -->
          <div class="tab-pane fade" id="2faSection">
            @include('admin.clients.profile.partials._2fa_security_form')
          </div>
          <!-- /Security section -->

          <!-- Mentor fields -->
          @role('Mentor')
          <!-- Security section -->
          <div class="tab-pane fade" id="InfoProfileSection">
            @include('admin.clients.profile.partials._job_info_form')
          </div>
          @endrole
          <!-- /Mentor fields -->

          <!-- Notifications section -->
          <div class="tab-pane fade" id="notificationsSection">
            <div class="card">
              <h5 class="card-header">
                <i class="ti ti-bell me-1"></i>
                {{ __('admin.Notification_Settings') }}
              </h5>
              @include('admin.clients.profile.partials._notification_settings_form')
            </div>
          </div>
          <!-- /Notifications section -->
        </div>
        <!-- /Tab Content -->

        <!-- Deactivate account -->
        {{-- <div class="card mb-6">
          <h5 class="card-header">{{ __('admin.Delete Account') }}</h5>
          <div class="card-body">
            <div class="mb-6 col-12 mb-0">
              <div class="alert alert-warning">
                <h5 class="alert-heading mb-1">{{ __('admin.Wanna_Delete_Account') }}</h5>
                <p class="mb-0">{{ __('admin.Delete_Account_Alert') }}</p>
              </div>
            </div>
            <form id="formAccountDeactivation" onsubmit="return false">
              <div class="form-check my-8">
                <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" />
                <label class="form-check-label" for="accountActivation">{{ __('admin.Delete_Account_Confirm') }}</label>
              </div>
              <button type="submit" class="btn btn-danger deactivate-account" disabled>
                {{ __('admin.Deactivate_Account') }}
              </button>
            </form>
          </div>
        </div> --}}
        <!-- /Deactivate account -->
      </div>
    </div>
  </div>
  <!-- / Content -->

  <!-- Verfiy Whatsapp Code Modal -->
  <div class="modal fade" id="verifyWhatsappCodeModal" tabindex="-1" aria-labelledby="verifyCodeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content" style="padding: 0.9rem !important;">
        <div class="modal-header position-relative d-grid gap-2">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <h4 class="modal-title" id="verifyCodeModalLabel">{{ __('auth.Verify_Number_With_Code') }}</h4>
          <h6 class="text-secondary mb-0" id="verifyCodeModalLabel">{{ __('auth.Enter_Code_Sent') }}.</h6>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <div id="errorotp" class="alert alert-danger mb-2" style="display: none"></div>
            <div id="sentMessageotp" class="alert alert-success" style="display: none"></div>
          </div>
          <div class="w-100">
            <label class="fs-6 mb-2 text-secondary">
              {{ (__('auth.otp_label')) }}
            </label>
            <div class="d-flex justify-content-between w-100">
              @for ($i = 0; $i < 6; $i++)
                <input 
                  type="text" 
                  onchange="this.value ? this.classList.remove('is-invalid') : this.classList.add('is-invalid')"
                  class="form-control otp-input" 
                  maxlength="1" 
                  style="width: 2.4rem;" 
                  required 
                />
              @endfor
            </div>
            <div id="otp-error" class="invalid-feedback">
              {{ $message ?? '' }}
            </div>
          </div>
          <div class="mt-4">
            <button type="button" onclick="verifyWhatsappCode()" class="btn btn-primary w-100 tracking-wide">
              {{ __('auth.Verify_Code') }}
            </button>
            <div class="d-flex justify-content-center align-items-center gap-1 mt-6 w-100">
              <span>{{ __('auth.Didnt_Receive_Code') }}</span>
              <button type="button" onclick="sendCode('whatsapp')" class="btn btn-link p-0">
                {{ __('auth.Resend_Code') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Verfiy Whatsapp Code Modal -->

  <div class="content-backdrop fade"></div>
</div>
<!-- Content wrapper -->
@stop

@section('scripts')
<!-- Vendors JS -->
<script src="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>

<script>
  $(document).on('click', '.change-email-cta-button', function(e) {
    e.preventDefault();
    var get_form = $(this).data('form');
    $("#" + get_form).submit();
  });
  window.CONFIG = {
    csrf_token: "{{ csrf_token() }}",
    user: @json(Auth::user()),
    phoneCountryCode: "+{{ $client->country_code ? $client->country_code : '' }}",
    whatsappCountryCode: "+{{ $client->country_code_w_a ? $client->country_code_w_a : '' }}",
    countriesIso3Map: @json($countriesIso3Map ?? []),
    routes: {
      sendCode: "{{ route('security.2fa_code_send') }}",
      verifyCode: "{{ route('security.2fa_code_verify') }}",
    }
  }
</script>
@stop

@section('pagescripts')
@if($_user->hasRole('serviceProvider'))
<script src="/vuexy/assets/js/service-provider-wizard.js"></script>
<script>
  const lang = $('html').attr('lang') || 'en';
  function submitFormData() {
    releaseAlert('success', {
      title: lang === 'ar' ? 'تم!' : 'Done!',
      message: lang === 'ar' ? 'تم حفظ الملف الشخصي بنجاح' : 'Profile saved successfully',
    });
    setTimeout(() => { window.location.href = '{{ $save_redirect_url }}'; }, 1500);
  }
</script>
@else
<script>
  window.lang = '{{ $l }}';
</script>

<!-- Page JS -->
@if ($educationFields ?? null)
<script src="/vuexy/assets/js/isf-education-fields.js?v={{ @filemtime(public_path('vuexy/assets/js/isf-education-fields.js')) ?: 1 }}"></script>
@endif
<script src="/vuexy/assets/js/app-edit-profile.js?v={{ @filemtime(public_path('vuexy/assets/js/app-edit-profile.js')) ?: 1 }}"></script>
<script src="/vuexy/assets/js/pages-account-settings-account.js"></script>

<script>
  $(document).ready(function() {
      getStates($('#country_id').val());
      $('#country_id').change(function() {
        getStates($(this).val());
      });
      $('#state_id').change(function() {
        getCities($(this).val());
      });
    });

    function getStates(country_id) {
      var $state = $('#state_id');
      var currentState = '{{ $client->state_id }}';
      var selected = '';
      $.ajax({
        url: "{{ route('utils.locations_list_states_by_country') }}",
        data: {
          country_id: country_id
        },
        success: function(data) {
          $state.html('<option value="" >{{ __('admin.State') }}</option>');
          $.each(data, function(id, value) {
            // selected = '';
            if (currentState === id) {
              selected = 'selected';
              getCities(id)
            } else {
              selected = '';
            }
            $state.append('<option value="' + id + '" ' + selected + '>' + value + '</option>');
            $('#city_id').html('<option value="" >{{ __('admin.City') }}</option>');
          });
          $('#state').show(150);
        }
      });
    }

    function getCities(state_id) {
      var $city = $('#city_id');
      var currentCity = '{{ $client->city_id }}';
      var selected = '';
      $.ajax({
        url: "{{ route('cities.index') }}",
        data: {
          state_id: state_id
        },
        success: function(data) {
          $city.html('<option value="" >{{ __('admin.City') }}</option>');
          $.each(data, function(id, value) {
            if (currentCity == id) {
              selected = 'selected';
            } else {
              selected = '';
            }
            $city.append('<option value="' + id + '" ' + selected + ' >' + value + '</option>');
          });
          $('#city').show(150);
        }
      });
    }
</script>
@role('Mentor')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  var useDarkMode = window.matchMedia('(prefers-color-scheme: light)').matches;

  tinymce.init({
    selector: '#basicTextarea',
    plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount textpattern noneditable help charmap quickbars emoticons',
    imagetools_cors_hosts: ['picsum.photos'],
    menubar: 'edit view insert format tools table help',
    toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview  print | insertfile image media template link anchor codesample | ltr rtl',
    toolbar_sticky: true,
    autosave_ask_before_unload: true,
    autosave_interval: '30s',
    autosave_prefix: '{path}{query}-{id}-',
    autosave_restore_when_empty: false,
    autosave_retention: '2m',
    image_advtab: true,
    importcss_append: true,
    template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
    template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
    height: 400,
    image_caption: true,
    quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
    noneditable_noneditable_class: 'mceNonEditable',
    toolbar_mode: 'sliding',
    contextmenu: 'link image imagetools table',
    skin: useDarkMode ? 'oxide-dark' : 'oxide',
    content_css: useDarkMode ? 'dark' : 'default',
    content_style: 'body { font-family:Times New Roman,Arial,sans-serif; font-size:14px }',
    image_title: true,
    automatic_uploads: true,
    media_live_embeds: true,
    relative_urls: false,
    remove_script_host: false,
    images_upload_url: "{{ route('trainings.files_upload', ['_token' => csrf_token()]) }}",
    file_picker_types: 'image',
    convert_urls: true,
    file_picker_callback: function(cb, value, meta) {
      var input = document.createElement('input');
      input.setAttribute('type', 'file');
      input.setAttribute('accept', 'image/*');
      input.onchange = function() {
        var file = this.files[0];
        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function() {
          var id = 'blobid' + (new Date()).getTime();
          var blobCache = tinymce.activeEditor.editorUpload.blobCache;
          var base64 = reader.result.split(',')[1];
          var blobInfo = blobCache.create(id, file, base64);
          blobCache.add(blobInfo);
          cb(blobInfo.blobUri(), {
            title: file.name
          });
        };
      };
      input.click();
    }
  });
  
  @if ($l == 'ar')
    tinyMCE.settings['directionality'] = 'rtl';
  @endif
</script>
<script>
  $(document).ready(function() {
    @if ($client->bio)
      switch ('{{ $client->bio->entrepreneurial_experience }}') {
        case '1':
          $('#entrepreneurial_experience_brief').show();
          break;
        case '0':
          $('#entrepreneurial_experience_brief').hide();
          break;
        default:
          $('#entrepreneurial_experience_brief').hide();
          break;
      } 
    @endif

    $('.entrepreneurial_experience').change(function() {
      var value = $(this).val();
      switch (value) {
        case '1':
          $('#entrepreneurial_experience_brief').show();
          break;
        case '0':
          $('#entrepreneurial_experience_brief').hide();
          break;
        default:
          $('#entrepreneurial_experience_brief').hide();
          break;
      }
    });
  });
</script>
@endrole

{{-- <script>
// Send code
function sendCode(type) {    
    if (type === 'whatsapp' && !whatsappIti.isValidNumber()) {
      whatsappError.style.display = 'block';
      whatsappError.innerText = '{{ __('auth.Invalid_WhatsApp_Number') }}';
      return;
    }
    
    $(type == 'phone' ? '#verifyCodeModal' : '#verifyWhatsappCodeModal').modal('show');
    let number = $('#whatsapp').val();

    console.log('number', number);

    let countryCode = whatsappIti.getSelectedCountryData().dialCode;
    if (number.startsWith('0') && countryCode == '20') {
        number = number.substring(1); // Remove the first character (the zero)
    }else{
        number = number;
    }

    console.log(countryCode, number);
    $.ajax({
      url: '{{ route('security.2fa_code_send') }}',
      type: 'POST',
      data: {
        countryCode: countryCode,
        phone: number,
        _token: '{{ csrf_token() }}'
      },
      success: function(response) {
        console.log(response);
        if (response.success && response.success !== "") {
        // عرض رسالة النجاح
        $('#sentMessageotp').text(response.success).show();
        $('#whatsapp-error').hide(); // إخفاء رسالة الخطأ إذا ظهرت مسبقًا
    } else if (response.error && response.error !== "") {
        // عرض رسالة الخطأ
        $('#whatsapp-error').text(response.error).show();
        $('#sentMessageotp').hide(); // إخفاء رسالة النجاح إذا ظهرت مسبقًا
        $('#verifyWhatsappCodeModal').modal('hide'); // إخفاء رسالة النجاح إذا ظهرت مسبقًا
    } else {
        console.warn("رد غير متوقع من السيرفر: ", response);
        // إغلاق المودال كإجراء احتياطي
        $('#verifyWhatsappCodeModal').modal('hide');
    }
        
      },
      error: function(xhr, status, error) {
        $('#error').text('Not Successfully: ' + error);
        $('#error').show();
      }
    });
}

// Verify code of WhatsApp
function verifyWhatsappCode() {
  var code = '';
  $('.otp-input').each(function() {
    code += $(this).val();
  });

  if (code.length !== 6) {
    $('#otp-error').text('{{ __('auth.Please_enter_otp') }}').show();

    $('.otp-input').each(function() {
      $(this).addClass('is-invalid');
    });
    
    return;
  }

  $('#otp-error').hide();
  $('.otp-input').each(function() {
    $(this).removeClass('is-invalid');
  });

  console.log('code', code)

  $.ajax({
    url: '{{ route('security.2fa_code_verify') }}',
    type: 'POST',
    data: {
        code: code,
        _token: '{{ csrf_token() }}'
    },
    success: function(response) {
      console.log(response);
      if (response.success && response.success !== "") {
        // عرض رسالة النجاح
        $('#sentMessageotp').text(response.success).show();
        $('#errorotp').hide(); // إخفاء رسالة الخطأ إذا ظهرت مسبقًا
        location.reload();
    } else if (response.error && response.error !== "") {
        // عرض رسالة الخطأ
        $('#errorotp').text(response.error).show();
        $('#sentMessageotp').hide(); // إخفاء رسالة النجاح إذا ظهرت مسبقًا
    } else {
        console.warn("رد غير متوقع من السيرفر: ", response);
    }
    },
    error: function(xhr, status, error) {
      console.log(error);
      $('#errorotp').text('حدث خطأ أثناء التحقق من الرمز: ' + error);
      $('#errorotp').show();
    }
  });
}

// Automatically focus on the next input
$(document).on('input', '.otp-input', function() {
  if (this.value.length === this.maxLength) {
    $(this).next('.otp-input').focus();
  }
});
</script> --}}
@endif
@stop