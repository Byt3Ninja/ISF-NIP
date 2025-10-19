@php
if($company->team_num == '1'){
$team_num = __('admin.Self-employed');
}else if($company->team_num == '2'){
$team_num = '1-10 '.__('admin.employees');
}else if($company->team_num == '3'){
$team_num = '11-50 '.__('admin.employees');
}else if($company->team_num == '4'){
$team_num = '51-200 '.__('admin.employees');
}else if($company->team_num == '5'){
$team_num = '201-500 '.__('admin.employees');
}else if($company->team_num == '6'){
$team_num = '501-1000 '.__('admin.employees');
}else if($company->team_num == '7'){
$team_num = '1001-5000 '.__('admin.employees');
}else if($company->team_num == '8'){
$team_num = '5001-10000 '.__('admin.employees');
}else if($company->team_num == '9'){
$team_num = '10001+ '.__('admin.employees');
}
@endphp
<section class="mt-3">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center gap-4">
              <div class="p-2 bg-label-warning radius-15 d-flex align-items-center"
                style="min-width: 180px; gap: 10px;">
                <i class="ti ti-users ti-28px"></i>
                <div class="mx-1 d-flex flex-column">
                  <span class="fw-bold text-black fs-6">{{ $team_num ?? 0 }}</span>
                  <span class="fw-bold mt-2" style="font-size: 12px;">{{ __('admin.Team number') }}</span>
                </div>
              </div>
              <div class="mt-3" style="width: 100%;">
                <h5 class="fw-bold mb-1">{{ __('admin.Working_Team_Details') }}</h5>
                <p class="text-muted line-clamp-2">
                  {{ $company->working_team_summary ?? __('admin.no_available_yet') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>