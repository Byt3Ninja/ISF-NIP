@extends('layouts.admin')
@section('title', __('admin.Profile'))
@section('subTitle', __('admin.Edit your profile information'))

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/animate-css/animate.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
@stop

@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row">
        <div class="col-md-12">
          <div class="nav-align-top">
            <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-2 gap-lg-0">
              <li class="nav-item">
                <a class="nav-link active" href="javascript:void(0);"><i class="ti-sm ti ti-users me-1_5"></i> Account</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="pages-account-settings-security.html"><i class="ti-sm ti ti-lock me-1_5"></i>
                  Security</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="pages-account-settings-billing.html"><i class="ti-sm ti ti-bookmark me-1_5"></i>
                  Billing & Plans</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="pages-account-settings-notifications.html"><i
                    class="ti-sm ti ti-bell me-1_5"></i> Notifications</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="pages-account-settings-connections.html"><i class="ti-sm ti ti-link me-1_5"></i>
                  Connections</a>
              </li>
            </ul>
          </div>
          <div class="card mb-6">
            <!-- Account -->
            <div class="card-body">
              <div class="d-flex align-items-start align-items-sm-center gap-6">
                <img src="/vuexy/assets/img/avatars/1.png" alt="user-avatar" class="d-block w-px-100 h-px-100 rounded"
                  id="uploadedAvatar" />
                <div class="button-wrapper">
                  <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
                    <span class="d-none d-sm-block">Upload new photo</span>
                    <i class="ti ti-upload d-block d-sm-none"></i>
                    <input type="file" id="upload" class="account-file-input" hidden
                      accept="image/png, image/jpeg" />
                  </label>
                  <button type="button" class="btn btn-label-secondary account-image-reset mb-4">
                    <i class="ti ti-refresh-dot d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Reset</span>
                  </button>

                  <div>Allowed JPG, GIF or PNG. Max size of 800K</div>
                </div>
              </div>
            </div>
            <div class="card-body pt-4">
              <form id="formAccountSettings" method="GET" onsubmit="return false">
                <div class="row">
                  <div class="mb-4 col-md-6">
                    <label for="firstName" class="form-label">First Name</label>
                    <input class="form-control" type="text" id="firstName" name="firstName" value="John" autofocus />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input class="form-control" type="text" name="lastName" id="lastName" value="Doe" />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="email" class="form-label">E-mail</label>
                    <input class="form-control" type="text" id="email" name="email" value="john.doe@example.com"
                      placeholder="john.doe@example.com" />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="organization" class="form-label">Organization</label>
                    <input type="text" class="form-control" id="organization" name="organization" value="Pixinvent" />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label class="form-label" for="phoneNumber">Phone Number</label>
                    <div class="input-group input-group-merge">
                      <span class="input-group-text">US (+1)</span>
                      <input type="text" id="phoneNumber" name="phoneNumber" class="form-control"
                        placeholder="202 555 0111" />
                    </div>
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Address" />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="state" class="form-label">State</label>
                    <input class="form-control" type="text" id="state" name="state"
                      placeholder="California" />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="zipCode" class="form-label">Zip Code</label>
                    <input type="text" class="form-control" id="zipCode" name="zipCode" placeholder="231465"
                      maxlength="6" />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label class="form-label" for="country">Country</label>
                    <select id="country" class="select2 form-select">
                      <option value="">Select</option>
                      <option value="Australia">Australia</option>
                      <option value="Bangladesh">Bangladesh</option>
                      <option value="Belarus">Belarus</option>
                      <option value="Brazil">Brazil</option>
                      <option value="Canada">Canada</option>
                      <option value="China">China</option>
                      <option value="France">France</option>
                      <option value="Germany">Germany</option>
                      <option value="India">India</option>
                      <option value="Indonesia">Indonesia</option>
                      <option value="Israel">Israel</option>
                      <option value="Italy">Italy</option>
                      <option value="Japan">Japan</option>
                      <option value="Korea">Korea, Republic of</option>
                      <option value="Mexico">Mexico</option>
                      <option value="Philippines">Philippines</option>
                      <option value="Russia">Russian Federation</option>
                      <option value="South Africa">South Africa</option>
                      <option value="Thailand">Thailand</option>
                      <option value="Turkey">Turkey</option>
                      <option value="Ukraine">Ukraine</option>
                      <option value="United Arab Emirates">United Arab Emirates</option>
                      <option value="United Kingdom">United Kingdom</option>
                      <option value="United States">United States</option>
                    </select>
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="language" class="form-label">Language</label>
                    <select id="language" class="select2 form-select">
                      <option value="">Select Language</option>
                      <option value="en">English</option>
                      <option value="fr">French</option>
                      <option value="de">German</option>
                      <option value="pt">Portuguese</option>
                    </select>
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="timeZones" class="form-label">Timezone</label>
                    <select id="timeZones" class="select2 form-select">
                      <option value="">Select Timezone</option>
                      <option value="-12">(GMT-12:00) International Date Line West</option>
                      <option value="-11">(GMT-11:00) Midway Island, Samoa</option>
                      <option value="-10">(GMT-10:00) Hawaii</option>
                      <option value="-9">(GMT-09:00) Alaska</option>
                      <option value="-8">(GMT-08:00) Pacific Time (US & Canada)</option>
                      <option value="-8">(GMT-08:00) Tijuana, Baja California</option>
                      <option value="-7">(GMT-07:00) Arizona</option>
                      <option value="-7">(GMT-07:00) Chihuahua, La Paz, Mazatlan</option>
                      <option value="-7">(GMT-07:00) Mountain Time (US & Canada)</option>
                      <option value="-6">(GMT-06:00) Central America</option>
                      <option value="-6">(GMT-06:00) Central Time (US & Canada)</option>
                      <option value="-6">(GMT-06:00) Guadalajara, Mexico City, Monterrey</option>
                      <option value="-6">(GMT-06:00) Saskatchewan</option>
                      <option value="-5">(GMT-05:00) Bogota, Lima, Quito, Rio Branco</option>
                      <option value="-5">(GMT-05:00) Eastern Time (US & Canada)</option>
                      <option value="-5">(GMT-05:00) Indiana (East)</option>
                      <option value="-4">(GMT-04:00) Atlantic Time (Canada)</option>
                      <option value="-4">(GMT-04:00) Caracas, La Paz</option>
                    </select>
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="currency" class="form-label">Currency</label>
                    <select id="currency" class="select2 form-select">
                      <option value="">Select Currency</option>
                      <option value="usd">USD</option>
                      <option value="euro">Euro</option>
                      <option value="pound">Pound</option>
                      <option value="bitcoin">Bitcoin</option>
                    </select>
                  </div>
                </div>
                <div class="mt-2">
                  <button type="submit" class="btn btn-primary me-3">Save changes</button>
                  <button type="reset" class="btn btn-label-secondary">Cancel</button>
                </div>
              </form>
            </div>
            <!-- /Account -->
          </div>
          <div class="card">
            <h5 class="card-header">Delete Account</h5>
            <div class="card-body">
              <div class="mb-6 col-12 mb-0">
                <div class="alert alert-warning">
                  <h5 class="alert-heading mb-1">Are you sure you want to delete your account?</h5>
                  <p class="mb-0">Once you delete your account, there is no going back. Please be certain.</p>
                </div>
              </div>
              <form id="formAccountDeactivation" onsubmit="return false">
                <div class="form-check my-8">
                  <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" />
                  <label class="form-check-label" for="accountActivation">I confirm my account deactivation</label>
                </div>
                <button type="submit" class="btn btn-danger deactivate-account" disabled>
                  Deactivate Account
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- / Content -->

    <div class="content-backdrop fade"></div>
  </div>
  <!-- Content wrapper -->
@stop

@section('scripts')
  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->

  <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
  <script src="/vuexy/assets/vendor/libs/popper/popper.js"></script>
  <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
  <script src="/vuexy/assets/vendor/libs/node-waves/node-waves.js"></script>
  <script src="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="/vuexy/assets/vendor/libs/hammer/hammer.js"></script>
  <script src="/vuexy/assets/vendor/libs/i18n/i18n.js"></script>
  <script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
  <script src="/vuexy/assets/vendor/js/menu.js"></script>

  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
    <script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
    <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

    <!-- Main JS -->
    <script src="/vuexy/assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="/vuexy/assets/js/pages-account-settings-account.js"></script>
@stop

@section('pagescripts')
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
        url: "{{ route('get_states_by_country') }}",
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
        images_upload_url: "{{ route('training.upload', ['_token' => csrf_token()]) }}",
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
          } @endif

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

@stop
