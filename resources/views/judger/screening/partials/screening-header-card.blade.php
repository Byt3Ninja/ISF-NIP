<div class="card card-body mb-6">
  <div class="row">
    <div class="col-12">
      <div class="row">
        @forEach($statusData as $i => $st)
        <div class="col-6 col-md-3 mb-2 mb-md-0">
          <div class="card px-4 py-3 shadow-sm border" style="height: 76px;">
            <div class="d-flex justify-content-between align-items-center">
              <div class="badge bg-label-secondary me-2 me-md-0">
                @php($icon = $st->status == 'in-progress' ? 'ti ti-file-text' : ($st->status == 'completed' ? 'ti ti-circle-check' : 'ti ti-gavel'))
                @if ($i == 0)
                <img src="/icons/primary-rocket.svg" height="38" width="40" />
                @else
                <i class="ti ti-{{$icon}} fs-3 text-primary"></i>
                @endif
              </div>
              <div class="d-flex flex-column">
                <h6 class="mb-0 text-capitalize text-secondary text-nowrap d-none d-md-inline">
                  {{ __('admin.' . $st->status) }}
                </h6>
                <small class="mb-0 text-capitalize text-secondary text-nowrap d-inline d-md-none">
                  {{ __('admin.' . $st->status) }}
                </small>
                <span class="fs-5 text-dark fw-bold">{{ $st->count }}</span>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
    {{-- <div class="col-12">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
          <h6 class="mb-0 fw-bold">Filter</h6>
          <div
            class="px-8 px-md-4 pt-3 pt-md-2 pb-1 pb-md-0 bg-label-secondary d-flex align-items-center justify-content-between justify-content-md-center gap-2 rounded flex-wrap mb-2 mb-md-0">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="statusFilter" id="status-all" value="all" checked />
              <label class="form-check-label text-capitalize fs-sm" for="status-all">All</label>
            </div>
            @foreach($statuses as $status)
            <div class="form-check">
              <input class="form-check-input" type="radio" name="statusFilter" id="status-{{ $status }}"
                value="{{ $status }}" />
              <label class="form-check-label text-capitalize fs-sm" for="status-{{ $status }}">{{ str_replace('-', ' ',
                $status) }}</label>
            </div>
            @endforeach
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="row d-flex justify-content-end">
            <div class="col-12">
              <div class="position-relative w-100">
                <input type="text" class="form-control w-100" placeholder="Search..." id="searchInput">
                <i class="ti ti-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div> --}}
  </div>
</div>