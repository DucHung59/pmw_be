<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
    protected $table = 'tblProjects';

    protected $fillable = [
        'workspace_id',
        'project_name',
        'project_key',
        'description',
        'start_date',
        'end_date',
        'created_by',
    ];

    public function issues()
    {
        return $this->hasMany(ProjectIssue::class, 'project_id');
    }

    public function members()
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
