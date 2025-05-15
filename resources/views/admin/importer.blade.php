@extends('layouts.admin')

@section('style')
<style>
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #555; }
    .drag-enter { border-style: dashed; border-color: #3b82f6; background-color: #eff6ff; }
    .table-section-header th {
        background-color: #eef2ff;
        color: #3730a3;
        font-weight: 600;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #c7d2fe;
    }
</style>
@endsection

@section('content')
<body class="bg-slate-100 min-h-screen flex flex-col items-center justify-center p-4 sm:p-8 font-sans">

<div class="w-full max-w-6xl bg-white shadow-2xl rounded-xl p-6 sm:p-10 space-y-8">
    <header class="text-center">
        <h1 class="text-3xl sm:text-4xl font-bold text-slate-800 mb-2">{{ __('admin.Advanced Startup & Founder Importer') }}</h1>
        <p class="text-slate-600 text-sm sm:text-base">{{ __('admin.Upload CSV, map columns to Cycle, Company, and Founder details, and import.') }}</p>
    </header>

     <div class="text-sm text-slate-600">
        <p>{{ __('admin.Download') }} <a href="{{ $templateUrl }}" download class="text-indigo-600 font-medium hover:underline">{{ __('admin.CSV template') }}</a> {{ __('admin.text') }}</p>
    </div>
    <!-- Step 1: File Upload -->
    <section id="upload-section" class="space-y-6">
        <h2 class="text-2xl font-semibold text-slate-700 border-b pb-3 mb-5 flex items-center">
            <span class="bg-indigo-600 text-white rounded-full h-8 w-8 text-sm flex items-center justify-center mr-3">1</span>
            {{ __('admin.Upload CSV File') }}
        </h2>
        <div id="drop-zone" class="border-2 border-dashed border-slate-300 rounded-lg p-8 sm:p-12 text-center cursor-pointer hover:border-indigo-500 transition-colors duration-300 bg-slate-50">
            <input type="file" id="csv-file-input" class="hidden" accept=".csv">
            <div class="flex flex-col items-center justify-center space-y-4">
                <svg class="w-12 h-12 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l-3 3m3-3l3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.338 0 4.5 4.5 0 01-1.41 8.775H6.75z" />
                </svg>
                <p class="text-slate-700 font-medium">{{ __('admin.Drag & drop your CSV file here') }}</p>
                <p class="text-slate-500 text-sm">or</p>
                <button type="button" id="browse-button" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-300 text-sm font-medium shadow-sm">
                    {{ __('admin.Browse Files') }}
                </button>
            </div>
        </div>
        <div id="file-info" class="hidden mt-4 p-4 bg-indigo-50 border border-indigo-200 rounded-md text-sm text-indigo-700">
            <p><strong class="font-semibold">File selected:</strong> <span id="file-name"></span></p>
            <p><strong class="font-semibold">Size:</strong> <span id="file-size"></span></p>
        </div>
    </section>

    <!-- Step 2: Column Mapping -->
    <section id="mapping-section" class="space-y-6 hidden">
        <h2 class="text-2xl font-semibold text-slate-700 border-b pb-3 mb-5 flex items-center">
            <span class="bg-indigo-600 text-white rounded-full h-8 w-8 text-sm flex items-center justify-center mr-3">2</span>
            Map Columns
        </h2>
        <div class="flex justify-between items-center mb-4">
            <p class="text-slate-600 text-sm">Match your CSV columns to Cycle, Company, and Founder fields. Required fields: Company Name (English), Address (English). Note: Governorate maps to State ID.</p>
            <button id="auto-map-button" class="px-4 py-2 bg-sky-500 text-white text-sm rounded-md hover:bg-sky-600 transition-colors duration-300 font-medium shadow-sm disabled:opacity-50" disabled>
                Auto-map
            </button>
        </div>

        <div id="uploaded-headers-preview" class="mb-4 p-3 bg-slate-50 border border-slate-200 rounded-md text-sm">
            <p class="font-semibold text-slate-700 mb-1">Detected Headers from your file:</p>
            <div id="detected-headers-list" class="text-slate-600 flex flex-wrap gap-2"></div>
        </div>

        <div class="overflow-x-auto max-h-[500px] rounded-md border border-slate-200 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-1/2">{{ __('admin.Application Field (Destination)') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-1/2">{{ __('admin.Your CSV Column (Source)') }}</th>
                    </tr>
                </thead>
                <tbody id="mapping-table-body-cycle" class="bg-white divide-y divide-slate-200">
                    <tr class="table-section-header">
                        <th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-indigo-700 bg-indigo-50">{{ __('admin.Program Details') }}</th>
                    </tr>
                </tbody>
                <tbody id="mapping-table-body-company" class="bg-white divide-y divide-slate-200">
                    <tr class="table-section-header">
                        <th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-indigo-700 bg-indigo-50">{{ __('admin.Company Details') }}</th>
                    </tr>
                </tbody>
                <tbody id="mapping-table-body-founder" class="bg-white divide-y divide-slate-200">
                    <tr class="table-section-header">
                        <th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-indigo-700 bg-indigo-50">{{ __('admin.Founder Details') }}</th>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="text-xs text-slate-500 mt-2">
            * If a source column is not applicable, select "--- Do not import ---".
        </div>
    </section>

    <!-- Step 3: Review & Import -->
    <section id="review-section" class="space-y-6 hidden">
        <h2 class="text-2xl font-semibold text-slate-700 border-b pb-3 mb-5 flex items-center">
            <span class="bg-indigo-600 text-white rounded-full h-8 w-8 text-sm flex items-center justify-center mr-3">3</span>
            Review & Import
        </h2>
        <p class="text-slate-600 text-sm">Please review the mappings and select an organization before starting the import.</p>

        <!-- Organization Selection -->
        <div class="mb-4">
            <label for="organization-select" class="block text-sm font-medium text-slate-700 mb-2">{{ __('admin.Select Organization') }}</label>
            <select id="organization-select" class="w-full p-2 border border-slate-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm">
                <option value="">{{ __('admin.Select') }}</option>
            </select>
            <p id="organization-error" class="hidden text-red-600 text-sm mt-1">{{ __('Please select an organization') }}</p>
        </div>

        <div id="mapping-summary" class="p-4 bg-slate-50 border border-slate-200 rounded-md space-y-3 text-sm"></div>

        <h3 class="text-lg font-medium text-slate-700 pt-4">Sample Data Preview (First 3 Rows)</h3>
        <div id="data-preview-table-container" class="overflow-x-auto max-h-[300px] rounded-md border border-slate-200 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead id="data-preview-thead" class="bg-slate-100"></thead>
                <tbody id="data-preview-tbody" class="bg-white divide-y divide-slate-200 text-xs"></tbody>
            </table>
        </div>
    </section>

    <!-- Action Buttons -->
    <div id="action-buttons" class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3">
        <button id="back-button" type="button" class="px-6 py-2.5 border border-slate-300 text-slate-700 rounded-md hover:bg-slate-100 transition-colors duration-300 font-medium shadow-sm hidden">
            Back
        </button>
        <button id="next-button" type="button" class="px-6 py-2.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-300 font-medium shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            Next
        </button>
        <button id="import-button" type="button" class="hidden px-6 py-2.5 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-300 font-medium shadow-sm">
            Start Import
        </button>
    </div>

    <!-- Progress/Status Message -->
    <div id="status-message" class="hidden mt-6 p-4 rounded-md text-sm"></div>
</div>

<footer class="text-center text-sm text-slate-500 mt-8 pb-4">
    © 2023 Growthlabs Inc. All rights reserved.
</footer>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const DESTINATION_FIELDS_STRUCTURED = [
        { entity: "Cycle", field: "title", label: "Cycle Title" },
        { entity: "Cycle", field: "start", label: "Cycle Start Date" },
        { entity: "Cycle", field: "end", label: "Cycle End Date" },
        { entity: "Company", field: "name_en", label: "Company Name (EN) *" },
        { entity: "Company", field: "country_id", label: "Country" },
        { entity: "Company", field: "state_id", label: "State/Governorate" },
        { entity: "Company", field: "industries", label: "Industries" },
        { entity: "Company", field: "sector", label: "Sector" },
        { entity: "Company", field: "address_en", label: "Address (EN) *" },
        { entity: "Company", field: "years_of_operations", label: "Years of Operations" },
        { entity: "Company", field: "product_or_service", label: "Product or Service" },
        { entity: "Company", field: "targeted_market", label: "Targeted Market" },
        { entity: "Company", field: "est_date", label: "Establishment Date" },
        { entity: "Founder", field: "first_name_en", label: "First Name (EN)" },
        { entity: "Founder", field: "last_name_en", label: "Last Name (EN)" },
        { entity: "Founder", field: "gender", label: "Gender" },
        { entity: "Founder", field: "phone", label: "Phone" },
        { entity: "Founder", field: "email", label: "Email" },
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
    const mappingTableBodyCycle = document.getElementById('mapping-table-body-cycle');
    const mappingTableBodyCompany = document.getElementById('mapping-table-body-company');
    const mappingTableBodyFounder = document.getElementById('mapping-table-body-founder');
    const autoMapButton = document.getElementById('auto-map-button');
    const detectedHeadersList = document.getElementById('detected-headers-list');
    const mappingSummaryDiv = document.getElementById('mapping-summary');
    const dataPreviewThead = document.getElementById('data-preview-thead');
    const dataPreviewTbody = document.getElementById('data-preview-tbody');
    const nextButton = document.getElementById('next-button');
    const backButton = document.getElementById('back-button');
    const importButton = document.getElementById('import-button');
    const statusMessageDiv = document.getElementById('status-message');
    const organizationSelect = document.getElementById('organization-select');
    const organizationError = document.getElementById('organization-error');

    let currentStep = 1;
    let uploadedFile = null;
    let uploadedHeaders = [];
    let uploadedData = [];
    let columnMappings = {};

    browseButton.addEventListener('click', () => fileInput.click());
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
    autoMapButton.addEventListener('click', autoMapColumns);
    nextButton.addEventListener('click', handleNextStep);
    backButton.addEventListener('click', handlePreviousStep);
    importButton.addEventListener('click', handleImport);

    // Fetch organizations when entering Step 3
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
            organizationSelect.innerHTML = '<option value="">-- Select an Organization --</option>';
            data.organizations.forEach(org => {
                const option = document.createElement('option');
                option.value = org.id;
                option.textContent = org.name_en || org.name_ar || `Organization ${org.id}`;
                organizationSelect.appendChild(option);
            });
        })
        .catch(error => {
            alert('Fetch organizations error:', error);
            updateStatusMessage('Failed to load organizations: ' + error.message, 'error');
            organizationSelect.innerHTML = '<option value="">Error loading organizations</option>';
        });
    }

    function handleFile(file) {
        if (file && file.type === "text/csv") {
            uploadedFile = file;
            fileNameSpan.textContent = file.name;
            fileSizeSpan.textContent = `${(file.size / 1024).toFixed(2)} KB`;
            fileInfoDiv.classList.remove('hidden');
            nextButton.disabled = false;

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
                console.log('Upload response data:', data);
                if (data.success) {
                    uploadedHeaders = data.headers;
                    uploadedData = data.data;
                    displayDetectedHeaders();
                    populateMappingTable();
                    autoMapButton.disabled = false;
                    updateStatusMessage('File uploaded successfully.', 'success', 3000);
                } else {
                    updateStatusMessage(data.message || 'Error uploading file.', 'error');
                    resetUploadState();
                }
            })
            .catch(error => {
                console.error('Upload fetch error:', error.message);
                updateStatusMessage('Error uploading file: ' + error.message, 'error');
                resetUploadState();
            });
        } else {
            alert("Please upload a valid CSV file.");
            resetUploadState();
        }
    }

    function resetUploadState() {
        uploadedFile = null;
        uploadedHeaders = [];
        uploadedData = [];
        columnMappings = {};
        fileInput.value = '';
        fileInfoDiv.classList.add('hidden');
        fileNameSpan.textContent = '';
        fileSizeSpan.textContent = '';
        nextButton.disabled = true;
        autoMapButton.disabled = true;
        mappingTableBodyCycle.innerHTML = '<tr class="table-section-header"><th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-indigo-700 bg-indigo-50">Program Details</th></tr>';
        mappingTableBodyCompany.innerHTML = '<tr class="table-section-header"><th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-indigo-700 bg-indigo-50">Company Details</th></tr>';
        mappingTableBodyFounder.innerHTML = '<tr class="table-section-header"><th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-indigo-700 bg-indigo-50">Founder Details</th></tr>';
        detectedHeadersList.innerHTML = '';
        updateStatusMessage('', 'info', 1);
        organizationSelect.innerHTML = '<option value="">-- Select an Organization --</option>';
    }

    function displayDetectedHeaders() {
        detectedHeadersList.innerHTML = '';
        if (uploadedHeaders.length > 0) {
            uploadedHeaders.forEach(header => {
                const span = document.createElement('span');
                span.className = 'px-2 py-1 bg-slate-200 text-slate-700 rounded text-xs';
                span.textContent = header;
                detectedHeadersList.appendChild(span);
            });
        } else {
            detectedHeadersList.textContent = 'No headers detected or file is empty.';
        }
    }

    function populateMappingTable() {
        mappingTableBodyCycle.innerHTML = '<tr class="table-section-header"><th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-indigo-700 bg-indigo-50">Cycle Details</th></tr>';
        mappingTableBodyCompany.innerHTML = '<tr class="table-section-header"><th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-indigo-700 bg-indigo-50">Company Details</th></tr>';
        mappingTableBodyFounder.innerHTML = '<tr class="table-section-header"><th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-indigo-700 bg-indigo-50">Founder Details</th></tr>';

        const doNotImportOption = "<option value=''>--- Do not import ---</option>";
        const sourceHeaderOptions = uploadedHeaders.map((header, index) => 
            `<option value="${index}">${header}</option>`
        ).join('');

        DESTINATION_FIELDS_STRUCTURED.forEach(dest => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">${dest.label} <span class="text-xs text-slate-400">(${dest.entity})</span></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                    <select data-destination-key="${dest.field}" data-destination-label="${dest.label}" data-entity="${dest.entity}" class="mapping-select w-full p-2 border border-slate-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm">
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
            'title': ['program name', 'cycle name', 'program title'],
            'start': ['program start date', 'start date'],
            'end': ['program end date', 'end date'],
            'name_en': ['startup name', 'company name', 'business name', 'name'],
            'address_en': ['address', 'location', 'company address'],
            'country_id': ['country', 'country name'],
            'state_id': ['governorate', 'state', 'region'],
            'industries': ['industry', 'industries'],
            'sector': ['sector'],
            'years_of_operations': ['years of operations', 'years'],
            'product_or_service': ['product or service', 'product', 'service'],
            'targeted_market': ['target markets', 'market'],
            'est_date': ['establishment date', 'founded date'],
            'first_name_en': ['founder name', 'first name'],
            'last_name_en': ['founder last name', 'last name'],
            'gender': ['gender'],
            'phone': ['phone', 'founder phone'],
            'email': ['e-mail', 'email', 'founder email']
        };

        const selects = document.querySelectorAll('.mapping-select');
        selects.forEach(select => {
            const destLabel = select.dataset.destinationLabel.toLowerCase().replace(/[^a-z0-9]/gi, '');
            const destKey = select.dataset.destinationKey;
            let bestMatchIndex = -1;

            const possibleMatches = AUTO_MAP_DICTIONARY[destKey] || [];
            uploadedHeaders.forEach((sourceHeader, index) => {
                const srcHeaderNormalized = sourceHeader.toLowerCase().replace(/[^a-z0-9]/gi, '');
                if (destLabel === srcHeaderNormalized || possibleMatches.includes(sourceHeader.toLowerCase())) {
                    bestMatchIndex = index;
                }
            });

            select.value = (bestMatchIndex !== -1) ? bestMatchIndex.toString() : '';
        });
        updateStatusMessage('Auto-mapping completed. Please review the mappings.', 'info', 3000);
    }

    function updateButtonStates() {
        backButton.classList.toggle('hidden', currentStep === 1);
        nextButton.classList.toggle('hidden', currentStep === 3);
        importButton.classList.toggle('hidden', currentStep !== 3);
        nextButton.disabled = (currentStep === 1 && !uploadedFile);
    }

    function updateSectionVisibility() {
        uploadSection.classList.toggle('hidden', currentStep !== 1);
        mappingSection.classList.toggle('hidden', currentStep !== 2);
        reviewSection.classList.toggle('hidden', currentStep !== 3);
        if (currentStep === 3) {
            fetchOrganizations();
        }
    }

    function handleNextStep() {
        if (currentStep === 1 && (!uploadedFile || uploadedHeaders.length === 0)) {
            alert(uploadedFile ? "Uploaded file seems empty or lacks headers." : "Please upload a file first.");
            return;
        }
        if (currentStep === 2) {
            collectMappings();
            populateMappingSummary();
            populateDataPreview();
            if (Object.keys(columnMappings).length === 0) {
                if (!confirm("No columns are mapped. Proceed to review anyway? You won't be able to import data.")) return;
            }
            const requiredFields = ['name_en', 'address_en'];
            const missingRequired = requiredFields.filter(field => !columnMappings.hasOwnProperty(field));
            if (missingRequired.length > 0) {
                updateStatusMessage(`Please map the following required fields: ${missingRequired.join(', ')}`, 'error', 5000);
                return;
            }
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
            organizationError.classList.add('hidden');
        }
    }

    function collectMappings() {
        columnMappings = {};
        document.querySelectorAll('.mapping-select').forEach(select => {
            const destKey = select.dataset.destinationKey;
            const sourceHeaderIndex = select.value;
            if (sourceHeaderIndex !== '') {
                columnMappings[destKey] = parseInt(sourceHeaderIndex, 10);
            }
        });
    }

    function populateMappingSummary() {
        mappingSummaryDiv.innerHTML = '';
        let cycleHtml = '<h4 class="text-md font-semibold text-indigo-700 mb-2">Cycle Mappings:</h4><ul class="list-disc list-inside space-y-1 text-slate-600 pl-4">';
        let companyHtml = '<h4 class="text-md font-semibold text-indigo-700 mt-4 mb-2">Company Mappings:</h4><ul class="list-disc list-inside space-y-1 text-slate-600 pl-4">';
        let founderHtml = '<h4 class="text-md font-semibold text-indigo-700 mt-4 mb-2">Founder Mappings:</h4><ul class="list-disc list-inside space-y-1 text-slate-600 pl-4">';
        let cycleMappingsExist = false;
        let companyMappingsExist = false;
        let founderMappingsExist = false;

        DESTINATION_FIELDS_STRUCTURED.forEach(dest => {
            if (columnMappings.hasOwnProperty(dest.field)) {
                const srcIndex = columnMappings[dest.field];
                const sourceColumn = uploadedHeaders[srcIndex] || 'Unknown';
                const listItem = `<li><span class="font-semibold text-slate-800">${dest.label}</span> → <span class="text-indigo-600 font-medium">${sourceColumn}</span></li>`;
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
            cycleHtml = '<h4 class="text-md font-semibold text-indigo-700 mb-2">Cycle Mappings:</h4><p class="text-slate-500 pl-4">No cycle fields mapped.</p>';
        }
        if (!companyMappingsExist) {
            companyHtml = '<h4 class="text-md font-semibold text-indigo-700 mt-4 mb-2">Company Mappings:</h4><p class="text-slate-500 pl-4">No company fields mapped.</p>';
        }
        if (!founderMappingsExist) {
            founderHtml = '<h4 class="text-md font-semibold text-indigo-700 mt-4 mb-2">Founder Mappings:</h4><p class="text-slate-500 pl-4">No founder fields mapped.</p>';
        }

        mappingSummaryDiv.innerHTML = cycleHtml + companyHtml + founderHtml;

        if (Object.keys(columnMappings).length === 0) {
            mappingSummaryDiv.innerHTML = '<p class="text-slate-500">No columns are currently mapped for import.</p>';
        }
    }

    function populateDataPreview() {
        dataPreviewThead.innerHTML = '';
        dataPreviewTbody.innerHTML = '';

        if (Object.keys(columnMappings).length === 0 || uploadedData.length === 0) {
            const placeholderRow = dataPreviewTbody.insertRow();
            const cell = placeholderRow.insertCell();
            cell.colSpan = DESTINATION_FIELDS_STRUCTURED.length;
            cell.className = 'px-6 py-4 text-center text-slate-500';
            cell.textContent = 'No data to preview (no mappings or no data).';
            return;
        }

        const headerRow = dataPreviewThead.insertRow();
        headerRow.className = "bg-slate-100";

        DESTINATION_FIELDS_STRUCTURED.forEach(dest => {
            if (columnMappings.hasOwnProperty(dest.field)) {
                const th = document.createElement('th');
                th.className = "px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider";
                const srcIndex = columnMappings[dest.field];
                th.innerHTML = `${dest.label}<br><span class="text-indigo-500 normal-case text-[10px]">(Mapped to: ${uploadedHeaders[srcIndex]})</span>`;
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
                    td.className = "px-4 py-3 whitespace-nowrap text-slate-700";
                    const sourceHeaderIndex = columnMappings[dest.field];
                    const sourceHeader = uploadedHeaders[sourceHeaderIndex];
                    td.textContent = csvRowData[sourceHeader] || '';
                }
            });
        }
    }

    function handleImport() {
        if (Object.keys(columnMappings).length === 0) {
            updateStatusMessage("Cannot import: No columns are mapped.", 'error');
            return;
        }

        const organizationId = organizationSelect.value;
        if (!organizationId) {
            organizationError.classList.remove('hidden');
            updateStatusMessage("Please select an organization before importing.", 'error', 5000);
            return;
        }

        updateStatusMessage("Importing data... Please wait.", 'info');
        nextButton.classList.add('hidden');
        backButton.classList.add('hidden');
        importButton.classList.add('hidden');

        // console.log('Initiating import with mappings:', columnMappings);
        // console.log('Selected organization_id:', organizationId);
        // console.log('Data (first 3 rows):', uploadedData.slice(0, 3));
        // console.log('Total data rows:', uploadedData.length);

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
                    let message = `Successfully imported ${result.imported} records!`;
                    updateStatusMessage(message, 'success', 10000);
                    setTimeout(() => {
                        currentStep = 1;
                        updateSectionVisibility();
                        updateButtonStates();
                        resetUploadState();
                    }, 3000);
                } else {
                    updateStatusMessage(result.message || 'Error during import.', 'error');
                    nextButton.classList.remove('hidden');
                    backButton.classList.remove('hidden');
                    importButton.classList.remove('hidden');
                }
            },
            error: function(xhr, status, error) {
                console.error('Import AJAX error:', error, xhr.status, 'Response:', xhr.responseText);
                let errorMessage = 'Error during import.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || `HTTP error! Status: ${xhr.status}`;
                } catch (e) {
                    errorMessage = `HTTP error! Status: ${xhr.status} (Non-JSON response)`;
                }
                updateStatusMessage(errorMessage, 'error');
                nextButton.classList.remove('hidden');
                backButton.classList.remove('hidden');
                importButton.classList.remove('hidden');
            }
        });
    }

    function updateStatusMessage(message, type = 'info', duration = 0) {
        statusMessageDiv.innerHTML = message.replace(/\n/g, '<br>');
        statusMessageDiv.className = 'mt-6 p-4 rounded-md text-sm';
        if (type === 'success') statusMessageDiv.classList.add('bg-green-100', 'text-green-700');
        else if (type === 'error') statusMessageDiv.classList.add('bg-red-100', 'text-red-700');
        else statusMessageDiv.classList.add('bg-indigo-100', 'text-indigo-700');
        statusMessageDiv.classList.toggle('hidden', !message);
        if (duration > 0) {
            setTimeout(() => { statusMessageDiv.classList.add('hidden'); }, duration);
        }
    }

    updateSectionVisibility();
    updateButtonStates();
</script>

</body>
@endsection