<form action="{{ route('cycles.modify') }}" method="POST" enctype="multipart/form-data">
  @csrf
  <div class="row mb-4">
    <div class="col-8">
      <div class="form-group">
        <label for="budget_allocation" class="form-label fs-6 fw-semibold">{{ __('cycles.Budget_Allocation') }}</label>
        <input 
          type="number" 
          name="budget_allocation" 
          id="budget_allocation" 
          class="form-control" 
          placeholder="{{ __('cycles.Budget_Allocation') }}"
        />
      </div>
    </div>
    <div class="col-4">
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
          <!-- Add more currencies as needed -->
        </select>
      </div>
    </div>
  </div>

  <div class="form-group mb-4">
    <label for="financial_policy" class="form-label fs-6 fw-semibold">{{ __('cycles.Financial_Policy') }}</label>
    <textarea 
      name="financial_policy" 
      id="text-editor" 
      class="form-control" 
      rows="5" 
      oninput="this.value = this.value.trimStart()"
    ></textarea>
  </div>
  <div class="form-group mb-6">
    <label for="financial_policy_pdf" class="form-label fs-6 fw-semibold">
      {{ __('cycles.Attach_Document') }} ({{__('cycles.PDF_Only')}})
    </label>
    <div class="d-flex justify-content-center align-items-center gap-4 border rounded p-4">
      <input 
        type="file" 
        name="financial_policy_pdf" 
        id="financial_policy_pdf" 
        class="form-control-file"
        accept="application/pdf" 
      />
    </div>
  </div>
  <div class="d-flex align-items-center justify-content-end gap-3 mt-12">
    <button type="button" class="btn btn-secondary me-2 w-px-100" data-bs-dismiss="modal">{{ __('cycles.Cancel') }}</button>
    <button type="submit" class="btn btn-primary w-px-100">{{ __('cycles.Save') }}</button>
  </div>
</form>

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
</script>