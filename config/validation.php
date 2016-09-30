<?php

return array(
    'installation'  =>   array( 
        'server'    =>  'required|max:150',
        'dbname'    =>  'required|max:100',
        'dbuser'    =>  'required|max:100',
        'dbpwd'     =>  'required|max:30',
        'url'       =>  'required|url|max:200'
    ),    
    'password_reset_request'    =>  array(
        'email'     =>  'required|email|exists:users,email',
        'captcha'   =>  'required|captcha',
    ),
    'password_reset'    =>  array(
        'new_password'      =>  'required|min:6',
        'repeat_password'   =>  'required|same:new_password',
    ),
    'add_user'  =>  array(
        'firstname' =>  'required|max:30',
        'lastname'  =>  'required|max:30',
        'email'     =>  'required|email|unique:users,email',
        'password'  =>  'required|min:6|max:50',
        'verify_password'   =>  'required|same:password',
    ),
    'domain_create'            => array(
        'node_name'     =>  'required|max:100',
        'parent_domain' =>  'max:200|exists:domains,full_name',
        'fake_domain'   =>  'int'
    ),
    'server_create'            => array(
        'hostname'  =>  'required|max:100',
        'domain'    =>  'required|string|max:255|exists:domains,full_name',
        'ip'        =>  'required|string|max:35',
        'os'        =>  'required|string|max:50'
    ),
    'server_update'            => array(
        'serverId'  =>  'required|int|exists:servers,id',
        'hostname'  =>  'required|max:100',
        'ip'        =>  'required|ip|max:35',
        'os'        =>  'required|string|max:50'
    ),
    'create_service'    =>  array(     
        'server'    =>  'required|int|exists:servers,id',
        'stype'      =>  'required|max:50|exists:service_types,codename',
        'port'      =>  'required|integer',
        'version'   =>  'required|max:50'        
    ),
    'create_webapp'    =>  array(     
        'server'    =>  'required|int|exists:servers,id',
        'url'       =>  'required|string|max:100|unique:webapps,url',
        'language'  =>  'required|string|max:15|exists:webapp_types,codename',
        'developer' =>  'required|string|max:50',
        'contact'   =>  'required|string|max:50'
    ),
    'create_database'    =>  array(     
        'server'    =>  'required|int|exists:servers,id',
        'dbname'    =>  'required|string|max:30',
        'type'      =>  'required|string|max:15|exists:database_types,codename',
        'related_webapp' =>  'string|max:100|exists:webapps,url'
    ),
    'domain_delegation_create'            => array(
        'dtype'     =>  'required|in:domain,server',
        'ditem'     =>  'required|string|max:255|exists:domains,full_name',
        'duser'     =>  'required|max:50|email|exists:users,email',
    ),
    'server_delegation_create'            => array(
        'dtype'     =>  'required|in:domain,server',
        'ditem'     =>  'required|int|exists:servers,id',
        'duser'     =>  'required|max:50|email|exists:users,email',
    ),
    'login'            => array(
        'inputEmail'    =>  'required|max:100|email|exists:users,email',
        'inputPassword' =>  'required|max:100'
    ),
    'update_webapp'    =>  array(
        'url'       =>  'required|max:100|url',
        'language'  =>  'required|max:15',
        'developer' =>  'required|max:50',
        'contact'   =>  'required|max:50|email',
        'server'    =>  'required|integer|exists:servers,id'
    ),
    'update_service'    =>  array(   
        'id'        =>  'required|int|exists:services,id',
        'stype'     =>  'required|max:50|exists:service_types,codename',
        'port'      =>  'required|integer',
        'version'   =>  'required|max:50'        
    ),
    'update_database'    =>  array(   
        'dbname'    =>  'required|max:30',        
        'server'    =>  'required|integer',
        'type'      =>  'required|max:15|exists:database_types,codename',
        'related_webapp'   =>  'integer'        
    ),
    'config_monitor'    =>  array(   
        'monitoring_status'    =>  'max:10',        
        'monitoring_period'    =>  'required|integer'     
    ),
);