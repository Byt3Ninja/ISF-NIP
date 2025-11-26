<!-- Advanced Export Modal -->
<div class="modal fade" id="advancedExportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center">
          <i class="ti ti-package me-2"></i> {{ __('admin.advanced_export') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h6 class="mb-0">{{ __('admin.export_status') }}</h6>
          <button class="btn btn-sm btn-outline-primary refresh-export-btn">
            <i class="ti ti-refresh me-1"></i> {{ __('admin.refresh') }}
          </button>
        </div>

        <div class="export-loading d-none text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ __('forms.loading') }}...</span>
          </div>
          <p class="mt-2 text-muted">{{ __('admin.processing') }}</p>
        </div>

        <div class="export-content">
          <div class="row mb-4 text-center">
            <div class="col-6 border-end">
              <h3 class="text-primary mb-0 ready-count">-</h3>
              <small class="text-muted">{{ __('admin.ready_to_download') }}</small>
            </div>
            <div class="col-6">
              <h3 class="text-dark mb-0 total-count">-</h3>
              <small class="text-muted">{{ __('admin.total_entries') }}</small>
            </div>
          </div>

          <div class="progress-container d-none mb-4">
            <label class="form-label">{{ __('admin.export_progress') }}</label>
            <div class="progress" style="height: 20px;">
              <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"
                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
          </div>

          <div class="file-info alert alert-secondary d-none mb-4">
            <div class="d-flex align-items-center mb-1">
              <i class="ti ti-file-zip fs-4 me-2"></i>
              <span class="fw-bold file-name">filename.zip</span>
            </div>
            <div class="d-flex justify-content-between text-muted small ps-4">
              <span class="file-size">-</span>
              <span class="file-date">-</span>
            </div>
          </div>

          <div class="d-grid gap-2">
            <button class="btn btn-primary generate-export-btn">
              <i class="ti ti-settings me-1"></i> {{ __('admin.generate_export') }}
            </button>

            <button class="btn btn-success download-export-btn d-none">
              <i class="ti ti-download me-1"></i> {{ __('admin.download_generated_file') }}
            </button>

            <button class="btn btn-outline-secondary regenerate-export-btn d-none">
              <i class="ti ti-reload me-1"></i> {{ __('admin.regenerate') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>