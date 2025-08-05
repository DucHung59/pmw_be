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
        Schema::create('tblTaskComments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('task_id');
            $table->longText('comment');

            $table->timestamps(); // created_at, updated_at
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Foreign keys
            $table->foreign('task_id')->references('id')->on('tblTasks')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('tblUsers')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('tblUsers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblTaskComments');
    }
};
