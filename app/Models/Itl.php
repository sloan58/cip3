<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Itl extends Model
{
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $guarded = ['id'];
    protected $casts = [
        'sequence' => 'array'
    ];

}
