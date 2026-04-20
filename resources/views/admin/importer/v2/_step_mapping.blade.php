<section aria-labelledby="mapping-heading">
    <h5 id="mapping-heading" class="fw-semibold border-bottom pb-3 mb-4">
        2. {{ __('importer.ui.step2') }}
    </h5>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="small text-muted">
            <span id="importer-mapping-count">0</span> / <span id="importer-mapping-total">57</span>
            fields mapped
        </div>
        <div>
            <button type="button" id="importer-automap-btn" class="btn btn-outline-primary btn-sm">
                {{ __('importer.ui.actions.auto_map') }}
            </button>
        </div>
    </div>

    <div id="importer-automap-message" class="alert d-none" role="status"></div>

    <!-- Sections rendered by importer-mapping.js from window.importerSections -->
    <div id="importer-mapping-sections" class="accordion"></div>

    <div id="importer-row-errors" class="alert alert-danger d-none mt-4" role="alert">
        <h6 class="fw-bold mb-2" id="importer-row-errors-title">Validation errors</h6>
        <div class="small text-muted mb-2" id="importer-row-errors-summary"></div>
        <div class="table-responsive">
            <table class="table table-sm importer-error-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 5rem;">Row</th>
                        <th>Field</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody id="importer-row-errors-body"></tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <button type="button" id="importer-mapping-back" class="btn btn-outline-secondary">
            {{ __('importer.ui.actions.back') }}
        </button>
        <button type="button" id="importer-mapping-next" class="btn btn-primary">
            {{ __('importer.ui.actions.next') }}
        </button>
    </div>
</section>
