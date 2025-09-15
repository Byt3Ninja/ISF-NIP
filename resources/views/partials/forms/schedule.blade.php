
  
<div class="col-md-6 col-4">
    <div class="form-group mb-3">
        <label class="form-label" for="privacy_url">{{ __('admin.Hours') }}</label>
        <input type="text" id="value" name="value" class="form-control" placeholder="{{ __('admin.Hours') }}">
    </div>
    
</div>
<div class="col-md-6 col-4">
    <div class="form-group mb-3">
        <label class="form-label" for="privacy_url">{{ __('admin.Unit') }}</label>
        <select name="unit" class="select form-control" id="unit">
            <option value="hours">{{ __('cycles.Hours') }}</option>
            <option value="minutess">{{ __('cycles.Minutes') }}</option>
        </select>
    </div>
</div>

<button type="submit" id='addSchedule' class="btn btn-primary mr-1 mb-1">{{ __('admin.Add') }}</button>

<div class="table-responsive">
                  <table class="table" id="userstable">
                    <thead class="table-light">
                      <tr>
                        <th>{{ __('admin.Value') }}</th>
                        <th>{{ __('admin.Active') }}</th>
                        <th>{{ __('cycles.Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                        @php
                            if(auth()->user()->hasRole('organizationManager')) {   
                                $scahedules = \App\MentorshipSchedule::where('organization_id', auth()->user()->organization_id)->get();
                            }elseif(auth()->user()->hasRole('Admin')) {
                                $scahedules = \App\MentorshipSchedule::all();
                            }else{
                                $scahedules = [];
                            }
                        @endphp
                      @foreach ($scahedules as $scahedule)
                        <tr>
                          <td>
                            <small>{{ $scahedule->value }} {{ $scahedule->unit }}</small>
                          </td>
                          <td>
                            <div class="form-check form-switch mb-6">
                                <input class="form-check-input toggle-active" data-value="{{ $scahedule->value }}" data-unit="{{ $scahedule->unit }}" data-id="{{ $scahedule->id }}" {{ $scahedule->is_active ? 'checked' : '' }} type="checkbox" id="reminder_enabled" name="reminder_enabled" />
                            </div>
                            
                          </td>
                         
                          <td>
                            <div class="btn-group">
                              <button class="me-1 btn-icon rounded-circle btn btn-sm btn-label-info" onclick="edit({{ $scahedule->id }})" title="Edit">
                                <i class="ti ti-edit"></i>
                              </button>
                              <button class="me-1 btn-icon rounded-circle btn btn-sm btn-label-danger" onclick="deleteme({{ $scahedule->id }})" title="Delete">
                                <i class="ti ti-trash"></i>
                              </button>
                              
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
</div>


 
