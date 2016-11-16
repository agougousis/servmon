
function AjaxManagerClass(){
    
    this.baseUrl = window.location.protocol + "//" + window.location.host + "/";
    
    // Used for page initialization 
    
    this.initializeUserManagementPage = function(){
        $('#loading-image').center().show();
        $.ajax({
            url: ajaxManager.baseUrl+"api/users",
            type: 'GET',
            success: function( json,textStatus,jqXHR ) {                
                for(var j=0; j<json.data.length; j++){
                    var user = json.data[j];
                    var light = "" , status = "";
                    if(user.superuser == 1){
                        superuserIcon = "<img class='imgLink superuserIcon' src='"+ajaxManager.baseUrl+"images/super.png' onmouseover='this.src=\""+ajaxManager.baseUrl+"images/edit.png\"' onmouseout='this.src=\""+ajaxManager.baseUrl+"images/super.png\"' onclick='ajaxManager.superuserIconClicked("+user.id+",0)'>";
                    } else {
                        superuserIcon = "<img class='imgLink superuserIcon' src='"+ajaxManager.baseUrl+"images/super_black.png' onmouseover='this.src=\""+ajaxManager.baseUrl+"images/edit.png\"' onmouseout='this.src=\""+ajaxManager.baseUrl+"images/super_black.png\"' onclick='ajaxManager.superuserIconClicked("+user.id+",1)'>"
                    }
                    if(user.activated == 1){
                        light = "<span class='glyphicon glyphicon-ok-sign' style='color:green'></span>"
                    } else {
                        light = "<span class='glyphicon glyphicon-minus-sign' style='color:red'></span>";
                    }
                    if(user.activated == 1){
                        status = "<div class='linkStyle disableUserButton' onclick='ajaxManager.disableUser("+user.id+")'> Disable </div>";
                    } else {
                        status = "<div class='linkStyle enableUserButton' onclick='ajaxManager.enableUser("+user.id+")'> Enable </div>";
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
                        "<div class='linkStyle deleteUserButton' onclick='ajaxManager.deleteUserIconClicked("+user.id+")'> Delete </div>"+
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
            success: function( json,textStatus,jqXHR ) {                
                $('#user_profile_form input[name="email"]').val(json.data.email);
                $('#user_profile_form input[name="firstname"]').val(json.data.firstname);
                $('#user_profile_form input[name="lastname"]').val(json.data.lastname);
                $('#user_profile_form input[name="registration_date"]').val(json.data.registration_date);
                $('#user_profile_form input[name="last_login"]').val(json.data.last_login);
                if(json.data.activated == 1){
                    $('#user_status_div').append("<span class='btn btn-sm btn-success'>Enabled</span>");
                } else {
                    $('#user_status_div').append("<span class='btn btn-sm btn-danger'>Disabled</span>");
                }
                if(json.data.superuser == 1){
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
            success: function( json,textStatus,jqXHR ) {                
                $('#user_profile_form input[name="email"]').val(json.data.email);
                $('#user_profile_form input[name="firstname"]').val(json.data.firstname);
                $('#user_profile_form input[name="lastname"]').val(json.data.lastname);
                $('#user_profile_form input[name="registration_date"]').val(json.data.registration_date);
                $('#user_profile_form input[name="last_login"]').val(json.data.last_login);
                if(json.data.activated == 1){
                    $('#user_status_div').append("<span class='btn btn-sm btn-success'>Enabled</span>");
                } else {
                    $('#user_status_div').append("<span class='btn btn-sm btn-danger'>Disabled</span>");
                }
                if(json.data.superuser == 1){
                    $('#user_status_div').append('<img class="imgLink" style="float:right" src="'+ajaxManager.baseUrl+'images/super.png" title="Super user">');
                } else {
                    $('#user_status_div').append('<img class="imgLink" style="float:right" src="'+ajaxManager.baseUrl+'images/super_black.png" title="Normal User">');
                }
                $('#loading-image').hide();
            }, 
            error: ajaxFailure
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
   
    // Modal window-related actions
   
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

                superuserIconOn = "<img class='imgLink superuserIcon' src='"+ajaxManager.baseUrl+"images/super.png' onmouseover='this.src=\""+ajaxManager.baseUrl+"images/edit.png\"' onmouseout='this.src=\""+ajaxManager.baseUrl+"images/super.png\"' onclick='ajaxManager.superuserIconClicked("+userId+",0)'>";
                superuserIconOff = "<img class='imgLink superuserIcon' src='"+ajaxManager.baseUrl+"images/super_black.png' onmouseover='this.src=\""+ajaxManager.baseUrl+"images/edit.png\"' onmouseout='this.src=\""+ajaxManager.baseUrl+"images/super_black.png\"' onclick='ajaxManager.superuserIconClicked("+userId+",1)'>"
                
                eManager.selectedServer = '';
                $('#loading-image').hide();
                $('#superuserDialog').modal('hide'); 
                if(new_superuser_status == 1){
                    $('#user'+userId+"_row td:nth-child(1)").empty();
                    $('#user'+userId+"_row td:nth-child(1)").html(superuserIconOn);                    
                } else {
                    $('#user'+userId+"_row td:nth-child(1)").empty();
                    $('#user'+userId+"_row td:nth-child(1)").html(superuserIconOff);      
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

var ajaxManager = new AjaxManagerClass();
    

   
     