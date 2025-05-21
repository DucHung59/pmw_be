<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('tblWorkspaceMembers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('workspace_id');
            $table->string('role')->default('member');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('tblUsers')->onDelete('cascade');
            $table->foreign('workspace_id')->references('id')->on('tblWorkspaces')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('tblWorkspaceMembers');
    }
};
