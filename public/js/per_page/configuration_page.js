function AjaxManagerClass(){
    
    this.baseUrl = window.location.protocol + "//" + window.location.host + "/";           
    
    // Used for page initialization     
    
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
    
    // User actions not related to modal windows    
    
    this.logout = function(){
        $('#loading-image').center().show();
        $.ajax({
            url: window.location.protocol + "//" + window.location.host+"/api/auth/logout",
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
    
}

var ajaxManager = new AjaxManagerClass();
    

   
     