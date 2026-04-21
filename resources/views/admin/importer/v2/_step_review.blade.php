<section aria-labelledby="review-heading">
    <h5 id="review-heading" class="fw-semibold border-bottom pb-3 mb-4">
        3. {{ __('importer.ui.step3') }}
    </h5>

    <div class="row g-3" id="importer-review-org-row">
        <!-- Organization selector. Hidden when user has exactly 1 reachable org. -->
        <div class="col-md-6">
            <label for="importer-review-org" class="form-label fw-semibold">
                {{ __('importer.ui.org_label') }}
                <span class="text-danger">*</span>
            </label>
            <select id="importer-review-org" class="form-select">
                <option value="">—</option>
            </select>
            <small class="form-text text-muted">{{ __('importer.ui.org_help') }}</small>
            <div id="importer-review-org-error" class="invalid-feedback d-block d-none"></div>
        </div>
    </div>

    <!-- Mapping summary -->
    <div class="mt-4">
        <h6 class="fw-semibold mb-3">Mapping summary</h6>
        <div id="importer-review-mappings" class="importer-review-groups"></div>
    </div>

    <!-- Sample data preview -->
    <div class="mt-4">
        <h6 class="fw-semibold mb-3">Sample Data Preview (first 3 rows)</h6>
        <div class="table-responsive">
            <table class="table table-sm importer-preview-table">
                <thead id="importer-review-preview-head"></thead>
                <tbody id="importer-review-preview-body"></tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <button type="button" id="importer-review-back" class="btn btn-outline-secondary">
            {{ __('importer.ui.actions.back') }}
        </button>
        <button type="button" id="importer-review-start" class="btn btn-primary">
            {{ __('importer.ui.actions.start_import') }}
        </button>
    </div>

    <div id="importer-result" class="mt-4 d-none">
        <div class="alert" role="status" id="importer-result-banner"></div>
        <div class="card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <div class="small text-muted">Processed</div>
                        <div id="importer-result-processed" class="h5"></div>
                    </div>
                    <div class="col-3">
                        <div class="small text-muted">Imported</div>
                        <div id="importer-result-imported" class="h5 text-success"></div>
                    </div>
                    <div class="col-3">
                        <div class="small text-muted">Skipped</div>
                        <div id="importer-result-skipped" class="h5 text-warning"></div>
                    </div>
                    <div class="col-3">
                        <div class="small text-muted">Failed</div>
                        <div id="importer-result-failed" class="h5 text-danger"></div>
                    </div>
                </div>
                <details class="mt-3">
                    <summary>Details</summary>
                    <ul id="importer-result-list" class="mt-2 small"></ul>
                </details>
                <div class="mt-3">
                    <button type="button" id="importer-start-new" class="btn btn-outline-primary">
                        {{ __('importer.ui.actions.start_new') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
