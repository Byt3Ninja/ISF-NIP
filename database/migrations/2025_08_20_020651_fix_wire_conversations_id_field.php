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
        // Check if we need to fix the wire_conversations table
        if (Schema::hasTable('wire_conversations')) {
            // Check if the ID field is UUID format (char(36))
            $columns = DB::select('DESCRIBE wire_conversations');
            $idColumn = collect($columns)->firstWhere('Field', 'id');
            
            if ($idColumn && $idColumn->Type === 'char(36)') {
                // Check what tables have foreign keys to conversations
                $relatedTables = ['wire_participants', 'wire_messages'];
                
                // Drop foreign key constraints temporarily if tables exist
                foreach ($relatedTables as $tableName) {
                    if (Schema::hasTable($tableName)) {
                        try {
                            Schema::table($tableName, function (Blueprint $table) {
                                $table->dropForeign(['conversation_id']);
                            });
                        } catch (\Exception $e) {
                            // Foreign key might not exist, continue
                        }
                    }
                }
                
                // Recreate the wire_conversations table with proper auto-incrementing ID
                Schema::dropIfExists('wire_conversations');
                
                Schema::create('wire_conversations', function (Blueprint $table) {
                    $table->id(); // This creates an auto-incrementing bigint primary key
                    $table->string('type')->comment('Private is 1-1 , group or channel');
                    $table->timestamp('disappearing_started_at')->nullable();
                    $table->integer('disappearing_duration')->nullable();
                    $table->index('type');
                    $table->timestamps();
                });
                
                // Fix the conversation_id columns in related tables
                foreach ($relatedTables as $tableName) {
                    if (Schema::hasTable($tableName)) {
                        try {
                            Schema::table($tableName, function (Blueprint $table) {
                                $table->dropColumn('conversation_id');
                            });
                            
                            Schema::table($tableName, function (Blueprint $table) {
                                $table->unsignedBigInteger('conversation_id')->after('id');
                                $table->foreign('conversation_id')->references('id')->on('wire_conversations')->onDelete('cascade');
                            });
                        } catch (\Exception $e) {
                            // Column might not exist or other issue, continue
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration fixes a structural issue, so we won't reverse it
        // If you need to revert, you would need to recreate the original UUID structure
    }
};
