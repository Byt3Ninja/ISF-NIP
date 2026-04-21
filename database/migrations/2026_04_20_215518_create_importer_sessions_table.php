<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('importer_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('uploaded');
            $table->string('original_filename');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('file_hash', 64)->nullable();
            $table->unsignedInteger('row_count')->nullable();
            $table->json('headers')->nullable();
            $table->json('mappings')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->json('conflict_warnings')->nullable();
            $table->json('import_result')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importer_sessions');
    }
};
