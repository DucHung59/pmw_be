<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCategories extends Model
{
    //
    protected $table = 'tblTaskCategories';

    protected $fillable = [
        'category_type',
        'category_color',
    ];
}
