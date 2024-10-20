@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet"
    href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
  <style>
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
      .custom-container {
        width: 80%;
      }
    }
  </style>
@stop

@php
  $name = 'name_' . $l;
  $tabs = collect([
      ['company-info', 'ti-building-skyscraper', $l == 'en' ? 'Company Info' : 'معلومات الشركة'],
      ['company-contact-info', 'ti-phone', $l == 'en' ? 'Contact Info' : 'معلومات الاتصال'],
      ['company-description', 'ti-file-description', $l == 'en' ? 'Description' : 'الوصف'],
      ['company-financial-info', 'ti-currency-dollar', $l == 'en' ? 'Financial' : ' المالية'],
      ['team-info', 'ti-users', $l == 'en' ? 'Team Info' : ' الفريق'],
      ['pitch-deck', 'ti-presentation', $l == 'en' ? 'Pitch Deck' : 'الوثائق'],
      ['company-milestones', 'ti-flag', $l == 'en' ? 'Milestones' : 'الإنجازات'],
      ['company-social', 'ti-brand-facebook', $l == 'en' ? 'Logo & Social' : 'الشعار والتواصل'],
  ])
      ->map(function ($tab) {
          return ['target' => $tab[0], 'icon' => $tab[1], 'label' => $tab[2]];
      })
      ->toArray();
@endphp

@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y overflow-x-hidden" style="max-width: 100%;">
      <!-- Alert -->
      @if (session()->has('msg'))
        <x-alert type="{{ session()->get('class') ?? 'danger' }}"
          message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" />
      @endif
      <!-- Validation messages -->
      @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
          <x-alert type="danger" message="{{ $error }}" />
        @endforeach
      @endif
      <!-- / Alert -->

      <!-- Tabs (mobile) -->
      <div style="max-width: 100%; overflow-x: auto;" class="mb-4 d-md-none">
        <ul class="nav nav-pills flex-nowrap" role="tablist">
          @foreach ($tabs as $key => $tab)
            <li class="nav-item d-flex justify-content-center align-items-center">
              <a href="#navs-left-{{ $tab['target'] }}"
                class="nav-link {{ $key == 0 ? 'active' : '' }}"
                data-bs-toggle="tab" role="tab">
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
          class="nav-align-left nav-tabs-shadow mb-6 p-4 bg-white position-fixed d-none d-md-block" 
          style="height: 440px; min-width: 15%; max-width: 15%;">
          <!-- Tabs -->
          <ul class="nav nav-tabs nav-fill border-none m-auto" role="tablist">
            @foreach ($tabs as $key => $tab)
              <li class="nav-item bg-label-light mb-2 rounded overflow-x-hidden d-flex align-items-center">
                <a href="#navs-left-{{ $tab['target'] }}"
                  class="nav-link {{ $key == 0 ? 'active' : '' }} py-3"
                  data-bs-toggle="tab" role="tab">
                  <i class="ti {{ $tab['icon'] }} me-2 fs-5"></i>
                  {{ $tab['label'] }}
                </a>
              </li>
            @endforeach
          </ul>
          <!-- / Tabs -->
        </nav>
        <!-- Tab Content On Tab Click -->
        <div class="tab-content p-0 overflow-y-auto custom-container mb-6" 
          style="{{ $l == 'en' ? 'margin-left: auto' : 'margin-right: auto' }}"
        >
          @foreach ($tabs as $index => $tab)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
              id="navs-left-{{ $tab['target'] }}">
              @switch($tab['target'])
                @case('company-info')
                  @include('partials.forms.company.company-info-form',['heading' => $tab['label']])
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
                  @include('partials.forms.company.company-team-info', ['heading' => $tab['label']])
                @break
                @case('pitch-deck')
                  @include('partials.forms.company.company-pitch-deck', ['heading' => $tab['label']])
                @break
                @case('company-milestones')
                  @include('partials.forms.company.company-milestones', ['heading' => $tab['label']])
                @break
                @case('company-social')
                  @include('partials.forms.company.company-social', ['heading' => $tab['label']])
                @break
              @endswitch
            </div>
          @endforeach
        </div>
        <!-- / Tab Content On Tab Click -->
      </div>
    </div>
    <!-- / Content -->
  </div>
  <!-- / Content wrapper -->
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
  <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
  <script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/forms-selects.js"></script>
  <script src="/vuexy/assets/js/forms-pickers.js"></script>

  <script>
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
  </script> 

  <script>
    document.getElementById('update-form').addEventListener('submit', function(
      event) {
      var button = document.getElementById('submit-button');
      var buttonText = document.getElementById('button-text');
      var buttonSpinner = document.getElementById('button-spinner');

      button.disabled = true;
      buttonText.classList.add('d-none');
      buttonSpinner.classList.remove('d-none');
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
        url: "{{ route('get_states_by_country') }}",
        data: {
          country_id: country_id
        },
        success: function(data) {
          $state.html(
          '<option value="" >{{ __('admin.State') }}</option>');
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
          $city.html('<option value="" >{{ __('admin.City') }}</option>');
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
    $('#logo').filemanager(['image']);

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
        '{{ route('update_my_company') }}');
    });

    // Upload for Pitch Deck Video
    $('#pitch_deck_video').on('change', function() {
      uploadFile('pitch_deck_video', 'progress-bar-pitch_deck_video',
        '{{ route('update_my_company') }}');
    });

    // Upload for Pitch Deck Video
    $('#img_url').on('change', function() {
      uploadFile('img_url', 'progress-bar-Logo',
        '{{ route('update_my_company') }}');
    });
  </script>
@stop


