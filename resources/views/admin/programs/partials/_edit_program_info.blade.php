<div class="row">
  <div class="col-12 mb-12">
    <!-- Upload Avatar -->
    <div class="d-flex align-items-start align-items-sm-center gap-6">
      <div class="position-relative">
        <label for="upload" class="cursor-pointer">
          <div class="d-flex align-items-center justify-content-center border shadow-sm rounded bg-label-light"
            style="width: 100px; height: 100px;">
            <img id="uploadImage" src="/{{ $ccycle?->img_url }}" alt="{{ __('admin.Preview') }}" class="img-fluid rounded" style="max-width: 100%; max-height: 100%;">
            @if(empty($ccycle->img_url))
            <i class="ti ti-camera fs-4 text-muted" id="uploadIcon"></i>
            @endif
          </div>
        </label>
        <input hidden type="file" name="img_url" class="program-image-input" id="upload"
          accept="image/png, image/jpg, image/jpeg" />
      </div>
      <div class="d-flex flex-column">
        <div class="d-flex align-items-center gap-3 mb-4">
          <button type="button" class="btn btn-label-primary upload-img">
            <i class="ti ti-upload"></i>
            <span class="d-none d-md-flex ms-2">{{ __('admin.Upload') }}</span>
          </button>
          <button type="button" class="btn btn-label-secondary program-image-reset">
            <i class="ti ti-refresh"></i>
            <span class="d-none d-md-flex ms-2">{{ __('admin.Reset') }}</span>
          </button>
        </div>
        <div>{{ __('admin.Image_Format') }}</div>
      </div>
    </div>
    <!-- /Upload Avatar -->
  </div>
  @php
  $user = auth()->user();
  $isOrgMan = $user->hasRole('organizationManager');
  $org_id = $user->organization_id ?? 0;
  @endphp
  <div class="col-12">
    <div class="mb-6 {{ $isOrgMan ? 'd-none' : '' }}">
      <label for="organization" class="form-label fs-6 fw-semibold">
        {{ __('cycles.Select_Organization') }}
      </label>
      <select class="form-select" id="organization" name="organization_id2"
        data-placeholder="{{ __('cycles.Select_Organization') }}">
        @foreach (\App\Organization::all() as $org)
        @php if( $isOrgMan && $org->id != $org_id ) { continue; } @endphp
        <option value="{{ $org->id }}">{{ $org->{'name_' . app()->getLocale()} }}</option>
        @endforeach
      </select>
    </div>
    <div class="mb-6">
      <label for="program-title" class="form-label fs-6 fw-semibold">
        {{ __('cycles.Cycle title') }}
      </label>
      <input type="hidden" name="type" id="type">
      <input type="hidden" name="cycleid" id="cycleid">
      <input type="hidden" name="start" id="start" />
      <input type="hidden" name="end" id="end" />

      <input type="text" class="form-control" id="title" name="title" placeholder="{{ __('cycles.Cycle title') }}"
        oninput="this.value = this.value.trimStart()" required>
    </div>
    <div class="col-12 mb-4">
      <label for="date-range-picker" class="form-label fs-6 fw-semibold">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
      <input type="text" name="date_range" id="date-range-picker" class="form-control" />
    </div>
    {{-- <div class="row">
      <div class="col-12 col-md-6">
        <div class="mb-6">
          <label for="valid-from" class="form-label fs-6 fw-semibold">
            {{ __('cycles.Valid from') }}
          </label>
          <div class="input-group input-group-merge">
            <input id="start" type="text" name="start" class="form-control valid-from"
              placeholder="{{ __('cycles.Valid from') }}" />
            <span class="input-group-text">
              <i class="ti ti-calendar text-light"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="mb-6">
          <label for="valid-to" class="form-label fs-6 fw-semibold">
            {{ __('cycles.Valid to') }}
          </label>
          <div class="input-group input-group-merge">
            <input id="end" type="text" name="end" class="form-control valid-to"
              placeholder="{{ __('cycles.Valid to') }}" />
            <span class="input-group-text">
              <i class="ti ti-calendar text-light"></i>
            </span>
          </div>
        </div>
      </div>
    </div> --}}
    <div class="mb-6">
      <label for="description" class="form-label fs-6 fw-semibold">
        {{ __('cycles.Description') }}
      </label>
      <textarea class="form-control" id="description" name="description" placeholder="{{ __('cycles.Description') }}"
        oninput="this.value = this.value.trimStart()" rows="3" required></textarea>
    </div>
    <div class="mb-6">
      <div class="form-check form-switch mt-2">
        <label class="form-check-label" for="external-participation">
          {{ __('cycles.External_Participation_Label') }}
        </label>
        <input class="form-check-input" type="checkbox" id="allow_non_company" name="allow_non_company" />
      </div>
    </div>
    <div class="form-check form-switch mb-6">
      <label class="form-check-label" for="private-link">
        {{ __('cycles.Private Link') }}
      </label>
      <input class="form-check-input" type="checkbox" id="private" name="private" />
    </div>
  </div>
</div>