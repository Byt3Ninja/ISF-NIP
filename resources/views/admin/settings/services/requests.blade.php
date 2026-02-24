@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
<style>
  #historyTabs .nav-item .nav-link { font-size: 12px; }
</style>
@stop

@php
  function badgeClass($status) {
    return match($status) {
      'Completed' => 'bg-label-success',
      'Pending'   => 'bg-label-warning',
      'Rejected'  => 'bg-label-danger',
      'Approved'  => 'bg-label-primary',
      default     => 'bg-warning text-dark',
    };
  }

  $tabs = [
    ['title' => __('admin.All Services'), 'route' => 'services.index', 'icon' => 'arrows-move'],
    ['title' => __('admin.Services Requests'), 'route' => 'service_requests.list_all', 'icon' => 'ti ti-briefcase'],
    // ['title' => __('admin.Services Categories'), 'route' => 'serviceCategories.index', 'icon' => 'layout-grid'],
  ];
  if( $_user->ableTo('serviceCategories.index', $permissions) )
  {
    $tabs[] = ['title' => __('admin.Services Categories'), 'route' => 'serviceCategories.index', 'icon' => 'layout-grid'];
  }
@endphp

@section('content')
@if (!$_user->hasRole('serviceProvider') )
  <x-navigation-pills :tabs="$tabs" />
@endif     

<div>

 {{-- Stats Cards --}}
  <div class="row g-3 mb-6">
    @php
    $config = [
      'total'     => ['label' => 'total_requests',  'icon' => 'ti-clipboard-list', 'color' => 'primary'],
      'pending'   => ['label' => 'Pending',          'icon' => 'ti-clock',          'color' => 'warning'],
      'completed' => ['label' => 'Completed',         'icon' => 'ti-checks',         'color' => 'success'],
      'decision'  => ['label' => 'approved_rejected', 'icon' => 'ti-checks',         'color' => 'info'],
    ];
    @endphp

    @foreach($config as $key => $item)
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body pb-3">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('admin.' . $item['label']) }}</span>

              @if($key === 'decision')
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">
                  <span>{{ $stats['approved'] ?? 0 }}</span>
                  <span class="small text-muted">{{ __('admin.Approved') }}</span>
                  /
                  <span>{{ $stats['rejected'] ?? 0 }}</span>
                  <span class="small text-muted">{{ __('admin.Rejected') }}</span>
                </h4>
              </div>
              @else
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats[$key] ?? 0 }}</h4>
              </div>
              @endif
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-{{ $item['color'] }}">
                <i class="ti {{ $item['icon'] }} ti-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{--  Navigation Status Pills --}}
  <nav class="nav-align-top">
    <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-2 gap-lg-0" role="tablist">

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == null ? 'active' : '' }}" href="{{ route('service_requests.list_all') }}">
          {{ __('admin.all_requests') }}
          <span class="badge badge-center rounded-pill bg-danger bg-glow ms-2">
            {{ $stats['total'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'pending' ? 'active' : '' }}"
          href="{{ route('service_requests.list_all', ['request_status' => 'pending']) }}">
          {{ __('admin.pending') }}
          <span class="badge badge-center rounded-pill bg-danger ms-2">
            {{ $stats['pending'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'completed' ? 'active' : '' }}"
          href="{{ route('service_requests.list_all', ['request_status' => 'completed']) }}">
          {{ __('admin.completed') }}
          <span class="badge badge-center rounded-pill bg-danger ms-2">
            {{ $stats['completed'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'approved' ? 'active' : '' }}"
          href="{{ route('service_requests.list_all', ['request_status' => 'approved']) }}">
          {{ __('admin.Approved') }}
          <span class="badge badge-center rounded-pill bg-danger ms-2">
            {{ $stats['approved'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'rejected' ? 'active' : '' }}"
          href="{{ route('service_requests.list_all', ['request_status' => 'rejected']) }}">
          {{ __('admin.Rejected') }}
          <span class="badge badge-center rounded-pill bg-danger ms-2">
            {{ $stats['rejected'] ?? 0 }}
          </span>
        </a>
      </li>
    </ul>
  </nav>

  {{-- Requests Grid --}}
  <div class="row g-4">
    @forelse($requests as $request)
    <div class="col-md-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body d-flex flex-column">

          {{-- Header --}}
          <div class="d-flex justify-content-between align-items-start mb-4">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar avatar-md rounded">
                <img src="{{ $request['image_url'] }}" alt="Avatar" class="rounded object-fit-cover border">
              </div>
              <div>
                <h6 class="mb-0">{{ $request['title'] }}</h6>
                <small class="text-muted">{{ $request['date'] }}</small>
              </div>
            </div>
            <span class="badge {{ badgeClass($request['status']) }}">
              {{ __('admin.' . $request['status']) }}
            </span>
          </div>

          {{-- Description --}}
          <p class="text-muted small flex-grow-1">{{ $request['description'] }}</p>

          {{-- Price --}}
          @if($request['price'])
          <div class="mb-3">
            <span class="fw-semibold text-dark">{{ $request['price'] }} {{ $request['currency'] }}</span>
          </div>
          @endif

          {{-- Startup & Founder --}}
          <div class="d-flex flex-column gap-1 mb-3">
            <div class="small">
              <span class="text-muted">{{ __('admin.StartUp_name') }}:</span>
              <a href="{{ $request['startup_url'] ?? '#' }}" class="fw-semibold text-dark text-decoration-none">{{ $request['startup_name'] }}</a>
            </div>
            <div class="small">
              <span class="text-muted">{{ __('admin.founder_name') }}:</span>
              <a href="{{ $request['founder_url'] ?? '#' }}" class="fw-semibold text-dark text-decoration-none">{{ $request['founder_name'] }}</a>
            </div>
          </div>

          {{-- Service Provider --}}
          <a href="{{ $request['service_provider']['profile_url'] ?? '#' }}" class="d-flex align-items-center mb-4 text-decoration-none">
            <img src="{{ $request['service_provider']['image_url'] }}" class="rounded-circle me-2"
              width="40" height="40" alt="provider">
            <div class="fw-semibold text-dark">{{ $request['service_provider']['name'] }}</div>
          </a>

          {{-- Actions --}}
          <div class="d-flex gap-2 mt-auto">
            @if($request['status'] === 'Pending')
            <button class="btn btn-primary w-100"
              data-bs-toggle="offcanvas" data-bs-target="#offcanvasRespond"
              data-request-id="{{ $request['id'] }}"
              data-request-title="{{ $request['title'] }}"
              data-startup-name="{{ $request['startup_name'] ?? 'N/A' }}"
              data-founder-name="{{ $request['founder_name'] ?? 'N/A' }}"
              data-provider-name="{{ $request['service_provider']['name'] }}">
              <i class="ti ti-mail-fast me-1"></i> {{ __('admin.respond_to_request') }}
            </button>

            @elseif($request['status'] === 'Completed')
              <button class="btn btn-primary w-100"
                data-bs-toggle="offcanvas" data-bs-target="#offcanvasHistory"
                data-request-id="{{ $request['id'] }}"
                data-request-title="{{ $request['title'] }}"
                data-provider-name="{{ $request['service_provider']['name'] }}"
                data-provider-img="{{ $request['service_provider']['image_url'] }}"
                data-status="{{ $request['status'] }}"
                data-review-rating="{{ $request['rating'] ?? '' }}"
                data-review-title="{{ $request['review_title'] ?? '' }}"
                data-review-body="{{ $request['review_body'] ?? '' }}"
                data-reviewed-at="{{ $request['reviewed_at'] ?? '' }}"
                data-has-review="{{ $request['has_review'] ? '1' : '0' }}"
                data-founder-name="{{ $request['founder_name'] ?? '' }}"
                data-founder-img="{{ $request['founder_img'] ?? '' }}">
                <i class="ti ti-history me-1"></i> {{ __('admin.view_history') }}
              </button>

            @else
              <button class="btn btn-primary w-100"
                data-bs-toggle="offcanvas" data-bs-target="#offcanvasMessage"
                data-request-id="{{ $request['id'] }}"
                data-request-title="{{ $request['title'] }}"
                data-provider-name="{{ $request['service_provider']['name'] }}"
                data-provider-img="{{ $request['service_provider']['image_url'] }}">
                <i class="ti ti-send me-1"></i> {{ __('admin.Send_Message') }}
              </button>
            @endif

            <button class="btn btn-outline-primary"
              data-bs-toggle="offcanvas" data-bs-target="#offcanvasHistory"
              data-request-id="{{ $request['id'] }}"
              data-request-title="{{ $request['title'] }}"
              data-provider-name="{{ $request['service_provider']['name'] }}"
              data-provider-img="{{ $request['service_provider']['image_url'] }}"
              data-status="{{ $request['status'] }}"
              data-review-rating="{{ $request['rating'] ?? '' }}"
              data-review-title="{{ $request['review_title'] ?? '' }}"
              data-review-body="{{ $request['review_body'] ?? '' }}"
              data-reviewed-at="{{ $request['reviewed_at'] ?? '' }}"
              data-has-review="{{ $request['has_review'] ? '1' : '0' }}"
              data-founder-name="{{ $request['founder_name'] ?? '' }}"
              data-founder-img="{{ $request['founder_img'] ?? '' }}">
              <i class="ti ti-history"></i>
            </button>
          </div>

        </div>
      </div>
    </div>
    @empty
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body text-center py-5">
          <i class="ti ti-clipboard-list ti-48px text-muted mb-3 d-block"></i>
          <p class="text-muted mb-0">{{ __('admin.no_service_requests_yet') }}</p>
        </div>
      </div>
    </div>
    @endforelse
  </div>

  {{-- Pagination --}}
  @if($requests->hasPages())
  <div class="d-flex justify-content-end mt-4">
    {{ $requests->links('pagination::bootstrap-5') }}
  </div>
  @endif
</div>

{{-- ═══════════════════════════════════════════
     OFFCANVAS — Respond to Request
═══════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRespond" aria-labelledby="offcanvasRespondLabel">
  <div class="offcanvas-header border-bottom">
    <div class="d-flex align-items-center gap-2">
      <div class="avatar avatar-sm">
        <div class="avatar-initial rounded-circle bg-label-primary">
          <i class="ti ti-mail-opened ti-sm"></i>
        </div>
      </div>
      <h5 class="offcanvas-title mb-0" id="offcanvasRespondLabel">{{ __('admin.respond_to_request') }}</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body">
    {{-- Request Summary --}}
    <div class="mb-6 p-3 bg-light-subtle rounded border">
      <h6 class="mb-3 text-uppercase small fw-bold text-dark">{{ __('admin.request_summary') }}</h6>
      <div class="d-flex flex-column gap-2">
        <div class="d-flex justify-content-between">
          <span class="text-secondary small">{{ __('admin.Service_Name') }}:</span>
          <span class="fw-semibold small" id="summaryServiceName">—</span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-secondary small">{{ __('admin.StartUp_name') }}:</span>
          <span class="fw-semibold small" id="summaryStartupName">—</span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-secondary small">{{ __('admin.founder_name') }}:</span>
          <span class="fw-semibold small" id="summaryFounderName">—</span>
        </div>
      </div>
    </div>

    <form id="formRespond" novalidate>
      <input type="hidden" name="request_id" id="respondRequestId">

      {{-- Decision --}}
      <div class="mb-4">
        <label class="form-label fw-bold">{{ __('admin.response_decision') }} <span class="text-danger">*</span></label>
        <div class="row g-2">
          <div class="col-6">
            <input type="radio" class="btn-check" name="decision" id="decisionApprove" value="approve">
            <label class="btn btn-outline-success w-100" for="decisionApprove">
              <i class="ti ti-check me-1"></i> {{ __('admin.approve') }}
            </label>
          </div>
          <div class="col-6">
            <input type="radio" class="btn-check" name="decision" id="decisionReject" value="reject">
            <label class="btn btn-outline-danger w-100" for="decisionReject">
              <i class="ti ti-x me-1"></i> {{ __('admin.reject') }}
            </label>
          </div>
        </div>
        <div id="decisionError" class="fv-plugins-message-container invalid-feedback"></div>
      </div>

      {{-- Message --}}
      <div class="mb-4">
        <label for="respondMessage" class="form-label fw-bold">
          {{ __('admin.response_message') }} 
          <span id="messageRequiredStar" class="text-danger d-none">*</span>
        </label>
        <textarea class="form-control" id="respondMessage" name="message" rows="5" 
          placeholder="{{ __('admin.respond_message_placeholder') }}" maxlength="500"></textarea>
        <div class="d-flex justify-content-between mt-1">
          <small class="text-muted"><span id="respondCharCount">0</span>/500</small>
        </div>
      </div>

      <div class="d-flex gap-3 mt-4">
        <button type="submit" class="btn btn-primary flex-fill">
          <i class="ti ti-send me-1"></i> {{ __('admin.send_response') }}
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
          {{ __('admin.Cancel') }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ═══════════════════════════════════════════
     OFFCANVAS 2 — Send Message
═══════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasMessage" aria-labelledby="offcanvasMessageLabel">
  <div class="offcanvas-header border-bottom">
    <div class="d-flex align-items-center gap-2">
      <div class="avatar avatar-sm">
        <div class="avatar-initial rounded-circle bg-label-primary">
          <i class="ti ti-send ti-sm"></i>
        </div>
      </div>
      <h5 class="offcanvas-title mb-0" id="offcanvasMessageLabel">{{ __('admin.Send_Message') }}</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body d-flex flex-column">

    {{-- Provider info --}}
    <div class="d-flex align-items-center gap-3 p-3 bg-light-subtle border rounded mb-4">
      <img src="" alt="Provider" class="rounded-circle msg-provider-img" width="48" height="48">
      <div>
        <div class="fw-semibold msg-provider-name">—</div>
        <small class="text-muted msg-request-title">—</small>
      </div>
    </div>

    {{-- Simulated thread --}}
    <div class="border rounded p-3 mb-4 overflow-auto" style="max-height:200px" id="msgThread">
      <div class="d-flex flex-column gap-3">
        <div>
          <div class="d-flex justify-content-start">
            <div class="bg-label-secondary border rounded-3 px-3 py-2 small" style="max-width:75%">
              Hello! Looking forward to working on your project.
            </div>
          </div>
          <small class="text-muted ps-1">Tech Solutions Inc · Jan 15, 09:35</small>
        </div>
        <div>
          <div class="d-flex justify-content-end">
            <div class="bg-primary text-white rounded-3 px-3 py-2 small" style="max-width:75%">
              Great, please let us know the timeline.
            </div>
          </div>
          <div class="text-end"><small class="text-muted pe-1">You · Jan 15, 09:38</small></div>
        </div>
      </div>
    </div>

    {{-- Compose form --}}
    <form id="formMessage" novalidate>
      <input type="hidden" name="request_id" id="msgRequestId">

      <div class="mb-3">
        <label for="msgBody" class="form-label fw-semibold">
          {{ __('admin.message') }} <span class="text-danger">*</span>
        </label>
        <textarea class="form-control" id="msgBody" name="message" rows="4"
          placeholder="{{ __('admin.message_body_placeholder') }}" maxlength="1000"></textarea>
        <div class="d-flex justify-content-end mt-1">
          <small class="text-muted"><span id="msgBodyCount">0</span>/1000</small>
        </div>
      </div>

      <div class="mb-4">
        <label for="msgAttachment" class="form-label fw-semibold">
          {{ __('admin.attachment') }}
          <span class="text-muted fw-normal small">({{ __('admin.optional') }})</span>
        </label>
        <input type="file" class="form-control" id="msgAttachment" name="attachment"
          accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
        <div class="form-text">Max 10 MB — PDF, DOC, PNG, JPG</div>
      </div>

      <div class="d-flex gap-3">
        <button type="submit" class="btn btn-primary flex-fill">
          <i class="ti ti-send me-1"></i> {{ __('admin.Send_Message') }}
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
          {{ __('admin.Cancel') }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ═══════════════════════════════════════════
     OFFCANVAS 3 — View History (3 tabs)
═══════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasHistory" aria-labelledby="offcanvasHistoryLabel">
  <div class="offcanvas-header border-bottom">
    <div class="d-flex align-items-center gap-2">
      <div class="avatar avatar-sm">
        <div class="avatar-initial rounded-circle bg-label-info">
          <i class="ti ti-history ti-sm"></i>
        </div>
      </div>
      <h5 class="offcanvas-title mb-0" id="offcanvasHistoryLabel">{{ __('admin.view_history') }}</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body p-0 d-flex flex-column">

    {{-- Provider strip --}}
    <div class="d-flex align-items-center gap-3 p-3 border-bottom">
      <img src="" alt="Provider" class="rounded-circle hist-provider-img" width="44" height="44">
      <div>
        <div class="fw-semibold hist-provider-name">—</div>
        <small class="text-muted hist-request-title">—</small>
      </div>
      <span class="badge ms-auto hist-status-badge">—</span>
    </div>

    {{-- Tabs nav --}}
    <ul class="nav nav-tabs px-3 pt-2 border-bottom flex-shrink-0" id="historyTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active px-2" id="tab-timeline" data-bs-toggle="tab"
          data-bs-target="#tabpane-timeline" type="button" role="tab">
          <i class="ti ti-timeline me-1"></i>{{ __('admin.status_timeline') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link position-relative px-2" id="tab-messages" data-bs-toggle="tab"
          data-bs-target="#tabpane-messages" type="button" role="tab">
          <i class="ti ti-message me-1"></i>{{ __('admin.Messages') }}
          <small 
            class="position-absolute rounded-circle bg-danger text-white w-px-20 h-px-20 d-flex flex-shrink-0 align-items-center justify-content-center"
            style="top: 0; right: 0;"
          >
            4
          </small>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link px-2" id="tab-reviews" data-bs-toggle="tab"
          data-bs-target="#tabpane-reviews" type="button" role="tab">
          <i class="ti ti-star me-1"></i>{{ __('admin.reviews') }}
        </button>
      </li>
    </ul>

    <div class="tab-content flex-grow-1 overflow-auto p-3" id="historyTabsContent">

      {{-- ── TAB 1 · Status Timeline ── --}}
      <div class="tab-pane fade show active" id="tabpane-timeline" role="tabpanel">
        <ul class="list-unstyled mt-2 mb-0">

          <li class="d-flex gap-3 pb-4">
            <div class="d-flex flex-column align-items-center flex-shrink-0">
              <span class="badge rounded-pill bg-success p-2"><i class="ti ti-check"></i></span>
              <div class="border-start border-2 border-success opacity-25 flex-grow-1 mt-1"></div>
            </div>
            <div class="d-flex justify-content-between w-100 pt-1">
              <div>
                <div class="fw-semibold text-success small">Request Submitted</div>
                <small class="text-muted">Client submitted the service request.</small>
              </div>
              <small class="text-muted text-nowrap ms-3">Jan 15, 09:00</small>
            </div>
          </li>

          <li class="d-flex gap-3 pb-4">
            <div class="d-flex flex-column align-items-center flex-shrink-0">
              <span class="badge rounded-pill bg-primary p-2"><i class="ti ti-thumb-up"></i></span>
              <div class="border-start border-2 border-primary opacity-25 flex-grow-1 mt-1"></div>
            </div>
            <div class="d-flex justify-content-between w-100 pt-1">
              <div>
                <div class="fw-semibold text-primary small">Approved</div>
                <small class="text-muted">Request reviewed and approved by admin.</small>
              </div>
              <small class="text-muted text-nowrap ms-3">Jan 16, 10:15</small>
            </div>
          </li>

          <li class="d-flex gap-3 pb-4">
            <div class="d-flex flex-column align-items-center flex-shrink-0">
              <span class="badge rounded-pill bg-info p-2"><i class="ti ti-loader"></i></span>
              <div class="border-start border-2 border-info opacity-25 flex-grow-1 mt-1"></div>
            </div>
            <div class="d-flex justify-content-between w-100 pt-1">
              <div>
                <div class="fw-semibold text-info small">In Progress</div>
                <small class="text-muted">Service provider started working on the project.</small>
              </div>
              <small class="text-muted text-nowrap ms-3">Jan 17, 08:30</small>
            </div>
          </li>

          <li class="d-flex gap-3 pb-4">
            <div class="d-flex flex-column align-items-center flex-shrink-0">
              <span class="badge rounded-pill bg-warning p-2"><i class="ti ti-edit"></i></span>
              <div class="border-start border-2 border-warning opacity-25 flex-grow-1 mt-1"></div>
            </div>
            <div class="d-flex justify-content-between w-100 pt-1">
              <div>
                <div class="fw-semibold text-warning small">Revision Requested</div>
                <small class="text-muted">Client requested adjustments to the deliverable.</small>
              </div>
              <small class="text-muted text-nowrap ms-3">Jan 19, 13:00</small>
            </div>
          </li>

          <li class="d-flex gap-3">
            <div class="d-flex flex-column align-items-center flex-shrink-0">
              <span class="badge rounded-pill bg-danger p-2"><i class="ti ti-x"></i></span>
            </div>
            <div class="d-flex justify-content-between w-100 pt-1">
              <div>
                <div class="fw-semibold text-danger small hist-final-status">Rejected</div>
                <small class="text-muted hist-final-note">Request was closed after revision deadline passed.</small>
              </div>
              <small class="text-muted text-nowrap ms-3">Jan 20, 17:00</small>
            </div>
          </li>

        </ul>
      </div>

      {{-- ── TAB 2 · Messages ── --}}
      <div class="tab-pane fade" id="tabpane-messages" role="tabpanel">
        <div class="d-flex flex-column gap-3 pt-2" id="histMsgThread">

          <div>
            <div class="d-flex justify-content-start">
              <div class="bg-light border rounded-3 px-3 py-2 small" style="max-width:75%">
                Hello! Looking forward to working on your project.
              </div>
            </div>
            <small class="text-muted ps-1">Provider · Jan 15, 09:35</small>
          </div>

          <div>
            <div class="d-flex justify-content-end">
              <div class="bg-primary text-white rounded-3 px-3 py-2 small" style="max-width:75%">
                Great, please share a draft timeline.
              </div>
            </div>
            <div class="text-end"><small class="text-muted pe-1">You · Jan 15, 09:38</small></div>
          </div>

          <div>
            <div class="d-flex justify-content-start">
              <div class="bg-light border rounded-3 px-3 py-2 small" style="max-width:75%">
                Sure! Here's a rough breakdown for the first 2 weeks.
              </div>
            </div>
            <small class="text-muted ps-1">Provider · Jan 16, 08:10</small>
          </div>

          <div>
            <div class="d-flex justify-content-end">
              <div class="bg-primary text-white rounded-3 px-3 py-2 small" style="max-width:75%">
                Please note we need the mobile layout to be responsive from day one.
              </div>
            </div>
            <div class="text-end"><small class="text-muted pe-1">You · Jan 19, 12:50</small></div>
          </div>

          <div>
            <div class="d-flex justify-content-start">
              <div class="bg-light border rounded-3 px-3 py-2 small" style="max-width:75%">
                Understood, however the deadline makes that difficult at this stage.
              </div>
            </div>
            <small class="text-muted ps-1">Provider · Jan 19, 14:00</small>
          </div>

        </div>

        {{-- Quick reply --}}
        <div class="border-top mt-4 pt-3">
          <form id="formHistoryReply" novalidate>
            <input type="hidden" name="request_id" id="histReplyRequestId">
            <div class="mb-2">
              <textarea class="form-control" id="histReplyBody" name="reply" rows="3"
                placeholder="{{ __('admin.reply_placeholder') }}" maxlength="500"></textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <small class="text-muted"><span id="histReplyCount">0</span>/500</small>
              <button type="submit" class="btn btn-sm btn-primary">
                <i class="ti ti-send me-1"></i>{{ __('admin.reply') }}
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- ── TAB 3 · Reviews ── --}}
      <div class="tab-pane fade" id="tabpane-reviews" role="tabpanel">
        <div id="reviewTabContent" class="pt-2"></div>
      </div>

    </div>{{-- /tab-content --}}
  </div>{{-- /offcanvas-body --}}
</div>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>

<script>
const statuses = {
  Completed : '{{ __('admin.completed') }}',
  Pending   : '{{ __('admin.pending') }}',
  Rejected  : '{{ __('admin.Rejected') }}',
  Approved  : '{{ __('admin.Approved') }}',
};

document.addEventListener('DOMContentLoaded', function () {

  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const FV = FormValidation;

  /* ── Char counter ── */
  function charCounter(textarea, counterId) {
    textarea.addEventListener('input', () =>
      document.getElementById(counterId).textContent = textarea.value.length
    );
  }

  /* ── Badge class (mirrors PHP helper) ── */
  function badgeClass(status) {
    const map = {
      Completed : 'bg-label-success',
      Pending   : 'bg-label-warning',
      Rejected  : 'bg-label-danger',
      Approved  : 'bg-label-primary',
    };
    return map[status] ?? 'bg-warning text-dark';
  }

  /* ── XSS escape ── */
  function esc(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
              .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  }

  const userRole = '{{ $_user->getRoleNames()->first() }}';
  const emptyReviewMsg = userRole === 'serviceProvider'
    ? '{{ __('admin.no_review_submitted_yet') }}'
    : '{{ __('admin.no_review_available_yet') }}';

  /* ── Render star icons ── */
  function renderStarIcons(rating) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
      html += i <= rating
        ? '<i class="ti ti-star-filled"></i>'
        : '<i class="ti ti-star"></i>';
    }
    return html;
  }

  /* ── Render Review Tab content ── */
  function renderReviewTab(btn) {
    const container = document.getElementById('reviewTabContent');
    const hasReview = btn.dataset.hasReview === '1';

    if (!hasReview) {
      container.innerHTML = `
        <div class="text-center py-5">
          <i class="ti ti-star-off ti-48px text-muted mb-3 d-block"></i>
          <p class="text-muted mb-0">${esc(emptyReviewMsg)}</p>
        </div>`;
      return;
    }

    const rating      = parseInt(btn.dataset.reviewRating) || 0;
    const title       = btn.dataset.reviewTitle || '';
    const body        = btn.dataset.reviewBody || '';
    const reviewedAt  = btn.dataset.reviewedAt || '';
    const founderName = btn.dataset.founderName || '—';
    const founderImg  = btn.dataset.founderImg || '/images/deck-video.svg';

    container.innerHTML = `
      <div class="d-flex flex-column gap-3">
        <div class="border rounded p-3 bg-light-subtle">
          <div class="d-flex align-items-center gap-2 mb-2">
            <img src="${esc(founderImg)}" class="rounded-circle" width="32" height="32" alt="reviewer">
            <div>
              <div class="fw-semibold small">${esc(founderName)}</div>
              <div class="text-warning small">${renderStarIcons(rating)}</div>
            </div>
            <small class="text-muted ms-auto">${esc(reviewedAt)}</small>
          </div>
          ${title ? `<p class="small fw-semibold mb-1">${esc(title)}</p>` : ''}
          ${body ? `<p class="small text-muted mb-0">${esc(body)}</p>` : ''}
        </div>
      </div>`;
  }

  /* ══════════════════════════════════════
   OFFCANVAS — Respond Logic
══════════════════════════════════════ */
const ocRespond = document.getElementById('offcanvasRespond');
const formRespond = document.getElementById('formRespond');

if (ocRespond) {
  ocRespond.addEventListener('show.bs.offcanvas', e => {
    const btn = e.relatedTarget;
    $('#summaryServiceName').textContent = btn.dataset.requestTitle;
    $('#summaryStartupName').textContent = btn.dataset.startupName;
    $('#summaryFounderName').textContent = btn.dataset.founderName;
    $('#respondRequestId').value = btn.dataset.requestId;
    
    formRespond.reset();
    $('#respondCharCount').textContent = '0';
    $('#messageRequiredStar').classList.add('d-none');
    fvRespond.resetForm(true);
  });

  // Toggle visual "required" star based on decision
  formRespond.querySelectorAll('input[name="decision"]').forEach(radio => {
    radio.addEventListener('change', (e) => {
      if (e.target.value === 'reject') {
        $('#messageRequiredStar').classList.remove('d-none');
      } else {
        $('#messageRequiredStar').classList.add('d-none');
      }
      fvRespond.revalidateField('message');
    });
  });

  charCounter($('#respondMessage'), 'respondCharCount');

  const fvRespond = FormValidation.formValidation(formRespond, {
    fields: {
      decision: {
        validators: {
          notEmpty: { 
            message: '{{ app()->getLocale() == "ar" ? "يرجى اختيار قرار الرد" : "Please select a response decision." }}' 
          }
        }
      },
      message: {
        validators: {
          callback: {
            message: '{{ app()->getLocale() == "ar" ? "يرجى إضافة رسالة لتوضيح سبب الرفض" : "Please add a message to explain the rejection." }}',
            callback: function(input) {
              const decision = formRespond.querySelector('input[name="decision"]:checked')?.value;
              // If reject, message is mandatory
              if (decision === 'reject' && input.value.trim() === '') {
                return false;
              }
              return true;
            }
          },
          stringLength: {
            max: 500,
            message: '{{ __('admin.reply_message_max_length') }}'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({ 
        rowSelector: '.mb-4',
        eleInvalidClass: 'is-invalid',
        eleValidClass: ''
      }),
      autoFocus: new FormValidation.plugins.AutoFocus(),
      submitButton: new FormValidation.plugins.SubmitButton(),
    }
  }).on('core.form.valid', () => {
    const decision  = formRespond.querySelector('input[name="decision"]:checked').value;
    const requestId = $('#respondRequestId').value;
    const message   = $('#respondMessage').value;

    fetch('{{ route("service_requests.take_action") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        id: requestId,
        stat: decision,
        comment: message || null,
      }),
    })
    .then(res => res.json())
    .then(data => {
      if (data.status) {
        showToast({ type: 'success', message: data.message });
        bootstrap.Offcanvas.getInstance(ocRespond).hide();
        window.location.reload();
      } else {
        showToast({ type: 'error', message: data.message || '{{ __("admin.unexpected_error") }}' });
      }
    })
    .catch(() => {
      showToast({ type: 'error', message: '{{ __("admin.unexpected_error") }}' });
    });
  });
}
  /* ══════════════════════════════════════
     OFFCANVAS 2 — Send Message
  ══════════════════════════════════════ */
  const ocMessage = document.getElementById('offcanvasMessage');

  ocMessage.addEventListener('show.bs.offcanvas', e => {
    const btn = e.relatedTarget;
    if (!btn) return;
    $('.msg-provider-img').src          = btn.dataset.providerImg  ?? '';
    $('.msg-provider-name').textContent = btn.dataset.providerName ?? '—';
    $('.msg-request-title').textContent = btn.dataset.requestTitle ?? '—';
    $('#msgRequestId').value            = btn.dataset.requestId    ?? '';
    formMessage.reset();
    $('#msgBodyCount').textContent = '0';
    fvMessage && fvMessage.resetForm(true);
  });

  charCounter($('#msgBody'), 'msgBodyCount');

  const formMessage = $('#formMessage');
  const fvMessage = FV.formValidation(formMessage, {
    fields: {
      message: {
        validators: {
          notEmpty:     { message: '{{ __('admin.message_required') }}' },
          stringLength: { min: 10, max: 1000, message: '{{ __('admin.message_body_length') }}' },
        },
      },
      attachment: {
        validators: {
          file: {
            extension : 'pdf,doc,docx,png,jpg,jpeg,webp,svg,xls,xlsx,ppt,pptx',
            type      : 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/png,image/jpeg',
            maxSize   : 10 * 1024 * 1024, // 10 MB
            message   : '{{ __('admin.message_attachment_validation') }}',
          },
        },
      },
    },
    plugins: {
      trigger:      new FV.plugins.Trigger(),
      bootstrap5:   new FV.plugins.Bootstrap5({ rowSelector: '.mb-3, .mb-4' }),
      autoFocus:    new FV.plugins.AutoFocus(),
      submitButton: new FV.plugins.SubmitButton(),
    },
  }).on('core.form.valid', () => {
    showToast({ type: 'success', message: '{{ __('admin.MessageSent') }}' });
    bootstrap.Offcanvas.getInstance(ocMessage).hide();
  });

  /* ══════════════════════════════════════
     OFFCANVAS 3 — View History
  ══════════════════════════════════════ */
  const ocHistory = document.getElementById('offcanvasHistory');

  ocHistory.addEventListener('show.bs.offcanvas', e => {
    const btn = e.relatedTarget;
    if (!btn) return;
    $('.hist-provider-img').src          = btn.dataset.providerImg  ?? '';
    $('.hist-provider-name').textContent = btn.dataset.providerName ?? '—';
    $('.hist-request-title').textContent = btn.dataset.requestTitle ?? '—';
    $('#histReplyRequestId').value       = btn.dataset.requestId    ?? '';

    const status      = btn.dataset.status ?? '';
    const statusBadge = $('.hist-status-badge');
    statusBadge.textContent = statuses[status] ?? '—';
    statusBadge.className   = `badge ms-auto hist-status-badge ${badgeClass(status)}`;

    renderReviewTab(btn);

    bootstrap.Tab.getOrCreateInstance($('#tab-timeline')).show();
    formHistoryReply.reset();
    $('#histReplyCount').textContent = '0';
    fvHistoryReply && fvHistoryReply.resetForm(true);
  });

  charCounter($('#histReplyBody'), 'histReplyCount');

  const formHistoryReply = $('#formHistoryReply');
  const fvHistoryReply = FV.formValidation(formHistoryReply, {
    fields: {
      reply: {
        validators: {
          notEmpty:     { message: 'Reply cannot be empty.' },
          stringLength: { min: 2, max: 500, message: 'Reply must be 2–500 characters.' },
        },
      },
    },
    plugins: {
      trigger:      new FV.plugins.Trigger(),
      bootstrap5:   new FV.plugins.Bootstrap5({ rowSelector: '.mb-2' }),
      autoFocus:    new FV.plugins.AutoFocus(),
      submitButton: new FV.plugins.SubmitButton(),
    },
  }).on('core.form.valid', () => {
    const now     = new Date().toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
      <div>
        <div class="d-flex justify-content-end">
          <div class="bg-primary text-white rounded-3 px-3 py-2 small" style="max-width:75%">
            ${esc($('#histReplyBody').value)}
          </div>
        </div>
        <div class="text-end"><small class="text-muted pe-1">You · ${now}</small></div>
      </div>`;
    $('#histMsgThread').appendChild(wrapper);
    formHistoryReply.reset();
    $('#histReplyCount').textContent = '0';
    fvHistoryReply.resetForm(true);
    showToast({ type: 'success', message: '{{ __('admin.reply_sent_successfully') }}' });
  });

  /* ══════════════════════════════════════
     Auto-open panel from notification URL
  ══════════════════════════════════════ */
  const urlParams = new URLSearchParams(window.location.search);
  const openRequest = urlParams.get('open_request');
  const activeTab   = urlParams.get('tab');

  if (openRequest) {
    const btn = document.querySelector(`[data-bs-target="#offcanvasHistory"][data-request-id="${openRequest}"]`);
    if (btn) {
      btn.click();
      if (activeTab === 'review') {
        ocHistory.addEventListener('shown.bs.offcanvas', function onceShown() {
          bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-reviews')).show();
          ocHistory.removeEventListener('shown.bs.offcanvas', onceShown);
        });
      }
    }
  }

});
</script>
@stop

