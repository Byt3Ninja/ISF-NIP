<div class="tab-pane fade" id="Users_Activities" role="tabpanel" aria-labelledby="Users_Activities-tab">
  <div class="card">
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="ti ti-list-details me-1"></i> @lang('admin.Users_Activities')</h6>
        <div class="btn-group">
          <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown"
            aria-expanded="false" id="projectsExportBtn">
            <i class="bi bi-download me-1"></i> {{ $l == 'ar' ? 'تصدير' : 'Export' }}
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="javascript:void(0);"
                onclick="exportTable('activities-datatable', 'csv')"><i class="bi bi-filetype-csv me-2"></i>{{ $l
                == 'ar' ? 'تصدير CSV' : 'Export CSV' }}</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);"
                onclick="exportTable('activities-datatable', 'excel')"><i class="bi bi-file-earmark-excel me-2"></i>{{
                $l == 'ar' ? 'تصدير Excel' : 'Export Excel' }}</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="table-responsive card-datatable">
      <table id="activities-datatable" class="table align-middle">
        <thead class="table-light">
          <tr>
            <th>@lang('admin.User')</th>
            <th>@lang('admin.browser')</th>
            <th>@lang('admin.device')</th>
            <th>@lang('admin.platform')</th>
            <th>@lang('admin.IP_Address')</th>
            <th>@lang('admin.location')</th>
            <th>@lang('admin.Login_Time')</th>
            <th>@lang('admin.Actions')</th>
          </tr>
        </thead>
        <tbody>
          @foreach($activities as $activity)
          <tr>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                  @if($activity->user->img_url ?? false)
                  <img src="{{ asset('191014/' . $activity->user->img_url) }}"
                    alt="{{ $activity->user->first_name_en }}" class="rounded-circle border shadow-xs"
                    onerror="this.onerror=null; this.src='/icons/account.svg';">
                  @else
                  <div class="avatar avatar-sm avatar-initial rounded-circle bg-label-primary">
                    {{ strtoupper(substr($activity->user->first_name_en ?? 'U', 0, 1)) }}{{
                    strtoupper(substr($activity->user->last_name_en ?? 'U', 0, 1)) }}
                  </div>
                  @endif
                </div>
                <div class="d-flex flex-column">
                  <h6 class="mb-0 text-sm">{{ $activity->user->first_name_en . ' ' .
                    $activity->user->last_name_en }}</h6>
                  <small class="text-muted">{{ $activity->user->email }}</small>
                </div>
              </div>
            </td>
            <td class="text-nowrap">{{ $activity->browser }}</td>
            <td class="text-nowrap">{{ $activity->device }}</td>
            <td class="text-nowrap">{{ $activity->platform }}</td>
            <td class="text-nowrap">{{ $activity->ip_address }}</td>
            <td class="text-nowrap">{{ $activity->location }}</td>
            <td class="text-nowrap">{{ $activity->activity_time->format('d M Y, H:i') }}</td>
            <td>
              <form action="{{ route('activities.destroy', $activity->id) }}" method="POST"
                class="d-inline delete-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-icon btn-label-danger" title="@lang('admin.Delete')">
                  <i class="ti ti-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>