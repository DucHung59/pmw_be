<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    //
    protected $table = 'tblDocuments';

    protected $fillable = [
        'project_id',
        'title',
        'content',
        'file_url',
        'manager_view',
        'created_by',
        'updated_by'
    ];

    function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
