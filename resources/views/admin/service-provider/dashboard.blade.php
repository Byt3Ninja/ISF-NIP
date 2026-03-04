@extends('layouts.admin')

@section('title', __('dashboard.sp_dashboard'))
@section('subTitle', __('dashboard.sp_my_dashboard'))

@section('content')

{{-- APPROVAL STATUS BANNER --}}
@if($approvalStatus === 'pending')
<div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
    <div class="me-3">
        <span class="avatar-initial rounded bg-label-warning">
            <i class="ti ti-clock-hour-4 ti-md"></i>
        </span>
    </div>
    <div>
        <p class="mb-0 fw-semibold">{{ __('dashboard.sp_status_pending_message') }}</p>
    </div>
</div>
{{--  @elseif($approvalStatus === 'accepted')
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
</div>  --}}
@elseif($approvalStatus === 'rejected')
<div class="alert alert-danger mb-4" role="alert">
    <div class="d-flex align-items-center mb-2">
        <span class="avatar-initial rounded bg-label-danger me-3">
            <i class="ti ti-circle-x ti-md"></i>
        </span>
        <p class="mb-0 fw-semibold">{{ __('dashboard.sp_status_rejected_message') }}</p>
    </div>
    @if(!empty($rejectionReason))
    <p class="mb-1"><strong>{{ __('dashboard.sp_status_rejected_reason_label') }}:</strong> {{ $rejectionReason }}</p>
    @endif
    @if(!empty($_settings->Platform_support_email))
    <p class="mb-0">{{ __('dashboard.sp_status_rejected_support', ['email' => $_settings->Platform_support_email]) }}</p>
    @endif
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

{{-- REFRESH BUTTON --}}
@if($_user->ableTo('service_provider_dashboard.refresh', $permissions))
<div class="mb-6 text-end">
    <button id="btn-refresh" class="btn btn-outline-primary">
        <i class="ti ti-refresh me-1"></i> {{ __('dashboard.sp_refresh') }}
    </button>
</div>
@endif

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
                            <th>{{ __('dashboard.sp_note') }}</th>
                            <th>{{ __('dashboard.sp_service_name') }}</th>
                            <th>{{ __('dashboard.sp_submitted') }}</th>
                            <th>{{ __('dashboard.sp_status') }}</th>
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
<script>
(function() {
    'use strict';

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
        refreshError: @json(__('dashboard.sp_refresh_error'))
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

        rows.forEach(function(row) {
            let statusBadge = '';
            if (row.status === 'Pending') {
                statusBadge = '<span class="badge bg-label-warning text-warning">'+LABELS.pending+'</span>';
            } else if (row.status === 'Accepted') {
                statusBadge = '<span class="badge bg-label-success text-success">'+LABELS.accepted+'</span>';
            } else if (row.status === 'Rejected') {
                statusBadge = '<span class="badge bg-label-danger text-danger">'+LABELS.rejected+'</span>';
            }

            let actions = '';
            if (CAN_QUICK_ACTION && row.is_pending) {
                actions = '<button class="btn btn-sm btn-success me-1 btn-accept" data-id="'+row.id+'">'+LABELS.accept+'</button>' +
                          '<button class="btn btn-sm btn-danger btn-reject" data-id="'+row.id+'">'+LABELS.reject+'</button>';
            }

            let checkbox = '';
            if (CAN_BULK_ACTION && row.is_pending) {
                checkbox = '<td><input type="checkbox" class="form-check-input row-check" data-id="'+row.id+'"></td>';
            } else if (CAN_BULK_ACTION) {
                checkbox = '<td></td>';
            }

            let actionCell = CAN_QUICK_ACTION ? '<td>'+actions+'</td>' : '';

            $body.append(
                '<tr data-id="'+row.id+'">' +
                    checkbox +
                    '<td>'+escapeHtml(row.startup_name)+'</td>' +
                    '<td><small class="text-muted">'+escapeHtml(row.comment)+'</small></td>' +
                    '<td>'+escapeHtml(row.service_name)+'</td>' +
                    '<td><small>'+escapeHtml(row.submitted)+'</small></td>' +
                    '<td>'+statusBadge+'</td>' +
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

        entries.forEach(function(entry) {
            $feed.append(
                '<div class="d-flex align-items-start py-3 px-4 border-bottom border-light">' +
                    '<div class="w-8 h-8 d-flex align-items-center justify-content-center rounded-circle bg-label-'+entry.color+' me-3 flex-shrink-0">' +
                        '<i class="ti '+entry.icon+' ti-sm"></i>' +
                    '</div>' +
                    '<div class="flex-grow-1">' +
                        '<p class="mb-0 small">'+escapeHtml(entry.text)+'</p>' +
                        '<small class="text-muted">'+escapeHtml(entry.time_ago)+'</small>' +
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
            success: function(response) {
                $('#inbox-loading').addClass('d-none');
                currentInboxData = response.rows;
                renderInboxRows(response.rows);
                if (response.summary) updateSummaryWidgets(response.summary);
            },
            error: function() {
                $('#inbox-loading').addClass('d-none');
                Swal.fire({ icon: 'error', title: 'Error', text: LABELS.refreshError, timer: 3000 });
            }
        });
    }

    function doQuickAction(id, action, reason) {
        var $row = $('tr[data-id="'+id+'"]');
        $row.addClass('opacity-50 pe-none');

        $.ajax({
            url: ROUTES.quickAction,
            method: 'POST',
            data: { id: id, action: action, reason: reason, _token: CSRF_TOKEN },
            success: function(response) {
                $row.removeClass('opacity-50 pe-none');
                if (response.status) {
                    Swal.fire({ icon: 'success', title: response.title, text: response.message, timer: 2000, showConfirmButton: false });
                    if (response.summary) updateSummaryWidgets(response.summary);
                    fetchInbox();
                    fetchActivity();
                } else {
                    Swal.fire({ icon: 'error', title: response.title || 'Error', text: response.message });
                }
            },
            error: function(xhr) {
                $row.removeClass('opacity-50 pe-none');
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    }

    function doBulkAction(ids, action, reason) {
        $('#btn-bulk-accept, #btn-bulk-reject').prop('disabled', true);

        $.ajax({
            url: ROUTES.bulkAction,
            method: 'POST',
            data: { ids: ids, action: action, reason: reason, _token: CSRF_TOKEN },
            success: function(response) {
                if (response.status) {
                    Swal.fire({ icon: 'success', title: response.title, text: response.message, timer: 2000, showConfirmButton: false });
                    if (response.summary) updateSummaryWidgets(response.summary);
                    fetchInbox();
                    fetchActivity();
                } else {
                    Swal.fire({ icon: 'error', title: response.title || 'Error', text: response.message });
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
                updateBulkButtons();
            }
        });
    }

    function fetchActivity() {
        $.ajax({
            url: ROUTES.activity,
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(response) {
                currentActivityData = response.activity;
                renderActivityFeed(response.activity);
            }
        });
    }

    function doRefresh() {
        var $btn = $('#btn-refresh');
        $btn.prop('disabled', true).find('i').addClass('ti-spin');

        $.ajax({
            url: ROUTES.refresh,
            method: 'GET',
            data: getFilterParams(),
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(response) {
                $btn.prop('disabled', false).find('i').removeClass('ti-spin');
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
            error: function() {
                $btn.prop('disabled', false).find('i').removeClass('ti-spin');
                Swal.fire({ icon: 'error', title: 'Error', text: LABELS.refreshError, timer: 3000 });
            }
        });
    }

    // ========================
    // EVENT HANDLERS
    // ========================

    // Filter changes
    $('#filter-status, #filter-service, #filter-period').on('change', function() {
        fetchInbox();
    });

    // Select all checkbox
    $(document).on('change', '#select-all', function() {
        $('.row-check').prop('checked', this.checked);
        updateBulkButtons();
    });

    // Individual checkboxes
    $(document).on('change', '.row-check', function() {
        var total = $('.row-check').length;
        var checked = $('.row-check:checked').length;
        $('#select-all').prop('checked', total === checked && total > 0);
        updateBulkButtons();
    });

    // Accept button
    $(document).on('click', '.btn-accept', function() {
        var id = $(this).data('id');
        doQuickAction(id, 'accept', null);
    });

    // Reject button
    $(document).on('click', '.btn-reject', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: LABELS.rejectTitle,
            input: 'textarea',
            inputLabel: LABELS.rejectRequired,
            inputValidator: function(value) {
                if (!value || !value.trim()) return LABELS.rejectRequired;
            },
            showCancelButton: true,
            confirmButtonText: LABELS.reject,
            confirmButtonColor: 'btn btn-danger'
        }).then(function(result) {
            if (result.isConfirmed) {
                doQuickAction(id, 'reject', result.value);
            }
        });
    });

    // Bulk accept
    $('#btn-bulk-accept').on('click', function() {
        var ids = [];
        $('.row-check:checked').each(function() { ids.push($(this).data('id')); });
        if (ids.length === 0) return;
        doBulkAction(ids, 'accept', null);
    });

    // Bulk reject
    $('#btn-bulk-reject').on('click', function() {
        var ids = [];
        $('.row-check:checked').each(function() { ids.push($(this).data('id')); });
        if (ids.length === 0) return;
        Swal.fire({
            title: LABELS.rejectTitle,
            input: 'textarea',
            inputLabel: LABELS.rejectRequired,
            inputValidator: function(value) {
                if (!value || !value.trim()) return LABELS.rejectRequired;
            },
            showCancelButton: true,
            confirmButtonText: LABELS.reject,
            confirmButtonColor: 'btn btn-danger'
        }).then(function(result) {
            if (result.isConfirmed) {
                doBulkAction(ids, 'reject', result.value);
            }
        });
    });

    // Refresh button
    $('#btn-refresh').on('click', function() {
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
        ]
    });

})();
</script>
@endsection
