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
    $ccycle = \App\Cycle::findOrFail($id);
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
                <x-alert type="success" message="{{ __('admin.' . session()->get('message')) }} {{ Session::forget('message') }}" />
            @endif
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
                <input type="hidden" name="id" id="cycleid" value="{{ $id }}">
                <input type="hidden" name="typeedit" id="type" value="1">
                <input type="hidden" name="organization_id" value="{{ $ccycle->organization_id ?? 0 }}">

                <!-- Tabs (mobile) -->
                <div style="max-width: 100%; overflow-x: auto;" class="mb-4 d-md-none pb-3">
                    <ul class="nav nav-pills flex-nowrap" role="tablist">
                        @foreach ($tabs as $tab)
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

                <ul class="nav nav-pills d-none d-md-flex" id="pills-tab-edit" role="tablist">
                    @foreach ($tabs as $tab)
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
                <hr />
                <div class="tab-content shadow-none p-2 p-md-4" id="pills-tabContent-edit">
                    <!-- Program Guideline Tab -->
                    <div
                        class="tab-pane fade {{ $tabs[1]['active'] ? 'show active' : '' }}"
                        id="pills-program_guideline-edit"
                        role="tabpanel"
                        aria-labelledby="pills-program_guideline-tab-edit"
                    >
                        <div class="row mb-12">
                            <div class="form-group mb-6">
                                <label for="program_guideline" class="form-label fs-6 fw-semibold">
                                    {{ __('cycles.Program_Guideline') }}
                                </label>
                                <div style="height: 150px; overflow-y: hidden;" class="border border-light rounded">
                                    <div id="guideline-editor" class="form-control border-none"></div>
                                </div>
                                <textarea
                                    name="program_guideline"
                                    id="program_guideline"
                                    class="form-control guidline d-none"
                                    rows="5"
                                    placeholder="{{ __('cycles.Enter_Program_Guideline') }}"
                                >{{ $ccycle->program_guideline ?? '' }}</textarea>
                            </div>
                            <div class="form-group mb-6">
                                <label for="program-guideline-pdf" class="form-label fs-6 fw-semibold">
                                    {{ __('cycles.Attach_Document') }} ({{ __('cycles.PDF_Only') }})
                                </label>
                                <div class="mb-3">
                                    <input
                                        type="file"
                                        name="program-guideline-pdf"
                                        id="program-guideline-pdf"
                                        class="form-control"
                                        accept="application/pdf"
                                    />
                                </div>
                            </div>
                            <div id="guideline-file-container" class="mb-4"></div>
                        </div>
                    </div>

                    <!-- Financial Policy Tab -->
                    <div
                        class="tab-pane fade {{ $tabs[2]['active'] ? 'show active' : '' }}"
                        id="pills-financial_policy-edit"
                        role="tabpanel"
                        aria-labelledby="pills-financial_policy-tab-edit"
                    >
                        <div class="row mb-12">
                            <div class="col-12 col-md-8 mb-6">
                                <div class="form-group">
                                    <label for="budget_allocation" class="form-label fs-6 fw-semibold">{{ __('cycles.Budget_Allocation') }}</label>
                                    <input
                                        type="number"
                                        name="budget_allocation"
                                        id="budget_allocation"
                                        class="form-control"
                                        placeholder="{{ __('cycles.Budget_Allocation') }}"
                                        value="{{ $ccycle->budget_allocation ?? '' }}"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-4 mb-6">
                                <div class="form-group">
                                    <label for="currency_type" class="form-label fs-6 fw-semibold">{{ __('cycles.Currency_Type') }}</label>
                                    <select
                                        name="currency_type"
                                        id="currency_type"
                                        class="form-select"
                                        placeholder="{{ __('cycles.Currency_Type') }}"
                                        value="{{ $ccycle->currency_type ?? '' }}"
                                    >
                                        <option value="" disabled selected>{{ __('cycles.Select_Currency') }}</option>
                                        <option value="USD" {{ $ccycle->currency_type == 'USD' ? 'selected' : '' }}>{{ __('admin.USD') }}</option>
                                        <option value="EUR" {{ $ccycle->currency_type == 'EUR' ? 'selected' : '' }}>{{ __('admin.EUR') }}</option>
                                        <option value="GBP" {{ $ccycle->currency_type == 'GBP' ? 'selected' : '' }}>{{ __('admin.GBP') }}</option>
                                        <option value="SAR" {{ $ccycle->currency_type == 'SAR' ? 'selected' : '' }}>{{ __('admin.SAR') }}</option>
                                        <option value="EGP" {{ $ccycle->currency_type == 'EGP' ? 'selected' : '' }}>{{ __('admin.EGP') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-6">
                                <label for="financial_policy" class="form-label fs-6 fw-semibold">{{ __('cycles.Financial_Policy') }}</label>
                                <div style="height: 150px; overflow-y: hidden;" class="border border-light rounded">
                                    <div id="financial-editor" class="form-control border-none"></div>
                                </div>
                                <textarea
                                    name="financial_policy"
                                    id="financial_policy"
                                    class="form-control policy d-none"
                                    rows="5"
                                    placeholder="{{ __('cycles.Financial_Policy') }}"
                                >{{ $ccycle->financial_guideline ?? '' }}</textarea>
                            </div>
                            <div class="form-group mb-6">
                                <label for="financial_policy_pdf" class="form-label fs-6 fw-semibold">
                                    {{ __('cycles.Attach_Document') }} ({{ __('cycles.PDF_Only') }})
                                </label>
                                <div class="mb-3">
                                    <input
                                        type="file"
                                        name="financial_policy_pdf"
                                        id="financial_policy_pdf"
                                        class="form-control"
                                        accept="application/pdf"
                                    />
                                </div>
                            </div>
                            <div id="financial-file-container" class="mb-6"></div>
                        </div>
                    </div>

                    <!-- Legal Policy Tab -->
                    <div
                        class="tab-pane fade {{ $tabs[3]['active'] ? 'show active' : '' }}"
                        id="pills-legal_policy-edit"
                        role="tabpanel"
                        aria-labelledby="pills-legal_policy-tab-edit"
                    >
                        <div class="row">
                            <div class="form-group mb-4">
                                <label for="legal_policy" class="form-label fs-6 fw-semibold">{{ __('cycles.Legal_Policy_Description') }}</label>
                                <div style="height: 150px; overflow-y: hidden;" class="border border-light rounded">
                                    <div id="legal-editor" class="form-control border-none"></div>
                                </div>
                                <textarea
                                    name="legal_policy"
                                    id="legal_policy"
                                    class="form-control legal d-none"
                                    rows="5"
                                    placeholder="{{ __('cycles.legal_policy') }}"
                                >{{ $ccycle->legal_guideline ?? '' }}</textarea>
                            </div>
                            <div class="form-group mb-6">
                                <label for="legal_policy_pdf" class="form-label fs-6 fw-semibold">
                                    {{ __('cycles.Attach_Document') }} ({{ __('cycles.PDF_Only') }})
                                </label>
                                <div class="mb-3">
                                    <input
                                        type="file"
                                        name="legal_policy_pdf"
                                        id="legal_policy_pdf"
                                        class="form-control"
                                        accept="application/pdf"
                                    />
                                </div>
                            </div>
                            <div id="legal-file-container" class="mb-4"></div>
                            <div class="form-group mb-4">
                                <div class="form-check form-switch">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="delegation_option"
                                        id="delegation_option"
                                        onchange="toggleFormGroup(this)"
                                        {{ $ccycle && $ccycle->delegation_file ? 'checked' : '' }}
                                        value="yes"
                                    />
                                    <label class="form-check-label" for="delegation_option">
                                        {{ __('cycles.Delegation_Option') }}
                                    </label>
                                </div>
                            </div>
                            <div class="form-group mb-6" id="delegation-form-group" style="display: {{ $ccycle && $ccycle->delegation_file ? 'block' : 'none' }};">
                                <label for="delegation_pdf" class="form-label fs-6 fw-semibold">
                                    {{ __('cycles.Attach_Delegation_Document') }} ({{ __('cycles.PDF_Only') }})
                                </label>
                                <div class="mb-3">
                                    <input
                                        type="file"
                                        name="delegation_pdf"
                                        id="delegation_pdf"
                                        class="form-control"
                                        accept="application/pdf"
                                    />
                                </div>
                            </div>
                            <div id="delegation-file-container" class="mb-4"></div>
                        </div>
                    </div>

                    @foreach ($tabs as $tab)
                        @if ($tab['id'] != 'program_guideline' && $tab['id'] != 'financial_policy' && $tab['id'] != 'legal_policy')
                            <div
                                class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}"
                                id="pills-{{ $tab['id'] }}-edit"
                                role="tabpanel"
                                aria-labelledby="pills-{{ $tab['id'] }}-tab-edit"
                            >
                                @include('admin.programs.partials._edit_' . $tab['id'], ['mode' => 'edit'])
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="d-flex align-items-center justify-content-end gap-3 mt-4 p-4 p-md-0">
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
    <script>
        // Set Language
        const lang = '{{ $l }}';

        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            allowInput: true,
            locale: "ar",
        });

        // Initialize Quill Editors
        const editorConfig = {
            theme: 'snow'
        };
        const editGuidelineEditor = new Quill('#guideline-editor', editorConfig);
        const editFinancialEditor = new Quill('#financial-editor', editorConfig);
        const editLegalEditor = new Quill('#legal-editor', editorConfig);

        // Set initial editor content
        editGuidelineEditor.root.innerHTML = @json($ccycle->program_guideline ?? '');
        editFinancialEditor.root.innerHTML = @json($ccycle->financial_guideline ?? '');
        editLegalEditor.root.innerHTML = @json($ccycle->legal_guideline ?? '');

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
        const appendFile = (mode, file, containerId) => {
            // console.log('file', file, 'disk', disk);
            if (!file) return;
            const fileName = file.split('/').pop();
            const displayName = fileName.replace(/_\d+\.pdf$/, '.pdf');
            const content = `
                <div class="d-flex align-items-center justify-content-between border p-2 rounded mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-file fs-4 text-warning"></i>
                        <span class="text-truncate" style="max-width: 200px;">${displayName}</span>
                    </div> 
                    <a class="btn btn-label-primary btn-icon btn-sm" href="{{ asset('191014/programs') }}/${fileName}" download="${displayName}">
                        <i class="ti ti-download fs-5"></i>
                    </a>
                </div>`;
            $(containerId).append(content);
        };

        $(document).ready(function() {
            @if($ccycle->program_guideline_file)
                appendFile('edit', @json(basename($ccycle->program_guideline_file)), '#guideline-file-container');
            @endif
            @if($ccycle->financial_guideline_file)
                appendFile('edit', @json(basename($ccycle->financial_guideline_file)), '#financial-file-container');
            @endif
            @if($ccycle->legal_guideline_file)
                appendFile('edit', @json(basename($ccycle->legal_guideline_file)), '#legal-file-container');
            @endif
            @if($ccycle->delegation_file)
                appendFile('edit', @json(basename($ccycle->delegation_file)), '#delegation-file-container');
            @endif

            // Handle Edit Program AJAX
            const cycleId = '{{ $id }}';
            if (cycleId) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "/dashboard/cycles/modify",
                    data: {
                        cycleid: cycleId
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
                        $('#allow_non_company').prop('checked', response.allow_non_company == 1);
                        $('#auto-approve-mentorship-requests').prop('checked', response.auto_approve_mentorship_requests == 1);

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
                        
                        // Validate fields after loading data
                        setTimeout(validateRequiredFields, 100);
                    },
                    error: function(error) {
                        console.log(error);
                        alert("Error fetching cycle data. Please try again.");
                    }
                });
            }
        });

        // Handle image upload and reset
        document.querySelector('.upload-img')?.addEventListener('click', function() {
            document.querySelector('.program-image-input')?.click();
        });
        document.querySelector('.program-image-input')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const uploadImage = document.getElementById('uploadImage');
                    const uploadIcon = document.getElementById('uploadIcon');
                    if (uploadImage && uploadIcon) {
                        uploadImage.src = event.target.result;
                        uploadImage.style.display = 'block';
                        uploadIcon.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
        document.querySelector('.program-image-reset')?.addEventListener('click', function() {
            const uploadImage = document.getElementById('uploadImage');
            const uploadIcon = document.getElementById('uploadIcon');
            if (uploadImage && uploadIcon) {
                uploadImage.src = '';
                uploadImage.style.display = 'none';
                uploadIcon.style.display = 'block';
                document.querySelector('.program-image-input').value = '';
            }
        });

        function toggleFormGroup(radio) {
            const formGroup = document.querySelector('#delegation-form-group');
            if (formGroup) {
                formGroup.style.display = radio.checked ? 'block' : 'none';
            }
        }

        // Function to validate title and description fields
        function validateRequiredFields() {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const isValid = title !== '' && description !== '';
            
            // Get all tab buttons except program_info
            const tabButtons = document.querySelectorAll('[id^="pills-"][id$="-tab-edit"]:not([id="pills-program_info-tab-edit"])');
            
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
                        const programInfoTab = document.querySelector('#pills-program_info-tab-edit');
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
