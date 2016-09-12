jQuery.fn.center = function () {
    this.css("position","fixed");
    this.css("top", (jQuery(window).height() / 2) - (this.outerHeight() / 2));
    this.css("left", (jQuery(window).width() / 2) - (this.outerWidth() / 2));
    return this;
}

function ajaxFailure(jqXHR, textStatus, errorThrown) {
    $('#loading-image').hide();
    response = JSON.parse(jqXHR.responseText);
    var errorMessage = jqXHR.statusText+"<br>";
    for(var j=0; j < response.errors.length; j++){
        errorItem = response.errors[j];
        errorMessage = errorMessage+"<strong>"+errorItem.field+"</strong>: "+errorItem.message+"<br>";                    
    }
    toastr.error(errorMessage,{timeOut: 5000});  
}

function GuiManagerClass(){
    
    this.clearServerList = function(){
        $('#server-list-table').empty();
        $('#services-list-table tbody').empty();
        $('#webapps-list-table tbody').empty();
        $('#databases-list-table tbody').empty();
        $('#webappInfoDialog select[name="server"] option').remove();
    }
    
    this.addServers = function(serverList){
        for(var i=0; i<serverList.length; i++){
            this.addServer(serverList[i]);
        }
        /*
        for (var key in serverList) {
            if (serverList.hasOwnProperty(key)) {
                this.addServer(serverList[key]);
            }
        }
        */
    };

    this.updateServers = function(serverList){
        for (var key in serverList) {
            if (serverList.hasOwnProperty(key)) {
                this.updateServer(serverList[key]);
            }
        }
    };

    this.addServer = function(serverInfo){
        var serverListTable = $('#server-list-table');
        if(serverInfo.status === 'on')
            statusImg = '/images/green.png';
        else
            statusImg = '/images/red.png';
        serverListTable.append("<tr onclick='ajaxManager.serverSelected("+serverInfo.id+")' id='server-"+serverInfo.id+"' class='server-list-row'><td><img src='"+statusImg+"'></td><td>"+serverInfo.hostname+"</td><td>"+serverInfo.domain_name+"</td><td>"+serverInfo.ip+"</td><td>"+serverInfo.os+"</td><td class='services'></td></tr>");

        var servicesCell = $('#server-'+serverInfo.id+' .services').first();     
        this.addServicesIcons(serverInfo.services,servicesCell);  
        
        // Add server option in relevant dropdowns
        $('#webappInfoDialog select[name="server"]').append("<option value='"+serverInfo.id+"'>"+serverInfo.hostname+"."+serverInfo.domain_name+"</option>");
        $('#serviceInfoDialog select[name="server"]').append("<option value='"+serverInfo.id+"'>"+serverInfo.hostname+"."+serverInfo.domain_name+"</option>");
        $('#databaseInfoDialog select[name="server"]').append("<option value='"+serverInfo.id+"'>"+serverInfo.hostname+"."+serverInfo.domain_name+"</option>");
    };

    this.loadServerInfo = function(serverInfo){         
        
        var target = $('#services-list-table tbody');        
        target.empty();
        this.addServices(serverInfo.services);        
        
        var target = $('#webapps-list-table tbody');        
        target.empty();
        this.addWebapps(serverInfo.webapps);             
        
        var target = $('#databases-list-table tbody');        
        target.empty();
        this.addDatabases(serverInfo.databases);                 
        
    }

    this.updateServer = function(serverInfo){
        // Get pointers to each row cell
        var col1 =  $('#server-'+serverInfo.id+' td:nth-child(1)');
        var col2 =  $('#server-'+serverInfo.id+' td:nth-child(2)');
        var col3 =  $('#server-'+serverInfo.id+' td:nth-child(3)');
        var col4 =  $('#server-'+serverInfo.id+' td:nth-child(4)');
        var col5 =  $('#server-'+serverInfo.id+' td:nth-child(5)');

        // Update status info
        if(serverInfo.status === 'on')
                statusImg = '/images/green.png'
            else
                statusImg = '/images/red.png';

        col1.empty().append("<img src='/servmon-ui"+statusImg+"'>");

        // Update text info
        col2.empty().append(serverInfo.dns);
        col3.empty().append(serverInfo.ip);
        col4.empty().append(serverInfo.os);

        // Update services
        col5.empty();        
        this.addServicesIcons(serverInfo.services,col5);    
    };

    this.deleteServer = function(hostname){
        $('#server-'+hostname).remove();
    };

    this.addServicesIcons = function(serviceList,targetCell){        
        for(var serviceKey in serviceList){    
            switch(serviceList[serviceKey]){
                case 'mysql':                            
                    targetCell.append("<img class='serviceImg' src='/images/mysql.png' title='mysql'>");
                    break;
                case 'apache':
                    targetCell.append("<img class='serviceImg' src='/images/apache.png' title='apache'>");
                    break;
                case 'tomcat':
                    targetCell.append("<img class='serviceImg' src='/images/tomcat.png' title='tomcat'>");
                    break;
                case 'glassfish':
                    targetCell.append("<img class='serviceImg' src='/images/glassfish.png' title='glassfish'>");
                    break;
                case 'virtuoso':
                    targetCell.append("<img class='serviceImg' src='/images/virtuoso.png' title='virtuoso'>");
                    break;
                case 'geoserver':                            
                    targetCell.append("<img class='serviceImg' src='/images/geoserver.png' title='geoserver'>");
                    break;
                case 'jetty':                            
                    targetCell.append("<img class='serviceImg' src='/images/jetty.png' title='jetty'>");
                    break;
                case 'mariadb':                            
                    targetCell.append("<img class='serviceImg' src='/images/mariadb.png' title='mariadb'>");
                    break;
                case 'postgres':                            
                    targetCell.append("<img class='serviceImg' src='/images/postgres.png' title='postgres'>");
                    break;
            }
        }
    };

    this.addServices = function(serviceList){
        for (var key in serviceList) {
            if (serviceList.hasOwnProperty(key)) {            
                this.addService(serviceList[key]);
            }
        }
    };

    this.addService = function(serviceInfo){

        var serviceListTable = $('#services-list-table tbody');
        if(serviceInfo.status === 'on')
            statusImg = '/images/green.png';
        else if(serviceInfo.status === 'off')
            statusImg = '/images/red.png';
        else 
            statusImg = '/images/gray.png';
        if(serviceInfo.time != null){
            respTimeNumber = new Number(serviceInfo.time);
            respTime = respTimeNumber.toFixed(5);
        } else {
            respTime = '';
        }
        serviceIcon = this.getServiceIcon(serviceInfo);

        var rowId = "service-row-"+serviceInfo.id;
        serviceListTable.append("<tr id='"+rowId+"'><td>"+serviceIcon+"</td><td>"+ajaxManager.supported_service_types[serviceInfo.stype].title+"</td><td>"+serviceInfo.version+"</td><td>"+respTime+"</td><td><img src='"+statusImg+"'></td></tr>");    

    };

    this.getServiceIcon = function(serviceInfo){          
        serviceImgInfo = ajaxManager.supported_service_types[serviceInfo.stype];
        serviceIcon = "<img class='serviceImg' src='/images/"+serviceImgInfo.image+"' title='"+serviceImgInfo.title+"' class=\"service-type-img\" onclick='ajaxManager.editServiceIconClicked("+serviceInfo.id+")' onmouseover=\"this.src='/images/edit.png'\" onmouseout=\"this.src='/images/"+serviceImgInfo.image+"'\">";                
        return serviceIcon;
    };

    this.updateServices = function(serviceList){
        for (var key in serviceList) {
            if (serviceList.hasOwnProperty(key)) {            
                this.updateService(serviceList[key]);
            }
        }
    };

    this.updateService = function(serviceInfo){
        var rowId = "service-row-"+serviceInfo.id;

        // Get pointers to each row cell
        var col1 =  $('#'+rowId+' td:nth-child(1)');
        var col2 =  $('#'+rowId+' td:nth-child(2)');
        var col3 =  $('#'+rowId+' td:nth-child(3)');
        var col4 =  $('#'+rowId+' td:nth-child(4)');
        var col5 =  $('#'+rowId+' td:nth-child(5)');

        // Update status info
        if(serviceInfo.status === 'on')
            statusImg = '/images/green.png';
        else if(serviceInfo.status == 'off')
            statusImg = '/images/red.png';
        else
            statusImg = '/images/gray.png';
        if(serviceInfo.time != null){
            respTimeNumber = new Number(serviceInfo.time);
            respTime = respTimeNumber.toFixed(5);
        } else {
            respTime = '';
        }

        col5.empty().append("<img src='"+statusImg+"'>");
        col4.empty().append(respTime);

        // Update text info    
        col2.empty().append(ajaxManager.supported_service_types[serviceInfo.stype].title);
        col3.empty().append(serviceInfo.version);

        // Update services
        col1.empty();        
        col1.append(this.getServiceIcon(serviceInfo));   
    };

    this.deleteService = function(serviceInfo){
        var rowId = "service-"+serviceInfo.type+"-"+serviceInfo.version;
        $('#'+rowId).remove();
    };

    this.addDatabases = function(databaseList){
        for (var key in databaseList) {
            if (databaseList.hasOwnProperty(key)) {            
                this.addDatabase(databaseList[key]);
            }
        }
    };

    this.addDatabase = function(databaseInfo){
        var serviceListTable = $('#databases-list-table');
        typeImg = this.getDatabaseIcon(databaseInfo);
        if(databaseInfo.related_webapp == null){
            related_app = '';
        } else {
            related_app = databaseInfo.related_webapp_name;
        }
        var rowId = "db-row-"+databaseInfo.id;
        serviceListTable.append("<tr id='"+rowId+"'><td>"+typeImg+"</td><td>"+databaseInfo.dbname+"</td><td>"+related_app+"</td></tr>");    

    };

    this.getDatabaseIcon = function(databaseInfo){
        databaseImgInfo = ajaxManager.supported_database_types[databaseInfo.type];
        databaseIcon = "<img class='databaseImg' src='/images/"+databaseImgInfo.image+"' title='"+databaseImgInfo.title+"' class=\"service-type-img\" onclick='ajaxManager.editDatabaseIconClicked("+databaseInfo.id+")' onmouseover=\"this.src='/images/edit.png'\" onmouseout=\"this.src='/images/"+databaseImgInfo.image+"'\">";                
        return databaseIcon;
    };

    this.updateDatabases = function(databaseList){
        for (var key in databaseList) {
            if (databaseList.hasOwnProperty(key)) {            
                this.updateDatabase(databaseList[key]);
            }
        }
    };

    this.updateDatabase = function(databaseInfo){
        var rowId = "db-row-"+databaseInfo.id;

        // Get pointers to each row cell
        var col1 =  $('#'+rowId+' td:nth-child(1)');
        var col2 =  $('#'+rowId+' td:nth-child(2)');
        var col3 =  $('#'+rowId+' td:nth-child(3)');    

        // Update text info    
        col2.empty().append(databaseInfo.dbname);
        col3.empty().append(databaseInfo.related_webapp_name);

        // Update services
        col1.empty();
        col1.append(this.getDatabaseIcon(databaseInfo));   
    };

    this.deleteDatabase = function(databaseInfo){
        var rowId = "db-"+databaseInfo.dbname;
        $('#'+rowId).remove();
    };

    this.getWebappIcon = function(webappInfo){                        
        webappImgInfo = ajaxManager.supported_webapp_types[webappInfo.language];
        webappIcon = "<img class='webappImg' src='/images/"+webappImgInfo.image+"' title='"+webappImgInfo.title+"' class=\"service-type-img\" onclick='ajaxManager.editWebappIconClicked("+webappInfo.id+")' onmouseover=\"this.src='/images/edit.png'\" onmouseout=\"this.src='/images/"+webappImgInfo.image+"'\">";                
        return webappIcon;
    };

    this.addWebapps = function(webappList){
        for (var key in webappList) {
            if (webappList.hasOwnProperty(key)) {            
                this.addWebapp(webappList[key]);
            }
        }
    };

    this.addWebapp = function(webappInfo){
        var webappsListTable = $('#webapps-list-table tbody');
        typeImg = this.getWebappIcon(webappInfo);

        if(webappInfo.status === 'on')
            statusImg = '/images/green.png';
        else if(webappInfo.status === 'off')
            statusImg = '/images/red.png';
        else
            statusImg = '/images/gray.png';
        if(webappInfo.time != null){
            respTimeNumber = new Number(webappInfo.time);
            respTime = respTimeNumber.toFixed(5);
        } else {
            respTime = '';
        }
        
        var rowId = "webapp-row-"+webappInfo.id;
                
        webappsListTable.append("<tr id='"+rowId+"'><td>"+typeImg+"</td><td><a href='"+webappInfo.url+"'>"+webappInfo.url+"</a></td><td>"+webappInfo.developer+"</td><td>"+respTime+"</td><td><img src='"+statusImg+"'></td></tr>");    

    };

    this.updateWebapps = function(webappList){
        for (var key in webappList) {
            if (webappList.hasOwnProperty(key)) {            
                this.updateWebapp(webappList[key]);
            }
        }
    };

    this.updateWebapp = function(webappInfo){
        var rowId = "webapp-row-"+webappInfo.id;

        // Get pointers to each row cell
        var col1 =  $('#'+rowId+' td:nth-child(1)');
        var col2 =  $('#'+rowId+' td:nth-child(2)');
        var col3 =  $('#'+rowId+' td:nth-child(3)');    
        var col4 =  $('#'+rowId+' td:nth-child(4)'); 
        var col5 =  $('#'+rowId+' td:nth-child(5)');

        // Update status info
        if(webappInfo.status === 'on')
            statusImg = '/images/green.png';
        else if (webappInfo.status === 'off')
            statusImg = '/images/red.png';
        else
            statusImg = '/images/gray.png';
        if(webappInfo.time != null){
            respTimeNumber = new Number(webappInfo.time);
            respTime = respTimeNumber.toFixed(5);
        } else {
            respTime = '';
        }
        col4.empty().append(respTime);
        col5.empty().append("<img src='"+statusImg+"'>");

        // Update text info    
        col2.empty().append("<a href='"+webappInfo.url+"'>"+webappInfo.url+"</a>");
        col3.empty().append(webappInfo.developer);

        // Update services
        col1.empty();
        col1.append(this.getWebappIcon(webappInfo));   
    };

    this.deleteWebapp = function(webappInfo){
        var rowId = "webapp-"+webappInfo.dns;
        $('#'+rowId).remove();
    };
    
    this.changeServerListDomain = function(domainName){
        $('#server-list-domain').html("("+domainName+")");
    }
    
    this.manageDynamicIcons = function(action){
        switch(action){
            case 'domainSelected':
                //$('#addDomainButton').show();
                $('#deleteDomainButton').show();
                $('#addServerButton').show();
                $('#editServerButton').hide();
                $('#deleteServerButton').hide();
                break;
            case 'domainUnselected':
                //$('#addDomainButton').hide();
                $('#deleteDomainButton').hide();
                $('#addServerButton').hide();
                $('#editServerButton').hide();
                $('#deleteServerButton').hide();
                $('#server-list-table').empty();
                $('#server-list-standalone').empty();
                $('#services-list-table tbody').empty();
                $('#webapps-list-table tbody').empty();
                $('#databases-list-table tbody').empty();
                break;
            case 'serverUnselected':
                $('#editServerButton').hide();
                $('#deleteServerButton').hide();
                break;
            case 'serverSelected':
                $('#editServerButton').show();
                $('#deleteServerButton').show();
                $('#add-service-icon').show();
                $('#add-webapp-icon').show();
                $('#add-database-icon').show();
                break;
        }
    }
    
    this.indicateServerLine = function(oldSelectedId,newSelectedId){
        if(oldSelectedId > 0){       
            $('#server-'+oldSelectedId).css('background-color','');            
        } 
        $('#server-'+newSelectedId).css('background-color','#f5f5f5');
    }
    
    this.reloadTree = function(treeData){
        // Locate the tree container
        $('#domain_tree')
            // Listen for events 
            .on('select_node.jstree', function (event, data) {                                             
                if(!data.node.state.disabled){
                    ajaxManager.domainSelected(data.node.text);                                        
                }                                                                                                           
            })
            .on('deselect_node.jstree', function (event, data) {
                ajaxManager.domainUnselected();                                    
            })
            // Create the tree instance
            .jstree({
                "core" : {
                    "multiple" : false,
                    "animation" : 0,
                    "data": treeData
                }
        });
    }
    
}

function AjaxManagerClass(){
    
    this.selectedServer = -1;
    this.domainServers = null;
    this.selectedDomain = '';
    this.webappList = {};
    this.myServersList = {};
    this.baseUrl = window.location.protocol + "//" + window.location.host + "/";
    this.supported_webapp_types = {};
    this.supported_service_types = {};
    this.supported_database_types = {};    
    
    
    // Helper functions    
    
    this.loadServerInfo = function(){
        
        $.ajax({
            url: this.baseUrl+"api/servers/"+this.selectedServer,
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data,textStatus,jqXHR ) {                                
                guiManager.loadServerInfo(data);
                guiManager.manageDynamicIcons('serverSelected');
                $('#loading-image').hide();
            },
            error: ajaxFailure
        });
        
    }
    
    this.loadDomainServers = function(fullDomainName){  
        
        var thisObject = this;
        
        $.ajax({
            url: this.baseUrl+"api/domains/"+fullDomainName+"/all_servers",
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data,textStatus,jqXHR ) {
                thisObject.selectedDomain = fullDomainName;
                thisObject.domainServers = data;
                guiManager.changeServerListDomain(fullDomainName);
                guiManager.clearServerList();
                guiManager.addServers(data);
            },
            error: ajaxFailure
        });
                
    };
    
    // Used for page initialization 
    
    this.initializeBackupPage = function(){
        $.ajax({
            url: this.baseUrl+"api/info/backup_items",
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data ) {                                
                $('#domains_counter').html(data.domains+' domains');
                $('#servers_counter').html(data.domains+' servers');
                $('#services_counter').html(data.domains+' services');
                $('#webapps_counter').html(data.domains+' webapps');
                $('#databases_counter').html(data.domains+' databases');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Counting backup items failed!!');                        
            }
        });

        $.ajax({
            url: this.baseUrl+"api/backup",
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data ) {                                
                for(var w=0; w<data.length; w++){
                    $('#backup-list-table tbody').append("<tr>"+
                            "<td>"+data[w].when+"</td>"+"<td>"+data[w].size+"</td><td>"+
                            "<img class='imgLink' src='"+ajaxManager.baseUrl+"images/delete.png' onclick=\"ajaxManager.deleteBackupIconClicked('"+data[w].filename+"')\">"+
                            "<img class='imgLink' src='"+ajaxManager.baseUrl+"images/restore.png' onclick=\"ajaxManager.restoreBackupIconClicked('"+data[w].filename+"')\">"+
                            "</td></tr>");
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Counting backup items failed!!');                        
            }
        });
    }
    
    this.initializeHomePage = function(){        
        
        var eManager = this;
        
        $.ajax({
            url: "http://servmon.gr/api/info/supported_types",
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data,textStatus,jqXHR ) {  
                var services = data.service;
                var webapps = data.webapp;
                var databases = data.database;
                
                for(var j=0; j<services.length; j++){    
                    // Store service types for future reference
                    eManager.supported_service_types[services[j].codename] = {
                        codename: services[j].codename,
                        title:    services[j].title,
                        default_port: services[j].default_port,
                        image: services[j].image
                    };
                    // Initialize modals
                    if(j == 0){
                        $('#addServiceDialog select[name="stype"]').append("<option selected='selected'>"+services[j].codename+"</option>");
                    } else {
                        $('#addServiceDialog select[name="stype"]').append("<option>"+services[j].codename+"</option>");
                    } 
                    selectedOption = $('#addServiceDialog select[name="stype"] option:selected').text();
                    $('#addServiceDialog input[name="port"]').val(ajaxManager.supported_service_types[selectedOption].default_port);
                    
                    $('#serviceInfoDialog select[name="stype"]').append("<option>"+services[j].codename+"</option>");
                }
                for(var i=0; i<webapps.length; i++){                    
                    // Store webapp types for future reference
                    eManager.supported_webapp_types[webapps[i].codename] = {
                        codename: webapps[i].codename,
                        title:    webapps[i].title,
                        image: webapps[i].image
                    };
                    // Initialize some modals
                    if(i == 0){
                        $('#addWebappDialog select[name="language"]').append("<option selected='selected'>"+webapps[i].codename+"</option>");
                    } else {
                        $('#addWebappDialog select[name="language"]').append("<option>"+webapps[i].codename+"</option>");
                    } 
                    $('#webappInfoDialog select[name="language"]').append("<option>"+webapps[i].codename+"</option>");
                }
                for(var i=0; i<databases.length; i++){        
                    // Store database types for future reference
                    eManager.supported_database_types[databases[i].codename] = {
                        codename: databases[i].codename,
                        title:    databases[i].title,
                        image: databases[i].image
                    };
                    // Initialize some modals
                    if(i == 0){
                        $('#addDatabaseDialog select[name="type"]').append("<option selected='selected'>"+databases[i].codename+"</option>");
                    } else {
                        $('#addDatabaseDialog select[name="type"]').append("<option>"+databases[i].codename+"</option>");
                    }  
                    $('#databaseInfoDialog select[name="type"]').append("<option>"+databases[i].codename+"</option>");
                }
            },
            error: ajaxFailure
        });
                
        // Load the tree data
        $.ajax({
            url: this.baseUrl+'api/domains',
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data ) {
                guiManager.reloadTree(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Tree loading failed!');                        
            }
        });
        
        $.ajax({
            url: this.baseUrl+"api/delegations?mode=my_servers",
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data ) {                                
                for(var k=0; k<data.length; k++){
                    var server = data[k];
                    if(server.status == 'on'){
                        statusImg = 'green.png';
                    } else {
                        statusImg = 'red.png';
                    }
                    $('#server-list-table-standalone').append("<tr onclick='ajaxManager.serverSelected(\""+server.id+"\")' id='server-"+server.id+"' class='server-list-row'><td><img src='images/"+statusImg+"'></td><td>"+server.hostname+"</td><td>"+server.full_name+"</td><td>"+server.ip+"</td><td>"+server.os+"</td><td class='services'></td></tr>");
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Delegated servers loading failed!!');                        
            }
        });
    }
    
    this.initializeConfigurationPage = function(){
        $.ajax({
            url: ajaxManager.baseUrl+"api/info/settings",
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data ) {                                
                if(data.monitoring_status == 1){
                    $('#monitoringButton').prop('checked',true);
                } 
                $("#changeStatusForm select[name='monitoring_period'] option[value='"+data.monitoring_period+"']").attr('selected','selected');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Settings loading failed!!');                        
            }
        });

        var mTable = $('#monitor-items-table');

        $.ajax({
            url: "http://servmon.gr/api/monitor/items",
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data ) {                                
                for(var domainName in data) {
                    var domainData = data[domainName];
                    mTable.append("<tr><td style='padding-left: 20px'><img class='conf-img' src='/images/domain.png' title='domain'>"+domainName+"</td></td></tr>");
                    for(var j=0; j<domainData.length; j++){
                        var serverData = domainData[j];
                        if(serverData.watch == '1')
                            selectedText = "checked ";
                        else
                            selectedText = "";
                        mTable.append("<tr><td style='padding-left: 50px'><img class='conf-img' src='/images/server.png' title='server'>"+serverData.hostname+"</td><td><input class='checkboxType' type='checkbox' name='server--"+serverData.id+"' "+selectedText+"></td></tr>");
                        for(var k=0; k<serverData.services.length; k++){
                            var serviceData = serverData.services[k];
                            if(serviceData.watch == 1)
                                selectedText = "checked ";
                            else
                                selectedText = "";
                            mTable.append("<tr><td style='padding-left: 80px'><img class='conf-img' src='/images/gear.png' title='service'>"+serviceData.stype+"</td><td><input class='checkboxType' type='checkbox' name='service--"+serviceData.id+"' "+selectedText+"></td></tr>");
                        }
                        for(var i=0; i<serverData.webapps.length; i++){
                            var webappData = serverData.webapps[i];
                            if(webappData.watch == 1)
                                selectedText = "checked ";
                            else
                                selectedText = "";
                            mTable.append("<tr><td style='padding-left: 80px'><img class='conf-img' src='/images/webapp.png' title='webapp'>"+webappData.url+"</td><td><input class='checkboxType' type='checkbox' name='webapp--"+webappData.id+"' "+selectedText+"></td></tr>");
                        }
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Information loading failed!!');                        
            }
        });
    }
    
    this.initializeUserManagementPage = function(){
        $('#loading-image').center().show();
        $.ajax({
            url: ajaxManager.baseUrl+"api/users/",
            type: 'GET',
            success: function( data,textStatus,jqXHR ) {                
                for(var j=0; j<data.length; j++){
                    var user = data[j];
                    var light = "" , status = "";
                    if(user.superuser == 1){
                        superuserIcon = "<img class='imgLink' src='"+ajaxManager.baseUrl+"images/super.png' onmouseover='this.src=\""+ajaxManager.baseUrl+"images/edit.png\"' onmouseout='this.src=\""+ajaxManager.baseUrl+"images/super.png\"' onclick='ajaxManager.superuserIconClicked("+user.id+",0)'>";
                    } else {
                        superuserIcon = "<img class='imgLink' src='"+ajaxManager.baseUrl+"images/super_black.png' onmouseover='this.src=\""+ajaxManager.baseUrl+"images/edit.png\"' onmouseout='this.src=\""+ajaxManager.baseUrl+"images/super_black.png\"' onclick='ajaxManager.superuserIconClicked("+user.id+",1)'>"
                    }
                    if(user.activated == 1){
                        light = "<span class='glyphicon glyphicon-ok-sign' style='color:green'></span>"
                    } else {
                        light = "<span class='glyphicon glyphicon-minus-sign' style='color:red'></span>";
                    }
                    if(user.activated == 1){
                        status = "<div class='linkStyle' onclick='ajaxManager.disableUser("+user.id+")'> Disable </div>";
                    } else {
                        status = "<div class='linkStyle' onclick='ajaxManager.enableUser("+user.id+")'> Enable </div>";
                    }

                    $('#usersTable tbody').append("<tr id='user"+user.id+"_row'>"+
                        "<td>"+superuserIcon+"</td>"+
                        "<td>"+user.firstname+"</td>"+
                        "<td>"+user.lastname+"</td>"+
                        "<td>"+user.email+"</td>"+
                        "<td>"+light+"</td>"+
                        "<td>"+user.created_at+"</td>"+
                        "<td>"+user.last_login+"</td>"+
                        "<td>"+
                        "<a href='"+ajaxManager.baseUrl+"user_management/"+user.id+"'> View </a>"+
                        status+
                        "<div class='linkStyle' onclick='ajaxManager.deleteUserIconClicked("+user.id+")'> Delete </div>"+
                        "</td>"+
                        "</tr>"
                    );
                }
                $('#loading-image').hide();
            },
            error: ajaxFailure
        });
    }
    
    this.initializeUserProfilePage = function(userId){
        $('#loading-image').center().show();
        $.ajax({
            url: ajaxManager.baseUrl+"api/users/"+userId,
            type: 'GET',
            success: function( data,textStatus,jqXHR ) {                
                $('#user_profile_form input[name="email"]').val(data.email);
                $('#user_profile_form input[name="firstname"]').val(data.firstname);
                $('#user_profile_form input[name="lastname"]').val(data.lastname);
                $('#user_profile_form input[name="registration_date"]').val(data.registration_date);
                $('#user_profile_form input[name="last_login"]').val(data.last_login);
                if(data.activated == 1){
                    $('#user_status_div').append("<span class='btn btn-sm btn-success'>Enabled</span>");
                } else {
                    $('#user_status_div').append("<span class='btn btn-sm btn-danger'>Disabled</span>");
                }
                if(data.superuser == 1){
                    $('#user_status_div').append('<img class="imgLink" style="float:right" src="'+ajaxManager.baseUrl+'images/super.png" title="Super user">');
                } else {
                    $('#user_status_div').append('<img class="imgLink" style="float:right" src="'+ajaxManager.baseUrl+'images/super_black.png" title="Normal User">');
                }
                $('#loading-image').hide();
            }, 
            error: ajaxFailure
        });
    }
    
    this.initializeMyProfilePage = function(){
        $('#loading-image').center().show();
        $.ajax({
            url: ajaxManager.baseUrl+"api/info/myprofile",
            type: 'GET',
            success: function( data,textStatus,jqXHR ) {                
                $('#user_profile_form input[name="email"]').val(data.email);
                $('#user_profile_form input[name="firstname"]').val(data.firstname);
                $('#user_profile_form input[name="lastname"]').val(data.lastname);
                $('#user_profile_form input[name="registration_date"]').val(data.registration_date);
                $('#user_profile_form input[name="last_login"]').val(data.last_login);
                if(data.activated == 1){
                    $('#user_status_div').append("<span class='btn btn-sm btn-success'>Enabled</span>");
                } else {
                    $('#user_status_div').append("<span class='btn btn-sm btn-danger'>Disabled</span>");
                }
                if(data.superuser == 1){
                    $('#user_status_div').append('<img class="imgLink" style="float:right" src="'+ajaxManager.baseUrl+'images/super.png" title="Super user">');
                } else {
                    $('#user_status_div').append('<img class="imgLink" style="float:right" src="'+ajaxManager.baseUrl+'images/super_black.png" title="Normal User">');
                }
                $('#loading-image').hide();
            }, 
            error: ajaxFailure
        });
    }
    
    this.initializeDelegationsPage = function(){
        var mTable = $('#delegate-items-table');
        var serverDelegations;
        var domainDelegations;

        $.ajax({
            url: ajaxManager.baseUrl+"api/users?mode=basic",
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data ) {                                
                for(var key in data){
                    $('#new_delegation_form select[name="duser"]').append("<option value='"+key+"'>"+data[key]+"</option>");
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('User list loading failed!!');                        
            }
        });

        $.ajax({
            url: ajaxManager.baseUrl+"api/delegations",
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data ) {                                
                domainDelegations = data.domain_delegations;
                serverDelegations = data.server_delegations;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Delegation information loading failed!!');                        
            }
        });

        $.ajax({
            url: ajaxManager.baseUrl+"api/domains?mode=with_servers",
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function( data ) {                                
                for(var domainName in data) {
                    var dashedDomain = domainName.replace(/\./g , "-");
                    var domainData = data[domainName];
                    var delegatedNames = "";
                    if(domainDelegations[domainName] != null){
                        var delegations = domainDelegations[domainName];
                        for(var k=0; k<delegations.length; k++){
                            delegatedNames = delegatedNames+ "<div id='domainDelegation"+delegations[k].id+"' class='alert alert-warning' role='alert' style='padding: 5px; margin-bottom: 0px; display: inline-block'>  <button type='button' class='close' aria-label='Revoke' style='margin-left: 10px' onclick='ajaxManager.revokeDelegationIconClicked(\"domain\","+delegations[k].id+")'><span aria-hidden='true'>&times;</span></button>"+delegations[k].firstname+" "+delegations[k].lastname+"</div>";
                        }
                    }

                    mTable.append("<tr id='domainLine-"+dashedDomain+"'><td style='padding-left: "+((parseInt(domainData.depth)+1)*25)+"px'><img class='conf-img' src='/images/domain.png' title='domain'>"+domainName+"</td><td>"+delegatedNames+"</td><td><img src='/images/add_user.png' class='imgLink' title='New delegation' onclick='ajaxManager.addDelegationIconClicked(\"domain\",\""+domainName+"\")'></td></tr>");
                    for(var j=0; j<domainData.servers.length; j++){
                        var serverData = domainData.servers[j];     
                        delegatedNames = "";
                        if(serverDelegations[serverData.id] != null){
                            delegations = serverDelegations[serverData.id];
                            for(var k=0; k<delegations.length; k++){
                                delegatedNames = delegatedNames+ "<div id='serverDelegation"+delegations[k].id+"' class='alert alert-warning' role='alert' style='padding: 5px; margin-bottom: 0px; display: inline-block'>  <button type='button' class='close' aria-label='Revoke' style='margin-left: 10px' onclick='ajaxManager.revokeDelegationIconClicked(\"server\","+delegations[k].id+")'><span aria-hidden='true'>&times;</span></button>"+delegations[k].firstname+" "+delegations[k].lastname+"</div>";
                            }
                        }                    
                        mTable.append("<tr id='serverLine"+serverData.id+"'><td style='padding-left: "+((parseInt(domainData.depth)+2)*25)+"px'><img class='conf-img' src='/images/server.png' title='server'>"+serverData.hostname+"</td><td>"+delegatedNames+"</td><td><img src='/images/add_user.png' class='imgLink' title='New delegation' onclick='ajaxManager.addDelegationIconClicked(\"server\","+serverData.id+")'></td></tr>");                    
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Domain and server information loading failed!!');                        
            }
        });
    }
    
    // User actions not related to modal windows
    
    this.login = function(){
        $('#loading-image').center().show();
        var postData = { 
            inputEmail: $('#inputEmail').val(),
            inputPassword: $('#inputPassword').val()
        };

        $.ajax({
            url: window.location.protocol + "//" + window.location.host+"/api/login",
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data ) {                
                window.location = window.location.protocol + "//" + window.location.host+"/home";  
            },
            error: ajaxFailure
        });
    }
    
    this.logout = function(){
        $('#loading-image').center().show();
        $.ajax({
            url: window.location.protocol + "//" + window.location.host+"/api/logout",
            type: 'POST',
            dataType: 'json',
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data ) {                
                window.location = window.location.protocol + "//" + window.location.host;
            },
            error: ajaxFailure
        });
    }
    
    this.domainSelected = function(fullDomainName){
        $('#loading-image').center().show();
        this.selectedDomain = fullDomainName;
        this.loadDomainServers(fullDomainName);
        guiManager.manageDynamicIcons('domainSelected');
        $('#loading-image').hide();
    }
    
    this.domainUnselected = function(){
        this.selectedDomain = '';
        guiManager.manageDynamicIcons('domainUnselected');
    }
    
    this.serverSelected = function(serverId){
        $('#loading-image').center().show();
        guiManager.indicateServerLine(this.selectedServer,serverId);        
        this.selectedServer = serverId;                
        this.loadServerInfo(serverId);
        $('#loading-image').hide();
    }
    
    this.enableUser = function(userId){
        $('#loading-image').center().show();
        $.ajax({
            url : this.baseUrl+'api/users/'+userId+"/enable",
            type: "PUT",            
            dataType : 'json',
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success:function(data, textStatus, jqXHR){
                localStorage.setItem("success_toastr",jqXHR.statusText);
                window.location.reload();
            },
            error: ajaxFailure
        });
    }
    
    this.disableUser = function(userId){
        $('#loading-image').center().show();
        $.ajax({
            url : this.baseUrl+'api/users/'+userId+"/disable",
            type: "PUT",            
            dataType : 'json',
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success:function(data, textStatus, jqXHR){
                localStorage.setItem("success_toastr",jqXHR.statusText);
                window.location.reload();
            },
            error: ajaxFailure
        });
    }
    
    this.loadWebappList = function(){
        
        var eManager = this;
        $.ajax({
            url: this.baseUrl+"api/webapps",
            type: 'GET',
            success: function( data,textStatus,jqXHR ) {                                
                if (data.length > 0 ){
                    $('#databaseInfoDialog select[name="related_webapp"]').append("<option value='-1'>Noone</option>");
                    for(var c=0; c<data.length; c++){                        
                        $('#databaseInfoDialog select[name="related_webapp"]').append("<option value='"+data[c].id+"'>"+data[c].url+"</option>");
                        //eManager.webappList[data[c].id] = data[c].url; 
                    }
                } else {
                    //eManager.webappList = {};
                    $('#databaseInfoDialog select[name="related_webapp"]').append("<option value='-1'>There is no web app under this domain!</option>");                    
                }                
            },
            error: ajaxFailure
        });
    }    
    
    this.loadMyServersList = function(){
        
        var eManager = this;
        $.ajax({
            url: this.baseUrl+"api/servers?mode=mine",
            type: 'GET',
            success: function( data,textStatus,jqXHR ) {                                
                $('#webappInfoDialog select[name="server"]').empty();
                if (data.length > 0 ){                    
                    //$('#webappInfoDialog select[name="server"]').append("<option value='-1'>Noone</option>");
                    //$('#serviceInfoDialog select[name="server"]').append("<option value='-1'>Noone</option>");
                    for(var c=0; c<data.length; c++){                        
                        $('#webappInfoDialog select[name="server"]').append("<option value='"+data[c].id+"'>"+data[c].hostname+'.'+data[c].domain_name+"</option>");
                        $('#serviceInfoDialog select[name="server"]').append("<option value='"+data[c].id+"'>"+data[c].hostname+'.'+data[c].domain_name+"</option>");                        
                        //eManager.webappList[data[c].id] = data[c].url; 
                    }
                } else {
                    //eManager.webappList = {};
                    $('#webappInfoDialog select[name="server"]').append("<option value='-1'>There is no web app under this domain!</option>");                    
                }                
            },
            error: ajaxFailure
        });
    }
    
    this.installationButtonClicked = function(){
        $('#loading-image').center().show();
        /* get some values from elements on the page: */
        var postData = { 
            url: $('#installation_form input[name="url"]').val(), 
            server: $('#installation_form input[name="server"]').val(), 
            dbname: $('#installation_form input[name="dbname"]').val(), 
            dbuser: $('#installation_form input[name="dbuser"]').val(),
            dbpwd: $('#installation_form input[name="dbpwd"]').val(),           
        };
        
        $.ajax({
            url : this.baseUrl+"install",
            type: "POST",            
            dataType : 'json',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success:function(data, textStatus, jqXHR){
                localStorage.setItem("success_toastr",jqXHR.statusText);
                window.location = window.location.protocol + "//" + window.location.host;
            },
            error: ajaxFailure
        });
    }
    
    // Modal window-related actions
    
    this.addDelegationIconClicked = function(itemType,itemId){
        $('#new_delegation_form input[name="dtype"]').val(itemType);
        $('#new_delegation_form input[name="ditem"]').val(itemId);
        $('#new_delegation_form input[name="duser"]').val();
        $('#newDelegationDialog').modal();
    }
    
    this.revokeDelegationIconClicked = function(itemType,itemId){
        $('#loading-image').center().show();
        var targetUrl;
        switch(itemType){            
            case 'domain':
                targetUrl = this.baseUrl+"api/delegations/domain"+"/"+itemId+"?_token="+$('#page_token').val();
                break;
            case 'server':
                targetUrl = this.baseUrl+"api/delegations/server"+"/"+itemId+"?_token="+$('#page_token').val();
                break;
        }

        $.ajax({
            url: targetUrl,
            type: 'DELETE',
            dataType: 'json',
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                switch(itemType){            
                    case 'domain':
                        $('#domainDelegation'+itemId).remove();
                        toastr.success(jqXHR.statusText);
                        break;
                    case 'server':
                        $('#serverDelegation'+itemId).remove();
                        toastr.success(jqXHR.statusText);
                        break;
                }
                $('#loading-image').hide();
            },
            error: ajaxFailure
        });
    }
    
    this.addDomainIconClicked = function(){
        $('#add_domain_form input[name="node_name"]').val('');
        $('#add_domain_form input[name="parent_domain"]').val(this.selectedDomain);
        $('#add_domain_form input[name="fake_domain"]').prop('checked',false);
        $('#addDomainDialog').modal();
    }
        
    this.addWebappIconClicked = function(){
        $serverName = $('#server-'+this.selectedServer+' td:nth-child(2)').text();
        $('#addWebappDialog input[name="server"]').val($serverName);
        $('#addWebappDialog').modal();
    }
        
    this.addServerIconClicked = function(){
        $('#add_server_form input[name="hostname"]').val('');
        $('#add_server_form input[name="ip"]').val('');
        $('#add_server_form input[name="os"]').val('');
        $('#add_server_form input[name="domain"]').val(this.selectedDomain);
        $('#addServerDialog').modal();
    }    
    
    this.addServiceIconClicked = function(){
        $serverName = $('#server-'+this.selectedServer+' td:nth-child(2)').text();
        $('#addServiceDialog input[name="server"]').val($serverName);
        $('#addServiceDialog').modal(); 
    }
        
    this.addDatabaseIconClicked = function(){
        $serverName = $('#server-'+this.selectedServer+' td:nth-child(2)').text();
        $('#addDatabaseDialog input[name="server"]').val($serverName);
        $('#addDatabaseDialog').modal(); 
    }        
    
    this.editServerIconClicked = function(){
        var serverLine = $('#server-'+this.selectedServer);
        var hostname = serverLine.find(':nth-child(2)').text();
        var domain_name = serverLine.find(':nth-child(3)').text();        
        $('#edit_server_form input[name="hostname"]').val(hostname);
        $('#edit_server_form input[name="ip"]').val(serverLine.find(':nth-child(4)').text());
        $('#edit_server_form input[name="os"]').val(serverLine.find(':nth-child(5)').text());
        $('#edit_server_form input[name="domain"]').val(domain_name);
        $('#editServerDialog').modal();
    }
   
    this.editServiceIconClicked = function(serviceId){
        $('#loading-image').center().show();
        this.loadMyServersList();        
        $.ajax({
            url: this.baseUrl+"api/services/"+serviceId,
            type: 'GET',
            success: function( data,textStatus,jqXHR ) {                
                $('#serviceInfoDialog input[name="serviceId"]').val(serviceId);                
                $('#serviceInfoDialog select[name="stype"]').val(data.data.stype);
                $('#serviceInfoDialog input[name="port"]').val(data.data.port);
                $('#serviceInfoDialog input[name="version"]').val(data.data.version);
                $('#loading-image').hide();
                $('#serviceInfoDialog').modal();
                $("#serviceInfoDialog select[name='server']").prop('disabled',false);
                $("#serviceInfoDialog select[name='server'] option[value='"+data.data.server+"']").prop('selected',true);
                $("#serviceInfoDialog select[name='server']").prop('disabled',true);
            },
            error: ajaxFailure
        });
    }
  
    this.editWebappIconClicked = function(webappId){   
        $('#loading-image').center().show();
        this.loadMyServersList();        
        $.ajax({
            url: this.baseUrl+"api/webapps/"+webappId,
            type: 'GET',
            success: function( data,textStatus,jqXHR ) {                
                $('#webappInfoDialog input[name="appId"]').val(webappId);
                $('#webappInfoDialog input[name="url"]').val(data.data.url);
                $('#webappInfoDialog select[name="language"]').val(data.data.language);
                $('#webappInfoDialog input[name="developer"]').val(data.data.developer);
                $('#webappInfoDialog input[name="contact"]').val(data.data.contact);
                $('#webappInfoDialog input[name="origServer"]').val(data.data.server);
                $('#loading-image').hide();
                $('#webappInfoDialog').modal();
                $("#webappInfoDialog select[name='server'] option[value='"+data.data.server+"']").prop('selected',true);
            },
            error: ajaxFailure
        });
    }
    
    this.editDatabaseIconClicked = function(databaseId){
        $('#loading-image').center().show();
        $('#databaseInfoDialog select[name="related_webapp"]').empty();
        
        var eManager = this;
        
        // We need to load the list every time because someone else may have edited the webapps meanwhile
        this.loadWebappList();
        
        if(this.selectedDomain == ''){ // In case we work with an independently delegated server
            $('#databaseInfoDialog select[name="server"]').append("<option value='"+this.selectedServer+"'>You have not permission to change it!</option>");
            $('#databaseInfoDialog select[name="server"] option[value="'+this.selectedServer+'"]').attr('selected','selected');
        } else {
            // Get list of servers
            $.ajax({
                url: this.baseUrl+"api/domains/"+this.selectedDomain+"/servers",
                type: 'GET',
                async: false,
                success: function( data,textStatus,jqXHR ) {     
                        var dropdown = $('#databaseInfoDialog select[name="server"]');
                        dropdown.empty();
                        for(var c=0; c<data.length; c++){''                        
                            dropdown.append("<option value='"+data[c].id+"'>"+data[c].hostname+'.'+data[c].domain_name+"</option>");
                        }                       
                },
                error: ajaxFailure
            });
        }                
        
        // Get information about the database
        $.ajax({
            url: this.baseUrl+"api/databases/"+databaseId,
            type: 'GET',
            success: function( data,textStatus,jqXHR ) {                
                $('#databaseInfoDialog input[name="databaseId"]').val(databaseId);
                $('#databaseInfoDialog input[name="dbname"]').val(data.data.dbname);
                $('#databaseInfoDialog select[name="server"]').val(data.data.server);
                $('#databaseInfoDialog select[name="type"]').val(data.data.type);                
                $('#databaseInfoDialog input[name="origServer"]').val(data.data.server);
                $('#loading-image').hide();
                $('#databaseInfoDialog').modal();
                $("#databaseInfoDialog select[name='server']").val(ajaxManager.selectedServer);
                $('#databaseInfoDialog select[name="related_webapp"]').val(data.data.related_webapp);
            },
            error: ajaxFailure
        });
    }
       
    this.deleteServerIconClicked = function(){
        if(this.selectedServer != ""){
            $("#server_span_name").html($('#server-'+this.selectedServer+" td:nth-child(2)").text());
            $("#deleteServerForm input[name='delete_server_id']").val(this.selectedServer);
            $('#deleteServerDialog').modal('show');
        }  
    }
    
    this.deleteDomainIconClicked = function(){
        if(this.selectedDomain != ""){
            $("#domain_span_fullname").html(""+this.selectedDomain);
            $("#deleteDomainForm input[name='delete_domain_name']").val(this.selectedDomain);
            $('#deleteDomainDialog').modal('show');
        }  
    }
    
    this.createBackupIconclicked = function(){
        
        $('#loading-image').center().show();
        
        $.ajax({
            url : this.baseUrl+"api/backup",
            type: "POST",            
            dataType : 'json',
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success:function(data, textStatus, jqXHR){
                localStorage.setItem("success_toastr",jqXHR.statusText);
                window.location.reload();
            },
            error: ajaxFailure
        });
        
    }
    
    this.deleteBackupIconClicked = function(filename){
        $("#deleteBackupForm input[name='delete_backup_filename']").val(filename);
        $('#deleteBackupDialog').modal('show');
    }
    
    this.restoreBackupIconClicked = function(filename){
        $("#restoreBackupForm input[name='restore_backup_filename']").val(filename);
        $('#restoreBackupDialog').modal('show');
    }
   
    this.updateConfigurationButtonClicked = function(){
        $('#loading-image').center().show();
        var checkedItems = [];
        $('.checkboxType').each(function (index, value) { 
            if($(this).is(':checked')){
                checkedItems.push($(this).attr('name'));
            }                       
        });

        var postData = { 
            items: checkedItems
        };
        
        $.ajax({
            url: this.baseUrl+"api/monitor/items",
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {                
                localStorage.setItem("success_toastr",jqXHR.statusText);
                window.location.reload();           
            },
            error: ajaxFailure
        });
        
    }
   
    this.monitoringStatusButtonClicked = function(){
        $('#loading-image').center().show();
        var new_status = 1;
        if($('#monitoringButton').is(":checked")){
            new_status = 1;
        } else {
            new_status = 0;
        }
        var postData = { 
            config: {
                monitoring_status: new_status, 
                monitoring_period: $('#changeStatusForm select[name="monitoring_period"]').val(), 
            }
        };

        $.ajax({
            url: this.baseUrl+"api/monitor/status",
            type: 'PUT',
            dataType: 'json',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {                
                localStorage.setItem("success_toastr",jqXHR.statusText);
                window.location.reload();           
            },
            error: ajaxFailure
        });
    }
   
    this.selectWebappIconClicked = function(){
        $('#webapp-list-box').empty();
        $('#loading-image').show();
        
        $.ajax({
            url: this.baseUrl+'api/webapps',
            type: 'GET',
            async: false,
            success: function( data,textStatus,jqXHR ) {
                for(var j=0; j < data.length; j++){
                    $('#webapp-list-box').append('<li>'+data[j].url+'</li>');
                }
                add_webapp_selection_listener();
                $('#loading-image').hide();                
            },
            error: ajaxFailure
        });
        
        $('#webapp-list-div').show();
    }
    
    this.deleteUserIconClicked = function(userId){
        var firstname = $('#user'+userId+"_row td:nth-child(1)").text();
        var lastname = $('#user'+userId+"_row td:nth-child(2)").text();
        var email = $('#user'+userId+"_row td:nth-child(3)").text();
        $('input[name="delete_user_id"]').val(""+userId);
        $('#user_email').text(email);
        $('#user_fullname').text(firstname+" "+lastname);
        $('#deleteUserDialog').modal('show');
    }
   
    this.superuserIconClicked = function(userId,enable){
        var email = $('#user'+userId+"_row td:nth-child(4)").text();
        $('#superuserForm input[name="user_id"]').val(userId);
        if(enable == 1){
            $('#change_status_message').html('Do you really want to give admin privileges to this user ('+email+') ?');            
            $('#superuserForm input[name="new_superuser_status"]').val('1');
        } else {
            $('#change_status_message').html('Do you really want to revoke admin privileges from this user ('+email+') ?');
            $('#superuserForm input[name="new_superuser_status"]').val('0');
        }        
        $('#superuserDialog').modal('show');
    }
   
    // Modal submisions - AJAX
    
    this.addUserModalSubmit = function(){
        
        $('#loading-image').center().show();
        /* get some values from elements on the page: */
        var postData = { 
            users: [{
                firstname: $('#addUserForm input[name="firstname"]').val(), 
                lastname: $('#addUserForm input[name="lastname"]').val(), 
                email: $('#addUserForm input[name="email"]').val(),
                password: $('#addUserForm input[name="password"]').val(),
                verify_password: $('#addUserForm input[name="verify_password"]').val()
            }]
        };
        
        $.ajax({
            url : this.baseUrl+"api/users",
            type: "POST",            
            dataType : 'json',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success:function(data, textStatus, jqXHR){
                localStorage.setItem("success_toastr",jqXHR.statusText);
                window.location.reload();
            },
            error: ajaxFailure
        });
    }
    
    this.deleteUserModalSubmit = function(){
        $('#loading-image').center().show();     
        
        var userId = $('#deleteUserForm input[name="delete_user_id"]').val();
        var eManager = this;
        
        $.ajax({
            url: this.baseUrl+"api/users/"+userId,
            type: 'DELETE',
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                eManager.selectedServer = '';
                $('#loading-image').hide();
                $('#deleteUserDialog').modal('hide');                                
                localStorage.setItem("success_toastr",jqXHR.statusText);
                window.location.reload();                                               
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#deleteUserDialog').modal('hide');
                $('#loading-image').hide();
                response = JSON.parse(jqXHR.responseText);
                var errorMessage = jqXHR.statusText+" ";
                for(var j=0; j < response.errors.length; j++){
                    errorItem = response.errors[j];
                    errorMessage = errorMessage+"<strong>"+errorItem.field+"</strong>: "+errorItem.message+"<br>";                    
                }
                toastr.error(errorMessage,{timeOut: 5000});  
            }
        });
    }
    
    this.addDomainModalSubmit = function(){
        $('#loading-image').center().show();
        var fake = 0;
        if($('#add_domain_form input[name="fake_domain"]').is(':checked')){
            fake = 1;
        } else {
            fake = 0;
        }
        var postData = { 
            domains: [{
                node_name: $('#add_domain_form input[name="node_name"]').val(), 
                parent_domain: $('#add_domain_form input[name="parent_domain"]').val(),
                fake_domain: fake
            }]
        };

        $.ajax({
            url: this.baseUrl+"api/domains",
            type: 'POST',            
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#addDomainDialog').modal('hide');
                window.location.reload();
            },
            error: ajaxFailure
        });
        
    }
   
    this.addServerModalSubmit = function(){
        
        $('#loading-image').center().show();
        var postData = { 
            servers: [{
                domain: $('#add_server_form input[name="domain"]').val(), 
                hostname: $('#add_server_form input[name="hostname"]').val(), 
                ip: $('#add_server_form input[name="ip"]').val(), 
                os: $('#add_server_form input[name="os"]').val(),
            }]
        };

        $.ajax({
            url: this.baseUrl+"api/servers",
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#addServerDialog').modal('hide');
                window.location.reload();
            },
            error: ajaxFailure
        });
    }
   
    this.addDatabaseModalSubmit = function(){
        
        $('#loading-image').center().show();
        var databaseInfo = {
            server: this.selectedServer,
            dbname: $('#addDatabaseDialog input[name="dbname"]').val(),
            type: $('#addDatabaseDialog select[name="type"]').val(),
        };
        var webapp = $('#addDatabaseDialog input[name="related_webapp"]').val();
        if(webapp.length > 0){
            databaseInfo['related_webapp'] = webapp;
        }
        var postData = { 
            databases: [databaseInfo]
        };
        
        var eManager = this;
        $.ajax({
            url: this.baseUrl+"api/databases",
            type: 'POST',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#loading-image').hide();
                $('#addDatabaseDialog').modal('hide');
                toastr.success(jqXHR.statusText,{timeOut: 5000}); 
                guiManager.addDatabase(data[0]); 
            },
            error: ajaxFailure
        });
        
    }
    
    this.addWebappModalSubmit = function(){
        
        $('#loading-image').center().show();
        var postData = { 
            webapps: [{
                server: this.selectedServer,
                url: $('#addWebappDialog input[name="url"]').val(),
                language: $('#addWebappDialog select[name="language"]').val(),
                developer: $('#addWebappDialog input[name="developer"]').val(),
                contact: $('#addWebappDialog input[name="contact"]').val()                
            }]
        };
        
        var eManager = this;
        
        $.ajax({
            url: this.baseUrl+"/api/webapps",
            type: 'POST',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                toastr.success(jqXHR.statusText,{timeOut: 5000});                                             
                eManager.loadServerInfo();
                $('#loading-image').hide();
                $('#addWebappDialog').modal('hide');
            },
            error: ajaxFailure
        });
        
    }
    
    this.addServiceModalSubmit = function(){
        
        $('#loading-image').center().show();
        var postData = { 
            services: [{
                server: this.selectedServer,
                stype: $('#addServiceDialog select[name="stype"]').val(),
                port: $('#addServiceDialog input[name="port"]').val(),
                version: $('#addServiceDialog input[name="version"]').val()   
            }]
        };
        
        var eManager = this;
        
        $.ajax({
            url: this.baseUrl+"api/services",
            type: 'POST',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#loading-image').hide();
                $('#addServiceDialog').modal('hide');
                toastr.success(jqXHR.statusText,{timeOut: 5000}); 
                guiManager.addService(data[0]);  
            },
            error: ajaxFailure
        });
        
    }
    
    this.editServerModalSubmit = function(){
        
        $('#loading-image').center().show();
        var postData = { 
            servers: [{
                serverId: this.selectedServer, 
                hostname: $('#edit_server_form input[name="hostname"]').val(), 
                ip: $('#edit_server_form input[name="ip"]').val(), 
                os: $('#edit_server_form input[name="os"]').val(),
            }]           
        };

        $.ajax({
            url: this.baseUrl+"api/servers",
            type: 'PUT',
            dataType: 'json',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#editServerDialog').modal('hide');
                // Update server rows
                for(var k=0; k < data.length; k++){
                    $('#server-'+data[k].id+" td:nth-child(2)").text(data[k].hostname);
                    $('#server-'+data[k].id+" td:nth-child(4)").text(data[k].ip);
                    $('#server-'+data[k].id+" td:nth-child(5)").text(data[k].os);
                };
                $('#loading-image').hide();
                toastr.success('Server info updated!')
            },
            error: ajaxFailure
        });
    }
    
    this.editServiceModalSubmit = function(){
        $('#loading-image').center().show();
        var postData = { 
            services: [{
                id: $('#serviceInfoDialog input[name="serviceId"]').val(),
                stype: $('#serviceInfoDialog select[name="stype"]').val(),
                port: $('#serviceInfoDialog input[name="port"]').val(),
                version: $('#serviceInfoDialog input[name="version"]').val()
            }]
        };
        
        var eManager = this;
        $.ajax({
            url: this.baseUrl+"api/services",
            type: 'PUT',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#loading-image').hide();
                $('#serviceInfoDialog').modal('hide');  
                guiManager.updateService(data[0]);
                toastr.success(jqXHR.statusText,{timeOut: 5000}); 
            },
            error: ajaxFailure
        });
    }
    
    this.editWebappModalSubmit = function(){
        
        $('#loading-image').center().show();
                       
        var postData = {
            webapps:[{
                id: $('#webappInfoDialog input[name="appId"]').val(),
                url: $('#webappInfoDialog input[name="url"]').val(),
                language: $('#webappInfoDialog select[name="language"]').val(),
                developer: $('#webappInfoDialog input[name="developer"]').val(),
                contact: $('#webappInfoDialog input[name="contact"]').val(),
                server: $('#webappInfoDialog select[name="server"]').val(),
            }]            
        };
        
        var eManager = this; 
        $.ajax({
            url: this.baseUrl+"api/webapps",
            type: 'PUT',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#loading-image').hide();
                $('#webappInfoDialog').modal('hide');       
                var origServer = $('#webappInfoDialog input[name="origServer"]').val();
                var finalServer = $('#webappInfoDialog select[name="server"]').val();
                if(origServer != finalServer){
                    $('#webapp-row-'+appId).remove();
                } else {
                    guiManager.updateWebapp(data[0]);
                }
                toastr.success(jqXHR.statusText,{timeOut: 5000}); 
            },
            error: ajaxFailure
        });
        
    }
        
    this.editDatabaseModalSubmit = function(){
        
        $('#loading-image').center().show();
            
        var databaseInfo = {
            id: $('#databaseInfoDialog input[name="databaseId"]').val(),
            server: $('#databaseInfoDialog select[name="server"]').val(),
            dbname: $('#databaseInfoDialog input[name="dbname"]').val(),
            type: $('#databaseInfoDialog select[name="type"]').val(),
        };
        var webappId = $('#databaseInfoDialog select[name="related_webapp"]').val();
        if(webappId > 0){
            databaseInfo['related_webapp'] = webappId;
        }
        var postData = { 
            databases: [databaseInfo]
        };
        
        var eManager = this;
        
        $.ajax({
            url: this.baseUrl+"api/databases",
            type: 'PUT',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#loading-image').hide();
                $('#databaseInfoDialog').modal('hide');     
                var origServer = $('#databaseInfoDialog input[name="origServer"]').val();
                var finalServer = $('#databaseInfoDialog select[name="server"]').val();
                if(origServer != finalServer){
                    $('#db-row-'+databaseInfo.id).remove();
                } else {
                    guiManager.updateDatabase(data[0]);                                    
                }
                toastr.success(jqXHR.statusText,{timeOut: 5000}); 
            },
            error: ajaxFailure
        });
        
    }
    
    this.deleteDomainModalSubmit = function(){
        
        $('#loading-image').center().show();
        
        var domainName = $('#deleteDomainForm input[name="delete_domain_name"]').val();
        
        $.ajax({
            url: this.baseUrl+"api/domains/"+domainName,
            type: 'DELETE',
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#loading-image').hide();
                $('#deleteDomainDialog').modal('hide');                
                window.location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#deleteDomainDialog').modal('hide');
                $('#loading-image').hide();
                response = JSON.parse(jqXHR.responseText);
                var errorMessage = jqXHR.statusText+" ";
                for(var j=0; j < response.errors.length; j++){
                    errorItem = response.errors[j];
                    errorMessage = errorMessage+"<strong>"+errorItem.field+"</strong>: "+errorItem.message+"<br>";                    
                }
                toastr.error(errorMessage,{timeOut: 5000}); ; 
            }
        });
        
    }    
    
    this.deleteDatabaseModalSubmit = function(){
        
        $('#loading-image').center().show();
        
        var databaseId = $('#databaseInfoDialog input[name="databaseId"]').val();
        
        $.ajax({
            url: this.baseUrl+"api/databases/"+databaseId,
            type: 'DELETE',
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#loading-image').hide();
                $('#databaseInfoDialog').modal('hide');                
                $('#db-row-'+databaseId).remove();
            },
            error: ajaxFailure
        });
        
    }
    
    this.deleteServerModalSubmit = function(){
        $('#loading-image').center().show();
        
        var serverId = $('#deleteServerForm input[name="delete_server_id"]').val();
        var eManager = this;
        
        $.ajax({
            url: this.baseUrl+"api/servers/"+serverId,
            type: 'DELETE',
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                eManager.selectedServer = '';
                $('#loading-image').hide();
                $('#deleteServerDialog').modal('hide');                
                $('#server-'+serverId).remove();
                $('#services-list-table tbody').empty();
                $('#webapps-list-table tbody').empty();
                $('#databases-list-table tbody').empty();
                
                if(eManager.selectedServer == serverId){
                    guiManager.manageDynamicIcons('serverUnselected');
                }                
                
                toastr.success(jqXHR.statusText,{timeOut: 5000});
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#deleteServerDialog').modal('hide');
                $('#loading-image').hide();
                response = JSON.parse(jqXHR.responseText);
                var errorMessage = jqXHR.statusText+" ";
                for(var j=0; j < response.errors.length; j++){
                    errorItem = response.errors[j];
                    errorMessage = errorMessage+"<strong>"+errorItem.field+"</strong>: "+errorItem.message+"<br>";                    
                }
                toastr.error(errorMessage,{timeOut: 5000});  
            }
        });
    }
    
    this.deleteServiceModalSubmit = function(){
        $('#loading-image').center().show();
        
        var appId = $('#serviceInfoDialog input[name="serviceId"]').val();
        
        $.ajax({
            url: this.baseUrl+"api/services/"+appId,
            type: 'DELETE',
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#loading-image').hide();
                $('#serviceInfoDialog').modal('hide');                
                $('#service-row-'+appId).remove();
            },
            error: ajaxFailure
        });
    }
    
    this.deleteWebappModalSubmit = function(){
        
        $('#loading-image').center().show();
        
        var appId = $('#webappInfoDialog input[name="appId"]').val();
        
        $.ajax({
            url: this.baseUrl+"api/webapps/"+appId,
            type: 'DELETE',
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#loading-image').hide();
                $('#webappInfoDialog').modal('hide');                
                $('#webapp-row-'+appId).remove();                 
            },
            error: ajaxFailure
        });
        
    }
    
    this.deleteBackupModalSubmit = function(){
        
        $('#loading-image').center().show();
        $.ajax({
            url: this.baseUrl+"api/backup/"+$('#delete_backup_filename').val(),
            type: 'DELETE',
            dataType: 'json',
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                localStorage.setItem("success_toastr",jqXHR.statusText);
                window.location.reload();
            },
            error: ajaxFailure
        });
    }
    
    this.restoreBackupModalSubmit = function(){
        
        $('#loading-image').center().show();
        $.ajax({
            url: this.baseUrl+"api/backup/"+$('#restore_backup_filename').val()+"/restore",
            type: 'POST',
            dataType: 'json',
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function(data,textStatus,jqXHR) {
                localStorage.setItem("success_toastr",jqXHR.statusText);
                window.location.reload(); 
            },
            error: ajaxFailure
        });
    }
    
    this.addDelegationModalSubmit = function(){
        
        $('#loading-image').center().show();
        
        var itemType = $('#new_delegation_form input[name="dtype"]').val();
        var itemId = $('#new_delegation_form input[name="ditem"]').val();

        var postData = { 
            delegations: [{
                dtype: itemType, 
                ditem: itemId, 
                duser: $('#new_delegation_form select[name="duser"]').val(), 
            }]
        };

        $.ajax({
            url: this.baseUrl+"api/delegations",
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify(postData),
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                $('#loading-image').hide();
                $('#newDelegationDialog').modal('hide');
                for(var k=0; k<data.length; k++){                    
                    var fullname = $('#new_delegation_form select[name="duser"] option:selected').text();
                    switch(itemType){            
                        case 'domain':
                            var dashedDomain = itemId.replace(/\./g , "-");
                            $('#domainLine-'+dashedDomain+" td:nth-child(2)").prepend("<div id='domainDelegation"+data[k].id+"' class='alert alert-warning' role='alert' style='padding: 5px; margin-bottom: 0px; display: inline-block'>  <button type='button' class='close' aria-label='Revoke' style='margin-left: 10px' onclick='ajaxManager.revokeDelegationIconClicked(\"domain\","+data[k].id+")'><span aria-hidden='true'>&times;</span></button>"+fullname+"</div>");
                            break;
                        case 'server':
                            $('#serverLine'+itemId+" td:nth-child(2)").prepend("<div id='serverDelegation"+data[k].id+"' class='alert alert-warning' role='alert' style='padding: 5px; margin-bottom: 0px; display: inline-block'>  <button type='button' class='close' aria-label='Revoke' style='margin-left: 10px' onclick='ajaxManager.revokeDelegationIconClicked(\"server\","+data[k].id+")'><span aria-hidden='true'>&times;</span></button>"+fullname+"</div>");                        
                            break;
                    }
                }
                
                toastr.success(jqXHR.statusText);
            },
            error: ajaxFailure
        });
    }
    
    this.superuserModalSubmit = function(){
        $('#loading-image').center().show();     
        
        var userId = $('#superuserForm input[name="user_id"]').val();
        var new_superuser_status = $('#superuserForm input[name="new_superuser_status"]').val();
        if(new_superuser_status == 1){
            targetUrl = this.baseUrl+"api/users/"+userId+"/make_superuser";
        } else {
            targetUrl = this.baseUrl+"api/users/"+userId+"/unmake_superuser";
        }
        var eManager = this;
        
        $.ajax({
            url: targetUrl,
            type: 'PUT',
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function( data,textStatus,jqXHR ) {
                eManager.selectedServer = '';
                $('#loading-image').hide();
                $('#superuserDialog').modal('hide'); 
                if(new_superuser_status == 1){
                    $('#user'+userId+"_row td:nth-child(1) img").attr("src", eManager.baseUrl+'images/super.png');
                } else {
                    $('#user'+userId+"_row td:nth-child(1) img").attr("src", eManager.baseUrl+'images/super_black.png');
                }                
                toastr.success(jqXHR.statusText);                                            
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#superuserDialog').modal('hide');
                $('#loading-image').hide();
                response = JSON.parse(jqXHR.responseText);
                var errorMessage = jqXHR.statusText+" ";
                for(var j=0; j < response.errors.length; j++){
                    errorItem = response.errors[j];
                    errorMessage = errorMessage+"<strong>"+errorItem.field+"</strong>: "+errorItem.message+"<br>";                    
                }
                toastr.error(errorMessage,{timeOut: 5000});  
            }
        });
    }
    
}

    
    var guiManager = new GuiManagerClass();
    var ajaxManager = new AjaxManagerClass();
    

   
     