@extends('layouts.admin')

@section('vendorstyle')
{{-- Monthly Date Picker CDN ( For Financial Projection Form ) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
@stop

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/intl-tel-input/css/intlTelInput.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
<style>
	.flatpickr-monthSelect-month.selected {
		background-color: var(--bs-primary) !important;
	}
	.valuation-history-dropdown {
		max-height: 300px;
		overflow-y: auto;
	}
   #expertiseDropdownList {
    display: none; 
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050; /* Higher than other UI elements */
    background-color: #fff;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.1);
    margin-top: 2px;
  }

  #expertiseDropdownList.show {
    display: block;
  }
</style>
@stop

@php
  $name = 'name_' . $l;

  // Show the Support & Mentorship tab only when active Areas of Expertise exist.
  $showSupportTab = isset($hasAreasOfExpertise) ? (bool) $hasAreasOfExpertise : false;

  $tabs = collect([
      ['company-info', 'ti-building-skyscraper', $l == 'en' ? 'Startup Info' : 'معلومات الشركة'],
      ['industry-sectors', 'ti-apps', $l == 'en' ? 'Industry / Sectors' : ' الصناعة / القطاعات'],
      ['company-contact', 'ti-phone', $l == 'en' ? 'Contact Info' : 'معلومات الاتصال'],
      ['company-desc', 'ti-file-description', __('admin.about_or_description')],
      ['company-financial-info', 'ti-report-money', $l == 'en' ? 'Financial Info' : 'المعلومات المالية'],
      ['company-financial-projection', 'ti-database-dollar', $l == 'en' ? 'Financial Projection' : 'التوقعات المالية'],
      ['team-info', 'ti-briefcase', __('startup.team_info')],
      ['employees', 'ti-users-group', __('startup.employees_statistics')],
      ['pitch-deck', 'ti-presentation', $l == 'en' ? 'Pitch Deck' : 'الوثائق'],
      ['company-milestones', 'ti-flag', $l == 'en' ? 'Milestones' : 'الإنجازات'],
      ['business_model', 'ti-cash-banknote', $l == 'en' ? 'Business Model / MVP' : 'نموذج العمل / المنتج'],
      ['customer_growth', 'ti-trending-up', $l == 'en' ? 'Customer Growth' : 'نمو العملاء'],
  ]);

  if ($showSupportTab) {
      $tabs->push(['support_mentorship', 'ti-brand-my-oppo', $l == 'en' ? 'Support & Mentorship' : 'الدعم والمشاركة']);
  }

  $tabs = $tabs->map(function ($tab) {
      return [
          'target' => $tab[0],
          'icon'   => $tab[1],
          'label'  => $tab[2]
      ];
  })->toArray();

  // Lock every tab except "Startup Info" until the startup name has been saved.
  $lockTabs = ! ($client && $client->company && trim((string) $client->company->name_en) !== '');
@endphp

@section('content')
  <!-- Content wrapper -->
  <div class="" style="margin-bottom: 5rem;">
    <!-- Content -->
    <div class="overflow-x-hidden" style="max-width: 100%;">
      <!-- Tabs (mobile) -->
      <div style="max-width: 100%; overflow-x: auto;" class="mb-4 d-md-none">
        <ul class="nav nav-pills flex-nowrap" role="tablist">
          @foreach ($tabs as $key => $tab)
            <li class="nav-item d-flex justify-content-center align-items-center">
              <a
                id="mobile-company-tab"
                href="#navs-left-{{ $tab['target'] }}"
                class="nav-link {{ $key == 0 ? 'active' : '' }} {{ $lockTabs && $key != 0 ? 'disabled' : '' }}"
                @if ($lockTabs && $key != 0) aria-disabled="true" tabindex="-1" title="{{ __('admin.tabs_locked_until_startup_saved') }}" @endif
                data-bs-toggle="tab"
                role="tab"
              >
                <i class="ti {{ $tab['icon'] }} me-2 fs-5"></i>
                {{ $tab['label'] }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>
      <!-- / Tabs (mobile) -->

      <div class="row g-6">
        <div class="col-12 col-lg-3">
          <nav class="bg-card p-4 rounded shadow-sm d-none d-md-block" style="position: sticky; top: 20px;">
            <!-- Tabs -->
            <ul class="nav nav-align-left nav-pills flex-column">
              @foreach ($tabs as $key => $tab)
              <li class="nav-item bg-label-light {{ $key == count($tabs) - 1 ? '' : 'mb-2' }} rounded overflow-x-hidden d-flex align-items-center">
                <a
                    id="company-tab"
                    href="#navs-left-{{ $tab['target'] }}"
                    class="nav-link py-3 {{ $key == 0 ? 'active' : '' }} {{ $lockTabs && $key != 0 ? 'disabled' : '' }}"
                    @if ($lockTabs && $key != 0) aria-disabled="true" tabindex="-1" title="{{ __('admin.tabs_locked_until_startup_saved') }}" @endif
                    data-bs-toggle="tab"
                    role="tab"
                  >
                    <i class="ti {{ $tab['icon'] }} me-2 fs-5"></i>
                    {{ $tab['label'] }}
                  </a>
                </li>
              @endforeach
            </ul>
            <!-- / Tabs -->
          </nav>
        </div>

        <div class="col-12 col-lg-9">
          <!-- Tab Content On Tab Click -->
          <div id="company-tab-content" class="tab-content p-0 overflow-y-auto mb-6">
            <!-- Alert -->
            @if (session()->has('msg'))
              <x-alert 
                type="{{ session()->get('class') ?? 'danger' }}"
                message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" 
              />
            @endif
            @if (session()->has('success'))
              <x-alert 
                type="success"
                message="{{ session()->get('success') }}" 
              />
            @endif
            @if (session()->has('error'))
              <x-alert 
                type="danger"
                message="{{ session()->get('error') }}" 
              />
            @endif
            <!-- Validation messages -->
            @if (count($errors) > 0)
              @foreach ($errors->all() as $error)
                <x-alert type="danger" message="{{ $error }}" />
              @endforeach
            @endif
            <!-- / Alert -->

            {{-- Say why the other tabs are greyed out, rather than leaving the
                 founder to guess why they cannot be clicked (GV-9668). --}}
            @if ($lockTabs)
              <div class="alert alert-info d-flex align-items-start gap-2 mb-6" role="alert">
                <i class="ti ti-lock ti-18px flex-shrink-0 mt-1"></i>
                <span>{{ __('admin.tabs_locked_until_startup_saved') }}</span>
              </div>
            @endif

            @foreach ($tabs as $index => $tab)
              <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="navs-left-{{ $tab['target'] }}">
                @switch($tab['target'])
                  @case('company-info')
                    @include('partials.forms.company.company-info-form',['heading' => $tab['label']])
                  @break
                  @case('industry-sectors')
                    @include('partials.forms.company.classification_form', ['heading' => $tab['label']])
                  @break
                  @case('company-contact')
                    @include('partials.forms.company.company-contact', ['heading' => $tab['label']])
                  @break
                  @case('company-desc')
                    @include('partials.forms.company.company-desc', ['heading' => $tab['label']])
                  @break
                  @case('company-financial-info')
                    @include('partials.forms.company.company-financial', ['heading' => $tab['label']])
                  @break
                  @case('company-financial-projection')
                    @include('partials.forms.company.company-financial-projection', ['heading' => $tab['label']])
                  @break
                  @case('team-info')
                    @include('partials.forms.company.company-team-info', ['heading' => $tab['label']])
                  @break
                  @case('employees')
                    @include('partials.forms.company.company-employees', ['heading' => $tab['label']])
                  @break
                  @case('pitch-deck')
                    @include('partials.forms.company.company-pitch-deck', ['heading' => $tab['label']])
                  @break
                  @case('company-milestones')
                    @include('partials.forms.company.company-milestones', ['heading' => $tab['label']])
                  @break
                  @case('business_model')
                    @include('partials.forms.company.business_model', ['heading' => $tab['label']])
                  @break
                  @case('customer_growth')
                    @include('partials.forms.company.company-customers', ['heading' => $tab['label']])
                  @break
                  @case('support_mentorship')
                    @include('partials.forms.company.company-support', ['heading' => $tab['label']])
                  @break
                @endswitch
              </div>
            @endforeach
          </div>
          <!-- / Tab Content On Tab Click -->
        </div>
      </div>
    </div>
    <!-- / Content -->
    <div class="content-backdrop fade"></div>
  </div>
  <!-- / Content wrapper -->
@stop

@section('scripts')
{{-- Monthly Date Picker CDN ( For Financial Projection Form ) --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script src="/vuexy/assets/vendor/libs/intl-tel-input/js/intlTelInput.min.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/toastr/toastr.js"></script>
<script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
<script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<!-- <script src="/vuexy/assets/vendor/libs/tagify/tagify.js"></script> -->
@stop

@section('pagescripts')
  <script>
    const lang = '{{ $l }}';
  </script>
  <script src="/app-assets/js/1.js"></script>
  
  <script src="/vuexy/assets/js/pages-edit-compnay.js?v={{ @filemtime(public_path('vuexy/assets/js/pages-edit-compnay.js')) ?: 1 }}"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const pitchTabs = document.querySelectorAll('#pitch-tab');
      const tabs = document.querySelectorAll('#company-tab');
      const mobileTabs = document.querySelectorAll('#mobile-company-tab');
      const tabsContent = document.querySelectorAll('#company-tab-content > .tab-pane');
      const pitchContent = document.querySelectorAll('#pitch-tab-content .tab-pane');
      const activeCompanyTab = localStorage.getItem('activeCompanyTab');
      const activePitchTab = localStorage.getItem('activePitchTab');
      
      const urlParams = new URLSearchParams(window.location.search);
      const hasCompleteProfile = urlParams.get('action') === 'complete-profile';

      // Every tab but Startup Info is locked until the startup name is saved.
      // A remembered tab from an earlier visit must not reopen a locked pane,
      // otherwise the founder lands on e.g. Financial Info and fills in a form
      // that cannot be saved (GV-9668).
      const tabsLocked = @json($lockTabs);

      // Clear the remembered tab on complete-profile, and while locked, so a
      // stale index cannot survive into the next visit.
      if (hasCompleteProfile || tabsLocked) {
        localStorage.removeItem('activeCompanyTab');
        localStorage.removeItem('activePitchTab');
      }

      // ?tab=<target> deep-links to one section, so a prompt elsewhere in the
      // app can send the founder to the exact field it is asking them to fill
      // (e.g. the assessment's "set your development stage" hint). Ignored when
      // the tabs are locked, and when the slug is unknown.
      const tabTargets = @json(array_column($tabs, 'target'));
      const requestedTab = urlParams.get('tab');
      const requestedIndex = tabsLocked ? -1 : tabTargets.indexOf(requestedTab);

      const restoreDisabled = hasCompleteProfile || tabsLocked;
      const activeIndex = requestedIndex > -1
        ? requestedIndex
        : (restoreDisabled || !activeCompanyTab ? 0 : JSON.parse(activeCompanyTab));
      const activePitchIndex = restoreDisabled || !activePitchTab ? 0 : JSON.parse(activePitchTab);

      // Initialize pitch tabs (internal tabs within pitch deck)
      pitchTabs.forEach((tab, index) => {
        tab.addEventListener('click', function() {
          localStorage.setItem('activePitchTab', JSON.stringify(index));
        });
        
        if (index === activePitchIndex) {
          tab.classList.add('active');
        } else {
          tab.classList.remove('active');
        }
      });

      // Initialize pitch content
      pitchContent.forEach((content, index) => {
        if (index === activePitchIndex) {
          content.classList.add('show', 'active');
        } else {
          content.classList.remove('show', 'active');
        }
      });

      // Initialize desktop tabs
      tabs.forEach((tab, index) => {
        tab.addEventListener('click', function() {
          if (tabsLocked) return;
          localStorage.setItem('activeCompanyTab', JSON.stringify(index));
        });
        
        if (index === activeIndex) {
          tab.classList.add('active');
        } else {
          tab.classList.remove('active');
        }
      });

      // Initialize mobile tabs
      mobileTabs.forEach((tab, index) => {
        tab.addEventListener('click', function() {
          if (tabsLocked) return;
          localStorage.setItem('activeCompanyTab', JSON.stringify(index));
        });
        
        if (index === activeIndex) {
          tab.classList.add('active');
        } else {
          tab.classList.remove('active');
        }
      });

      // Initialize tab content
      tabsContent.forEach((tab, index) => {
        if (index === activeIndex) {
          tab.classList.add('show', 'active');
        } else {
          tab.classList.remove('show', 'active');
        }
      });
    });
  </script>
  
  <script>
    // While the tabs are locked (startup name not saved yet), pages-edit-compnay.js
    // reloads the page after a successful save so the tabs unlock without a manual refresh.
    window.RELOAD_AFTER_INFO_SAVE = @json($lockTabs);
  </script>

  <script>
    $(document).ready(function() {
      $('.roundBtn').click(function() {
        if ($(this).data('value') === 'create') {
          $('.fundingMethod').attr('name', '');
        } else {
          $('.fundingMethod').attr('name', '_method');
          $.ajax({
            url: "/dashboard/startup/fundingRounds/" + $(this).data(
              'id'),
            success: function(data) {
              $('#funding_stage').val(data['funding_stage']);
              $('#amount').val(data['amount']);
              $('#post_money_valuation').val(data[
                'post_money_valuation']);
              $('#date_raised').val(data['date_raised']);
              $('#investment_vehicle').val(data[
                'investment_vehicle']);
              $('#amount_raised').val(data['amount_raised']);

            }
          });
        }
        $('.fundingForm').attr('action', $(this).data('url'));
        $('#SendRequest').modal('show');
      });

      $('#institutions').select2({
        tags: true,
        placeholder: 'Type and press enter to add',
        allowClear: true
      });
    });
  </script>

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
      var currentState =
        '{{ $client->company ? optional($client->company)->state_id : 0 }}';
      var selected = '';
      $.ajax({
        url: "{{ route('utils.locations_list_states_by_country') }}",
        data: {
          country_id: country_id
        },
        success: function(data) {
          $state.html(
          '<option value="" disabled >{{ __('admin.State') }}</option>');
          $.each(data, function(id, value) {
            // selected = '';
            if (currentState === id) {
              selected = 'selected';
              getCities(id)
            } else {
              selected = '';
            }
            $state.append('<option value="' + id + '" ' + selected +
              '>' + value + '</option>');
            $('#city_id').html(
              '<option value="" >{{ __('admin.City') }}</option>');
          });
          $('#state').show(150);
        }
      });
    }

    function getCities(state_id) {
      var $city = $('#city_id');
      var currentCity =
        '{{ $client->company ? optional($client->company)->city_id : 0 }}';
      var selected = '';
      $.ajax({
        url: "{{ route('cities.index') }}",
        data: {
          state_id: state_id
        },
        success: function(data) {
          $city.html('<option value="" disabled >{{ __('admin.City') }}</option>');
          $.each(data, function(id, value) {
            if (currentCity == id) {
              selected = 'selected';
            } else {
              selected = '';
            }
            $city.append('<option value="' + id + '" ' + selected +
              ' >' + value + '</option>');
          });
          $('#city').show(150);
        }
      });
    }
  </script>
    
  <script>
    // $('#logo').filemanager(['image']);

    function ssector(selection) {
      if (selection === 'other') {
        let other = prompt('{{ __('Enter your company sector name') }}');
        if (other) {
          let newItem = "<option selected value='" + other + "'>" + other +
            "</option>";
          $('#selectsector').append(newItem);
        } else {
          $('#selectsector').val('Industrial');
        }
      }
    }

    // document.addEventListener('DOMContentLoaded', function() {
    //   // الاستماع لتغيير التبويبات
    //   document.querySelectorAll('.nav-link').forEach(tab => {
    //     tab.addEventListener('shown.bs.tab', function(e) {
    //       // تحديد التبويب النشط
    //       const activeTab = e.target.getAttribute('href');
    //       // تعطيل الحقول المطلوبة للتبويبات غير النشطة
    //       document.querySelectorAll('.tab-pane').forEach(pane => {
    //         if (pane.getAttribute('id') === activeTab.substring(
    //           1)) {
    //           pane.querySelectorAll('input').forEach(input =>
    //             input.removeAttribute('disabled'));
    //         } else {
    //           pane.querySelectorAll('input').forEach(input =>
    //             input.setAttribute('disabled', 'disabled'));
    //         }
    //       });
    //     });
    //   });
    // });

    // Function to upload file with progress bar
    function uploadFile(inputId, progressBarId, route) {
      var formData = new FormData();
      var file = document.getElementById(inputId).files[0];
      formData.append(inputId, file);

      $.ajax({
        url: route,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        xhr: function() {
          var xhr = new window.XMLHttpRequest();
          xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
              var percentComplete = Math.round((e.loaded / e.total) *
                100);
              $('#' + progressBarId).css('width', percentComplete +
              '%');
              $('#' + progressBarId).attr('aria-valuenow',
                percentComplete);
              $('#' + progressBarId).text(percentComplete + '%');
            }
          }, false);
          return xhr;
        },
        success: function(data) {
          if (data.success) {
            console.log('File uploaded successfully: ' + data.path);
          }
        },
        error: function(xhr, status, error) {
          console.log('Error uploading file: ' + error);
        }
      });
    }

    // Upload for Pitch Deck
    $('#pitch_deck').on('change', function() {
      uploadFile('pitch_deck', 'progress-bar-pitch_deck',
        '{{ route('company.profile_update_mine') }}');
    });

    // Upload for Pitch Deck Video
    $('#pitch_deck_video').on('change', function() {
      uploadFile('pitch_deck_video', 'progress-bar-pitch_deck_video',
        '{{ route('company.profile_update_mine') }}');
    });

    // Upload for Pitch Deck Video
    $('#img_url').on('change', function() {
      uploadFile('img_url', 'progress-bar-Logo',
        '{{ route('company.profile_update_mine') }}');
    });
  </script>
  

   <!-- Get countries and phone codes -->
  <script>
    fetch('https://restcountries.com/v3.1/all?fields=idd,cca2')
      .then(response => response.json())
      .then(data => {
        const select = document.getElementById('country_code');
        if (Array.isArray(data)) {
          data.forEach(country => {
            const option = document.createElement('option');
            const countryCode =
              `${country.idd.root}${country.idd.suffixes ? country.idd.suffixes[0] : ''}`;
            option.value = countryCode;
            option.text = `${countryCode} (${country.cca2})`;
            option.style.fontSize = '12px';
            if (country.cca2 === 'EG') {
              option.selected = true;
            }
            select.appendChild(option);
          });
        } else {
          console.error('Unexpected response format:', data);
        }
      })
      .catch(error => {
        console.error('Error fetching countries:', error);
      });
  </script>
@stop


