<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Posts;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Redirect;
use App\Services\FileManagerService;

class PostsController extends Controller
{
	public function add_post()
	{
		return view('admin.settings.posts.add_new_post');
	}

	public function store_post(Request $request)
	{
		$filename2 = null;
		if($request->file('image'))
		{
			if(in_array($request->file('image')->getClientMimeType(),["image/png","image/jpeg","image/gif"]))
			{
				$file = $request->file('image');
				$path = 'images/blog/';
				// Whitelist + real-content check; the stored name is generated server-side,
				// never taken from the client (a GIF-headered shell.php passes a MIME check).
				$filename2 = app(FileManagerService::class)->validatedSafeName($file, 'image');
				$file->move($path, $filename2);
			}
		}
		$newpost = new posts();
		$newpost->image = $filename2;
		$newpost->title = $request->input('title');
		$newpost->ar_title = $request->input('ar_title');
		$newpost->body = $request->input('body');
		$newpost->ar_body = $request->input('ar_body');
		$newpost->published = $request->input('published');
		$newpost->user_id = Auth::user()->id;
		$newpost->Save();
		return redirect('dashboard/posts')->with('message', 'StoredSuccessfully');
	}

	public function myposts()
	{
		$getposts = posts::orderBy('created_at','ASC')->get();
		return view('admin.settings.posts.post')->with('getposts',$getposts);
	}

	public function editpost($id,Request $request)
	{
		$getpost = posts::findOrfail($id);
		return view('admin.settings.posts.edit_post')->with('getpost',$getpost);
	}

	public function updatepost($id,Request $request)
	{
		$updatepost = posts::findOrfail($id);
		if($request->file('image'))
		{
			if(in_array($request->file('image')->getClientMimeType(),["image/png","image/jpeg","image/gif"]))
			{
				$file = $request->file('image');
				$path = 'images/blog/';
				// Whitelist + real-content check; the stored name is generated server-side,
				// never taken from the client (a GIF-headered shell.php passes a MIME check).
				$filename2 = app(FileManagerService::class)->validatedSafeName($file, 'image');
				$file->move($path, $filename2);
				$updatepost->image = $filename2;
			}
		}
		$updatepost->title = $request->input('title');
		$updatepost->ar_title = $request->input('ar_title');
		$updatepost->body = $request->input('body');
		$updatepost->ar_body = $request->input('ar_body');
		$updatepost->published = $request->input('published');
		if($request->input('created_at')){$updatepost->created_at = $request->input('created_at');}
		$updatepost->user_id = Auth::user()->id;
		$updatepost->Save();
		return redirect('dashboard/posts')->with('message', 'StoredSuccessfully');
	}

	public function deletepost($id)
	{
		posts::Destroy($id);
		return redirect()->back()->with('message', 'DeletedSuccessfully');
	}
}
