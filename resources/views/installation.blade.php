<style type="text/css">
    #installation-panel {
        width: 500px;
    }
</style>

{{ Form::open(array('class'=>'form-horizontal','style'=>'color:white','id'=>'installation_form')) }}

<div class="panel panel-default" id="installation-panel" style="color: black">
    <div class="panel-body">

        <div style="text-align: center; margin-bottom: 20px">
            <img src="{{ asset('images/configuration.png') }}">
        </div>
        
        <div style="text-align: center; margin-bottom: 20px; font-weight: bold; font-size: 24px">ServMon Installation</div>
        
            <div class="form-group">                
                <label for="url" class="col-sm-3 control-label"><img src="{{ asset('images/url.png') }}" style="height: 26px"></label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="url" name="url" placeholder="Application URL (e.g http://mydomain.com)">                   
                </div>
            </div>
            <div class="form-group">                
                <label for="server" class="col-sm-3 control-label"><img src="{{ asset('images/server.png') }}" style="height: 26px"></label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="server" name="server" placeholder="Database Server">                   
                </div>
            </div>
            <div class="form-group">                
                <label for="dbname" class="col-sm-3 control-label"><img src="{{ asset('images/database.png') }}" style="height: 26px"></label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="dbname" name="dbname" placeholder="Database Name">                   
                </div>
            </div>
            <div class="form-group">
                <label for="dbuser" class="col-sm-3 control-label"><img src="{{ asset('images/user.png') }}" style="height: 26px"></label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="dbuser" name="dbuser" placeholder="Database user">                   
                </div>
            </div>
            <div class="form-group">
                <label for="dbpwd" class="col-sm-3 control-label"><img src="{{ asset('images/password.png') }}" style="height: 26px"></label>
                <div class="col-sm-9">
                    <input type="password" class="form-control" id="dbpwd" name="dbpwd" placeholder="Password">                    
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="btn btn-danger" style="width: 100%" onclick="ajaxManager.installationButtonClicked()">Configure</div>
                </div>
            </div>            
        
    </div>
</div>

{{ Form::close() }}

<script type="text/javascript">
    $('#installation-panel').center();    
</script>