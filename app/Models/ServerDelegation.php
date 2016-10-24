<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model to handle database data about server delegations
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class ServerDelegation extends Model
{
    protected $table = 'server_delegations';
    protected $fillable = array('user_id', 'server_id');

    /**
     * Deletes all delegations related to a specific user
     *
     * @param int $user_id
     */
    public static function deleteUserDelegations($user_id)
    {
        ServerDelegation::where('user_id', $user_id)->delete();
    }

    /**
     * Returns a list with all server delegations
     *
     * @return array
     */
    public static function getAllWithUser()
    {
        return ServerDelegation::join('servers', 'server_delegations.server_id', '=', 'servers.id')->join('users', 'users.id', '=', 'server_delegations.user_id')->select('server_delegations.id', 'server_id', 'email', 'firstname', 'lastname')->get()->toArray();
    }

    /**
     * Returns the IDs from all servers a user is delegated to
     *
     * @param int $user_id
     * @return array
     */
    public static function getUserDelegatedIds($user_id)
    {
        return ServerDelegation::where('user_id', $user_id)->select('server_id')->get()->toArray();
    }

    /**
     * Returns information about a specific server delegation
     *
     * @param int $serverId
     * @param int $userId
     * @return ServerDelegation
     */
    public static function searchByUserAndServer($serverId, $userId)
    {
        return ServerDelegation::where('server_id', $serverId)->where('user_id', $userId)->first();
    }

    /**
     * Removes a specific server delegation
     *
     * @param int $server_id
     */
    public static function deleteByServerId($server_id)
    {
        ServerDelegation::where('server_id', $server_id)->delete();
    }

    /**
     * Removes all server delegations for server that belong to one of the domains in a list
     *
     * @param array $domain_list
     */
    public static function removeUserDelegationsInDomains($user_id, $domain_list)
    {
        ServerDelegation::join('servers', 'server_delegations.server_id', '=', 'servers.id')->where('user_id', $user_id)->whereIn('domain', $domain_list)->delete();
    }
}
