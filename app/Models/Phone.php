<?php

namespace App\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    /**
     * Custom UI Button to Export Phones
     *
     * @param bool $crud
     * @return string
     */
    public function exportPhones($crud = false)
    {
        return '<button type="button" class="btn btn-primary ladda-button" data-toggle="modal" data-target="#exportPhones"><i class="fa fa-clipboard"></i>
                        Export
                </button>';
    }

    /**
     * Custom UI Button to Bulk Delete ITL Files
     *
     * @param bool $crud
     * @return string
     */
    public function bulkDeleteItl($crud = false)
    {
        return '<button type="button" class="btn btn-primary ladda-button" data-toggle="modal" data-target="#bulkDeleteItl"><i class="fa fa-bomb"></i>
                        Bulk Delete ITL
                </button>';
    }

    /**
     * Return the Phone's current IP Address
     *
     * @return mixed
     */
    public function currentIpAddress()
    {
        return $this->realtime_data[0]['IPAddress'];
    }

    /**
     * Check if the IP Phone is registered
     *
     * @return bool
     */
    public function isRegistered()
    {
        if(isset($this->realtime_data[0])) {
            return $this->realtime_data[0]['Status'] == 'Registered' ? true : false;
        }
    }

    /**
     * Get the full size dimensions for the models background image
     *
     * @return mixed
     */
    public function getFullSizeBgDimensions()
    {
        $bgid = BgImageDimension::where('model', $this->model)->first();

        return $bgid ? $bgid->full_size : null;
    }

    /**
     * Get the thumb size dimensions for the models background image
     *
     * @return mixed
     */
    public function getThumbSizeBgDimensions()
    {
        $bgid = BgImageDimension::where('model', $this->model)->first();

        return $bgid ? $bgid->thumb : null;
    }

    /**
     * Get BgImages that are the right dimensions and assigned
     * to either the phone's device pool or All device pools
     *
     * @return mixed
     */
    public function assignedBgImages()
    {
        return $this->bgImages->filter(function($image) {
            return in_array($this->device_pool, $image->device_pools) || $image->device_pools[0] == "All" ? true: false;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     *  A Phone Belongs to a UCM
     *
     * @return BelongsTo
     */
    public function ucm()
    {
        return $this->belongsTo(Ucm::class);
    }

    /**
     *  A Phone Belongs to an ITL
     *
     * @return BelongsTo
     */
    public function itl()
    {
        return $this->belongsTo(Itl::class, 'model', 'model');
    }

    /**
     *  A Phone Has Many BgImage History events
     *
     * @return HasMany
     */
    public function bgImageHistories()
    {
        return $this->hasMany(BgImageHistory::class, 'name', 'phone');
    }

    /**
     *  A Phone Has Many ITL History events
     *
     * @return HasMany
     */
    public function itlHistories()
    {
        return $this->hasMany(ItlHistory::class, 'name', 'phone');
    }

    /**
     * A Phone Belongs To a BgImageDimension
     *
     * @return BelongsTo
     */
    public function bgImageDimensions()
    {
        return $this->belongsTo(BgImageDimension::class, 'model', 'model');
    }

    /**
     * A Phone Has Many BgImage through BgImageDimensions
     *
     * @return HasManyThrough
     */
    public function bgImages()
    {
        return $this->hasManyThrough(BgImage::class, BgImageDimension::class, 'model', 'dimensions', 'model', 'full_size');
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
