<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\Organization;
use Illuminate\Http\Request;

class LandingPagesController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * The public custom-landing URL (publicCustomLanding) is intentionally
	 * excluded from the auth guard so visitors can open a saved link.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth')->except(['publicCustomLanding']);
	}

	/**
	 * List custom landing pages.
	 */
	public function index(Request $request)
	{
		$_user = auth()->user();
		
		return view('admin.landing-pages.index');
	}
  
  public function create(Request $request)
  {
    $cycles = Cycle::all();
    
    return view('admin.landing-pages.create', compact('cycles')); 
  }

  public function edit(Request $request)
  {
    $cycles = Cycle::all();
    
    return view('admin.landing-pages.edit', compact('cycles'));
  }
  
  public function show(Request $request)
  {
    return view('admin.landing-pages.show') ;
  }

	/**
	 * Store a landing page section.
	 *
	 * Temporary stub: always returns a successful response so the
	 * front-end section save never hits a "route not found" error.
	 *
	 * @param  string  $section
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function updateSection(Request $request, $section)
	{
		return response()->json(['success' => true]);
	}

	/**
	 * Session-backed simulated store for custom landing-page URLs.
	 *
	 * No DB model exists yet, so the saved configuration is kept in the
	 * admin's session to mimic the behaviour of the real persistence layer.
	 *
	 * @return array
	 */
	protected function customUrlStore(): array
	{
		return (array) session()->get('landing_page_urls', []);
	}

	/**
	 * Slugs that can no longer be claimed: real organization slugs plus
	 * every custom landing-page URL saved earlier in this session.
	 *
	 * @return array
	 */
	protected function customUrlTakenSlugs(): array
	{
		$orgSlugs = Organization::whereNotNull('slug')
			->where('slug', '!=', '')
			->pluck('slug')
			->unique()
			->values()
			->all();

		$savedSlugs = array_keys($this->customUrlStore());

		return array_values(array_unique(array_merge($orgSlugs, $savedSlugs)));
	}

	/**
	 * Simulated "Check Availability" endpoint for a custom landing-page URL.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function checkCustomUrl(Request $request)
	{
		$locale = app()->getLocale();
		$slug = strtolower(trim((string) $request->input('slug', '')));

		if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
			return response()->json([
				'valid' => false,
				'available' => false,
				'message' => $locale === 'ar'
					? 'يمكن أن يحتوي الرابط على أحرف إنجليزية صغيرة وأرقام وشرطات فقط.'
					: 'URL slug can only contain lowercase letters, numbers, and hyphens.',
			]);
		}

		if (in_array($slug, $this->customUrlTakenSlugs())) {
			return response()->json([
				'valid' => true,
				'available' => false,
				'message' => $locale === 'ar'
					? 'هذا الرابط مستخدم بالفعل. يرجى اختيار رابط آخر.'
					: 'This URL is already taken. Please choose another one.',
			]);
		}

		return response()->json([
			'valid' => true,
			'available' => true,
			'message' => $locale === 'ar'
				? 'هذا الرابط متاح.'
				: 'This URL is available.',
		]);
	}

	/**
	 * Simulated "Save" endpoint for the custom landing-page URL configuration.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function saveCustomUrl(Request $request)
	{
		$locale = app()->getLocale();
		$title = trim((string) $request->input('title', ''));
		$name = trim((string) $request->input('name', ''));
		$slug = strtolower(trim((string) $request->input('slug', '')));
		$active = $request->boolean('active');

		$fail = function (string $message) {
			return response()->json(['success' => false, 'message' => $message], 422);
		};

		if ($name === '') {
			return $fail($locale === 'ar'
				? 'يرجى إدخال الاسم.'
				: 'Please enter the name.');
		}

		if ($title === '') {
			return $fail($locale === 'ar'
				? 'يرجى إدخال العنوان.'
				: 'Please enter the title.');
		}

		if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
			return $fail($locale === 'ar'
				? 'يمكن أن يحتوي الرابط على أحرف إنجليزية صغيرة وأرقام وشرطات فقط.'
				: 'URL slug can only contain lowercase letters, numbers, and hyphens.');
		}

		if (in_array($slug, $this->customUrlTakenSlugs())) {
			return $fail($locale === 'ar'
				? 'هذا الرابط مستخدم بالفعل. يرجى اختيار رابط آخر.'
				: 'This URL is already taken. Please choose another one.');
		}

		// Simulated persistence — swap for a real model once wired up.
		$store = $this->customUrlStore();
		$store[$slug] = [
			'title'     => $title,
			'name'      => $name,
			'active'    => $active,
			'saved_at'  => now()->toDateTimeString(),
		];
		session()->put('landing_page_urls', $store);

		return response()->json([
			'success' => true,
			'active'  => $active,
			'url'     => url('/lp/' . $slug),
			'message' => $locale === 'ar'
				? 'تم حفظ رابط صفحة الهبوط المخصصة بنجاح.'
				: 'Custom landing page URL saved successfully.',
		]);
	}

	/**
	 * Public access to a saved custom landing-page URL.
	 *
	 * A slug that was never saved (or whose landing page is disabled)
	 * resolves to a 404, exactly like the real persistence layer will.
	 *
	 * @param  string  $slug
	 * @return \Illuminate\View\View|\Illuminate\Http\Response
	 */
	public function publicCustomLanding($slug)
	{
		$slug = strtolower(trim((string) $slug));
		$config = $this->customUrlStore()[$slug] ?? null;

		if (!$config || empty($config['active'])) {
			abort(404);
		}

		return view('admin.landing-pages.show');
	}
}
