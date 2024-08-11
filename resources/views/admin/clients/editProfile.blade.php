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
                <img src="/191014/{{ $client->img_url ?? '../images/placeholder.jpg' }}" alt="user-avatar"
                  class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />
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
              <form method="post" action="{{ route('update_my_profile') }}" enctype="multipart/form-data">
                <div class="row">
                  <div class="mb-4 col-md-6">
                    <label for="first_name_en" class="form-label">{{ __('admin.English UserName') }}</label>
                    <input value="{{ $client->first_name_en }}" name="first_name_en" type="text" class="form-control"
                      id="first_name_en" style="direction: ltr;" placeholder="{{ __('admin.First Name') }}" required
                      autocomplete="first_name_en" autofocus />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="last_name_en" class="form-label">{{ __('admin.Last Name') }}</label>
                    <input value="{{ $client->last_name_en }}" name="last_name_en" type="text" class="form-control"
                      id="last_name_en" style="direction: ltr;" placeholder="{{ __('admin.Last Name') }}" required
                      autocomplete="last_name_en" />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="first_name_ar" class="form-label">{{ __('admin.Arabic UserName') }}</label> <input
                      value="{{ $client->first_name_ar }}" name="first_name_ar" type="text" class="form-control"
                      id="first_name_ar" style="direction: rtl;" placeholder="{{ __('admin.First Name') }}" required
                      autocomplete="first_name_ar" autofocus />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="last_name_ar" class="form-label">Arabic {{ __('admin.Last Name') }}</label>
                    <input value="{{ $client->last_name_ar }}" name="last_name_ar" type="text" class="form-control"
                      id="last_name_ar" style="direction: rtl;" placeholder="{{ __('admin.Last Name') }}" required
                      autocomplete="last_name_ar" />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="email" class="form-label">{{ __('admin.E-mail') }}</label>
                    <input value="{{ $client->email }}" name="email" type="email" class="form-control"
                      id="email" style="direction: ltr;" placeholder="{{ __('admin.E-mail') }}" required
                      autocomplete="email">
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="gender" class="form-label">Gender</label>
                    <select name="gender" id="gender" class="select2 form-select">
                      <option value="Male" {{ $client->gender == 'Male' ? 'selected' : '' }}>{{ __('Male') }}
                      </option>
                      <option value="Female" {{ $client->gender == 'Female' ? 'selected' : '' }}>{{ __('Female') }}
                      </option>
                    </select>
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="title_ar" class="form-label">{{ __('admin.JobTitleAr') }}</label>
                    <input value="{{ $client->title_ar }}" name="title_ar" type="title_ar" class="form-control"
                      id="title_ar" style="direction: rtl;" placeholder="{{ __('admin.JobTitleAr') }}" required
                      autocomplete="title_ar">
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="title_en">{{ __('admin.JobTitleEn') }}</label>
                    <input value="{{ $client->title_en }}" name="title_en" type="title_en" class="form-control"
                      id="title_en" style="direction: ltr;" placeholder="{{ __('admin.JobTitleEn') }}" required
                      autocomplete="title_en">
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="organization" class="form-label">Organization</label>
                    <input type="text" class="form-control" id="organization" name="organization"
                      value="Pixinvent" />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="phone" class="form-label">{{ __('admin.Phone') }}</label>
                    <input type="text" class="form-control" id="phone" style="direction: ltr;"
                      placeholder="{{ __('admin.Phone') }}" value="{{ $client->phone }}" name="phone" required
                      autocomplete="new-phone" />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Address" />
                  </div>
                  {{-- <div class="mb-4 col-md-6">
                    <label for="state" class="form-label">State</label>
                    <input class="form-control" type="text" id="state" name="state"
                      placeholder="California" />
                  </div> --}}
                  <div class="mb-4 col-md-6">
                    <label for="zipCode" class="form-label">Zip Code</label>
                    <input type="text" class="form-control" id="zipCode" name="zipCode" placeholder="231465"
                      maxlength="6" />
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="state_id" class="form-label">{{ __('admin.Country') }}</label>
                    <select id="country_id" name='country_id' class="select2 form-select" required>
                      <option value="">{{ __('admin.Country') }}</option>
                      @foreach ($countries as $country)
                        <option value="{{ $country->id }}"
                          {{ $country->id == $client->country_id ? 'selected' : '' }}>
                          {{ $country->{'name_' . $l} }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="state_id">{{ __('admin.State') }}</label>
                    <select class="select2 form-select" required id="state_id" name='state_id'>
                      <option value="">{{ __('admin.State') }}</option>
                      @php $name = 'name_'.$l; @endphp
                      @if ($client->state_id)
                        <option value="{{ $client->state_id }}" selected>{{ $client->state->{'name_' . $l} }}</option>
                      @endif
                    </select>
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="city_id" class="form-label">{{ __('admin.City') }}</label>
                    <select class="select2 form-select" required id="city_id" name='city_id'>
                      <option value="">{{ __('admin.City') }}</option>
                      @php $name = 'name_'.$l; @endphp
                      @if ($client->city_id)
                        <option value="{{ $client->city_id }}" selected>{{ $client->city->{'name_' . $l} }}</option>
                      @endif
                    </select>
                  </div>
                  <div class="mb-4 col-md-6">
                    <label for="HaveCompany" class="form-label">{{ __('auth.DoYouHaveCompany') }}</label>
                    <select name="HaveCompany" id="HaveCompany" class="select2 form-select">
                      <option value="1" {{ $client->HaveCompany ? 'selected' : '' }}>{{ __('Yes') }}</option>
                      <option value="" {{ $client->HaveCompany ? '' : 'selected' }}>{{ __('No') }}</option>
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

                  {{--                ================> Section Mentor Bio <==================== --}}
                  @role('Mentor')
                    <input type="hidden" name="mentor_section" value="1">
                    <a id="bio">
                      <section id="basic-input">
                        <div class="row">
                          <div class="col-md-12">
                            <div class="card">
                              <div class="card-header">
                                <h4 class="card-title">{{ __('admin.addbio') }}</h4>
                              </div>
                              <div class="card-content">
                                <div class="card-body">

                                  <div class="row">

                                    <div class="col-md-12">
                                      <div class="col-md-12 col-12">
                                        <label for="linkedin" class="form-label">{{ __('admin.Linkedin') }}</label>
                                        <div class="form-group">
                                          <input value="{{ $client->bio ? $client->bio->linkedin : '' }}" name="linkedin"
                                            type="text" class="form-control" id="linkedin" style="direction: ltr;"
                                            placeholder="{{ __('admin.Linkedin') }} - https://linkedin.com/in/yourname"
                                            autocomplete="linkedin">

                                        </div>
                                      </div>
                                      <div class="col-md-12 col-12 mt-4">
                                        <label class="form-label">{{ __('admin.arbio') }}: </label>
                                        <div class="form-group">
                                          <textarea class="form-control" id="bio_ar" name='bio_ar' rows="3" dir="rtl"
                                            placeholder="{{ __('admin.writebioar') }}">{{ $client->bio->bio_ar ?? '' }}</textarea>
                                        </div>
                                      </div>
                                      <div class="col-md-12 col-12 mt-4">
                                        <label class="form-label">{{ __('admin.enbio') }}: </label>
                                        <div class="form-group">
                                          <textarea class="form-control" id="bio_en" name='bio_en' rows="3" dir="ltr"
                                            placeholder="{{ __('admin.writebioen') }}">{{ $client->bio->bio_en ?? '' }}</textarea>
                                        </div>
                                      </div>
                                    </div>
                                  </div>


                                  <div class="col-md-12 col-12 mt-4">
                                    <div class="form-label-group">
                                      <h6 for="selectExpertises">{{ __('admin.SelectExpertises') }}</h6>
                                      <p>{{ __('admin.SelectMultiExpertises') }}.</p>
                                      <div class="form-group">
                                        <select name="expertises[]" class="select2 form-control" multiple="multiple"
                                          id="selectExpertises">
                                          @foreach ($expertises as $expertise_group)
                                            <optgroup label="{{ $expertise_group->title }}">
                                              @foreach ($expertise_group->children as $expertise)
                                                <option value="{{ $expertise->id }}"
                                                  @if (in_array($expertise->id, $client->mentorExpertises()->pluck('expertises.id')->toArray())) selected @endif>{{ $expertise->title }}
                                                </option>
                                              @endforeach
                                            </optgroup>
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                  </div>

                                  <div class="mt-4 col-md-6">
                                    <label for="basicInput" class="form-label">
                                      {{ __('admin.Entrepreneurial Experience') }}
                                    </label>

                                    <select name="entrepreneurial_experience" id="entrepreneurial_experience"
                                      class="select2 form-select">
                                      <option value="1"
                                        {{ optional($client->bio)->entrepreneurial_experience ? 'selected' : '' }}>
                                        {{ __('Yes') }}</option>
                                      <option value="0"
                                        {{ optional($client->bio)->entrepreneurial_experience ? '' : 'selected' }}>
                                        {{ __('No') }}</option>
                                    </select>
                                  </div>
                                  <br>

                                  <div class="mt-4 col-md-6">
                                    <label class="form-label">{{ __('admin.Entrepreneurial Experience desc') }}.</label>
                                    <textarea class="form-control" id="basicTextarea" rows="8" name="entrepreneurial_experience_brief" required>{{ $client->bio ? $client->bio->entrepreneurial_experience_brief : old('entrepreneurial_experience_brief') }} </textarea>
                                    <label for="tcontent"
                                      class="mt-2">{{ __('admin.Entrepreneurial Experience Brief') }}</label>

                                  </div>

                                  <div class="my-4 col-md-6">
                                    <label for="profile_privacy" class="form-label">{{ __('admin.Profile Privacy') }}</label>
                                    <select name="profile_privacy" id="profile_privacy" class="select2 form-select">
                                        <option value="1" {{ optional($client->bio)->profile_privacy ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                        <option value="0" {{ optional($client->bio)->profile_privacy ? '' : 'selected' }}>{{ __('No') }}</option>
                                    </select>
                                </div>

                                  <div class="col-md-12 col-12">
                                    <label for="approach" class="form-label">{{ __('admin.mentorship approach') }}
                                      :</label>
                                    <div class="form-group" id="approach">
                                      <div class="checkbox mr-5">
                                        <input type='checkbox' id="checkbox1" class="checkbox-input"
                                          name='mentorship_approach[]'
                                          {{ $client->bio && in_array('E-mail', $client->bio->mentorship_approaches) ? 'checked=""' : '' }}
                                          value="E-mail">
                                        <label for="checkbox1" class="form-label">
                                          {{ __('admin.E-mail') }}
                                        </label>
                                      </div>
                                      <div class="checkbox mr-5">
                                        <input type='checkbox' id="checkbox2" class="checkbox-input"
                                          name='mentorship_approach[]'
                                          {{ $client->bio && in_array('phone calls', $client->bio->mentorship_approaches) ? 'checked=""' : '' }}
                                          value="phone calls">
                                        <label for="checkbox2" class="form-label">
                                          {{ __('admin.phone calls') }}
                                        </label>
                                      </div>
                                      <div class="checkbox mr-5">
                                        <input type='checkbox' id="checkbox3" class="checkbox-input"
                                          name='mentorship_approach[]'
                                          {{ $client->bio && in_array('video calls', $client->bio->mentorship_approaches) ? 'checked=""' : '' }}
                                          value="video calls">
                                        <label for="checkbox3" class="form-label">
                                          {{ __('admin.video calls') }}
                                        </label>
                                      </div>
                                      <div class="checkbox mr-5">
                                        <input type='checkbox' id="checkbox4" class="checkbox-input"
                                          name='mentorship_approach[]'
                                          {{ $client->bio && in_array('in-person meetings', $client->bio->mentorship_approaches) ? 'checked=""' : '' }}
                                          value="in-person meetings">
                                        <label for="checkbox4" class="form-label">
                                          {{ __('admin.in-person meetings') }}
                                        </label>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <br>
                                <div class="row">
                                  <div class="col-12 d-flex justify-content-end">
                                    <button type="submit"
                                      class="btn btn-primary mr-1 mb-1">{{ __('admin.Update') }}</button>
                                  </div>
                                </div>
                              </div>

                            </div>
                          </div>
                        </div>
                      </section>
                    </a>
                  @endrole
                  {{--                ================> Section Mentor Bio <==================== --}}
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
  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

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
