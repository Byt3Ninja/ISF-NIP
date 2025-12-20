<section>


  <!-- Evaluation Model CRUD Tab -->
  <div id="evaluation-model" class="card card-body tab-content active">
    <h4>{{ __('cycles.ManageEvaluationModel') }}</h4>
    <p class="text-muted">{{ __('cycles.EvaluationModelDescription') }}</p>

    <!-- Screening Guidelines Section -->
    <div class="mb-4">
      <h5>{{ __('cycles.Screening Guidelines') }}</h5>
      <div class="form-group">
        <textarea class="form-control" id="screening_guidelines" name="screening_guidelines" rows="5">{{ $evaluationForm->screening_guidelines ?? '' }}</textarea>
      </div>
    </div>
    
    <!-- Screamer Warning -->

    
    <div>
      <div class="d-flex align-items-center gap-4 mb-4">
        @if( $_user->ableTo('screening.evaluation_save_draft', $permissions) && $status == 'draft' )
        <button id="draft-model-btn" class="btn btn-light">
          <i class="fas fa-save me-2"></i> {{ __('cycles.SaveDraft') }}
        </button>
        @endif
        @if( $_user->ableTo('screening.evaluation_publish', $permissions) )
          @if( $status == 'draft' )
            <button id="save-model-btn" class="btn btn-primary">
              <i class="ti ti-world me-2"></i> {{ __('cycles.Publish') }}
            </button>
          @else
            <button onclick="event.preventDefault();" class="btn btn-primary disabled" disabled>
              <i class="ti ti-check me-2"></i> {{ __('cycles.Published') }}
            </button>
          @endif
        @endif
      </div>
      <div id="model-validation-summary" style="display: none;"></div>
    </div>

    <div id="model-editor" class="mt-4"></div>
    @if( $_user->ableTo('screening.evaluation_add_criteria') && $status == 'draft' )
    <button id="add-category-btn" class="btn btn-label-primary">
      <i class="fas fa-plus me-2"></i> {{ __('cycles.AddNewCriteria') }}
    </button>
    @endif
  </div>
</section>

@push('scripts')
  <!-- Summernote CSS/JS -->
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
  
  <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
      // Initialize Summernote
      $('#screening_guidelines').summernote({
        placeholder: '{{ __('cycles.Enter_Program_Guideline') }}',
        tabsize: 2,
        height: 200,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture', 'video']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ]
      });
      // --- Tab Navigation ---
      // const navLinks = document.querySelectorAll('.nav-link');
      // const tabContents = document.querySelectorAll('.main-content .tab-content');

      // function setActiveTab(targetId) {
      //   navLinks.forEach(l => {
      //     l.classList.toggle('active', l.getAttribute('href') === `#${targetId}`);
      //   });
      //   tabContents.forEach(t => {
      //     t.classList.toggle('active', t.id === targetId);
      //   });
      // }

      // navLinks.forEach(link => {
      //   link.addEventListener('click', (e) => {
      //     e.preventDefault();

      //     const href = link.getAttribute('href');
      //     if (!href) return; // Skip if no href

      //     const targetId = href.substring(1);
      //     setActiveTab(targetId);

      //     if (targetId === 'evaluation-model') {
      //       renderModelEditor();
      //     }
      //   });
      // });


      // const initialTab = window.location.hash ? window.location.hash.substring(1) : 'evaluation-model';
      // setActiveTab(initialTab);

      // // --- Chart Initialization ---
      // const revenueChartCanvas = document.getElementById('revenueChart');
      // if (revenueChartCanvas) {
      //   const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
      //   const ctx = revenueChartCanvas.getContext('2d');
      //   new Chart(ctx, {
      //     type: 'line',
      //     data: { /* ... chart data ... */ },
      //     options: { /* ... chart options ... */ }
      //   });
      // }

      // --- Evaluation Model CRUD Logic ---
      const modelEditorContainer = document.getElementById('model-editor');
      @if( $status == 'draft' )
        @if( $_user->ableTo('screening.evaluation_add_criteria') )
        const addCategoryBtn = document.getElementById('add-category-btn');
        @endif
        @if( $_user->ableTo('screening.evaluation_publish') )
        const saveModelBtn = document.getElementById('save-model-btn');
        @endif
        @if( $_user->ableTo('screening.evaluation_save_draft') )
        const draftModelBtn = document.getElementById('draft-model-btn');
        @endif
      @endif

      const modelValidationSummary = document.getElementById('model-validation-summary');
      let evaluationModelData = @json( $evaluationForm );

      // Helper function to show dismissible alerts
      function showAlert(element, message, type = 'info', timeout = null) {
        const alertTypes = {
          'success': 'alert-success',
          'danger': 'alert-danger', 
          'warning': 'alert-warning',
          'info': 'alert-info'
        };
        
        const alertClass = alertTypes[type] || 'alert-info';
        
        element.innerHTML = `
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('cycles.Close') }}"></button>
        `;
        element.className = `alert ${alertClass} alert-dismissible`;
        element.style.display = 'block';
        
        // Initialize Bootstrap alert dismiss functionality
        const dismissButton = element.querySelector('.btn-close');
        if (dismissButton) {
          dismissButton.addEventListener('click', function() {
            element.style.display = 'none';
            element.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'alert-info');
          });
        }
        
        // Auto-dismiss after timeout if specified
        if (timeout) {
          setTimeout(() => {
            if (element.classList.contains(alertClass)) {
              element.style.display = 'none';
              element.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'alert-info');
            }
          }, timeout);
        }
      }

      function renderModelEditor() {
        if (!modelEditorContainer) return;
        modelEditorContainer.innerHTML = '';

        evaluationModelData.categories.forEach((category, categoryIndex) => {
          const categoryDiv = document.createElement('div');
          categoryDiv.className = 'card shadow-sm border mb-4';
          categoryDiv.setAttribute('data-category-id', category.id);

          let subCriteriaHTML = '<div class="mt-3 ps-3 border-start border-primary border-3">';
          category.subCriteria.forEach((sub, subIndex) => {
            subCriteriaHTML += `
              <div class="d-flex gap-3 align-items-center py-3 border-bottom" data-subcriterion-id="${sub.id}">
                <input type="text" class="form-control subcriterion-name flex-grow-1" placeholder="{{ __('cycles.Sub Criteria Name') }}" value="${sub.name || ''}" data-category-index="${categoryIndex}" data-sub-index="${subIndex}" pattern="[A-Za-z\\s\\u0600-\\u06FF]*" title="{{ __('cycles.Only letters and spaces allowed') }}">
                <div class="input-group" style="width: 150px;">
                  <input type="number" class="form-control text-center subcriterion-weight" placeholder="{{ __('cycles.Weight (%)') }}" value="${sub.weight * 100}" min="0" max="100" step="0.1" data-category-index="${categoryIndex}" data-sub-index="${subIndex}" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                  <span class="input-group-text bg-light">%</span>
                </div>
                @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
                <button class="btn btn-outline-danger btn-sm delete-subcriterion-btn" data-category-index="${categoryIndex}" data-sub-index="${subIndex}" title="{{ __('cycles.Delete Sub Criteria') }}">
                  <i class="fas fa-trash-alt"></i>
                </button>
                @endif
              </div>
            `;
          });
          subCriteriaHTML += '</div>';

          categoryDiv.innerHTML = `
            <div class="card-body">
              <div class="d-flex flex-nowrap justify-content-between align-items-center border-bottom pb-3 mb-3">
                <div class="w-100 d-flex justify-content-between align-items-center">
                  <input type="text" class="form-control category-name me-3 fw-bold fs-5" placeholder="{{ __('cycles.Category Name') }}" value="${category.name || ''}" data-category-index="${categoryIndex}" pattern="[A-Za-z\\s\\u0600-\\u06FF]*" title="{{ __('cycles.Only letters and spaces allowed') }}">
                  <div class="d-flex align-items-center">
                    <div class="input-group" style="width: 150px;">
                      <input type="number" class="form-control text-center category-weight" placeholder="{{ __('cycles.Weight (%)') }}" value="${category.weight * 100}" min="0" max="100" step="0.1" data-category-index="${categoryIndex}" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                      <span class="input-group-text bg-light">%</span>
                    </div>
                    @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
                    <button class="btn btn-outline-danger btn-sm delete-category-btn text-nowrap ms-3" data-category-index="${categoryIndex}" title="{{ __('cycles.Delete Criteria') }}">
                      <i class="fas fa-trash-alt me-2"></i> {{ __('cycles.Delete Criteria') }}
                    </button>
                    @endif
                  </div>
                </div>
              </div>
              <div class="validation-message category-validation" style="display: none;"></div>
              ${subCriteriaHTML}
              @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
              <div class="mt-3 d-flex justify-content-end">
                <button class="btn btn-outline-secondary btn-sm add-subcriterion-btn" data-category-index="${categoryIndex}">
                  <i class="fas fa-plus me-2"></i> {{ __('cycles.Add Sub-criteria') }}
                </button>
              </div>
              @endif
            </div>
          `;
          modelEditorContainer.appendChild(categoryDiv);
        });

        attachEventListeners();
        validateModel(false);
      }

      function attachEventListeners() {
        @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
        document.querySelectorAll('.delete-category-btn').forEach(btn => {
          btn.replaceWith(btn.cloneNode(true));
        });
        document.querySelectorAll('.delete-category-btn').forEach(btn => {
          btn.addEventListener('click', handleDeleteCategory);
        });
        @endif

        @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
        document.querySelectorAll('.delete-subcriterion-btn').forEach(btn => {
          btn.replaceWith(btn.cloneNode(true));
        });
        document.querySelectorAll('.delete-subcriterion-btn').forEach(btn => {
          btn.addEventListener('click', handleDeleteSubCriterion);
        });
        @endif

        @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
        document.querySelectorAll('.add-subcriterion-btn').forEach(btn => {
          btn.replaceWith(btn.cloneNode(true));
        });
        document.querySelectorAll('.add-subcriterion-btn').forEach(btn => {
          btn.addEventListener('click', handleAddSubCriterion);
        });
        @endif

        document.querySelectorAll('.category-name, .category-weight, .subcriterion-name, .subcriterion-weight').forEach(input => {
          input.replaceWith(input.cloneNode(true));
        });
        document.querySelectorAll('.category-name, .category-weight, .subcriterion-name, .subcriterion-weight').forEach(input => {
          input.addEventListener('input', handleInputChange);
          input.addEventListener('change', () => validateModel(false));
          
          // Add specific input validation
          if (input.classList.contains('category-name') || input.classList.contains('subcriterion-name')) {
            // Text fields - only letters, spaces, and Arabic characters
            input.addEventListener('keypress', function(e) {
              const regex = /^[A-Za-z\s\u0600-\u06FF]*$/;
              if (!regex.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
                e.preventDefault();
              }
            });
            
            input.addEventListener('paste', function(e) {
              e.preventDefault();
              const paste = (e.clipboardData || window.clipboardData).getData('text');
              const regex = /^[A-Za-z\s\u0600-\u06FF]*$/;
              if (regex.test(paste)) {
                this.value = paste;
                handleInputChange({ target: this });
              }
            });
          }
          
          if (input.classList.contains('category-weight') || input.classList.contains('subcriterion-weight')) {
            // Number fields - only numbers and decimal point
            input.addEventListener('keypress', function(e) {
              const regex = /^[0-9.]$/;
              if (!regex.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight' && e.key !== 'Tab') {
                e.preventDefault();
              }
              
              // Prevent multiple decimal points
              if (e.key === '.' && this.value.includes('.')) {
                e.preventDefault();
              }
              
              // Prevent values over 100
              const newValue = this.value + e.key;
              if (parseFloat(newValue) > 100) {
                e.preventDefault();
              }
            });
            
            input.addEventListener('blur', function() {
              // Ensure value doesn't exceed 100
              if (parseFloat(this.value) > 100) {
                this.value = 100;
                handleInputChange({ target: this });
              }
              // Ensure value is not negative
              if (parseFloat(this.value) < 0) {
                this.value = 0;
                handleInputChange({ target: this });
              }
            });
          }
        });
      }

      function handleAddCategory() {
        evaluationModelData.categories.push({
          id: null,
          name: "",
          weight: 0,
          subCriteria: []
        });
        renderModelEditor();
      }

      @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
      function handleAddSubCriterion(event) {
        const categoryIndex = parseInt(event.target.closest('button').dataset.categoryIndex);
        if (isNaN(categoryIndex) || !evaluationModelData.categories[categoryIndex]) return;

        evaluationModelData.categories[categoryIndex].subCriteria.push({
          id: null,
          name: "",
          weight: 0
        });
        renderModelEditor();
      }
      @endif

      @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
      function handleDeleteCategory(event) {
        const categoryIndex = parseInt(event.target.closest('button').dataset.categoryIndex);
        if (isNaN(categoryIndex)) return;

        const categoryName = evaluationModelData.categories[categoryIndex]?.name || '{{ __('cycles.this category') }}';
        if (confirm(`{{ __('cycles.Are you sure you want to delete') }} "${categoryName}" {{ __('cycles.and all its sub-criteria?') }}`)) {
          evaluationModelData.categories.splice(categoryIndex, 1);
          renderModelEditor();
        }
      }
      @endif

      @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
      function handleDeleteSubCriterion(event) {
        const btn = event.target.closest('button');
        const categoryIndex = parseInt(btn.dataset.categoryIndex);
        const subIndex = parseInt(btn.dataset.subIndex);
        if (isNaN(categoryIndex) || isNaN(subIndex)) return;

        const subName = evaluationModelData.categories[categoryIndex]?.subCriteria[subIndex]?.name || '{{ __('cycles.this sub-criterion') }}';
        if (confirm(`{{ __('cycles.Are you sure you want to delete') }} "${subName}"?`)) {
          evaluationModelData.categories[categoryIndex].subCriteria.splice(subIndex, 1);
          renderModelEditor();
        }
      }
      @endif

      function handleInputChange(event) {
        const input = event.target;
        const categoryIndex = parseInt(input.dataset.categoryIndex);
        const subIndex = input.dataset.subIndex !== undefined ? parseInt(input.dataset.subIndex) : null;

        if (isNaN(categoryIndex)) return;

        const category = evaluationModelData.categories[categoryIndex];
        if (!category) return;

        if (input.classList.contains('category-name')) {
          category.name = input.value;
        } else if (input.classList.contains('category-weight')) {
          category.weight = parseFloat(input.value) / 100 || 0;
        } else if (subIndex !== null) {
          const subCriterion = category.subCriteria[subIndex];
          if (!subCriterion) return;
          if (input.classList.contains('subcriterion-name')) {
            subCriterion.name = input.value;
          } else if (input.classList.contains('subcriterion-weight')) {
            subCriterion.weight = parseFloat(input.value) / 100 || 0;
          }
        }
      }

      @if( $status == 'draft' )
        @if( $_user->ableTo('screening.evaluation_publish') )
        function handleSaveModel() {
          if (validateModel(true, true)) {  // Use strict validation for publishing
            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
            });
            $.ajax({
              type: "POST",
              url: "{{ route('screening.evaluation_publish') }}",
              data: {
                "action": "publish",
                "criteria": evaluationModelData.categories,
                "stepId": {{ $cstep->id }},
                "screening_guidelines": $('#screening_guidelines').summernote('code')
              },
              success: function(response) {
                console.log(response);
                if (response.status) {
                  evaluationModelData.categories = response.data;
                  showAlert(modelValidationSummary, '{{ __('cycles.Model published successfully!') }}', 'success', 3000);
                  // Refresh page to update status
                  setTimeout(() => {
                    window.location.reload();
                  }, 2000);
                } else {
                  showAlert(modelValidationSummary, response.message, 'danger');
                }
              },
              error: function(error) {
                console.error("Error publishing model:", error);
                showAlert(modelValidationSummary, '{{ __('cycles.Error publishing model. Please try again.') }}', 'danger');
              }
            });
          } else {
            alert("{{ __('cycles.Please fix all validation errors before publishing.') }}");
          }
        }
        @endif

        @if( $_user->ableTo('screening.evaluation_save_draft') )
        function handleDraftModel() {
          // Save draft without validation - allow incomplete forms
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          });
          $.ajax({
            type: "POST",
            url: "{{ route('screening.evaluation_save_draft') }}",
            data: {
              "action": "draft",
              "criteria": evaluationModelData.categories,
              "stepId": {{ $cstep->id }},
              "screening_guidelines": $('#screening_guidelines').summernote('code')
            },
            success: function(response) {
              console.log(response);
              if (response.status) {
                evaluationModelData.categories = response.data;
                showAlert(modelValidationSummary, '{{ __('cycles.Draft saved successfully!') }}', 'success', 3000);
              } else {
                showAlert(modelValidationSummary, response.message, 'danger');
              }
            },
            error: function(error) {
              console.error("Error saving draft:", error);
              showAlert(modelValidationSummary, '{{ __('cycles.Error saving draft. Please try again.') }}', 'danger');
            }
          });
        }
        @endif
      @endif

      function validateModel(showGlobalSummary, isPublishing = false) {
        let isValid = true;
        let totalCategoryWeight = 0;
        let globalErrorMessages = [];

        evaluationModelData.categories.forEach((category, categoryIndex) => {
          const categoryElement = modelEditorContainer.querySelector(`.card[data-category-id="${category.id}"]`);
          const categoryValidationDiv = categoryElement ? categoryElement.querySelector('.category-validation') : null;
          let categoryErrorMessage = '';

          totalCategoryWeight += category.weight;

          // Only apply strict validation when publishing
          if (isPublishing) {
            // Check if category name is empty
            if (!category.name || category.name.trim() === '') {
              isValid = false;
              categoryErrorMessage = `{{ __('cycles.Category name is required') }}`;
              globalErrorMessages.push(`{{ __('cycles.Category') }} ${categoryIndex + 1}: ${categoryErrorMessage}`);
            }
            
            // Check if category has weight but no sub-criteria
            if (category.weight > 0 && category.subCriteria.length === 0) {
              isValid = false;
              categoryErrorMessage = `{{ __('cycles.Category with weight must have sub-criteria') }}`;
              globalErrorMessages.push(`{{ __('cycles.Category') }} "${category.name}": ${categoryErrorMessage}`);
            }
            
            // Check sub-criteria names
            category.subCriteria.forEach((sub, subIndex) => {
              if (!sub.name || sub.name.trim() === '') {
                isValid = false;
                globalErrorMessages.push(`{{ __('cycles.Category') }} "${category.name}", {{ __('cycles.Sub-criterion') }} ${subIndex + 1}: {{ __('cycles.Sub-criterion name is required') }}`);
              }
            });
          }

          let totalSubCriterionWeight = 0;
          if (category.subCriteria.length > 0) {
            category.subCriteria.forEach(sub => {
              totalSubCriterionWeight += sub.weight;
            });

            // Only enforce weight validation when publishing
            if (isPublishing && Math.abs(totalSubCriterionWeight - 1.0) > 0.001) {
              isValid = false;
              categoryErrorMessage = `{{ __('cycles.Sub-criterion weights must sum to 100%') }} ({{ __('cycles.currently') }} ${(totalSubCriterionWeight * 100).toFixed(1)}%).`;
              globalErrorMessages.push(`{{ __('cycles.Category') }} "${category.name}": ${categoryErrorMessage}`);
            } else if (!isPublishing && totalSubCriterionWeight > 1.0) {
              // Just show warning for drafts when over 100%
              categoryErrorMessage = `{{ __('cycles.Warning: Sub-criterion weights exceed 100%') }} ({{ __('cycles.currently') }} ${(totalSubCriterionWeight * 100).toFixed(1)}%).`;
            }
          } else if (category.weight > 0 && !isPublishing) {
            categoryErrorMessage = `{{ __('cycles.Warning: Category has weight but no sub-criteria. It won\'t contribute to score.') }}`;
          }

          if (categoryValidationDiv) {
            if (categoryErrorMessage && categoryErrorMessage.startsWith('Warning:')) {
              showAlert(categoryValidationDiv, categoryErrorMessage, 'warning');
            } else if (categoryErrorMessage) {
              showAlert(categoryValidationDiv, categoryErrorMessage, 'danger');
            } else {
              categoryValidationDiv.style.display = 'none';
            }
          }
        });

        // Only enforce total weight validation when publishing
        if (isPublishing && Math.abs(totalCategoryWeight - 1.0) > 0.001) {
          isValid = false;
          globalErrorMessages.push(`{{ __('cycles.Total category weights must sum to 100%') }} ({{ __('cycles.currently') }} ${(totalCategoryWeight * 100).toFixed(1)}%).`);
        }

        if (showGlobalSummary) {
          if (!isValid) {
            showAlert(modelValidationSummary, '<strong>{{ __('cycles.Validation Errors:') }}</strong><br>' + globalErrorMessages.join('<br>'), 'danger');
          } else {
            const message = isPublishing ? '{{ __('cycles.Model structure is valid and ready for publishing.') }}' : '{{ __('cycles.Model structure is valid.') }}';
            showAlert(modelValidationSummary, message, 'success', 3000);
          }
        } else {
          if (!modelValidationSummary.classList.contains('alert-danger') && !modelValidationSummary.classList.contains('alert-success')) {
            modelValidationSummary.style.display = 'none';
          }
        }

        return isValid;
      }

      @if( $status == 'draft' )
        @if( $_user->ableTo('screening.evaluation_add_criteria') )
        addCategoryBtn.addEventListener('click', handleAddCategory);
        @endif
        @if( $_user->ableTo('screening.evaluation_publish') )
        saveModelBtn.addEventListener('click', handleSaveModel);
        @endif
        @if( $_user->ableTo('screening.evaluation_save_draft') )
        draftModelBtn.addEventListener('click', handleDraftModel);
        @endif
      @endif

      if (document.getElementById('evaluation-model').classList.contains('active')) {
        renderModelEditor();
      }
    });

    $(document).on('click', '#addPanelBtn', function(e) {
      e.preventDefault();
      let lastTrData = $('#panels-list-body tr').length ?? 0;
      let panel = lastTrData + 1;
      let html = `<tr data-panel="${panel}">
        <td>
          <i title="{{ __('cycles.Remove panel') }}" class="remove-panel-btn cursor-pointer fas fa-fw fa-sm fa-minus-circle text-danger"></i> 
          {{ __('cycles.Panel list') }} ${panel}
        </td>
        <td>
          <select name="judges[${panel}][]" class="select2 form-control panel judgers-selectors" multiple="multiple">
            @foreach( $judges as $judge )
              <option value="{{ $judge->id }}">{{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }} ({{ $judge->email }})</option>
            @endforeach
          </select>
        </td>
        <td>
          <select name="users[${panel}][]" class="select2 form-control panel users-selectors" multiple="multiple">
            @foreach( $companies as $company )
              <option value="{{ $company->id }}">{{ $company->{'name_' . $l} }}</option>
            @endforeach
          </select>
        </td>
      </tr>`;

      $('#panels-list-body').append( html );
      $('.judgers-selectors').each(function() {
        const $select = $(this);
        $select.select2({
          placeholder: "{{ __('cycles.Select judges') }}",
          allowClear: false,
          dropdownParent: null,
          width: '100%'
        });
      });
      $('.users-selectors').each(function() {
        const $select = $(this);
        $select.select2({
          placeholder: "{{ __('cycles.Select Company') }}",
          allowClear: false,
          dropdownParent: null,
          width: '100%'
        });
      });
    });

    $(document).on('click', '.remove-panel-btn', function(e) {
      e.preventDefault();
      $(this).closest('tr').remove();
      let index = 1;
      $('#panels-list-body tr').each(function() {
        const $tr = $(this);
        let line = `
            <i title="{{ __('cycles.Remove panel') }}" class="remove-panel-btn cursor-pointer fas fa-fw fa-sm fa-minus-circle text-danger"></i> 
            {{ __('cycles.Panel list') }} ${index}
        `;
        index++;
        $($tr).find('td').eq(0).html(line);
        $($tr).find('td').eq(1).find('select').attr('name', `judges[${index}][]`);
        $($tr).find('td').eq(2).find('select').attr('name', `users[${index}][]`);
      });
    });
  </script>
@endpush