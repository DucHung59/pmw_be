<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceMember extends Model
{
    //
    protected $table = 'tblWorkspaceMembers';
    protected $fillable = ['user_id', 'workspace_id', 'role'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
}
