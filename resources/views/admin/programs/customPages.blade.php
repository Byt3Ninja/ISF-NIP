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
        /* Hide stray Bootstrap-3 style close buttons. Summernote's own dialog
           dismiss button is re-shown by the .note-modal .close rule below, which
           wins on specificity. */
        button.close {
            display: none;
        }

        /* Summernote ships Bootstrap-3 flavoured dialog markup (.note-modal) for its
           Link/Picture/Video dialogs, which misbehaves over a Bootstrap 5 modal
           (Create/Edit Page). The editors pass dialogsInBody:true so these render in
           <body> rather than inheriting the page modal's stacking context; the rules
           below then fix the remaining Bootstrap-3 assumptions:
           1. .note-modal-footer is height:40px with a floated .note-btn, so the CTA
              escapes the dialog border.
           2. Summernote's z-index (dialog 1050, backdrop 1040) sits under this theme's
              modal (--bs-modal-zindex: 1090, backdrop 1089), so the dialog and its
              backdrop would render behind the page modal.
           3. The dismiss button needs to stay visible and clickable. */
        .note-modal {
            /* Above the theme's .modal (1090) and its in-modal tooltip/popover
               layers (1091/1092) so the dialog is on top. */
            z-index: 1094;
            overflow-y: auto;
        }

        .note-modal-backdrop {
            /* Above those same layers, but below .note-modal itself. */
            z-index: 1093;
        }

        .note-modal .note-modal-content {
            /* Keep the dialog clear of the fixed admin header. */
            margin-top: 5rem;
            margin-bottom: 2rem;
        }

        .note-modal .note-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        /* Summernote renders .close BEFORE the title and relies on float:right to
           push it over. The float is removed above, so use flex order instead to keep
           it on the trailing edge - which follows the text direction, so it lands on
           the right in English and the left in Arabic. */
        .note-modal .note-modal-header .note-modal-title {
            order: 1;
        }

        /* Restore the dismiss button: visible, aligned, and easy to hit. */
        .note-modal .note-modal-header .close {
            display: block;
            float: none;
            flex: 0 0 auto;
            order: 2;
            font-size: 1.5rem;
            line-height: 1;
            opacity: .6;
            padding: .25rem .5rem;
        }

        .note-modal .note-modal-header .close:hover,
        .note-modal .note-modal-header .close:focus {
            opacity: 1;
        }

        /* Un-float the CTA and let the footer grow to contain it. */
        .note-modal .note-modal-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            height: auto;
            padding: .75rem 1.25rem 1.25rem;
            text-align: initial;
        }

        .note-modal .note-modal-footer .note-btn {
            float: none;
            margin: 0;
        }

        .bg-first { background-color: #fff0e1 !important; }
        .bg-second { background-color: #ebebed !important; }
        .bg-third { background-color: #ddf6e8 !important; }
    </style>
@stop

@section('content')
<div class="">
    <div class="">
        <div class="row">
            <div class="col-10">
                <h4 class="fw-bold mb-4">
                    {{ __('admin.Custom Pages') }}
                </h4>
            </div>
            <div class="col-2 text-end">
                @if( $_user->ableTo('programlist.index', $permissions) )
                <a href="{{ route('programlist.index') }}" class="btn btn-sm btn-outline-danger px-2">
                    <i class="fas fa-reply me-2 mx-0"></i>
                    {{ __('cycles.Back to List') }}
                </a>
                @endif
            </div>
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
                <input type="hidden" name="tab_id" value="1">
                <input type="hidden" name="id" id="cycleid" value="{{ $id }}">
                <input type="hidden" name="typeedit" id="type" value="1">
                <input type="hidden" name="type" id="type" value="1">
                <input type="hidden" name="organization_id" value="{{ $ccycle->organization_id ?? 0 }}">

                
                @include('admin.programs.partials._edit_custom_pages')

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
@stop

@section('pagescripts')
    <script>
        // Set Language
        const lang = '{{ $l }}';

        $(document).ready(function() {
            var custom_pages_drag = dragula([document.getElementById('custom_pages_list')]);
            custom_pages_drag.on('drop', function(el) {
                let rows = $(el).parent().children();
                let sorting = [];
                rows.each(function() {
                    sorting.push($(this).attr('data-index'));
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
        });
    </script>
@stop
