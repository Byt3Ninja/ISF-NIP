<div>
  <!-- Filters for this specific report -->
  <div class="bg-label-light p-4 rounded border mb-6">
    <h5 class=""><i class="fas fa-filter"></i> {{ __('admin.Filter Options (Performance)') }} </h5>
    <form class="row g-3 align-items-end performance-filter-form" method="GET"
      action="{{ route('programs.report', $cycle->id) }}">
      <div class="col-md-3 col-sm-6">
        <label for="perfFilterSector" class="form-label fw-semibold">{{ __('admin.Sector') }}</label>
        <select class="form-select select2" id="perfFilterSector" name="sector">
          <option selected value="">{{ __('admin.All') }}</option>
          @foreach ($sectorsLabel as $sector)
          <option value="{{ $sector->name_en }}" {{ request('sector')==$sector->name_en ? 'selected' : '' }}>
            {{ $sector->{'name_'.$l} }}
          </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3 col-sm-6">
        <label for="perfFilterStage" class="form-label fw-semibold">{{ __('admin.Stage') }}</label>
        <select class="form-select select2" id="perfFilterStage" name="stage">
          <option selected value="">{{ __('admin.All') }}</option>
          @foreach ($stages as $stage)
          <option value="{{ $stage->name_en }}" {{ request('stage')==$stage->name_en ? 'selected' : '' }}>
            {{ $stage->{'name_' . $l} }}
          </option>
          @endforeach
        </select>
      </div>

      {{-- <div class="col-md-2 col-sm-6"> <label for="perfFilterMinRating" class="form-label fw-semibold">{{
          __('admin.Min Rating') }}</label>
        <input type="number" class="form-control" id="perfFilterMinRating" min="1" max="5" step="0.1"
          placeholder="e.g., 3.5">
      </div> --}}
      {{-- <div class="col-md-2 col-sm-6"> <label for="perfFilterMinProgress" class="form-label fw-semibold">{{
          __('admin.Min Progress') }}
          (%)</label>
        <input type="number" class="form-control form-control" id="perfFilterMinProgress" min="0" max="100" step="5"
          placeholder="e.g., 50">
      </div> --}}
      <div class="col-md-2 col-sm-12 mt-3 mt-md-0"> 
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill"><i
              class="fas fa-search me-2"></i> {{ __('admin.Search') }}</button>
          <a href="{{ route('programs.report', $cycle->id) }}" class="btn btn-outline-secondary flex-fill"><i
              class="fas fa-undo me-2"></i> {{ __('admin.Reset') }}</a>
        </div>
      </div>
    </form>
  </div>
  <!-- Content from performance report -->
  <div class="row mt-4 ">
    <div class="col-12 mb-8">
      <div class="row">
        <div class="col-md-12">
          <!-- Chart 1: Startups by Sector -->
          <div class="card card-body border">
            <h4 class="mb-6 fw-semibold border-bottom border-2 border-primary"><i
                class="fas fa-chart-pie text-primary me-2"></i> {{ __('admin.Distribution by Sector') }}</h4>
            <div class="chart-container">
              <canvas id="perfSectorDistributionChart"></canvas>
            </div>
          </div>
        </div>

        {{-- <div class="col-md-6">
          <!-- Chart 2: Average Progress by Stage -->
          <div class="card card-body border">
            <h4 class="mb-6 fw-semibold border-bottom border-2 border-primary"><i
                class="fas fa-chart-bar text-primary me-2"></i> {{ __('admin.Average Progress by Stage') }}</h4>
            <div class="chart-container">
              <canvas id="perfProgressByStageChart"></canvas>
            </div>
          </div>
        </div> --}}
      </div>
    </div>
    <div class="col-12">
      <div class="card card-body border">
        <h4 class="fw-semibold border-bottom border-2 border-primary">
          <i class="fas fa-list text-primary me-2"></i> {{ __('admin.Startup Performance List') }}
        </h4>
        <div class="table-responsive">
          <!-- Copied Performance Table Structure Here -->
          <table class="table table-striped table-hover align-middle performance-table">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th class="text-center">{{ __('admin.company name') }}</th>
                <th class="text-center">{{ __('admin.Sector') }}</th>
                <th class="text-center">{{ __('admin.Stage') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($companies as $index => $company)
              <tr>
                <th class="text-center">{{ $index + 1 }}</th>
                <td class="text-center">{{ $company->{'name_'.$l} }}</td>
                <td class="text-center">
                  {{--  {{ $company->sectoR?->{'name_'.$l} ?? '-' }}  --}}
                  @foreach( $company->companyRelations->whereNotNull('sector_id')->unique('sector_id')->pluck('sector') as $_sector)
                    <span class="badge bg-label-primary mx-1">{{ $_sector->{'name_'.$l} }}</span>
                  @endforeach
                </td>
                <td class="text-center">{{ $company->stagE->{'name_'.$l} ?? '-' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>