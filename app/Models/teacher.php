<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class teacher extends Model
{
    protected $fillable = [
        'name',
        'grade',
        'subject',
        'user_id'
    ];
}
