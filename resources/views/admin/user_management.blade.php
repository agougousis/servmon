<script type="text/javascript" src="{{ asset('js/per_page/users_page.js') }}"></script>

<div class="container">

    <div class="row">
        <div class="col-md-12">
            
            <div class="panel panel-warning" style="margin-top: 30px">
                <div class="panel-heading">User Management</div>
                <div class="panel-body">
                    <div style='text-align: right; margin-bottom: 10px'>
                        <div id="addUserButton" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addUserDialog">Add new User</div>
                    </div>
                    <table class='table table-bordered table-condensed' id="usersTable">
                        <thead>
                            <th></th>
                            <th>First name</th>
                            <th>Last name</th>
                            <th>E-mail</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                            <th>Last login</th>
                            <th>Actions</th>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>       
                                    
        </div>
    </div>
    
</div>

<!-- Add user Modal -->
<div class="modal fade" id="addUserDialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cancel</span></button>
        <h4 class="modal-title" id="myModalLabel"><img src="{{ asset('images/add_user.png') }}" style="display: inline; height: 20px; margin-right: 10px">Add new user</h4>
      </div>
      <div class="modal-body">
        {{ Form::open(array('id'=>'addUserForm')) }}

        <table class="table borderless_td">
            <tr>
                <td style="width: 50%">
                    {{ Form::label('email','E-mail') }} <mq>*</mq>
                    {{ Form::text('email','',array('class'=>'form-control')) }}                    
                </td>
                <td>                        
                                       
                </td>
            </tr>
            <tr>
                <td>
                    {{ Form::label('password','Password') }} <mq>*</mq>
                    {{ Form::password('password',array('class'=>'form-control','autocomplete'=>'off')) }}       
                </td>
                <td>
                    {{ Form::label('verify_password','Verify Password') }} <mq>*</mq>
                    {{ Form::password('verify_password',array('class'=>'form-control','autocomplete'=>'off')) }}                       
                </td>
            </tr>
            <tr>
                <td>
                    {{ Form::label('lastname','Last name') }} <mq>*</mq>
                    {{ Form::text('lastname','',array('class'=>'form-control')) }}                    
                </td>
                <td>
                    {{ Form::label('firstname','First name') }} <mq>*</mq>
                    {{ Form::text('firstname','',array('class'=>'form-control')) }} 
                </td>
            </tr>
        </table>

        {{ Form::close() }}
        <div id="addUserErrors" style="text-align: center; color: red;"></div>
      </div>
      <div class="modal-footer" style="margin-top:0px">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" id="add_user_confirm" class="btn btn-primary" onclick="ajaxManager.addUserModalSubmit()">Submit</button>
      </div>
    </div>
  </div>
</div>

<!-- Delete user Modal -->
<div class="modal fade" id="deleteUserDialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color: salmon">        
        <h4 class="modal-title" id="myModalLabel">User deletion</h4>
      </div>
      <div class="modal-body">
        {{ Form::open(array('id'=>'deleteUserForm','name'=>'deleteUserForm')) }}

        <span class="label label-danger">Warning!</span> The user <span id="user_fullname" style="font-weight: bold"></span> with e-mail address <span id="user_email" style="font-weight: bold"></span> will be completely deleted!
        <input type="hidden" name="delete_user_id" value="">        

        {{ Form::close() }}        
      </div>
      <div class="modal-footer" style="margin-top:0px">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="deleteUserConfirmButton" onclick="ajaxManager.deleteUserModalSubmit()">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Change superuser status modal -->
<div class="modal fade" id="superuserDialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color: salmon">        
        <h4 class="modal-title" id="myModalLabel">Change superuser status</h4>
      </div>
      <div class="modal-body">
        {{ Form::open(array('id'=>'superuserForm','name'=>'superuserForm')) }}

        <span class="label label-danger">Warning!</span> <span id="change_status_message"></span>
        <input type="hidden" name="user_id" value="">        
        <input type="hidden" name="new_superuser_status" value="">

        {{ Form::close() }}        
      </div>
      <div class="modal-footer" style="margin-top:0px">
        <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
        <button type="button" class="btn btn-primary" onclick="ajaxManager.superuserModalSubmit();" id="superuserConfirmButton">Yes</button>
      </div>
    </div>
  </div>
</div>
  
<script type="text/javascript">   
    ajaxManager.initializeUserManagementPage();
</script>
