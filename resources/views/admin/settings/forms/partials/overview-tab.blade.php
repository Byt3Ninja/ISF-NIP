<!-- Overview Pane -->
<div class="tab-pane fade {{ $activeTab == 'overview' ? 'show active' : '' }}" id="overview-pane" role="tabpanel">

  <!-- KPI Cards -->
  <div class="row g-6 mb-4">
    @foreach($kpis as $key => $item)
      <div class="col-xl-3">
        <div class="card card-border-shadow-{{ $colors[$key] }}">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="mb-0">{{ $item['label'] }}</h6>
                <h4 class="mb-0">{{ $item['value'] ?? 0 }}</h4>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-{{ $colors[$key] }}">
                  <i class="ti ti-{{ $icons[$key] }} ti-lg"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <!-- Bar Chart -->
  <div class="row">
    <div class="col-12 mb-4">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="card-title mb-0">
            <h5 class="m-0 me-2">{{ __('forms.top_organizations') }}</h5>
            <small class="text-muted">{{ __('forms.forms_submissions_by_organizations') }}</small>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div id="organizationsChart"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
