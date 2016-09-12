<?php

namespace App\Http\Controllers;

use Artisan;
use DirectoryIterator;

/**
 * Implements functionality related to backups
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class BackupController extends RootController {
    
    /**
     * Returns a list of exiting backups
     * 
     * @return Response
     */
    public function search(){
        
        $backup_list = array();
        $dir = new DirectoryIterator(storage_path('backup'));
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {                                
                $basename = $fileinfo->getBasename('.gz');
                $filename = $fileinfo->getFilename();
                $filesize = $fileinfo->getSize();
                
                $fparts = explode('_',$basename);
                $date_parts = explode('-',$fparts[1]);
                $time_parts = explode('-',$fparts[2]);
                $dt_string = $fparts[1]." ".implode(':',$time_parts);
                
                $backup_list[] = array(
                    'when'  =>  $dt_string,
                    'size'  =>  $filesize,
                    'filename'  =>  $filename
                );
            }
        }
        return response()->json($backup_list)->setStatusCode(200, '');
        
    }
    
    /**
     * Displays the backup administration page
     * 
     * @return View
     */
    public function backup_page(){                            
        
        return $this->load_view('backup',"Backup");
        
    }
    
    /**
     * Creates a new database backup of monitorable items
     * 
     * @return Response
     */
    public function create(){
        
        $filename = 'backup_'.date("d-m-Y_H-i-s");
        try {
            Artisan::call('db:backup',[
                '--database'    =>'mysql',
                '--destination' =>'local',
                '--compression' =>'gzip',
                '--destinationPath' =>  $filename
            ]);
        } catch (Exception $ex) {
            $filepath = storage_path('backup')."/".$filename;
            if(file_exists()){
                unlink($filepath);
            }
            $this->log_event("Backup failed! ".$ex->getMessage(),"error");
            return response()->json(['errors' => array()])->setStatusCode(400, 'Backup failed! Please, check the error logs!');      
        }                
            
        return response()->json([])->setStatusCode(200, 'Backup created successfully!'); 
        
    }
    
    /**
     * Restores a backup
     * 
     * @param String $filename The backup file name
     * @return Response
     */
    public function restore($filename){
        $filepath = storage_path('backup')."/".$filename;
        if(file_exists($filepath)){            
            
            try {
                Artisan::call('db:restore',[
                    '--source'      =>  'local',
                    '--sourcePath'  =>  $filename,
                    '--database'    =>  'mysql',
                    '--compression' =>  'gzip'                
                ]);
            } catch (Exception $ex) {
                $this->log_event("Database restoration failed! ".$ex->getMessage(),"error");
                return response()->json(['errors' => array()])->setStatusCode(400, 'Backup restoration failed! Please, check the error logs!');
            }                        

            return response()->json([])->setStatusCode(200, 'Backup restored successfully!'); 
        } else {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Backup file were not found!');      
        }
    }
    
    /**
     * Deletes an existing backup
     * 
     * @param String $filename The backup file name
     * @return Response
     */
    public function delete($filename){
        
        $filepath = storage_path('backup')."/".$filename;
        if(file_exists($filepath)){            
            unlink($filepath);            
            return response()->json([])->setStatusCode(200, 'Backup deleted!');
        } else {
            return response()->json([])->setStatusCode(400, 'Backup file were not found');          
        }
        
    }
    
}