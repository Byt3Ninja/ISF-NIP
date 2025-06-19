
<div class="row mb-12">
  <div class="col-12 col-md-8 mb-6">
    <div class="form-group">
      <label for="budget_allocation" class="form-label fs-6 fw-semibold">{{ __('cycles.Budget_Allocation') }}</label>
      <input 
        type="number" 
        name="budget_allocation" 
        id="budget_allocation" 
        class="form-control" 
        placeholder="{{ __('cycles.Budget_Allocation') }}"
      />
      <input type="hidden" name="type" id="type">
      <input type="hidden" name="cycleid" id="cycleid">
      <input type="hidden" name="organization_id" value="@if(isset($cycle)) {{ optional($cycle)->organization_id }} @else 0 @endif">
    </div>
  </div>
  <div class="col-12 col-md-4 mb-6">
    <div class="form-group">
      <label for="currency_type" class="form-label fs-6 fw-semibold">{{ __('cycles.Currency_Type') }}</label>
      <select 
        name="currency_type" 
        id="currency_type" 
        class="form-select" 
        placeholder="{{ __('cycles.Currency_Type') }}"
      >
        <option value="USD">USD</option>
        <option value="EUR">EUR</option>
        <option value="GBP">GBP</option>
        <option value="SAR">SAR (ريال)</option>
        <option value="EGP">EGP (الجنيه المصري)</option>
      </select>
    </div>
  </div>
  <div class="form-group mb-6">
    <label for="financial_policy" class="form-label fs-6 fw-semibold">{{ __('cycles.Financial_Policy') }}</label>
    <div style="height: 150px; overflow-y: hidden;" class="border border-light rounded">
      <div id="financial-editor" class="form-control border-none"></div>
    </div>
    <textarea 
      name="financial_policy"
      id="financial_policy"
      class="form-control policy d-none" 
      rows="5"
      placeholder="{{ __('cycles.Financial_Policy') }}"
    >
    </textarea>
  </div>
  <div class="form-group mb-6">
    <label for="financial_policy_pdf" class="form-label fs-6 fw-semibold">
      {{ __('cycles.Attach_Document') }} ({{__('cycles.PDF_Only')}})
    </label>
    <div class="mb-3">
      <input 
        type="file" 
        name="financial_policy_pdf" 
        id="financial_policy_pdf" 
        class="form-control" 
        accept="application/pdf" 
      />
    </div>
  </div>
  <div id="financial-file-container" class="mb-6"></div>
</div>
{{-- 
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const budgetCategoriesInput = document.querySelector('#budget_categories');
    TagsCustomInlineSuggestion = new Tagify(budgetCategoriesInput, {
      whitelist: ['marketing', 'research', 'development', 'operations'],
      maxTags: 10,
      dropdown: {
        maxItems: 20,
        classname: 'tags-inline',
        enabled: 0,
        closeOnSelect: false
      }
    }).on('change', handleTagifyChange);

    function handleTagifyChange(e) {
      selected_budget_categories =  e.detail.value.trim() ? selected_budget_categories = JSON.parse( e.detail.value ) : [];
    }
  });      
</script> --}}