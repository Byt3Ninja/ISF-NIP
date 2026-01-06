<div class="row text-center mb-6">
  @php
    $colors = ['success', 'danger', 'warning', 'primary'];
    $icons = ['brand-my-oppo', 'book', 'gavel', 'device-watch-check'] 
  @endphp
  @foreach ($programsSummary as $label => $value)
    <div class="col-md-3 mb-3">
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
          <small class="text-center">{{ __('startup.' . $label) }}</small>
        </div>
      </div>
    </div>
  @endforeach
</div>

<div class="card card-datatable table-responsive">
  <table class="datatables-basic table" id="programsTable">
    <thead class="table-light">
      <tr>
        <th>{{ __('startup.program_Name') }}</th>
        <th>{{ __('startup.organization') }}</th>
        <th>{{ __('startup.cohort') }}</th>
        <th>{{ __('startup.start_date') }}</th>
        <th>{{ __('startup.completion_date') }}</th>
        <th>{{ __('startup.final_screening_score') }}</th>
        <th>{{ __('startup.status') }}</th>
        <th>{{ __('startup.actions') }}</th>
      </tr>
    </thead>

    <tbody>
      @foreach ($programs as $program)
      <tr>
        <td>{{ $program->name }}</td>
        <td>{{ $program->organization->name ?? '-' }}</td>
        <td>{{ $program->cohort->name ?? '-' }}</td>
        <td>{{ $program->start_date?->format('Y-m-d') }}</td>
        <td>{{ $program->completion_date?->format('Y-m-d') }}</td>
        <td>{{ $program->final_screening_score ?? '-' }}</td>
        <td>
          <span 
            class="badge 
            @if($program->status === 'completed') bg-label-success
            @elseif($program->status === 'ongoing') bg-label-primary
            @else bg-label-secondary @endif">
            {{ __('startup.' . $program->status) }}
          </span>
        </td>
        <td class="text-center">
          {{-- <a href="{{ route('program.participation', $program->id) }}" class="btn btn-sm btn-outline-primary"> --}}
          <a href="{{ route('company.alumni_view', ['id' => $company->id, 'cycle_id' => $program->id]) }}"
            class="btn btn-sm btn-label-primary text-nowrap">
              <i class="ti ti-eye me-1"></i>
              {{ __('startup.view_details') }}
          </a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

@push('scripts')
<script>
  $(document).ready(function(){
    $('#programsTable').DataTable({
      pageLength: 10,
      responsive: true,
      ordering: true,
      searching: true,
      order: [[3, 'desc']]
      @if (app()->getLocale() == 'ar')
      ,language: {
        search: 'ابحث:',
        lengthMenu: 'أظهر _MENU_ مدخلات',
        info: 'إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل',
        paginate: { previous: 'السابق', next: 'التالي' },
        emptyTable: 'لا توجد بيانات'
      }
      @endif
    });
  });
</script>
@endpush