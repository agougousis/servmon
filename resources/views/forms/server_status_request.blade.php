<div id="serverStatusRequestDialog" class="modal fade" style="display: none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Insert SSH credentials</h4>
            </div>
            <div class="modal-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Server</td>
                            <td>{{ Form::text('server','',array('class'=>'form-control','disabled'=>'disabled')) }}</td>
                        </tr>
                        <tr>
                            <td>SSH port <mq>*</mq></td>
                            <td>{{ Form::text('sshport','22',array('class'=>'form-control')) }}</td>
                        </tr>
                        <tr>
                            <td>Username <mq>*</mq></td>
                            <td>{{ Form::text('sshuser','',array('class'=>'form-control')) }}</td>
                        </tr>
                        <tr>
                            <td>Password <mq>*</mq></td>
                            <td>{{ Form::password('sshpass',array('class'=>'form-control')) }} </td>
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


