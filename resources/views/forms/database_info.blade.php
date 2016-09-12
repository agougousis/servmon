<div id="databaseInfoDialog" class="modal fade" style="display: none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Database information</h4>
            </div>
            <div class="modal-body">
                {{ Form::hidden('databaseId','',array('class'=>'form-control','style'=>'display:none')) }}
                {{ Form::hidden('origServer','',array('class'=>'form-control','style'=>'display:none')) }}
                <table class="table">
                    <tbody>
                        <tr>
                            <td>dbname</td>
                            <td>{{ Form::text('dbname','',array('class'=>'form-control')) }} </td>
                        </tr>
                        <tr>
                            <td>Server</td>
                            <td>{{ Form::select('server',array(),'',array('class'=>'form-control')) }}</td>
                        </tr>
                        <tr>
                            <td>Type</td>
                            <td>{{ Form::select('type',array(),'',array('class'=>'form-control')) }} </td>
                        </tr>                        
                        <tr>
                            <td>Related Web application</td>
                            <td>{{ Form::select('related_webapp',array(),'',array('class'=>'form-control')) }} </td>
                        </tr>
                    </tbody>
                </table>                                   

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="ajaxManager.deleteDatabaseModalSubmit()" style="float: left">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="update_database_button" onclick="ajaxManager.editDatabaseModalSubmit()">Save</button>
            </div>
        </div>
    </div>
</div>


