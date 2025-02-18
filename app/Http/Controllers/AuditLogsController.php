<?php

namespace App\Http\Controllers;

use App\AuditLog;

class AuditLogsController extends Controller
{
    public function index()
    {
        $auditLogs = AuditLog::orderBy("Created_at",'DESC')->take(1000)->get();
        $TotalRec = AuditLog::count();

        return view('admin.auditLogs.index', compact('auditLogs'))->with('TotalRec',$TotalRec);
    }

    public function show(AuditLog $auditLog)
    {
        return view('admin.auditLogs.show', compact('auditLog'));
    }
    public function destroy()
    {
        AuditLog::truncate();
        return back();
    }
}