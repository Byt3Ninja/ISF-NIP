
<div class="row text-center mb-6">
  @php
		$growthMetrics = ['growth_since_first_record' => $growthSinceFirstRecord, 'average_monthly_growth' => $avgMonthlyGrowth, 'compound_annual_growth_rate' => $compoundAnnualGrowthRate];
    $colors = ['success', 'primary', 'warning'];
    $icons = ['trending-up', 'presentation-analytics', 'circle-percentage']
  @endphp
  @foreach ($growthMetrics as $key => $value)
    <div class="col-md-4 mb-3">
      <div class="card card-border-shadow-{{ $colors[$loop->index] }} h-100">
        <div class="card-body text-center">
          <div class="d-flex align-items-center justify-content-center mb-2">
            <div class="avatar me-4">
              <div class="avatar-initial rounded bg-label-{{ $colors[$loop->index] }}">
                <i class="ti ti-{{ $icons[$loop->index] }}"></i>
              </div>
            </div>
            <h5 class="mb-0 fw-semibold">{{ $value }}</h5>
          </div>
          <small class="text-center text-capitalize">{{ __('startup.' . $key) }}</small>
        </div>
      </div>
    </div>
  @endforeach
</div>

<div class="card card-datatable table-responsive">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">{{ __('startup.Customers History') }}</h5>
    <button id="add-new-startup-growth-modal-btn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startupGrowthModal">
      <i class="ti ti-plus me-2"></i> {{ __('admin.Add New') }}
    </button>
  </div>
  <table class="datatables-basic table">
    <thead class="table-light">
      <tr>
        <th>{{ __('startup.Year') }}</th>
        <th>{{ __('startup.Month') }}</th>
        <th>{{ __('startup.Total customers') }}</th>
        <th>{{ __('startup.New customers') }}</th>
        <th>{{ __('startup.MoM growth') }}</th>
        <th>{{ __('startup.Segment') }}</th>
      </tr>
    </thead>
    <tbody>
      @forelse( $growth_records as $record )
        <tr>
          <td>{{ $record->year }}</td>
          <td>{{ __('startup.mini_month_' . $record->month) }}</td>
          <td>{{ $record->total }}</td>
          <td>{{ $record->newly }}</td>
          <td>{{ $record->MoM }}</td>
          <td>{{ strtoupper( $record->segment ) }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center">{{ __('admin.No_records_found') }}</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="modal fade" id="startupGrowthModal" tabindex="-1" aria-labelledby="startupGrowthModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form method="POST" action="/dashboard/startupGrowth/add" id="add-startup-growth-form">
				@csrf
				<div class="modal-header">
					<h5 class="modal-title" id="startupGrowthModalLabel">{{ __('startup.Add Customer Record') }}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6 col-lg-6 col-sm-12 mb-6">
							<label for="add_growth_year" class="form-label">{{ __('startup.Year') }}</label>
							<select class="form-select" name="add_growth_year" id="add_growth_year" aria-label="Year">
								@foreach( $years as $year )
									<option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-6 col-lg-6 col-sm-12 mb-6">
							<label for="add_growth_month" class="form-label">{{ __('startup.Month') }}</label>
							<select class="form-select" name="add_growth_month" id="add_growth_month" aria-label="Month">
								@for( $i = 1; $i <= 12; $i++ )
									<option value="{{ $i }}" {{ $i == date('m') ? 'selected' : '' }}>{{ __('startup.month_' . $i) }}</option>
								@endfor
							</select>
						</div>
						<div class="col-md-4 col-lg-4 col-sm-12 mb-6">
							<label for="add_growth_total_customers" class="form-label">{{ __('startup.Total customers') }}</label>
							<input type="number" class="form-control" name="add_growth_total_customers" id="add_growth_total_customers" placeholder="{{ __('startup.Total customers') }}">
						</div>
						<div class="col-md-4 col-lg-4 col-sm-12 mb-6">
							<label for="add_growth_new_customers" class="form-label">{{ __('startup.New customers this period') }}</label>
							<input type="number" class="form-control" name="add_growth_new_customers" id="add_growth_new_customers" placeholder="{{ __('startup.New customers this period') }}">
						</div>
						<div class="col-md-4 col-lg-4 col-sm-12 mb-6">
							<label for="add_growth_segment" class="form-label">{{ __('startup.customer_type') }}</label>
							<select class="form-select" name="add_growth_segment" id="add_growth_segment" aria-label="Segment">
								<option value="mixed">{{ __('startup.mixed') }}</option>
								<option value="b2c">B2C</option>
								<option value="b2b">B2B</option>
								<option value="enterprise">{{ __('startup.enterprise') }}</option>
							</select>
						</div>
						<div class="col-md-12 col-lg-12 col-sm-12 mb-4">
							<label for="add_growth_notes" class="form-label">{{ __('startup.notes') }}</label>
							<textarea class="form-control" name="add_growth_notes" id="add_growth_notes" placeholder="{{ __('startup.notes') }}" style="height: 130px; resize: none;"></textarea>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="company_id" id="company_id" value="{{ $company->id }}" />
					<button type="submit" class="btn btn-primary" id="add-new-startup-growth-btn">{{ __('admin.Save') }}</button>
					<button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
				</div>
			</form>
		</div>
	</div>
</div>