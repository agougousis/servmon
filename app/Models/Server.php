<?php

namespace App\Models;

use DB;
use Auth;
use App\Models\DomainDelegation;
use Illuminate\Database\Eloquent\Model;

/**
 * Model to handle database data about servers
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class Server extends Model
{
    protected $table = 'servers';
    protected $fillable = array('hostname', 'domain','ip','os');
    protected $hidden = array('created_at', 'updated_at');

    /**
     * Returns a list of all servers that are being watched/monitored
     *
     * @return Collection
     */
    public static function toWatch()
    {
        return Server::where('watch', 1)->get();
    }

    /**
     * Returns information about specified servers
     *
     * @param array $server_ids
     * @return array
     */
    public static function getServersInfoByIds($server_ids)
    {
        return DB::table('servers')->join('domains', 'servers.domain', '=', 'domains.id')->whereIn('servers.id', $server_ids)->get();
    }

    /**
     * Returns the list of servers that belong to a specific domain
     *
     * @param int $domain_id
     * @return array
     */
    public static function getByDomain($domain_id)
    {
        return Server::select('id','hostname','watch')->where('domain', $domain_id)->get();
    }

    /**
     * Returns a list of servers that belong to a domain or a subdomain of this
     *
     * @param string $domain_name
     * @return array
     */
    public static function getAllUnderDomain($domain_name)
    {
        // Find domain ID and all subdomain IDs
        $domain = Domain::findByFullname($domain_name);
        $descentant_ids = array_flatten($domain->descendantsAndSelf()->select('id')->get()->toArray());
        return DB::table('servers')->join('domains', 'domains.id', '=', 'servers.domain')->select('servers.id', 'hostname', 'ip', 'os', 'domain', 'domains.full_name')->whereIn('domain', $descentant_ids)->get();
    }

    /**
     * Returns a list with all servers that can be managed by a user
     *
     * @param int $user_id
     * @return Collection
     */
    public static function allUserServers($user_id)
    {
        // Find domains delegated to me
        $delegated_domains_ids = array_flatten(DomainDelegation::getUserDelegatedIds($user_id));
        // Find subdomains
        $my_domain_ids = array();
        foreach ($delegated_domains_ids as $domainId) {
            $domain = Domain::find($domainId);
            $descentant_ids = array_flatten($domain->descendantIds());
            $my_domain_ids = array_merge($my_domain_ids, $descentant_ids);
        }
        // Find servers that have been independently delegated to me
        $independent_server_ids = array_flatten(ServerDelegation::getUserDelegatedIds(Auth::user()->id));
        // Find my servers
        $servers = Server::join('domains', 'servers.domain', '=', 'domains.id')->whereIn('domain', $my_domain_ids)->orWhereIn('servers.id', $independent_server_ids)->select('servers.id', 'ip', 'os', 'hostname', 'supervisor_email', 'domain', 'full_name AS domain_name')->get();
        return $servers;
    }
}
