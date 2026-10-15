<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who opened (or was refused) which confidential file, when and from where.
     */
    public function up(): void
    {
        Schema::create('file_access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('protected_file_id')->nullable();
            $table->string('action', 20);                // view | download | denied | legacy | export
            $table->string('set', 40)->nullable();
            $table->string('requested', 500)->nullable(); // what was asked for when no file record matched
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['protected_file_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_access_logs');
    }
};
