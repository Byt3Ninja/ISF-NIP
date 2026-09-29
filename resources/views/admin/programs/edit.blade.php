@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/pickr/pickr-themes.css" />
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/dragula.min.css">
<link rel="stylesheet" type="text/css" href="/app-assets/css/plugins/extensions/drag-and-drop.css">
{{-- <link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify.min.css"> --}}
{{-- <link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify-bootstrap.min.css"> --}}
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/tagify/tagify.css" />
<!-- Summernote CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">

<style>
    button.close {
        display: none;
    }
    .bg-first { background-color: #fff0e1 !important; }
    .bg-second { background-color: #ebebed !important; }
    .bg-third { background-color: #ddf6e8 !important; }
</style>
@stop

@php
    $labels = [
        'program_info' => $l == 'ar' ? 'معلومات البرنامج' : 'Program Info',
        'program_guideline' => $l == 'ar' ? 'إرشادات البرنامج' : 'Program Guideline',
        'custom_pages' => $l == 'ar' ? 'صفحات مخصصة' : 'Custom Pages',
        'financial_policy' => $l == 'ar' ? 'السياسة المالية' : 'Financial Policy',
        'legal_policy' => $l == 'ar' ? 'السياسة القانونية' : 'Legal Policy',
        'mentorship_request' => $l == 'ar' ? 'طلب الإرشاد' : 'Mentorship Request',
        // 'winner_selection' => $l == 'ar' ? 'اختيار الفائزين' : 'Winner Selection',
        'Program_Settings' => $l == 'ar' ? 'إعدادات البرنامج' : 'Program Settings',
    ];

    $tabs = [
        ['id' => 'program_info', 'label' => $labels['program_info'], 'active' => true],
        ['id' => 'program_settings', 'label' => $labels['Program_Settings'], 'active' => false],
        // ['id' => 'program_guideline', 'label' => $labels['program_guideline'], 'active' => false],
        ['id' => 'custom_pages', 'label' => $labels['custom_pages'], 'active' => false],
        // ['id' => 'financial_policy', 'label' => $labels['financial_policy'], 'active' => false],
        // ['id' => 'legal_policy', 'label' => $labels['legal_policy'], 'active' => false],
        // ['id' => 'mentorship_request', 'label' => $labels['mentorship_request'], 'active' => false],
    ];

    $invalid_feedbacks = [
        'page_name' => $l == 'ar' ? 'يرجى إدخال اسم الصفحة.' : 'Please enter a page name.',
        'page_content' => $l == 'ar' ? 'يرجى إدخال محتوى الصفحة.' : 'Please enter page content.',
    ];
    // if( $ccycle->type == 'Hackathon' )
    // {
    //     $tabs[] = ['id' => 'winner_selection', 'label' => $labels['winner_selection'], 'active' => false];
    // }
@endphp

@section('content')
<div class="">
    <div class="">
        <div class="row">
            <h4 class="mb-4">
                {{ __('cycles.program_setup') }}
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
            <form id="modify-app-form" action="{{ route('cycles.modify') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tab_id" value="{{ \App\Enums\CycleTabAction::CREATE_CYCLE }}">
                <input type="hidden" name="id" id="cycleid" value="{{ $id }}">
                <input type="hidden" name="typeedit" id="type" value="{{ \App\Enums\CycleModifyAction::EDIT_CYCLE }}">
                <input type="hidden" name="type" id="type" value="{{ \App\Enums\CycleModifyAction::EDIT_CYCLE }}">
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
                                    {{-- {{ $tab['id'] != 'program_info' ? 'disabled' : '' }} --}}
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
                                {{-- {{ $tab['id'] != 'program_info' ? 'disabled' : '' }} --}}
                            >
                                <span class="fw-semibold">{{ $tab['label'] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
                <hr />
                <div class="tab-content shadow-none p-2 p-md-4" id="pills-tabContent-edit">
                    <!-- Program Guideline Tab -->
                    {{-- <div
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
                    </div> --}}

                    <!-- Financial Policy Tab -->
                    {{-- <div
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
                    </div> --}}

                    <!-- Legal Policy Tab -->
                    {{-- <div
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
                    </div> --}}

                    @foreach ($tabs as $key => $tab)
                        @if ($tab['id'] != 'program_guideline' && $tab['id'] != 'financial_policy' && $tab['id'] != 'legal_policy')
                            <div
                                class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}"
                                id="pills-{{ $tab['id'] }}-edit"
                                role="tabpanel"
                                aria-labelledby="pills-{{ $tab['id'] }}-tab-edit"
                            >
                                @include('admin.programs.partials._edit_' . $tab['id'], ['mode' => 'edit'])
                                <!-- Tab Navigation Buttons -->
                                <div class="d-flex align-items-center justify-content-between gap-3 mt-4 pt-4 border-top">
                                    <div>
                                    @if($key > 0)
                                        <button type="button" class="btn btn-label-secondary prev-tab-btn" data-current-tab="{{ $key }}">
                                        <i class="ti ti-arrow-{{$l == 'en' ? 'left' : 'right'}} me-2"></i>
                                        {{ __('cycles.Previous') }}
                                        </button>
                                    @else 
                                        <a href="{{ route('programlist.index') }}" type="button" class="btn btn-label-secondary">
                                            <i class="ti ti-arrow-{{$l == 'en' ? 'left' : 'right'}} me-2"></i>
                                            {{ __('cycles.Back') }}
                                        </a>
                                    @endif
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                    @if($key < count($tabs) - 1)
                                        <button type="button" class="btn btn-primary save-next-btn" data-current-tab="{{ $key }}">
                                        {{ __('cycles.Save & Next') }}
                                        <i class="ti ti-arrow-{{$l == 'en' ? 'right' : 'left'}} ms-2"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-success publish-btn" data-current-tab="{{ $key }}">
                                        {{ __('admin.Publish') }}
                                        <i class="ti ti-circle-check ms-2"></i>
                                        </button>
                                    @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                 <!-- Hidden submit button for programmatic form submission -->
                <button type="submit" id="hidden-submit-btn" style="display: none;"></button>
            </form>
        </div>
    </div>
</div>
@stop

@section('scripts')
    <script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
    <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
    <script src="/app-assets/vendors/js/extensions/dragula.min.js"></script>
    {{-- <script src="/app-assets/plugins/alertify/alertify.min.js"></script> --}}
    <script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
@stop

@section('pagescripts')
    <script>
        // Set Language
        const lang = '{{ $l }}';

        // Initialize Quill Editors
        // const editorConfig = {
        //     theme: 'snow'
        // };
        // const editGuidelineEditor = new Quill('#guideline-editor', editorConfig);
        // const editFinancialEditor = new Quill('#financial-editor', editorConfig);
        // const editLegalEditor = new Quill('#legal-editor', editorConfig);
        // var createPageEditor = new Quill('#custom-page-content', editorConfig);;
        // var editPageEditor = new Quill('#edit-custom-page-content', editorConfig);

        // // Set initial editor content
        // editGuidelineEditor.root.innerHTML = @json($ccycle->program_guideline ?? '');
        // editFinancialEditor.root.innerHTML = @json($ccycle->financial_guideline ?? '');
        // editLegalEditor.root.innerHTML = @json($ccycle->legal_guideline ?? '');

        // // Handle Editor Change
        // const handleEditorChange = (editor, input) => {
        //     editor.on('text-change', function() {
        //         $(input).val(editor.root.innerHTML);
        //     });
        // };

        // handleEditorChange(editGuidelineEditor, '#program_guideline');
        // handleEditorChange(editFinancialEditor, '#financial_policy');
        // handleEditorChange(editLegalEditor, '#legal_policy');
        // handleEditorChange(createPageEditor, '#custom-page-content');
        // handleEditorChange(editPageEditor, '#edit-custom-page-content');

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
            $('#is_public').trigger('change');
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

            var custom_pages_drag = dragula([document.getElementById('custom_pages_list')]);
            custom_pages_drag.on('drop', function(el) {
                let rows = $(el).parent().children();
                let sorting = [];
                rows.each(function() {
                    sorting.push($(this).attr('data-index'));
                });
                
                let program_id = '{{ $id }}';
                if( program_id )
                {
                    let data = {
                        cycleId: program_id,
                        sorting: sorting,
                    };
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: "POST",
                        url: "/dashboard/cycles/" + program_id + "/pages_resort",
                        data: data,
                        success: function(response) {
                            if( response.status )
                            {
                                populate_pages_table(response.pages, '{{ $l }}');
                            }
                            else
                            {
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                                    text: response.message
                                });
                            }
                        },
                        error: function(error) {
                            // console.log(error);
                            Swal.fire({
                                icon: 'error',
                                title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                                text: '{{ $l == 'ar' ? 'خطأ في إعادة ترتيب الصفحات. يرجى المحاولة مرة أخرى.' : 'Error resorting pages. Please try again.' }}',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: "{{ __('admin.OK') }}",
                                customClass: {
                                    confirmButton: 'btn btn-primary',
                                },
                                buttonsStyling: false,
                            });
                        }
                    });
                }
            });

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
                        // console.log(response);
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
                        $('#title_ar').val(response.title_ar ?? '');
                        $('#description').val(response.description ?? '');
                        $('#description_ar').val(response.description_ar ?? '');
                        $('#cycleid').val(cycleId);
                        $('#type').val(1);
                        $('#organization').val(response.organization_id ?? '').trigger('change');
                        $('#program_step_type').val(response.step_type ?? '').trigger('change');
                        $('#allow_non_company').prop('checked', response.allow_non_company == 1);
                        $('#reminder_enabled').prop('checked', response.is_active_Rminder == 1);
                        if( response.is_active_Rminder == 1){
                            $('#hours_reminder').show();
                        }
                        $('#auto-approve-mentorship-requests').prop('checked', response.auto_approve_mentorship_requests == 1);

                        if (response.private) {
                            $('#private').prop('checked', true);
                            $('#cyclelink').html('{{ __('cycles.Join URL') }}');
                            $('#cyclelink').attr('href', '/dashboard/cycles/view/' + response.id + '?id=' + response.private);
                        }

                        // if (editGuidelineEditor) {
                        //     editGuidelineEditor.root.innerHTML = response.program_guideline ?? '';
                        // }
                        // $('#program_guideline').val(response.program_guideline ?? '');

                        // if (editFinancialEditor) {
                        //     editFinancialEditor.root.innerHTML = response.financial_guideline ?? '';
                        // }
                        // $('#financial_policy').val(response.financial_guideline ?? '');
                        // $('#budget_allocation').val(response.budget_allocation ?? '');
                        // $('#currency_type').val(response.currency_type ?? '');

                        // if (editLegalEditor) {
                        //     editLegalEditor.root.innerHTML = response.legal_guideline ?? '';
                        // }
                        // $('#legal_policy').val(response.legal_guideline ?? '');
                        
                        // Validate fields after loading data
                        // setTimeout(validateRequiredFields, 100);
                    },
                    error: function(error) {
                        console.log(error);
                        Swal.fire({
                            icon: 'error',
                            title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                            text: '{{ $l == 'ar' ? 'خطأ في جلب بيانات الدورة. يرجى المحاولة مرة أخرى.' : 'Error fetching cycle data. Please try again.' }}',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: "{{ __('admin.OK') }}",
                            customClass: {
                                confirmButton: 'btn btn-primary',
                            },
                            buttonsStyling: false,
                        });
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
    </script>

    <script>
        $(document).ready(function() {
            // Tab navigation state
            let currentTabIndex = 0;
            const totalTabs = {{ count($tabs) }};
            const tabIds = @json(array_column($tabs, 'id'));
            
            // Validation functions for each tab
            const tabValidations = {
                'program_info': function() {
                let isValid = true;
                
                // Clear all previous errors
                $('.form-control, .form-select').removeClass('is-invalid');
                $('.select2-container').removeClass('is-invalid');
                $('.invalid-feedback').text('').removeClass('d-block');

                // Validate organization (if visible and not disabled)
                const orgSelect = $('#organization');
                if (orgSelect.length && orgSelect.is(':visible') && !orgSelect.is(':disabled') && !orgSelect.val()) {
                    orgSelect.addClass('is-invalid');
                    const select2Container = orgSelect.next('.select2-container');
                    if (select2Container.length) {
                    select2Container.addClass('is-invalid');
                    }
                    orgSelect.closest('.form-group').find('.invalid-feedback').text('{{ $l == "en" ? "Please select an organization" : "يرجى اختيار المنظمة" }}').addClass('d-block');
                    isValid = false;
                }

                // Validate program type
                const programType = $('#program_type');
                if (!programType.val() && !programType.is(':disabled')) {
                    programType.addClass('is-invalid');
                    const select2Container = programType.next('.select2-container');
                    if (select2Container.length) {
                    select2Container.addClass('is-invalid');
                    }
                    programType.closest('.form-group').find('.invalid-feedback').text('{{ $l == "en" ? "Please select a program type" : "يرجى اختيار نوع البرنامج" }}').addClass('d-block');
                    isValid = false;
                }

                // Validate program steps type
                const programStepsType = $('#program_step_type');
                if (!programStepsType.val() && !programStepsType.is(':disabled')) {
                    programStepsType.addClass('is-invalid');
                    const select2Container = programStepsType.next('.select2-container');
                    if (select2Container.length) {
                        select2Container.addClass('is-invalid');
                    }
                    programStepsType.closest('.form-group').find('.invalid-feedback').text('{{ __("cycles.Select Program Steps Type") }}').addClass('d-block');
                    isValid = false;
                }

                // Validate title
                const title = $('#title');
                if (!title.val().trim()) {
                    title.addClass('is-invalid');
                    title.next('.invalid-feedback').text('{{ $l == "en" ? "Program title is required" : "عنوان البرنامج مطلوب" }}');
                    isValid = false;
                } else if (title.val().trim().length < 3) {
                    title.addClass('is-invalid');
                    title.next('.invalid-feedback').text('{{ $l == "en" ? "Title must be at least 3 characters" : "العنوان يجب أن يكون 3 أحرف على الأقل" }}');
                    isValid = false;
                }

                // Validate title_ar
                const title_ar = $('#title_ar');
                if (!title_ar.val().trim()) {
                    title_ar.addClass('is-invalid');
                    title_ar.next('.invalid-feedback').text('{{ $l == "en" ? "Program Title in Arabic is required." : "عنوان البرنامج باللغة العربية مطلوب." }}');
                    isValid = false;
                } else if (title_ar.val().length > 50) {
                    title_ar.addClass('is-invalid');
                    title_ar.next('.invalid-feedback').text('{{ $l == "en" ? "Title in Arabic cannot exceed 50 characters." : "العنوان باللغة العربية لا يمكن أن يتجاوز 50 حرف." }}');
                    isValid = false;
                }

                // Validate date range
                const dateRange = $('#date-range-picker');
                if (!dateRange.val()) {
                    dateRange.addClass('is-invalid');
                    dateRange.next('.invalid-feedback').text('{{ $l == "en" ? "Please select start and end dates" : "يرجى اختيار تاريخ البداية والنهاية" }}');
                    isValid = false;
                }

                // Validate description
                const description = $('#description');
                if (!description.val().trim()) {
                    description.addClass('is-invalid');
                    description.next('.invalid-feedback').text('{{ $l == "en" ? "Description is required" : "الوصف مطلوب" }}');
                    isValid = false;
                } else if (description.val().trim().length < 10) {
                    description.addClass('is-invalid');
                    description.next('.invalid-feedback').text('{{ $l == "en" ? "Description must be at least 10 characters" : "الوصف يجب أن يكون 10 أحرف على الأقل" }}');
                    isValid = false;
                }

                // Validate description_ar
                const description_ar = $('#description_ar');
                if (!description_ar.val().trim()) {
                    description_ar.addClass('is-invalid');
                    description_ar.next('.invalid-feedback').text('{{ $l == "en" ? "Description in Arabic is required." : "وصف البرنامج باللغة العربية مطلوب." }}');
                    isValid = false;
                } else if (description_ar.val().length > 250) {
                    description_ar.addClass('is-invalid');
                    description_ar.next('.invalid-feedback').text('{{ $l == "en" ? "Description in Arabic cannot exceed 250 characters." : "الوصف باللغة العربية لا يمكن أن يتجاوز 250 حرف." }}');
                    isValid = false;
                }

                return isValid;
                },
                
                'Program_Settings': function() {
                let isValid = true;
                
                // Clear previous errors
                $('.form-control', '#pills-Program_Settings-edit').removeClass('is-invalid');
                $('.invalid-feedback', '#pills-Program_Settings-edit').text('');
                $('.start-color-error, .end-color-error, .main-color-error').text('').hide();
                
                // Validate public URL when visibility is public
                const isPublic = $('#is_public').val();
                const publicSlug = $('#public_slug').val().trim();
                
                if (isPublic == '1') {
                    if (!publicSlug) {
                    $('#public_slug').addClass('is-invalid');
                    $('#public_slug').siblings('.invalid-feedback').text('{{ $l == "en" ? "Public URL is required when program is set to public" : "الرابط العام مطلوب عندما يكون البرنامج عاماً" }}');
                    isValid = false;
                    } else if (publicSlug.length < 3) {
                    $('#public_slug').addClass('is-invalid');
                    $('#public_slug').siblings('.invalid-feedback').text('{{ $l == "en" ? "Public URL must be at least 3 characters" : "الرابط العام يجب أن يكون 3 أحرف على الأقل" }}');
                    isValid = false;
                    }
                }
                
                // Validate hours reminder when reminder is enabled
                const reminderEnabled = $('#reminder_enabled').is(':checked');
                const hoursReminder = $('#hours_reminder_input').val();
                
                if (reminderEnabled && (!hoursReminder || parseInt(hoursReminder) < 1)) {
                    $('#hours_reminder_input').addClass('is-invalid');
                    $('#hours_reminder_input').siblings('.invalid-feedback').text('{{ $l == "en" ? "Hours reminder must be at least 1 hour" : "تذكير الساعات يجب أن يكون ساعة واحدة على الأقل" }}');
                    isValid = false;
                }

                // Validate branding colors
                const startColor = $('#start_color_input').val();
                const endColor = $('#end_color_input').val();
                const mainColor = $('#main_color_input').val();
                const colorRegex = /^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/;

                // Start Color
                if (!startColor || !colorRegex.test(startColor.replace(/^#+/, '#'))) {
                    $('.start-color-error').text('{{ $l == "en" ? "Please enter a valid start color." : "يرجى إدخال لون صالح." }}').show();
                    isValid = false;
                }

                // End Color
                if (!endColor || !colorRegex.test(endColor.replace(/^#+/, '#'))) {
                    $('.end-color-error').text('{{ $l == "en" ? "Please enter a valid end color." : "يرجى إدخال لون صالح." }}').show();
                    isValid = false;
                }

                // Main Color
                if (!mainColor || !colorRegex.test(mainColor.replace(/^#+/, '#'))) {
                    $('.main-color-error').text('{{ $l == "en" ? "Please enter a valid button color." : "يرجى إدخال لون صالح." }}').show();
                    isValid = false;
                }
                
                return isValid;
                },
                
                'custom_pages': function() {
                // Custom pages validation - this tab is optional
                return true;
                }
            };

            // Function to validate current tab
            function validateCurrentTab(tabId) {
                if (tabValidations[tabId]) {
                return tabValidations[tabId]();
                }
                return true;
            }

            // Function to enable/disable tabs
            function updateTabStates() {
                tabIds.forEach((tabId, index) => {
                const tabButton = $(`#pills-${tabId}-tab, #pills-${tabId}-tab-edit`);
                if (index <= currentTabIndex) {
                    tabButton.removeClass('tab-disabled').prop('disabled', false);
                } else {
                    tabButton.addClass('tab-disabled').prop('disabled', true);
                }
                });
            }

            // Function to navigate to tab
            function navigateToTab(targetIndex, validateCurrent = true) {
                const currentTabId = tabIds[currentTabIndex];
                
                // Validate current tab before moving (if required)
                if (validateCurrent && !validateCurrentTab(currentTabId)) {
                // Scroll to first error
                const firstError = $('.is-invalid').first();
                if (firstError.length) {
                    $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                    }, 500);
                }
                
                // Show error message
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ $l == "en" ? "Please fix the errors in this tab before proceeding" : "يرجى إصلاح الأخطاء في هذه الصفحة قبل المتابعة" }}');
                }
                return false;
                }

                // Check if trying to skip ahead to publish tab without completing all previous tabs
                if (targetIndex === totalTabs - 1 && targetIndex > currentTabIndex + 1) {
                // Find first incomplete tab
                let firstIncompleteTab = -1;
                for (let i = 0; i < totalTabs - 1; i++) {
                    if (!validateCurrentTab(tabIds[i])) {
                    firstIncompleteTab = i;
                    break;
                    }
                }
                
                if (firstIncompleteTab >= 0) {
                    // Navigate to first incomplete tab
                    const targetTabId = tabIds[firstIncompleteTab];
                    
                    // Remove active class from all tabs and content
                    $('.nav-link[data-bs-toggle="pill"]').removeClass('active').attr('aria-selected', 'false');
                    $('.tab-pane').removeClass('show active');
                    
                    // Add active class to target tab and content
                    $(`#pills-${targetTabId}-tab, #pills-${targetTabId}-tab-edit`).addClass('active').attr('aria-selected', 'true');
                    $(`#pills-${targetTabId}-edit`).addClass('show active');
                    
                    currentTabIndex = firstIncompleteTab;
                    $('#current_tab_id').val(firstIncompleteTab + 1);
                    updateTabStates();
                    
                    if (typeof toastr !== 'undefined') {
                    toastr.warning('{{ $l == "en" ? "Please complete all required fields in previous tabs before publishing" : "يرجى إكمال جميع الحقول المطلوبة في الصفحات السابقة قبل النشر" }}');
                    }
                    
                    return false;
                }
                }

                // Navigate to target tab
                const targetTabId = tabIds[targetIndex];
                
                // Remove active class from all tabs and content
                $('.nav-link[data-bs-toggle="pill"]').removeClass('active').attr('aria-selected', 'false');
                $('.tab-pane').removeClass('show active');
                
                // Add active class to target tab and content (both desktop and mobile versions)
                $(`#pills-${targetTabId}-tab, #pills-${targetTabId}-tab-edit`).addClass('active').attr('aria-selected', 'true');
                $(`#pills-${targetTabId}-edit`).addClass('show active');
                
                currentTabIndex = targetIndex;
                $('#current_tab_id').val(targetIndex + 1);
                updateTabStates();
                
                return true;
            }

            // Previous button click handler
            $(document).on('click', '.prev-tab-btn', function() {
                const currentTab = parseInt($(this).data('current-tab'));
                if (currentTab > 0) {
                navigateToTab(currentTab - 1, false); // Don't validate when going back
                }
            });

            // Save & Next button click handler
            $(document).on('click', '.save-next-btn', function() {
                const currentTab = parseInt($(this).data('current-tab'));
                const currentTabId = tabIds[currentTab];
                
                if (validateCurrentTab(currentTabId)) {
                // Save current progress (you can implement auto-save here if needed)
                navigateToTab(currentTab + 1, false);
                }
            });

            // Publish button click handler
            $(document).on('click', '.publish-btn', function() {
                const currentTab = parseInt($(this).data('current-tab'));
                
                // Validate all tabs before publishing
                let allTabsValid = true;
                let firstInvalidTab = -1;
                
                for (let i = 0; i < totalTabs; i++) {
                // Temporarily switch to each tab to validate
                const tabId = tabIds[i];
                const tempTabIndex = currentTabIndex;
                currentTabIndex = i;
                
                if (!validateCurrentTab(tabId)) {
                    if (firstInvalidTab === -1) {
                    firstInvalidTab = i;
                    }
                    allTabsValid = false;
                }
                
                currentTabIndex = tempTabIndex;
                }
                
                if (!allTabsValid && firstInvalidTab >= 0) {
                // Navigate to first invalid tab
                navigateToTab(firstInvalidTab, false);
                
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ $l == "en" ? "Please complete all required fields before publishing" : "يرجى إكمال جميع الحقول المطلوبة قبل النشر" }}');
                }
                return;
                }
                
                // All validations passed, set action and submit the form
                $('#action_type').val('create'); // Use 'create' instead of 'publish' for new programs

                // Make sure cycleid is set to 0 for new programs
                $('#cycleid').val('{{ isset($ccycle) ? $ccycle->id : 0 }}');
                
                // Submit the form
                $('#modify-app-form').submit();
                // $('#hidden-submit-btn').click();
            });

            // Tab click handler - prevent direct navigation to disabled tabs
            $(document).on('click', '[data-bs-toggle="pill"]', function(e) {
                const targetIndex = parseInt($(this).data('tab-index'));
                
                if ($(this).hasClass('tab-disabled')) {
                e.preventDefault();
                e.stopPropagation();
                
                if (typeof toastr !== 'undefined') {
                    toastr.warning('{{ $l == "en" ? "Please complete the previous tabs first" : "يرجى إكمال الصفحات السابقة أولاً" }}');
                }
                return false;
                }
                
                // Update current tab index when manually switching tabs
                if (targetIndex !== undefined && targetIndex <= currentTabIndex + 1) {
                // Allow the natural Bootstrap tab switching to happen
                // But update our internal state
                setTimeout(function() {
                    currentTabIndex = targetIndex;
                    $('#current_tab_id').val(targetIndex + 1);
                    updateTabStates();
                }, 50);
                }
            });

            // Form submit handler
            $('#programForm').on('submit', function(e) {
                const actionType = $('#action_type').val();
                
                // For new program creation, always use 'save' or 'create' action type
                if (actionType === 'publish' || actionType === 'create') {
                // Final validation before submit
                let allTabsValid = true;
                
                for (let i = 0; i < totalTabs; i++) {
                    const tabId = tabIds[i];
                    const tempTabIndex = currentTabIndex;
                    currentTabIndex = i;
                    
                    if (!validateCurrentTab(tabId)) {
                    allTabsValid = false;
                    navigateToTab(i, false);
                    break;
                    }
                    
                    currentTabIndex = tempTabIndex;
                }
                
                if (!allTabsValid) {
                    e.preventDefault();
                    if (typeof toastr !== 'undefined') {
                    toastr.error('{{ $l == "en" ? "Please fix all validation errors before publishing" : "يرجى إصلاح جميع أخطاء التحقق قبل النشر" }}');
                    }
                    return false;
                }
                
                // Ensure cycleid is set correctly for new programs
                $('#cycleid').val('{{ isset($ccycle) ? $ccycle->id : 0 }}');
                }
            });

            $(document).on('change', '#program_step_type', function(e) { initStepTypeHelpText(); });

            function initStepTypeHelpText()
            {
                var selectedType = $('#program_step_type').val();
                var helpText = '';
                if( selectedType == 'cycle' ) { helpText = "{{ __('cycles.type_by_cycle') }} : {{ __('cycles.type_by_cycle_helper') }}"; }
                else if( selectedType == 'time' ) { helpText = "{{ __('cycles.type_by_time') }} : {{ __('cycles.type_by_time_helper') }}"; }

                $('#program_step_type_help').html(helpText);
            }

            // Initialize tab states
            updateTabStates();
            
            // Set initial tab
            currentTabIndex = 0;
            $('#current_tab_id').val(1);
            initStepTypeHelpText();

            // Deep link from the landing page "Program not public" warning:
            // open the Program Settings tab and scroll to the Program
            // Visibility section.
            if (window.location.hash === '#program-settings-visibility') {
                const settingsTabIndex = tabIds.indexOf('program_settings');
                if (settingsTabIndex > -1) {
                    navigateToTab(settingsTabIndex, false);
                    setTimeout(function () {
                        const $section = $('#program-settings-visibility');
                        if ($section.length) {
                            $('html, body').animate({ scrollTop: $section.offset().top - 120 }, 500);
                        }
                    }, 350);
                }
            }
        });
    </script>
@stop