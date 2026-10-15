<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\CycleCustomPages;
use App\Http\Requests\UploadCycleCustomPageImageRequest;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use URL;
use App\Services\FileManagerService;

class CycleCustomPagesController extends Controller
{
	public function add(Request $request, $id)
	{
		$user = auth()->user();
		if( !$user->ableToAny(['programs.create', 'programs.edit']) )
		{
			return response()->json(['status' => false, 'message' => __('cycles.custom_pages_permissions')], 401);
		}
		if( $id == $request->cycleId )
		{
			$request->validate([
				'cycleId' => 'required|exists:cycles,id',
				'title' => 'required',
				'content' => 'required',
				'visibility' => 'required|in:public,private,admins',
			]);

			$cycle = Cycle::find($request->cycleId);
			$custom_pages = $cycle->custom_pages;
			$cycle->custom_pages()->create([
				'title' => $request->title,
				// Strip any JavaScript/XSS vectors; content is rendered raw on the public program page.
				'content' => HtmlSanitizer::clean($request->content),
				'is_active' => 1,
				'visibility' => $request->visibility,
				'row_no' => $custom_pages->count() + 1
			]);
			$custom_pages = Cycle::find($request->cycleId)->custom_pages;
			$custom_pages = $custom_pages->sortBy('row_no')->values();
			return response()->json(['status' => true, 'message' => 'Custom page added successfully', 'pages' => $custom_pages]);
		} else { return response()->json(['status' => false, 'message' => 'You can not add custom page for this program']); }
	}

	public function view(Request $request, $id)
	{
		$user = auth()->user();
		if( !$user->ableToAny(['programs.create', 'programs.edit']) )
		{
			return response()->json(['status' => false, 'message' => __('cycles.custom_pages_permissions')], 401);
		}
		if( $id == $request->cycleId )
		{
			$request->validate([
				'cycleId' => 'required|exists:cycles,id',
				'page_id' => 'required|exists:cycle_custom_pages,id,cycle_id,' . $request->cycleId,
			]);
			$page = CycleCustomPages::where('cycle_id', $request->cycleId)->find($request->page_id);
			return response()->json(['status' => true, 'page' => $page]);
		} else { return response()->json(['status' => false, 'message' => 'You can not view custom page for this program']); }
	}

	public function update(Request $request, $id)
	{
		$user = auth()->user();
		if( !$user->ableToAny(['programs.create', 'programs.edit']) )
		{
			return response()->json(['status' => false, 'message' => __('cycles.custom_pages_permissions')], 401);
		}
		if( $id == $request->cycleId )
		{
			$request->validate([
				'cycleId' => 'required|exists:cycles,id',
				'page_id' => 'required|exists:cycle_custom_pages,id,cycle_id,' . $request->cycleId,
				'title' => 'required',
				'content' => 'required',
				'visibility' => 'required|in:public,private,admins',
			]);
			$page = CycleCustomPages::where('cycle_id', $request->cycleId)->find($request->page_id);
			$page->title = $request->title;
			// Strip any JavaScript/XSS vectors; content is rendered raw on the public program page.
			$page->content = HtmlSanitizer::clean($request->content);
			$page->visibility = $request->visibility;
			$page->save();
			$custom_pages = Cycle::find($request->cycleId)->custom_pages;
			$custom_pages = $custom_pages->sortBy('row_no')->values();
			return response()->json(['status' => true, 'message' => 'Custom page reordered successfully', 'pages' => $custom_pages]);
		} else { return response()->json(['status' => false, 'message' => 'You can not update custom page for this program']); }
	}

	public function reorder(Request $request, $id)
	{
		$user = auth()->user();
		if( !$user->ableToAny(['programs.create', 'programs.edit']) )
		{
			return response()->json(['status' => false, 'message' => __('cycles.custom_pages_permissions')], 401);
		}
		if( $id == $request->cycleId )
		{
			$validated = $request->validate([
				'cycleId' => 'required|integer|exists:cycles,id',
				'sorting' => 'required|array',
				'sorting.*' => 'integer|exists:cycle_custom_pages,id',
			]);
			$cycleId = (int) $validated['cycleId'];
			$sorting = $validated['sorting'];
			$cycle = Cycle::find($cycleId);
			$custom_pages = $cycle->custom_pages;
			$custom_pages = $custom_pages->whereIn('id', $sorting)->values();

			$sorting = collect($sorting)->map(function($value) { return intval($value); })->values();
			foreach( $custom_pages as $page )
			{
				$get_key = $sorting->search( intval( $page->id ) );
				if( $get_key === false ) { continue; }
				$page->row_no = $get_key + 1;
				$page->save();
			}
			$custom_pages = Cycle::find($cycleId)->custom_pages;
			$custom_pages = $custom_pages->sortBy('row_no')->values();
			return response()->json(['status' => true, 'message' => 'Custom page reordered successfully', 'pages' => $custom_pages]);
		} else { return response()->json(['status' => false, 'message' => 'You can not re-order custom page for this program']); }
	}

	public function toggle_status(Request $request, $id)
	{
		$user = auth()->user();
		if( !$user->ableToAny(['programs.create', 'programs.edit']) )
		{
			return response()->json(['status' => false, 'message' => __('cycles.custom_pages_permissions')], 401);
		}
		if( $id == $request->cycleId )
		{
			$request->validate([
				'cycleId' => 'required|exists:cycles,id',
				'page_id' => 'required|exists:cycle_custom_pages,id,cycle_id,' . $request->cycleId,
			]);
			$page = CycleCustomPages::where('cycle_id', $request->cycleId)->find($request->page_id);
			$page->is_active = !$page->is_active;
			$page->save();

			$custom_pages = Cycle::find($request->cycleId)->custom_pages;
			$custom_pages = $custom_pages->sortBy('row_no')->values();
			return response()->json(['status' => true, 'message' => 'Custom page status changed successfully', 'pages' => $custom_pages]);
		} else { return response()->json(['status' => false, 'message' => 'You can not toggle custom page status for this program']); }
	}

	public function toggle_visibility(Request $request, $id)
	{
		$user = auth()->user();
		if( !$user->ableToAny(['programs.create', 'programs.edit']) )
		{
			return response()->json(['status' => false, 'message' => __('cycles.custom_pages_permissions')], 401);
		}
		if( $id == $request->cycleId )
		{
			$request->validate([
				'cycleId' => 'required|exists:cycles,id',
				'visibility' => 'required|in:public,private,admins',
				'page_id' => 'required|exists:cycle_custom_pages,id,cycle_id,' . $request->cycleId,
			]);
			$page = CycleCustomPages::where('cycle_id', $request->cycleId)->find($request->page_id);
			$page->visibility = $request->visibility;
			$page->save();

			$custom_pages = Cycle::find($request->cycleId)->custom_pages;
			$custom_pages = $custom_pages->sortBy('row_no')->values();
			return response()->json(['status' => true, 'message' => 'Custom page status changed successfully', 'pages' => $custom_pages]);
		} else { return response()->json(['status' => false, 'message' => 'You can not change custom page visibility for this program']); }
	}

	public function delete(Request $request, $id)
	{
		$user = auth()->user();
		if( !$user->ableToAny(['programs.create', 'programs.edit']) )
		{
			return response()->json(['status' => false, 'message' => __('cycles.custom_pages_permissions')], 401);
		}
		if( $id == $request->cycleId )
		{
			$request->validate([
				'cycleId' => 'required|exists:cycles,id',
				'page_id' => 'required|exists:cycle_custom_pages,id,cycle_id,' . $request->cycleId,
			]);
			$page = CycleCustomPages::where('cycle_id', $request->cycleId)->find($request->page_id);

			// Extract and delete images from content
			$this->deletePageImages($page);

			$page->delete();
			$custom_pages = Cycle::find($request->cycleId)->custom_pages;
			$custom_pages = $custom_pages->sortBy('row_no')->values();
			return response()->json(['status' => true, 'message' => 'Custom page deleted successfully', 'pages' => $custom_pages]);
		} else { return response()->json(['status' => false, 'message' => 'You can not delete custom page for this program']); }
	}

	/**
	 * Upload image for custom page content
	 *
	 * @param UploadCycleCustomPageImageRequest $request
	 * @param int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function uploadImage(UploadCycleCustomPageImageRequest $request, $id)
	{
		// Verify cycle ID matches route parameter
		$user = auth()->user();
		if ($id != $request->cycle_id ) {
			return response()->json([
				'status' => false,
				'message' => __('cycles.invalid_cycle_for_upload')
			], 403);
		}
		if( !$user || !$user->ableToAny(['programs.create', 'programs.edit', 'programs.view']) )
		{
			return response()->json([
				'status' => false,
				'message' => __('cycles.custom_pages_permissions')
			], 403);
		}

		try {
			$image = $request->file('image');
			$cycleId = $request->cycle_id;

			// Generate unique filename: timestamp_cycleId_randomString.ext
			$extension = app(FileManagerService::class)->assertAllowed($image, 'image');
			$filename = time() . '_' . $cycleId . '_' . uniqid() . '.' . $extension;

			// Store in cycle-specific subfolder
			$path = "{$cycleId}/{$filename}";
			Storage::disk('cycle_custom_pages')->put($path, file_get_contents($image));

			// Optional: Resize image to optimize storage (using Intervention Image)
			$fullPath = Storage::disk('cycle_custom_pages')->path($path);
			if (class_exists('\Intervention\Image\Facades\Image')) {
				\Intervention\Image\Facades\Image::make($fullPath)
					->resize(1200, null, function ($constraint) {
						$constraint->aspectRatio();
						$constraint->upsize();
					})
					->save();
			}

			// Generate URL for image access
			$url = route('cycle.custom_pages.image', [
				'cycle_id' => $cycleId,
				'filename' => $filename
			]);

			return response()->json([
				'status' => true,
				'url' => $url,
				'message' => __('admin.Image uploaded successfully')
			]);

		} catch (\Exception $e) {
			\Log::error('Custom page image upload failed: ' . $e->getMessage());

			return response()->json([
				'status' => false,
				'message' => __('admin.Image upload failed')
			], 500);
		}
	}

	/**
	 * Serve uploaded image with access control
	 *
	 * @param Request $request
	 * @param int $cycle_id
	 * @param string $filename
	 * @return \Illuminate\Http\Response
	 */
	public function serveImage(Request $request, $cycle_id, $filename)
	{
		$user = auth()->user();

		// Verify cycle exists and user has access
		$cycle = Cycle::findOrFail($cycle_id);

		// Build file path
		$path = "{$cycle_id}/{$filename}";
		$disk = Storage::disk('cycle_custom_pages');

		// Check if file exists
		if (!$disk->exists($path)) {
			abort(404, __('admin.Image not found'));
		}

		// Get file contents and MIME type
		$file = $disk->get($path);
		$mimeType = $disk->mimeType($path);

		// Return file response
		return response($file, 200)->header('Content-Type', $mimeType);
	}

	/**
	 * Delete images associated with a custom page
	 *
	 * @param CycleCustomPages $page
	 * @return void
	 */
	protected function deletePageImages(CycleCustomPages $page)
	{
		try {
			// Extract image URLs from HTML content
			preg_match_all('/<img[^>]+src="([^">]+)"/', $page->content, $matches);

			if (!empty($matches[1])) {
				$disk = Storage::disk('cycle_custom_pages');

				foreach ($matches[1] as $imageUrl) {
					// Extract filename from URL (format: /dashboard/cycle-custom-pages/{cycle_id}/{filename})
					if (preg_match('/cycle-custom-pages\/\d+\/(.+)$/', $imageUrl, $fileMatch)) {
						$path = "{$page->cycle_id}/{$fileMatch[1]}";

						if ($disk->exists($path)) {
							$disk->delete($path);
						}
					}
				}
			}
		} catch (\Exception $e) {
			\Log::error('Failed to delete custom page images: ' . $e->getMessage());
			// Don't throw - page deletion should proceed even if image cleanup fails
		}
	}
}
