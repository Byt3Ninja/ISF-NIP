<div class="row mb-12">
  <div class="form-group mb-6">
    <h5>
      {{ __('cycles.Auto_Approve_Mentorship_Requests') }}
    </h5>
    <div class="form-check form-switch">
      <input 
        class="form-check-input" 
        type="checkbox" 
        id="auto-approve-mentorship-requests" 
        name="auto_approve_mentorship_requests"
       
      >
      <label class="form-check-label" for="auto-approve-mentorship-requests">
        {{ __('cycles.Enable_Auto_Approval') }}
      </label>
    </div>
    <small class="text-muted">
      {{ __('cycles.Auto_Approve_Helper_Text') }}
    </small>

    <input type="hidden" name="type" id="type">
    <input type="hidden" name="cycleid" id="cycleid">
    <input type="hidden" name="organization_id" value="@if(isset($cycle)) {{ optional($cycle)->organization_id }} @else 0 @endif">
  </div>
</div>
