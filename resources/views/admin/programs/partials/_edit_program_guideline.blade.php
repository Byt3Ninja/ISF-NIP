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
      required
    >
    </textarea>
  </div>
  <div class="form-group mb-6">
    <label for="program-guideline-pdf" class="form-label fs-6 fw-semibold">
      {{ __('cycles.Attach_Document') }} ({{__('cycles.PDF_Only')}})
    </label>
    <div class="d-flex justify-content-center align-items-center border rounded p-4">
      <input 
        type="file" 
        name="program-guideline-pdf" 
        id="program-guideline-pdf" 
        class="form-control-file" 
        accept="application/pdf" 
        required
      >
    </div>
  </div>
  <div class="d-flex align-items-center justify-content-end gap-3 mt-12">
    @include('partials.modal.modal-dismiss-button')
    <button type="submit" class="btn btn-primary w-px-100">
      {{ __('cycles.Save') }}
    </button>
  </div>
</form>