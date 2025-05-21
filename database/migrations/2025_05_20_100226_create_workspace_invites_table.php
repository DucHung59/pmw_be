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
        Schema::create('tblWorkspaceInvites', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->foreignId('workspace_id')->constrained('tblWorkspaces')->onDelete('cascade');
            $table->string('role')->default('member');
            $table->uuid('token')->unique();
            $table->foreignId('invited_by')->constrained('tblUsers')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_invites');
    }
};
