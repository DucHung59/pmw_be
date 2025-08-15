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
        Schema::create('tblProjectStatuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('status_id');
            $table->timestamps();

            // Khóa ngoại
            $table->foreign('project_id')->references('id')->on('tblProjects')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('tblTaskCategories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblTaskStatuses');
    }
};
