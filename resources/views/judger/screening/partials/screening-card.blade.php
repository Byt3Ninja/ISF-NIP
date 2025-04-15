<div class="card screening-card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <img 
        src="/images/background/logo.png" 
        alt="startup-logo" 
        class="rounded shadow-sm border p-2"
        style="object-fit: contain; width: 100px; height: 65px;" 
      />
      <div class="d-flex flex-column align-items-center justify-content-center gap-1">
        <img src="/icons/{{ $screening->status }}.svg" />
        <span class="text-capitalize fs-sm fs-semibold status" data-id="{{ $screening->status }}">
          {{ str_replace('-', ' ', $screening->status) }}
        </span>
      </div>
    </div>
    <h5 class="card-title text-truncate mb-2 text-dark">{{ $screening->startupTitle }}</h5>
    <p class="card-text line-clamp-4 text-muted">
      Lorem ipsum dolor sit amet consectetur adipisicing elit.
      Dolorum, itaque nam? Sint dolore, reiciendis soluta eveniet impedit 
      consectetur rerum nam quidem delectus amet nostrum repudiandae, 
      quos aspernatur dolor reprehenderit nesciunt.
    </p>
  </div>
  <div class="card-overlay">
    <div class="position-relative w-100 h-100">
      <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
        <h6 class="text-center text dark fw-bold mb-3">Screening For a judge,</h6>
        <img 
          src="/icons/{{ $screening->status }}.svg" 
          class="mx-auto mb-3 w-px-75 h-px-75" 
        />
        <p class="text-muted line-clamp-2 mb-3">
          Lorem ipsum dolor sit amet consectetur adipisicing elit. 
          A asperiores ipsam autem assumenda. Nobis sunt consectetur aperiam 
          dignissimos eveniet alias nostrum cumque,
          quis eius aspernatur accusantium temporibus aliquid expedita labore.
        </p>
        @php($startupId = '44')
        @php($status = $screening->status)
        <button class="btn btn-outline-primary">
          <a 
            href="/dashboard/companyProfile/{{ $startupId }}" 
            class="text-decoration-none text-primary fw-bold">
            {{ $status == 'completed' ? 'View' : ($status == 'in-progress' ? 'Complete' : 'Start') }} Screening
            <i class="ti ti-{{ $status == 'completed' ? 'eye' : 'gavel' }} ms-1"></i>
          </a>
        </button>
      </div>
      <img
        src="/icons/{{ $status }}-l-c.svg"
        alt="completed-l-c"
        class="position-absolute {{ $status == 'in-progress' ? 'top' : 'bottom' }}-0 start-0"
      />
      <img
        src="/icons/{{ $status }}-r-c.svg"
        alt="completed-r-c"
        class="position-absolute {{ $status == 'in-progress' ? 'bottom' : 'top' }}-0 end-0"
      />
    </div>
  </div>
</div>