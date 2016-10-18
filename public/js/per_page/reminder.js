function AjaxManagerClass(){
    
    this.baseUrl = window.location.protocol + "//" + window.location.host + "/";  
    
    this.requestPasswordReset = function(){
        
        $('#loading-image').center().show();
        
        var email = $('#password_request_form input[name="email"]').val();       
        var postData = { 
            email: $('#password_request_form input[name="email"]').val(),
            captcha: $('#password_request_form input[name="captcha"]').val()
        };
        
        $.ajax({
            url: this.baseUrl+"api/auth/request_reset_link",
            type: 'POST',
            data: JSON.stringify(postData),            
            dataType: 'json',
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function(data,textStatus,jqXHR) {                
                window.location = window.location.protocol + "//" + window.location.host + "/reset_link_sent"; 
            },
            error: ajaxFailure
        });
    }
    
    this.setNewPassword = function(code){
        $('#loading-image').center().show();
             
        var postData = { 
            new_password: $('#password_reset_form input[name="new_password"]').val(),
            repeat_password: $('#password_reset_form input[name="repeat_password"]').val()
        };
        
        $.ajax({
            url: this.baseUrl+"api/auth/set_new_password/"+code,
            type: 'POST',
            data: JSON.stringify(postData),            
            dataType: 'json',
            contentType:"application/json; charset=utf-8",
            headers:{'X-CSRF-Token': $('#page_token').val()},
            success: function(data,textStatus,jqXHR) {             
                localStorage.setItem("success_toastr",jqXHR.statusText);
                window.location = window.location.protocol + "//" + window.location.host; 
            },
            error: ajaxFailure
        });
    }
    
}

var ajaxManager = new AjaxManagerClass();