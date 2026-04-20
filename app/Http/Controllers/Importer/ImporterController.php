<?php

namespace App\Http\Controllers\Importer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * New importer controller. Methods are stubs in PR 1; they are filled in
 * PR 2–7. Legacy App\Http\Controllers\ImporterController remains untouched.
 */
class ImporterController extends Controller
{
    public function showWizard()
    {
        // PR 2 renders the wizard Blade; PR 1 returns a placeholder response
        return response()->json(['success' => true, 'data' => ['message' => 'importer_v2_placeholder']]);
    }

    public function downloadTemplate(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function createSession(Request $request): JsonResponse
    {
        return $this->notImplemented();
    }

    public function showSession(Request $request, string $uuid): JsonResponse
    {
        return $this->notImplemented();
    }

    public function uploadFile(Request $request, string $uuid): JsonResponse
    {
        return $this->notImplemented();
    }

    public function deleteFile(Request $request, string $uuid): JsonResponse
    {
        return $this->notImplemented();
    }

    public function discardSession(Request $request, string $uuid): JsonResponse
    {
        return $this->notImplemented();
    }

    public function schema(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function autoMap(Request $request, string $uuid): JsonResponse
    {
        return $this->notImplemented();
    }

    public function validateRows(Request $request, string $uuid): JsonResponse
    {
        return $this->notImplemented();
    }

    public function detectConflicts(Request $request, string $uuid): JsonResponse
    {
        return $this->notImplemented();
    }

    public function preview(Request $request, string $uuid): JsonResponse
    {
        return $this->notImplemented();
    }

    public function execute(Request $request, string $uuid): JsonResponse
    {
        return $this->notImplemented();
    }

    public function organizations(Request $request): JsonResponse
    {
        return $this->notImplemented();
    }

    private function notImplemented(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'errors'  => [[
                'code'       => 'not_implemented',
                'message_en' => 'Endpoint not implemented yet.',
                'message_ar' => 'هذا المسار لم يتم تنفيذه بعد.',
            ]],
        ], 501);
    }
}
