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
@endphp

@section('content')
<div>
  {{-- Stats Cards --}}
  <div class="row g-3 mb-6">
    @php
    $config = [
      'total'     => ['label' => 'total_requests',  'icon' => 'ti-clipboard-list', 'color' => 'primary', 'status' => null],
      'pending'   => ['label' => 'Pending',          'icon' => 'ti-clock',          'color' => 'warning', 'status' => 'pending'],
      'completed' => ['label' => 'Completed',         'icon' => 'ti-checks',         'color' => 'success', 'status' => 'completed'],
      'decision'  => ['label' => 'approved_rejected', 'icon' => 'ti-checks',         'color' => 'info',    'status' => null],
    ];
    @endphp

    @foreach($config as $key => $item)
    <div class="col-sm-6 col-xl-3">
      @if($key === 'decision')
      <div class="card h-100">
        <div class="card-body pb-3">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('admin.' . $item['label']) }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">
                  <a href="{{ route('service_requests.list_mine', ['request_status' => 'approved']) }}" class="text-decoration-none" style="color: inherit;">
                    <span>{{ $stats['approved'] ?? 0 }}</span>
                    <span class="small text-muted">{{ __('admin.Approved') }}</span>
                  </a>
                  /
                  <a href="{{ route('service_requests.list_mine', ['request_status' => 'rejected']) }}" class="text-decoration-none" style="color: inherit;">
                    <span>{{ $stats['rejected'] ?? 0 }}</span>
                    <span class="small text-muted">{{ __('admin.Rejected') }}</span>
                  </a>
                </h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-{{ $item['color'] }}">
                <i class="ti {{ $item['icon'] }} ti-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
      @else
      <a href="{{ route('service_requests.list_mine', $item['status'] ? ['request_status' => $item['status']] : []) }}" class="text-decoration-none" style="color: inherit;">
        <div class="card h-100" style="cursor: pointer;">
          <div class="card-body pb-3">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="text-heading">{{ __('admin.' . $item['label']) }}</span>
                <div class="d-flex align-items-center my-1">
                  <h4 class="mb-0 me-2">{{ $stats[$key] ?? 0 }}</h4>
                </div>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-{{ $item['color'] }}">
                  <i class="ti {{ $item['icon'] }} ti-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </a>
      @endif
    </div>
    @endforeach
  </div>

  {{--  Navigation Status Pills --}}
  <nav class="nav-align-top">
    <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-2 gap-lg-0" role="tablist">

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == null ? 'active' : '' }}" href="{{ route('service_requests.list_mine') }}">
          {{ __('admin.all_requests') }}
          <span class="badge badge-center rounded-pill bg-light text-primary bg-glow ms-2">
            {{ $stats['total'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'pending' ? 'active' : '' }}"
          href="{{ route('service_requests.list_mine', ['request_status' => 'pending']) }}">
          {{ __('admin.pending') }}
          <span class="badge badge-center rounded-pill bg-light text-primary ms-2">
            {{ $stats['pending'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'completed' ? 'active' : '' }}"
          href="{{ route('service_requests.list_mine', ['request_status' => 'completed']) }}">
          {{ __('admin.completed') }}
          <span class="badge badge-center rounded-pill bg-light text-primary ms-2">
            {{ $stats['completed'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'approved' ? 'active' : '' }}"
          href="{{ route('service_requests.list_mine', ['request_status' => 'approved']) }}">
          {{ __('admin.Approved') }}
          <span class="badge badge-center rounded-pill bg-light text-primary ms-2">
            {{ $stats['approved'] ?? 0 }}
          </span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request('request_status') == 'rejected' ? 'active' : '' }}"
          href="{{ route('service_requests.list_mine', ['request_status' => 'rejected']) }}">
          {{ __('admin.Rejected') }}
          <span class="badge badge-center rounded-pill bg-light text-primary ms-2">
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

          {{-- Service Provider --}}
          @if(!$request['hide_vendor_info'])
          <a href="{{ $request['service_provider']['profile_url'] ?? '#' }}" class="d-flex align-items-center mb-4 text-decoration-none">
            <img src="{{ $request['service_provider']['image_url'] }}" class="rounded-circle me-2"
              width="40" height="40" alt="provider">
            <div class="fw-semibold text-dark">{{ $request['service_provider']['name'] }}</div>
          </a>
          @else
          <div class="d-flex align-items-center mb-4">
            <img src="/icons/account.svg" class="rounded-circle me-2"
              width="40" height="40" alt="provider">
          </div>
          @endif

          {{-- Actions --}}
          <div class="d-flex gap-2 mt-auto">
            @if($request['status'] === 'Completed')
              <button class="btn btn-primary w-100"
                data-bs-toggle="offcanvas" data-bs-target="#offcanvasReview"
                data-request-id="{{ $request['id'] }}"
                data-request-title="{{ $request['title'] }}"
                data-provider-name="{{ $request['service_provider']['name'] }}"
                data-provider-img="{{ $request['service_provider']['image_url'] }}"
                data-existing-rating="{{ $request['rating'] ?? '' }}"
                data-existing-title="{{ $request['review_title'] ?? '' }}"
                data-existing-body="{{ $request['review_body'] ?? '' }}">
                <i class="ti ti-star me-1"></i> {{ $request['has_review'] ? __('admin.edit_review') : __('admin.add_review') }}
              </button>

            @elseif($request['status'] === 'Rejected')
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
                data-founder-img="{{ $request['founder_img'] ?? '' }}"
                data-unread-count="{{ $request['unread_count'] ?? 0 }}">
                <i class="ti ti-history me-1"></i> {{ __('admin.view_history') }}
              </button>

            @else
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
                data-founder-img="{{ $request['founder_img'] ?? '' }}"
                data-unread-count="{{ $request['unread_count'] ?? 0 }}"
                data-tab="messages">
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
              data-founder-img="{{ $request['founder_img'] ?? '' }}"
              data-unread-count="{{ $request['unread_count'] ?? 0 }}"
              data-tab="messages">
              <i class="ti ti-send"></i>
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
          <p class="text-muted mb-3">{{ __('admin.no_service_requests_yet') }}</p>
          <a href="{{ route('services.index') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> {{ __('admin.request_a_service') }}
          </a>
        </div>
      </div>
    </div>
    @endforelse
  </div>

  {{-- Pagination --}}
  @if($requests->hasPages())
  <div class="w-100 mt-8">
    {{ $requests->links('pagination::bootstrap-5') }}
  </div>
  @endif
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
          {{ __('admin.review_title') }}
        </label>
        <input type="text" class="form-control" id="reviewTitle" name="review_title"
          placeholder="{{ __('admin.review_title_placeholder') }}">
      </div>

      {{-- Review body --}}
      <div class="mb-4">
        <label for="reviewBody" class="form-label fw-semibold">
          {{ __('admin.your_review') }}
        </label>
        <textarea class="form-control" id="reviewBody" name="review_body" rows="5"
          placeholder="{{ __('admin.your_review_placeholder') }}" maxlength="500"></textarea>
        <div class="d-flex justify-content-end mt-1">
          <small class="text-muted"><span id="reviewBodyCount">0</span>/500</small>
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

    {{-- Message thread (loaded via AJAX) --}}
    <div class="border rounded p-3 mb-4 overflow-auto" style="max-height:200px" id="msgThread">
      <div class="text-center py-5" id="msgThreadLoading">
        <div class="spinner-border spinner-border-sm text-primary"></div>
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
          <small id="msgUnreadBadge"
            class="position-absolute rounded-circle bg-danger text-white w-px-20 h-px-20 d-flex flex-shrink-0 align-items-center justify-content-center"
            style="top: 0; right: 0; display: none;"
          >
            0
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
        <div id="timelineContainer">
          <div class="text-center py-5" id="timelineLoading">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <ul class="list-unstyled mt-2 mb-0" id="timelineList" style="display:none;"></ul>
          <div class="text-center py-5" id="timelineEmpty" style="display:none;">
            <i class="ti ti-timeline-event-x ti-48px text-muted mb-3 d-block"></i>
            <p class="text-muted mb-0">{{ __('admin.no_timeline_entries') }}</p>
          </div>
        </div>
      </div>

      {{-- ── TAB 2 · Messages ── --}}
      <div class="tab-pane fade" id="tabpane-messages" role="tabpanel">
        <div class="d-flex flex-column gap-3 pt-2" id="histMsgThread">
          <div class="text-center py-5" id="histMsgLoading">
            <div class="spinner-border spinner-border-sm text-primary"></div>
          </div>
        </div>

        {{-- Quick reply --}}
        <div class="border-top mt-4 pt-3">
          <form id="formHistoryReply" novalidate>
            <input type="hidden" name="request_id" id="histReplyRequestId">
            <div class="mb-2">
              <textarea class="form-control" id="histReplyBody" name="message" rows="3"
                placeholder="{{ __('admin.reply_placeholder') }}" maxlength="1000"></textarea>
              <div class="d-flex justify-content-end mt-1">
                <small class="text-muted"><span id="histReplyCount">0</span>/1000</small>
              </div>
            </div>
            <div class="mb-3">
              <label for="histReplyAttachment" class="form-label fw-semibold">
                {{ __('admin.attachment') }}
                <span class="text-muted fw-normal small">({{ __('admin.optional') }})</span>
              </label>
              <input type="file" class="form-control" id="histReplyAttachment" name="attachment"
                accept=".png,.jpg,.jpeg,.webp,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar">
              <div id="histReplyAttachmentError" class="invalid-feedback"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <button type="submit" class="btn btn-primary" id="btnHistReplySubmit" disabled>
                <i class="ti ti-send me-1"></i>{{ __('admin.Send_Message') }}
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

  /* ── Render Review Tab content (Founder view) ── */
  let lastHistoryBtn = null;
  function renderReviewTab(btn) {
    const container = document.getElementById('reviewTabContent');
    const hasReview = btn.dataset.hasReview === '1';
    const requestStatus = btn.dataset.status ?? '';

    if (!hasReview && requestStatus === 'Completed') {
      container.innerHTML = `
        <div class="text-center py-5">
          <i class="ti ti-star-off ti-48px text-muted mb-3 d-block"></i>
          <p class="text-muted mb-3">{{ __('admin.no_review_submitted_founder') }}</p>
          <button class="btn btn-primary btn-sm review-cta-btn"
            data-request-id="${esc(btn.dataset.requestId)}"
            data-request-title="${esc(btn.dataset.requestTitle)}"
            data-provider-name="${esc(btn.dataset.providerName)}"
            data-provider-img="${esc(btn.dataset.providerImg)}">
            <i class="ti ti-star me-1"></i> {{ __('admin.add_review') }}
          </button>
        </div>`;

      container.querySelector('.review-cta-btn')?.addEventListener('click', function() {
        bootstrap.Offcanvas.getInstance(ocHistory).hide();
        const reviewBtn = document.querySelector(`[data-bs-target="#offcanvasReview"][data-request-id="${this.dataset.requestId}"]`);
        if (reviewBtn) {
          setTimeout(() => reviewBtn.click(), 300);
        }
      });
      return;
    }

    if (!hasReview) {
      container.innerHTML = `
        <div class="text-center py-5">
          <i class="ti ti-star-off ti-48px text-muted mb-3 d-block"></i>
          <p class="text-muted mb-0">{{ __('admin.no_review_available_yet') }}</p>
        </div>`;
      return;
    }

    const rating      = parseInt(btn.dataset.reviewRating) || 0;
    const title       = btn.dataset.reviewTitle || '';
    const body        = btn.dataset.reviewBody || '';
    const reviewedAt  = btn.dataset.reviewedAt || '';
    const founderName = btn.dataset.founderName || '—';
    const founderImg  = btn.dataset.founderImg || '/icons/account.svg';

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
    $('#starErrorMsg').classList.add('d-none');
    fvReview && fvReview.resetForm(true);

    const existingRating = parseInt(btn.dataset.existingRating) || 0;
    if (existingRating > 0) {
      currentRating = existingRating;
      $('#ratingValueInput').value = currentRating;
      renderStars(currentRating);
      $('#reviewTitle').value = btn.dataset.existingTitle || '';
      $('#reviewBody').value  = btn.dataset.existingBody  || '';
      $('#reviewBodyCount').textContent = ($('#reviewBody').value || '').length;
    } else {
      currentRating = 0;
      renderStars(0);
      $('#ratingValueInput').value = '';
      $('#reviewBodyCount').textContent = '0';
    }
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
          callback: {
            message: '{{ __('admin.review_title_required') }}',
            callback: function(input) {
              const body = $('#reviewBody').value.trim();
              if (body.length > 0 && input.value.trim().length === 0) return false;
              return true;
            }
          },
          stringLength: { min: 1, max: 50, message: '{{ __('admin.review_title_min_max') }}' },
        },
      },
      review_body: {
        validators: {
          callback: {
            message: '{{ __('admin.review_body_required') }}',
            callback: function(input) {
              const title = $('#reviewTitle').value.trim();
              if (title.length > 0 && input.value.trim().length === 0) return false;
              return true;
            }
          },
          stringLength: { min: 1, max: 500, message: '{{ __('admin.review_body_min_max') }}' },
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

    const submitBtn = formReview.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __("admin.submit_review") }}';

    fetch('{{ route("service_review.submit") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        request_id:   $('#reviewRequestId').value,
        rating:       parseInt($('#ratingValueInput').value),
        review_title: $('#reviewTitle').value || null,
        review_body:  $('#reviewBody').value || null,
      }),
    })
    .then(res => res.json())
    .then(data => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="ti ti-send me-1"></i> {{ __("admin.submit_review") }}';

      if (data.status) {
        showToast({ type: 'success', message: data.message });
        bootstrap.Offcanvas.getInstance(ocReview).hide();
        window.location.reload();
      } else {
        showToast({ type: 'error', message: data.message || '{{ __("admin.unexpected_error") }}' });
      }
    })
    .catch(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="ti ti-send me-1"></i> {{ __("admin.submit_review") }}';
      showToast({ type: 'error', message: '{{ __("admin.unexpected_error") }}' });
    });
  });

  $('#reviewTitle').addEventListener('input', () => fvReview.revalidateField('review_body'));
  $('#reviewBody').addEventListener('input', () => fvReview.revalidateField('review_title'));

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

    // Load existing messages via AJAX
    const requestId = btn.dataset.requestId;
    const msgThread = document.getElementById('msgThread');
    msgThread.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

    fetch(`/dashboard/requests/${requestId}/messages`, {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    })
    .then(res => res.json())
    .then(data => {
      if (!data.status || !data.messages || data.messages.length === 0) {
        msgThread.innerHTML = `
          <div class="text-center py-5">
            <i class="ti ti-message-off ti-48px text-muted mb-3 d-block"></i>
            <p class="text-muted mb-0">{{ __('admin.no_messages_yet') }}</p>
          </div>`;
        return;
      }
      let html = '';
      data.messages.forEach(msg => {
        const align = msg.is_mine ? 'end' : 'start';
        const bgClass = msg.is_mine ? 'bg-primary text-white' : 'bg-label-secondary border';
        const nameAlign = msg.is_mine ? 'text-end' : '';
        const namepad = msg.is_mine ? 'pe-1' : 'ps-1';
        let attachHtml = '';
        if (msg.attachment_url) {
          attachHtml = `<div class="mt-1"><a href="${esc(msg.attachment_url)}" target="_blank" class="${msg.is_mine ? 'text-white' : 'text-primary'}"><i class="ti ti-paperclip"></i> ${esc(msg.attachment_name)}</a></div>`;
        }
        html += `
          <div>
            <div class="d-flex justify-content-${align}">
              <div class="${bgClass} rounded-3 px-3 py-2 small" style="max-width:75%">
                ${esc(msg.body)}${attachHtml}
              </div>
            </div>
            <div class="${nameAlign}"><small class="text-muted ${namepad}">${esc(msg.sender_name)} · ${esc(msg.created_at)}</small></div>
          </div>`;
      });
      msgThread.innerHTML = html;
      msgThread.scrollTop = msgThread.scrollHeight;
    })
    .catch(() => {
      msgThread.innerHTML = `<p class="text-muted text-center py-3">{{ __('admin.message_thread_error') }}</p>`;
    });
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
    const submitBtn = formMessage.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __("admin.Send_Message") }}';

    const formData = new FormData(formMessage);

    fetch('{{ route("service_requests.send_message") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: formData,
    })
    .then(res => res.json())
    .then(data => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="ti ti-send me-1"></i> {{ __("admin.Send_Message") }}';
      if (data.status) {
        const d = data.data;
        let attachHtml = '';
        if (d.attachment_url) {
          attachHtml = `<div class="mt-1"><a href="${esc(d.attachment_url)}" target="_blank" class="text-white"><i class="ti ti-paperclip"></i> ${esc(d.attachment_name)}</a></div>`;
        }
        const msgThread = document.getElementById('msgThread');
        // Remove "no messages" placeholder if present
        const placeholder = msgThread.querySelector('.ti-message-off');
        if (placeholder) msgThread.innerHTML = '';
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
          <div>
            <div class="d-flex justify-content-end">
              <div class="bg-primary text-white rounded-3 px-3 py-2 small" style="max-width:75%">
                ${esc(d.body)}${attachHtml}
              </div>
            </div>
            <div class="text-end"><small class="text-muted pe-1">${esc(d.sender_name)} · ${esc(d.created_at)}</small></div>
          </div>`;
        msgThread.appendChild(wrapper);
        msgThread.scrollTop = msgThread.scrollHeight;
        formMessage.reset();
        $('#msgBodyCount').textContent = '0';
        fvMessage.resetForm(true);
        showToast({ type: 'success', message: data.message });
      } else {
        showToast({ type: 'error', message: data.message || '{{ __("admin.unexpected_error") }}' });
      }
    })
    .catch(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="ti ti-send me-1"></i> {{ __("admin.Send_Message") }}';
      showToast({ type: 'error', message: '{{ __("admin.unexpected_error") }}' });
    });
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

    lastHistoryBtn = btn;
    renderReviewTab(btn);

    if (btn.dataset.tab === 'messages') {
      bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-messages')).show();
    } else {
      bootstrap.Tab.getOrCreateInstance($('#tab-timeline')).show();
    }
    formHistoryReply.reset();
    $('#histReplyCount').textContent = '0';
    fvHistoryReply && fvHistoryReply.resetForm(true);

    // Update unread badge
    const unreadCount = parseInt(btn.dataset.unreadCount) || 0;
    const msgBadge = document.getElementById('msgUnreadBadge');
    if (msgBadge) {
      if (unreadCount > 0) {
        msgBadge.textContent = unreadCount;
        msgBadge.style.display = 'flex';
      } else {
        msgBadge.style.display = 'none';
      }
    }

    // Fetch timeline via AJAX
    const requestId = btn.dataset.requestId;
    const timelineList = document.getElementById('timelineList');
    const timelineLoading = document.getElementById('timelineLoading');
    const timelineEmpty = document.getElementById('timelineEmpty');

    timelineList.style.display = 'none';
    timelineList.innerHTML = '';
    timelineLoading.style.display = 'block';
    timelineEmpty.style.display = 'none';

    fetch(`/dashboard/requests/${requestId}/timeline`, {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    })
    .then(res => res.json())
    .then(data => {
      timelineLoading.style.display = 'none';

      if (!data.status || !data.timeline || data.timeline.length === 0) {
        timelineEmpty.style.display = 'block';
        return;
      }

      let html = '';
      data.timeline.forEach((item, index) => {
        const isLast = index === data.timeline.length - 1;
        const connector = !isLast
          ? `<div class="border-start border-2 border-${esc(item.color)} opacity-25 flex-grow-1 mt-1"></div>`
          : '';

        html += `
          <li class="d-flex gap-3 ${!isLast ? 'pb-4' : ''}">
            <div class="d-flex flex-column align-items-center flex-shrink-0">
              <span class="badge rounded-pill bg-${esc(item.color)} p-2"><i class="ti ${esc(item.icon)}"></i></span>
              ${connector}
            </div>
            <div class="d-flex justify-content-between w-100 pt-1">
              <div>
                <div class="fw-semibold text-${esc(item.color)} small">${esc(item.label)}</div>
                <small class="text-muted">${esc(item.description)}</small>
              </div>
              <small class="text-muted text-nowrap ms-3">${esc(item.occurred_at)}</small>
            </div>
          </li>`;
      });

      timelineList.innerHTML = html;
      timelineList.style.display = 'block';
    })
    .catch(() => {
      timelineLoading.style.display = 'none';
      timelineEmpty.style.display = 'block';
    });

    // Fetch messages via AJAX
    const histMsgThread = document.getElementById('histMsgThread');
    histMsgThread.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

    fetch(`/dashboard/requests/${requestId}/messages`, {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    })
    .then(res => res.json())
    .then(data => {
      if (!data.status || !data.messages || data.messages.length === 0) {
        histMsgThread.innerHTML = `
          <div class="text-center py-5">
            <i class="ti ti-message-off ti-48px text-muted mb-3 d-block"></i>
            <p class="text-muted mb-0">{{ __('admin.no_messages_yet') }}</p>
          </div>`;
        return;
      }
      let html = '';
      data.messages.forEach(msg => {
        const align = msg.is_mine ? 'end' : 'start';
        const bgClass = msg.is_mine ? 'bg-primary text-white' : 'bg-light border text-dark';
        const linkClass = msg.is_mine ? 'text-white' : 'text-primary';
        const avatar = msg.sender_img || '/icons/account.svg';
        const roleBadge = msg.sender_role ? `<span class="badge bg-label-secondary ms-1 me-1 px-1 py-0" style="font-size:10px;">${esc(msg.sender_role)}</span>` : '';
        let attachHtml = '';
        if (msg.attachment_url) {
          attachHtml = `<div class="mt-2 pt-1 border-top border-white-50"><a href="${esc(msg.attachment_url)}" target="_blank" class="${linkClass} text-decoration-underline small fw-semibold"><i class="ti ti-paperclip me-1"></i>${esc(msg.attachment_name)}</a></div>`;
        }
        html += `
          <div class="mb-3">
            <div class="d-flex align-items-center gap-1 mb-1 ${msg.is_mine ? 'flex-row-reverse' : ''}">
              <img src="${esc(avatar)}" class="rounded-circle border" width="24" height="24" alt="avatar">
              <span class="fw-semibold small">${esc(msg.sender_name)}</span>
              ${roleBadge}
              <small class="text-muted" style="font-size:11px;">${esc(msg.created_at)}</small>
            </div>
            <div class="d-flex justify-content-${align}">
              <div class="${bgClass} rounded-3 px-3 py-2 small shadow-sm" style="max-width:80%">
                <div style="white-space: pre-wrap; word-break: break-word;">${esc(msg.body)}</div>
                ${attachHtml}
              </div>
            </div>
          </div>`;
      });
      histMsgThread.innerHTML = html;
      histMsgThread.scrollTop = histMsgThread.scrollHeight;

      // Hide unread badge since messages are now read
      const msgBadge = document.getElementById('msgUnreadBadge');
      if (msgBadge) msgBadge.style.display = 'none';
    })
    .catch(() => {
      histMsgThread.innerHTML = `<p class="text-muted text-center py-3">{{ __('admin.message_thread_error') }}</p>`;
    });
  });

  const histReplyBody = $('#histReplyBody');
  const btnHistReplySubmit = $('#btnHistReplySubmit');

  if (histReplyBody) {
    histReplyBody.addEventListener('input', () => {
      const textVal = histReplyBody.value.trim();
      $('#histReplyCount').textContent = histReplyBody.value.length;
      if (btnHistReplySubmit) {
        btnHistReplySubmit.disabled = (textVal.length === 0);
      }
    });
  }

  const formHistoryReply = $('#formHistoryReply');
  const fvHistoryReply = FV.formValidation(formHistoryReply, {
    fields: {
      message: {
        validators: {
          notEmpty:     { message: '{{ __('admin.message_required') }}' },
          stringLength: { min: 1, max: 1000, message: '{{ __('admin.message_body_length') }}' },
        },
      },
      attachment: {
        validators: {
          callback: {
            callback: function(input) {
              const files = input.element.files;
              if (!files || files.length === 0) return true;

              const file = files[0];
              const ext = file.name.split('.').pop().toLowerCase();
              const allowed = ['png','jpg','jpeg','webp','doc','docx','xls','xlsx','ppt','pptx','zip','rar'];

              if (!allowed.includes(ext)) {
                return {
                  valid: false,
                  message: '{{ app()->getLocale() == "ar" ? "نوع الملف غير مدعوم. الملفات المسموحة: PNG, JPG, JPEG, WEBP, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP, RAR." : "Unsupported file type. Allowed files: PNG, JPG, JPEG, WEBP, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP, RAR" }}'
                };
              }

              if (file.size > 10 * 1024 * 1024) {
                return {
                  valid: false,
                  message: '{{ app()->getLocale() == "ar" ? "يجب ألا يزيد حجم الملف عن 10 ميجابايت." : "File size must be 10MB or less." }}'
                };
              }

              return true;
            },
          },
        },
      },
    },
    plugins: {
      trigger:      new FV.plugins.Trigger({
        event: {
          attachment: 'change',
        },
      }),
      bootstrap5:   new FV.plugins.Bootstrap5({ rowSelector: '.mb-2, .mb-3' }),
      autoFocus:    new FV.plugins.AutoFocus(),
      submitButton: new FV.plugins.SubmitButton(),
    },
  }).on('core.form.valid', () => {
    const submitBtn = formHistoryReply.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __("admin.Send_Message") }}';

    const formData = new FormData(formHistoryReply);

    fetch('{{ route("service_requests.send_message") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: formData,
    })
    .then(res => res.json())
    .then(data => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="ti ti-send me-1"></i>{{ __("admin.Send_Message") }}';
      if (data.status) {
        const d = data.data;
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-3';
        let attachHtml = '';
        if (d.attachment_url) {
          attachHtml = `<div class="mt-2 pt-1 border-top border-white-50"><a href="${esc(d.attachment_url)}" target="_blank" class="text-white text-decoration-underline small fw-semibold"><i class="ti ti-paperclip me-1"></i>${esc(d.attachment_name)}</a></div>`;
        }
        const avatar = d.sender_img || '/icons/account.svg';
        const roleBadge = d.sender_role ? `<span class="badge bg-label-secondary ms-1 me-1 px-1 py-0" style="font-size:10px;">${esc(d.sender_role)}</span>` : '';
        wrapper.innerHTML = `
          <div>
            <div class="d-flex align-items-center gap-1 mb-1 flex-row-reverse">
              <img src="${esc(avatar)}" class="rounded-circle border" width="24" height="24" alt="avatar">
              <span class="fw-semibold small">${esc(d.sender_name)}</span>
              ${roleBadge}
              <small class="text-muted" style="font-size:11px;">${esc(d.created_at)}</small>
            </div>
            <div class="d-flex justify-content-end">
              <div class="bg-primary text-white rounded-3 px-3 py-2 small shadow-sm" style="max-width:80%">
                <div style="white-space: pre-wrap; word-break: break-word;">${esc(d.body)}</div>
                ${attachHtml}
              </div>
            </div>
          </div>`;
        const msgThread = document.getElementById('histMsgThread');
        const placeholder = msgThread.querySelector('.ti-message-off');
        if (placeholder) msgThread.innerHTML = '';
        msgThread.appendChild(wrapper);
        msgThread.scrollTop = msgThread.scrollHeight;

        formHistoryReply.reset();
        $('#histReplyCount').textContent = '0';
        if (btnHistReplySubmit) btnHistReplySubmit.disabled = true;
        fvHistoryReply.resetForm(true);
        showToast({ type: 'success', message: '{{ __("admin.message_sent_successfully") }}' });
      } else {
        showToast({ type: 'error', message: data.message || '{{ __("admin.unexpected_error") }}' });
      }
    })
    .catch(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="ti ti-send me-1"></i>{{ __("admin.Send_Message") }}';
      showToast({ type: 'error', message: '{{ __("admin.unexpected_error") }}' });
    });
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
      if (activeTab === 'review' || activeTab === 'messages') {
        ocHistory.addEventListener('shown.bs.offcanvas', function onceShown() {
          const tabId = activeTab === 'messages' ? 'tab-messages' : 'tab-reviews';
          bootstrap.Tab.getOrCreateInstance(document.getElementById(tabId)).show();
          ocHistory.removeEventListener('shown.bs.offcanvas', onceShown);
        });
      }
    }
  }

});
</script>
@stop