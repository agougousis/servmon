<script type="text/javascript" src="{{ asset('js/per_page/reminder.js') }}"></script>

<br>
<div style="text-align: center; margin-top: 40px;">
    <div class="alert alert-success" style="display:inline-block"> 
        You have requested a password reset.<br>
        A message has been sent to your <strong>{{ Session::get('email') }}</strong> mailbox containing a 
        link to a page where you can set a new passowrd. <br>             
    </div>
</div>

