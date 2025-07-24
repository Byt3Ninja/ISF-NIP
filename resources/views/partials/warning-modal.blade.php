<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header text-dark">
        <h5 class="modal-title" id="warningModalLabel">{{ isset($title) && $title ? __('admin.'.$title) : 'Warning' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>{{ isset($message) && $message ? __('admin.'.$message) : 'Are you sure you want to proceed with this action? This action cannot be undone.' }}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          {{ __('admin.Cancel') }}
        </button>
        <button type="button" class="btn btn-primary" onclick="{{ isset($onConfirm) && $onConfirm ? $onConfirm : 'confirmAction()' }}">
          {{ isset($confirmText) && $confirmText ? __('admin.'.$confirmText) : __('admin.Confirm') }}
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  function confirmAction() {
    // Default action if no specific onConfirm function is provided
    window.history.back();
  }
</script>