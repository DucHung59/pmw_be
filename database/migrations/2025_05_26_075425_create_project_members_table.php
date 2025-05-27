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
        Schema::create('tblProjectMembers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->string('project_role'); // Vai trò của thành viên trong dự án (ví dụ: quản lý, thành viên, khách mời)
            $table->timestamps();

            // Khóa ngoại
            $table->foreign('project_id')->references('id')->on('tblProjects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('tblUsers')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblProjectMembers');
    }
};
