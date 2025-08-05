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
        Schema::create('tblDocuments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('file_url')->nullable();
            $table->foreignId('created_by');
            $table->foreignId('updated_by');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('tblProjects')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('tblUsers')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('tblUsers')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
