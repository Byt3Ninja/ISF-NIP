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
  $tabs = [
    ['id' => 'program_info', 'label' => 'Program Info', 'active' => true],
    ['id' => 'program_guideline', 'label' => 'Program Guideline', 'active' => false],
    ['id' => 'financial_policy', 'label' => 'Financial Policy', 'active' => false],
    ['id' => 'legal_policy', 'label' => 'Legal Policy', 'active' => false],
    ['id' => 'mentorship_request', 'label' => 'Mentorship Request', 'active' => false],
  ];
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <h4 class="fw-bold mb-4">
        {{ __('cycles.edit_program') }}
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
                  class="nav-link {{ $tab['active'] ? 'active' : '' }}"
                  id="pills-{{ $tab['id'] }}-tab-edit"
                  data-bs-toggle="pill"
                  data-bs-target="#pills-{{ $tab['id'] }}-edit"
                  type="button"
                  role="tab"
                  aria-controls="pills-{{ $tab['id'] }}-edit"
                  aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
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
          id="pills-tab-edit"
          role="tablist"
        >
          @foreach ($tabs as $tab)
            <li class="nav-item" role="presentation">
              <button
                class="nav-link {{ $tab['active'] ? 'active' : '' }}"
                id="pills-{{ $tab['id'] }}-tab-edit"
                data-bs-toggle="pill"
                data-bs-target="#pills-{{ $tab['id'] }}-edit"
                type="button"
                role="tab"
                aria-controls="pills-{{ $tab['id'] }}-edit"
                aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
              >
                <span class="fw-semibold">{{ $tab['label'] }}</span>
              </button>
            </li>
          @endforeach
        </ul>
        <hr />
        <div class="tab-content shadow-none p-2 p-md-4" id="pills-tabContent-edit">
          @foreach ($tabs as $tab)
            <div
              class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}"
              id="pills-{{ $tab['id'] }}-edit"
              role="tabpanel"
              aria-labelledby="pills-{{ $tab['id'] }}-tab-edit"
            >
              @include('admin.programs.partials._edit_' . $tab['id'], ['mode' => 'edit'])
            </div>
          @endforeach
        </div>            
        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 p-4 p-md-0">
          {{-- <button type="reset" class="btn btn-label-secondary w-px-120">
            {{ __('cycles.Reset') }}
            <i class="ti ti-refresh-dot ms-2"></i>
          </button> --}}
          <button type="submit" class="btn btn-primary w-px-120">
            {{ __('cycles.Edit') }}
            <i class="ti ti-edit ms-2"></i>
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
  @php($ccycle = \App\Cycle::find(0))
  <script>
    // Set Language
    const lang = '{{ $l }}';

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

    $(document).ready(function() {
      // Handle Edit Program
      const cycleId = '{{ $id }}';
      if (cycleId) {
        // $('#edit-program-modal').modal('show');

        $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
        });

        $.ajax({
        type: "POST",
        url: "/dashboard/cycles/modify",
        data: {
          "cycleid": cycleId
        },
        success: function(response) {
          console.log(response);

          $('#date-range-picker').daterangepicker({
          opens: isRtl ? 'left' : 'right',
          drops: 'up',
          startDate: response?.start ? moment(response?.start) : null,
          endDate: response?.end ? moment(response?.end) : null,
          }).on('apply.daterangepicker', function(ev, picker) {
          $('#start').val(picker.startDate.format('YYYY-MM-DD'));
          $('#end').val(picker.endDate.format('YYYY-MM-DD'));
          });

          $('#start').val(response.start ?? '');
          $('#end').val(response.end ?? '');
          $('#title').val(response.title ?? '');
          $('#description').val(response.description ?? '');
          $('#cycleid').val(cycleId);
          $('#type').val(1);
          $('#organization').val(response.organization_id ?? '');
          $('#allow_non_company').prop('checked', response.allow_non_company == 1 ? true :false);
          $('#auto-approve-mentorship-requests').prop('checked', response.auto_approve_mentorship_requests == 1 ? true : false);

          if (response.private) {
          $('#private').prop('checked', true);
          $('#cyclelink').html('{{ __('cycles.Join URL') }}');
          $('#cyclelink').attr('href', '/dashboard/cycles/view/' + response.id + '?id=' + response.private);
          }

          if (editGuidelineEditor) {
          editGuidelineEditor.root.innerHTML = response.program_guideline ?? '';
          }

          $('#program_guideline').val(response.program_guideline ?? '');

          if (editFinancialEditor) {
          editFinancialEditor.root.innerHTML = response.financial_guideline ?? '';
          }
          $('#financial_policy').val(response.financial_guideline ?? '');
          $('#budget_allocation').val(response.budget_allocation ?? '');
          $('#currency_type').val(response.currency_type ?? '');

          if (editLegalEditor) {
          editLegalEditor.root.innerHTML = response.legal_guideline ?? '';
          }
          $('#legal_policy').val(response.legal_guideline ?? '');
          const guidelineFile = response?.program_guideline_file;
          const financialFile = response.financial_guideline_file;
          const legalFile = response?.legal_guideline_file;
          const delegationFile = response.delegation_file;

          appendFile('edit', guidelineFile, '#guideline-file-container');
          appendFile('edit', financialFile, '#financial-file-container');
          appendFile('edit', legalFile, '#legal-file-container');

          if (delegationFile) {
          appendFile('edit', delegationFile, '#delegation-file-container');
          $('#delegation_option').prop('checked', true);
          }
        },
        error: function(error) {
          console.log(error);
          resetValues();
          alert("Error Occurred, Try again!");
        }
        });
      }
    });
  </script>
  <script>
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
  </script>
@stop
