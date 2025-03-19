@extends('layouts.admin')
@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.My Dashboard'))
@section('vendorstyle')
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/apex-charts/apex-charts.css" />
@stop
@section('style')
	<link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
@stop
@section('content')
	<!-- Content wrapper -->
	<div class="content-wrapper">
		<div class="container-xxl flex-grow-1 container-p-y">
			<div class="content-body">
				@if( session()->has('msg') )
				<div class="col-12">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2" role="alert">
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
							<div class="d-flex justify-content-between align-items-center">
								<div class="d-flex align-items-center">
								<i class="ti ti-{{ session()->get('class') == 'success' ? 'like' : 'error' }}"></i>
								<span> {{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
							</div>
							</div>
						</div>
					</div>
				</div>
				@endif
			</div>
			<div class="row g-6">
				<!-- Count cards -->
				@foreach( $countCards as $card )
				@if( array_intersect($card['permissions'], $permissions) == $card['permissions'] )
				<a href="{{ $card['href'] }}" class="col-lg-3 col-sm-6">
					<div class="card card-border-shadow-{{ $card['color'] }} h-100">
						<div class="card-body">
							<div class="d-flex justify-content-center align-items-center mb-2">
								<div class="avatar me-4">
									<span class="avatar-initial rounded bg-label-primary">
										<i class="ti {{ $card['icon'] }} text-{{ $card['color'] }} ti-28px"></i>
									</span>
								</div>
								<h4 class="mb-0 text-center">{{ $card['count'] }}</h4>
							</div>
							<p class="mb-1 text-center">{{ $card['text'] }}</p>
						</div>
					</div>
				</a>
				@endif
				@endforeach
				<!-- / Count cards -->
			</div>
			<div class="row mt-6">
				@foreach( $graphsCards as $card )
				@if( array_intersect($card['permissions'], $permissions) == $card['permissions'] )
				<div class="{{ $card['div-class'] }}">
				  <div class="card">
					<div class="card-header d-flex justify-content-between align-items-center">
					  <div class="d-flex align-items-center">
						<h5 class="card-title mb-0"> {{ $card['card-title'] }} </h5>
					  </div>
					</div>
					<div class="card-body">
						@if( $card['type'] == 'div' ) <div id="{{ $card['id'] }}" class="chartjs" data-height="345"></div> @endif
						@if( $card['type'] == 'canvas' ) <canvas id="{{ $card['id'] }}" class="chartjs" data-height="345"></canvas> @endif
					</div>
				  </div>
				</div>
				@endif
				@endforeach
			</div>
		</div>
	</div>
	<!-- / Content Wrapper -->
@stop
@section('scripts')
	<script src="/vuexy/assets/vendor/libs/apex-charts/apexcharts.js"></script>
	<script src="/vuexy/assets/vendor/libs/chartjs/chartjs.js"></script>
@stop
@section('pagescripts')
	<script>
		// User distribution by country
		const userDistributionData = @json($countries);

		// Sector Breakdown Chart Data
		const sectorBreakdownData = @json($sector_data);

		// Project Distribution Pie Chart Data
		const projectsPieData = @json($project_types);

		// Development Stage Chart Data
		const devStageData = @json($development_stages);

		// Target Market Analysis Chart Data
		const targetMarketData = @json($target_market_data);

		// Project Status Over Time Chart Data
		const projectStatusData = @json($project_status);

		// Total jobs created
		const totalJobsCreated = @json($total_jobs_created);

		// Total Founders
		const totalFounders = @json($total_founders);

		// Companies by SDG Chart Data
		const sdgData = @json($sdg_data);

		// Companies by sector
		const companiesBySectorData = @json($sector_distribution);

		// Horizontal Bar Chart
		const seriesName = '{{ __("admin.Registered") }}';
		const barChartCategories = {!! json_encode($cycles_labels) !!};
		const seriesData = {!! json_encode($cycles_startups_count) !!};

		// Vertical Bar Chart 
		const verticalBarLabels = {!! json_encode($users_gov->pluck('state_' . $l)) !!};
		const verticalBarData = {!! json_encode($users_gov->pluck('total')) !!};

		// Pie Chart
		const pieLabels = ['{{ __("admin.Female") }}', '{{ __("admin.Male") }}'];
		const pieData = {!! json_encode($users_gender->pluck('total')) !!};

		const internshipSeriesName = '';
		const mentorshipStatisticsLabels = null;
		const mentorshipStatisticsData = null;
		const internshipBarChartCategories = null;
		const internshipSeriesData = null;
		const cyclesStartupName = '';
		const cyclesStartupLabels = null;
		const cyclesStartupData = null;
	</script>
	<script src="/vuexy/assets/js/app-admin-dashboard.js"></script>
	<script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>
	<script src="/vuexy/assets/js/charts-apex.js"></script>
	<script src="/vuexy/assets/js/charts-chartjs.js"></script>
	<script>
		$(document).ready(function() {
			$('.table').DataTable({
				"responsive": true,
				"searching": false,
				"lengthChange": false,
				"paging": false,
				"bInfo": false,
				"columnDefs": [{
					"orderable": false,
					"targets": [2]
				}]
			});
		});
	</script>
@stop