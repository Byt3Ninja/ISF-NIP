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

{{-- Charts Section --}}
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
    <h5 class="mb-0">{{ __('startup.customer_growth_trends') }}</h5>
    <div class="d-flex gap-2 flex-wrap mt-2 mt-md-0">
      {{-- View Toggle --}}
      <div class="btn-group" role="group">
        <input type="radio" class="btn-check" name="view-toggle" id="monthly-view" value="monthly" checked>
        <label class="btn btn-outline-primary btn-sm" for="monthly-view">{{ __('startup.monthly') }}</label>
        
        <input type="radio" class="btn-check" name="view-toggle" id="yearly-view" value="yearly">
        <label class="btn btn-outline-primary btn-sm" for="yearly-view">{{ __('startup.yearly') }}</label>
      </div>

      {{-- Chart Type Toggle --}}
      <div class="btn-group" role="group">
        <input type="radio" class="btn-check" name="chart-type" id="customers-chart" value="customers" checked>
        <label class="btn btn-outline-secondary btn-sm" for="customers-chart">{{ __('startup.customers') }}</label>
        
        <input type="radio" class="btn-check" name="chart-type" id="growth-chart" value="growth">
        <label class="btn btn-outline-secondary btn-sm" for="growth-chart">{{ __('startup.growth') }} %</label>
      </div>
    </div>
  </div>
  <div class="card-body">
    {{-- Filters --}}
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <label for="year-range-filter" class="form-label">{{ __('startup.year_range') }}</label>
        <select class="form-select" id="year-range-filter">
          <option value="all">{{ __('startup.all_years') }}</option>
          <option value="last-year">{{ __('startup.last_year') }}</option>
          <option value="last-2-years">{{ __('startup.last_2_years') }}</option>
          <option value="last-3-years">{{ __('startup.last_3_years') }}</option>
          <option value="custom">{{ __('startup.custom_range') }}</option>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label for="segment-filter" class="form-label">{{ __('startup.customer_type') }}</label>
        <select class="form-select" id="segment-filter">
          <option value="all">{{ __('admin.All_Types') }}</option>
          <option value="mixed">{{ __('startup.mixed') }}</option>
          <option value="b2c">B2C</option>
          <option value="b2b">B2B</option>
          <option value="enterprise">{{ __('startup.enterprise') }}</option>
        </select>
      </div>
      <div class="col-md-6 mb-3" id="custom-year-start-wrapper" style="display: none;">
        <label for="custom-year-start" class="form-label">{{ __('startup.start_year') }}</label>
        <select class="form-select" id="custom-year-start">
          @foreach( $years as $year )
            <option value="{{ $year }}">{{ $year }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6 mb-3" id="custom-year-end-wrapper" style="display: none;">
        <label for="custom-year-end" class="form-label">{{ __('startup.end_year') }}</label>
        <select class="form-select" id="custom-year-end">
          @foreach( $years as $year )
            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Chart Canvas --}}
    <div class="chart-container" style="position: relative; height: 400px;">
      <canvas id="customerGrowthChart"></canvas>
    </div>
  </div>
</div>

<div class="card card-datatable table-responsive">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">{{ __('startup.Customers History') }}</h5>
    <button id="add-new-startup-growth-modal-btn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startupGrowthModal">
      <i class="ti ti-plus me-2"></i> {{ __('admin.Add New') }}
    </button>
  </div>
  <table class="datatables-basic table" id="growthTable">
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
      @foreach( $growth_records as $record )
        <tr>
          <td>{{ $record->year }}</td>
          <td>{{ __('startup.mini_month_' . $record->month) }}</td>
          <td>{{ $record->total }}</td>
          <td>{{ $record->newly }}</td>
          <td>{{ $record->MoM }}</td>
          <td>{{ strtoupper( $record->segment ) }}</td>
        </tr>
      @endforeach
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  // Growth data from Laravel
  const growthData = @json($growth_records);
  
  let customerChart = null;
  
  // Month names for labels
  const monthNames = [
    @for($i = 1; $i <= 12; $i++)
      "{{ __('startup.mini_month_' . $i) }}"{{ $i < 12 ? ',' : '' }}
    @endfor
  ];

  $(document).ready(function(){
    // Initialize DataTable
    $('#growthTable').DataTable({
      pageLength: 10,
      responsive: true,
      ordering: true,
      searching: true,
      @if (app()->getLocale() == 'ar')
      language: {
        search: 'ابحث:',
        lengthMenu: 'أظهر _MENU_ مدخلات',
        info: 'إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل',
        paginate: { previous: 'السابق', next: 'التالي' },
        emptyTable: 'لا توجد بيانات'
      }
      @endif
    });

    // Initialize chart
    initializeChart();

    // Event listeners for filters and toggles
    $('input[name="view-toggle"]').on('change', updateChart);
    $('input[name="chart-type"]').on('change', updateChart);
    $('#year-range-filter').on('change', function() {
      if ($(this).val() === 'custom') {
        $('#custom-year-start-wrapper, #custom-year-end-wrapper').show();
      } else {
        $('#custom-year-start-wrapper, #custom-year-end-wrapper').hide();
      }
      updateChart();
    });
    $('#segment-filter').on('change', updateChart);
    $('#custom-year-start, #custom-year-end').on('change', updateChart);

    // Update chart when new data is added
    $('#add-startup-growth-form').on('submit', function(e) {
      // After successful submission, you would reload the page or update the chart data
      // This depends on your form submission handling (AJAX or regular form post)
    });
  });

  function initializeChart() {
    const ctx = document.getElementById('customerGrowthChart').getContext('2d');
    const chartData = prepareChartData();

    customerChart = new Chart(ctx, {
      type: 'bar',
      data: chartData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                  label += ': ';
                }
                if (context.parsed.y !== null) {
                  if ($('input[name="chart-type"]:checked').val() === 'growth') {
                    label += context.parsed.y.toFixed(2) + '%';
                  } else {
                    label += context.parsed.y.toLocaleString();
                  }
                }
                return label;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                if ($('input[name="chart-type"]:checked').val() === 'growth') {
                  return value.toFixed(1) + '%';
                }
                return value.toLocaleString();
              }
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  function updateChart() {
    if (customerChart) {
      const chartData = prepareChartData();
      customerChart.data = chartData;
      customerChart.update();
    }
  }

  function prepareChartData() {
    const viewType = $('input[name="view-toggle"]:checked').val();
    const chartType = $('input[name="chart-type"]:checked').val();
    const segmentFilter = $('#segment-filter').val();
    
    // Filter data by segment
    let filteredData = growthData.filter(record => {
      if (segmentFilter === 'all') return true;
      return record.segment.toLowerCase() === segmentFilter.toLowerCase();
    });

    // Filter by year range
    filteredData = filterByYearRange(filteredData);

    // Sort data chronologically
    filteredData.sort((a, b) => {
      if (a.year !== b.year) return a.year - b.year;
      return a.month - b.month;
    });

    if (viewType === 'monthly') {
      return prepareMonthlyChart(filteredData, chartType);
    } else {
      return prepareYearlyChart(filteredData, chartType);
    }
  }

  function filterByYearRange(data) {
    const yearRange = $('#year-range-filter').val();
    const currentYear = new Date().getFullYear();
    
    if (yearRange === 'all') {
      return data;
    } else if (yearRange === 'last-year') {
      return data.filter(r => r.year >= currentYear - 1);
    } else if (yearRange === 'last-2-years') {
      return data.filter(r => r.year >= currentYear - 2);
    } else if (yearRange === 'last-3-years') {
      return data.filter(r => r.year >= currentYear - 3);
    } else if (yearRange === 'custom') {
      const startYear = parseInt($('#custom-year-start').val());
      const endYear = parseInt($('#custom-year-end').val());
      return data.filter(r => r.year >= startYear && r.year <= endYear);
    }
    
    return data;
  }

  function prepareMonthlyChart(data, chartType) {
    const labels = data.map(record => `${monthNames[record.month - 1]} ${record.year}`);
    
    if (chartType === 'customers') {
      return {
        labels: labels,
        datasets: [{
          label: "{{ __('startup.Total customers') }}",
          data: data.map(record => record.total),
          backgroundColor: 'rgba(75, 192, 192, 0.8)',
          borderColor: 'rgb(75, 192, 192)',
          borderWidth: 1
        }, {
          label: "{{ __('startup.New customers') }}",
          data: data.map(record => record.newly),
          backgroundColor: 'rgba(255, 159, 64, 0.8)',
          borderColor: 'rgb(255, 159, 64)',
          borderWidth: 1
        }]
      };
    } else {
      return {
        labels: labels,
        datasets: [{
          label: "{{ __('startup.MoM growth') }} (%)",
          data: data.map(record => parseFloat(record.MoM) || 0),
          backgroundColor: 'rgba(153, 102, 255, 0.8)',
          borderColor: 'rgb(153, 102, 255)',
          borderWidth: 1
        }]
      };
    }
  }

  function prepareYearlyChart(data, chartType) {
    // Group data by year
    const yearlyData = {};
    
    data.forEach(record => {
      if (!yearlyData[record.year]) {
        yearlyData[record.year] = {
          year: record.year,
          total: 0,
          newly: 0,
          count: 0
        };
      }
      // Use the last month's total for the year
      yearlyData[record.year].total = Math.max(yearlyData[record.year].total, record.total);
      yearlyData[record.year].newly += record.newly;
      yearlyData[record.year].count++;
    });

    const years = Object.keys(yearlyData).sort();
    const labels = years;

    if (chartType === 'customers') {
      return {
        labels: labels,
        datasets: [{
          label: "{{ __('startup.Total customers') }}",
          data: years.map(year => yearlyData[year].total),
          backgroundColor: 'rgba(75, 192, 192, 0.8)',
          borderColor: 'rgb(75, 192, 192)',
          borderWidth: 1
        }, {
          label: "{{ __('startup.New customers') }}",
          data: years.map(year => yearlyData[year].newly),
          backgroundColor: 'rgba(255, 159, 64, 0.8)',
          borderColor: 'rgb(255, 159, 64)',
          borderWidth: 1
        }]
      };
    } else {
      // Calculate year-over-year growth
      const growthData = years.map((year, index) => {
        if (index === 0) return 0;
        const previousYear = years[index - 1];
        const previousTotal = yearlyData[previousYear].total;
        const currentTotal = yearlyData[year].total;
        if (previousTotal === 0) return 0;
        return ((currentTotal - previousTotal) / previousTotal * 100);
      });

      return {
        labels: labels,
        datasets: [{
          label: "{{ __('startup.YoY growth') }} (%)",
          data: growthData,
          backgroundColor: 'rgba(153, 102, 255, 0.8)',
          borderColor: 'rgb(153, 102, 255)',
          borderWidth: 1
        }]
      };
    }
  }
</script>
@endpush