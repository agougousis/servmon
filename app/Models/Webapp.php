<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

/**
 * Model to handle database data about webapps
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class Webapp extends Model
{
    protected $table = 'webapps';
    protected $fillable = array('url', 'server', 'language', 'developer', 'contact');
    protected $hidden = array('created_at', 'updated_at','owner','supervisor_email');

    /**
     * Returns a list of all webapps that are hosted on a specific server and
     * are being watched/monitored
     *
     * @return Collection
     */
    public static function toWatchFromServer($serverId)
    {
        return Webapp::where('server', $serverId)->where('watch', 1)->get();
    }

    /**
     * Retrieves a webapp with specific URL
     *
     * @param string $url
     * @return Webapp
     */
    public static function getByUrl($url)
    {
        return Webapp::where('url', $url)->first();
    }

    /**
     * Deletes all the webapps that are hosted on a specific server
     *
     * @param int $server_id
     */
    public static function deleteByServerId($server_id)
    {
        Webapp::where('server', $server_id)->delete();
    }

    /**
     * Returns a list of all the webapps that are hosted on a specific server
     *
     * @param int $serverId
     * @return array
     */
    public static function getAllOnServer($serverId)
    {
        return DB::table('webapps')->join('webapp_types', 'webapps.language', '=', 'webapp_types.codename')->where('server', $serverId)->get();
    }

    /**
     * Returns a list of al the webapps that are hosted on servers that belong to a domain or a subdomain of it
     *
     * @param string $full_domain_name
     * @return array
     */
    public static function getAllUnderDomain($full_domain_name)
    {
        $domain = Domain::findByFullname($full_domain_name);
        $domainMembers = $domain->descendantsAndSelf()->get();
        $domainMemberIds = array();
        foreach ($domainMembers as $domainMember) {
            $domainMemberIds[] = $domainMember->id;
        }

        $webapps = DB::table('webapps')->join('servers', 'servers.id', '=', 'webapps.server')->whereIn('servers.domain', $domainMemberIds)->get();
        return $webapps;
    }
}
