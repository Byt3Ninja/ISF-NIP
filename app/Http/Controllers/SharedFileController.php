<?php

namespace App\Http\Controllers;

use App\SharedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        try {
            $request->validate([
                'file'        => 'required|file|mimes:pptx,zip,docx,pdf|max:10240',
                'description' => 'nullable|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = auth()->user();

        $team_member = DB::select("SELECT id, team_formation_id FROM team_members WHERE user_id = ?", [$user->id]);
        if (empty($team_member)) {
            return response()->json(['status' => 'error', 'message' => 'No team found for user'], 400);
        }

        $file = $request->file('file');
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('hackathon/sharedfiles/'), $fileName);

        $sharedFile = new SharedFile();
        $sharedFile->team_formation_id = $team_member[0]->team_formation_id;
        $sharedFile->team_member_id    = $team_member[0]->id;
        $sharedFile->file              = 'hackathon/sharedfiles/' . $fileName;
        $sharedFile->description       = $request->description;
        $sharedFile->user_id           = $user->id;
        $sharedFile->cycle_id          = $user->team_member->teamFormation->user->company->cycle;
        $sharedFile->step_id           = $user->team_member->teamFormation->user->company->step;
        $sharedFile->save();

        return response()->json(['status' => 'success', 'message' => 'File uploaded successfully']);
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
            return response()->json(['status' => 'error', 'message' => 'File not found'], 404);
        }
        // حذف الملف من السيرفر
        $filePath = public_path($file->file);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $file->delete();

        return response()->json(['status' => 'success', 'message' => 'File deleted successfully']);

    }
}
