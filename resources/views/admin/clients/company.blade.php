@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />

  <style>
    #contentWrapper {
      margin-bottom: 5rem;
    }
    .radius-15 {
      border-radius: 15px;
    }
    
    .light-shadow {
      box-shadow: 0px 2px 0px 0px #F2F1F3;
    }
    .custom-container {
      width: 100%;
    }

    @media screen and (min-width: 768px) {
      #contentWrapper {
       margin-bottom: 12rem;
      }
      .custom-container {
        width: 78%;
      }
    }
  </style>
@stop

@php
  $name = 'name_' . $l;
  $tabs = collect([
      ['company-info', 'ti-building-skyscraper', $l == 'en' ? 'Startup Info' : 'معلومات الشركة'],
      ['classification', 'ti-apps', $l == 'en' ? 'Industry / Sectors' : ' الصناعة / القطاعات'],
      ['company-contact-info', 'ti-phone', $l == 'en' ? 'Contact Info' : 'معلومات الاتصال'],
      ['company-description', 'ti-file-description', $l == 'en' ? 'Description' : 'الوصف'],
      ['company-financial-info', 'ti-currency-dollar', $l == 'en' ? 'Financial' : ' المالية'],
      ['team-info', 'ti-users-group', __('startup.Employees')],
      ['pitch-deck', 'ti-presentation', $l == 'en' ? 'Pitch Deck' : 'الوثائق'],
      ['company-milestones', 'ti-flag', $l == 'en' ? 'Milestones' : 'الإنجازات'],
      ['business_model', 'ti-presentation', $l == 'en' ? 'Business Model' : ' نموذج الاعمال '],
  ])
      ->map(function ($tab) {
          return ['target' => $tab[0], 'icon' => $tab[1], 'label' => $tab[2]];
      })
      ->toArray();
@endphp

@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper" id="contentWrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y overflow-x-hidden" style="max-width: 100%;">
      <!-- Tabs (mobile) -->
      <div style="max-width: 100%; overflow-x: auto;" class="mb-4 d-md-none">
        <ul class="nav nav-pills flex-nowrap" role="tablist">
          @foreach ($tabs as $key => $tab)
            <li class="nav-item d-flex justify-content-center align-items-center">
              <a 
                id="mobile-company-tab"
                href="#navs-left-{{ $tab['target'] }}"
                class="nav-link {{ $key == 0 ? 'active' : '' }}"
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

      <div class="d-flex justify-content-start w-100 mb-6">
        <nav 
          class="nav-align-left nav-tabs-shadow mb-6 px-4 py-2 bg-white position-fixed d-none d-md-block" 
          style="height: auto; min-width: 15%; max-width: 15%;">
          <!-- Tabs -->
          <ul class="nav nav-tabs nav-fill border-none m-auto" role="tablist">
            @foreach ($tabs as $key => $tab)
            <li class="nav-item bg-label-light {{ $key == count($tabs) - 1 ? '' : 'mb-2' }} rounded overflow-x-hidden d-flex align-items-center">
              <a 
                  id="company-tab"
                  href="#navs-left-{{ $tab['target'] }}"
                  class="nav-link py-3 {{ $key == 0 ? 'active' : '' }}"
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
        <!-- Tab Content On Tab Click -->
        <div 
          id="company-tab-content"
          class="tab-content p-0 overflow-y-auto custom-container mb-6" 
          style="{{ $l == 'en' ? 'margin-left: auto' : 'margin-right: auto' }}"
        >
          <!-- Alert -->
          @if (session()->has('msg'))
            <x-alert 
              type="{{ session()->get('class') ?? 'danger' }}"
              message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" 
            />
          @endif
          <!-- Validation messages -->
          @if (count($errors) > 0)
            @foreach ($errors->all() as $error)
              <x-alert type="danger" message="{{ $error }}" />
            @endforeach
          @endif
          <!-- / Alert -->

          @foreach ($tabs as $index => $tab)
            <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="navs-left-{{ $tab['target'] }}">
              @switch($tab['target'])
                @case('company-info')
                  @include('partials.forms.company.company-info-form',['heading' => $tab['label']])
                @break
                @case('classification')
                  @include('partials.forms.company.classification_form', ['heading' => $tab['label']])
                @break
                @case('company-contact-info')
                  @include('partials.forms.company.company-contact', ['heading' => $tab['label']])
                @break
                @case('company-description')
                  @include('partials.forms.company.company-desc', ['heading' => $tab['label']])
                @break
                @case('company-financial-info')
                  @include('partials.forms.company.company-financial', ['heading' => $tab['label']])
                @break
                @case('team-info')
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
              @endswitch
            </div>
          @endforeach
        </div>
        <!-- / Tab Content On Tab Click -->
      </div>
    </div>
    <!-- / Content -->
    <div class="content-backdrop fade"></div>
  </div>
  <!-- / Content wrapper -->
@stop

@section('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
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

  <!-- <script src="/vuexy/assets/js/forms-tagify.js"></script> -->
  <script src="/vuexy/assets/js/pages-edit-compnay.js"></script>
  {{-- <script src="/vuexy/assets/js/founder-form.js"></script> --}}
  {{-- <script src="/vuexy/assets/js/shareholder-form.js"></script> --}}
  {{-- <script src="/vuexy/assets/js/investor-form.js"></script> --}}

  <!-- <script>
    document.getElementById('img_url').addEventListener('change', function(
    event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('company-logo').src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  </script>  -->

  <script>
    
    document.getElementById('reset-company-logo').addEventListener('click', async () => {
        if (!confirm('هل أنت متأكد من حذف الشعار؟')) return;

        const response = await fetch("{{ route('company.profile_images_delete', $client->company->id ?? 0) }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                "Content-Type": "application/json"
            }
        });
    });




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
      const activeIndex = hasCompleteProfile || !activeCompanyTab ? 0 : JSON.parse(activeCompanyTab);
      const activePitchIndex = hasCompleteProfile || !activePitchTab ? 0 : JSON.parse(activePitchTab);

      // Clear localStorage if complete-profile action
      if (hasCompleteProfile) {
        localStorage.removeItem('activeCompanyTab');
        localStorage.removeItem('activePitchTab');
      }

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

    document.addEventListener('DOMContentLoaded', function() {
      // الاستماع لتغيير التبويبات
      document.querySelectorAll('.nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
          // تحديد التبويب النشط
          const activeTab = e.target.getAttribute('href');
          // تعطيل الحقول المطلوبة للتبويبات غير النشطة
          document.querySelectorAll('.tab-pane').forEach(pane => {
            if (pane.getAttribute('id') === activeTab.substring(
              1)) {
              pane.querySelectorAll('input').forEach(input =>
                input.removeAttribute('disabled'));
            } else {
              pane.querySelectorAll('input').forEach(input =>
                input.setAttribute('disabled', 'disabled'));
            }
          });
        });
      });
    });

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

{{-- <script>
@if($client->company)
  const selectedCountryCode = '{{$client->company->country_code}}';
 @else
  const selectedCountryCode = '+20';
@endif
  fetch('https://restcountries.com/v3.1/all')
  .then(response => response.json())
  .then(data => {
      const select = document.getElementById('country_code');
      data.forEach(country => {
          // دمج root و suffixes لتكوين كود الهاتف الدولي الكامل
          const countryCode = `${country.idd.root}${country.idd.suffixes ? country.idd.suffixes[0] : ''}`;
          
          // إنشاء عنصر <option> لكل دولة
          const option = document.createElement('option');
          option.value = countryCode;
          option.text = `${countryCode} (${country.cca2})`;
          option.style.fontSize = '12px';
          
          // إذا كان كود الهاتف من قاعدة البيانات مطابقاً للكود الحالي
          if (countryCode === selectedCountryCode) {
              option.selected = true;  // يتم تحديد الخيار
          }if (!selectedCountryCode && country.cca2 === 'EG') {
              option.selected = true; // تعيين "مصر" كخيار افتراضي
          }

          select.appendChild(option);  // إضافة العنصر إلى القائمة المنسدلة
      });
  });
</script> --}}
{{-- 
<script>
  // Initialize the phone input
const phoneInput = document.getElementById("phone");
const whatsappInput = document.getElementById("WhatsApp");
const phoneError = document.getElementById('phone-error');
const whatsappError = document.getElementById('whatsapp-error');

const countryCodeFromDB = "{{ $client->company ? $client->company->country_code : '' }}".trim();
const countryCodeWAFromDB = "{{ $client->company ? $client->company->country_code_w_a : '' }}".trim();

// Initialize intl-tel-input
const iti = intlTelInput(phoneInput, {
  initialCountry: getCountryCode(countryCodeFromDB),
  preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"],
  utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
  formatOnDisplay: false,
  separateDialCode: true,
  autoPlaceholder: "aggressive",
  customPlaceholder: (placeholder) => "e.g. " + placeholder,
});

function getCountryCode(code) {
  let dialCode = code.toString().trim();
  const countryData = intlTelInputGlobals.getCountryData();
  const country = countryData.find(country => `+${country.dialCode}` === dialCode);

  if (country) {
    console.log("Country Code Found: " + country.iso2);
    return country.iso2;
  }
  return "eg";
}

const whatsappIti = intlTelInput(whatsappInput, {
  initialCountry: getCountryCode(countryCodeWAFromDB),
  preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"],
  utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
  separateDialCode: true,
  formatOnDisplay: false,
  autoPlaceholder: "aggressive",
  customPlaceholder: (placeholder) => "e.g. " + placeholder,
});


// حقل مخفي للكود الدولي للواتساب
const countryCodeWAInput = document.createElement("input");
countryCodeWAInput.type = "hidden";
countryCodeWAInput.name = "country_code_w_a";
countryCodeWAInput.value = "{{ $client->company ? ( $client->company->country_code_w_a ?? '' ) : '' }}";
whatsappInput.parentNode.appendChild(countryCodeWAInput);

// تحديث الحقل المخفي عند تغيير الدولة في الواتساب
whatsappInput.addEventListener("countrychange", () => {
    const dialCode = whatsappIti.getSelectedCountryData().dialCode;
    countryCodeWAInput.value =  dialCode; // تعيين الكود الدولي
    console.log("WhatsApp Dial Code Updated: " + countryCodeWAInput.value);
});

// حقل مخفي للكود الدولي
const countryCodeInput = document.createElement("input");
countryCodeInput.type = "hidden";
countryCodeInput.name = "country_code";
countryCodeInput.value = "{{ $client->company ? ( $client->company->country_code ?? '' ) : '' }}";
phoneInput.parentNode.appendChild(countryCodeInput);

// ضبط الدولة الافتراضية
if (countryCodeFromDB) {
    iti.setCountry(countryCodeFromDB);
    countryCodeInput.value =  iti.getSelectedCountryData().dialCode;
}

// تحديث الحقل المخفي عند تغيير الدولة
phoneInput.addEventListener("countrychange", () => {
    const dialCode = iti.getSelectedCountryData().dialCode;
    countryCodeInput.value =  dialCode;
});

whatsappInput.addEventListener("countrychange", () => {
    const dialCode = whatsappIti.getSelectedCountryData().dialCode;
    console.log("WhatsApp Dial Code: " + dialCode);
});

// التحقق من صحة الرقم
phoneInput.addEventListener("input", () => {
    if (iti.isValidNumber()) {
        phoneError.style.display = 'none';
    } else {
        phoneError.style.display = 'block';
        phoneError.innerText = "Invalid phone number";
    }
});

whatsappInput.addEventListener("input", () => {
    if (whatsappIti.isValidNumber()) {
        whatsappError.style.display = 'none';
    } else {
        whatsappError.style.display = 'block';
        whatsappError.innerText = "Invalid WhatsApp number";
    }
});


</script> --}}
@stop


