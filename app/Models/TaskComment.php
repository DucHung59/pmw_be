<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    //
    protected $table = 'tblTaskComments';

    protected $fillable = [
        'task_id',
        'comment',
        'created_by',
        'updated_by',
    ];

    // Relationships

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
