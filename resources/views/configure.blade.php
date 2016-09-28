<script type="text/javascript" src="{{ asset('js/per_page/configuration_page.js') }}"></script>

<div class="container">

    <div class="panel panel-info" style="margin-top: 30px">
        <div class="panel-heading">Basic configuration</div>
        <div class="panel-body">
            {{ Form::open(array('class'=>'form-horizontal','id'=>'changeStatusForm')) }}
                <input id="monitoringButton" name="monitoring_status" data-toggle="toggle" data-on="Monitoring On" data-off="Monitoring Off" type="checkbox">
                Monitoring Period: {{ Form::select('monitoring_period',array('10'=>'10 min','30'=>'30 min','60'=>'1 hour'),'',array("autocomplete"=>"off")) }}                        
                <div class="btn btn-primary" style="float: right; margin-right: 20px" onclick="ajaxManager.monitoringStatusButtonClicked()">Save State</div>
            {{ Form::close() }}
            <div style="clear: right"></div>
        </div>
    </div>                        

    <div class="panel panel-warning">
        <div class="panel-heading">Monitoring Items</div>
        <div class="panel-body">
            {{ Form::open(array('class'=>'form-horizontal','id'=>'monitoringForm')) }}
            <table class="table table-hover" id="monitor-items-table">
                <tbody>

                </tbody>
            </table>
            <div style="text-align: right; margin-bottom: 10px"><div class="btn btn-sm btn-primary" onclick="ajaxManager.updateConfigurationButtonClicked()">Update Configuration</div></div>
            {{ Form::close() }}
        </div>
    </div>

</div>


<script type="text/javascript">

    ajaxManager.initializeConfigurationPage();

    $('#monitoringButton').change(function() {
        var newState = $(this).prop('checked');  // true/false
    })

</script>
