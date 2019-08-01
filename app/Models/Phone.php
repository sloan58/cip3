<?php

namespace App\Models;

use Carbon\Carbon;
use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phone extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'phones';
    protected $guarded = ['id'];
    protected $casts = [
        'realtime_data' => 'array'
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function exportPhones($crud = false)
    {
        return '<button type="button" class="btn btn-primary ladda-button" data-toggle="modal" data-target="#exportPhones"><i class="fa fa-clipboard"></i>
                        Export All
                </button>';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function ucm()
    {
        return $this->belongsTo(Ucm::class);
    }

    /**
     * @return BelongsTo
     */
    public function itl()
    {
        return $this->belongsTo(Itl::class, 'model', 'model');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
