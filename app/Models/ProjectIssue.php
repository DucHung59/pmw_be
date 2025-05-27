<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectIssue extends Model
{
    //
    protected $table = 'tblProjectIssues';

    protected $fillable = [
        'project_id',
        'issue_type',
        'issue_color',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
