@extends('layouts.admin')

@section('title', __('dashboard.sp_dashboard'))
@section('subTitle', __('dashboard.sp_my_dashboard'))

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/spinkit/spinkit.css" />
@endsection

@section('content')
  <div class="row mb-4">
    <div class="col-6 d-flex align-items-center gap-2">
      <div class="avatar avatar-md mb-0">
        <div class="avatar-initial bg-label-primary rounded">
          <i class="ti ti-dashboard ti-xl"></i>
        </div>
      </div>
      <div>
        <h5 class="mb-1">{{ __('dashboard.sp_dashboard') }}</h5>
        <p class="mb-0">{{ __('dashboard.sp_dashboard_desc') }}</p>
      </div>
    </div>
    <div class="col-6 d-flex justify-content-end align-items-center">
      {{-- REFRESH BUTTON --}}
      @if($_user->ableTo('service_provider_dashboard.refresh', $permissions))
        <button id="btn-refresh" class="btn btn-outline-primary btn-page-block" style="height: fit-content;">
          <i class="ti ti-refresh me-1"></i> {{ __('dashboard.sp_refresh') }}
        </button>
      @endif
    </div>
  </div>

  {{-- APPROVAL STATUS BANNER --}}
  @if($approvalStatus === 'pending')
    <div class="alert alert-warning d-flex align-items-center justify-content-between shadow-sm border-0 rounded-3 p-3 mb-4"
      role="alert">

      <div class="d-flex align-items-center">
        <div class="me-3">
          <div class="rounded-circle bg-warning bg-opacity-25 d-flex align-items-center justify-content-center"
            style="width: 48px; height: 48px;">
            <i class="ti ti-clock-hour-4 text-warning fs-4"></i>
          </div>
        </div>

        <div>
          <h6 class="mb-1 fw-bold text-warning">
            {{ __('dashboard.sp_pending_status_title') ?? 'Pending Status' }}
          </h6>
          <p class="mb-0 text-body">
            {{ __('dashboard.sp_status_pending_message') }}
          </p>
        </div>
      </div>
    </div>
    {{-- @elseif($approvalStatus === 'accepted')
    <div class="alert alert-success d-flex align-items-center mb-4 alert-dismissible" role="alert">
      <div class="me-3">
        <span class="avatar-initial rounded bg-label-success">
          <i class="ti ti-circle-check ti-md"></i>
        </span>
      </div>
      <div>
        <p class="mb-0 fw-semibold">{{ __('dashboard.sp_status_accepted_message') }}</p>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div> --}}
  @elseif($approvalStatus === 'rejected')
    <div class="alert alert-danger d-flex align-items-start justify-content-between shadow-sm border-0 rounded-3 p-3 mb-4"
      role="alert">

      <div class="d-flex align-items-start">
        <!-- Icon -->
        <div class="me-3">
          <div class="rounded-circle bg-danger bg-opacity-25 d-flex align-items-center justify-content-center"
            style="width: 48px; height: 48px;">
            <i class="ti ti-circle-x text-danger fs-4"></i>
          </div>
        </div>

        <!-- Content -->
        <div>
          <h6 class="mb-1 fw-bold text-danger">
            {{ __('dashboard.sp_status_rejected_title') ?? 'Request Rejected' }}
          </h6>

          <p class="mb-1 text-body">
            {{ __('dashboard.sp_status_rejected_message') }}
          </p>

          @if(!empty($rejectionReason))
            <div class="mb-1 text-body">
              <span class="fw-semibold text-dark">
                {{ __('dashboard.sp_status_rejected_reason_label') }}:
              </span>
              <span class="text-muted">
                {{ $rejectionReason }}
              </span>
            </div>
          @endif

          @if(!empty($_settings->Platform_support_email))
            <div class="small text-body">
              {{ __('dashboard.sp_status_rejected_support', ['email' => $_settings->Platform_support_email]) }}
            </div>
          @endif
        </div>
      </div>
    </div>
  @endif

  <div class="{{ $approvalStatus !== 'accepted' ? 'pe-none opacity-50 user-select-none' : '' }}">

    {{-- SUMMARY WIDGET CARDS --}}
    <div class="row mb-4 justify-content-center" id="summary-widgets">
      @if($_user->ableTo('dashboard.widgets_cards_service_provider_total_services_view', $permissions))
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
          <div class="card h-100 card-border-shadow-primary" style="cursor: pointer;">
            <div class="card-body pb-3">
              <div class="d-flex align-items-start justify-content-between">
                <div class="content-left">
                  <span class="text-heading">{{ __('dashboard.sp_total_services') }}</span>
                  <div class="d-flex align-items-center my-1">
                    <h4 class="mb-0 me-2" id="widget-total-services">{{ $summary['totalServices'] }}</h4>
                  </div>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="ti ti-briefcase ti-26px"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif

      @if($_user->ableTo('dashboard.widgets_cards_service_provider_active_services_view', $permissions))
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
          <div class="card h-100 card-border-shadow-success" style="cursor: pointer;">
            <div class="card-body pb-3">
              <div class="d-flex align-items-start justify-content-between">
                <div class="content-left">
                  <span class="text-heading">{{ __('dashboard.sp_active_services') }}</span>
                  <div class="d-flex align-items-center my-1">
                    <h4 class="mb-0 me-2" id="widget-active-services">{{ $summary['activeServices'] }}</h4>
                  </div>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="ti ti-circle-check ti-26px"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif

      @if($_user->ableTo('dashboard.widgets_cards_service_provider_completion_rate_view', $permissions))
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
          <div class="card h-100 card-border-shadow-secondary" style="cursor: pointer;">
            <div class="card-body pb-3">
              <div class="d-flex align-items-start justify-content-between">
                <div class="content-left">
                  <span class="text-heading">{{ __('dashboard.sp_completion_rate') }}</span>
                  <div class="d-flex align-items-center my-1">
                    <h4 class="mb-0 me-2" id="widget-completion-rate">
                      {{ $summary['completionRate'] !== null ? $summary['completionRate'] . '%' : '—' }}
                    </h4>
                  </div>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-danger">
                    <i class="ti ti-chart-pie-2 ti-26px"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif

      @if($_user->ableTo('dashboard.widgets_cards_service_provider_requests_30_days_view', $permissions))
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
          <div class="card h-100 card-border-shadow-info" style="cursor: pointer;">
            <div class="card-body pb-3">
              <div class="d-flex align-items-start justify-content-between">
                <div class="content-left">
                  <span class="text-heading">{{ __('dashboard.sp_requests_30_days') }}</span>
                  <div class="d-flex align-items-center my-1">
                    <h4 class="mb-0 me-2" id="widget-requests-30">{{ $summary['requests30Days'] }}</h4>
                  </div>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class="ti ti-file-text ti-26px"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif

      @if($_user->ableTo('dashboard.widgets_cards_service_provider_pending_requests_view', $permissions))
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
          <div class="card h-100 card-border-shadow-warning" style="cursor: pointer;">
            <div class="card-body pb-3">
              <div class="d-flex align-items-start justify-content-between">
                <div class="content-left">
                  <span class="text-heading">{{ __('dashboard.sp_pending_requests') }}</span>
                  <div class="d-flex align-items-center my-1">
                    <h4 class="mb-0 me-2" id="widget-pending">{{ $summary['pendingRequests'] }}</h4>
                  </div>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-warning">
                    <i class="ti ti-clock-hour-4 ti-26px"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif

      @if($_user->ableTo('dashboard.widgets_cards_service_provider_median_response_time_view', $permissions))
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
          <div class="card h-100 card-border-shadow-secondary" style="cursor: pointer;">
            <div class="card-body pb-3">
              <div class="d-flex align-items-start justify-content-between">
                <div class="content-left">
                  <span class="text-heading">{{ __('dashboard.sp_median_response_time') }}</span>
                  <div class="d-flex align-items-center my-1">
                    <h4 class="mb-0 me-2" id="widget-median-time">
                      {{ $summary['medianResponseTime'] !== null ? $summary['medianResponseTime'] . 'h' : '—' }}
                    </h4>
                  </div>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="ti ti-chart-bar ti-26px"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif
    </div>

    <div class="row g-3 mb-6">

      {{-- Request Volume --}}
      <div class="col-12 col-lg-8">
        <div class="card border rounded-4 p-3 h-100">
          <div class="card-body p-1">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-3 p-2"
                  style="width:40px;height:40px;background:#EEEDFE;">
                  <i class="ti ti-chart-area-line fs-5" style="color:#534AB7;" aria-hidden="true"></i>
                </div>
                <span class="fw-medium fs-6">{{ __('dashboard.sp_request_volume') }}</span>
              </div>
              <button class="btn btn-sm btn-label-secondary border px-3">{{ __('dashboard.sp_weekly') }}</button>
            </div>
            <div class="position-relative" style="height:255px;">
              <canvas id="rvChart" role="img" aria-label="Line chart of weekly request volume.">
              </canvas>
            </div>
          </div>
        </div>
      </div>

      {{-- Service Distribution --}}
      <div class="col-12 col-lg-4">
        <div class="card border rounded-4 p-3 h-100">
          <div class="card-body p-1 d-flex flex-column">

            <div class="d-flex align-items-center gap-2 mb-4">
              <div class="d-flex align-items-center justify-content-center rounded-3 p-2"
                style="width:40px;height:40px;background:#EEEDFE;">
                <i class="ti ti-layout-grid fs-5" style="color:#534AB7;" aria-hidden="true"></i>
              </div>
              <span class="fw-medium fs-6">{{ __('dashboard.sp_service_distribution') }}</span>
            </div>

            <div class="d-flex align-items-center gap-3 flex-grow-1">

              {{-- Donut --}}
              <div class="position-relative flex-shrink-0" style="width:150px;height:150px;">
                <canvas id="sdChart" width="150" height="150" role="img"
                  aria-label="Donut chart of service distribution.">
                </canvas>
                <div class="position-absolute top-50 start-50 translate-middle text-center">
                  <div class="fw-medium" style="font-size:26px;line-height:1;">{{ $totalRequests }}</div>
                  <div class="text-muted" style="font-size:11px;">{{ __('dashboard.sp_requests') }}</div>
                </div>
              </div>

              {{-- Legend --}}
              <div class="flex-grow-1 d-flex flex-column gap-1">
                @foreach ($chartServices as $i => $service)
                  <div class="d-flex align-items-center gap-2 px-2 py-1 rounded-3 sd-row"
                    style="cursor:pointer;transition:background .15s;" onclick="highlight({{ $i }})">
                    <span class="rounded-circle flex-shrink-0"
                      style="width:9px;height:9px;background:{{ $service['color'] }};display:inline-block;"></span>
                    <span class="small flex-grow-1">{{ $service['name'] }}</span>
                    <div class="rounded-pill overflow-hidden flex-shrink-0"
                      style="width:48px;height:4px;background:rgba(0,0,0,0.08);">
                      <div class="h-100 rounded-pill" style="width:{{ $service['pct'] }}%;background:{{ $service['color'] }};"></div>
                    </div>
                    <span class="text-muted"
                      style="font-size:11px;width:18px;text-align:right;">{{ $service['count'] }}</span>
                    <span class="fw-medium"
                      style="font-size:11px;width:34px;text-align:right;color:{{ $service['color'] }};">{{ $service['pct'] }}%</span>
                  </div>
                @endforeach
              </div>
            </div>

            {{-- Footer stats --}}
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
              <div class="text-center">
                <div class="fw-medium" style="font-size:17px;">{{ count($chartServices) }}</div>
                <div class="text-muted" style="font-size:11px;">{{ __('dashboard.Services') }}</div>
              </div>
              <div style="width:1px;height:28px;background:rgba(0,0,0,0.08);"></div>
              <div class="text-center">
                <div class="fw-medium" style="font-size:17px;color:{{ !empty($topService) ? $topService['color'] : '#000' }};">
                  {{ $totalRequests > 0 ? (round($topService['count'] / $totalRequests * 100)) : 0 }}%
                </div>
                <div class="text-muted" style="font-size:11px;">{{ __('dashboard.sp_top_share') }}</div>
              </div>
              <div style="width:1px;height:28px;background:rgba(0,0,0,0.08);"></div>
              <div class="text-center">
                <div class="fw-medium" style="font-size:17px;">{{ $totalRequests }}</div>
                <div class="text-muted" style="font-size:11px;">{{ __('dashboard.sp_total') }}</div>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>

    <div class="row">
      {{-- REQUESTS INBOX TABLE --}}
      @if($_user->ableTo('dashboard.widgets_tables_service_provider_requests_inbox_view', $permissions))
        <div class="col-lg-8 mb-4">
          <div class="card card-border-shadow">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom mb-6">
              <h5 class="card-title mb-0">{{ __('dashboard.sp_requests_inbox') }}</h5>
              @if($canBulkAction)
                <div class="d-flex align-items-center gap-3" id="bulk-actions">
                  <button id="btn-bulk-accept" class="btn btn-primary" disabled>
                    <i class="ti ti-checks me-1"></i>{{ __('dashboard.sp_bulk_accept') }}
                  </button>
                  <button id="btn-bulk-reject" class="btn btn-label-secondary" disabled>
                    <i class="ti ti-x me-1"></i>{{ __('dashboard.sp_bulk_reject') }}
                  </button>
                </div>
              @endif
            </div>
            <div class="card-body">
              {{-- Filter row --}}
              <div class="row mb-3 g-2">
                <div class="col-md-4">
                  <select id="filter-status" class="form-select">
                    <option value="pending" selected>{{ __('dashboard.sp_filter_pending') }}</option>
                    <option value="accepted">{{ __('dashboard.sp_filter_accepted') }}</option>
                    <option value="rejected">{{ __('dashboard.sp_filter_rejected') }}</option>
                    <option value="all">{{ __('dashboard.sp_filter_all') }}</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <select id="filter-service" class="form-select">
                    <option value="0">{{ __('dashboard.sp_all_services') }}</option>
                    @foreach($services as $service)
                      <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <select id="filter-period" class="form-select">
                    <option value="7">{{ __('dashboard.sp_period_7_days') }}</option>
                    <option value="30" selected>{{ __('dashboard.sp_period_30_days') }}</option>
                    <option value="year">{{ __('dashboard.sp_period_this_year') }}</option>
                  </select>
                </div>
              </div>

            </div>
            {{-- Table --}}
            <div class="table-responsive card-datatable">
              <table class="table" id="inbox-table">
                <thead class="table-light">
                  <tr>
                    @if($canBulkAction)
                      <th style="width: 40px;"><input type="checkbox" id="select-all" class="form-check-input"></th>
                    @endif
                    <th>{{ __('dashboard.sp_startup_name') }}</th>
                    <th style="max-width: 250px;">{{ __('dashboard.sp_note') }}</th>
                    <th>{{ __('dashboard.sp_service_name') }}</th>
                    <th>{{ __('dashboard.sp_submitted') }}</th>
                    <th>{{ __('dashboard.sp_status') }}</th>
                    <th>{{ __('dashboard.sp_response_time') }}</th>
                    @if($canQuickAction)
                      <th>{{ __('dashboard.sp_actions') }}</th>
                    @endif
                  </tr>
                </thead>
                <tbody id="inbox-body"></tbody>
              </table>
            </div>
            <div id="inbox-loading" class="text-center py-4 d-none">
              <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
              <span class="ms-2">{{ __('dashboard.sp_loading') }}</span>
            </div>
          </div>
        </div>
      @endif

      {{-- RECENT ACTIVITY PANEL --}}
      @if($_user->ableTo('dashboard.widgets_tables_service_provider_recent_activity_view', $permissions))
        <div class="col-lg-4 mb-4">
          <div class="card card-border-shadow">
            <div class="card-header border-bottom mb-6">
              <h5 class="card-title mb-0">{{ __('dashboard.sp_recent_activity') }}</h5>
            </div>
            <div class="card-body p-0" id="activity-feed"></div>
            <div id="activity-empty" class="text-center text-muted d-none py-4">
              <i class="ti ti-activity-heartbeat ti-xl mb-2 d-block"></i>
              {{ __('dashboard.sp_no_activity') }}
            </div>
          </div>
        </div>
      @endif
    </div>

  </div>{{-- end .sp-dashboard-disabled wrapper --}}

@endsection

@section('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
  <script src="/vuexy/assets/vendor/libs/block-ui/block-ui.js"></script>
  <script src="/vuexy/assets/js/extended-ui-blockui.js"></script>
  <script>
    (function () {
      'use strict';
      const lang = document.documentElement.lang;
      // Translations for multilingual support
      const translations = {
        en: {
          actions: 'Actions',
          search: 'Search',
          view: 'View Details',
          show: 'Show',
          entries: 'entries',
          showing: 'Showing',
          to: 'to',
          of: 'of',
          filtered: 'filtered',
          noMatchingRecords: 'No matching records found',
          noDataAvailable: 'No data available in table',
        },
        ar: {
          actions: 'الإجراءات',
          search: 'بحث',
          view: 'عرض التفاصيل',
          show: 'عرض',
          entries: 'المدخلات',
          showing: 'عرض',
          to: 'إلى',
          of: 'من',
          filtered: 'مصفاة',
          noMatchingRecords: 'لا توجد سجلات مطابقة',
          noDataAvailable: 'لا توجد بيانات متاحة في الجدول',
        }
      };
      const t = translations[lang] || translations.en;
      const APPROVAL_STATUS = @json($approvalStatus ?? 'accepted');

      // If not approved, do not initialize interactive features
      if (APPROVAL_STATUS !== 'accepted') return;

      const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
      const CAN_QUICK_ACTION = @json($canQuickAction);
      const CAN_BULK_ACTION = @json($canBulkAction);
      const ROUTES = {
        inbox: "{{ route('service_provider_dashboard.requests_inbox') }}",
        quickAction: "{{ route('service_provider_request.quick_action') }}",
        bulkAction: "{{ route('service_provider_request.bulk_action') }}",
        activity: "{{ route('service_provider_dashboard.activity') }}",
        refresh: "{{ route('service_provider_dashboard.refresh') }}"
      };
      const LABELS = {
        accept: @json(__('dashboard.sp_accept')),
        reject: @json(__('dashboard.sp_reject')),
        pending: @json(__('dashboard.sp_filter_pending')),
        accepted: @json(__('dashboard.sp_filter_accepted')),
        rejected: @json(__('dashboard.sp_filter_rejected')),
        rejectTitle: @json(__('dashboard.sp_reject_reason')),
        rejectRequired: @json(__('dashboard.sp_reject_reason_required')),
        successTitle: @json(__('dashboard.sp_action_success')),
        refreshError: @json(__('dashboard.sp_refresh_error')),
        cancel: @json(__('admin.Cancel')),
        showDetails: @json(__('dashboard.sp_show_request_details'))
      };

      // Initial data from server
      let currentInboxData = @json($inboxRows);
      let currentActivityData = @json($activityEntries);

      // ========================
      // RENDER FUNCTIONS
      // ========================

      function renderInboxRows(rows) {
        const $body = $('#inbox-body');
        const $empty = $('#inbox-empty');
        $body.empty();

        if (!rows || rows.length === 0) {
          $empty.removeClass('d-none');
          return;
        }
        $empty.addClass('d-none');

        rows.forEach(function (row) {
          let statusBadge = '';
          if (row.status === 'Pending') {
            statusBadge = '<span class="badge bg-label-warning text-warning">' + LABELS.pending + '</span>';
          } else if (row.status === 'Accepted') {
            statusBadge = '<span class="badge bg-label-success text-success">' + LABELS.accepted + '</span>';
          } else if (row.status === 'Rejected') {
            statusBadge = '<span class="badge bg-label-danger text-danger">' + LABELS.rejected + '</span>';
          }

          let actions = '';
          if (CAN_QUICK_ACTION && row.is_pending) {
            actions = `
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill btn-accept"
                                data-id="${row.id}"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="${LABELS.accept}">
                                <i class="ti ti-circle-check"></i>
                            </button>

                            <button class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill btn-reject"
                                data-id="${row.id}"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="${LABELS.reject}">
                                <i class="ti ti-circle-x"></i>
                            </button>
                        </div>
                    `;
          } else if (CAN_QUICK_ACTION) {
            actions = `
                        <div class="d-flex align-items-center gap-2">
                            <a href="${escapeHtml(row.details_url)}" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill btn-details"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="${LABELS.showDetails}">
                                <i class="ti ti-eye"></i>
                            </a>
                        </div>
                    `;
          }

          let checkbox = '';
          if (CAN_BULK_ACTION && row.is_pending) {
            checkbox = '<td><input type="checkbox" class="form-check-input row-check" data-id="' + row.id + '"></td>';
          } else if (CAN_BULK_ACTION) {
            checkbox = '<td></td>';
          }

          let actionCell = CAN_QUICK_ACTION ? '<td>' + actions + '</td>' : '';

          $body.append(
            '<tr data-id="' + row.id + '">' +
            checkbox +
            '<td>' +
            '<a href="' + escapeHtml(row.startup_url) + '" target="_blank" class="text-decoration-none">' +
            escapeHtml(row.startup_company)
            + '</a>'
            + '</td>' +
            '<td style="max-width: 250px; min-width: 150px; white-space: normal; word-break: break-word;"><small class="text-muted">' + escapeHtml(row.comment) + '</small></td>' +
            '<td>' + escapeHtml(row.service_name) + '</td>' +
            '<td><small>' + escapeHtml(row.submitted) + '</small></td>' +
            '<td>' + statusBadge + '</td>' +
            '<td>' + escapeHtml(row.response_time) + '</td>' +
            actionCell +
            '</tr>'
          );
        });
      }

      function renderActivityFeed(entries) {
        const $feed = $('#activity-feed');
        const $empty = $('#activity-empty');
        $feed.empty();

        if (!entries || entries.length === 0) {
          $empty.removeClass('d-none');
          return;
        }
        $empty.addClass('d-none');

        entries.forEach(function (entry) {
          $feed.append(
            '<div class="d-flex align-items-start py-3 px-4 border-bottom border-light">' +
            '<div class="w-8 h-8 d-flex align-items-center justify-content-center rounded-circle bg-label-' + entry.color + ' me-3 flex-shrink-0">' +
            '<i class="ti ' + entry.icon + ' ti-sm"></i>' +
            '</div>' +
            '<div class="flex-grow-1">' +
            '<p class="mb-0 small">' + escapeHtml(entry.text) + '</p>' +
            '<small class="text-muted">' + escapeHtml(entry.time_ago) + '</small>' +
            '</div>' +
            '</div>'
          );
        });
      }

      function updateSummaryWidgets(summary) {
        if (summary.totalServices !== undefined) $('#widget-total-services').text(summary.totalServices);
        if (summary.activeServices !== undefined) $('#widget-active-services').text(summary.activeServices);
        if (summary.requests30Days !== undefined) $('#widget-requests-30').text(summary.requests30Days);
        if (summary.pendingRequests !== undefined) $('#widget-pending').text(summary.pendingRequests);
        if (summary.completionRate !== undefined) {
          $('#widget-completion-rate').text(summary.completionRate !== null ? summary.completionRate + '%' : '—');
        }
        if (summary.medianResponseTime !== undefined) {
          $('#widget-median-time').text(summary.medianResponseTime !== null ? summary.medianResponseTime + 'h' : '—');
        }
      }

      function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
      }

      function getFilterParams() {
        return {
          status: $('#filter-status').val(),
          service_id: $('#filter-service').val(),
          period: $('#filter-period').val()
        };
      }

      function updateBulkButtons() {
        var checked = $('.row-check:checked').length;
        $('#btn-bulk-accept, #btn-bulk-reject').prop('disabled', checked === 0);
      }

      // ========================
      // AJAX FUNCTIONS
      // ========================

      function fetchInbox() {
        $('#inbox-loading').removeClass('d-none');
        $('#inbox-body').empty();
        $('#inbox-empty').addClass('d-none');

        $.ajax({
          url: ROUTES.inbox,
          method: 'GET',
          data: getFilterParams(),
          headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
          success: function (response) {
            $('#inbox-loading').addClass('d-none');
            currentInboxData = response.rows;
            renderInboxRows(response.rows);
            if (response.summary) updateSummaryWidgets(response.summary);
          },
          error: function () {
            $('#inbox-loading').addClass('d-none');
            releaseAlert('danger', {
              title: 'Error',
              message: LABELS.refreshError,
              autoClose: 3000
            });
          }
        });
      }

      function doQuickAction(id, action, reason) {
        var $row = $('tr[data-id="' + id + '"]');
        $row.addClass('opacity-50 pe-none');

        $.ajax({
          url: ROUTES.quickAction,
          method: 'POST',
          data: { id: id, action: action, reason: reason, _token: CSRF_TOKEN },
          success: function (response) {
            $row.removeClass('opacity-50 pe-none');
            if (response.status) {
              releaseAlert('success', {
                title: response.title,
                message: response.message,
                autoClose: 2000
              });
              if (response.summary) updateSummaryWidgets(response.summary);
              fetchInbox();
              fetchActivity();
            } else {
              releaseAlert('danger', {
                title: response.title || 'Error',
                message: response.message
              });
            }
          },
          error: function (xhr) {
            $row.removeClass('opacity-50 pe-none');
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            releaseAlert('danger', {
              title: 'Error',
              message: msg
            });
          }
        });
      }

      function doBulkAction(ids, action, reason) {
        $('#btn-bulk-accept, #btn-bulk-reject').prop('disabled', true);

        $.ajax({
          url: ROUTES.bulkAction,
          method: 'POST',
          data: { ids: ids, action: action, reason: reason, _token: CSRF_TOKEN },
          success: function (response) {
            if (response.status) {
              releaseAlert('success', {
                title: response.title,
                message: response.message,
                autoClose: 2000
              });
              if (response.summary) updateSummaryWidgets(response.summary);
              fetchInbox();
              fetchActivity();
            } else {
              releaseAlert('danger', {
                title: response.title || 'Error',
                message: response.message
              });
            }
          },
          error: function (xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            releaseAlert('danger', {
              title: 'Error',
              message: msg
            });
            updateBulkButtons();
          }
        });
      }

      function fetchActivity() {
        $.ajax({
          url: ROUTES.activity,
          method: 'GET',
          headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
          success: function (response) {
            currentActivityData = response.activity;
            renderActivityFeed(response.activity);
          }
        });
      }

      function doRefresh() {
        var $btn = $('#btn-refresh');
        $btn
          .prop('disabled', true)
          .html('<i class="ti ti-loader-2 ti-spin me-1"></i>@lang("admin.refreshing")...')

        $.ajax({
          url: ROUTES.refresh,
          method: 'GET',
          data: getFilterParams(),
          headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
          success: function (response) {
            $btn
              .prop('disabled', false)
              .html('<i class="ti ti-refresh me-1"></i> @lang("dashboard.sp_refresh")')

            if (response.summary) updateSummaryWidgets(response.summary);
            if (response.inbox) {
              currentInboxData = response.inbox;
              renderInboxRows(response.inbox);
            }
            if (response.activity) {
              currentActivityData = response.activity;
              renderActivityFeed(response.activity);
            }
          },
          error: function () {
            $btn
              .prop('disabled', false)
              .html('<i class="ti ti-refresh me-1"></i> @lang("dashboard.sp_refresh")')
            releaseAlert('danger', {
              title: 'Error',
              message: LABELS.refreshError,
              autoClose: 3000
            });
          }
        });
      }

      // ========================
      // EVENT HANDLERS
      // ========================

      // Filter changes
      $('#filter-status, #filter-service, #filter-period').on('change', function () {
        fetchInbox();
      });

      // Select all checkbox
      $(document).on('change', '#select-all', function () {
        $('.row-check').prop('checked', this.checked);
        updateBulkButtons();
      });

      // Individual checkboxes
      $(document).on('change', '.row-check', function () {
        var total = $('.row-check').length;
        var checked = $('.row-check:checked').length;
        $('#select-all').prop('checked', total === checked && total > 0);
        updateBulkButtons();
      });

      // Accept button
      $(document).on('click', '.btn-accept', function () {
        var id = $(this).data('id');
        doQuickAction(id, 'accept', null);
      });

      // Reject button
      $(document).on('click', '.btn-reject', function () {
        var id = $(this).data('id');
        Swal.fire({
          title: LABELS.rejectTitle,
          input: 'textarea',
          inputLabel: LABELS.rejectRequired,
          inputValidator: function (value) {
            if (!value || !value.trim()) return LABELS.rejectRequired;
          },
          showCancelButton: true,
          showDenyButton: false,
          confirmButtonText: LABELS.reject,
          cancelButtonText: LABELS.cancel,
          customClass: {
            confirmButton: 'btn btn-danger ms-3',
            cancelButton: 'btn btn-label-secondary'
          },
          buttonsStyling: false,
          reverseButtons: true
        }).then(function (result) {
          if (result.isConfirmed) {
            doQuickAction(id, 'reject', result.value);
          }
        });
      });

      // Bulk accept
      $('#btn-bulk-accept').on('click', function () {
        var ids = [];
        $('.row-check:checked').each(function () { ids.push($(this).data('id')); });
        if (ids.length === 0) return;
        doBulkAction(ids, 'accept', null);
      });

      // Bulk reject
      $('#btn-bulk-reject').on('click', function () {
        var ids = [];
        $('.row-check:checked').each(function () { ids.push($(this).data('id')); });
        if (ids.length === 0) return;
        Swal.fire({
          title: LABELS.rejectTitle,
          input: 'textarea',
          inputLabel: LABELS.rejectRequired,
          inputValidator: function (value) {
            if (!value || !value.trim()) return LABELS.rejectRequired;
          },
          showCancelButton: true,
          showDenyButton: false,
          confirmButtonText: LABELS.reject,
          cancelButtonText: LABELS.cancel,
          customClass: {
            confirmButton: 'btn btn-danger ms-3',
            cancelButton: 'btn btn-label-secondary'
          },
          buttonsStyling: false,
          reverseButtons: true
        }).then(function (result) {
          if (result.isConfirmed) {
            doBulkAction(ids, 'reject', result.value);
          }
        });
      });

      // Refresh button
      $('#btn-refresh').on('click', function () {
        doRefresh();
      });

      // ========================
      // INITIAL RENDER
      // ========================

      renderInboxRows(currentInboxData);
      renderActivityFeed(currentActivityData);

      // Initialize DataTable
      $('#inbox-table').DataTable({
        destroy: true,
        paging: false,
        searching: false,
        info: false,
        ordering: true,
        columnDefs: [
          { orderable: false, targets: [0, 5] } // checkbox and actions columns
        ],
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
        language: {
          paginate: {
            next: '<i class="ti ti-chevron-right ti-sm"></i>',
            previous: '<i class="ti ti-chevron-left ti-sm"></i>'
          },
          search: `${t.search}:`,
          searchPlaceholder: `${t.search}...`,
          lengthMenu: `${t.show} <select class="form-select form-select-sm">` +
            `<option value="10">10</option>` +
            `<option value="25">25</option>` +
            `<option value="50">50</option>` +
            `<option value="75">75</option>` +
            `<option value="100">100</option>` +
            '</select> ' + t.entries,
          info: `${t.showing} <span class="text-primary">_START_</span> ${t.to} <span class="text-primary">_END_</span> ${t.of} <span class="text-primary">_TOTAL_</span> ${t.entries}`,
          infoEmpty: `${t.showing} 0 ${t.to} 0 ${t.of} 0 ${t.entries}`,
          infoFiltered: `(${t.filtered} ${t.of} _MAX_ ${t.entries})`,
          zeroRecords: `${t.noMatchingRecords}`,
          emptyTable: `${t.noDataAvailable}`,
        },
      });

    })();
  </script>

  <script>
    (function () {
      const lang = @json($l);
      const rvLabels = {!! $rvLabels !!};
      const rvData = {!! $rvData !!};
      const sdLabels = {!! $sdLabels !!};
      const sdData = {!! $sdData !!};
      const sdColors = {!! $sdColors !!};
      const sdDimColors = {!! $sdDimColors !!};
      const purple = '#6c63d8';

      /* Derive a "nice" y-axis range from the actual data so the scale isn't hardcoded. */
      function niceAxis(values) {
        const nums = (values || []).map(Number).filter(n => !isNaN(n));
        const dataMax = nums.length ? Math.max(...nums) : 0;
        const dataMin = nums.length ? Math.min(...nums) : 0;

        // Pick a step that yields roughly 5 ticks, rounded to a friendly increment.
        const span = Math.max(dataMax, 1);
        const rawStep = span / 5;
        const mag = Math.pow(10, Math.floor(Math.log10(rawStep)));
        const norm = rawStep / mag;
        const niceNorm = norm <= 1 ? 1 : norm <= 2 ? 2 : norm <= 5 ? 5 : 10;
        const stepSize = Math.max(1, niceNorm * mag);

        // Floor the min and ceil the max to step boundaries (min never below 0),
        // leaving one extra step of headroom for the value labels above each point.
        const min = Math.max(0, Math.floor(dataMin / stepSize) * stepSize - stepSize);
        const max = Math.ceil(dataMax / stepSize) * stepSize + stepSize;

        return { min, max, stepSize };
      }

      const rvAxis = niceAxis(rvData);

      /* ── Request Volume ── */
      new Chart(document.getElementById('rvChart'), {
        type: 'line',
        data: {
          labels: rvLabels,
          datasets: [{
            label: lang === 'ar' ? 'الطلبات' : 'Requests',
            data: rvData,
            borderColor: purple,
            borderWidth: 2.5,
            pointBackgroundColor: purple,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 7,
            tension: 0.4,
            fill: true,
            backgroundColor: (ctx) => {
              const { ctx: c, chartArea } = ctx.chart;
              if (!chartArea) return 'rgba(108,99,216,0.10)';
              const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
              g.addColorStop(0, 'rgba(108,99,216,0.18)');
              g.addColorStop(1, 'rgba(108,99,216,0.01)');
              return g;
            }
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          layout: { padding: { top: 28, right: 8, left: 4, bottom: 4 } },
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#fff',
              borderColor: 'rgba(0,0,0,0.10)',
              borderWidth: 1,
              titleColor: '#333',
              bodyColor: '#555',
              padding: 10,
              cornerRadius: 8,
              displayColors: true,
              boxWidth: 8, boxHeight: 8,
              callbacks: {
                title: (i) => i[0].label,
                label: (i) => '  ' + (lang === 'ar' ? 'الطلبات' : 'Requests') + ':  ' + i.raw
              }
            }
          },
          scales: {
            x: {
              grid: { display: false },
              border: { display: false },
              ticks: { color: '#aaa', font: { size: 13 }, maxRotation: 0 }
            },
            y: {
              min: rvAxis.min, max: rvAxis.max,
              ticks: { stepSize: rvAxis.stepSize, color: '#aaa', font: { size: 12 } },
              grid: { color: 'rgba(0,0,0,0.06)', drawBorder: false },
              border: { display: false, dash: [4, 4] }
            }
          }
        },
        plugins: [{
          id: 'dataLabels',
          afterDatasetsDraw(chart) {
            const { ctx, data, scales: { x, y } } = chart;
            data.datasets[0].data.forEach((val, i) => {
              const xp = x.getPixelForValue(i);
              const yp = y.getPixelForValue(val);
              const w = 26, h = 22, r = 5;
              ctx.save();
              ctx.fillStyle = purple;
              ctx.beginPath();
              ctx.roundRect(xp - w / 2, yp - h - 10, w, h, r);
              ctx.fill();
              ctx.fillStyle = '#fff';
              ctx.font = '500 12px sans-serif';
              ctx.textAlign = 'center';
              ctx.textBaseline = 'middle';
              ctx.fillText(val, xp, yp - h / 2 - 10);
              ctx.restore();
            });
          }
        }, {
          id: 'crosshair',
          afterDraw(chart) {
            if (chart.tooltip._active && chart.tooltip._active.length) {
              const { ctx, chartArea, scales: { x } } = chart;
              const xPos = x.getPixelForValue(chart.tooltip._active[0].index);
              ctx.save();
              ctx.setLineDash([5, 4]);
              ctx.strokeStyle = 'rgba(108,99,216,0.35)';
              ctx.lineWidth = 1;
              ctx.beginPath();
              ctx.moveTo(xPos, chartArea.top);
              ctx.lineTo(xPos, chartArea.bottom);
              ctx.stroke();
              ctx.restore();
            }
          }
        }]
      });

      /* ── Service Distribution ── */
      const sdChart = new Chart(document.getElementById('sdChart'), {
        type: 'doughnut',
        data: {
          labels: sdLabels,
          datasets: [{
            data: sdData,
            backgroundColor: sdColors,
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 6
          }]
        },
        options: {
          responsive: false,
          cutout: '70%',
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#fff',
              borderColor: 'rgba(0,0,0,0.10)',
              borderWidth: 1,
              titleColor: '#333',
              bodyColor: '#555',
              padding: 10,
              cornerRadius: 8,
              displayColors: true,
              boxWidth: 8, boxHeight: 8,
              callbacks: {
                label: (i) => ' ' + i.raw + (lang === 'ar' ? ' طلبات' : ' requests') + ' (' + i.raw + '%)'
              }
            }
          },
          onHover: (e, els) => syncRow(els.length ? els[0].index : -1)
        }
      });

      window.highlight = function (i) {
        document.querySelectorAll('.sd-row').forEach((r, idx) => {
          r.classList.toggle('bg-light', idx === i);
        });
        const meta = sdChart.getDatasetMeta(0);
        meta.data.forEach((arc, idx) => {
          arc.options.backgroundColor = (idx === i) ? sdColors[idx] : sdDimColors[idx];
        });
        sdChart.update('none');
      };

      function syncRow(i) {
        document.querySelectorAll('.sd-row').forEach((r, idx) => {
          r.classList.toggle('bg-light', i !== -1 && idx === i);
        });
      }
    })();
  </script>
@endsection
