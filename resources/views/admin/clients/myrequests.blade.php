@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
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
@endphp

@section('content')
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
        <a class="nav-link {{ request('request_status') == null ? 'active' : '' }}" href="{{ route('service_requests.list_mine') }}">
          {{ __('admin.all_requests') }}
          <span class="badge badge-center rounded-pill bg-danger bg-glow ms-2">
            {{ $stats['total'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'pending' ? 'active' : '' }}"
          href="{{ route('service_requests.list_mine', ['request_status' => 'pending']) }}">
          {{ __('admin.pending') }}
          <span class="badge badge-center rounded-pill bg-danger ms-2">
            {{ $stats['pending'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'completed' ? 'active' : '' }}"
          href="{{ route('service_requests.list_mine', ['request_status' => 'completed']) }}">
          {{ __('admin.completed') }}
          <span class="badge badge-center rounded-pill bg-danger ms-2">
            {{ $stats['completed'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'approved' ? 'active' : '' }}"
          href="{{ route('service_requests.list_mine', ['request_status' => 'approved']) }}">
          {{ __('admin.Approved') }}
          <span class="badge badge-center rounded-pill bg-danger ms-2">
            {{ $stats['approved'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'rejected' ? 'active' : '' }}"
          href="{{ route('service_requests.list_mine', ['request_status' => 'rejected']) }}">
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
    @foreach($requests as $request)
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

          {{-- Service Provider --}}
          <a href="#" class="d-flex align-items-center mb-4 text-decoration-none">
            <img src="{{ $request['service_provider']['image_url'] }}" class="rounded-circle me-2"
              width="40" height="40" alt="provider">
            <div class="fw-semibold text-dark">{{ $request['service_provider']['name'] }}</div>
          </a>

          {{-- Actions --}}
          <div class="d-flex gap-2 mt-auto">
            @if($request['status'] === 'Completed')
              <button class="btn btn-primary w-100"
                data-bs-toggle="offcanvas" data-bs-target="#offcanvasReview"
                data-request-id="{{ $request['id'] }}"
                data-request-title="{{ $request['title'] }}"
                data-provider-name="{{ $request['service_provider']['name'] }}"
                data-provider-img="{{ $request['service_provider']['image_url'] }}">
                <i class="ti ti-star me-1"></i> {{ __('admin.add_review') }}
              </button>

            @elseif($request['status'] === 'Rejected')
              <button class="btn btn-primary w-100"
                data-bs-toggle="offcanvas" data-bs-target="#offcanvasHistory"
                data-request-id="{{ $request['id'] }}"
                data-request-title="{{ $request['title'] }}"
                data-provider-name="{{ $request['service_provider']['name'] }}"
                data-provider-img="{{ $request['service_provider']['image_url'] }}"
                data-status="{{ $request['status'] }}">
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
              data-status="{{ $request['status'] }}">
              <i class="ti ti-history"></i>
            </button>
          </div>

        </div>
      </div>
    </div>
    @endforeach
  </div>
</div>

{{-- ═══════════════════════════════════════════
     OFFCANVAS 1 — Add Review
═══════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasReview" aria-labelledby="offcanvasReviewLabel">
  <div class="offcanvas-header border-bottom">
    <div class="d-flex align-items-center gap-2">
      <div class="avatar avatar-sm">
        <div class="avatar-initial rounded-circle bg-label-warning">
          <i class="ti ti-star ti-sm"></i>
        </div>
      </div>
      <h5 class="offcanvas-title mb-0" id="offcanvasReviewLabel">{{ __('admin.add_review') }}</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body">

    {{-- Provider info --}}
    <div class="d-flex align-items-center gap-3 p-3 bg-light-subtle border rounded mb-4">
      <img src="" alt="Provider" class="rounded-circle review-provider-img" width="48" height="48">
      <div>
        <div class="fw-semibold review-provider-name">—</div>
        <small class="text-muted review-request-title">—</small>
      </div>
    </div>

    <form id="formReview" novalidate>
      <input type="hidden" name="request_id" id="reviewRequestId">

      {{-- Star Rating --}}
      <div class="mb-4">
        <label class="form-label fw-semibold">
          {{ __('admin.Rating') }} <span class="text-danger">*</span>
        </label>
        <div class="d-flex gap-1 fs-3" id="starBtnGroup">
          @for($s = 1; $s <= 5; $s++)
          <button type="button" class="btn btn-link p-0 text-secondary star-btn"
            data-value="{{ $s }}" aria-label="{{ $s }} star">
            <i class="ti ti-star"></i>
          </button>
          @endfor
        </div>
        {{-- Hidden field that FV validates --}}
        <input type="hidden" name="rating_value" id="ratingValueInput">
        <div id="starErrorMsg" class="text-danger small mt-1 d-none">
          {{ __('admin.please_select_rating') }}
        </div>
      </div>

      {{-- Title --}}
      <div class="mb-4">
        <label for="reviewTitle" class="form-label fw-semibold">
          {{ __('admin.review_title') }} <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" id="reviewTitle" name="review_title"
          placeholder="{{ __('admin.review_title_placeholder') }}">
      </div>

      {{-- Review body --}}
      <div class="mb-4">
        <label for="reviewBody" class="form-label fw-semibold">
          {{ __('admin.your_review') }} <span class="text-danger">*</span>
        </label>
        <textarea class="form-control" id="reviewBody" name="review_body" rows="5"
          placeholder="{{ __('admin.your_review_placeholder') }}" maxlength="500"></textarea>
        <div class="d-flex justify-content-end mt-1">
          <small class="text-muted"><span id="reviewBodyCount">0</span>/500</small>
        </div>
      </div>

      {{-- Recommend --}}
      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="recommendSwitch" name="recommend" value="1">
          <label class="form-check-label" for="recommendSwitch">
            {{ __('admin.recommend_provider_label') }}
          </label>
        </div>
      </div>

      <div class="d-flex gap-3">
        <button type="submit" class="btn btn-primary flex-fill">
          <i class="ti ti-send me-1"></i> {{ __('admin.submit_review')}}
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
        <button class="nav-link active" id="tab-timeline" data-bs-toggle="tab"
          data-bs-target="#tabpane-timeline" type="button" role="tab">
          <i class="ti ti-timeline me-1"></i>{{ __('admin.status_timeline') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link position-relative" id="tab-messages" data-bs-toggle="tab"
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
        <button class="nav-link" id="tab-reviews" data-bs-toggle="tab"
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
        <div class="d-flex flex-column gap-3 pt-2">

          <div class="border rounded p-3 bg-light-subtle">
            <div class="d-flex align-items-center gap-2 mb-2">
              <img src="https://i.pravatar.cc/32?img=10" class="rounded-circle" width="32" height="32" alt="reviewer">
              <div>
                <div class="fw-semibold small">Ahmed Al-Mansouri</div>
                <div class="text-warning small">
                  <i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i>
                  <i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i>
                  <i class="ti ti-star"></i>
                </div>
              </div>
              <small class="text-muted ms-auto">Jan 18, 2025</small>
            </div>
            <p class="small fw-semibold mb-1">Great communication but delivery was late.</p>
            <p class="small text-muted mb-0">The work quality was excellent, however the final handover missed the agreed deadline by two days. Would still recommend for quality-focused projects.</p>
          </div>

          <div class="border rounded p-3 bg-light-subtle">
            <div class="d-flex align-items-center gap-2 mb-2">
              <img src="https://i.pravatar.cc/32?img=20" class="rounded-circle" width="32" height="32" alt="reviewer">
              <div>
                <div class="fw-semibold small">Sara Khalid</div>
                <div class="text-warning small">
                  <i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i>
                  <i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i>
                  <i class="ti ti-star-filled"></i>
                </div>
              </div>
              <small class="text-muted ms-auto">Jan 20, 2025</small>
            </div>
            <p class="small fw-semibold mb-1">Outstanding service, highly recommend!</p>
            <p class="small text-muted mb-0">Exceeded all expectations. Professional team, clear communication, and delivered ahead of schedule. Will definitely work with them again.</p>
          </div>

          <p class="text-center text-muted small mb-0">Showing 2 of 2 reviews</p>

        </div>
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

  /* ══════════════════════════════════════
     OFFCANVAS 1 — Add Review
  ══════════════════════════════════════ */
  const ocReview = document.getElementById('offcanvasReview');
  let currentRating = 0;
  const starBtns = document.querySelectorAll('.star-btn');

  function renderStars(val) {
    starBtns.forEach(btn => {
      const icon = btn.querySelector('i');
      const filled = parseInt(btn.dataset.value) <= val;
      icon.className = filled ? 'ti ti-star-filled' : 'ti ti-star';
      btn.classList.toggle('text-warning', filled);
      btn.classList.toggle('text-secondary', !filled);
    });
  }

  starBtns.forEach(btn => {
    btn.addEventListener('mouseenter', () => renderStars(parseInt(btn.dataset.value)));
    btn.addEventListener('mouseleave', () => renderStars(currentRating));
    btn.addEventListener('click', () => {
      currentRating = parseInt(btn.dataset.value);
      $('#ratingValueInput').value = currentRating;
      renderStars(currentRating);
      $('#starErrorMsg').classList.add('d-none');
      fvReview && fvReview.revalidateField('rating_value');
    });
  });

  ocReview.addEventListener('show.bs.offcanvas', e => {
    const btn = e.relatedTarget;
    if (!btn) return;
    $('.review-provider-img').src          = btn.dataset.providerImg  ?? '';
    $('.review-provider-name').textContent = btn.dataset.providerName ?? '—';
    $('.review-request-title').textContent = btn.dataset.requestTitle ?? '—';
    $('#reviewRequestId').value            = btn.dataset.requestId    ?? '';
    formReview.reset();
    currentRating = 0;
    renderStars(0);
    $('#ratingValueInput').value = '';
    $('#reviewBodyCount').textContent = '0';
    $('#starErrorMsg').classList.add('d-none');
    fvReview && fvReview.resetForm(true);
  });

  charCounter($('#reviewBody'), 'reviewBodyCount');

  const formReview = $('#formReview');
  const fvReview = FV.formValidation(formReview, {
    fields: {
      rating_value: {
        validators: { notEmpty: { message: '{{ __('admin.star_rating_required') }}' } },
      },
      review_title: {
        validators: {
          notEmpty:     { message: '{{ __('admin.review_title_required') }}' },
          stringLength: { min: 3, max: 50, message: '{{ __('admin.review_title_min_max') }}' },
        },
      },
      review_body: {
        validators: {
          notEmpty:     { message: '{{ __('admin.review_body_required') }}' },
          stringLength: { min: 3,max: 500, message: '{{ __('admin.review_body_min_max') }}' },
        },
      },
    },
    plugins: {
      trigger:      new FV.plugins.Trigger(),
      bootstrap5:   new FV.plugins.Bootstrap5({ rowSelector: '.mb-4' }),
      autoFocus:    new FV.plugins.AutoFocus(),
      submitButton: new FV.plugins.SubmitButton(),
    },
  }).on('core.form.valid', () => {
    if (!$('#ratingValueInput').value) {
      $('#starErrorMsg').classList.remove('d-none');
      return;
    }
    showToast({ type: 'success', message: '{{ __('admin.review_submitted_successfully') }}' });
    bootstrap.Offcanvas.getInstance(ocReview).hide();
  });

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

});
</script>
@stop