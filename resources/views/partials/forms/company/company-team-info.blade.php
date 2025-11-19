<style>
  .cursor-pointer {
    cursor: pointer;
  }
</style>


@if( session()->has('success') )
<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  <div class="d-flex align-items-center">
    <i class="ti ti-thumb-up me-3 ti-md"></i>
    <span> {{ session()->get('success') }} </span>
  </div>
</div>
@endif

@if( session()->has('error') )
<div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <div class="d-flex align-items-center">
      <i class="ti ti-alert-triangle me-3 ti-md"></i>
      <span> {{ session()->get('error') }} </span>
    </div>
</div>
@endif

<div class="row TapPageTeam">
  {{-- <div class="col-md-12 mb-3">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title border-bottom pb-2 mb-6">{{ __('admin.Working_Team_Details') }}</h5>
        <form method="post" 
          enctype="multipart/form-data"
          action="{{ 
            Auth::user()->hasanyRole('Admin|organizationManager') 
            ? route('company.profile_update_mine', $client->company->id) 
            : route('company.profile_update_mine') }}" 
        >
          @csrf
          <input type="hidden" value="5" name="CompanyN">
          <div class="row">
            <div class="col-12 mb-3">
              <div class="form-label-group mt-75">
                <label class="form-label fw-semibold fs-6 mb-2" for="team_num">{{ __('admin.team_num') }}</label>
                <select class="form-control" id="team_num" name="team_num">
                  <option value="" disabled>{{ __('admin.team_num') }}</option>
                  <option value="1" {{ $client->company && $client->company->team_num == '1' ? 'selected' : '' }}>Self-employed</option>
                  <option value="2" {{ $client->company && $client->company->team_num == '2' ? 'selected' : '' }}>1-10 employees</option>
                  <option value="3" {{ $client->company && $client->company->team_num == '3' ? 'selected' : '' }}>11-50 employees</option>
                  <option value="4" {{ $client->company && $client->company->team_num == '4' ? 'selected' : '' }}>51-200 employees</option>
                  <option value="5" {{ $client->company && $client->company->team_num == '5' ? 'selected' : '' }}>201-500 employees</option>
                  <option value="6" {{ $client->company && $client->company->team_num == '6' ? 'selected' : '' }}>501-1000 employees</option>
                  <option value="7" {{ $client->company && $client->company->team_num == '7' ? 'selected' : '' }}>1001-5000 employees</option>
                  <option value="8" {{ $client->company && $client->company->team_num == '8' ? 'selected' : '' }}>5001-10000 employees</option>
                  <option value="9" {{ $client->company && $client->company->team_num == '9' ? 'selected' : '' }}>10001+ employees</option>
                </select>
              </div>
            </div>
            <div class="col-12 mb-6">
              <div class="form-label-group mt-75">
                <label class="form-label fw-semibold fs-6 mb-2" for="working_team_summary">{{ __('admin.Working_Team_Details') }}</label>
                <textarea 
                  oninput="this.value = this.value.trimStart()"
                  pattern="[A-Za-z\s]*"
                  maxlength="255"
                  class="form-control" id="working_team_summary" placeholder="{{ __('admin.Working_Team_Details') }}" value="" name="working_team_summary" autocomplete="new-working_team_summary">
                  {{ $client->company ? $client->company->working_team_summary : old('working_team_summary') }}</textarea>
              </div>
            </div>
            <div class="col-12">
              <div class="w-100 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" id="submit-button">
                  <span id="button-text">
                    {{ __('admin.Update') }}
                    <i class="ti ti-edit ms-2"></i>
                  </span>
                  <span id="button-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div> --}}
  @include('partials.forms.company._founder_team')
  @include('partials.forms.company._shareholder_team')
  @include('partials.forms.company._investor_team')

</div>

<script>
  function togglePopup(id) {
    const popup = document.getElementById(`popup-${id}`);
    popup.style.display = popup.style.display === 'none' ? 'block' : 'none';
  }

  // Close the popup if clicked outside
  document.addEventListener('click', function(event) {
    const popups = document.querySelectorAll('.popup-menu z-3');
    popups.forEach(popup => {
      if (!popup.contains(event.target) && !popup.previousElementSibling
        .contains(event.target)) {
        popup.style.display = 'none';
      }
    });
  });

  function resetForm(form) {
    form.reset();
  }
</script>