@extends('layouts.admin')

@section('title', __('admin.Questions Bank') )
@section('subTitle', __('admin.Questions Bank List'))

@php
$tabs = [
	['title' => __('admin.Questions Bank'), 'url' => 'dashboard/questions-bank', 'icon' => 'question-mark'],
	['title' => __('admin.Categories'), 'url' => 'dashboard/question-categories', 'icon' => 'category-2'],
	['title' => __('admin.weights'), 'url' => 'dashboard/weights', 'icon' => 'weight'],
]
@endphp

@section('content')
<div>
	{{-- Alerts --}}
	@if (session()->has('success'))
	<x-alert type="success" message="{{ __('admin.'. session()->get('success')) }}" />
	@endif

	@if (session()->has('error'))
	<x-alert type="danger" message="{{ __('admin.'.session()->get('error')) }}" />
	@endif

	<x-navigation-pills :tabs="$tabs" />

	<div class="card">
		<div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center">
			<h5 class="card-title mb-0">{{__('admin.Questions Bank List')}}</h5>
			<div class="d-flex align-items-center gap-3">
				<x-export-dropdown />
				<a href="{{ route('questions-bank.create') }}" class="btn btn-primary">
					<i class="ti ti-plus me-2"></i> {{ __('admin.Add New')}}
				</a>
			</div>
		</div>

		<div class="card-datatable table-responsive">
			<table id="questions-bank-datatable" class="table">
				<thead>
					<tr class="table-light">
						<th>#</th>
						<th>{{__('admin.question')}}</th>
						<th>{{__('admin.Organization')}}</th>
						<th class="text-center text-nowrap">{{__('admin.questionGroup')}}</th>
						<th class="text-center">{{__('admin.question_Type')}}</th>
						<th class="text-center">{{__('admin.question_difficulty')}}</th>
						<th class="text-center">{{__('admin.Actions')}}</th>
					</tr>
				</thead>
				<tbody>
					@foreach( $questions as $question )
					<tr id="row_{{ $question->id }}">
						<td>{{ $question->id }}</td>
						<td>{{ $question->question }}</td>
						<td>
							@forelse($question->organizations as $organization)
								<span class="badge bg-label-secondary">{{ $organization->name }}</span>
							@empty
								N/A
							@endforelse
						</td>
						<td class="text-center">
							<span class="badge bg-label-{{ $question->group == 'quiz' ? 'primary' : ($question->group == 'survey' ? 'info' : 'warning') }}">
								{{ __('admin.' . ucfirst($question->group)) }}
							</span>
						</td>
						<td class="text-center">
							@php
								$typeLabel = $question->group == 'quiz' ? ($question->type == 'mcq' ? 'type_mcq' : 'type_tfq') : 
																($question->group == 'survey' ? ($question->survey_type == 'stars' ? 'survey_type_stars' : 'survey_type_radio') : 'Assessment');
							@endphp
							<span class="badge bg-label-secondary">{{ __('admin.' . $typeLabel) }}</span>
						</td>
						<td class="text-center">
							@if( $question->group == 'quiz' )
								<span class="badge bg-label-{{ $question->difficulty == 'easy' ? 'success' : ($question->difficulty == 'medium' ? 'warning' : 'danger') }}">
									{{ __('admin.difficulty_' . $question->difficulty) }}
								</span>
							@else
								-
							@endif
						</td>
						<td class="text-center">
							<div class="d-flex justify-content-center gap-2">
								<a href="{{ route('questions-bank.edit', $question->id) }}" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill">
									<i class="ti ti-pencil ti-md"></i>
								</a>
								<button class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-record" 
										data-id="{{ $question->id }}" 
										data-url="{{ route('questions-bank.destroy', $question->id) }}">
									<i class="ti ti-trash ti-md"></i>
								</button>
							</div>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
@stop

@section('pagescripts')
<script>
$(function () {
  'use strict';

  const table = $('#questions-bank-datatable').DataTable({
    ...getSharedDataTableConfig({ title: 'Questions Bank', descIndex: 0, actionsIndex: 6 }),
    /* 
       DOM Configuration:
       <"wrapper-class" l <"filter-injection-point"> f >
       l = length menu, f = filter input, t = table
    */
    dom: '<"card-header d-flex flex-column flex-md-row align-items-center justify-content-between p-3 gap-3" <"d-flex align-items-center gap-3" l <"dt-custom-filters d-flex gap-2">> f>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

    initComplete: function () {
      const $wrapper = $(this.api().table().container());
      const api = this.api();
      const filterContainer = $('.dt-custom-filters');

      // Columns to build filters for: [ColumnIndex, LocalizationKey/Label]
      const filterConfigs = [
        { idx: 2, label: "{{ __('admin.All_Organizations') }}" },
        { idx: 3, label: "{{ __('admin.All_Groups') }}" },
        { idx: 4, label: "{{ __('admin.All_Types') }}" },
        { idx: 5, label: "{{ __('admin.All_Difficulties') }}" }
      ];

      filterConfigs.forEach(config => {
        const column = api.column(config.idx);

        // Create the Select element
        const select = $(`<select class="form-select" style="min-width: 120px;"><option value="">${config.label}</option></select>`)
          .appendTo(filterContainer)
          .on('change', function () {
            const val = $.fn.dataTable.util.escapeRegex($(this).val());
            // Use exact match regex ^...$ to avoid partial matches (e.g. 'Easy' inside 'Easy-Going')
            column.search(val ? `^${val}$` : '', true, false).draw();
          });

        // Extract unique data from the column
        column.data().unique().sort().each(function (d) {
          if (!d || d === '-') return;

          // Production Tip: Cells contain HTML badges. We must extract the plain text.
          const textValue = $('<div/>').html(d).text().trim();

          if (textValue) {
            // Check if option already exists (to prevent duplicates after HTML stripping)
            if (select.find(`option[value="${textValue}"]`).length === 0) {
              select.append(`<option value="${textValue}">${textValue}</option>`);
            }
          }
        });
      });

			$('.dataTables_filter .form-control').removeClass('form-control-sm').css('width', '');
      $('.dataTables_length .form-select').removeClass('form-select-sm');
      
      $wrapper.find('.form-select, .dataTables_length select').each(function () {
        const $this = $(this);
        if ($this.hasClass('select2-hidden-accessible')) return;

        const isLength = $this.closest('.dataTables_length').length > 0;

        $this.wrap('<div class="position-relative' + (isLength ? ' d-inline-block' : '') + '"></div>');

        $this.select2({
          dropdownParent: $this.parent(),
          width: isLength ? '80px' : '100%',
          minimumResultsForSearch: Infinity,
        });

        if (!isLength) {
          $this.next('.select2-container').css({
            'min-width': '150px',
            'max-width': '200px'
          });
        }
      });
    }
  });

  // Link external triggers (Excel/CSV) if applicable
  $('#exportExcel').on('click', () => table.button('.buttons-excel').trigger());
  $('#exportCsv').on('click', () => table.button('.buttons-csv').trigger());

  // Generic Delete Handler
  $(document).on('click', '.delete-record', function () {
    const btn = $(this);
    const url = btn.data('url');

    releaseAlert('warning', {
      title: "{{__('admin.SureDelete')}}",
      confirmText: "{{ __('admin.DYes') }}",
      cancelText: "{{ __('admin.No') }}",
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
        $.ajax({
          url: url,
          type: 'POST',
          data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
          success: function () {
            table.row(btn.closest('tr')).remove().draw(false);
            toastr.success("{{ __('admin.DeletedSuccessfully') }}");
          },
          error: function (xhr) {
            toastr.error("{{ __('admin.Error') }}");
          }
        });
      }
    });
  });
});
</script>
@stop
