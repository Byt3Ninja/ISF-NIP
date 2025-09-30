<div class="tab-pane fade" id="technologies" role="tabpanel" aria-labelledby="technologies-tab">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
        <h5 class="card-title">{{ __('settings.Technologies') }}</h5>
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-primary" id="addTech"><i class="ti ti-plus ti-sm me-1"></i>{{ __('settings.Add_Technology') }}</button>
          <button class="btn btn-label-primary" id="addOtherTech"><i class="ti ti-plus ti-sm me-1"></i>{{ __('settings.Add_Other') }}</button>
          <div class="btn-group">
            <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="ti ti-download ti-sm me-1"></i>{{ __('settings.Export') }}
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="exportTechnologies('csv')">
                  <i class="ti ti-file-text me-1"></i>{{ __('settings.Export_CSV') }}
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="exportTechnologies('excel')">
                  <i class="ti ti-file-spreadsheet me-1"></i>{{ __('settings.Export_Excel') }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover" id="tblTech">
          <thead class="table-light">
            <tr>
              <th style="width:40px">#</th>
              <th>{{ __('settings.Technology') }}</th>
              <th style="width:160px" class="text-end">{{ __('settings.Actions') }}</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>