<form>
  <div class="row">
    <div class="col-12 col-md-2 text-center text-md-start mb-4">
      <img 
        src="/images/background/logo.png" 
        class="border" 
        alt="Program Image"
        style="width: 100px; height: 90px; object-fit: contain; border-radius: 15px;"
      >
    </div>
    <div class="col-12 col-md-10">
      <div class="mb-3">
        <label for="organization" class="form-label fs-6 fw-semibold">
          Select Organization
        </label>
        <select 
          class="form-select" 
          id="organization" 
          name="organization" 
          data-placeholder="Select Organization"
        >
          <option value="">Select Organization</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="program-title" class="form-label fs-6 fw-semibold">
          Program Title
        </label>
        <input 
          type="text" 
          class="form-control" 
          id="program-title" 
          name="program-title" 
          placeholder="Enter Program Title"
          oninput="this.value = this.value.trimStart()"
          {{-- value="{{ $mode == 'edit' ? $program->title : '' }}" --}}
        >
      </div>
      <div class="row">
        <div class="col-12 col-md-6">
          <div class="mb-3">
            <label for="valid-from" class="form-label fs-6 fw-semibold">
              Valid From
            </label>
            <input 
              type="date" 
              class="form-control" 
              id="valid-from" 
              name="valid-from"
            >
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="mb-3">
            <label for="valid-to" class="form-label fs-6 fw-semibold">
              Valid To
            </label>
            <input 
              type="date" 
              class="form-control" 
              id="valid-to" 
              name="valid-to"
            >
          </div>
        </div>
      </div>
      <div class="mb-3">
        <label for="description" class="form-label fs-6 fw-semibold">
          Description
        </label>
        <textarea 
          class="form-control" 
          id="description" 
          name="description" 
          placeholder="Enter description" 
          oninput="this.value = this.value.trimStart()"
          rows="3"
        ></textarea>
      </div>
      <div class="mb-6">
        <span class="form-label fs-6 fw-semibold" for="private-link">
          Enable or disable participations for external participants
        </span>
        <div class="form-check form-switch mt-2">
          <input 
            class="form-check-input" 
            type="checkbox" 
            id="external-participation" 
            name="external-participation"
          >
          <label class="form-check-label" for="external-participation">
            Enable External Participation
          </label>
        </div>
      </div>
      <div class="form-check mb-3">
        <input 
          class="form-check-input" 
          type="checkbox" 
          id="private-link" 
          name="private-link"
        >
        <label class="form-check-label" for="private-link">
          Private Link
        </label>
      </div>
    </div>
  </div>
  <div class="d-flex align-items-center justify-content-end gap-3 mt-12">
    <button 
      type="button" 
      class="btn btn-secondary me-2 w-px-100" 
      data-bs-dismiss="modal"
    >
      {{ __('cycles.Cancel') }}
    </button>
    <button type="submit" class="btn btn-primary w-px-100">
      {{ __('cycles.Save') }}
    </button>
  </div>
</form>