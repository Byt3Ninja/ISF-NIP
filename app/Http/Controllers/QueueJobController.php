<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class QueueJobController extends Controller
{
    public function index()
    {
        // Get pending/processing jobs from jobs table
        $pendingJobs = DB::table('jobs')
            ->select(
                'id',
                'queue',
                'payload',
                'attempts',
                'reserved_at',
                'available_at',
                'created_at',
                DB::raw("'Pending' as status"),
                DB::raw('NULL as finished_at'),
                DB::raw('NULL as exception'),
                DB::raw('NULL as failed_at')
            )
            ->get();

        // Get failed jobs from failed_jobs table
        $failedJobs = DB::table('failed_jobs')
            ->select(
                'id',
                'queue',
                'payload',
                DB::raw('0 as attempts'),
                DB::raw('NULL as reserved_at'),
                DB::raw('NULL as available_at'),
                DB::raw('UNIX_TIMESTAMP(failed_at) as created_at'),
                DB::raw("'Failed' as status"),
                'failed_at as finished_at',
                'exception',
                'failed_at'
            )
            ->get();

        // Combine both collections
        $jobs = $pendingJobs->concat($failedJobs);

        // Process jobs to add display information
        $jobs = $jobs->map(function ($job) {
            // Parse payload to extract job name
            $payload = json_decode($job->payload, true);
            $job->displayName = $payload['displayName'] ?? $payload['data']['commandName'] ?? 'Unknown Job';
            
            // Determine status for pending jobs
            if ($job->status === 'Pending') {
                $job->status = $job->reserved_at ? 'Processing' : 'Pending';
            }
            
            // Convert timestamps to Carbon instances for easier formatting
            $job->created_at = Carbon::createFromTimestamp($job->created_at);
            if ($job->finished_at && $job->status === 'Failed') {
                $job->finished_at = Carbon::parse($job->finished_at);
            } else {
                $job->finished_at = null;
            }
            
            return $job;
        });

        return view('admin.queueJob', compact('jobs'));
    }
    public function runOnce()
    {
        Artisan::call('queue:work --once');

        return back()->with('msg', 'تم تنفيذ مهمة واحدة من الـ Queue.');
    }




}
