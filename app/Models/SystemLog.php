<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model to handle database data about system logs
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class SystemLog extends Model
{

    protected $table = 'system_logs';
    protected $fillable = ['message','category','when'];

    public $timestamps = false;

}
