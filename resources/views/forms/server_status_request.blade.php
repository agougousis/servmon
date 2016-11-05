<div id="serverStatusRequestDialog" class="modal fade" style="display: none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Insert SSH credentials</h4>
            </div>
            <div class="modal-body">
                <table class="table table-condensed no-top-border">
                    <tbody>
                        <tr>
                            <td colspan="3" style="font-weight: bold">Connect to:</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="min-width: 110px">Server</td>
                            <td>{{ Form::text('server','',array('class'=>'form-control','disabled'=>'disabled')) }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>SSH port <mq>*</mq></td>
                            <td>{{ Form::text('sshport','22',array('class'=>'form-control')) }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Username <mq>*</mq></td>
                            <td>{{ Form::text('sshuser','',array('class'=>'form-control')) }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Password</td>
                            <td>
                                {{ Form::password('sshpass',array('class'=>'form-control')) }}
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                <span style="color:gray">The password field is required if your
                                are using Password Authentication <strong>OR</strong> the account
                                that will be used with RSA Key Authentication is not the root account.</span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="font-weight: bold">Connect with:</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td colspan="2">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="sshAuthType" id="passwordAuth" value="passwordAuth" checked>
                                        <span style="color: #800000">Password Authentication</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td colspan="2">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="sshAuthType" id="rsaAuth" value="rsaAuth" style="color: blue">
                                        <span style="color: #800000">Public Key (RSA)</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>SSH Key <mq>*</mq></td>
                            <td>{{ Form::text('sshkey','',array('class'=>'form-control','disabled'=>'disabled','placeholder'=>'Type the key file path')) }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                <span style="color:gray">This should be the path for private key but we expect to find
                                the public key too, by adding a ".pub" extension to this path.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="ajaxManager.serverStatusModalSubmit()">Load</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#passwordAuth').on('click',function(){
        $("#serverStatusRequestDialog input[name='sshkey']").prop('disabled',true);
    });
    $('#rsaAuth').on('click',function(){
        $("#serverStatusRequestDialog input[name='sshkey']").prop('disabled',false);
    });
</script>
