<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model to handle database data about delays
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class Delay extends Model
{
    protected $table = 'monitor_delays';
    public $timestamps = false;
}
