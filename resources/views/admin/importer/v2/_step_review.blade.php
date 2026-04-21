<section aria-labelledby="review-heading">
    <h5 id="review-heading" class="fw-semibold border-bottom pb-3 mb-4">
        3. {{ __('importer.ui.step3') }}
    </h5>

    <div class="row g-3">
        <!-- Organization selector -->
        <div class="col-md-6">
            <label for="importer-review-org" class="form-label fw-semibold">
                {{ __('importer.ui.step3') }}
                <span class="text-danger">*</span>
            </label>
            <select id="importer-review-org" class="form-select">
                <option value="">—</option>
            </select>
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
</section>
