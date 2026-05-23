<section aria-labelledby="upload-heading">
    <h5 id="upload-heading" class="fw-semibold border-bottom pb-3 mb-4">
        1. {{ __('importer.ui.step1') }}
    </h5>

    <div class="importer-drop-zone border rounded p-4 text-center mb-3"
         id="importer-drop-zone"
         tabindex="0"
         role="button"
         aria-label="{{ __('importer.ui.step1') }}">
        <input type="file" id="importer-file-input" accept=".csv,text/csv" class="d-none">
        <p class="mb-2">{{ __('importer.ui.step1') }} (.csv)</p>
        {{--  <p class="text-muted small mb-0">
            {{ __('importer.ui.actions.download') }}:
            <a href="{{ route('importer.template') }}">{{ __('importer.ui.title') }}</a>
        </p>  --}}
    </div>

    <div id="importer-uploaded-info" class="alert alert-info d-none">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong id="importer-file-name"></strong>
                <span class="ms-2 text-muted small">
                    (<span id="importer-row-count">0</span> rows)
                </span>
            </div>
            <div>
                <button type="button" id="importer-replace-btn" class="btn btn-sm btn-outline-secondary">
                    {{ __('importer.ui.actions.replace_file') }}
                </button>
                <button type="button" id="importer-next-to-mapping" class="btn btn-sm btn-primary ms-2">
                    {{ __('importer.ui.actions.next') }}
                </button>
            </div>
        </div>
    </div>

    <div id="importer-upload-errors" class="d-none"></div>
</section>
