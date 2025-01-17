
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
        name="organization_id" 
        data-placeholder="{{ __('cycles.Select_Organization') }}"
      >
        @php
          use App\Organization;
        @endphp
        @foreach (Organization::all() as $org)
          <option value="{{ $org->id }}" {{ $org->id == $cycle->organization_id ? 'selected' : ''}}>{{ $org->{'name_'.app()->getLocale()} }}</option>
        @endforeach
      </select>
    </div>
    <div class="mb-3">
      <label for="program-title" class="form-label fs-6 fw-semibold">
        {{ __('cycles.Cycle title') }}
      </label>
      <input type="hidden" name="type" id="type">
      <input type="hidden" name="cycleid" id="cycleid">
      <input type="hidden" name="tab_id" value="1">

      <input 
        type="text" 
        class="form-control" 
        id="title" 
        name="title" 
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
          <div class="input-group input-group-merge">
            <input 
              id="start" 
              type="text" 
              name="start" 
              class="form-control valid-date" 
              placeholder="YYYY-MM-DD"
            />
            <span class="input-group-text">
              <i class="ti ti-calendar"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="mb-3">
          <label for="valid-to" class="form-label fs-6 fw-semibold">
            {{ __('cycles.Valid to') }}
          </label>
          <div class="input-group input-group-merge">
            <input 
              id="end" 
              type="text" 
              name="end" 
              class="form-control valid-date" 
              placeholder="YYYY-MM-DD"
            />
            <span class="input-group-text">
              <i class="ti ti-calendar"></i>
            </span>
          </div>
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
          id="allow_non_company" 
          name="allow_non_company"
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
        id="private" 
        name="private"
      />
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const validDate = document.querySelectorAll('.valid-date');
    if (validDate) {
      validDate.flatpickr({
        monthSelectorType: 'static'
      });
    }
  });
</script>
