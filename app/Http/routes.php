<?php

// Visitor API endpoints
Route::group(['prefix' => 'api','middleware' => ['myweb']], function () { 
    
    Route::post('/login', array('uses'=>'LoginController@login'));  
    
});

// Normal API endpoints
Route::group(['prefix' => 'api','middleware' => ['logged']], function () {
    
    // Domain-related endpoints
    Route::get('/domains', array('uses'=>'DomainController@search')); 
    Route::post('/domains', array('uses'=>'DomainController@create')); 
    Route::delete('/domains/{domname}', array('uses'=>'DomainController@delete'));        
    Route::get('/domains/{domName}/servers', array('uses'=>'DomainController@server_list'));
    Route::get('/domains/{domName}/all_servers', array('uses'=>'DomainController@servers_under_domain')); 
    Route::get('/domains/{domName}/webapps', array('uses'=>'DomainController@webapp_list'));   
    
    // Server-related endpoints
    Route::get('servers', array('uses'=>'ServerController@search')); 
    Route::get('/servers/{serverId}', array('uses'=>'ServerController@read')); 
    Route::post('servers', array('uses'=>'ServerController@create'));
    Route::put('servers', array('uses'=>'ServerController@update')); 
    Route::delete('servers/{serverId}', array('uses'=>'ServerController@delete')); 
    
    // Service-related endpoints
    Route::get('/services/{serviceId}', array('uses'=>'ServiceController@read'));
    Route::post('/services', array('uses'=>'ServiceController@create'));
    Route::put('/services', array('uses'=>'ServiceController@update'));
    Route::delete('/services/{serviceId}', array('uses'=>'ServiceController@delete'));
    
    // Webapp-related endpoints
    Route::get('/webapps', array('uses'=>'WebappController@search'));
    Route::get('/webapps/{appId}', array('uses'=>'WebappController@read'));
    Route::put('/webapps', array('uses'=>'WebappController@update'));
    Route::post('/webapps', array('uses'=>'WebappController@create'));
    Route::delete('/webapps/{appId}', array('uses'=>'WebappController@delete'));
    
    // Database-related endpoints
    Route::get('/databases/{databaseId}', array('uses'=>'DatabaseController@read'));
    Route::post('/databases', array('uses'=>'DatabaseController@create'));
    Route::put('/databases', array('uses'=>'DatabaseController@update'));
    Route::delete('/databases/{databaseId}', array('uses'=>'DatabaseController@delete'));        
    
    // Generic Info endpoints
    Route::get('/info/supported_types', array('uses'=>'InfoController@supported_types_list'));
    Route::get('/info/backup_items', array('uses'=>'InfoController@backup_items'));
    Route::get('/info/settings', array('uses'=>'InfoController@settings'));
    Route::get('/info/myprofile', array('uses'=>'InfoController@myprofile'));
    
    // Delegation Info
    Route::get('delegations', array('uses'=>'DelegationController@search'));
    
    Route::post('/logout', array('uses'=>'LoginController@logout'));
    
});

// Elevated API endpoints
Route::group(['prefix' => 'api','middleware' => ['superuser']], function () {        
    
    Route::get('users/{userId}',array('uses'=>'UserController@read'))->where('userId', '[0-9]+');
    Route::get('users',array('uses'=>'UserController@search'));
    Route::post('users',array('uses'=>'UserController@add_users'));
    Route::put('users/{userId}/enable',array('uses'=>'UserController@enable_user'))->where('userId', '[0-9]+');    
    Route::put('users/{userId}/disable',array('uses'=>'UserController@disable_user'))->where('userId', '[0-9]+');    
    Route::put('users/{userId}/make_superuser',array('uses'=>'UserController@make_superuser'))->where('userId', '[0-9]+');
    Route::put('users/{userId}/unmake_superuser',array('uses'=>'UserController@unmake_superuser'))->where('userId', '[0-9]+');
    Route::delete('users/{userId}',array('uses'=>'UserController@delete_user'))->where('userId', '[0-9]+');
    
    // Delegation Management    
    Route::post('delegations', array('uses'=>'DelegationController@create'));
    Route::delete('/delegations/domain/{delegationId}', array('uses'=>'DelegationController@delete_domain_delegation'));
    Route::delete('/delegations/server/{delegationId}', array('uses'=>'DelegationController@delete_server_delegation'));
    
    // Backup Management
    Route::get('backup', array('uses'=>'BackupController@search'));
    Route::post('backup', array('uses'=>'BackupController@create'));
    Route::post('backup/{filename}/restore', array('uses'=>'BackupController@restore'));
    Route::delete('backup/{filename}', array('uses'=>'BackupController@delete'));
    
    // Monitoring Scheduler Management
    Route::get('/monitor/items', array('uses'=>'MonitorController@get_monitorable'));
    Route::post('/monitor/items', array('uses'=>'MonitorController@update_configuration'));
    Route::put('monitor/status',array('uses'=>'MonitorController@change_status'));
    
});

// Elevated Web Pages
Route::group(['middleware' => ['superuser']], function () {
    
    Route::get('user_management',array('uses'=>'WebController@user_management'));
    Route::get('user_management/{userId}',array('uses'=>'WebController@user_profile_management'))->where('userId', '[0-9]+');
    Route::get('/domains/delegation', array('uses'=>'WebController@delegations_page'));       
    Route::get('/backup', array('uses'=>'WebController@backup_page'));   
    Route::get('/monitor/configure', array('uses'=>'WebController@configure'));

});

// Logged Web Pages
Route::group(['middleware' => ['logged']], function () {        
    
    Route::get('home', array('uses'=>'WebController@index'));     
    Route::get('profile', array('uses'=>'WebController@profile'));
    
});

// Visitor Routes
Route::group(['middleware' => ['myweb']], function () {   
     
    Route::get('installation_page',array('uses'=>'WebController@installation_page'));
    Route::post('install',array('uses'=>'SystemController@install'));
     
    // Authentication
    Route::get('/', array('uses'=>'WebController@landing_page'));              
    
});        
 
 