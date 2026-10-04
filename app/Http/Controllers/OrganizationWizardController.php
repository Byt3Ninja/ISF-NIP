<?php

namespace App\Http\Controllers;

use App\Organization;
use App\OrganizationTypes;
use App\StaticCategory;
use App\Country;
use App\State;
use App\City;
use App\Http\Requests\OrganizationStep1Request;
use App\Http\Requests\OrganizationStep2Request;
use App\Http\Requests\OrganizationStep4Request;
use App\Http\Requests\OrganizationStep5Request;
use App\Http\Requests\CreateOrganizationManagerRequest;
use App\Services\OrganizationWizardService;
use App\Services\OrganizationManagerService;
use App\Services\OrganizationLandingPageService;
use App\Services\OrganizationCustomSectionService;
use App\Services\ProgramLandingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationWizardController extends Controller
{
    public function __construct(
        protected OrganizationWizardService $wizardService,
        protected OrganizationManagerService $managerService,
        protected OrganizationLandingPageService $landingPageService,
        protected OrganizationCustomSectionService $customSectionService,
        protected ProgramLandingService $programLandingService
    ) {}

    /**
     * Show the wizard form for creating a new organization.
     */
    public function create()
    {
        $organizationTypes = OrganizationTypes::all();
        $industries = StaticCategory::all();
        $countries = Country::all();
        $states = collect();
        $cities = collect();

        return view('admin.organizations.wizard.create', compact(
            'organizationTypes',
            'industries',
            'countries',
            'states',
            'cities'
        ));
    }

    /**
     * Show the wizard form for editing an existing organization.
     */
    public function edit($id)
    {
        $organization = Organization::with(['organizationType', 'industries', 'country', 'state', 'city', 'assignedUsers'])->findOrFail($id);
        $organizationTypes = OrganizationTypes::all();
        $industries = StaticCategory::all();
        $countries = Country::all();

        // Pre-load states and cities if organization already has selections
        $states = $organization->country_id
            ? State::where('country_id', $organization->country_id)->get()
            : collect();
        $cities = $organization->state_id
            ? City::where('state_id', $organization->state_id)->get()
            : collect();

        // Get assigned managers for step 3
        $assignedManagers = $organization->assignedUsers()->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->full_name ?? ($user->first_name_en . ' ' . $user->last_name_en),
                'email' => $user->email,
            ];
        });

        return view('admin.organizations.wizard.create', compact(
            'organization',
            'organizationTypes',
            'industries',
            'countries',
            'states',
            'cities',
            'assignedManagers'
        ));
    }

    /**
     * Store step 1 data for a new organization.
     */
    public function store(OrganizationStep1Request $request)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.create')) {
            return abort(401);
        }

        $data = $request->validated();

        // Add file if uploaded
        if ($request->hasFile('img_url')) {
            $data['img_url'] = $request->file('img_url');
        }

        $result = $this->wizardService->create($data);

        if ($result['success']) {
            return redirect()
                ->route('organization.wizard.edit', ['id' => $result['data']->id, 'step' => 2])
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Update step 1 data for an existing organization.
     */
    public function updateStep1(OrganizationStep1Request $request, $id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $data = $request->validated();

        // Add file if uploaded
        if ($request->hasFile('img_url')) {
            $data['img_url'] = $request->file('img_url');
        }

        // Add revert_to_draft flag if present
        if ($request->has('revert_to_draft')) {
            $data['revert_to_draft'] = true;
        }

        $result = $this->wizardService->updateStep1($id, $data);

        if ($result['success']) {
            $redirectTo = $request->input('redirect_to', 'next');
            $step = $redirectTo === 'same' ? 1 : 2;

            return redirect()
                ->route('organization.wizard.edit', ['id' => $result['data']->id, 'step' => $step])
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Update step 2 data (Contact Information & Social Media).
     */
    public function updateStep2(OrganizationStep2Request $request, $id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $result = $this->wizardService->updateStep2($id, $request->validated());

        if ($result['success']) {
            $redirectTo = $request->input('redirect_to', 'next');
            $step = $redirectTo === 'same' ? 2 : 3;

            return redirect()
                ->route('organization.wizard.edit', ['id' => $result['data']->id, 'step' => $step])
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Get managers for AJAX DataTable.
     */
    public function getManagers(Request $request, $id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $filters = [
            'search_term' => $request->input('search_term'),
            'start' => $request->input('start', 0),
            'length' => $request->input('length', 5),
        ];

        $result = $this->managerService->getAvailableManagers($id, $filters);

        if (!$result['success']) {
            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $result['message'],
            ]);
        }

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $result['data']['total'],
            'recordsFiltered' => $result['data']['filtered'],
            'data' => $result['data']['records'],
        ]);
    }

    /**
     * Update step 3 data (Organization Managers).
     */
    public function updateStep3(Request $request, $id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        // Validate at least one manager
        $request->validate([
            'manager_ids' => 'required|array|min:1',
            'manager_ids.*' => 'exists:users,id',
        ], [
            'manager_ids.required' => __('admin.Please select or create at least one organization manager'),
            'manager_ids.min' => __('admin.Please select or create at least one organization manager'),
        ]);

        $result = $this->managerService->assignManagers($id, $request->manager_ids);

        if ($result['success']) {
            return redirect()
                ->route('organization.wizard.edit', ['id' => $result['data']->id, 'step' => 3])
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Create a new manager via AJAX.
     */
    public function createManager(CreateOrganizationManagerRequest $request, $id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $result = $this->managerService->createManager($id, $request->validated());

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'manager' => $result['data'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 500);
    }

    /**
     * Save organization as draft (no validation required for mandatory fields).
     */
    public function saveDraft(Request $request, $id = null)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        // Minimal validation for draft
        $rules = [
            // Step 1 fields - name_en is only required when creating a new draft
            'name_en' => ($id ? 'nullable' : 'required') . '|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'organization_type_id' => 'nullable|exists:organization_types,id',
            'status' => 'nullable|boolean',
            'bio' => 'nullable|string|max:1000',
            'industries' => 'nullable|array',
            'industries.*' => 'exists:static_categories,id',
            'img_url' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            // Step 2 fields (Contact Information)
            'contact_email' => 'nullable|email|max:255|regex:/^[a-zA-Z0-9._%+\-@]+$/',
            'country_code' => 'nullable|string|max:10',
            'contact_phone' => 'nullable|string|max:20',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'address' => 'nullable|string|max:500',
            'google_map_url' => 'nullable|string|max:500',
            // Step 2 fields (Social Media)
            'linkedin_company_url' => 'nullable|url|max:255',
            'twitter_handle' => 'nullable|url|max:255',
            'facebook_page_url' => 'nullable|url|max:255',
            'instagram_profile_url' => 'nullable|url|max:255',
            'youtube_channel_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            // Step 3 fields (Managers)
            'manager_ids' => 'nullable|array',
            'manager_ids.*' => 'exists:users,id',
        ];

        $locale = app()->getLocale();
        $messages = $locale === 'ar'
            ? [
                'name_en.required' => 'اسم المنظمة مطلوب',
                'img_url.image' => 'نوع الملف غير صالح، يرجى رفع ملفات من نوع PNG أو JPG فقط',
                'img_url.mimes' => 'نوع الملف غير صالح، يرجى رفع ملفات من نوع PNG أو JPG فقط',
                'img_url.max' => 'الملف اكبر من الحد المسموح. الحد الأقصى المسموح به هو 5 ميجابايت',
            ]
            : [
                'name_en.required' => 'Organization name is required.',
                'img_url.image' => 'Invalid file format. Please upload PNG or JPG files only.',
                'img_url.mimes' => 'Invalid file format. Please upload PNG or JPG files only.',
                'img_url.max' => 'File too large. Maximum size allowed is 5MB.',
            ];

        $request->validate($rules, $messages);

        $data = $request->all();

        // Add file if uploaded
        if ($request->hasFile('img_url')) {
            $data['img_url'] = $request->file('img_url');
        }

        $currentStep = (int) $request->input('current_step', 1);
        $result = $this->wizardService->saveDraft($id, $data, $currentStep);

        if (!$result['success']) {
            return back()
                ->withInput()
                ->with('error', $result['message']);
        }

        // Sync managers if provided (Step 3)
        if ($request->has('manager_ids') && is_array($request->manager_ids) && !empty($request->manager_ids)) {
            $managerResult = $this->managerService->assignManagers($result['data']->id, $request->manager_ids);
            if (!$managerResult['success']) {
                return back()
                    ->withInput()
                    ->with('error', $managerResult['message']);
            }
        }

        return redirect()
            ->route('organization.wizard.edit', ['id' => $result['data']->id, 'step' => $currentStep])
            ->with('success', $result['message']);
    }

    /**
     * Publish a draft organization.
     */
    public function publish($id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $result = $this->wizardService->publish($id);

        if ($result['success']) {
            return redirect()
                ->route('organization.wizard.edit', $result['data']->id)
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Serve organization logo from storage.
     *
     * The wizard stores a bare "<file>" on the organizations disk, while the
     * Org Settings page stores "organizations/<file>" under public/. Both
     * formats live in organizations.img_url, so both are served here.
     */
    public function getLogo($filename)
    {
        if (str_starts_with($filename, 'organizations/')) {
            $path = public_path('organizations/' . basename($filename));

            if (!is_file($path)) {
                abort(404);
            }

            return response()->file($path);
        }

        $disk = Storage::disk('organizations');

        if (!$disk->exists($filename)) {
            abort(404);
        }

        return $disk->response($filename);
    }

    /**
     * Update step 4 data (Landing Page Setup).
     */
    public function updateStep4(OrganizationStep4Request $request, $id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $data = $request->validated();

        // Collect files for processing
        $files = [
            'articles' => [],
            'partners_list' => [],
        ];

        // Gather article images
        $articles = $request->input('articles', []);
        foreach ($articles as $index => $article) {
            if ($request->hasFile("articles.{$index}.image")) {
                $files['articles'][$index]['image'] = $request->file("articles.{$index}.image");
            }
        }

        // Gather partner logos
        $partners = $request->input('partners_list', []);
        foreach ($partners as $index => $partner) {
            if ($request->hasFile("partners_list.{$index}.logo")) {
                $files['partners_list'][$index]['logo'] = $request->file("partners_list.{$index}.logo");
            }
        }

        $result = $this->landingPageService->update($id, $data, $files);

        if ($result['success']) {
            $redirectTo = $request->input('redirect_to', 'next');

            // If coming from standalone landing page, redirect back there
            if ($redirectTo === 'landing_page') {
                $activeTab = $request->input('active_tab', 'howItWorks');
                return redirect()
                    ->route('organization.wizard.landing', ['id' => $id, 'tab' => $activeTab])
                    ->with('success', $result['message']);
            }

            $step = $redirectTo === 'same' ? 4 : 5;

            return redirect()
                ->route('organization.wizard.edit', ['id' => $id, 'step' => $step])
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Serve landing page assets from storage.
     */
    public function getLandingAsset($organizationId, $type, $filename)
    {
        $disk = Storage::disk('organization_landing');
        $path = "{$organizationId}/{$type}/{$filename}";

        if (!$disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path);
    }

    /**
     * Show the standalone landing page management view.
     */
    public function editLandingPage($id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $organization = Organization::with(['landingPage', 'customSections'])->findOrFail($id);
        $landingPage = $organization->landingPage;
        $customSections = $organization->customSections ?? collect();

        $orgPrograms = $organization->cycles()
            ->whereNull('deleted_at')
            ->with(['focusAreas', 'benefits'])
            ->orderBy('created_at', 'desc')
            ->get();
        $focusAreaOptions = StaticCategory::all();

        return view('admin.organizations.landing-page.edit', compact(
            'organization',
            'landingPage',
            'customSections',
            'orgPrograms',
            'focusAreaOptions'
        ));
    }

    /**
     * Preview landing page.
     */
    public function previewLandingPage($id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $result = $this->landingPageService->getPreviewUrl($id);

        if ($result['success']) {
            return redirect()->to($result['data']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Generate slug via AJAX.
     */
    public function generateSlug(Request $request, $id = null)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $name = $request->input('name', null);
        $result = $this->wizardService->generateSlug($id, $name);

        if ($result['success']) {
            return response()->json([
                'status' => true,
                'message' => $result['message'],
                'slug' => $result['data'],
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Check slug availability via AJAX.
     */
    public function checkSlugAvailability(Request $request, $id = null)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $slug = $request->input('slug');
        $result = $this->wizardService->checkSlugAvailability($slug, $id);

        return response()->json([
            'available' => $result['data'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Update step 5 data (Custom Sections).
     */
    public function updateStep5(OrganizationStep5Request $request, $id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $sections = $request->input('custom_sections', []);

        $result = $this->customSectionService->update($id, $sections);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ], $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            $redirectTo = $request->input('redirect_to', 'next');

            // If coming from standalone landing page, redirect back there
            if ($redirectTo === 'landing_page') {
                $activeTab = $request->input('active_tab', 'customSections');
                return redirect()
                    ->route('organization.wizard.landing', ['id' => $id, 'tab' => $activeTab])
                    ->with('success', $result['message']);
            }

            return redirect()
                ->route('organization.wizard.edit', ['id' => $id, 'step' => 5])
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Reorder custom sections via AJAX.
     */
    public function reorderStep5Sections(Request $request, $id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer',
        ]);

        $result = $this->customSectionService->reorder($id, $request->ordered_ids);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Delete a custom section via AJAX.
     */
    public function deleteStep5Section(Request $request, $id, $sectionId)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $result = $this->customSectionService->delete($id, $sectionId);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Update a single program's landing page data via AJAX.
     */
    public function updateProgramLanding(Request $request, $id, $cycleId)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        // Validate the cycle belongs to this org
        $cycle = \App\Cycle::where('id', $cycleId)
            ->where('organization_id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'landing_visible' => 'nullable|boolean',
            'short_description' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'landing_sort_order' => 'nullable|integer',
            'focus_area_ids' => 'nullable|array',
            'focus_area_ids.*' => 'exists:static_categories,id',
            'benefits' => 'nullable|array|max:8',
            'benefits.*.label_en' => 'nullable|string|max:255',
            'benefits.*.label_ar' => 'nullable|string|max:255',
            'benefits.*.icon' => 'nullable|string|max:50',
        ]);

        $result = $this->programLandingService->updateProgramLanding($cycleId, $validated);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Reorder programs for landing page via AJAX.
     */
    public function reorderPrograms(Request $request, $id)
    {
        $_user = auth()->user();
        if (!$_user->ableTo('organization.wizard.edit')) {
            return abort(401);
        }

        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer',
        ]);

        $result = $this->programLandingService->reorderPrograms($id, $request->ordered_ids);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }
}
