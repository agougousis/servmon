<div id="addDatabaseDialog" class="modal fade" style="display: none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add a new Database</h4>
            </div>
            <div class="modal-body">                
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Server <mq>*</mq></td>
                            <td>{{ Form::text('server','',array('class'=>'form-control','disabled'=>'disabled')) }}</td>
                        </tr>
                        <tr>
                            <td>Database name <mq>*</mq></td>
                            <td>{{ Form::text('dbname','',array('class'=>'form-control')) }} </td>
                        </tr>
                        <tr>
                            <td>Choose a database type from the list <mq>*</mq></td>
                            <td>
                                {{ Form::select('type',array(),'',array('class'=>'form-control')) }} 
                            </td>
                        </tr>                        
                        <tr>
                            <td>Related webapp</td>
                            <td>
                                {{ Form::text('related_webapp','',array('class'=>'form-control','autocomplete'=>'off','disabled'=>'disabled','style'=>'width: 80%; display: inline-block')) }} 
                                <div onclick="ajaxManager.selectWebappIconClicked()" class="btn btn-default" style="display:inline-block" title="Select a deployed web application"><span class="glyphicon glyphicon-zoom-in"></span></div>
                            </td>
                        </tr>
                    </tbody>
                </table>                                   

                <div id="webapp-list-div" style="display: none">
                    <span style="color: blue">Click one of the web applications in the list:</span>
                    <ul class="b-list-box" id="webapp-list-box"></ul>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="ajaxManager.addDatabaseModalSubmit()">Save</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    function add_webapp_selection_listener(){
     
        $('#webapp-list-box li').on('click',function(){        
            $('#addDatabaseDialog input[name="related_webapp"]').val($(this).text());
            $('#webapp-list-div').hide();
        });
     
    } 

</script>

