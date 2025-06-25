<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStatus extends Model
{
    //
    protected $table = 'tblProjectStatuses';

    protected $fillable = [
        'project_id',
        'status_type',
        'status_color',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
