<?php

namespace App\Http\Controllers;

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

    public function index() {
        if (Auth::user()->ableTo('my_files.list')){
            return view('admin.filemanager');}
            else {return redirect('dashboard');}
    }
    public function showfile($id,$filename) {
        if (Auth::id() == $id || Auth::user()->ableTo('my_files.view_file')) {
            $storagePath = $this->safePath(storage_path('app/uploads/files/' . intval($id)), $filename);
            return response()->file($storagePath);
        } else {
          abort(401);
        }
    }
    public function showimage($id,$filename) {
        if (Auth::id() == $id || Auth::user()->ableTo('my_files.view_image')) {
            $storagePath = $this->safePath(storage_path('app/uploads/photos/' . intval($id)), $filename);
            return response()->file($storagePath);
        } else {abort(401);}
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
        if (Auth::user()->ableTo('my_files.list')) {
            $storagePath = $this->safePath(storage_path('app/users/' . intval($id)), $filename);
            return response()->file($storagePath);
        } else {
            return redirect('dashboard');
        }
    }
}