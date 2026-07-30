<?php

namespace App\Http\Controllers;

use App\Organization;
use App\OrganizationLandingPage;
use App\Services\OrganizationCustomSectionService;
use App\Services\ProgramLandingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublicLandingController extends Controller
{
    protected $customSectionService;
    protected $programLandingService;

    public function __construct(
        OrganizationCustomSectionService $customSectionService,
        ProgramLandingService $programLandingService
    ) {
        $this->customSectionService = $customSectionService;
        $this->programLandingService = $programLandingService;
    }
    /**
     * Display the public landing page for an organization.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show(Request $request, $slug)
    {
        // Find organization by slug
        $organization = Organization::where('slug', $slug)
            ->with(['landingPage', 'organizationType', 'industries', 'city', 'country'])
            ->first();

        // Return 404 if organization doesn't exist
        if (!$organization) {
            abort(404);
        }

        $tokenized = md5('preview-org-' . $organization->id . '-gl' );
        // Check if preview mode
        $isPreview = $request->has('preview') && $request->get('preview') == $tokenized;

        // If not preview mode, check visibility requirements
        if (!$isPreview) {
            // Draft organizations are not publicly visible
            if ($organization->is_draft) {
                abort(404);
            }

            // Inactive organizations are not publicly visible
            if (!$organization->status) {
                abort(404);
            }
        } else {
            // Preview mode requires authentication
            if (!auth()->check()) {
                abort(404);
            }

            // Check if user has permission to preview
            $user = auth()->user();
            if (!$user->ableTo('organization.wizard.edit')) {
                abort(403);
            }
        }

        // Get landing page data
        $landingPage = $organization->landingPage;
        if( $landingPage->landing_enabled == true && $isPreview == true ) {
            $isPreview = false;
        }

        // If no landing page exists, create empty data
        if (!$landingPage) {
            $landingPage = new OrganizationLandingPage();
        }

        // Check if landing page is enabled (unless in preview mode)
        if (!$isPreview && $landingPage->id && !$landingPage->landing_enabled) {
            abort(404);
        }

        // Get articles with URLs
        $articlesWithUrls = $landingPage->id ? $landingPage->getArticlesWithUrls() : [];

        // Get partners with URLs
        $partnersWithUrls = $landingPage->id ? $landingPage->getPartnersWithUrls() : [];

        // Get locale and direction
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';

        // Get theme (default to gradient)
        $theme = $landingPage->landing_theme ?? 'gradient';

        // Get custom sections filtered by visibility for current user
        $currentUser = auth()->user();
        $customSections = $this->customSectionService->getVisibleSections($organization->id, $currentUser);

        // Get landing programs
        $orderBy = $landingPage->programs_order_by ?? 'newest';
        $landingPrograms = $landingPage->programs_enabled
            ? $this->programLandingService->getLandingPrograms($organization->id, $orderBy)
            : collect();

        $socialLinks = [
            'x' => $organization->twitter_handle,
            'linkedin' => $organization->linkedin_company_url,
            'facebook' => $organization->facebook_page_url,
            'instagram' => $organization->instagram_profile_url,
            'tiktok' => $organization->tiktok_url,
            'youtube' => $organization->youtube_channel_url
        ];
        $socialLinks = array_filter($socialLinks);
        $mapQuery = $organization->address ?: $organization->name_en;
        $mapEmbedUrl = 'https://www.google.com/maps?q=' . urlencode($mapQuery) . '&output=embed&z=14';
        $color_palette = ['#fff', '#ebebed'];
        $sections = [
            ['id' => 'programs', 'label' => __('admin.Programs'), 'visible' => $landingPage->programs_enabled && isset($landingPrograms) && $landingPrograms->count() > 0],
            ['id' => 'how-it-works', 'label' => __('admin.How It Works'), 'visible' => $landingPage->how_it_works_enabled && !empty($landingPage->process_steps)],
            ['id' => 'achievements', 'label' => __('admin.Achievements & Impact'), 'visible' => $landingPage->achievements_enabled && !empty($landingPage->metrics)],
            ['id' => 'media', 'label' => __('admin.Media & Recognition'), 'visible' => $landingPage->media_enabled && !empty($articlesWithUrls)],
            ['id' => 'partners', 'label' => __('admin.Partners'), 'visible' => $landingPage->partners_enabled && !empty($partnersWithUrls)],
        ];

        if (!empty($customSections)) {
            foreach ($customSections as $customSection) {
                $sections[] = [
                    'id' => 'custom-' . $customSection->id,
                    'label' => $customSection->title,
                    'visible' => !empty($customSection->content),
                ];
            }
        }
        $sections = collect($sections)->filter(function ($section) {
            return $section['visible'];
        })->values()->toArray();
        foreach ($sections as $index => $section) {
            $sections[$index]['color'] = $color_palette[$index % count($color_palette)];
        }

        $colors = ['warning', 'success', 'danger', 'info', 'primary', 'secondary', 'dark', 'success', 'primary', 'danger', 'info', 'warning', 'secondary'];

        $now = now();
        $l = app()->getLocale();
        // Application status + CTA
        $landingPrograms = $landingPrograms->map(function ($program) use ($now, $l) {
            $firstFormStep = $program->steps()->where('stype', 1)->orderBy('arr', 'asc')->first();
            if ($firstFormStep)
            {
                $stepStart = $firstFormStep->from ? Carbon::parse($firstFormStep->from) : null;
                $stepEnd = $firstFormStep->to ? Carbon::parse($firstFormStep->to) : null;

                $isOpen = ($stepStart === null || $now->gte($stepStart)) && ($stepEnd === null || $now->lte($stepEnd));
                $isUpcoming = $stepStart !== null && $now->lt($stepStart);
                $isClosed = $stepEnd !== null && $now->gt($stepEnd);
            }
            else
            {
                $isOpen = false;
                $isUpcoming = false;
                $isClosed = true;
                $stepStart = null;
                $stepEnd = null;
            }

            if ($isOpen)
            {
                $statusClass = 'bg-label-success';
                $dotColor = 'success';
                $statusIcon = 'hourglass';
                $statusLabel = $l == 'ar'
                    ? "مفتوح حتى " . ($stepEnd ? $stepEnd->format('Y-m-d') : '') . "."
                    : "Open Till " . ($stepEnd ? $stepEnd->format('Y-m-d') : '') . ".";
                $btnText = $l == 'ar' ? 'تقدم الآن.' : 'Apply Now.';
            }
            elseif ($isUpcoming)
            {
                $statusClass = 'bg-label-warning';
                $dotColor = 'warning';
                $statusIcon = 'hourglass-empty';
                $statusLabel = $l == 'ar'
                    ? "سيفتح في " . ($stepStart ? $stepStart->format('Y-m-d') : '') . "."
                    : "Will open in " . ($stepStart ? $stepStart->format('Y-m-d') : '') . ".";
                $btnText = $l == 'ar' ? 'التقديم يفتح قريباً' : 'Applications Open Soon';
            }
            else
            {
                $statusClass = 'bg-label-danger';
                $dotColor = 'danger';
                $statusIcon = 'lock';
                $statusLabel = $l == 'ar' ? "مغلق." : "Closed.";
                $btnText = $l == 'ar' ? 'التقديم مغلق.' : 'Applications Closed.';
            }

            $program->isOpen = $isOpen;
            $program->isUpcoming = $isUpcoming;
            $program->isClosed = $isClosed;
            $program->statusClass = $statusClass;
            $program->dotColor = $dotColor;
            $program->statusIcon = $statusIcon;
            $program->statusLabel = $statusLabel;
            $program->btnText = $btnText;

            return $program;
        });
        return view('public.organization-landing', [
            'organization' => $organization,
            'landingPage' => $landingPage,
            'socialLinks' => $socialLinks,
            'mapEmbedUrl' => $mapEmbedUrl,
            'articlesWithUrls' => $articlesWithUrls,
            'partnersWithUrls' => $partnersWithUrls,
            'customSections' => $customSections,
            'sections' => $sections,
            'colors' => $colors,
            'landingPrograms' => $landingPrograms,
            'locale' => $locale,
            'l' => $locale,
            'isRtl' => $isRtl,
            'isPreview' => $isPreview,
            'theme' => $theme,
        ]);
    }

    /**
     * Handle contact form submission for an organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function submitContact(Request $request, $slug)
    {
        // Find organization by slug
        $organization = Organization::where('slug', $slug)->first();

        // Return 404 if organization doesn't exist
        if (!$organization) {
            abort(404);
        }

        // Validate the form data
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Here you can save to database or send email
        // For now, let's send an email to the organization's contact email
        try {
            \Mail::raw("New contact message from {$validated['fullname']} ({$validated['email']}):\n\n{$validated['message']}", function ($message) use ($organization, $validated) {
                $message->to($organization->contact_email ?? 'admin@example.com')
                        ->subject("Contact Form Submission from {$organization->name_en} Landing Page");
            });

            return back()->with('success', 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send message. Please try again later.');
        }
    }
}
