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
        Schema::create('invite_mentor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->nullable()
                  ->constrained('users')
                  ->comment('mentor user id (role = mentor)');

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->comment('inviter user id');

            $table->string('email');
            $table->string('name');

            $table->json('organization_ids')->nullable()
                  ->comment("used if scope = organization => ['id'=>1,'name'=>'PQR Industries']");

            $table->string('scope')
                  ->comment('public or organization');

            $table->string('status')
                  ->default('sent')
                  ->comment('sent or accepted');

            $table->string('subject');
            $table->longText('message_content');

            $table->json('attachments')->nullable()
                  ->comment("['product_brochure.pdf','invitation_card.jpg']");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invite_mentor');
    }
};
