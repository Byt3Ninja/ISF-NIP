<div class="row">
  <!-- Achieved Milestones Column -->
  <div class="col-md-6">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title">{{ __('admin.Achieved_Milestones') }}</h5>
        <form method="post" action="{{ route('update_my_company') }}" enctype="multipart/form-data" onsubmit="resetForm(this)">
          {!! csrf_field() !!}
          <div class="show_Achieved_Milestones row m-2 border radius-15 light-shadow">
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control mt-2 achieved_milestone_name light-shadow" id="achieved_milestone_name" placeholder="{{ __('admin.Milestone_Name') }}" value="{{ $client->company ? $client->company->achieved_milestone_name : old('achieved_milestone_name') }}" name="achieved_milestone_name" autocomplete="achieved_milestone_name">
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control my-2 achieved_milestone_description light-shadow" id="achieved_milestone_description" placeholder="{{ __('admin.Description') }}" value="{{ $client->company ? $client->company->achieved_milestone_description : old('achieved_milestone_description') }}" name="achieved_milestone_description" autocomplete="achieved_milestone_description">
              </div>
            </div>
          </div>
          <div class="col-12 text-center mt-2" style="height: 40px;">
            <button type="submit" class="btn btn-outline-primary text-black fw-bold rounded-pill">
              <i class="ti ti-plus me-3"></i>
              {{ __('admin.Add') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Planned Milestones Column -->
  <div class="col-md-6">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title">{{ __('admin.Planned_Milestones') }}</h5>
        <form method="post" action="{{ route('update_my_company') }}" enctype="multipart/form-data" onsubmit="resetForm(this)">
          {!! csrf_field() !!}
          <div class="show_Planned_Milestones row m-2 border radius-15 light-shadow">
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control mt-2 planned_milestone_name light-shadow" id="planned_milestone_name" placeholder="{{ __('admin.Milestone_Name') }}" value="{{ $client->company ? $client->company->planned_milestone_name : old('planned_milestone_name') }}" name="planned_milestone_name" autocomplete="planned_milestone_name">
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control my-2 planned_milestone_description light-shadow" id="planned_milestone_description" placeholder="{{ __('admin.Description') }}" value="{{ $client->company ? $client->company->planned_milestone_description : old('planned_milestone_description') }}" name="planned_milestone_description" autocomplete="planned_milestone_description">
              </div>
            </div>
          </div>
          <div class="col-12 text-center mt-2" style="height: 40px;">
            <button type="submit" class="btn btn-outline-primary text-black fw-bold rounded-pill">
              <i class="ti ti-plus me-3"></i>
              {{ __('admin.Add') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function resetForm(form) {
    form.reset();
  }
</script>