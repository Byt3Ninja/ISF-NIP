<div class="card">
	{{-- MERGE financialProjection VALUES INTO $sections --}}
	@php
			// -----------------------
			// DEFINE SECTIONS FIRST
			// -----------------------
			$sections = [
					[
							'title' => 'startup.profit_and_loss',
							'rows' => [
									['label' => 'startup.revenues', 'name' => 'rev', 'required' => true],
									['label' => 'startup.cogs', 'name' => 'cogs', 'required' => true],
									['label' => 'startup.salaries', 'name' => 'sal', 'required' => true],
									['label' => 'startup.other_op_expenses', 'name' => 'opex', 'required' => true],
									['label' => 'startup.ebitda', 'name' => 'ebitda', 'calculated' => true],
									['label' => 'startup.depreciation_and_amortization', 'name' => 'da', 'required' => false],
									['label' => 'startup.ebit', 'name' => 'ebit', 'calculated' => true],
									['label' => 'startup.interest', 'name' => 'int', 'required' => false],
									['label' => 'startup.taxes', 'name' => 'tax', 'required' => false],
									['label' => 'startup.net_profit', 'name' => 'np', 'calculated' => true],
							],
					],
					[
							'title' => 'startup.balance_sheet_and_working_capital',
							'rows' => [
									['label' => 'startup.receivables', 'name' => 'rec', 'required' => false],
									['label' => 'startup.inventory', 'name' => 'inv', 'required' => false],
									['label' => 'startup.payables', 'name' => 'pay', 'required' => false],
									['label' => 'startup.working_capital', 'name' => 'wc', 'calculated' => true],
									['label' => 'startup.change_in_wc', 'name' => 'dwc', 'calculated' => true, 'special' => 'delta'],
							],
					],
					[
							'title' => 'startup.cash_flow_items',
							'rows' => [
									['label' => 'startup.capital_expenditures', 'name' => 'capex', 'required' => false],
									['label' => 'startup.debt_at_end_of_year', 'name' => 'debt', 'required' => false],
									['label' => 'startup.debt_change', 'name' => 'ddebt', 'calculated' => true, 'special' => 'delta'],
									['label' => 'startup.free_cash_flow', 'name' => 'fcf', 'calculated' => true, 'fw-bold' => true],
							],
					],
			];

			// -----------------------
			// MAP short names to DB columns
			// -----------------------
			$map = [
					'rev' => 'revenues',
					'cogs' => 'cogs',
					'sal' => 'salaries',
					'opex' => 'other_op_expenses',
					'ebitda' => 'ebitda',
					'da' => 'depreciation_amortization',
					'ebit' => 'ebit',
					'int' => 'interest',
					'tax' => 'taxes',
					'np' => 'net_profit',

					'rec' => 'receivables',
					'inv' => 'inventory',
					'pay' => 'payables',
					'wc' => 'working_capital',
					'dwc' => 'change_in_wc',

					'capex' => 'capital_expenditures',
					'debt' => 'debt_end_year',
					'ddebt' => 'debt_change',
					'fcf' => 'free_cash_flow',
			];

			// -----------------------
			// NORMALIZE incoming collection/array to a plain PHP array indexed 0..n-1
			// so we can safely reference years by index (0..3)
			// -----------------------
			if (isset($financialprojection) && $financialprojection instanceof \Illuminate\Support\Collection) {
					$fpArray = $financialprojection->values()->all();
			} elseif (isset($financialprojection) && is_array($financialprojection)) {
					$fpArray = array_values($financialprojection);
			} else {
					$fpArray = [];
			}

			// -----------------------
			// MERGE VALUES INTO $sections -> each row gets 'values' => [year0, year1, year2, year3]
			// -----------------------
			foreach ($sections as $sIndex => $section) {
					foreach ($section['rows'] as $rIndex => $row) {
							$key = $row['name'];

							// initialize empty values array for 4 years
							$values = [null, null, null, null];

							if (isset($map[$key])) {
									for ($year = 0; $year < 4; $year++) {
											$val = null;
											if (isset($fpArray[$year]) && $fpArray[$year] !== null) {
													// data_get works for Eloquent model / array / stdClass
													$val = data_get($fpArray[$year], $map[$key]);
											}
											$values[$year] = $val;
									}
							}

							$sections[$sIndex]['rows'][$rIndex]['values'] = $values;
					}
			}
	@endphp
	{{-- END MERGE --}}

	 <form id="financialForm" method="post" enctype="multipart/form-data" action="{{ route('company.profile_update_mine', isset($client) && $client->company ? $client->company->id : auth()->user()->id) }}">
    {!! csrf_field() !!}
		<div class="card-header">
			<div class="d-flex flex-wrap align-items-md-center justify-content-between gap-3">
			<div>
				<h5 class="card-title mb-0">{{ $heading }}</h5>
				<small class="text-muted">{{ __('startup.enter_valuation_date_and_financial_data_to_generate_metrics') }}</small>
			</div>
			<div class="d-flex align-items-center gap-3">
				<label for="valuationDate" class="form-label text-nowrap">
				{{ __('startup.valuation_date') }} <span class="text-danger">*</span>
				</label>
				<input type="text" class="form-control" id="valuationDate" name="valuation_date" required>
			</div>
			</div>
		</div>
 

	<input name="CompanyN" type="hidden" value="15">
    
    <div class="card-datatable table-responsive">
      <table class="datatable-basic table mb-0" id="financialTable">
        <thead class="table-light">
          <tr>
            <th class="col-label">{{ __('startup.line_item') }}</th>
            <th class="text-end" id="header-year-0">{{ __('startup.past_year') }}<br><small class="fw-normal text-muted">-</small>
            </th>
            <th class="text-end" id="header-year-1">
              {{ __('startup.forecast_year_1') }}<br>
              <small class="fw-normal text-muted">-</small>
            </th>
            <th class="text-end" id="header-year-2">{{ __('startup.year_2') }}<br><small class="fw-normal text-muted">-</small></th>
            <th class="text-end" id="header-year-3">{{ __('startup.year_3') }}<br><small class="fw-normal text-muted">-</small></th>
          </tr>
        </thead>       
        <tbody>
          @foreach ($sections as $section)
            <tr class="fw-bold">
              <td colspan="5">{{ __($section['title']) }}</td>
            </tr>

            @foreach ($section['rows'] as $row)
              @if (isset($row['calculated']) && $row['calculated'])
                <tr class="row-calculated bg-light-subtle">
                  <td class="col-label">{{ __($row['label']) }}</td>
                  @for ($i = 0; $i < 4; $i++)
                    <td>
                      <input 
												type="text" 
												class="form-control @if (isset($row['fw-bold'])) fw-bold @endif"
                        id="{{ $row['name'] }}_{{ $i }}"  
												name="{{ $row['name'] }}_{{ $i }}"
												value="{{ $row['values'][$i] }}"
                        @if (isset($row['special']) && $row['special'] == 'delta' && $i == 0) value="-" @endif readonly tabindex="-1"
											/>
                    </td>
                  @endfor
                </tr>
              @else
                <tr>
                  <td class="col-label text-nowrap">{{ __($row['label']) }}
                    @if ($row['required'])
                      <span class="text-danger">*</span>
                    @endif
                  </td>
                  @for ($i = 0; $i < 4; $i++)
                    <td>
                      <input 
												type="number" 
												class="form-control year-{{ $i }} input-trigger"
                        name="{{ $row['name'] }}_{{ $i }}" @if ($row['required']) required @endif
												value="{{ $row['values'][$i] ?? '' }}"	
											/>
                    </td>
                  @endfor
                </tr>
              @endif
            @endforeach
          @endforeach
        </tbody>
      </table>
    </div>
		<div class="card-footer text-end">
			<button type="reset" class="btn btn-label-secondary me-3">{{ __('admin.Reset') }}</button>
			<button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
		</div>
  </form>
</div>

@push('scripts')
<script>
  // Financial Projection Form Logic
document.addEventListener('DOMContentLoaded', function () {
	const dateInput = document.getElementById('valuationDate');
	const formInputs = document.querySelectorAll('.input-trigger');

	// Initialize Flatpickr for Valuation Date
	flatpickr('#valuationDate', {
		defaultDate: new Date(),
		dateFormat: 'Y-m-d',
		onChange: function(selectedDates, dateStr, instance) {
			updateHeaders();
		}
	});
	updateHeaders();

	// 2. Input Change Listener (for all financial inputs)
	formInputs.forEach(input => {
		// Enforce max length of 15
		input.addEventListener('input', function () {
			if (this.value.length > 15) {
				this.value = this.value.slice(0, 15);
			}
			calculateAll();
		});
	});

	// Function to update header labels based on Valuation Date
	function updateHeaders() {
		const valDateStr = dateInput.value;
		if (!valDateStr) return;

		const valDate = moment(valDateStr);

		// Definitions
		// Past Year: 12 months before Val Date
		const pastStart = moment(valDate).subtract(1, 'year');
		const pastEnd = moment(valDate).subtract(1, 'day');

		// Forecast Y1: 12 months starting Val Date
		const fy1Start = moment(valDate);
		const fy1End = moment(valDate).add(1, 'year').subtract(1, 'day');

		// Year 2
		const y2Start = moment(fy1End).add(1, 'day');
		const y2End = moment(y2Start).add(1, 'year').subtract(1, 'day');

		// Year 3
		const y3Start = moment(y2End).add(1, 'day');
		const y3End = moment(y3Start).add(1, 'year').subtract(1, 'day');

		// Helper to format
		const fmt = (s, e) => `${s.format('MM/YYYY')} – ${e.format('MM/YYYY')}`;

		document.querySelector('#header-year-0 small').textContent = fmt(pastStart, pastEnd);
		document.querySelector('#header-year-1 small').textContent = fmt(fy1Start, fy1End);
		document.querySelector('#header-year-2 small').textContent = fmt(y2Start, y2End);
		document.querySelector('#header-year-3 small').textContent = fmt(y3Start, y3End);
	}

	// Helper: Get value from input name, return 0 if empty
	function getVal(name) {
		const el = document.querySelector(`input[name="${name}"]`);
		return el && el.value ? parseFloat(el.value) : 0;
	}

	// Helper: Format number with commas
	function fmtNum(num) {
		return num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
	}

	// Main Calculation Logic
	function calculateAll() {
		// Loop through 4 years (0 to 3)
		let prevWC = 0; // Previous year WC placeholder
		let prevDebt = 0; // Previous year Debt placeholder

		for (let i = 0; i < 4; i++) {
			// --- Inputs ---
			const rev = getVal(`rev_${i}`);
			const cogs = getVal(`cogs_${i}`);
			const sal = getVal(`sal_${i}`);
			const opex = getVal(`opex_${i}`);

			const da = getVal(`da_${i}`);
			const int = getVal(`int_${i}`);
			const tax = getVal(`tax_${i}`);

			const rec = getVal(`rec_${i}`);
			const inv = getVal(`inv_${i}`);
			const pay = getVal(`pay_${i}`);

			const capex = getVal(`capex_${i}`);
			const debt = getVal(`debt_${i}`);

			// --- Formulas ---

			// EBITDA = Revenues − COGS − Salaries − Other Operating Expenses
			const ebitda = rev - cogs - sal - opex;
			document.getElementById(`ebitda_${i}`).value = fmtNum(ebitda);

			// EBIT = EBITDA − D&A
			const ebit = ebitda - da;
			document.getElementById(`ebit_${i}`).value = fmtNum(ebit);

			// Net Profit = EBIT − Interest − Taxes
			const netProfit = ebit - int - tax;
			document.getElementById(`np_${i}`).value = fmtNum(netProfit);

			// Working Capital = Receivables + Inventory − Payables
			const wc = rec + inv - pay;
			document.getElementById(`wc_${i}`).value = fmtNum(wc);

			// Change in Working Capital & Debt Change
			let deltaWC = 0;
			let debtChange = 0;

			// Logic: For Past Year (i=0), we don't have "Previous Year" data in this form.
			// We assume 0 change or N/A. Here we calculate it as Current - 0 (which is just current)
			// or treat it as 0 change for calculation safety. 
			// Typically in financial modeling, Year 0 Delta is usually 0 if no Year -1 exists.

			if (i === 0) {
				// Start of model, assume no delta impact or strictly based on 0
				deltaWC = 0;
				debtChange = 0;
				document.getElementById(`dwc_${i}`).value = "-";
				document.getElementById(`ddebt_${i}`).value = "-";
			} else {
				deltaWC = wc - prevWC;
				debtChange = debt - prevDebt;
				document.getElementById(`dwc_${i}`).value = fmtNum(deltaWC);
				document.getElementById(`ddebt_${i}`).value = fmtNum(debtChange);
			}

			// FCF = Net Profit + D&A − Capital Expenditures − Change in Working Capital
			const fcf = netProfit + da - capex - deltaWC;
			document.getElementById(`fcf_${i}`).value = fmtNum(fcf);

			// Store current values as "Previous" for next iteration
			prevWC = wc;
			prevDebt = debt;
		}
	}
});
</script>
@endpush
