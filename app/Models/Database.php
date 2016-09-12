<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
/**
 * Model to handle database data about databases
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class Database extends Model {	
    
    protected $table = 'databases';
    protected $fillable = array('dbname','server','type','related_webapp');
    
    /**
     * Deletes all databases hosted on a specific server
     * 
     * @param int $server_id
     */
    public static function deleteByServerId($server_id){
        Database::where('server',$server_id)->delete();
    }
    
    /**
     * Returns a list of databases hosted on a specific server
     * 
     * @param int $serverId
     * @return array
     */
    public static function getAllOnServer($serverId){
        // Left join is easier with SQL - BD::raw is not a problem since no user input is used to form the SQL query
        return DB::select(DB::raw("select dbs.id,dbs.dbname,dbs.server,dbs.type,dbs.related_webapp,wa.url as related_webapp_name,dbt.title,dbt.image from `databases` as dbs join database_types as dbt on dbt.codename = dbs.type left join `webapps` as wa on dbs.related_webapp = wa.id where dbs.server = ".$serverId));
    }
    
}