<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceInvite extends Model
{
    protected $table = 'tblWorkspaceInvites';
    protected $fillable = [
        'email', 'workspace_id', 'role', 'token',
        'invited_by', 'status', 'expires_at'
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
