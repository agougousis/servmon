<script type="text/javascript" src="{{ asset('js/per_page/users_page.js') }}"></script>

<div class="container">

    <div class="row">
        <div class="col-md-12">
            
            <div class="panel panel-warning" style="margin-top: 30px">
                <div class="panel-heading">User Profile</div>
                <div class="panel-body">
                    <form class="form-horizontal" id="user_profile_form">

                        <div class='row'>

                            <div class="col-md-6">
                                <div style='text-align: center; margin-bottom: 30px'>
                                    <img src="{{ asset('images/user-info.png') }}" class="img-rounded">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="col-sm-4 control-label">Status</label>
                                    <div class="col-sm-8" id="user_status_div">
                                      
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="col-sm-4 control-label">E-mail</label>
                                    <div class="col-sm-8">
                                      <input type="text" class="form-control" name="email" value="">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class='row'>

                            <div class="col-md-6">            

                                <div class="form-group">
                                    <label for="firstname" class="col-sm-4 control-label">First name</label>
                                    <div class="col-sm-8">
                                      <input type="text" class="form-control" name="firstname" value="">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="lastname" class="col-sm-4 control-label">Last name</label>
                                    <div class="col-sm-8">
                                      <input type="text" class="form-control" name="lastname" value="">
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-6">                        

                                <div class="form-group">
                                    <label for="registration_date" class="col-sm-4 control-label">Registration Date</label>
                                    <div class="col-sm-8">
                                      <input type="text" class="form-control" name="registration_date" value="">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="last_login" class="col-sm-4 control-label">Last Login</label>
                                    <div class="col-sm-8">
                                      <input type="text" class="form-control" name="last_login" value="">
                                    </div>
                                </div>

                            </div>       

                        </div>

                    </form>
                </div>
            </div>
                        
        </div>
    </div>
    
</div>

<script type="text/javascript">    
    ajaxManager.initializeUserProfilePage({{ $user_id }});
</script>
