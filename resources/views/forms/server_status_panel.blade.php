<script src="{{ asset('js/raphael-2.1.4.min.js') }}"></script>
<script src="{{ asset('js/justgage.js') }}"></script>
<div id="serverStatusPanel" class="modal fade" style="display: none">
    <div class="modal-dialog wider-modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Server Status</h4>
            </div>
            <div class="modal-body">
                <table class=" table server-panel-table">
                    <tbody>
                        <tr>
                            <td width="20%">
                                <div class="status-panel-header">UPTIME</div>
                                <div id="uptime-holder"></div>
                            </td>
                            <td colspan="2">
                                <div class="status-panel-header">MEMORY<img title="Show details" class="details-icon" src="{{ asset('images/details-off.png') }}"></div>
                                <div class="details-div" style='display: none'>
                                    The progress displays the percentage of physical memory that is reserved. The calculation is made
                                    using the 'free' command.
                                </div>
                                <table class=" table server-panel-table">
                                    <tbody>
                                        <tr>
                                            <td>0</td>
                                            <td width="80%">
                                                <div class="progress">
                                                    <div id="memory-usage-holder" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">
                                                        Reserved: 0%
                                                    </div>
                                                </div>
                                            </td>
                                            <td id="total-memory-holder" style="width: 80px"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="status-panel-header">CPU<img title="Show details" class="details-icon" src="{{ asset('images/details-off.png') }}"></div>
                <div class="details-div" style='display: none'>
                    The number of processors is extracted through the following command: grep 'model name' /proc/cpuinfo | wc -l.<br>
                    The CPU is retrieved by the 'uptime' command. The gauge considers as maximum value a full load for all processors.
                    Of course, this is not a limit that cannot be surpassed but even if it happens to a server, under normal
                    conditions it should happen rarely.
                    <br><br>
                    <span style="text-decoration: underline">Understanding load average:</span> If we had only one CPU, then average load
                    equal to 1.05 for the last five minutes means that during the last 5 minutes the computer was overloaded by 5% on
                    average. Namely, on average, 0.05 processes were waiting for the CPU. Load average equal to 0.7 means that the CPU
                    idled for 30% of the time. The load average numbers work a bit differently on such a system. For example, if you have
                    a load average of 2 on a single-CPU system, this means your system was overloaded by 100 percent — the entire period
                    of time, one process was using the CPU while one other process was waiting.
                </div>
                <table class="normal-table">
                    <tr>
                        <td style="width: 200px">
                            <div style="margin-bottom: 5px">Number of processors:</div>
                            <div id="cpu-count-holder"></div>
                        </td>
                        <td><div id="gauge5min" style="display: inline-block; width: 230px"></div></td>
                        <td><div id="gauge10min" style="display: inline-block; width: 230px"></div></td>
                    </tr>
                </table>
                <div class="status-panel-header">DISK<img title="Show details" class="details-icon" src="{{ asset('images/details-off.png') }}"></div>
                <div class="details-div" style='display: none'>
                    The disk usage information is extracted using the 'df' command. the <span style='color:blue'>blue</span> bar indicates the blocks
                    usage and the <span style='color:green'>green</span> bar the Inodes usage.
                </div>
                <table class=" table server-panel-table">
                    <thead>
                        <th style="width: 140px">Disk</th>
                        <th style="width: 100px">Mount point</th>
                        <th>Usage</th>
                    </thead>
                    <tbody id="disk-usage-holder">
                    </tbody>
                </table>
                <div class="status-panel-header">NETWORK SERVICES<img title="Show details" class="details-icon" src="{{ asset('images/details-off.png') }}"></div>
                <div class="details-div" style='display: none'>
                    The list of network services is extracted using the 'lsof' command. Duplicate rows has been eliminated, so multiple processes of the same
                    service are not going to be depicted on that list.
                </div>
                <div style="height: 210px; overflow-y: scroll">
                    <table class="table table-condensed">
                        <thead>
                            <th>Command</th>
                            <th>User</th>
                            <th>IP Type</th>
                            <th>Protocol</th>
                            <th>Port</th>
                            <th>Bind Address</th>
                        </thead>
                        <tbody id="server-services-holder">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    var g5,g10;

    function load5minGauge(gValue,maxValue){
        $('#gauge5min').empty();
        g5 = new JustGage({
            id: "gauge5min",
            value: gValue,
            min: 0,
            max: maxValue,
            title: "Last 5 min load",
            label: "",
            startAnimationTime: 2000,
            startAnimationType: ">",
            refreshAnimationTime: 1000,
            refreshAnimationType: "bounce"
        });
    }

    function load10minGauge(gValue,maxValue){
        $('#gauge10min').empty();
        g10 = new JustGage({
            id: "gauge10min",
            value: gValue,
            min: 0,
            max: maxValue,
            title: "Last 10 min load",
            label: "",
            startAnimationTime: 2000,
            startAnimationType: ">",
            refreshAnimationTime: 1000,
            refreshAnimationType: "bounce"
        });
    }

    $('.details-icon').on('click',function(){

        var offImage = "{{ asset('images/details-off.png') }}";
        var onImage = "{{ asset('images/details-on.png') }}";

        // Check the current status
        if($(this).hasClass('clicked-icon')){
            $(this).removeClass('clicked-icon');
            $(this).parent().next('.details-div').hide();
            $(this).prop('title','Show details');
            $(this).prop('src',offImage);
        } else {
            $(this).addClass('clicked-icon');
            $(this).parent().next('.details-div').show();
            $(this).prop('title','Hide details');
            $(this).prop('src',onImage);
        }
    });

</script>