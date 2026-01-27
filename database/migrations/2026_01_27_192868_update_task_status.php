<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\ProgramTask;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('program_tasks', function (Blueprint $table) {
            $table->string('status', 255)->default('Pending')->nullable()->change();
        });
        // update enum status 
        $tasks = ProgramTask::where('id', '>', 0)->get();
        foreach( $tasks as $task )
        {
            $status = trim( strtolower( $task->status ) );
            switch( $status )
            {
                case 'pending':
                    $task->status = 'pending';
                    break;
                case 'in progress':
                    $task->status = 'progress';
                    break;
                case 'in review':
                    $task->status = 'review';
                    break;
                case 'completed':
                    $task->status = 'completed';
                    break;
                default:
                    $task->status = null;
            }

            $task->save();
        }
        Schema::table('program_tasks', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'review', 'progress'])->default('pending')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //rollback enum status 
        Schema::table('program_tasks', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'review','progress'])->default('pending')->nullable()->change();
        });
    }
};
