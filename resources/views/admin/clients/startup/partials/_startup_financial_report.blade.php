@php
  // Chart Series Data (For Line/Bar Charts)
  $chartLabels = $periods ? array_map(fn($p) => $p['label'], array_values($periods)) : [];

  // Helper to extract a specific key across all periods
  $extractSeries = function ($key) use ($periods) {
    if (empty($periods)) {
      return [];
    }
    if ($key === 'wcChange') {
      $wcValues = array_map(fn($p) => $p['data']['workingCapital'], array_values($periods));
      $changes = [];
      $prev = null;
      foreach ($wcValues as $wc) {
        if ($prev === null) {
          $changes[] = 0;
        } else {
          $changes[] = $wc - $prev;
        }
        $prev = $wc;
      }
      return $changes;
    }
    return array_map(function ($p) use ($key) {
        if ($key === 'revenue') {
          return $p['data']['revenue'];
        }
        if ($key === 'grossMargin') {
          return $p['data']['revenue'] != 0 ? round(($p['data']['grossProfit'] / $p['data']['revenue']) * 100, 1) : 0;
        }
        if ($key === 'ebitdaMargin') {
          return $p['data']['revenue'] != 0 ? round(($p['data']['ebitda'] / $p['data']['revenue']) * 100, 1) : 0;
        }
        if ($key === 'operatingMargin') {
          return $p['data']['revenue'] != 0 ? round(($p['data']['ebit'] / $p['data']['revenue']) * 100, 1) : 0;
        }
        if ($key === 'netMargin') {
          return $p['data']['revenue'] != 0 ? round(($p['data']['netProfit'] / $p['data']['revenue']) * 100, 1) : 0;
        }
        if ($key === 'debtEbitda') {
          return $p['data']['ebitda'] != 0 ? round($p['data']['totalDebt'] / $p['data']['ebitda'], 2) : 0;
        }
        return $p['data'][$key];
    }, array_values($periods));
  };

  $chartSeries = [
    'revenue' => $extractSeries('revenue'),
    'grossMargin' => $extractSeries('grossMargin'),
    'ebitdaMargin' => $extractSeries('ebitdaMargin'),
    'operatingMargin' => $extractSeries('operatingMargin'),
    'netMargin' => $extractSeries('netMargin'),
    'fcf' => $extractSeries('fcf'),
    'capex' => $extractSeries('capex'),
    'netProfit' => $extractSeries('netProfit'),
    'wcTotal' => $extractSeries('workingCapital'),
    'debtTotal' => $extractSeries('totalDebt'),
    'debtEbitda' => $extractSeries('debtEbitda'),
    'wcChange' => $extractSeries('wcChange'),
  ];

  $valuationDate = request()->get('valuation_date') ?? $valuationDate;
  $historicalDates = $historicalValuationDates ?? [];
@endphp

<div class="row">
  <div class="col-12">

    @if (empty($historicalDates) || count($historicalDates) == 0)
      {{-- Empty State: No financial data submitted --}}
      <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body py-5">
          <div class="mb-4">
            <i class="ti ti-database-x text-warning" style="font-size: 5rem !important;"></i>
          </div>
          <h4 class="card-title text-dark fw-bold mb-2">{{ __('startup.financial.no_financial_data_title') }}</h4>
          <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
            {{ __('startup.financial.no_financial_data_desc') }}
          </p>
          @if( $_user->hasAnyRole('Admin') || ( $_user->ableTo('company.profile_view_mine', $permissions) && $_user->hasRole('registered') && $_user->company_id == $company->id ) )
            <a href="/dashboard/myCompany/{{ $company->id }}#company-financial-projection" class="btn btn-primary">
              {{ __('startup.financial.enter_financial_data') }} <i class="ti ti-arrow-right ms-2"></i>
            </a>
          @endif
        </div>
      </div>
    @elseif (!$hasFinancials)
      {{-- Empty State: Required financial fields missing --}}
      <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body py-5">
          <div class="mb-4 text-center">
            <i class="ti ti-alert-triangle text-danger" style="font-size: 5rem !important;"></i>
          </div>
          <h4 class="card-title text-dark fw-bold mb-2">{{ __('startup.financial.missing_required_fields_title') }}</h4>
          <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
            {{ __('startup.financial.missing_required_fields_desc') }}
          </p>
          @if( $_user->hasAnyRole('Admin') || ( $_user->ableTo('company.profile_view_mine', $permissions) && $_user->hasRole('registered') && $_user->company_id == $company->id ) )
            <a href="/dashboard/myCompany/{{ $company->id }}#company-financial-projection" class="btn btn-primary">
              {{ __('startup.financial.complete_financial_data') }} <i class="ti ti-arrow-right ms-2"></i>
            </a>
          @endif
        </div>
      </div>
    @else

      <!-- Dashboard Header -->
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3 border-bottom pb-4">
        <div>
          <h4 class="mb-1 text-dark fw-bold">{{ $company->name_en ?? $company->name_ar }}</h4>
          <p class="mb-0 text-muted">
            {{ __('startup.financial.valuation_date') }}: 
            <span class="fw-semibold text-primary">
              {{ \Carbon\Carbon::parse($valuationDate . '-01')->format('F Y') }}
            </span>
          </p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="valuationDateSelect" class="form-label mb-0 text-muted text-nowrap">{{ __('startup.select_valuation_date') }}:</label>
          <select id="valuationDateSelect" class="form-select w-auto shadow-sm" onchange="window.location.search = '?valuation_date=' + this.value;">
            @foreach($historicalDates as $date)
              <option value="{{ $date }}" {{ $valuationDate == $date ? 'selected' : '' }}>
                {{ \Carbon\Carbon::parse($date . '-01')->format('F Y') }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- Executive Summary && Period Switcher -->
      <div class="card border-0 shadow-sm mb-6">
        <div class="card-body">
          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
              <h5 class="card-title mb-1"> 
                {{ __('startup.financial.executive_summary') }}
                <span id="summary-period-label">({{ $periods ? reset($periods)['label'] : '' }})</span>
              </h5>
            </div>

            @if ($periods && count($periods) > 1)
              <nav class="nav-align-end">
                <ul class="nav nav-tabs d-flex flex-column flex-md-row gap-2 gap-lg-0">
                  @foreach ($periods as $key => $info)
                    <li class="nav-item">
                      <button
                        class="d-flex flex-column nav-link {{ $loop->first ? 'active' : '' }}"
                        onclick="switchPeriod('{{ $key }}', {{ $loop->index }}, this)">
                        <div id="period-label" class="fw-bold">{{ $info['label'] }}</div>
                        <div class="small text-muted">{{ $info['sub'] }}</div>
                      </button>
                    </li>
                  @endforeach
                </ul>
              </nav>
            @endif
          </div>
        </div>
      </div>

      <!-- KPIs -->
      @php
        $kpis = [
          ['title' => __('startup.financial.total_revenue'), 'id' => 'kpi-revenue'],
          ['title' => __('startup.financial.free_cash_flow'), 'id' => 'kpi-fcf'],
          ['title' => __('startup.financial.ebitda_margin'), 'id' => 'kpi-ebitda-margin'],
          ['title' => __('startup.financial.debt_to_ebitda'), 'id' => 'kpi-debt-ebitda'],
        ];
        $kpiIcons = ['fa-dollar-sign', 'fa-chart-line', 'fa-percent', 'fa-credit-card'];
        $kpiColors = ['primary', 'success', 'info', 'warning'];
      @endphp

      <div class="row g-6 mb-6">
        @foreach ($kpis as $key => $kpi)
        <div class="col-md-6 col-xl-3">
          <div class="card h-100 card-border-shadow-{{ $kpiColors[$key] }}">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-secondary fw-medium small mb-1">
                  {{ $kpi['title'] }}
                </div>
                <h3 class="mb-0 fw-semibold text-dark">
                  <span id="{{ $kpi['id'] }}">--</span>
                </h3>
              </div>

              <div class="avatar avatar-md h-px-40 ms-4">
                <div class="avatar-initial rounded bg-label-{{ $kpiColors[$key] }}">
                  <i class="fa-solid {{ $kpiIcons[$key] }}"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      <!--/ KPIs -->

      <!-- Additional KPIs -->
      @php
        $additionalKpis = [
          ['title' => __('startup.financial.operating_profit'), 'id' => 'kpi-operating-profit'],
          ['title' => __('startup.financial.net_profit'), 'id' => 'kpi-net-profit'],
          ['title' => __('startup.financial.total_expenses'), 'id' => 'kpi-total-expenses'],
        ];
        $additionalKpiIcons = ['fa-chart-bar', 'fa-coins', 'fa-receipt'];
        $additionalKpiColors = ['success', 'primary', 'warning'];
      @endphp

      <div class="row g-6 mb-6">
        @foreach ($additionalKpis as $key => $kpi)
        <div class="col-md-4">
          <div class="card h-100 card-border-shadow-{{ $additionalKpiColors[$key] }}">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-secondary fw-medium small mb-1">
                  {{ $kpi['title'] }}
                </div>
                <h3 class="mb-0 fw-semibold text-dark">
                  <span id="{{ $kpi['id'] }}">--</span>
                </h3>
              </div>

              <div class="avatar avatar-md h-px-40 ms-4">
                <div class="avatar-initial rounded bg-label-{{ $additionalKpiColors[$key] }}">
                  <i class="fa-solid {{ $additionalKpiIcons[$key] }}"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      <!--/ Additional KPIs -->

      <!-- Growth & Revenue -->
      <h5 class="fw-semibold text-dark mb-3">{{ __('startup.financial.growth_revenue') }}</h5>
      <div class="row g-6 mb-6">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h6 class="card-title text-muted mb-3">{{ __('startup.financial.revenue_trend') }}</h6>
              <div style="height: 250px; position: relative;">
                <canvas id="revenueChart"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h6 class="card-title text-muted mb-3">{{ __('startup.financial.gross') }}</h6>
              <div
                class="d-flex justify-content-center position-relative h-px-200">
                <canvas id="grossMarginChart"></canvas>
                <div class="text-center"
                  style="position: absolute; top: 55%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;">
                  <div class="h2 fw-bold mb-0"><span id="d-gross-val">--</span>%
                  </div>
                  <small class="text-muted">{{ __('startup.financial.gross') }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Profitability & Efficiency -->
      <h5 class="fw-semibold text-dark mb-3">{{ __('startup.financial.profitability_efficiency') }}</h5>
      <div class="row g-6 mb-6">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h6 class="card-title text-muted mb-3">{{ __('startup.financial.margin_trends') }}</h6>
              <div style="height: 320px; position: relative;">
                <canvas id="marginTrendChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="row gy-3 h-100">
            @php $colors = ['info', 'success', 'primary', 'warning']; @endphp
            @foreach (['gross', 'ebitda', 'operating', 'net'] as $key => $kpiName)
              <div class="col-12">
                <div
                  class="card card-border-shadow-{{ $colors[$key] }} h-100 flex-fill">
                  <div class="card-body text-center">
                    <small class="text-muted">{{ __('startup.financial.' . $kpiName) }}</small>
                    <div class="fs-4 mb-0 fw-semibold text-{{ $colors[$key] }}">
                      <span id="kpi2-{{ substr($kpiName, 0, 3) }}">--</span>%
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      <!-- Cash Flow & Capital -->
      <h5 class="fw-semibold text-dark mb-3">{{ __('startup.financial.cash_flow_capital') }}</h5>
      <div class="row g-6 mb-6">
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h6 class="card-title text-muted mb-3">{{ __('startup.financial.cash_flow_composition') }}</h6>
              <div style="height: 250px; position: relative;">
                <canvas id="cashFlowChart"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h6 class="card-title text-muted mb-3">{{ __('startup.financial.working_capital') }}</h6>
              <div style="height: 250px; position: relative;">
                <canvas id="workingCapitalChart"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Financial Health & Debt -->
      <h5 class="fw-semibold text-dark mb-3">{{ __('startup.financial.financial_health_debt') }}</h5>
      <div class="row g-6 mb-6">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h6 class="card-title text-muted mb-3">{{ __('startup.financial.debt_trajectory') }}</h6>
              <div style="height: 320px; position: relative;">
                <canvas id="debtChart"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          @php
            $financialKpis = [
              ['label' => __('startup.financial.total_debt'), 'id' => 'kpi-total-debt'],
              ['label' => __('startup.financial.interest_coverage'), 'id' => 'kpi-interest-cov'],
              ['label' => __('startup.financial.fcf_margin'), 'id' => 'kpi-fcf-margin'],
            ];
          @endphp

          <div class="d-flex flex-column gap-3 h-100">
            <div class="d-flex flex-column gap-3 h-100">
              @foreach ($financialKpis as $key => $kpi)
                <div class="card card-border-shadow-danger h-100 flex-fill">
                  <div class="card-body d-flex flex-column justify-content-center h-100 text-center">
                    <div class="text-muted small">{{ $kpi['label'] }}</div>
                    <div class="fs-4 mb-0 fw-semibold text-dark">
                      <span id="{{ $kpi['id'] }}">--</span>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Metrics Table -->
      <h5 class="fw-semibold text-dark mb-3">{{ __('startup.financial.detailed_metrics') }}</h5>
      <div class="card border-0 shadow-sm mb-5">
        <div class="card-datatable">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">{{ __('startup.financial.metric') }}</th>
                  <th class="text-end">{{ __('startup.financial.value') }}</th>
                  <th class="text-end pe-4">{{ __('startup.financial.percent_of_revenue') }}</th>
                </tr>
              </thead>
              <tbody id="metrics-table-body">
                <!-- Populated by JS -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>

@if ($hasFinancials)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Data from Laravel
  const dashboardData = @json(array_map(fn($p) => $p['data'], $periods));
  const labels = @json($chartLabels);
  const series = @json($chartSeries);
  const periodsInfo = @json($periods);
  const currencyCode = @json($currencyCode);

  // Formatting utilities
  const fmt = {
    currency: v => {
      const absV = Math.abs(v);
      let formatted = absV >= 1e6 ? `${(absV/1e6).toFixed(1)}M` : absV >= 1e3 ? `${(absV/1e3).toFixed(0)}K` : `${absV.toFixed(0)}`;
      return (v < 0 ? '-' : '') + `${currencyCode} ${formatted}`;
    },
    percent: (v, t) => t != 0 ? ((v/t)*100).toFixed(1) : '0.0'
  };

  // Chart defaults
  Chart.defaults.font.family = "'Inter', sans-serif";
  Chart.defaults.color = '#6c757d';
  const grid = { color: '#dfdfe3', drawBorder: false };

  // Chart factory
  const createChart = (id, config) => {
      const ctx = document.getElementById(id);
      if(ctx) return new Chart(ctx, config);
      return null;
  };

  // Scale chart inputs to handle large numbers cleanly
  const revenueSeriesInMillions = series.revenue.map(r => r / 1000000);

  // Initialize charts
  const charts = {
    revenue: createChart('revenueChart', {
      type: 'line',
      data: {
        labels,
        datasets: [{
          data: revenueSeriesInMillions,
          borderColor: '#00bad1',
          backgroundColor: 'rgba(0,186,209,0.1)',
          fill: true,
          tension: 0.3,
          pointRadius: [3,3,3,3],
          pointBackgroundColor: '#00bad1'
        }]
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `${currencyCode} ${(context.parsed.y).toFixed(1)}M`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid,
            title: { display: true, text: `In Millions (${currencyCode})` }
          },
          x: { grid: { display: false } }
        }
      }
    }),

    grossMargin: createChart('grossMarginChart', {
      type: 'doughnut',
      data: {
        labels: ['Margin', 'COGS'],
        datasets: [{ data: [], backgroundColor: ['#28c76f','#dfdfe3'], borderWidth: 1, cutout: '80%' }]
      },
      options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        rotation: 225,
        circumference: 270
      }
    }),

    marginTrend: createChart('marginTrendChart', {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label: 'Gross %', data: series.grossMargin, borderColor: '#0dcaf0', tension: 0.1 },
          { label: 'EBITDA %', data: series.ebitdaMargin, borderColor: '#28c76f', tension: 0.1 },
          { label: 'Net %', data: series.netMargin, borderColor: '#ff9f43', tension: 0.1 }
        ]
      },
      options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
        scales: { y: { min: 0, max: 100, grid }, x: { grid: { display: false } } }
      }
    }),

    cashFlow: createChart('cashFlowChart', {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label: 'FCF', data: series.fcf, backgroundColor: '#28c76f' },
          { label: 'CapEx', data: series.capex, backgroundColor: '#ff9f43' },
          { label: 'Net Profit', data: series.netProfit, backgroundColor: '#00bad1' }
        ]
      },
      options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } },
        scales: { y: { beginAtZero: true, grid }, x: { grid: { display: false } } }
      }
    }),

    wc: createChart('workingCapitalChart', {
      data: {
        labels,
        datasets: [
          { type: 'line', label: 'Change', data: series.wcChange, borderColor: '#ff9f43', borderWidth: 2, yAxisID: 'y1' },
          { type: 'bar', label: 'Working Capital', data: series.wcTotal, backgroundColor: '#00bad1', yAxisID: 'y' }
        ]
      },
      options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, grid }, y1: { display: false, position: 'right' }, x: { grid: { display: false } } }
      }
    }),

    debt: createChart('debtChart', {
      data: {
        labels,
        datasets: [
          { type: 'line', label: '{{ __('startup.financial.debt_to_ebitda') }}', data: series.debtEbitda, borderColor: '#ff9f43', borderWidth: 2, yAxisID: 'y1' },
          { type: 'bar', label: '{{ __('startup.financial.total_debt') }}', data: series.debtTotal, backgroundColor: '#ff4c51', yAxisID: 'y' }
        ]
      },
      options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, grid }, y1: { display: false, position: 'right', min: 0 }, x: { grid: { display: false } } }
      }
    })
  };

  // Update DOM helper
  const update = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.innerText = val;
  };

  // Highlight helper
  const dim = (idx, color) => [0,1,2,3].map(i => i === idx ? color : color + '40');

  const periodKeys = ['past', 'year1', 'year2', 'year3'];

  // Switch period
  function switchPeriod(key, idx, btn) {
    // Update tab styles
    document.querySelectorAll('.nav-tabs .nav-link').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const d = dashboardData[key];
    const pInfo = periodsInfo[key];

    if (d) {
      // Update header label
      update('summary-period-label', '(' + pInfo.label + ')');

      const gmVal = parseFloat(fmt.percent(d.grossProfit, d.revenue));
      const ebitdaMVal = parseFloat(fmt.percent(d.ebitda, d.revenue));
      const opMVal = parseFloat(fmt.percent(d.ebit, d.revenue));
      const netMVal = parseFloat(fmt.percent(d.netProfit, d.revenue));

      // Update KPIs
      update('kpi-revenue', fmt.currency(d.revenue));
      update('kpi-fcf', fmt.currency(d.fcf));
      update('kpi-ebitda-margin', ebitdaMVal + '%');
      update('kpi-debt-ebitda', d.ebitda != 0 ? (d.totalDebt / d.ebitda).toFixed(2) : '0.00');
      
      update('d-gross-val', gmVal.toFixed(1));
      update('kpi2-gro', gmVal.toFixed(1));
      update('kpi2-ebi', ebitdaMVal.toFixed(1));
      update('kpi2-ope', opMVal.toFixed(1));
      update('kpi2-net', netMVal.toFixed(1));
      
      update('kpi-total-debt', fmt.currency(d.totalDebt));
      update('kpi-interest-cov', d.interest != 0 ? (d.ebit / d.interest).toFixed(2) : 'N/A');
      update('kpi-fcf-margin', fmt.percent(d.fcf, d.revenue) + '%');

      // Recalculate and update additional KPIs dynamically
      const operatingProfit = d.revenue - d.cogs - d.salaries - d.opex - d.da;
      const totalExpenses = d.cogs + d.salaries + d.opex + d.da + d.interest + d.taxes;
      const netProfit = d.revenue - totalExpenses;

      update('kpi-operating-profit', fmt.currency(operatingProfit));
      update('kpi-net-profit', fmt.currency(netProfit));
      update('kpi-total-expenses', fmt.currency(totalExpenses));

      // Update charts active indicator
      if(charts.revenue) {
          charts.revenue.data.datasets[0].pointRadius = [0,1,2,3].map(i => i === idx ? 6 : 3);
          charts.revenue.update();
      }

      if(charts.grossMargin) {
          const clampedGm = Math.max(0, Math.min(100, gmVal));
          charts.grossMargin.data.datasets[0].data = [clampedGm, 100 - clampedGm];
          charts.grossMargin.update();
      }

      if(charts.marginTrend) {
          charts.marginTrend.data.datasets.forEach(ds => ds.pointRadius = [0,1,2,3].map(i => i === idx ? 6 : 3));
          charts.marginTrend.update();
      }

      if(charts.cashFlow) {
          charts.cashFlow.data.datasets[0].backgroundColor = dim(idx, '#28c76f');
          charts.cashFlow.data.datasets[1].backgroundColor = dim(idx, '#ff9f43');
          charts.cashFlow.data.datasets[2].backgroundColor = dim(idx, '#00bad1');
          charts.cashFlow.update();
      }

      if(charts.wc) {
          charts.wc.data.datasets[1].backgroundColor = dim(idx, '#00bad1');
          charts.wc.update();
      }

      if(charts.debt) {
          charts.debt.data.datasets[1].backgroundColor = dim(idx, '#ff4c51');
          charts.debt.update();
      }

      // Calculate custom metrics for table
      let revenueGrowthRate = '-';
      if (idx > 0) {
        const prevRev = dashboardData[periodKeys[idx - 1]].revenue;
        revenueGrowthRate = prevRev !== 0 ? (((d.revenue - prevRev) / prevRev) * 100).toFixed(1) + '%' : '0.0%';
      }

      const operatingExpenseRatio = d.revenue !== 0 ? (((d.salaries + d.opex) / d.revenue) * 100).toFixed(1) + '%' : '0.0%';

      // Detailed Metrics Table rows definition
      const rows = [
        { key: 'revenue', n: '{{ __('startup.financial.revenue') }}', v: d.revenue, b: 1 },
        { key: 'revenueGrowthRate', n: '{{ __('startup.financial.revenue_growth_rate') }}', v: revenueGrowthRate, isCustom: true, b: 1 },
        { key: 'cogs', n: '{{ __('startup.financial.cost_goods_sold') }}', v: d.cogs },
        { key: 'grossProfit', n: '{{ __('startup.financial.gross_profit') }}', v: d.grossProfit, b: 1 },
        { key: 'salaries', n: '{{ __('startup.salaries') }}', v: d.salaries },
        { key: 'opex', n: '{{ __('startup.financial.operating_expenses') }}', v: d.opex },
        { key: 'operatingExpenseRatio', n: '{{ __('startup.financial.operating_expense_ratio') }}', v: operatingExpenseRatio, isCustom: true },
        { key: 'ebitda', n: '{{ __('startup.ebitda') }}', v: d.ebitda, b: 1 },
        { key: 'da', n: '{{ __('startup.depreciation_and_amortization') }}', v: d.da },
        { key: 'ebit', n: '{{ __('startup.ebit') }}', v: d.ebit },
        { key: 'interest', n: '{{ __('startup.financial.interest') }}', v: d.interest },
        { key: 'taxes', n: '{{ __('startup.financial.taxes') }}', v: d.taxes },
        { key: 'netProfit', n: '{{ __('startup.financial.net_profit') }}', v: d.netProfit, b: 1, bg: 1 },
        { key: 'fcf', n: '{{ __('startup.financial.free_cash_flow') }}', v: d.fcf },
        { key: 'capex', n: '{{ __('startup.financial.capital_expenditures') }}', v: d.capex },
        { key: 'workingCapital', n: '{{ __('startup.financial.working_capital') }}', v: d.workingCapital },
        { key: 'totalDebt', n: '{{ __('startup.financial.total_debt') }}', v: d.totalDebt, b: 1 }
      ];

      document.getElementById('metrics-table-body').innerHTML = rows.map(r => `
        <tr${r.bg ? ' class="table-primary"' : ''}>
          <td class="ps-4${r.b ? ' fw-bold' : ''}">${r.n}</td>
          <td class="text-end${r.b ? ' fw-bold' : ''}">${r.isCustom ? r.v : fmt.currency(r.v)}</td>
          <td class="text-end pe-4 text-muted">${r.key === 'revenue' || r.key === 'workingCapital' || r.key === 'totalDebt' || r.isCustom ? '-' : fmt.percent(r.v, d.revenue) + '%'}</td>
        </tr>
      `).join('');
    }
  }

  // Initialize switchPeriod on load
  if(Object.keys(dashboardData).length > 0) {
      const firstKey = Object.keys(dashboardData)[0];
      switchPeriod(firstKey, 0, document.querySelector('.nav-tabs .nav-link'));
  }
</script>
@endpush
@endif
