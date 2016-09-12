<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>{{ $title }}</title>
        <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/main.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/toastr.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap-toggle.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/tree-themes/default/style.min.css') }}" />        
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <script type="text/javascript" src="{{ asset('js/jquery-1.11.2.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/bootstrap.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/bootstrap-toggle.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/jstree.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/toastr.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/monitor.js') }}"></script>
    </head>
    <body>        
        
        <nav class="navbar navbar-default navbar-fixed-top" style="margin-top: 10px">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#"><img src="{{ asset('images/servmon_logo.png') }}" style="height:27px"></a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a href="{{ url('/') }}" class="speedLink" title="Home">
                                <span class="glyphicon glyphicon-home" aria-hidden="true" style="font-size: 20px; top: 4px; padding-bottom: 6px"></span>
                            </a>
                        </li>  
                        @if($isSuperuser)
                        <li> 
                            <a href="{{ url('user_management') }}" class="speedLink" title="User administration">
                                <span class="glyphicon glyphicon-user" aria-hidden="true" style="font-size: 20px; top: 4px; padding-bottom: 6px"></span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('domains/delegation') }}" class="speedLink" title="Administration Delegation">
                                <span class="glyphicon glyphicon-copy" aria-hidden="true" style="font-size: 20px; top: 4px; padding-bottom: 6px"></span>
                            </a>
                        </li>  
                        <li>
                            <a href="{{ url('backup') }}" class="speedLink" title="System Backup">
                                <span class="glyphicon glyphicon-save" aria-hidden="true" style="font-size: 20px; top: 4px; padding-bottom: 6px"></span>
                            </a>
                        </li>                                    
                        <li>
                            <a href="{{ url('monitor/configure') }}" class="speedLink" title="Configure monitoring">                                
                                <i class="small material-icons">settings</i>
                            </a>
                        </li>
                        @endif
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ (Auth::user()->firstname)." ".(Auth::user()->lastname) }} <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ url('profile') }}">My Profile</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#" onclick="ajaxManager.logout()">                                        
                                        <span class="glyphicon glyphicon-log-out" aria-hidden="true" style="margin-right: 5px"></span>Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
        
        {!! $content !!}      
           
        <img src="{{ asset('/images/loading.gif') }}" style="display:none" id="loading-image" />
        {{ Form::hidden('_token',csrf_token(),array('id'=>'page_token')) }}        
    </body>
    <script type="text/javascript">
        if(localStorage.getItem("success_toastr") !== null ) {
             toastr.success(localStorage.getItem("success_toastr"));
             localStorage.clear();
        }
        if(localStorage.getItem("failure_toastr") !== null ) {
             toastr.danger(localStorage.getItem("failure_toastr"));
             localStorage.clear();
        }
    </script>
    
    <script type="text/javascript">
        // Enable the bootstrap popovers
        $(document).ready(function(){
              $('[data-toggle="popover"]').popover();   
        });
        
    </script>
</html>
