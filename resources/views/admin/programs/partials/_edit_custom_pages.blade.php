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


 <script>
		$(document).ready(function() {
			$('#custom-page-content').summernote({
				placeholder: '{{ $l == 'ar' ? 'أدخل محتوى الصفحة' : 'Enter page content' }}',
				height: 200
			});
			$('#edit-custom-page-content').summernote({
				placeholder: '{{ $l == 'ar' ? 'أدخل محتوى الصفحة' : 'Enter page content' }}',
				height: 200
			});

			// For Summernote, remove invalid-feedback on content change
			$('#custom-page-content, #edit-custom-page-content').on('summernote.change', function() {
				$(this).removeClass('is-invalid');
				$(this).siblings('.invalid-feedback').remove();
			});
    });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable) {
      $('.table').DataTable({
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
    }
  });
</script>