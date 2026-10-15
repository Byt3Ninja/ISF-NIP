<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per confidential uploaded file. This table, not the path stored in
     * the owning record, decides where a file is and who may open it.
     */
    public function up(): void
    {
        Schema::create('protected_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();              // public identifier used in URLs
            $table->string('set', 40);                   // App\Enums\ProtectedFileSet
            $table->string('owner_type', 40);            // company | milestone | employee | task | task_assignee | cycle
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('cycle_id')->nullable(); // program the file was submitted to, where that matters
            $table->string('field', 100)->nullable();    // column / form field the file belongs to
            $table->string('disk', 60);
            $table->string('path', 500);
            $table->string('original_name')->nullable();
            $table->string('mime', 120)->nullable();     // sniffed from the content at upload/migration time
            $table->unsignedBigInteger('size')->nullable();
            $table->char('sha256', 64)->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('legacy_path', 500)->nullable(); // old public URL path, for the legacy redirect
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['disk', 'path']);
            $table->index(['set', 'path']);
            $table->index(['owner_type', 'owner_id']);
            $table->index('company_id');
            $table->index('cycle_id');
            $table->index('legacy_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protected_files');
    }
};
