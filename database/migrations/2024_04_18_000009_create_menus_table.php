<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->integer('menu_type');

            $table->unsignedBigInteger('parent_id');
            $table->foreign('parent_id')->references('id')->on('menus')->onDelete('cascade');

            $table->string('title', 191);
            $table->string('ar_title', 191);
            $table->string('url', 191);
            $table->string('target', 250)->default('_self');
            $table->integer('order');

            $table->unsignedBigInteger('user_added')->nullable();
            $table->foreign('user_added')->references('id')->on('users')->onDelete('SET NULL');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menus');
    }
};
