<?php

namespace App\Http\Controllers;

use App\Organization;
use App\OrganizationLandingPage;
use App\Services\OrganizationCustomSectionService;
use Illuminate\Http\Request;

class PublicLandingController extends Controller
{
    protected $customSectionService;

    public function __construct(OrganizationCustomSectionService $customSectionService)
    {
        $this->customSectionService = $customSectionService;
    }
    /**
     * Display the public landing page for an organization.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show(Request $request, $slug)
    {
        // Handle language query parameter
        if ($request->query('lang')) {
            return redirect(url()->current());
        }

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

        return view('public.organization-landing', [
            'organization' => $organization,
            'landingPage' => $landingPage,
            'articlesWithUrls' => $articlesWithUrls,
            'partnersWithUrls' => $partnersWithUrls,
            'customSections' => $customSections,
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
