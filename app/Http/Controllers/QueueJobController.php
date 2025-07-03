<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class QueueJobController extends Controller
{
    public function index()
    {
        $jobs = DB::table('jobs')->get();

        return view('admin.queueJob', compact('jobs'));
    }
    public function runOnce()
    {
        Artisan::call('queue:work --once');

        return back()->with('msg', 'تم تنفيذ مهمة واحدة من الـ Queue.');
    }




}
