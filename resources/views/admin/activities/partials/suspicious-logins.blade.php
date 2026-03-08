<div class="tab-pane fade show active" id="Suspicious_Logins" role="tabpanel" aria-labelledby="Suspicious_Logins-tab">
  <div class="row g-4 mb-4">
    <div class="col-md-12">
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between bg-white">
          <div class="d-flex align-items-center gap-2">
            <i class="ti ti-alert-triangle text-danger"></i>
            <h5 class="mb-0">@lang('admin.Suspicious_Logins')</h5>
          </div>
          <div class="d-flex gap-2">
            <input type="search" class="form-control form-control-sm" id="searchSuspiciousUsers"
              placeholder="@lang('admin.Search')" style="max-width: 200px;" />
            <div class="btn-group">
              <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="ti ti-download me-1"></i> @lang('admin.Export')
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="javascript:void(0);" onclick="exportSuspiciousLogins('csv')"><i
                      class="ti ti-file-type-csv me-2"></i>CSV</a></li>
                {{-- <li><a class="dropdown-item" href="javascript:void(0);"
                    onclick="exportSuspiciousLogins('excel')"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                --}}
              </ul>
            </div>
          </div>
        </div>

        @if ($suspiciousLogins->isNotEmpty())
          <div class="list-group list-group-flush" id="suspiciousUsersList">
            @foreach ($suspiciousLogins as $login)
            <div class="list-group-item suspicious-user-card"
              data-user="{{ strtolower($login->user->first_name_en . ' ' . $login->user->last_name_en . ' ' . $login->user->email) }}">
              <div class="p-3 d-flex flex-wrap align-items-center justify-content-between">
                <a href="/dashboard/show/UserProfile/{{ $login->user->id }}" class="d-flex align-items-center gap-3">
                  <div class="avatar avatar-lg">
                    @if($login->user->img_url ?? false)
                    <img src="{{ asset('191014/' . $login->user->img_url) }}" alt="avatar"
                      class="rounded-circle border shadow-xs" onerror="this.onerror=null; this.src='/icons/account.svg';">
                    @else
                    <div class="avatar avatar-lg avatar-initial rounded-circle bg-label-primary">
                      {{ strtoupper(substr($login->user->first_name_en ?? 'U', 0, 1)) }}{{
                      strtoupper(substr($login->user->last_name_en ?? 'U', 0, 1)) }}
                    </div>
                    @endif
                  </div>
                  <div>
                    <div class="fw-bold text-dark">{{ $login->user->first_name_en }} {{ $login->user->last_name_en }}
                    </div>
                    <div class="text-muted small">{{ $login->user->email }}</div>
                  </div>
                </a>

                <div class="d-flex flex-wrap gap-4">
                  <div class="text-muted small"><strong class="text-dark">{{ $login->login_count }}</strong>
                    @lang('admin.Logins')</div>
                  <div class="text-muted small"><strong class="text-dark">{{ $login->location_count }}</strong>
                    @lang('admin.Locations')</div>
                  <div class="text-muted small">@lang('admin.Last') <strong class="text-dark">{{
                      \Carbon\Carbon::parse($login->last_login)->format('d M Y, H:i') }}</strong></div>
                  <span class="badge 
                                      @if($login->risk_level == 'high') bg-label-danger 
                                      @elseif($login->risk_level == 'medium') bg-label-warning 
                                      @else bg-label-success 
                                      @endif
                                      align-self-center fw-semibold">
                    @if($login->risk_level == 'high') @lang('admin.High_Risk')
                    @elseif($login->risk_level == 'medium') @lang('admin.Medium_Risk')
                    @else @lang('admin.Low_Risk')
                    @endif
                  </span>
                </div>

                <div class="d-flex gap-2">
                  <button class="btn btn-icon btn-outline-danger btn-sm" onclick="forceLogout({{ $login->user_id }})"
                    title="@lang('admin.Force_Logout')" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ti ti-power"></i>
                  </button>
                  <button class="btn btn-icon btn-outline-warning btn-sm" onclick="resetPassword({{ $login->user_id }})"
                    title="@lang('admin.Reset_Password')" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ti ti-shield-lock"></i>
                  </button>
                  <button class="btn btn-icon btn-outline-secondary btn-sm" data-bs-toggle="collapse"
                    data-bs-target="#u{{ $login->user_id }}Details" title="@lang('admin.View_Details')"
                    data-bs-placement="top">
                    <i class="ti ti-list"></i>
                  </button>
                </div>
              </div>

              <!-- Country summary -->
              <div class="px-3 pb-3">
                <div class="d-flex flex-wrap gap-2">
                  @foreach($login->location_counts as $location => $count)
                  @if($location && $location !== 'Unknown' && $location !== '')
                  <small class="badge bg-label-light text-dark py-1">
                    <i class="ti ti-xs ti-map-pin me-1"></i>{{ $location }} ({{ $count }})
                  </small>
                  @endif
                  @endforeach
                  @if(isset($login->location_counts['Unknown']) || isset($login->location_counts['']) ||
                  in_array('Unknown', $login->unique_locations))
                  <small class="badge bg-label-warning text-dark py-1">
                    <i class="ti ti-xs ti-help me-1"></i>@lang('admin.Unknown')
                    ({{ $login->location_counts['Unknown'] ?? $login->location_counts[''] ?? 1 }})
                  </small>
                  @endif
                </div>
              </div>

              <!-- Details Table -->
              <div class="collapse" id="u{{ $login->user_id }}Details">
                <div class="table-responsive">
                  <table class="table table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>@lang('admin.Date_Time')</th>
                        <th>@lang('admin.location')</th>
                        <th>@lang('admin.IP_Address')</th>
                        <th>@lang('admin.browser')</th>
                        <th>@lang('admin.device')</th>
                        <th>@lang('admin.Actions')</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($login->recent_activities as $activity)
                      <tr>
                        <td class="text-nowrap">{{ $activity->activity_time->format('d M Y, H:i') }}</td>
                        <td>
                          @if($activity->location && $activity->location !== 'Unknown')
                          <i class="ti ti-map-pin me-1 text-primary"></i>{{ $activity->location }}
                          @else
                          <i class="ti ti-help text-warning me-1"></i>@lang('admin.Unknown')
                          @endif
                        </td>
                        <td class="text-nowrap">{{ $activity->ip_address ?: '—' }}</td>
                        <td class="text-nowrap">{{ $activity->browser }}</td>
                        <td class="text-nowrap">{{ $activity->device }}</td>
                        <td>
                          @if($activity->ip_address)
                          <button class="btn btn-icon btn-outline-danger btn-sm"
                            onclick="blockIp('{{ $activity->ip_address }}')" title="@lang('admin.Block_IP')"
                            data-bs-toggle="tooltip" data-bs-placement="top">
                            <i class="ti ti-ban"></i>
                          </button>
                          @endif
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                @if($login->has_more_activities ?? false)
                <div class="p-3 border-top text-center">
                  <button class="btn btn-light btn-sm" onclick="loadMoreActivities({{ $login->user_id }})">
                    <i class="ti ti-refresh me-1"></i>@lang('admin.Load_more')
                  </button>
                  <small class="text-muted d-block mt-1">
                    @lang('admin.showing') {{ $login->recent_activities->count() }} @lang('admin.of') {{
                    $login->total_activities_count ?? 0 }} @lang('admin.Activities')
                  </small>
                </div>
                @endif
              </div>
            </div>
            @endforeach
          </div>

          {{-- Pagination --}}
          <div class="card-footer">
            {{ $suspiciousLogins->links('pagination::bootstrap-5') }}
          </div>
        @else
          <div class="card-body text-center py-5">
            <div class="avatar avatar-xl mx-auto mb-3">
              <div class="avatar avatar-xl avatar-initial rounded-circle bg-label-success">
                <i class="ti ti-shield-check" style="font-size: 2rem;"></i>
              </div>
            </div>
            <h6 class="mb-1">@lang('admin.No_suspicious_logins')</h6>
            <p class="text-muted mb-0">@lang('admin.All_user_activities_appear_normal')</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>