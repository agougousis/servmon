<?php

namespace App\Http\Controllers;

use Artisan;
use DB;
use PDO;
use Redirect;
use PDOException;
use Validator;
use Illuminate\Http\Request;

/**
 * Implements functionality related to installation
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class SystemController extends RootController {                         
    
    /**
     * Installs the application
     * 
     * @param Request $request
     * @return Response
     */
    public function install(Request $request){
        
        $input = $request->all();
        $rules = config('validation.installation');
        $validator = Validator::make($input,$rules);
        if ($validator->fails()){
            foreach($validator->errors()->getMessages() as $key => $errorMessages){
                foreach($errorMessages as $msg){
                    $errors[] = array(
                        'field'     =>  $key,
                        'message'   =>  $msg
                    );
                }                    
            }                   
            return response()->json(['errors' => $errors])->setStatusCode(400, 'Invalid database parameters!');
        } else {
            
            $server = $input['server'];
            $dbname = $input['dbname'];
            $dbuser = $input['dbuser'];
            $dbpwd = $input['dbpwd'];
            $url = $input['url'];
            
            // Test database credentials
            try {
                $conn = new PDO("mysql:host=$server;dbname=$dbname", $dbuser, $dbpwd);               
            } catch(PDOException $e) {
                return response()->json(['errors'=>[]])->setStatusCode(500, 'Invalid database credentials!');
            }

            try {    
                // Save database configuration permanently
                $params = $this->load_env_params();
                $params['DB_HOST'] = $server;
                $params['DB_DATABASE'] = $dbname;
                $params['DB_USERNAME'] = $dbuser;
                $params['DB_PASSWORD'] = $dbpwd;
                $this->save_env_params($params);                                             

                // Update current database configuration (already loaded)
                // We have to update it. We will need it for the transaction.
                config([
                    'database.connections.mysql.host'       =>  $server,
                    'database.connections.mysql.database'   =>  $dbname,
                    'database.connections.mysql.username'   =>  $dbuser,
                    'database.connections.mysql.password'   =>  $dbpwd
                ]); 
                
                // Build the databse schema
                DB::beginTransaction();
                $exitCode1 = Artisan::call('migrate:refresh',[]);
                $exitCode2 = Artisan::call('db:seed',[]);
                DB::commit();        
                 
                // Set the installaton flag off and change session driver
                $params = $this->load_env_params();
                $params['APP_DEBUG'] = 'false';
                $params['APP_URL'] = $url;
                $params['APP_INSTALLATION'] = 'done';
                $this->save_env_params($params);   

                // Update current configuration (already loaded)
                config([
                    'app.debug'             =>  'false',
                    'app.url'               =>  $url,
                    'app.installation'      =>  'done',
                ]); 
                
                // There is some cache issue with config. Do not remove it!
                $a = config('app.installation');
                
                return response()->json([$a])->setStatusCode(200,'Installation completed!');                
            } catch (Exception $ex) {
                DB::rollBack();
                return response()->json(['errors'=>[]])->setStatusCode(500, 'Installation failed! Unexpected Error!');
            }            
        }                       
        
    }
    
    private function load_env_params(){
        $path = base_path('.env');
        $contents = file_get_contents($path);
        $lines = preg_split('/[\n]/',$contents);
        $params = array();
        foreach($lines as $line){
            $parts = explode('=',$line);
            if(!empty($parts[1])){
                $params[$parts[0]] = $parts[1];
            } else {
                $params[$parts[0]] = '';
            }                    
        }
        
        return $params;
    }
    
    private function save_env_params($params){
        $path = base_path('.env');
        $new_lines = array();
        foreach($params as $key => $value){
            if(!empty($key)){
                $new_lines[] = $key.'='.$value;
            }            
        }
        $new_content = implode(PHP_EOL,$new_lines);

        $handle = fopen($path, "w+");
        fwrite($handle ,$new_content);
        fclose($handle);
    }
    
}