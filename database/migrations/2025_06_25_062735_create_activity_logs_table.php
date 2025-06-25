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
        Schema::create('tblActivityLogs', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['add', 'update', 'delete']);

            $table->unsignedBigInteger('workspace_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Foreign keys
            $table->foreign('workspace_id')->references('id')->on('tblWorkspaces')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('tblProjects')->onDelete('set null');
            $table->foreign('task_id')->references('id')->on('tblTasks')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('tblUsers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblActivityLogs');
    }
};
