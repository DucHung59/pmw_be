<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    //
    protected $table = 'tblWorkspaces';
    protected $fillable = ['workspace_name'];
    public function members()
    {
        return $this->belongsToMany(User::class, 'tblWorkspaceMembers', 'workspace_id', 'user_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }
}
