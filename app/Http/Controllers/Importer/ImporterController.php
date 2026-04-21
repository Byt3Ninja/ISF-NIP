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
 * Importer v2 controller. Owns every importer endpoint as of PR 7 cutover.
 * Previously routed behind /dashboard/importer/v2; now primary.
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

    public function detectConflicts(
        \App\Http\Requests\Importer\DetectConflictsRequest $request,
        string $uuid,
        ImporterSessionService $svc,
        CsvParser $parser,
        \App\Services\Importer\ConflictDetector $detector
    ): JsonResponse {
        $session = $svc->findForUserByUuid($uuid, $request->user()->id);

        if ($session->status !== ImporterSession::STATUS_VALIDATED
            && $session->status !== ImporterSession::STATUS_CONFLICTS_REVIEWED) {
            return response()->json([
                'success' => false,
                'errors'  => [[
                    'code'       => 'invalid_state_transition',
                    'message_en' => __('importer.errors.invalid_state_transition', [], 'en'),
                    'message_ar' => __('importer.errors.invalid_state_transition', [], 'ar'),
                ]],
            ], 409);
        }

        $orgId  = (int) $request->input('organization_id');
        $accept = (bool) $request->input('accept', false);

        $absPath = Storage::disk('importer')->path($session->file_path);
        $parsed  = $parser->parse($absPath);

        $warnings      = $detector->detect($parsed['rows'], $session->mappings ?? [], $orgId);
        $warningArrays = array_map(fn($w) => $w->toArray(), $warnings);

        if ($accept) {
            $session->organization_id   = $orgId;
            $session->conflict_warnings = $warningArrays;
            $session->save();
            if ($session->status === ImporterSession::STATUS_VALIDATED) {
                $svc->transitionTo($session, ImporterSession::STATUS_CONFLICTS_REVIEWED);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'warnings' => $warningArrays,
                'total'    => count($warningArrays),
            ],
        ]);
    }

    public function preview(
        Request $request,
        string $uuid,
        ImporterSessionService $svc,
        CsvParser $parser
    ): JsonResponse {
        $session = $svc->findForUserByUuid($uuid, $request->user()->id);

        if (!in_array($session->status, [
            ImporterSession::STATUS_VALIDATED,
            ImporterSession::STATUS_CONFLICTS_REVIEWED,
        ], true)) {
            return response()->json([
                'success' => false,
                'errors' => [['code' => 'invalid_state_transition', 'message_en' => __('importer.errors.invalid_state_transition', [], 'en'), 'message_ar' => __('importer.errors.invalid_state_transition', [], 'ar')]],
            ], 409);
        }

        $rowsLimit = (int) $request->input('rows', 3);
        $rowsLimit = max(1, min(20, $rowsLimit));

        $absPath = Storage::disk('importer')->path($session->file_path);
        $parsed  = $parser->parse($absPath);

        $mappings = $session->mappings ?? [];

        $columns = [];
        foreach (ImporterFieldSchema::all() as $key => $field) {
            if (!array_key_exists($key, $mappings) || $mappings[$key] === null) continue;
            $columns[] = [
                'field_key' => $key,
                'label_en'  => $field['label_en'],
                'label_ar'  => $field['label_ar'],
                'section'   => $field['section'],
                'header_index' => $mappings[$key],
            ];
        }

        $sliced = array_slice($parsed['rows'], 0, $rowsLimit);
        $rowsOut = [];
        foreach ($sliced as $r) {
            $assoc = [];
            foreach ($columns as $col) {
                $assoc[$col['field_key']] = $r[$col['header_index']] ?? '';
            }
            $rowsOut[] = $assoc;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'columns' => $columns,
                'rows'    => $rowsOut,
            ],
        ]);
    }

    public function execute(
        \App\Http\Requests\Importer\ExecuteImportRequest $request,
        string $uuid,
        ImporterSessionService $svc,
        \App\Services\Importer\ImportOrchestrator $orchestrator
    ): JsonResponse {
        $session = $svc->findForUserByUuid($uuid, $request->user()->id);

        $orgId = (int) $request->input('organization_id');
        $user  = $request->user();
        if (!$user->hasRole('Admin')) {
            $allowed = $user->organizations_ids ?? [];
            if (!in_array($orgId, $allowed, true)) {
                return response()->json([
                    'success' => false,
                    'errors' => [[
                        'code' => 'organization_forbidden',
                        'message_en' => __('importer.errors.organization_required', [], 'en'),
                        'message_ar' => __('importer.errors.organization_required', [], 'ar'),
                    ]],
                ], 403);
            }
        }

        if (!in_array($session->status, [
            ImporterSession::STATUS_VALIDATED,
            ImporterSession::STATUS_CONFLICTS_REVIEWED,
        ], true)) {
            return response()->json([
                'success' => false,
                'errors' => [[
                    'code' => 'invalid_state_transition',
                    'message_en' => __('importer.errors.invalid_state_transition', [], 'en'),
                    'message_ar' => __('importer.errors.invalid_state_transition', [], 'ar'),
                ]],
            ], 409);
        }

        try {
            $acceptedWarnings = (bool) $request->input('accepted_warnings', false);
            // Admin = no scoping (null); others scoped to their reachable org IDs.
            $reachableOrgIds = $user->hasRole('Admin') ? null : ($user->organizations_ids ?? []);
            $result = $orchestrator->run($session, $orgId, $acceptedWarnings, $reachableOrgIds);

            $session->refresh();
            $session->import_result = $result->toArray();
            $session->save();

            return response()->json(['success' => true, 'data' => $result->toArray()]);
        } catch (\Throwable $e) {
            \Log::error('Importer execute failed', ['uuid' => $uuid, 'e' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'errors' => [[
                    'code' => 'fatal',
                    'message_en' => __('importer.ui.results.fatal', [], 'en'),
                    'message_ar' => __('importer.ui.results.fatal', [], 'ar'),
                ]],
            ], 500);
        }
    }

    public function organizations(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $organizations = collect();
        if ($user->hasRole('Admin')) {
            $organizations = \App\Organization::query()->select('id', 'name_en', 'name_ar')->get();
        } else {
            $orgIds = $user->organizations_ids ?? [];
            if (!empty($orgIds)) {
                $organizations = \App\Organization::query()
                    ->select('id', 'name_en', 'name_ar')
                    ->whereIn('id', $orgIds)
                    ->get();
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'organizations' => $organizations,
            ],
        ]);
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
