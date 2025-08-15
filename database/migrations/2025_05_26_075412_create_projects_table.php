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
        Schema::create('tblProjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('project_name');
            $table->string('project_key')->unique();
            $table->longText('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('created_by'); // Added created_by field
            $table->timestamps();

            // Khóa ngoại
            $table->foreign('workspace_id')->references('id')->on('tblWorkspaces')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('tblUsers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblProjects');
    }
};
