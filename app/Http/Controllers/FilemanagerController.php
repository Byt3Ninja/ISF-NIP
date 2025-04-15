<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class FilemanagerController extends Controller
{
    public function index() {
        if (Auth::user()->ableTo('myFiles.index')){
            return view('admin.filemanager');}
            else {return redirect('dashboard');}
    }
    public function showfile($id,$filename) {
        if (Auth::id() == $id || Auth::user()->ableTo('myFiles.showFile')) {
            
        $storagePath = storage_path('app/uploads/files/' . $id . '/' . $filename);
        if (!File::exists($storagePath)) {
            return abort(404, 'File not found.');
        }
            return response()->file($storagePath);
        } else {
          abort(401);
        }
    }
    public function showimage($id,$filename) {
        if (Auth::id() == $id || Auth::user()->ableTo('myFiles.showImage')) {
            $storagePath = storage_path('app/uploads/photos/' . $id . '/' . $filename);
            return response()->file($storagePath);
        } else {abort(401);}
    }
    public function allfiles() {
        if (Auth::user()->ableTo('myFiles.index')){
            return view('admin.filemanager');}
        else {return redirect('dashboard');}
    }
    public function trainingfile($filename) {
            $storagePath = storage_path('app/training/files/' . $filename);
            return response()->file($storagePath);
    }
    public function trainingimage($filename) {
            $storagePath = storage_path('app/training/photos/'. $filename);
            return response()->file($storagePath);
    }
    public function showprivate($id,$filename) {
        if (Auth::user()->ableTo('myFiles.index')) {
            $storagePath = storage_path('app/users/' . $id . '/' . $filename);
            return response()->file($storagePath);
        } else {
            return redirect('dashboard');
        }
    }
}