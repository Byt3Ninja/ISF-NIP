<?php

namespace App\Http\Controllers\Importer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Importer\UploadFileRequest;
use App\Services\Importer\CsvFileValidator;
use App\Services\Importer\CsvParser;
use App\Services\Importer\CsvTemplateGenerator;
use App\Services\Importer\Exceptions\ImporterException;
use App\Services\Importer\Exceptions\InvalidStateTransitionException;
use App\Services\Importer\Exceptions\SessionNotFoundException;
use App\Services\Importer\ImporterFieldSchema;
use App\Services\Importer\ImporterSession;
use App\Services\Importer\ImporterSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * New importer controller. Methods are stubs in PR 1; they are filled in
 * PR 2–7. Legacy App\Http\Controllers\ImporterController remains untouched.
 */
class ImporterController extends Controller
{
    public function showWizard()
    {
        return view('admin.importer.v2.wizard', [
            'schemaJson'   => json_encode(ImporterFieldSchema::all(), JSON_UNESCAPED_UNICODE),
            'sectionsJson' => json_encode(ImporterFieldSchema::bySection(), JSON_UNESCAPED_UNICODE),
            'i18nJson'     => json_encode(__('importer'), JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function schema(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_fields'      => count(ImporterFieldSchema::all()),
                'sections'          => ImporterFieldSchema::SECTION_ORDER,
                'fields'            => ImporterFieldSchema::all(),
                'cross_field_rules' => ImporterFieldSchema::crossFieldRules(),
            ],
        ]);
    }

    public function downloadTemplate(CsvTemplateGenerator $gen)
    {
        $content = $gen->generate();
        return response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $gen->filename() . '"',
        ]);
    }

    public function createSession(Request $request, ImporterSessionService $svc): JsonResponse
    {
        // Session pre-creation before file upload: permits the client to hold a uuid
        // while user picks a file. The 'original_filename' is a placeholder until upload.
        $session = $svc->create($request->user()->id, '__pending__');
        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $session->uuid,
                'status'     => $session->status,
                'expires_at' => $session->expires_at->toIso8601String(),
            ],
        ]);
    }

    public function showSession(Request $request, string $uuid, ImporterSessionService $svc): JsonResponse
    {
        $session = $svc->findForUserByUuid($uuid, $request->user()->id);
        return response()->json([
            'success' => true,
            'data'    => $this->serializeSession($session),
        ]);
    }

    public function uploadFile(
        UploadFileRequest $request,
        string $uuid,
        ImporterSessionService $svc,
        CsvFileValidator $validator,
        CsvParser $parser
    ): JsonResponse {
        $session = $svc->findForUserByUuid($uuid, $request->user()->id);

        if ($session->isTerminal()) {
            return response()->json([
                'success' => false,
                'errors' => [[
                    'code'       => 'invalid_state_transition',
                    'message_en' => __('importer.errors.invalid_state_transition', [], 'en'),
                    'message_ar' => __('importer.errors.invalid_state_transition', [], 'ar'),
                ]],
            ], 409);
        }

        $file = $request->file('file');

        // Validate BEFORE persisting
        $result = $validator->validate($file);
        if (!$result->valid) {
            return response()->json([
                'success' => false,
                'errors'  => $result->toResponseArray(),
                'data'    => ['original_filename' => $file->getClientOriginalName()],
            ], 422);
        }

        // Parse structure (cache headers, row_count on session)
        $parsed = $parser->parse($file->getRealPath());

        // If session already had a file, clean it up first (supports replace)
        if ($session->file_path) {
            $svc->cleanupFile($session);
        }

        // Persist under session uuid
        Storage::disk('importer')->putFileAs(
            $session->uuid,
            $file,
            'source.csv'
        );
        $storedPath = $session->uuid . '/source.csv';

        $session->original_filename = $file->getClientOriginalName();
        $session->file_path         = $storedPath;
        $session->file_size         = $file->getSize();
        $session->file_hash         = hash_file('sha256', $file->getRealPath());
        $session->row_count         = $parsed['row_count'];
        $session->headers           = $parsed['headers'];
        $svc->touchExpiresAt($session);

        return response()->json([
            'success' => true,
            'data'    => $this->serializeSession($session->fresh()),
        ]);
    }

    public function deleteFile(
        Request $request,
        string $uuid,
        ImporterSessionService $svc
    ): JsonResponse {
        $session = $svc->findForUserByUuid($uuid, $request->user()->id);
        if ($session->isTerminal()) {
            return response()->json([
                'success' => false,
                'errors' => [[
                    'code'       => 'invalid_state_transition',
                    'message_en' => __('importer.errors.invalid_state_transition', [], 'en'),
                    'message_ar' => __('importer.errors.invalid_state_transition', [], 'ar'),
                ]],
            ], 409);
        }
        $svc->cleanupFile($session);
        $session->headers   = null;
        $session->row_count = null;
        $session->save();
        return response()->json(['success' => true, 'data' => $this->serializeSession($session->fresh())]);
    }

    public function discardSession(Request $request, string $uuid, ImporterSessionService $svc): JsonResponse
    {
        $session = $svc->findForUserByUuid($uuid, $request->user()->id);
        $svc->discard($session);
        return response()->json(['success' => true, 'data' => ['status' => $session->fresh()->status]]);
    }

    public function autoMap(
        Request $request,
        string $uuid,
        ImporterSessionService $svc,
        \App\Services\Importer\HeaderAutoMapper $mapper
    ): JsonResponse {
        $session = $svc->findForUserByUuid($uuid, $request->user()->id);

        if (empty($session->headers)) {
            return response()->json([
                'success' => false,
                'errors'  => [[
                    'code'       => 'invalid_state_transition',
                    'message_en' => __('importer.errors.invalid_state_transition', [], 'en'),
                    'message_ar' => __('importer.errors.invalid_state_transition', [], 'ar'),
                ]],
            ], 409);
        }

        $result = $mapper->match($session->headers);

        $msgKey = $result['matched_count'] > 0
            ? 'importer.ui.automap_messages.matched'
            : 'importer.ui.automap_messages.unmatched';

        return response()->json([
            'success' => true,
            'data'    => [
                'mappings'           => $result['mappings'],
                'matched_count'      => $result['matched_count'],
                'unmatched_required' => $result['unmatched_required'],
                'unmatched_optional' => $result['unmatched_optional'],
                'message_en'         => __($msgKey, [], 'en'),
                'message_ar'         => __($msgKey, [], 'ar'),
            ],
        ]);
    }

    public function validateRows(
        \App\Http\Requests\Importer\ValidateRowsRequest $request,
        string $uuid,
        ImporterSessionService $svc,
        CsvParser $parser,
        \App\Services\Importer\RowValidator $validator
    ): JsonResponse {
        $session = $svc->findForUserByUuid($uuid, $request->user()->id);

        if (!$session->file_path) {
            return response()->json([
                'success' => false,
                'errors' => [[
                    'code'       => 'invalid_state_transition',
                    'message_en' => __('importer.errors.invalid_state_transition', [], 'en'),
                    'message_ar' => __('importer.errors.invalid_state_transition', [], 'ar'),
                ]],
            ], 409);
        }

        $absPath  = Storage::disk('importer')->path($session->file_path);
        $parsed   = $parser->parse($absPath);
        $mappings = $request->input('mappings', []);

        $result = $validator->validateRows($parsed['rows'], $mappings);

        if ($result->isValid()) {
            $session->mappings = $mappings;
            $session->save();
            if ($session->status === ImporterSession::STATUS_UPLOADED) {
                $svc->transitionTo($session, ImporterSession::STATUS_VALIDATED);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $result->toResponseArray(),
        ]);
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

    private function serializeSession(ImporterSession $s): array
    {
        return [
            'session_id'        => $s->uuid,
            'status'            => $s->status,
            'original_filename' => $s->original_filename === '__pending__' ? null : $s->original_filename,
            'row_count'         => $s->row_count,
            'headers'           => $s->headers,
            'mappings'          => $s->mappings,
            'organization_id'   => $s->organization_id,
            'expires_at'        => $s->expires_at ? $s->expires_at->toIso8601String() : null,
        ];
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
