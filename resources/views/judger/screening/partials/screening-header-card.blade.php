<div class="d-flex align-items-center gap-3 mb-6">
  <a href="javascript:history.back()" class="btn btn-sm btn-icon shadow btn-white rounded-circle">
    <i class="ti ti-chevron-{{ $l == 'en' ? 'left' : 'right' }} ti-md text-dark"></i>
  </a>
  <a class="d-flex align-items-center gap-2 text-capitalize">
    <i class="ti ti-gavel fs-4 text-primary"></i>
    <strong class="text-dark">{{ __('admin.Screening') }}</strong>
  </a>
</div>

<div class="card card-body mb-6">
  <div class="row">
    <div class="col-12 mb-6">
      <div class="row">
        @forEach($statusData as $i => $st)
          <div class="col-6 col-md-3">
            <div class="card px-4 py-3 shadow-sm border" style="height: 76px;">
              <div class="d-flex justify-content-between align-items-center">
                <div class="badge bg-label-secondary">
                  @php($icon = $st->status == 'in-progress' ? 'ti ti-file-text' : ($st->status == 'completed' ? 'ti ti-circle-check' : 'ti ti-gavel'))
                  @if ($i == 0)
                    <img src="/icons/primary-rocket.svg" height="38" width="40" />
                  @else
                    <i class="ti ti-{{$icon}} fs-3 text-primary"></i>
                  @endif
                </div>
                <div class="d-flex flex-column">
                  <h6 class="mb-0 text-capitalize text-secondary">{{ str_replace('-', ' ', $st->status) }}</h6>
                  <span class="fs-5 text-dark fw-bold">{{ $st->count }}</span>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
          <h6 class="mb-0 fw-bold">Filter</h6>
          <div class="px-4 pt-2 bg-label-secondary d-flex align-items-center justify-content-center gap-2 rounded">
            <div class="form-check">
              <label class="form-check-label text-capitalize fs-sm" for="status-all">All</label>
              <input class="form-check-input" type="radio" name="statusFilter" id="status-all" value="all" checked />
            </div>
            @forEach($statuses as $status)
            <div class="form-check">
              <label class="form-check-label text-capitalize fs-sm" for="status-{{ $status }}">{{ str_replace('-', ' ', $status) }}</label>
              <input class="form-check-input" type="radio" name="statusFilter" id="status-{{ $status }}" value="{{ $status }}" />
            </div>
            @endforeach
          </div>
        </div>
        <div class="position-relative w-25">
          <input type="text" class="form-control w-100" placeholder="Search..." id="searchInput">
          <i class="ti ti-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
        </div>
      </div>
    </div>
  </div>
</div>