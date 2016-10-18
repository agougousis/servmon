function AjaxManagerClass(){
    
    this.baseUrl = window.location.protocol + "//" + window.location.host + "/";  
    
    
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
                    $('#backup-list-table tbody').append("<tr class='backup-item-row'>"+
                            "<td>"+data[w].when+"</td>"+"<td>"+data[w].size+"</td><td>"+
                            "<img class='imgLink backupDeleteIcon' src='"+ajaxManager.baseUrl+"images/delete.png' onclick=\"ajaxManager.deleteBackupIconClicked('"+data[w].filename+"')\">"+
                            "<img class='imgLink backupRestoreIcon' src='"+ajaxManager.baseUrl+"images/restore.png' onclick=\"ajaxManager.restoreBackupIconClicked('"+data[w].filename+"')\">"+
                            "</td></tr>");
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Counting backup items failed!!');                        
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
    
    this.createBackupIconClicked = function(){
        
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
        $("#backup_filename_to_delete").html(filename);
        $('#deleteBackupDialog').modal('show');
    }
    
    this.restoreBackupIconClicked = function(filename){
        $("#backup_filename_to_restore").html(filename);
        $('#restoreBackupDialog').modal('show');
    }
   
    // Modal submisions - AJAX
    
    this.deleteBackupModalSubmit = function(){
        
        $('#loading-image').center().show();
        $.ajax({
            url: this.baseUrl+"api/backup/"+$("#backup_filename_to_delete").text(),
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
            url: this.baseUrl+"api/backup/"+$('#backup_filename_to_restore').val()+"/restore",
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
    
}

var ajaxManager = new AjaxManagerClass();
    

   
     