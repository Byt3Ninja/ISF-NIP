<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Team logos were historically written by two different code paths: the
     * create path move()d them into public/programs/teams (inside the web root,
     * and git-tracked), while the edit path wrote them to the `programTeams`
     * disk at storage/app/public/programs/teams. Both stored the same bare
     * 'programs/teams/<name>' value, so rows written by the edit path pointed at
     * files no view could resolve.
     *
     * Uploads now go exclusively to the storage disk, so this moves any
     * remaining file out of public/ and clears rows whose file is gone from
     * both locations (the views already fall back to the default team SVG).
     */
    public function up(): void
    {
        $storageDir = storage_path('app/public/programs/teams');

        if (!File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        $rows = DB::table('team_formations')
            ->whereNotNull('img_team')
            ->where('img_team', '!=', '')
            ->select('id', 'img_team')
            ->get();

        foreach ($rows as $row) {
            $filename = basename($row->img_team);

            if ($filename === '') {
                continue;
            }

            $publicPath = public_path('programs/teams/' . $filename);
            $storagePath = $storageDir . DIRECTORY_SEPARATOR . $filename;

            if (File::exists($storagePath)) {
                // Already on the storage disk (written by the edit path).
                continue;
            }

            if (File::exists($publicPath)) {
                File::move($publicPath, $storagePath);
                Log::info('Moved team logo to storage disk', [
                    'team_formation_id' => $row->id,
                    'file' => $filename,
                ]);
                continue;
            }

            // File is gone from both locations -- clear the dead reference so
            // the UI shows the default team logo instead of a broken image.
            DB::table('team_formations')
                ->where('id', $row->id)
                ->update(['img_team' => null]);

            Log::warning('Cleared missing team logo reference', [
                'team_formation_id' => $row->id,
                'file' => $filename,
            ]);
        }
    }

    /**
     * Files are moved back into the public directory. Rows nulled in up()
     * cannot be restored -- their files no longer existed anywhere.
     */
    public function down(): void
    {
        $publicDir = public_path('programs/teams');

        if (!File::isDirectory($publicDir)) {
            File::makeDirectory($publicDir, 0755, true);
        }

        $rows = DB::table('team_formations')
            ->whereNotNull('img_team')
            ->where('img_team', '!=', '')
            ->select('id', 'img_team')
            ->get();

        foreach ($rows as $row) {
            $filename = basename($row->img_team);

            if ($filename === '') {
                continue;
            }

            $storagePath = storage_path('app/public/programs/teams/' . $filename);
            $publicPath = $publicDir . DIRECTORY_SEPARATOR . $filename;

            if (File::exists($storagePath) && !File::exists($publicPath)) {
                File::move($storagePath, $publicPath);
            }
        }
    }
};
