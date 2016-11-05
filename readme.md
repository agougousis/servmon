# Server Monitoring - ServMon

## Requirements

* Linux
    * OpenSSL
    * libssh2 (see: http://php.net/manual/en/ssh2.requirements.php)
* PHP >= 5.6.4
    * OpenSSL PHP Extension
    * PDO PHP Extension
    * Mbstring PHP Extension
    * Tokenizer PHP Extension
    * XML PHP Extension
* MySQL


## Frameworks/Packages/Libraries 

PHP

* Laravel 5 PHP Framework (https://laravel.com/)
* Nested Set pattern for Eloquent ORM (https://github.com/etrepat/baum)
* HTML and Form Builders for the Laravel Framework (https://github.com/laravelcollective/html)
* Framework-agnostic backup manager (https://github.com/backup-manager/backup-manager)
* Laravel driver for Backup Manager (https://github.com/backup-manager/laravel)
* Constant-Time character encoding (https://github.com/paragonie/constant_time_encoding)

Javascript

* jQuery 1.11.2 (https://jquery.com/)
* toastr - non-blocking notifications library (https://github.com/CodeSeven/toastr)
* jsTree - jQuery plugin for tree structures (https://www.jstree.com/)
* JustGage - javascript plugin for gauge animation (http://justgage.com/)
* Raphaël - javascript library for vector graphics (https://dmitrybaranovskiy.github.io/raphael/)
* Bootstrap CSS Framework (http://getbootstrap.com/)
* Bootstrap Toggle - Bootstrap plugin for toggle buttons (http://www.bootstraptoggle.com/)

## Features

### Embedded Installer
A basic installer is included in order to automate the task of buidling the database schema and filling in some 
vital information.

### Basic user roles
Normal users can add their own domains and servers, add items to them and check their current status. 
When they are delegated a domain or server created by another user, they are allowed the same tasks on them. 
Superusers are allowed to delegate domain or server management to other users, access the user management 
functionality, backup and restore the system database, enable or disable monitoring of specific items and 
make changes to system-wide configuration. 
			
### Multi-level monitoring
ServMon allows monitoring in three levels. First of all, you can check the status of servers. Server status is tested
with 'ping' command so, make sure there is not any firewall blocking ping access to the servers you want to monitor.
Secondly, you can monitor services running on a server. Service status is tested by establishing a TCP/UDP connection to 
the service. Lastly, you can monitor the status of web applications. The status of a web application is tested by making
an HTTP HEADER request to that web application.

### Management Delegation
You can delegate the task of maintaining the correct structure of some domains and servers to other users without
hesitation since ServMon does not affect in any way your real servers and domains. The ServMon items are just a 
representation of your real network items. 

### Scheduled Monitoring with Email notifications
Apart from monitoring the current status of your network items (servers, services, web apps) you can schedule 
a number of items to monitor periodically in fixed time intervals selected by you. When an item status is found 
"OFF" or "DOWN", an email will be sent to the address that has been designated for this item. If notifications 
for multiple items need to be sent to the same email address, they are all sent through a single email.

### Backup/Restore		
You can take multiple backups of the application's database and restore the database using any of these backus.

## Screenshots

![installation](https://cloud.githubusercontent.com/assets/5471589/18911754/8f4b6872-8587-11e6-9e2e-a197ebd0fddc.png)

![home](https://cloud.githubusercontent.com/assets/5471589/18911341/3750c5c4-8585-11e6-8310-9d36729e4a6b.png)

![user_management](https://cloud.githubusercontent.com/assets/5471589/18911358/461f981e-8585-11e6-9ff2-13dd3b6884f8.png)

![delegations](https://cloud.githubusercontent.com/assets/5471589/18911361/4b76ff46-8585-11e6-925a-c3212458f7ad.png)

![monitor](https://cloud.githubusercontent.com/assets/5471589/18911365/5014a9a4-8585-11e6-9a22-0f904812e04e.png)

![backup](https://cloud.githubusercontent.com/assets/5471589/18911398/8325d070-8585-11e6-9895-1b571948c512.png)

![ssh-credentials](https://cloud.githubusercontent.com/assets/5471589/20030805/08e07ad2-a375-11e6-9ae5-96c5d3b5a2e6.png)

![ssh-status](https://cloud.githubusercontent.com/assets/5471589/20030808/16c1893e-a375-11e6-8f29-6b4bc759f091.png)

## Back-end API documentation

The API documentation has been written with API Blueprint and is located in /docs folder.