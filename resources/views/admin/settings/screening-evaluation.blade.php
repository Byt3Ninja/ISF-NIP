<section>
  <div id="model-validation-summary"></div>
  
  <div id="evaluation-model">

    <div class="d-flex flex-wrap justify-content-between align-items-md-center border-bottom mb-6">
      <div>
        <h5 class="mb-1">{{ __('cycles.ManageEvaluationModel') }}</h5>
        <p class="text-muted">{{ __('cycles.EvaluationModelDescription') }}</p> 
      </div>
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
              <i class="ti ti-circle-check me-2"></i> {{ __('cycles.Published') }}
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
<script>
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

  // Single source of truth, shared with the backend validator.
  const NAME_MAX_LENGTH = {{ \App\ScreeningEvaluation::CRITERION_NAME_MAX_LENGTH }};
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
          stringLength: {
            max: NAME_MAX_LENGTH,
            message: '{{ __('cycles.evaluation_criterion_name_too_long', ['max' => \App\ScreeningEvaluation::CRITERION_NAME_MAX_LENGTH]) }}'
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
            stringLength: {
              max: NAME_MAX_LENGTH,
              message: '{{ __('cycles.evaluation_subcriterion_name_too_long', ['max' => \App\ScreeningEvaluation::CRITERION_NAME_MAX_LENGTH]) }}'
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
            return '.col, .col-auto';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });
  }

  function renderModelEditor() {
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
                maxlength="${NAME_MAX_LENGTH}"
                placeholder="{{ __('cycles.Sub Criteria Name') }}" 
                value="${sub.name || ''}" 
                data-category-index="${categoryIndex}" 
                data-sub-index="${subIndex}">
            </div>
            <div class="col-auto" style="width: 180px;">
              <div class="input-group shadow-none">
                <input type="number" 
                  class="form-control text-center subcriterion-weight" 
                  name="subcriterion_weight_${categoryIndex}_${subIndex}"
                  placeholder="{{ __('cycles.Weight (%)') }}"
                  value="${Math.round(sub.weight * 1000) / 10}"
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
                maxlength="${NAME_MAX_LENGTH}"
                placeholder="{{ __('cycles.Category Name') }}" 
                value="${category.name || ''}" 
                data-category-index="${categoryIndex}">
            </div>
            <div class="col-auto" style="width: 180px;">
              <div class="input-group shadow-none">
                <input type="number" 
                  class="form-control text-center category-weight" 
                  name="category_weight_${categoryIndex}"
                  placeholder="{{ __('cycles.Weight (%)') }}"
                  value="${Math.round(category.weight * 1000) / 10}"
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
          @if( $status == 'draft' )
          <div class="mt-2 text-muted small">{{ __('cycles.Total Sub-criteria Weight') }}: <span class="total-sub-weight" data-category-index="${categoryIndex}">${(category.subCriteria.reduce((sum, sub) => sum + sub.weight, 0) * 100).toFixed(1)}%</span></div>
          @endif
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
      input.addEventListener('input', handleInputChange);
    });
    document.querySelectorAll('.category-weight, .subcriterion-weight').forEach(input => {
      input.addEventListener('keyup', handleInputChange);
    });
  }

  function handleAddCategory() {
    evaluationModelData.categories.push({ id: null, name: "", weight: 0, subCriteria: [] });
    renderModelEditor();
  }

  @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
  function handleAddSubCriterion(event) {
    const categoryIndex = parseInt(event.target.closest('button').dataset.categoryIndex);
    if (!isNaN(categoryIndex) && evaluationModelData.categories[categoryIndex]) {
      evaluationModelData.categories[categoryIndex].subCriteria.push({ id: null, name: "", weight: 0 });
      renderModelEditor();
    }
  }
  @endif

  @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
  function handleDeleteCategory(event) {
    const categoryIndex = parseInt(event.target.closest('button').dataset.categoryIndex);
    if (isNaN(categoryIndex)) return;

    evaluationModelData.categories.splice(categoryIndex, 1);
    renderModelEditor();
  }
  @endif

  @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
  function handleDeleteSubCriterion(event) {
    const btn = event.target.closest('button');
    const categoryIndex = parseInt(btn.dataset.categoryIndex);
    const subIndex = parseInt(btn.dataset.subIndex);
    if (isNaN(categoryIndex) || isNaN(subIndex)) return;

    evaluationModelData.categories[categoryIndex].subCriteria.splice(subIndex, 1);
    renderModelEditor();
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
        
        // Update the total display
        updateSubCriteriaWeightDisplay(categoryIndex);
      }
    }
  }

  function updateSubCriteriaWeightDisplay(categoryIndex) {
    const category = evaluationModelData.categories[categoryIndex];
    if (!category) return;

    const totalSubCriterionWeight = category.subCriteria.reduce((sum, sub) => sum + sub.weight, 0);
    const categoryElement = modelEditorContainer.querySelector(`.card[data-category-index="${categoryIndex}"]`);
    const totalSpan = categoryElement?.querySelector('.total-sub-weight');
    
    if (totalSpan) {
      totalSpan.textContent = (totalSubCriterionWeight * 100).toFixed(1) + '%';
    }
  }

  function validateWeights(showAlerts = false) {
    let totalCategoryWeight = 0;
    let hasWeightErrors = false;
    let errorMessages = [];

    // Clear all existing validation alerts only if we're showing new ones
    if (showAlerts) {
      document.querySelectorAll('.category-validation').forEach(div => {
        div.style.display = 'none';
        div.className = 'validation-message category-validation';
        div.innerHTML = '';
      });
    }

    evaluationModelData.categories.forEach((category, categoryIndex) => {
      const categoryElement = modelEditorContainer.querySelector(`.card[data-category-index="${categoryIndex}"]`);
      const categoryValidationDiv = categoryElement?.querySelector('.category-validation');

      totalCategoryWeight += category.weight;

      let totalSubCriterionWeight = category.subCriteria.reduce((sum, sub) => sum + sub.weight, 0);

      if (category.subCriteria.length > 0) {
        const difference = Math.abs(totalSubCriterionWeight - 1.0);
        if (difference > 0.001) {
          hasWeightErrors = true;
          const percentage = (totalSubCriterionWeight * 100).toFixed(1);
          const errorMsg = '{{ __('cycles.Sub-criterion weights must sum to 100%') }} ({{ __('cycles.currently') }} ' + percentage + '%).';
          
          if (showAlerts && categoryValidationDiv) {
            categoryValidationDiv.innerHTML = `
              ${errorMsg}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('cycles.Close') }}"></button>
            `;
            categoryValidationDiv.className = 'validation-message category-validation alert alert-danger alert-dismissible';
            categoryValidationDiv.style.display = 'block';
            
            const dismissButton = categoryValidationDiv.querySelector('.btn-close');
            if (dismissButton) {
              dismissButton.addEventListener('click', () => {
                categoryValidationDiv.style.display = 'none';
                categoryValidationDiv.className = 'validation-message category-validation';
                categoryValidationDiv.innerHTML = '';
              });
            }
          }
          errorMessages.push(`Category "${category.name || 'Unnamed'}": ${errorMsg}`);
        }
      } else if (category.weight > 0) {
        hasWeightErrors = true;
        const warningMsg = '{{ __('cycles.evaluation_criteria_score_warning') }}';
        
        if (showAlerts && categoryValidationDiv) {
          categoryValidationDiv.innerHTML = `
            ${warningMsg}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('cycles.Close') }}"></button>
          `;
          categoryValidationDiv.className = 'validation-message category-validation alert alert-warning alert-dismissible';
          categoryValidationDiv.style.display = 'block';
          
          const dismissButton = categoryValidationDiv.querySelector('.btn-close');
          if (dismissButton) {
            dismissButton.addEventListener('click', () => {
              categoryValidationDiv.style.display = 'none';
              categoryValidationDiv.className = 'validation-message category-validation';
              categoryValidationDiv.innerHTML = '';
            });
          }
        }
        errorMessages.push(`Category "${category.name || 'Unnamed'}": ${warningMsg}`);
      }
    });

    if (Math.abs(totalCategoryWeight - 1.0) > 0.001) {
      hasWeightErrors = true;
      const percentage = (totalCategoryWeight * 100).toFixed(1);
      const mainError = '{{ __('cycles.Category weights must sum to 100%') }} ({{ __('cycles.currently') }} ' + percentage + '%).';
      errorMessages.unshift(mainError);
    }

    return { isValid: !hasWeightErrors, errors: errorMessages };
  }

  // Returns a message naming the first criterion / sub-criterion whose name
  // exceeds the cap, or null when every name is within it.
  function findNameOverLimit() {
    for (const category of evaluationModelData.categories) {
      if ((category.name || '').trim().length > NAME_MAX_LENGTH) {
        return '{{ __('cycles.evaluation_criterion_name_too_long', ['max' => \App\ScreeningEvaluation::CRITERION_NAME_MAX_LENGTH]) }}';
      }
      for (const sub of (category.subCriteria || [])) {
        if ((sub.name || '').trim().length > NAME_MAX_LENGTH) {
          return '{{ __('cycles.evaluation_subcriterion_name_too_long', ['max' => \App\ScreeningEvaluation::CRITERION_NAME_MAX_LENGTH]) }}';
        }
      }
    }
    return null;
  }

  @if( $status == 'draft' )
    @if( $_user->ableTo('screening.evaluation_publish') )
    function handleSaveModel() {
      fv.validate().then(function(status) {
        if (status === 'Valid') {
          // Call validateWeights with showAlerts = true to display alerts
          const validation = validateWeights(true);
          
          if (!validation.isValid) {
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

          // Remove all alerts
          document.querySelectorAll('.alert').forEach(div => div.remove());

          releaseWarningAlert({
            title: '{{ __('admin.confirm_publication') }}',
            message: '{{ __('admin.evaulation_publish_note') }}',
            onConfirm: publishModel,
          });
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

            // Show success alert
            releaseSuccessAlert({
              title: '{{ __('admin.success') }}',
              message: '{{ __('cycles.Model published successfully!') }}'
            });

            // Hide publish button
            $('#save-model-btn').html('<i class="ti ti-circle-check me-2"></i> {{ __('cycles.Published') }}').prop('disabled', true);

            // Hide Save Draft button
            $('#draft-model-btn').removeClass('d-flex').addClass('d-none');
            
          } else {
            releaseFailureAlert({
              title: '{{ __('admin.error') }}',
              message: response.message ? response.message : '{{ __('admin.unexpected_error') }}'
            });
          }
        },
        error: function(xhr, status, error) {
          const errMsg = xhr.responseJSON?.message ? xhr.responseJSON.message : '{{ __('admin.unexpected_error') }}';
          releaseFailureAlert({
            title: '{{ __('admin.error') }}',
            message: errMsg
          });
        }
      });
    }
    @endif

    @if( $_user->ableTo('screening.evaluation_save_draft') )
    function handleDraftModel() {
      // A draft skips fv.validate() on purpose (partial models are allowed to be
      // saved), but the name cap is a hard limit, so it is enforced here too.
      const tooLong = findNameOverLimit();
      if (tooLong) {
        releaseFailureAlert({
          title: '{{ __('admin.error') }}',
          message: tooLong
        });
        return;
      }

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
            releaseSuccessAlert({
              title: '{{ __('admin.success') }}',
              message: '{{ __('cycles.Draft saved successfully!') }}'
            });
          } else {
            releaseFailureAlert({
              title: '{{ __('admin.error') }}',
              message: response.message ? response.message : '{{ __('admin.unexpected_error') }}'
            });
          }
        },
        error: function(xhr, status, error) {
          const errMsg = xhr.responseJSON?.message ? xhr.responseJSON.message : '{{ __('admin.unexpected_error') }}';
          releaseFailureAlert({
            title: '{{ __('admin.error') }}',
            message: errMsg
          });
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

  // Always render the editor on page load to display saved evaluation data
  renderModelEditor();
});
</script>
@endpush
