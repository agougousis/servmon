<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

/**
 * Model to handle database data about services
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class Service extends Model
{
    protected $table = 'services';
    protected $fillable = array('server', 'stype', 'port', 'version');
    protected $hidden = array('created_at', 'updated_at','owner');

    /**
     * Returns a list of all services that are hosted on a specific server and
     * are being watched/monitored
     *
     * @return Collection
     */
    public static function toWatchFromServer($serverId)
    {
        return Service::where('server', $serverId)->where('watch', 1)->get();
    }

    /**
     * Deletes all services hosted on a specific server
     *
     * @param int $server_id
     */
    public static function deleteByServerId($server_id)
    {
        Service::where('server', $server_id)->delete();
    }

    /**
     * Returns a list with the type of every service on a specific server
     *
     * @param int $server_id
     * @return array
     */
    public static function getServiceTypesOnServer($server_id)
    {
        return array_flatten(Service::select('stype')->where('server', $server_id)->get()->toArray());
    }

    /**
     * Returns a list of all services on a specific server
     *
     * @param int $serverId
     * @return array
     */
    public static function getAllOnServer($serverId)
    {
        return DB::table('services')->join('service_types', 'services.stype', '=', 'service_types.codename')->select('id', 'server', 'stype', 'port', 'version', 'title', 'image', 'watch')->where('server', $serverId)->get();
    }
}
