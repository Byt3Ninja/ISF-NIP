<?php

namespace App\Http\Controllers;

use App\User;
use App\ServicesRequests;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $l = App::getLocale();
        $summary = [
            'totalServices' => 0,
            'activeServices' => 0,
            'requests30Days' => 0,
            'pendingRequests' => 0,
            'medianResponseTime' => null,
            'completionRate' => null,
            'extras' => [
                'totalRequests' => 0,
                'chartServices' => [],
                'topService'    => null,
                'rvLabels'      => '[]',
                'rvData'        => '[]',
                'sdColors'      => '[]',
                'sdDimColors'   => '[]',
                'sdData'        => '[]',
                'sdLabels'      => '[]'
            ]
        ];

        if (empty($serviceIds)) {
            return $summary;
        }

        $base_services = DB::table('services')
                ->whereIn('id', $serviceIds)
                ->whereNull('deleted_at')
                ->get();

        $services_requests = DB::table('services_requests as sr')
                ->select([
                    'sr.id',
                    'sr.service_id',
                    'sr.user_id',
                    'sr.comment',
                    'sr.date_time',
                    'sr.approved',
                    'sr.decided_at',
                    'sr.rate',
                    'sr.rate_comment',
                    'sr.review_title',
                    'sr.review_body',
                    'sr.reviewed_at',
                    'sr.admin_comment',
                    'sr.admin',
                    'sr.done',
                    'sr.created_at',
                    'sr.updated_at',
                    'sr.deleted_at',
                    's.name_en',
                    's.name_ar',
                ])
                ->leftJoin('services as s', 'sr.service_id', '=', 's.id')
                ->whereIn('sr.service_id', $serviceIds)
                ->whereNull('sr.deleted_at')
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

        if (in_array('dashboard.widgets_cards_service_provider_completion_rate_view', $permissions)) {
            $requestsCount = $services_requests->count();
            $summary['completionRate'] = $requestsCount > 0 ? ( round( ( $services_requests->where('done', 1)->count() / $services_requests->count() ) * 100 ) ) : 0;
        }

        $volumes = $services_requests->map(function ($r) {
            $r->weekday = Carbon::parse($r->created_at)->format('D');
            return $r;
        });
        
        $requestVolume = [
            ['day' => $l === 'ar' ? 'السبت' : 'Sat', 'value' => $volumes->where('weekday', 'Sat')->count()],
            ['day' => $l === 'ar' ? 'الأحد' : 'Sun', 'value' => $volumes->where('weekday', 'Sun')->count()],
            ['day' => $l === 'ar' ? 'الإثنين' : 'Mon', 'value' => $volumes->where('weekday', 'Mon')->count()],
            ['day' => $l === 'ar' ? 'الثلاثاء' : 'Tue', 'value' => $volumes->where('weekday', 'Tue')->count()],
            ['day' => $l === 'ar' ? 'الأربعاء' : 'Wed', 'value' => $volumes->where('weekday', 'Wed')->count()],
            ['day' => $l === 'ar' ? 'الخميس' : 'Thu', 'value' => $volumes->where('weekday', 'Thu')->count()],
            ['day' => $l === 'ar' ? 'الجمعة' : 'Fri', 'value' => $volumes->where('weekday', 'Fri')->count()],
        ];

        $available_colors = [
            ['color' => '#7F77DD', 'dim' => '#C8C4EE'],
            ['color' => '#1D9E75', 'dim' => '#9FE1CB'],
            ['color' => '#EF9F27', 'dim' => '#FAD89A'],
            ['color' => '#5DCAA5', 'dim' => '#A8E4D5'],
            ['color' => '#E5616F', 'dim' => '#F6BEC4'],
            ['color' => '#3B9AD9', 'dim' => '#AED7F0'],
            ['color' => '#C778DD', 'dim' => '#E8C4EF'],
            ['color' => '#E07A3F', 'dim' => '#F4C8A9'],
            ['color' => '#4FB286', 'dim' => '#B9E2D0'],
            ['color' => '#D9B23B', 'dim' => '#F0E0AE'],
            ['color' => '#5C77E0', 'dim' => '#BCC6F4'],
            ['color' => '#9E5DCA', 'dim' => '#D5BCE8'],
            ['color' => '#27A8B0', 'dim' => '#A9DEE2'],
            ['color' => '#E0617F', 'dim' => '#F4BECB'],
        ];

        $totalRequests = $services_requests->count();
        $chartServices = $services_requests->groupBy('service_id')->map(function ($r) use ($l, $totalRequests) {
            $count = $r->count();
            return ['name' => $r->first()->{'name_' . $l}, 'count' => $count, 'color' => '', 'dim' => '', 'pct' =>  $totalRequests > 0 ? ( round($count / $totalRequests * 100) ) : 0 ];
        })->values()->map(function ($r, $index) use ($available_colors) {
            $palette = $available_colors[$index % count($available_colors)];
            $r['color'] = $palette['color'];
            $r['dim']   = $palette['dim'];
            return $r;
        });

        $chartServices = $chartServices->toArray();

        $topService = collect($chartServices)->sortByDesc('count')->first();
        $rvLabels = json_encode(array_column($requestVolume, 'day'));
        $rvData = json_encode(array_column($requestVolume, 'value'));
        $sdColors = json_encode(array_column($chartServices, 'color'));
        $sdDimColors = json_encode(array_column($chartServices, 'dim'));
        $sdData = json_encode(array_column($chartServices, 'count'));
        $sdLabels = json_encode(array_column($chartServices, 'name'));

        $summary['extras'] = [
            'totalRequests' => $totalRequests,
            'chartServices' => $chartServices,
            'topService'    => $topService,
            'rvLabels'      => $rvLabels,
            'rvData'        => $rvData,
            'sdColors'      => $sdColors,
            'sdDimColors'   => $sdDimColors,
            'sdData'        => $sdData,
            'sdLabels'      => $sdLabels
        ];
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
                'details_url' => route('service_requests.list_all', ['open_request' => $row->id]),
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

        $srSubjectTypes = array_map(fn($id) => "App\\ServicesRequests#{$id}", $srIds);
        $serviceSubjectTypes = array_map(fn($id) => "App\\Service#{$id}", $serviceIds);
        $allowedSubjectTypes = array_merge($srSubjectTypes, $serviceSubjectTypes);

        $logs = DB::table('audit_logs')
            ->whereIn('subject_type', $allowedSubjectTypes)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $srIdsInLogs = [];
        $serviceIdsInLogs = [];
        foreach ($logs as $log) {
            if (str_contains($log->subject_type, 'ServicesRequests')) {
                $srIdsInLogs[] = $log->subject_id;
            } elseif (str_contains($log->subject_type, 'Service#')) {
                $serviceIdsInLogs[] = $log->subject_id;
            }
        }

        $serviceRequestsMap = [];
        if (!empty($srIdsInLogs)) {
            $serviceRequestsMap = DB::table('services_requests')
                ->join('services', 'services_requests.service_id', '=', 'services.id')
                ->join('users', 'services_requests.user_id', '=', 'users.id')
                ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                ->whereIn('services_requests.id', $srIdsInLogs)
                ->select([
                    'services_requests.id',
                    "services.name_{$l} as service_name",
                    "services.name_en as service_name_en",
                    "companies.name_{$l} as startup_name",
                    "companies.name_en as startup_name_en"
                ])
                ->get()
                ->keyBy('id')
                ->toArray();
        }

        $servicesMap = [];
        if (!empty($serviceIdsInLogs)) {
            $servicesMap = DB::table('services')
                ->whereIn('id', $serviceIdsInLogs)
                ->select([
                    'id',
                    "name_{$l} as name",
                    "name_en as name_en"
                ])
                ->get()
                ->keyBy('id')
                ->toArray();
        }

        return $logs->map(function ($log) use ($l, $serviceRequestsMap, $servicesMap) {
            $properties = json_decode($log->properties, true) ?? [];
            $type = 'info';
            $icon = 'ti-info-circle';
            $color = 'info';
            $text = '';
            $secondaryText = '';

            $isServiceRequest = str_contains($log->subject_type, 'ServicesRequests');
            $isService = str_contains($log->subject_type, 'Service#') && !$isServiceRequest;

            if ($isServiceRequest) {
                $srData = $serviceRequestsMap[$log->subject_id] ?? null;
                $serviceName = $srData ? ($srData->service_name ?? $srData->service_name_en) : '';
                $startupName = $srData ? ($srData->startup_name ?? $srData->startup_name_en) : '';

                if ($log->description === 'audit:created') {
                    $type = 'new_request';
                    $icon = 'ti-mail-forward';
                    $color = 'primary';
                    $text = __('dashboard.sp_new_request', ['startup' => $startupName]);
                } elseif ($log->description === 'audit:updated') {
                    $approved = $properties['approved'] ?? null;
                    if ($approved === 'Yes') {
                        $type = 'accepted';
                        $icon = 'ti-circle-check';
                        $color = 'success';
                        $text = __('dashboard.sp_request_accepted', ['service' => $serviceName]);
                    } elseif ($approved === 'No') {
                        $type = 'rejected';
                        $icon = 'ti-circle-x';
                        $color = 'danger';
                        $text = __('dashboard.sp_request_rejected', ['service' => $serviceName]);
                    } else {
                        $type = 'updated';
                        $icon = 'ti-edit';
                        $color = 'warning';
                        $text = __('dashboard.sp_service_updated', ['name' => $serviceName]);
                    }
                }
            } elseif ($isService) {
                $sData = $servicesMap[$log->subject_id] ?? null;
                $serviceName = $sData ? ($sData->name ?? $sData->name_en) : '';
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
        try {
            $founder  = $serviceRequest->user;
            if (!$founder) {
                return;
            }
            $service  = $serviceRequest->service;
            $provider = $service->provider;

            $founderLang  = method_exists($founder, 'preferredLocale') ? $founder->preferredLocale() : ($founder->language ?? 'en');
            $firstName    = $founder->{"first_name_{$founderLang}"} ?? $founder->first_name_en;
            $lastName     = $founder->{"last_name_{$founderLang}"} ?? $founder->last_name_en;
            $founderName  = trim($firstName . ' ' . $lastName);
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
            ], $founderLang);
            $notification->ActionText = __('admin.service_request_view_details', [], $founderLang);

            $founder->notify($notification);
        } catch (\Throwable $th) {
            Log::error('Failed to send service request action notification', [
                'error'      => $th->getMessage(),
                'request_id' => $serviceRequest->id,
                'action'     => $actionLabel,
            ]);
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
        $extras = $summary['extras'];
        unset($summary['extras']);

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

        return view('admin.service-provider.dashboard', array_merge(compact(
            'summary',
            'services',
            'inboxRows',
            'activityEntries',
            'canQuickAction',
            'canBulkAction',
            'approvalStatus',
            'rejectionReason'
        ), $extras));
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
