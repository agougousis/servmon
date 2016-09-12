<!-- Delete server Modal -->
<div class="modal fade" id="deleteServerDialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color: salmon">        
        <h4 class="modal-title" id="myModalLabel">Server deletion</h4>
      </div>
      <div class="modal-body">
        {{ Form::open(array('url'=>'','id'=>'deleteServerForm','name'=>'deleteServerForm')) }}

        <span class="label label-danger">Warning!</span> The server <span id="server_span_name" style="font-weight: bold"></span> along with any delegation related to it will be completely deleted!
        <input type="hidden" name="delete_server_id" value="">        

        {{ Form::close() }}        
      </div>
      <div class="modal-footer" style="margin-top:0px">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="delete_server_confirm_button" onclick="ajaxManager.deleteServerModalSubmit()">Delete</button>
      </div>
    </div>
  </div>
</div>
