<div id="addDomainDialog" class="modal fade" style="display: none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add a new Domain</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'add_domain_form')) }}
                     
                    <div class="form-group">
                        <label for="id" class="col-sm-5 control-label">Short domain name <mq>*</mq></label>
                        <div class="col-sm-3">
                          {{ Form::text('node_name','',array('class'=>'form-control')) }}              
                        </div>            
                    </div> 

                    <div class="form-group">
                        <label for="parent_domain" class="col-sm-5 control-label">Parent domain <mq>*</mq></label>
                        <div class="col-sm-4">
                          {{ Form::text('parent_domain','',array('class'=>'form-control','disabled'=>true)) }}                            
                        </div>                                    
                    </div>   
                
                    <div class="form-group">
                        <label for="fake_domain" class="col-sm-5 control-label" style="padding-top: 0px">Fake domain <br>(just for server grouping)</label>
                        <div class="col-sm-4">
                          {{ Form::checkbox('fake_domain',1,false) }}                            
                        </div>                                    
                    </div> 

                    <div id='result_div' style='margin-top: 20px'></div>

                {{ Form::close() }}  
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="add_domain_confirm_button" onclick="ajaxManager.addDomainModalSubmit()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete domain Modal -->
<div class="modal fade" id="deleteDomainDialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color: salmon">        
        <h4 class="modal-title" id="myModalLabel">Domain deletion</h4>
      </div>
      <div class="modal-body">
        {{ Form::open(array('url'=>'','id'=>'deleteDomainForm','name'=>'deleteDomainForm')) }}

        <span class="label label-danger">Warning!</span> The domain <span id="domain_span_fullname" style="font-weight: bold"></span> along with every delegation related to it will be completely deleted!
        <input type="hidden" name="delete_domain_name" value="">        

        {{ Form::close() }}        
      </div>
      <div class="modal-footer" style="margin-top:0px">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="delete_domain_confirm_button" onclick="ajaxManager.deleteDomainModalSubmit()">Delete</button>
      </div>
    </div>
  </div>
</div>
