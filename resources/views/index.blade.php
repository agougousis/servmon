<script type="text/javascript" src="{{ asset('js/per_page/home_page.js') }}"></script>

<div class="row" style="margin:30px">
    <div class="col-md-6">

        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Domains Tree</strong>
                <img src="{{ asset('images/help.png') }}" style="float: right" class="imgLink" data-container="body" data-toggle="popover" data-placement="left" data-content="A domain with gray icon is a domain that you cannot manage. Domains with yellow icons are domains that have been delegated to you." >                        
                <img id="deleteDomainButton" src="{{ asset('images/delete.png') }}" onclick="ajaxManager.deleteDomainIconClicked()"  class="imgLink20" title="Delete the selected domain" style="float: right; margin-right: 10px; display: none">
                <img id="addDomainButton" src="{{ asset('images/plus.png') }}" onclick="ajaxManager.addDomainIconClicked()"  class="imgLink20" title="Add a new domain" style="float: right; margin-right: 10px">
            </div>
            <div class="panel-body">
                <div id="domain_tree"></div>
            </div>
        </div>                

        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Server List</strong><span id="server-list-domain" style="margin-left: 15px; color: gray"></span>                
                <img src="{{ asset('images/help.png') }}" style="float: right" class="imgLink" data-container="body" data-toggle="popover" data-placement="left" data-content="Select a domain to display servers of this domain. Servers that belong to a subdomain of the selected domain will not be displayed." >
                <img id="addServerButton" src="{{ asset('images/plus.png') }}" onclick="ajaxManager.addServerIconClicked()" class="imgLink20" style="float: right; display: none; margin-right: 10px">
            </div>
            <div class="panel-body">
                <table class="table table-condensed table-bordered table-hover" id="server-list-table"></table>
            </div>
        </div>    
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Independently Delegated Servers</strong><span id="server-list-standalone" style="margin-left: 15px; color: gray"></span>    
                <img src="{{ asset('images/help.png') }}" style="float: right" class="imgLink" data-container="body" data-toggle="popover" data-placement="left" data-content="Servers that belong to a domain you cannot manage and have been delegated to you, will be displayed here." >            
            </div>
            <div class="panel-body">
                <table class="table table-condensed table-bordered table-hover" id="server-list-table-standalone">
                    <!-- It will be filled up through AJAX -->
                </table>
            </div>
        </div>
        
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading" id="server-info-title">                        
                <div id="server-info-title-text">
                    <img  src="{{ asset('images/server.png') }}" class="server-info-img" style="margin-right: 5px">
                    <img id="deleteServerButton" src="{{ asset('images/delete.png') }}" onclick="ajaxManager.deleteServerIconClicked()" class="imgLink20" style="float: right; display: none; margin-right: 10px" title="Delete server">                                                    
                    <img id="editServerButton" src="{{ asset('images/edit.png') }}" onclick="ajaxManager.editServerIconClicked()" class="imgLink20" style="float: right; display: none; margin-right: 10px" title="Edit server information">            
                    <strong>Server Information</strong>
                </div>
            </div>
            <div class="panel-body">
                <div class="panel panel-success">
                    <div class="panel-heading">Services<span title="Add service" class="glyphicon glyphicon-plus add-item-icon" aria-hidden="true" style="display: none" id="add-service-icon" onclick="ajaxManager.addServiceIconClicked()"></span></div>
                    <div class="panel-body panel-no-padding">
                        <table class="table table-condensed table-bordered table-hover" id="services-list-table">
                            <thead>
                                <th></th>
                                <th>Service Type</th>
                                <th>Version</th>
                                <th>Response Time</th>
                                <th>Status</th>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel panel-success">
                    <div class="panel-heading">Webapps<span title="Add webapp" class="glyphicon glyphicon-plus add-item-icon" aria-hidden="true" style="display: none" id="add-webapp-icon" onclick="ajaxManager.addWebappIconClicked()"></span></div>
                    <div class="panel-body panel-no-padding">
                        <table class="table table-condensed table-bordered table-hover" id="webapps-list-table">
                            <thead>
                                <th></th>
                                <th>URL</th>          
                                <th>Developer</th>
                                <th>Response Time</th>
                                <th>Status</th>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel panel-success">
                    <div class="panel-heading">Databases<span title="Add database" class="glyphicon glyphicon-plus add-item-icon" aria-hidden="true" style="display: none" id="add-database-icon" onclick="ajaxManager.addDatabaseIconClicked()"></span></div>
                    <div class="panel-body panel-no-padding">
                        <table class="table table-condensed table-bordered table-hover" id="databases-list-table">
                            <thead>
                                <th></th>
                                <th>Database name</th>
                                <th>Related web app</th>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>     
    </div>
</div>

@include('forms.add_domain')
@include('forms.add_server')
@include('forms.edit_server')
@include('forms.delete_server')
@include('forms.add_service')
@include('forms.add_webapp')
@include('forms.add_database')
@include('forms.service_info')
@include('forms.webapp_info')
@include('forms.database_info')


<script type="text/javascript">   
    ajaxManager.initializeHomePage();
</script>