<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix malformed tags in the tags table
        $tags = DB::table('tags')->get();
        
        foreach ($tags as $tag) {
            $name = $tag->name;
            
            // Try to decode if it's JSON
            $decoded = json_decode($name, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // It's a JSON array, extract the actual values
                $cleanedName = $this->extractCleanName($decoded);
                
                if ($cleanedName && $cleanedName !== $name) {
                    DB::table('tags')
                        ->where('id', $tag->id)
                        ->update(['name' => $cleanedName]);
                    
                    echo "Fixed tag ID {$tag->id}: '{$name}' -> '{$cleanedName}'\n";
                }
            }
        }
        
        // Fix malformed tags in program_tasks table
        $tasks = DB::table('program_tasks')->whereNotNull('tags')->get();
        
        foreach ($tasks as $task) {
            $tags = json_decode($task->tags, true);
            
            if (is_array($tags)) {
                $cleanedTags = array_map(function($tag) {
                    return $this->extractCleanName($tag);
                }, $tags);
                
                // Remove empty values and re-index
                $cleanedTags = array_values(array_filter($cleanedTags));
                
                if ($cleanedTags !== $tags) {
                    DB::table('program_tasks')
                        ->where('id', $task->id)
                        ->update(['tags' => json_encode($cleanedTags)]);
                    
                    echo "Fixed task ID {$task->id} tags\n";
                }
            }
        }
    }

    /**
     * Extract clean name from potentially nested structure
     */
    private function extractCleanName($data): string
    {
        if (is_string($data)) {
            // Try to decode if it's JSON
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->extractCleanName($decoded);
            }
            return trim($data);
        }
        
        if (is_array($data)) {
            // If it's an array with a 'value' key, extract it
            if (isset($data['value'])) {
                return $this->extractCleanName($data['value']);
            }
            
            // If it's a numeric array, take the first element
            if (isset($data[0])) {
                return $this->extractCleanName($data[0]);
            }
        }
        
        return '';
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this migration as we're cleaning up malformed data
    }
};
