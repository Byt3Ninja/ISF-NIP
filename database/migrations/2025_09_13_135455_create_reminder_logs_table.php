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
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cycle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->nullable()->comment('user,mentor');
            $table->string('channel')->nullable()->comment(' [email,in-app,whatsapp]');
            $table->string('status')->nullable()->comment('[sent,failed,skipped]');
            $table->string('delivery_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
