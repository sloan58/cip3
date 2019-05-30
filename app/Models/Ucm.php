<?php

namespace App\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class Ucm extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'ucms';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
     protected $guarded = ['id'];
//    protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

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
}
