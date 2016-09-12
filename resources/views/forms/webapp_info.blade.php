<div id="webappInfoDialog" class="modal fade" style="display: none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Web app information</h4>
            </div>
            <div class="modal-body">
                 {{ Form::hidden('appId','',array('class'=>'form-control','style'=>'display:none')) }}
                 {{ Form::hidden('origServer','',array('class'=>'form-control','style'=>'display:none')) }}
                <table class="table">
                    <tbody>
                        <tr>
                            <td>URL</td>
                            <td>{{ Form::text('url','',array('class'=>'form-control')) }}</td>
                        </tr>
                        <tr>
                            <td>Language</td>
                            <td>{{ Form::select('language',array(),'',array('class'=>'form-control')) }} </td>
                        </tr>
                        <tr>
                            <td>Developer</td>
                            <td>{{ Form::text('developer','',array('class'=>'form-control')) }} </td>
                        </tr>
                        <tr>
                            <td>Contact</td>
                            <td>{{ Form::text('contact','',array('class'=>'form-control')) }} </td>
                        </tr>
                        <tr>
                            <td>Server</td>
                            <td>{{ Form::select('server',array(),'',array('class'=>'form-control')) }} </td>
                        </tr>
                    </tbody>
                </table>                                   

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="ajaxManager.deleteWebappModalSubmit()" style="float: left">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="update_webapp_button" onclick="ajaxManager.editWebappModalSubmit()">Save</button>
            </div>
        </div>
    </div>
</div>

