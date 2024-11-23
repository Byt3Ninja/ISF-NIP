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
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('TeamMember');
            $table->string('Name');
            $table->string('Email')->nullable();
            $table->string('LinkedIn')->nullable();
            $table->string('RoleShereholder')->nullable();
            $table->integer('PresentagShereholder')->default(0);
            $table->string('investorType')->nullable();
            $table->unsignedBigInteger('Company_id');
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint
            $table->foreign('Company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
