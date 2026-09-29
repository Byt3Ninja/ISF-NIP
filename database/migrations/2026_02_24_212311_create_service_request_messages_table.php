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
        Schema::create('service_request_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_request_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('body');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->timestamps();

            $table->foreign('service_request_id')
                  ->references('id')->on('services_requests')
                  ->onDelete('cascade');
            $table->foreign('sender_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->index(['service_request_id', 'created_at']);
            $table->index(['service_request_id', 'sender_id', 'read_at'], 'srm_request_sender_read_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_messages');
    }
};
