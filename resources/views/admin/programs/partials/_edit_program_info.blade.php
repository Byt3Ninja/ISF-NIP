  <div class="row">
    <div class="col-12 text-center mb-4">
      <img 
        src="/images/background/logo.png" 
        class="border" 
        alt="Program Image"
        style="width: 100px; height: 90px; object-fit: contain; border-radius: 15px;"
      >
    </div>
    <div class="col-12">
      <div class="mb-3">
        <label for="organization" class="form-label fs-6 fw-semibold">
          {{ __('cycles.Select_Organization') }}
        </label>
        <select 
          class="form-select" 
          id="organization" 
          name="organization" 
          data-placeholder="{{ __('cycles.Select_Organization') }}"
        >
          <option value="">{{ __('cycles.Select_Organization') }}</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="program-title" class="form-label fs-6 fw-semibold">
          {{ __('cycles.Cycle title') }}
        </label>
        <input type="hidden" name="type" id="type">
        <input type="hidden" name="cycleid" id="cycleid">
        <input 
          type="text" 
          class="form-control" 
          id="program-title" 
          name="program-title" 
          placeholder="{{ __('cycles.Cycle title') }}"
          oninput="this.value = this.value.trimStart()"
          {{-- value="{{ $mode == 'edit' ? $program->title : '' }}" --}}
        >
      </div>
      <div class="row">
        <div class="col-12 col-md-6">
          <div class="mb-3">
            <label for="valid-from" class="form-label fs-6 fw-semibold">
              {{ __('cycles.Valid from') }}
            </label>
            <input 
              type="date" 
              class="form-control" 
              id="start" 
              name="start"
            >
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="mb-3">
            <label for="valid-to" class="form-label fs-6 fw-semibold">
              {{ __('cycles.Valid to') }}
            </label>
            <input 
              type="date" 
              class="form-control" 
              id="end" 
              name="end"
            >
          </div>
        </div>
      </div>
      <div class="mb-3">
        <label for="description" class="form-label fs-6 fw-semibold">
          {{ __('cycles.Description') }}
        </label>
        <textarea 
          class="form-control" 
          id="description" 
          name="description" 
          placeholder="{{ __('cycles.Description') }}" 
          oninput="this.value = this.value.trimStart()"
          rows="3"
        ></textarea>
      </div>
      <div class="mb-3">
        <div class="form-check form-switch mt-2">
          <label class="form-check-label" for="external-participation">
            {{ __('cycles.External_Participation_Label') }}
          </label>
          <input 
            class="form-check-input" 
            type="checkbox" 
            id="external-participation" 
            name="external-participation"
          />
        </div>
      </div>
      <div class="form-check form-switch mb-3">
        <label class="form-check-label" for="private-link">
          {{ __('cycles.Private Link') }}
        </label>
        <input 
          class="form-check-input" 
          type="checkbox" 
          id="private-link" 
          name="private-link"
        />
      </div>
    </div>
  </div>
