<style type="text/css">
    #login-panel {
        width: 400px;
    }
</style>


{{ Form::open(array('class'=>'form-horizontal','style'=>'color:white')) }}

<div class="panel panel-default" id="login-panel" style="color: black">
    <div class="panel-body">

        <div style="text-align: center; margin-bottom: 20px">
            <img src="{{ asset('images/servmon.png') }}">
        </div>
        
        <div style="text-align: center; margin-bottom: 20px"><img src="{{ asset('images/servmon_logo.png') }}"></div>
        
            <div class="form-group">                
                <label for="inputEmail" class="col-sm-2 control-label"><img src="{{ asset('images/email.png') }}" style="height: 26px"></label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" id="inputEmail" name="inputEmail" placeholder="Email">                    
                </div>
            </div>
            <div class="form-group">
                <label for="inputPassword" class="col-sm-2 control-label"><img src="{{ asset('images/password.png') }}" style="height: 26px"></label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="inputPassword" name="inputPassword" placeholder="Password">                    
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="btn btn-primary" style="width: 100%" onclick="ajaxManager.login()">Sign in</div>
                </div>
            </div>
            <div class="col-sm-offset-2 col-sm-10" style="text-align: right"><a href="">Forgot your password?</a></div>
        
    </div>
</div>

{{ Form::close() }}

<script type="text/javascript">
    $('#login-panel').center();    
</script>