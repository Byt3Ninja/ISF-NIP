<?php

namespace App\Http\Controllers;

use App\User;
use App\ServicesRequests;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\TemplateEmail;
use App\Services\ServiceRequestStatusLogger;

class ServiceProviderDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Check if the authenticated service provider's registration is approved.
     */
    private function isProviderApproved(): bool
    {
        $status = DB::table('service_provider_registration')
            ->where('user_id', auth()->id())
            ->value('status');

        if( $status === null ) { return true; }
        else
        {
            return  $status === 1 || $status === true;
        }
    }

    /**
     * Get service IDs belonging to the authenticated service provider.
     */
    private function getProviderServiceIds(): array
    {
        $providerId = auth()->user()->service_provider->id ?? 0;

        return DB::table('services')
            ->where('service_provider_id', $providerId)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Compute summary widget data for the service provider dashboard.
     */
    private function getSummaryData(array $serviceIds, array $permissions): array
    {
        $summary = [
            'totalServices' => 0,
            'activeServices' => 0,
            'requests30Days' => 0,
            'pendingRequests' => 0,
            'medianResponseTime' => null,
        ];

        if (empty($serviceIds)) {
            return $summary;
        }

        $base_services = DB::table('services')
                ->whereIn('id', $serviceIds)
                ->whereNull('deleted_at')
                ->get();

        $services_requests = DB::table('services_requests')
                ->whereIn('service_id', $serviceIds)
                ->whereNull('deleted_at')
                ->get();

        if (in_array('dashboard.widgets_cards_service_provider_total_services_view', $permissions)) {
            $summary['totalServices'] = $base_services->count();
        }

        if (in_array('dashboard.widgets_cards_service_provider_active_services_view', $permissions)) {
            $summary['activeServices'] = $base_services->where('status', true)->count();
        }

        if (in_array('dashboard.widgets_cards_service_provider_requests_30_days_view', $permissions)) {
            $summary['requests30Days'] = $services_requests
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
        }

        if (in_array('dashboard.widgets_cards_service_provider_pending_requests_view', $permissions)) {
            $summary['pendingRequests'] = $services_requests
                ->whereNull('approved')
                ->count();
        }

        if (in_array('dashboard.widgets_cards_service_provider_median_response_time_view', $permissions)) {
            $responseMinutes = $services_requests
                ->whereNotNull('approved')
                ->whereNotNull('decided_at')
                ->map(function ($r) {
                    return Carbon::parse($r->created_at)->diffInMinutes(Carbon::parse($r->decided_at));
                })
                ->sort()
                ->values();

            if ($responseMinutes->isNotEmpty()) {
                $count = $responseMinutes->count();
                $middle = intdiv($count, 2);

                if ($count % 2 === 0) {
                    $medianMinutes = ($responseMinutes[$middle - 1] + $responseMinutes[$middle]) / 2;
                } else {
                    $medianMinutes = $responseMinutes[$middle];
                }

                $hours = round($medianMinutes / 60, 1);
                $summary['medianResponseTime'] = $hours;
            }
        }

        return $summary;
    }

    /**
     * Build the inbox query with filters applied.
     */
    private function buildInboxQuery(array $serviceIds, Request $request)
    {
        $l = App::getLocale();
        $status = $request->input('status', 'pending');
        $serviceId = $request->input('service_id', 0);
        $period = $request->input('period', '30');

        $query = DB::table('services_requests as sr')
            ->join('services as s', 'sr.service_id', '=', 's.id')
            ->join('users as u', 'sr.user_id', '=', 'u.id')
            ->join('companies as co', 'co.id', '=', 'u.company_id')
            ->whereIn('sr.service_id', $serviceIds)
            ->whereNull('sr.deleted_at')
            ->select([
                'sr.id',
                'sr.comment',
                'sr.approved',
                'sr.created_at',
                'sr.decided_at',
                "s.name_{$l} as service_name",
                's.id as service_id',
                DB::raw("CONCAT(COALESCE(u.first_name_{$l}, ''), ' ', COALESCE(u.last_name_{$l}, '')) as startup_name"),
                "co.id as startup_id",
                "co.name_{$l} as startup_company_name",
            ]);

        // Status filter
        switch ($status) {
            case 'pending':
                $query->whereNull('sr.approved');
                break;
            case 'accepted':
                $query->where('sr.approved', 'Yes');
                break;
            case 'rejected':
                $query->where('sr.approved', 'No');
                break;
            // 'all' - no filter
        }

        // Service filter
        if ($serviceId && $serviceId != 0) {
            $query->where('sr.service_id', intval($serviceId));
        }

        // Period filter
        switch ($period) {
            case '7':
                $query->where('sr.created_at', '>=', now()->subDays(7));
                break;
            case '30':
                $query->where('sr.created_at', '>=', now()->subDays(30));
                break;
            case 'year':
                $query->where('sr.created_at', '>=', now()->startOfYear());
                break;
        }

        return $query->orderByDesc('sr.created_at');
    }

    /**
     * Format inbox rows for JSON response.
     */
    private function formatInboxRows($rows): array
    {
        return $rows->map(function ($row) {
            $statusLabel = 'Pending';
            if ($row->approved === 'Yes') {
                $statusLabel = 'Accepted';
            } elseif ($row->approved === 'No') {
                $statusLabel = 'Rejected';
            }

            $response_time = '—';
            $response_time = $row->decided_at ? sprintf('%02d:%02d', intdiv((int) Carbon::parse($row->created_at)->diffInMinutes(Carbon::parse($row->decided_at)), 60), ((int) Carbon::parse($row->created_at)->diffInMinutes(Carbon::parse($row->decided_at))) % 60) : '—';
            return [
                'id' => $row->id,
                'startup_name' => trim($row->startup_name),
                'startup_company' => trim( $row->startup_company_name ),
                'startup_url' => route('company.profile_view', ['id' => $row->startup_id]),
                'response_time' => $response_time,
                'comment' => $row->comment ?? '',
                'service_name' => $row->service_name,
                'service_id' => $row->service_id,
                'submitted' => Carbon::parse($row->created_at)->diffForHumans(),
                'submitted_raw' => $row->created_at,
                'decided' => Carbon::parse($row->decided_at)->diffForHumans(),
                'decided_raw' => $row->decided_at,
                'status' => $statusLabel,
                'is_pending' => is_null($row->approved),
            ];
        })->toArray();
    }

    /**
     * Query recent activity from audit logs.
     */
    private function getRecentActivity(array $serviceIds): array
    {
        if (empty($serviceIds)) {
            return [];
        }

        $l = App::getLocale();

        // Get service request IDs for this provider
        $srIds = DB::table('services_requests')
            ->whereIn('service_id', $serviceIds)
            ->pluck('id')
            ->toArray();

        $query = DB::table('audit_logs');

        if (!empty($srIds)) {
            $query->where(function ($q) use ($serviceIds, $srIds) {
                $q->where(function ($q2) use ($srIds) {
                    $q2->where('subject_type', 'like', 'App\\ServicesRequests#%')
                        ->whereIn('subject_id', $srIds);
                })->orWhere(function ($q2) use ($serviceIds) {
                    $q2->where('subject_type', 'like', 'App\\Service#%')
                        ->whereIn('subject_id', $serviceIds);
                });
            });
        } else {
            $query->where(function ($q) use ($serviceIds) {
                $q->where('subject_type', 'like', 'App\\Service#%')
                    ->whereIn('subject_id', $serviceIds);
            });
        }

        $logs = $query->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return $logs->map(function ($log) use ($l) {
            $properties = json_decode($log->properties, true) ?? [];
            $type = 'info';
            $icon = 'ti-info-circle';
            $color = 'info';
            $text = '';
            $secondaryText = '';

            $isServiceRequest = str_contains($log->subject_type, 'ServicesRequests');
            $isService = str_contains($log->subject_type, 'Service#') && !$isServiceRequest;

            if ($isServiceRequest) {
                if ($log->description === 'audit:created') {
                    $type = 'new_request';
                    $icon = 'ti-mail-forward';
                    $color = 'primary';
                    $text = __('dashboard.sp_new_request', ['startup' => '']);
                } elseif ($log->description === 'audit:updated') {
                    $approved = $properties['approved'] ?? null;
                    if ($approved === 'Yes') {
                        $type = 'accepted';
                        $icon = 'ti-circle-check';
                        $color = 'success';
                        $text = __('dashboard.sp_request_accepted', ['service' => '']);
                    } elseif ($approved === 'No') {
                        $type = 'rejected';
                        $icon = 'ti-circle-x';
                        $color = 'danger';
                        $text = __('dashboard.sp_request_rejected', ['service' => '']);
                    } else {
                        $type = 'updated';
                        $icon = 'ti-edit';
                        $color = 'warning';
                        $text = __('dashboard.sp_service_updated', ['name' => '']);
                    }
                }
            } elseif ($isService) {
                $serviceName = $properties["name_{$l}"] ?? $properties['name_en'] ?? '';
                if ($log->description === 'audit:created') {
                    $type = 'service_created';
                    $icon = 'ti-plus';
                    $color = 'success';
                    $text = __('dashboard.sp_service_created', ['name' => $serviceName]);
                } elseif ($log->description === 'audit:updated') {
                    if (isset($properties['status'])) {
                        $type = 'service_status';
                        $icon = 'ti-toggle-right';
                        $color = 'warning';
                        $text = __('dashboard.sp_service_status_changed', ['name' => $serviceName]);
                    } else {
                        $type = 'service_updated';
                        $icon = 'ti-edit';
                        $color = 'info';
                        $text = __('dashboard.sp_service_updated', ['name' => $serviceName]);
                    }
                } elseif ($log->description === 'audit:deleted') {
                    $type = 'service_deleted';
                    $icon = 'ti-trash';
                    $color = 'danger';
                    $text = __('dashboard.sp_service_updated', ['name' => $serviceName]);
                }
            }

            return [
                'type' => $type,
                'icon' => $icon,
                'color' => $color,
                'text' => $text,
                'secondary_text' => $secondaryText,
                'time_ago' => Carbon::parse($log->created_at)->diffForHumans(),
            ];
        })->toArray();
    }

    /**
     * Send bilingual email + in-app notification for a service request action.
     */
    private function sendActionNotification(ServicesRequests $serviceRequest, string $actionLabel): void
    {
        if (in_array(env('APP_ENV', 'local'), ['local'])) {
            return;
        }

        try {
            $founder  = $serviceRequest->user;
            $service  = $serviceRequest->service;
            $provider = $service->provider;

            $founderLang  = $founder->language ?? 'en';
            $founderName  = trim($founder->first_name_en . ' ' . $founder->last_name_en);
            $serviceName  = $founderLang === 'ar' ? ($service->name_ar ?: $service->name_en) : $service->name_en;
            $providerName = $provider->company_name ?? '';

            $params = [
                'founder_name'  => $founderName,
                'service_name'  => $serviceName,
                'provider_name' => $providerName,
            ];

            $providerMessage = $serviceRequest->admin_comment;

            if ($actionLabel === 'approved') {
                $subject  = __('admin.service_request_approved_subject', [], $founderLang);
                $bodyKey  = $providerMessage ? 'service_request_approved_body_with_message' : 'service_request_approved_body';
                $inAppKey = 'service_request_approved_inapp';
            } else {
                $subject  = __('admin.service_request_rejected_subject', [], $founderLang);
                $bodyKey  = $providerMessage ? 'service_request_rejected_body_with_message' : 'service_request_rejected_body';
                $inAppKey = 'service_request_rejected_inapp';
            }

            $bodyParams = $params;
            if ($providerMessage) {
                $bodyParams['provider_message'] = $providerMessage;
            }

            $greeting = __('admin.service_request_notification_greeting', $params, $founderLang);
            $body     = __("admin.{$bodyKey}", $bodyParams, $founderLang);
            $inApp    = __("admin.{$inAppKey}", $params, $founderLang);

            $notification = new TemplateEmail([
                $subject,
                $greeting,
                $body,
                '/dashboard/requests/mine',
                $inApp,
            ]);
            $notification->ActionText = __('admin.service_request_view_details', [], $founderLang);

            $founder->notify($notification);
        } catch (\Throwable $th) {
            // Silently fail - matches existing pattern
        }
    }

    // ========================
    // PUBLIC METHODS (ROUTES)
    // ========================

    /**
     * Render the service provider dashboard home page.
     */
    public function index()
    {
        $user = auth()->user();
        $l = App::getLocale();
        $permissions = $user->myPermissions();

        // Fetch approval status from registration record
        $registration = DB::table('service_provider_registration')
            ->where('user_id', $user->id)
            ->select('status', 'reason')
            ->first();

        $approvalStatus = 'pending';
        if ($registration) {
            if ($registration->status === 1 || $registration->status === true) {
                $approvalStatus = 'accepted';
            } elseif ($registration->status === 0 || $registration->status === false) {
                $approvalStatus = 'rejected';
            }
        }
        else
        {
            if( empty( $registration ) ) { $approvalStatus = 'accepted'; }
        }
        $rejectionReason = $registration->reason ?? '';

        $serviceIds = $this->getProviderServiceIds();

        // Summary widget data
        $summary = $this->getSummaryData($serviceIds, $permissions);

        // Services list for filter dropdown
        $services = DB::table('services')
            ->whereIn('id', !empty($serviceIds) ? $serviceIds : [0])
            ->whereNull('deleted_at')
            ->select('id', "name_{$l} as name")
            ->orderBy("name_{$l}")
            ->get();

        // Initial inbox (default filters: pending, 30 days, all services)
        $inboxRows = [];
        if ($approvalStatus === 'accepted' && in_array('dashboard.widgets_tables_service_provider_requests_inbox_view', $permissions) && !empty($serviceIds)) {
            $request = new Request(['status' => 'pending', 'period' => '30', 'service_id' => 0]);
            $rows = $this->buildInboxQuery($serviceIds, $request)->get();
            $inboxRows = $this->formatInboxRows($rows);
        }

        // Initial activity
        $activityEntries = [];
        if ($approvalStatus === 'accepted' && in_array('dashboard.widgets_tables_service_provider_recent_activity_view', $permissions)) {
            $activityEntries = $this->getRecentActivity($serviceIds);
        }

        // Permission flags for view
        $canQuickAction = in_array('service_provider_request.quick_action', $permissions);
        $canBulkAction = in_array('service_provider_request.bulk_action', $permissions);

        return view('admin.service-provider.dashboard', compact(
            'summary',
            'services',
            'inboxRows',
            'activityEntries',
            'canQuickAction',
            'canBulkAction',
            'approvalStatus',
            'rejectionReason'
        ));
    }

    /**
     * AJAX: Fetch filtered requests inbox.
     */
    public function requestsInbox(Request $request)
    {
        if (!$this->isProviderApproved()) {
            return response()->json(['status' => false, 'message' => 'Account not approved.'], 403);
        }

        $user = auth()->user();
        $permissions = $user->myPermissions();
        $serviceIds = $this->getProviderServiceIds();

        if (empty($serviceIds)) {
            return response()->json([
                'rows' => [],
                'summary' => $this->getSummaryData([], $permissions),
            ]);
        }

        $rows = $this->buildInboxQuery($serviceIds, $request)->get();
        $formattedRows = $this->formatInboxRows($rows);
        $summary = $this->getSummaryData($serviceIds, $permissions);

        return response()->json([
            'rows' => $formattedRows,
            'summary' => $summary,
        ]);
    }

    /**
     * AJAX: Accept or reject a single service request.
     */
    public function quickAction(Request $request)
    {
        if (!$this->isProviderApproved()) {
            return response()->json(['status' => false, 'message' => 'Account not approved.'], 403);
        }

        $this->validate($request, [
            'id' => 'required|integer|exists:services_requests,id',
            'action' => 'required|in:accept,reject',
            'reason' => 'required_if:action,reject|nullable|string|max:500',
        ]);

        $user = auth()->user();
        $serviceIds = $this->getProviderServiceIds();

        // Security: scope to provider's services
        $serviceRequest = ServicesRequests::where('id', $request->id)
            ->whereIn('service_id', $serviceIds)
            ->whereNull('approved')
            ->first();

        if (!$serviceRequest) {
            return response()->json([
                'status' => false,
                'title' => __('auth.unauthorized'),
                'message' => 'Service request not found or already processed.',
            ], 404);
        }

        $action = $request->input('action');
        $serviceRequest->approved = $action === 'accept' ? 'Yes' : 'No';
        $serviceRequest->admin_comment = $request->input('reason');
        $serviceRequest->admin = $user->id;
        $serviceRequest->decided_at = now();
        $serviceRequest->save();

        ServiceRequestStatusLogger::log(
            $serviceRequest->id,
            $action === 'accept' ? 'approved' : 'rejected',
            $user->id,
            $request->input('reason')
        );

        // Send email notification
        $actionLabel = $action === 'accept' ? 'approved' : 'rejected';
        $this->sendActionNotification($serviceRequest, $actionLabel);

        // Return updated data
        $permissions = $user->myPermissions();
        $summary = $this->getSummaryData($serviceIds, $permissions);

        $successMessage = $actionLabel === 'approved'
            ? __('admin.request_now_in_progress')
            : __('admin.request_has_been_rejected');

        return response()->json([
            'status' => true,
            'title' => __('dashboard.sp_action_success'),
            'message' => $successMessage,
            'summary' => $summary,
        ]);
    }

    /**
     * AJAX: Bulk accept or reject service requests.
     */
    public function bulkAction(Request $request)
    {
        if (!$this->isProviderApproved()) {
            return response()->json(['status' => false, 'message' => 'Account not approved.'], 403);
        }

        $this->validate($request, [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:services_requests,id',
            'action' => 'required|in:accept,reject',
            'reason' => 'required_if:action,reject|nullable|string|max:500',
        ]);

        $user = auth()->user();
        $serviceIds = $this->getProviderServiceIds();
        $requestedIds = $request->input('ids');
        $action = $request->input('action');

        // Security: scope to provider's pending services only
        $serviceRequests = ServicesRequests::whereIn('id', $requestedIds)
            ->whereIn('service_id', $serviceIds)
            ->whereNull('approved')
            ->get();

        if ($serviceRequests->isEmpty()) {
            return response()->json([
                'status' => false,
                'title' => 'Error',
                'message' => 'No valid pending requests found.',
            ], 404);
        }

        $processedCount = 0;
        $actionLabel = $action === 'accept' ? 'approved' : 'rejected';

        DB::transaction(function () use ($serviceRequests, $action, $request, $user, $actionLabel, &$processedCount) {
            foreach ($serviceRequests as $sr) {
                $sr->approved = $action === 'accept' ? 'Yes' : 'No';
                $sr->admin_comment = $request->input('reason');
                $sr->admin = $user->id;
                $sr->decided_at = now();
                $sr->save(); // Triggers Auditable

                ServiceRequestStatusLogger::log(
                    $sr->id,
                    $action === 'accept' ? 'approved' : 'rejected',
                    $user->id,
                    $request->input('reason')
                );

                $this->sendActionNotification($sr, $actionLabel);
                $processedCount++;
            }
        });

        $permissions = $user->myPermissions();
        $summary = $this->getSummaryData($serviceIds, $permissions);

        $successMessage = $actionLabel === 'approved'
            ? __('admin.request_now_in_progress')
            : __('admin.request_has_been_rejected');

        return response()->json([
            'status' => true,
            'title' => __('dashboard.sp_action_success'),
            'message' => $processedCount . ' ' . $successMessage,
            'count' => $processedCount,
            'summary' => $summary,
        ]);
    }

    /**
     * AJAX: Fetch recent activity feed.
     */
    public function recentActivity()
    {
        if (!$this->isProviderApproved()) {
            return response()->json(['status' => false, 'message' => 'Account not approved.'], 403);
        }

        $serviceIds = $this->getProviderServiceIds();
        $activity = $this->getRecentActivity($serviceIds);

        return response()->json([
            'activity' => $activity,
        ]);
    }

    /**
     * AJAX: Refresh all dashboard data.
     */
    public function refresh(Request $request)
    {
        if (!$this->isProviderApproved()) {
            return response()->json(['status' => false, 'message' => 'Account not approved.'], 403);
        }

        $user = auth()->user();
        $permissions = $user->myPermissions();
        $serviceIds = $this->getProviderServiceIds();

        $summary = $this->getSummaryData($serviceIds, $permissions);

        $inboxRows = [];
        if (in_array('dashboard.widgets_tables_service_provider_requests_inbox_view', $permissions) && !empty($serviceIds)) {
            $rows = $this->buildInboxQuery($serviceIds, $request)->get();
            $inboxRows = $this->formatInboxRows($rows);
        }

        $activity = [];
        if (in_array('dashboard.widgets_tables_service_provider_recent_activity_view', $permissions)) {
            $activity = $this->getRecentActivity($serviceIds);
        }

        return response()->json([
            'summary' => $summary,
            'inbox' => $inboxRows,
            'activity' => $activity,
        ]);
    }
}
