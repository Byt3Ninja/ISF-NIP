<?php
namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Pages;
use Carbon\Carbon;
use Redirect;

class PagesController extends Controller
{
	public function add_page()
	{
		return view('admin.settings.pages.add_new_page');
	}

	public function store_page(Request $request)
	{
		if( $request->input('url') ) { $filename2 = null; }
		if( $request->file('image') )
		{
			if(in_array($request->file('image')->getClientMimeType(),["image/png","image/jpeg","image/gif"]))
			{
				$file = $request->file('image');
				$path = 'images/resource/';
				$fileExtension = $request->file('image')->getClientOriginalExtension();
				$filename = \Illuminate\Support\Str::random(5).\Illuminate\Support\Str::limit(Carbon::today().Carbon::now(),10);
				$filename2 =$filename.'.'.$fileExtension;
				$file->move($path, $filename.'.'.$fileExtension);
			}
		}
		$newpage = new Pages();
		$newpage->image = $filename2;
		$newpage->url = $request->input('url');
		$newpage->title = $request->input('title');
		$newpage->ar_title = $request->input('ar_title');
		$newpage->body = $request->input('body');
		$newpage->ar_body = $request->input('ar_body');
		$newpage->published = $request->input('published');
		$newpage->user_id = Auth::user()->id;
		$newpage->Save();
		return redirect('dashboard/pages')->with('message', 'StoredSuccessfully');
	}

	public function mypages()
	{
		$getpages = Pages::orderBy('created_at','ASC')->get();
		return view('admin.settings.pages.pages')->with('getpages',$getpages);
	}

	public function editpage($id,Request $request)
	{
		$getpage = Pages::findOrfail($id);
		return view('admin.settings.pages.edit_page')->with('getpage',$getpage);
	}

	public function editmypage()
	{
		$getpage = Pages::findOrfail(Auth::user()->PageId);
		return view('admin.settings.pages.edit_my_page')->with('getpage',$getpage);
	}

	public function updatepage($id,Request $request)
	{
		if (!Auth::user()->hasAnyPermission(['manage_pages', 'manage_page'])) {return abort(401);}
		Auth::user()->can('pages.update') ? $updatepage = Pages::findOrfail(Auth::user()->PageId) :  $updatepage = Pages::findOrfail($id);
		if($request->file('image'))
		{
			if(in_array($request->file('image')->getClientMimeType(),["image/png","image/jpeg","image/gif"]))
			{
				$file = $request->file('image');
				$path = 'images/resource/';
				$fileExtension = $request->file('image')->getClientOriginalExtension();
				$filename = \Illuminate\Support\Str::random(20).\Illuminate\Support\Str::limit(Carbon::today().Carbon::now(),10,'page');
				$filename2 =$filename.'.'.$fileExtension;
				$file->move($path, $filename.'.'.$fileExtension);
				$updatepage->image = $filename2;
			}
		}
		if($request->input('url')) { $updatepage->url = $request->input('url'); }
		$updatepage->title = $request->input('title');
		$updatepage->ar_title = $request->input('ar_title');
		$updatepage->body = $request->input('body');
		$updatepage->ar_body = $request->input('ar_body');
		$updatepage->published = $request->input('published');
		$updatepage->user_id = Auth::user()->id;
		$updatepage->Save();
		$users_ids = $request->input('users_ids');
	  	if (Auth::user()->hasanyPermission('manage_pages'))
		{
			User::where('PageId',$id)->update(['PageId' => Null]);
			if (isset($users_ids))
			{
				foreach ($users_ids as $user_id)  { User::findorfail($user_id)->update(['PageId' => $id]); }
			}
		}
		return redirect()->back()->with('message', 'StoredSuccessfully');
	}
	public function deletepage($id)
	{
		Pages::Destroy($id);
		return redirect()->back()->with('message', 'DeletedSuccessfully');
	}
}
