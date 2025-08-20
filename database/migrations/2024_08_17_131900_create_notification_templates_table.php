<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('channel', ['email', 'whatsapp', 'pusher']);
            $table->string('subject')->nullable(); // For email templates
            $table->text('content'); // The template content with placeholders
            $table->json('variables')->nullable(); // Available variables for this template
            $table->json('settings')->nullable(); // Channel-specific settings
            $table->string('sender')->nullable(); // Default sender
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(1);
            $table->unsignedBigInteger('parent_template_id')->nullable(); // For versioning/cloning
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('parent_template_id')->references('id')->on('notification_templates')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['channel', 'is_active']);
            $table->index('parent_template_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_templates');
    }
}
