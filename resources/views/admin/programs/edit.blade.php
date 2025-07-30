@extends('layouts.admin')

@section('style')
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
    ];

    $tabs = [
        ['id' => 'program_info', 'label' => $labels['program_info'], 'active' => true],
        ['id' => 'program_guideline', 'label' => $labels['program_guideline'], 'active' => false],
        ['id' => 'custom_pages', 'label' => $labels['custom_pages'], 'active' => false],
        ['id' => 'financial_policy', 'label' => $labels['financial_policy'], 'active' => false],
        ['id' => 'legal_policy', 'label' => $labels['legal_policy'], 'active' => false],
        ['id' => 'mentorship_request', 'label' => $labels['mentorship_request'], 'active' => false],
    ];

    $invalid_feedbacks = [
        'page_name' => $l == 'ar' ? 'يرجى إدخال اسم الصفحة.' : 'Please enter a page name.',
        'page_content' => $l == 'ar' ? 'يرجى إدخال محتوى الصفحة.' : 'Please enter page content.',
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
                <div id="submit_container" class="d-flex align-items-center justify-content-end gap-3 mt-4 p-4 p-md-0">
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
    <script src="/app-assets/vendors/js/extensions/dragula.min.js"></script>
    {{-- <script src="/app-assets/plugins/alertify/alertify.min.js"></script> --}}
    <script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>

@stop

@section('pagescripts')
    <script>
        $(document).on('change', '#is_public', function() {
            let _public = $(this).val();
            if( _public == 1 ) { $('#public_slug_div').show(); } else { $('#public_slug_div').hide(); }
        });
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
        var createPageEditor = new Quill('#custom-page-content', editorConfig);;
        var editPageEditor = new Quill('#edit-custom-page-content', editorConfig);

        $(document).on('click', '#create_custom_page_btn', function(e) {
            e.preventDefault();
            $('#custom-page-name').val('');
            $('#custom-page-visibility').val('public');
            // $('#custom-page-content').val('');
            $('#custom-page-content').summernote('code', '');
            $("#createPageModal").modal('show');
        })

        $(document).on('click', '#pills-tab-edit .nav-item', function(e) {
            if( $(this).find('.nav-link').attr('id') == 'pills-custom_pages-tab-edit' )
            {
                $("#submit_container").hide();
                $("#submit_container").removeClass('d-flex');
                $("#submit_container").addClass('d-none');
            } else {
                $("#submit_container").show();
                $("#submit_container").removeClass('d-none');
                $("#submit_container").addClass('d-flex');
            }
        });

        $(document).on('click', '#save_custom_page_btn', function(e) {
            e.preventDefault();

            if (!$('#custom-page-name').val()) {
                $('#custom-page-name').addClass('is-invalid').trigger('focus');
                $('#custom-page-name').after('<div class="invalid-feedback d-block">{{ $invalid_feedbacks['page_name'] }}</div>');
                return;
            }

            if (!$('#custom-page-content').val()) {
                $('#custom-page-content').closest('.form-group').append('<div class="invalid-feedback d-block">{{ $invalid_feedbacks['page_content'] }}</div>');
                return;
            }

            let program_id = '{{ $id }}';
            if( program_id )
            {
                let name = $('#custom-page-name').val();
                let content = $('#custom-page-content').val();
                let visibility = $('#custom-page-visibility').val();
                let data = {
                    cycleId: program_id,
                    title: name,
                    content: content,
                    visibility: visibility
                };
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "/dashboard/cycles/" + program_id + "/add_page",
                    data: data,
                    success: function(response) {
                        if( response.status )
                        {
                            Swal.fire({
                                icon: 'success',
                                title: '{{ $l == 'ar' ? 'تم إنشاء الصفحة بنجاح' : 'Page Created Successfully' }}',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: "{{ __('admin.OK') }}",
                                customClass: {
                                    confirmButton: 'btn btn-primary',
                                },
                                buttonsStyling: false,
                            });
                            $('#createPageModal').modal('hide');
                            $('#custom-page-name').val('');
                            $('#custom-page-content').val('');
                            $('#custom-page-visibility').val('public');
                            populate_pages_table(response.pages, '{{ $l }}');
                        }
                        else
                        {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                                text: response.message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: "{{ __('admin.OK') }}",
                                customClass: {
                                    confirmButton: 'btn btn-primary',
                                },
                                buttonsStyling: false,
                            });
                        }
                    },
                    error: function(error) {
                        console.log(error);
                        Swal.fire({
                            icon: 'error',
                            title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                            text: '{{ $l == 'ar' ? 'خطأ في إنشاء الصفحة. يرجى المحاولة مرة أخرى.' : 'Error creating page. Please try again.' }}',
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
            } else { 
                Swal.fire({
                    icon: 'warning',
                    title: '{{ $l == 'ar' ? 'تنبيه' : 'Warning' }}',
                    text: '{{ $l == 'ar' ? 'يرجى تحديد دورة أولاً' : 'Please select a cycle first' }}',
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

        $(document).on('click', '#update_custom_page_btn', function(e) {
            e.preventDefault();

            if (!$('#edit-custom-page-name').val()) {
                $('#edit-custom-page-name').addClass('is-invalid').trigger('focus');
                $('#edit-custom-page-name').after('<div class="invalid-feedback d-block">{{ $invalid_feedbacks['page_name'] }}</div>');
                return;
            }

            if (!$('#edit-custom-page-content').val()) {
                $('#edit-custom-page-content').closest('.form-group').append('<div class="invalid-feedback d-block">{{ $invalid_feedbacks['page_content'] }}</div>');
                return;
            }

            let program_id = '{{ $id }}';
            if( program_id )
            {
                let name = $('#edit-custom-page-name').val();
                let content = $('#edit-custom-page-content').val();
                let visibility = $('#edit-custom-page-visibility').val();
                let custom_page_id = $('#edit-custom-page-id').val();
                let data = {
                    cycleId: program_id,
                    page_id: custom_page_id,
                    title: name,
                    content: content,
                    visibility: visibility
                };
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "/dashboard/cycles/" + program_id + "/update_page",
                    data: data,
                    success: function(response) {
                        if( response.status )
                        {
                            Swal.fire({
                                icon: 'success',
                                title: '{{ $l == 'ar' ? 'تم تحديث الصفحة بنجاح' : 'Page Updated Successfully' }}',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: "{{ __('admin.OK') }}",
                                customClass: {
                                    confirmButton: 'btn btn-primary',
                                },
                                buttonsStyling: false,
                            });
                            $('#editPageModal').modal('hide');
                            $('#edit-custom-page-name').val('');
                            $('#edit-custom-page-content').val('');
                            $('#edit-custom-page-visibility').val('public');
                            populate_pages_table(response.pages, '{{ $l }}');
                        }
                        else
                        {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                                text: response.message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: "{{ __('admin.OK') }}",
                                customClass: {
                                    confirmButton: 'btn btn-primary',
                                },
                                buttonsStyling: false,
                            });
                        }
                    },
                    error: function(error) {
                        console.log(error);
                        Swal.fire({
                            icon: 'error',
                            title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                            text: '{{ $l == 'ar' ? 'خطأ في تحديث الصفحة. يرجى المحاولة مرة أخرى.' : 'Error updating page. Please try again.' }}',
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
            } else { 
                Swal.fire({
                    icon: 'warning',
                    title: '{{ $l == 'ar' ? 'تنبيه' : 'Warning' }}',
                    text: '{{ $l == 'ar' ? 'يرجى اختيار دورة أولاً' : 'Please select a cycle first' }}',
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

        // Hide errors on change
        $(document).on('input', '#custom-page-name', function() {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        });

        $(document).on('input', '#edit-custom-page-name', function() {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        });
        
        $(document).on('click', '.custom-page-edit', function(e) {
            e.preventDefault();
            let program_id = '{{ $id }}';
            if( program_id )
            {
                let custom_page_id = $(this).data('page-id');
                let data = {
                    page_id: custom_page_id,
                    cycleId: program_id
                };
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "/dashboard/cycles/" + program_id + "/show_page",
                    data: data,
                    success: function(response) {
                        if( response.status )
                        {
                            $('#edit-custom-page-id').val(response.page.id);
                            $('#edit-custom-page-name').val(response.page.title);
                            $('#edit-custom-page-content').summernote('code', response.page.content ?? '');
                            $('#edit-custom-page-visibility').val(response.page.visibility);
                            editPageEditor.root.innerHTML = response.page.content ?? '';
                            $("#editPageModal").modal('show');
                        }
                        else
                        {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                                text: response.message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: "{{ __('admin.OK') }}",
                                customClass: {
                                    confirmButton: 'btn btn-primary',
                                },
                                buttonsStyling: false,
                            });
                        }
                    },
                    error: function(error) {
                        console.log(error);
                         Swal.fire({
                            icon: 'error',
                            title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                            text: '{{ $l == 'ar' ? 'خطأ في جلب بيانات الصفحة. يرجى المحاولة مرة أخرى.' : 'Error fetching page data. Please try again.' }}',
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
            } else { 
                 Swal.fire({
                    icon: 'warning',
                    title: '{{ $l == 'ar' ? 'تنبيه' : 'Warning' }}',
                    text: '{{ $l == 'ar' ? 'يرجى اختيار دورة أولاً' : 'Please select a cycle first' }}',
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
        $(document).on('click', '.custom-page-toggle-status', function(e) {
            e.preventDefault();
            let program_id = '{{ $id }}';
            var custom_page_id = $(this).data('page-id');
            if( program_id )
            {
                Swal.fire({
                    title: '{{ $l == "ar" ? "تأكيد تغيير الحالة" : "Confirm Toggle Status" }}',
                    text: '{{ $l == "ar" ? "هل أنت متأكد أنك تريد تغيير حالة هذه الصفحة؟" : "Are you sure you want to toggle the status of this page?" }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ $l == "ar" ? "نعم، قم بالتغيير" : "Yes, toggle it!" }}',
                    cancelButtonText: '{{ $l == "ar" ? "إلغاء" : "Cancel" }}',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-label-secondary ms-2'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        let data = {
                            page_id: custom_page_id,
                            cycleId: program_id
                        };
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            type: "POST",
                            url: "/dashboard/cycles/" + program_id + "/toggle_status",
                            data: data,
                            success: function(response) {
                                if( response.status )
                                {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '{{ $l == 'ar' ? 'تم تحديث حالة الصفحة بنجاح' : 'Page Status Updated Successfully' }}',
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: "{{ __('admin.OK') }}",
                                        customClass: {
                                            confirmButton: 'btn btn-primary',
                                        },
                                        buttonsStyling: false,
                                    });
                                    populate_pages_table(response.pages, '{{ $l }}');
                                }
                                else
                                {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                                        text: response.message,
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: "{{ __('admin.OK') }}",
                                        customClass: {
                                            confirmButton: 'btn btn-primary',
                                        },
                                        buttonsStyling: false,
                                    });
                                }
                            },
                            error: function(error) {
                                console.log(error);
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                                    text: '{{ $l == 'ar' ? 'خطأ في تغيير الحالة. يرجى المحاولة مرة أخرى.' : 'Error toggling status. Please try again.' }}',
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
            } else { 
                 Swal.fire({
                    icon: 'warning',
                    title: '{{ $l == 'ar' ? 'تنبيه' : 'Warning' }}',
                    text: '{{ $l == 'ar' ? 'يرجى اختيار دورة أولاً' : 'Please select a cycle first' }}',
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
        $(document).on('change', '.custom-page-visibility-toggle', function(e) {
            let program_id = '{{ $id }}';
            var visibility = $(this).val();
            var custom_page_id = $(this).data('page-id');
            if( program_id )
            {
                 Swal.fire({
                    title: '{{ $l == "ar" ? "تأكيد تغيير الظهور" : "Confirm Toggle Visibility" }}',
                    text: '{{ $l == "ar" ? "هل أنت متأكد أنك تريد تغيير ظهور هذه الصفحة؟" : "Are you sure you want to toggle the visibility of this page?" }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ $l == "ar" ? "نعم، قم بالتغيير" : "Yes, toggle it!" }}',
                    cancelButtonText: '{{ $l == "ar" ? "إلغاء" : "Cancel" }}',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-label-secondary ms-2'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if(result.isConfirmed) {
                        let data = {
                            page_id: custom_page_id,
                            cycleId: program_id,
                            visibility: visibility
                        };
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            type: "POST",
                            url: "/dashboard/cycles/" + program_id + "/toggle_visibility",
                            data: data,
                            success: function(response) {
                                if( response.status )
                                {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '{{ $l == 'ar' ? 'تم تحديث ظهور الصفحة بنجاح' : 'Page Visibility Updated Successfully' }}',
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: "{{ __('admin.OK') }}",
                                        customClass: {
                                            confirmButton: 'btn btn-primary',
                                        },
                                        buttonsStyling: false,
                                    });
                                    populate_pages_table(response.pages, '{{ $l }}');
                                }
                                else
                                {
                                     Swal.fire({
                                        icon: 'error',
                                        title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                                        text: response.message,
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: "{{ __('admin.OK') }}",
                                        customClass: {
                                            confirmButton: 'btn btn-primary',
                                        },
                                        buttonsStyling: false,
                                    });
                                }
                            },
                            error: function(error) {
                                console.log(error);
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                                    text: '{{ $l == 'ar' ? 'خطأ في تغيير الظهور. يرجى المحاولة مرة أخرى.' : 'Error toggling visibility. Please try again.' }}',
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
            } else { 
                Swal.fire({
                    icon: 'warning',
                    title: '{{ $l == 'ar' ? 'تنبيه' : 'Warning' }}',
                    text: '{{ $l == 'ar' ? 'يرجى اختيار دورة أولاً' : 'Please select a cycle first' }}',
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
        $(document).on('click', '.custom-page-delete', function(e) {
            e.preventDefault();
            let program_id = '{{ $id }}';
            if( program_id )
            {
                let custom_page_id = $(this).data('page-id');
                Swal.fire({
                    title: '{{ $l == "ar" ? "تأكيد الحذف" : "Confirm Delete" }}',
                    text: '{{ $l == "ar" ? "هل أنت متأكد أنك تريد حذف هذه الصفحة؟" : "Are you sure you want to delete this page?" }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ $l == "ar" ? "نعم، قم بالحذف" : "Yes, delete it!" }}',
                    cancelButtonText: '{{ $l == "ar" ? "إلغاء" : "Cancel" }}',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-label-secondary ms-2'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        let send_data = {
                            page_id: custom_page_id,
                            cycleId: program_id
                        };
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            type: "POST",
                            url: "/dashboard/cycles/" + program_id + "/delete_page",
                            data: send_data,
                            success: function(response) {
                                if( response.status )
                                {
                                    Swal.fire({
                                       icon: 'success',
                                        title: "{{ __('admin.Deleted') }}!",
                                        html: "{{ __('admin.DeletedSuccessfully') }}.",
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: "{{ __('admin.OK') }}",
                                        customClass: {
                                            confirmButton: 'btn btn-primary',
                                        },
                                        buttonsStyling: false,
                                    });
                                    populate_pages_table(response.pages, '{{ $l }}');
                                }
                                else
                                {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                                        text: response.message,
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: "{{ __('admin.OK') }}",
                                        customClass: {
                                            confirmButton: 'btn btn-primary',
                                        },
                                        buttonsStyling: false,
                                    });
                                }
                            },
                            error: function(error) {
                                console.log(error);
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
                                    text: '{{ $l == 'ar' ? 'خطأ في حذف الصفحة. يرجى المحاولة مرة أخرى.' : 'Error deleting page. Please try again.' }}',
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
            } else { 
                Swal.fire({
                    icon: 'warning',
                    title: '{{ $l == 'ar' ? 'تنبيه' : 'Warning' }}',
                    text: '{{ $l == 'ar' ? 'يرجى تحديد دورة أولاً' : 'Please select a cycle first' }}',
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

        /**
         * Populates the custom pages table with data.
         * @param {Array} pages - An array of page objects.
         * @param {string} l - The current language ('ar' for Arabic, 'en' for English).
         */
        function populate_pages_table(pages, l) {
            // Define translations for easy access
            const translations = {
                ar: {
                    public: 'عام',
                    participants: 'المشاركين',
                    adminOnly: 'الإدارة فقط',
                    active: 'نشط',
                    inactive: 'غير نشط',
                    edit: 'تعديل',
                    toggleStatus: 'تغيير الحالة',
                    delete: 'حذف'
                },
                en: {
                    public: 'Public',
                    participants: 'Participants',
                    adminOnly: 'Admin Only',
                    active: 'Active',
                    inactive: 'Inactive',
                    edit: 'Edit',
                    toggleStatus: 'Toggle Status',
                    delete: 'Delete'
                }
            };

            // Determine the current language, default to 'en'
            const T = translations[l] || translations.en;

            let tableRows = '';
            if (pages.length === 0) {
                // Optional: Handle the case where there are no pages
                const colCount = 4; // Number of columns in your table
                tableRows = `<tr><td colspan="${colCount}" class="text-center">No pages found.</td></tr>`;
            } else {
                $.each(pages, function(index, page) {
                    const statusBadge = page.is_active == 1 ?
                        `<span class="badge bg-label-success me-1"><i class="ti ti-circle-filled me-1"></i> ${T.active}</span>` :
                        `<span class="badge bg-label-danger me-1"><i class="ti ti-circle-filled me-1"></i> ${T.inactive}</span>`;

                    tableRows += `
                        <tr data-index="${page.id}">
                            <td><strong>${page.title}</strong></td>
                            <td>
                                <select class="form-select form-select-sm custom-page-visibility-toggle" data-page-id="${page.id}">
                                    <option value="public" ${page.visibility == 'public' ? 'selected' : ''}>${T.public}</option>
                                    <option value="private" ${page.visibility == 'private' ? 'selected' : ''}>${T.participants}</option>
                                    <option value="admins" ${page.visibility == 'admins' ? 'selected' : ''}>${T.adminOnly}</option>
                                </select>
                            </td>
                            <td>
                                ${statusBadge}
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item custom-page-edit" href="javascript:void(0);"
                                            data-page-id="${page.id}" data-bs-toggle="modal" data-bs-target="#editPageModal">
                                            <i class="ti ti-pencil me-1"></i> ${T.edit}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item custom-page-toggle-status" href="javascript:void(0);"
                                            data-page-id="${page.id}">
                                            <i class="ti ti-toggle-left me-1"></i> ${T.toggleStatus}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item custom-page-delete" href="javascript:void(0);"
                                            data-page-id="${page.id}">
                                            <i class="ti ti-trash me-1"></i> ${T.delete}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }
            $("#custom_pages_list").html(tableRows);
        }

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
        handleEditorChange(createPageEditor, '#custom-page-content');
        handleEditorChange(editPageEditor, '#edit-custom-page-content');

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
                            console.log(error);
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
                
                // Show SweetAlert notification
                Swal.fire({
                    icon: 'warning',
                    title: '{{ $l == "ar" ? "معلومات غير مكتملة" : "Incomplete Information" }}',
                    text: '{{ $l == "ar" ? "يرجى ملء عنوان البرنامج ووصفه قبل الانتقال إلى علامات التبويب الأخرى." : "Please fill in the Program Title and Description before accessing other tabs." }}',
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: "{{ __('admin.OK') }}",
                    customClass: {
                        confirmButton: 'btn btn-primary',
                    },
                    buttonsStyling: false,
                });
                return false;
            }
        });
    </script>
@stop