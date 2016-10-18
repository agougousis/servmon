<?php

// Visitor API endpoints
Route::group(['prefix' => 'api','middleware' => ['web']], function () {

    Route::post('/system/install',array('uses'=>'SystemController@install'));

    Route::post('/auth/login', array('uses'=>'LoginController@login'));

    // Password reset
    Route::post('/auth/request_reset_link',array('uses'=>'PasswordController@sendResetLink'));
    Route::post('/auth/set_new_password/{code}', array('uses' => 'PasswordController@setPassword'));

});

// Normal API endpoints
Route::group(['prefix' => 'api','middleware' => ['logged']], function () {

    // Domain-related endpoints
    Route::get('/domains', array('uses'=>'DomainController@search'));
    Route::post('/domains', array('uses'=>'DomainController@create'));
    Route::delete('/domains/{domname}', array('uses'=>'DomainController@delete'));
    Route::get('/domains/{domName}/servers', array('uses'=>'DomainController@serverList'));
    Route::get('/domains/{domName}/all_servers', array('uses'=>'DomainController@serversUnderDomain'));
    Route::get('/domains/{domName}/webapps', array('uses'=>'DomainController@webappList'));

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
    Route::get('/info/supported_types', array('uses'=>'InfoController@supportedTypesList'));
    Route::get('/info/backup_items', array('uses'=>'InfoController@backupItems'));
    Route::get('/info/settings', array('uses'=>'InfoController@settings'));
    Route::get('/info/myprofile', array('uses'=>'InfoController@myprofile'));

    // Delegation Info
    Route::get('delegations', array('uses'=>'DelegationController@search'));

    Route::post('/auth/logout', array('uses'=>'LoginController@logout'));

});

// Elevated API endpoints
Route::group(['prefix' => 'api','middleware' => ['superuser']], function () {

    Route::get('users/{userId}',array('uses'=>'UserController@read'))->where('userId', '[0-9]+');
    Route::get('users',array('uses'=>'UserController@search'));
    Route::post('users',array('uses'=>'UserController@addUsers'));
    Route::put('users/{userId}/enable',array('uses'=>'UserController@enableUser'))->where('userId', '[0-9]+');
    Route::put('users/{userId}/disable',array('uses'=>'UserController@disableUser'))->where('userId', '[0-9]+');
    Route::put('users/{userId}/make_superuser',array('uses'=>'UserController@makeSuperuser'))->where('userId', '[0-9]+');
    Route::put('users/{userId}/unmake_superuser',array('uses'=>'UserController@unmakeSuperuser'))->where('userId', '[0-9]+');
    Route::delete('users/{userId}',array('uses'=>'UserController@deleteUser'))->where('userId', '[0-9]+');

    // Delegation Management
    Route::post('delegations', array('uses'=>'DelegationController@create'));
    Route::delete('/delegations/domain/{delegationId}', array('uses'=>'DelegationController@deleteDomainDelegation'));
    Route::delete('/delegations/server/{delegationId}', array('uses'=>'DelegationController@deleteServerDelegation'));

    // Backup Management
    Route::get('backup', array('uses'=>'BackupController@search'));
    Route::post('backup', array('uses'=>'BackupController@create'));
    Route::post('backup/{filename}/restore', array('uses'=>'BackupController@restore'));
    Route::delete('backup/{filename}', array('uses'=>'BackupController@delete'));

    // Monitoring Scheduler Management
    Route::get('/monitor/items', array('uses'=>'MonitorController@getMonitorable'));
    Route::post('/monitor/items', array('uses'=>'MonitorController@updateConfiguration'));
    Route::put('monitor/status',array('uses'=>'MonitorController@changeStatus'));

});

// Elevated Web Pages
Route::group(['middleware' => ['superuser']], function () {

    Route::get('user_management',array('uses'=>'WebController@userManagement'));
    Route::get('user_management/{userId}',array('uses'=>'WebController@userProfileManagement'))->where('userId', '[0-9]+');
    Route::get('/domains/delegation', array('uses'=>'WebController@delegationsPage'));
    Route::get('/backup', array('uses'=>'WebController@backupPage'));
    Route::get('/monitor/configure', array('uses'=>'WebController@configure'));

});

// Logged Web Pages
Route::group(['middleware' => ['logged']], function () {

    Route::get('home', array('uses'=>'WebController@index'));
    Route::get('profile', array('uses'=>'WebController@profile'));

});

// Visitor Routes
Route::group(['middleware' => ['web']], function () {

    Route::get('installation_page',array('uses'=>'WebController@installationPage'));

    // Authentication
    Route::get('/', array('uses'=>'WebController@landingPage'));

    // Password Reset
    Route::get('password_reset_request',array('uses'=>'WebController@passwordResetRequest'));
    Route::get('reset_link_sent',array('uses'=>'WebController@resetLinkSent'));
    Route::get('password_reset/{code}', array('uses' => 'WebController@setPasswordPage'));

});

