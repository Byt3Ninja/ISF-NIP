<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTypeRequest;
use App\Http\Requests\UpdateTypeRequest;

use App\Type;

use DB;
use Log;
use Exception;
use Session;


class TypeController extends Controller
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
        $types = Type::all();
        return view('admin/settings/types/index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/settings/types/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTypeRequest $request)
    {
        $result = Type::create($request->all());
        if($result)
            return redirect()->route('types.index')->with( ['msg' => __('StoredSuccessfully')] );
        else
            return back()->withInput()->with( ['msg' => __('FailedStore')] );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Type  $Type
     * @return \Illuminate\Http\Response
     */
    public function show(Type $Type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Type  $Type
     * @return \Illuminate\Http\Response
     */
    public function edit(Type $type)
    {
        return view('admin/settings/types/edit', compact('type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Type  $Type
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTypeRequest $request, Type $type)
    {
        $result = $type->update($request->all());
        if($result)
            return redirect()->route('types.index')->with( ['msg' => __('StoredSuccessfully')] );
        else
            return back()->withInput()->with( ['msg' => __('FailedStore')] );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Type  $Type
     * @return \Illuminate\Http\Response
     */
    public function destroy(Type $type)
    {
        $type->delete();
        return response()->json(['state' => 'success'], 200);
    }
}
