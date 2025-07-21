<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tblTasks';

    protected $fillable = [
        'project_id',
        'subject',
        'task_key',
        'status',
        'issue_type',
        'assignee',
        'description',
        'priority',
        'due_date',
        'created_by',
        'updated_by',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function statusInfo()
    {
        return $this->belongsTo(ProjectStatus::class, 'status');
    }

    public function issueType()
    {
        return $this->belongsTo(ProjectIssue::class, 'issue_type');
    }

    public function assigneeUser()
    {
        return $this->belongsTo(User::class, 'assignee');
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
