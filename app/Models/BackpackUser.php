<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Backpack\Base\app\Models\Traits\InheritsRelationsFromParentModel;
use Backpack\Base\app\Notifications\ResetPasswordNotification as ResetPasswordNotification;

class BackpackUser extends User
{
    use InheritsRelationsFromParentModel;

    protected $table = 'users';

    /**
     * Send the password reset notification.
     *
     * @param string $token
     *
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * A User Has Many Reports
     *
     * @return HasMany
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'submitted_by', 'email');
    }
}
