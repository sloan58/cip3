<?php

namespace App\Models;

use Carbon\Carbon;
use Backpack\CRUD\CrudTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ucm extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'ucms';
    protected $guarded = ['id'];
    protected $appends = [
        'totalPhoneCount'
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Return an array of supported API Versions
     *
     * @return array
     */
    public static function getApiVersions()
    {
        $versions = Storage::disk('wsdl')->directories();

        arsort($versions);

        return $versions;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasMany
     */
    public function phones()
    {
        return $this->hasMany(Phone::class);
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

    /**
     * Decrypt the CUCM Password when accessing
     *
     * @param $value
     * @return string
     */
    public function getPasswordAttribute($value)
    {
        return decrypt($value);
    }

    /**
     * Convert UTC to local TZ for display in view
     *
     * @param $value
     * @return mixed
     */
    public function getSyncAtAttribute($value)
    {
        return Carbon::createFromFormat(
            'H:i:s', $value, 'UTC'
        )->tz($this->timezone)->toTimeString();
    }

    /**
     * Return the total phone count for this Ucm
     *
     * @return mixed
     */
    public function getTotalPhoneCountAttribute()
    {
        return $this->phones()->count();
    }


    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    /**
     * Encrypt the CUCM Password when setting
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] =  encrypt($value);
    }

    /**
     * Convert local TZ to UTC before storing in database
     *
     * @param $value
     */
    public function setSyncAtAttribute($value)
    {
        $this->attributes['sync_at'] = Carbon::createFromFormat(
            'H:i:s', $value, $this->timezone
        )->tz('UTC')->toTimeString();
    }
}
