{{-- Customers history (data entry surface — summary metrics live on the Startup Profile › Startup Growth page) --}}
<div class="card card-datatable table-responsive">
  <div class="card-header d-flex justify-content-between align-items-center border-bottom">
    <h5 class="mb-0">{{ __('startup.Customers History') }}</h5>
    <button id="add-new-startup-growth-modal-btn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startupGrowthModal">
      <i class="ti ti-plus me-2"></i> {{ __('admin.Add New') }}
    </button>
  </div>

  <!-- Custom Filters & Export Row -->
  <div class="card-body border-bottom py-5">
    <div class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label fw-bold" for="filter_year">{{ __('startup.Year') }}</label>
        <select id="filter_year" class="form-select">
          <option value="">{{ __('startup.all_years') }}</option>
          @foreach($growth_records->pluck('year')->unique()->sort() as $yr)
            <option value="{{ $yr }}">{{ $yr }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold" for="filter_segment">{{ __('startup.Segment') }}</label>
        <select id="filter_segment" class="form-select">
          <option value="">{{ __('startup.all_segments') }}</option>
          <option value="MIXED">{{ __('startup.mixed') }}</option>
          <option value="B2C">B2C</option>
          <option value="B2B">B2B</option>
          <option value="ENTERPRISE">{{ __('startup.enterprise') }}</option>
        </select>
      </div>
      <div class="col-md-4 text-md-end text-start">
        <a id="export-csv-btn" href="/dashboard/startupGrowth/export/{{ $company?->id }}" class="btn btn-outline-secondary w-100 w-md-auto">
          <i class="ti ti-download me-2"></i>{{ __('startup.Export CSV') }}
        </a>
      </div>
    </div>
  </div>
  <table class="datatables-basic table" id="growthTable">
    <thead class="table-light">
      <tr>
        <th>{{ __('startup.Year') }}</th>
        <th>{{ __('startup.Month') }}</th>
        <th>{{ __('startup.Total customers') }}</th>
        <th>{{ __('startup.New customers') }}</th>
        <th>{{ __('startup.MoM growth') }}</th>
        <th>{{ __('startup.Year cumulative growth') }}</th>
        <th>{{ __('startup.Segment') }}</th>
        <th>{{ __('admin.Actions') }}</th>
      </tr>
    </thead>
    <tbody>
      @foreach( $growth_records as $record )
        <tr>
          <td data-order="{{ $record->year }}">{{ $record->year }}</td>
          <td data-order="{{ $record->month ?? 0 }}">{{ $record->month ? __('startup.mini_month_' . $record->month) : __('startup.Yearly') }}</td>
          <td data-order="{{ $record->total }}">{{ $record->total }}</td>
          <td data-order="{{ $record->newly }}">{{ $record->newly }}</td>
          <td>{{ $record->MoM }}</td>
          <td>{{ $record->YearCumulative }}</td>
          <td>{{ strtoupper( $record->segment ) }}</td>
          <td>
            <div class="d-flex align-items-center">
              <button type="button"
                class="btn btn-sm btn-icon btn-text-secondary rounded-pill edit-growth-btn"
                data-id="{{ $record->id }}"
                data-year="{{ $record->year }}"
                data-month="{{ $record->month }}"
                data-total="{{ $record->total }}"
                data-new="{{ $record->newly }}"
                data-segment="{{ $record->segment }}"
                data-notes="{{ $record->notes }}"
                data-bs-toggle="modal" data-bs-target="#startupGrowthModal"
                title="{{ __('admin.Edit') }}">
                <i class="ti ti-edit"></i>
              </button>
              <form action="/dashboard/startupGrowth/delete/{{ $record->id }}" method="POST" class="delete-record-form d-inline ms-1">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="deleteRecord(event, this)" class="btn btn-sm btn-icon text-danger btn-text-danger rounded-pill waves-effect">
                  <i class="ti ti-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="modal fade" id="startupGrowthModal" tabindex="-1" aria-labelledby="startupGrowthModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="/dashboard/startupGrowth/add" id="add-startup-growth-form">
        @csrf
        <input type="hidden" name="record_id" id="record_id" value="" />
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
                <option value="0">{{ __('startup.Yearly') }}</option>
                @for( $i = 1; $i <= 12; $i++ )
                  <option value="{{ $i }}" {{ $i == date('m') ? 'selected' : '' }}>{{ __('startup.month_' . $i) }}</option>
                @endfor
              </select>
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 mb-6">
              <label for="add_growth_total_customers" class="form-label">{{ __('startup.Total customers') }}</label>
              <input type="number" class="form-control" name="add_growth_total_customers" id="add_growth_total_customers" placeholder="{{ __('startup.Total customers') }}">
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 mb-6">
              <label for="add_growth_new_customers" class="form-label">{{ __('startup.New customers this period') }}</label>
              <input type="number" class="form-control" name="add_growth_new_customers" id="add_growth_new_customers" placeholder="{{ __('startup.New customers this period') }}">
            </div>
            <div class="col-12 mb-6">
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
              <textarea class="form-control" name="add_growth_notes" id="add_growth_notes" maxlength="255" placeholder="{{ __('startup.notes') }}" style="height: 130px; resize: none;"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="company_id" id="company_id" value="{{ $company?->id }}" />
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
          <button type="submit" class="btn btn-primary" id="add-new-startup-growth-btn">{{ __('admin.Save') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  $(document).ready(function(){
    let table = $('#growthTable').DataTable({
      pageLength: 10,
      responsive: true,
      ordering: true,
      searching: true,
      columnDefs: [{ orderable: false, targets: -1 }],
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
    // Custom Filters
    $('#filter_year').on('change', function() {
      let val = this.value;
      table.column(0).search(val ? '^\\s*' + $.fn.dataTable.util.escapeRegex(val) + '\\s*$' : '', true, false).draw();
      updateExportUrl();
    });

    $('#filter_segment').on('change', function() {
      let val = this.value;
      table.column(6).search(val ? '^\\s*' + $.fn.dataTable.util.escapeRegex(val) + '\\s*$' : '', true, false).draw();
      updateExportUrl();
    });

    function updateExportUrl() {
      let year = $('#filter_year').val();
      let segment = $('#filter_segment').val();
      let baseUrl = '/dashboard/startupGrowth/export/{{ $company?->id }}';
      let params = [];
      if (year) params.push('year=' + year);
      if (segment) params.push('segment=' + segment.toLowerCase());
      let queryStr = params.length > 0 ? '?' + params.join('&') : '';
      $('#export-csv-btn').attr('href', baseUrl + queryStr);
    }

    $('#add-new-startup-growth-modal-btn').on('click', function() {
      setAddMode();
    });

    $(document).on('click', '.edit-growth-btn', function() {
      const d = $(this).data();
      $('#startupGrowthModalLabel').text("{{ __('startup.edit_customer_record') }}");
      $('#add-startup-growth-form').attr('action', '/dashboard/startupGrowth/update');
      $('#record_id').val(d.id);
      $('#add_growth_year').val(d.year);
      $('#add_growth_month').val(d.month);
      $('#add_growth_total_customers').val(d.total);
      $('#add_growth_new_customers').val(d.new);
      $('#add_growth_segment').val(d.segment);
      $('#add_growth_notes').val(d.notes || '');
    });

    $('#add-startup-growth-form').on('submit', function(e) {
      e.preventDefault();
      $('.is-invalid').removeClass('is-invalid');
      $('.invalid-feedback').remove();
      let isValid = true;
      const totalCustomers = $('#add_growth_total_customers').val();
      if (!totalCustomers || totalCustomers <= 0) {
        showError('#add_growth_total_customers', '{{ __("startup.Total customers is required and must be greater than 0") }}');
        isValid = false;
      } else if (!Number.isInteger(Number(totalCustomers))) {
        showError('#add_growth_total_customers', '{{ __("startup.please_enter_a_valid_whole_number") }}');
        isValid = false;
      }
      const newCustomers = $('#add_growth_new_customers').val();
      if (newCustomers === '' || newCustomers < 0) {
        showError('#add_growth_new_customers', '{{ __("startup.New customers is required and must be 0 or greater") }}');
        isValid = false;
      } else if (!Number.isInteger(Number(newCustomers))) {
        showError('#add_growth_new_customers', '{{ __("startup.please_enter_a_valid_whole_number") }}');
        isValid = false;
      } else if (totalCustomers && parseInt(newCustomers) > parseInt(totalCustomers)) {
        showError('#add_growth_new_customers', '{{ __("startup.New customers cannot exceed total customers") }}');
        isValid = false;
      }
      if (isValid) { this.submit(); }
    });

    function showError(fieldId, message) {
      $(fieldId).addClass('is-invalid');
      $(fieldId).after('<div class="invalid-feedback d-block">' + message + '</div>');
    }
    $('#add_growth_total_customers, #add_growth_new_customers').on('input', function() {
      $(this).removeClass('is-invalid');
      $(this).next('.invalid-feedback').remove();
    });
  });

  function setAddMode() {
    $('#startupGrowthModalLabel').text("{{ __('startup.Add Customer Record') }}");
    $('#add-startup-growth-form').attr('action', '/dashboard/startupGrowth/add');
    $('#record_id').val('');
    $('#add-startup-growth-form')[0].reset();
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
  }

  function deleteRecord(event, button) {
    event.preventDefault();
    if (typeof releaseAlert === 'function') {
      releaseAlert('warning', {
        title: '{{ __("admin.Confirm") }}',
        message: "{{ __('startup.delete_customer_growth_confirm') }}",
        confirmText: '{{ __("admin.Yes") }}',
        cancelText: '{{ __("admin.cancel") }}',
        confirmColor: 'danger',
        onConfirm: function () { $(button).parent().submit(); }
      });
    } else if (confirm("{{ __('startup.delete_customer_growth_confirm') }}")) {
      $(button).parent().submit();
    }
  }
</script>
@endpush
