<?php

namespace App\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class BgImage extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'bg_images';
    protected $fillable = [
        'name',
        'dimensions',
        'image',
        'device_pools'
    ];

    protected $casts = [
        'device_pools' => 'array'
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the available Device Pools to associate with the BgImage
     * @return array
     */
    public static function availableDevicePools()
    {
        $devicePools = \App\Models\Phone::distinct('device_pool')->pluck('device_pool')->toArray();
        array_unshift($devicePools, 'All');
        $devicePools = array_map(function($devicePool) {
            return [$devicePool => $devicePool];
        }, $devicePools);
        $devicePools = array_merge(...$devicePools);
        return $devicePools;
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

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
