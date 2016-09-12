<div class="container">
    
    <div class="panel panel-warning" style="margin-top: 30px">
        <div class="panel-heading">Administration Delegations</div>
        <div class="panel-body">
            <table class="table table-hover" id="delegate-items-table">
                <tbody>

                </tbody>
            </table> 
        </div>
    </div>       
    
</div>

<div id="newDelegationDialog" class="modal fade" style="display: none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">New delegation</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'new_delegation_form')) }}
                     
                    <div class="form-group">
                        <label for="dtype" class="col-sm-5 control-label">Delegation Type</label>
                        <div class="col-sm-4">
                          {{ Form::text('dtype','',array('class'=>'form-control','disabled'=>true)) }}                            
                        </div>                                    
                    </div>   
                
                    <div class="form-group">
                        <label for="ditem" class="col-sm-5 control-label">Item</label>
                        <div class="col-sm-3">
                          {{ Form::text('ditem','',array('class'=>'form-control','disabled'=>true)) }}              
                        </div>            
                    </div>                     
                
                    <div class="form-group">
                        <label for="duser" class="col-sm-5 control-label">User</label>
                        <div class="col-sm-5">
                          {{ Form::select('duser',array(),null,array('class'=>'form-control')) }}              
                        </div>            
                    </div> 

                    <div id='result_div' style='margin-top: 20px'></div>

                {{ Form::close() }}  
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="ajaxManager.addDelegationModalSubmit()">Save</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">    
    ajaxManager.initializeDelegationsPage();
</script>

{{ Form::hidden('_token',csrf_token(),array('id'=>'page_token')) }}