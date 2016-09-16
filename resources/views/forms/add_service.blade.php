<div id="addServiceDialog" class="modal fade" style="display: none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add a new Service</h4>
            </div>
            <div class="modal-body">                
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Server <mq>*</mq></td>
                            <td>{{ Form::text('server','',array('class'=>'form-control','disabled'=>'disabled')) }}</td>
                        </tr>
                        <tr>
                            <td>Service type <mq>*</mq></td>
                            <td>
                                {{ Form::select('stype',array(),'',array('class'=>'form-control')) }} 
                            </td>
                        </tr>
                        <tr>
                            <td>Port <mq>*</mq></td>
                            <td>{{ Form::text('port','',array('class'=>'form-control')) }} </td>
                        </tr>
                        <tr>
                            <td>Version <mq>*</mq></td>
                            <td>{{ Form::text('version','',array('class'=>'form-control')) }} </td>
                        </tr>
                    </tbody>
                </table>                                   

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="add_service_button" onclick="ajaxManager.addServiceModalSubmit()">Save</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">    

    $('#addServiceDialog select[name="stype"]').on('change',function(){
        var serviceName = $(this).val();
        $('#addServiceDialog input[name="port"]').val(ajaxManager.supported_service_types[serviceName].default_port);
    });


</script>

