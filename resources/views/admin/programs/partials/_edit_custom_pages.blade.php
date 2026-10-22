<div class="card border shadow-sm p-0">
 	<div class="card-header d-flex justify-content-between align-items-center">
 		<h5 class="mb-0 fw-bold card-title text-dark">
			{{  $l == 'ar' ? 'الصفحات المخصصة' : 'Custom Pages' }}
		</h5>
 		<button id="create_custom_page_btn" class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#createPageModal">
 			<i class="ti ti-plus me-1"></i> {{ $l == 'ar' ? 'إضافة صفحة جديدة' : 'Add New Page' }}
 		</button>
 	</div>
 	<div class="card-body">
		@if ($ccycle && $ccycle->custom_pages)
 		<div class="table-responsive text-nowrap rounded">
 			<table class="table rounded">
 				<thead class="table-light">
 					<tr>
 						<th>{{ $l == 'ar' ? 'اسم الصفحة' : 'Page Name' }}</th>
 						<th style="width: 15%">{{ $l == 'ar' ? 'الظهور' : 'Visibility' }}</th>
 						<th style="width: 10%">{{ $l == 'ar' ? 'الحالة' : 'Status' }}</th>
 						<th style="width: 10%">{{ $l == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
 					</tr>
 				</thead>
 				<tbody id="custom_pages_list">
 					@foreach( $ccycle->custom_pages->sortBy('row_no')->values() as $custom_page )
 					<tr data-index="{{ $custom_page->id }}">
 						<td><strong>{{ $custom_page->title }}</strong></td>
 						<td>
 							<select class="form-select form-select-sm custom-page-visibility-toggle"
 								data-page-id="{{ $custom_page->id }}">
 								<option value="public" {{ $custom_page->visibility == 'public' ? 'selected' : '' }}>
 									{{ $l == 'ar' ? 'عام' : 'Public' }}</option>
 								<option value="private" {{ $custom_page->visibility == 'private' ? 'selected' : '' }}>
 									{{ $l == 'ar' ? 'المشاركين' : 'Participants' }}
 								</option>
 								<option value="admins" {{ $custom_page->visibility == 'admins' ? 'selected' : '' }}>
 									{{ $l == 'ar' ? 'الإدارة فقط' : 'Admin Only' }}</option>
 							</select>
 						</td>
 						<td>
 							@if( $custom_page->is_active == 1 )
 							<span class="badge bg-label-success me-1">
 								<i class="ti ti-circle-filled me-1"></i> {{ $l == 'ar' ? 'نشط' : 'Active' }}
 							</span>
 							@else
 							<span class="badge bg-label-danger me-1">
 								<i class="ti ti-circle-filled me-1"></i> {{ $l == 'ar' ? 'غير نشط' : 'Inactive' }}
 							</span>
 							@endif
 						</td>
 						<td>
 							<div class="dropdown">
 								<button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
 									<i class="ti ti-dots-vertical"></i>
 								</button>
 								<ul class="dropdown-menu dropdown-menu-end">
 									<li>
 										<a class="dropdown-item custom-page-edit" href="javascript:void(0);"
 											data-page-id="{{ $custom_page->id }}" data-bs-toggle="modal" data-bs-target="#editPageModal">
 											<i class="ti ti-pencil me-1"></i> {{ $l == 'ar' ? 'تعديل' : 'Edit' }}
 										</a>
 									</li>
 									<li>
 										<a class="dropdown-item custom-page-toggle-status" href="javascript:void(0);"
 											data-page-id="{{ $custom_page->id }}">
 											<i class="ti ti-toggle-left me-1"></i> {{ $l == 'ar' ? 'تغيير الحالة' : 'Toggle Status' }}
 										</a>
 									</li>
 									<li>
 										<a class="dropdown-item custom-page-delete" href="javascript:void(0);"
 											data-page-id="{{ $custom_page->id }}">
 											<i class="ti ti-trash me-1"></i> {{ $l == 'ar' ? 'حذف' : 'Delete' }}
 										</a>
 									</li>
 								</ul>
 							</div>
 						</td>
 					</tr>
 					@endforeach
 				</tbody>
 			</table>
 		</div>
		@else
			<p class="text-center">{{ $l == 'ar' ? 'لا توجد صفحات مخصصة حتى الآن.' : 'No custom pages yet.' }}</p>
		@endif
 	</div>
 </div>

<!-- Create Modal -->
 <div class="modal fade" id="createPageModal" tabindex="-1" aria-labelledby="createPageModalLabel" aria-hidden="true">
 	<div class="modal-dialog modal-dialog-centered modal-xl">
 		<div class="modal-content">
 			<div class="modal-header">
 				<h5 class="modal-title" id="createPageModalLabel">{{ $l == 'ar' ? 'إنشاء صفحة جديدة' : 'Create New Page' }}</h5>
 				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
 			</div>
 			<div class="modal-body">
 				<div class="form-group mb-3">
 					<label for="custom-page-name" class="form-label">{{ $l == 'ar' ? 'اسم الصفحة' : 'Page Name' }}</label>
 					<input type="text" class="form-control" id="custom-page-name" name="custom-page-name"
 						placeholder="{{ $l == 'ar' ? 'أدخل اسم الصفحة' : 'Enter page name' }}">
 				</div>
 				<div class="form-group mb-3">
 					<label for="custom-page-visibility" class="form-label">{{ $l == 'ar' ? 'الظهور' : 'Visibility' }}</label>
 					<select class="form-select" id="custom-page-visibility" name="custom-page-visibility">
 						<option value="public">{{ $l == 'ar' ? 'عام' : 'Public' }}</option>
 						<option value="private">{{ $l == 'ar' ? 'المشاركين' : 'Participants' }}</option>
 						<option value="admins">{{ $l == 'ar' ? 'الإدارة فقط' : 'Admin Only' }}</option>
 					</select>
 				</div>
 				<div class="form-group">
 					<label for="custom-page-content" class="form-label">{{ $l == 'ar' ? 'الوصف' : 'Description' }}</label>
 					<textarea class="form-control" id="custom-page-content" name="custom-page-content"
 						rows="5"></textarea>
 				</div>
 			</div>
 			<div class="modal-footer mt-8">
 				<button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ $l == 'ar' ? 'إغلاق' : 'Close' }}</button>
 				<button id="save_custom_page_btn" type="button" class="btn btn-primary">{{ $l == 'ar' ? 'حفظ الصفحة' : 'Save Page' }}</button>
 			</div>
 		</div>
 	</div>
 </div>

<!-- Edit Modal -->
 <div class="modal fade" id="editPageModal" tabindex="-1" aria-labelledby="editPageModalLabel" aria-hidden="true">
 	<div class="modal-dialog modal-dialog-centered modal-xl">
 		<div class="modal-content">
 			<div class="modal-header">
 				<h5 class="modal-title" id="editPageModalLabel">{{ $l == 'ar' ? 'تعديل الصفحة' : 'Edit Page' }}</h5>
 				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
 			</div>
 			<div class="modal-body">
 				<input type="hidden" name="edit-custom-page-id" id="edit-custom-page-id" value="" />
 				<div class="form-group mb-3">
 					<label for="edit-custom-page-name" class="form-label">{{ $l == 'ar' ? 'اسم الصفحة' : 'Page Name' }}</label>
 					<input type="text" class="form-control" id="edit-custom-page-name" name="edit-custom-page-name"
 						placeholder="{{ $l == 'ar' ? 'أدخل اسم الصفحة' : 'Enter page name' }}">
 				</div>
 				<div class="form-group mb-3">
 					<label for="edit-custom-page-visibility" class="form-label">{{ $l == 'ar' ? 'الظهور' : 'Visibility' }}</label>
 					<select class="form-select" id="edit-custom-page-visibility" name="edit-custom-page-visibility">
 						<option value="public">{{ $l == 'ar' ? 'عام' : 'Public' }}</option>
 						<option value="private">{{ $l == 'ar' ? 'المشاركين' : 'Participants' }}</option>
 						<option value="admins">{{ $l == 'ar' ? 'الإدارة فقط' : 'Admin Only' }}</option>
 					</select>
 				</div>
 				<div class="form-group">
 					<label for="edit-custom-page-content" class="form-label">{{ $l == 'ar' ? 'الوصف' : 'Description' }}</label>
 					<textarea class="form-control" id="edit-custom-page-content" name="edit-custom-page-content"
 						rows="5"></textarea>
 				</div>
 			</div>
 			<div class="modal-footer mt-8">
 				<button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ $l == 'ar' ? 'إغلاق' : 'Close' }}</button>
 				<button id="update_custom_page_btn" type="button" class="btn btn-primary">{{ $l == 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}</button>
 			</div>
 		</div>
 	</div>
 </div>


@push('scripts')
    {{--
        These rules live with the partial rather than in a host page's @section('style')
        so they reach every page that includes it - the standalone Custom Pages screen
        and the Program Setup wizard's Custom Pages tab both render this editor. The
        layout only exposes a 'scripts' stack, so the <style> rides along here; it lands
        late in <body> and still wins the cascade over Summernote's CDN stylesheet.
    --}}
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
           2. The dismiss button needs to stay visible and clickable.
           Stacking above the page modal and focus inside the dialog are handled
           globally (admin.includes.head + /js/editor-dialogs-in-modal.js). */
        .note-modal {
            overflow-y: auto;
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
    </style>

    <script>
        $(document).ready(function() {
            $('.custom-table').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                language: {
                @if($l == 'ar')
                "search": "{{ __('admin.Search') }}:",
                "lengthMenu": "{{ __('admin.Show') }} _MENU_ {{ __('admin.Entries') }}",
                "info": "{{ __('admin.showing') }} _START_ {{ __('admin.To') }} _END_ {{ __('admin.Of') }} _TOTAL_ {{ __('admin.Entries') }}",
                "infoEmpty": "{{ __('admin.no_entries_available') }}",
                "infoFiltered": "({{ __('admin.filtered from') }} _MAX_ {{ __('admin.total entries') }})",
                "paginate": {
                    "first": "{{ __('admin.First') }}",
                    "last": "{{ __('admin.Last') }}",
                    "next": "{{ __('admin.Next') }}",
                    "previous": "{{ __('admin.Previous') }}"
                },
                "emptyTable": "{{ __('admin.No_data_available_in_table') }}",
                "zeroRecords": "{{ __('admin.No_matching_records_found') }}"
                @endif
                },
            });

            // Initialize Summernote editors for custom pages with image upload
            // Render the Link/Picture/Video dialogs in <body> instead of inside the
            // editor. Nested in the Bootstrap 5 modal they inherit the modal's
            // stacking context, so their z-index/backdrop can never sit above it.
            $('#custom-page-content').summernote({
                placeholder: '{{ $l == 'ar' ? 'أدخل محتوى الصفحة' : 'Enter page content' }}',
                height: 200,
                dialogsInBody: true,
                callbacks: {
                    onImageUpload: function(files) {
                        uploadSummernoteImage(files[0], $('#custom-page-content'));
                    }
                }
            });
            $('#edit-custom-page-content').summernote({
                placeholder: '{{ $l == 'ar' ? 'أدخل محتوى الصفحة' : 'Enter page content' }}',
                height: 200,
                dialogsInBody: true,
                callbacks: {
                    onImageUpload: function(files) {
                        uploadSummernoteImage(files[0], $('#edit-custom-page-content'));
                    }
                }
            });

            /**
             * Esc while a Link/Picture/Video dialog is open must close only that
             * dialog. Summernote binds Esc on the dialog element itself, so it fires
             * only when focus is inside it, and it does not stopPropagation - so the
             * key otherwise reaches Bootstrap's document handler and closes the
             * surrounding Create/Edit Page modal. Handle it in the capture phase,
             * ahead of Bootstrap, and drive the dismissal explicitly.
             */
            document.addEventListener('keydown', function(e) {
                if (e.key !== 'Escape') {
                    return;
                }

                var $dialog = $('.note-modal.open').last();
                if (!$dialog.length) {
                    return;
                }

                // Stop Bootstrap from closing the page modal underneath...
                e.stopPropagation();
                // ...and close the Summernote dialog ourselves. Summernote binds Esc on
                // the dialog element, which only fires while focus is inside it, so we
                // cannot rely on that listener here.
                $dialog.find('.note-modal-header .close').trigger('click');
            }, true);

            /**
             * Summernote only binds dismissal to its .close button - it has no
             * click-outside-to-dismiss. Add it, and stop dialog clicks from reaching the
             * Bootstrap 5 modal's click-outside handler, which would otherwise dismiss
             * the Create/Edit Page modal underneath.
             *
             * This must bind on the dialog element itself, not delegated on document:
             * delegated handlers run only after the event has bubbled past the page
             * modal, far too late to stop it, while a capture-phase handler would run
             * before Summernote's own delegated .close handler and break the X button.
             * Dialogs are built lazily, so bind each one as it is first shown.
             */
            $('#custom-page-content, #edit-custom-page-content').on('summernote.dialog.shown', function() {
                $('.note-modal').not('[data-outside-dismiss-bound]').each(function() {
                    $(this)
                        .attr('data-outside-dismiss-bound', '1')
                        .on('click', function(e) {
                            e.stopPropagation();

                            // Only a click on the dialog shell counts as "outside".
                            if (e.target === this) {
                                $(this).find('.note-modal-header .close').trigger('click');
                            }
                        });
                });
            });

            // Image upload handler function
            function uploadSummernoteImage(file, editor) {
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    toastr.error('{{ $l == 'ar' ? 'حجم الصورة يجب أن لا يتجاوز 5 ميجابايت' : 'Image size must not exceed 5MB' }}');
                    return;
                }

                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    toastr.error('{{ $l == 'ar' ? 'يرجى اختيار صورة صالحة (jpeg, png, jpg, gif, webp)' : 'Please select a valid image (jpeg, png, jpg, gif, webp)' }}');
                    return;
                }

                const data = new FormData();
                data.append('image', file);
                data.append('cycle_id', '{{ $ccycle->id }}');
                data.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('cycle.custom_pages.upload_image', ['id' => $ccycle->id]) }}',
                    method: 'POST',
                    data: data,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status) {
                            // Insert image into editor
                            editor.summernote('insertImage', response.url);
                        } else {
                            toastr.error(response.message || '{{ $l == 'ar' ? 'فشل تحميل الصورة' : 'Image upload failed' }}');
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || '{{ $l == 'ar' ? 'حدث خطأ أثناء تحميل الصورة' : 'An error occurred while uploading the image' }}';
                        toastr.error(message);
                    }
                });
            }

            // For Summernote, remove invalid-feedback on content change
            $('#custom-page-content, #edit-custom-page-content').on('summernote.change', function() {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').remove();
            });

            // Custom Pages Event Handlers
            $(document).on('click', '#create_custom_page_btn', function(e) {
                e.preventDefault();
                // Reset form fields
                $('#custom-page-name').val('');
                $('#custom-page-visibility').val('public');
                $('#custom-page-content').summernote('code', '');
                // Remove any validation errors
                $('.form-control, .form-select').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                $("#createPageModal").modal('show');
            });

            $(document).on('click', '#save_custom_page_btn', function(e) {
                e.preventDefault();

                if (!$('#custom-page-name').val()) {
                    $('#custom-page-name').addClass('is-invalid').trigger('focus');
                    $('#custom-page-name').after('<div class="invalid-feedback d-block">{{ $invalid_feedbacks['page_name'] ?? ($l == 'ar' ? 'يرجى إدخال اسم الصفحة.' : 'Please enter a page name.') }}</div>');
                    return;
                }

                if (!$('#custom-page-content').val()) {
                    $('#custom-page-content').closest('.form-group').append('<div class="invalid-feedback d-block">{{ $invalid_feedbacks['page_content'] ?? ($l == 'ar' ? 'يرجى إدخال محتوى الصفحة.' : 'Please enter page content.') }}</div>');
                    return;
                }

                let program_id = '{{ isset($id) ? $id : ($ccycle->id ?? '') }}';
                // if( program_id )
                // {
                    let name = $('#custom-page-name').val();
                    let content = $('#custom-page-content').val();
                    let visibility = $('#custom-page-visibility').val();
                    let data = {
                        cycleId: program_id ? program_id : undefined,
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
                                // Reset form fields
                                $('#custom-page-name').val('');
                                $('#custom-page-content').summernote('code', '');
                                $('#custom-page-visibility').val('public');
                                // Remove any validation errors
                                $('.form-control, .form-select').removeClass('is-invalid');
                                $('.invalid-feedback').remove();
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
                                text: error?.message || '{{ $l == 'ar' ? 'خطأ في إنشاء الصفحة. يرجى المحاولة مرة أخرى.' : 'Error creating page. Please try again.' }}',
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
                // } else { 
                //     Swal.fire({
                //         icon: 'warning',
                //         title: '{{ $l == 'ar' ? 'تنبيه' : 'Warning' }}',
                //         text: '{{ $l == 'ar' ? 'يرجى تحديد دورة أولاً' : 'Please select a cycle first' }}',
                //         showCancelButton: false,
                //         showConfirmButton: true,
                //         confirmButtonText: "{{ __('admin.OK') }}",
                //         customClass: {
                //             confirmButton: 'btn btn-primary',
                //         },
                //         buttonsStyling: false,
                //     });
                // }
            });

            $(document).on('click', '#update_custom_page_btn', function(e) {
                e.preventDefault();

                if (!$('#edit-custom-page-name').val()) {
                    $('#edit-custom-page-name').addClass('is-invalid').trigger('focus');
                    $('#edit-custom-page-name').after('<div class="invalid-feedback d-block">{{ $invalid_feedbacks['page_name'] ?? ($l == 'ar' ? 'يرجى إدخال اسم الصفحة.' : 'Please enter a page name.') }}</div>');
                    return;
                }

                if (!$('#edit-custom-page-content').val()) {
                    $('#edit-custom-page-content').closest('.form-group').append('<div class="invalid-feedback d-block">{{ $invalid_feedbacks['page_content'] ?? ($l == 'ar' ? 'يرجى إدخال محتوى الصفحة.' : 'Please enter page content.') }}</div>');
                    return;
                }

                let program_id = '{{ isset($id) ? $id : ($ccycle->id ?? '') }}';
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
                                // Reset form fields
                                $('#edit-custom-page-name').val('');
                                $('#edit-custom-page-content').summernote('code', '');
                                $('#edit-custom-page-visibility').val('public');
                                $('#edit-custom-page-id').val('');
                                // Remove any validation errors
                                $('.form-control, .form-select').removeClass('is-invalid');
                                $('.invalid-feedback').remove();
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
                let program_id = '{{ isset($id) ? $id : ($ccycle->id ?? '') }}';
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
                                if (typeof editPageEditor !== 'undefined') {
                                    editPageEditor.root.innerHTML = response.page.content ?? '';
                                }
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
                let program_id = '{{ isset($id) ? $id : ($ccycle->id ?? '') }}';
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
                let program_id = '{{ isset($id) ? $id : ($ccycle->id ?? '') }}';
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
                let program_id = '{{ isset($id) ? $id : ($ccycle->id ?? '') }}';
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

            // Initialize dragula for page sorting
            var custom_pages_drag = dragula([document.getElementById('custom_pages_list')]);
            custom_pages_drag.on('drop', function(el) {
                let rows = $(el).parent().children();
                let sorting = [];
                rows.each(function() {
                    sorting.push($(this).attr('data-index'));
                });
                
                let program_id = '{{ isset($id) ? $id : ($ccycle->id ?? '') }}';
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
@endpush
