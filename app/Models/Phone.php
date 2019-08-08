<?php

namespace App\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        return $this->realtime_data[0]['Status'] == 'Registered' ? true : false;
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
     * Get the available image files for this Phone
     *
     * @return array
     */
    public function getAvailableImages()
    {
        return array_filter(array_map(function($file) {
            $fileName = $file->getFileName();
            if(preg_match('/_thumb\.png$/', $fileName)) {
                return false;
            }
            return $fileName;
        }, File::files(storage_path("app/public/backgrounds/{$this->getFullSizeBgDimensions()}"))
        ));
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
     *  A Phone Has Many RemoteOperation events
     *
     * @return HasMany
     */
    public function erasers()
    {
        return $this->hasMany(RemoteOperation::class, 'name', 'phone');
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
