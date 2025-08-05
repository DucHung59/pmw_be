<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectIssue extends Model
{
    //
    protected $table = 'tblTaskCategories';

    protected $fillable = [
        'project_id',
        'category_type',
        'category_color',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
