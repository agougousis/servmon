<div id="addWebappDialog" class="modal fade" style="display: none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add a new Webapp</h4>
            </div>
            <div class="modal-body">                
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Server</td>
                            <td>{{ Form::text('server','',array('class'=>'form-control','disabled'=>'disabled')) }}</td>
                        </tr>
                        <tr>
                            <td>URL</td>
                            <td>{{ Form::text('url','',array('class'=>'form-control')) }} </td>
                        </tr>
                        <tr>
                            <td>Choose the web app language</td>
                            <td>
                                {{ Form::select('language',array(),'',array('class'=>'form-control')) }} 
                            </td>
                        </tr>                        
                        <tr>
                            <td>Developer</td>
                            <td>{{ Form::text('developer','',array('class'=>'form-control')) }} </td>
                        </tr>
                        <tr>
                            <td>Contact</td>
                            <td>{{ Form::text('contact','',array('class'=>'form-control')) }} </td>
                        </tr>
                    </tbody>
                </table>                                   

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="add_webapp_button" onclick="ajaxManager.addWebappModalSubmit()">Save</button>
            </div>
        </div>
    </div>
</div>

