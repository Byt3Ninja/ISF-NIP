<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Slider;
use Carbon\Carbon;
use Redirect;
use App\Services\FileManagerService;

class SlidersController extends Controller
{
	public function add_slide()
	{
		return view('admin.settings.sliders.add_new_slide');
	}

	public function store_slide(Request $request)
	{
		if(in_array($request->file('image')->getClientMimeType(),["image/png","image/jpeg","image/gif"]))
		{
			$file = $request->file('image');
			$path = 'images/slider/';
			// Whitelist + real-content check; the stored name is generated server-side,
			// never taken from the client (a GIF-headered shell.php passes a MIME check).
			$filename2 = app(FileManagerService::class)->validatedSafeName($file, 'image');
			$file->move($path, $filename2);
			$newslider = new Slider();
			$newslider->img_path = $filename2;
			$newslider->title = $request->input('title');
			$newslider->ar_title = $request->input('ar_title');
			$newslider->img_desc = $request->input('img_desc');
			$newslider->ar_img_desc = $request->input('ar_img_desc');
			$newslider->img_url = $request->input('img_url');
			$newslider->url_title = $request->input('url_title');
			$newslider->ar_url_title = $request->input('ar_url_title');
			$newslider->slideorder = $request->input('slideorder');
			$newslider->user_added = Auth::user()->id;
			$newslider->Save();
			return redirect('dashboard/slides')->with('message', 'StoredSuccessfully');
		} else { return redirect()->back()->with('message2', 'FailedStore'); }
	}

	public function slides()
	{
		$getSliders = Slider::orderBy('slideorder','ASC')->get();
		return view('admin.settings.sliders.sliders')->with('getSliders',$getSliders);
	}

	public function editslide($id,Request $request)
	{
		$getslider = Slider::findOrfail($id);
		return view('admin.settings.sliders.edit_slide')->with('getslider',$getslider);
	}

	public function updateslide($id,Request $request)
	{
		$updateslider = Slider::findOrfail($id);
		if($request->file('image'))
		{
			if(in_array($request->file('image')->getClientMimeType(),["image/png","image/jpeg","image/gif"]))
			{
				$file = $request->file('image');
				$path = 'images/slider/';
				// Whitelist + real-content check; the stored name is generated server-side,
				// never taken from the client (a GIF-headered shell.php passes a MIME check).
				$filename2 = app(FileManagerService::class)->validatedSafeName($file, 'image');
				$file->move($path, $filename2);
				$updateslider->img_path = $filename2;
			}
		}
		$updateslider->title = $request->input('title');
		$updateslider->ar_title = $request->input('ar_title');
		$updateslider->img_desc = $request->input('img_desc');
		$updateslider->ar_img_desc = $request->input('ar_img_desc');
		$updateslider->img_url = $request->input('img_url');
		$updateslider->url_title = $request->input('url_title');
		$updateslider->ar_url_title = $request->input('ar_url_title');
		$updateslider->slideorder = $request->input('slideorder');
		$updateslider->user_added = Auth::user()->id;
		$updateslider->Save();
		return redirect('dashboard/slides')->with('message', 'StoredSuccessfully');
	}

	public function deleteslide($id)
	{
		slider::Destroy($id);
		return redirect()->back()->with('message', 'DeletedSuccessfully');
	}
}
