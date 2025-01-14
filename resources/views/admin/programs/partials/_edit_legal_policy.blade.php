<div class="form-group mb-4">
  <label for="legal_policy" class="form-label fs-6 fw-semibold">{{ __('cycles.Legal_Policy_Description') }}</label>
  <textarea 
    name="legal_policy" 
    id="text-editor" 
    class="form-control" 
    placeholder="{{ __('cycles.Legal_Policy_Description') }}"
    rows="5" 
    oninput="this.value = this.value.trimStart()">
  </textarea>
</div>
<div class="form-group mb-6">
  <label for="legal_policy_pdf" class="form-label fs-6 fw-semibold">
    {{ __('cycles.Attach_Document') }} ({{__('cycles.PDF_Only')}})
  </label>
  <div class="d-flex justify-content-end align-items-center border rounded p-4">
    <input 
      type="file" 
      name="legal_policy_pdf" 
      id="legal_policy_pdf" 
      class="form-control-file" 
      accept="application/pdf" 
    />
    <input type="hidden" name="type" id="type">
    <input type="hidden" name="cycleid" id="cycleid">
    <input type="hidden" name="tab_id" value="4">
    <input type="hidden" name="organization_id" value="{{ $mode == 'create'? $cycle->organization_id : '' }}">
  </div>
</div>
<div class="form-group mb-4">
  <div class="form-check">
    <input 
      class="form-check-input" 
      type="radio" 
      name="delegation_option" 
      id="delegation_option" 
      value="yes" 
      onclick="toggleFormGroup(this.checked)">
    <label class="form-check-label" for="delegation_option">
      {{ __('cycles.Delegation_Option') }}
    </label>
  </div>
</div>
<div class="form-group mb-6" id="delegation-form-group" style="display: none;">
  <label for="legal_policy_pdf" class="form-label fs-6 fw-semibold">
    {{ __('cycles.Attach_Delegation_Document') }} ({{__('cycles.PDF_Only')}})
  </label>
  <div class="d-flex justify-content-end align-items-center border rounded p-4">
    <input 
      type="file" 
      name="delegation_pdf" 
      id="legal_policy_pdf" 
      class="form-control-file" 
      accept="application/pdf" 
      >
  </div>
</div>


<script>
  function toggleFormGroup(show) {
    const formGroup = document.querySelector('#delegation-form-group');
    if (show) {
      formGroup.style.display = 'block';
    } else {
      formGroup.style.display = 'none';
    }
  }

  // Initialize form group visibility based on the default selected checkbox state
  document.addEventListener('DOMContentLoaded', function() {
    toggleFormGroup(document.getElementById('delegation_option').checked);
  });
</script>