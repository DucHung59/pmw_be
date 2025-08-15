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
        Schema::create('tblTasks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('project_id');
            $table->string('subject');
            $table->string('task_key')->nullable();

            $table->unsignedBigInteger('status');       // FK to tblProjectStatuses
            $table->unsignedBigInteger('category_type');   // FK to tblProjectIssues
            $table->unsignedBigInteger('assignee')->nullable(); // FK to tblUsers

            $table->longText('description')->nullable();
            $table->string('priority')->default('normal');
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('is_del')->default(0);

            $table->timestamps(); // created_at, updated_at
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Foreign Keys
            $table->foreign('project_id')->references('id')->on('tblProjects')->onDelete('cascade');
            $table->foreign('status')->references('id')->on('tblTaskStatuses')->onDelete('restrict');
            $table->foreign('category_type')->references('id')->on('tblTaskCategories')->onDelete('restrict');
            $table->foreign('assignee')->references('id')->on('tblUsers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblTasks');
    }
};
