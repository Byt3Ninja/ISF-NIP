<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ServiceProviderProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the service provider profile page.
     */
    public function show($id)
    {
        $l = app()->getLocale();
        $user = auth()->user();
        $request = request();
        $orgIds = $user->organizations_ids;
        $service_id = $request->query('service_id', null);

        // 1. Fetch service provider with country and user data
        $provider = DB::table('service_providers as sp')
            ->leftJoin('countries as c', 'c.id', '=', 'sp.country_id')
            ->leftJoin('users as u', 'u.id', '=', 'sp.user_id')
            ->where('sp.id', $id)
            ->select([
                'sp.*',
                "c.name_{$l} as country_name",
                'u.id as owner_user_id',
                'u.img_url as user_img_url',
                'u.created_at as user_joined_at',
            ])
            ->first();

        if (!$provider) {
            abort(404);
        }

        $my_services = [];
        if( $user->hasRole('registered') )
        {
            $my_services = DB::table('organization_service')->whereIn('organization_id', $orgIds)
                ->pluck('service_id')
                ->toArray();
        }

        // 2. Get all service IDs for this provider (including inactive, for reviews)
        $serviceIds = DB::table('services')
            ->where('service_provider_id', $id)
            ->whereNull('deleted_at')
            ->when(!empty( $service_id ), function ($query) use ($service_id) {
                $query->where('id', $service_id);
            })
            ->when( count( $my_services ) > 0, function ($query) use ($my_services) {
                $query->whereIn('id', $my_services);
            })
            ->pluck('id')
            ->toArray();

        // 3. Compute overall rating and review count
        $ratingData = (object) ['avg_rating' => 0, 'review_count' => 0];
        if (!empty($serviceIds)) {
            $ratingData = DB::table('services_requests')
                ->whereIn('service_id', $serviceIds)
                ->whereNotNull('rate')
                ->whereNull('deleted_at')
                ->selectRaw('COALESCE(ROUND(AVG(rate), 1), 0) as avg_rating, COUNT(*) as review_count')
                ->first();
        }

        $provider->avg_rating = $ratingData->avg_rating ?? 0;
        $provider->review_count = $ratingData->review_count ?? 0;

        // 4. Fetch active services with per-service review count
        $services = DB::table('services as s')
            ->where('s.service_provider_id', $id)
            ->where('s.status', true)
            ->whereNull('s.deleted_at')
            ->leftJoin('service_categories as sc', 'sc.id', '=', 's.service_category_id')
            ->when( count( $my_services ) > 0, function ($query) use ($my_services) {
                $query->whereIn('s.id', $my_services);
            })
            ->select([
                's.id',
                "s.name_{$l} as name",
                's.name_en',
                "s.description_{$l} as description",
                's.img_url',
                's.price',
                's.hide_price',
                's.currency',
                's.status',
                's.avg_rating',
                's.created_at',
                's.service_category_id as category_id',
                "sc.name_{$l} as category_name",
            ])
            ->OrderBy('s.created_at', 'desc')->paginate(9)->withQueryString()->appends(['tab' => 'services']);
        // Batch-fetch review counts per service
        if ($services->isNotEmpty()) {
            $serviceCounts = DB::table('services_requests')
                ->whereIn('service_id', $services->pluck('id'))
                ->whereNotNull('rate')
                ->whereNull('deleted_at')
                ->groupBy('service_id')
                ->selectRaw('service_id, COUNT(*) as review_count')
                ->pluck('review_count', 'service_id');

            $services = $services->through(function ($s) use ($serviceCounts) {
                $s->review_count = $serviceCounts[$s->id] ?? 0;
                return $s;
            });
        }

        // 5. Fetch portfolios (only if section enabled)
        $portfolios = collect();
        if ($provider->show_portfolio) {
            $portfolios = DB::table('service_provider_portfolios')
                ->where('service_provider_id', $id)
                ->whereNotNull('media_url')
                ->whereNotNull('title')
                ->whereNotNull('description')
                ->where('media_url', '!=', '')
                ->where('title', '!=', '')
                ->where('description', '!=', '')
                ->orderBy('order')
                ->get();

            $portfolios = $portfolios->map(function ($item) {
                if ($item->media_url && !str_starts_with($item->media_url, 'http')) {
                    if (Storage::disk('public')->exists($item->media_url)) {
                        $item->media_url = Storage::disk('public')->url($item->media_url);
                    } else {
                        $item->media_url = '/191014/' . ltrim($item->media_url, '/');
                    }
                }
                return $item;
            });
        }

        // 6. Fetch clients (only if section enabled)
        $clients = collect();
        if ($provider->show_clients) {
            $clients = DB::table('service_provider_clients')
                ->where('service_provider_id', $id)
                ->orderBy('order')
                ->get();
        }

        // 7. Fetch initial reviews (page 1, 6 items, most recent)
        $reviews = $this->fetchReviews($serviceIds, null, null, 'recent', 1, 6);

        // 8. Service filter options for the review dropdown
        $serviceFilterOptions = DB::table('services')
            ->where('service_provider_id', $id)
            ->where('status', true)
            ->whereNull('deleted_at')
            ->whereIn('id', $reviews['data']->pluck('service_id')->unique()->values()->toArray())
            ->select('id', "name_{$l} as name", 'name_en')
            ->get();

        // 9. Rating distribution for filter buttons
        $ratingDistribution = [];
        if (!empty($serviceIds)) {
            $ratingDistribution = DB::table('services_requests')
                ->whereIn('service_id', $serviceIds)
                ->whereNotNull('rate')
                ->whereNull('deleted_at')
                ->selectRaw('FLOOR(rate) as star, COUNT(*) as count')
                ->groupBy('star')
                ->pluck('count', 'star')
                ->toArray();
        }

        // 10. Decode tags
        $tags = [];
        /* if (!empty($provider->tags)) {
            $decoded = json_decode($provider->tags, true);
            $tags = is_array($decoded) ? $decoded : [];
        }*/
        $tags = $services->pluck('category_name')->map(function ($name) {
            return str_replace(['   ', '  '], '' , $name);
        })->unique()->values()->toArray();

        // 11. Build stats array
        $stats = [];
        if ($provider->show_stats) {
            for ($i = 1; $i <= 4; $i++) {
                $label = $provider->{"stat{$i}_label"};
                $value = $provider->{"stat{$i}_value"};
                if ($label && $value) {
                    $stats[] = ['label' => $label, 'value' => $value];
                }
            }
        }

        // 12. Role-based flags
        $isOwner = ((int) $user->id === (int) $provider->owner_user_id);
        $isFounder = $user->hasRole('registered');
        $isAdmin = $user->hasAnyRole(['Admin', 'tenantAdmin']);
        $isServiceProvider = $user->hasRole('serviceProvider');

        $canEdit = $isOwner || $isAdmin;
        $canSchedule = ($isFounder && !$isOwner) || ($isOwner && $isServiceProvider);
        $canRequestService = $isFounder && !$isOwner;
        $showDateStatus = !$isFounder;

        // 13. Image handling (follows User::image_handler pattern)
        $providerLogo = $provider->img_url
            ? (str_starts_with($provider->img_url, 'https://') ? $provider->img_url : '/191014/' . $provider->img_url)
            : '/images/user-placeholder.jpeg';

        $heroBanner = $provider->hero_image
            ? (str_starts_with($provider->hero_image, 'https://') ? $provider->hero_image : '/191014/' . $provider->hero_image)
            : '/vuexy/assets/img/pages/profile-banner.png';

        // 14. Joined date
        $joinedDate = $provider->user_joined_at
            ? Carbon::parse($provider->user_joined_at)->translatedFormat('F Y')
            : null;

        // 15. Operating mode label
        $operatingModeLabel = $provider->operating_mode ?? '';

        $active_tab = 'overview';
        if( $request->has('tab') ) { $active_tab = $request->tab; }

        return view('admin.clients.provider-profile', compact(
            'provider',
            'services',
            'active_tab',
            'portfolios',
            'clients',
            'reviews',
            'serviceFilterOptions',
            'ratingDistribution',
            'tags',
            'stats',
            'operatingModeLabel',
            'providerLogo',
            'heroBanner',
            'joinedDate',
            'canEdit',
            'canSchedule',
            'canRequestService',
            'showDateStatus',
            'isOwner',
            'serviceIds'
        ));
    }

    /**
     * AJAX endpoint for filtered/sorted/paginated reviews.
     */
    public function reviews($id, Request $request)
    {
        $l = app()->getLocale();

        $rating = $request->input('rating', 'all');
        $serviceId = $request->input('service_id', null);
        $sort = $request->input('sort', 'recent');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 6;

        // Get all service IDs for this provider (including inactive, for reviews)
        $serviceIds = DB::table('services')
            ->where('service_provider_id', $id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();

        if (empty($serviceIds)) {
            return response()->json([
                'html' => view('admin.clients.partials._reviews-list', ['reviews' => collect(), 'l' => $l])->render(),
                'pagination' => '',
                'has_more' => false,
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1,
            ]);
        }

        $result = $this->fetchReviews($serviceIds, $rating, $serviceId, $sort, $page, $perPage);

        $html = view('admin.clients.partials._reviews-list', ['reviews' => $result['data'], 'l' => $l])->render();
        $pagination = view('admin.clients.partials._reviews-pagination', [
            'currentPage' => $page,
            'lastPage' => $result['last_page'],
        ])->render();

        return response()->json([
            'html' => $html,
            'pagination' => $pagination,
            'has_more' => $result['has_more'],
            'total' => $result['total'],
            'current_page' => $page,
            'last_page' => $result['last_page'],
        ]);
    }

    /**
     * Fetch reviews with filters, sorting, and pagination.
     */
    private function fetchReviews(array $serviceIds, $rating, $serviceId, $sort, $page, $perPage)
    {
        $l = app()->getLocale();

        if (empty($serviceIds)) {
            return ['data' => collect(), 'has_more' => false, 'total' => 0, 'last_page' => 1];
        }

        $query = DB::table('services_requests as sr')
            ->join('services as s', 'sr.service_id', '=', 's.id')
            ->join('users as u', 'sr.user_id', '=', 'u.id')
            ->leftJoin('companies as co', 'co.id', '=', 'u.company_id')
            ->whereIn('sr.service_id', $serviceIds)
            ->whereNotNull('sr.rate')
            ->whereNotNull('sr.reviewed_at')
            ->whereNull('sr.deleted_at');

        // Rating filter
        if ($rating && $rating !== 'all') {
            $ratingVal = (int) $rating;
            $query->whereRaw('FLOOR(sr.rate) = ?', [$ratingVal]);
        }

        // Service filter
        if ($serviceId && $serviceId !== 'all') {
            $query->where('sr.service_id', (int) $serviceId);
        }

        // Total count before pagination
        $total = (clone $query)->count();

        // Sort
        switch ($sort) {
            case 'oldest':
                $query->orderBy('sr.reviewed_at', 'asc');
                break;
            case 'highest':
                $query->orderBy('sr.rate', 'desc')->orderBy('sr.reviewed_at', 'desc');
                break;
            case 'lowest':
                $query->orderBy('sr.rate', 'asc')->orderBy('sr.reviewed_at', 'desc');
                break;
            case 'recent':
            default:
                $query->orderBy('sr.reviewed_at', 'desc');
                break;
        }

        $data = $query
            ->select([
                'sr.id',
                'sr.rate',
                'sr.rate_comment',
                'sr.review_title',
                'sr.review_body',
                'sr.reviewed_at',
                'sr.user_id as reviewer_user_id',
                's.id as service_id',
                "s.name_{$l} as service_name",
                's.name_en as service_name_en',
                DB::raw("CONCAT(COALESCE(u.first_name_{$l}, u.first_name_en, ''), ' ', COALESCE(u.last_name_{$l}, u.last_name_en, '')) as reviewer_name"),
                'u.img_url as reviewer_img',
                'u.created_at as reviewer_joined_at',
                'co.id as company_id',
                "co.name_{$l} as company_name",
                'co.name_en as company_name_en',
            ])
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;
        $hasMore = $page < $lastPage;

        return [
            'data' => $data,
            'has_more' => $hasMore,
            'total' => $total,
            'last_page' => $lastPage,
        ];
    }
}
