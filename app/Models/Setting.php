<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model to handle database data about settings
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class Setting extends Model {

  protected $primaryKey = 'sname';
  public $incrementing = false;
  protected $table = 'settings';
  public $timestamps = false;  
  
}
