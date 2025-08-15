<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStatuses extends Model
{
    //
    protected $table = 'tblTaskStatuses';

    protected $fillable = [
        'status_type',
        'status_color',
    ];
}
