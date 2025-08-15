<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectCategory extends Model
{
    //
    protected $table = 'tblProjectCategories';

    protected $fillable = [
        'project_id',
        'category_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    
    public function category()
    {
        return $this->belongsTo(TaskCategories::class, 'category_id');
    }
}
