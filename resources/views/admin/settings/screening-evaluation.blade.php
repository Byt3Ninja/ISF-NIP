<section>
  <div id="model-validation-summary"></div>
  
  <div id="evaluation-model" class="card card-body tab-content active">
    <h5 class="card-title mb-1">{{ __('cycles.ManageEvaluationModel') }}</h5>
    <p class="text-muted">{{ __('cycles.EvaluationModelDescription') }}</p>
    
    <div>
      <div class="d-flex align-items-center gap-4 mb-4">
        @if( $_user->ableTo('screening.evaluation_save_draft', $permissions) && $status == 'draft' )
        <button id="draft-model-btn" class="btn btn-light">
          <i class="ti ti-device-floppy me-2"></i> {{ __('cycles.SaveDraft') }}
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
    </div>
    <form id="evaluation-model-form">
      <div id="model-editor" class="mt-4"></div>
    </form>
    @if( $_user->ableTo('screening.evaluation_add_criteria') && $status == 'draft' )
    <button id="add-category-btn" class="btn btn-label-primary">
      <i class="ti ti-plus me-2"></i> {{ __('cycles.AddNewCriteria') }}
    </button>
    @endif
  </div>
</section>

@push('scripts')
  <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
      const modelEditorContainer = document.getElementById('model-editor');
      const modelValidationSummary = document.getElementById('model-validation-summary');
      const evaluationForm = document.getElementById('evaluation-model-form');
      
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

      let evaluationModelData = @json( $evaluationForm );
      let fv = null;

      function showAlert(message, type = 'info', timeout = null, element = modelValidationSummary) {
        const alertClass = `alert-${type}`;
        
        element.innerHTML = `
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('cycles.Close') }}"></button>
        `;
        element.className = `alert ${alertClass} alert-dismissible`;
        element.style.display = 'block';
        
        const dismissButton = element.querySelector('.btn-close');
        if (dismissButton) {
          dismissButton.addEventListener('click', () => {
            element.style.display = 'none';
            element.className = 'alert';
          });
        }
        
        if (timeout) {
          setTimeout(() => {
            element.style.display = 'none';
            element.className = 'alert';
          }, timeout);
        }
      }

      function initFormValidation() {
        if (fv) {
          fv.destroy();
        }

        const fields = {};
        
        evaluationModelData.categories.forEach((category, categoryIndex) => {
          // Category name validation
          fields[`category_name_${categoryIndex}`] = {
            validators: {
              notEmpty: {
                message: '{{ __('cycles.Category name is required') }}'
              },
              regexp: {
                regexp: /^[A-Za-z\s\u0600-\u06FF]+$/,
                message: '{{ __('cycles.Only letters and spaces allowed') }}'
              }
            }
          };

          // Category weight validation
          fields[`category_weight_${categoryIndex}`] = {
            validators: {
              notEmpty: {
                message: '{{ __('admin.weight_required') }}'
              },
              numeric: {
                message: '{{ __('admin.weight_must_be_number') }}'
              },
              between: {
                min: 0,
                max: 100,
                message: '{{ __('admin.weight_min_max') }}'
              }
            }
          };

          // Sub-criteria validation
          category.subCriteria.forEach((sub, subIndex) => {
            fields[`subcriterion_name_${categoryIndex}_${subIndex}`] = {
              validators: {
                notEmpty: {
                  message: '{{ __('cycles.Sub-criterion name is required') }}'
                },
                regexp: {
                  regexp: /^[A-Za-z\s\u0600-\u06FF]+$/,
                  message: '{{ __('cycles.Only letters and spaces allowed') }}'
                }
              }
            };

            fields[`subcriterion_weight_${categoryIndex}_${subIndex}`] = {
              validators: {
                notEmpty: {
                  message: '{{ __('admin.weight_required') }}'
                },
                numeric: {
                  message: '{{ __('cycles.weight_must_be_number') }}'
                },
                between: {
                  min: 0,
                  max: 100,
                  message: '{{ __('cycles.weight_min_max') }}'
                }
              }
            };
          });
        });

        fv = FormValidation.formValidation(evaluationForm, {
          fields: fields,
          plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
              eleValidClass: '',
              eleInvalidClass: 'is-invalid',
              rowSelector: function(field, ele) {
                // Target the parent column for proper error placement
                return '.col, .col-auto';
              }
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            autoFocus: new FormValidation.plugins.AutoFocus()
          }
        });
      }

      function renderModelEditor(skipValidation = false) {
        if (!modelEditorContainer) return;
        modelEditorContainer.innerHTML = '';

        evaluationModelData.categories.forEach((category, categoryIndex) => {
          const categoryDiv = document.createElement('div');
          categoryDiv.className = 'card shadow-sm border mb-4';
          categoryDiv.setAttribute('data-category-id', category.id);
          categoryDiv.setAttribute('data-category-index', categoryIndex);

          let subCriteriaHTML = '<div class="mt-3 ps-3 border-start border-primary border-3">';
          category.subCriteria.forEach((sub, subIndex) => {
            subCriteriaHTML += `
              <div class="row g-3 align-items-start py-3 border-bottom" data-subcriterion-id="${sub.id}">
                <div class="col">
                  <input type="text" 
                    class="form-control subcriterion-name" 
                    name="subcriterion_name_${categoryIndex}_${subIndex}"
                    placeholder="{{ __('cycles.Sub Criteria Name') }}" 
                    value="${sub.name || ''}" 
                    data-category-index="${categoryIndex}" 
                    data-sub-index="${subIndex}">
                </div>
                <div class="col-auto" style="width: 180px;">
                  <div class="input-group">
                    <input type="number" 
                      class="form-control text-center subcriterion-weight" 
                      name="subcriterion_weight_${categoryIndex}_${subIndex}"
                      placeholder="{{ __('cycles.Weight (%)') }}" 
                      value="${sub.weight * 100}" 
                      min="0" 
                      max="100" 
                      step="0.1" 
                      data-category-index="${categoryIndex}" 
                      data-sub-index="${subIndex}">
                    <span class="input-group-text bg-light">%</span>
                  </div>
                </div>
                @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
                <div class="col-auto">
                  <button type="button" class="btn btn-outline-danger btn-sm delete-subcriterion-btn" data-category-index="${categoryIndex}" data-sub-index="${subIndex}" title="{{ __('cycles.Delete Sub Criteria') }}" style="margin-top: 0;">
                    <i class="ti ti-trash"></i>
                  </button>
                </div>
                @endif
              </div>
            `;
          });
          subCriteriaHTML += '</div>';

          categoryDiv.innerHTML = `
            <div class="card-body">
              <div class="row g-3 align-items-start border-bottom pb-3 mb-3">
                <div class="col">
                  <input type="text" 
                    class="form-control category-name" 
                    name="category_name_${categoryIndex}"
                    placeholder="{{ __('cycles.Category Name') }}" 
                    value="${category.name || ''}" 
                    data-category-index="${categoryIndex}">
                </div>
                <div class="col-auto" style="width: 180px;">
                  <div class="input-group">
                    <input type="number" 
                      class="form-control text-center category-weight" 
                      name="category_weight_${categoryIndex}"
                      placeholder="{{ __('cycles.Weight (%)') }}" 
                      value="${category.weight * 100}" 
                      min="0" 
                      max="100" 
                      step="0.1" 
                      data-category-index="${categoryIndex}">
                    <span class="input-group-text bg-light">%</span>
                  </div>
                </div>
                @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
                <div class="col-auto">
                  <button type="button" class="btn btn-outline-danger btn-sm delete-category-btn text-nowrap" data-category-index="${categoryIndex}" title="{{ __('cycles.Delete Criteria') }}" style="margin-top: 0;">
                    <i class="ti ti-trash me-2"></i> {{ __('cycles.Delete Criteria') }}
                  </button>
                </div>
                @endif
              </div>
              <div class="validation-message category-validation" style="display: none;"></div>
              ${subCriteriaHTML}
              @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
              <div class="mt-3 d-flex justify-content-end">
                <button type="button" class="btn btn-outline-secondary btn-sm add-subcriterion-btn" data-category-index="${categoryIndex}">
                  <i class="ti ti-plus me-2"></i> {{ __('cycles.Add Sub-criteria') }}
                </button>
              </div>
              @endif
            </div>
          `;
          modelEditorContainer.appendChild(categoryDiv);
        });

        initFormValidation();
        attachEventListeners();
        if (!skipValidation) {
          validateWeights();
        }
      }

      function attachEventListeners() {
        const replaceListeners = (selector, handler) => {
          document.querySelectorAll(selector).forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.replaceWith(newBtn);
            newBtn.addEventListener('click', handler);
          });
        };

        @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
        replaceListeners('.delete-category-btn', handleDeleteCategory);
        @endif

        @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
        replaceListeners('.delete-subcriterion-btn', handleDeleteSubCriterion);
        @endif

        @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
        replaceListeners('.add-subcriterion-btn', handleAddSubCriterion);
        @endif

        document.querySelectorAll('.category-name, .subcriterion-name').forEach(input => {
          input.addEventListener('change', handleInputChange);
        });
        document.querySelectorAll('.category-weight, .subcriterion-weight').forEach(input => {
          input.addEventListener('input', handleInputChange);
        });
      }

      function handleAddCategory() {
        evaluationModelData.categories.push({ id: null, name: "", weight: 0, subCriteria: [] });
        renderModelEditor(true);
        updateWeightAlerts();
      }

      @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
      function handleAddSubCriterion(event) {
        const categoryIndex = parseInt(event.target.closest('button').dataset.categoryIndex);
        if (!isNaN(categoryIndex) && evaluationModelData.categories[categoryIndex]) {
          evaluationModelData.categories[categoryIndex].subCriteria.push({ id: null, name: "", weight: 0 });
          renderModelEditor(true);
          updateWeightAlerts();
        }
      }
      @endif

      @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
      function handleDeleteCategory(event) {
        const categoryIndex = parseInt(event.target.closest('button').dataset.categoryIndex);
        if (isNaN(categoryIndex)) return;

        const categoryName = evaluationModelData.categories[categoryIndex]?.name || '{{ __('cycles.this category') }}';
        if (confirm(`{{ __('cycles.Are you sure you want to delete') }} "${categoryName}" {{ __('cycles.and all its sub-criteria?') }}`)) {
          evaluationModelData.categories.splice(categoryIndex, 1);
          renderModelEditor(true);
          updateWeightAlerts();
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
          renderModelEditor(true);
          updateWeightAlerts();
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
          const newWeight = parseFloat(input.value) || 0;
          category.weight = newWeight / 100;
        } else if (subIndex !== null && category.subCriteria[subIndex]) {
          const subCriterion = category.subCriteria[subIndex];
          if (input.classList.contains('subcriterion-name')) {
            subCriterion.name = input.value;
          } else if (input.classList.contains('subcriterion-weight')) {
            const newWeight = parseFloat(input.value) || 0;
            subCriterion.weight = newWeight / 100;
          }
        }

        // Update alert after data model is updated
        updateWeightAlerts();
      }

      function validateWeights(showAlerts = false) {
        let totalCategoryWeight = 0;
        let hasWeightErrors = false;

        evaluationModelData.categories.forEach((category, categoryIndex) => {
          const categoryElement = modelEditorContainer.querySelector(`.card[data-category-index="${categoryIndex}"]`);
          const categoryValidationDiv = categoryElement?.querySelector('.category-validation');
          let categoryErrorMessage = '';

          totalCategoryWeight += category.weight;

          let totalSubCriterionWeight = category.subCriteria.reduce((sum, sub) => sum + sub.weight, 0);

          if (category.subCriteria.length > 0) {
            if (Math.abs(totalSubCriterionWeight - 1.0) > 0.001) {
              hasWeightErrors = true;
              if (showAlerts) {
                categoryErrorMessage = `{{ __('cycles.Sub-criterion weights must sum to 100%') }} ({{ __('cycles.currently') }} ${(totalSubCriterionWeight * 100).toFixed(1)}%).`;
              }
            }
          } else if (category.weight > 0) {
            if (showAlerts) {
              categoryErrorMessage = `{{ __('cycles.evaluation_criteria_score_warning') }}`;
            }
          }

          if (categoryValidationDiv && showAlerts) {
            if (categoryErrorMessage) {
              showAlert(categoryErrorMessage, categoryErrorMessage.startsWith('Warning:') ? 'warning' : 'danger', null, categoryValidationDiv);
            } else {
              categoryValidationDiv.style.display = 'none';
            }
          } else if (categoryValidationDiv) {
            categoryValidationDiv.style.display = 'none';
          }
        });

        if (Math.abs(totalCategoryWeight - 1.0) > 0.001) {
          hasWeightErrors = true;
        }

        return !hasWeightErrors;
      }

      function updateWeightAlerts() {
        evaluationModelData.categories.forEach((category, categoryIndex) => {
          updateSingleCategoryWeightAlert(categoryIndex);
        });
      }

      function updateSingleCategoryWeightAlert(categoryIndex) {
        const category = evaluationModelData.categories[categoryIndex];
        if (!category) return;

        const categoryElement = modelEditorContainer.querySelector(`.card[data-category-index="${categoryIndex}"]`);
        const categoryValidationDiv = categoryElement?.querySelector('.category-validation');

        if (!categoryValidationDiv) return;

        // Recalculate the total from the actual data model
        let totalSubCriterionWeight = 0;
        category.subCriteria.forEach(sub => {
          totalSubCriterionWeight += sub.weight;
        });

        let categoryErrorMessage = '';

        if (category.subCriteria.length > 0) {
          const difference = Math.abs(totalSubCriterionWeight - 1.0);
          if (difference > 0.001) {
            const percentage = (totalSubCriterionWeight * 100).toFixed(1);
            categoryErrorMessage = `{{ __('cycles.Sub-criterion weights must sum to 100%') }} ({{ __('cycles.currently') }} ${percentage}%).`;
            showAlert(categoryErrorMessage, 'danger', null, categoryValidationDiv);
          } else {
            // Weights are now correct, hide the alert
            categoryValidationDiv.style.display = 'none';
            categoryValidationDiv.className = 'validation-message category-validation';
          }
        } else if (category.weight > 0) {
          categoryErrorMessage = `{{ __('cycles.evaluation_criteria_score_warning') }}`;
          showAlert(categoryErrorMessage, 'warning', null, categoryValidationDiv);
        } else {
          categoryValidationDiv.style.display = 'none';
          categoryValidationDiv.className = 'validation-message category-validation';
        }
      }

      @if( $status == 'draft' )
        @if( $_user->ableTo('screening.evaluation_publish') )
        function handleSaveModel() {
          fv.validate().then(function(status) {
            if (status === 'Valid') {
              const weightsValid = validateWeights(true);
              
              if (!weightsValid) {
                showAlert('{{ __('admin.evaluation_unabled_to_publish_wights') }}', 'danger');
                return;
              }

              // Check for empty categories with weights
              let hasEmptyCategories = false;
              evaluationModelData.categories.forEach(category => {
                if (category.weight > 0 && category.subCriteria.length === 0) {
                  hasEmptyCategories = true;
                }
              });

              if (hasEmptyCategories) {
                showAlert('{{ __('cycles.Unable to publish: Categories with weights must have at least one sub-criterion.') }}', 'danger');
                return;
              }

              // Remove all danger alerts
              const allAlerts = document.querySelectorAll('.alert-danger');
              allAlerts.forEach(alert => {
                alert.remove();
              });

              releaseWarningAlert({
                title: '{{ __('admin.confirm_publication') }}',
                message: '{{ __('admin.evaulation_publish_note') }}',
                onConfirm: publishModel,
              })
            } else {
              showAlert('{{ __('admin.evaluation_unabled_to_publish') }}', 'danger');
            }
          });
        }

        function publishModel() {
          $.ajax({
            type: "POST",
            url: "{{ route('screening.evaluation_publish') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
              action: "publish",
              criteria: evaluationModelData.categories,
              stepId: {{ $cstep->id }}
            },
            success: function(response) {
              if (response.status) {
                evaluationModelData.categories = response.data;
                showAlert('{{ __('cycles.Model published successfully!') }}', 'success', 3000);
                setTimeout(() => window.location.reload(), 2000);
              } else {
                showAlert(response.message, 'danger');
              }
            },
            error: function() {
              showAlert('{{ __('cycles.Error publishing model. Please try again.') }}', 'danger');
            }
          });
        }
        @endif

        @if( $_user->ableTo('screening.evaluation_save_draft') )
        function handleDraftModel() {
          // Validate weights when saving draft
          validateWeights(true);
          
          $.ajax({
            type: "POST",
            url: "{{ route('screening.evaluation_save_draft') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
              action: "draft",
              criteria: evaluationModelData.categories,
              stepId: {{ $cstep->id }}
            },
            success: function(response) {
              if (response.status) {
                evaluationModelData.categories = response.data;
                showAlert('{{ __('cycles.Draft saved successfully!') }}', 'success', 3000);
              } else {
                showAlert(response.message, 'danger');
              }
            },
            error: function() {
              showAlert('{{ __('cycles.Error saving draft. Please try again.') }}', 'danger');
            }
          });
        }
        @endif
      @endif

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
        renderModelEditor(true);
      }
    });
  </script>
@endpush
