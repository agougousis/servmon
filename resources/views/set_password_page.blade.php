<script type="text/javascript" src="{{ asset('js/per_page/reminder.js') }}"></script>

{{ Form::open(array('class'=>'form-horizontal','style'=>'color:white')) }}

<div class="panel panel-default" id="password-reset-panel" style="color: black; width: 400px">
    <div class="panel-body">

        <div style="text-align: center; margin-bottom: 20px"><img src="{{ asset('images/servmon_logo.png') }}"></div>
        
        <div class="form-group">
            <label for="new_passowrd" class="col-sm-4 control-label">New password</label>
            <div class="col-sm-8">
              <input type="password" class="form-control" id="new_passowrd" name='new_password'>
              {{ $errors->first('new_password',"<span style='color:red'>:message</span>") }}
            </div>                
        </div>

        <div class="form-group">
            <label for="repeat_password" class="col-sm-4 control-label">Repeat password</label>
            <div class="col-sm-8">
              <input type="password" class="form-control" id="repeat_password" name='repeat_password'>
              {{ $errors->first('repeat_password',"<span style='color:red'>:message</span>") }}
            </div>                
        </div>    
        
        <div style='text-align: center'>
            <div class='btn btn-default' onclick="ajaxManager.setNewPassword('{{ $code }}')">Reset password</div>
        </div>
        
    </div>
</div>

{{ Form::close() }}

<script type="text/javascript">
    $('#password-reset-panel').center();    
</script>
