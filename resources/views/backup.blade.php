<script type="text/javascript" src="{{ asset('js/per_page/backups_page.js') }}"></script>

<div class="container">

    <div class="row">
        <div class="col-md-6">
             <div class="panel panel-info" style="margin-top: 30px">
                 <div class="panel-heading">New backup</div>
                <div class="panel-body">
                    <p>The following items will be backup up:</p>

                    <ul>
                        <li id="domains_counter"></li>
                        <li id="servers_counter"></li>
                        <li id="services_counter"></li>
                        <li id="webapps_counter"></li>
                        <li id="databases_counter"></li>
                    </ul>

                    <div style="text-align: right">
                        {{ Form::open(array('id'=>'createBackupForm','name'=>'createBackupForm')) }}
                        <button type='button' class="btn btn-primary" id="createBackupButton" onclick="ajaxManager.createBackupIconClicked()">Backup</button>
                        {{ Form::close() }}
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-info" style="margin-top: 30px">
                 <div class="panel-heading">Backup history</div>
                 <div class="panel-body">
                     <table class="table table-bordered table-condensed" id="backup-list-table">
                         <thead>
                            <th>Date</th>
                            <th>Size (in KB)</th>
                            <th></th>
                         </thead>
                         <tbody>

                         </tbody>
                     </table>
                 </div>
            </div>
        </div>
    </div>

</div>

<!-- Delete backup Modal -->
<div class="modal fade" id="deleteBackupDialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color: salmon">
        <h4 class="modal-title" id="myModalLabel">Backup deletion</h4>
      </div>
      <div class="modal-body">
        {{ Form::open(array('id'=>'deleteBackupForm','name'=>'deleteBackupForm')) }}

        <span class="label label-danger">Warning!</span> The backup file <span id="backup_filename_to_delete" style="font-weight: bold"></span> will be completely deleted!
        <input type="hidden" name="delete_backup_filename" value="">

        {{ Form::close() }}
      </div>
      <div class="modal-footer" style="margin-top:0px">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="deleteBackupButton" onclick="ajaxManager.deleteBackupModalSubmit()">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Restore backup Modal -->
<div class="modal fade" id="restoreBackupDialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color: salmon">
        <h4 class="modal-title" id="myModalLabel">Backup restore</h4>
      </div>
      <div class="modal-body">
        {{ Form::open(array('id'=>'restoreBackupForm','name'=>'restoreBackupForm')) }}

        <span class="label label-danger">Warning!</span> The backup file <span id="backup_filename_to_restore" style="font-weight: bold"></span> will be restored and the current database contents will be replaced deleted!
        <input type="hidden" name="restore_backup_filename" value="">

        {{ Form::close() }}
      </div>
      <div class="modal-footer" style="margin-top:0px">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id=restoreBackupButton" onclick="ajaxManager.restoreBackupModalSubmit()">Restore</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
    ajaxManager.initializeBackupPage();
</script>