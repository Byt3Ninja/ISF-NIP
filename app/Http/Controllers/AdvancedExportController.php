<?php

namespace App\Http\Controllers;

use App\Services\AdvancedExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvancedExportController extends Controller
{
    protected $exportService;

    public function __construct(AdvancedExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function status()
    {
        try {
            \Illuminate\Support\Facades\Log::info('AdvancedExportController: Status method called');
            $user = Auth::user();
            if (!$user) {
                \Illuminate\Support\Facades\Log::error('AdvancedExportController: User not authenticated');
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $orgIds = $user->organizations_ids;
            if (!$user->hasRole('admin') && !($user->hasAnyRole(['organizationManager', 'subadmin']) && !empty($orgIds))) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            $organizationId = $user->hasRole('admin') ? null : $orgIds[0];
            \Illuminate\Support\Facades\Log::info('AdvancedExportController: Status requested', ['user_id' => $user->id, 'org_id' => $organizationId]);

            $status = $this->exportService->getExportStatus($organizationId);
            return response()->json($status);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AdvancedExportController: Error in status', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generate()
    {
        $user = Auth::user();
        $orgIds = $user->organizations_ids;
        // A non-admin without an organization has nothing to export. (It used to fall through
        // to the "all organizations" export that belongs to platform admins.)
        abort_unless($user->hasRole('admin') || ($user->hasAnyRole(['organizationManager', 'subadmin']) && !empty($orgIds)), 403);
        $organizationId = $user->hasRole('admin') ? null : $orgIds[0];
        try {
            $result = $this->exportService->generateExport($organizationId);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function download()
    {
        $user = Auth::user();
        $orgIds = $user->organizations_ids;
        // A non-admin without an organization has nothing to export. (It used to fall through
        // to the "all organizations" export that belongs to platform admins.)
        abort_unless($user->hasRole('admin') || ($user->hasAnyRole(['organizationManager', 'subadmin']) && !empty($orgIds)), 403);
        $organizationId = $user->hasRole('admin') ? null : $orgIds[0];
        $download = $this->exportService->downloadExport($organizationId);

        if ($download) {
            return $download;
        }

        return redirect()->back()->with('error', 'Export file not found.');
    }

    public function progress()
    {
        $user = Auth::user();
        $orgIds = $user->organizations_ids;
        // A non-admin without an organization has nothing to export. (It used to fall through
        // to the "all organizations" export that belongs to platform admins.)
        abort_unless($user->hasRole('admin') || ($user->hasAnyRole(['organizationManager', 'subadmin']) && !empty($orgIds)), 403);
        $organizationId = $user->hasRole('admin') ? null : $orgIds[0];
        $progress = $this->exportService->getProgress($organizationId);
        return response()->json(['progress' => $progress]);
    }
}
