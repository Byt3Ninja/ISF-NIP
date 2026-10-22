<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\LandingPage;
use App\Services\FileManagerService;
use App\Services\LandingPageCustomSectionService;
use App\Services\TimezoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LandingPagesController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth')->except(['show']);
	}

	/**
	 * List custom landing pages.
	 */
	public function index(Request $request)
	{
		// Count only cycles that aren't expired, so the badge matches what
		// actually renders on the public landing page (see show(), which
		// filters $page->cycles the same way via reject->is_expired).
		$pagesQuery = $this->scopedLandingPagesQuery()->with('createdBy')->withCount(['cycles' => function ($query) {
			$query->where(function ($query) {
				// GV-9566: `end` is an inclusive DATE in the platform zone.
				$query->whereNull('end')->orWhere('end', '>=', TimezoneService::platformToday());
			});
		}]);

		if ($request->status !== null && $request->status !== '') {
			$pagesQuery->where('status', $request->status);
		}

		if ($request->search) {
			$search = $request->search;
			$pagesQuery->where(function ($query) use ($search) {
				$query->where('title', 'LIKE', '%' . $search . '%')
					->orWhere('description', 'LIKE', '%' . $search . '%');
			});
		}

		$pagesQuery->orderBy('created_at', 'desc');

		$pages = $pagesQuery->paginate(9)->withQueryString();

		$titleMaxLength = LandingPage::TITLE_MAX_LENGTH;

		return view('admin.landing-pages.index', compact('pages', 'titleMaxLength'));
	}

	/**
	 * Delete a custom landing page.
	 */
	public function destroy(Request $request, $id)
	{
		$page = $this->scopedLandingPagesQuery()->findOrFail($id);

		$page->delete();

		return response()->json(['success' => true]);
	}

	/**
	 * Display the public landing page.
	 */
	public function show(Request $request, $slug)
	{
		$page = LandingPage::with(['createdBy', 'cycles.organization', 'cycles.steps', 'customSections'])
			->where('slug', $slug)
			->when(!auth()->check(), function ($query) {
				$query->where('status', true);
			})
			->firstOrFail();

		$colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];
		$l = app()->getLocale();

		// Only keep programs whose organization is still within the page
		// creator's organization access. The landing_page_cycles pivot is
		// otherwise reconciled only when the Programs tab is explicitly
		// re-saved (see updatePrograms()), so without this an Organization
		// removed from a Sub Admin would keep showing its programs here —
		// and accepting applications — until that manual save.
		$visiblePrograms = $this->accessibleCycles($page)->reject->is_expired->values()->map(function ($program) use ($l) {
			// Keep the application status identical to the Organization's public
			// Program page (PublicLandingController): it is driven solely by the
			// first form step (stype = 1). When no form step is configured the
			// program is treated as Closed, exactly like that page.
			$firstFormStep = $program->steps->where('stype', 1)->sortBy('arr')->first();

			if ($firstFormStep) {
				$stepStart = $firstFormStep->from ? \Carbon\Carbon::parse($firstFormStep->from) : null;
				$stepEnd = $firstFormStep->to ? \Carbon\Carbon::parse($firstFormStep->to) : null;

				// GV-9566: step dates are inclusive days in the program's platform zone
				// (comparing now() with the `to` midnight closed it on its last morning).
				$today = TimezoneService::platformToday(TimezoneService::platform($program->organization_id));
				$isOpen = ($stepStart === null || $today >= $stepStart->format('Y-m-d')) && ($stepEnd === null || $today <= $stepEnd->format('Y-m-d'));
				$isUpcoming = $stepStart !== null && $today < $stepStart->format('Y-m-d');
				$isClosed = $stepEnd !== null && $today > $stepEnd->format('Y-m-d');
			} else {
				$isOpen = false;
				$isUpcoming = false;
				$isClosed = true;
				$stepStart = null;
				$stepEnd = null;
			}

			if ($isOpen) {
				$statusClass = 'bg-label-success';
				$statusIcon = 'hourglass';
				$statusLabel = $l == 'ar'
					? 'مفتوح حتى ' . ($stepEnd ? $stepEnd->format('Y-m-d') : '') . '.'
					: 'Open Till ' . ($stepEnd ? $stepEnd->format('Y-m-d') : '') . '.';
				$btnText = $l == 'ar' ? 'تقدم الآن' : 'Apply Now';
			} elseif ($isUpcoming) {
				$statusClass = 'bg-label-warning';
				$statusIcon = 'hourglass-empty';
				$statusLabel = $l == 'ar'
					? 'سيفتح في ' . ($stepStart ? $stepStart->format('Y-m-d') : '') . '.'
					: 'Will open in ' . ($stepStart ? $stepStart->format('Y-m-d') : '') . '.';
				$btnText = $l == 'ar' ? 'التقديم يفتح قريباً' : 'Applications Open Soon';
			} else {
				$statusClass = 'bg-label-danger';
				$statusIcon = 'lock';
				$statusLabel = $l == 'ar' ? 'مغلق.' : 'Closed.';
				$btnText = $l == 'ar' ? 'التقديم مغلق' : 'Applications Closed';
			}

			$program->isOpen = $isOpen;
			$program->isUpcoming = $isUpcoming;
			$program->isClosed = $isClosed;
			$program->statusClass = $statusClass;
			$program->statusIcon = $statusIcon;
			$program->statusLabel = $statusLabel;
			$program->btnText = $btnText;
			$program->canApply = $isOpen && !empty($program->public_slug) && !empty($program->organization) && !empty($program->organization->short_code);

			// Program is within its application window but has no public
			// application page yet (not published as a public program). Say so
			// on the CTA instead of a dead "Apply Now" button.
			$program->applyUnavailable = $isOpen && !$program->canApply;
			if ($program->applyUnavailable) {
				$program->statusIcon = 'clock-hour-4';
				$program->btnText = $l == 'ar'
					? 'صفحة التقديم قيد الإعداد'
					: 'Application page coming soon';
			}

			return $program;
		});

		$customSections = $page->customSections
			->where('visibility', true)
			->filter(fn ($section) => !empty($section->content) || !empty($section->content_ar))
			->values();

		// An enabled but empty How It Works section must not render (nor its
		// nav / CTA / footer links and hero stat).
		$showHow = $page->isHowRenderable($l == 'ar');

		// Same for About Us and Programs: an enabled section without its
		// mandatory content is hidden together with its nav / CTA / footer
		// links and hero stat. Programs is kept when the section is enabled
		// even if there is nothing to list, so the section (and its nav link)
		// still renders the "no programs" empty state.
		$showAbout = $page->isAboutRenderable($l == 'ar');
		$showPrograms = $page->isProgramsRenderable($l == 'ar');

		return view('admin.landing-pages.show', compact('page', 'colors', 'visiblePrograms', 'customSections', 'showHow', 'showAbout', 'showPrograms'));
	}

  public function create(Request $request)
  {
    $cycles = $this->scopedCycles();
    $programOrganizations = $this->programOrganizations($cycles);
    $programTypes = Cycle::programTypes();
    $sections = $this->buildSections();

    return view('admin.landing-pages.create', compact('cycles', 'programOrganizations', 'programTypes', 'sections'));
  }

  /**
   * Organizations behind the given programs, deduplicated and sorted by
   * name in the current locale — used to populate the Programs tab's
   * organization filter dropdown.
   */
  protected function programOrganizations($cycles)
  {
    $locale = app()->getLocale();

    return $cycles->pluck('organization')
      ->filter()
      ->unique('id')
      ->sortBy($locale === 'ar' ? 'name_ar' : 'name_en');
  }

  /**
   * Build the tab/field configuration shared by the create and edit
   * screens. Field values are pulled from $currentPage when editing an
   * existing page; they're left null on the create screen, where the
   * Programs/Custom Sections tabs are also fully disabled until the page
   * exists (see the "Programs_Note" banner).
   */
  protected function buildSections(?LandingPage $currentPage = null): array
  {
    $title_max_length = LandingPage::TITLE_MAX_LENGTH;
	$description_max_length = LandingPage::DESCRIPTION_MAX_LENGTH;
	$program_description_max_length = LandingPage::PROGRAM_DESCRIPTION_MAX_LENGTH;

    $sections = [
      [
        'key'      => 'general',
        'icon'     => 'ti-settings',
        'label_en' => 'General',
        'label_ar' => 'عام',
        'route'    => $currentPage ? route('landing-pages.general.update', $currentPage->id) : route('landing-pages.store'),
        'fields'   => [
          [
            'name'      => 'title',
            'type'      => 'text',
            'label_en'  => 'Page Title',
            'label_ar'  => 'عنوان الصفحة',
            'value'     => $currentPage?->title,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 12,
          ],
          [
            'name'        => 'logo',
            'type'        => 'image',
            'label_en'    => 'Logo',
            'label_ar'    => 'الشعار',
            'value'       => $currentPage?->logo_url,
            'required'    => true,
            'accept'      => '.png,.jpg,.jpeg,.webp',
            'max_size_mb' => 5,
            'col'         => 12,
          ],
          [
            'name'      => 'description',
            'type'      => 'textarea',
            'label_en'  => 'Description',
            'label_ar'  => 'الوصف',
            'value'     => $currentPage?->description,
            'required'  => true,
            'maxlength' => $description_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'description_ar',
            'type'      => 'textarea',
            'label_en'  => 'Description (Arabic)',
            'label_ar'  => 'الوصف بالعربية',
            'value'     => $currentPage?->description_ar,
            'required'  => true,
            'maxlength' => $description_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'linkedin',
            'type'      => 'url',
            'label_en'  => 'LinkedIn',
            'label_ar'  => 'لينكدإن',
            'value'     => $currentPage?->linkedin,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
          [
            'name'      => 'facebook',
            'type'      => 'url',
            'label_en'  => 'Facebook',
            'label_ar'  => 'فيسبوك',
            'value'     => $currentPage?->facebook,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
          [
            'name'      => 'instagram',
            'type'      => 'url',
            'label_en'  => 'Instagram',
            'label_ar'  => 'إنستغرام',
            'value'     => $currentPage?->instagram,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
          [
            'name'      => 'x',
            'type'      => 'url',
            'label_en'  => 'X',
            'label_ar'  => 'إكس',
            'value'     => $currentPage?->x,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
          [
            'name'      => 'tiktok',
            'type'      => 'url',
            'label_en'  => 'TikTok',
            'label_ar'  => 'تيك توك',
            'value'     => $currentPage?->tiktok,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
          [
            'name'      => 'youtube',
            'type'      => 'url',
            'label_en'  => 'YouTube',
            'label_ar'  => 'يوتيوب',
            'value'     => $currentPage?->youtube,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
        ],
      ],
      [
        'key'      => 'hero',
        'icon'     => 'ti-align-box-top-center',
        'label_en' => 'Hero',
        'label_ar' => 'البطل',
        'route'    => $currentPage ? route('landing-pages.hero.update', $currentPage->id) : route('landing-pages.hero.update', 0),
        'fields'   => [
          [
            'name'      => 'title',
            'type'      => 'text',
            'label_en'  => 'Title',
            'label_ar'  => 'العنوان',
            'value'     => $currentPage?->hero_title,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_ar',
            'type'      => 'text',
            'label_en'  => 'Title (Arabic)',
            'label_ar'  => 'العنوان بالعربية',
            'value'     => $currentPage?->hero_title_ar,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag',
            'type'      => 'text',
            'label_en'  => 'Title tag',
            'label_ar'  => 'العنوان الفرعي',
            'value'     => $currentPage?->hero_title_tag,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag_ar',
            'type'      => 'text',
            'label_en'  => 'Title tag (Arabic)',
            'label_ar'  => 'العنوان الفرعي بالعربية',
            'value'     => $currentPage?->hero_title_tag_ar,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'description',
            'type'      => 'textarea',
            'label_en'  => 'Description',
            'label_ar'  => 'الوصف',
            'value'     => $currentPage?->hero_description,
            'required'  => true,
            'maxlength' => $description_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'description_ar',
            'type'      => 'textarea',
            'label_en'  => 'Description (Arabic)',
            'label_ar'  => 'الوصف بالعربية',
            'value'     => $currentPage?->hero_description_ar,
            'required'  => true,
            'maxlength' => $description_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'cta_text',
            'type'      => 'text',
            'label_en'  => 'CTA Text (English)',
            'label_ar'  => 'نص الإجراء (بالإنجليزية)',
            'value'     => $currentPage?->hero_cta_text ?: 'View Programs',
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'cta_text_ar',
            'type'      => 'text',
            'label_en'  => 'CTA Text (Arabic)',
            'label_ar'  => 'نص الإجراء (بالعربية)',
            'value'     => $currentPage?->hero_cta_text_ar ?: 'عرض البرامج',
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'cta_link',
            'type'      => 'url',
            'label_en'  => 'CTA Link',
            'label_ar'  => 'رابط الإجراء',
            'value'     => $currentPage?->hero_cta_link,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 12,
            'hint_en'   => 'Leave empty to navigate to the Programs section on this landing page.',
            'hint_ar'   => 'اتركه فارغاً للانتقال إلى قسم البرامج في صفحة الهبوط هذه.',
          ],
          [
            'name'        => 'hero_image',
            'type'        => 'image',
            'label_en'    => 'Hero image',
            'label_ar'    => 'صورة القسم الرئيسي',
            'value'       => $currentPage?->hero_image_url,
            'required'    => true,
            'accept'      => '.png,.jpg,.jpeg,.webp',
            'max_size_mb' => 5,
            'col'         => 12,
          ],
        ],
      ],
      [
        'key'      => 'about',
        'icon'     => 'ti-users',
        'label_en' => 'About Us',
        'label_ar' => 'من نحن',
        'route'    => $currentPage ? route('landing-pages.about.update', $currentPage->id) : route('landing-pages.about.update', 0),
        'fields'   => [
          [
            'name'      => 'is_enabled',
            'type'      => 'checkbox',
            'label_en'  => 'Enable Section',
            'label_ar'  => 'تفعيل القسم',
            'value'     => $currentPage ? (bool) $currentPage->about_is_enabled : 0,
            'col'       => 12,
          ],
          [
            'name'      => 'title',
            'type'      => 'text',
            'label_en'  => 'Title',
            'label_ar'  => 'العنوان',
            'value'     => $currentPage?->about_title,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_ar',
            'type'      => 'text',
            'label_en'  => 'Title (Arabic)',
            'label_ar'  => 'العنوان بالعربية',
            'value'     => $currentPage?->about_title_ar,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag',
            'type'      => 'text',
            'label_en'  => 'Title tag',
            'label_ar'  => 'العنوان الفرعي',
            'value'     => $currentPage?->about_title_tag,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag_ar',
            'type'      => 'text',
            'label_en'  => 'Title tag (Arabic)',
            'label_ar'  => 'العنوان الفرعي بالعربية',
            'value'     => $currentPage?->about_title_tag_ar,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'description',
            'type'      => 'textarea',
            'label_en'  => 'Description',
            'label_ar'  => 'الوصف',
            'value'     => $currentPage?->about_description,
            'required'  => true,
            'maxlength' => $description_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'description_ar',
            'type'      => 'textarea',
            'label_en'  => 'Description (Arabic)',
            'label_ar'  => 'الوصف بالعربية',
            'value'     => $currentPage?->about_description_ar,
            'required'  => true,
            'maxlength' => $description_max_length,
            'col'       => 6,
          ],
        ],
      ],
      [
        'key'      => 'programs',
        'icon'     => 'ti-rocket',
        'label_en' => 'Programs',
        'label_ar' => 'البرامج',
        'route'    => $currentPage ? route('landing-pages.programs.update', $currentPage->id) : route('landing-pages.programs.update', 0),
        'fields'   => [
          [
            'name'      => 'is_enabled',
            'type'      => 'checkbox',
            'label_en'  => 'Enable Section',
            'label_ar'  => 'تفعيل القسم',
            'value'     => $currentPage ? (bool) $currentPage->programs_is_enabled : 0,
            'col'       => 12,
          ],
          [
            'name'      => 'title',
            'type'      => 'text',
            'label_en'  => 'Title',
            'label_ar'  => 'العنوان',
            'value'     => $currentPage?->programs_title,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_ar',
            'type'      => 'text',
            'label_en'  => 'Title (Arabic)',
            'label_ar'  => 'العنوان بالعربية',
            'value'     => $currentPage?->programs_title_ar,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag',
            'type'      => 'text',
            'label_en'  => 'Title tag',
            'label_ar'  => 'العنوان الفرعي',
            'value'     => $currentPage?->programs_title_tag,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag_ar',
            'type'      => 'text',
            'label_en'  => 'Title tag (Arabic)',
            'label_ar'  => 'العنوان الفرعي بالعربية',
            'value'     => $currentPage?->programs_title_tag_ar,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'description',
            'type'      => 'textarea',
            'label_en'  => 'Description',
            'label_ar'  => 'الوصف',
            'value'     => $currentPage?->programs_description,
            'required'  => true,
            'maxlength' => $program_description_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'description_ar',
            'type'      => 'textarea',
            'label_en'  => 'Description (Arabic)',
            'label_ar'  => 'الوصف بالعربية',
            'value'     => $currentPage?->programs_description_ar,
            'required'  => true,
            'maxlength' => $program_description_max_length,
            'col'       => 6,
          ],
        ],
      ],
      [
        'key'      => 'how',
        'icon'     => 'ti-settings-bolt',
        'label_en' => 'How It Works',
        'label_ar' => 'كيف يعمل',
        'route'    => $currentPage ? route('landing-pages.how.update', $currentPage->id) : route('landing-pages.how.update', 0),
        'fields'   => [
          [
            'name'      => 'is_enabled',
            'type'      => 'checkbox',
            'label_en'  => 'Enable Section',
            'label_ar'  => 'تفعيل القسم',
            'value'     => $currentPage ? (bool) $currentPage->how_is_enabled : 1,
            'col'       => 12,
          ],
          [
            'name'      => 'title',
            'type'      => 'text',
            'label_en'  => 'Title',
            'label_ar'  => 'العنوان',
            'value'     => $currentPage?->how_title,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_ar',
            'type'      => 'text',
            'label_en'  => 'Title (Arabic)',
            'label_ar'  => 'العنوان بالعربية',
            'value'     => $currentPage?->how_title_ar,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag',
            'type'      => 'text',
            'label_en'  => 'Title tag',
            'label_ar'  => 'العنوان الفرعي',
            'value'     => $currentPage?->how_title_tag,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag_ar',
            'type'      => 'text',
            'label_en'  => 'Title tag (Arabic)',
            'label_ar'  => 'العنوان الفرعي بالعربية',
            'value'     => $currentPage?->how_title_tag_ar,
            'required'  => true,
            'maxlength' => $title_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'description',
            'type'      => 'textarea',
            'label_en'  => 'Description',
            'label_ar'  => 'الوصف',
            'value'     => $currentPage?->how_description,
            'required'  => true,
            'maxlength' => $description_max_length,
            'col'       => 6,
          ],
          [
            'name'      => 'description_ar',
            'type'      => 'textarea',
            'label_en'  => 'Description (Arabic)',
            'label_ar'  => 'الوصف بالعربية',
            'value'     => $currentPage?->how_description_ar,
            'required'  => true,
            'maxlength' => $description_max_length,
            'col'       => 6,
          ],
        ],
      ],
      [
        'key'      => 'branding',
        'icon'     => 'ti-palette',
        'label_en' => 'Branding',
        'label_ar' => 'الهوية البصرية',
        // Colors only: no generic fields. Saves through its own endpoint
        // (like the other tabs), which never touches the section repeater
        // below it. The id-0 fallback matches the other create-mode routes.
        'route'    => $currentPage ? route('landing-pages.branding.update', $currentPage->id) : route('landing-pages.branding.update', 0),
        'fields'   => [],
      ],
      [
        'key'      => 'custom',
        'icon'     => 'ti-puzzle',
        'label_en' => 'Custom sections',
        'label_ar' => 'أقسام مخصصة',
        'route'    => $currentPage ? route('landing-pages.custom-sections.update', $currentPage->id) : route('landing-pages.custom-sections.update', 0),
        'fields'   => [],
      ],
    ];

    foreach ($sections as &$section) {
      // Every tab besides General needs an existing page to save into, so
      // until the page is created (General saved), lock them: this pushes
      // the admin toward the General tab first.
      $section['locked'] = !$currentPage && $section['key'] !== 'general';

      foreach ($section['fields'] as &$field) {
        $field['name_attr'] = "{$section['key']}[{$field['name']}]";
        $field['id_attr'] = "{$section['key']}_{$field['name']}";

        if ($section['locked']) {
          $field['disabled'] = true;
        }
      }
      unset($field);
    }
    unset($section);

    return $sections;
  }

  /**
   * Landing pages visible to the current user: Admin sees every page,
   * Sub Admin sees pages created by users belonging to any organization
   * they manage, and everyone else (including Organization Manager) only
   * sees pages they personally created.
   */
  protected function scopedLandingPagesQuery()
  {
    $user = auth()->user();
    $query = LandingPage::query();

    if ($user->hasRole('admin')) {
      return $query;
    }

    if ($user->hasRole('subadmin')) {
      $orgIds = $user->organizations_ids;

      // A creator belongs to an organization either via the single
      // `organization_id` column (organization managers / registered
      // users) or via the user_organizations pivot (subadmins, who
      // manage multiple orgs through that table instead of the single
      // column — see User::getOrganizationsIdsAttribute()). Checking
      // only the column misses subadmin-created pages, since a
      // subadmin's own `organization_id` column is typically null.
      return $query->whereHas('createdBy', function ($query) use ($orgIds) {
        $query->whereIn('organization_id', $orgIds)
          ->orWhereHas('organizations', function ($query) use ($orgIds) {
            $query->whereIn('organizations.id', $orgIds);
          });
      });
    }

    return $query->where('created_by', $user->id);
  }

  /**
   * The programs attached to a landing page, minus any whose
   * organization is no longer within the page creator's organization
   * access. Used to keep both the public page and the editor in step
   * with the creator's current Sub Admin / Organization Manager scope
   * without waiting for the Programs tab to be re-saved (the only thing
   * that reconciles the landing_page_cycles pivot — see updatePrograms()).
   */
  protected function accessibleCycles(LandingPage $page)
  {
    $creator = $page->createdBy;

    // No creator on record (e.g. the account was removed) or an Admin
    // creator: leave every attachment in place rather than blanking a
    // published page.
    $cycles = (!$creator || $creator->hasRole('admin'))
      ? $page->cycles
      : $page->cycles->whereIn('organization_id', $creator->organizations_ids)->values();

    return $this->applyProgramsOrder($cycles, $page->programs_order ?? []);
  }

  /**
   * Reorder a collection of programs/cycles according to a saved list of
   * IDs (see LandingPagesController::reorderPrograms()), placing anything
   * not present in that list — new or never-reordered programs — after
   * the ones it does cover, in their original order.
   */
  protected function applyProgramsOrder($cycles, array $order)
  {
    if (empty($order)) {
      return $cycles->values();
    }

    $cyclesById = $cycles->keyBy('id');
    $ordered = collect($order)->map(fn ($id) => $cyclesById->get($id))->filter();

    return $ordered->merge($cycles->whereNotIn('id', $order))->values();
  }

  /**
   * Programs available for attaching to a landing page: Super Admin sees
   * every program, Sub Admin / Tenant Admin see only programs belonging to
   * their organizations. For an existing page the scope is the page
   * creator's, not the editor's, so whoever edits it (e.g. an Admin, or a
   * Sub Admin with more organizations) works on the creator's programs only
   * -- the same scope accessibleCycles() applies on the public page.
   */
  protected function scopedCycles(?LandingPage $page = null)
  {
    $user = auth()->user();

    // Fall back to the editor when the creator is gone or has no
    // organizations left, rather than leaving the page with no programs.
    $creator = $page?->createdBy;
    if ($creator && ($creator->hasRole('admin') || !empty($creator->organizations_ids))) {
      $user = $creator;
    }

    $query = Cycle::with('organization')->withCount('steps');

    if (!$user->hasRole('admin')) {
      $orgIds = $user->organizations_ids;
      $query->whereIn('organization_id', $orgIds);
    }

    return $query->orderBy('created_at', 'desc')->get();
  }

  /**
   * Create a new custom landing page from the General tab of the
   * create-page flow.
   */
  public function store(Request $request)
  {
	$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.create')) {
            return abort(401);
        }
    $user = auth()->user();
    $title_max_length = LandingPage::TITLE_MAX_LENGTH;
	$description_max_length = LandingPage::DESCRIPTION_MAX_LENGTH;

    $validator = Validator::make($request->all(), [
      'general.title' => 'required|string|max:' . $title_max_length,
      'general.logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
      'general.description' => 'required|string|max:' . $description_max_length,
      'general.description_ar' => 'required|string|max:' . $description_max_length,
      'general.linkedin' => 'nullable|url|max:255',
      'general.facebook' => 'nullable|url|max:255',
      'general.instagram' => 'nullable|url|max:255',
      'general.x' => 'nullable|url|max:255',
      'general.tiktok' => 'nullable|url|max:255',
      'general.youtube' => 'nullable|url|max:255',
    ], [
      'general.title.required' => __('landing.validation.page_title_required'),
      'general.title.max' => __('landing.validation.page_title_max', ['max' => $title_max_length]),
      'general.logo.required' => __('landing.validation.logo_required'),
      'general.logo.image' => __('landing.validation.image_format_invalid'),
      'general.logo.mimes' => __('landing.validation.image_format_invalid'),
      'general.logo.max' => __('landing.validation.logo_size_exceeded'),
      'general.description.required' => __('landing.validation.description_required'),
      'general.description_ar.required' => __('landing.validation.description_ar_required'),
      'general.linkedin.url' => __('landing.validation.invalid_url', ['field' => 'LinkedIn', 'example' => 'https://linkedin.com/in/username']),
      'general.facebook.url' => __('landing.validation.invalid_url', ['field' => 'Facebook', 'example' => 'https://facebook.com/yourpage']),
      'general.instagram.url' => __('landing.validation.invalid_url', ['field' => 'Instagram', 'example' => 'https://instagram.com/yourprofile']),
      'general.x.url' => __('landing.validation.invalid_url', ['field' => 'X', 'example' => 'https://x.com/yourprofile']),
      'general.tiktok.url' => __('landing.validation.invalid_url', ['field' => 'TikTok', 'example' => 'https://tiktok.com/@yourprofile']),
      'general.youtube.url' => __('landing.validation.invalid_url', ['field' => 'YouTube', 'example' => 'https://youtube.com/@yourchannel']),
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => __('landing.validation.please_correct_fields'),
        'errors' => $validator->errors(),
      ], 422);
    }

    $general = $request->input('general', []);

    $page = new LandingPage();
    $page->created_by = $user->id;
    $page->title = $general['title'];
    $page->slug = LandingPage::generateSlug($general['title']);
    $page->status = true;
    $page->logo = app(FileManagerService::class)->upload($request->file('general.logo'), 'landing-pages', 'public_uploads', 'image');
    $page->description = $general['description'];
    $page->description_ar = $general['description_ar'];
    $page->linkedin = $general['linkedin'] ?? null;
    $page->facebook = $general['facebook'] ?? null;
    $page->instagram = $general['instagram'] ?? null;
    $page->x = $general['x'] ?? null;
    $page->tiktok = $general['tiktok'] ?? null;
    $page->youtube = $general['youtube'] ?? null;
    $page->save();

    return response()->json([
      'success' => true,
      'message' => __('landing.success.general_saved'),
      'redirect' => route('landing-pages.edit', $page->id),
      'id' => $page->id,
      'routes' => collect($this->buildSections($page))->pluck('route', 'key'),
      'url_save_route' => route('landing-pages.url.save', $page->id),
    ]);
  }

  public function edit(Request $request, $id)
  {
    $currentPage = $this->scopedLandingPagesQuery()->with('cycles', 'customSections', 'createdBy')->findOrFail($id);
    $cycles = $this->scopedCycles($currentPage);
    $cycles = $this->applyProgramsOrder($cycles, $currentPage->programs_order ?? []);

    $programOrganizations = $this->programOrganizations($cycles);
    $programTypes = Cycle::programTypes();
    $sections = $this->buildSections($currentPage);
    $aboutCards = $currentPage->about_cards ?? [];
    $howSteps = $currentPage->how_steps ?? [];
    $customSectionBlocks = $currentPage->customSections;
    $selectedCycleIds = $currentPage->cycles->pluck('id')->toArray();
    $aboutIconsList = [
      'ti-rocket' => 'Rocket', 'ti-bulb' => 'Idea', 'ti-target' => 'Target',
      'ti-chart-line' => 'Growth', 'ti-users' => 'Community', 'ti-trophy' => 'Achievement',
      'ti-shield' => 'Protection', 'ti-heart' => 'Care', 'ti-star' => 'Quality',
      'ti-book' => 'Education', 'ti-code' => 'Code', 'ti-cpu' => 'Technology',
      'ti-world' => 'Global', 'ti-flag' => 'Goal', 'ti-crown' => 'Leadership',
      'ti-gift' => 'Reward', 'ti-briefcase' => 'Career', 'ti-cloud' => 'Cloud',
    ];

    return view('admin.landing-pages.edit', compact(
      'cycles',
      'currentPage',
      'programOrganizations',
      'programTypes',
      'sections',
      'aboutCards',
      'howSteps',
      'customSectionBlocks',
      'selectedCycleIds',
      'aboutIconsList'
    ));
  }


	/**
	 * Save the General section for a custom landing page.
	 */
	public function updateGeneral(Request $request, $id)
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
            return abort(401);
        } 
		$page = $this->scopedLandingPagesQuery()->findOrFail($id);
		$title_max_length = LandingPage::TITLE_MAX_LENGTH;
		$description_max_length = LandingPage::DESCRIPTION_MAX_LENGTH;

		$validator = Validator::make($request->all(), [
			'general.title' => 'required|string|max:' . $title_max_length,
			'general.logo' => [
				$page->logo ? 'nullable' : 'required',
				'image',
				'mimes:png,jpg,jpeg,webp',
				'max:5120',
			],
			'general.description' => 'required|string|max:' . $description_max_length,
			'general.description_ar' => 'required|string|max:' . $description_max_length,
			'general.linkedin' => 'nullable|url|max:255',
			'general.facebook' => 'nullable|url|max:255',
			'general.instagram' => 'nullable|url|max:255',
			'general.x' => 'nullable|url|max:255',
			'general.tiktok' => 'nullable|url|max:255',
			'general.youtube' => 'nullable|url|max:255',
		], [
			'general.title.required' => __('landing.validation.page_title_required'),
			'general.title.max' => __('landing.validation.page_title_max', ['max' => $title_max_length]),
			'general.logo.required' => __('landing.validation.logo_required'),
			'general.logo.image' => __('landing.validation.image_format_invalid'),
			'general.logo.mimes' => __('landing.validation.image_format_invalid'),
			'general.logo.max' => __('landing.validation.logo_size_exceeded'),
			'general.description.required' => __('landing.validation.description_required'),
			'general.description_ar.required' => __('landing.validation.description_ar_required'),
			'general.linkedin.url' => __('landing.validation.invalid_url', ['field' => 'LinkedIn', 'example' => 'https://linkedin.com/in/username']),
			'general.facebook.url' => __('landing.validation.invalid_url', ['field' => 'Facebook', 'example' => 'https://facebook.com/yourpage']),
			'general.instagram.url' => __('landing.validation.invalid_url', ['field' => 'Instagram', 'example' => 'https://instagram.com/yourprofile']),
			'general.x.url' => __('landing.validation.invalid_url', ['field' => 'X', 'example' => 'https://x.com/yourprofile']),
			'general.tiktok.url' => __('landing.validation.invalid_url', ['field' => 'TikTok', 'example' => 'https://tiktok.com/@yourprofile']),
			'general.youtube.url' => __('landing.validation.invalid_url', ['field' => 'YouTube', 'example' => 'https://youtube.com/@yourchannel']),
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => __('landing.validation.please_correct_fields'),
				'errors' => $validator->errors(),
			], 422);
		}

		$general = $request->input('general', []);

		if ($request->hasFile('general.logo')) {
			$page->logo = app(FileManagerService::class)->update(
				$request->file('general.logo'),
				$page->logo,
				'landing-pages',
				'public_uploads',
				'image'
			);
		}

		$page->title = $general['title'];
		$page->description = $general['description'];
		$page->description_ar = $general['description_ar'];
		$page->linkedin = $general['linkedin'] ?? null;
		$page->facebook = $general['facebook'] ?? null;
		$page->instagram = $general['instagram'] ?? null;
		$page->x = $general['x'] ?? null;
		$page->tiktok = $general['tiktok'] ?? null;
		$page->youtube = $general['youtube'] ?? null;
		$page->save();

		return response()->json([
			'success' => true,
			'message' => __('landing.success.general_saved'),
		]);
	}

	/**
	 * Save the Hero section for a custom landing page.
	 */
	public function updateHero(Request $request, $id)
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
            return abort(401);
        }
		$page = $this->scopedLandingPagesQuery()->findOrFail($id);
		$title_max_length = LandingPage::TITLE_MAX_LENGTH;
		$description_max_length = LandingPage::DESCRIPTION_MAX_LENGTH;

		$validator = Validator::make($request->all(), [
			'hero.title' => 'required|string|max:' . $title_max_length,
			'hero.title_ar' => 'required|string|max:' . $title_max_length,
			'hero.title_tag' => 'required|string|max:' . $title_max_length,
			'hero.title_tag_ar' => 'required|string|max:' . $title_max_length,
			'hero.description' => 'required|string|max:' . $description_max_length,
			'hero.description_ar' => 'required|string|max:' . $description_max_length,
			'hero.hero_image' => [
				$page->hero_image ? 'nullable' : 'required',
				'image',
				'mimes:png,jpg,jpeg,webp',
				'max:5120',
			],
		], [
			'hero.title.required' => __('landing.validation.field_required'),
			'hero.title_ar.required' => __('landing.validation.field_required'),
			'hero.title_tag.required' => __('landing.validation.field_required'),
			'hero.title_tag_ar.required' => __('landing.validation.field_required'),
			'hero.description.required' => __('landing.validation.field_required'),
			'hero.description_ar.required' => __('landing.validation.field_required'),
			'hero.hero_image.required' => __('landing.validation.field_required'),
			'hero.hero_image.image' => __('landing.validation.image_format_invalid'),
			'hero.hero_image.mimes' => __('landing.validation.image_format_invalid'),
			'hero.hero_image.max' => __('landing.validation.hero_image_size_exceeded'),
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => $validator->errors()->first(),
				'errors' => $validator->errors(),
			], 422);
		}

		$hero = $request->input('hero', []);

		if ($request->hasFile('hero.hero_image')) {
			$page->hero_image = app(FileManagerService::class)->update(
				$request->file('hero.hero_image'),
				$page->hero_image,
				'landing-pages',
				'public_uploads',
				'image'
			);
		}

		$page->hero_title = $hero['title'];
		$page->hero_title_ar = $hero['title_ar'];
		$page->hero_title_tag = $hero['title_tag'];
		$page->hero_title_tag_ar = $hero['title_tag_ar'];
		$page->hero_description = $hero['description'];
		$page->hero_description_ar = $hero['description_ar'];
		$page->save();

		return response()->json([
			'success' => true,
			'message' => __('landing.success.hero_saved'),
		]);
	}

	/**
	 * Save the Programs section for a custom landing page: which programs
	 * (cycles) are featured. Only programs within the page creator's scope
	 * (per scopedCycles()) may be attached, regardless of what IDs are
	 * submitted.
	 */
	public function updatePrograms(Request $request, $id)
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
            return abort(401);
        }
		$page = $this->scopedLandingPagesQuery()->findOrFail($id);
		$title_max_length = LandingPage::TITLE_MAX_LENGTH;
		$program_description_max_length = LandingPage::PROGRAM_DESCRIPTION_MAX_LENGTH;

		$validator = Validator::make($request->all(), [
			'programs.selected_programs' => 'nullable|array',
			'programs.selected_programs.*' => 'in:0,1',
			'programs.is_enabled' => 'nullable|boolean',
			'programs.title' => 'nullable|string|max:' . $title_max_length,
			'programs.title_ar' => 'nullable|string|max:' . $title_max_length,
			'programs.title_tag' => 'nullable|string|max:' . $title_max_length,
			'programs.title_tag_ar' => 'nullable|string|max:' . $title_max_length,
			'programs.description' => 'nullable|string|max:' . $program_description_max_length,
			'programs.description_ar' => 'nullable|string|max:' . $program_description_max_length,
		]);

		$requiredMsg = __('landing.validation.field_required');

		$validator->after(function ($validator) use ($request, $requiredMsg) {
			$programs = $request->input('programs', []);
			$isEnabled = !empty($programs['is_enabled']);

			if ($isEnabled) {
				foreach (['title', 'title_ar', 'title_tag', 'title_tag_ar', 'description', 'description_ar'] as $field) {
					if (!trim((string) ($programs[$field] ?? ''))) {
						$validator->errors()->add("programs.{$field}", $requiredMsg);
					}
				}
			}
		});

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => $validator->errors()->first(),
				'errors' => $validator->errors(),
			], 422);
		}

		$programs = $request->input('programs', []);

		$selectedPrograms = $programs['selected_programs'] ?? [];
		$requestedIds = array_keys(array_filter($selectedPrograms, fn ($selected) => (bool) $selected));
		// Ended programs can never be attached, no matter what the request
		// says: reject.is_expired mirrors the same accessor used to hide
		// expired programs from the public landing page (see show()).
		$allowedIds = $this->scopedCycles($page)->reject->is_expired->pluck('id')->toArray();
		$cycleIds = array_values(array_intersect($requestedIds, $allowedIds));

		$page->cycles()->sync($cycleIds);

		$page->programs_is_enabled = !empty($programs['is_enabled']);
		$page->programs_title = $programs['title'] ?? null;
		$page->programs_title_ar = $programs['title_ar'] ?? null;
		$page->programs_title_tag = $programs['title_tag'] ?? null;
		$page->programs_title_tag_ar = $programs['title_tag_ar'] ?? null;
		$page->programs_description = $programs['description'] ?? null;
		$page->programs_description_ar = $programs['description_ar'] ?? null;
		$page->save();

		return response()->json([
			'success' => true,
			'message' => __('landing.success.programs_saved'),
		]);
	}

	/**
	 * Persist the drag-and-drop order of the Programs list for a custom
	 * landing page (see edit.blade.php's Sortable wiring).
	 */
	public function reorderPrograms(Request $request, $id)
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
            return abort(401);
        }

		$request->validate([
			'order' => 'required|array',
			'order.*' => 'integer',
		]);

		$page = $this->scopedLandingPagesQuery()->findOrFail($id);
		// The order applies to the whole admin-table list (selected and
		// not-yet-selected programs alike), so it can't live on the
		// landing_page_cycles pivot — that only tracks selected/featured
		// programs, and a page with nothing selected yet would have no
		// pivot rows to update. Persist it on the page itself instead,
		// scoped to the programs this page's editor is shown (see scopedCycles()).
		$allowedIds = $this->scopedCycles($page)->pluck('id')->toArray();
		$orderedIds = array_values(array_intersect($request->input('order'), $allowedIds));

		$page->programs_order = $orderedIds;
		$page->save();

		return response()->json(['success' => true]);
	}

	/**
	 * Save the About Us section for a custom landing page.
	 */
	public function updateAbout(Request $request, $id)
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
            return abort(401);
        }
		$page = $this->scopedLandingPagesQuery()->findOrFail($id);
		$title_max_length = LandingPage::TITLE_MAX_LENGTH;
		$description_max_length = LandingPage::DESCRIPTION_MAX_LENGTH;

		$validator = Validator::make($request->all(), [
			'about.is_enabled' => 'nullable|boolean',
			'about.title' => 'nullable|string|max:' . $title_max_length,
			'about.title_ar' => 'nullable|string|max:' . $title_max_length,
			'about.title_tag' => 'nullable|string|max:' . $title_max_length,
			'about.title_tag_ar' => 'nullable|string|max:' . $title_max_length,
			'about.description' => 'nullable|string|max:' . $description_max_length,
			'about.description_ar' => 'nullable|string|max:' . $description_max_length,
			'about.cards' => 'nullable|array|max:3',
			'about.cards.*.icon' => 'nullable|string|max:50',
			'about.cards.*.title' => 'nullable|string|max:' . $title_max_length,
			'about.cards.*.title_ar' => 'nullable|string|max:' . $title_max_length,
			'about.cards.*.description' => 'nullable|string|max:300',
			'about.cards.*.description_ar' => 'nullable|string|max:300',
		]);

		$validator->after(function ($validator) use ($request) {
			$about = $request->input('about', []);
			$isEnabled = !empty($about['is_enabled']);

			if ($isEnabled) {
				$mainFields = [
					'about.title' => [$about['title'] ?? '', __('landing.validation.title_required')],
					'about.title_ar' => [$about['title_ar'] ?? '', __('landing.validation.title_ar_required')],
					'about.title_tag' => [$about['title_tag'] ?? '', __('landing.validation.subtitle_required')],
					'about.title_tag_ar' => [$about['title_tag_ar'] ?? '', __('landing.validation.subtitle_ar_required')],
					'about.description' => [$about['description'] ?? '', __('landing.validation.description_required')],
					'about.description_ar' => [$about['description_ar'] ?? '', __('landing.validation.description_ar_required')],
				];

				foreach ($mainFields as $field => [$value, $message]) {
					if (!trim((string) $value)) {
						$validator->errors()->add($field, $message);
					}
				}
			}

			$cardIconMsg = __('landing.validation.icon_required');
			$cardTitleMsg = __('landing.validation.card_title_required');
			$cardTitleArMsg = __('landing.validation.card_title_ar_required');
			$cardDescriptionMsg = __('landing.validation.card_description_required');
			$cardDescriptionArMsg = __('landing.validation.card_description_ar_required');

			foreach (($about['cards'] ?? []) as $index => $card) {
				$icon = trim((string) ($card['icon'] ?? ''));
				$title = trim((string) ($card['title'] ?? ''));
				$titleAr = trim((string) ($card['title_ar'] ?? ''));
				$description = trim((string) ($card['description'] ?? ''));
				$descriptionAr = trim((string) ($card['description_ar'] ?? ''));

				if (!$icon && ($title || $description || $titleAr || $descriptionAr)) {
					$validator->errors()->add("about.cards.{$index}.icon", $cardIconMsg);
				}

				if (!$title && ($icon || $description || $titleAr || $descriptionAr)) {
					$validator->errors()->add("about.cards.{$index}.title", $cardTitleMsg);
				}

				if (!$titleAr && ($icon || $description || $descriptionAr || $title)) {
					$validator->errors()->add("about.cards.{$index}.title_ar", $cardTitleArMsg);
				}

				if (!$description && ($icon || $title)) {
					$validator->errors()->add("about.cards.{$index}.description", $cardDescriptionMsg);
				}

				if (!$descriptionAr && ($icon || $title || $titleAr || $description)) {
					$validator->errors()->add("about.cards.{$index}.description_ar", $cardDescriptionArMsg);
				}
			}
		});

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => $validator->errors()->first(),
				'errors' => $validator->errors(),
			], 422);
		}

		$about = $request->input('about', []);

		$page->about_is_enabled = !empty($about['is_enabled']);
		$page->about_title = $about['title'] ?? null;
		$page->about_title_ar = $about['title_ar'] ?? null;
		$page->about_title_tag = $about['title_tag'] ?? null;
		$page->about_title_tag_ar = $about['title_tag_ar'] ?? null;
		$page->about_description = $about['description'] ?? null;
		$page->about_description_ar = $about['description_ar'] ?? null;
		$page->about_cards = $this->processAboutCards($about['cards'] ?? []);
		$page->save();

		return response()->json([
			'success' => true,
			'message' => __('landing.success.about_saved'),
		]);
	}

	/**
	 * Filter, normalize, and cap About Us info cards.
	 */
	protected function processAboutCards(array $cards): array
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
            return abort(401);
        }
		$validCards = [];

		foreach ($cards as $card) {
			if (!empty($card['icon']) || !empty($card['title']) || !empty($card['title_ar']) || !empty($card['description']) || !empty($card['description_ar'])) {
				$validCards[] = [
					'icon' => $card['icon'] ?? '',
					'title' => $card['title'] ?? '',
					'title_ar' => $card['title_ar'] ?? '',
					'description' => $card['description'] ?? '',
					'description_ar' => $card['description_ar'] ?? '',
				];
			}
		}

		return array_slice($validCards, 0, 3);
	}

	/**
	 * Save the How It Works section for a custom landing page.
	 */
	public function updateHow(Request $request, $id)
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
            return abort(401);
        }
		$page = $this->scopedLandingPagesQuery()->findOrFail($id);
		$title_max_length = LandingPage::TITLE_MAX_LENGTH;
		$description_max_length = LandingPage::DESCRIPTION_MAX_LENGTH;
		$steps_description_max_length = LandingPage::STEPS_DESCRIPTION_MAX_LENGTH;

		$validator = Validator::make($request->all(), [
			'how.is_enabled' => 'nullable|boolean',
			'how.title' => 'nullable|string|max:' . $title_max_length,
			'how.title_ar' => 'nullable|string|max:' . $title_max_length,
			'how.title_tag' => 'nullable|string|max:' . $title_max_length,
			'how.title_tag_ar' => 'nullable|string|max:' . $title_max_length,
			'how.description' => 'nullable|string|max:' . $description_max_length,
			'how.description_ar' => 'nullable|string|max:' . $description_max_length,
			'how.steps' => 'nullable|array|max:3',
			'how.steps.*.icon' => 'nullable|string|max:50',
			'how.steps.*.title' => 'nullable|string|max:' . $title_max_length,
			'how.steps.*.title_ar' => 'nullable|string|max:' . $title_max_length,
			'how.steps.*.description' => 'nullable|string|max:' . $steps_description_max_length,
			'how.steps.*.description_ar' => 'nullable|string|max:' . $steps_description_max_length,
		]);

		$validator->after(function ($validator) use ($request) {
			$how = $request->input('how', []);
			$isEnabled = !empty($how['is_enabled']);

			if ($isEnabled) {
				$mainFields = [
					'how.title' => [$how['title'] ?? '', __('landing.validation.section_title_required')],
					'how.title_ar' => [$how['title_ar'] ?? '', __('landing.validation.section_title_ar_required')],
					'how.title_tag' => [$how['title_tag'] ?? '', __('landing.validation.section_subtitle_required')],
					'how.title_tag_ar' => [$how['title_tag_ar'] ?? '', __('landing.validation.section_subtitle_ar_required')],
					'how.description' => [$how['description'] ?? '', __('landing.validation.section_description_required')],
					'how.description_ar' => [$how['description_ar'] ?? '', __('landing.validation.section_description_ar_required')],
				];

				foreach ($mainFields as $field => [$value, $message]) {
					if (!trim((string) $value)) {
						$validator->errors()->add($field, $message);
					}
				}
			}

			$stepIconMsg = __('landing.validation.icon_required');
			$stepTitleMsg = __('landing.validation.step_title_required');
			$stepTitleArMsg = __('landing.validation.step_title_ar_required');
			$stepDescriptionMsg = __('landing.validation.step_description_required');
			$stepDescriptionArMsg = __('landing.validation.step_description_ar_required');

			foreach (($how['steps'] ?? []) as $index => $step) {
				$icon = trim((string) ($step['icon'] ?? ''));
				$title = trim((string) ($step['title'] ?? ''));
				$titleAr = trim((string) ($step['title_ar'] ?? ''));
				$description = trim((string) ($step['description'] ?? ''));
				$descriptionAr = trim((string) ($step['description_ar'] ?? ''));

				if (!$icon && ($title || $description)) {
					$validator->errors()->add("how.steps.{$index}.icon", $stepIconMsg);
				}

				if (!$title && ($icon || $description)) {
					$validator->errors()->add("how.steps.{$index}.title", $stepTitleMsg);
				}

				if (!$titleAr && ($icon || $description)) {
					$validator->errors()->add("how.steps.{$index}.title_ar", $stepTitleArMsg);
				}

				if (!$description && ($title || $icon)) {
					$validator->errors()->add("how.steps.{$index}.description", $stepDescriptionMsg);
				}

				if (!$descriptionAr && ($title || $icon)) {
					$validator->errors()->add("how.steps.{$index}.description_ar", $stepDescriptionArMsg);
				}
			}
		});

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => $validator->errors()->first(),
				'errors' => $validator->errors(),
			], 422);
		}

		$how = $request->input('how', []);

		$page->how_is_enabled = !empty($how['is_enabled']);
		$page->how_title = $how['title'] ?? null;
		$page->how_title_ar = $how['title_ar'] ?? null;
		$page->how_title_tag = $how['title_tag'] ?? null;
		$page->how_title_tag_ar = $how['title_tag_ar'] ?? null;
		$page->how_description = $how['description'] ?? null;
		$page->how_description_ar = $how['description_ar'] ?? null;
		$page->how_steps = $this->processHowSteps($how['steps'] ?? []);
		$page->save();

		return response()->json([
			'success' => true,
			'message' => __('landing.success.how_saved'),
		]);
	}

	/**
	 * Filter, normalize, and cap How It Works process steps.
	 */
	protected function processHowSteps(array $steps): array
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
            return abort(401);
        }
		$validSteps = [];

		foreach ($steps as $step) {
			if (!empty($step['icon']) || !empty($step['title']) || !empty($step['title_ar']) || !empty($step['description']) || !empty($step['description_ar'])) {
				$validSteps[] = [
					'icon' => $step['icon'] ?? '',
					'title' => $step['title'] ?? '',
					'title_ar' => $step['title_ar'] ?? '',
					'description' => $step['description'] ?? '',
					'description_ar' => $step['description_ar'] ?? '',
				];
			}
		}

		return array_slice($validSteps, 0, 3);
	}

	/**
	 * Save the Custom Sections for a custom landing page. Each block is
	 * fully optional; if any of its mandatory fields is filled, all of
	 * them become required. Button Text/Link are always optional, but
	 * the Button Link must be a valid URL when provided.
	 */
	public function updateCustomSections(Request $request, $id)
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
            return abort(401);
        }
		$page = $this->scopedLandingPagesQuery()->findOrFail($id);
		$title_max_length = LandingPage::TITLE_MAX_LENGTH;

		$requiredMsg = __('landing.validation.custom_section_field_required');
		$buttonLinkMsg = __('landing.validation.button_link_invalid');
		$titleMaxMsg = __('landing.validation.custom_section_field_max', ['max' => $title_max_length]);

		$validator = Validator::make($request->all(), [
			'custom_sections' => 'nullable|array',
			'custom_sections.*.id' => 'nullable|integer',
			'custom_sections.*.title' => 'nullable|string|max:' . $title_max_length,
			'custom_sections.*.title_ar' => 'nullable|string|max:' . $title_max_length,
			'custom_sections.*.title_tag' => 'nullable|string|max:' . $title_max_length,
			'custom_sections.*.title_tag_ar' => 'nullable|string|max:' . $title_max_length,
			'custom_sections.*.content' => 'nullable|string',
			'custom_sections.*.content_ar' => 'nullable|string',
			'custom_sections.*.button_text' => 'nullable|string|max:255',
			'custom_sections.*.button_link' => ['nullable', 'string', 'max:500', 'url'],
		], [
			'custom_sections.*.button_link.url' => $buttonLinkMsg,
			'custom_sections.*.title.max' => $titleMaxMsg,
			'custom_sections.*.title_ar.max' => $titleMaxMsg,
			'custom_sections.*.title_tag.max' => $titleMaxMsg,
			'custom_sections.*.title_tag_ar.max' => $titleMaxMsg,
		]);

		$validator->after(function ($validator) use ($request, $requiredMsg) {
			$sections = $request->input('custom_sections', []);

			$requiredFields = ['title', 'title_ar', 'title_tag', 'title_tag_ar', 'content', 'content_ar'];

			foreach ($sections as $index => $section) {
				// A section with its visibility switch off is never rendered, so
				// its fields are legitimately left incomplete; requiring them
				// would block saving a section nobody can see. The length and
				// URL rules above still apply, since the values are stored either way.
				if (empty($section['visibility'])) {
					continue;
				}

				$values = [];
				foreach ($requiredFields as $field) {
					$values[$field] = trim((string) ($section[$field] ?? ''));
				}

				$anyFilled = collect($values)->contains(fn ($v) => $v !== '');

				if (!$anyFilled) {
					continue;
				}

				foreach ($requiredFields as $field) {
					if ($values[$field] === '') {
						$validator->errors()->add("custom_sections.{$index}.{$field}", $requiredMsg);
					}
				}
			}
		});

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => $validator->errors()->first(),
				'errors' => $validator->errors(),
			], 422);
		}

		$result = app(LandingPageCustomSectionService::class)->update($page->id, $request->input('custom_sections', []));

		return response()->json($result, $result['success'] ? 200 : 422);
	}

	/**
	 * Delete a single custom section from a landing page.
	 */
	public function destroyCustomSection($id, $sectionId)
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
            return abort(401);
        }
		$page = $this->scopedLandingPagesQuery()->findOrFail($id);

		$result = app(LandingPageCustomSectionService::class)->delete($page->id, (int) $sectionId);

		return response()->json($result, $result['success'] ? 200 : 422);
	}

	/**
	 * Upload an image for insertion into a Custom Section's rich text
	 * content (used by the Quill editor's image toolbar button).
	 */
	public function uploadCustomSectionImage(Request $request, $id)
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
            return abort(401);
        }
		$this->scopedLandingPagesQuery()->findOrFail($id);

		$validator = Validator::make($request->all(), [
			'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
		], [
			'image.required' => __('landing.validation.custom_image_type_invalid'),
			'image.image' => __('landing.validation.custom_image_type_invalid'),
			'image.mimes' => __('landing.validation.custom_image_type_invalid'),
			'image.max' => __('landing.validation.custom_image_size_exceeded'),
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => $validator->errors()->first(),
			], 422);
		}

		$path = app(FileManagerService::class)->upload($request->file('image'), 'landing-pages/custom-sections', 'public_uploads', 'image');
		$url = app(FileManagerService::class)->getUrl($path, 'public_uploads');

		return response()->json([
			'success' => true,
			'location' => $url,
		]);
	}

	/**
	 * Save the Branding block of the Custom Sections tab: the hero gradient
	 * (start / middle / end) and the primary button colors. Kept separate
	 * from updateCustomSections() so branding can be saved without touching
	 * the section repeater it sits above.
	 */
	public function updateBranding(Request $request, $id)
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
			return abort(401);
		}
		$page = $this->scopedLandingPagesQuery()->findOrFail($id);

		return response()->json([
			'success' => true,
			'message' => __('landing.success.branding_saved'),
		]);
	}

	/**
	 * Check whether a custom URL slug is available for the given landing
	 * page (used by the "Custom Landing Page URL" card's live-availability
	 * check).
	 */
	public function checkCustomUrl(Request $request, $id = null)
	{
		$_user = auth()->user();
		if (!$_user->ableTo($id ? 'landing-pages.edit' : 'landing-pages.create')) {
			return abort(401);
		}
		// On the create screen there's no page yet, so $id is absent and
		// the slug is just checked for global uniqueness.
		$page = $id ? $this->scopedLandingPagesQuery()->findOrFail($id) : null;

		// Validate the slug exactly as it was typed. Lowercasing it first would
		// make "MyPage" pass the pattern check and be stored as "mypage",
		// silently accepting input the field's own format rule rejects.
		$slug = trim((string) $request->input('slug'));

		if (mb_strlen($slug) > LandingPage::SLUG_MAX_LENGTH) {
			return response()->json([
				'valid' => false,
				'available' => false,
				'message' => __('landing.validation.custom_url_slug_max', ['max' => LandingPage::SLUG_MAX_LENGTH]),
			]);
		}

		if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
			return response()->json([
				'valid' => false,
				'available' => false,
				'message' => __('landing.validation.custom_url_invalid'),
			]);
		}

		$taken = LandingPage::where('slug', $slug)
			->when($page, fn ($query) => $query->where('id', '!=', $page->id))
			->exists();

		return response()->json([
			'valid' => true,
			'available' => !$taken,
			'message' => $taken
				? __('landing.validation.custom_url_taken')
				: __('landing.validation.custom_url_available'),
		]);
	}

	/**
	 * Save the custom URL card for a landing page: the page's title,
	 * public slug, and active (published) status. "URL Name" is a
	 * display-only convenience field with no dedicated column yet, so it
	 * isn't persisted.
	 */
	public function saveCustomUrl(Request $request, $id)
	{
		$_user = auth()->user();
		if (!$_user->ableTo('landing-pages.edit')) {
			return abort(401);
		}
		$page = $this->scopedLandingPagesQuery()->findOrFail($id);

		// Kept raw (no strtolower) so the regex below sees the real input and
		// rejects uppercase instead of quietly normalising it.
		$slug = trim((string) $request->input('slug'));

		$validator = Validator::make(['title' => $request->input('title'), 'slug' => $slug], [
			// 'title' => 'required|string|max:100',
			'slug' => ['required', 'string', 'max:' . LandingPage::SLUG_MAX_LENGTH, 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
		], [
			// 'title.required' => __('landing.validation.custom_url_title_required'),
			'slug.required' => __('landing.validation.custom_url_slug_required'),
			'slug.max' => __('landing.validation.custom_url_slug_max', ['max' => LandingPage::SLUG_MAX_LENGTH]),
			'slug.regex' => __('landing.validation.custom_url_invalid'),
		]);

		if ($validator->fails()) {
			return response()->json([
				'message' => $validator->errors()->first(),
			], 422);
		}

		if (LandingPage::where('slug', $slug)->where('id', '!=', $page->id)->exists()) {
			return response()->json([
				'message' => __('landing.validation.custom_url_taken'),
			], 422);
		}

		// $page->title = $request->input('title');
		$page->slug = $slug;
		$page->status = (bool) $request->input('active');
		$page->save();

		return response()->json([
			'message' => __('landing.success.custom_url_saved'),
			'slug' => $slug,
			'url' => url('/lp/' . $slug),
		]);
	}
}
