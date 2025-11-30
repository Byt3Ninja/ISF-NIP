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
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('cycle_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('alumni')->default(0);
            $table->date('established_date')->nullable();
            $table->json('basic_startup_information')->nullable();   
            $table->json('startup_leader_information')->nullable();  
            $table->json('founders')->nullable();                  
            $table->json('employees')->nullable();                   
            $table->json('shareholders')->nullable();               
            $table->json('investors')->nullable();                   
            $table->json('sustainability_goals')->nullable();      
            $table->json('pitch_deck')->nullable();                  
            $table->json('legal_information')->nullable();          
            $table->json('program')->nullable();                    
            $table->json('screening')->nullable();                   
            $table->json('final_screening')->nullable();            
            $table->json('training')->nullable();                    
            $table->json('mentorship')->nullable();                 
            $table->json('milestones')->nullable();                  
            $table->json('tasks')->nullable();                       
            $table->json('quizzes')->nullable();                  
            $table->json('surveys')->nullable();                     
            $table->json('services')->nullable();                   
            $table->json('submitted_forms')->nullable();            
            $table->json('other')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
