<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('full_name', 100);
            $table->string('job_title', 100);
            $table->enum('gender', ['male', 'female']);
            $table->enum('employment_type', ['full-time', 'part-time', 'contractor', 'intern']);
            $table->date('hiring_date');
            $table->text('documents')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
