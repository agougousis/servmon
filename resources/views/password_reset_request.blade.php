<script type="text/javascript" src="{{ asset('js/per_page/reminder.js') }}"></script>

<div class="panel panel-default" id="password-reset-panel" style="color: black; width: 550px">
    <div class="panel-body">

        <div style="text-align: center; margin-bottom: 20px">
            <a href='{{ url('/') }}'>
            <img src="{{ asset('images/servmon_logo.png') }}">
            </a>
        </div>

        {{ Form::open(array('class'=>'form-horizontal','id'=>'password_request_form')) }}

            <div class="form-group">
                <label for="email" class="col-sm-4 control-label">Your E-mail</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="email" name='email' placeholder="Your registration e-mail">
                  {{ $errors->first('new_password',"<span style='color:red'>:message</span>") }}
                </div>
            </div>
            <div class="form-group">
                <label for="captcha" class="col-sm-4 control-label">Fill in the image text:</label>
                <div class="col-sm-8">
                    <table style="width: 100%; margin-bottom: 10px" id="captcha_table">
                        <tr>
                            <td style="width: 150px; padding-left: 0px">
                                {{ Form::text('captcha','',array('class'=>'form-control')) }}
                            </td>
                            <td>
                                {!! captcha_img() !!}
                                <div title="Refresh image" class="btn btn-sm btn-default" onclick="javascript:refresh_captcha()"><span class="glyphicon glyphicon-repeat"></span></div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                {{ $errors->first('captcha',"<span style='color:red'>:message</span>") }}
                            </td>
                        </tr>
                    </table>
                    {{ $errors->first('new_password',"<span style='color:red'>:message</span>") }}
                </div>
            </div>

            <div style='text-align: center'>
                <div class='btn btn-default' id="password_request_form_button" onclick="ajaxManager.requestPasswordReset()">Reset password</div>
            </div>

        {{ Form::close() }}

    </div>
</div>

<script type="text/javascript">
    $('#password-reset-panel').center();

    function refresh_captcha(){
        var formURL = "{{ url('new_captcha_link') }}";
        $.get(formURL).done(function( data ) {
                $('#captcha_table img').attr('src',data);
            }
        );
    }
</script>