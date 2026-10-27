<div class="card" dir="{{ $l == 'en' ? 'ltr' : 'rtl' }}">
	{{-- MERGE financialProjection VALUES INTO $sections --}}
	@php
		$requestDate = request()->get('valuation_date');
		$currentDate = $requestDate ?? ($valuation_date ?? '');
		$historicalDates = $historicalValuationDates ?? [];

		$sections = [
			[
				'title' => 'startup.profit_and_loss',
				'rows' => [
					['label' => 'startup.revenues',                       'name' => 'rev',    'required' => true],
					['label' => 'startup.cogs',                           'name' => 'cogs',   'required' => true],
					['label' => 'startup.salaries',                       'name' => 'sal',    'required' => true],
					['label' => 'startup.other_op_expenses',              'name' => 'opex',   'required' => true],
					['label' => 'startup.ebitda',                         'name' => 'ebitda', 'calculated' => true],
					['label' => 'startup.depreciation_and_amortization',  'name' => 'da',     'required' => false],
					['label' => 'startup.ebit',                           'name' => 'ebit',   'calculated' => true],
					['label' => 'startup.interest',                       'name' => 'int',    'required' => false],
					['label' => 'startup.taxes',                          'name' => 'tax',    'required' => false],
					['label' => 'startup.net_profit',                     'name' => 'np',     'calculated' => true],
				],
			],
			[
				'title' => 'startup.balance_sheet_and_working_capital',
				'rows' => [
					['label' => 'startup.receivables',   'name' => 'rec', 'required' => false],
					['label' => 'startup.inventory',     'name' => 'inv', 'required' => false],
					['label' => 'startup.payables',      'name' => 'pay', 'required' => false],
					['label' => 'startup.working_capital','name' => 'wc', 'calculated' => true],
					['label' => 'startup.change_in_wc',  'name' => 'dwc','calculated' => true, 'special' => 'delta'],
				],
			],
			[
				'title' => 'startup.cash_flow_items',
				'rows' => [
					['label' => 'startup.capital_expenditures',  'name' => 'capex', 'required' => false],
					['label' => 'startup.debt_at_end_of_year',   'name' => 'debt',  'required' => false],
					['label' => 'startup.debt_change',           'name' => 'ddebt', 'calculated' => true, 'special' => 'delta'],
					['label' => 'startup.free_cash_flow',        'name' => 'fcf',   'calculated' => true, 'fw-bold' => true],
				],
			],
		];

		$map = [
			'rev'   => 'revenues',
			'cogs'  => 'cogs',
			'sal'   => 'salaries',
			'opex'  => 'other_op_expenses',
			'ebitda'=> 'ebitda',
			'da'    => 'depreciation_amortization',
			'ebit'  => 'ebit',
			'int'   => 'interest',
			'tax'   => 'taxes',
			'np'    => 'net_profit',
			'rec'   => 'receivables',
			'inv'   => 'inventory',
			'pay'   => 'payables',
			'wc'    => 'working_capital',
			'dwc'   => 'change_in_wc',
			'capex' => 'capital_expenditures',
			'debt'  => 'debt_end_year',
			'ddebt' => 'debt_change',
			'fcf'   => 'free_cash_flow',
		];

		if (isset($financialprojection) && $financialprojection instanceof \Illuminate\Support\Collection) {
			$fpArray = $financialprojection->values()->all();
		} elseif (isset($financialprojection) && is_array($financialprojection)) {
			$fpArray = array_values($financialprojection);
		} else {
			$fpArray = [];
		}

		foreach ($sections as $sIndex => $section) {
			foreach ($section['rows'] as $rIndex => $row) {
				$key    = $row['name'];
				$values = [null, null, null, null];

				if (isset($map[$key])) {
					for ($year = 0; $year < 4; $year++) {
						$val = null;
						if (isset($fpArray[$year]) && $fpArray[$year] !== null) {
							$val = data_get($fpArray[$year], $map[$key]);
						}
						$values[$year] = $val;
					}
				}

				$sections[$sIndex]['rows'][$rIndex]['values'] = $values;
			}
		}
	@endphp

	<form id="financialProjectionForm" method="post" enctype="multipart/form-data"
		action="{{ route('company.profile_update_mine', isset($client) && $client->company ? $client->company->id : auth()->user()->id) }}">
		{!! csrf_field() !!}

		<div class="card-header border-bottom">
			<h5 class="card-title mb-0">{{ $heading }}</h5>
			<p class="mb-0 text-muted">{{ __('startup.enter_valuation_date_and_financial_data_to_generate_metrics') }}</p>
		</div>

		<input name="CompanyN" type="hidden" value="{{ \App\Enums\CompanyFormStep::FinancialProjection->value }}">

		<div class="card-body">
			<div class="row g-6">

				{{-- Valuation Date --}}
				<div class="col-lg-5">
					<div class="mb-3 mb-md-0">
						<label for="valuationDate" class="form-label">
							{{ __('startup.valuation_date') }} <span class="text-danger">*</span>
						</label>
						<div class="input-group">
							<input type="hidden"
								id="valuationDateHidden"
								name="valuation_date"
								value="{{ $currentDate ?: date('Y-m') }}">

							<input type="text"
								class="form-control rounded-start"
								id="valuationDate"
								value="{{ $currentDate ?: date('Y-m') }}"
								name="valuation_date_display"
								required
								readonly>

							<button class="btn btn-outline-primary" type="button" id="openDatePicker"
								title="{{ __('startup.select_new_date') }}">
								<i class="ti ti-calendar-plus"></i>
							</button>

							@if(count($historicalDates) > 0)
								<button class="btn btn-outline-secondary dropdown-toggle" type="button"
									data-bs-toggle="dropdown" aria-expanded="false"
									title="{{ __('startup.view_history') }}">
									<i class="ti ti-history"></i>
								</button>
								<ul class="dropdown-menu dropdown-menu-end overflow-auto" style="max-height:300px">
									<li class="dropdown-header">{{ __('startup.previous_valuation_dates') }}</li>
									<li><hr class="dropdown-divider"></li>
									@foreach(collect($historicalDates)->unique() as $histDate)
										<li>
											<a class="dropdown-item valuation-history-item d-flex justify-content-between align-items-center"
												href="#"
												data-date="{{ $histDate }}">
													<span>
															<i class="ti ti-calendar me-2"></i>
															{{ \Carbon\Carbon::parse($histDate)->format('F Y') }}
													</span>
													@if($histDate === $currentDate)
															<span class="badge bg-primary ms-2">{{ __('startup.current') }}</span>
													@endif
											</a>
										</li>
									@endforeach
								</ul>
							@endif
						</div>
						<small class="text-muted">
							{{ __('startup.click_calendar_to_enter_new_or_history_to_view_past') }}
						</small>
					</div>
				</div>

				{{-- Currency --}}
				@php
					$getCurrancy = !empty($getCurrancy)
						? $getCurrancy
						: (!empty(auth()->user()->company?->Currency)
							? auth()->user()->company->Currency
							: auth()->user()->currency);
				@endphp
				<div class="col-lg-4">
					<div class="mb-3 mb-md-0">
						<label for="Currency" class="form-label">
							{{ __('settings.Currency') }} <span class="text-danger">*</span>
						</label>
						<select class="form-control form-select" name="Currency" id="currency-select" required
							@if($is_currency_disabled) disabled @endif
							data-original-value="{{ $getCurrancy }}"
							data-warn-confirmed="false"
						>
							<option value="" disabled>{{ __('settings.Currency') }}</option>
							@foreach ($Currencies as $Currency)
								<option value="{{ $Currency->currency_code }}"
									{{ $getCurrancy == $Currency->currency_code ? 'selected' : '' }}>
									{{ $Currency->name }}
								</option>
							@endforeach
						</select>
					</div>
				</div>

				{{-- Save Button --}}
				<div class="col-lg-3">
					<div class="d-flex justify-content-end align-items-center h-100">
						<button 
							type="submit"
							class="btn btn-primary d-flex align-items-center flex-shrink-0 text-nowrap"
							id="fp-save-btn"
							style="min-width: 150px;"
						>
							<i class="ti ti-device-floppy me-2"></i>
							{{ __('admin.Save_Changes') }}
						</button>
					</div>
				</div>

			</div>
		</div>

		{{-- Financial Table --}}
		<div class="card-datatable table-responsive">
			<table class="datatable-basic table mb-0" id="financialTable">
				<thead class="table-light">
					<tr>
						<th class="col-label">
							{{ __('startup.line_item') }}
							<span id="currency-label">{{ !empty($getCurrancy) ? ' ( ' . __('admin.' . $getCurrancy) . ' )' : '' }}</span>
						</th>
						<th id="header-year-0">
							{{ __('startup.past_year') }}<br>
							<small class="fw-normal text-secondary">-</small>
						</th>
						<th id="header-year-1">
							{{ __('startup.forecast_year_1') }}<br>
							<small class="fw-normal text-secondary">-</small>
						</th>
						<th id="header-year-2">
							{{ __('startup.year_2') }}<br>
							<small class="fw-normal text-secondary">-</small>
						</th>
						<th id="header-year-3">
							{{ __('startup.year_3') }}<br>
							<small class="fw-normal text-secondary">-</small>
						</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($sections as $section)
						<tr class="fw-bold">
							<td colspan="5">{{ __($section['title']) }}</td>
						</tr>

						@foreach ($section['rows'] as $row)
							@if (isset($row['calculated']) && $row['calculated'])
								<tr class="table-light">
									<td class="col-label">{{ __($row['label']) }}</td>
									@for ($i = 0; $i < 4; $i++)
										<td>
											<input
												type="text"
												class="form-control @if(isset($row['fw-bold'])) fw-bold @endif"
												id="{{ $row['name'] }}_{{ $i }}"
												name="{{ $row['name'] }}_{{ $i }}"
												value="{{ $row['values'][$i] }}"
												@if(isset($row['special']) && $row['special'] == 'delta' && $i == 0) value="-" @endif
												readonly
												tabindex="-1"
											/>
										</td>
									@endfor
								</tr>
							@else
								<tr>
									<td class="col-label text-nowrap">
										{{ __($row['label']) }}
										@if ($row['required'])
											<span class="text-danger">*</span>
										@endif
									</td>
									@for ($i = 0; $i < 4; $i++)
										<td>
											<input
												type="number"
												class="form-control year-{{ $i }} input-trigger"
												name="{{ $row['name'] }}_{{ $i }}"
												@if($row['required']) required @endif
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

	</form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
	// ═══════════════════════════════════════════════════════════════════════
	// CONFIG
	// ═══════════════════════════════════════════════════════════════════════
	@php $fpCompanyId = (isset($client) && $client->company) ? $client->company->id : auth()->user()->company_id; @endphp
	const CONFIG = {
		COMPANY_ID: {{ $fpCompanyId ?? 'null' }},
		IS_CURRENCY_DISABLED: "{{ $is_currency_disabled }}",
		REALTIME_URL: "{{ $fpCompanyId ? route('company.financial_projection.realtime', $fpCompanyId) : '' }}",
		AUTOSAVE_URL: "{{ $fpCompanyId ? route('company.financial_projection.save', $fpCompanyId) : '' }}",
		CSRF_TOKEN: "{{ csrf_token() }}",
		EDITABLE_FIELDS: ['rev','cogs','sal','opex','da','int','tax','rec','inv','pay','capex','debt']
	};

	// ═══════════════════════════════════════════════════════════════════════
	// STATE
	// ═══════════════════════════════════════════════════════════════════════
	let state = {
		currentValuationDate: "{{ $currentDate ?: '' }}",
		isSaving: false,
		pendingDateChange: null  // Holds the date to switch to after user confirms
	};

	// ═══════════════════════════════════════════════════════════════════════
	// CACHED SELECTORS
	// ═══════════════════════════════════════════════════════════════════════
	const $dateVisible = $('#valuationDate');
	const $dateHidden = $('#valuationDateHidden');
	const $currency = $('#currency-select');
	const $saveBtn = $('#fp-save-btn');
	const $table = $('#financialTable');
	const $form = $('#financialProjectionForm');

	// ═══════════════════════════════════════════════════════════════════════
	// UTILITY FUNCTIONS
	// ═══════════════════════════════════════════════════════════════════════
	const utils = {
		getVal: (name) => {
			const val = $(`input[name="${name}"]`).val();
			return (val === '' || val === '-') ? 0 : parseFloat(val.replace(/,/g, '')) || 0;
		},

		formatNumber: (num) => {
			if (num == null) return '-';
			return Number(num).toLocaleString('en-US', {
				minimumFractionDigits: 0,
				maximumFractionDigits: 2
			});
		},

		setCalc: (id, value) => {
			const formatted = (value == null) ? '-' : utils.formatNumber(value);
			$(`#${id}`).val(formatted);
		},

		setTableLoading: (loading) => {
			$table.toggleClass('opacity-50 pe-none', loading);
		},

		formatDateRange: (start, end) => {
			return `${start.format('MM/YYYY')} – ${end.format('MM/YYYY')}`;
		}
	};

	// ═══════════════════════════════════════════════════════════════════════
	// VALUATION DATE CHANGE WARNING
	// ═══════════════════════════════════════════════════════════════════════

	/**
	 * Returns true if the user has typed a value into at least one
	 * editable financial input (number inputs that are NOT readonly/calculated).
	 */
	function hasFinancialInput() {
		return CONFIG.EDITABLE_FIELDS.some(field => {
			for (let i = 0; i < 4; i++) {
				const val = $(`input[name="${field}_${i}"]`).val();
				if (val !== '' && val !== null && val !== undefined) {
					return true;
				}
			}
			return false;
		});
	}

	/**
	 * Show a confirmation warning before switching valuation dates,
	 * but ONLY when the user has filled at least one financial input.
	 * If there is nothing filled in, skip the warning and call onConfirm directly.
	 *
	 * @param {string} newDate      - The new YYYY-MM date the user selected.
	 * @param {Function} onConfirm  - Called when the user clicks "Yes, change it" (or immediately if no data).
	 * @param {Function} onCancel   - Called when the user clicks "Cancel".
	 */
	function confirmValuationDateChange(newDate, onConfirm, onCancel) {
		if (!hasFinancialInput()) {
			onConfirm();
			return;
		}

		releaseAlert('warning', {
			title: '{{ __("startup.change_valuation_date") }}',
			message: '{{ __("startup.change_valuation_date_warning") }}',
			onConfirm: onConfirm,
			onCancel: onCancel
		});
	}

	// ═══════════════════════════════════════════════════════════════════════
	// UPDATE HEADER DATES
	// ═══════════════════════════════════════════════════════════════════════
	function updateHeaders(valuationDateStr) {
		if (!valuationDateStr || typeof moment === 'undefined') return;

		const v = moment(valuationDateStr);
		const periods = [
			{ id: 0, start: moment(v).subtract(1, 'year'), end: moment(v).subtract(1, 'day') },
			{ id: 1, start: moment(v), end: moment(v).add(1, 'year').subtract(1, 'day') },
			{ id: 2, start: moment(v).add(1, 'year'), end: moment(v).add(2, 'years').subtract(1, 'day') },
			{ id: 3, start: moment(v).add(2, 'years'), end: moment(v).add(3, 'years').subtract(1, 'day') }
		];

		periods.forEach(p => {
			$(`#header-year-${p.id} small`).text(utils.formatDateRange(p.start, p.end));
		});
	}

	// ═══════════════════════════════════════════════════════════════════════
	// CALCULATE ALL FINANCIAL METRICS
	// ═══════════════════════════════════════════════════════════════════════
	function calculateAll() {
		let prevWC = 0, prevDebt = 0;

		for (let i = 0; i < 4; i++) {
			const data = {
				rev: utils.getVal(`rev_${i}`),
				cogs: utils.getVal(`cogs_${i}`),
				sal: utils.getVal(`sal_${i}`),
				opex: utils.getVal(`opex_${i}`),
				da: utils.getVal(`da_${i}`),
				int: utils.getVal(`int_${i}`),
				tax: utils.getVal(`tax_${i}`),
				rec: utils.getVal(`rec_${i}`),
				inv: utils.getVal(`inv_${i}`),
				pay: utils.getVal(`pay_${i}`),
				capex: utils.getVal(`capex_${i}`),
				debt: utils.getVal(`debt_${i}`)
			};

			const calc = {
				ebitda: data.rev - data.cogs - data.sal - data.opex,
				ebit: 0,
				netProfit: 0,
				wc: data.rec + data.inv - data.pay,
				deltaWC: (i === 0) ? null : 0,
				debtChg: (i === 0) ? null : 0,
				fcf: 0
			};

			calc.ebit = calc.ebitda - data.da;
			calc.netProfit = calc.ebit - data.int - data.tax;
			calc.deltaWC = (i === 0) ? null : calc.wc - prevWC;
			calc.debtChg = (i === 0) ? null : data.debt - prevDebt;
			calc.fcf = calc.netProfit + data.da - data.capex - (calc.deltaWC ?? 0);

			utils.setCalc(`ebitda_${i}`, calc.ebitda);
			utils.setCalc(`ebit_${i}`, calc.ebit);
			utils.setCalc(`np_${i}`, calc.netProfit);
			utils.setCalc(`wc_${i}`, calc.wc);
			utils.setCalc(`dwc_${i}`, calc.deltaWC);
			utils.setCalc(`ddebt_${i}`, calc.debtChg);
			utils.setCalc(`fcf_${i}`, calc.fcf);

			prevWC = calc.wc;
			prevDebt = data.debt;
		}
	}

	// ═══════════════════════════════════════════════════════════════════════
	// LOAD PROJECTION FROM SERVER
	// ═══════════════════════════════════════════════════════════════════════
	function loadProjection(valuationDate) {
		if (!valuationDate) return;

		utils.setTableLoading(true);

		$.ajax({
			url: `${CONFIG.REALTIME_URL}?valuation_date=${encodeURIComponent(valuationDate)}`,
			method: 'GET',
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
			dataType: 'json'
		})
		.done(function(data) {
			if (!data.success) {
				handleError(data.message ?? '{{ __("admin.error_loading_financial_data") }}');
				return;
			}

			// Update state
			state.currentValuationDate = data.valuation_date;
			$dateHidden.val(data.valuation_date);
			$dateVisible.val(data.valuation_date);

			// Update currency if locked
			if (data.is_currency_locked && data.currency) {
				$currency.val(data.currency);
				$currency.prop('disabled', true);
				$currency.data('original-value', data.currency);
			}

			// Populate fields
			for (let i = 0; i < 4; i++) {
				const yearData = data.years[i]?.data ?? {};
				CONFIG.EDITABLE_FIELDS.forEach(field => {
					const val = yearData[field];
					$(`input[name="${field}_${i}"]`).val((val != null) ? val : '');
				});
			}

			calculateAll();
			updateHeaders(data.valuation_date);
			updateHistoryBadges(data.valuation_date);
		})
		.fail(function(err, xhr) {
			const errMsg = err.responseJSON?.message;
			console.log('errMsg', errMsg);
			handleError(err.responseJSON?.message || '{{ __("admin.error_loading_financial_data") }}');
			console.error('[FinancialProjection] Load error:', err);
		})
		.always(function() {
			utils.setTableLoading(false);
		});
	}

	// ═══════════════════════════════════════════════════════════════════════
	// UPDATE HISTORY BADGES
	// ═══════════════════════════════════════════════════════════════════════
	function updateHistoryBadges(currentDate) {
		$('.valuation-history-item .badge.bg-primary').remove();
		$(`.valuation-history-item[data-date="${currentDate}"] span`).append(
			`<span class="badge bg-primary ms-2">{{ __("startup.current") }}</span>`
		);
	}

	// ═══════════════════════════════════════════════════════════════════════
	// SAVE PROJECTION
	// ═══════════════════════════════════════════════════════════════════════
	function save() {
		if (state.isSaving) return;

		// Check currency change warning
		const originalCurrency = $currency.data('original-value');
		const currentCurrency = $currency.val();
		const currencyChanged = currentCurrency !== originalCurrency;
		
		if (currencyChanged && $currency.data('warn-confirmed') !== 'true' && !CONFIG.IS_CURRENCY_DISABLED) {
			releaseAlert('warning', {
				title: '{{ __('admin.Warning') }}!',
				message: '{{ __('admin.change_currency_warning') }}',
				onConfirm: () => {
					$currency.data('warn-confirmed', 'true');
					save();
				},
				onCancel: () => {
					$currency.val(originalCurrency);
				}
			});
			return;
		}

		const valuationDate = $dateHidden.val() || state.currentValuationDate;
		if (!valuationDate) return;

		state.isSaving = true;
		$saveBtn.prop('disabled', true).html('{{ __("admin.saving") }}…');

		const formData = new FormData();
		formData.append('valuation_date', valuationDate);
		formData.append('Currency', $currency.val() || '');
		formData.append('_token', CONFIG.CSRF_TOKEN);

		// Add all editable fields
		CONFIG.EDITABLE_FIELDS.forEach(field => {
			for (let i = 0; i < 4; i++) {
				const val = $(`input[name="${field}_${i}"]`).val().replace(/,/g, '');
				formData.append(`${field}_${i}`, val);
			}
		});

		$.ajax({
			url: CONFIG.AUTOSAVE_URL,
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			headers: { 'X-CSRF-TOKEN': CONFIG.CSRF_TOKEN }
		})
		.done(function(data) {
			if (!data.success) {
				releaseAlert('danger', {
					title: '{{ __("admin.Error") }}',
					message: data.message ?? '{{ __("admin.unepxected_error") }}'
				});
				return;
			}

			// Update recalculated values
			$.each(data.recalculated ?? {}, function(key, val) {
				$(`#${key}`).val((val == null) ? '-' : utils.formatNumber(val));
			});

			releaseAlert('success', {
				title: '{{ __("admin.Success") }}',
				message: data.message ?? '{{ __("admin.financial_data_saved") }}'
			});

			// Update original value and lock currency if changed
			const wasCurrencyChanged = $currency.val() !== $currency.data('original-value');
			$currency.data('original-value', $currency.val());
			$currency.data('warn-confirmed', 'false');
			if (wasCurrencyChanged) {
				$currency.prop('disabled', true);
			}
		})
		.fail(function(err) {
			releaseAlert('danger', {
				title: '{{ __("admin.Error") }}',
				message: '{{ __("admin.save_failed") }}'
			});
			console.error('[FinancialProjection] Save error:', err);
		})
		.always(function() {
			state.isSaving = false;
			$saveBtn.prop('disabled', false).html('<i class="ti ti-device-floppy me-2"></i> {{ __("admin.Save_Changes") }}');
		});
	}

	// ═══════════════════════════════════════════════════════════════════════
	// ERROR HANDLER
	// ═══════════════════════════════════════════════════════════════════════
	function handleError(message) {
		console.error('[FinancialProjection]', message);
		releaseAlert('danger', {
			title: '{{ __("admin.Error") }}',
			message: message || '{{ __("admin.unexpected_error") }}'
		});
	}

	// ═══════════════════════════════════════════════════════════════════════
	// EVENT HANDLERS
	// ═══════════════════════════════════════════════════════════════════════
	
	// Currency change - reset warning flag if user reverts to original value
	$currency.on('change', function() {
		if ($(this).prop('disabled')) return;
		if ($(this).val() === $(this).data('original-value')) {
			$(this).data('warn-confirmed', 'false');
		}
		const selectedVal = $(this).val();
		$('#currency-label').text(selectedVal ? ' ( ' + selectedVal + ' )' : '');
	});

	// Input triggers recalculation
	$('.input-trigger').on('input', function() {
		const val = $(this).val();
		if (val.length > 15) $(this).val(val.slice(0, 15));
		this.setCustomValidity(''); // Clear custom validation message on input
		calculateAll();
	});

	// Set custom validation message on required fields
	$('.input-trigger[required]').on('invalid', function() {
		this.setCustomValidity('{{ __("admin.field_is_required") }}');
	});

	// History item click — warn before switching dates
	$('.valuation-history-item').on('click', function(e) {
		e.preventDefault();
		e.stopImmediatePropagation();

		const selectedDate = $(this).data('date');
		if (!selectedDate || selectedDate === state.currentValuationDate) return;

		confirmValuationDateChange(selectedDate,
			// Confirmed — proceed with loading the selected date
			function() {
				loadProjection(selectedDate);
			},
			// Cancelled — do nothing; stay on current date
			function() { /* no-op */ }
		);
	});

	// Form submit
	$form.on('submit', function(e) {
		e.preventDefault();

		if (!$dateHidden.val()) {
			releaseAlert('warning', {
				hideCancel: true,
				title: '{{ __("startup.valuation_date_required") }}',
				message: '{{ __("startup.please_select_valuation_date_before_saving") }}'
			});
			$dateVisible.addClass('is-invalid');
			return;
		}

		save();
	});

	// ═══════════════════════════════════════════════════════════════════════
	// FLATPICKR INITIALIZATION
	// ═══════════════════════════════════════════════════════════════════════
	const flatpickrInstance = flatpickr('#valuationDate', {
		defaultDate: state.currentValuationDate ? new Date(state.currentValuationDate + '-01') : null,
		plugins: [
			new monthSelectPlugin({
				shorthand: true,
				dateFormat: "Y-m",
				altFormat: "F Y"
			})
		],
		onChange: function(selectedDates, dateStr) {
			if (!selectedDates.length) return;

			const newDateStr = moment(selectedDates[0]).format("YYYY-MM");

			// No change — nothing to warn about
			if (newDateStr === state.currentValuationDate) return;

			$dateVisible.removeClass('is-invalid');

			confirmValuationDateChange(newDateStr,
				// Confirmed — load the new date
				function() {
					loadProjection(newDateStr);
				},
				// Cancelled — restore flatpickr to the previous date without triggering onChange again
				function() {
					if (state.currentValuationDate) {
						flatpickrInstance.setDate(state.currentValuationDate + '-01', false);
					} else {
						flatpickrInstance.clear(false);
					}
				}
			);
		}
	});

	$('#openDatePicker').on('click', function(e) {
		e.preventDefault();
		flatpickrInstance.open();
	});

	// ═══════════════════════════════════════════════════════════════════════
	// INITIALIZATION
	// ═══════════════════════════════════════════════════════════════════════
	calculateAll();
	updateHeaders(state.currentValuationDate || moment().format('YYYY-MM'));
	
	if (state.currentValuationDate) {
		loadProjection(state.currentValuationDate);
	}
});
</script>
@endpush