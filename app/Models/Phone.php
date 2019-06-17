<?php

namespace App\Models;

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
        $now = \Carbon\Carbon::now()->timestamp;
        $fileName = "cip3_phone_export_{$now}.csv";

//        return '<a href="/admin/phone/export" class="btn btn-primary ladda-button" data-style="zoom-in"><span class="ladda-label"><i class="fa fa-clipboard"></i> Export</span></a>';
        return '<a href="/admin/phone/export" class="btn btn-primary ladda-button" data-style="zoom-in" download="' . $fileName . '">
                    <span class="ladda-label">
                        <i class="fa fa-clipboard"></i>
                        Export All
                    </span>
                </a>';

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
