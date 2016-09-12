<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model to handle database data about domain delegations
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class DomainDelegation extends Model {
    
    protected $table = 'domain_delegations';    
  
    /**
     * Removes a specific domain delegation
     * 
     * @param int $domain_id
     */
    public static function deleteByDomainId($domain_id){
        DomainDelegation::where('domain_id',$domain_id)->delete();
    }
    
    /**
     * Returns information about all domain delegations
     * 
     * @return array
     */
    public static function getAllFullInfo(){
        return DomainDelegation::join('domains','domain_delegations.domain_id','=','domains.id')->join('users','users.id','=','domain_delegations.user_id')->select('domain_delegations.id','full_name','email','firstname','lastname')->get()->toArray();
    }
    
    /**
     * Returns a list with all the domain delegations that are related to a specific user
     * 
     * @param int $user_id
     * @return array
     */
    public static function getUserDelegatedIds($user_id){
        return DomainDelegation::where('user_id',$user_id)->select('domain_id')->get()->toArray();
    }
    
    /**
     * Returns a list with the IDs of every domain that lies under a specific domain (including itself)
     * 
     * @return array
     */
    public function descendantIds(){
        return $this->descendantsAndSelf()->select('id')->get()->toArray();
    }
    
    /**
     * Returns the first domain delegation found that is related to a specific user
     * and a domain included in a domain list
     * 
     * @param int $userId
     * @param array $domainList
     * @return DomainDelegation
     */
    public static function firstUserDelegationToDomains($userId,$domainList){
        return DomainDelegation::where('user_id',$userId)->whereIn('domain_id',$domainList)->first();
    }
    
}
