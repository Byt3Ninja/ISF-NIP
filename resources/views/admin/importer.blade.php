@extends('layouts.admin')

@section('content')
<div class="">
    <div class="">
        <div class="card card-body">
            <div class="mb-4">
                <h4 class="fw-bold mb-2">{{ __('admin.Advanced Startup & Founder Importer') }}</h4>
                <p class="text-secondary">
                    {{ __('admin.upload_Csv') }}
                </p>
            </div>

            <div class="small text-secondary mb-4">
                <p>
                    {{ __('admin.Download') }}
                    <a href="{{ $templateUrl }}" download class="text-primary fw-medium text-decoration-none">
                        {{ __('admin.CSV template') }}
                    </a>
                    {{ __('admin.text') }}
                </p>
            </div>
            
            <!-- Step 1: File Upload -->
            <section id="upload-section" class="mb-4">
                <h5 class="fw-semibold border-bottom pb-3 mb-4 d-flex align-items-center">
                    <span 
                        class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 32px; height: 32px;"
                    >1</span>
                    {{ __('admin.Upload CSV File') }}
                </h5>
                <div 
                    id="drop-zone"
                    class="border border-2 border-dashed rounded p-4 p-md-5 text-center mb-3 cursor-pointer bg-label-secondary"
                >
                    <input type="file" id="csv-file-input" class="d-none" accept=".csv">
                    <div class="d-flex flex-column align-items-center justify-content-center gap-3">
                        <i class="ti ti-cloud-upload text-primary fs-1"></i>
                        <p class="fw-medium mb-0">{{ __('admin.Drag & drop your CSV file here') }}</p>
                        <h6 class="mb-0 text-muted">{{ __('admin.OR') }}</h6>
                        <button type="button" id="browse-button" class="btn btn-primary btn-sm shadow-sm">
                            {{ __('admin.Browse Files') }}
                        </button>
                    </div>
                </div>
                <div id="file-info" class="d-none mt-3 p-3 bg-info bg-opacity-10 border border-info rounded text-info">
                    <p><strong class="fw-semibold">{{ __('admin.File selected') }}:</strong> <span id="file-name"></span></p>
                    <p class="mb-0"><strong class="fw-semibold">{{ __('admin.Size') }}:</strong> <span id="file-size"></span></p>
                </div>
            </section>

            <!-- Step 2: Column Mapping -->
            <section id="mapping-section" class="mb-4 d-none">
                <h5 class="fw-semibold border-bottom pb-3 mb-4 d-flex align-items-center">
                    <span 
                        class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 32px; height: 32px;"
                    >2</span>
                    {{ __('admin.Map Columns') }}
                </h5>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="text-secondary small">
                        {{ __('admin.Importer match fields') }}
                        {{ __('admin.Importer required fields') }}
                    </p>
                    <button id="auto-map-button" class="btn btn-label-primary btn-sm shadow-sm">
                        {{ __('admin.Auto map') }}
                    </button>
                </div>

                <div id="uploaded-headers-preview" class="mb-4 p-3 bg-light border rounded text-sm">
                    <p class="fw-semibold text-secondary mb-1">{{ __('admin.Detected Headers from your file') }}:</p>
                    <div id="detected-headers-list" class="text-secondary d-flex flex-wrap gap-2"></div>
                </div>

                <div id="validation-errors" class="d-none mb-4 p-3 bg-danger bg-opacity-10 border border-danger rounded text-danger">
                    <ul id="validation-errors-list" class="list-disc ps-4"></ul>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col" class="w-50">{{ __('admin.Application Field (Destination)') }}</th>
                                <th scope="col" class="w-50">{{ __('admin.Your CSV Column (Source)') }}</th>
                            </tr>
                        </thead>
                        <tbody id="mapping-table-body-cycle">
                            <tr class="table-light">
                                <th colspan="2">{{ __('admin.Program Details') }}</th>
                            </tr>
                        </tbody>
                        <tbody id="mapping-table-body-company">
                            <tr class="table-light">
                                <th colspan="2">{{ __('admin.Company Details') }}</th>
                            </tr>
                        </tbody>
                        <tbody id="mapping-table-body-founder">
                            <tr class="table-light">
                                <th colspan="2">{{ __('admin.Founder Details') }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="small text-muted mt-2">
                    {{ __('admin.Importer note') }}:
                </div>
            </section>

            <!-- Step 3: Review & Import -->
            <section id="review-section" class="mb-4 d-none">
                <h5 class="fw-semibold border-bottom pb-3 mb-4 d-flex align-items-center">
                    <span 
                        class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 32px; height: 32px;"
                    >3</span>
                    {{ __('admin.Review and Import') }}
                </h5>
                <p class="text-secondary small">
                    {{ __('admin.Importer review note') }}:
                </p>

                <!-- Organization Selection -->
                <div class="mb-4">
                    <label for="organization-select" class="form-label fw-medium">
                        {{ __('admin.Select Organization') }}
                    </label>
                    <select id="organization-select" class="form-select">
                        <option value="">{{ __('admin.Select') }}</option>
                    </select>
                    <p id="organization-error" class="d-none text-danger small mt-1">
                        {{ __('admin.Please select an organization') }}
                    </p>
                </div>

                <div id="mapping-summary" class="p-3 bg-light border rounded mb-4"></div>

                <h3 class="h5 fw-medium mb-3">{{ __('admin.Sample Data Preview (First 3 Rows)') }}</h3>
                <div id="data-preview-table-container" class="table-responsive mb-3" style="max-height: 300px;">
                    <table class="table">
                        <thead id="data-preview-thead"></thead>
                        <tbody id="data-preview-tbody"></tbody>
                    </table>
                </div>
            </section>

            <!-- Import Result Summary -->
            <section id="result-section" class="mb-4 d-none">
                <h5 class="fw-semibold border-bottom pb-3 mb-4">{{ __('admin.Import Result Summary') }}</h5>
                <div id="result-summary" class="p-3 bg-light border rounded">
                    <p><strong>{{ __('admin.Total Rows in CSV') }}:</strong> <span id="total-rows"></span></p>
                    <p><strong>{{ __('admin.Rows Attempted') }}:</strong> <span id="rows-attempted"></span></p>
                    <p><strong>{{ __('admin.Successful Imports') }}:</strong> <span id="successful-imports"></span></p>
                    <p><strong>{{ __('admin.Failed Rows') }}:</strong> <span id="failed-rows"></span></p>
                    <p><strong>{{ __('admin.Skipped Rows') }}:</strong> <span id="skipped-rows"></span></p>
                    <p><strong>{{ __('admin.Duplicates Found') }}:</strong> <span id="duplicates-found"></span></p>
                    <div id="result-details" class="mt-3"></div>
                </div>
            </section>

            <!-- Action Buttons -->
            <div id="action-buttons" class="pt-4 border-top d-flex flex-column flex-sm-row justify-content-end gap-2">
                <button id="back-button" type="button" class="btn btn-outline-secondary d-none">
                    {{ __('admin.Back') }}
                </button>
                <button id="next-button" type="button" class="btn btn-primary disabled">
                    {{ __('admin.Next') }}
                </button>
                <button id="import-button" type="button" class="btn btn-success d-none">
                    {{ __('admin.Start Import') }}
                </button>
            </div>

            <!-- Progress/Status Message -->
            <div id="status-message" class="d-none mt-4 p-3 rounded"></div>
        </div>
    </div>
</div>
@stop

@section('pagescripts')
<script>
    const DESTINATION_FIELDS_STRUCTURED = [
        { entity: "Cycle", field: "title", label: "{{ __('admin.Program Name *') }}" },
        { entity: "Cycle", field: "type", label: "{{ __('admin.Program_Type *') }}" },
        { entity: "Cycle", field: "start", label: "{{ __('admin.Program Start Date *') }}" },
        { entity: "Cycle", field: "end", label: "{{ __('admin.Program End Date *') }}" },
        { entity: "Company", field: "name_en", label: "{{ __('admin.Company Name (EN) *') }}" },
        { entity: "Company", field: "country_id", label: "{{ __('admin.Country') }}" },
        { entity: "Company", field: "state_id", label: "{{ __('admin.State/Governorate') }}" },
        { entity: "Company", field: "industries", label: "{{ __('admin.Industries') }}" },
        { entity: "Company", field: "sector", label: "{{ __('admin.Sector') }}" },
        { entity: "Company", field: "address_en", label: "{{ __('admin.Address (EN) *') }}" },
        { entity: "Company", field: "years_of_operations", label: "{{ __('admin.Years of Operations') }}" },
        { entity: "Company", field: "product_or_service", label: "{{ __('admin.Product or Service') }}" },
        { entity: "Company", field: "targeted_market", label: "{{ __('admin.Targeted Market') }}" },
        { entity: "Company", field: "est_date", label: "{{ __('admin.Establishment Date') }}" },
        { entity: "Founder", field: "first_name_en", label: "{{ __('admin.First Name (EN) *') }}" },
        { entity: "Founder", field: "last_name_en", label: "{{ __('admin.Last Name (EN) *') }}" },
        { entity: "Founder", field: "gender", label: "{{ __('admin.Gender') }}" },
        { entity: "Founder", field: "phone", label: "{{ __('admin.Phone') }}" },
        { entity: "Founder", field: "email", label: "{{ __('admin.Email *') }}" },
        { entity: "Founder", field: "country_code", label: "{{ __('admin.Country_Code') }}" }
    ];

    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('csv-file-input');
    const browseButton = document.getElementById('browse-button');
    const fileInfoDiv = document.getElementById('file-info');
    const fileNameSpan = document.getElementById('file-name');
    const fileSizeSpan = document.getElementById('file-size');
    const uploadSection = document.getElementById('upload-section');
    const mappingSection = document.getElementById('mapping-section');
    const reviewSection = document.getElementById('review-section');
    const resultSection = document.getElementById('result-section');
    const mappingTableBodyCycle = document.getElementById('mapping-table-body-cycle');
    const mappingTableBodyCompany = document.getElementById('mapping-table-body-company');
    const mappingTableBodyFounder = document.getElementById('mapping-table-body-founder');
    const autoMapButton = document.getElementById('auto-map-button');
    const detectedHeadersList = document.getElementById('detected-headers-list');
    const mappingSummaryDiv = document.getElementById('mapping-summary');
    const dataPreviewThead = document.getElementById('data-preview-thead');
    const dataPreviewTbody = document.getElementById('data-preview-tbody');
    const resultSummaryDiv = document.getElementById('result-summary');
    const nextButton = document.getElementById('next-button');
    const backButton = document.getElementById('back-button');
    const importButton = document.getElementById('import-button');
    const statusMessageDiv = document.getElementById('status-message');
    const organizationSelect = document.getElementById('organization-select');
    const organizationError = document.getElementById('organization-error');
    const validationErrorsDiv = document.getElementById('validation-errors');
    const validationErrorsList = document.getElementById('validation-errors-list');

    organizationSelect.addEventListener('change', function() {
        organizationError.classList.add('d-none');
    });

    let currentStep = 1;
    let uploadedFile = null;
    let uploadedHeaders = [];
    let uploadedData = [];
    let columnMappings = {};
    let validationErrors = [];

    browseButton.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.click();
    });
    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('drag-enter'); });
    dropZone.addEventListener('dragleave', (e) => { e.preventDefault(); dropZone.classList.remove('drag-enter'); });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-enter');
        if (e.dataTransfer.files.length > 0) handleFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) handleFile(e.target.files[0]);
    });
    autoMapButton.addEventListener('click', function() {
        try {
            autoMapColumns();
        } catch (err) {
            console.error("Error in autoMapColumns:", err);
            alert("Error during auto-mapping: " + err.message);
        }
    });
    nextButton.addEventListener('click', handleNextStep);
    backButton.addEventListener('click', handlePreviousStep);
    importButton.addEventListener('click', handleImport);

    function fetchOrganizations() {
        fetch('{{ route('importer.organizations') }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch organizations');
            return response.json();
        })
        .then(data => {
            organizationSelect.innerHTML = '<option value="">-- {{ __('admin.Select Organization') }} --</option>';
            data.organizations.forEach(org => {
                const option = document.createElement('option');
                option.value = org.id;
                option.textContent = org.name_en || org.name_ar || `Organization ${org.id}`;
                organizationSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Fetch organizations error:', error);
            updateStatusMessage('Failed to load organizations: ' + error.message, 'error');
            organizationSelect.innerHTML = '<option value="">-- {{ __('admin.Error loading organizations') }} --</option>';
        });
    }

    function handleFile(file) {
        if (file && file.type === "text/csv") {
            uploadedFile = file;
            fileNameSpan.textContent = file.name;
            fileSizeSpan.textContent = `${(file.size / 1024).toFixed(2)} KB`;
            fileInfoDiv.classList.remove('d-none');
            nextButton.disabled = false;
            nextButton.classList.remove('disabled');

            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('importer.upload') }}', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || `HTTP error! Status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    uploadedHeaders = data.headers;
                    uploadedData = data.data;
                    validationErrors = data.errors || [];
                    displayDetectedHeaders();
                    populateMappingTable();
                    autoMapButton.disabled = false;
                    updateStatusMessage('{{ __('admin.File uploaded successfully') }}', 'success', 3000);
                } else {
                    updateStatusMessage(data.message || '{{ __('admin.Error uploading file') }}', 'error');
                    resetUploadState();
                }
            })
            .catch(error => {
                console.error('Upload fetch error:', error.message);
                updateStatusMessage('{{ __('admin.Error uploading file') }}: ' + error.message, 'error');
                resetUploadState();
            });
        } else {
            alert("{{ __('admin.Please upload a valid CSV file') }}");
            resetUploadState();
        }
    }

    function resetUploadState() {
        uploadedFile = null;
        uploadedHeaders = [];
        uploadedData = [];
        validationErrors = [];
        columnMappings = {};
        fileInput.value = '';
        fileInfoDiv.classList.add('d-none');
        fileNameSpan.textContent = '';
        fileSizeSpan.textContent = '';
        nextButton.disabled = true;
        nextButton.classList.add('disabled');
        autoMapButton.disabled = true;
        mappingTableBodyCycle.innerHTML = '<tr class="table-light"><th colspan="2" class="text-primary">{{ __("admin.Program Details") }}</th></tr>';
        mappingTableBodyCompany.innerHTML = '<tr class="table-light"><th colspan="2" class="text-primary">{{ __("admin.Company Details") }}</th></tr>';
        mappingTableBodyFounder.innerHTML = '<tr class="table-light"><th colspan="2" class="text-primary">{{ __("admin.Founder Details") }}</th></tr>';
        detectedHeadersList.innerHTML = '';
        validationErrorsDiv.classList.add('d-none');
        validationErrorsList.innerHTML = '';
        updateStatusMessage('', 'info', 1);
        organizationSelect.innerHTML = '<option value="">-- {{ __('admin.Select Organization') }} --</option>';
        resultSection.classList.add('d-none');
        resultSummaryDiv.innerHTML = '';
    }

    function displayDetectedHeaders() {
        detectedHeadersList.innerHTML = '';
        if (uploadedHeaders.length > 0) {
            uploadedHeaders.forEach(header => {
                const span = document.createElement('span');
                span.className = 'badge bg-secondary me-1 mb-1';
                span.textContent = header;
                detectedHeadersList.appendChild(span);
            });
        } else {
            detectedHeadersList.textContent = '{{ __('admin.No headers detected or file is empty') }}';
        }
    }

    function populateMappingTable() {
        mappingTableBodyCycle.innerHTML = '<tr class="table-light"><th colspan="2" class="text-primary">{{ __("admin.Program Details") }}</th></tr>';
        mappingTableBodyCompany.innerHTML = '<tr class="table-light"><th colspan="2" class="text-primary">{{ __("admin.Company Details") }}</th></tr>';
        mappingTableBodyFounder.innerHTML = '<tr class="table-light"><th colspan="2" class="text-primary">{{ __("admin.Founder Details") }}</th></tr>';

        const doNotImportOption = "<option value=''>--- {{ __('admin.Do not import') }} ---</option>";
        const sourceHeaderOptions = uploadedHeaders.map((header, index) =>
            `<option value="${index}">${header}</option>`
        ).join('');

        DESTINATION_FIELDS_STRUCTURED.forEach(dest => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="align-middle fw-medium">${dest.label} <span class="text-muted small">(${dest.entity})</span></td>
                <td class="align-middle">
                    <select data-destination-key="${dest.field}" data-destination-label="${dest.label}" data-entity="${dest.entity}" class="mapping-select form-select form-select-sm">
                        ${doNotImportOption}
                        ${sourceHeaderOptions}
                    </select>
                </td>
            `;
            if (dest.entity === "Cycle") {
                mappingTableBodyCycle.appendChild(row);
            } else if (dest.entity === "Company") {
                mappingTableBodyCompany.appendChild(row);
            } else if (dest.entity === "Founder") {
                mappingTableBodyFounder.appendChild(row);
            }
        });
    }

    function autoMapColumns() {
        const AUTO_MAP_DICTIONARY = {
            'title': ['Program Name', 'Cycle Name', 'Program Title'],
            'start': ['Program Start Date', 'Start Date', 'Cycle Start Date'],
            'end': ['Program End Date', 'End Date', 'Cycle End Date'],
            'type': ['Type', 'Program Type', 'Cycle Type'],
            'name_en': ['Startup Name', 'Company Name', 'Business Name', 'Name'],
            'address_en': ['Address', 'Location', 'Company Address'],
            'country_id': ['Country', 'Country Name'],
            'state_id': ['Governorate', 'State', 'Region'],
            'industries': ['industry', 'Industries'],
            'sector': ['Sector'],
            'years_of_operations': ['Years of Operations', 'Years'],
            'product_or_service': ['Product or Service', 'Product', 'Service'],
            'targeted_market': ['Target Markets', 'Market'],
            'est_date': ['Establishment date', 'Founded Date'],
            'first_name_en': ['Founder Name', 'First Name', 'First Name (EN)'],
            'last_name_en': ['Founder Last Name', 'Last Name', 'Last Name (EN)'],
            'gender': ['Gender'],
            'phone': ['Phone', 'Founder Phone'],
            'email': ['E-Mail', 'Email', 'Founder Email'],
            'country_code': ['country code', 'Country Code']
        };

        const selects = document.querySelectorAll('.mapping-select');
        const mappingLog = [];

        selects.forEach(select => {
            const destKey = select.dataset.destinationKey;
            const destLabel = select.dataset.destinationLabel;
            let bestMatchIndex = -1;
            let matchedHeader = '';

            const possibleMatches = AUTO_MAP_DICTIONARY[destKey] || [];

            // Prioritize exact matches
            uploadedHeaders.forEach((sourceHeader, index) => {
                if (possibleMatches.includes(sourceHeader)) {
                    bestMatchIndex = index;
                    matchedHeader = sourceHeader;
                }
            });

            // Fallback to normalized comparison if no exact match
            if (bestMatchIndex === -1) {
                const destLabelNormalized = destLabel.toLowerCase().replace(/[^a-z0-9]/gi, '');
                uploadedHeaders.forEach((sourceHeader, index) => {
                    const srcHeaderNormalized = sourceHeader.toLowerCase().replace(/[^a-z0-9]/gi, '');
                    if (destLabelNormalized === srcHeaderNormalized || possibleMatches.some(match => match.toLowerCase() === sourceHeader.toLowerCase())) {
                        if (bestMatchIndex === -1 || index < bestMatchIndex) {
                            bestMatchIndex = index;
                            matchedHeader = sourceHeader;
                        }
                    }
                });
            }

            // Only set value if a valid match is found and select is not already mapped
            if (bestMatchIndex !== -1 && bestMatchIndex < uploadedHeaders.length && select.value === '') {
                select.value = bestMatchIndex.toString();
            }

            mappingLog.push({
                destKey,
                destLabel,
                bestMatchIndex,
                matchedHeader,
                selectValue: select.value,
                headers: uploadedHeaders
            });
        });

        updateStatusMessage('{{ __('admin.Auto-mapping completed') }}', 'info', 3000);
    }

   function updateButtonStates() {
    backButton.classList.toggle('d-none', currentStep === 1 || currentStep === 4);
    nextButton.classList.toggle('d-none', currentStep === 3 || currentStep === 4);
    importButton.classList.toggle('d-none', currentStep !== 3);
    nextButton.disabled = (currentStep === 1 && !uploadedFile);
    if (nextButton.disabled) {
        nextButton.classList.add('disabled');
    } else {
        nextButton.classList.remove('disabled');
    }
}

    function updateSectionVisibility() {
        uploadSection.classList.toggle('d-none', currentStep !== 1);
        mappingSection.classList.toggle('d-none', currentStep !== 2);
        reviewSection.classList.toggle('d-none', currentStep !== 3);
        resultSection.classList.toggle('d-none', currentStep !== 4);
        if (currentStep === 2 && validationErrors.length > 0) {
            validationErrorsList.innerHTML = '';
            validationErrors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                validationErrorsList.appendChild(li);
            });
            validationErrorsDiv.classList.remove('d-none');
        } else {
            validationErrorsDiv.classList.add('d-none');
        }
        if (currentStep === 3) {
            fetchOrganizations();
        }
    }

    function handleNextStep() {
        if (currentStep === 1 && (!uploadedFile || uploadedHeaders.length === 0)) {
            alert(uploadedFile ? "{{ __('admin.Uploaded file is empty or lacks headers') }}" : "{{ __('admin.Please upload a file first') }}");
            return;
        }
        if (currentStep === 2) {
            collectMappings();
            const requiredFields = [
                {label: '{{ __('admin.Company Name (EN) *') }}', value: 'name_en'},
                {label: '{{ __('admin.Address (EN) *') }}', value: 'address_en'},
                {label: '{{ __('admin.Email *') }}', value: 'email'},
                {label: '{{ __('admin.First Name (EN) *') }}', value: 'first_name_en'},
                {label: '{{ __('admin.Last Name (EN) *') }}', value: 'last_name_en'},
                {label: '{{ __('admin.Program Name *') }}', value: 'title'},
                {label: '{{ __('admin.Program Start Date *') }}', value: 'start'},
                {label: '{{ __('admin.Program End Date *') }}', value: 'end'},
                {label: '{{ __('admin.Program_Type *') }}', value: 'type'},
            ];
            const missingRequired = requiredFields.filter(field => !columnMappings.hasOwnProperty(field.value));
            if (missingRequired.length > 0) {
                updateStatusMessage(`{{ __('admin.Please map the following required fields: ') }}${missingRequired.map(field => field.label).join(', ')}`, 'error', 5000);
                return;
            }
            if (Object.keys(columnMappings).length === 0) {
                if (!confirm("{{ __('admin.No columns are mapped') }}")) return;
            }
            if (validationErrors.length > 0) {
                updateStatusMessage('{{ __('admin.Please fix the validation errors below') }}', 'error', 5000);
                return;
            }
            populateMappingSummary();
            populateDataPreview();
        }
        if (currentStep < 3) {
            currentStep++;
            updateSectionVisibility();
            updateButtonStates();
        }
    }

    function handlePreviousStep() {
        if (currentStep > 1) {
            currentStep--;
            updateSectionVisibility();
            updateButtonStates();
            organizationError.classList.add('d-none');
        }
    }

    function collectMappings() {
        columnMappings = {};
        document.querySelectorAll('.mapping-select').forEach(select => {
            const destKey = select.dataset.destinationKey;
            const sourceHeaderIndex = select.value;
            if (sourceHeaderIndex !== '' && sourceHeaderIndex < uploadedHeaders.length) {
                columnMappings[destKey] = parseInt(sourceHeaderIndex, 10);
            }
        });
        console.log('Column Mappings:', columnMappings);
        console.log('Available Headers:', uploadedHeaders);
    }

    function populateMappingSummary() {
        mappingSummaryDiv.innerHTML = '';
        let cycleHtml = '<h4 class="h6 fw-semibold text-primary mb-2">{{ __('admin.Program Mappings') }}</h4><ul class="list-disc ps-4 mb-3 small">';
        let companyHtml = '<h4 class="h6 fw-semibold text-primary mb-2">{{ __('admin.Company Mappings') }}</h4><ul class="list-disc ps-4 mb-3 small">';
        let founderHtml = '<h4 class="h6 fw-semibold text-primary mb-2">{{ __('admin.Founder Mappings') }}</h4><ul class="list-disc ps-4 mb-3 small">';
        let cycleMappingsExist = false;
        let companyMappingsExist = false;
        let founderMappingsExist = false;

        DESTINATION_FIELDS_STRUCTURED.forEach(dest => {
            if (columnMappings.hasOwnProperty(dest.field)) {
                const srcIndex = columnMappings[dest.field];
                const sourceColumn = uploadedHeaders[srcIndex] || 'Unknown';
                const listItem = `<li><span class="fw-semibold">${dest.label}</span> → <span class="text-primary">${sourceColumn}</span></li>`;
                if (dest.entity === "Cycle") {
                    cycleHtml += listItem;
                    cycleMappingsExist = true;
                } else if (dest.entity === "Company") {
                    companyHtml += listItem;
                    companyMappingsExist = true;
                } else if (dest.entity === "Founder") {
                    founderHtml += listItem;
                    founderMappingsExist = true;
                }
            }
        });

        cycleHtml += '</ul>';
        companyHtml += '</ul>';
        founderHtml += '</ul>';

        if (!cycleMappingsExist) {
            cycleHtml = '<h4 class="h6 fw-semibold text-primary mb-2">{{ __('admin.Program Mappings') }}:</h4><p class="text-muted ms-2 small">{{ __('admin.No cycle fields mapped.') }}</p>';
        }
        if (!companyMappingsExist) {
            companyHtml = '<h4 class="h6 fw-semibold text-primary mb-2">{{ __('admin.Company Mappings') }}:</h4><p class="text-muted ms-2 small">{{ __('admin.No company fields mapped.') }}</p>';
        }
        if (!founderMappingsExist) {
            founderHtml = '<h4 class="h6 fw-semibold text-primary mb-2">{{ __('admin.Founder Mappings') }}:</h4><p class="text-muted ms-2 small">{{ __('admin.No founder fields mapped.') }}</p>';
        }

        mappingSummaryDiv.innerHTML = cycleHtml + companyHtml + founderHtml;

        if (Object.keys(columnMappings).length === 0) {
            mappingSummaryDiv.innerHTML = '<p class="text-muted">{{ __('admin.No columns are currently mapped for import.') }}</p>';
        }
    }

    function populateDataPreview() {
        dataPreviewThead.innerHTML = '';
        dataPreviewTbody.innerHTML = '';

        if (Object.keys(columnMappings).length === 0 || uploadedData.length === 0) {
            const placeholderRow = dataPreviewTbody.insertRow();
            const cell = placeholderRow.insertCell();
            cell.colSpan = DESTINATION_FIELDS_STRUCTURED.length;
            cell.className = 'text-center text-muted';
            cell.textContent = '{{ __('admin.No data to preview') }}';
            return;
        }

        const headerRow = dataPreviewThead.insertRow();
        DESTINATION_FIELDS_STRUCTURED.forEach(dest => {
            if (columnMappings.hasOwnProperty(dest.field)) {
                const th = document.createElement('th');
                th.className = "text-start small";
                const srcIndex = columnMappings[dest.field];
                th.innerHTML = `${dest.label}<br><span class="text-primary small">({{ __('admin.Mapped to') }}: ${uploadedHeaders[srcIndex]})</span>`;
                headerRow.appendChild(th);
            }
        });

        const previewRowCount = Math.min(3, uploadedData.length);
        for (let i = 0; i < previewRowCount; i++) {
            const csvRowData = uploadedData[i];
            const tr = dataPreviewTbody.insertRow();
            DESTINATION_FIELDS_STRUCTURED.forEach(dest => {
                if (columnMappings.hasOwnProperty(dest.field)) {
                    const td = tr.insertCell();
                    td.className = "align-middle";
                    const sourceHeaderIndex = columnMappings[dest.field];
                    const sourceHeader = uploadedHeaders[sourceHeaderIndex];
                    td.textContent = csvRowData[sourceHeader] || '';
                }
            });
        }
    }

    function handleImport() {
        if (Object.keys(columnMappings).length === 0) {
            updateStatusMessage("{{ __('admin.Cannot import: No columns are mapped.') }}", 'error');
            return;
        }

        const organizationId = organizationSelect.value;
        if (!organizationId) {
            organizationError.classList.remove('d-none');
            updateStatusMessage("{{ __('admin.Please select an organization before importing.') }}", 'error', 5000);
            return;
        }

        updateStatusMessage("{{ __('admin.Importing data... Please wait.') }}", 'info');
        nextButton.classList.add('d-none');
        backButton.classList.add('d-none');
        importButton.classList.add('d-none');

        $.ajax({
            url: '{{ route('importer.import') }}',
            type: 'POST',
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: JSON.stringify({
                mappings: columnMappings,
                data: uploadedData,
                organization_id: parseInt(organizationId, 10)
            }),
            success: function(result) {
                if (result.success) {
                    updateStatusMessage(`{{ __('admin.Successfully imported') }} ${result.imported} {{ __('admin.records') }}`, 'success', 10000);
                    displayImportResult(result);
                    currentStep = 4;
                    updateSectionVisibility();
                    updateButtonStates();

                    Swal.fire({
                        title: '{{ __('admin.Import Completed') }}',
                        text: '',
                        icon: 'success',
                       	showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: "{{ __('admin.OK') }}",
                        customClass: {
                            confirmButton: 'btn btn-primary',
                        },
                        buttonsStyling: false,
                    });
                } else {
                    updateStatusMessage(result.message || '{{ __('admin.Error during import') }}', 'error');
                    nextButton.classList.remove('d-none');
                    backButton.classList.remove('d-none');
                    importButton.classList.remove('d-none');

                    Swal.fire({
                        title: '{{ __('admin.Import Failed') }}',
                        text: result.message || '',
                        icon: 'error',
                        confirmButtonText: '{{ __('admin.OK') }}',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Import AJAX error:', error, xhr.status, 'Response:', xhr.responseText);
                let errorMessage = '{{ __('admin.Error during import') }}';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || `HTTP error! Status: ${xhr.status}`;
                } catch (e) {
                    errorMessage = `HTTP error! Status: ${xhr.status} (Non-JSON response)`;
                }
                updateStatusMessage(errorMessage, 'error');
                nextButton.classList.remove('d-none');
                backButton.classList.remove('d-none');
                importButton.classList.remove('d-none');

                Swal.fire({
                    title: '{{ __('admin.Import Failed') }}',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: '{{ __('admin.OK') }}',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                }
            });
        }

        function displayImportResult(result) {
            document.getElementById('total-rows').textContent = result.total_rows || 0;
            document.getElementById('rows-attempted').textContent = result.attempted || 0;
            document.getElementById('successful-imports').textContent = result.imported || 0;
            document.getElementById('failed-rows').textContent = result.failed || 0;
            document.getElementById('skipped-rows').textContent = result.skipped || 0;
            document.getElementById('duplicates-found').textContent = result.duplicates || 0;

            const detailsDiv = document.getElementById('result-details');
            detailsDiv.innerHTML = '';

            if (result.failed_rows && result.failed_rows.length > 0) {
                detailsDiv.innerHTML += '<h6 class="mt-3">{{ __('admin.Failed Rows') }}:</h6><ul class="list-disc ps-4 text-danger">';
                result.failed_rows.forEach(row => {
                    detailsDiv.innerHTML += `<li>${row}</li>`;
                });
                detailsDiv.innerHTML += '</ul>';
            }

            if (result.skipped_rows && result.skipped_rows.length > 0) {
                detailsDiv.innerHTML += '<h6 class="mt-3">{{ __('admin.Skipped Rows') }}:</h6><ul class="list-disc ps-4 text-warning">';
                result.skipped_rows.forEach(row => {
                    detailsDiv.innerHTML += `<li>${row}</li>`;
                });
                detailsDiv.innerHTML += '</ul>';
            }

            if (result.duplicates_found && result.duplicates_found.length > 0) {
                detailsDiv.innerHTML += '<h6 class="mt-3">{{ __('admin.Duplicates Found') }}:</h6><ul class="list-disc ps-4 text-warning">';
                result.duplicates_found.forEach(row => {
                    detailsDiv.innerHTML += `<li>${row}</li>`;
                });
                detailsDiv.innerHTML += '</ul>';
            }
        }

        function updateStatusMessage(message, type = 'info', duration = 0) {
            statusMessageDiv.innerHTML = message.replace(/\n/g, '<br>');
            statusMessageDiv.className = 'mt-4 p-3 rounded';
            
            if (type === 'success') {
                statusMessageDiv.classList.add('alert', 'alert-success');
            } else if (type === 'error') {
                statusMessageDiv.classList.add('alert', 'alert-danger');
            } else {
                statusMessageDiv.classList.add('alert', 'alert-info');
            }
            
            statusMessageDiv.classList.toggle('d-none', !message);
            if (duration > 0) {
                setTimeout(() => { statusMessageDiv.classList.add('d-none'); }, duration);
            }
        }

        updateSectionVisibility();
        updateButtonStates();
    </script>
@stop