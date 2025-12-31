<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\CycleCustomPages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use URL;

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
				'content' => $request->content,
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
			$page->content = $request->content;
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
			$page->delete();
			$custom_pages = Cycle::find($request->cycleId)->custom_pages;
			$custom_pages = $custom_pages->sortBy('row_no')->values();
			return response()->json(['status' => true, 'message' => 'Custom page deleted successfully', 'pages' => $custom_pages]);
		} else { return response()->json(['status' => false, 'message' => 'You can not delete custom page for this program']); }
	}
}
