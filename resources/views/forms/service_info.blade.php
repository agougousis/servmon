<div id="serviceInfoDialog" class="modal fade" style="display: none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Service information</h4>
            </div>
            <div class="modal-body">
                {{ Form::hidden('serviceId','',array('class'=>'form-control','style'=>'display:none')) }}
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Server</td>
                            <td>{{ Form::select('server',array(),'',array('class'=>'form-control','disabled'=>'disabled')) }}</td>
                        </tr>
                        <tr>
                            <td>Type</td>
                            <td>{{ Form::select('stype',array(),'',array('class'=>'form-control')) }} </td>
                        </tr>
                        <tr>
                            <td>Port</td>
                            <td>{{ Form::text('port','',array('class'=>'form-control')) }} </td>
                        </tr>
                        <tr>
                            <td>Version</td>
                            <td>{{ Form::text('version','',array('class'=>'form-control')) }} </td>
                        </tr>
                    </tbody>
                </table>                                   

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="ajaxManager.deleteServiceModalSubmit()" style="float: left">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="update_service_button" onclick="ajaxManager.editServiceModalSubmit()">Save</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">    

    $('#serviceInfoDialog select[name="stype"]').on('change',function(){
        var serviceName = $(this).val();
        $('#serviceInfoDialog input[name="port"]').val(ajaxManager.supported_service_types[serviceName].default_port);
    });


</script>