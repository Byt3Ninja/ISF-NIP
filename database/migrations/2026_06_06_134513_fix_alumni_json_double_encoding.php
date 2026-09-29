<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $columns = [
        'basic_startup_information',
        'startup_leader_information',
        'founders',
        'employees',
        'shareholders',
        'investors',
        'sustainability_goals',
        'pitch_deck',
        'legal_information',
        'program',
        'screening',
        'final_screening',
        'training',
        'mentorship',
        'milestones',
        'tasks',
        'quizzes',
        'surveys',
        'services',
        'submitted_forms',
        'other',
    ];

    public function up(): void
    {
        DB::table('alumni')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                $updates = [];
                foreach ($this->columns as $col) {
                    $raw = $row->$col ?? null;
                    if (empty($raw)) {
                        continue;
                    }

                    $decoded = json_decode($raw, true);

                    // If the decoded value is a string, it was double-encoded.
                    // Decode once more to get the actual array, re-encode properly.
                    if (is_string($decoded)) {
                        $inner = json_decode($decoded, true);
                        if ($inner !== null) {
                            $updates[$col] = json_encode($inner);
                        }
                    }
                    // Already single-encoded array/object — no change needed
                }

                if (!empty($updates)) {
                    DB::table('alumni')->where('id', $row->id)->update($updates);
                }
            }
        });
    }

    public function down(): void
    {
        // Reversing double-encoding is not safe to roll back
    }
};
