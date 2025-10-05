<div class="tab-pane fade" id="sectors" role="tabpanel" aria-labelledby="sectors-tab">
  <div class="card">
    <div class="card-body">
      <div class="row d-flex justify-content-end" id="tblSectors-filters">
        <div class="col-12 col-md-4 mb-2 mb-md-0">
          <div class="d-flex align-items-center gap-2">
            <label for="selIndustrySector" class="fs-6">{{ __('settings.Industry') }}:</label>
            <select class="form-select form-select-sm" id="selIndustrySector"></select>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
        <h5 class="card-title">{{ __('settings.Sectors') }}</h5>
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-primary" id="addSector"><i class="ti ti-plus ti-sm me-1"></i>{{ __('settings.Add_Sector') }}</button>
          <button class="btn btn-label-primary" id="addOtherSector"><i class="ti ti-plus ti-sm me-1"></i>{{ __('settings.Add_Other') }}</button>
          <div class="btn-group">
            <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="ti ti-download ti-sm me-1"></i>{{ __('settings.Export') }}
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="exportSectors('csv')">
                  <i class="ti ti-file-text me-1"></i>{{ __('settings.Export_CSV') }}
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="exportSectors('excel')">
                  <i class="ti ti-file-spreadsheet me-1"></i>{{ __('settings.Export_Excel') }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover" id="tblSectors">
          <thead class="table-light">
            <tr>
              <th style="width:40px">#</th>
              <th>{{ __('settings.Sector') }}</th>
              <th>{{ __('settings.Industry') }}</th>
              <th class="text-end">{{ __('settings.Actions') }}</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>