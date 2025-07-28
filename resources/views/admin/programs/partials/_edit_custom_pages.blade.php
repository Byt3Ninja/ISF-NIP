<div class="row">
	<div class="col-12 mb-12 text-end">
		<button id="create_custom_page_btn" class="btn btn-dark btn-sm" type="button"> <i class="fas fa-fw fa-sm fa-plus-circle mx-1"></i> Add new page</button>
	</div>
	<div class="col-12 mb-12">
		<div class="table-responsive">
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th>Page name</th>
						<th style="width: 15%">Visibility</th>
						<th style="width: 10%">Status</th>
						<th style="width: 10%">Actions</th>
					</tr>
				</thead>
				<tbody id="custom_pages_list">
					@foreach( $ccycle->custom_pages->sortBy('row_no')->values() as $custom_page )
						<tr data-index="{{ $custom_page->id }}">
							<td>{{ $custom_page->title }}</td>
							<td>
								<select class="form-select custom-page-visibility-toggle" data-page-id="{{ $custom_page->id }}">
									<option value="public" {{ $custom_page->visibility == 'public' ? 'selected' : '' }}>Public</option>
									<option value="private" {{ $custom_page->visibility == 'private' ? 'selected' : '' }}>Participants</option>
									<option value="admins" {{ $custom_page->visibility == 'admins' ? 'selected' : '' }}>Admin Only</option>
								</select>
							</td>
							<td>
								@if( $custom_page->is_active == 1 )
									<span class="text-success"> <i class="fas fa-fw fa-sm fa-circle mx-1"></i> Active</span>
								@else
									<span class="text-danger"> <i class="fas fa-fw fa-sm fa-circle mx-1"></i> Inactive</span>
								@endif
							</td>
							<td>
								<div class="dropdown">
									<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
										Actions
									</button>
									<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
										<li data-page-id="{{ $custom_page->id }}" class="custom-page-edit"><a class="dropdown-item" href="javascript:void(0);">Edit</a></li>
										<li data-page-id="{{ $custom_page->id }}" class="custom-page-toggle-status"><a class="dropdown-item" href="javascript:void(0);">Toggle</a></li>
										<li data-page-id="{{ $custom_page->id }}" class="custom-page-delete"><a class="dropdown-item" href="javascript:void(0);">Delete</a></li>
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

<div class="modal fade" id="createPageModal" tabindex="-1" aria-labelledby="createPageModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="createPageModalLabel">Create Page</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="container-fluid">
					<div class="row">
						<div class="col-12">
							<div class="form-group mb-3">
								<label for="custom-page-name" class="form-label">Page name</label>
								<input type="text" class="form-control" id="custom-page-name" name="custom-page-name" placeholder="Enter page name">
							</div>
							<div class="form-group mb-3">
								<label for="custom-page-visibility" class="form-label">Visibility</label>
								<select class="form-control" id="custom-page-visibility" name="custom-page-visibility">
									<option value="public">Public</option>
									<option value="private">Participants</option>
									<option value="admins">Admin Only</option>
								</select>
							</div>
							<div class="form-group mb-3">
								<label for="custom-page-content" class="form-label">Description</label>
								<textarea class="form-control" id="custom-page-content" name="custom-page-content" rows="5"></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button id="save_custom_page_btn" type="button" class="btn btn-primary">Save Page</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="editPageModal" tabindex="-1" aria-labelledby="editPageModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="editPageModalLabel">Edit Page</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="container-fluid">
					<div class="row">
						<div class="col-12">
							<div class="form-group mb-3">
								<label for="edit-custom-page-name" class="form-label">Page name</label>
								<input type="text" class="form-control" id="edit-custom-page-name" name="edit-custom-page-name" placeholder="Enter page name">
							</div>
							<div class="form-group mb-3">
								<label for="edit-custom-page-visibility" class="form-label">Visibility</label>
								<select class="form-control" id="edit-custom-page-visibility" name="edit-custom-page-visibility">
									<option value="public">Public</option>
									<option value="private">Participants</option>
									<option value="admins">Admin Only</option>
								</select>
							</div>
							<div class="form-group mb-3">
								<label for="edit-custom-page-content" class="form-label">Description</label>
								<textarea class="form-control" id="edit-custom-page-content" name="edit-custom-page-content" rows="5"></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit-custom-page-id" id="edit-custom-page-id" value="" />
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button id="update_custom_page_btn" type="button" class="btn btn-primary">Save Page</button>
			</div>
		</div>
	</div>
</div>
