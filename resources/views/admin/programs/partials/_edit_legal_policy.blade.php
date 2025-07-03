<div class="row">
  <div class="form-group mb-4">
    <label for="legal_policy" class="form-label fs-6 fw-semibold">{{ __('cycles.Legal_Policy_Description') }}</label>
    <div style="height: 150px; overflow-y: hidden;" class="border border-light rounded">
      <div id="legal-editor" class="form-control border-none"></div>
    </div>
    <textarea 
      name="legal_policy" 
      id="legal_policy"
      class="form-control legal d-none" 
      rows="5"
      placeholder="{{ __('cycles.legal_policy') }}"
    >
    </textarea>
  </div>
  <div class="form-group mb-6">
    <label for="legal_policy_pdf" class="form-label fs-6 fw-semibold">
      {{ __('cycles.Attach_Document') }} ({{__('cycles.PDF_Only')}})
    </label>
    <div class="mb-3">
      <input 
        type="file" 
        name="legal_policy_pdf" 
        id="legal_policy_pdf" 
        class="form-control" 
        accept="application/pdf" 
      />
      <input type="hidden" name="type" id="type">
      <input type="hidden" name="cycleid" id="cycleid">
      <input type="hidden" name="organization_id" value="@if(isset($cycle)) {{ optional($cycle)->organization_id }} @else 0 @endif">
    </div>
  </div>
  <div id="legal-file-container" class="mb-4"></div>
  <div class="form-group mb-4">
    <div class="form-check form-switch">
      <input 
        class="form-check-input" 
        type="checkbox" 
        name="delegation_option" 
        id="delegation_option"
        onchange="toggleFormGroup(this)"
        value="yes"
      />
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
        id="delegation_pdf" 
        class="form-control-file" 
        accept="application/pdf" 
      />
    </div>
    <div id="delegation-file-container" class="mb-4"></div>
  </div>
</div>

<script>
  function toggleFormGroup(radio) {
    // console.log(radio.checked);
    const formGroups = document.querySelectorAll('#delegation-form-group');
    formGroups.forEach(formGroup => {
      if (radio.checked) {
        formGroup.style.display = 'block';
      } else {
        formGroup.style.display = 'none';
      }
    });
  }
</script>