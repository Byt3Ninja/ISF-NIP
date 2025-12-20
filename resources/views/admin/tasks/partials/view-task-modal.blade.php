<!-- Modal to view task details -->
<div
  class="modal fade"
  id="viewTaskModal"
  tabindex="-1"
  aria-labelledby="viewTaskModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewTaskModalLabel">{{ __('admin.task_details') }}</h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="task-details-content">
          <!-- Task details will be populated here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
      </div>
    </div>
  </div>
</div>
<!--/ Modal to view task details -->