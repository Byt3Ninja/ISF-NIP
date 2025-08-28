@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/tagify/tagify.css" />
@stop

@php
  $id = request()->route('id');
  $ccycle = \App\Cycle::find(0);
  $tabs = [
    ['id' => 'program_info', 'label' => __('admin.program_info'), 'active' => true],
    ['id' => 'program_guideline', 'label' => __('admin.program_guideline'), 'active' => false],
    ['id' => 'financial_policy', 'label' => __('admin.financial_policy'), 'active' => false],
    ['id' => 'legal_policy', 'label' => __('admin.legal_policy'), 'active' => false],
    ['id' => 'mentorship_request', 'label' => __('admin.mentorship_request'), 'active' => false],
  ];
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <h4 class="fw-bold mb-4">
        {{ __('cycles.create_program') }}
      </h4>
      @if (session()->has('message'))
        <x-alert type="success" message="{{ __('admin.' . session()->get('message')) }} {{ Session::forget('message') }}" />      @endif
      @if (session()->has('message2'))
        <x-alert type="danger" message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" />
      @endif
      @if ($errors->any())
        @foreach ($errors->all() as $error)
          <x-alert type="danger" message="{{ $error }}" />
        @endforeach
      @endif
    </div>

    <div class="card p-4 p-md-8">
      <form action="{{ route('cycles.modify') }}" method="POST" enctype="multipart/form-data">
        @csrf 
        <input type="hidden" name="tab_id" value="1">
        <!-- Tabs (mobile) -->
        <div style="max-width: 100%; overflow-x: auto;" class="mb-4 d-md-none pb-3">
          <ul class="nav nav-pills flex-nowrap" role="tablist">
            @foreach ($tabs as $key => $tab)
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link {{ $tab['active'] ? 'active' : '' }} {{ $tab['id'] != 'program_info' ? 'tab-disabled' : '' }}"
                  id="pills-{{ $tab['id'] }}-tab-edit"
                  data-bs-toggle="pill"
                  data-bs-target="#pills-{{ $tab['id'] }}-edit"
                  type="button"
                  role="tab"
                  aria-controls="pills-{{ $tab['id'] }}-edit"
                  aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
                  {{ $tab['id'] != 'program_info' ? 'disabled' : '' }}
                >
                  <span class="fw-semibold">{{ $tab['label'] }}</span>
                </button>
              </li>
            @endforeach
          </ul>
        </div>
        <!-- / Tabs (mobile) -->

        <ul
          class="nav nav-pills d-none d-md-flex"
          id="pills-tab"
          role="tablist"
        >
          @foreach ($tabs as $tab)
            <li class="nav-item" role="presentation">
              <button
                class="nav-link {{ $tab['active'] ? 'active' : '' }} {{ $tab['id'] != 'program_info' ? 'tab-disabled' : '' }}"
                id="pills-{{ $tab['id'] }}-tab"
                data-bs-toggle="pill"
                data-bs-target="#pills-{{ $tab['id'] }}-edit"
                type="button"
                role="tab"
                aria-controls="pills-{{ $tab['id'] }}-edit"
                aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
                {{ $tab['id'] != 'program_info' ? 'disabled' : '' }}
              >
                <span class="fw-semibold">{{ $tab['label'] }}</span>
              </button>
            </li>
          @endforeach
        </ul>
        <hr />
        <div class="tab-content shadow-none p-4" id="pills-tabContent">
          @foreach ($tabs as $tab)
            <div
              class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}"
              id="pills-{{ $tab['id'] }}-edit"
              role="tabpanel"
              aria-labelledby="pills-{{ $tab['id'] }}-tab"
            >
              @include('admin.programs.partials._edit_' . $tab['id'], ['mode' => 'edit', 'ccycle' => $ccycle])
            </div>
          @endforeach
        </div>            
        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 p-4 p-md-0">
          {{-- <button type="button" class="btn btn-label-secondary w-px-120">
            {{ __('cycles.Reset') }}
            <i class="ti ti-refresh-dot ms-2"></i>
          </button> --}}
          <button type="submit" class="btn btn-primary w-px-120">
            {{ __('cycles.Create') }}
            <i class="ti ti-circle-check ms-2"></i>
          </button>
        </div> 
      </form>
    </div>
  </div>
</div>
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
@stop

@section('pagescripts')
  <script>
    // Set Language
    const lang = '{{ $l }}';

    $('#date-range-picker').daterangepicker({
        opens: isRtl ? 'left' : 'right',
        drops: 'up',
      }).on('apply.daterangepicker', function(ev, picker) {
        $('#start').val(picker.startDate.format('YYYY-MM-DD'));
        $('#end').val(picker.endDate.format('YYYY-MM-DD'));
      });

    const editValidFrom = document.querySelector('.valid-from');
    // const editValidalidTo = document.querySelector('.valid-to');
    flatpickr(".datepicker", {
      dateFormat: "Y-m-d", // تنسيق التاريخ المطلوب
      allowInput: true, // السماح للمستخدم بكتابة التاريخ يدويًا
      locale: "ar", // دعم اللغة العربية (يمكنك تغييره)
    });

    // Initialize Quill Editor
    const editorConfig = {
      theme: 'snow'
    };

    const editGuidelineEditor = new Quill('#guideline-editor', editorConfig);
    const editFinancialEditor = new Quill('#financial-editor', editorConfig);
    const editLegalEditor = new Quill('#legal-editor', editorConfig);

    // Clear Editor
    editGuidelineEditor.root.innerHTML = '';
    editFinancialEditor.root.innerHTML = '';
    editLegalEditor.root.innerHTML = '';

    // Handle Editor Change
    const handleEditorChange = (editor, input) => {
      editor.on('text-change', function() {
        $(input).val(editor.root.innerHTML);
      });
    };

    handleEditorChange(editGuidelineEditor, '#program_guideline');
    handleEditorChange(editFinancialEditor, '#financial_policy');
    handleEditorChange(editLegalEditor, '#legal_policy');

    // Handle Append File
    const appendFile = (mode, file, conainerId) => {
      console.log('file', file)
      if (file) {
        const fileName = file.split('/').pop();
        const content = (`
          <div class="d-flex align-items-center justify-content-between border p-2 rounded mb-2">
            <div class="d-flex align-items-center gap-2">
              <i class="ti ti-file fs-4 text-warning"></i>
              <span class="text-truncate" style="max-width: 200px;">${fileName}</span>
            </div> 
            <a class="btn btn-label-primary btn-icon btn-sm" href="/storage/${file}" target="_blank" download="${file}">
              <i class="ti ti-download fs-5"></i>
            </a>
          </div>`);
        $(`${conainerId}`).append(content);
      }
    };
  </script>
  <script>
    $(document).on('change', '#is_public', function() {
        let _public = $(this).val();
        if( _public == 1 ) { $('#public_slug_div').show(); } else { $('#public_slug_div').hide(); }
    });
    // Handle image upload and reset
    document.querySelector('.upload-img').addEventListener('click', function () {
      document.querySelector('.program-image-input').click();
    });
    document.querySelector('.program-image-input').addEventListener('change', function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
          const uploadImage = document.getElementById('uploadImage');
          const uploadIcon = document.getElementById('uploadIcon');
          uploadImage.src = event.target.result;
          uploadImage.style.display = 'block';
          uploadIcon.style.display = 'none';
        };
        reader.readAsDataURL(file);
      }
    });
    document.querySelector('.program-image-reset').addEventListener('click', function () {
      const uploadImage = document.getElementById('uploadImage');
      const uploadIcon = document.getElementById('uploadIcon');
      uploadImage.src = '';
      uploadImage.style.display = 'none';
      uploadIcon.style.display = 'block';
      document.querySelector('.program-image-input').value = '';
    });

    $(document).ready(function() {
      $('#is_public').trigger('change');
    });

    // Function to validate title and description fields
    function validateRequiredFields() {
      const title = document.getElementById('title').value.trim();
      const description = document.getElementById('description').value.trim();
      const isValid = title !== '' && description !== '';
      
      // Get all tab buttons except program_info
      const tabButtons = document.querySelectorAll('[id^="pills-"][id$="-tab"]:not([id="pills-program_info-tab"]), [id^="pills-"][id$="-tab-edit"]:not([id="pills-program_info-tab-edit"])');
      
      tabButtons.forEach(button => {
        if (isValid) {
          // Enable tabs
          button.classList.remove('tab-disabled');
          button.removeAttribute('disabled');
          button.style.pointerEvents = '';
        } else {
          // Disable tabs
          button.classList.add('tab-disabled');
          button.setAttribute('disabled', 'disabled');
          button.style.pointerEvents = 'none';
          
          // If current tab is disabled, switch back to program_info
          if (button.classList.contains('active')) {
            button.classList.remove('active');
            button.setAttribute('aria-selected', 'false');
            
            // Hide current tab content
            const targetId = button.getAttribute('data-bs-target');
            const targetContent = document.querySelector(targetId);
            if (targetContent) {
              targetContent.classList.remove('show', 'active');
            }
            
            // Show program_info tab
            const programInfoTab = document.querySelector('#pills-program_info-tab') || document.querySelector('#pills-program_info-tab-edit');
            const programInfoContent = document.querySelector('#pills-program_info-edit');
            if (programInfoTab && programInfoContent) {
              programInfoTab.classList.add('active');
              programInfoTab.setAttribute('aria-selected', 'true');
              programInfoContent.classList.add('show', 'active');
            }
          }
        }
      });
    }

    // Add event listeners to title and description fields
    document.addEventListener('DOMContentLoaded', function() {
      const titleField = document.getElementById('title');
      const descriptionField = document.getElementById('description');
      
      if (titleField && descriptionField) {
        titleField.addEventListener('input', validateRequiredFields);
        descriptionField.addEventListener('input', validateRequiredFields);
        
        // Initial validation
        validateRequiredFields();
      }
    });

    // Prevent tab switching if fields are not filled
    document.addEventListener('click', function(e) {
      if (e.target.matches('.tab-disabled') || e.target.closest('.tab-disabled')) {
        e.preventDefault();
        e.stopPropagation();
        
        // Show alert or notification
        alert('Please fill in the Program Title and Description before accessing other tabs.');
        return false;
      }
    });
  </script>
@stop
