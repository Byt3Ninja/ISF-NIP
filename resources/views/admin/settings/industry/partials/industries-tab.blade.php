<div class="tab-pane fade show active" id="industries" role="tabpanel" aria-labelledby="industries-tab">
  <div class="card">
    <div class="card-body">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-3">
        <h5 class="card-title mb-2 mb-md-0">{{ __('settings.Industries') }}</h5>
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-primary" id="addIndustry">
            <i class="ti ti-plus ti-sm me-md-2"></i>
            <span class="d-none d-md-inline">{{ __('settings.Add_Industry') }}</span>
          </button>
          <button class="btn btn-label-warning" id="importJson">
            <i class="ti ti-upload ti-sm me-md-2"></i>
            <span class="d-none d-md-inline">{{ __('settings.Import_JSON') }}</span>
          </button>
          <div class="btn-group">
            <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="ti ti-download ti-sm me-md-2"></i>
              <span class="d-none d-md-inline">{{ __('settings.Export') }}</span>
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item" href="javascript:void(0)" id="exportJson">
                  <i class="ti ti-download ti-sm me-1"></i>{{ __('settings.Export_JSON') }}
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="exportIndustries('csv')">
                  <i class="ti ti-file-text me-1"></i>{{ __('settings.Export_CSV') }}
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="exportIndustries('excel')">
                  <i class="ti ti-file-spreadsheet me-1"></i>{{ __('settings.Export_Excel') }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover" id="tblIndustries">
          <thead class="table-light">
            <tr>
              <th style="width:40px">#</th>
              <th>{{ __('settings.Name') }}</th>
              <th>{{ __('cycles.Created At') }}</th>
              <th style="width:160px">{{ __('settings.Actions') }}</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>