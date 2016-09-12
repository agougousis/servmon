<?php
namespace App\Models;

use Baum\Node;
use App\Models\UserDomain;

/**
 * Model to handle database data about domains
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class Domain extends Node {

  /**
   * Table name.
   *
   * @var string
   */
  protected $table = 'domains';
  
  public function descendantIds(){
      return array_flatten($this->descendantsAndSelf()->select('id')->get()->toArray());
  }
  
  /**
   * Returns information about a domain with specific full domain name
   * 
   * @param string $fullname
   * @return Node
   */
  public static function findByFullname($fullname){
      return Domain::where('full_name',$fullname)->first(); 
  }
  
  /**
   * Count the subdomains of a specific domain
   * 
   * @param int $parent_id
   * @return int
   */
  public static function countByParentId($parent_id){
      return Domain::where('parent_id',$parent_id)->get()->count();
  }
  
  /**
   * Deletes the domain with the specified full domain name
   * 
   * @param string $fullname
   */
  public static function deleteByFullname($fullname){
      Domain::where('full_name',$fullname)->delete();
  }
  
  /**
   * Checks if a domain has been delegated to a specific user
   * 
   * @param int $user_id
   * @return boolean
   */
  public function isDelegatedTo($user_id){     
      
    $delegations = DomainDelegation::where('user_id',$user_id)->get();
    foreach($delegations as $delegation){
        $delegatedDomain = Domain::find($delegation->domain_id);
        if($this->isSelfOrDescendantOf($delegatedDomain)){
            return true;
        }
    }
    
    return false;
    
  }
  
  //////////////////////////////////////////////////////////////////////////////

  //
  // Below come the default values for Baum's own Nested Set implementation
  // column names.
  //
  // You may uncomment and modify the following fields at your own will, provided
  // they match *exactly* those provided in the migration.
  //
  // If you don't plan on modifying any of these you can safely remove them.
  //

  // /**
  //  * Column name which stores reference to parent's node.
  //  *
  //  * @var string
  //  */
  // protected $parentColumn = 'parent_id';

  // /**
  //  * Column name for the left index.
  //  *
  //  * @var string
  //  */
  // protected $leftColumn = 'lft';

  // /**
  //  * Column name for the right index.
  //  *
  //  * @var string
  //  */
  // protected $rightColumn = 'rgt';

  // /**
  //  * Column name for the depth field.
  //  *
  //  * @var string
  //  */
  // protected $depthColumn = 'depth';

  // /**
  //  * Column to perform the default sorting
  //  *
  //  * @var string
  //  */
  // protected $orderColumn = null;

  // /**
  // * With Baum, all NestedSet-related fields are guarded from mass-assignment
  // * by default.
  // *
  // * @var array
  // */
  // protected $guarded = array('id', 'parent_id', 'lft', 'rgt', 'depth');

  //
  // This is to support "scoping" which may allow to have multiple nested
  // set trees in the same database table.
  //
  // You should provide here the column names which should restrict Nested
  // Set queries. f.ex: company_id, etc.
  //

  // /**
  //  * Columns which restrict what we consider our Nested Set list
  //  *
  //  * @var array
  //  */
  // protected $scoped = array();

  //////////////////////////////////////////////////////////////////////////////

  //
  // Baum makes available two model events to application developers:
  //
  // 1. `moving`: fired *before* the a node movement operation is performed.
  //
  // 2. `moved`: fired *after* a node movement operation has been performed.
  //
  // In the same way as Eloquent's model events, returning false from the
  // `moving` event handler will halt the operation.
  //
  // Please refer the Laravel documentation for further instructions on how
  // to hook your own callbacks/observers into this events:
  // http://laravel.com/docs/5.0/eloquent#model-events

}
