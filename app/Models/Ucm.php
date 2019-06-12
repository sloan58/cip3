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
    protected $guarded = ['id', 'sync_history'];
    protected $appends = [
        'totalPhoneCount'
    ];
    protected $casts = [
        'sync_history' => 'array'
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

    /**
     * @param $sync_completed
     * @param $timestamp
     * @param null $errorMessage
     * @param null $errorCode
     */
    public function updateSyncHistory($sync_completed, $timestamp, $errorCode = null, $errorMessage = null)
    {
        $history = (array) $this->sync_history;
        array_unshift($history, [
            'status' => $sync_completed,
            'timestamp' => $timestamp,
            'error_code' => $errorCode,
            'error_message' => $errorMessage
        ]);

        $history = array_slice($history, 0, 3);

        $this->sync_history = $history;
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
     * Return an empty sync_history array if null
     *
     * @param $value
     * @return array|mixed
     */
    public function getSyncHistoryAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        return json_decode($value, TRUE);
    }

    /**
     * Convert local TZ to UTC before storing in database
     *
     * @param $value
     */
    public function setSyncAtAttribute($value)
    {
        if(count(explode(':', $value)) == 2) {
            $this->attributes['sync_at'] = Carbon::createFromFormat(
                'H:i:s', $value . ':00', $this->timezone
            )->tz('UTC')->toTimeString();
        }
    }
}
