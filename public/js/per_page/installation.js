
function AjaxManagerClass(){
    
    this.baseUrl = window.location.protocol + "//" + window.location.host + "/";  
    
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
            url : this.baseUrl+"api/system/install",
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
    
}

var ajaxManager = new AjaxManagerClass();