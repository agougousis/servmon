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
    protected $fillable = array('url','server','language','developer','contact');

    /**
     * Retrieves a webapp with specific URL
     *
     * @param string $url
     * @return Webapp
     */
    public static function getByUrl($url)
    {
        return Webapp::where('url',$url)->first();
    }

    /**
     * Returns a list with all the webapps
     *
     * @return array
     */
    public static function getAllAsArray()
    {
        return Webapp::select('id','url','language','developer','server','contact')->get()->toArray();
    }

    /**
     * Deletes all the webapps that are hosted on a specific server
     *
     * @param int $server_id
     */
    public static function deleteByServerId($server_id)
    {
        Webapp::where('server',$server_id)->delete();
    }

    /**
     * Returns a list of all the webapps that are hosted on a specific server
     *
     * @param int $serverId
     * @return array
     */
    public static function getAllOnServer($serverId)
    {
        return DB::table('webapps')->join('webapp_types','webapps.language','=','webapp_types.codename')->select('id','url','language','developer','contact','image','watch')->where('server',$serverId)->get();;
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

        $webapps = DB::table('webapps')->join('servers','servers.id','=','webapps.server')->select('webapps.id','webapps.url','webapps.language','webapps.developer','webapps.server','webapps.contact')->whereIn('servers.domain',$domainMemberIds)->get();
        return $webapps;
    }

}