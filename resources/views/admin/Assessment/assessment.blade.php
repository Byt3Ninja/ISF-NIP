@extends('layouts.admin')
@php
	$l = app()->getLocale();
@endphp

@section('content')
<div>
	@if (session()->has('message'))
		<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible" role="alert">
			{{ session()->get('message_resolved') ? session()->get('message') : __('admin.' . session()->get('message')) }}
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	@endif

	<section class="row">
		<div class="col-xl-12 col-md-12 col-sm-12">
			<div class="card">
				@if( $_user->ableToAny(['assessments.create', 'assessments.store'], $permissions) )
				<div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between border-bottom">
					<h5 class="card-title mb-3 mb-lg-0">{{ __('admin.Assessments') }}</h5>
					<div class="d-flex align-items-center gap-2">
            <x-export-dropdown />

						<button
							class="btn btn-primary"
							data-bs-toggle="modal"
							data-bs-target="#AssessmentModal"
							data-mode="create"
						>
							<i class="ti ti-plus"></i>
							<span class="ms-2">{{ __('admin.Add_New') }}</span>
						</button>
					</div>
				</div>
				@endif

				<div class="card-datatable table-responsive">
					<table class="table mb-0" id="assessments-table">
						<thead class="table-light">
							<tr>
								<th>#</th>
								<th>{{ __('admin.Company') }}</th>
								<th>{{ __('admin.Founder') }}</th>
								<th>{{ __('admin.start_date') }}</th>
								<th>{{ __('admin.end_date') }}</th>
								<th>{{ __('admin.Created at') }}</th>
								@if( $_user->ableToAny(['assessments.show', 'question-assessments.questions_manage', 'assessments.edit', 'assessments.update', 'assessments.destroy'], $permissions) )
								<th>{{ __('admin.Actions') }}</th>
								@endif
							</tr>
						</thead>
						<tbody>
							@php $c = 1; @endphp
							@foreach ($assessments as $assessment)
								<tr data-founder-id="{{ $assessment->user_id }}" data-company-id="{{ optional($assessment->user->company)->id ?? '0' }}">
									<td>{{ $c++ }}</td>
									<td>
										@if($assessment->user->company)
											<a href="{{ route('company.profile_view', $assessment->user->company->id) }}" class="d-flex align-items-center gap-2">
												<img
													src="{{ $assessment->user->company->logo ?? '/images/logo-placeholder.png' }}"
													alt="logo"
													class="rounded-circle border"
													height="40" width="40"
												/>
												<span class="fs-6 mb-0 text-dark">{{ $assessment->user->company->{'name_'.app()->getLocale()} }}</span>
											</a>
										@else
											<span class="text-muted">{{ $l == 'en' ? 'No Company' : 'لا توجد شركة' }}</span>
										@endif
									</td>
									<td>{{ $assessment->user->{'first_name_'.$l} }} {{ $assessment->user->{'last_name_'.$l} }}</td>
									<td class="text-center">{{ $assessment->start_date ?? '-' }}</td>
									<td class="text-center">{{ $assessment->end_date ?? '-' }}</td>
									<td>{{ $assessment->created_at }}</td>

									@if( $_user->ableToAny(['assessments.show', 'assessments.questions_manage', 'assessments.edit', 'assessments.update', 'assessments.destroy'], $permissions) )
									<td>
										@if( $_user->ableTo('assessments.show', $permissions) )
											<a
												class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
												href="{{ route('assessments.show', $assessment->id) }}"
												data-bs-toggle="tooltip" data-bs-placement="top"
												title="{{ __('admin.View') }}"
											>
												<i class="ti ti-eye ti-md"></i>
											</a>
										@endif

										@if( $_user->ableTo('assessments.questions_manage', $permissions) )
											<a
												class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
												href="{{ route('assessments.questions_manage', $assessment->id) }}"
												data-bs-toggle="tooltip" data-bs-placement="top"
												title="{{ __('admin.manageQuestions') }}"
											>
												<i class="ti ti-list-check ti-md"></i>
											</a>
										@endif

										@if( $_user->ableToAny(['assessments.edit', 'assessments.update'], $permissions) )
											<button
												class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
												data-bs-toggle="modal"
												data-bs-target="#AssessmentModal"
												data-mode="edit"
												data-id="{{ $assessment->id }}"
												data-user-id="{{ $assessment->user_id }}"
												data-start-date="{{ $assessment->start_date }}"
												data-end-date="{{ $assessment->end_date }}"
												data-bs-placement="top"
												title="{{ __('admin.Edit') }}"
											>
												<i class="ti ti-pencil ti-md"></i>
											</button>
										@endif

										@if( $_user->ableTo('assessments.destroy', $permissions) )
											<form action="{{ route('assessments.destroy', $assessment->id) }}" method="POST" style="display:inline-block;">
												@csrf
												@method('DELETE')
												<button
													class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
													type="submit"
													onclick="return confirmDelete(event)"
													data-bs-toggle="tooltip" data-bs-placement="top"
													title="{{ __('admin.Delete') }}"
												>
													<i class="ti ti-trash ti-md"></i>
												</button>
											</form>
										@endif
									</td>
									@endif
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</section>
</div>

{{-- ============================================================
     Unified Create / Edit Assessment Modal
     Mode is switched entirely via data-* attributes on the
     trigger button — no duplicate markup, no per-row modals.
     ============================================================ --}}
@if( $_user->ableToAny(['assessments.create', 'assessments.store', 'assessments.edit', 'assessments.update'], $permissions) )
<div class="modal fade" id="AssessmentModal" tabindex="-1" aria-labelledby="AssessmentModalLabel" aria-hidden="true" role="dialog">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title mb-0" id="AssessmentModalLabel"></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="assessment-form" method="POST" class="pt-0">
					@csrf
					{{-- Method field — switched between POST (create) and PUT (edit) --}}
					<input type="hidden" name="_method" id="assessment-form-method" value="POST">

					<div class="row">
						<div class="col-12 mb-4">
							<label class="form-label fs-6">{{ __('admin.Founder') }}</label>
							<select class="form-select select2" name="user_id" id="modal-user-id" data-placeholder="{{ __('admin.Select_Founder') }}">
								<option value=""></option>
								@foreach ($users as $user)
									<option value="{{ $user->id }}">{{ $user->name }}</option>
								@endforeach
							</select>
						</div>

						<div class="col-12 mb-4">
							<label for="modal-start-date" class="form-label">{{ __('admin.start_date') }}</label>
							<input
								class="form-control date"
								id="modal-start-date"
								type="text"
								name="start_date"
								autocomplete="off"
                placeholder="{{ __('admin.start_date') }}"
							/>
						</div>

						<div class="col-12 mb-4" id="end-date-group">
							<label for="modal-end-date" class="form-label">{{ __('admin.end_date') }}</label>
							<input
								class="form-control date"
								id="modal-end-date"
								type="text"
								name="end_date"
								autocomplete="off"
                placeholder="{{ __('admin.end_date') }}"
							/>
						</div>
					</div>

					<div class="d-flex align-items-center justify-content-end gap-3 mt-4">
						<button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">
							{{ __('admin.Cancel') }}
						</button>
						@if( $_user->ableToAny(['assessments.create', 'assessments.store', 'assessments.edit', 'assessments.update'], $permissions) )
						<button type="submit" class="btn btn-primary data-submit">
							{{ __('admin.submit') }}
						</button>
						@endif
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endif

@stop

@section('pagescripts')
<script>
/* ============================================================
   Assessments Table
   ============================================================ */

$(function () {
  'use strict';

  // ── Constants ────────────────────────────────────────────

  const TABLE_ID = 'assessments-table';
  const LANG     = '{{ $l }}';

  // Column indices
  const COL = { COMPANY: 0, FOUNDER: 1, START_DATE: 3, END_DATE: 4 };

  // Active filter state
  const filters = { company: '', founder: '', dateFrom: '', dateTo: '' };

  // Routes passed from Laravel
  const ROUTES = {
    store:  '{{ route('assessments.store') }}',
    update: (id) => '{{ route('assessments.update', '') }}/' + id,
  };

  // ── Flatpickr factory ────────────────────────────────────

  function makePicker(target, opts = {}) {
    const { onClear, ...rest } = opts;
    return $(target).flatpickr({
      monthSelectorType: 'static',
      allowInput: true,
      ...rest,
      onReady(_, __, instance) {
        const btn = Object.assign(document.createElement('button'), {
          type: 'button',
          innerHTML: 'Clear',
        });
        btn.classList.add('btn', 'btn-sm', 'btn-outline-secondary');
        btn.addEventListener('click', () => { instance.clear(); instance.close(); onClear?.(); });
        instance.calendarContainer.appendChild(btn);
      },
    });
  }

  // ── Custom search — uses DataTables API row data ─────────

  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    if (settings.nTable.id !== TABLE_ID) return true;

    if (filters.company || filters.founder) {
      const api  = new $.fn.dataTable.Api(settings);
      const node = api.row(dataIndex).node();

      if (filters.company) {
        const id = node?.dataset.companyId;
        if (!id || id === '0' || id !== filters.company) return false;
      }
      if (filters.founder) {
        const id = node?.dataset.founderId;
        if (!id || id !== filters.founder) return false;
      }
    }

    if (filters.dateFrom && data[COL.START_DATE] < filters.dateFrom) return false;
    if (filters.dateTo   && data[COL.END_DATE]   > filters.dateTo)   return false;

    return true;
  });

  // ── DataTable initialisation ─────────────────────────────

  const table = $(`#${TABLE_ID}`).DataTable({
    ...getSharedDataTableConfig({ title: 'Assessments', descIndex: 5, actionsIndex: 6 }),
    dom: `
      <"dt-toolbar card-header d-flex flex-wrap gap-2 align-items-center py-3 px-4"
        <"dt-toolbar__length"l>
        <"dt-filter dt-filter--company">
        <"dt-filter dt-filter--founder">
        <"dt-filter dt-filter--date-from">
        <"dt-filter dt-filter--date-to">
        <"dt-toolbar__search ms-auto"f>
      >
      <"table-responsive"t>
      <"card-footer d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-4"
        <"dt-info"i>
        <"dt-paging"p>
      >
    `,
    initComplete() {
      buildCompanyFilter(this.api());
      buildFounderFilter(this.api());
      buildDateFilter('date-from', 'dt-filter--date-from', "{{ __('cycles.Start_Date') }}", 'dateFrom');
      buildDateFilter('date-to',   'dt-filter--date-to',   "{{ __('cycles.End_Date') }}",   'dateTo');

      $('.dataTables_filter .form-control').removeClass('form-control-sm');
      $('.dataTables_length .form-select').removeClass('form-select-sm');
    },
  });

  // ── Filter builders ──────────────────────────────────────

  function buildCompanyFilter(api) {
    const COMPANIES = {!! json_encode($startups->mapWithKeys(fn($c) => [$c->id => $c->{'name_'.$l}])) !!};

    const seenIds = new Set(
      api.rows().nodes().toArray()
         .map(tr => tr.dataset.companyId)
         .filter(id => id && id !== '0')
    );

    const $select = buildSelect('company-filter', "{{ __('admin.all_startups') }}", 'dt-filter--company');

    Object.entries(COMPANIES)
      .filter(([id]) => seenIds.has(String(id)))
      .forEach(([id, name]) => $select.append(`<option value="${id}">${name}</option>`));

    $select.on('change', function () { filters.company = this.value; table.draw(); });
  }

  function buildFounderFilter(api) {
    const FOUNDERS = @json($founders);
    const founderMap = Object.fromEntries(
      FOUNDERS.map(f => [String(f.id), `${f[`first_name_${LANG}`]} ${f[`last_name_${LANG}`]}`])
    );

    const seenIds = new Set(
      api.rows().nodes().toArray()
         .map(tr => tr.dataset.founderId)
         .filter(Boolean)
    );

    const $select = buildSelect('founder-filter', "{{ __('admin.all_founders') }}", 'dt-filter--founder');

    seenIds.forEach(id => {
      if (founderMap[id]) $select.append(`<option value="${id}">${founderMap[id]}</option>`);
    });

    $select.on('change', function () { filters.founder = this.value; table.draw(); });
  }

  function buildDateFilter(idSuffix, containerClass, placeholder, filterKey) {
    const colIndex = filterKey === 'dateFrom' ? COL.START_DATE : COL.END_DATE;
    const colDates = table.column(colIndex).data().toArray().filter(Boolean).sort();
    const minDate  = colDates.at(0);
    const maxDate  = colDates.at(-1);

    const $input = $(`
      <input type="text" id="filter-${idSuffix}"
             class="form-control"
             placeholder="${placeholder}"
             style="min-width:130px;">
    `).appendTo(`#${TABLE_ID}_wrapper .${containerClass}`);

    makePicker($input[0], {
      dateFormat: 'Y-m-d',
      ...(minDate && { minDate }),
      ...(maxDate && { maxDate }),
      onChange([date]) { filters[filterKey] = date ? date.toISOString().slice(0, 10) : ''; table.draw(); },
      onClear()        { filters[filterKey] = ''; table.draw(); },
    });
  }

  function buildSelect(id, placeholder, containerClass) {
    return $(`
      <select id="${id}" class="form-select" style="min-width:150px;">
        <option value="">${placeholder}</option>
      </select>
    `).appendTo(`#${TABLE_ID}_wrapper .${containerClass}`);
  }

  // ── Export buttons ───────────────────────────────────────

  $('#exportCsv').on('click',   e => { e.preventDefault(); table.button('.buttons-csv').trigger(); });
  $('#exportExcel').on('click', e => { e.preventDefault(); table.button('.buttons-excel').trigger(); });

  // ── Unified Assessment Modal ─────────────────────────────

  const $modal       = $('#AssessmentModal');
  const $form        = $('#assessment-form');
  const $modalTitle  = $('#AssessmentModalLabel');
  const $methodInput = $('#assessment-form-method');
  const $userId      = $('#modal-user-id');
  const $startDate   = $('#modal-start-date');
  const $endDate     = $('#modal-end-date');
  const $endGroup    = $('#end-date-group');

  // Initialise Select2 once; it persists across open/close cycles
  $userId.select2({ dropdownParent: $modal });

  // Initialise flatpickr on both date fields once
  const startPicker = makePicker($startDate[0], { dateFormat: 'Y-m-d' });
  const endPicker   = makePicker($endDate[0],   { dateFormat: 'Y-m-d' });

  $modal.on('show.bs.modal', function (e) {
    const $trigger  = $(e.relatedTarget);
    const mode      = $trigger.data('mode'); // 'create' | 'edit'
    const isEdit    = mode === 'edit';

    // ── Title ──────────────────────────────────────────────
    $modalTitle.text(isEdit
      ? "{{ __('admin.Edit') . ' ' . __('admin.Assessment') }}"
      : "{{ __('admin.Add new Assessment') }}"
    );

    // ── Form action & HTTP method ──────────────────────────
    $form.attr('action', isEdit ? ROUTES.update($trigger.data('id')) : ROUTES.store);
    $methodInput.val(isEdit ? 'PUT' : 'POST');

    // ── Field values ───────────────────────────────────────
    $userId.val(isEdit ? $trigger.data('userId') : null).trigger('change');
    startPicker.set('minDate', isEdit ? null : 'today');
    startPicker.setDate(isEdit ? $trigger.data('startDate') : null, false);
    endPicker.setDate(isEdit  ? $trigger.data('endDate')   : null, false);

    // end_date field: visible on edit, hidden on create
    $endGroup.toggle(isEdit);
  });

  // Reset form state when modal fully closes
  $modal.on('hidden.bs.modal', function () {
    $form[0].reset();
    $userId.val(null).trigger('change');
    startPicker.clear();
    endPicker.clear();
    $methodInput.val('POST');
  });

  // ── Delete confirmation ──────────────────────────────────

  window.confirmDelete = function (event) {
    event.preventDefault();
    releaseAlert('warning', {
      title: "{{__('admin.SureDelete')}}",
      message: "{{__('admin.Recover')}}",
      confirmText: "{{ __('admin.DYes') }}",
      cancelText: "{{ __('admin.No') }}",
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) $(event.target).closest('form').submit();
    });
    return false;
  };
});
</script>
@stop