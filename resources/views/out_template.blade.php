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
        <script type="text/javascript" src="{{ asset('js/jquery-1.11.2.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/bootstrap.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/bootstrap-toggle.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/jstree.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/toastr.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/monitor.js') }}"></script>
    </head>
    <body>        
        
        {!! $content !!}      
           
        {{ Form::hidden('_token',csrf_token(),array('id'=>'page_token')) }}   
        <img src="{{ asset('/images/loading.gif') }}" style="display:none" id="loading-image" />
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

</html>
