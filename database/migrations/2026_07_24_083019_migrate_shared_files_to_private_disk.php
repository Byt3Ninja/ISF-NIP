<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Move existing hackathon shared files off the public web root onto the
     * private `hackathon_shared` disk (root storage/app/hackathon) and
     * normalize every shared_files.file to the disk-relative `sharedfiles/<name>`.
     * Rows whose physical file is missing are tolerated (path normalized only).
     * Idempotent: safe to run when files already live on the private disk.
     */
    public function up(): void
    {
        $disk = Storage::disk('hackathon_shared');
        $rows = DB::table('shared_files')->get();

        foreach ($rows as $row) {
            $basename    = basename($row->file);
            $newRelative = 'sharedfiles/' . $basename;

            // Move from the public web root if the file still lives there.
            $publicPath = public_path('hackathon/sharedfiles/' . $basename);
            if (is_file($publicPath)) {
                if (!$disk->exists($newRelative)) {
                    $disk->put($newRelative, file_get_contents($publicPath));
                }
                @unlink($publicPath);
            } elseif (!$disk->exists($newRelative)) {
                Log::info('GV-3667 migrate: source file missing, path normalized only', [
                    'shared_file_id' => $row->id,
                    'expected'       => $publicPath,
                ]);
            }

            $update = ['file' => $newRelative];
            if (empty($row->original_name)) {
                $update['original_name'] = $basename;
            }
            DB::table('shared_files')->where('id', $row->id)->update($update);
        }
    }

    /**
     * Reverse: move files back to the public web root and restore the legacy
     * `hackathon/sharedfiles/<name>` path form.
     */
    public function down(): void
    {
        $disk      = Storage::disk('hackathon_shared');
        $publicDir = public_path('hackathon/sharedfiles');
        $rows      = DB::table('shared_files')->get();

        foreach ($rows as $row) {
            $basename    = basename($row->file);
            $newRelative = 'sharedfiles/' . $basename;

            if ($disk->exists($newRelative)) {
                if (!is_dir($publicDir)) {
                    @mkdir($publicDir, 0755, true);
                }
                file_put_contents($publicDir . '/' . $basename, $disk->get($newRelative));
                $disk->delete($newRelative);
            }

            DB::table('shared_files')->where('id', $row->id)->update([
                'file' => 'hackathon/sharedfiles/' . $basename,
            ]);
        }
    }
};
