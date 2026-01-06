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
        // Logic to calculate margins on the fly if needed, or grab raw data
        if ($key === 'revenue') {
          return $p['data']['revenue'] / 1000000;
        } // In Millions
        if ($key === 'grossMargin') {
          return $p['data']['revenue'] != 0 ? round(($p['data']['grossProfit'] / $p['data']['revenue']) * 100, 1) : 0;
        }
        if ($key === 'ebitdaMargin') {
          return $p['data']['revenue'] != 0 ? round(($p['data']['ebitda'] / $p['data']['revenue']) * 100, 1) : 0;
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
    'netMargin' => $extractSeries('netMargin'),
    'fcf' => $extractSeries('fcf'),
    'capex' => $extractSeries('capex'),
    'netProfit' => $extractSeries('netProfit'),
    'wcTotal' => $extractSeries('workingCapital'),
    'debtTotal' => $extractSeries('totalDebt'),
    'debtEbitda' => $extractSeries('debtEbitda'),
    'wcChange' => $extractSeries('wcChange'),
  ];
@endphp

<div class="row">
  <div class="col-12">

    <!-- Executive Summary && Period Switcher -->
    <div class="card border-0 shadow-sm mb-6">
      <div class="card-body">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
          <div>
            <h5 class="card-title mb-1"> 
              {{ __('startup.financial.executive_summary') }}
              <span id="summary-period-label">({{ $periods ? reset($periods)['label'] : '' }})</span>
            </h5>
            <!-- <div class="d-flex align-items-center gap-2 mt-2">
              <span class="text-muted small">{{ __('startup.financial.valuation_date') }}:</span>
              <select class="form-select form-select-sm w-auto" onchange="window.location.href='?valuation_date='+this.value">
                @foreach($availableValuationDates as $date)
                  <option value="{{ $date }}" {{ $valuationDate == $date ? 'selected' : '' }}>{{ $date }}</option>
                @endforeach
              </select>
            </div> -->
          </div>

          @if ($periods && count($periods) > 1)
            <nav class="nav-align-end">
              <ul class="nav nav-tabs d-flex flex-column flex-md-row gap-2 gap-lg-0">
                @foreach ($periods as $key => $info)
                  <li class="nav-item">
                    <button
                      class="d-flex flex-column nav-link {{ $loop->first ? 'active' : '' }}"
                      onclick="switchPeriod('{{ $key }}', {{ $loop->index }}, this)">
                      <div id="period-label">{{ $info['label'] }}</div>
                      <div class="small">{{ $info['sub'] }}</div>
                    </button>
                  </li>
                @endforeach
              </ul>
            </nav>
          @endif
        </div>
      </div>
    </div>

    @if(empty($periods))
      <div class="alert alert-warning" role="alert">
        {{ __('startup.financial.no_data_for_date') }}
      </div>
    @else

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
                <span id="{{ $kpi['id'] }}">--</span> {{ $key === 2 ? '%' : '' }}
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
                    <span id="{{ $kpi['id'] }}">--</span> {{ $key === 2 ? '%' : '' }}
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Data from Laravel
  const dashboardData = @json(array_map(fn($p) => $p['data'], $periods));
  const labels = @json($chartLabels);
  const series = @json($chartSeries);
  const periodsInfo = @json($periods);

  // Utilities
  const fmt = {
    currency: v => v >= 1e6 ? `$${(v/1e6).toFixed(1)}M` : v >= 1e3 ? `$${(v/1e3).toFixed(0)}K` : `$${v}`,
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

  // Initialize charts
  const charts = {
    revenue: createChart('revenueChart', {
      type: 'line',
      data: {
        labels,
        datasets: [{
          data: series.revenue,
          borderColor: '#00bad1',
          backgroundColor: 'rgba(13,110,253,0.1)',
          fill: true,
          tension: 0.3,
          pointRadius: [6,4,4,4],
          pointBackgroundColor: '#00bad1'
        }]
      },
      options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, grid }, x: { grid: { display: false } } }
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

  // Switch period
  function switchPeriod(key, idx, btn) {
    // Update tab
    document.querySelectorAll('.nav-tabs .nav-link').forEach(b => b.classList.remove('active'));

    if (btn) btn.classList.add('active');

    const d = dashboardData[key];
    const pInfo = periodsInfo[key];

    if (d) {
      // Update header label
      update('summary-period-label', '(' + pInfo.label + ')');

      const gm = fmt.percent(d.grossProfit, d.revenue);

      // Update KPIs
      update('kpi-revenue', fmt.currency(d.revenue));
      update('kpi-fcf', fmt.currency(d.fcf));
      update('kpi-ebitda-margin', fmt.percent(d.ebitda, d.revenue));
      update('kpi-debt-ebitda', d.ebitda != 0 ? (d.totalDebt / d.ebitda).toFixed(2) : '0.00');
      update('d-gross-val', gm);
      update('kpi2-gro', gm);
      update('kpi2-ebi', fmt.percent(d.ebitda, d.revenue));
      update('kpi2-ope', fmt.percent(d.ebit, d.revenue));
      update('kpi2-net', fmt.percent(d.netProfit, d.revenue));
      update('kpi-total-debt', fmt.currency(d.totalDebt));
      update('kpi-interest-cov', d.interest != 0 ? (d.ebit / d.interest).toFixed(2) : '0.00');
      update('kpi-fcf-margin', fmt.percent(d.fcf, d.revenue));
  
      // Update charts
      if(charts.revenue) {
          charts.revenue.data.datasets[0].pointRadius = [0,1,2,3].map(i => i === idx ? 6 : 3);
          charts.revenue.update();
      }
  
      if(charts.grossMargin) {
          charts.grossMargin.data.datasets[0].data = [gm, 100 - gm];
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
  
      // Update table
      const rows = [
        { n: '{{ __('startup.financial.revenue') }}', v: d.revenue, b: 1 },
        { n: '{{ __('startup.financial.cost_goods_sold') }}', v: d.cogs },
        { n: '{{ __('startup.financial.growth_profit') }}', v: d.grossProfit, b: 1 },
        { n: '{{ __('startup.salaries') }}', v: d.salaries },
        { n: '{{ __('startup.financial.operating_expenses') }}', v: d.opex },
        { n: '{{ __('startup.ebitda') }}', v: d.ebitda, b: 1 },
        { n: '{{ __('startup.depreciation_and_amortization') }}', v: d.da },
        { n: '{{ __('startup.ebit') }}', v: d.ebit },
        { n: '{{ __('startup.financial.interest') }}', v: d.interest },
        { n: '{{ __('startup.financial.taxes') }}', v: d.taxes },
        { n: '{{ __('startup.financial.net') }}', v: d.netProfit, b: 1, bg: 1 },
        { n: '{{ __('startup.financial.free_cash_flow') }}', v: d.fcf },
        { n: '{{ __('startup.financial.capital_expenditures') }}', v: d.capex },
        { n: '{{ __('startup.financial.working_capital') }}', v: d.workingCapital },
        { n: '{{ __(('startup.financial.total_debt')) }}', v: d.totalDebt, b: 1 }
      ];
  
      document.getElementById('metrics-table-body').innerHTML = rows.map(r => `
        <tr${r.bg ? ' class="table-primary"' : ''}>
          <td class="ps-4${r.b ? ' fw-bold' : ''}">${r.n}</td>
          <td class="text-end${r.b ? ' fw-bold' : ''}">${fmt.currency(r.v)}</td>
          <td class="text-end pe-4 text-muted">${r.n === 'Revenue' ? '-' : fmt.percent(r.v, d.revenue) + '%'}</td>
        </tr>
      `).join('');
    }
  }

  // Initialize
  if(Object.keys(dashboardData).length > 0) {
      const firstKey = Object.keys(dashboardData)[0];
      switchPeriod(firstKey, 0, document.querySelector('.nav-tabs .nav-link'));
  }
</script>
@endpush
