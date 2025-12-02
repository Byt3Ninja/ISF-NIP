
<style type="text/css">
	.metric {
		background: #f7f9fc !important;
		border-radius: 6px !important;
		padding: 8px 10px !important;
	}
	.metric-label {
		font-size: 16px;
		color: #666;
		margin-bottom: 3px;
	}
	.metric-value {
		font-size: 22px;
		font-weight: 700;
		color: #0b3b77;
	}
	.metric-sub {
		font-size: 14px;
		color: #888;
	}
</style>

<div class="row">
	<div class="col-12 col-md-12 mb-6">
		<div class="card">
			<div class="card-body">
				<div class="row mb-4">
					<div class="col-9"><h5 class="card-title">{{ __('startup.startup_growth') }}</h5></div>
					<div class="col-3 text-end">
						<button id="add-new-startup-growth-modal-btn" class="btn btn-sm btn-dark">
							<i class="fas fa-sm fa-fw fa-plus"></i> {{ __('admin.Add New') }}
						</button>
					</div>
				</div>
				<div class="d-flex flex-column gap-2">
					<div class="row mb-4">
						<div class="col-md-4 col-lg-4 col-sm-12">
							<div class="metric">
            					<div class="metric-label">{{ __('startup.Growth since first record') }}</div>
								<div class="metric-value">
									{{ $growthSinceFirstRecord }}
								</div>
								{{--  <div class="metric-sub">Last month 100</div>  --}}
							</div>
						</div>
						<div class="col-md-4 col-lg-4 col-sm-12">
							<div class="metric">
            					<div class="metric-label">{{ __('startup.Average monthly growth') }}</div>
								<div class="metric-value">{{ $avgMonthlyGrowth }}</div>
								{{--  <div class="metric-sub">Last month 100</div>  --}}
							</div>
						</div>
						<div class="col-md-4 col-lg-4 col-sm-12">
							<div class="metric">
            					<div class="metric-label">{{ __('startup.Compound annual growth rate') }}</div>
								<div class="metric-value">{{ $compoundAnnualGrowthRate }}</div>
								{{--  <div class="metric-sub">Last month 100</div>  --}}
							</div>
						</div>
					</div>
					<div class="card">
						<div class="card-body">
							<h6 class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
								{{ __('startup.Customers History') }}
							</h6>
							<div class="row">
								<div class="col-12 col-md-12">
									<div class="table-responsive">
										<table class="table table-hover table-striped">
											<thead>
												<tr>
													<th> {{ __('startup.Year') }} </th>
													<th> {{ __('startup.Month') }} </th>
													<th> {{ __('startup.Total customers') }} </th>
													<th> {{ __('startup.New customers') }} </th>
													<th> {{ __('startup.MoM growth') }} </th>
													<th> {{ __('startup.Segment') }} </th>
												</tr>
											</thead>
											<tbody>
												@forelse( $growth_records as $record )
													<tr>
														<td> {{ $record->year }} </td>
														<td> {{ __('startup.mini_month_' . $record->month) }} </td>
														<td> {{ $record->total }} </td>
														<td> {{ $record->newly }} </td>
														<td> {{ $record->MoM }} </td>
														<td> {{ strtoupper( $record->segment ) }} </td>
													</tr>
												@empty
													<tr>
														<td colspan="7" class="text-center">{{ __('admin.No_records_found') }}</td>
													</tr>
												@endforelse
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
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
					<div class="container-fluid px-0">
						<div class="row">
							<div class="col-md-6 col-lg-6 col-sm-12 py-2">
								<div class="form-floating">
									<select class="form-select" name="add_growth_year" id="add_growth_year" aria-label="Year">
										@foreach( $years as $year )
											<option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
										@endforeach
									</select>
									<label for="add_growth_year">Year</label>
								</div>
							</div>
							<div class="col-md-6 col-lg-6 col-sm-12 py-2">
								<div class="form-floating">
									<select class="form-select" name="add_growth_month" id="add_growth_month" aria-label="Month">
										@for( $i = 1; $i <= 12; $i++ )
											<option value="{{ $i }}" {{ $i == date('m') ? 'selected' : '' }}>{{ __('startup.month_' . $i) }}</option>
										@endfor
									</select>
									<label for="add_growth_month">Month</label>
								</div>
							</div>
							<div class="col-md-4 col-lg-4 col-sm-12 py-2">
								<div class="form-floating">
									<input type="number" class="form-control" name="add_growth_total_customers" id="add_growth_total_customers" placeholder="Total customers">
									<label for="add_growth_total_customers">Total customers</label>
								</div>
							</div>
							<div class="col-md-4 col-lg-4 col-sm-12 py-2">
								<div class="form-floating">
									<input type="number" class="form-control" name="add_growth_new_customers" id="add_growth_new_customers" placeholder="New customers">
									<label for="add_growth_new_customers">New customers this period</label>
								</div>
							</div>
							<div class="col-md-4 col-lg-4 col-sm-12 py-2">
								<div class="form-floating">
									<select class="form-select" name="add_growth_segment" id="add_growth_segment" aria-label="Segment">
										<option value="mixed">Mixed</option>
										<option value="b2c">B2C</option>
										<option value="b2b">B2B</option>
										<option value="enterprise">Enterprise</option>
									</select>
									<label for="add_growth_segment">Customer type</label>
								</div>
							</div>
							<div class="col-md-12 col-lg-12 col-sm-12 py-2">
								<div class="form-floating">
									<textarea class="form-control" name="add_growth_notes" id="add_growth_notes" placeholder="Notes" style="height: 130px; resize: none;"></textarea>
									<label for="add_growth_notes">Notes</label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="company_id" id="company_id" value="{{ $company->id }}" />
					<button type="submit" class="btn btn-primary" id="add-new-startup-growth-btn">Save</button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.Close') }}</button>
				</div>
			</form>
		</div>
	</div>
</div>

@section('scripts')
	<script src="/app-assets/plugins/alertify/alertify.min.js"></script>
	<script type="text/javascript">
		alertify.defaults.transition = 'zoom';
		alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
		alertify.defaults.theme.cancel = 'btn btn-sm btn-info';
		alertify.defaults.theme.input = 'form-control';

		$(document).on('click', '#add-new-startup-growth-modal-btn', function(e) {
			e.preventDefault();
			$("#startupGrowthModal").modal('show');
		});
	</script>
@stop
