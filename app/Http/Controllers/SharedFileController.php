<?php

namespace App\Http\Controllers;

use App\SharedFile;
use App\Services\FileManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SharedFileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $user = auth()->user();

        // Validation
        try {
            $request->validate([
                'file'        => 'required|file|mimes:ppt,pptx,rar,zip,doc,docx,pdf|max:10240',
                'description' => 'nullable|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('validation.mimes'),
                'errors' => $e->errors(),
            ], 422);
        }

        // Ensure user belongs to a team — and is still ON it. Without the
        // acceptInvition filter a removed member kept uploading into their old
        // team, attributed to the stale membership row.
        $team_member = DB::select(
            "SELECT id, team_formation_id FROM team_members WHERE user_id = ? AND acceptInvition = 'accepted'",
            [$user->id]
        );
        if (empty($team_member)) {
            return response()->json([
                'status'  => 'error',
                'message' => __('cycles.no_team_for_user'),
            ], 400);
        }

        // File Upload — store on the private disk via the hardened service (server-generated name + blacklist)
        $file         = $request->file('file');
        $relativePath = app(FileManagerService::class)->upload($file, 'sharedfiles', 'hackathon_shared');
        // Sanitize the client-supplied display name at the write boundary (defense in depth):
        // neutralizes stored XSS and path injection before it ever reaches the database.
        $originalName = $this->sanitizeFileName($file->getClientOriginalName(), basename($relativePath));

        // Save record
        $sharedFile = new SharedFile();
        $sharedFile->team_formation_id = $team_member[0]->team_formation_id;
        $sharedFile->team_member_id    = $team_member[0]->id;
        $sharedFile->file              = $relativePath;
        $sharedFile->original_name     = $originalName;
        $sharedFile->description       = $request->description;
        $sharedFile->user_id           = $user->id;
        $sharedFile->cycle_id          = $user->team_member->teamFormation->user->company->cycle;
        $sharedFile->step_id           = $user->team_member->teamFormation->user->company->step;
        $sharedFile->save();

        // Success Response
        return response()->json([
            'status'        => 'success',
            'message'       => __('cycles.file_upload_success'),
            'id'            => $sharedFile->id,
            'file'          => $sharedFile->file,
            'original_name' => $sharedFile->original_name,
        ]);
    }

    /**
     * Sanitize a client-supplied file name for safe storage/display.
     * Drops any path component, strips HTML tags and characters a real file
     * name never contains (< > " ' \ / and control chars), collapses
     * whitespace, and caps the length. Preserves spaces and unicode
     * (e.g. Arabic) and the extension. Falls back to $fallback when empty.
     */
    private function sanitizeFileName(string $name, string $fallback): string
    {
        // Keep only the base name — no directory traversal survives.
        $name = basename($name);
        // Remove any HTML/script markup.
        $name = strip_tags($name);
        // Remove angle brackets, quotes, slashes/backslashes, and control chars.
        $name = preg_replace('/[<>"\'\\\\\/\x00-\x1F\x7F]/u', '', $name);
        // Collapse runs of whitespace and trim.
        $name = trim(preg_replace('/\s+/u', ' ', $name));
        // Cap length to fit the column and stay reasonable.
        $name = mb_substr($name, 0, 255);

        return $name !== '' ? $name : $fallback;
    }

    /**
     * Stream a shared file to authorized users only.
     * Authorized = team member of the file's team_formation, OR admin,
     * OR org-scoped subadmin / organizationManager. Everyone else -> 404.
     */
    public function download($id)
    {
        $file = SharedFile::find($id);
        if (!$file) {
            abort(404);
        }

        $user = auth()->user();
        if (!$this->canAccessTeam($user, $file->team_formation_id)) {
            abort(404);
        }

        if (!Storage::disk('hackathon_shared')->exists($file->file)) {
            abort(404);
        }

        $downloadName = $file->original_name ?: basename($file->file);
        return Storage::disk('hackathon_shared')->download($file->file, $downloadName);
    }

    /**
     * Whether $user may access files of team_formation $teamFormationId.
     */
    private function canAccessTeam($user, $teamFormationId): bool
    {
        if (!$user) {
            return false;
        }

        // Team member of this team formation. Must still be ON the team:
        // acceptInvition filters out removed / rejected / cancelled / pending
        // rows, which previously kept granting access after a member was removed.
        $isMember = DB::table('team_members')
            ->where('team_formation_id', $teamFormationId)
            ->where('user_id', $user->id)
            ->where('acceptInvition', 'accepted')
            ->exists();
        if ($isMember) {
            return true;
        }

        // Full admin.
        if ($user->hasRole('admin')) {
            return true;
        }

        // Org-scoped subadmin / organizationManager: team's cycle -> organization must be in scope.
        if ($user->hasAnyRole(['subadmin', 'organizationManager'])) {
            $organizationId = DB::table('team_formations as tf')
                ->join('cycles as c', 'c.id', '=', 'tf.cycle_id')
                ->where('tf.id', $teamFormationId)
                ->value('c.organization_id');

            if ($organizationId && in_array($organizationId, $user->organizations_ids)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether $user may delete shared file $file.
     * Allowed = the file's uploader (shared_files.user_id),
     * the team leader (team_formations.user_id), or a full admin.
     * Mirrors the delete-button visibility rule in the manage-team view.
     */
    private function canDeleteFile($user, SharedFile $file): bool
    {
        if (!$user) {
            return false;
        }

        // Uploader of this specific file.
        if ($file->user_id == $user->id) {
            return true;
        }

        // Full admin.
        if ($user->hasRole('admin')) {
            return true;
        }

        // Team leader (owner of the team formation this file belongs to).
        $teamLeaderId = DB::table('team_formations')
            ->where('id', $file->team_formation_id)
            ->value('user_id');

        return $teamLeaderId !== null && $teamLeaderId == $user->id;
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

            $file = SharedFile::find($id);
        if (!$file) {
            return response()->json(['status' => 'error', 'message' => __('cycles.file_not_found')], 404);
        }

        // Authorization: only the file's uploader, the team leader, or an admin may delete.
        // Mirrors the delete button's visibility rule in manage-team.blade.php.
        if (!$this->canDeleteFile(auth()->user(), $file)) {
            return response()->json(['status' => 'error', 'message' => __('cycles.file_delete_unauthorized')], 403);
        }

        // Delete the physical file from the private disk
        if (Storage::disk('hackathon_shared')->exists($file->file)) {
            Storage::disk('hackathon_shared')->delete($file->file);
        }

        $file->delete();

        return response()->json(['status' => 'success', 'message' => __('cycles.file_delete_success')]);

    }
}
