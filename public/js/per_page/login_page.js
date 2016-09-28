
function AjaxManagerClass(){
    
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
    
}

var ajaxManager = new AjaxManagerClass();