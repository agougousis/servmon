function AjaxManagerClass(){
    
    this.baseUrl = window.location.protocol + "//" + window.location.host + "/";  
    
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
   
    // Modal submisions - AJAX
    
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
    
}

var ajaxManager = new AjaxManagerClass();
    

   
     