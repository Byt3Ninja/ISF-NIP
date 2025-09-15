<div class="table-responsive">
                  <table class="table" id="userstable">
                    <thead class="table-light">
                      <tr>
                        <th>{{ __('admin.User') }}</th>
                        <th>{{ __('admin.Type') }}</th>
                        <th>{{ __('admin.Program') }}</th>
                        <th>{{ __('admin.Channel') }}</th>
                        <th>{{ __('admin.Status') }}</th>
                        <th>{{ __('admin.Date') }}</th>
                        <th>{{ __('cycles.Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                        @php
                            if(auth()->user()->hasRole('organizationManager')) {
                                $cycles = \App\Cycle::where('organization_id', auth()->user()->organization_id)->get(); 
                                $ReminderLogs = \App\ReminderLog::whereIn('cycle_id', $cycles->pluck('id'))->get();
                            }elseif(auth()->user()->hasRole('Admin')) {
                                $ReminderLogs = \App\ReminderLog::all();
                            }else{
                                $ReminderLogs = [];
                            }
                        @endphp
                      @foreach ($ReminderLogs as $ReminderLog)
                        <tr>
                          <td>
                            <small>{{ $ReminderLog->user->{'first_name_' . app()->getLocale()} }} {{ $ReminderLog->user->{'last_name_' . app()->getLocale()} }}</small>
                          </td>
                          <td>
                            <small>{{ $ReminderLog->type }}</small>
                          </td>
                          <td>
                            <small>{{ $ReminderLog->cycle->title }}</small> 
                          </td>
                          <td>
                            <small>{{ $ReminderLog->channel }}</small>
                          </td>
                          <td>
                            <small>{{ __('admin.' . $ReminderLog->status) }}</small>
                          </td>
                         
                          <td>
                            <div class="btn-group">
                              
                            
                              
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
</div>