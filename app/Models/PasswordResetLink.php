<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetLink extends Model
{
    protected $table = 'password_reset_links';
    public $timestamps = false;

    /**
     * Checks if this reset link is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        // The user exists
        if(empty(User::find($this->uid))){
            return false;
        }

        // It has not expired
        $now = new DateTime();
        $valid_until = new DateTime($this->valid_until);

        if ($now > $valid_until) {
            return false;
        }

        return true;
    }

}
