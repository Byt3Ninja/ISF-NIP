<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\Jobs\ExportCycleData;
use App\Organization;
use Illuminate\Http\Request;


class ProgramController extends Controller
{

    public function exportProg(Request $request)
    {
        // تحقق من أن البريد الإلكتروني موجود
        $userEmail = auth()->user()->email;

        // إرسال وظيفة (Job) لتحميل البيانات وإرسال البريد
        ExportCycleData::dispatch($userEmail);

        return response()->json(['message' => 'Export process started. You will receive an email with the download attached.']);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(auth()->user()->title_en == 'Administrator'){

         $cycles = Cycle::all();
        
        }else{
            
            $Organization = Organization::where('user_id','=', auth()->user()->id)->first();
            
            $cycles = Cycle::where('organization_id','=', $Organization->id)->get();
            
        }
        return view('admin.programs.index', compact('cycles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
