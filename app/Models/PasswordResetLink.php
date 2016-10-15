<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetLink extends Model
{

    protected $table = 'password_reset_links';
    public $timestamps = false;

}