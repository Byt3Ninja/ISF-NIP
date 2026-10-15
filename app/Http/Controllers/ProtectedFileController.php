<?php

namespace App\Http\Controllers;

use App\FileAccessLog;
use App\ProtectedFile;
use App\Services\ProtectedFiles\FileAccess;
use App\Services\ProtectedFiles\FileResponder;
use Illuminate\Http\Request;
use App\Services\ProtectedFiles\ProtectedFiles;
use App\Enums\ProtectedFileSet;

/**
 * The only door to confidential uploads (web session and API token alike).
 *
 * A file is addressed by its record's uuid, never by a path. Access is decided
 * by FileAccess against that record, every attempt is logged, and a refusal
 * looks exactly like a missing file.
 */
class ProtectedFileController extends Controller
{
    public function __construct(protected FileAccess $access, protected FileResponder $responder)
    {
    }

    public function show(Request $request, string $uuid)
    {
        $user = $request->user();
        $file = ProtectedFile::where('uuid', $uuid)->first();

        if (!$file || !$this->access->canView($user, $file)) {
            $this->log($request, 'denied', $file, $uuid);
            abort(404);
        }

        $download = $request->boolean('download');
        $this->log($request, $download ? 'download' : 'view', $file);

        return $this->responder->respond($file, $download);
    }

    /**
     * Old public URLs (links in sent e-mails, bookmarks, text pasted somewhere).
     * Once the public copies are retired these requests reach the app: the path is
     * only used to find the file's record, then the same rule applies as above.
     * Unknown, unrecorded or refused all look the same: 404.
     */
    public function legacy(Request $request, string $legacyFile)
    {
        $set = $this->legacySet($legacyFile);
        $file = $set ? app(ProtectedFiles::class)->find($set, $legacyFile) : null;

        if (!$file || !$this->access->canView($request->user(), $file)) {
            $this->log($request, 'denied', $file, $legacyFile);
            abort(404);
        }

        // Logged as `legacy`: a steady stream of these points at a link the app still builds the old way.
        $this->log($request, 'legacy', $file, $legacyFile);

        return $this->responder->respond($file, $request->boolean('download'));
    }

    /** Which set an old URL path belongs to (mirrors the old public folder layout). */
    protected function legacySet(string $path): ?string
    {
        foreach ([
            '#^pitch_decks/#' => ProtectedFileSet::COMPANY_PITCH,
            '#^documents/#' => ProtectedFileSet::COMPANY_LEGAL,
            '#^formsubmissions/#' => ProtectedFileSet::FORM_ANSWER,
            '#^storage/milestones/#' => ProtectedFileSet::MILESTONE_DOCUMENT,
            '#^storage/(employees|employee_documents)/#' => ProtectedFileSet::EMPLOYEE_DOCUMENT,
            '#^storage/tasks/attachments/#' => ProtectedFileSet::TASK_ATTACHMENT,
            '#^(191014/)?programs/#' => ProtectedFileSet::PROGRAM_GUIDELINE,
        ] as $pattern => $set) {
            if (preg_match($pattern, $path)) {
                return $set;
            }
        }

        return null;
    }

    protected function log(Request $request, string $action, ?ProtectedFile $file, ?string $requested = null): void
    {
        FileAccessLog::create([
            'user_id' => optional($request->user())->id,
            'protected_file_id' => optional($file)->id,
            'action' => $action,
            'set' => optional($file)->set,
            'requested' => $requested === null ? null : mb_substr($requested, 0, 500),
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);
    }
}
