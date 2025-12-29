<?php

namespace App\Http\Controllers;

use App\AuditLog;

class AuditLogsController extends Controller
{
    public function index()
    {
        $auditLogs = AuditLog::orderBy('Created_at', 'DESC')->with(['user']);
        $TotalRec = $auditLogs->count();
        $auditLogs = $auditLogs->take(1000)->get();

        $auditLogs = $auditLogs->map(function ($log) {
            $class = $log->subject;
            $instance = new $class();
            $log->instance = $instance::$module;
            return $log;
        });
        return view('admin.auditLogs.index', compact('auditLogs'))->with('TotalRec',$TotalRec);
    }

    public function show(AuditLog $auditLog)
    {
        $properties = json_decode($auditLog->properties, true);
        unset( $properties['created_at'] );
        unset( $properties['updated_at'] );
        $auditLog->properties = $properties;
        return view('admin.auditLogs.show', compact('auditLog'));
    }

    public function destroy()
    {
        AuditLog::truncate();
        return back();
    }
}