<form action="{{ route('cycles.modify') }}" method="POST" enctype="multipart/form-data">
  @csrf
<div class="form-group mb-6">
  <label for="program-guideline" class="form-label fs-6 fw-semibold">
    {{ __('cycles.Program_Guideline') }}
  </label>
  <textarea 
    name="program_guideline" 
    id="text-editor" 
    class="form-control" 
    rows="5"
    placeholder="{{ __('cycles.Enter_Program_Guideline') }}"
    
  >
  </textarea>
  <input type="hidden" name="type" id="type">
  <input type="hidden" name="cycleid" id="cycleid">
  <input type="hidden" name="tab_id" value="2">
  <input type="hidden" name="organization_id" value="{{ $mode == 'create'? $cycle->organization_id : '' }}">
</div>
<div class="form-group mb-6">
  <label for="program-guideline-pdf" class="form-label fs-6 fw-semibold">
    {{ __('cycles.Attach_Document') }} ({{__('cycles.PDF_Only')}})
  </label>
  <div class="d-flex justify-content-end align-items-center border rounded p-4">
    <input 
      type="file" 
      name="program-guideline-pdf" 
      id="program-guideline-pdf" 
      class="form-control-file" 
      accept="application/pdf" 
      
    >
  </div>

</div>
<div class="d-flex align-items-center justify-content-end gap-3 mt-4 p-4 p-md-0">
  <button type="submit" class="btn btn-primary w-px-100">
    {{ __('cycles.Save') }}
    <i class="ti ti-circle-check ms-2"></i>
  </button>
  <button type="button" class="btn btn-outline-secondary w-px-100" data-bs-dismiss="modal">
    {{ __('cycles.Cancel') }}
    <i class="ti ti-circle-x ms-2"></i>
  </button>
</div>

</form>