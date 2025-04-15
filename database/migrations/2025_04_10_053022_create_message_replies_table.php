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
        Schema::create('message_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('messenger_id'); // الرسالة الأصلية
            $table->unsignedBigInteger('reply_id');     // الرسالة التي تعتبر رد
            $table->timestamps();
            $table->foreign('messenger_id')->references('id')->on('messenger')->onDelete('cascade');
            $table->foreign('reply_id')->references('id')->on('messenger')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_replies');
    }
};
