# Server Monitoring - ServMon

## Requirements

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
* Bootstrap CSS Framework (http://getbootstrap.com/)
* Bootstrap Toggle - Bootstrap plugin for toggle buttons (http://www.bootstraptoggle.com/)

## Features

### Embedded Installer
A basic installer is included in order to automate the task of buidling the database schema and filling in some vital information.

### User roles
Normal users can add their own domains and servers, add items to them and check their current status. When they are delegated a domain or server created by another user, they are allowed the same tasks on them.
			
Superusers can manage delegations, backups, users, and configure monitoring of every item. They can modify items as long they are delegated (by someone else or by themselves) the relevant domain or server that item belongs to. 
			
### Multi-level monitoring
You can monitor the status of servers, services and web applications. For server monitoring the "ping" tool is used. For services, we try to establish a tcp connection on the relevant port. For web applications, an HTTP HEADER request is used.

### Task Delegation
You can delegate the task of maintaining the correct structure of some domains and servers to other users.

### Scheduled Monitoring with Email notifications
You can select which items you want to monitor periodically and how often. When an item status is found "OFF" or "DOWN", an email will be sent to the address that has been designated for this item.

### Backup/Restore		
You can backup and restore the application's database. 

## Screenshots

![installation](https://cloud.githubusercontent.com/assets/5471589/18911754/8f4b6872-8587-11e6-9e2e-a197ebd0fddc.png)

![home](https://cloud.githubusercontent.com/assets/5471589/18911341/3750c5c4-8585-11e6-8310-9d36729e4a6b.png)

![user_management](https://cloud.githubusercontent.com/assets/5471589/18911358/461f981e-8585-11e6-9ff2-13dd3b6884f8.png)

![delegations](https://cloud.githubusercontent.com/assets/5471589/18911361/4b76ff46-8585-11e6-925a-c3212458f7ad.png)

![monitor](https://cloud.githubusercontent.com/assets/5471589/18911365/5014a9a4-8585-11e6-9a22-0f904812e04e.png)

![backup](https://cloud.githubusercontent.com/assets/5471589/18911398/8325d070-8585-11e6-9895-1b571948c512.png)

