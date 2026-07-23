<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('alumni')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                $raw = $row->basic_startup_information ?? null;
                if (empty($raw)) continue;

                $info = json_decode($raw, true);
                if (!is_array($info)) continue;

                $changed = false;
                foreach (['Sectors', 'Industries', 'Technologies'] as $key) {
                    if (isset($info[$key]) && is_string($info[$key])) {
                        $decoded = json_decode($info[$key], true);
                        if ($decoded !== null) {
                            $info[$key] = $decoded;
                            $changed = true;
                        }
                    }
                }

                if ($changed) {
                    DB::table('alumni')->where('id', $row->id)->update([
                        'basic_startup_information' => json_encode($info),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        //
    }
};
