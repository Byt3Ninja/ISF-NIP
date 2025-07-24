
<div class="row mb-12">
  <div class="form-group mb-6">
    <label for="program-guideline" class="form-label fs-6 fw-semibold">
      {{ __('cycles.Program_Guideline') }}
    </label>
    <div style="height: 150px; overflow-y: hidden;" class="border border-light rounded">
      <div id="guideline-editor" class="form-control border-none"></div>
    </div>
    <textarea 
      name="program_guideline" 
      id="program_guideline"
      class="form-control guidline d-none" 
      rows="5"
      placeholder="{{ __('cycles.Enter_Program_Guideline') }}"
    >
    </textarea>
    <input type="hidden" name="type" id="type">
    <input type="hidden" name="cycleid" id="cycleid">
    <input type="hidden" name="organization_id" value="@if(isset($cycle)) {{ optional($cycle)->organization_id }} @else 0 @endif">
  </div>
  <div class="form-group mb-6">
    <label for="program-guideline-pdf" class="form-label fs-6 fw-semibold">
      {{ __('cycles.Attach_Document') }} ({{__('cycles.PDF_Only')}})
    </label>
    <div class="mb-3">
      <input 
        type="file" 
        name="program-guideline-pdf" 
        id="program-guideline-pdf" 
        class="form-control" 
        accept="application/pdf"  
      />
    </div>
  </div>
  <div id="guideline-file-container" class="mb-4"></div>
</div>  
