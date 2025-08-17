<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTriggersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('notification_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "user_created_admin", "user_created_organization"
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event'); // e.g., "user.created", "user.role_assigned", "order.created"
            $table->json('conditions')->nullable(); // Additional conditions like role, status, etc.
            $table->unsignedBigInteger('template_id');
            $table->json('recipient_rules'); // Who should receive the notification
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher priority triggers first
            $table->json('settings')->nullable(); // Additional settings like delay, retry, etc.
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['event', 'is_active']);
            $table->index(['is_active', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('notification_triggers');
    }
}
