FORMAT: 1A

# ServMon Back-end API

This API allows the development of an independent front-end for ServMon.

# Group Logged

API endpoints that can be used by a logged in user.

## Domains Collection [/domains]

### List [GET /domains{?mode}]

Returns a list of domains defined in the system.

+ Parameters
    + mode: 'with_servers' (string,optional) - Defines a optional filtering mode

        + Default: 'normal'

        + Members
            + 'normal'
            + 'with_servers'

+ Response 200 (application/json)

        [
            {
                "id": "treeItem-gougousis.gr",
                "nid": 1,
                "text": "gougousis.gr",
                "children": [
                    {
                        "id": "treeItem-dom1.gougousis.gr",
                        "nid": 2,
                        "text": "dom1.gougousis.gr"
                    },
                    {
                        "id": "treeItem-dom2.gougousis.gr",
                        "nid": 3,
                        "text": "dom2.gougousis.gr"
                    }
                ]
            },
            {
                "id": "treeItem-takis.gr",
                "nid": 4,
                "text": "takis.gr",
                "state": {
                    "disabled": true
                }
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[]
        }

### List domain servers [GET /domains/{domName}/servers]

Returns a list of the servers that are attached to a specific domain but not to a subdomain of this, alongside their status.

+ Parameters
    + domName: site.mydomain.com (string) - The full name of the domain  

+ Response 200 (application/json)

        [
            {
                "id": 1,
                "ip": "62.169.226.30",
                "os": "Windows",
                "hostname": "s1",
                "owner": 0,
                "domain": 1,
                "watch": 0,
                "supervisor_email": "",
                "services": [

                ],
                "status": "on",
                "response_time": "10.195",
                "domain_name": "gougousis.gr"
            }
        ]

+ Response 401 (application/json)

        {
            "errors":[]
        }

+ Response 404 (application/json)

        {
            "errors":[]
        }

### List servers under domain [GET /domains/{domName}/all_servers]

Returns a list of all servers that belong to a domain or a subdomain of this domain.

+ Parameters
    + domName: site.mydomain.com (string) - The full name of the domain  

+ Response 200 (application/json)

        [
            {
                "id": 1,
                "hostname": "s1",
                "ip": "62.169.226.30",
                "os": "Windows",
                "domain": 1,
                "watch": 0,
                "full_name": "gougousis.gr",
                "services": [

                ],
                "status": "on",
                "response_time": "10.277",
                "domain_name": "gougousis.gr"
            },
            {
                "id": 2,
                "hostname": "s2",
                "ip": "148.251.138.169",
                "os": "Linux",
                "domain": 2,
                "watch": 0,
                "full_name": "dom1.gougousis.gr",
                "services": [

                ],
                "status": "on",
                "response_time": "61.486",
                "domain_name": "dom1.gougousis.gr"
            }
        ]

+ Response 401 (application/json)

        {
            "errors":[]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

### List webapps [GET /domains/{domName}/webapps]

Returns a list of all the web applications that are hosted on servers that belong to a domain or a subdomain of this domain.

+ Response 200 (application/json)

        [
            {
                "id": 1,
                "url": "http:\/\/www.iefimerida.gr",
                "language": "php",
                "developer": "Aris Tomas",
                "server": 1,
                "contact": "aris@tomas.com"
            }
        ]


+ Response 404 (application/json)

        {
            "errors":[]
        }

### Create [POST]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "domains": [
                    {
                        "node_name": "dummy.com",
                        "parent_domain": "",
                        "fake_domain": 0
                    }
                ]
            }

+ Response 200 (application/json)

        [
            {
                "id": 20,
                "parent_id": null,
                "node_name": "dummy.com",
                "full_name": "dummy.com",
                "fake": 0
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "node_name",
                    "message":  "The node name field is required.",
                    "index":  0
                }
            ]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

### Delete [DELETE /domains/{domName}]

+ Parameters
    + domName: 'site.mydomain.com' (string,required) - The full name of the domain  

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        [

        ]

+ Response 403 (application/json)

        {
            "errors":[]
        }

+ Response 404 (application/json)

        {
            "errors":[]
        }

+ Response 409 (application/json)

        {
            "errors":[]
        }

## Servers Collection [/servers]

### List [GET /servers{?mode}]

Returns a list of server

+ Parameters
    + mode: 'mine' (string,optional) - Defines a optional filtering mode

        + Default: 'mine'

        + Members
            + 'mine'

+ Response 200 (application/json)

        [
            {
                "id": 1,
                "ip": "62.169.226.30",
                "os": "Windows",
                "hostname": "s1",
                "supervisor_email": "",
                "domain": 1,
                "domain_name": "gougousis.gr"
            },
            {
                "id": 2,
                "ip": "148.251.138.169",
                "os": "Linux",
                "hostname": "s2",
                "supervisor_email": "",
                "domain": 2,
                "domain_name": "dom1.gougousis.gr"
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[]
        }

### Read [GET /servers/{serverId}]

Returns information about a server

+ Parameters
    + serverId: '3' (number,required) - The ID of the server

+ Response 200 (application/json)

        {
            "services": [

            ],
            "webapps": [
                {
                  "id": 1,
                  "url": "http:\/\/www.iefimerida.gr",
                  "language": "php",
                  "developer": "Aris Tomas",
                  "contact": "aris@tomas.com",
                  "image": "php.png",
                  "watch": 0,
                  "status": "on",
                  "time": 0.166827917099
                }
            ],
            "databases": [

            ]
        }

+ Response 404 (application/json)

        {
            "errors":[]
        }

### Create [POST]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "servers": [
                    {
                        "domain": "effr",
                        "hostname": "node1",
                        "ip": "121.23.145.33",
                        "os": "Windows Server 2008"
                    }
                ]
            }

+ Response 200 (application/json)

        [
            {
                "hostname": "node1",
                "domain": 19,
                "ip": "121.23.145.33",
                "os": "Windows Server 2008",
                "id": 5
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "hostname",
                    "message":  "The hostname field is required.",
                    "index":  0
                }
            ]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

### Snapshot [POST /servers/{serverId}/snapshot]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "authType": "password",
                "sshuser": "myusername",
                "sshpass": "mysecretpass",
                "sshport": 22
            }

+ Response 200 (application/json)

        {
             "uptime":"279 days",
             "count_processors":"4",
             "last5min_load":"0.10",
             "last10min_load":"0.09",
             "total_memory":"4048036",
             "total_memory_text":"4.05 GB",
             "free_memory":"820344",
             "free_memory_text":"820.34 MB",
             "df_blocks":[  
                  {  
                       "disk_name":"\/dev\/vda1",
                       "usage":"48",
                       "mount_point":"\/"
                  }
             ],
             "df_inodes":[  
                  {  
                       "disk_name":"\/dev\/vda1",
                       "usage":"40",
                       "mount_point":"\/"
                  }
             ],
             "services":[  
                  {  
                       "command":"java",
                       "user":"alexandros",
                       "ipType":"IPv6",
                       "protocol":"TCP",
                       "port":"10216",
                       "address":"\*"
                  },
                  {  
                       "command":"java",
                       "user":"root",
                       "ipType":"IPv6",
                       "protocol":"TCP",
                       "port":"80",
                       "address":"\*"
                  },
                  {  
                       "command":"java",
                       "user":"root",
                       "ipType":"IPv6",
                       "protocol":"TCP",
                       "port":"8181",
                       "address":"\*"
                  },
                  {  
                       "command":"postgres",
                       "user":"postgres",
                       "ipType":"IPv4",
                       "protocol":"TCP",
                       "port":"5432",
                       "address":"\*"
                  },
                  {  
                       "command":"postgres",
                       "user":"postgres",
                       "ipType":"IPv6",
                       "protocol":"TCP",
                       "port":"5432",
                       "address":"\*"
                  },
                  {  
                       "command":"sshd",
                       "user":"root",
                       "ipType":"IPv4",
                       "protocol":"TCP",
                       "port":"22",
                       "address":"\*"
                  },
                  {  
                       "command":"sshd",
                       "user":"root",
                       "ipType":"IPv6",
                       "protocol":"TCP",
                       "port":"22",
                       "address":"\*"
                  }
             ]
        }

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "sshuser",
                    "message":  "The sshuser field is required."
                }
            ]
        }

### Update [PUT]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "servers": [
                    {
                        "serverId": 5,
                        "hostname": "node1",
                        "ip": "121.23.145.34",
                        "os": "Windows Server 2008"
                    }
                ]
            }

+ Response 200 (application/json)

        [
             {
                  "id":5,
                  "ip":"121.23.145.34",
                  "os":"Windows Server 2008",
                  "hostname":"node1",
                  "owner":0,
                  "domain":19,
                  "watch":0,
                  "supervisor_email":"",
             }
        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "ip",
                    "message":  "The ip must be a valid IP address.",
                    "index":  0
                }
            ]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

### Delete [DELETE /servers/{serverId}]

+ Parameters
    + serverId: '3' (number,required) - The ID of the server

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

+ Response 403 (application/json)

        {
            "errors":[]
        }

+ Response 404 (application/json)

        {
            "errors":[]
        }

## Services Collection [/services]

### Read [GET /services/{serviceId}]

Returns information about a service

+ Parameters
    + serviceId: '3' (number,required) - The ID of the service

+ Response 200 (application/json)

        {
            "data": {
              "id": 2,
              "server": 2,
              "stype": "apache",
              "port": "80",
              "version": "2.2.2",
              "watch": 0
            }
        }

+ Response 400 (application/json)

        {
            "errors":[]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

### Create [POST]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "services": [
                    {
                        "server": 5,
                        "stype": "apache",
                        "port": "80",
                        "version": "2.2.2"
                    }
                ]
            }

+ Response 200 (application/json)

        [
            {
                "server": 5,
                "stype": "apache",
                "port": "80",
                "version": "2.2.2",
                "id": 3
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "version",
                    "message":  "The version field is required.",
                    "index":  0
                }
            ]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

### Update [PUT]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "services": [
                    {
                        "id": "3",
                        "stype": "apache",
                        "port": "80",
                        "version": "2.2.4"
                    }
                ]
            }

+ Response 200 (application/json)

        [
            {
                "id": 3,
                "server": 5,
                "stype": "apache",
                "port": "80",
                "version": "2.2.4",
                "owner": 0,
                "watch": 0,
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "port",
                    "message":  "The port must be an integer.",
                    "index":  0
                }
            ]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

### Delete [DELETE /services/{serviceId}]

+ Parameters
    + serviceId: '3' (number,required) - The ID of the service

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        [

        ]

+ Response 400 (application/json)

        {
            "errors":[]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

## Webapps Collection [/webapps]

### List [GET /webapps]

+ Response 200 (application/json)

        [
            {
                "id": 1,
                "url": "http:\/\/www.iefimerida.gr",
                "language": "php",
                "developer": "Aris Tomas",
                "server": 1,
                "contact": "aris@tomas.com"
            },
            {
                "id": 2,
                "url": "http:\/\/www.protagon.gr",
                "language": "java",
                "developer": "Michael Jordan",
                "server": 3,
                "contact": "mic@gmail.com"
            }
        ]

### Read [GET /webapps/{webappId}]

Returns information about a webapp

+ Parameters
    + webappId: '3' (number,required) - The ID of the webapp

+ Response 200 (application/json)

        {
            "data": {
                "id": 1,
                "url": "http:\/\/www.iefimerida.gr",
                "server": 1,
                "language": "php",
                "developer": "Aris Tomas",
                "contact": "aris@tomas.com",
                "watch": 0
            }
        }

+ Response 400 (application/json)

        {
            "errors":[]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }


### Create [POST]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "webapps": [
                    {
                        "server": 5,
                        "url": "http:\/\/www.voria.gr",
                        "language": "j2ee",
                        "developer": "aafdrg  ewttertete",
                        "contact": "asss@gmail.com"
                    }
                ]
            }

+ Response 200 (application/json)

        [
            {
                "server": 5,
                "url": "http:\/\/www.voria.gr",
                "language": "j2ee",
                "developer": "aafdrg  ewttertete",
                "contact": "asss@gmail.com",
                "id": 3
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "developer",
                    "message":  "The developer field is required.",
                    "index":  0
                }
            ]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

### Update [PUT]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "webapps": [
                    {
                        "id": "3",
                        "url": "http:\/\/www.voria.gr",
                        "language": "j2ee",
                        "developer": "aafdrg  ewtterte",
                        "contact": "asss@gmail.com",
                        "server": "5"
                    }
                ]
            }

+ Response 200 (application/json)

        [
            {
                "id": 3,
                "url": "http:\/\/www.voria.gr",
                "server": "5",
                "language": "j2ee",
                "developer": "aafdrg  ewtterte",
                "contact": "asss@gmail.com",
                "owner": 0,
                "watch": 0,
                "supervisor_email": "",
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "developer",
                    "message":  "The developer field is required.",
                    "index":  0
                }
            ]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

### Delete [DELETE /webapps/{webappId}]

+ Parameters
    + webappId: '3' (number,required) - The ID of the webapp

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        [

        ]

+ Response 400 (application/json)

        {
            "errors":[]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

## Databases Collection [/databases]

### Read [GET /databases/{databaseId}]

Returns information about a database

+ Parameters
    + databaseId: '3' (number,required) - The ID of the database

+ Response 200 (application/json)

        {
            "data": {
              "id": 1,
              "dbname": "prodb",
              "server": 1,
              "type": "mariadb",
              "related_webapp": 2,
              "related_webapp_name": "http:\/\/www.protagon.gr"
            }
        }

+ Response 400 (application/json)

        {
            "errors":[]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }


### Create [POST]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "databases": [
                    {
                        "server": 5,
                        "dbname": "blogdb",
                        "type": "postgres",
                        "related_webapp": "http:\/\/www.iefimerida.gr"
                    }
                ]
            }

+ Response 200 (application/json)

        [
            {
                "owner": 1,
                "server": 5,
                "dbname": "blogdb",
                "type": "postgres",
                "related_webapp": 1,
                "id": 2
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "dbname",
                    "message":  "The dbname field is required.",
                    "index":  0
                }
            ]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

### Update [PUT]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "databases": [
                    {
                        "id": "2",
                        "server": "5",
                        "dbname": "blogdb1",
                        "type": "postgres",
                        "related_webapp": "1"
                    }
                ]
            }

+ Response 200 (application/json)

        [
            {
                "id": 2,
                "dbname": "blogdb1",
                "server": "5",
                "type": "postgres",
                "related_webapp": "1",
                "owner": 1,
                "related_webapp_name": "http:\/\/www.iefimerida.gr"
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "dbname",
                    "message":  "The dbname field is required.",
                    "index":  0
                }
            ]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

### Delete [DELETE /databases/{databaseId}]

+ Parameters
    + databaseId: '3' (number,required) - The ID of the database

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        [

        ]

+ Response 400 (application/json)

        {
            "errors":[]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

## Generic Info [/info]

### Supported types [GET /info/supported_types]

+ Response 200 (application/json)

        {
          "service": [
            {
              "codename": "apache",
              "title": "Apache Web Server",
              "image": "apache.png",
              "default_port": 80
            },
            {
              "codename": "geoserver",
              "title": "Geoserver",
              "image": "geoserver.png",
              "default_port": 8080
            },
            {
              "codename": "glassfish",
              "title": "Glassfish",
              "image": "glassfish.png",
              "default_port": 8080
            },
            {
              "codename": "jetty",
              "title": "Jetty",
              "image": "jetty.png",
              "default_port": 8080
            },
            {
              "codename": "mariadb",
              "title": "MariaDB",
              "image": "mariadb.png",
              "default_port": 3306
            },
            {
              "codename": "mysql",
              "title": "MySQL",
              "image": "mysql.png",
              "default_port": 3306
            },
            {
              "codename": "postgres",
              "title": "PostgreSQL",
              "image": "postgres.png",
              "default_port": 5432
            },
            {
              "codename": "tomcat",
              "title": "Tomcat",
              "image": "tomcat.png",
              "default_port": 8080
            },
            {
              "codename": "virtuoso",
              "title": "Virtuoso",
              "image": "virtuoso.png",
              "default_port": 8890
            }
          ],
          "webapp": [
            {
              "codename": "j2ee",
              "title": "J2EE (servlets)",
              "image": "j2ee.png"
            },
            {
              "codename": "java",
              "title": "Java",
              "image": "java.png"
            },
            {
              "codename": "php",
              "title": "PHP",
              "image": "php.png"
            },
            {
              "codename": "proxy",
              "title": "Proxy Virtual Host",
              "image": "proxy.png"
            }
          ],
          "database": [
            {
              "codename": "mariadb",
              "title": "MariaDB",
              "image": "mariadb.png"
            },
            {
              "codename": "mysql",
              "title": "MySQL",
              "image": "mysql.png"
            },
            {
              "codename": "postgres",
              "title": "PostgreSQL",
              "image": "postgres.png"
            },
            {
              "codename": "sqlserver",
              "title": "SQL Server",
              "image": "sqlserver.png"
            },
            {
              "codename": "virtuoso",
              "title": "Virtuoso",
              "image": "virtuoso.png"
            }
          ]
        }

### Backup items [GET /info/backup_items]

+ Response 200 (application/json)

        {
            "domains": 4,
            "servers": 3,
            "services": 1,
            "webapps": 2,
            "databases": 1
        }

### Settings [GET /info/settings]

+ Response 200 (application/json)

        {
            "monitoring_period": "30",
            "monitoring_status": "0"
        }

### My Profile [GET /info/myprofile]

+ Response 200 (application/json)

        {
            "id": 1,
            "email": "user1@gmail.com",
            "firstname": "Alexandros",
            "lastname": "Gougousis",
            "activated": 1,
            "superuser": 1,
            "last_login": "2016-10-17 13:09:16"
        }

## Delegations [/delegations]

### List [GET /delegations{?mode}]

+ Parameters
    + mode: 'all' (string,optional) - Defines a optional filtering mode

          + Default: 'all'

          + Members
              + 'all'
              + 'my_servers'

+ Response 200 (application/json)

+ Response 400 (application/json)

        {
            "errors":[]
        }

+ Response 403 (application/json)

        {
            "errors":[]
        }

## Authentication [/auth]

### Logout [POST /auth/logout]

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g


+ Response 200 (application/json)

        [

        ]

# Group Elevated

API endpoints that can be used by superusers.

## User management [/users]

### List [GET /users{?mode}]

+ Parameters
    + mode: 'normal' (string,optional) - Defines a optional filtering mode

          + Default: 'normal'

          + Members
              + 'normal'
              + 'basic'

+ Response 200 (application/json)

        [
            {
                "id": 1,
                "firstname": "Alexandros",
                "lastname": "Gougousis",
                "email": "user1@gmail.com",
                "activated": 1,
                "superuser": 1,
                "last_login": "2016-10-17 13:09:16"
            },
            {
                "id": 2,
                "firstname": "Sydnee Bechtelar",
                "lastname": "Hermina Bartell DDS",
                "email": "casandra.frami@example.org",
                "activated": 0,
                "superuser": 0,
                "last_login": "0000-00-00 00:00:00"
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[]
        }

### Read [GET /users/{userId}]

+ Parameters
    + userId: '3' (number,required) - The ID of the user

+ Response 200 (application/json)

        {
            "id": 1,
            "email": "user1@gmail.com",
            "firstname": "Alexandros",
            "lastname": "Gougousis",
            "activated": 1,
            "superuser": 1,
            "last_login": "2016-10-17 13:09:16"
        }

+ Response 400 (application/json)

        {
            "errors":[]
        }

### Create [POST]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "users": [
                    {
                        "firstname": "Varkaris",
                        "lastname": "Antonis",
                        "email": "user9@gmail.com",
                        "password": "user4pwd",
                        "verify_password": "user4pwd"
                    }
                ]
            }

+ Response 200 (application/json)

        [
            {
                "firstname": "Varkaris",
                "lastname": "Antonis",
                "email": "user9@gmail.com",
                "activated": 0,
                "id": 5
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "lastname",
                    "message":  "The lastname field is required.",
                    "index":  0
                }
            ]
        }

### Enable user [PUT /users/{userId}/enable]

+ Parameters
    + userId: '3' (number,required) - The ID of the user

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        {
            "id": 2,
            "email": "casandra.frami@example.org",
            "firstname": "Sydnee Bechtelar",
            "lastname": "Hermina Bartell DDS",
            "activated": 1,
            "superuser": 0,
            "last_login": "0000-00-00 00:00:00"
        }

+ Response 400 (application/json)

        {
            "errors":[]
        }

### Disable user [PUT /users/{userId}/disable]

+ Parameters
    + userId: '3' (number,required) - The ID of the user

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        {
            "id": 2,
            "email": "casandra.frami@example.org",
            "firstname": "Sydnee Bechtelar",
            "lastname": "Hermina Bartell DDS",
            "activated": 0,
            "superuser": 0,
            "last_login": "0000-00-00 00:00:00"
        }

+ Response 400 (application/json)

        {
            "errors":[]
        }

### Make superuser [PUT /users/{userId}/make_superuser]

+ Parameters
    + userId: '3' (number,required) - The ID of the user

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        {
            "id": 2,
            "email": "casandra.frami@example.org",
            "firstname": "Sydnee Bechtelar",
            "lastname": "Hermina Bartell DDS",
            "activated": 0,
            "superuser": 1,
            "last_login": "0000-00-00 00:00:00"
        }

### Unmake superuser [PUT /users/{userId}/unmake_superuser]

+ Parameters
    + userId: '3' (number,required) - The ID of the user

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        {
            "id": 2,
            "email": "casandra.frami@example.org",
            "firstname": "Sydnee Bechtelar",
            "lastname": "Hermina Bartell DDS",
            "activated": 0,
            "superuser": 0,
            "last_login": "0000-00-00 00:00:00"
        }

+ Response 400 (application/json)

        {
            "errors":[]
        }

### Delete [DELETE /users/{userId}]

+ Parameters
    + userId: '3' (number,required) - The ID of the user

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        [

        ]

+ Response 400 (application/json)

        {
            "errors":[]
        }

## Delegation management [/delegations]

### Create [POST]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "delegations": [
                    {
                        "dtype": "domain",
                        "ditem": "funnydomain.com",
                        "duser": "user1@gmail.com"
                    }
                ]
            }

+ Response 200 (application/json)

        [
            {
                "user_id": 1,
                "domain_id": 19,
                "updated_at": "2016-10-17 21:20:51",
                "created_at": "2016-10-17 21:20:51",
                "id": 9
            }
        ]

+ Response 400 (application/json)

        {
            "errors":[]
        }

### Delete domain delegation [DELETE /delegations/domain/{delegationId}]

+ Parameters
    + delegationId: '3' (number,required) - The ID of a specific domain delegation

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        [

        ]

+ Response 404 (application/json)

        {
            "errors":[]
        }

### Delete server delegations [DELETE /delegations/server/{delegationId}]

+ Parameters
    + delegationId: '3' (number,required) - The ID of a specific server delegation

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        [

        ]

+ Response 404 (application/json)

        {
            "errors":[]
        }

## Backup management [/backup]

### List [GET]

+ Response 200 (application/json)

        [
            {
                "when": "13-10-2016 11:04:49",
                "size": 3312,
                "filename": "backup_13-10-2016_11-04-49.gz"
            },
            {
                "when": "13-10-2016 11:08:48",
                "size": 3313,
                "filename": "backup_13-10-2016_11-08-48.gz"
            },
            {
                "when": "13-10-2016 11:14:11",
                "size": 3313,
                "filename": "backup_13-10-2016_11-14-11.gz"
            }
        ]

### Create [POST]

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        [

        ]

### Restore [POST /backup/{filename}/restore]

+ Parameters
    + filename: 'backup_15-7-2016_11-04-30.gz' (string,required) - The filename of a specific backup

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        [

        ]

+ Response 404 (application/json)

        {
            "errors":[]
        }

### Delete [DELETE /backup/{filename}]

+ Parameters
    + filename: 'backup_15-7-2016_11-04-30.gz' (string,required) - The filename of a specific backup

+ Request

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

+ Response 200 (application/json)

        [

        ]

+ Response 404 (application/json)

        {
            "errors":[]
        }

## Monitoring management [/monitor]

### Items list [GET /monitor/items]

+ Response 200 (application/json)

        {
            "gougousis.gr": [
                {
                    "id": 1,
                    "hostname": "s1",
                    "watch": 0,
                    "services": [

                    ],
                    "webapps": [
                        {
                            "id": 1,
                            "url": "http:\/\/www.iefimerida.gr",
                            "language": "php",
                            "developer": "Aris Tomas",
                            "contact": "aris@tomas.com",
                            "image": "php.png",
                            "watch": 0
                        }
                    ]
                }
            ],
            "dom1.gougousis.gr": [
                {
                    "id": 2,
                    "hostname": "s2",
                    "watch": 0,
                    "services": [
                        {
                            "id": 2,
                            "server": 2,
                            "stype": "apache",
                            "port": "80",
                            "version": "2.2.2",
                            "title": "Apache Web Server",
                            "image": "apache.png",
                            "watch": 0
                        }
                    ],
                    "webapps": [

                    ]
                }
            ],
            "dom2.gougousis.gr": [

            ],
            "takis.gr": [
                {
                    "id": 3,
                    "hostname": "s4",
                    "watch": 0,
                    "services": [

                    ],
                    "webapps": [
                        {
                            "id": 2,
                            "url": "http:\/\/www.protagon.gr",
                            "language": "java",
                            "developer": "Michael Jordan",
                            "contact": "mic@gmail.com",
                            "image": "java.png",
                            "watch": 0
                        }
                    ]
                }
            ]
        }

### New Configuration [POST /monitor/items]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "items": [
                    "server--2",
                    "service--2"
                ]
            }

+ Response 200 (application/json)

        [

        ]

### Change status [PUT /monitor/status]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "config": {
                    "monitoring_status": 0,
                    "monitoring_period": "30"
                }
            }

+ Response 200 (application/json)

        [

        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "monitoring_period",
                    "message":  "The monitoring_period must be an integer."
                }
            ]
        }

# Group Visitors

API endpoints that can be used by a user that is not logged in.

## Authentication [/auth]

### Login [POST /auth/login]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "inputEmail": "user1@gmail.com",
                "inputPassword": "userpwd"
            }

+ Response 200 (application/json)

          [

          ]

+ Response 400 (application/json)

          {
              "errors":[]
          }

+ Response 403 (application/json)

          {
              "errors":[]
          }

### Request pwd reset link [POST /auth/request_reset_link]

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

            {
                "email": "user1@gmail.com",
                "captcha": "joEjw"
            }

+ Response 200 (application/json)

        [

        ]

+ Response 400 (application/json)

        {
            "errors":[
                {
                    "field":  "email",
                    "message":  "The email field is required."
                }
            ]
        }

### Set new password [POST /auth/set_new_password/{resetCode}]

+ Parameters
    + resetCode: '4geg99fjEG0QD@111tsmH73D' (string,required) - A one time reset code

+ Request (application/json)

    + Headers

            X-CSRF-Token: 3p3KXxl6JQ1fVREVv9by1T81qPDehAa7yCbOW3g

    + Body

              {
                  "new_password": "inffW23dqq",
                  "repeat_password": "inffW23dqq"
              }

+ Response 200 (application/json)

          [

          ]

+ Response 400 (application/json)

        {
            "errors":[]
        }
