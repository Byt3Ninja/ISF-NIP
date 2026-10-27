<?php

namespace App\Http\Controllers;

use App\Services\ProtectedFiles\FileAccess;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class FilemanagerController extends Controller
{
    private function safePath(string $basePath, string $filename): string
    {
        $filename = basename($filename);
        $fullPath = $basePath . DIRECTORY_SEPARATOR . $filename;
        $realBase = realpath($basePath);
        $realFull = realpath($fullPath);

        if ($realBase === false || $realFull === false || !str_starts_with($realFull, $realBase)) {
            abort(404, 'File not found.');
        }

        return $realFull;
    }

    /**
     * H6: a user's file-manager folders are readable by the owner, by whoever may manage the
     * owner's startup (admin, teammates, staff of its program), and by judges assigned to it.
     */
    private function canOpenUserFiles($ownerId): bool
    {
        $viewer = Auth::user();
        if ((int) $viewer->id === (int) $ownerId) {
            return true;
        }

        $companyId = User::whereKey((int) $ownerId)->value('company_id');
        $fileAccess = app(FileAccess::class);

        return $viewer->hasRole('admin')
            || ($companyId && ($fileAccess->canManageCompany($viewer, (int) $companyId) || $fileAccess->isAssignedJudge($viewer, (int) $companyId)));
    }

    public function index() {
        if (Auth::user()->ableTo('my_files.list')){
            return view('admin.filemanager');}
            else {return redirect('dashboard');}
    }
    public function showfile($id,$filename) {
        if ($this->canOpenUserFiles($id)) {
            $storagePath = $this->safePath(storage_path('app/uploads/files/' . intval($id)), $filename);
            return response()->file($storagePath);
        } else {
          abort(403);
        }
    }
    public function showimage($id,$filename) {
        if ($this->canOpenUserFiles($id)) {
            $storagePath = $this->safePath(storage_path('app/uploads/photos/' . intval($id)), $filename);
            return response()->file($storagePath);
        } else {abort(403);}
    }
    public function allfiles() {
        if (Auth::user()->ableTo('my_files.list')){
            return view('admin.filemanager');}
        else {return redirect('dashboard');}
    }
    public function trainingfile($filename) {
            $storagePath = $this->safePath(storage_path('app/training/files'), $filename);
            return response()->file($storagePath);
    }
    public function trainingimage($filename) {
            $storagePath = $this->safePath(storage_path('app/training/photos'), $filename);
            return response()->file($storagePath);
    }
    public function showprivate($id,$filename) {
        abort_unless($this->canOpenUserFiles($id), 403);
        $storagePath = $this->safePath(storage_path('app/users/' . intval($id)), $filename);
        return response()->file($storagePath);
    }
}