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