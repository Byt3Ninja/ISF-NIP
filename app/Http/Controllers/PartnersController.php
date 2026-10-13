<?php

namespace App\Http\Controllers;
use App\Http\Requests\StorePartnerRequest;
use App\Http\Requests\UpdatePartnerRequest;
use App\Partner;
use DB;
use Log;
use Exception;
use Session;
use App\Services\FileManagerService;


class PartnersController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){

        $this->middleware('auth');


    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $partners = Partner::all();
        return view('admin/settings/partners/index', compact('partners'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $partner = Partner::all();
        return view('admin/settings/partners/create', compact('partner'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorepartnerRequest $request)
    {
        if( $request->hasFile('img_url') )
        {
            //store the image
            $img_url = $request->file('img_url');
            //get the path of the stores image
            $img_url = app(FileManagerService::class)->upload($img_url, 'partners', 'public_uploads', 'image');
        } else { $img_url = ''; }
        $user =auth()->user()->id;
        $result = Partner::create($request->except(['img_url','user_added']) + ['img_url' => $img_url,'user_added' => $user]);

        if($result)
            return redirect()->route('partners.index')->with( ['msg' => 'StoredSuccessfully'] );
        else
            return back()->withInput()->with( ['msg' => 'FailedStore'] );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\partner  partner
     * @return \Illuminate\Http\Response
     */
    public function show(Partner $partner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function edit(Partner $partner)
    {
        return view('admin/settings/partners/edit', compact('partner'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePartnerRequest $request, Partner $partner)
    {
        $user =auth()->user()->id;
        if( $request->hasFile('img_url') )
        {
            //store the image
            $img_url = $request->file('img_url');
            //get the path of the stores image
            $img_url = app(FileManagerService::class)->upload($img_url, 'partners', 'public_uploads', 'image');
            //update
            $result = $partner->update($request->except(['img_url','user_added']) + ['img_url' => $img_url,'user_added'=>$user]);
        } else { $result = $partner->update($request->except(['user_added'])+ ['user_added'=>$user]); }

        if($result)
            return redirect()->route('partners.index')->with( ['msg' => __('StoredSuccessfully')] );
        else
            return back()->withInput()->with( ['msg' => 'FailedStore'] );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function destroy(Partner $partner)
    {
        $partner->delete();
        return response()->json(['state' => 'success'], 200);
    }
}
