<div class="row">
  <!-- Milestone Cards Container -->
  <div id="milestones-container" class="col-12">
    <!-- Template for a single milestone card (will be cloned by JS) -->
    <template id="milestone-card-template">
      <div class="card shadow-sm border mb-3 milestone-card">
        <div class="card-body">
          <!-- Card Header - Using Bootstrap's flex utilities for alignment -->
          <h6 class="border-bottom d-flex pb-2 align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 flex-grow-1 w-100">
              <div 
                class="badge bg-primary rounded-circle w-px-30 h-px-30 d-flex justify-content-center align-items-center milestone-number">
                <!-- Milestone number goes here -->
              </div>
              <h5 class="card-title mb-0 me-2 text-truncate milestone-title-display w-50">Milestone Name</h5>
            </div>
            <div class="milestone-header-actions d-flex gap-2">
              <!-- Remove button for unsaved cards (initially hidden by JS) -->
              <button type="button" class="btn btn-sm btn-label-danger remove-unsaved-milestone-btn d-none">
                <i class="ti ti-trash me-1"></i>{{ __('admin.Remove') }}
              </button>
              <!-- Delete button for saved cards (initially hidden by JS) -->
              <button type="button" class="btn btn-sm btn-label-danger delete-milestone-btn d-none">
                <i class="ti ti-trash me-1"></i>{{ __('admin.Delete') }}
              </button>
            </div>
          </h6>

          <!-- Form for adding/editing a milestone -->
          <div class="milestone-form-section">
            <div class="mb-3">
              <label for="milestone-title-input" class="form-label mb-1">{{ __('admin.Milestone_Name') }}</label>
              <input 
                type="text" 
                id="milestone-title-input" 
                class="form-control milestone-title" 
                maxlength="300"
                placeholder="{{ __('admin.Milestone_Name') }}"
              />
              <div class="invalid-feedback milestone-title-error"></div>
            </div>
            <div class="mb-3">
              <label for="milestone-description-textarea" class="form-label mb-1">{{ __('admin.Description') }}</label>
              <textarea 
                id="milestone-description-textarea" 
                class="form-control milestone-description" 
                rows="3"
                maxlength="1000" 
                placeholder="{{ __('admin.Description') }}"
              ></textarea>
              <div class="invalid-feedback milestone-description-error"></div>
            </div>
            <div class="mb-3">
              <label for="milestone-completion-input" class="form-label mb-1">
                {{ $l == 'ar' ? 'نسبة الإكتمال (%)' : 'Completion Percentage (%)' }}
              </label>
              <input 
                type="number" 
                id="milestone-completion-input" 
                class="form-control milestone-completion" 
                min="0"
                max="100" 
                placeholder="0-100"
              />
              <div class="invalid-feedback milestone-completion-error"></div>
            </div>
            <div class="mb-3">
              <label for="milestone-documents-input" class="form-label mb-1">
                {{ $l == 'ar' ? 'المستندات الداعمة' : 'Supporting Documents' }}
              </label>
              <input 
                type="file" 
                id="milestone-documents-input" 
                class="form-control milestone-documents" 
                multiple
                accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
              />
              <div class="invalid-feedback milestone-documents-error"></div>
              <small class="form-text text-muted">
                {{ $l == 'ar' ? 'تنسيق الملفات المسموح به: PDF، JPG، PNG، DOCX، و XLSX. الحد الأقصى للحجم = 10 ميجابايت لكل ملف.' : 'Allowed file formats: PDF, JPG, PNG, DOCX, and XLSX. Max size = 10 MB per file.' }}
              </small>
              <div class="uploaded-documents mt-2">
                <!-- Uploaded files will be listed here by JS -->
              </div>
            </div>
            <div class="d-flex w-100 gap-2 justify-content-end">
              <button type="button" class="btn btn-primary submit-milestone-btn">{{ __('admin.Save') }}</button>
              <button type="button" class="btn btn-label-secondary cancel-edit-btn d-none">{{ __('admin.Cancel') }}</button>
            </div>
          </div>

          <!-- Display section for submitted milestones -->
          <div class="milestone-display-section d-none">
            <h6 class="mb-2 milestone-title-display-preview"></h6>
            <p class="text-muted line-clamp-2 fs-6 milestone-description-display"></p>
            <div class="mb-3 d-flex align-items-center gap-4 mb-4 pb-4 border-bottom">
              <span>{{ $l == 'ar' ? 'النسبة المئوية لإكتمال' : 'Completion Percentage' }}:</span>
              <span class="milestone-completion-display fw-bold badge bg-label-secondary text-dark">0%</span>
            </div>
            <div class="supporting-documents-display mb-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <strong>{{ $l == 'ar' ? 'المستندات الداعمة' : 'Supporting Documents' }}:</strong>
                <span class="badge bg-light text-muted milestone-documents-count">0</span>
              </div>
              <ul class="list-unstyled uploaded-files-list">
                <!-- Display uploaded file names with download links -->
              </ul>
              <div class="text-muted milestone-no-documents d-none">
                <small>{{ $l == 'ar' ? 'لا توجد مستندات مرفقة' : 'No documents attached' }}</small>
              </div>
            </div>
            <div class="d-flex w-100 gap-2 justify-content-end">
              <button type="button" class="btn btn-sm btn-label-primary edit-milestone-btn">
                <i class="ti ti-pencil me-2"></i>{{ __('admin.Edit') }}
              </button>
              <!-- The delete button has been moved to the header and is controlled by JS -->
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Dynamic Milestone Cards will be inserted here by JavaScript -->
  </div>

  <!-- Add New Milestone Button (always visible) -->
  <div class="col-12 text-center mt-4">
    <button id="add-milestone-btn" class="btn btn-outline-primary">
      <i class="ti ti-plus me-2"></i>{{ $l == 'ar' ? 'إضافة إنجاز جديد' : 'Add New Milestone' }}
    </button>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const milestonesContainer = document.getElementById('milestones-container');
  const addMilestoneBtn = document.getElementById('add-milestone-btn');
  const milestoneCardTemplate = document.getElementById('milestone-card-template');
  let nextMilestoneId = 1;
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  
  @if(auth()->user() && auth()->user()->company)
    const companyId = {{ auth()->user()->company->id }};
  @else
    const companyId = null;
  @endif

  // Constants for validation messages and allowed file types
  const MESSAGES = {
    titleRequired: "{{ $l == 'ar' ? 'يرجى إدخال اسم الإنجاز.' : 'Please enter a milestone title.' }}",
    descriptionRequired: "{{ $l == 'ar' ? 'يرجى إدخال وصف الإنجاز.' : 'Please enter a milestone description.' }}",
    completionRequired: "{{ $l == 'ar' ? 'يرجى إدخال النسبة المئوية للإكمال.' : 'Please enter a completion percentage.' }}",
    completionRange: "{{ $l == 'ar' ? 'النسبة المئوية للإكمال يجب أن تكون بين 0 و 100.' : 'Completion percentage must be between 0 and 100.' }}",
    fileType: "{{ $l == 'ar' ? 'يُسمح فقط بملفات PDF وJPG وPNG وDOCX وXLSX.' : 'Only PDF, JPG, PNG, DOCX, and XLSX files are allowed.' }}",
    fileSize: "{{ $l == 'ar' ? 'حجم الملف كبير جداً. الحد الأقصى المسموح به 10 ميجابايت.' : 'File too large. Maximum size allowed is 10 MB.' }}",
    saving: "{{ $l == 'ar' ? 'جاري الحفظ...' : 'Saving...' }}",
    success: "{{ $l == 'ar' ? 'تم تقديم الإنجاز بنجاح.' : 'Milestone submitted successfully.' }}",
    deleteSuccess: "{{ $l == 'ar' ? 'تم حذف الإنجاز بنجاح.' : 'Your milestone has been deleted successfully.' }}",
    deleteConfirm: "{{ $l == 'ar' ? 'أنت على وشك حذف هذا الإنجاز نهائيًا. لا يمكن التراجع عن هذا الإجراء.' : 'You are about to permanently delete this milestone. This action cannot be undone.' }}",
    documentDeleteConfirm: "{{ $l == 'ar' ? 'أنت على وشك حذف هذا المستند. لا يمكن التراجع عن هذا الإجراء.' : 'You are about to delete this document. This action cannot be undone.' }}",
    error: "{{ $l == 'ar' ? 'حدث خطأ. يرجى المحاولة مرة أخرى.' : 'An error occurred. Please try again.' }}",
    deleteError: "{{ $l == 'ar' ? 'فشل في حذف الإنجاز. يرجى المحاولة مرة أخرى.' : 'Failed to delete milestone. Please try again.' }}"
  };

  const ALLOWED_FILE_TYPES = ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
  const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB

  function loadExistingMilestones() {
    @if(auth()->user() && auth()->user()->company)
      @if(auth()->user()->company->milestones && auth()->user()->company->milestones->count() > 0)
        const existingMilestones = @json(auth()->user()->company->milestones);
        
        existingMilestones.forEach((milestone, index) => {
          const milestoneCard = createMilestoneCard(index === 0, milestone);
          populateMilestoneCard(milestoneCard, milestone);
        });
        updateMilestoneNumbers();
      @else
        createMilestoneCard(true);
      @endif
    @else
      createMilestoneCard(true);
    @endif
  }

  // Utility functions
  function parseDocuments(documents) {
    if (typeof documents === 'string') {
      try {
        documents = JSON.parse(documents);
      } catch (e) {
        console.warn('Failed to parse milestone documents JSON:', e);
        return [];
      }
    }
    return Array.isArray(documents) ? documents : [];
  }

  function getFileIcon(fileName) {
    const extension = fileName.split('.').pop().toLowerCase();
    const iconMap = {
      pdf: 'ti ti-file-type-pdf text-danger',
      jpg: 'ti ti-photo text-info',
      jpeg: 'ti ti-photo text-info',
      png: 'ti ti-photo text-info',
      docx: 'ti ti-file-type-docx text-primary',
      doc: 'ti ti-file-type-docx text-primary',
      xlsx: 'ti ti-file-type-xls text-success',
      xls: 'ti ti-file-type-xls text-success'
    };
    return iconMap[extension] || 'ti ti-file';
  }

  function populateMilestoneCard(card, milestone) {
    Object.assign(card.dataset, { milestoneId: milestone.id, isNew: false });
    card.milestoneData = milestone;
    
    // Toggle display mode
    card.querySelector('.milestone-form-section').classList.add('d-none');
    card.querySelector('.milestone-display-section').classList.remove('d-none');
    
    // Button visibility
    card.querySelector('.remove-unsaved-milestone-btn').classList.add('d-none');
    card.querySelector('.delete-milestone-btn').classList.remove('d-none');
    
    // Populate text data
    card.querySelector('.milestone-description-display').textContent = milestone.description;
    const titleDisplayPreview = card.querySelector('.milestone-title-display-preview');
    if (titleDisplayPreview) titleDisplayPreview.textContent = milestone.name;
    
    const completionPercentage = milestone.completion_percentage || 0;
    card.querySelector('.milestone-completion-display').textContent = `${completionPercentage}%`;
    
    // Handle documents
    const documents = parseDocuments(milestone.documents);
    const uploadedFilesList = card.querySelector('.uploaded-files-list');
    const documentsCount = card.querySelector('.milestone-documents-count');
    const noDocumentsDiv = card.querySelector('.milestone-no-documents');
    
    uploadedFilesList.innerHTML = '';
    documentsCount.textContent = documents.length;
    
    if (documents.length > 0) {
      noDocumentsDiv.classList.add('d-none');
      documents.forEach(doc => {
        const listItem = document.createElement('li');
        listItem.className = 'mb-2';
        listItem.innerHTML = `
          <a href="${doc.url}" target="_blank" class="d-flex align-items-center gap-2 bg-label-secondary rounded p-2">
            <i class="${getFileIcon(doc.name)}"></i>
            <span class="text-decoration-none flex-grow-1">${doc.name}</span>
            <i class="ti ti-external-link text-muted"></i>
          </a>
        `;
        uploadedFilesList.appendChild(listItem);
      });
    } else {
      noDocumentsDiv.classList.remove('d-none');
    }
    
    addValidationClearListeners(card);
  }

  function createMilestoneCard(isInitialCard = false, existingData = null) {
    const newCard = milestoneCardTemplate.content.cloneNode(true).querySelector('.milestone-card');
    
    const currentMilestoneId = existingData ? existingData.id : `temp_${nextMilestoneId++}`;
    Object.assign(newCard.dataset, {
      milestoneId: currentMilestoneId,
      isNew: !existingData
    });

    if (isInitialCard) {
      newCard.classList.add('initial-milestone-card');
      newCard.dataset.isNew = false;
    }

    // Set initial content
    const milestoneText = '{{ $l == 'ar' ? 'إنجاز' : 'Milestone' }}';
    const milestoneNumber = milestonesContainer.children.length + 1;
    const milestoneTitle = `${milestoneText} ${milestoneNumber}`;
    
    newCard.querySelector('.milestone-number').textContent = milestoneNumber;
    newCard.querySelector('.milestone-title').placeholder = `{{ __('admin.Milestone_Name') }}`;
    newCard.querySelector('.milestone-title-display').textContent = milestoneTitle;

    // Button visibility
    const removeBtn = newCard.querySelector('.remove-unsaved-milestone-btn');
    const deleteBtn = newCard.querySelector('.delete-milestone-btn');
    
    removeBtn.classList.toggle('d-none', isInitialCard);
    deleteBtn.classList.add('d-none');

    milestonesContainer.appendChild(newCard);
    updateMilestoneNumbers();
    addValidationClearListeners(newCard);
    
    return newCard;
  }

  function updateMilestoneNumbers() {
    const milestoneText = '{{ $l == 'ar' ? 'إنجاز' : 'Milestone' }}';
    document.querySelectorAll('.milestone-card').forEach((card, index) => {
      const number = index + 1;
      card.querySelector('.milestone-number').textContent = number;
      const titleDisplay = card.querySelector('.milestone-title-display');
      if (titleDisplay) titleDisplay.textContent = `${milestoneText} ${number}`;
    });
  }

  function clearValidation(card) {
    card.querySelectorAll('.form-control').forEach(input => input.classList.remove('is-invalid'));
    card.querySelectorAll('.invalid-feedback').forEach(errorDiv => errorDiv.textContent = '');
  }

  function showValidationError(inputElement, errorDiv, message) {
    if (inputElement) inputElement.classList.add('is-invalid');
    if (errorDiv) errorDiv.textContent = message;
  }

  function clearSingleValidation(inputElement) {
    if (!inputElement) return;
    
    inputElement.classList.remove('is-invalid');
    const card = inputElement.closest('.milestone-card');
    if (!card) return;

    const errorClassMap = {
      'milestone-title': '.milestone-title-error',
      'milestone-description': '.milestone-description-error',
      'milestone-completion': '.milestone-completion-error',
      'milestone-documents': '.milestone-documents-error'
    };
    
    for (const [inputClass, errorClass] of Object.entries(errorClassMap)) {
      if (inputElement.classList.contains(inputClass)) {
        const errorDiv = card.querySelector(errorClass);
        if (errorDiv) errorDiv.textContent = '';
        break;
      }
    }
  }

  function addValidationClearListeners(card) {
    const inputs = card.querySelectorAll('.milestone-title, .milestone-description, .milestone-completion, .milestone-documents');
    inputs.forEach(input => {
      ['input', 'change', 'keyup'].forEach(eventType => {
        input.addEventListener(eventType, () => clearSingleValidation(input));
      });
    });
  }

  function handleServerValidationErrors(card, errors) {
    const fieldMap = {
      name: { errorSelector: '.milestone-title-error', inputSelector: '.milestone-title' },
      description: { errorSelector: '.milestone-description-error', inputSelector: '.milestone-description' },
      completion_percentage: { errorSelector: '.milestone-completion-error', inputSelector: '.milestone-completion' },
      documents: { errorSelector: '.milestone-documents-error', inputSelector: '.milestone-documents' }
    };

    Object.entries(errors).forEach(([field, messages]) => {
      const fieldKey = field.startsWith('documents.') ? 'documents' : field;
      const mapping = fieldMap[fieldKey];
      
      if (mapping) {
        const errorElement = card.querySelector(mapping.errorSelector);
        const inputElement = card.querySelector(mapping.inputSelector);
        if (errorElement && inputElement) {
          showValidationError(inputElement, errorElement, messages[0]);
        }
      }
    });
  }

  function handleSuccessfulSubmission(card, data, titleInput, descriptionInput, completionInput) {
    // Switch to display mode
    card.querySelector('.milestone-form-section').classList.add('d-none');
    card.querySelector('.milestone-display-section').classList.remove('d-none');

    // Update card state
    card.dataset.isNew = false;
    if (data.milestone?.id) card.dataset.milestoneId = data.milestone.id;

    // Update button visibility
    card.querySelector('.remove-unsaved-milestone-btn').classList.add('d-none');
    card.querySelector('.delete-milestone-btn').classList.remove('d-none');

    // Update display content
    card.querySelector('.milestone-description-display').textContent = descriptionInput.value;
    const titleDisplayPreview = card.querySelector('.milestone-title-display-preview');
    if (titleDisplayPreview) titleDisplayPreview.textContent = titleInput.value;
    
    const completionPercentage = parseInt(completionInput.value);
    card.querySelector('.milestone-completion-display').textContent = `${completionPercentage}%`;

    // Update documents list
    if (data.milestone?.documents) {
      const documents = parseDocuments(data.milestone.documents);
      const uploadedFilesList = card.querySelector('.uploaded-files-list');
      uploadedFilesList.innerHTML = '';
      
      documents.forEach(doc => {
        const listItem = document.createElement('li');
        listItem.className = 'mb-2';
        listItem.innerHTML = `
          <a href="${doc.url}" target="_blank" class="d-flex align-items-center gap-2 bg-label-secondary rounded p-2">
            <i class="${getFileIcon(doc.name)}"></i>
            <span class="text-decoration-none flex-grow-1">${doc.name}</span>
            <i class="ti ti-external-link text-muted"></i>
          </a>
        `;
        uploadedFilesList.appendChild(listItem);
      });
    }

    releaseSuccessAlert({
      title: '{{ __('admin.Success!') }}',
      message: data.state || MESSAGES.success
    });
  }

  function handleMilestoneDelete(card) {
    const isLastCard = document.querySelectorAll('.milestone-card').length === 1;
    
    Swal.fire({
      title: "{{ __('Are you sure?') }}",
      text: MESSAGES.deleteConfirm,
      icon: 'warning',
      showCancelButton: true,
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-label-secondary'
      },
      confirmButtonText: "{{ __('admin.DYes') }}",
      cancelButtonText: "{{ __('Cancel') }}"
    }).then((result) => {
      if (result.isConfirmed) {
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('id', card.dataset.milestoneId);
        
        fetch(`{{ route('company.profile_milestones_delete', '') }}/${companyId}`, {
          method: 'POST',
          body: formData,
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(response => {
          if (!response.ok) {
            return response.json().then(data => {
              const errorMsg = data.errors?.general?.[0] || data.errors?.milestone?.[0] || 'Server error';
              throw new Error(errorMsg);
            });
          }
          return response.json();
        })
        .then(data => {
          if (data.success || data.state) {
            card.remove();
            if (isLastCard) createMilestoneCard(true);
            else updateMilestoneNumbers();
            
            releaseSuccessAlert({
              title: "{{ __('admin.Deleted') }}",
              message: MESSAGES.deleteSuccess
            });
          } else {
            throw new Error(MESSAGES.deleteError);
          }
        })
        .catch(error => {
          releaseFailureAlert({
            title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
            message: error.message || MESSAGES.deleteError
          });
        });
      }
    });
  }

  function populateExistingDocumentsInForm(card) {
    const uploadedDocumentsContainer = card.querySelector('.uploaded-documents');
    uploadedDocumentsContainer.innerHTML = '';
    
    const milestone = card.milestoneData;
    if (!milestone?.documents) return;
    
    const documents = parseDocuments(milestone.documents);
    
    if (documents.length > 0) {
      const documentsTitle = document.createElement('small');
      documentsTitle.className = 'form-text text-muted mb-2 d-block';
      documentsTitle.textContent = '{{ $l == "ar" ? "الملفات الحالية:" : "Current files:" }}';
      uploadedDocumentsContainer.appendChild(documentsTitle);
      
      documents.forEach((doc, index) => {
        const docElement = document.createElement('div');
        docElement.className = 'existing-document d-flex align-items-center justify-content-between p-2 mb-2 border rounded';
        docElement.dataset.documentIndex = index;
        
        docElement.innerHTML = `
          <div class="d-flex align-items-center">
            <i class="ti ti-file me-2"></i>
            <a href="${doc.url}" target="_blank" class="text-decoration-none">${doc.name}</a>
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger remove-document-btn">
            <i class="ti ti-trash"></i>
          </button>
        `;
        
        uploadedDocumentsContainer.appendChild(docElement);
      });
    }
  }

  function handleDocumentRemoval(removeBtn) {
    const documentElement = removeBtn.closest('.existing-document');
    
    Swal.fire({
      title: "{{ __('Are you sure?') }}",
      text: MESSAGES.documentDeleteConfirm,
      icon: 'warning',
      showCancelButton: true,
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-label-secondary'
      },
      confirmButtonText: "{{ __('admin.DYes') }}",
      cancelButtonText: "{{ __('Cancel') }}"
    }).then((result) => {
      if (result.isConfirmed) {
        documentElement.classList.add('document-to-remove');
        Object.assign(documentElement.style, {
          opacity: '0.5',
          textDecoration: 'line-through'
        });
        
        removeBtn.innerHTML = '<i class="ti ti-rotate-clockwise"></i>';
        removeBtn.className = 'btn btn-sm btn-outline-secondary restore-document-btn';
        removeBtn.title = '{{ $l == "ar" ? "استعادة المستند" : "Restore document" }}';
      }
    });
  }

  function handleDocumentRestore(restoreBtn) {
    const documentElement = restoreBtn.closest('.existing-document');
    
    documentElement.classList.remove('document-to-remove');
    Object.assign(documentElement.style, {
      opacity: '',
      textDecoration: ''
    });
    
    restoreBtn.innerHTML = '<i class="ti ti-trash"></i>';
    restoreBtn.className = 'btn btn-sm btn-outline-danger remove-document-btn';
    restoreBtn.title = '';
  }

  function handleEditMode(card) {
    // Toggle sections
    card.querySelector('.milestone-form-section').classList.remove('d-none');
    card.querySelector('.milestone-display-section').classList.add('d-none');
    card.querySelector('.cancel-edit-btn').classList.remove('d-none');

    // Button visibility during edit
    card.querySelector('.delete-milestone-btn').classList.add('d-none');
    const showRemoveBtn = !card.classList.contains('initial-milestone-card') && card.dataset.isNew === 'true';
    card.querySelector('.remove-unsaved-milestone-btn').classList.toggle('d-none', !showRemoveBtn);

    // Populate form with saved data
    const titleDisplayPreview = card.querySelector('.milestone-title-display-preview');
    const savedTitle = titleDisplayPreview ? titleDisplayPreview.textContent : '';
    card.querySelector('.milestone-title').value = savedTitle;
    card.querySelector('.milestone-description').value = card.querySelector('.milestone-description-display').textContent;
    card.querySelector('.milestone-completion').value = parseInt(card.querySelector('.milestone-completion-display').textContent);

    populateExistingDocumentsInForm(card);
    clearValidation(card);
  }

  function handleCancelEdit(card) {
    clearValidation(card);

    if (card.dataset.isNew === 'true') {
      card.remove();
      updateMilestoneNumbers();
    } else {
      // Revert to display mode
      card.querySelector('.milestone-form-section').classList.add('d-none');
      card.querySelector('.milestone-display-section').classList.remove('d-none');
      card.querySelector('.cancel-edit-btn').classList.add('d-none');

      // Clear uploaded documents and restore buttons
      card.querySelector('.uploaded-documents').innerHTML = '';
      card.querySelector('.delete-milestone-btn').classList.remove('d-none');
      card.querySelector('.remove-unsaved-milestone-btn').classList.add('d-none');
    }
  }

  // Initial load: Load existing milestones or create one empty card
  loadExistingMilestones();

  // Add new milestone card on button click
  addMilestoneBtn.addEventListener('click', function () {
    createMilestoneCard(false); // New cards are generally removable
  });

  // Event delegation for actions within milestone cards
  milestonesContainer.addEventListener('click', function (event) {
    const card = event.target.closest('.milestone-card');
    if (!card) return; // Not a click within a card

    const submitBtn = event.target.closest('.submit-milestone-btn');
    const editBtn = event.target.closest('.edit-milestone-btn');
    const deleteBtn = event.target.closest('.delete-milestone-btn'); // For saved cards in header
    const removeUnsavedBtn = event.target.closest('.remove-unsaved-milestone-btn'); // For unsaved cards in header
    const cancelEditBtn = event.target.closest('.cancel-edit-btn');

    if (submitBtn) {
      // Handle Submit
      const titleInput = card.querySelector('.milestone-title');
      const descriptionInput = card.querySelector('.milestone-description');
      const completionInput = card.querySelector('.milestone-completion');
      const documentsInput = card.querySelector('.milestone-documents');

      // Clear previous errors
      clearValidation(card);

      let isValid = true;
      let firstInvalidInput = null;

      // Validation rules
      const validationRules = [
        { input: titleInput, errorSelector: '.milestone-title-error', message: MESSAGES.titleRequired, validate: () => !titleInput.value.trim() },
        { input: descriptionInput, errorSelector: '.milestone-description-error', message: MESSAGES.descriptionRequired, validate: () => !descriptionInput.value.trim() },
        { input: completionInput, errorSelector: '.milestone-completion-error', message: MESSAGES.completionRequired, validate: () => !completionInput.value.trim() },
        { input: completionInput, errorSelector: '.milestone-completion-error', message: MESSAGES.completionRange, validate: () => {
          if (!completionInput.value.trim()) return false;
          const percentage = parseFloat(completionInput.value);
          return isNaN(percentage) || percentage < 0 || percentage > 100;
        }}
      ];

      // Validate inputs
      validationRules.forEach(rule => {
        if (rule.validate()) {
          showValidationError(rule.input, card.querySelector(rule.errorSelector), rule.message);
          isValid = false;
          if (!firstInvalidInput) firstInvalidInput = rule.input;
        }
      });

      // Validate documents
      const files = documentsInput.files;
      if (files.length > 0) {
        for (const file of files) {
          if (!ALLOWED_FILE_TYPES.includes(file.type)) {
            showValidationError(documentsInput, card.querySelector('.milestone-documents-error'), MESSAGES.fileType);
            isValid = false;
            if (!firstInvalidInput) firstInvalidInput = documentsInput;
            break;
          }
          if (file.size > MAX_FILE_SIZE) {
            showValidationError(documentsInput, card.querySelector('.milestone-documents-error'), MESSAGES.fileSize);
            isValid = false;
            if (!firstInvalidInput) firstInvalidInput = documentsInput;
            break;
          }
        }
      }


      if (isValid) {
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${MESSAGES.saving}`;
        
        // Prepare form data
        const formData = new FormData();
        Object.entries({
          '_token': csrfToken,
          'name': titleInput.value,
          'description': descriptionInput.value,
          'completion_percentage': completionInput.value
        }).forEach(([key, value]) => formData.append(key, value));
        
        // Add files
        Array.from(files).forEach((file, index) => {
          formData.append(`documents[${index}]`, file);
        });
        
        // Collect documents marked for removal
        const documentsToRemove = Array.from(card.querySelectorAll('.existing-document.document-to-remove'))
          .map(doc => parseInt(doc.dataset.documentIndex));
        
        if (documentsToRemove.length > 0) {
          formData.append('remove_documents', JSON.stringify(documentsToRemove));
        }
        
        // Determine if editing
        const isEditing = card.dataset.isNew === 'false' && 
                         card.dataset.milestoneId && 
                         !card.dataset.milestoneId.toString().startsWith('temp_');
        
        const url = isEditing 
          ? `{{ route('company.profile_milestones_edit', '') }}/${companyId}`
          : `{{ route('company.profile_milestones_add', '') }}/${companyId}`;
        
        if (isEditing) formData.append('id', card.dataset.milestoneId);

        // Make API call
        fetch(url, {
          method: 'POST',
          body: formData,
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(response => response.ok ? response.json() : Promise.reject(`HTTP ${response.status}: ${response.statusText}`))
        .then(data => {
          if (data.errors) {
            handleServerValidationErrors(card, data.errors);
          } else if (data.state || data.success) {
            handleSuccessfulSubmission(card, data, titleInput, descriptionInput, completionInput);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          releaseFailureAlert({
            title: '{{ $l == 'ar' ? 'خطأ' : 'Error' }}',
            message: MESSAGES.error
          });
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '{{ __("admin.Save") }}';
        });
      } else {
        if (firstInvalidInput) {
          firstInvalidInput.focus(); // Focus the first invalid input
        }
      }
    } else if (editBtn) {
      handleEditMode(card);

    } else if (deleteBtn) {
      handleMilestoneDelete(card);
    } else if (removeUnsavedBtn) {
      const isLastCard = document.querySelectorAll('.milestone-card').length === 1;
      card.remove();
      
      if (isLastCard) createMilestoneCard(true);
      else updateMilestoneNumbers();
    } else if (event.target.closest('.remove-document-btn')) {
      handleDocumentRemoval(event.target.closest('.remove-document-btn'));
    } else if (event.target.closest('.restore-document-btn')) {
      handleDocumentRestore(event.target.closest('.restore-document-btn'));
    } else if (cancelEditBtn) {
      handleCancelEdit(card);
    }
  });
});
</script>
@endpush